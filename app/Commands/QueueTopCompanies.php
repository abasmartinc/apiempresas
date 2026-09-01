<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;

class QueueTopCompanies extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:queue-top';
    protected $description = 'Encola las empresas más visitadas para generación de IA proactiva.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        $limit = 1000; // Hard-cap: máximo 1000 empresas por ejecución
        
        CLI::write("Buscando las empresas con mayor tráfico único para encolar...", 'cyan');

        // Ranking por visitantes únicos (COUNT DISTINCT anonymous_id) en los últimos 30 días con event_name = 'page_view'
        $query = "
            SELECT page, COUNT(DISTINCT anonymous_id) as unique_visitors
            FROM tracking_events
            WHERE event_name = 'page_view'
              AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
              AND page LIKE '%-%'
            GROUP BY page
            ORDER BY unique_visitors DESC
            LIMIT 2000
        ";
        
        $events = $db->query($query)->getResultArray();
        $queued = 0;
        
        $companyModel = new CompanyModel();
        
        foreach ($events as $event) {
            if ($queued >= $limit) {
                break;
            }
            
            $url = $event['page'];
            $path = parse_url($url, PHP_URL_PATH);
            if (!$path) continue;
            
            $segment = ltrim($path, '/');
            
            // Ficha de empresa CIF (primera letra + 7 dígitos + carácter)
            if (preg_match('/^([a-zA-Z][0-9]{7}[a-zA-Z0-9])(-.*)?$/', $segment, $matches)) {
                $cif = $matches[1];
                
                $company = $companyModel->getByCif($cif);
                
                if (!$company) {
                    continue;
                }

                // 1. Filtro de calidad mercantil: exclusivamente empresa ACTIVA
                $estado = trim((string)($company['status'] ?? $company['estado'] ?? ''));
                if ($estado !== 'ACTIVA') {
                    continue;
                }

                // 2. Filtro de contenido: objeto social con longitud útil (> 10 caracteres)
                $objetoSocial = trim((string)($company['corporate_purpose'] ?? $company['objeto_social'] ?? ''));
                if (mb_strlen($objetoSocial) <= 10) {
                    continue;
                }

                // 3. AI Guard: no encolar si ya tiene texto IA (normalizando whitespace)
                $aiText = trim((string)($company['ai_seo_text'] ?? ''));
                if ($aiText !== '') {
                    continue;
                }

                // 4. Deduplicación en cola (comprueba si ya existe en cualquier estado, incluyendo pending, processing y failed)
                $inQueue = $db->table('seo_generation_queue')->where('company_id', $company['id'])->countAllResults() > 0;
                
                if (!$inQueue) {
                    $db->query("INSERT IGNORE INTO seo_generation_queue (company_id, requested_at, status) VALUES (?, ?, 'pending')", [$company['id'], date('Y-m-d H:i:s')]);
                    $compName = $company['name'] ?? $company['company_name'] ?? 'N/A';
                    CLI::write("Encolada: {$compName} (Visitantes únicos: {$event['unique_visitors']})", 'green');
                    $queued++;
                }
            }
        }
        
        CLI::write("Proceso terminado. Se han encolado {$queued} nuevas empresas.", 'yellow');

        $email = \Config\Services::email();
        $email->setTo('papelo.amh@gmail.com');
        $email->setSubject("Reporte Diario: Encolado SEO IA ({$queued} empresas)");
        $email->setMessage("El comando seo:queue-top ha finalizado.\n\nSe han encolado exitosamente {$queued} nuevas empresas (con mayor número de visitantes únicos en el mes y datos mercantiles activos) para ser enriquecidas proactivamente por la IA.\n\nAPIEmpresas.es Cron");
        if (!$email->send()) {
            CLI::error("No se pudo enviar el email de reporte.");
        }
    }
}
