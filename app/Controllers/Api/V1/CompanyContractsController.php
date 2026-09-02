<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Services\PlanAccessService;
use OpenApi\Attributes as OA;

class CompanyContractsController extends BaseApiController
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

    #[OA\Get(
        path: "/api/v1/companies/contracts",
        summary: "Contratos Públicos y Licitaciones",
        description: "Devuelve el historial oficial de adjudicaciones y licitaciones del Sector Público asociadas a una empresa mediante su CIF. **Coste:** 1 llamada. Exclusivo para el plan Business.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "El CIF de la empresa a consultar",
        schema: new OA\Schema(type: "string", example: "A10007516")
    )]
    #[OA\Parameter(
        name: "page",
        in: "query",
        required: false,
        description: "Número de página (>= 1). Por defecto: 1.",
        schema: new OA\Schema(type: "integer", default: 1)
    )]
    #[OA\Parameter(
        name: "limit",
        in: "query",
        required: false,
        description: "Número de resultados por página (1 a 100). Por defecto: 20.",
        schema: new OA\Schema(type: "integer", default: 20)
    )]
    #[OA\Response(
        response: 200,
        description: "Historial de contratos públicos de la empresa",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "cif", type: "string", example: "A10007516"),
                        new OA\Property(property: "company_name", type: "string", example: "CONSTRUCCIONES ALOR SA"),
                        new OA\Property(
                            property: "summary",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total_contracts", type: "integer", example: 14),
                                new OA\Property(property: "total_amount", type: "string", example: "311190.55"),
                                new OA\Property(property: "currency", type: "string", example: "EUR")
                            ]
                        ),
                        new OA\Property(
                            property: "contracts",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "tender_id", type: "string", nullable: true, example: "https://contrataciondelestado.es/sindicacion/licitacionesPerfilContratante/16026766"),
                                    new OA\Property(property: "title", type: "string", nullable: true, example: "Obras de Restauración de la Ermita de San Jorge de Alor en Cáceres"),
                                    new OA\Property(property: "contracting_authority", type: "string", nullable: true, example: "Consejería de Cultura, Turismo, Jóvenes y Deportes"),
                                    new OA\Property(property: "award_date", type: "string", format: "date", nullable: true, example: "2024-12-30"),
                                    new OA\Property(property: "amount", type: "string", nullable: true, example: "311190.55"),
                                    new OA\Property(property: "currency", type: "string", example: "EUR"),
                                    new OA\Property(property: "tender_url", type: "string", nullable: true, example: "https://contrataciondelestado.es/wps/poc?uri=deeplink:detalle_licitacion&idEvl=T1rE11yqCklWhbmkna2nXQ%3D%3D")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "pagination",
                            type: "object",
                            properties: [
                                new OA\Property(property: "total", type: "integer", example: 14),
                                new OA\Property(property: "page", type: "integer", example: 1),
                                new OA\Property(property: "limit", type: "integer", example: 20),
                                new OA\Property(property: "total_pages", type: "integer", example: 1),
                                new OA\Property(property: "has_more", type: "boolean", example: false)
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Error de validación de parámetros",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "VALIDATION_ERROR"),
                new OA\Property(property: "message", type: "string", example: "El parámetro \"cif\" es obligatorio.")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "No autorizado (API key inválida o inactiva)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "UNAUTHORIZED"),
                new OA\Property(property: "message", type: "string", example: "API key inválida o inactiva.")
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Restricción de plan (exclusivo Business)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "PLAN_RESTRICTION"),
                new OA\Property(property: "message", type: "string", example: "El acceso a contratos públicos requiere el plan Business.")
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
                new OA\Property(property: "message", type: "string", example: "Empresa no encontrada.")
            ]
        )
    )]
    #[OA\Response(
        response: 429,
        description: "Límite de peticiones excedido",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "TOO_MANY_REQUESTS"),
                new OA\Property(property: "message", type: "string", example: "Has superado el límite de peticiones.")
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Error interno del servidor",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "error", type: "string", example: "SERVER_ERROR"),
                new OA\Property(property: "message", type: "string", example: "Se ha producido un error interno al consultar los contratos públicos.")
            ]
        )
    )]
    public function index()
    {
        // 1. Authorization: Business entitlement check (Zero query to company_contracts for Free/Pro)
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'public_contracts')) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'PLAN_RESTRICTION',
                    'message' => 'El acceso a contratos públicos requiere el plan Business.'
                ],
                ResponseInterface::HTTP_FORBIDDEN
            );
        }

        // 2. Input validation
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
        if (in_array($cif, $fakeCifs, true)) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'FAKE_CIF_NOT_ALLOWED',
                    'message' => 'Este parece ser un CIF de prueba. Por favor, utiliza un CIF real o prueba con el de Inditex (A15075062).'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

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
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'INVALID_CIF_FORMAT',
                    'message' => 'El CIF proporcionado no tiene un formato válido.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // 3. Verify company existence in the master database
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

        // 4. Pagination parameters (Strict numeric validation & clamp)
        $rawPage = $this->request->getGet('page');
        $page = (is_numeric($rawPage) && (int) $rawPage >= 1) ? (int) $rawPage : 1;

        $rawLimit = $this->request->getGet('limit');
        $limit = (is_numeric($rawLimit) && (int) $rawLimit >= 1) ? min((int) $rawLimit, 100) : 20;

        $offset = ($page - 1) * $limit;

        // 5. Query company_contracts (Parameterized query builder)
        try {
            $db = \Config\Database::connect();

            // Aggregated summary
            $summaryRow = $db->table('company_contracts')
                ->select('COUNT(*) AS total_count, COALESCE(SUM(importe_adjudicacion), 0.00) AS total_amount')
                ->where('company_cif', $cif)
                ->get()
                ->getRowArray();

            $totalContracts = (int) ($summaryRow['total_count'] ?? 0);
            $rawTotalAmount = $summaryRow['total_amount'] ?? '0.00';
            $totalAmount = $this->formatDecimalString((string) $rawTotalAmount) ?? '0.00';

            // Paged contract list
            $contracts = [];
            if ($totalContracts > 0) {
                $rows = $db->table('company_contracts')
                    ->select('id_licitacion, organo_contratacion, titulo_contrato, fecha_adjudicacion, importe_adjudicacion, enlace_licitacion')
                    ->where('company_cif', $cif)
                    ->orderBy('fecha_adjudicacion', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray();

                foreach ($rows as $r) {
                    $rawAmount = $r['importe_adjudicacion'] !== null ? (string) $r['importe_adjudicacion'] : null;
                    $amount = $this->formatDecimalString($rawAmount);

                    $contracts[] = [
                        'tender_id'             => $r['id_licitacion'] !== null ? (string) $r['id_licitacion'] : null,
                        'title'                 => $r['titulo_contrato'] !== null ? (string) $r['titulo_contrato'] : null,
                        'contracting_authority' => $r['organo_contratacion'] !== null ? (string) $r['organo_contratacion'] : null,
                        'award_date'            => $r['fecha_adjudicacion'] !== null ? (string) $r['fecha_adjudicacion'] : null,
                        'amount'                => $amount,
                        'currency'              => 'EUR',
                        'tender_url'            => $r['enlace_licitacion'] !== null ? (string) $r['enlace_licitacion'] : null,
                    ];
                }
            }

            $totalPages = $totalContracts > 0 ? (int) ceil($totalContracts / $limit) : 0;
            $hasMore = $page < $totalPages;

            return $this->respond(
                [
                    'success' => true,
                    'data'    => [
                        'cif'          => $cif,
                        'company_name' => $company['name'] ?? $company['company_name'] ?? '',
                        'summary'      => [
                            'total_contracts' => $totalContracts,
                            'total_amount'    => $totalAmount,
                            'currency'        => 'EUR',
                        ],
                        'contracts'    => $contracts,
                        'pagination'   => [
                            'total'       => $totalContracts,
                            'page'        => $page,
                            'limit'       => $limit,
                            'total_pages' => $totalPages,
                            'has_more'    => $hasMore,
                        ],
                    ],
                ],
                ResponseInterface::HTTP_OK
            );
        } catch (\Throwable $e) {
            log_message('error', '[CompanyContractsController::index] ' . $e->getMessage());

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al consultar los contratos públicos.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Normalizes DECIMAL(15,2) strings without binary float conversion.
     *
     * @param string|null $value
     * @return string|null
     */
    private function formatDecimalString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || !is_numeric($value)) {
            return '0.00';
        }

        $parts = explode('.', $value, 2);
        $integerPart = ($parts[0] === '' || $parts[0] === '-') ? ($parts[0] . '0') : $parts[0];
        $decimalPart = $parts[1] ?? '00';

        // Pad with zeros or truncate to exact 2 decimal places
        $decimalPart = substr(str_pad($decimalPart, 2, '0'), 0, 2);

        return $integerPart . '.' . $decimalPart;
    }
}
