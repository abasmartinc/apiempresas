<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Qué correo automático convierte: envíos, clics y compras por tipo de correo.
 *
 *   php spark email:report              últimos 30 días, en pantalla
 *   php spark email:report 7 --email    últimos 7 días, enviado por correo (el del cron)
 *
 * Los avisos que comparten plantilla (automation_generic, risk_generic) salen por
 * separado: EmailService guarda el tipo de aviso en email_logs.template_slug.
 *
 * Necesita la columna email_logs.template_slug. Las aperturas no se miden a propósito:
 * la copia oculta al admin y la precarga de Apple Mail las inflan y no significan nada.
 */
class EmailReportCommand extends BaseCommand
{
    protected $group       = 'Automation';
    protected $name        = 'email:report';
    protected $description = 'Envíos, clics y compras por tipo de correo automático (en pantalla o por correo con --email).';
    protected $usage       = 'email:report [dias] [--email]';
    protected $options     = ['--email' => 'Envía el informe por correo en vez de solo mostrarlo.'];

    /** Destinatario del informe */
    private const DESTINO = 'papelo.amh@gmail.com';

    public function run(array $params)
    {
        $dias      = max(1, (int) ($params[0] ?? 30));
        $porCorreo = CLI::getOption('email') !== null || in_array('--email', $params, true);
        $desde     = date('Y-m-d H:i:s', strtotime("-{$dias} days"));
        $db        = \Config\Database::connect();

        if (!in_array('template_slug', $db->getFieldNames('email_logs'), true)) {
            CLI::error('Falta la columna email_logs.template_slug. Ejecuta antes:');
            CLI::write('  ALTER TABLE email_logs ADD COLUMN template_slug VARCHAR(64) NULL, ADD INDEX idx_template_slug (template_slug);', 'yellow');
            return;
        }

        $filas = $this->datos($db, $desde);

        $titulo = 'Correos automáticos: ' . ($dias === 7 ? 'última semana' : "últimos {$dias} días")
            . ' (' . date('d/m', strtotime($desde)) . ' - ' . date('d/m') . ')';

        if (empty($filas)) {
            CLI::write("Sin envíos registrados con plantilla en los últimos {$dias} días.", 'yellow');
            if ($porCorreo) {
                $this->enviar($titulo, '<p>No hay envíos registrados en este periodo.</p>');
            }
            return;
        }

        // En pantalla
        $tabla = array_map(fn ($f) => [
            $f['tipo'], $f['enviados'], $f['clics'], $this->pct($f['clics'], $f['enviados']), $f['compras_clic'], $f['compras_14d'],
        ], $filas);
        $tot = $this->totales($filas);
        $tabla[] = ['TOTAL', $tot['enviados'], $tot['clics'], $this->pct($tot['clics'], $tot['enviados']), $tot['compras_clic'], $tot['compras_14d']];

        CLI::write($titulo, 'cyan');
        CLI::table($tabla, ['Correo', 'Enviados', 'Clics', '% clic', 'Compras (clic)', 'Compras (14 d)']);
        CLI::write('Compras (clic): el checkout llevaba source=email_<correo>. Compras (14 d): pagó en los 14 días siguientes al envío, por el camino que fuera.', 'dark_gray');

        if ($porCorreo) {
            $ok = $this->enviar($titulo, $this->html($filas, $tot));
            CLI::write($ok ? 'Informe enviado a ' . self::DESTINO : 'No se pudo enviar el informe (ver log).', $ok ? 'green' : 'red');
        }
    }

    /** Envíos, clics y compras por tipo de correo */
    private function datos($db, string $desde): array
    {
        $filas = $db->query("
            SELECT template_slug AS tipo,
                   COUNT(*)                    AS enviados,
                   SUM(clicked_at IS NOT NULL) AS clics
            FROM email_logs
            WHERE created_at >= ? AND status = 'success' AND template_slug IS NOT NULL
            GROUP BY template_slug
        ", [$desde])->getResultArray();

        // Compras atribuidas directamente (el checkout llevaba source=email_<tipo>)
        $directas = [];
        foreach ($db->query("
            SELECT element, COUNT(*) AS n
            FROM tracking_events
            WHERE event_name = 'checkout_completed' AND created_at >= ? AND element LIKE 'email\\_%'
            GROUP BY element
        ", [$desde])->getResultArray() as $r) {
            $directas[substr($r['element'], 6)] = (int) $r['n'];
        }

        // Compras en los 14 días siguientes al envío, vengan o no del clic
        $en14 = [];
        foreach ($db->query("
            SELECT l.template_slug, COUNT(DISTINCT t.user_id) AS n
            FROM email_logs l
            JOIN tracking_events t
              ON t.user_id = l.user_id
             AND t.event_name = 'checkout_completed'
             AND t.created_at BETWEEN l.created_at AND DATE_ADD(l.created_at, INTERVAL 14 DAY)
            WHERE l.created_at >= ? AND l.status = 'success' AND l.template_slug IS NOT NULL
            GROUP BY l.template_slug
        ", [$desde])->getResultArray() as $r) {
            $en14[$r['template_slug']] = (int) $r['n'];
        }

        $salida = [];
        foreach ($filas as $f) {
            $salida[] = [
                'tipo'         => (string) $f['tipo'],
                'enviados'     => (int) $f['enviados'],
                'clics'        => (int) $f['clics'],
                'compras_clic' => $directas[$f['tipo']] ?? 0,
                'compras_14d'  => $en14[$f['tipo']] ?? 0,
            ];
        }
        usort($salida, static fn ($a, $b) => $b['enviados'] <=> $a['enviados']);

        return $salida;
    }

    private function totales(array $filas): array
    {
        $t = ['enviados' => 0, 'clics' => 0, 'compras_clic' => 0, 'compras_14d' => 0];
        foreach ($filas as $f) {
            foreach ($t as $k => $_) {
                $t[$k] += $f[$k];
            }
        }
        return $t;
    }

    private function pct(int $a, int $b): string
    {
        return $b > 0 ? number_format($a / $b * 100, 1, ',', '') . ' %' : '-';
    }

    private function html(array $filas, array $tot): string
    {
        $td  = 'padding:8px 10px;border-bottom:1px solid #e5e7eb;font-size:13px;';
        $num = $td . 'text-align:right;';
        $th  = 'padding:8px 10px;background:#f1f5f9;font-size:12px;text-align:left;color:#334155;';

        $cab = '<tr>'
            . '<th style="' . $th . '">Correo</th>'
            . '<th style="' . $th . 'text-align:right;">Enviados</th>'
            . '<th style="' . $th . 'text-align:right;">Clics</th>'
            . '<th style="' . $th . 'text-align:right;">% clic</th>'
            . '<th style="' . $th . 'text-align:right;">Compras (clic)</th>'
            . '<th style="' . $th . 'text-align:right;">Compras (14 d)</th></tr>';

        $cuerpo = '';
        foreach ($filas as $f) {
            $destaca = $f['compras_clic'] > 0 ? 'background:#f0fdf4;' : '';
            $cuerpo .= '<tr style="' . $destaca . '">'
                . '<td style="' . $td . '"><code>' . esc($f['tipo']) . '</code></td>'
                . '<td style="' . $num . '">' . $f['enviados'] . '</td>'
                . '<td style="' . $num . '">' . $f['clics'] . '</td>'
                . '<td style="' . $num . '">' . $this->pct($f['clics'], $f['enviados']) . '</td>'
                . '<td style="' . $num . '"><strong>' . $f['compras_clic'] . '</strong></td>'
                . '<td style="' . $num . '">' . $f['compras_14d'] . '</td></tr>';
        }
        $cuerpo .= '<tr style="font-weight:700;">'
            . '<td style="' . $td . '">TOTAL</td>'
            . '<td style="' . $num . '">' . $tot['enviados'] . '</td>'
            . '<td style="' . $num . '">' . $tot['clics'] . '</td>'
            . '<td style="' . $num . '">' . $this->pct($tot['clics'], $tot['enviados']) . '</td>'
            . '<td style="' . $num . '">' . $tot['compras_clic'] . '</td>'
            . '<td style="' . $num . '">' . $tot['compras_14d'] . '</td></tr>';

        return '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:720px;font-family:Arial,sans-serif;">'
            . $cab . $cuerpo . '</table>'
            . '<p style="font-family:Arial,sans-serif;font-size:12px;color:#64748b;max-width:720px;">'
            . '<strong>Compras (clic)</strong>: el pago llevaba el source del correo. '
            . '<strong>Compras (14 d)</strong>: el usuario pagó en los 14 días siguientes al envío, llegara como llegara. '
            . 'Las aperturas no se miden: la copia oculta y la precarga de Apple Mail las inflan.</p>';
    }

    private function enviar(string $titulo, string $html): bool
    {
        try {
            $email = \Config\Services::email();
            $email->clear(true);
            $email->setFrom(env('email.fromEmail', 'soporte@apiempresas.es'), env('email.fromName', 'APIEmpresas.es'));
            $email->setTo(self::DESTINO);
            $email->setSubject('📊 ' . $titulo);
            $email->setMailType('html');
            $email->setMessage('<h2 style="font-family:Arial,sans-serif;font-size:18px;color:#0f172a;">' . esc($titulo) . '</h2>' . $html);

            if (!$email->send()) {
                log_message('error', '[email:report] ' . $email->printDebugger(['headers']));
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[email:report] ' . $e->getMessage());
            return false;
        }
    }
}
