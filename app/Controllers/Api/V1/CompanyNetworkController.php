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
        description: "Obtiene la red de vinculación entre empresas a través de sus administradores. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 3 créditos del monedero (bono prepago). Las respuestas con error no consumen cuota ni créditos.",
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
            
            $model = new CompanyModel();
            $company = $model->getByCif($cif);
            
            $stats = [
                'total_administrators' => '🔒 Pro/Business Plan',
                'total_linked_companies' => '🔒 Pro/Business Plan',
            ];
            
            if ($company) {
                $adminModel = new CompanyAdministratorModel();
                $admins = $adminModel->getByCompanyId($company['id']);
                if (!empty($admins)) {
                    $adminNames = array_unique(array_column($admins, 'name'));
                    $linkedRecords = $adminModel->getLinkedCompaniesByAdminNames($adminNames, $company['id']);
                    $stats = [
                        'total_administrators' => count($adminNames),
                        'total_linked_companies' => count(array_unique(array_column($linkedRecords, 'company_id'))),
                    ];
                }
            }

            return $this->respond([
                'success' => false,
                'message' => $validation['message'],
                'data' => [
                    'nodes' => [],
                    'edges' => [],
                    'stats' => $stats
                ],
                'upsell_opportunities' => [
                    'grafos_completos' => '🔒 Desbloquea el plan Professional o Business para ver las conexiones societarias completas.',
                    'nodos_detectados' => ($company && isset($stats['total_administrators']) && is_numeric($stats['total_administrators'])) 
                        ? "Hemos detectado {$stats['total_administrators']} administradores y {$stats['total_linked_companies']} empresas vinculadas. ¡Pásate a Pro para mapear su red!"
                        : 'Mapea la red de vinculación entre empresas a través de sus administradores.',
                    'upgrade_url' => site_url('billing')
                ]
            ], 403);
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
