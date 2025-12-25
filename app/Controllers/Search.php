<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
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

        // session_id estable (si no hay, lo generamos y lo persistimos)
        $sid = session()->get('sid');
        if (!$sid) {
            $sid = session_id();
            if (!$sid) {
                $sid = bin2hex(random_bytes(16));
            }
            session()->set('sid', $sid);
        }

        // user_id (ajusta según tu auth real)
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
     * Cambia 5s si quieres más/menos.
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
     * Requiere índice UNIQUE(event_hash) o similar si quieres dedupe real.
     */
    private function logSearch(array $payload): void
    {
        try {
            $db = Database::connect();
            $builder = $db->table('company_search_logs');

            // Si tu tabla tiene UNIQUE(event_hash), esto evita duplicados:
            // CI4 soporta ignore(true) para generar INSERT IGNORE en MySQL.
            $builder->ignore(true)->insert($payload);
        } catch (\Throwable $e) {
            log_message('error', '[SEARCH] logSearch failed: ' . $e->getMessage());
        }
    }

    /**
     * JSON API:
     * GET /search?cif=B12345678
     */
    public function index()
    {
        $cif = trim((string) $this->request->getGet('cif'));

        $ctx   = $this->getRequestContext();
        $qInfo = $this->normalizeQuery($cif);

        // 1) VALIDATION ERROR
        if ($cif === '') {
            $http = ResponseInterface::HTTP_BAD_REQUEST;

            $this->logSearch([
                'created_at'      => date('Y-m-d H:i:s'),
                'user_id'         => $ctx['user_id'],
                'session_id'      => $ctx['session_id'],
                'channel'         => 'web', // si luego quieres distinguir api/web, lo refinamos
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
                    'message' => 'El parámetro "cif" es obligatorio.'
                ],
                $http
            );
        }

        // 2) OK / NOT_FOUND / ERROR
        try {
            $company = $this->companyModel->getCompanyByCif($cif);

            // NOT FOUND
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
                        'message' => 'No se encontró ninguna empresa con ese CIF.'
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
                'company_cif'     => (string)($companyArr['cif'] ?? $companyArr['nif'] ?? $qInfo['norm']),
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
                    'data'    => $company,
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

    // --- Tus métodos HTML los dejo igual ---
    public function search_company()
    {
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
            try {
                $company = $this->companyModel->getCompanyByCif($q);
                if (is_object($company)) $company = (array) $company;

                if (!$company) $data['errorMsg'] = 'No se encontró ninguna empresa con ese CIF.';
                else $data['company'] = $company;

            } catch (\Throwable $e) {
                $data['errorMsg'] = 'Se ha producido un error interno al consultar la empresa.';
            }
        }

        return view('search', $data);
    }

    public function search_company_post()
    {
        $q = trim((string) $this->request->getPost('q'));
        $url = site_url('search_company') . ($q ? ('?q=' . rawurlencode($q)) : '');
        return redirect()->to($url)->setStatusCode(303);
    }
}
