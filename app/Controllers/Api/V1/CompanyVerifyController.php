<?php

namespace App\Controllers\Api\V1;

use App\Models\CompanyModel;
use App\Services\ApiCompanyEnricher;
use App\Services\ApiVerifyService as V;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;

/**
 * GET /api/v1/companies/verify (28-09-2026). Verificación KYB en una llamada.
 * Pro (o saldo): comprobaciones básicas. Business: además, nivel de riesgo. Coste: 2 consultas.
 */
class CompanyVerifyController extends BaseApiController
{
    protected $format = 'json';

    public function __construct()
    {
        helper(['api', 'company']);
    }

    #[OA\Get(
        path: "/api/v1/companies/verify",
        summary: "Verificación KYB",
        description: "En una llamada: si la empresa existe y está operativa (status_code), si el nombre coincide con la razón social, si la persona que firma es administrador vigente, si el NIF-IVA está en VIES y las alertas. Devuelve decision_hint: pass, review o fail. En Business añade el nivel de riesgo. **Coste:** 2 consultas de tu cuota (o 2 créditos). Los errores no consumen.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\Parameter(name: "cif", in: "query", required: true, description: "CIF de la empresa", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "name", in: "query", required: false, description: "Razón social que te han dado, para compararla con la oficial.", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "person", in: "query", required: false, description: "Nombre y apellidos de quien firma, para comprobar que es administrador vigente.", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "vat", in: "query", required: false, description: "Si es true, comprueba el NIF-IVA intracomunitario en VIES.", schema: new OA\Schema(type: "boolean"))]
    #[OA\Response(response: 200, description: "Resultado de la verificación")]
    #[OA\Response(response: 403, description: "Plan Free sin saldo")]
    #[OA\Response(response: 404, description: "Empresa no encontrada")]
    public function index()
    {
        $meta = \App\Filters\ApiKeyFilter::$apiMeta;
        $planSlug = strtolower((string) ($meta['plan_slug'] ?? 'free'));
        $access = strtolower((string) ($meta['access_slug'] ?? $planSlug));
        if (!in_array($access, ['pro', 'business', 'enterprise'], true)) {
            return $this->respond([
                'success' => false,
                'error'   => 'PLAN_RESTRICTION',
                'message' => 'La verificación KYB requiere un plan Pro o Business.',
                'upsell_opportunities' => [
                    'mensaje'     => 'Comprueba en una llamada que la empresa existe y opera, que el nombre coincide, que quien firma es administrador y que el NIF está en VIES.',
                    'upgrade_url' => site_url('billing?plan=pro&source=api_403_verify'),
                ],
            ], ResponseInterface::HTTP_FORBIDDEN);
        }
        $esBusiness = in_array($planSlug, ['business', 'enterprise'], true);

        $cif = ApiCompanyEnricher::normalizeCif((string) $this->request->getGet('cif'));
        if ($cif === '') {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "cif" es obligatorio.'], ResponseInterface::HTTP_BAD_REQUEST);
        }
        if (in_array($cif, ['B99999999', 'B12345678', 'B12345674', 'A12345678', 'B00000000'], true)) {
            return $this->respond(['success' => false, 'error' => 'FAKE_CIF_NOT_ALLOWED', 'message' => 'Este parece ser un CIF de prueba. Usa uno real, por ejemplo el de Mercadona (A46103834).'], ResponseInterface::HTTP_BAD_REQUEST);
        }
        if (preg_match('/^[0-9]{8}[A-Z]$/', $cif) || preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $cif)) {
            return $this->respond(['success' => false, 'error' => 'AUTONOMO_NOT_SUPPORTED', 'message' => 'No proporcionamos datos de autónomos por motivos de RGPD, únicamente sociedades (CIF).'], ResponseInterface::HTTP_BAD_REQUEST);
        }
        if (!is_valid_cif($cif)) {
            return $this->respond(['success' => false, 'error' => 'INVALID_CIF_FORMAT', 'message' => 'El CIF proporcionado no tiene un formato válido.'], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $company = (new CompanyModel())->getByCif($cif, true);
        if (!$company) {
            return $this->respond([
                'success'       => false,
                'error'         => 'COMPANY_NOT_FOUND',
                'message'       => 'No consta ninguna sociedad con este CIF.',
                'decision_hint' => 'fail',
            ], ResponseInterface::HTTP_NOT_FOUND);
        }

        $c = ApiCompanyEnricher::enrich([$company], true)[0];
        $companyId = (int) ($c['id'] ?? 0);

        $checks = [];

        $name = trim((string) ($this->request->getGet('name') ?? ''));
        $nameScore = null;
        if ($name !== '') {
            $nameScore = V::nameScore($name, (string) ($c['name'] ?? ''));
            $checks['name'] = ['provided' => $name, 'score' => $nameScore, 'match' => $nameScore >= V::UMBRAL_NOMBRE];
        }

        $person = trim((string) ($this->request->getGet('person') ?? ''));
        $personIsAdmin = null;
        if ($person !== '') {
            $vigentes = $companyId ? (ApiCompanyEnricher::currentAdministrators([$companyId])[$companyId] ?? []) : [];
            $m = V::matchPerson($person, $vigentes);
            $personIsAdmin = $m !== null;
            $checks['person'] = [
                'provided'         => $person,
                'is_current_admin' => $personIsAdmin,
                'matched_name'     => $m['name'] ?? null,
                'position'         => $m['position'] ?? null,
                'since'            => $m['since'] ?? null,
            ];
        }

        $vatValid = null;
        if (filter_var($this->request->getGet('vat'), FILTER_VALIDATE_BOOLEAN)) {
            $vies = V::vies($cif);
            $checks['vat'] = ['vat_number' => 'ES' . $cif] + $vies;
            $vatValid = $vies['checked'] ? $vies['valid'] : null;
        }

        $lastYear = $c['financials']['last_accounts_year'] ?? null;
        $checks['accounts'] = ['last_accounts_year' => $lastYear];

        $risk = null;
        if ($esBusiness) {
            try {
                $r = \Config\Database::connect()->table('company_risk_profiles')
                    ->select('risk_level, risk_score, updated_at')->where('cif', $cif)->get()->getRowArray();
                if ($r) {
                    $risk = ['risk_level' => $r['risk_level'], 'risk_score' => (int) $r['risk_score'], 'updated_at' => $r['updated_at']];
                }
            } catch (\Throwable $e) {
                log_message('error', '[CompanyVerifyController] ' . $e->getMessage());
            }
        }

        // Business: listado de deudores de la AEAT. Desactivado salvo AEAT_DEBTORS_ENABLED=true (ver ApiVerifyService).
        $aeat = null;
        if ($esBusiness) {
            $aeat = V::aeatDebtor($cif);
            if ($aeat !== null) {
                $checks['tax_debt'] = $aeat;
            }
        }

        $d = V::decide([
            'status_code'        => $c['status_code'] ?? 'UNKNOWN',
            'name_score'         => $nameScore,
            'person_is_admin'    => $personIsAdmin,
            'vat_valid'          => $vatValid,
            'risk_level'         => $risk['risk_level'] ?? null,
            'last_accounts_year' => $lastYear,
            'aeat_debtor'        => $aeat,
        ]);

        $data = [
            'cif'           => $cif,
            'exists'        => true,
            'name'          => $c['name'] ?? null,
            'status'        => $c['status'] ?? null,
            'status_code'   => $c['status_code'] ?? null,
            'status_source' => $c['status_source'] ?? null,
            'status_date'   => $c['status_date'] ?? null,
            'checks'        => $checks,
        ];
        if ($esBusiness) {
            $data['risk'] = $risk;
        }
        $data['flags'] = $d['flags'];
        $data['decision_hint'] = $d['decision_hint'];
        $data['checked_at'] = date('c');

        return $this->respond(['success' => true, 'data' => $data]);
    }
}
