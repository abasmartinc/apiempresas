<?php

namespace App\Controllers;


use App\Models\ApikeysModel;
use App\Models\ApiRequestsModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;

class Usage extends BaseController
{
    /** @var UserModel */
    protected $userModel;
    protected $ApikeysModel;
    protected $UsersuscriptionsModel;
    protected $ApiRequestsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
        $this->ApiRequestsModel = new ApiRequestsModel();
    }
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        $userId = (int) session('user_id');
        $user   = $this->userModel->find($userId);

        $data['user']    = $user;
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();
        
        // 1. Intentar plan activo
        $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        
        // 2. Fallback: cualquier suscripción (ej. trialing, canceled)
        if (!$plan) {
            $plan = $this->UsersuscriptionsModel->getUserSubscriptionWithPlan($userId);
        }

        // 3. Fallback: Forzar lectura del plan FREE (ID 1) si no hay nada
        if (!$plan) {
            $apiPlanModel = new \App\Models\ApiPlanModel();
            $freePlan = $apiPlanModel->find(1);
            if ($freePlan) {
                // Objeto dummy para la vista
                $plan = (object)[
                    'plan_id'              => 1,
                    'plan_name'            => $freePlan->name,
                    'monthly_quota'        => $freePlan->monthly_quota,
                    'status'               => 'inactive',
                    'current_period_start' => null,
                    'current_period_end'   => null
                ];
            }
        }

        // --- LÓGICA DE MONEDERO PREPAGO (Igual que Dashboard) ---
        $db = \Config\Database::connect();
        $walletBalance = 0;
        $walletTotal = 0;
        
        if ($db->tableExists('user_wallets')) {
            $walletRow = $db->table('user_wallets')->where('user_id', $userId)->get()->getRow();
            if ($walletRow) {
                $walletBalance = (int)$walletRow->balance;
            }
            
            $spentRow = $db->table('api_usage_daily')->selectSum('credits_used', 'total')->where('user_id', $userId)->get()->getRow();
            $walletSpent = (int)($spentRow->total ?? 0);
            
            $walletTotal = $walletBalance + $walletSpent;
        }
        
        // Si el usuario no tiene plan de pago mensual, pero tiene un bono, lo tratamos como Bono
        $isFreeOrNull = (!$plan || !isset($plan->plan_id) || $plan->plan_id == 1);
        if ($isFreeOrNull && isset($walletTotal) && $walletTotal > 0) {
            $plan = (object)[
                'plan_name'            => 'Bono API Prepago',
                'monthly_quota'        => null, // Límite mensual no aplica
                'status'               => 'active',
                'current_period_start' => null,
                'current_period_end'   => null,
                'is_bonus'             => true,
                'wallet_balance'       => $walletBalance,
                'wallet_spent'         => $walletSpent ?? 0
            ];
        }
        
        $data['plan'] = $plan;

        // KPIs: Aislar consumo según suscripción/plan actual
        $db = \Config\Database::connect();
        $usageMonth = 0;
        $usageToday = 0;

        if ($plan && isset($plan->plan_id) && !isset($plan->is_bonus) && $plan->plan_id != 1) {
            // Usuario con plan mensual Activo (Pro, Business)
            $sumRow = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('plan_id', $plan->plan_id)
                ->where('date >=', date('Y-m-01'))
                ->get()->getRow();
            
            $usageMonth = ($sumRow->total ?? 0) + ($sumRow->credits_total ?? 0);

            $todayRow = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('plan_id', $plan->plan_id)
                ->where('date', date('Y-m-d'))
                ->get()->getRow();
            
            $usageToday = ($todayRow->total ?? 0) + ($todayRow->credits_total ?? 0);
        } else {
            // Usuario Free o Usuario con Bono
            $sumRow = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('date >=', date('Y-m-01'))
                ->get()->getRow();
            
            $usageMonth = ($sumRow->total ?? 0) + ($sumRow->credits_total ?? 0);

            $todayRow = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('date', date('Y-m-d'))
                ->get()->getRow();
            
            $usageToday = ($todayRow->total ?? 0) + ($todayRow->credits_total ?? 0);
        }

        $data['api_request_total_month'] = $usageMonth;
        $data['api_request_total_today'] = $usageToday;

        // Log usage page visit
        log_activity('usage_visit');

        // ===== RANGO para gráfico =====
        $range = $this->request->getGet('range') ?: '30';

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        if ($range === 'today') {
            $from = date('Y-m-d');
            $to   = date('Y-m-d');
        } elseif ($range === '7') {
            $from = date('Y-m-d', strtotime('-6 days'));
            $to   = date('Y-m-d');
        } elseif ($range === '30') {
            $from = date('Y-m-d', strtotime('-29 days'));
            $to   = date('Y-m-d');
        } else {
            // custom: si falta algo, fallback a 30
            if (!$from || !$to) {
                $from = date('Y-m-d', strtotime('-29 days'));
                $to   = date('Y-m-d');
            }
        }

        // Serie DB: solo días con requests
        $rows = $this->ApiRequestsModel->getDailyCountsForRange($from, $to, ['user_id' => $userId]);

        // Rellenar días faltantes con 0 (para una línea continua)
        $map = [];
        foreach ($rows as $r) {
            $map[$r['day']] = (int)$r['total'];
        }

        $labels = [];
        $values = [];

        $cursor = strtotime($from);
        $end    = strtotime($to);

        while ($cursor <= $end) {
            $day = date('Y-m-d', $cursor);
            $labels[] = $day;
            $values[] = $map[$day] ?? 0;
            $cursor = strtotime('+1 day', $cursor);
        }

        $data['chart_labels'] = $labels; // ['2026-01-01', ...]
        $data['chart_values'] = $values; // [0, 3, 1, ...]

        // ===== ENDPOINT BREAKDOWN =====
        $currentMonth = date('Y-m');
        $endpointBreakdown = $this->ApiRequestsModel->getEndpointBreakdownForMonth($currentMonth, ['user_id' => $userId]);
        
        // Add today's count for each endpoint
        $today = date('Y-m-d');
        foreach ($endpointBreakdown as &$ep) {
            $ep['today'] = $this->ApiRequestsModel->getEndpointCountForDay($ep['endpoint'], $today, ['user_id' => $userId]);
        }
        unset($ep); // Break reference
        
        $data['endpoint_breakdown'] = $endpointBreakdown;

        // ===== REGISTRO RECIENTE =====
        $db = \Config\Database::connect();
        $recentRequests = $db->table('api_requests')
            ->select('api_requests.endpoint, api_requests.search_term, api_requests.status_code, api_requests.duration_ms, api_requests.created_at, api_plans.name as plan_name')
            ->join('user_subscriptions', 'user_subscriptions.id = api_requests.subscription_id', 'left')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id', 'left')
            ->where('api_requests.user_id', $userId)
            ->orderBy('api_requests.created_at', 'DESC')
            ->limit(50)
            ->get()->getResultArray();
            
        $isBonusUser = isset($plan->is_bonus) && $plan->is_bonus;
        foreach ($recentRequests as &$r) {
            if (empty($r['plan_name']) || (strtolower($r['plan_name']) === 'free' && $isBonusUser)) {
                $r['plan_name'] = 'Bono Prepago';
            }
        }
        $data['recent_requests'] = $recentRequests;

        return view('usage', $data);
    }

}

