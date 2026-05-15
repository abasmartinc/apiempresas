<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\CompanyRadarService;
use OpenApi\Attributes as OA;

class RadarApiController extends ResourceController
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
        description: "Obtener empresas de reciente creación según la provincia y prioridad. Los resultados están limitados por tu plan.",
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

        $results = $this->radarService->getRadarResults($filters, $planSlug);

        return $this->respond([
            'success' => true,
            'meta' => [
                'plan' => $planSlug,
                'count' => count($results),
                'limit' => $this->planAccess->getRadarLimit($planSlug)
            ],
            'data' => $results
        ]);
    }
}
