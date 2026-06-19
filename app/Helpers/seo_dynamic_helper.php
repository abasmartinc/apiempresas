<?php

if (!function_exists('calculateCompanySeoScore')) {
    /**
     * Calcula un score de calidad SEO basado en los datos disponibles.
     * Score máximo aproximado: 9
     */
    function calculateCompanySeoScore(array $company): int
    {
        $score = 0;

        $isValid = function ($value) {
            if ($value === null) return false;
            $v = trim((string)$value);
            return !in_array(strtoupper($v), ['', '-', '00 DESCONOCIDA', 'NULL', 'UNDEFINED']);
        };

        // 1. Identificación (+2)
        if ($isValid($company['name'] ?? null)) $score += 1;
        if ($isValid($company['cif'] ?? null)) $score += 1;

        // 2. Datos Geográficos y Actividad (+2)
        if ($isValid($company['province'] ?? null)) $score += 1;
        if ($isValid($company['cnae'] ?? null)) $score += 1;

        // 3. Objeto Social (+2) - Factor de peso
        if ($isValid($company['corporate_purpose'] ?? null)) $score += 2;

        // 4. Administradores (+2) - Factor de calidad humana
        if (!empty($company['num_admins']) && (int)$company['num_admins'] > 0) {
            $score += 2;
        }

        // 5. Historial BORME (+1)
        if (!empty($company['num_borme_posts']) && (int)$company['num_borme_posts'] > 0) {
            $score += 1;
        }

        // 6. Bonus Empresa Nueva (+1)
        $name = $company['name'] ?? '';
        if (strpos($name, '2024') !== false || strpos($name, '2025') !== false) {
            $score += 1;
        }

        // 7. Textos IA (+3) - Contenido original y extenso
        if (!empty($company['ai_seo_text'])) {
            $score += 3;
        }

        return $score;
    }
}

if (!function_exists('shouldIndexCompany')) {
    /**
     * Determina si una empresa debe ser indexada.
     * Umbral a 4 para incluir empresas básicas completas (Nombre+CIF+Provincia+CNAE).
     */
    function shouldIndexCompany(array $company): bool
    {
        return calculateCompanySeoScore($company) >= 4;
    }
}

if (!function_exists('getOrGenerateAiSeoData')) {
    /**
     * Obtiene los textos SEO de la IA y preguntas frecuentes si ya están cacheados,
     * o los genera de forma síncrona si no existen y hay API Key de OpenAI.
     */
    function getOrGenerateAiSeoData(array $company): ?array
    {
        // 1. Si ya tiene texto, devolverlo
        if (!empty($company['ai_seo_text'])) {
            $faqs = null;
            if (!empty($company['ai_faqs'])) {
                $faqs = json_decode($company['ai_faqs'], true);
            }
            return [
                'status' => 'cached',
                'text'   => $company['ai_seo_text'],
                'faqs'   => $faqs
            ];
        }

        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return null;
        }

        try {
            $aiService = new \App\Services\OpenAiService();
            
            $name    = $company['name'] ?? 'la empresa';
            $cnae    = $company['cnae_label'] ?? 'un sector no especificado';
            $founded = $company['founded'] ?? 'fecha desconocida';
            
            $prov = '';
            if (!empty($company['province'])) {
                $prov = $company['province'];
            } elseif (!empty($company['provincia'])) {
                $prov = $company['provincia'];
            } else {
                $prov = 'España';
            }
            
            $purpose = $company['corporate_purpose'] ?? '';
            
            $prompt = "Genera un objeto JSON que contenga los siguientes campos obligatorios:
            1. 'seo_text': Un texto descriptivo de la empresa '{$name}' altamente optimizado para SEO. Escribe exactamente 2 párrafos redactados con excelente estilo periodístico y de negocios. El primer párrafo debe presentar a la empresa, integrando de forma natural su sector de actividad (CNAE: {$cnae}) y su trayectoria (ubicación: {$prov}, fundación: {$founded}) de forma fluida. Evita empezar con plantillas robóticas como 'La empresa X es...'. El segundo párrafo debe sintetizar su objeto social ({$purpose}) detallando sus servicios, valor diferencial en el mercado y operaciones. No uses Markdown, asteriscos, negritas ni viñetas. Separa los párrafos con un salto de línea doble.
            2. 'faqs': Una lista de exactamente 3 preguntas frecuentes ('q') y respuestas ('a') personalizadas y de alta calidad para esta empresa. Las preguntas deben ser específicas sobre la actividad, servicios o sector de la empresa basándose en su objeto social y CNAE (evita preguntas totalmente genéricas). Las respuestas deben ser profesionales, informativas y en formato de texto plano sin Markdown ni negritas.
            3. 'seo_tags': Un array de strings con 5 a 8 palabras clave o servicios específicos extraídos de su objeto social (ej: [\"Construcción\", \"Reformas\", \"Albañilería\"]). Ideal para SEO.
            4. 'seo_pitch': Una sola frase comercial atractiva de máximo 150 caracteres que resuma lo que hace la empresa. Ideal para la meta description.
            
            Formato de salida JSON estricto esperado:
            {
              \"seo_text\": \"Párrafo 1\\n\\nPárrafo 2\",
              \"faqs\": [
                {
                  \"q\": \"¿Pregunta específica 1?\",
                  \"a\": \"Respuesta específica 1...\"
                }
              ],
              \"seo_tags\": [\"Tag1\", \"Tag2\", \"Tag3\"],
              \"seo_pitch\": \"Frase comercial corta...\"
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
                $generatedText = $responseJson;
                $faqsData      = [];
                $faqsJson      = null;
                $seoTagsJson   = null;
                $seoPitch      = null;
            } else {
                $generatedText = trim($data['seo_text']);
                $faqsData      = $data['faqs'] ?? [];
                $faqsJson      = !empty($faqsData) ? json_encode($faqsData, JSON_UNESCAPED_UNICODE) : null;
                $seoTags       = $data['seo_tags'] ?? [];
                $seoTagsJson   = !empty($seoTags) ? json_encode($seoTags, JSON_UNESCAPED_UNICODE) : null;
                $seoPitch      = !empty($data['seo_pitch']) ? trim($data['seo_pitch']) : null;
            }
            
            if (empty($generatedText) || strpos($generatedText, 'Hubo un error') !== false) {
                throw new \Exception("Error generado por OpenAI");
            }
            
            // Guardar en la tabla company_enrichment
            $db = \Config\Database::connect();
            $enrichmentRow = $db->table('company_enrichment')->where('company_id', $company['id'])->get()->getRowArray();
            
            if ($enrichmentRow) {
                $db->table('company_enrichment')
                   ->where('company_id', $company['id'])
                   ->update([
                       'ai_seo_text' => $generatedText,
                       'ai_faqs'     => $faqsJson,
                       'ai_tags'     => $seoTagsJson,
                       'ai_pitch'    => $seoPitch,
                       'updated_at'  => date('Y-m-d H:i:s')
                   ]);
            } else {
                $db->table('company_enrichment')->insert([
                    'company_id'  => $company['id'],
                    'ai_seo_text' => $generatedText,
                    'ai_faqs'     => $faqsJson,
                    'ai_tags'     => $seoTagsJson,
                    'ai_pitch'    => $seoPitch,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }

            return [
                'status' => 'generated',
                'text'   => $generatedText,
                'faqs'   => $faqsData,
                'tags'   => $seoTags ?? [],
                'pitch'  => $seoPitch
            ];

        } catch (\Throwable $e) {
            log_message('error', '[seo_dynamic_helper] Error generando texto para ID ' . $company['id'] . ': ' . $e->getMessage());
            return null;
        }
    }
}
