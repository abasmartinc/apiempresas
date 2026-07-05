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
            ->select('api_requests.endpoint, api_requests.search_term, api_requests.status_code, api_requests.duration_ms, api_requests.created_at, api_requests.request_id, api_requests.ip_address, api_requests.user_agent, api_requests.http_method, api_plans.name as plan_name')
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

    public function getRequestDetails($reqId)
    {
        $db = \Config\Database::connect();
        $userId = (int) session('user_id');
        $req = $db->table('api_requests')
            ->where('request_id', $reqId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if ($req) {
            return $this->response->setJSON(['success' => true, 'data' => $req]);
        }
        return $this->response->setJSON(['success' => false]);
    }

    public function getLogsAjax()
    {
        $db = \Config\Database::connect();
        $userId = (int) session('user_id');
        session_write_close();

        $page = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 15;
        $search = $this->request->getGet('search');
        $endpoint = $this->request->getGet('endpoint');
        $status = $this->request->getGet('status');

        $builder = $db->table('api_requests')
            ->select('api_requests.endpoint, api_requests.search_term, api_requests.status_code, api_requests.duration_ms, api_requests.created_at, api_requests.request_id, api_requests.ip_address, api_requests.user_agent, api_requests.http_method, api_plans.name as plan_name')
            ->join('user_subscriptions', 'user_subscriptions.id = api_requests.subscription_id', 'left')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id', 'left')
            ->where('api_requests.user_id', $userId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('api_requests.search_term', $search)
                ->orLike('api_requests.request_id', $search)
                ->groupEnd();
        }

        if (!empty($endpoint)) {
            $builder->like('api_requests.endpoint', $endpoint);
        }

        if ($status === 'success') {
            $builder->where('api_requests.status_code', 200);
        } elseif ($status === 'error') {
            $builder->where('api_requests.status_code !=', 200);
        }

        $countBuilder = clone $builder;
        $totalRecords = $countBuilder->countAllResults();
        $totalPages = ceil($totalRecords / $limit);
        if ($totalPages == 0) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $limit;

        $requests = $builder->orderBy('api_requests.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResultArray();

        $UsersuscriptionsModel = new \App\Models\UsersuscriptionsModel();
        $plan = $UsersuscriptionsModel->getActivePlanByUserId($userId);
        if (!$plan) {
            $plan = $UsersuscriptionsModel->getUserSubscriptionWithPlan($userId);
        }
        $isBonusUser = isset($plan->is_bonus) && $plan->is_bonus;

        foreach ($requests as &$r) {
            if (empty($r['plan_name']) || (strtolower($r['plan_name']) === 'free' && $isBonusUser)) {
                $r['plan_name'] = 'Bono Prepago';
            }
            $r['short_endpoint'] = str_replace('/apiempresas/api/v1', '', $r['endpoint']);
            $r['date_display'] = date('d/m/Y H:i', strtotime($r['created_at']));
            $r['date_iso'] = date('c', strtotime($r['created_at']));
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $requests,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'limit' => $limit
            ]
        ]);
    }
}

