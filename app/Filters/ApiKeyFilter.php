<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyFilter implements FilterInterface
{
    public static array $apiMeta = [];
    public static float $apiT0 = 0.0;
    public static string $apiRequestId = '';
    public static bool $apiSkipBilling = false;

    private function getEndpointCost(string $path): int
    {
        // Endpoints que no deben costar
        if (strpos($path, 'api/sandbox/v1') !== false) return 0;
        if (strpos($path, 'api/v1/webhooks') !== false) return 0;
        if (strpos($path, 'api/v1/usage') !== false) return 0;
        if (strpos($path, 'api/v1/companies/batch') !== false) return 0;

        // "vamos a dejar los dos primeros a 1 credito y los otros a 3"
        // 2. api/v1/companies/search
        if (strpos($path, 'api/v1/companies/search') !== false) return 1;
        // 1. api/v1/companies (Exact match ignoring query params and trailing slash)
        if (preg_match('#api/v1/companies/?$#', $path)) return 1;

        // Los demás (api/v1/*) a 3 créditos
        if (strpos($path, 'api/v1/') !== false) return 3;

        return 1; // Fallback
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        helper('api');
        // ====== Medición de duración + request_id ======
        self::$apiT0 = microtime(true);
        self::$apiRequestId = bin2hex(random_bytes(16)); // 32 hex chars

        $endpointPath = (string) $request->getUri()->getPath();
        
        // 0) Bypass para Sandbox ÚNICAMENTE desde el Playground (Nuestra Web)
        if (strpos($endpointPath, 'api/sandbox/v1') !== false) {
            self::$apiSkipBilling = true; // El sandbox nunca cobra
            
            $referer = (string) $request->getHeaderLine('Referer');
            $origin  = (string) $request->getHeaderLine('Origin');
            $host    = (string) $request->getHeaderLine('Host');
            
            // Si la petición viene desde nuestra web (Playground), no exigimos API Key
            if ($host !== '' && (strpos($referer, $host) !== false || strpos($origin, $host) !== false)) {
                return null; 
            }
            // Si viene desde Postman o cURL, caerá al flujo normal y exigirá una API Key válida (que se comprobará abajo)
        }

        // 1) Leer API key (header recomendado)
        $apiKey = trim((string) $request->getHeaderLine('X-API-KEY'));

        if ($apiKey === '') {
            $auth = trim((string) $request->getHeaderLine('Authorization'));
            if (stripos($auth, 'Bearer ') === 0) {
                $apiKey = trim(substr($auth, 7));
            }
        }

        if ($apiKey === '') {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'Falta la API key (X-API-KEY).']);
        }

        // 3) Validar contra DB
        $db = \Config\Database::connect('default');

        $row = $db->table('api_keys')
            ->select('api_keys.id AS api_key_id, api_keys.user_id, api_keys.is_active, api_keys.last_used_at, users.is_active as user_active, users.created_at, users.migration_reset_done')
            ->join('users', 'users.id = api_keys.user_id', 'left')
            ->where('api_keys.api_key', $apiKey)
            ->get()
            ->getRow();

        if (!$row) {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'API key inválida']);
        }

        if ((int)$row->is_active !== 1 || (int)$row->user_active !== 1) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'API key inactiva o usuario inactivo']);
        }

        // 4) Registrar uso (con throttling para evitar bloqueos en ráfagas)
        try {
            $lastUsed = $row->last_used_at ? strtotime($row->last_used_at) : 0;
            if (time() - $lastUsed > 300) { // Solo actualizar cada 5 minutos
                $db->table('api_keys')->where('id', (int)$row->api_key_id)->update(['last_used_at' => date('Y-m-d H:i:s')]);
            }
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
                    ->groupStart()
                        ->where('api_plans.product_type', 'api')
                        ->orWhere('api_plans.product_type', 'bundle')
                    ->groupEnd()
                    ->orderBy('user_subscriptions.id', 'DESC')
                    ->get()
                    ->getRow();

                if ($sub) {
                    $now = date('Y-m-d H:i:s');
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
                    ->groupStart()
                        ->where('api_plans.product_type', 'api')
                        ->orWhere('api_plans.product_type', 'bundle')
                    ->groupEnd()
                    ->orderBy('usersuscriptions.id', 'DESC')
                    ->get()
                    ->getRow();

                if ($sub) {
                    $subscriptionId = (int)$sub->subscription_id;
                    $planId         = (int)$sub->plan_id;
                    $planSlug       = $sub->plan_slug;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:subscription] ' . $e->getMessage());
        }

        // 4.1.2) Rate Limiting (Throttling per second)
        try {
            $maxRequestsPerSecond = ((int)$planId === 1) ? 2 : 20;
            
            $rateLimitKey = 'throttle_' . (int)$row->api_key_id . '_' . time();
            $requestsThisSecond = (int) cache()->get($rateLimitKey);
            
            if ($requestsThisSecond >= $maxRequestsPerSecond) {
                return service('response')->setStatusCode(429)->setJSON([
                    'success' => false,
                    'error'   => 'TOO_MANY_REQUESTS',
                    'message' => 'Has superado el límite de ' . $maxRequestsPerSecond . ' peticiones por segundo. Por favor, reduce la velocidad de tus peticiones o utiliza el endpoint /batch.',
                    'type'    => 'https://apiempresas.com/docs/errors/too_many_requests',
                    'title'   => 'TOO_MANY_REQUESTS',
                    'status'  => 429,
                    'detail'  => 'Has superado el límite de ' . $maxRequestsPerSecond . ' peticiones por segundo. Por favor, reduce la velocidad de tus peticiones o utiliza el endpoint /batch.',
                    'instance'=> self::$apiRequestId
                ]);
            }
            
            if ($requestsThisSecond === 0) {
                cache()->save($rateLimitKey, 1, 2); // TTL 2 segundos
            } else {
                cache()->increment($rateLimitKey, 1);
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:throttling] ' . $e->getMessage());
        }

        // 4.1.5) Determinar Coste (Universal Credits) y Saldo
        $endpointPath = (string) $request->getUri()->getPath();
        $creditCost = $this->getEndpointCost($endpointPath);

        if ($creditCost === 0) {
            self::$apiSkipBilling = true;
        }

        $walletBalance = 0;
        if ($creditCost > 0 && $db->tableExists('user_wallets')) {
            $walletRow = $db->table('user_wallets')->where('user_id', (int)$row->user_id)->get()->getRow();
            if ($walletRow) {
                $walletBalance = (int)$walletRow->balance;
            }
        }

        $subCost = 0;
        $walletCost = 0;

        // 4.2) Verificar Límites de Consumo
        try {
            $planRow = $db->table('api_plans')->select('monthly_quota')->where('id', (int)$planId)->get()->getRow();
            $monthlyQuota = $planRow ? (int)$planRow->monthly_quota : get_free_plan_limit();

            $currentMonth = date('Y-m');
            $cacheKey = ((int)$planId === 1) ? "api_usage_lifetime_{$row->user_id}" : "api_usage_{$row->user_id}_{$currentMonth}";
            $currentUsage = cache()->get($cacheKey);

            if ($currentUsage === null) {
                if ((int)$planId === 1) {
                    $usageRow = $db->table('api_usage_daily')->selectSum('requests_count')->where('user_id', (int)$row->user_id)->where('date >=', '2026-05-28')->get()->getRow();
                } else {
                    $usageRow = $db->table('api_usage_daily')->selectSum('requests_count')->where('user_id', (int)$row->user_id)->where('plan_id', (int)$planId)->like('date', $currentMonth, 'after')->get()->getRow();
                }
                $currentUsage = $usageRow ? (int)$usageRow->requests_count : 0;
                cache()->save($cacheKey, $currentUsage, 30);
            }

            // Waterfall Billing Logic: Suscripciones pagan 1 petición, Monedero paga el peso en créditos (1 o 3)
            if (!self::$apiSkipBilling && $creditCost > 0) {
                $monthlyRemaining = max(0, $monthlyQuota - $currentUsage);
                
                if ((int)$planId === 1 && $walletBalance > 0) {
                    $monthlyRemaining = 0; // Force wallet billing
                }
                
                $requestCost = 1; // Para la suscripción, 1 llamada = 1 petición
                
                if ($monthlyRemaining >= $requestCost) {
                    // Queda cuota mensual, se cobra 1 petición de la suscripción
                    $subCost = $requestCost;
                } else {
                    // Cuota agotada, se cobra del monedero el peso completo en créditos
                    $walletCost = $creditCost;
                }

                if ($walletCost > $walletBalance) {
                    $errorMsg = ((int)$planId === 1)
                        ? 'Has consumido las ' . $monthlyQuota . ' consultas gratuitas garantizadas y no tienes saldo suficiente en el monedero. Recarga créditos o actualiza a un plan de pago.'
                        : 'Has superado el límite de consultas de tu plan (' . $monthlyQuota . ') y no tienes saldo suficiente en el monedero. Recarga créditos para continuar.';

                    return service('response')->setStatusCode(429)->setJSON([
                        'success' => false,
                        'error'   => 'Quota Exceeded',
                        'message' => $errorMsg,
                        'current_usage' => $currentUsage,
                        'wallet_balance' => $walletBalance,
                        'cost_required' => $creditCost,
                        'upgrade_url' => site_url('billing')
                    ]);
                }
            }

            // IP Limits for free plan
            if ((int)$planId === 1 && $db->tableExists('api_requests')) {
                $ipAddress = $request->getIPAddress();
                $subscriptionTable = $db->tableExists('user_subscriptions') ? 'user_subscriptions' : 'usersuscriptions';
                $ipUsage = $db->table('api_requests r')->join($subscriptionTable . ' us', 'us.user_id = r.user_id')->where('us.plan_id', 1)->where('us.status', 'active')->where('r.ip_address', $ipAddress)->where('r.created_at >=', '2026-05-28 00:00:00')->countAllResults();

                if ($ipUsage >= 100) {
                    return service('response')->setStatusCode(429)->setJSON([
                        'success' => false,
                        'error'   => 'Quota Exceeded',
                        'message' => 'Límite de seguridad por IP alcanzado. Actualiza tu plan.',
                    ]);
                }
            }

            if ((int)$planId === 7) {
                $this->checkThresholdNotification($db, (int)$row->user_id);
            }

        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:limit_check] ' . $e->getMessage());
        }

        // 4.3) Resolver slug del plan
        if ($planId !== 1 && $planSlug === 'free') {
            try {
                $planRow = $db->table('api_plans')->select('slug')->where('id', (int)$planId)->get()->getRow();
                if ($planRow) $planSlug = $planRow->slug;
            } catch (\Throwable $e) {
                log_message('error', '[ApiKeyFilter::before:plan_slug] ' . $e->getMessage());
            }
        }

        $searchTerm = $request->getGet('cif');
        if (!$searchTerm) $searchTerm = $request->getGet('q');
        if (!$searchTerm) $searchTerm = $request->getGet('name');

        self::$apiMeta = [
            'user_id'         => (int)$row->user_id,
            'api_key_id'      => (int)$row->api_key_id,
            'subscription_id' => $subscriptionId,
            'plan_id'         => (int)$planId,
            'plan_slug'       => $planSlug,
            'request_id'      => (string)self::$apiRequestId,
            'search_term'     => $searchTerm ? (string)$searchTerm : null,
            'sub_cost'        => $subCost,
            'wallet_cost'     => $walletCost,
            'wallet_balance'  => $walletBalance,
        ];

        $request->setGlobal('get', array_merge($request->getGet(), [
            '__auth_user_id'    => (int)$row->user_id,
            '__auth_api_key_id' => (int)$row->api_key_id,
        ]));

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $meta = self::$apiMeta;
        if (empty($meta) || empty($meta['user_id']) || empty($meta['api_key_id'])) {
            return;
        }

        try {
            $db = \Config\Database::connect('default');
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $statusCode = (int)$response->getStatusCode();

            $t0 = self::$apiT0;
            $durationMs = null;
            if ($t0 > 0.0) {
                $durationMs = (int) round((microtime(true) - $t0) * 1000);
            }

            $endpoint = (string)$request->getUri()->getPath();
            $method   = (string)$request->getMethod();
            $ip = $request->getIPAddress();
            $ua = (string)$request->getUserAgent();
            if (strlen($ua) > 255) $ua = substr($ua, 0, 255);

            $isSearch = (strpos($endpoint, 'api/v1/professional/search') !== false);
            $isEnterprise = ($meta['plan_slug'] === 'enterprise');
            
            if ($db->tableExists('api_requests')) {
                if (!$isSearch && (!$isEnterprise || $statusCode !== 200)) {
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
            }

            $skipBilling = self::$apiSkipBilling;
            
            // CRO Promise: Solo cobramos si la petición es exitosa (200 OK)
            if ($statusCode !== 200) {
                $skipBilling = true;
            }

            if ($db->tableExists('api_usage_daily') && !$skipBilling && ($meta['sub_cost'] > 0 || $meta['wallet_cost'] > 0)) {
                $sqlDaily = "
                    INSERT INTO api_usage_daily (user_id, plan_id, date, requests_count, credits_used, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      requests_count = requests_count + VALUES(requests_count),
                      credits_used = credits_used + VALUES(credits_used),
                      updated_at = VALUES(updated_at)
                ";
                $db->query($sqlDaily, [
                    (int)$meta['user_id'],
                    (int)$meta['plan_id'],
                    $today,
                    (int)$meta['sub_cost'], 
                    (int)$meta['wallet_cost'],
                    $now,
                    $now,
                ]);

                if ((int)$meta['wallet_cost'] > 0) {
                    $db->table('user_wallets')
                       ->where('user_id', (int)$meta['user_id'])
                       ->set('balance', 'balance - ' . (int)$meta['wallet_cost'], false)
                       ->update();
                }
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
