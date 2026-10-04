<?php

namespace App\Controllers\Api\V1;

use App\Filters\ApiKeyFilter;
use App\Services\ApiCompanyEnricher;
use App\Services\ApiSegmentService as S;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;

/**
 * GET /api/v1/companies/filter (29-09-2026). Segmentos de empresas.
 * Recuento gratis en todos los planes; filas solo en Business, a 5 consultas por fila.
 */
class CompanyFilterController extends BaseApiController
{
    protected $format = 'json';

    #[OA\Get(
        path: "/api/v1/companies/filter",
        summary: "Segmentos de empresas",
        description: "Filtra empresas por CNAE, provincia, municipio, estado, fecha de constitución, teléfono, tramo de facturación y año de cuentas. Con count_only=true devuelve solo cuántas hay: **gratis en todos los planes**. Sin count_only devuelve las filas (plan Business): **5 consultas por fila devuelta**, hasta 1.000 por petición, paginando con cursor. No devuelve teléfonos, solo has_phone.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(name: "cnae", in: "query", required: false, description: "Prefijos CNAE separados por comas (ej: 62 o 4711,4719). Hace falta cnae, province o municipality.", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "province", in: "query", required: false, description: "Provincia del Registro Mercantil (ej: MADRID).", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "municipality", in: "query", required: false, description: "Municipio (ej: GETAFE).", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "status", in: "query", required: false, description: "active (por defecto: activas en el Registro), active_or_unknown (también las que no tienen estado) o any.", schema: new OA\Schema(type: "string", default: "active"))]
    #[OA\Parameter(name: "founded_from", in: "query", required: false, description: "Constituidas desde esta fecha (YYYY-MM-DD).", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Parameter(name: "founded_to", in: "query", required: false, description: "Constituidas hasta esta fecha (YYYY-MM-DD).", schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Parameter(name: "has_phone", in: "query", required: false, description: "true: solo empresas con teléfono (no se devuelve el número).", schema: new OA\Schema(type: "boolean"))]
    #[OA\Parameter(name: "size_band", in: "query", required: false, description: "Tramos de facturación separados por comas: NO_REVENUE, LT_500K, 500K_1M, GT_1M.", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "min_accounts_year", in: "query", required: false, description: "Último año de cuentas depositadas igual o posterior a este.", schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "count_only", in: "query", required: false, description: "true: solo el recuento (gratis, todos los planes).", schema: new OA\Schema(type: "boolean"))]
    #[OA\Parameter(name: "limit", in: "query", required: false, description: "Filas por página (1-1000). Por defecto, 100.", schema: new OA\Schema(type: "integer", default: 100))]
    #[OA\Parameter(name: "cursor", in: "query", required: false, description: "meta.next_cursor de la página anterior.", schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Recuento o filas")]
    #[OA\Response(response: 400, description: "Filtros no válidos")]
    #[OA\Response(response: 403, description: "Filas sin plan Business (incluye el recuento)")]
    public function index()
    {
        $meta = ApiKeyFilter::$apiMeta;
        $planSlug = strtolower((string) ($meta['plan_slug'] ?? 'free'));
        $planId = (int) ($meta['plan_id'] ?? 1);
        $userId = (int) ($meta['user_id'] ?? 0);

        [$f, $err] = S::parse($this->request->getGet() ?? []);
        if ($err !== null) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => $err], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $countOnly = filter_var($this->request->getGet('count_only'), FILTER_VALIDATE_BOOLEAN);

        try {
            $count = S::count($f);
        } catch (\Throwable $e) {
            log_message('error', '[CompanyFilterController::count] ' . $e->getMessage());
            return $this->respond(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'No se ha podido calcular el segmento. Prueba con filtros más concretos.'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($countOnly) {
            return $this->respond([
                'success' => true,
                'data'    => ['total' => $count['total']],
                'meta'    => ['filters' => $f, 'cost' => 0, 'counted_at' => $count['counted_at']],
            ]);
        }

        if (!in_array($planSlug, ['business', 'enterprise'], true)) {
            $n = number_format($count['total'], 0, ',', '.');
            return $this->respond([
                'success' => false,
                'error'   => 'PLAN_RESTRICTION',
                'message' => 'Descargar las empresas de un segmento requiere el plan Business. El recuento (count_only=true) es gratis en todos los planes.',
                'upsell_opportunities' => [
                    'mensaje'      => $n . ' empresas encajan con tu búsqueda. Con el plan Business puedes descargarlas por API.',
                    'total'        => $count['total'],
                    'cost_per_row' => S::ROW_COST,
                    'upgrade_url'  => \App\Filters\ApiKeyFilter::urlGancho('business', 'api_403_filter'),
                ],
            ], ResponseInterface::HTTP_FORBIDDEN);
        }

        $limit = (int) ($this->request->getGet('limit') ?? S::DEF_LIMIT);
        $limit = max(1, min(S::MAX_LIMIT, $limit ?: S::DEF_LIMIT));
        $cursorRaw = $this->request->getGet('cursor');
        $afterId = S::decodeCursor(is_string($cursorRaw) ? $cursorRaw : null);
        if (is_string($cursorRaw) && $cursorRaw !== '' && $afterId === null) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "cursor" no es válido. Usa el valor de meta.next_cursor.'], ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Lo que se puede pagar: cupo del mes y después monedero, a ROW_COST por fila.
        $esMonitor = $userId === ApiKeyFilter::MONITOR_USER_ID;
        $pay = $esMonitor
            ? ['rows' => $limit, 'sub_cost' => 0, 'wallet_cost' => 0]
            : S::affordable($limit, (int) ($meta['quota_remaining'] ?? 0), (int) ($meta['wallet_balance'] ?? 0));
        if ($pay['rows'] === 0) {
            return $this->response->setStatusCode(429)->setJSON([
                'success' => false,
                'error'   => 'Quota Exceeded',
                'message' => 'No te quedan consultas suficientes para descargar filas (' . S::ROW_COST . ' por fila). El recuento sigue siendo gratis.',
                'cost_per_row' => S::ROW_COST,
                'upgrade_url'  => site_url('billing'),
                // Mismo identificador que el 429 de cupo del resto de la API
                'code'   => 'QUOTA_EXCEEDED',
                'status' => 429,
            ] + ApiKeyFilter::enlacesCompra($planId, 'api_429_filter'));
        }
        $truncated = $pay['rows'] < $limit;

        try {
            [$rows, $more] = S::rows($f, $pay['rows'], $afterId);
        } catch (\Throwable $e) {
            log_message('error', '[CompanyFilterController::rows] ' . $e->getMessage());
            return $this->respond(['success' => false, 'error' => 'SERVER_ERROR', 'message' => 'No se han podido leer las empresas del segmento.'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $lastId = $rows ? (int) end($rows)['id'] : null;
        $rows = ApiCompanyEnricher::withoutOptedOut($rows);
        $rows = ApiCompanyEnricher::enrich($rows, true);
        $data = array_map([S::class, 'publicRow'], $rows);

        // Se cobra solo lo devuelto (las bajas por privacidad no cuentan).
        $cost = $esMonitor ? 0 : count($data) * S::ROW_COST;
        $sub = min($cost, $pay['sub_cost']);
        ApiKeyFilter::$apiSkipBilling = $esMonitor || $cost === 0;
        ApiKeyFilter::$apiMeta['sub_cost'] = $sub;
        ApiKeyFilter::$apiMeta['wallet_cost'] = $cost - $sub;

        return $this->respond([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'        => $count['total'],
                'returned'     => count($data),
                'limit'        => $limit,
                'has_more'     => $more,
                'next_cursor'  => ($more && $lastId) ? S::encodeCursor($lastId) : null,
                'cost'         => $cost,
                'cost_per_row' => S::ROW_COST,
                'truncated'    => $truncated,
                'filters'      => $f,
            ],
        ]);
    }
}
