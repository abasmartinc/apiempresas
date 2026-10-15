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
        // 15-10-2026: con límite de tiempo. Sin él, una petición colgada bloqueaba la cola SEO
        // (y su fichero de bloqueo) hasta que el proceso moría.
        $httpClient = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 90, 'connect_timeout' => 10]);
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

        $params = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
        ], $options);

        // 15-10-2026: con 'throw' (la cola SEO, sin nadie esperando) un fallo pasajero de OpenAI
        // (respuesta que no es JSON, 5xx, límite de peticiones, red) se reintenta dos veces antes
        // de rendirse: un solo "Syntax error" paraba toda la tanda de la cola.
        $intentos = $throw ? 3 : 1;
        for ($intento = 1; ; $intento++) {
            try {
                $response = $this->client->chat()->create($params);

                return $response->choices[0]->message->content ?? 'Lo siento, no he podido procesar tu solicitud.';
            } catch (\Exception $e) {
                $detalle = self::describirError($e);
                if ($intento < $intentos && self::esPasajero($e)) {
                    log_message('warning', "OpenAI Chat: fallo pasajero (intento {$intento}/{$intentos}), se reintenta: {$detalle}");
                    sleep($intento * 5);
                    continue;
                }
                log_message('error', 'OpenAI Chat Error: ' . $detalle);
                if ($throw) {
                    // 503 = OpenAI no está disponible para nosotros (la cola SEO para sin gastar el
                    // intento). 400 = la petición de ESA empresa falla (la cola gasta el intento y
                    // sigue): si no, una empresa con un prompt rechazado bloqueaba la cola para siempre.
                    throw new \RuntimeException('OpenAI: ' . $detalle, self::esCaida($e) ? 503 : 400, $e);
                }
                return 'Hubo un error al conectar con la inteligencia artificial.';
            }
        }
    }

    /**
     * Mensaje del error con lo que hace falta para saber qué pasó. "Syntax error" a secas era la
     * excepción de openai-php cuando la respuesta no es JSON (p. ej. una página HTML de error 502);
     * ahora se añade el código HTTP y el principio de la respuesta.
     */
    public static function describirError(\Throwable $e): string
    {
        $msg = $e->getMessage();
        $resp = null;
        if ($e instanceof \OpenAI\Exceptions\UnserializableResponse || $e instanceof \OpenAI\Exceptions\ErrorException || $e instanceof \OpenAI\Exceptions\RateLimitException) {
            $resp = $e->response;
        }
        if ($resp !== null) {
            $cuerpo = trim(preg_replace('/\s+/', ' ', strip_tags((string) $resp->getBody())));
            $msg .= ' [HTTP ' . $resp->getStatusCode() . ', ' . ($resp->getHeaderLine('Content-Type') ?: 'sin Content-Type') . ']';
            if ($e instanceof \OpenAI\Exceptions\UnserializableResponse || $e instanceof \OpenAI\Exceptions\RateLimitException) {
                $msg .= ' respuesta: ' . ($cuerpo !== '' ? mb_substr($cuerpo, 0, 160) : '(vacía)');
            }
        }
        return $msg;
    }

    /** OpenAI no responde o no nos deja usarlo: fallo pasajero, clave inválida o sin permiso. */
    public static function esCaida(\Throwable $e): bool
    {
        return self::esPasajero($e)
            || ($e instanceof \OpenAI\Exceptions\ErrorException && in_array($e->getStatusCode(), [401, 403], true));
    }

    /** Fallos que suelen arreglarse solos al poco: no son un problema de la petición. */
    public static function esPasajero(\Throwable $e): bool
    {
        if ($e instanceof \OpenAI\Exceptions\UnserializableResponse
            || $e instanceof \OpenAI\Exceptions\TransporterException
            || $e instanceof \OpenAI\Exceptions\RateLimitException) {
            return true;
        }
        return $e instanceof \OpenAI\Exceptions\ErrorException && $e->getStatusCode() >= 500;
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
