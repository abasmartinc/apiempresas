<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Services\BotDetectionService;
use Config\Database;

class Search extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * Normaliza query y detecta tipo (cif vs name vs unknown)
     */
    private function normalizeQuery(string $q): array
    {
        $raw  = trim($q);
        $norm = strtoupper(preg_replace('/\s+/', ' ', $raw));

        // CIF típico: letra + 7 dígitos + control (letra o número)
        $isCif = (bool) preg_match('/^[A-Z]\d{7}[A-Z0-9]$/', $norm);

        return [
            'raw'  => $raw,
            'norm' => $norm,
            'type' => $isCif ? 'cif' : ($norm !== '' ? 'name' : 'unknown'),
        ];
    }

    /**
     * Contexto de request: user/session/ip/UA/etc.
     */
    private function getRequestContext(): array
    {
        $req = $this->request;

        $sid = session()->get('sid');
        if (!$sid) {
            $sid = session_id();
            if (!$sid) {
                $sid = bin2hex(random_bytes(16));
            }
            session()->set('sid', $sid);
        }

        $userId = session('user_id') ?: (session('logged_in') ? (session('id') ?? null) : null);

        return [
            'user_id'         => $userId ? (int) $userId : null,
            'session_id'      => $sid,
            'route'           => (string) ($req->getPath() ?? ''),
            'method'          => strtoupper($req->getMethod() ?? 'GET'),
            'ip'              => $req->getIPAddress(),
            'user_agent'      => (string) ($req->getUserAgent() ? $req->getUserAgent()->getAgentString() : ''),
            'referer'         => (string) $req->getServer('HTTP_REFERER'),
            'accept_language' => (string) $req->getServer('HTTP_ACCEPT_LANGUAGE'),
        ];
    }

    /**
     * Dedupe hash en ventana corta (absorbe doble click/reintentos)
     */
    private function buildEventHash(array $ctx, array $qInfo, string $resultStatus, int $httpStatus): string
    {
        $bucket = (int) floor(time() / 5);

        $base = implode('|', [
            (string)($ctx['session_id'] ?? ''),
            (string)($ctx['ip'] ?? ''),
            (string)($qInfo['norm'] ?? ''),
            (string)$resultStatus,
            (string)$httpStatus,
            (string)$bucket,
        ]);

        return hash('sha256', $base);
    }

    /**
     * Inserta en company_search_logs sin romper el flujo.
     */
    private function logSearch(array $payload): void
    {
        try {
            $db = Database::connect();
            $db->table('company_search_logs')->ignore(true)->insert($payload);
        } catch (\Throwable $e) {
            log_message('error', '[SEARCH] logSearch failed: ' . $e->getMessage());
        }
    }

    /**
     * JSON API (web):
     * GET /search?q=...
     * - Si q parece CIF => exact CIF
     * - Si no => fuzzy por nombre (1 resultado)
     * - Si parece CIF pero no hay => fallback a fuzzy
     */
    public function index()
    {
        // Rate limiting: 10 búsquedas por minuto por IP
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress()), 10, 60) === false) {
            // Block IP for rate limit violation
            $botService = new BotDetectionService();
            $botService->detectAndBlock($this->request, 'rate_limit_exceeded', [
                'endpoint' => '/search',
                'limit' => '10 requests per minute',
            ]);
            
            return $this->respond([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Tu IP ha sido bloqueada por actividad sospechosa.'
            ], 429);
        }

        // Solo permitir peticiones AJAX para este endpoint
        if (!$this->request->isAJAX()) {
            // Block IP for non-AJAX request to protected endpoint
            $botService = new BotDetectionService();
            $botService->detectAndBlock($this->request, 'non_ajax_request', [
                'endpoint' => '/search',
                'attempted_access' => 'direct_browser_access',
            ]);
            
            return $this->failForbidden('Acceso no permitido. Tu IP ha sido bloqueada.');
        }

        $q = trim((string) $this->request->getGet('q'));
        if ($q === '') {
            // retrocompatibilidad: si te siguen llamando con ?cif=
            $q = trim((string) $this->request->getGet('cif'));
        }

        $ctx   = $this->getRequestContext();
        $qInfo = $this->normalizeQuery($q);

        // 1) VALIDATION ERROR
        if ($q === '') {
            $http = ResponseInterface::HTTP_BAD_REQUEST;

            $this->logSearch([
                'created_at'      => date('Y-m-d H:i:s'),
                'user_id'         => $ctx['user_id'],
                'session_id'      => $ctx['session_id'],
                'channel'         => 'web',
                'route'           => $ctx['route'],
                'method'          => $ctx['method'],
                'query_raw'       => '',
                'query_norm'      => null,
                'query_type'      => 'unknown',
                'result_status'   => 'validation_error',
                'http_status'     => $http,
                'result_count'    => 0,
                'company_cif'     => null,
                'company_name'    => null,
                'ip'              => $ctx['ip'],
                'user_agent'      => $ctx['user_agent'],
                'referer'         => $ctx['referer'],
                'accept_language' => $ctx['accept_language'],
                'meta'            => json_encode(['error' => 'VALIDATION_ERROR'], JSON_UNESCAPED_UNICODE),
                'event_hash'      => $this->buildEventHash($ctx, $qInfo, 'validation_error', $http),
            ]);

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'VALIDATION_ERROR',
                    'message' => 'El parámetro "q" es obligatorio.'
                ],
                $http
            );
        }

        // 2) OK / NOT_FOUND / ERROR
        try {
            $company = null;

            // A) Si parece CIF => exacto
            if ($qInfo['type'] === 'cif') {
                $company = $this->companyModel->getByCif($qInfo['norm']);
            }

            // B) Si no es CIF, o si CIF no encontró => fuzzy nombre (1 resultado)
            if (!$company) {
                $best = $this->companyModel->getBestByName($qInfo['raw']);
                $company = is_array($best) && array_key_exists('data', $best) ? $best['data'] : $best;
            }

            // NOT FOUND (ni CIF ni fuzzy)
            if (!$company) {
                $http = ResponseInterface::HTTP_NOT_FOUND;

                $this->logSearch([
                    'created_at'      => date('Y-m-d H:i:s'),
                    'user_id'         => $ctx['user_id'],
                    'session_id'      => $ctx['session_id'],
                    'channel'         => 'web',
                    'route'           => $ctx['route'],
                    'method'          => $ctx['method'],
                    'query_raw'       => $qInfo['raw'],
                    'query_norm'      => $qInfo['norm'],
                    'query_type'      => $qInfo['type'],
                    'result_status'   => 'not_found',
                    'http_status'     => $http,
                    'result_count'    => 0,
                    'company_cif'     => null,
                    'company_name'    => null,
                    'ip'              => $ctx['ip'],
                    'user_agent'      => $ctx['user_agent'],
                    'referer'         => $ctx['referer'],
                    'accept_language' => $ctx['accept_language'],
                    'meta'            => json_encode(['error' => 'COMPANY_NOT_FOUND'], JSON_UNESCAPED_UNICODE),
                    'event_hash'      => $this->buildEventHash($ctx, $qInfo, 'not_found', $http),
                ]);

                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'COMPANY_NOT_FOUND',
                        'message' => 'No se encontró ninguna empresa con ese CIF o nombre.'
                    ],
                    $http
                );
            }

            // OK
            $http = ResponseInterface::HTTP_OK;

            $companyArr = is_object($company) ? (array) $company : (array) $company;

            $this->logSearch([
                'created_at'      => date('Y-m-d H:i:s'),
                'user_id'         => $ctx['user_id'],
                'session_id'      => $ctx['session_id'],
                'channel'         => 'web',
                'route'           => $ctx['route'],
                'method'          => $ctx['method'],
                'query_raw'       => $qInfo['raw'],
                'query_norm'      => $qInfo['norm'],
                'query_type'      => $qInfo['type'],
                'result_status'   => 'ok',
                'http_status'     => $http,
                'result_count'    => 1,
                'company_cif'     => (string)($companyArr['cif'] ?? null),
                'company_name'    => (string)($companyArr['name'] ?? null),
                'ip'              => $ctx['ip'],
                'user_agent'      => $ctx['user_agent'],
                'referer'         => $ctx['referer'],
                'accept_language' => $ctx['accept_language'],
                'meta'            => null,
                'event_hash'      => $this->buildEventHash($ctx, $qInfo, 'ok', $http),
            ]);

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $companyArr,
                ],
                $http
            );

        } catch (\Throwable $e) {
            $http = ResponseInterface::HTTP_INTERNAL_SERVER_ERROR;

            $this->logSearch([
                'created_at'      => date('Y-m-d H:i:s'),
                'user_id'         => $ctx['user_id'],
                'session_id'      => $ctx['session_id'],
                'channel'         => 'web',
                'route'           => $ctx['route'],
                'method'          => $ctx['method'],
                'query_raw'       => $qInfo['raw'],
                'query_norm'      => $qInfo['norm'],
                'query_type'      => $qInfo['type'],
                'result_status'   => 'error',
                'http_status'     => $http,
                'result_count'    => 0,
                'company_cif'     => null,
                'company_name'    => null,
                'ip'              => $ctx['ip'],
                'user_agent'      => $ctx['user_agent'],
                'referer'         => $ctx['referer'],
                'accept_language' => $ctx['accept_language'],
                'meta'            => json_encode(['error' => 'SERVER_ERROR'], JSON_UNESCAPED_UNICODE),
                'event_hash'      => $this->buildEventHash($ctx, $qInfo, 'error', $http),
            ]);

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al consultar la empresa.'
                ],
                $http
            );
        }
    }

    // --- HTML (web) ---
    public function search_company()
    {
        // Rate limiting: 30 visualizaciones de búsqueda por minuto por IP
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . '_view'), 30, 60) === false) {
             return "Demasiadas solicitudes. Por favor, espera un momento.";
        }

        $q = trim((string) $this->request->getGet('q'));

        $data = [
            'q'           => $q,
            'company'     => null,
            'errorMsg'    => null,
            'title'       => $q ? ("Buscar empresa: {$q} | APIEmpresas.es") : 'Buscar empresa | APIEmpresas.es',
            'excerptText' => 'Busca empresas por CIF o nombre comercial. Resultados trazables con fuentes oficiales y salida por API.',
            'canonical'   => site_url('search_company') . ($q ? ('?q=' . rawurlencode($q)) : ''),
            'robots'      => 'noindex,follow',
        ];

        if ($q !== '') {
            $qInfo = $this->normalizeQuery($q);

            try {
                $company = null;

                // Si parece CIF => exacto
                if ($qInfo['type'] === 'cif') {
                    $company = $this->companyModel->getByCif($qInfo['norm']);
                }

                // Si no es CIF, o si CIF no encontró => búsqueda amplia (searchMany)
                if (!$company) {
                    $results = $this->companyModel->searchMany($qInfo['raw'], 50);
                    
                    if (count($results) === 1) {
                        // Solo 1 resultado: comportamiento clásico (ficha directa)
                        $company = $results[0];
                    } elseif (count($results) > 1) {
                        // Múltiples resultados: pasamos la lista
                        $data['companies'] = $results;
                    }
                }

                if (!$company && empty($data['companies'])) {
                    $data['errorMsg'] = 'No se encontró ninguna empresa con ese CIF, nombre, CNAE o provincia.';
                } elseif ($company) {
                    $data['company'] = is_object($company) ? (array) $company : (array) $company;
                }

            } catch (\Throwable $e) {
                $data['errorMsg'] = 'Se ha producido un error interno al consultar la empresa.';
            }
        }

        return view('search', $data);
    }

}


