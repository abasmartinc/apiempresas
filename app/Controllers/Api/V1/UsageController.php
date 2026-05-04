<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Models\ApiRequestsModel;

class UsageController extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;
    /** @var ApiRequestsModel */
    protected $apiRequestsModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->apiRequestsModel = new ApiRequestsModel();
        helper('api');
    }

    /**
     * Get usage statistics and history for the API key holder
     * GET /api/v1/usage
     */
    public function index()
    {
        $userId = $this->request->api_meta['user_id'] ?? null;

        if (!$userId) {
            return $this->failUnauthorized('No se pudo identificar al usuario desde la API Key.');
        }

        try {
            $currentMonth = date('Y-m');

            // 1. Stats
            $monthlyCount = $this->apiRequestsModel->countRequestsForMonth($currentMonth, ['user_id' => $userId]);
            $totalCount = $this->apiRequestsModel->where('user_id', $userId)->countAllResults();

            // 2. History (Recent Queried Companies)
            $recentRequests = $this->apiRequestsModel
                ->select('search_term, MAX(created_at) as last_query')
                ->where('user_id', $userId)
                ->where('search_term IS NOT NULL')
                ->where('search_term !=', '')
                ->groupStart()
                    ->like('endpoint', 'companies', 'both')
                    ->orLike('endpoint', 'professional', 'both')
                ->groupEnd()
                ->groupBy('search_term')
                ->orderBy('last_query', 'DESC')
                ->limit(20)
                ->findAll();

            $cifs = array_column($recentRequests, 'search_term');

            $history = [];
            foreach ($cifs as $cif) {
                // We use getByCif but we could also use a lighter search if needed
                $details = $this->companyModel->getByCif($cif);
                if ($details) {
                    $planId = $this->request->api_meta['plan_id'] ?? 1;
                    if ((int)$planId === 1) {
                        $details = mask_company_data($details);
                    }
                    $details = filter_company_data($details);
                    $history[] = $details;
                }
            }

            return $this->respond([
                'success' => true,
                'data'    => [
                    'stats' => [
                        'monthly_queries' => $monthlyCount,
                        'total_queries'   => $totalCount
                    ],
                    'history' => $history
                ]
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[UsageController::index] ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'error'   => 'SERVER_ERROR',
                'message' => 'Error al obtener estadísticas y historial.'
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
