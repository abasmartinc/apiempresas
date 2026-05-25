<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CompanyModel;
use App\Models\CompanyAdministratorModel;
use App\Services\PlanAccessService;
use App\Services\ApiRequestLogger;
use OpenApi\Attributes as OA;

class CompanyNetworkController extends ResourceController
{
    protected $format = 'json';

    #[OA\Get(
        path: "/api/v1/companies/network",
        summary: "Grafos de Poder Societario",
        description: "Obtiene la red de vinculación entre empresas a través de sus administradores.",
        tags: ["2. Plan Professional"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "CIF de la empresa base",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Grafo de nodos y conexiones (empresas y administradores)",
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
        
        if (!$cif) {
            return $this->fail('Parámetro cif requerido', 400);
        }

        // Verify API Key and Permissions
        $apiKey = $this->request->getHeaderLine('X-API-KEY');
        if (empty($apiKey)) {
            return $this->failUnauthorized('API Key no proporcionada');
        }

        $accessService = new PlanAccessService();
        // Permitting this for Pro and Business
        $validation = $accessService->validateAccess($apiKey, 'pro_and_business'); 
        
        if (!$validation['success']) {
            if (isset($validation['user_id'])) {
                ApiRequestLogger::log($validation['user_id'], 'network', 'error', $validation['message']);
            }
            return $this->failUnauthorized($validation['message']);
        }

        $userId = $validation['user_id'];
        $model = new CompanyModel();
        
        // Check if company exists
        $company = $model->getByCif($cif);
        if (!$company) {
            ApiRequestLogger::log($userId, 'network', 'error', 'Empresa no encontrada: ' . $cif);
            return $this->failNotFound('Empresa no encontrada');
        }

        $adminModel = new CompanyAdministratorModel();
        $admins = $adminModel->getByCompanyId($company['id']);

        if (empty($admins)) {
            return $this->respond([
                'success' => true,
                'data' => [
                    'nodes' => [
                        ['id' => 'C_' . $company['id'], 'type' => 'company', 'label' => $company['company_name'], 'cif' => $company['cif']]
                    ],
                    'edges' => []
                ],
                'message' => 'No se encontraron administradores para generar el grafo.'
            ]);
        }

        // Extract admin names
        $adminNames = array_unique(array_column($admins, 'name'));
        
        // Find linked companies
        $linkedRecords = $adminModel->getLinkedCompaniesByAdminNames($adminNames, $company['id']);

        // Build Graph structure
        $nodes = [];
        $edges = [];
        $nodeMap = []; // To prevent duplicates

        // Add root company node
        $rootNodeId = 'C_' . $company['id'];
        $nodes[] = [
            'id' => $rootNodeId,
            'type' => 'company',
            'label' => $company['company_name'],
            'cif' => $company['cif'],
            'root' => true
        ];
        $nodeMap[$rootNodeId] = true;

        // Add admin nodes and edges to root
        foreach ($admins as $admin) {
            $adminNodeId = 'A_' . md5($admin['name']);
            if (!isset($nodeMap[$adminNodeId])) {
                $nodes[] = [
                    'id' => $adminNodeId,
                    'type' => 'administrator',
                    'label' => $admin['name']
                ];
                $nodeMap[$adminNodeId] = true;
            }
            
            $edges[] = [
                'source' => $adminNodeId,
                'target' => $rootNodeId,
                'label' => $admin['position'] ?? 'Administrador'
            ];
        }

        // Add linked companies and their edges
        foreach ($linkedRecords as $link) {
            $compNodeId = 'C_' . $link['company_id'];
            $adminNodeId = 'A_' . md5($link['name']);

            if (!isset($nodeMap[$compNodeId])) {
                $nodes[] = [
                    'id' => $compNodeId,
                    'type' => 'company',
                    'label' => $link['linked_company_name'],
                    'cif' => $link['cif'],
                    'status' => $link['linked_company_status']
                ];
                $nodeMap[$compNodeId] = true;
            }

            $edges[] = [
                'source' => $adminNodeId,
                'target' => $compNodeId,
                'label' => $link['position'] ?? 'Administrador'
            ];
        }

        // Record usage
        $accessService->recordUsage($userId);
        ApiRequestLogger::log($userId, 'network', 'success', 'Grafo generado para ' . $cif);

        return $this->respond([
            'success' => true,
            'data' => [
                'nodes' => $nodes,
                'edges' => $edges,
                'stats' => [
                    'total_administrators' => count(array_unique(array_column($admins, 'name'))),
                    'total_linked_companies' => count(array_unique(array_column($linkedRecords, 'company_id')))
                ]
            ]
        ]);
    }
}
