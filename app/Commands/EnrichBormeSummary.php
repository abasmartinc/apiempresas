<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;
use App\Models\BormePostsModel;

class EnrichBormeSummary extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:enrich-borme';
    protected $description = 'Extrae el resumen del BORME para empresas antiguas.';

    public function run(array $params)
    {
        $limit = $params[0] ?? 50;
        $db = \Config\Database::connect();

        CLI::write("Iniciando enriquecimiento de BORME (Límite: $limit)...", 'cyan');

        // Buscar registros que tienen texto SEO pero les falta el borme_summary
        $rows = $db->table('company_enrichment')
            ->where('ai_seo_text IS NOT NULL')
            ->where('ai_seo_text !=', '')
            ->where('ai_borme_summary IS NULL')
            ->orderBy('company_id', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            CLI::write("No hay registros antiguos pendientes de resumir el BORME.", 'green');
            return;
        }

        $aiService = new \App\Services\OpenAiService();
        $companyModel = new CompanyModel();

        foreach ($rows as $row) {
            CLI::write("Procesando empresa ID: {$row['company_id']}...", 'yellow');
            
            $company = $companyModel->getById((int)$row['company_id']);
            if (!$company) {
                CLI::write("Empresa no encontrada.", 'red');
                continue;
            }

            $bormeModel = new BormePostsModel();
            $bormePosts = $bormeModel->getByCompanyId((int)$row['company_id']);
            
            $bormeText = '';
            if (!empty($bormePosts)) {
                $actsArr = [];
                foreach (array_slice($bormePosts, 0, 20) as $post) {
                    $actsArr[] = "- Fecha: " . ($post['borme_date'] ?? '') . " | Acto: " . ($post['act_types'] ?? '');
                }
                $bormeText = implode("\n", $actsArr);
            }

            if (empty($bormeText)) {
                CLI::write("Sin actos del BORME para resumir.", 'yellow');
                $db->table('company_enrichment')
                    ->where('company_id', $row['company_id'])
                    ->update(['ai_borme_summary' => '']);
                continue;
            }
            
            $prompt = "Lee atentamente estos actos del registro mercantil (BORME) de la empresa:\n\n";
            $prompt .= $bormeText . "\n\n";
            $prompt .= "Basándote SOLO en estos actos, devuelve un objeto JSON estricto con 1 campo obligatorio:\n";
            $prompt .= "1. 'borme_summary': Un resumen ejecutivo, jurídico y narrativo (máximo 40 palabras) del historial del registro mercantil de esta empresa.\n\n";
            $prompt .= "Formato estricto:\n{\"borme_summary\":\"Resumen...\"}";

            try {
                $responseJson = $aiService->getChatResponse([
                    ['role' => 'system', 'content' => 'Eres un extractor de datos JSON experto. Responde ÚNICAMENTE con un objeto JSON válido.'],
                    ['role' => 'user', 'content' => $prompt]
                ], [
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 250,
                    'temperature' => 0.4
                ]);

                $data = json_decode($responseJson, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($data['borme_summary'])) {
                    $bormeSummary = trim($data['borme_summary']);

                    $db->table('company_enrichment')
                        ->where('company_id', $row['company_id'])
                        ->update([
                            'ai_borme_summary' => $bormeSummary
                        ]);
                    CLI::write("Éxito ID {$row['company_id']}", 'green');
                } else {
                    CLI::write("Respuesta JSON inválida para ID {$row['company_id']}", 'red');
                    $db->table('company_enrichment')
                        ->where('company_id', $row['company_id'])
                        ->update(['ai_borme_summary' => null]);
                }
            } catch (\Exception $e) {
                CLI::write("Error API en ID {$row['company_id']}: " . $e->getMessage(), 'red');
                $db->table('company_enrichment')
                    ->where('company_id', $row['company_id'])
                    ->update(['ai_borme_summary' => null]);
            }

            // Pequeña pausa para no saturar OpenAI
            sleep(1);
        }
        
        CLI::write("Lote finalizado.", 'cyan');
    }
}
