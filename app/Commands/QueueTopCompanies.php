<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Encolado diario de fichas para el texto de IA (cron, una vez al día).
 *
 * 09-10-2026: antes solo encolaba las 1.000 fichas más visitadas de 30 días. Ahora rellena la
 * cola hasta un objetivo (por defecto 9.000 pendientes, lo que seo:process-queue saca en un
 * día) y por este orden de prioridad:
 *
 *  1. Fichas con visitas en los últimos 90 días, de más a menos visitantes únicos.
 *     Entran con requested_at de hace un día para que la cola (requested_at ASC) las haga antes.
 *  2. Constituidas en los últimos 12 meses, de la más nueva a la más antigua.
 *  3. Con algún anuncio en el BORME desde 2024, de las más nuevas (id) a las más antiguas.
 *
 * En todos: estado ACTIVA o sin estado, objeto social de más de 10 caracteres, sin texto de IA
 * y que no estén ya en la cola (en ningún estado). No se filtra el grupo de control.
 *
 *   php spark seo:queue-top                 (encola)
 *   php spark seo:queue-top --prueba        (cuenta y enseña ejemplos, no escribe ni manda correo)
 *   php spark seo:queue-top --objetivo 12000
 *   php spark seo:queue-top --cupo-nuevas 1500   (plazas reservadas cada día al nivel 2; por defecto 1500)
 */
class QueueTopCompanies extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:queue-top';
    protected $description = 'Rellena la cola de textos de IA por prioridad: visitadas, nuevas y con BORME reciente.';
    protected $usage       = 'seo:queue-top [--prueba] [--objetivo N] [--cupo-nuevas N]';

    private const FILTRO_BASE = "
        (c.estado IS NULL OR TRIM(c.estado) = '' OR UPPER(TRIM(c.estado)) = 'ACTIVA')
        AND CHAR_LENGTH(TRIM(COALESCE(c.objeto_social, ''))) > 10
        AND NOT EXISTS (SELECT 1 FROM company_enrichment ce WHERE ce.company_id = c.id AND ce.ai_seo_text IS NOT NULL AND ce.ai_seo_text <> '')
        AND NOT EXISTS (SELECT 1 FROM seo_generation_queue q WHERE q.company_id = c.id)
    ";

    public function run(array $params)
    {
        $startTime = microtime(true);
        $db        = \Config\Database::connect();
        $prueba    = CLI::getOption('prueba') !== null;
        $objetivo  = max(0, min(100000, (int) (CLI::getOption('objetivo') ?? 9000)));

        $pendientes = (int) $db->table('seo_generation_queue')->where('status', 'pending')->countAllResults();
        $hueco      = max(0, $objetivo - $pendientes);
        // Cupo reservado a las recién constituidas: sin él, las ~90.000 visitadas llenan la cola
        // durante semanas y las nuevas (las de sitemap-empresas-nuevas) se quedan sin texto.
        $cupoNuevas = max(0, (int) (CLI::getOption('cupo-nuevas') ?? 1500));

        CLI::write(($prueba ? 'PRUEBA (no escribe nada). ' : '') . "Pendientes en cola: {$pendientes}. Objetivo: {$objetivo}. Hueco: {$hueco}.", 'cyan');

        $resumen = [];
        $vistos  = [];
        $niveles = [
            1 => 'Visitadas en 90 días',
            2 => 'Constituidas en 12 meses',
            3 => 'Con BORME desde 2024',
        ];

        foreach ($niveles as $nivel => $nombre) {
            if ($hueco <= 0) {
                $resumen[$nivel] = 0;
                continue;
            }
            $t0  = microtime(true);
            if ($nivel === 1 && $hueco - min($cupoNuevas, $hueco) <= 0) {
                $resumen[$nivel] = 0;
                continue;
            }
            $ids = match ($nivel) {
                1 => $this->nivel1($db, max(0, $hueco - min($cupoNuevas, $hueco))),
                2 => $this->nivel2($db, $hueco),
                3 => $this->nivel3($db, $hueco),
            };
            if ($prueba) {
                // En la prueba no se inserta nada: se quitan las que ya salieron en un nivel anterior.
                $ids = array_values(array_diff($ids, $vistos));
                $vistos = array_merge($vistos, $ids);
            }
            $seg = round(microtime(true) - $t0, 1);
            CLI::write("Nivel {$nivel} ({$nombre}): " . count($ids) . " candidatas ({$seg} s)", 'yellow');

            if ($prueba) {
                $this->muestra($db, $ids);
                $resumen[$nivel] = count($ids);
                $hueco -= count($ids);   // en la prueba se simula el reparto
                continue;
            }

            $n = $this->encolar($db, $ids, $nivel === 1 ? date('Y-m-d H:i:s', time() - 86400) : date('Y-m-d H:i:s'));
            CLI::write("  Encoladas: {$n}", 'green');
            $resumen[$nivel] = $n;
            $hueco -= $n;
        }

        $queued  = array_sum($resumen);
        $elapsed = round(microtime(true) - $startTime, 2);
        CLI::write(($prueba ? 'Se encolarían ' : 'Encoladas ') . "{$queued} en total en {$elapsed}s.", 'cyan');

        if ($prueba) {
            return;
        }

        // Correo de reporte
        try {
            $email       = \Config\Services::email();
            $emailConfig = config('Email');
            $fromEmail   = !empty($emailConfig->fromEmail) ? $emailConfig->fromEmail : 'soporte@apiempresas.es';
            $fromName    = !empty($emailConfig->fromName) ? $emailConfig->fromName : 'APIEmpresas.es';

            $lineas = '';
            foreach ($niveles as $nivel => $nombre) {
                $lineas .= "- Nivel {$nivel} ({$nombre}): {$resumen[$nivel]}\n";
            }

            $email->setFrom($fromEmail, $fromName);
            $email->setTo('papelo.amh@gmail.com');
            $email->setSubject("Reporte Diario: Encolado SEO IA ({$queued} empresas)");
            $email->setMessage("El comando seo:queue-top ha finalizado.\n\n"
                . "Pendientes en cola antes de encolar: {$pendientes} (objetivo {$objetivo})\n"
                . "Nuevas empresas encoladas: {$queued}\n"
                . $lineas
                . "- Tiempo de ejecución: {$elapsed} segundos\n"
                . "- Fecha y hora: " . date('Y-m-d H:i:s') . "\n\n"
                . "Las empresas serán enriquecidas progresivamente por el worker seo:process-queue.\n\n"
                . "APIEmpresas.es Cron");

            if (!$email->send(false)) {
                $debugger = $email->printDebugger(['headers']);
                CLI::error("No se pudo enviar el email de reporte. Detalle: {$debugger}");
                log_message('error', '[QueueTopCompanies] Error enviando email de reporte: ' . $debugger);
            } else {
                CLI::write("Reporte enviado por email a papelo.amh@gmail.com con éxito.", 'green');
            }
        } catch (\Throwable $e) {
            CLI::error("Excepción al enviar email de reporte: " . $e->getMessage());
            log_message('error', '[QueueTopCompanies] Excepción email: ' . $e->getMessage());
        }
    }

    private function encolar($db, array $ids, string $cuando): int
    {
        if (!$ids) {
            return 0;
        }
        $antes = (int) $db->table('seo_generation_queue')->countAllResults();
        foreach (array_chunk($ids, 500) as $lote) {
            $filas = array_map(static fn ($id) => [
                'company_id' => (int) $id, 'requested_at' => $cuando, 'status' => 'pending', 'attempts' => 0,
            ], $lote);
            $db->table('seo_generation_queue')->ignore(true)->insertBatch($filas);
        }
        return (int) $db->table('seo_generation_queue')->countAllResults() - $antes;
    }

    private function muestra($db, array $ids): void
    {
        if (!$ids) {
            return;
        }
        $rows = $db->table('companies')->select('id, cif, company_name, fecha_constitucion, estado')
            ->whereIn('id', array_slice($ids, 0, 10))->get()->getResultArray();
        foreach ($rows as $m) {
            CLI::write("    {$m['cif']}  {$m['company_name']}  (" . ($m['fecha_constitucion'] ?: 's/f') . ', ' . ($m['estado'] ?: 'sin estado') . ')');
        }
    }

    /** Fichas visitadas en 90 días, de más a menos visitantes únicos. */
    private function nivel1($db, int $limit): array
    {
        $filas = $db->query("
            SELECT page, COUNT(DISTINCT anonymous_id) AS v
            FROM tracking_events
            WHERE event_name = 'page_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) AND page LIKE '%-%'
            GROUP BY page ORDER BY v DESC LIMIT 300000
        ")->getResultArray();

        $cifs = [];
        foreach ($filas as $f) {
            $seg = ltrim((string) parse_url($f['page'], PHP_URL_PATH), '/');
            if (preg_match('/^([A-Za-z]\d{7}[A-Za-z0-9])(-.*)?$/', $seg, $m)) {
                $cifs[strtoupper($m[1])] = true;
            }
        }
        CLI::write('  Fichas distintas con visitas en 90 días: ' . count($cifs));

        $ids = [];
        foreach (array_chunk(array_keys($cifs), 1000) as $lote) {   // en orden de visitas
            $in   = implode(',', array_map([$db, 'escape'], $lote));
            $rows = $db->query("SELECT c.id, c.cif FROM companies c WHERE c.cif IN ({$in}) AND " . self::FILTRO_BASE)->getResultArray();
            $porCif = [];
            foreach ($rows as $r) {
                $porCif[strtoupper(trim((string) $r['cif']))] = (int) $r['id'];
            }
            foreach ($lote as $cif) {
                if (isset($porCif[$cif])) {
                    $ids[] = $porCif[$cif];
                    if (count($ids) >= $limit) {
                        return $ids;
                    }
                }
            }
        }
        return $ids;
    }

    /** Constituidas en los últimos 12 meses, de la más nueva a la más antigua. */
    private function nivel2($db, int $limit): array
    {
        $rows = $db->query("
            SELECT c.id FROM companies c
            WHERE c.fecha_constitucion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
              AND c.fecha_constitucion <= CURDATE()
              AND " . self::FILTRO_BASE . "
            ORDER BY c.fecha_constitucion DESC, c.id DESC
            LIMIT " . (int) $limit
        )->getResultArray();
        return array_map('intval', array_column($rows, 'id'));
    }

    /**
     * Con algún anuncio en el BORME desde 2024. Se recorre por tramos de id (de los más nuevos
     * a los más antiguos) para no lanzar una consulta sobre toda la tabla de golpe.
     */
    private function nivel3($db, int $limit): array
    {
        $max   = (int) ($db->query('SELECT MAX(id) AS m FROM companies')->getRow()->m ?? 0);
        $ids   = [];
        $tramo = 50000;
        for ($hasta = $max; $hasta > 0 && count($ids) < $limit; $hasta -= $tramo) {
            $desde = max(1, $hasta - $tramo + 1);
            $falta = $limit - count($ids);
            $rows  = $db->query("
                SELECT c.id FROM companies c
                WHERE c.id BETWEEN {$desde} AND {$hasta}
                  AND c.cif IS NOT NULL AND c.cif <> ''
                  AND EXISTS (SELECT 1 FROM borme_posts b WHERE b.company_id = c.id AND b.borme_date >= '2024-01-01')
                  AND " . self::FILTRO_BASE . "
                ORDER BY c.id DESC
                LIMIT {$falta}
            ")->getResultArray();
            foreach ($rows as $r) {
                $ids[] = (int) $r['id'];
            }
        }
        return $ids;
    }
}
