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
 * Cada herramienta ejecuta la API (/api/v1) con esa clave DENTRO de esta misma petición
 * (ejecutarDentro): pasa por ApiKeyFilter (clave, plan, límites y cobro) y SubscriptionFilter igual
 * que una llamada normal, y queda registrada en api_requests con su ruta /api/v1/... de siempre.
 * Antes (16-10-2026) se llamaba por HTTP al propio servidor, pero en producción esa llamada se
 * quedaba colgada. La vía HTTP solo se usa si existe MCP_API_BASE_URL en el .env.
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

        $base = trim((string) env('MCP_API_BASE_URL'));
        $server = new McpServer($key, function (string $method, string $path, ?array $body) use ($key, $ip, $base) {
            return $base !== ''
                ? self::llamarApiHttp(rtrim($base, '/'), $path, $method, $body, $key, $ip)
                : $this->ejecutarDentro($path, $method, $body, $key);
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

    /** Controlador de cada ruta de la API que usan las herramientas del MCP. */
    private const RUTAS = [
        'GET /companies'              => [\App\Controllers\Api\V1\CompaniesByCif::class, 'index'],
        'GET /companies/search'       => [\App\Controllers\Api\V1\CompaniesSearch::class, 'index'],
        'GET /companies/verify'       => [\App\Controllers\Api\V1\CompanyVerifyController::class, 'index'],
        'POST /companies/reconcile'   => [\App\Controllers\Api\V1\CompanyReconcileController::class, 'index'],
        'GET /companies/borme'        => [\App\Controllers\Api\V1\CompanyBormeController::class, 'index'],
        'GET /companies/risk-profile' => [\App\Controllers\Api\V1\CompanyRiskProfileController::class, 'index'],
        'GET /companies/contracts'    => [\App\Controllers\Api\V1\CompanyContractsController::class, 'index'],
        'GET /usage'                  => [\App\Controllers\Api\V1\UsageController::class, 'index'],
    ];

    /**
     * Ejecuta una llamada a /api/v1 dentro de esta petición, con los mismos pasos que el enrutador:
     * ApiKeyFilter::before → SubscriptionFilter::before('api') → controlador → ApiKeyFilter::after.
     * La petición es una copia de la del MCP con la ruta, el método, los parámetros y la clave de la
     * llamada; mientras dura, service('request') y service('response') apuntan a esa copia y a una
     * respuesta nueva (los filtros y helpers como uri_string() los usan), y después se restauran.
     *
     * @return array{status:int, body:mixed}
     */
    private function ejecutarDentro(string $pathConQuery, string $method, ?array $body, string $key): array
    {
        $method = strtoupper($method);
        $ruta = (string) parse_url($pathConQuery, PHP_URL_PATH);
        parse_str((string) parse_url($pathConQuery, PHP_URL_QUERY), $get);
        $destino = self::RUTAS[$method . ' ' . $ruta] ?? null;
        if ($destino === null) {
            return ['status' => 0, 'body' => ['detalle' => 'ruta no disponible en el MCP: ' . $method . ' ' . $ruta]];
        }

        $req = $this->peticionInterna('api/v1' . $ruta, $method, $get, $body, $key);
        $res = \Config\Services::response(null, false);
        $reqAntes = service('request');
        $resAntes = service('response');
        \Config\Services::injectMock('request', $req);
        \Config\Services::injectMock('response', $res);

        ApiKeyFilter::$apiMeta = [];
        ApiKeyFilter::$apiSkipBilling = false;

        try {
            $filtro = new ApiKeyFilter();
            $out = $filtro->before($req);
            if (!$out instanceof \CodeIgniter\HTTP\ResponseInterface) {
                $out = (new \App\Filters\SubscriptionFilter())->before($req, ['api']);
            }
            if (!$out instanceof \CodeIgniter\HTTP\ResponseInterface) {
                [$clase, $accion] = $destino;
                $ctrl = new $clase();
                $ctrl->initController($req, $res, \Config\Services::logger());
                $ret = $ctrl->{$accion}();
                if ($ret instanceof \CodeIgniter\HTTP\ResponseInterface) {
                    $out = $ret;
                } else {
                    $out = $res;
                    if (is_string($ret)) {
                        $out->setBody($ret);
                    }
                }
                $filtro->after($req, $out);
            }
            $status = $out->getStatusCode();
            $json = json_decode((string) $out->getBody(), true);
            return ['status' => $status, 'body' => is_array($json) ? $json : ['detalle' => 'respuesta sin JSON (HTTP ' . $status . ')']];
        } catch (\Throwable $e) {
            log_message('error', '[McpController::ejecutarDentro] ' . $method . ' ' . $ruta . ': ' . $e->getMessage() . ' @' . basename($e->getFile()) . ':' . $e->getLine());
            return ['status' => 0, 'body' => ['detalle' => 'error interno']];
        } finally {
            \Config\Services::injectMock('request', $reqAntes);
            \Config\Services::injectMock('response', $resAntes);
            ApiKeyFilter::$apiMeta = [];
            ApiKeyFilter::$apiSkipBilling = false;
        }
    }

    /** Copia de la petición del MCP con la ruta, el método, los parámetros y la clave de la llamada a la API. */
    private function peticionInterna(string $rutaRelativa, string $method, array $get, ?array $body, string $key)
    {
        $req = clone $this->request;
        $uri = clone $this->request->getUri();
        $uri->setPath($rutaRelativa);
        $uri->setQuery(http_build_query($get));
        \Closure::bind(static function ($r) use ($uri) { $r->uri = $uri; }, null, \CodeIgniter\HTTP\IncomingRequest::class)($req);
        if (method_exists($req, 'setPath')) {
            $req->setPath($rutaRelativa);
        }
        $req->setMethod($method);
        $req->setGlobal('get', $get);
        $req->setGlobal('post', []);
        $req->setGlobal('request', $get);
        // La clave va en X-API-KEY; se quita Authorization para que no haya dos claves distintas.
        $req->removeHeader('Authorization');
        $req->setHeader('X-API-KEY', $key);
        if ($method === 'POST') {
            $req->setHeader('Content-Type', 'application/json');
            $req->setBody(json_encode($body ?? new \stdClass(), JSON_UNESCAPED_UNICODE));
        } else {
            $req->setBody('');
        }
        return $req;
    }

    /**
     * Vía HTTP (solo con MCP_API_BASE_URL en el .env): llama a la API por la red, con la IP del
     * usuario firmada (ver ApiKeyFilter::ipCliente).
     *
     * @return array{status:int, body:mixed}
     */
    private static function llamarApiHttp(string $base, string $path, string $method, ?array $body, string $key, string $ip): array
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
        $ch = curl_init($base . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
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
        $err = $raw === false ? curl_error($ch) : '';
        curl_close($ch);
        $json = $raw === false ? null : json_decode((string) $raw, true);
        if (!is_array($json)) {
            log_message('error', '[McpController] sin respuesta de la API por HTTP: ' . ($err ?: 'HTTP ' . $status));
            return ['status' => 0, 'body' => ['detalle' => $err ?: 'HTTP ' . $status . ' sin JSON']];
        }
        return ['status' => $status, 'body' => $json];
    }
}
