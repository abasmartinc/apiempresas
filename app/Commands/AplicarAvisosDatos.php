<?php

namespace App\Commands;

use App\Services\DataCorrectionService;
use App\Services\EmailService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Repasa los avisos de datos ya guardados en company_ratings que traen valor
 * correcto y aún no se han procesado, y los aplica a la ficha (29-09-2026).
 *
 * Desde el 29-09-2026 la web NO aplica nada sola: los avisos quedan "Pendiente" y
 * el administrador los revisa por correo. Este comando es la única vía automática y
 * solo se lanza a mano: simula por defecto y hay que pasar --aplicar para escribir.
 * No toca los avisos ya aplicados ni los marcados "No aplicado".
 *
 *   php spark datos:aplicar-avisos                 → simula, no escribe nada
 *   php spark datos:aplicar-avisos --aplicar       → aplica y marca cada aviso
 *   php spark datos:aplicar-avisos --aplicar --correo   → además, un correo por aviso
 *   --dias=90  → antigüedad máxima de los avisos (por defecto 90)
 */
class AplicarAvisosDatos extends BaseCommand
{
    protected $group       = 'Datos';
    protected $name        = 'datos:aplicar-avisos';
    protected $description = 'Aplica a la ficha los avisos "¿Son correctos estos datos?" pendientes.';
    protected $usage       = 'datos:aplicar-avisos [--aplicar] [--correo] [--dias=90]';

    public function run(array $params)
    {
        $aplicar = CLI::getOption('aplicar') !== null;
        $correo  = CLI::getOption('correo') !== null;
        $dias    = max(1, (int) (CLI::getOption('dias') ?? 90));

        $db = \Config\Database::connect();

        $avisos = $db->table('company_ratings r')
            ->select('r.id, r.company_id, r.feedback, r.ip_address, c.company_name, c.cif')
            ->join('companies c', 'c.id = r.company_id', 'left')
            ->where('r.rating', 1)
            ->where('r.created_at >=', date('Y-m-d H:i:s', strtotime("-{$dias} days")))
            ->like('r.feedback', '[DATOS] Campo:', 'after')
            ->like('r.feedback', '| Correcto:', 'both')
            ->notLike('r.feedback', '| Aplicado:', 'both')
            ->notLike('r.feedback', '| No aplicado:', 'both')
            ->orderBy('r.id', 'ASC')
            ->get()->getResultArray();

        CLI::write(count($avisos) . ' avisos pendientes con valor correcto' . ($aplicar ? '' : ' (SIMULACIÓN: añade --aplicar para escribir)'), 'yellow');

        $servicio = new DataCorrectionService($db);
        $mailer   = new EmailService();
        $ok = 0;

        foreach ($avisos as $a) {
            $d = $this->parsear((string) $a['feedback']);

            // Sin tope por IP aquí: lo lanza el administrador a conciencia.
            $r = $servicio->aplicar((int) $a['company_id'], $d['campo'], $d['valor'], '', !$aplicar);

            $linea = "#{$a['id']} " . ($a['company_name'] ?? '?') . " ({$d['campo']}): ";
            if ($r['aplicado']) {
                $ok++;
                $cache = $r['cache'] === null ? '' : ($r['cache'] ? ' [caché vaciada]' : ' [caché NO vaciada]');
                CLI::write($linea . "{$r['tabla']}.{$r['columna']} '{$r['anterior']}' → '{$r['nuevo']}'" . $cache, 'green');
            } else {
                CLI::write($linea . $r['motivo'], 'light_gray');
            }

            if ($aplicar) {
                $db->table('company_ratings')->where('id', $a['id'])
                    ->update(['feedback' => $a['feedback'] . DataCorrectionService::sufijoFeedback($r)]);

                if ($correo) {
                    $mailer->sendDataCorrectionNotification(
                        ['id' => $a['company_id'], 'company_name' => $a['company_name'], 'cif' => $a['cif']],
                        ['email' => $d['email'], 'ip' => $a['ip_address']],
                        [['id' => $a['id'], 'campo' => $d['campo'], 'valor' => $d['valor'], 'original' => null, 'resultado' => $r]]
                    );
                }
            }
        }

        CLI::write("{$ok} de " . count($avisos) . ($aplicar ? ' aplicados.' : ' se aplicarían.'), 'yellow');
    }

    private function parsear(string $fb): array
    {
        $out = ['campo' => '', 'valor' => '', 'email' => ''];
        foreach (explode('|', substr($fb, 7)) as $parte) {
            [$k, $v] = array_pad(array_map('trim', explode(':', $parte, 2)), 2, '');
            if ($k === 'Campo')    { $out['campo'] = $v; }
            if ($k === 'Correcto') { $out['valor'] = $v; }
            if ($k === 'Email')    { $out['email'] = $v; }
        }
        return $out;
    }
}
