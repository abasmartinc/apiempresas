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
        
        $limit = 100; // Queue top 100 per run
        
        CLI::write("Buscando las empresas más visitadas para encolar...", 'cyan');

        // Since tracking_events 'page' might contain full URL, we can group by page
        $query = "
            SELECT page, COUNT(*) as visits
            FROM tracking_events
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
              AND page LIKE '%-%'
            GROUP BY page
            ORDER BY visits DESC
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
            
            // Ficha de empresa CIF (first letter + 7 digits + char)
            if (preg_match('/^([a-zA-Z][0-9]{7}[a-zA-Z0-9])(-.*)?$/', $segment, $matches)) {
                $cif = $matches[1];
                
                $company = $companyModel->getByCif($cif);
                
                if ($company && empty($company['ai_seo_text'])) {
                    // Check if already in queue
                    $inQueue = $db->table('seo_generation_queue')->where('company_id', $company['id'])->countAllResults() > 0;
                    
                    if (!$inQueue) {
                        $db->query("INSERT IGNORE INTO seo_generation_queue (company_id, requested_at, status) VALUES (?, ?, 'pending')", [$company['id'], date('Y-m-d H:i:s')]);
                        CLI::write("Encolada: {$company['name']} (Visitas: {$event['visits']})", 'green');
                        $queued++;
                    }
                }
            }
        }
        
        CLI::write("Proceso terminado. Se han encolado {$queued} nuevas empresas.", 'yellow');

        $email = \Config\Services::email();
        $email->setTo('papelo.amh@gmail.com');
        $email->setSubject("Reporte Diario: Encolado SEO IA ({$queued} empresas)");
        $email->setMessage("El comando seo:queue-top ha finalizado.\n\nSe han encolado exitosamente {$queued} nuevas empresas (las más visitadas del mes) para ser enriquecidas proactivamente por la IA.\n\nAPIEmpresas.es Cron");
        if (!$email->send()) {
            CLI::error("No se pudo enviar el email de reporte.");
        }
    }
}
