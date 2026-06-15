<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\CompanyRadarService;
use OpenApi\Attributes as OA;

class RadarApiController extends BaseApiController
{
    protected PlanAccessService  $planAccess;
    protected CompanyRadarService $radarService;

    public function __construct()
    {
        $this->planAccess   = new PlanAccessService();
        $this->radarService = new CompanyRadarService();
    }

    /**
     * GET /api/v1/companies/radar
     * Filtros: province, priority, range (hoy, 7, 30)
     */
    #[OA\Get(
        path: "/api/v1/companies/radar",
        summary: "Búsqueda Radar (Leads)",
        description: "Obtener empresas de reciente creación según la provincia y prioridad. Los resultados están limitados por tu plan. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 3 créditos del monedero (bono prepago). Las respuestas con error no consumen cuota ni créditos.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "province",
        in: "query",
        required: false,
        description: "Filtro por provincia",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "range",
        in: "query",
        required: false,
        description: "Rango de tiempo: 'hoy', '7' días o '30' días",
        schema: new OA\Schema(type: "string", default: "hoy")
    )]
    #[OA\Response(
        response: 200,
        description: "Leads encontrados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "meta", type: "object"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
            ]
        )
    )]
    public function index()
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        
        $filters = [
            'province' => $this->request->getGet('province'),
            'priority' => $this->request->getGet('priority'),
            'range'    => $this->request->getGet('range') ?? 'hoy',
        ];

        $radarData = $this->radarService->getRadarResults($filters, $planSlug);
        
        $totalCount = $radarData['total'];
        $results = $radarData['results'];
        $limit = $this->planAccess->getRadarLimit($planSlug);
        
        $meta = [
            'plan' => $planSlug,
            'count' => count($results),
            'limit' => $limit,
            'total_disponibles' => $totalCount,
        ];

        $ocultos = $totalCount - count($results);
        if ($ocultos > 0) {
            $meta['oportunidades_ocultas'] = $ocultos;
            $meta['upsell'] = "🔒 Tienes {$ocultos} empresas nuevas esperándote hoy. Sube a Business para verlas todas y descargar listados completos.";
        }

        return $this->respond([
            'success' => true,
            'meta' => $meta,
            'data' => $results
        ]);
    }
}
