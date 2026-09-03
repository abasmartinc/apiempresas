<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use App\Services\PlanAccessService;
use OpenApi\Attributes as OA;

class CompanyRiskProfileController extends BaseApiController
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
        path: "/api/v1/companies/risk-profile",
        summary: "Perfil de Riesgo Corporativo",
        description: "Devuelve el perfil de riesgo corporativo, cumplimiento registral (depósito de cuentas), estabilidad de gobernanza y alertas mercantiles asociadas a una empresa mediante su CIF. **Coste:** 1 llamada. Exclusivo para el plan Business.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "El CIF de la empresa a consultar",
        schema: new OA\Schema(type: "string", example: "A01001411")
    )]
    #[OA\Response(
        response: 200,
        description: "Perfil de riesgo corporativo de la empresa",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "cif", type: "string", example: "A01001411"),
                        new OA\Property(property: "company_name", type: "string", example: "RHEINMETALL EXPAL MUNITIONS SA"),
                        new OA\Property(property: "risk_score", type: "integer", example: 62),
                        new OA\Property(property: "risk_level", type: "string", example: "ALTO"),
                        new OA\Property(property: "confidence_score", type: "integer", nullable: true, example: 49),
                        new OA\Property(property: "data_quality_score", type: "integer", nullable: true, example: 70),
                        new OA\Property(property: "summary_message", type: "string", nullable: true, example: "Atención: Constan indicadores de elevado riesgo financiero o corporativo."),
                        new OA\Property(property: "legal_state", type: "string", nullable: true, example: "REGISTRY_CLOSURE_GENERICO"),
                        new OA\Property(
                            property: "data_sources",
                            type: "object",
                            properties: [
                                new OA\Property(property: "borme_status", type: "string", nullable: true, example: "CHECKED_WITH_RECORDS"),
                                new OA\Property(property: "accounts_status", type: "string", nullable: true, example: "KNOWN_DELAYED"),
                                new OA\Property(property: "official_status", type: "string", nullable: true, example: "KNOWN")
                            ]
                        ),
                        new OA\Property(
                            property: "dimensions",
                            type: "object",
                            properties: [
                                new OA\Property(property: "legal_distress", type: "integer", example: 60),
                                new OA\Property(property: "filing_compliance", type: "integer", example: 0),
                                new OA\Property(property: "governance_volatility", type: "integer", example: 30),
                                new OA\Property(property: "capital_instability", type: "integer", example: 0),
                                new OA\Property(property: "structural_volatility", type: "integer", example: 0),
                                new OA\Property(property: "stabilizing_credit", type: "integer", example: 0)
                            ]
                        ),
                        new OA\Property(
                            property: "canonical_events",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "code", type: "string", nullable: true, example: "LEGAL_STATE_REGISTRY_CLOSURE_GENERICO"),
                                    new OA\Property(property: "dimension", type: "string", nullable: true, example: "legal_distress"),
                                    new OA\Property(property: "severity", type: "string", nullable: true, example: "high"),
                                    new OA\Property(property: "description", type: "string", nullable: true, example: "Consta publicación registral de cierre sin especificación de causa."),
                                    new OA\Property(property: "event_date", type: "string", format: "date", nullable: true, example: "2026-08-24"),
                                    new OA\Property(property: "classification_confidence", type: "string", nullable: true, example: "LOW")
                                ]
                            )
                        ),
                        new OA\Property(property: "model_version", type: "string", example: "2.0.0"),
                        new OA\Property(property: "calculated_at", type: "string", format: "date-time", nullable: true, example: "2026-08-24T00:00:00Z")
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
                new OA\Property(property: "message", type: "string", example: "El acceso al perfil de riesgo corporativo requiere el plan Business.")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Empresa no encontrada o perfil no disponible",
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
                new OA\Property(property: "message", type: "string", example: "Se ha producido un error interno al consultar el perfil de riesgo.")
            ]
        )
    )]
    public function index()
    {
        // 1. Authorization: Business entitlement check (Zero query to company_risk_profiles for Free/Pro)
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'corporate_risk_profile')) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'PLAN_RESTRICTION',
                    'message' => 'El acceso al perfil de riesgo corporativo requiere el plan Business.'
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

        // 4. Query company_risk_profiles (Parameterized query builder)
        try {
            $db = \Config\Database::connect();
            $riskRow = $db->table('company_risk_profiles')
                ->select('cif, risk_score, risk_level, risk_profile, updated_at')
                ->where('cif', $cif)
                ->get()
                ->getRowArray();

            if (!$riskRow) {
                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'RISK_PROFILE_NOT_AVAILABLE',
                        'message' => 'No se dispone de un perfil de riesgo calculado para esta empresa.'
                    ],
                    ResponseInterface::HTTP_NOT_FOUND
                );
            }

            $rawJson = $riskRow['risk_profile'] ?? '';
            $profileData = json_decode((string) $rawJson, true);

            if (!is_array($profileData)) {
                log_message('error', '[CompanyRiskProfileController] Malformed JSON in company_risk_profiles for CIF: ' . $cif);
                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'SERVER_ERROR',
                        'message' => 'Se ha producido un error interno al procesar el perfil de riesgo.'
                    ],
                    ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $dto = $this->buildRiskProfileDto($cif, $company, $riskRow, $profileData);

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $dto
                ],
                ResponseInterface::HTTP_OK
            );

        } catch (\Throwable $e) {
            log_message('error', '[CompanyRiskProfileController::index] ' . $e->getMessage());
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al consultar el perfil de riesgo.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Proyecta el payload crudo interno sobre el DTO público normalizado,
     * aplicando denylist estricto de campos internos.
     */
    protected function buildRiskProfileDto(string $cif, array $company, array $riskRow, array $p): array
    {
        // 1. Core identification
        $companyName = !empty($company['name']) ? trim((string) $company['name']) : (!empty($company['company_name']) ? trim((string) $company['company_name']) : '');

        // 2. Risk score & level (Stored source of truth)
        $riskScore = isset($p['risk_score']) && is_numeric($p['risk_score']) ? (int) $p['risk_score'] : (int) ($riskRow['risk_score'] ?? 0);
        $riskLevel = !empty($p['risk_level']) ? (string) $p['risk_level'] : (string) ($riskRow['risk_level'] ?? 'BAJO');

        // 3. Confidence & Quality scores
        $confidenceScore = isset($p['confidence_score']) && is_numeric($p['confidence_score']) ? (int) $p['confidence_score'] : null;
        $dataQualityScore = isset($p['data_quality_score']) && is_numeric($p['data_quality_score']) ? (int) $p['data_quality_score'] : null;

        // 4. Summary & Legal state
        $summaryMessage = !empty($p['summary_message']) ? (string) $p['summary_message'] : null;
        $legalState = !empty($p['legal_state']) ? (string) $p['legal_state'] : null;

        // 5. Data sources (Strict whitelist)
        $rawSources = is_array($p['data_sources'] ?? null) ? $p['data_sources'] : [];
        $dataSources = [
            'borme_status'    => isset($rawSources['borme_status']) ? (string) $rawSources['borme_status'] : null,
            'accounts_status' => isset($rawSources['accounts_status']) ? (string) $rawSources['accounts_status'] : null,
            'official_status' => isset($rawSources['official_status']) ? (string) $rawSources['official_status'] : null,
        ];

        // 6. Dimensions (Strict whitelist & numeric cast)
        $rawDim = is_array($p['dimensions'] ?? null) ? $p['dimensions'] : [];
        $dimensions = [
            'legal_distress'        => isset($rawDim['legal_distress']) && is_numeric($rawDim['legal_distress']) ? (int) $rawDim['legal_distress'] : 0,
            'filing_compliance'     => isset($rawDim['filing_compliance']) && is_numeric($rawDim['filing_compliance']) ? (int) $rawDim['filing_compliance'] : 0,
            'governance_volatility' => isset($rawDim['governance_volatility']) && is_numeric($rawDim['governance_volatility']) ? (int) $rawDim['governance_volatility'] : 0,
            'capital_instability'   => isset($rawDim['capital_instability']) && is_numeric($rawDim['capital_instability']) ? (int) $rawDim['capital_instability'] : 0,
            'structural_volatility' => isset($rawDim['structural_volatility']) && is_numeric($rawDim['structural_volatility']) ? (int) $rawDim['structural_volatility'] : 0,
            'stabilizing_credit'    => isset($rawDim['stabilizing_credit']) && is_numeric($rawDim['stabilizing_credit']) ? (int) $rawDim['stabilizing_credit'] : 0,
        ];

        // 7. Canonical events (Strict whitelist, stripping raw_record_ids & weights)
        $canonicalEvents = [];
        if (!empty($p['canonical_events']) && is_array($p['canonical_events'])) {
            foreach ($p['canonical_events'] as $ev) {
                if (!is_array($ev)) continue;
                $canonicalEvents[] = [
                    'code'                      => isset($ev['code']) ? (string) $ev['code'] : null,
                    'dimension'                 => isset($ev['dimension']) ? (string) $ev['dimension'] : null,
                    'severity'                  => isset($ev['severity']) ? (string) $ev['severity'] : null,
                    'description'               => isset($ev['description']) ? (string) $ev['description'] : null,
                    'event_date'                => isset($ev['event_date']) ? (string) $ev['event_date'] : null,
                    'classification_confidence' => isset($ev['classification_confidence']) ? (string) $ev['classification_confidence'] : null,
                ];
            }
        }

        // 8. Model metadata
        $modelVersion = !empty($p['model_version']) ? (string) $p['model_version'] : '2.0.0';
        $calculatedAt = !empty($p['calculated_at']) ? (string) $p['calculated_at'] : (!empty($riskRow['updated_at']) ? (string) $riskRow['updated_at'] : null);

        return [
            'cif'                => $cif,
            'company_name'       => $companyName,
            'risk_score'         => $riskScore,
            'risk_level'         => $riskLevel,
            'confidence_score'   => $confidenceScore,
            'data_quality_score' => $dataQualityScore,
            'summary_message'    => $summaryMessage,
            'legal_state'        => $legalState,
            'data_sources'       => $dataSources,
            'dimensions'         => $dimensions,
            'canonical_events'   => $canonicalEvents,
            'model_version'      => $modelVersion,
            'calculated_at'      => $calculatedAt,
        ];
    }
}
