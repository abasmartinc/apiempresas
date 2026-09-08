<?php

namespace App\Controllers;

use App\Services\CompanyRiskService;
use CodeIgniter\API\ResponseTrait;

class RiskProfileController extends BaseController
{
    use ResponseTrait;

    protected CompanyRiskService $riskService;

    public function __construct()
    {
        $this->riskService = new CompanyRiskService();
    }

    /**
     * Página principal de la herramienta de Perfil de Riesgo
     * GET /perfil-de-riesgo
     * GET /perfil-de-riesgo?cif=B12345678
     */
    public function index()
    {
        $throttler = service('throttler');
        if ($throttler->check(md5($this->request->getIPAddress() . '_risk_view'), 40, 60) === false) {
            return "Demasiadas solicitudes. Por favor, espera un momento.";
        }

        $cif = trim((string)($this->request->getGet('cif') ?: $this->request->getGet('q') ?: $this->request->getPost('cif') ?: $this->request->getPost('q')));
        $riskData = null;

        if (!empty($cif)) {
            $userId = (int)(session('user_id') ?? 0);
            $riskData = $this->riskService->getRiskData($cif, $userId);
        }

        $title = 'Perfil de Riesgo y Solvencia Mercantil de Empresas | APIEmpresas';
        $excerptText = 'Consulta el semáforo de solvencia, scoring IES (0-100), alertas de quiebra en BORME y depósito de cuentas de cualquier empresa en España mediante su CIF.';
        
        if (!empty($riskData['found']) && !empty($riskData['company']['name'])) {
            $compName = esc($riskData['company']['name']);
            $title = "Perfil de Riesgo y Solvencia: {$compName} ({$riskData['cleanCif']}) | APIEmpresas";
            $excerptText = "Informe de riesgo corporativo y estabilidad para {$compName}. Auditoría registral BORME, solvencia financiera y contratos públicos.";
        }

        $canonical = site_url('perfil-de-riesgo') . (!empty($cif) ? '?cif=' . rawurlencode($cif) : '');

        $viewData = [
            'cif'         => $cif,
            'riskData'    => $riskData,
            'title'       => $title,
            'excerptText' => $excerptText,
            'canonical'   => $canonical,
            'robots'      => !empty($cif) ? 'noindex,follow' : 'index,follow'
        ];

        return view('risk_profile/index', $viewData);
    }

    /**
     * Endpoint AJAX para consulta rápida de CIF sin recarga de página
     * GET /api/perfil-de-riesgo/lookup?cif=B12345678
     */
    public function ajaxLookup()
    {
        $throttler = service('throttler');
        if ($throttler->check(md5($this->request->getIPAddress() . '_risk_ajax'), 40, 60) === false) {
            return $this->fail('Demasiadas solicitudes. Por favor espera un minuto.', 429);
        }

        $cif = trim((string)($this->request->getGet('cif') ?: $this->request->getGet('q')));
        if (empty($cif)) {
            return $this->fail('Debes introducir un CIF válido.', 400);
        }

        $userId = (int)(session('user_id') ?? 0);
        $isLoggedIn = session('logged_in') || $userId > 0;
        $riskData = $this->riskService->getRiskData($cif, $userId);

        if (!$riskData['found']) {
            return $this->respond([
                'success' => false,
                'error'   => 'COMPANY_NOT_FOUND',
                'message' => "No se encontró ninguna empresa con el CIF «" . esc($cif) . "». Comprueba el código e inténtalo de nuevo."
            ]);
        }

        $company = $riskData['company'];
        $riskProfile = $riskData['riskProfile'];
        $riskQuota = $riskData['riskQuota'];
        $contracts = $riskData['contracts'] ?? [];
        $subsidies = $riskData['subsidies'] ?? [];

        if (empty($riskProfile)) {
            return $this->respond([
                'success'       => false,
                'error'         => 'NO_RISK_PROFILE',
                'company'       => $company,
                'message'       => "La empresa «" . esc($company['name']) . "» (" . esc($company['cif']) . ") está registrada en el directorio mercantil, pero su perfil de riesgo algorítmico todavía no ha sido procesado."
            ]);
        }

        // Renderizar el bloque HTML correspondiente según el estado del usuario
        $html = '';
        $redirectPath = 'perfil-de-riesgo?cif=' . urlencode((string)$company['cif']);

        if (!$isLoggedIn) {
            $html = view('partials/company_risk_teaser', [
                'riskProfile'  => $riskProfile,
                'company'      => $company,
                'companyName'  => $company['name'],
                'redirectPath' => $redirectPath
            ]);
        } elseif (!empty($riskQuota) && empty($riskQuota['allowed'])) {
            $html = view('partials/company_risk_paywall', [
                'company'   => $company,
                'riskQuota' => $riskQuota
            ]);
        } else {
            $html = view('partials/company_risk_profile', [
                'riskProfile' => $riskProfile,
                'company'     => $company,
                'contracts'   => $contracts,
                'subsidies'   => $subsidies,
                'riskQuota'   => $riskQuota
            ]);
        }

        return $this->respond([
            'success'      => true,
            'company'      => $company,
            'riskQuota'    => $riskQuota,
            'isLoggedIn'   => $isLoggedIn,
            'html'         => $html,
            'redirectUrl'  => site_url('perfil-de-riesgo?cif=' . urlencode((string)$company['cif']))
        ]);
    }
}
