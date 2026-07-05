<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Models\BormePostsModel;
use OpenApi\Attributes as OA;

class CompanyBormeController extends BaseApiController
{
    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;
    
    /** @var BormePostsModel */
    protected $bormeModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->bormeModel = new BormePostsModel();
        helper(['api', 'company']);
    }

    #[OA\Get(
        path: "/api/v1/companies/borme",
        summary: "Historial de Actos del BORME",
        description: "Devuelve el historial cronológico completo de publicaciones en el Registro Mercantil (BORME) para una empresa. **Coste:** 1 llamada. Exclusivo para planes Pro y Business.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "El CIF de la empresa a consultar",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Historial del BORME de la empresa",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "cif", type: "string", example: "B12345678"),
                        new OA\Property(property: "company_name", type: "string", example: "EMPRESA DE EJEMPLO SL"),
                        new OA\Property(
                            property: "events",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "date", type: "string", format: "date", example: "2023-11-01"),
                                    new OA\Property(property: "act_types", type: "string", example: "Nombramientos, Ceses"),
                                    new OA\Property(property: "description", type: "string", example: "Ceses/Dimisiones. Administrador único: JUAN PEREZ..."),
                                    new OA\Property(property: "url_pdf", type: "string", example: "https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf")
                                ]
                            )
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Error de validación",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "VALIDATION_ERROR"),
                new OA\Property(property: "message", type: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Plan insuficiente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "UPGRADE_REQUIRED"),
                new OA\Property(property: "message", type: "string", example: "Este endpoint requiere un plan Pro o Business.")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Empresa no encontrada",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "COMPANY_NOT_FOUND"),
                new OA\Property(property: "message", type: "string")
            ]
        )
    )]
    public function index()
    {
        // Require Pro/Business plan
        $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
        $walletBalance = \App\Filters\ApiKeyFilter::$apiMeta['wallet_balance'] ?? 0;
        
        if ((int)$planId === 1 && $walletBalance <= 0) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'UPGRADE_REQUIRED',
                    'message' => 'Este endpoint requiere un plan Pro o Business.'
                ],
                ResponseInterface::HTTP_FORBIDDEN
            );
        }

        $cif = trim((string) $this->request->getGet('cif'));

        if ($cif === '') {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'VALIDATION_ERROR',
                    'message' => 'El parámetro "cif" es obligatorio.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $cif = strtoupper($cif);
        $cif = preg_replace('/^ES/', '', $cif);
        $cif = preg_replace('/[^A-Z0-9]/', '', $cif);

        $fakeCifs = ['B99999999', 'B12345678', 'B12345674', 'A12345678', 'B00000000'];
        if (in_array($cif, $fakeCifs)) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'FAKE_CIF_NOT_ALLOWED',
                    'message' => 'Este parece ser un CIF de prueba. Por favor, utiliza un CIF real o prueba con el de Inditex (A15075062).'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        if (!is_valid_cif($cif)) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'INVALID_CIF_FORMAT',
                    'message' => 'El CIF proporcionado no tiene un formato válido.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $company = $this->companyModel->getByCif($cif);

        if (!$company) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'COMPANY_NOT_FOUND',
                    'message' => 'Empresa no encontrada.'
                ],
                ResponseInterface::HTTP_NOT_FOUND
            );
        }

        $companyId = $company['id'];
        $bormePostsRaw = $this->bormeModel->getByCompanyId($companyId);
        
        $events = [];
        foreach ($bormePostsRaw as $post) {
            $events[] = [
                'date' => $post['borme_date'] ?? null,
                'act_types' => $post['act_types'] ?? '',
                'description' => $post['description'] ?? '',
                'url_pdf' => $post['url_pdf'] ?? ''
            ];
        }

        return $this->respond(
            [
                'success' => true,
                'data'    => [
                    'cif' => $company['cif'],
                    'company_name' => $company['name'],
                    'events' => $events
                ]
            ],
            ResponseInterface::HTTP_OK
        );
    }
}
