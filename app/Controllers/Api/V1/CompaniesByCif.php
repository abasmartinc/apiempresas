<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use OpenApi\Attributes as OA;

class CompaniesByCif extends BaseApiController
{


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
        description: "Devuelve los datos detallados de una empresa a partir de su CIF exacto. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 1 crédito del monedero (bono prepago). Las respuestas con error (400, 404, etc.) no consumen cuota ni créditos. **Estado normalizado (todos los planes):** `status_code` (ACTIVE, PRESUMED_ACTIVE, INSOLVENCY, IN_LIQUIDATION, DISSOLVED, REGISTRY_CLOSED, MERGED, INACTIVE, EXTINCT, UNKNOWN), `status_source` (registry o borme_analysis) y `status_date`. `status` sigue siendo el texto del Registro tal cual. **Pro, Business o saldo:** `financials` con `size_band` (LT_500K, 500K_1M, GT_1M, NO_REVENUE; tramo orientativo de facturación), `size_band_label` y `last_accounts_year` (último ejercicio depositado que consta en nuestra base; puede haber uno posterior).",
        tags: ["1. Plan Free"]
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
        description: "Si es 'true', incluye los administradores y cargos vigentes: nombramientos del BORME menos los ceses, dimisiones y revocaciones publicados después. Cada uno lleva `since` (fecha del nombramiento vigente; null si es anterior a nuestro histórico). Exclusivo para planes Pro y Business.",
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
            return $this->respond(
                [
                    'success' => true,
                    'data'    => $this->completar($cached),
                ] + array_filter(['notice' => self::avisoCupo()]),
                ResponseInterface::HTTP_OK
            );
        }

        try {
            $company = $this->companyModel->getByCif($cif, true);

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

            cache()->save($cacheKey, $company, 2592000); // 30 dias

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $this->completar($company),
                    // Campo nuevo desde el 80 % del cupo (ver BaseApiController::avisoCupo)
                ] + array_filter(['notice' => self::avisoCupo()]),
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
        }
        // El correo de "primera consulta" ya no se envía aquí: se mandaba dentro de la
        // petición y hacía más lenta justo la primera llamada del usuario. Ahora lo
        // envía email:automation (trigger first_request).
    }

    /**
     * Deja la ficha lista para responder: enmascarado del Free, campos añadidos
     * (estado normalizado, financials o el gancho del Free), limpieza y, con
     * admin=true en planes de pago o con saldo, los administradores VIGENTES.
     * Mismo tratamiento para la ficha recién leída y para la cacheada.
     */
    private function completar(array $company): array
    {
        $planId        = (int) (\App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1);
        $walletBalance = (int) (\App\Filters\ApiKeyFilter::$apiMeta['wallet_balance'] ?? 0);
        $fullAccess    = $planId > 1 || $walletBalance > 0;

        if (!$fullAccess) {
            $company = mask_company_data($company);
            // Cuánto le queda, legible por código (campos nuevos; antes solo en cabeceras)
            $cupo = self::cupoTrasPeticion();
            if ($cupo !== null && isset($company['upsell_opportunities']) && is_array($company['upsell_opportunities'])) {
                $company['upsell_opportunities']['consultas_restantes'] = $cupo['restantes'];
                $company['upsell_opportunities']['consultas_totales']   = $cupo['total'];
                $company['upsell_opportunities']['se_renuevan']         = false;
            }
        }

        $company = \App\Services\ApiCompanyEnricher::enrich([$company], $fullAccess)[0];
        $companyId = $company['id'] ?? null; // id actual (enrich lo refresca por CIF)
        $company = filter_company_data($company);

        $includeAdmins = filter_var($this->request->getGet('admin'), FILTER_VALIDATE_BOOLEAN);
        if ($includeAdmins && $fullAccess && $companyId) {
            $vigentes = \App\Services\ApiCompanyEnricher::currentAdministrators([(int) $companyId]);
            $company['administrators'] = $vigentes[(int) $companyId] ?? [];
        }

        // Pro: qué añadiría Business sobre esta empresa (campo nuevo, con tope diario)
        $preview = self::businessPreview((string) ($company['cif'] ?? ''), 'api_pro_companies');
        if ($preview !== null) {
            $company['business_preview'] = $preview;
        }

        return $company;
    }
}
