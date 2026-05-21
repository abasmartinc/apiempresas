<?php

namespace App\Services;

use Config\Database;
use App\Services\OpenAiService;

class SerperEnrichmentService
{
    protected string $serperApiKey;
    protected OpenAiService $openAiService;
    protected $db;

    public function __construct()
    {
        $this->serperApiKey = env('SERPER_API_KEY') ?: '';
        $this->openAiService = new OpenAiService();
        $this->db = Database::connect();
    }

    /**
     * Enriches a company by CIF in real-time.
     * Searches Serper.dev, uses OpenAI to structure the data, and saves to the database.
     * 
     * @param string $cif CIF of the company
     * @return bool True if successfully enriched and saved, false otherwise.
     */
    public function enrichByCif(string $cif): bool
    {
        $cif = strtoupper(trim($cif));
        
        // 1. Validation: Spanish CIF format
        if (!preg_match('/^[A-Z]\d{7}[A-Z0-9]$/', $cif)) {
            return false;
        }

        // 2. Check API Key
        if (empty($this->serperApiKey)) {
            log_message('debug', '[SerperEnrichmentService] Bypassed enrichment: SERPER_API_KEY is not configured.');
            return false;
        }

        // 3. Check Negative Cache
        $cache = \Config\Services::cache();
        $cacheKey = 'cif_not_found_' . $cif;
        if ($cache->get($cacheKey)) {
            log_message('debug', '[SerperEnrichmentService] Bypassed enrichment: CIF ' . $cif . ' is marked as not found in negative cache.');
            return false;
        }

        log_message('info', '[SerperEnrichmentService] Starting enrichment for CIF: ' . $cif);

        // 4. Query Serper.dev
        $searchResults = $this->querySerper($cif);
        if (empty($searchResults)) {
            log_message('warning', '[SerperEnrichmentService] Serper returned no results for CIF: ' . $cif);
            $cache->save($cacheKey, true, 86400); // Cache negative result for 24h
            return false;
        }

        // 5. Parse using OpenAI
        $companyData = $this->parseResultsWithAi($cif, $searchResults);
        if (!$companyData || isset($companyData['error']) || empty($companyData['company_name'])) {
            log_message('warning', '[SerperEnrichmentService] AI could not resolve structured data for CIF: ' . $cif);
            $cache->save($cacheKey, true, 86400); // Cache negative result for 24h
            return false;
        }

        // 6. Normalize data (dates, status, etc.) before DB insert
        $companyData = $this->normalizeData($companyData);

        // 7. Save to database in a transaction
        return $this->saveToDatabase($cif, $companyData);
    }

    /**
     * Query Serper.dev Google Search API
     */
    protected function querySerper(string $cif): array
    {
        $url = 'https://google.serper.dev/search';
        $payload = json_encode([
            'q' => '"' . $cif . '"',
            'gl' => 'es',
            'hl' => 'es'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Max 5 seconds
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent SSL certificate issues locally
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $this->serperApiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            log_message('error', '[SerperEnrichmentService] Serper cURL failed. HTTP Code: ' . $httpCode . '. Error: ' . $error);
            return [];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', '[SerperEnrichmentService] Serper response JSON decode failed.');
            return [];
        }

        $results = [];

        // Add Knowledge Graph if present
        if (!empty($decoded['knowledgeGraph'])) {
            $results['knowledgeGraph'] = [
                'title' => $decoded['knowledgeGraph']['title'] ?? '',
                'description' => $decoded['knowledgeGraph']['description'] ?? '',
                'attributes' => $decoded['knowledgeGraph']['attributes'] ?? []
            ];
        }

        // Add Organic results (limit to top 6 to reduce prompt size and speed up LLM response)
        if (!empty($decoded['organic'])) {
            $results['organic'] = [];
            foreach (array_slice($decoded['organic'], 0, 6) as $item) {
                $results['organic'][] = [
                    'title' => $item['title'] ?? '',
                    'snippet' => $item['snippet'] ?? '',
                    'link' => $item['link'] ?? ''
                ];
            }
        }

        return $results;
    }

    /**
     * Send search results to OpenAI to extract structured database fields
     */
    protected function parseResultsWithAi(string $cif, array $searchResults): ?array
    {
        $systemPrompt = <<<PROMPT
Eres un asistente experto en prospección comercial y extracción de datos. Tu tarea es analizar los resultados de búsqueda de Google referentes a un CIF español y estructurar la información en un objeto JSON limpio y preciso.

Estructura el JSON devuelto con EXACTAMENTE las siguientes claves. Si un campo no se puede determinar o no está en la información suministrada, devuélvelo como null:
{
  "company_name": string|null,       // Nombre oficial de la empresa en MAYÚSCULAS. Ej: "CONSTRUCCIONES GONZALEZ SL".
  "cnae_code": string|null,          // Código CNAE de 4 dígitos. Ej: "4121".
  "cnae_label": string|null,         // Descripción del CNAE SIEMPRE EN ESPAÑOL. Ej: "Construcción de edificios residenciales".
  "objeto_social": string|null,      // Breve resumen del objeto social en español.
  "fecha_constitucion": string|null, // Fecha de constitución en formato ISO AAAA-MM-DD. Convierte cualquier formato (ej: "02/01/1952" -> "1952-01-02", "12 de marzo de 2021" -> "2021-03-12").
  "registro_mercantil": string|null, // Provincia del Registro Mercantil. Ej: "Madrid".
  "address": string|null,            // Domicilio social completo. Ej: "Calle Mayor, 12, Planta 3".
  "municipality": string|null,       // Municipio de la empresa en MAYÚSCULAS. Ej: "MADRID".
  "estado": string|null,             // Estado SIEMPRE EN ESPAÑOL: "Activa", "Disuelta", "Liquidada", "Inactiva". Si no se menciona, pon "Activa".
  "phone": string|null,              // Teléfono fijo (solo dígitos, sin espacios ni guiones). Ej: "914018500".
  "phone_mobile": string|null,       // Teléfono móvil (solo dígitos). Ej: "600000000".
  "website_official": string|null,   // Web oficial de la empresa (no directorios tipo eInforma/Axesor).
  "email": string|null               // Email oficial de contacto.
}

REGLAS IMPORTANTES:
- Si no encuentras el nombre de la empresa ni evidencias sólidas de que el CIF existe, responde con: { "error": "not_found" }
- Extrae únicamente información de la empresa correspondiente al CIF solicitado.
- No inventes datos. Si no hay información para un campo, déjalo en null.
- Devuelve únicamente el objeto JSON, sin explicaciones ni markdown.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            [
                'role' => 'user', 
                'content' => "Extrae la información para el CIF \"$cif\" de los siguientes resultados de búsqueda:\n\n" . json_encode($searchResults, JSON_UNESCAPED_UNICODE)
            ]
        ];

        $options = [
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.1, // Consistencia total
            'max_tokens' => 800
        ];

        $response = $this->openAiService->getChatResponse($messages, $options);

        if (!$response || strpos($response, 'Hubo un error') !== false) {
            return null;
        }

        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            log_message('error', '[SerperEnrichmentService] AI Response JSON Decode Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalizes data received from the AI to ensure proper formats before DB insert.
     */
    protected function normalizeData(array $data): array
    {
        // Normalize fecha_constitucion to YYYY-MM-DD
        if (!empty($data['fecha_constitucion'])) {
            $raw = trim($data['fecha_constitucion']);
            // Handle DD/MM/YYYY
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $m)) {
                $data['fecha_constitucion'] = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
            // Handle DD-MM-YYYY
            elseif (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $raw, $m)) {
                $data['fecha_constitucion'] = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
            // Validate final format, discard if invalid
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_constitucion'])) {
                $data['fecha_constitucion'] = null;
            }
        }

        // Normalize estado to valid Spanish value
        $estadoMap = [
            'active' => 'Activa', 'activo' => 'Activa', 'activa' => 'Activa',
            'inactive' => 'Inactiva', 'inactivo' => 'Inactiva',
            'dissolved' => 'Disuelta', 'disuelto' => 'Disuelta', 'disuelta' => 'Disuelta',
            'liquidated' => 'Liquidada', 'liquidado' => 'Liquidada', 'liquidada' => 'Liquidada',
        ];
        if (!empty($data['estado'])) {
            $key = strtolower(trim($data['estado']));
            $data['estado'] = $estadoMap[$key] ?? $data['estado'];
        } else {
            $data['estado'] = 'Activa';
        }

        // Sanitize phone numbers: keep only digits
        foreach (['phone', 'phone_mobile'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/[^0-9]/', '', $data[$field]) ?: null;
            }
        }

        return $data;
    }

    /**
     * Saves the parsed company data to the database inside a transaction.
     */
    protected function saveToDatabase(string $cif, array $data): bool
    {
        $this->db->transStart();

        try {
            // 1. Insert into companies table
            $companyData = [
                'company_name'       => $data['company_name'],
                'cif'                => $cif,
                'cnae_code'          => $data['cnae_code'] ?? null,
                'cnae_label'         => $data['cnae_label'] ?? null,
                'objeto_social'      => $data['objeto_social'] ?? null,
                'fecha_constitucion' => $data['fecha_constitucion'] ?? null,
                'registro_mercantil' => $data['registro_mercantil'] ?? null,
                'address'            => $data['address'] ?? null,
                'municipality'       => $data['municipality'] ?? null,
                'estado'             => $data['estado'] ?? 'Activa',
                'phone'              => $data['phone'] ?? null,
                'phone_mobile'       => $data['phone_mobile'] ?? null,
                'lat_num'            => null,
                'lng_num'            => null
            ];

            $this->db->table('companies')->insert($companyData);
            $companyId = $this->db->insertID();

            // 2. Insert into company_enrichment table
            if ($companyId) {
                $enrichmentData = [
                    'company_id'       => $companyId,
                    'website_official' => $data['website_official'] ?? null,
                    'email'            => $data['email'] ?? null,
                    'phone_enriched'   => null,
                    'phone_mobile_enriched' => null
                ];
                $this->db->table('company_enrichment')->insert($enrichmentData);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', '[SerperEnrichmentService] DB transaction failed for CIF: ' . $cif);
                return false;
            }

            log_message('info', '[SerperEnrichmentService] Successfully enriched and saved company: ' . $data['company_name'] . ' with CIF: ' . $cif);
            return true;

        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[SerperEnrichmentService] DB error during save for CIF ' . $cif . ': ' . $e->getMessage());
            return false;
        }
    }
}
