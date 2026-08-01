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
            // Get Plan Info and Limits
            $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
            $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
            $walletBalance = \App\Filters\ApiKeyFilter::$apiMeta['wallet_balance'] ?? 0;
            
            $db = \Config\Database::connect('default');
            $planRow = $db->table('api_plans')->select('name, monthly_quota')->where('id', (int)$planId)->get()->getRow();
            
            $monthlyQuota = $planRow ? (int)$planRow->monthly_quota : get_free_plan_limit();
            $planName = $planRow ? $planRow->name : 'Free';

            $currentMonth = date('Y-m');

            // Define what a "billable" request is
            $billableWhere = "user_id = {$db->escape($userId)} AND search_term IS NOT NULL AND search_term != '' AND (endpoint LIKE '%companies%' OR endpoint LIKE '%professional%')";

            // 1. Stats
            if ($planSlug === 'free') {
                // For Free plan, quota is lifetime
                $consumedCount = $this->apiRequestsModel->where($billableWhere, null, false)->countAllResults();
            } else {
                // For Paid plans, quota is monthly
                $consumedCount = $this->apiRequestsModel
                    ->where($billableWhere, null, false)
                    ->where('created_at >=', $currentMonth . '-01 00:00:00')
                    ->where('created_at <', date('Y-m-d H:i:s', strtotime($currentMonth . '-01 00:00:00 +1 month')))
                    ->countAllResults();
            }
            
            $totalCount = $this->apiRequestsModel->where($billableWhere, null, false)->countAllResults();

            // 2. History
            $historyQuery = $this->apiRequestsModel
                ->select('search_term, created_at as last_query')
                ->where($billableWhere, null, false);

            if ($planSlug !== 'free') {
                $historyQuery = $historyQuery
                    ->where('created_at >=', $currentMonth . '-01 00:00:00')
                    ->where('created_at <', date('Y-m-d H:i:s', strtotime($currentMonth . '-01 00:00:00 +1 month')));
            }

            // We don't groupBy search_term so that every individual request is shown, ensuring the count matches exactly
            $recentRequests = $historyQuery
                ->orderBy('created_at', 'DESC')
                ->limit(100) // Same as max free limit, so it matches perfectly
                ->findAll();

            $history = [];
            $memo = [];
            foreach ($recentRequests as $req) {
                $cif = $req['search_term'];
                if (isset($memo[$cif])) {
                    $history[] = $memo[$cif];
                    continue;
                }
                
                $details = $this->companyModel->getByCif($cif);
                if ($details) {
                    if ((int)$planId === 1) {
                        $details = mask_company_data($details);
                    }
                    $details = filter_company_data($details);
                    $details['found'] = true;
                    $memo[$cif] = $details;
                    $history[] = $details;
                } else {
                    $notFound = [
                        'cif'        => $cif,
                        'name'       => 'Empresa no encontrada',
                        'province'   => '-',
                        'cnae_label' => '-',
                        'found'      => false
                    ];
                    $memo[$cif] = $notFound;
                    $history[] = $notFound;
                }
            }

            $remainingCalls = max(0, $monthlyQuota - $consumedCount);

            return $this->respond([
                'success' => true,
                'data'    => [
                    'stats' => [
                        // Retenemos el nombre 'monthly_queries' para no romper la compatibilidad con apps móviles/plugins antiguos
                        'monthly_queries' => $consumedCount,
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
