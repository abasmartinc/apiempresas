<?php

namespace App\Controllers\Api\V1\Sandbox;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class SandboxController extends \App\Controllers\Api\V1\BaseApiController
{


    protected $format = 'json';

    /**
     * Valida si el CIF es un CIF mágico permitido en el Sandbox.
     * Devuelve el CIF limpiado o false si no es mágico.
     */
    private function validateMagicCif($cif)
    {
        $cif = strtoupper(trim((string)$cif));
        $cif = preg_replace('/[^A-Z0-9]/', '', $cif);

        $allowedCifs = ['A15075062', 'B00000000', 'C11111111'];
        if (!in_array($cif, $allowedCifs)) {
            return false;
        }
        return $cif;
    }

    private function getForbiddenResponse()
    {
        return $this->respond([
            'success' => false,
            'error' => 'TEST_MODE_RESTRICTION',
            'message' => 'Estás usando la API Key en modo Sandbox. Para buscar datos reales, utiliza tu Live API Key en la URL de producción. Los CIFs permitidos en pruebas son A15075062 (éxito) y B00000000 (no encontrado).',
            // Campos añadidos (26-09-2026): el mensaje de arriba habla de una "Live API
            // Key" que no existe (hay una sola clave) y quien prueba CIF reales se queda
            // atascado. 'message' no cambia por contrato; esto dice lo que hay que hacer.
            'hint'           => 'El sandbox solo acepta los CIF de prueba A15075062 y B00000000. Para consultar cualquier otro CIF usa esta misma API Key en la URL de producción (gasta 1 consulta de tu plan).',
            'allowed_cifs'   => ['A15075062', 'B00000000'],
            'production_url' => $this->urlProduccion(),
        ], ResponseInterface::HTTP_FORBIDDEN);
    }

    /** La misma petición contra producción: /api/sandbox/v1/... → /api/v1/... con sus parámetros. */
    private function urlProduccion(): string
    {
        $path  = (string) $this->request->getUri()->getPath();
        $prod  = preg_replace('#api/sandbox/v1#', 'api/v1', $path, 1);
        $query = (string) $this->request->getUri()->getQuery();
        return rtrim(site_url(ltrim((string) $prod, '/')), '/') . ($query !== '' ? '?' . $query : '');
    }

    private function getMockInditex()
    {
        return [
            'name' => 'INDUSTRIA DE DISENO TEXTIL SA',
            'cif' => 'A15075062',
            'cnae' => '4642',
            'cnae_label' => 'Comercio al por mayor de prendas de vestir y calzado',
            'cnae_2025' => '4642',
            'cnae_2025_label' => 'Comercio al por mayor de prendas de vestir y calzado',
            'corporate_purpose' => 'COMERCIO AL POR MAYOR Y MENOR DE TODA CLASE DE PRENDAS DE VESTIR.',
            'founded' => '1985-06-12',
            'province' => 'A CORUÑA',
            'address' => 'AVENIDA DE LA DIPUTACION (ED INDITEX), S/N',
            'municipality' => 'ARTEIXO',
            'lat' => '43.317',
            'lng' => '-8.508',
            'status' => 'ACTIVA',
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            // Campos añadidos en producción el 26-09-2026 (ApiCompanyEnricher)
            'status_code'   => 'ACTIVE',
            'status_source' => 'registry',
            'status_date'   => null,
            'financials'    => [
                'size_band'          => 'GT_1M',
                'size_band_label'    => 'Más de 1 M€',
                'last_accounts_year' => 2024,
            ]
        ];
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies
    // =========================================================================
    public function companies()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "cif" es obligatorio.'], 400);
        }

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif === 'B00000000' || $cif === 'C11111111') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa no encontrada.'], 404);
        }

        // A15075062 (Inditex) - Mock Data
        $data = $this->getMockInditex();

        // Mapeo opcional de administradores
        if (filter_var($this->request->getGet('admin'), FILTER_VALIDATE_BOOLEAN)) {
            $data['administrators'] = [
                ['name' => 'MARTA ORTEGA PEREZ', 'position' => 'Presidente', 'since' => '2022-04-01'],
                ['name' => 'OSCAR GARCIA MACEIRAS', 'position' => 'Consejero Delegado', 'since' => '2021-12-01']
            ];
        }

        return $this->respond(['success' => true, 'data' => $data]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/search
    // =========================================================================
    public function search()
    {
        $q = trim((string) $this->request->getGet('name'));
        if ($q === '') $q = trim((string) $this->request->getGet('q'));

        if ($q === '') {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "q" es obligatorio.'], 400);
        }

        $multiple = filter_var($this->request->getGet('multiple'), FILTER_VALIDATE_BOOLEAN);

        $mockInditex = $this->getMockInditex();

        if ($multiple) {
            $mockInditex2 = $mockInditex;
            $mockInditex2['id'] = 888888;
            $mockInditex2['cif'] = 'B00000001';
            $mockInditex2['name'] = 'INDITEX LOGISTICA SA';

            return $this->respond([
                'success' => true,
                'data' => [$mockInditex, $mockInditex2],
                'meta' => ['total' => 2, 'page' => 1, 'limit' => 20]
            ]);
        }

        return $this->respond(['success' => true, 'data' => $mockInditex]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/score
    // =========================================================================
    public function score()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Score no disponible.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'score' => 98,
                'fuerza_financiera' => 'Excelente',
                'riesgo_impago' => 'Muy Bajo',
                'trayectoria' => 'Sólida',
                'mensaje' => 'La empresa presenta una solidez financiera sobresaliente.'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/signals
    // =========================================================================
    public function signals()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Señales no disponibles.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'signals' => [
                    ['date' => '2025-01-15', 'type' => 'Nombramiento', 'description' => 'Nombramiento de nuevo Consejero Delegado publicado en BORME.'],
                    ['date' => '2024-11-20', 'type' => 'Ampliación de Capital', 'description' => 'Ampliación de capital social registrada.']
                ]
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/batch
    // =========================================================================
    public function batch()
    {
        $input = $this->request->getJSON();
        if (!$input || empty($input->cifs) || !is_array($input->cifs)) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El cuerpo de la petición debe ser un JSON válido que contenga un array "cifs".'], 400);
        }

        $foundData = [];
        $cost = 0;
        foreach ($input->cifs as $rawCif) {
            $cif = $this->validateMagicCif($rawCif);
            if ($cif === 'A15075062') {
                $foundData[] = $this->getMockInditex();
                $cost++;
            }
        }

        return $this->respond([
            'success' => true,
            'data' => $foundData,
            'meta' => [
                'requested' => count($input->cifs),
                'found' => count($foundData),
                'cost' => $cost, // En el sandbox no se resta saldo real, es una simulación.
                'truncated' => false
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/insights
    // =========================================================================
    public function insights()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Insights no disponibles.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'profile' => 'Retail / Fast Fashion',
                'summary' => 'Empresa líder mundial en la fabricación y distribución textil.',
                'needs' => ['Optimización logística', 'Sostenibilidad', 'Digitalización B2B'],
                'conversion_probability' => 'Alta',
                'estimated_ticket' => 'Muy Alto'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/contact-prep
    // =========================================================================
    public function contactPrep()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Datos no disponibles.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'sales_approach' => 'Enfoque altamente consultivo y corporativo.',
                'suggested_message' => 'Hola, conociendo el volumen de operaciones logísticas de Inditex, nuestra solución aporta...',
                'likely_objection' => 'Ya trabajamos con grandes consultoras internacionales.',
                'attack_angle' => 'Agilidad y especialización de nicho con menores costes de implantación.'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/match
    // =========================================================================
    public function match()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Match no disponible.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'match_score' => 85,
                'fit_level' => 'Alto',
                'pain_points_addressed' => ['Ineficiencia operativa', 'Gestión de cadena de suministro'],
                'sales_argument' => 'Nuestro software elimina el trabajo manual...',
                'recommendation' => 'Contactar de inmediato.'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/network
    // =========================================================================
    public function network()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Red no disponible.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'nodes' => [
                    ['id' => 'C_123', 'type' => 'company', 'label' => 'INDUSTRIA DE DISENO TEXTIL SA', 'cif' => 'A15075062', 'root' => true],
                    ['id' => 'A_abc', 'type' => 'administrator', 'label' => 'MARTA ORTEGA PEREZ']
                ],
                'edges' => [
                    ['source' => 'A_abc', 'target' => 'C_123', 'label' => 'Presidente']
                ],
                'stats' => [
                    'total_administrators' => 1,
                    'total_linked_companies' => 1
                ]
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/radar
    // =========================================================================
    public function radar()
    {
        return $this->respond([
            'success' => true,
            'meta' => [
                'plan' => 'business',
                'count' => 1,
                'limit' => 1000,
                'total_disponibles' => 1
            ],
            'data' => [
                [
                    'cif' => 'A15075062',
                    'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                    'registro_mercantil' => 'A CORUÑA',
                    'fecha_constitucion' => '1985-06-12'
                ]
            ]
        ]);
    }
    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/borme
    // =========================================================================
    public function borme()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa no encontrada.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                'events' => [
                    [
                        'date' => '2023-11-01',
                        'act_types' => 'Nombramientos, Ceses',
                        'description' => 'Ceses/Dimisiones. Administrador único: JUAN PEREZ...',
                        'url_pdf' => 'https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf'
                    ],
                    [
                        'date' => '2022-05-14',
                        'act_types' => 'Constitución',
                        'description' => 'Constitución de la sociedad. Capital: 3000 Euros.',
                        'url_pdf' => 'https://www.boe.es/borme/dias/2022/05/14/pdfs/BORME-A-2022-50-28.pdf'
                    ]
                ]
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/risk-profile
    // =========================================================================
    public function riskProfile()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa no encontrada.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                'risk_score' => 15,
                'risk_level' => 'BAJO',
                'confidence_score' => 95,
                'data_quality_score' => 98,
                'summary_message' => 'Excelente solvencia y estabilidad societaria. Sin incidencias registrales ni alertas mercantiles activas.',
                'legal_state' => 'ACTIVA',
                'data_sources' => [
                    'borme_status' => 'CHECKED_WITH_RECORDS',
                    'accounts_status' => 'KNOWN_ON_TIME',
                    'official_status' => 'KNOWN'
                ],
                'dimensions' => [
                    'legal_distress' => 0,
                    'filing_compliance' => 10,
                    'governance_volatility' => 5,
                    'capital_instability' => 0,
                    'structural_volatility' => 0,
                    'stabilizing_credit' => 85
                ],
                'canonical_events' => [
                    [
                        'code' => 'ACCOUNTS_FILED_ON_TIME',
                        'dimension' => 'filing_compliance',
                        'severity' => 'low',
                        'description' => 'Depósito de cuentas anuales presentado en plazo en el Registro Mercantil.',
                        'event_date' => date('Y-07-15'),
                        'classification_confidence' => 'HIGH'
                    ]
                ],
                'model_version' => '2.0.0',
                'calculated_at' => date('Y-m-d\TH:i:s\Z')
            ]
        ]);
    }

    // =========================================================================
    // Bloque "sandbox" en las respuestas correctas (campo nuevo, 30-09-2026)
    // =========================================================================

    /**
     * Plan mínimo de cada endpoint y, en /companies, qué campos son de pago. El sandbox
     * responde siempre con los datos completos; sin esto, quien integra aquí no sabía
     * qué iba a ver con su plan, ni cómo pasar a producción.
     */
    private const PLAN_POR_ENDPOINT = [
        'companies'        => 'free',
        'search'           => 'free',
        'score'            => 'pro',
        'signals'          => 'pro',
        'insights'         => 'business',
        'contact-prep'     => 'business',
        'radar'            => 'free',
        'match'            => 'business',
        'network'          => 'pro',
        'borme'            => 'pro',
        'risk-profile'     => 'business',
        'batch'            => 'pro',
        'contracts'        => 'business',
        'verify'           => 'pro',
        'filter'           => 'business',
        'reconcile'        => 'pro',
        'watchlist'        => 'pro',
        'events'           => 'pro',
    ];

    private function infoSandbox(): array
    {
        $path = (string) $this->request->getUri()->getPath();
        $seg  = basename(rtrim($path, '/'));
        if (preg_match('#watchlist/[^/]+$#', $path) && $seg !== 'events') {
            $seg = 'watchlist';
        }
        $info = [
            'mode'           => 'sandbox',
            'cost'           => 0,
            'plan_required'  => self::PLAN_POR_ENDPOINT[$seg] ?? null,
            'production_url' => $this->urlProduccion(),
        ];
        if (in_array($seg, ['companies', 'search', 'batch'], true)) {
            $info['fields_by_plan'] = [
                'address'           => 'pro',
                'corporate_purpose' => 'pro (en Free, 100 caracteres)',
                'lat'               => 'pro',
                'lng'               => 'pro',
                'financials'        => 'pro',
                'administrators'    => 'pro',
            ];
        }
        $notas = [
            'radar'  => 'Free: 10 resultados con datos ocultos. Pro: 100. Business: 1.000.',
            'filter' => 'El recuento (count_only=true) es gratis en todos los planes; las filas son de Business (5 consultas por fila).',
            'score'  => 'Free: solo la cifra, sin desglose.',
            'insights' => 'Pro: vista previa (perfil y probabilidad). Business: completo.',
            'verify' => 'Cuesta 2 consultas. El bloque risk es de Business.',
        ];
        if (isset($notas[$seg])) {
            $info['plan_notes'] = $notas[$seg];
        }

        return $info;
    }

    public function respond($data = null, ?int $statusCode = null, string $message = '')
    {
        if (is_array($data) && ($data['success'] ?? null) === true && !isset($data['sandbox'])) {
            $data['sandbox'] = $this->infoSandbox();
        }

        return parent::respond($data, $statusCode, $message);
    }

    /** CIF de la query, limpio, o respuesta de error (400 / 403 / 404) si no sirve. */
    private function cifOError(bool $permitirNoEncontrado = false)
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "cif" es obligatorio.'], 400);
        }
        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) {
            return $this->getForbiddenResponse();
        }
        if ($cif !== 'A15075062' && !$permitirNoEncontrado) {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa no encontrada.'], 404);
        }

        return $cif;
    }

    /** Sin tildes, mayúsculas, solo letras y números. */
    private static function normalizar(string $s): string
    {
        $s = strtoupper(strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
                                   'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']));
        return trim(preg_replace('/[^A-Z0-9 ]+/', ' ', $s));
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/contracts
    // =========================================================================
    public function contracts()
    {
        $cif = $this->cifOError();
        if (!is_string($cif)) return $cif;

        $contratos = [
            ['tender_id' => 'SBX-2026-001', 'title' => 'Suministro de uniformes para el personal de atención al público', 'contracting_authority' => 'Ayuntamiento de Ejemplo', 'award_date' => '2026-06-15', 'amount' => '184500.00', 'currency' => 'EUR', 'tender_url' => 'https://contrataciondelestado.es/'],
            ['tender_id' => 'SBX-2025-114', 'title' => 'Vestuario laboral para servicios municipales (lote 2)', 'contracting_authority' => 'Diputación Provincial de Ejemplo', 'award_date' => '2025-11-03', 'amount' => '62300.00', 'currency' => 'EUR', 'tender_url' => 'https://contrataciondelestado.es/'],
        ];

        return $this->respond([
            'success' => true,
            'data'    => [
                'cif'          => $cif,
                'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                'summary'      => ['total_contracts' => 2, 'total_amount' => '246800.00', 'currency' => 'EUR'],
                'contracts'    => $contratos,
                'pagination'   => ['total' => 2, 'page' => 1, 'limit' => 20, 'total_pages' => 1, 'has_more' => false],
            ],
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/verify
    // =========================================================================
    public function verify()
    {
        $cif = $this->cifOError(true);
        if (!is_string($cif)) return $cif;

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa no encontrada.', 'decision_hint' => 'fail'], 404);
        }

        $checks = [];
        $flags  = [];
        $hint   = 'pass';

        $name = trim((string) $this->request->getGet('name'));
        if ($name !== '') {
            $n = self::normalizar($name);
            $score = (str_contains($n, 'INDUSTRIA DE DISENO TEXTIL') || str_contains($n, 'INDITEX')) ? 100 : 40;
            $checks['name'] = ['provided' => $name, 'score' => $score, 'match' => $score >= 80];
            if ($score < 80) {
                $flags[] = ['code' => 'NAME_MISMATCH', 'severity' => 'medium', 'message' => 'El nombre no coincide con la razón social.'];
                $hint = 'review';
            }
        }

        $person = trim((string) $this->request->getGet('person'));
        if ($person !== '') {
            $palabras = array_filter(explode(' ', self::normalizar($person)));
            $admins = [
                ['name' => 'ORTEGA PEREZ MARTA', 'position' => 'Presidente', 'since' => '2022-04-01'],
                ['name' => 'GARCIA MACEIRAS OSCAR', 'position' => 'Consejero Delegado', 'since' => '2021-12-01'],
            ];
            $encontrado = null;
            foreach ($admins as $a) {
                $suyas = explode(' ', $a['name']);
                if (count($palabras) >= 2 && !array_diff($palabras, $suyas)) {
                    $encontrado = $a;
                    break;
                }
            }
            $checks['person'] = [
                'provided'         => $person,
                'is_current_admin' => $encontrado !== null,
                'matched_name'     => $encontrado['name'] ?? null,
                'position'         => $encontrado['position'] ?? null,
                'since'            => $encontrado['since'] ?? null,
            ];
            if ($encontrado === null) {
                $flags[] = ['code' => 'SIGNER_NOT_ADMIN', 'severity' => 'medium', 'message' => 'Quien firma no consta como administrador vigente.'];
                $hint = 'review';
            }
        }

        if (filter_var($this->request->getGet('vat'), FILTER_VALIDATE_BOOLEAN)) {
            $checks['vat'] = ['vat_number' => 'ES' . $cif, 'checked' => true, 'valid' => true, 'source' => 'VIES', 'error' => null];
        }
        $checks['accounts'] = ['last_accounts_year' => 2024];

        return $this->respond([
            'success' => true,
            'data'    => [
                'cif'           => $cif,
                'exists'        => true,
                'name'          => 'INDUSTRIA DE DISENO TEXTIL SA',
                'status'        => 'ACTIVA',
                'status_code'   => 'ACTIVE',
                'status_source' => 'registry',
                'status_date'   => null,
                'checks'        => $checks,
                'flags'         => $flags,
                'decision_hint' => $hint,
                'checked_at'    => date('c'),
            ],
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/filter
    // =========================================================================
    public function filter()
    {
        $f = array_filter([
            'cnae'         => $this->request->getGet('cnae'),
            'province'     => $this->request->getGet('province'),
            'municipality' => $this->request->getGet('municipality'),
        ], static fn ($v) => is_string($v) && trim($v) !== '');
        if (!$f) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'Indica al menos uno de estos filtros: cnae, province o municipality.'], 400);
        }

        if (filter_var($this->request->getGet('count_only'), FILTER_VALIDATE_BOOLEAN)) {
            return $this->respond([
                'success' => true,
                'data'    => ['total' => 1234],
                'meta'    => ['filters' => $f, 'cost' => 0, 'counted_at' => date('c')],
            ]);
        }

        $fila = static fn (string $cif, string $name, string $municipio, string $fundada) => [
            'cif' => $cif, 'name' => $name, 'cnae' => '4642', 'cnae_label' => 'Comercio al por mayor de prendas de vestir y calzado',
            'province' => 'A CORUÑA', 'municipality' => $municipio, 'founded' => $fundada,
            'status' => 'ACTIVA', 'status_code' => 'ACTIVE', 'status_source' => 'registry',
            'financials' => ['size_band' => 'GT_1M', 'size_band_label' => 'Más de 1 M€', 'last_accounts_year' => 2024],
            'has_phone' => true,
        ];

        return $this->respond([
            'success' => true,
            'data'    => [
                $fila('A15075062', 'INDUSTRIA DE DISENO TEXTIL SA', 'ARTEIXO', '1985-06-12'),
                $fila('B00000001', 'EMPRESA DE EJEMPLO SANDBOX SL', 'A CORUÑA', '2019-03-04'),
            ],
            'meta'    => [
                'total' => 1234, 'returned' => 2, 'limit' => 100, 'has_more' => true,
                'next_cursor' => 'c2FuZGJveA', 'cost' => 10, 'cost_per_row' => 5, 'truncated' => false, 'filters' => $f,
            ],
        ]);
    }

    // =========================================================================
    // ENDPOINT: POST /api/sandbox/v1/companies/reconcile
    // =========================================================================
    public function reconcile()
    {
        $json  = $this->request->getJSON(true) ?? [];
        $items = [];
        foreach ((array) ($json['items'] ?? []) as $it) {
            if (is_array($it) && isset($it['name'])) {
                $items[] = ['name' => trim((string) $it['name']), 'province' => isset($it['province']) ? (string) $it['province'] : null];
            }
        }
        foreach ((array) ($json['names'] ?? []) as $n) {
            if (is_string($n)) {
                $items[] = ['name' => trim($n), 'province' => null];
            }
        }
        if (!$items) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'Envía un JSON con "names" (lista de nombres) o "items" ([{"name", "province"}]).'], 400);
        }
        if (count($items) > 100) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'Máximo 100 nombres por petición.'], 400);
        }

        $out = [];
        $c = ['match' => 0, 'ambiguous' => 0, 'no_match' => 0, 'invalid' => 0];
        foreach ($items as $it) {
            $row = ['input' => $it];
            $n = self::normalizar($it['name']);
            if (mb_strlen($it['name']) < 3) {
                $row['status'] = 'invalid';
                $row['message'] = 'El nombre debe tener al menos 3 caracteres.';
            } elseif (str_contains($n, 'INDUSTRIA DE DISENO TEXTIL') || $n === 'INDITEX' || $n === 'INDITEX SA') {
                $row['status'] = 'match';
                $row['score'] = 100;
                $row['company'] = ['cif' => 'A15075062', 'name' => 'INDUSTRIA DE DISENO TEXTIL SA', 'province' => 'A CORUÑA', 'status' => 'ACTIVA', 'status_code' => 'ACTIVE', 'score' => 100];
            } elseif (str_contains($n, 'EJEMPLO')) {
                $row['status'] = 'ambiguous';
                $row['candidates'] = [
                    ['cif' => 'B00000001', 'name' => 'EMPRESA DE EJEMPLO SANDBOX SL', 'province' => 'A CORUÑA', 'status' => 'ACTIVA', 'status_code' => 'ACTIVE', 'score' => 78],
                    ['cif' => 'B00000002', 'name' => 'EJEMPLO SANDBOX SERVICIOS SA', 'province' => 'MADRID', 'status' => 'ACTIVA', 'status_code' => 'ACTIVE', 'score' => 74],
                ];
            } else {
                $row['status'] = 'no_match';
            }
            $c[$row['status']]++;
            $out[] = $row;
        }

        return $this->respond([
            'success' => true,
            'data'    => $out,
            'meta'    => [
                'requested' => count($items), 'matched' => $c['match'], 'ambiguous' => $c['ambiguous'],
                'no_match' => $c['no_match'], 'invalid' => $c['invalid'], 'skipped_quota' => 0,
                'cost' => $c['match'], 'thresholds' => ['match' => 85, 'ambiguous' => 70],
                'test_names' => '"Inditex" da match, cualquier nombre con "Ejemplo" da ambiguous y el resto no_match.',
            ],
        ]);
    }

    // =========================================================================
    // ENDPOINTS: /api/sandbox/v1/watchlist (GET, POST, DELETE /{cif}, GET /events)
    // Sin estado: responde como si ya vigilaras A15075062.
    // =========================================================================
    public function watchlistList()
    {
        return $this->respond([
            'success' => true,
            'data'    => [['cif' => 'A15075062', 'name' => 'INDUSTRIA DE DISENO TEXTIL SA', 'added_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]],
            'meta'    => ['total' => 1, 'watch_limit' => 100, 'page' => 1, 'limit' => 100, 'has_more' => false],
        ]);
    }

    public function watchlistAdd()
    {
        $json = $this->request->getJSON(true) ?? [];
        $cifs = $json['cifs'] ?? null;
        if (!is_array($cifs) || !$cifs) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'Envía un JSON con el array "cifs", por ejemplo {"cifs": ["A15075062"]}.'], 400);
        }
        $res = ['added' => [], 'already_watching' => [], 'not_found' => [], 'invalid' => [], 'rejected_over_limit' => []];
        foreach ($cifs as $raw) {
            $cif = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $raw));
            if ($cif === 'A15075062') {
                $res['already_watching'][] = $cif;
            } elseif ($cif === 'B00000000') {
                $res['not_found'][] = $cif;
            } elseif (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/', $cif)) {
                $res['added'][] = $cif;
            } else {
                $res['invalid'][] = (string) $raw;
            }
        }

        return $this->respond([
            'success' => true,
            'data'    => $res,
            'meta'    => ['total' => 1 + count($res['added']), 'watch_limit' => 100],
        ]);
    }

    public function watchlistRemove($cif = null)
    {
        $cif = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $cif));
        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'NOT_WATCHING', 'message' => 'Esa empresa no está en tu vigilancia.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data'    => ['cif' => $cif, 'removed' => true],
            'meta'    => ['total' => 0, 'watch_limit' => 100],
        ]);
    }

    public function watchlistEvents()
    {
        $since = (string) ($this->request->getGet('since') ?: date('Y-m-d', strtotime('-7 days')));
        $d1 = date('Y-m-d', strtotime('-5 days'));
        $d2 = date('Y-m-d', strtotime('-2 days'));

        return $this->respond([
            'success' => true,
            'data'    => [
                [
                    'id' => 'borme_act:sandbox1', 'type' => 'borme_act', 'date' => $d1, 'cif' => 'A15075062',
                    'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                    'data' => ['act_types' => 'Nombramientos', 'description' => 'Nombramientos. Consejero: EJEMPLO SANDBOX PERSONA.', 'url_pdf' => 'https://www.boe.es/borme/'],
                ],
                [
                    'id' => 'risk_level_change:A15075062:' . $d2, 'type' => 'risk_level_change', 'date' => $d2, 'cif' => 'A15075062',
                    'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                    'data' => ['from' => 'BAJO', 'to' => 'MEDIO', 'model_change' => false],
                ],
            ],
            'meta'    => ['since' => $since, 'types' => ['borme_act', 'status_change', 'risk_level_change'], 'total' => 2, 'page' => 1, 'limit' => 100, 'has_more' => false],
        ]);
    }
}
