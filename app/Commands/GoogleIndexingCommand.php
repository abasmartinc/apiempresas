<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GoogleIndexingCommand extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:indexing-api';
    protected $description = 'Envía las páginas enriquecidas con IA a la API de indexación de Google.';

    public function run(array $params)
    {
        $isDryRun = in_array('dry-run', $params);
        $limit = 200; // Google Indexing API daily limit per project

        CLI::write("Starting Google Indexing API submission...", 'green');

        if ($isDryRun) {
            CLI::write("DRY RUN MODE: No requests will be sent to Google.", 'yellow');
        }

        // Check for Google API Client
        if (!class_exists('\Google_Client') && !class_exists('\Google\Client')) {
            CLI::error("Google API Client is not installed. Please run: composer require google/apiclient:^2.15.0");
            return;
        }

        $db = \Config\Database::connect();
        helper(['company']);

        // Query: AI enriched companies not submitted in the last 30 days
        $builder = $db->table('companies');
        $builder->select('companies.id, companies.cif, companies.company_name as name, companies.cnae_code as cnae, companies.registro_mercantil as province, companies.objeto_social as corporate_purpose, company_enrichment.indexing_api_submitted_at')
                ->join('company_enrichment', 'company_enrichment.company_id = companies.id')
                ->where('company_enrichment.ai_seo_text IS NOT NULL')
                ->where("company_enrichment.ai_seo_text != ''")
                ->groupStart()
                    ->where('company_enrichment.indexing_api_submitted_at IS NULL')
                    ->orWhere('company_enrichment.indexing_api_submitted_at <', date('Y-m-d H:i:s', strtotime('-30 days')))
                ->groupEnd()
                ->orderBy('companies.id', 'DESC') // We could also join with tracking_events to order by visits
                ->limit($limit);

        $companies = $builder->get()->getResultArray();

        if (empty($companies)) {
            CLI::write("No AI enriched companies found to submit.", 'yellow');
            return;
        }

        // Initialize Google Client
        $client = new \Google\Client();
        // Set scopes
        $client->addScope('https://www.googleapis.com/auth/indexing');
        // Need credentials.json from Google Cloud Service Account
        $credentialsPath = WRITEPATH . 'credentials/google-service-account.json';
        if (!file_exists($credentialsPath)) {
            CLI::error("Credentials file not found at: {$credentialsPath}. Please download your Service Account JSON and place it there.");
            return;
        }

        try {
            $client->setAuthConfig($credentialsPath);
            $httpClient = $client->authorize();
        } catch (\Exception $e) {
            CLI::error("Failed to authorize Google Client: " . $e->getMessage());
            return;
        }

        $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
        
        $submitted = 0;
        $submittedCifs = [];
        foreach ($companies as $c) {
            // Force production domain for Google API, regardless of local CLI config
            $urlPath = !empty($c['cif']) ? $c['cif'] . '-' . url_title($c['name'], '-', true) : url_title($c['name'], '-', true);
            $url = 'https://apiempresas.es/' . ltrim($urlPath, '/');

            $content = json_encode([
                'url' => $url,
                'type' => 'URL_UPDATED' // URL_UPDATED or URL_DELETED
            ]);

            if ($isDryRun) {
                CLI::write("Would submit: {$url}");
                $submitted++;
                continue;
            }

            try {
                $response = $httpClient->post($endpoint, ['body' => $content]);
                $statusCode = $response->getStatusCode();
                
                if ($statusCode == 200) {
                    CLI::write("Successfully submitted: {$url}", 'green');
                    // Update DB
                    $db->table('company_enrichment')
                       ->where('company_id', $c['id'])
                       ->update(['indexing_api_submitted_at' => date('Y-m-d H:i:s')]);
                    $submitted++;
                    if (!empty($c['cif'])) {
                        $submittedCifs[] = $c['cif'];
                    }
                } else {
                    CLI::write("Failed to submit: {$url} (Status: {$statusCode})", 'red');
                }
            } catch (\Exception $e) {
                CLI::error("Exception submitting {$url}: " . $e->getMessage());
                if (strpos($e->getMessage(), 'quota') !== false || strpos($e->getMessage(), 'Rate Limit') !== false || strpos($e->getMessage(), '429') !== false) {
                    CLI::write("Quota exceeded. Stopping for today.", 'yellow');
                    break;
                }
            }
            
            usleep(100000); // 100ms pause
        }

        CLI::write("Done! Submitted {$submitted} URLs to Google Indexing API.", 'green');

        if (!$isDryRun) {
            $cifListText = !empty($submittedCifs) ? "\n\nCIFs procesados:\n" . implode(', ', $submittedCifs) : "";
            
            $email = \Config\Services::email();
            $email->setTo('papelo.amh@gmail.com');
            $email->setSubject('Reporte Diario: Google Indexing API');
            $email->setMessage("El comando automático seo:indexing-api ha finalizado.\n\nSe han enviado {$submitted} URLs enriquecidas con IA a Google para forzar su indexación rápida.\nLímite máximo permitido diario: 200.{$cifListText}\n\nAPIEmpresas.es Cron");
            if (!$email->send()) {
                CLI::error("No se pudo enviar el email de reporte.");
            }
        }
    }
}
