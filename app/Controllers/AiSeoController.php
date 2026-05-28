<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CompanyModel;
use OpenAI;
use Exception;

class AiSeoController extends ResourceController
{
    public function generate()
    {
        $cif = $this->request->getPost('cif') ?? $this->request->getGet('cif');
        if (empty($cif)) {
            return $this->fail('CIF es requerido', 400);
        }

        $companyModel = new CompanyModel();
        $company = $companyModel->getByCif($cif);

        if (!$company) {
            return $this->failNotFound('Empresa no encontrada');
        }

        // Si ya tiene texto, devolverlo
        if (!empty($company['ai_seo_text'])) {
            $faqs = null;
            if (!empty($company['ai_faqs'])) {
                $faqs = json_decode($company['ai_faqs'], true);
            }
            return $this->respond([
                'status' => 'cached', 
                'text' => $company['ai_seo_text'],
                'faqs' => $faqs
            ]);
        }

        // Configurar OpenAI
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return $this->failServerError('API key de OpenAI no configurada');
        }

        try {
            $aiService = new \App\Services\OpenAiService();
            
            $name = $company['name'] ?? 'la empresa';
            $cnae = $company['cnae_label'] ?? 'un sector no especificado';
            $founded = $company['founded'] ?? 'fecha desconocida';
            $prov = $company['province'] ?? $company['provincia'] ?? 'España';
            $purpose = $company['corporate_purpose'] ?? '';
            
            $prompt = "Genera un objeto JSON que contenga los siguientes campos obligatorios:
            1. 'seo_text': Un texto descriptivo de la empresa '{$name}' altamente optimizado para SEO. Escribe exactamente 2 párrafos redactados con excelente estilo periodístico y de negocios. El primer párrafo debe presentar a la empresa, integrando de forma natural su sector de actividad (CNAE: {$cnae}) y su trayectoria (ubicación: {$prov}, fundación: {$founded}) de forma fluida. Evita empezar con plantillas robóticas como 'La empresa X es...'. El segundo párrafo debe sintetizar su objeto social ({$purpose}) detallando sus servicios, valor diferencial en el mercado y operaciones. No uses Markdown, asteriscos, negritas ni viñetas. Separa los párrafos con un salto de línea doble.
            2. 'faqs': Una lista de exactamente 3 preguntas frecuentes ('q') y respuestas ('a') personalizadas y de alta calidad para esta empresa. Las preguntas deben ser específicas sobre la actividad, servicios o sector de la empresa basándose en su objeto social y CNAE (evita preguntas totalmente genéricas). Las respuestas deben ser profesionales, informativas y en formato de texto plano sin Markdown ni negritas.
            
            Formato de salida JSON estricto esperado:
            {
              \"seo_text\": \"Párrafo 1\\n\\nPárrafo 2\",
              \"faqs\": [
                {
                  \"q\": \"¿Pregunta específica 1?\",
                  \"a\": \"Respuesta específica 1...\"
                },
                {
                  \"q\": \"¿Pregunta específica 2?\",
                  \"a\": \"Respuesta específica 2...\"
                },
                {
                  \"q\": \"¿Pregunta específica 3?\",
                  \"a\": \"Respuesta específica 3...\"
                }
              ]
            }";

            $responseJson = $aiService->getChatResponse([
                ['role' => 'system', 'content' => 'Eres un analista de negocios y redactor SEO experto. Debes responder únicamente con un objeto JSON válido con los campos "seo_text" y "faqs".'],
                ['role' => 'user', 'content' => $prompt],
            ], [
                'response_format' => ['type' => 'json_object'],
                'max_tokens' => 800
            ]);

            $responseJson = trim($responseJson);
            $data = json_decode($responseJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data['seo_text'])) {
                // Fallback si no devuelve JSON válido
                $generatedText = $responseJson;
                $faqsData = [];
                $faqsJson = null;
            } else {
                $generatedText = trim($data['seo_text']);
                $faqsData = $data['faqs'] ?? [];
                $faqsJson = !empty($faqsData) ? json_encode($faqsData, JSON_UNESCAPED_UNICODE) : null;
            }
            
            if (empty($generatedText) || strpos($generatedText, 'Hubo un error') !== false) {
                throw new Exception("Error generado por OpenAI");
            }
            
            // Guardar en la tabla company_enrichment
            $db = \Config\Database::connect();
            
            $enrichmentRow = $db->table('company_enrichment')->where('company_id', $company['id'])->get()->getRowArray();
            
            if ($enrichmentRow) {
                $db->table('company_enrichment')
                   ->where('company_id', $company['id'])
                   ->update([
                       'ai_seo_text' => $generatedText,
                       'ai_faqs' => $faqsJson,
                       'updated_at' => date('Y-m-d H:i:s')
                   ]);
            } else {
                $db->table('company_enrichment')->insert([
                    'company_id' => $company['id'],
                    'ai_seo_text' => $generatedText,
                    'ai_faqs' => $faqsJson,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->respond([
                'status' => 'generated', 
                'text' => $generatedText,
                'faqs' => $faqsData
            ]);

        } catch (\Throwable $e) {
            log_message('error', '[AiSeoController] Error generando texto para CIF ' . $cif . ': ' . $e->getMessage());
            return $this->failServerError('No se pudo generar el resumen.');
        }
    }
}
