<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use OpenApi\Attributes as OA;

class CompaniesByCif extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    /** @var \App\Services\EmailService */
    protected $emailService;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->emailService = new \App\Services\EmailService();
        helper(['api', 'company']);
    }

    #[OA\Get(
        path: "/api/v1/companies",
        summary: "Obtener Empresa por CIF",
        description: "Devuelve los datos detallados de una empresa a partir de su CIF exacto. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 1 crédito del monedero (bono prepago). Las respuestas con error (400, 404, etc.) no consumen cuota ni créditos.",
        tags: ["1. Plan Free / General"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "El CIF de la empresa a consultar",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "admin",
        in: "query",
        required: false,
        description: "Si es 'true', incluye los administradores y cargos directivos actuales de la empresa. Exclusivo para planes Pro y Business.",
        schema: new OA\Schema(type: "boolean")
    )]
    #[OA\Response(
        response: 200,
        description: "Datos de la empresa",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Error de validación o formato de CIF incorrecto",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "INVALID_CIF_FORMAT", description: "Código del error: 'VALIDATION_ERROR' o 'INVALID_CIF_FORMAT'"),
                new OA\Property(property: "message", type: "string", example: "El CIF proporcionado no tiene un formato válido (debe tener una letra, 7 dígitos y un dígito o letra de control).")
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

        // Limpieza de formato
        $cif = strtoupper($cif);
        $cif = preg_replace('/^ES/', '', $cif); // Quitar prefijo de país si lo ponen
        $cif = preg_replace('/[^A-Z0-9]/', '', $cif); // Quitar guiones, espacios, símbolos raros

        // Detectar CIFs de prueba comunes
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

        // Detectar Autónomos (DNI: 8 números + Letra, o NIE: X/Y/Z + 7 números + Letra)
        if (preg_match('/^[0-9]{8}[A-Z]$/', $cif) || preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $cif)) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'AUTONOMO_NOT_SUPPORTED',
                    'message' => 'No proporcionamos datos de autónomos por motivos de RGPD, únicamente sociedades (CIF).'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        if (!is_valid_cif($cif)) {
            $message = 'El CIF proporcionado no tiene un formato válido (debe tener una letra, 7 dígitos y un dígito o letra de control).';
            if (strlen($cif) > 12) {
                $message = 'El parámetro "cif" parece contener el nombre de una empresa en lugar de un CIF. Para realizar búsquedas por nombre, utiliza el endpoint de búsqueda: /api/v1/companies/search?q=' . urlencode($cif);
            }
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'INVALID_CIF_FORMAT',
                    'message' => $message
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Cache interno por CIF (24h)
        $cacheKey = 'company_by_cif_' . md5(mb_strtolower($cif, 'UTF-8'));
        $cached = cache($cacheKey);

        if (is_array($cached) && !empty($cached)) {
            // Apply masking if Free plan
            $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
            if ((int)$planId === 1) {
                $cached = mask_company_data($cached);
            }

            $companyId = $cached['id'] ?? null;

            // Apply filtering (remove requested fields)
            $cached = filter_company_data($cached);

            // Administradores y Cargos
            $includeAdmins = filter_var($this->request->getGet('admin'), FILTER_VALIDATE_BOOLEAN);
            if ($includeAdmins && (int)$planId > 1 && $companyId) {
                $db = \Config\Database::connect();
                $cached['administrators'] = $db->table('company_administrators')
                    ->select('name, position')
                    ->where('company_id', $companyId)
                    ->get()->getResultArray();
            }

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $cached,
                ],
                ResponseInterface::HTTP_OK
            );
        }

        try {
            $company = $this->companyModel->getByCif($cif);

            if (!$company) {
                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'COMPANY_NOT_FOUND',
                        'message' => 'Empresa no encontrada en BD principal. Ha sido encolada automáticamente y estará disponible en los próximos minutos.'
                    ],
                    ResponseInterface::HTTP_NOT_FOUND
                );
            }

            // Guardar SOLO data en cache (completa)
            cache()->save($cacheKey, $company, 86400); // 24h

            // Apply masking if Free plan
            $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
            if ((int)$planId === 1) {
                $company = mask_company_data($company);
            }

            $companyId = $company['id'] ?? null;

            // Apply filtering (remove requested fields)
            $company = filter_company_data($company);

            // Administradores y Cargos
            $includeAdmins = filter_var($this->request->getGet('admin'), FILTER_VALIDATE_BOOLEAN);
            if ($includeAdmins && (int)$planId > 1 && $companyId) {
                $db = \Config\Database::connect();
                $company['administrators'] = $db->table('company_administrators')
                    ->select('name, position')
                    ->where('company_id', $companyId)
                    ->get()->getResultArray();
            }

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $company,
                ],
                ResponseInterface::HTTP_OK
            );
        } catch (\Throwable $e) {
            log_message('error', '[CompaniesByCif::index] ' . $e->getMessage());

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al consultar la empresa.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        } finally {
            // Trigger: Milestone 1st Request Email
            try {
                if (isset($company) && $company) {
                    $userId = \App\Filters\ApiKeyFilter::$apiMeta['user_id'] ?? null;
                    if ($userId) {
                        $automationModel = new \App\Models\EmailAutomationModel();
                        if (!$automationModel->wasSent($userId, 'first_request')) {
                            $userModel = new \App\Models\UserModel();
                            $user = $userModel->asArray()->find($userId);
                            if ($user && (int)$user['is_admin'] === 0) {
                                $this->emailService->sendFirstRequestMilestone($user);
                                $automationModel->markAsSent($userId, 'first_request');
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                log_message('error', '[CompaniesByCif::Milestone] ' . $e->getMessage());
            }
        }
    }
}
