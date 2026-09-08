<?php

namespace App\Services;

use App\Models\CompanyModel;
use App\Models\UserEventsModel;
use Config\Database;

class CompanyRiskService
{
    protected CompanyModel $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * Limpia y normaliza el CIF introducido (soporta formatos como B-12345678, B12345678, etc.).
     */
    public function cleanCif(string $cif): string
    {
        $raw = strtoupper(trim($cif));
        
        // Eliminar espacios, puntos y guiones
        $clean = preg_replace('/[\s\-\.]/', '', $raw);

        // Si empieza por patrón típico CIF (1 letra + 7 dígitos + 1 carácter de control)
        if (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]/', $clean, $m)) {
            return $m[0];
        }

        // Fallback: caracteres alfanuméricos en mayúsculas
        return preg_replace('/[^A-Z0-9]/', '', $clean);
    }

    /**
     * Calcula la cuota de consultas de perfil de riesgo para el usuario en el mes actual.
     */
    public function getRiskViewQuota(int $userId, string $cif): array
    {
        if ($userId <= 0) {
            return [
                'allowed'       => false,
                'reason'        => 'unauthenticated',
                'is_subscriber' => false,
                'views_used'    => 0,
                'views_limit'   => 3
            ];
        }

        $db = Database::connect();

        // 1. Verificar si tiene suscripción activa ESPECÍFICA para el producto de Riesgo / Solvencia (risk_pro o bundle)
        $activeRiskSub = $db->table('user_subscriptions')
            ->select('user_subscriptions.*, api_plans.name as plan_name, api_plans.slug as plan_slug')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
            ->where('user_subscriptions.user_id', $userId)
            ->groupStart()
                ->where('api_plans.product_type', 'risk')
                ->orWhere('api_plans.product_type', 'bundle')
                ->orWhere('api_plans.slug', 'risk_pro')
            ->groupEnd()
            ->groupStart()
                ->where('user_subscriptions.status', 'active')
                ->orGroupStart()
                    ->where('user_subscriptions.status', 'canceled')
                    ->where('user_subscriptions.current_period_end >', date('Y-m-d H:i:s'))
                ->groupEnd()
            ->groupEnd()
            ->orderBy('FIELD(user_subscriptions.status, "active", "canceled")', 'ASC', false)
            ->orderBy('user_subscriptions.current_period_end', 'DESC')
            ->get()->getRow();

        if ($activeRiskSub) {
            return [
                'allowed'       => true,
                'is_subscriber' => true,
                'plan_name'     => $activeRiskSub->plan_name ?? 'Solvencia Pro',
                'views_used'    => 0,
                'views_limit'   => 'unlimited'
            ];
        }

        // 2. Para usuarios gratuitos o de otros planes (API Pro, etc.): límite de 3 empresas distintas al mes natural
        $startOfMonth = date('Y-m-01 00:00:00');
        
        $viewsThisMonth = $db->table('user_events')
            ->select('trigger_type')
            ->where('user_id', $userId)
            ->where('event_type', 'view_risk_profile')
            ->where('created_at >=', $startOfMonth)
            ->groupBy('trigger_type')
            ->get()->getResultArray();

        $distinctCifs = array_filter(array_map('trim', array_column($viewsThisMonth, 'trigger_type')));
        $distinctCount = count($distinctCifs);
        $cleanCif = $this->cleanCif($cif);
        $alreadyViewed = (!empty($cleanCif) && in_array($cleanCif, array_map('strtoupper', $distinctCifs)));

        if ($alreadyViewed) {
            return [
                'allowed'          => true,
                'is_subscriber'    => false,
                'already_unlocked' => true,
                'views_used'       => $distinctCount,
                'views_limit'      => 3
            ];
        }

        if ($distinctCount < 3) {
            // Registrar el evento de consulta para esta nueva empresa
            if (!empty($cleanCif)) {
                $userEventsModel = new UserEventsModel();
                $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);
            }

            return [
                'allowed'          => true,
                'is_subscriber'    => false,
                'already_unlocked' => false,
                'views_used'       => $distinctCount + 1,
                'views_limit'      => 3
            ];
        }

        // Límite de 3 consultas alcanzado
        return [
            'allowed'       => false,
            'reason'        => 'limit_reached',
            'is_subscriber' => false,
            'views_used'    => 3,
            'views_limit'   => 3
        ];
    }

    /**
     * Obtiene el conjunto completo de datos para la evaluación del perfil de riesgo.
     */
    public function getRiskData(string $cif, ?int $userId = null): array
    {
        $cleanCif = $this->cleanCif($cif);
        if (empty($cleanCif)) {
            return [
                'found'       => false,
                'cleanCif'    => '',
                'company'     => null,
                'riskProfile' => null,
                'error'       => 'EMPTY_CIF'
            ];
        }

        $db = Database::connect();

        // 1. Buscar empresa
        $company = $this->companyModel->getByCif($cleanCif);
        if (!$company && $cleanCif !== strtoupper(trim($cif))) {
            $company = $this->companyModel->getByCif(strtoupper(trim($cif)));
        }
        if (!$company) {
            $company = $this->companyModel->where('cif', $cleanCif)->first();
        }

        if (!$company) {
            return [
                'found'       => false,
                'cleanCif'    => $cleanCif,
                'company'     => null,
                'riskProfile' => null,
                'error'       => 'COMPANY_NOT_FOUND'
            ];
        }

        $targetCif = (string)($company['cif'] ?? $cleanCif);

        // 2. Buscar perfil de riesgo calculado
        $riskRow = $db->table('company_risk_profiles')->where('cif', $targetCif)->get()->getRowArray();
        $riskProfile = null;
        if ($riskRow) {
            $riskProfile = $riskRow;
            if (!empty($riskProfile['risk_profile'])) {
                $riskProfile['data'] = json_decode($riskProfile['risk_profile'], true);
            }
        }

        // 3. Contratos públicos y subvenciones
        $contracts = $db->table('company_contracts')
            ->where('company_cif', $targetCif)
            ->orderBy('fecha_adjudicacion', 'DESC')
            ->get()->getResultArray();

        $subsidies = $db->table('company_subsidies')
            ->where('company_cif', $targetCif)
            ->orderBy('fecha_concesion', 'DESC')
            ->get()->getResultArray();

        // 4. Cuota del usuario
        $effectiveUserId = (int)($userId ?? session('user_id') ?? 0);
        $riskQuota = $this->getRiskViewQuota($effectiveUserId, $targetCif);

        return [
            'found'       => true,
            'cleanCif'    => $targetCif,
            'company'     => $company,
            'riskProfile' => $riskProfile,
            'contracts'   => $contracts,
            'subsidies'   => $subsidies,
            'riskQuota'   => $riskQuota
        ];
    }
}
