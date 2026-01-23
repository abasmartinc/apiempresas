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
                    'plan_name'            => $freePlan->name,
                    'monthly_quota'        => $freePlan->monthly_quota,
                    'status'               => 'inactive',
                    'current_period_start' => null,
                    'current_period_end'   => null
                ];
            }
        }
        
        $data['plan'] = $plan;

        // KPIs
        $data['api_request_total_month'] = $this->ApiRequestsModel->countRequestsForMonth(date('Y-m'), ['user_id' => $userId]);
        $data['api_request_total_today'] = $this->ApiRequestsModel->countRequestsForDay(date('Y-m-d'), ['user_id' => $userId]);

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

        return view('usage', $data);
    }

}

