<?php

namespace App\Controllers\Api\V1;

use App\Services\ApiWatchlistService as W;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;

/**
 * Vigilancia de empresas por API (27-09-2026, piloto). Ver App\Services\ApiWatchlistService.
 * Pro: 100 empresas. Business: 1.000. Vigilar y consultar eventos no gasta cupo.
 */
class WatchlistController extends BaseApiController
{
    protected $format = 'json';

    public function __construct()
    {
        helper(['api', 'company']);
    }

    /** Plan, límite y usuario; o la respuesta de error si no hay acceso. */
    private function contexto(): array
    {
        $userId = (int) (\App\Filters\ApiKeyFilter::$apiMeta['user_id'] ?? 0);
        $plan   = (string) (\App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free');
        $limit  = W::limitFor($plan);

        if ($limit <= 0) {
            return [null, $this->respond([
                'success' => false,
                'error'   => 'PLAN_RESTRICTION',
                'message' => 'La vigilancia de empresas requiere un plan Pro (100 empresas) o Business (1.000 empresas).',
                'upsell_opportunities' => [
                    'mensaje'     => 'Vigila empresas y consulta sus cambios en el BORME, en su estado y en su nivel de riesgo sin gastar consultas.',
                    'upgrade_url' => \App\Filters\ApiKeyFilter::urlGancho('pro', 'api_403_watchlist'),
                ],
            ], ResponseInterface::HTTP_FORBIDDEN)];
        }

        if (!W::tableReady()) {
            log_message('critical', '[WatchlistController] Falta la tabla api_watchlist (scripts/sql/2026-09-27_api_watchlist.sql).');
            return [null, $this->respond([
                'success' => false,
                'error'   => 'SERVICE_UNAVAILABLE',
                'message' => 'La vigilancia no está disponible en este momento.',
            ], ResponseInterface::HTTP_SERVICE_UNAVAILABLE)];
        }

        return [['user_id' => $userId, 'plan' => $plan, 'limit' => $limit], null];
    }

    private function pagina(): array
    {
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit = (int) ($this->request->getGet('limit') ?? 100);
        $limit = $limit <= 0 ? 100 : min($limit, W::MAX_POR_PAGINA);
        return [$page, $limit];
    }

    #[OA\Get(
        path: "/api/v1/watchlist",
        summary: "Empresas vigiladas",
        description: "Lista las empresas de tu vigilancia. **Coste:** 0. Pro: hasta 100 empresas; Business: hasta 1.000.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\Parameter(name: "page", in: "query", required: false, description: "Página (>= 1).", schema: new OA\Schema(type: "integer", default: 1))]
    #[OA\Parameter(name: "limit", in: "query", required: false, description: "Resultados por página (1-500).", schema: new OA\Schema(type: "integer", default: 100))]
    #[OA\Response(response: 200, description: "Lista de empresas vigiladas")]
    public function index()
    {
        [$ctx, $err] = $this->contexto();
        if ($err) return $err;
        [$page, $limit] = $this->pagina();

        $res = W::listFor($ctx['user_id'], $page, $limit);
        return $this->respond([
            'success' => true,
            'data'    => $res['items'],
            'meta'    => [
                'total'       => $res['total'],
                'watch_limit' => $ctx['limit'],
                'page'        => $page,
                'limit'       => $limit,
                'has_more'    => $page * $limit < $res['total'],
            ],
        ]);
    }

    #[OA\Post(
        path: "/api/v1/watchlist",
        summary: "Añadir empresas a la vigilancia",
        description: "Añade uno o varios CIF (máximo 500 por petición) enviando `{\"cifs\": [\"A46103834\", ...]}`. Devuelve qué pasó con cada uno. **Coste:** 0.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\Response(response: 200, description: "Resultado del alta por CIF")]
    public function create()
    {
        [$ctx, $err] = $this->contexto();
        if ($err) return $err;

        $json = $this->request->getJSON(true);
        $cifs = is_array($json) ? ($json['cifs'] ?? null) : null;
        if (is_string($cifs)) {
            $cifs = [$cifs];
        }
        if (!is_array($cifs) || empty($cifs)) {
            return $this->respond([
                'success' => false,
                'error'   => 'VALIDATION_ERROR',
                'message' => 'Envía un JSON con el array "cifs", por ejemplo {"cifs": ["A46103834"]}.',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }
        if (count($cifs) > W::MAX_ALTA_POR_PETICION) {
            return $this->respond([
                'success' => false,
                'error'   => 'VALIDATION_ERROR',
                'message' => 'Máximo ' . W::MAX_ALTA_POR_PETICION . ' CIF por petición.',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $res = W::add($ctx['user_id'], array_values($cifs), $ctx['limit']);
        $total = W::count($ctx['user_id']);

        $body = [
            'success' => true,
            'data'    => $res,
            'meta'    => ['total' => $total, 'watch_limit' => $ctx['limit']],
        ];
        if (!empty($res['rejected_over_limit'])) {
            $siguiente = strtolower($ctx['plan']) === 'pro';
            $body['message'] = 'Has llegado al límite de ' . $ctx['limit'] . ' empresas vigiladas de tu plan.';
            if ($siguiente) {
                $body['upgrade_url'] = \App\Filters\ApiKeyFilter::urlGancho('business', 'api_watchlist_limit');
            }
        }
        return $this->respond($body);
    }

    #[OA\Delete(
        path: "/api/v1/watchlist/{cif}",
        summary: "Quitar una empresa de la vigilancia",
        description: "**Coste:** 0.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\Parameter(name: "cif", in: "path", required: true, description: "CIF de la empresa", schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Quitada")]
    #[OA\Response(response: 404, description: "No estaba en tu vigilancia")]
    public function delete($cif = null)
    {
        [$ctx, $err] = $this->contexto();
        if ($err) return $err;

        if (!W::remove($ctx['user_id'], (string) $cif)) {
            return $this->respond([
                'success' => false,
                'error'   => 'NOT_WATCHING',
                'message' => 'Esa empresa no está en tu vigilancia.',
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->respond([
            'success' => true,
            'data'    => ['cif' => \App\Services\ApiCompanyEnricher::normalizeCif((string) $cif), 'removed' => true],
            'meta'    => ['total' => W::count($ctx['user_id']), 'watch_limit' => $ctx['limit']],
        ]);
    }

    #[OA\Get(
        path: "/api/v1/watchlist/events",
        summary: "Cambios en las empresas vigiladas",
        description: "Devuelve los cambios desde una fecha, ordenados de más antiguo a más reciente: `borme_act` (acto publicado en el BORME), `status_change` (estado del Registro) y `risk_level_change` (nivel de riesgo; `model_change: true` si coincide con un recálculo del modelo). **Coste:** 0. Recomendado: consultar una vez al día con `since` = la fecha de tu última consulta.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\Parameter(name: "since", in: "query", required: false, description: "Fecha YYYY-MM-DD (incluida). Por defecto, hace 7 días. Máximo 90 días atrás.", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Parameter(name: "types", in: "query", required: false, description: "Tipos separados por comas: borme_act, status_change, risk_level_change. Por defecto, todos.", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "cif", in: "query", required: false, description: "Solo los eventos de esta empresa (debe estar en tu vigilancia).", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "page", in: "query", required: false, description: "Página (>= 1).", schema: new OA\Schema(type: "integer", default: 1))]
    #[OA\Parameter(name: "limit", in: "query", required: false, description: "Eventos por página (1-500).", schema: new OA\Schema(type: "integer", default: 100))]
    #[OA\Response(response: 200, description: "Eventos")]
    public function events()
    {
        [$ctx, $err] = $this->contexto();
        if ($err) return $err;
        [$page, $limit] = $this->pagina();

        $since = W::parseSince($this->request->getGet('since'));
        if ($since === null) {
            return $this->respond([
                'success' => false,
                'error'   => 'VALIDATION_ERROR',
                'message' => 'El parámetro "since" debe ser una fecha YYYY-MM-DD.',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $tiposRaw = trim((string) ($this->request->getGet('types') ?? ''));
        $tipos = $tiposRaw === '' ? W::TIPOS : array_values(array_intersect(W::TIPOS, array_map('trim', explode(',', $tiposRaw))));
        if (empty($tipos)) {
            return $this->respond([
                'success' => false,
                'error'   => 'VALIDATION_ERROR',
                'message' => 'Tipos válidos en "types": ' . implode(', ', W::TIPOS) . '.',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $cif = $this->request->getGet('cif');
        $res = W::events($ctx['user_id'], $since, $tipos, $page, $limit, is_string($cif) && $cif !== '' ? $cif : null);

        return $this->respond([
            'success' => true,
            'data'    => $res['events'],
            'meta'    => [
                'since'    => $since,
                'types'    => $tipos,
                'total'    => $res['total'],
                'page'     => $page,
                'limit'    => $limit,
                'has_more' => $page * $limit < $res['total'],
            ],
        ]);
    }
}
