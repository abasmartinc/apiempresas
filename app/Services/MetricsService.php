<?php

namespace App\Services;

use Config\Database;

class MetricsService
{
    protected $db;
    protected $openAiService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->openAiService = new OpenAiService();
    }

    /**
     * Get all consolidated metrics with caching
     */
    public function getAllMetrics(): array
    {
        $cacheKey = 'admin_api_metrics';
        $cachedData = cache($cacheKey);

        if ($cachedData) {
            return $cachedData;
        }

        $metricsData = [
            'funnel'     => $this->getFunnelMetrics(),
            'revenue'    => $this->getRevenueMetrics(),
            'activation' => $this->getActivationMetrics(),
        ];

        // Add AI Analysis
        $aiAnalysis = $this->getAiAnalysis($metricsData);

        $metrics = array_merge($metricsData, [
            'ai_analysis' => $aiAnalysis,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Cache for 10 minutes
        cache()->save($cacheKey, $metrics, 600);

        return $metrics;
    }

    /**
     * A. FUNNEL METRICS
     */
    protected function getFunnelMetrics(): array
    {
        // 1. Signup -> 1st Request
        $totalUsers = $this->db->table('users')->countAllResults();
        
        $usersWithRequests = $this->db->table('users u')
            ->join('api_requests r', 'u.id = r.user_id')
            ->select('COUNT(DISTINCT u.id) as count')
            ->get()->getRowArray()['count'] ?? 0;
            
        $conversionToUse = ($totalUsers > 0) ? round(($usersWithRequests / $totalUsers) * 100, 2) : 0;

        // 2. 1st request -> pago
        $paidUsersWithRequests = $this->db->table('users u')
            ->join('api_requests r', 'u.id = r.user_id')
            ->join('user_subscriptions s', 'u.id = s.user_id')
            ->join('api_plans p', 's.plan_id = p.id')
            ->whereIn('p.slug', ['pro', 'business'])
            ->where('s.status', 'active')
            ->select('COUNT(DISTINCT u.id) as count')
            ->get()->getRowArray()['count'] ?? 0;
        
        $conversionToPaid = ($usersWithRequests > 0) ? round(($paidUsersWithRequests / $usersWithRequests) * 100, 2) : 0;

        // 3. Tiempo medio hasta pago (en horas)
        $avgTimeRaw = $this->db->query("
            SELECT AVG(TIMESTAMPDIFF(HOUR, u.created_at, s.created_at)) as avg_hours
            FROM users u
            JOIN user_subscriptions s ON u.id = s.user_id
            JOIN api_plans p ON s.plan_id = p.id
            WHERE p.slug IN ('pro', 'business')
            AND s.id = (SELECT MIN(id) FROM user_subscriptions WHERE user_id = u.id AND status != 'trialing')
        ")->getRowArray();
        
        $avgTimePayment = round($avgTimeRaw['avg_hours'] ?? 0, 1);

        return [
            'signup_to_request_pct' => $conversionToUse,
            'request_to_paid_pct'   => $conversionToPaid,
            'avg_time_to_paid'      => $avgTimePayment,
        ];
    }

    /**
     * B. REVENUE METRICS
     */
    protected function getRevenueMetrics(): array
    {
        // 4. ARPU (Income per paid user)
        $revenueData = $this->db->table('invoices')
            ->where('status', 'paid')
            ->selectSum('total_amount', 'total')
            ->select('COUNT(DISTINCT user_id) as users_count')
            ->get()->getRowArray();
        
        $totalRevenue = $revenueData['total'] ?? 0;
        $totalPaidUsers = $revenueData['users_count'] ?? 0;
        $arpu = ($totalPaidUsers > 0) ? round($totalRevenue / $totalPaidUsers, 2) : 0;

        // 5. MRR (Current Monthly Recurring Revenue)
        $mrrData = $this->db->query("
            SELECT SUM(
                CASE 
                    WHEN p.price_monthly > 0 THEN p.price_monthly 
                    WHEN p.price_annual > 0 THEN p.price_annual / 12 
                    ELSE 0 
                END
            ) as mrr
            FROM user_subscriptions s
            JOIN api_plans p ON s.plan_id = p.id
            WHERE s.status = 'active' AND p.slug IN ('pro', 'business')
        ")->getRowArray();
        
        $mrr = round($mrrData['mrr'] ?? 0, 2);

        // 6. Expansión (Upsells Pro -> Business)
        $expansionCount = $this->db->query("
            SELECT COUNT(DISTINCT s1.user_id) as count
            FROM user_subscriptions s1
            JOIN api_plans p1 ON s1.plan_id = p1.id
            JOIN user_subscriptions s2 ON s1.user_id = s2.user_id
            JOIN api_plans p2 ON s2.plan_id = p2.id
            WHERE p1.slug = 'business' AND s1.status = 'active'
            AND p2.slug = 'pro' AND s2.status = 'canceled'
        ")->getRowArray()['count'] ?? 0;

        return [
            'arpu'            => $arpu,
            'mrr'             => $mrr,
            'expansion_count' => $expansionCount,
        ];
    }

    /**
     * C. ACTIVATION METRICS
     */
    protected function getActivationMetrics(): array
    {
        // 7. % usuarios que usan la API (Same as Signup -> 1st Request)
        $totalUsers = $this->db->table('users')->countAllResults();
        
        $usersWithRequests = $this->db->table('users u')
            ->join('api_requests r', 'u.id = r.user_id')
            ->select('COUNT(DISTINCT u.id) as count')
            ->get()->getRowArray()['count'] ?? 0;
        
        $activationPct = ($totalUsers > 0) ? round(($usersWithRequests / $totalUsers) * 100, 2) : 0;

        // 8. % usuarios que llegan al 20% del Free
        // Threshold: 20 requests (as requested by user previously, but let's be dynamic if possible)
        // Defincion: Usuarios que en el mes actual alcanzan al menos el 20% de la cuota mensual del plan Free
        $currentMonth = date('Y-m');
        
        $activeFreeUsersCount = $this->db->query("
            SELECT COUNT(*) as count
            FROM (
                SELECT user_id, SUM(requests_count) as total 
                FROM api_usage_daily 
                WHERE date LIKE '{$currentMonth}%' 
                GROUP BY user_id
            ) usage_data
            JOIN user_subscriptions s ON usage_data.user_id = s.user_id
            JOIN api_plans p ON s.plan_id = p.id
            WHERE p.slug = 'free' AND usage_data.total >= (p.monthly_quota * 0.2)
        ")->getRowArray()['count'] ?? 0;
        
        $totalFreeUsers = $this->db->table('user_subscriptions s')
            ->join('api_plans p', 's.plan_id = p.id')
            ->where('p.slug', 'free')
            ->countAllResults();

        $thresholdActivationPct = ($totalFreeUsers > 0) ? round(($activeFreeUsersCount / $totalFreeUsers) * 100, 2) : 0;

        return [
            'active_users_pct'    => $activationPct,
            'threshold_20_pct'    => $thresholdActivationPct,
        ];
    }

    /**
     * D. AI ANALYSIS
     */
    protected function getAiAnalysis(array $metrics): array
    {
        $prompt = "Actúa como un analista experto en crecimiento de SaaS y monetización de APIs. 
        Analiza las siguientes métricas internas de nuestra plataforma APIEmpresas y genera un informe estratégico.

        MÉTRICAS:
        " . json_encode($metrics, JSON_PRETTY_PRINT) . "

        REGLAS:
        1. Sé crítico pero constructivo.
        2. Si el 'signup_to_request_pct' es bajo, sugiere mejoras en onboarding.
        3. Si el 'activation' es alto pero el 'revenue' bajo, sugiere estrategias de upsell.
        4. No inventes datos. Limítate a interpretar.
        5. Habla siempre en español.

        FORMATO DE RESPUESTA (JSON estricto):
        {
          \"summary\": \"Resumen ejecutivo breve (máximo 2 líneas)\",
          \"conclusions\": [\"Conclusión 1\", \"Conclusión 2\", \"Conclusión 3\"],
          \"action_plan\": [\"Acción 1\", \"Acción 2\", \"Acción 3\"]
        }";

        $messages = [
            ['role' => 'system', 'content' => 'Eres un analista de negocios experto que devuelve respuestas exclusivamente en formato JSON.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->openAiService->getChatResponse($messages, [
            'response_format' => ['type' => 'json_object']
        ]);

        try {
            $analysis = json_decode($response, true);
            
            // Validar estructura mínima
            if (!isset($analysis['summary']) || !isset($analysis['conclusions'])) {
                throw new \Exception("Formato de respuesta IA inválido");
            }

            return $analysis;
        } catch (\Exception $e) {
            return [
                'summary' => 'No se pudo generar el análisis automático en este momento.',
                'conclusions' => ['Error en la interpretación de datos por parte de la IA.'],
                'action_plan' => ['Revisar la conexión con el servicio de OpenAI.']
            ];
        }
    }
}
