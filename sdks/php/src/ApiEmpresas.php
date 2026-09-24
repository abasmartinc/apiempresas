<?php

namespace ApiEmpresas;

use ApiEmpresas\Exceptions\ApiException;
use ApiEmpresas\Resources\Companies;

class ApiEmpresas
{
    /** Versión del SDK, enviada en el User-Agent. */
    public const VERSION = '1.1.0';

    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    
    public Companies $companies;

    /**
     * ApiEmpresas constructor.
     *
     * @param string $apiKey Tu clave de API.
     * @param string $baseUrl URL base de la API (por defecto producción).
     * @param int $timeout Tiempo máximo de espera en segundos.
     * @throws ApiException Si no se proporciona la apiKey.
     */
    public function __construct(string $apiKey, string $baseUrl = 'https://apiempresas.es/api/v1', int $timeout = 30)
    {
        if (empty(trim($apiKey))) {
            throw new ApiException("La propiedad 'apiKey' es obligatoria para inicializar ApiEmpresas.", 401);
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;

        // Inicializar recursos
        $this->companies = new Companies($this);
    }

    /**
     * Método genérico para hacer peticiones HTTP usando cURL nativo.
     *
     * @param string $method GET, POST, etc.
     * @param string $endpoint Endpoint a llamar (ej. /companies).
     * @param array|null $body Datos para el cuerpo de la petición.
     * @return array La respuesta decodificada.
     * @throws ApiException Si ocurre un error HTTP o de red.
     */
    public function request(string $method, string $endpoint, ?array $body = null): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);

        if ($ch === false) {
            throw new ApiException("No se pudo inicializar cURL.", 500);
        }

        $headers = [
            'X-API-KEY: ' . $this->apiKey,
            'Accept: application/json',
            'User-Agent: apiempresas-php/' . self::VERSION,
        ];

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            if ($body !== null) {
                $payload = json_encode($body);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($payload);
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        // Descomentar para debug si hay problemas de SSL en local:
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new ApiException("Error de red o timeout: " . $error, 500);
        }

        $decodedData = json_decode($response, true);
        $isJson = (json_last_error() === JSON_ERROR_NONE);

        if ($statusCode >= 400) {
            $message = $statusCode . ' Error desconocido en la API';
            $errorCode = null;
            
            if ($isJson && is_array($decodedData)) {
                // Según el error, la API pone el texto en message, detail, error o messages.
                $message = self::extractErrorMessage($decodedData) ?? $message;
                $errorCode = isset($decodedData['error']) && is_scalar($decodedData['error']) ? (string) $decodedData['error'] : null;
            } else {
                $message = $response; // Fallback si devuelve HTML
            }

            throw new ApiException($message, $statusCode, $errorCode, $isJson ? $decodedData : null);
        }

        return $isJson ? $decodedData : ['raw' => $response];
    }

    private static function extractErrorMessage(array $data): ?string
    {
        foreach (['message', 'detail'] as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }
        if (isset($data['error']) && is_string($data['error']) && $data['error'] !== '') {
            return $data['error'];
        }
        if (isset($data['messages']) && is_array($data['messages'])) {
            $first = reset($data['messages']);
            if (is_string($first) && $first !== '') {
                return $first;
            }
        }
        return null;
    }
}
