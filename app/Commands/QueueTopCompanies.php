<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QueueTopCompanies extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:queue-top';
    protected $description = 'Encola las empresas más visitadas para generación de IA proactiva.';

    public function run(array $params)
    {
        $startTime = microtime(true);
        $db = \Config\Database::connect();
        
        $limit = 1000; // Hard-cap: máximo 1000 empresas por ejecución
        
        CLI::write("Buscando las empresas con mayor tráfico único para encolar...", 'cyan');

        // 1. Ranking por visitantes únicos en los últimos 30 días
        $query = "
            SELECT page, COUNT(DISTINCT anonymous_id) as unique_visitors
            FROM tracking_events
            WHERE event_name = 'page_view'
              AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
              AND page LIKE '%-%'
            GROUP BY page
            ORDER BY unique_visitors DESC
            LIMIT 25000
        ";
        
        $events = $db->query($query)->getResultArray();

        // 2. Extraer CIFs únicos preservando el orden de tráfico
        $cifVisitors = []; // cif => unique_visitors
        foreach ($events as $event) {
            $url = $event['page'];
            $path = parse_url($url, PHP_URL_PATH);
            if (!$path) continue;
            
            $segment = ltrim($path, '/');
            
            // Ficha de empresa CIF (primera letra + 7 dígitos + carácter)
            if (preg_match('/^([a-zA-Z][0-9]{7}[a-zA-Z0-9])(-.*)?$/', $segment, $matches)) {
                $cif = strtoupper($matches[1]);
                if (!isset($cifVisitors[$cif])) {
                    $cifVisitors[$cif] = (int)$event['unique_visitors'];
                }
            }
        }

        $uniqueCifs = array_keys($cifVisitors);
        CLI::write("Se han identificado " . count($uniqueCifs) . " CIFs únicos desde el tráfico.", 'yellow');

        if (empty($uniqueCifs)) {
            CLI::write("No se encontraron empresas candidatas.", 'yellow');
            return;
        }

        // 3. Consultar las empresas en lotes optimizados (WHERE IN)
        $cifChunks = array_chunk($uniqueCifs, 500);
        $companiesData = []; // cif => row

        foreach ($cifChunks as $chunk) {
            $builder = $db->table('companies c')
                ->select('c.id, c.cif, c.company_name, c.estado, c.objeto_social, ce.ai_seo_text, q.company_id as in_queue_id')
                ->join('company_enrichment ce', 'ce.company_id = c.id', 'left')
                ->join('seo_generation_queue q', 'q.company_id = c.id', 'left')
                ->whereIn('c.cif', $chunk);

            $rows = $builder->get()->getResultArray();
            foreach ($rows as $row) {
                $cifKey = strtoupper(trim((string)$row['cif']));
                $companiesData[$cifKey] = $row;
            }
        }

        // 4. Filtrar y seleccionar las mejores candidatas en orden de visitantes
        $toQueue = [];
        $now = date('Y-m-d H:i:s');

        foreach ($uniqueCifs as $cif) {
            if (count($toQueue) >= $limit) {
                break;
            }

            if (!isset($companiesData[$cif])) {
                continue;
            }

            $company = $companiesData[$cif];

            // Filtro 1: Exclusivamente empresa ACTIVA (case-insensitive)
            $estado = strtoupper(trim((string)($company['estado'] ?? '')));
            if ($estado !== 'ACTIVA') {
                continue;
            }

            // Filtro 2: Objeto social útil (> 10 caracteres)
            $objetoSocial = trim((string)($company['objeto_social'] ?? ''));
            if (mb_strlen($objetoSocial) <= 10) {
                continue;
            }

            // Filtro 3: AI Guard (no encolar si ya tiene texto IA)
            $aiText = trim((string)($company['ai_seo_text'] ?? ''));
            if ($aiText !== '') {
                continue;
            }

            // Filtro 4: No encolar si ya está en la cola
            if (!empty($company['in_queue_id'])) {
                continue;
            }

            $toQueue[] = [
                'company_id'   => (int)$company['id'],
                'requested_at' => $now,
                'status'       => 'pending',
                'attempts'     => 0,
            ];

            CLI::write("Encolada: {$company['company_name']} ({$cif}) [Visitantes: {$cifVisitors[$cif]}]", 'green');
        }

        $queued = count($toQueue);

        // 5. Inserción masiva en bloque
        if ($queued > 0) {
            $insertChunks = array_chunk($toQueue, 200);
            foreach ($insertChunks as $insChunk) {
                $db->table('seo_generation_queue')->ignore(true)->insertBatch($insChunk);
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        CLI::write("Proceso terminado en {$elapsed}s. Se han encolado {$queued} nuevas empresas.", 'yellow');

        // 6. Envío de email de reporte
        try {
            $email = \Config\Services::email();
            $emailConfig = config('Email');
            $fromEmail = !empty($emailConfig->fromEmail) ? $emailConfig->fromEmail : 'soporte@apiempresas.es';
            $fromName  = !empty($emailConfig->fromName) ? $emailConfig->fromName : 'APIEmpresas.es';
            
            $email->setFrom($fromEmail, $fromName);
            $email->setTo('papelo.amh@gmail.com');
            $email->setSubject("Reporte Diario: Encolado SEO IA ({$queued} empresas)");
            $email->setMessage("El comando seo:queue-top ha finalizado exitosamente.\n\n"
                . "Resumen de ejecución:\n"
                . "- Nuevas empresas encoladas: {$queued}\n"
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
}
