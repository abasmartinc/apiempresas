<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Models\ApiRequestsModel;
use OpenApi\Attributes as OA;

class UsageController extends BaseApiController
{


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
    #[OA\Get(
        path: "/api/v1/usage",
        summary: "Obtener Estadísticas de Consumo",
        description: "Devuelve el recuento de peticiones del mes actual y el historial reciente de empresas consultadas asociado a la API Key.",
        tags: ["1. Plan Free"]
    )]
    #[OA\Response(
        response: 200,
        description: "Estadísticas y datos del historial",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "No autorizado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string")
            ]
        )
    )]
    public function index()
    {
        $userId = \App\Filters\ApiKeyFilter::$apiMeta['user_id'] ?? null;

        if (!$userId) {
            return $this->failUnauthorized('No se pudo identificar al usuario desde la API Key.');
        }

        try {
            $db = \Config\Database::connect('default');
            $currentMonth = date('Y-m');

            // 1. Get Plan Info and Limits
            $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
            $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
            $walletBalance = \App\Filters\ApiKeyFilter::$apiMeta['wallet_balance'] ?? 0;

            $planRow = $db->table('api_plans')->select('name, monthly_quota')->where('id', (int)$planId)->get()->getRow();
            $monthlyQuota = $planRow ? (int)$planRow->monthly_quota : get_free_plan_limit();
            $planName = $planRow ? $planRow->name : 'Free';

            // 2. Stats (Consumo facturado real desde api_usage_daily para total consistencia con ApiKeyFilter y la web)
            if ($db->tableExists('api_usage_daily')) {
                if ((int)$planId === 1) {
                    $usageRow = $db->table('api_usage_daily')
                        ->selectSum('requests_count', 'total')
                        ->where('user_id', $userId)
                        ->where('date >=', '2026-05-28')
                        ->get()->getRow();
                } else {
                    $usageRow = $db->table('api_usage_daily')
                        ->selectSum('requests_count', 'total')
                        ->where('user_id', $userId)
                        ->where('plan_id', (int)$planId)
                        ->like('date', $currentMonth, 'after')
                        ->get()->getRow();
                }
                $monthlyCount = $usageRow ? (int)$usageRow->total : 0;

                $totalRow = $db->table('api_usage_daily')
                    ->selectSum('requests_count', 'total')
                    ->where('user_id', $userId)
                    ->get()->getRow();
                $totalCount = $totalRow ? (int)$totalRow->total : 0;
            } else {
                $monthlyCount = $this->apiRequestsModel->countRequestsForMonth($currentMonth, [
                    'user_id'     => $userId,
                    'status_code' => 200,
                ]);
                $totalCount = $this->apiRequestsModel
                    ->where('user_id', $userId)
                    ->where('status_code', 200)
                    ->countAllResults();
            }

            $remainingCalls = max(0, $monthlyQuota - $monthlyCount);

            // 3. History (Recent Queried Companies with query counts)
            $recentRequests = $this->apiRequestsModel
                ->select('search_term, COUNT(*) as query_count, MAX(created_at) as last_query')
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

            $history = [];
            foreach ($recentRequests as $reqData) {
                $cif        = $reqData['search_term'];
                $queryCount = (int)($reqData['query_count'] ?? 1);
                $lastQuery  = $reqData['last_query'] ?? null;

                $details = $this->companyModel->getByCif($cif);
                if ($details) {
                    if ((int)$planId === 1) {
                        $details = mask_company_data($details);
                    }
                    $details = filter_company_data($details);
                    $details['query_count'] = $queryCount;
                    $details['last_query']  = $lastQuery;
                    $details['found']       = true;
                    $history[] = $details;
                } else {
                    $history[] = [
                        'cif'         => $cif,
                        'name'        => 'Empresa no encontrada',
                        'province'    => '-',
                        'cnae_label'  => '-',
                        'query_count' => $queryCount,
                        'last_query'  => $lastQuery,
                        'found'       => false
                    ];
                }
            }

            return $this->respond([
                'success' => true,
                'data'    => [
                    'stats' => [
                        'monthly_queries' => $monthlyCount,
                        'total_queries'   => $totalCount,
                        'monthly_quota'   => $monthlyQuota,
                        'remaining_calls' => $remainingCalls,
                        'wallet_balance'  => $walletBalance,
                        'plan_name'       => $planName,
                        'plan_slug'       => $planSlug,
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
