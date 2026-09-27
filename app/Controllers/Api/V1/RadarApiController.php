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
    #[OA\Parameter(
        name: "priority",
        in: "query",
        required: false,
        description: "Prioridad comercial calculada (valor de priority_level, por ejemplo alta, media o baja).",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "cnae",
        in: "query",
        required: false,
        description: "Uno o varios prefijos CNAE separados por comas (por ejemplo 62 o 4711,4719).",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "min_score",
        in: "query",
        required: false,
        description: "Puntuación mínima de oportunidad (0-100).",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Parameter(
        name: "main_act_type",
        in: "query",
        required: false,
        description: "Tipo del último acto relevante del BORME (por ejemplo Constitución o Ampliación de capital).",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "has_phone",
        in: "query",
        required: false,
        description: "Si es true, solo empresas con teléfono.",
        schema: new OA\Schema(type: "boolean")
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
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['access_slug'] ?? (\App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free');
        
        $filters = [
            'province' => $this->request->getGet('province'),
            'priority' => $this->request->getGet('priority'),
            'range'    => $this->request->getGet('range') ?? 'hoy',
            // Añadidos el 26-09-2026 (mismos filtros que el Radar de la web)
            'cnae'          => $this->request->getGet('cnae'),
            'min_score'     => $this->request->getGet('min_score'),
            'main_act_type' => $this->request->getGet('main_act_type'),
            'has_phone'     => $this->request->getGet('has_phone'),
        ];

        $radarData = $this->radarService->getRadarResults($filters, $planSlug);
        
        $totalCount = $radarData['total'];
        $results = $radarData['results'];
        $limit = $this->planAccess->getRadarLimit($planSlug);
        
        $meta = [
            // El plan contratado, como siempre. access_slug (Free con saldo = pro) solo
            // decide los límites; aquí devolvía "pro" a un Free con bono.
            'plan' => \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? $planSlug,
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
