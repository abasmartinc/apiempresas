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
                'views_limit'   => 3,
                'risk_credits'  => 0,
            ];
        }

        $db = Database::connect();
        $cleanCif = $this->cleanCif($cif);

        // Obtener datos del usuario (admin y créditos disponibles)
        $userRow = $db->table('users')->select('is_admin, risk_credits')->where('id', $userId)->get()->getRow();
        $userRiskCredits = (int)($userRow->risk_credits ?? 0);
        $isAdmin = (bool)session('is_admin') || ($userRow && (int)$userRow->is_admin === 1);

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

        if ($activeRiskSub || $isAdmin) {
            // Registrar siempre la consulta para que aparezca en el historial y se mantenga al recargar
            if (!empty($cleanCif)) {
                $userEventsModel = new UserEventsModel();
                $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);
            }

            return [
                'allowed'       => true,
                'is_subscriber' => true,
                'plan_name'     => $activeRiskSub ? ($activeRiskSub->plan_name ?? 'Solvencia Pro') : 'Solvencia Pro (Admin)',
                'views_used'    => 0,
                'views_limit'   => 'unlimited',
                'risk_credits'  => $userRiskCredits,
            ];
        }

        // 2. Comprobar si esta empresa ya ha sido consultada/desbloqueada por el usuario alguna vez
        $alreadyViewedEver = false;
        if (!empty($cleanCif)) {
            $alreadyViewedEver = $db->table('user_events')
                ->where('user_id', $userId)
                ->where('event_type', 'view_risk_profile')
                ->where('trigger_type', $cleanCif)
                ->countAllResults() > 0;
        }

        // Consultas de empresas distintas en el mes natural actual
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

        if ($alreadyViewedEver) {
            // Empresa ya desbloqueada: actualizar fecha registrando evento, no consume cuota ni crédito
            if (!empty($cleanCif)) {
                $userEventsModel = new UserEventsModel();
                $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);
            }

            return [
                'allowed'          => true,
                'is_subscriber'    => false,
                'already_unlocked' => true,
                'from_pack'        => false,
                'views_used'       => $distinctCount,
                'views_limit'      => 3,
                'risk_credits'     => $userRiskCredits,
            ];
        }

        // 3. Si aún tiene consultas gratis del mes (hasta 3 empresas)
        if ($distinctCount < 3) {
            if (!empty($cleanCif)) {
                $userEventsModel = new UserEventsModel();
                $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);
            }

            return [
                'allowed'          => true,
                'is_subscriber'    => false,
                'already_unlocked' => false,
                'from_pack'        => false,
                'views_used'       => $distinctCount + 1,
                'views_limit'      => 3,
                'risk_credits'     => $userRiskCredits,
            ];
        }

        // 4. Si ha alcanzado el límite mensual gratuito pero tiene créditos comprados (Tripwire pack)
        if ($userRiskCredits > 0) {
            if (!empty($cleanCif)) {
                // Descontar 1 crédito de forma atómica
                $db->table('users')
                    ->where('id', $userId)
                    ->where('risk_credits >', 0)
                    ->set('risk_credits', 'risk_credits - 1', false)
                    ->update();

                $userEventsModel = new UserEventsModel();
                $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);
            }

            $remainingCredits = max(0, $userRiskCredits - 1);

            return [
                'allowed'          => true,
                'is_subscriber'    => false,
                'already_unlocked' => false,
                'from_pack'        => true,
                'credits_remaining'=> $remainingCredits,
                'risk_credits'     => $remainingCredits,
                'views_used'       => $distinctCount + 1,
                'views_limit'      => 3,
            ];
        }

        // 5. Límite de 3 consultas alcanzado y sin créditos
        return [
            'allowed'       => false,
            'reason'        => 'limit_reached',
            'is_subscriber' => false,
            'views_used'    => $distinctCount,
            'views_limit'   => 3,
            'risk_credits'  => 0,
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
        if (!$company && !empty($cif)) {
            $rawTerm = trim($cif);
            $company = $this->companyModel->where('company_name', $rawTerm)->first();
            if (!$company && strlen($rawTerm) >= 4) {
                $company = $this->companyModel->like('company_name', $rawTerm)->first();
            }
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

        if (empty($company['name']) && !empty($company['company_name'])) {
            $company['name'] = $company['company_name'];
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
