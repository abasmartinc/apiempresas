<?php

namespace App\Controllers\Api;

use App\Libraries\McpServer;
use CodeIgniter\Controller;

/**
 * Servidor MCP remoto: https://apiempresas.es/api/mcp (16-10-2026). Ver App\Libraries\McpServer.
 *
 * La API key del usuario puede llegar:
 *  - en la cabecera Authorization: Bearer <clave> o X-API-KEY (Claude Code, Cursor, VS Code...);
 *  - en la dirección: /api/mcp/<clave> o ?api_key=<clave> (Claude.ai y ChatGPT no dejan poner cabeceras).
 *
 * Cada herramienta llama a la API pública (/api/v1) con esa clave, así que el cobro, los límites y
 * los planes son los de siempre (ApiKeyFilter). Se reenvía la IP del usuario en X-Forwarded-For para
 * que el límite por IP del plan Free siga contando por usuario y no por servidor.
 *
 * Sin estado: no hay SSE ni sesiones. GET y DELETE responden 405, como permite la especificación.
 * Base de las llamadas: MCP_API_BASE_URL en el .env (por defecto site_url('api/v1')).
 */
class McpController extends Controller
{
    private const TIMEOUT = 30;

    public function handle(?string $keyEnRuta = null)
    {
        $metodo = strtoupper($this->request->getMethod());
        $this->response->setHeader('Cache-Control', 'no-store');

        if ($metodo === 'OPTIONS') {
            return $this->response->setStatusCode(204);
        }
        if ($metodo !== 'POST') {
            // Sin flujo SSE: los clientes usan solo POST. Un GET desde el navegador recibe una explicación.
            return $this->response->setStatusCode(405)->setHeader('Allow', 'POST, OPTIONS')
                ->setJSON([
                    'error'   => 'METHOD_NOT_ALLOWED',
                    'message' => 'Servidor MCP de APIEmpresas (transporte HTTP). Conéctalo desde un cliente MCP como Claude, ChatGPT o Cursor; las peticiones son POST. Guía: https://apiempresas.es/documentation#mcp',
                ]);
        }

        $key = $this->apiKey($keyEnRuta);
        $ip  = (string) $this->request->getIPAddress();
        $base = rtrim((string) (env('MCP_API_BASE_URL') ?: site_url('api/v1')), '/');

        $server = new McpServer($key, function (string $method, string $path, ?array $body) use ($key, $ip, $base) {
            return self::llamarApi($base . $path, $method, $body, $key, $ip);
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
        $auth = trim((string) $this->request->getHeaderLine('Authorization'));
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

    /** @return array{status:int, body:mixed} */
    private static function llamarApi(string $url, string $method, ?array $body, string $key, string $ip): array
    {
        $headers = [
            'X-API-KEY: ' . $key,
            'Accept: application/json',
            'User-Agent: apiempresas-mcp-remote/' . McpServer::VERSION,
        ];
        if ($ip !== '') {
            $headers[] = 'X-Forwarded-For: ' . $ip;
        }
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? new \stdClass(), JSON_UNESCAPED_UNICODE);
            $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false) {
            log_message('error', '[McpController] ' . curl_error($ch) . ' ' . preg_replace('/\?.*/', '', $url));
            curl_close($ch);
            return ['status' => 0, 'body' => null];
        }
        curl_close($ch);
        $json = json_decode((string) $raw, true);
        return ['status' => $status, 'body' => $json ?? (string) $raw];
    }
}
