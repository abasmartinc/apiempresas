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
        if ($metodo === 'GET' && $this->request->getGet('diag') === 'cabeceras') {
            // Diagnóstico (16-10-2026): qué cabeceras de autorización llegan a PHP. Solo nombres, nunca valores.
            $srv = array_values(array_filter(array_keys($_SERVER), static fn($k) => stripos((string) $k, 'AUTHORIZATION') !== false || stripos((string) $k, 'HTTP_X_API_KEY') !== false));
            $todas = function_exists('getallheaders') ? array_keys((array) getallheaders()) : ['(getallheaders no disponible)'];
            $longitudes = [];
            foreach ($srv as $k) {
                $longitudes[$k] = strlen((string) $_SERVER[$k]); // longitud del valor, nunca el valor
            }
            $hdr = function_exists('getallheaders') ? (array) getallheaders() : [];
            foreach ($hdr as $n => $v) {
                if (strcasecmp((string) $n, 'Authorization') === 0) {
                    $longitudes['getallheaders:Authorization'] = strlen((string) $v);
                }
            }
            $longitudes['getHeaderLine:Authorization'] = strlen((string) $this->request->getHeaderLine('Authorization'));
            return $this->response->setJSON(['server_auth_keys' => $srv, 'longitudes' => $longitudes, 'headers' => $todas, 'sapi' => PHP_SAPI, 'software' => $_SERVER['SERVER_SOFTWARE'] ?? '']);
        }
        if ($metodo === 'GET' && $this->request->getGet('diag') === 'red') {
            return $this->response->setJSON(self::diagnosticoRed());
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

    /**
     * Diagnóstico (16-10-2026): prueba las formas en que el servidor puede llamarse a sí mismo y dice
     * cuál responde. Solo llama a /api/v1/catalog sin clave (responde 401 en JSON si llega a la API).
     */
    private static function diagnosticoRed(): array
    {
        $publica = rtrim(site_url('api/v1'), '/');
        $host = (string) parse_url($publica, PHP_URL_HOST);
        $ipS = (string) ($_SERVER['SERVER_ADDR'] ?? '');
        $ipB = str_contains($ipS, ':') ? '[' . $ipS . ']' : $ipS;
        $ruta = '/api/v1/catalog';
        $casos = [
            'ip_servidor_https' => ['url' => 'https://' . $host . $ruta, 'resolve' => $host . ':443:' . $ipB],
            'localhost_https'   => ['url' => 'https://' . $host . $ruta, 'resolve' => $host . ':443:127.0.0.1'],
            'localhost_http'    => ['url' => 'http://' . $host . $ruta, 'resolve' => $host . ':80:127.0.0.1'],
            'apache_7080'       => ['url' => 'http://' . $ipB . ':7080' . $ruta, 'host' => $host],
            'apache_7081'       => ['url' => 'https://' . $ipB . ':7081' . $ruta, 'host' => $host],
            'apache_7080_local' => ['url' => 'http://127.0.0.1:7080' . $ruta, 'host' => $host],
            'publica'           => ['url' => $publica . '/catalog'],
            'apache_7081_sni'   => ['url' => 'https://' . $host . ':7081' . $ruta, 'resolve' => $host . ':7081:' . $ipB, 'host' => $host],
        ];
        $out = ['server_addr' => $ipS !== '' ? 'sí' : 'no', 'host' => $host];
        foreach ($casos as $nombre => $c) {
            $ch = curl_init($c['url']);
            $h = ['Accept: application/json', 'X-Forwarded-Proto: https', 'User-Agent: apiempresas-mcp-diag'];
            if (!empty($c['host'])) {
                $h[] = 'Host: ' . $c['host'];
            }
            $o = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 4,
                  CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => $h, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0];
            if (!empty($c['resolve'])) {
                $o[CURLOPT_RESOLVE] = [$c['resolve']];
            }
            curl_setopt_array($ch, $o);
            $t = microtime(true);
            $raw = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $err = $raw === false ? curl_error($ch) : '';
            curl_close($ch);
            $cuerpo = $raw === false ? '' : substr((string) $raw, $hs, 80);
            $loc = ($raw !== false && preg_match('/^Location:\s*(\S+)/mi', substr((string) $raw, 0, $hs), $m)) ? $m[1] : null;
            $out[$nombre] = [
                'ms' => (int) round((microtime(true) - $t) * 1000),
                'http' => $code,
                'llega_a_la_api' => $raw !== false && is_array(json_decode(substr((string) $raw, $hs), true)),
                'redirige_a' => $loc,
                'error' => $err,
                'inicio_respuesta' => $cuerpo,
            ];
        }
        // ¿Llega Authorization a PHP si se salta nginx? (prueba con un valor falso de 10 caracteres)
        $ch = curl_init('https://' . $host . ':7081/api/mcp?diag=cabeceras');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 4,
            CURLOPT_RESOLVE => [$host . ':7081:' . $ipB], CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => ['Host: ' . $host, 'Authorization: Bearer abcdefghij', 'Accept: application/json']]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $j = $raw !== false ? json_decode((string) $raw, true) : null;
        $out['authorization_sin_nginx'] = is_array($j) ? ($j['longitudes'] ?? $j) : ('sin respuesta: ' . substr((string) $raw, 0, 80));
        return $out;
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
            // Apache de Plesk directamente (puerto https interno, 7081), con el nombre del dominio en la
            // conexión (SNI). Diagnóstico del 16-10-2026: por el 443 de su IP se cuelga, el 7080 (http)
            // redirige a https y la dirección pública no conecta desde el propio servidor.
            $puerto = (int) (env('MCP_API_APACHE_PORT') ?: 7081);
            $ipB = str_contains($ipServidor, ':') ? '[' . $ipServidor . ']' : $ipServidor;
            $lista[] = ['nombre' => 'origen', 'base' => 'https://' . $host . ':' . $puerto . $path, 'resolve' => $host . ':' . $puerto . ':' . $ipB, 'host' => $host];
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
        if (!empty($d['host'])) {
            $headers[] = 'Host: ' . $d['host']; // sin el puerto: la API genera sus enlaces con el Host
        }
        // Conexión al propio servidor por su IP: el certificado puede ser el de origen de Cloudflare,
        // que no valida fuera de Cloudflare. La IP está fijada (CURLOPT_RESOLVE), no hay intermediarios.
        $ch = curl_init($d['base'] . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $esOrigen ? 8 : 12,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        if ($d['resolve'] !== null) {
            $opts[CURLOPT_RESOLVE] = [$d['resolve']];
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
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
            return ['ok' => false, 'status' => $status, 'body' => null, 'error' => 'HTTP ' . $status . ' sin JSON' . ($status >= 300 && $status < 400 ? ' (redirige)' : '')];
        }
        return ['ok' => true, 'status' => $status, 'body' => $json, 'error' => ''];
    }
}
