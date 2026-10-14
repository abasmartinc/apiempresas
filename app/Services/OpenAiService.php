<?php

namespace App\Services;

use OpenAI;

class OpenAiService
{
    protected $client;
    protected $model = 'gpt-4o-mini';

    public function __construct()
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'OpenAI API Key not found in .env');
        }

        // Custom Guzzle client with verify=false to avoid local SSL problems (cURL 60)
        $httpClient = new \GuzzleHttp\Client(['verify' => false]);
        $this->client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($httpClient)
            ->make();
    }

    /**
     * Send a conversation to OpenAI and get a response.
     *
     * Por defecto, si OpenAI falla devuelve un texto de disculpa (lo enseña el chat). Con
     * $options['throw'] = true lanza una RuntimeException con código 503 y el mensaje real: la
     * generación de textos SEO (14-10-2026) recibía la disculpa, la leía como "JSON no válido" y
     * gastaba los 3 intentos de cada empresa durante una caída de la API (732 errores el 12-10).
     */
    public function getChatResponse(array $messages, array $options = [])
    {
        $throw = !empty($options['throw']);
        unset($options['throw']);

        try {
            $params = array_merge([
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
            ], $options);

            $response = $this->client->chat()->create($params);

            return $response->choices[0]->message->content ?? 'Lo siento, no he podido procesar tu solicitud.';
        } catch (\Exception $e) {
            log_message('error', 'OpenAI Chat Error: ' . $e->getMessage());
            if ($throw) {
                throw new \RuntimeException('OpenAI: ' . $e->getMessage(), 503, $e);
            }
            return 'Hubo un error al conectar con la inteligencia artificial.';
        }
    }

    /**
     * Generate embeddings for a given text.
     */
    public function getEmbeddings(string $text)
    {
        try {
            $response = $this->client->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            log_message('error', 'OpenAI Embedding Error: ' . $e->getMessage());
            return null;
        }
    }
}
