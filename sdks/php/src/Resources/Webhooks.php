<?php

namespace ApiEmpresas\Resources;

use ApiEmpresas\ApiEmpresas;
use ApiEmpresas\Exceptions\ApiException;

/**
 * (Business) Webhooks: la API te avisa por POST firmado cuando cambia algo que vigilas.
 */
class Webhooks
{
    /** Antigüedad máxima de la firma, en segundos. */
    public const DEFAULT_TOLERANCE = 300;

    private ApiEmpresas $client;

    public function __construct(ApiEmpresas $client)
    {
        $this->client = $client;
    }

    public function list(): array
    {
        $response = $this->client->request('GET', '/webhooks');
        return $response['data'] ?? [];
    }

    /**
     * Crea un webhook. Opciones: url (HTTPS), event (p. ej. watchlist.*), secret (opcional).
     * Devuelve id, event y secret: guarda el secret para comprobar la firma.
     */
    public function create(array $options): array
    {
        $response = $this->client->request('POST', '/webhooks', $options);
        return [
            'id'      => $response['id'] ?? null,
            'event'   => $response['event'] ?? null,
            'secret'  => $response['secret'] ?? null,
            'message' => $response['message'] ?? null,
        ];
    }

    public function remove(int $id): bool
    {
        $response = $this->client->request('DELETE', '/webhooks/' . $id);
        return ($response['success'] ?? true) !== false;
    }

    /**
     * Envía un test.ping a la URL del webhook y devuelve lo que respondió
     * (delivered, http_status, duration_ms, error, delivery_id). Si tu servidor falla,
     * la API responde 502: aquí se devuelve igualmente el resultado con delivered = false.
     */
    public function test(int $id): array
    {
        try {
            $response = $this->client->request('POST', '/webhooks/' . $id . '/test');
            return $response['data'] ?? [];
        } catch (ApiException $e) {
            $raw = $e->getRawData();
            if ($e->getStatusCode() === 502 && is_array($raw) && isset($raw['data']) && is_array($raw['data'])) {
                return $raw['data'];
            }
            throw $e;
        }
    }

    /**
     * Comprueba la cabecera X-ApiEmpresas-Signature (t=<timestamp>,v1=<hex>).
     * La firma es HMAC-SHA256(secret, "t.cuerpo") sobre el cuerpo en bruto:
     * usa file_get_contents('php://input'), no el JSON ya decodificado.
     *
     * @param int $tolerance Segundos de antigüedad aceptados (0 = sin límite).
     */
    public static function verifySignature(string $rawBody, ?string $signatureHeader, string $secret, int $tolerance = self::DEFAULT_TOLERANCE, ?int $now = null): bool
    {
        if ($signatureHeader === null || $signatureHeader === '' || $secret === '') {
            return false;
        }
        $t = null;
        $v1 = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) !== 2) {
                continue;
            }
            $k = trim($kv[0]);
            $v = trim($kv[1]);
            if ($k === 't') {
                $t = $v;
            } elseif ($k === 'v1') {
                $v1[] = $v;
            }
        }
        if ($t === null || !ctype_digit($t) || !$v1) {
            return false;
        }
        $now = $now ?? time();
        if ($tolerance > 0 && abs($now - (int) $t) > $tolerance) {
            return false;
        }
        $expected = hash_hmac('sha256', $t . '.' . $rawBody, $secret);
        foreach ($v1 as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Comprueba la firma y devuelve el evento decodificado (id, event, created_at, data).
     *
     * @throws ApiException 400 si la firma no es válida o ha caducado.
     */
    public static function constructEvent(string $rawBody, ?string $signatureHeader, string $secret, int $tolerance = self::DEFAULT_TOLERANCE): array
    {
        if (!self::verifySignature($rawBody, $signatureHeader, $secret, $tolerance)) {
            throw new ApiException('Firma del webhook no válida o caducada.', 400, 'INVALID_SIGNATURE');
        }
        $event = json_decode($rawBody, true);
        if (!is_array($event)) {
            throw new ApiException('El cuerpo del webhook no es JSON válido.', 400, 'INVALID_PAYLOAD');
        }
        return $event;
    }
}
