<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EnrichOldSeoData extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:enrich-old';
    protected $description = 'Extrae Tags y Pitch de textos SEO antiguos.';

    public function run(array $params)
    {
        $limit = $params[0] ?? 50;
        $db = \Config\Database::connect();

        CLI::write("Iniciando enriquecimiento (Límite: $limit)...", 'cyan');

        $rows = $db->table('company_enrichment')
            ->where('ai_seo_text IS NOT NULL')
            ->where('ai_seo_text !=', '')
            // Para no quedarnos atascados si alguna falla, asumiremos que si ai_pitch es NULL hay que procesar
            ->where('ai_pitch IS NULL')
            ->orderBy('company_id', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            CLI::write("No hay registros antiguos pendientes de enriquecer.", 'green');
            return;
        }

        $aiService = new \App\Services\OpenAiService();

        foreach ($rows as $row) {
            CLI::write("Procesando empresa ID: {$row['company_id']}...", 'yellow');
            
            $text = strip_tags($row['ai_seo_text']);
            
            $prompt = "Lee atentamente este texto sobre una empresa:\n\n" . $text . "\n\n";
            $prompt .= "Basándote SOLO en este texto, devuelve un objeto JSON estricto con 2 campos obligatorios:\n";
            $prompt .= "1. 'seo_tags': Un array de strings con 5 a 8 palabras clave o servicios específicos extraídos del texto.\n";
            $prompt .= "2. 'seo_pitch': Una sola frase comercial muy atractiva de máximo 150 caracteres que resuma lo que hace la empresa, ideal para una meta description.\n\n";
            $prompt .= "Formato estricto:\n{\"seo_tags\":[\"Tag1\", \"Tag2\"], \"seo_pitch\":\"Frase...\"}";

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

                if (json_last_error() === JSON_ERROR_NONE && !empty($data['seo_pitch'])) {
                    $seoTags = $data['seo_tags'] ?? [];
                    $seoPitch = trim($data['seo_pitch']);

                    $db->table('company_enrichment')
                        ->where('company_id', $row['company_id'])
                        ->update([
                            'ai_tags'  => !empty($seoTags) ? json_encode($seoTags, JSON_UNESCAPED_UNICODE) : null,
                            'ai_pitch' => $seoPitch
                        ]);
                    CLI::write("Éxito ID {$row['company_id']}", 'green');
                } else {
                    CLI::write("Respuesta JSON inválida para ID {$row['company_id']}", 'red');
                    $db->table('company_enrichment')
                        ->where('company_id', $row['company_id'])
                        ->update(['ai_pitch' => null]);
                }
            } catch (\Exception $e) {
                CLI::write("Error API en ID {$row['company_id']}: " . $e->getMessage(), 'red');
                $db->table('company_enrichment')
                    ->where('company_id', $row['company_id'])
                    ->update(['ai_pitch' => null]);
            }

            // Pequeña pausa para no saturar OpenAI
            sleep(1);
        }
        
        CLI::write("Lote finalizado.", 'cyan');
    }
}
