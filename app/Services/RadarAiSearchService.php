<?php

namespace App\Services;

class RadarAiSearchService
{
    protected $openAiService;

    public function __construct()
    {
        $this->openAiService = new OpenAiService();
    }

    /**
     * Interpreta la consulta del usuario en lenguaje natural y la convierte en filtros estructurados.
     *
     * @param string $query Consulta del usuario
     * @return array|null JSON interpretado o null en caso de error
     */
    public function interpretQuery(string $query)
    {
        $systemPrompt = <<<PROMPT
Eres un asistente experto en prospección comercial B2B para el mercado español.
Tu tarea es convertir frases de búsqueda de usuarios en un objeto JSON estructurado con filtros para una base de datos de empresas.

DEVUELVE SIEMPRE un JSON con EXACTAMENTE estas claves (usa null si no aplica):
{
  "province": string|null,         // Nombre de la provincia en español con mayúscula inicial. Ej: "Madrid", "Barcelona", "Valencia"
  "sector": string|null,           // Sector o actividad principal. Ej: "construccion", "hosteleria", "tecnologia"
  "cnae_keywords": array|null,     // Lista de 1-3 palabras clave del sector para búsqueda en objeto social
  "date_range": string|null,       // Uno de: "today", "last_7_days", "last_30_days", "last_90_days"
  "min_score": integer|null,       // Score mínimo (0-100). "alto" = 75, "muy alto" = 85
  "has_phone": boolean,            // true si piden empresas con teléfono
  "has_email": boolean,            // true si piden empresas con email
  "has_website": boolean,          // true si piden empresas con web
  "limit": integer|null,           // Número de resultados (máx 100)
  "explanation": string            // Explicación corta en español de cómo interpretaste la búsqueda
}

REGLAS DE MAPEO:
- "score alto" → min_score: 75
- "score muy alto" / "excelentes leads" / "mejores leads" → min_score: 85
- "recientes" sin especificar → date_range: "last_30_days"
- "esta semana" → date_range: "last_7_days"
- "hoy" → date_range: "today"
- "con teléfono" → has_phone: true
- "con web" → has_website: true
- "con email" → has_email: true
- Si mencionan una provincia española, ponla EXACTAMENTE en "province" con mayúscula inicial
- Si el usuario NO menciona score o calidad, deja min_score en null
- La palabra "leads" sola NO implica min_score alto; solo si añade "buenos", "alto", "calidad"

IMPORTANTE: Devuelve ÚNICAMENTE el objeto JSON, sin texto adicional.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Interpreta esta búsqueda y devuelve el JSON: \"$query\""]
        ];

        // Usamos Function Calling o Structured Outputs mediante un formato de respuesta JSON
        $options = [
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.1, // Baja temperatura para mayor consistencia
        ];

        $response = $this->openAiService->getChatResponse($messages, $options);

        if (!$response || strpos($response, 'Hubo un error') !== false) {
            return null;
        }

        try {
            return json_decode($response, true);
        } catch (\Exception $e) {
            log_message('error', 'RadarAiSearchService - JSON Decode Error: ' . $e->getMessage());
            return null;
        }
    }
}
