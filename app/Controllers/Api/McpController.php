<?php

namespace App\Controllers\Api;

use App\Filters\ApiKeyFilter;
use App\Libraries\McpServer;
use CodeIgniter\Controller;

/**
 * Servidor MCP remoto: https://apiempresas.es/api/mcp (16-10-2026). Ver App\Libraries\McpServer.
 *
 * La API key del usuario puede llegar:
 *  - en la cabecera Authorization: Bearer <clave> o X-API-KEY (Claude Code, Cursor, VS Code...);
 *  - en la dirección: /api/mcp/<clave> o ?api_key=<clave> (Claude.ai y ChatGPT no dejan poner cabeceras).
 *
 * Cada herramienta llama a la API (/api/v1) con esa clave, así que el cobro, los límites y los
 * planes son los de siempre (ApiKeyFilter). La IP del usuario viaja firmada (X-MCP-Client-IP y
 * X-MCP-Signature, ver ApiKeyFilter::ipCliente) para que el límite por IP del Free siga contando
 * por usuario y no por servidor.
 *
 * A quién se llama, por orden:
 *  1. MCP_API_BASE_URL del .env, si existe.
 *  2. En producción, el propio servidor directamente (http, sin pasar por Cloudflare): la llamada
 *     a https://apiempresas.es desde el servidor se quedaba sin respuesta (16-10-2026).
 *  3. La dirección pública (site_url('api/v1')).
 * La que funciona se recuerda 1 hora.
 *
 * Sin estado: no hay SSE ni sesiones. GET y DELETE responden 405, como permite la especificación.
 */
class McpController extends Controller
{
    private const TIMEOUT = 25;

    public function handle(?string $keyEnRuta = null)
    {
        $metodo = strtoupper($this->request->getMethod());
        $this->response->setHeader('Cache-Control', 'no-store');

        if ($metodo === 'OPTIONS') {
            return $this->response->setStatusCode(204);
        }
        if ($metodo !== 'POST') {
            return $this->response->setStatusCode(405)->setHeader('Allow', 'POST, OPTIONS')
                ->setJSON([
                    'error'   => 'METHOD_NOT_ALLOWED',
                    'message' => 'Servidor MCP de APIEmpresas (transporte HTTP). Conéctalo desde un cliente MCP como Claude, ChatGPT o Cursor; las peticiones son POST. Guía: https://apiempresas.es/documentation#mcp',
                ]);
        }

        $key = $this->apiKey($keyEnRuta);
        $ip  = ApiKeyFilter::ipCliente($this->request);

        $server = new McpServer($key, static function (string $method, string $path, ?array $body) use ($key, $ip) {
            return self::llamarApi($path, $method, $body, $key, $ip);
        });

        [$status, $resp] = $server->handle((string) $this->request->getBody());

        if ($resp === null) {
            return $this->response->setStatusCode($status);
        }
        return $this->response->setStatusCode($status)
            ->setContentType('application/json')
            ->setBody(json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function apiKey(?string $keyEnRuta): string
    {
        $auth = ApiKeyFilter::cabeceraAuthorization($this->request);
        if (stripos($auth, 'Bearer ') === 0) {
            return trim(substr($auth, 7));
        }
        $h = trim((string) $this->request->getHeaderLine('X-API-KEY'));
        if ($h !== '') {
            return $h;
        }
        if ($keyEnRuta !== null && $keyEnRuta !== '') {
            return trim(urldecode($keyEnRuta));
        }
        return trim((string) ($this->request->getGet('api_key') ?? ''));
    }

    /** Destinos posibles para llamar a la API, por orden de preferencia. */
    private static function destinos(): array
    {
        $env = trim((string) env('MCP_API_BASE_URL'));
        if ($env !== '') {
            return [['nombre' => 'env', 'base' => rtrim($env, '/'), 'resolve' => null]];
        }
        $publica = rtrim(site_url('api/v1'), '/');
        $host = (string) parse_url($publica, PHP_URL_HOST);
        $path = (string) parse_url($publica, PHP_URL_PATH);
        $ipServidor = (string) ($_SERVER['SERVER_ADDR'] ?? '');
        $local = $host === 'localhost' || str_ends_with($host, '.test') || $ipServidor === '';

        $lista = [];
        $recordado = cache('mcp_api_destino');
        if (!$local) {
            $lista[] = ['nombre' => 'origen', 'base' => 'http://' . $host . $path, 'resolve' => $host . ':80:' . (str_contains($ipServidor, ':') ? '[' . $ipServidor . ']' : $ipServidor)];
        }
        $lista[] = ['nombre' => 'publica', 'base' => $publica, 'resolve' => null];
        if ($recordado === 'publica' && count($lista) > 1) {
            $lista = array_reverse($lista);
        }
        return $lista;
    }

    /** @return array{status:int, body:mixed} */
    private static function llamarApi(string $path, string $method, ?array $body, string $key, string $ip): array
    {
        $fallos = [];
        foreach (self::destinos() as $d) {
            $r = self::peticion($d, $path, $method, $body, $key, $ip);
            if ($r['ok']) {
                if ($d['nombre'] !== 'env') {
                    cache()->save('mcp_api_destino', $d['nombre'], 3600);
                }
                return ['status' => $r['status'], 'body' => $r['body']];
            }
            $fallos[] = $d['nombre'] . ': ' . $r['error'];
        }
        log_message('error', '[McpController] sin respuesta de la API (' . implode(' | ', $fallos) . ')');
        return ['status' => 0, 'body' => ['detalle' => implode(' | ', $fallos)]];
    }

    /** @return array{ok:bool, status:int, body:mixed, error:string} */
    private static function peticion(array $d, string $path, string $method, ?array $body, string $key, string $ip): array
    {
        $headers = [
            'X-API-KEY: ' . $key,
            'Accept: application/json',
            'User-Agent: apiempresas-mcp-remote/' . McpServer::VERSION,
        ];
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
            $headers[] = 'X-MCP-Client-IP: ' . $ip;
            $headers[] = 'X-MCP-Signature: ' . hash_hmac('sha256', $ip, ApiKeyFilter::secretoInterno());
        }
        $esOrigen = $d['nombre'] === 'origen';
        if ($esOrigen) {
            $headers[] = 'X-Forwarded-Proto: https'; // para que la API genere sus enlaces con https
        }
        $ch = curl_init($d['base'] . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $esOrigen ? 15 : self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => $esOrigen ? 3 : 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        if ($d['resolve'] !== null) {
            $opts[CURLOPT_RESOLVE] = [$d['resolve']];
        }
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? new \stdClass(), JSON_UNESCAPED_UNICODE);
            $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = $raw === false ? curl_error($ch) : '';
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'status' => 0, 'body' => null, 'error' => $err];
        }
        $json = json_decode((string) $raw, true);
        // Una redirección o una página que no es JSON no es la API (p. ej. la página por defecto del servidor)
        if (!is_array($json) || ($status >= 300 && $status < 400)) {
            return ['ok' => false, 'status' => $status, 'body' => null, 'error' => 'HTTP ' . $status . ' sin JSON'];
        }
        return ['ok' => true, 'status' => $status, 'body' => $json, 'error' => ''];
    }
}
