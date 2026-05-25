<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CompanyModel;
use App\Libraries\RadarAnalyzer;
use App\Services\PlanAccessService;
use App\Services\ApiRequestLogger;
use OpenApi\Attributes as OA;

class CompanyMatchController extends ResourceController
{
    protected $format = 'json';

    #[OA\Get(
        path: "/api/v1/companies/match",
        summary: "Calculadora de Match B2B",
        description: "Evalúa el encaje comercial entre una empresa prospecto y un sector de ventas.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "CIF de la empresa prospecto",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "seller_sector",
        in: "query",
        required: true,
        description: "Sector del vendedor (ej: Software, Marketing, Asesoría)",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Resultado del match score y argumentario de ventas",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function index()
    {
        $cif = $this->request->getGet('cif');
        $sellerSector = $this->request->getGet('seller_sector');
        
        if (!$cif || !$sellerSector) {
            return $this->fail('Parámetros cif y seller_sector requeridos', 400);
        }

        // Verify API Key and Permissions
        $apiKey = $this->request->getHeaderLine('X-API-KEY');
        if (empty($apiKey)) {
            return $this->failUnauthorized('API Key no proporcionada');
        }

        $accessService = new PlanAccessService();
        $validation = $accessService->validateAccess($apiKey, 'business_only'); 
        
        // Let's assume Business plan is required for this premium feature
        if (!$validation['success']) {
            if (isset($validation['user_id'])) {
                ApiRequestLogger::log($validation['user_id'], 'match', 'error', $validation['message']);
            }
            return $this->failUnauthorized($validation['message']);
        }

        $userId = $validation['user_id'];
        $model = new CompanyModel();
        
        // Check if company exists
        $company = $model->getByCif($cif);
        if (!$company) {
            ApiRequestLogger::log($userId, 'match', 'error', 'Empresa no encontrada: ' . $cif);
            return $this->failNotFound('Empresa no encontrada');
        }
        
        // Merge with scores if exist
        $db = \Config\Database::connect();
        $scoring = $db->table('company_radar_scores')
                      ->where('company_id', $company['id'])
                      ->get()
                      ->getRowArray();
        
        if ($scoring) {
            $company = array_merge($company, $scoring);
        }

        // Calculate Match
        $matchResult = RadarAnalyzer::calculateMatch($company, $sellerSector);

        // Record usage
        $accessService->recordUsage($userId);
        ApiRequestLogger::log($userId, 'match', 'success', 'Match calculado para ' . $cif);

        return $this->respond([
            'success' => true,
            'data' => $matchResult
        ]);
    }
}
