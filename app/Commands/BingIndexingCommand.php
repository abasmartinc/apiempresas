<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BingIndexingCommand extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:indexing-bing';
    protected $description = 'Envía masivamente las páginas de empresas a la API de indexación de Bing.';

    public function run(array $params)
    {
        $isDryRun = in_array('dry-run', $params);
        $limit = 10000; // Bing API allows up to 10,000 per day by default

        CLI::write("Starting Bing Indexing API submission...", 'green');

        if ($isDryRun) {
            CLI::write("DRY RUN MODE: No requests will be sent to Bing.", 'yellow');
        }

        $apiKey = env('BING_INDEXING_API_KEY');
        if (empty($apiKey)) {
            CLI::error("BING_INDEXING_API_KEY is not set in .env");
            return;
        }

        $db = \Config\Database::connect();
        helper(['company']);

        // Buscamos empresas enriquecidas con IA que no han sido enviadas a Bing o hace más de 30 días
        $builder = $db->table('companies');
        $builder->select('companies.id, companies.cif, companies.company_name as name, company_enrichment.bing_indexing_submitted_at')
                ->join('company_enrichment', 'company_enrichment.company_id = companies.id')
                ->where('company_enrichment.ai_seo_text IS NOT NULL')
                ->where("company_enrichment.ai_seo_text != ''")
                ->groupStart()
                    ->where('company_enrichment.bing_indexing_submitted_at IS NULL')
                    ->orWhere('company_enrichment.bing_indexing_submitted_at <', date('Y-m-d H:i:s', strtotime('-30 days')))
                ->groupEnd()
                ->orderBy('companies.id', 'DESC')
                ->limit($limit);

        $companies = $builder->get()->getResultArray();

        if (empty($companies)) {
            CLI::write("No AI enriched companies found to submit to Bing.", 'yellow');
            return;
        }

        $urlList = [];
        $companyMap = []; // To map URL back to ID for updating DB

        foreach ($companies as $c) {
            $urlPath = !empty($c['cif']) ? $c['cif'] . '-' . url_title($c['name'], '-', true) : url_title($c['name'], '-', true);
            $url = 'https://apiempresas.es/' . ltrim($urlPath, '/');
            $urlList[] = $url;
            $companyMap[$url] = $c;
        }

        CLI::write("Prepared " . count($urlList) . " URLs to submit to Bing.");

        if ($isDryRun) {
            CLI::write("Would submit to Bing. Skipping API call.");
            return;
        }

        $siteUrl = 'https://apiempresas.es';
        $endpoint = "https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey={$apiKey}";

        $submitted = 0;
        $submittedCifs = [];
        $hasError = false;

        // Bing limits each batch request to 500 URLs
        $urlChunks = array_chunk($urlList, 500);

        foreach ($urlChunks as $chunk) {
            $payload = json_encode([
                'siteUrl' => $siteUrl,
                'urlList' => $chunk
            ]);

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($payload)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode == 200) {
                // Bing returns 200 OK even if successful
                $result = json_decode($response, true);
                
                // Update database for all successful URLs in this chunk
                foreach ($chunk as $url) {
                    if (isset($companyMap[$url])) {
                        $c = $companyMap[$url];
                        $db->table('company_enrichment')
                           ->where('company_id', $c['id'])
                           ->update(['bing_indexing_submitted_at' => date('Y-m-d H:i:s')]);
                        
                        $submitted++;
                        if (!empty($c['cif'])) {
                            $submittedCifs[] = $c['cif'];
                        }
                    }
                }
            } else {
                CLI::error("Failed to submit chunk to Bing. HTTP Status: {$httpCode}");
                CLI::write("Response: {$response}", 'red');
                if ($error) {
                    CLI::write("cURL Error: {$error}", 'red');
                }
                $hasError = true;
                break; // Stop processing further chunks if we hit an API error
            }
            
            usleep(200000); // 200ms pause between chunk requests to be nice to the API
        }

        if (!$hasError && $submitted > 0) {
            CLI::write("Successfully submitted batches to Bing!", 'green');
        }

        CLI::write("Done! Processed {$submitted} URLs to Bing.", 'green');

        // Send email report
        if ($submitted > 0) {
            $cifListText = !empty($submittedCifs) ? "\n\nCIFs procesados (Bing):\n" . implode(', ', $submittedCifs) : "";
            
            $email = \Config\Services::email();
            $email->setTo('papelo.amh@gmail.com');
            $email->setSubject("Reporte Diario: Bing Indexing API ({$submitted})");
            $email->setMessage("El comando automático seo:indexing-bing ha finalizado.\n\nSe han enviado {$submitted} URLs enriquecidas con IA a Bing para forzar su indexación rápida.\nLímite máximo permitido diario: 10,000.{$cifListText}\n\nAPIEmpresas.es Cron");
            if (!$email->send()) {
                CLI::error("No se pudo enviar el email de reporte de Bing.");
            }
        }
    }
}
