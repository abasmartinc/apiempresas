<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\CompanyRadarService;

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
    public function index()
    {
        $planSlug = $this->request->api_meta['plan_slug'] ?? 'free';
        
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
