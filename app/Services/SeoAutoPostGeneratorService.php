<?php

namespace App\Services;

class SeoAutoPostGeneratorService
{
    protected $openAiService;

    public function __construct()
    {
        $this->openAiService = new OpenAiService();
    }

    /**
     * Generates a blog post using OpenAI.
     * 
     * @param string $keyword
     * @param string $intent
     * @return array|null Returns [title, meta_description, slug, content_html] or null on failure.
     */
    public function generate(string $keyword, string $intent): ?array
    {
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');
        
        $prompt = <<<EOT
Actúa como experto SEO, content strategist y copywriter SaaS B2B.

Debes generar un artículo de blog optimizado para SEO y conversión para un producto llamado AlertaEmpresas.

AlertaEmpresas permite monitorizar empresas y recibir alertas automáticas sobre cambios relevantes como:

* publicaciones BORME
* cambios de administrador
* ampliaciones de capital
* concursos de acreedores
* señales de crecimiento empresarial
* cambios societarios

Keyword principal:
{$keyword}

Intención:
{$intent}

Objetivo del artículo:

* Posicionar en Google.
* Resolver la intención real del usuario.
* Aportar valor práctico.
* Conducir de forma natural hacia AlertaEmpresas.

Reglas obligatorias:

* No generar contenido genérico.
* No crear listados de empresas.
* No crear directorios.
* No inventar datos estadísticos.
* No mencionar precios si no se proporcionan.
* No repetir la keyword artificialmente.
* Usar variaciones semánticas naturales.
* El contenido debe parecer escrito manualmente.
* Debe tener utilidad real para un profesional B2B.

Estructura recomendada:

* Introducción directa.
* Explicación del problema.
* Cómo resolverlo.
* Ejemplos prácticos.
* Beneficios de automatizar el proceso.
* Conexión natural con AlertaEmpresas.
* FAQ SEO.
* CTA final suave.

Devuelve SOLO un JSON válido con esta estructura exacta:

{
"title": "Título SEO máximo 60 caracteres",
"meta_description": "Meta description máximo 155 caracteres",
"slug": "slug-url-amigable",
"content_html": "<article>...</article>"
}

El content_html debe contener HTML limpio usando:
h1, h2, h3, p, ul, li, strong, a.

Incluir dentro del artículo un CTA natural hacia:
https://alertaempresas.es

No devuelvas markdown.
No devuelvas explicaciones.
No devuelvas texto fuera del JSON.
EOT;

        $messages = [
            ['role' => 'system', 'content' => 'Eres un experto copywriter SEO B2B.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->openAiService->getChatResponse($messages, [
            'model' => $model,
            'response_format' => ['type' => 'json_object']
        ]);

        if (empty($response) || strpos($response, 'Hubo un error') !== false) {
            return null;
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'OpenAI response is not valid JSON: ' . $response);
            return null;
        }

        // Basic validation of keys
        $requiredKeys = ['title', 'meta_description', 'slug', 'content_html'];
        foreach ($requiredKeys as $key) {
            if (!isset($decoded[$key])) {
                log_message('error', "OpenAI response missing key: {$key}");
                return null;
            }
        }

        return $decoded;
    }
}
