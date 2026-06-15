<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Services\PlanAccessService;
use OpenApi\Attributes as OA;

class CompaniesBatch extends BaseApiController
{


    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    /** @var PlanAccessService */
    protected $planAccess;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->planAccess = new PlanAccessService();
        helper(['api', 'company']);
    }

    #[OA\Post(
        path: "/api/v1/companies/batch",
        summary: "Consultar múltiples empresas por CIF",
        description: "Devuelve los datos de múltiples empresas enviando un array de CIFs (máximo 100 por petición). El coste es de 1 consulta por cada empresa encontrada. Si no tienes saldo suficiente, la respuesta se recortará hasta el número de empresas que puedas pagar.",
        tags: ["2. Plan Pro", "3. Plan Business"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "cifs", type: "array", items: new OA\Items(type: "string"), description: "Array de CIFs a consultar (Max 100)"),
                new OA\Property(property: "admin", type: "boolean", description: "Si es 'true', incluye los administradores (Exclusivo Pro/Business)")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Resultados encontrados",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                new OA\Property(property: "meta", type: "object", properties: [
                    new OA\Property(property: "requested", type: "integer"),
                    new OA\Property(property: "found", type: "integer"),
                    new OA\Property(property: "cost", type: "integer"),
                    new OA\Property(property: "truncated", type: "boolean")
                ])
            ]
        )
    )]
    public function index()
    {
        // 1. Validate Access
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        $planId = (int)(\App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1);
        $userId = (int)(\App\Filters\ApiKeyFilter::$apiMeta['user_id'] ?? 0);
        $walletBalance = (int)(\App\Filters\ApiKeyFilter::$apiMeta['wallet_balance'] ?? 0);

        if (!in_array($planSlug, ['pro', 'business', 'enterprise'])) {
            return $this->failForbidden('El endpoint de batch requiere un plan Pro o Business.');
        }

        // 2. Parse JSON
        $json = $this->request->getJSON(true);
        $cifs = $json['cifs'] ?? [];
        $includeAdmins = filter_var($json['admin'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!is_array($cifs)) {
            return $this->failValidationErrors('El parámetro "cifs" debe ser un array.');
        }

        if (empty($cifs)) {
            return $this->failValidationErrors('El array "cifs" no puede estar vacío.');
        }

        if (count($cifs) > 100) {
            return $this->failValidationErrors('El número máximo de CIFs por petición es 100.');
        }

        // Clean CIFs
        $cleanCifs = [];
        $fakeCifs = ['B99999999', 'B12345678', 'B12345674', 'A12345678', 'B00000000'];
        
        foreach ($cifs as $cif) {
            $c = strtoupper(trim((string)$cif));
            $c = preg_replace('/^ES/', '', $c);
            $c = preg_replace('/[^A-Z0-9]/', '', $c);
            
            if (in_array($c, $fakeCifs)) continue;
            if (preg_match('/^[0-9]{8}[A-Z]$/', $c) || preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $c)) continue;
            if (!is_valid_cif($c)) continue;

            $cleanCifs[] = $c;
        }

        $cleanCifs = array_unique($cleanCifs);

        if (empty($cleanCifs)) {
            return $this->respond([
                'success' => true,
                'data' => [],
                'meta' => [
                    'requested' => count($cifs),
                    'found' => 0,
                    'cost' => 0,
                    'truncated' => false
                ]
            ]);
        }

        // 3. Query Database
        $companies = $this->companyModel->getByCifs($cleanCifs);
        $foundCount = count($companies);

        if ($foundCount === 0) {
            return $this->respond([
                'success' => true,
                'data' => [],
                'meta' => [
                    'requested' => count($cifs),
                    'found' => 0,
                    'cost' => 0,
                    'truncated' => false
                ]
            ]);
        }

        // 4. Billing Logic
        $db = \Config\Database::connect();
        
        $planRow = $db->table('api_plans')->select('monthly_quota')->where('id', $planId)->get()->getRow();
        $monthlyQuota = $planRow ? (int)$planRow->monthly_quota : 0;

        $currentMonth = date('Y-m');
        $cacheKey = "api_usage_{$userId}_{$currentMonth}";
        $currentUsage = cache()->get($cacheKey);

        if ($currentUsage === null) {
            $usageRow = $db->table('api_usage_daily')
                ->selectSum('requests_count')
                ->where('user_id', $userId)
                ->where('plan_id', $planId)
                ->like('date', $currentMonth, 'after')
                ->get()->getRow();
            $currentUsage = $usageRow ? (int)$usageRow->requests_count : 0;
            cache()->save($cacheKey, $currentUsage, 30);
        }

        $monthlyRemaining = max(0, $monthlyQuota - $currentUsage);
        $totalAvailable = $monthlyRemaining + $walletBalance;

        $truncated = false;
        $allowedCount = $foundCount;

        if ($totalAvailable < $foundCount) {
            $allowedCount = $totalAvailable;
            $truncated = true;
            $companies = array_slice($companies, 0, $allowedCount);
        }

        if ($allowedCount === 0) {
            return $this->response->setStatusCode(429)->setJSON([
                'success' => false,
                'error'   => 'Quota Exceeded',
                'message' => 'Has superado el límite de consultas y no tienes saldo en el monedero.',
                'upgrade_url' => site_url('billing')
            ]);
        }

        $subCost = 0;
        $walletCost = 0;

        if ($monthlyRemaining >= $allowedCount) {
            $subCost = $allowedCount;
        } else {
            $subCost = $monthlyRemaining;
            $walletCost = $allowedCount - $monthlyRemaining;
        }

        // 5. Final Formatting (Filtering & Admins)
        $companyIds = array_column($companies, 'id');
        $administratorsMap = [];

        if ($includeAdmins && !empty($companyIds)) {
            $admins = $db->table('company_administrators')
                ->select('company_id, name, position')
                ->whereIn('company_id', $companyIds)
                ->get()->getResultArray();
            
            foreach ($admins as $ad) {
                $administratorsMap[$ad['company_id']][] = [
                    'name' => $ad['name'],
                    'position' => $ad['position']
                ];
            }
        }

        foreach ($companies as &$company) {
            $cid = $company['id'];
            $company = filter_company_data($company);
            if ($includeAdmins && isset($administratorsMap[$cid])) {
                $company['administrators'] = $administratorsMap[$cid];
            }
        }

        // 6. Update ApiKeyFilter Meta
        \App\Filters\ApiKeyFilter::$apiSkipBilling = false;
        \App\Filters\ApiKeyFilter::$apiMeta['sub_cost'] = $subCost;
        \App\Filters\ApiKeyFilter::$apiMeta['wallet_cost'] = $walletCost;

        return $this->respond([
            'success' => true,
            'data' => array_values($companies),
            'meta' => [
                'requested' => count($cifs),
                'found' => $foundCount,
                'cost' => $allowedCount,
                'truncated' => $truncated
            ]
        ]);
    }
}
