<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // ====== Medición de duración + request_id ======
        $request->api_t0 = microtime(true);
        $request->api_request_id = bin2hex(random_bytes(16)); // 32 hex chars

        // 1) Leer API key (header recomendado)
        $apiKey = trim((string) $request->getHeaderLine('X-API-KEY'));

        // 2) Alternativa: permitir también "Authorization: Bearer <APIKEY>"
        if ($apiKey === '') {
            $auth = trim((string) $request->getHeaderLine('Authorization'));
            if (stripos($auth, 'Bearer ') === 0) {
                $apiKey = trim(substr($auth, 7));
            }
        }

        if ($apiKey === '') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Falta la API key (X-API-KEY).']);
        }

        // 3) Validar contra DB
        $db = \Config\Database::connect('default');

        $row = $db->table('api_keys')
            ->select('api_keys.id AS api_key_id, api_keys.user_id, api_keys.is_active, users.is_active as user_active')
            ->join('users', 'users.id = api_keys.user_id', 'left')
            ->where('api_keys.api_key', $apiKey)
            ->get()
            ->getRow();

        if (!$row) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'API key inválida']);
        }

        if ((int)$row->is_active !== 1 || (int)$row->user_active !== 1) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'API key inactiva o usuario inactivo']);
        }

        // 4) Registrar uso (opcional)
        try {
            $db->table('api_keys')
                ->where('id', (int)$row->api_key_id)
                ->update(['last_used_at' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:last_used_at] ' . $e->getMessage());
        }

        // 4.1) Resolver suscripción y plan
        $subscriptionId = null;
        $planId         = 1;
        $planSlug       = 'free';

        try {
            if ($db->tableExists('user_subscriptions')) {
                $sub = $db->table('user_subscriptions')
                    ->select('user_subscriptions.id AS subscription_id, user_subscriptions.plan_id, user_subscriptions.status, user_subscriptions.current_period_end, api_plans.slug as plan_slug')
                    ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                    ->where('user_subscriptions.user_id', (int)$row->user_id)
                    ->where('user_subscriptions.status', 'active')
                    ->orderBy('user_subscriptions.id', 'DESC')
                    ->get()
                    ->getRow();

                if ($sub) {
                    $now = date('Y-m-d H:i:s');
                    // Perpetual Free Plan: If slug is 'free', it never expires.
                    // For others, we check the date.
                    if ($sub->plan_slug === 'free' || $sub->current_period_end > $now) {
                        $subscriptionId = (int)$sub->subscription_id;
                        $planId         = (int)$sub->plan_id;
                        $planSlug       = $sub->plan_slug;
                    }
                }
            } elseif ($db->tableExists('usersuscriptions')) {
                $sub = $db->table('usersuscriptions')
                    ->select('usersuscriptions.id AS subscription_id, usersuscriptions.plan_id, usersuscriptions.status, api_plans.slug as plan_slug')
                    ->join('api_plans', 'api_plans.id = usersuscriptions.plan_id')
                    ->where('usersuscriptions.user_id', (int)$row->user_id)
                    ->where('usersuscriptions.status', 'active')
                    ->orderBy('usersuscriptions.id', 'DESC')
                    ->get()
                    ->getRow();

                if ($sub) {
                    // For the legacy/fallback table, we just assume active means valid if found
                    $subscriptionId = (int)$sub->subscription_id;
                    $planId         = (int)$sub->plan_id;
                    $planSlug       = $sub->plan_slug;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:subscription] ' . $e->getMessage());
        }

        // 4.2) Verificar Límites de Consumo
        try {
            $planRow = $db->table('api_plans')
                ->select('monthly_quota')
                ->where('id', (int)$planId)
                ->get()
                ->getRow();
            
            $monthlyQuota = $planRow ? (int)$planRow->monthly_quota : 100;

            $currentMonth = date('Y-m');
            $usageRow = $db->table('api_usage_daily')
                ->selectSum('requests_count')
                ->where('user_id', (int)$row->user_id)
                ->like('date', $currentMonth, 'after')
                ->get()
                ->getRow();

            $currentUsage = $usageRow ? (int)$usageRow->requests_count : 0;

            if ($currentUsage >= $monthlyQuota && (int)$planId !== 7) {
                return service('response')
                    ->setStatusCode(429)
                    ->setJSON([
                        'success' => false,
                        'error'   => 'Quota Exceeded',
                        'message' => 'Has superado el límite mensual de consultas de tu plan (' . $monthlyQuota . ').',
                        'current_usage' => $currentUsage,
                        'upgrade_url' => site_url('billing/manage')
                    ]);
            }

            if ((int)$planId === 7) {
                $this->checkThresholdNotification($db, (int)$row->user_id);
            }

        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:limit_check] ' . $e->getMessage());
        }

        // 4.3) Resolver slug del plan (if not already resolved in step 4.1)
        if ($planId !== 1 && $planSlug === 'free') {
            try {
                $planRow = $db->table('api_plans')
                    ->select('slug')
                    ->where('id', (int)$planId)
                    ->get()
                    ->getRow();
                if ($planRow) {
                    $planSlug = $planRow->slug;
                }
            } catch (\Throwable $e) {
                log_message('error', '[ApiKeyFilter::before:plan_slug] ' . $e->getMessage());
            }
        }

        // Capture search term
        $searchTerm = $request->getGet('cif');
        if (!$searchTerm) $searchTerm = $request->getGet('q');
        if (!$searchTerm) $searchTerm = $request->getGet('name');

        $request->api_meta = [
            'user_id'         => (int)$row->user_id,
            'api_key_id'      => (int)$row->api_key_id,
            'subscription_id' => $subscriptionId,
            'plan_id'         => (int)$planId,
            'plan_slug'       => $planSlug,
            'request_id'      => (string)$request->api_request_id,
            'search_term'     => $searchTerm ? (string)$searchTerm : null,
        ];

        // 5) Exponer el user_id
        $request->setGlobal('get', array_merge($request->getGet(), [
            '__auth_user_id'    => (int)$row->user_id,
            '__auth_api_key_id' => (int)$row->api_key_id,
        ]));

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $meta = $request->api_meta ?? null;
        if (!$meta || empty($meta['user_id']) || empty($meta['api_key_id'])) {
            return;
        }

        try {
            $db = \Config\Database::connect('default');
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $statusCode = (int)$response->getStatusCode();

            $t0 = $request->api_t0 ?? null;
            $durationMs = null;
            if ($t0 !== null) {
                $durationMs = (int) round((microtime(true) - $t0) * 1000);
            }

            $endpoint = (string)$request->getUri()->getPath();
            $method   = (string)$request->getMethod();
            $ip = $request->getIPAddress();
            $ua = (string)$request->getUserAgent();
            if (strlen($ua) > 255) $ua = substr($ua, 0, 255);

            if ($db->tableExists('api_requests')) {
                $db->table('api_requests')->insert([
                    'user_id'         => (int)$meta['user_id'],
                    'api_key_id'      => (int)$meta['api_key_id'],
                    'subscription_id' => $meta['subscription_id'] !== null ? (int)$meta['subscription_id'] : null,
                    'endpoint'        => $endpoint,
                    'http_method'     => $method,
                    'status_code'     => $statusCode,
                    'request_id'      => (string)$meta['request_id'],
                    'ip_address'      => $ip,
                    'user_agent'      => $ua,
                    'duration_ms'     => $durationMs,
                    'search_term'     => $meta['search_term'] ?? null,
                    'created_at'      => $now,
                ]);
            }

            $skipBilling = $request->api_skip_billing ?? false;
            if (!$skipBilling && (strpos($endpoint, 'api/v1/professional/search') !== false)) {
                $skipBilling = true;
            }

            if ($db->tableExists('api_usage_daily') && !$skipBilling) {
                $sql = "
                    INSERT INTO api_usage_daily (user_id, plan_id, date, requests_count, created_at, updated_at)
                    VALUES (?, ?, ?, 1, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      requests_count = requests_count + 1,
                      updated_at = VALUES(updated_at)
                ";
                $db->query($sql, [
                    (int)$meta['user_id'],
                    (int)$meta['plan_id'],
                    $today,
                    $now,
                    $now,
                ]);
            }

            $response->setHeader('X-Request-Id', (string)$meta['request_id']);
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::after] ' . $e->getMessage());
        }
    }

    protected function checkThresholdNotification($db, int $userId)
    {
        try {
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $usage = $db->table('api_usage_daily')
                ->selectSum('requests_count')
                ->where('user_id', $userId)
                ->where('date >=', $thirtyDaysAgo)
                ->get()
                ->getRow();

            $total = $usage ? (int)$usage->requests_count : 0;

            if ($total >= 42500) {
                $cacheKey = "threshold_notif_sent_{$userId}";
                if (cache()->get($cacheKey)) return;

                $email = \Config\Services::email();
                $email->setFrom('soporte@apiempresas.es', 'APIEmpresas Support');
                $email->setTo('papelo.amh@gmail.com');
                $email->setSubject('ALERTA: Umbral de consumo alcanzado (Plan Professional)');
                
                $message = "Hola,\n\n"
                         . "El cliente del Plan Professional (ID Usuario: {$userId}) ha alcanzado el umbral de 42.500 peticiones en los últimos 30 días.\n\n"
                         . "Consumo detectado: " . number_format($total, 0, ',', '.') . " peticiones.\n"
                         . "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n\n"
                         . "Este es un aviso automático para control de facturación de excedentes.";

                $email->setMessage($message);
                
                if ($email->send()) {
                    cache()->save($cacheKey, true, 86400);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::checkThresholdNotification] ' . $e->getMessage());
        }
    }
}
