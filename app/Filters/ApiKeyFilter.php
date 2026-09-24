<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyFilter implements FilterInterface
{
    /**
     * Desde cuándo cuenta el cupo gratuito "de por vida" (100 consultas). Lo usan
     * este filtro y email:automation: si cambia, cambia en los dos sitios.
     */
    public const FREE_DESDE = '2026-05-28';

    /**
     * Cuenta interna del monitor de status.apiempresas.es: sus consultas no se
     * cobran ni gastan cupo (comprueba cada 5 minutos con un CIF real).
     */
    public const MONITOR_USER_ID = 376;

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
        if (strpos($path, 'api/v1/professional/search') !== false) return 0; // Autocompletado gratuito (mínimo 3 chars), el cobro real es en /professional/details

        // "vamos a dejar los dos primeros a 1 credito y los otros a 3"
        // 2. api/v1/companies/search
        if (strpos($path, 'api/v1/companies/search') !== false) return 1;
        // 1. api/v1/companies (Exact match ignoring query params and trailing slash)
        if (preg_match('#api/v1/companies/?$#', $path)) return 1;
        // 3. Custom Client endpoints
        if (preg_match('#api/v1/custom/.+/companies/?$#', $path)) return 1;

        // Professional Details: 1 crédito (el /search es gratuito, el /details es el que cuenta)
        if (strpos($path, 'api/v1/professional/details') !== false) return 1;

        // Los demás (api/v1/*) a 3 créditos
        if (strpos($path, 'api/v1/') !== false) return 3;

        return 1; // Fallback
    }

    /**
     * Respuesta de error de este filtro.
     *
     * Contrato: los campos que cada error ya devolvía ($legacy) salen tal cual, con
     * el mismo nombre y valor. Solo se AÑADEN los que falten: success=false, code
     * (identificador estable para programar contra él) y los campos RFC 7807 que ya
     * dan los controladores y promete la documentación, más la cabecera X-Request-Id.
     */
    /**
     * Guarda en api_requests los rechazos de este filtro con cliente identificado
     * (403 y 429). Antes no quedaban registrados: no se veían en el panel ni en
     * soporte. Con $agrupar, como mucho uno por minuto y clave, para que un cliente
     * que insiste no llene la tabla. No afecta al cobro (solo se cobran los 200).
     */
    /**
     * Enlaces de compra para los 429 de cupo (campos nuevos; upgrade_url no cambia).
     * Free → Pro, Pro → Business, Business → solo bono. El origen llega al checkout.
     */
    public static function enlacesCompra(int $planId, string $source): array
    {
        $siguiente = $planId === 1 ? 'pro' : ($planId === 2 ? 'business' : null);
        return [
            'checkout_url' => $siguiente !== null
                ? site_url('billing?plan=' . $siguiente . '&period=annual&source=' . $source)
                : null,
            'recharge_url' => site_url('crear-bono-api?source=' . $source),
        ];
    }

    private function registrarRechazo(RequestInterface $request, $row, int $status, ?string $agrupar = null): void
    {
        try {
            if ($agrupar !== null) {
                $clave = 'apilog_' . $agrupar . '_' . (int) $row->api_key_id;
                if (cache()->get($clave)) {
                    return;
                }
                cache()->save($clave, 1, 60);
            }

            $db = \Config\Database::connect('default');
            $ua = (string) $request->getUserAgent();
            $db->table('api_requests')->insert([
                'user_id'         => (int) $row->user_id,
                'api_key_id'      => (int) $row->api_key_id,
                'subscription_id' => !empty($row->subscription_id) ? (int) $row->subscription_id : null,
                'endpoint'        => (string) $request->getUri()->getPath(),
                'http_method'     => (string) $request->getMethod(),
                'status_code'     => $status,
                'request_id'      => (string) self::$apiRequestId,
                'ip_address'      => $request->getIPAddress(),
                'user_agent'      => substr($ua, 0, 255),
                'duration_ms'     => self::$apiT0 > 0.0 ? (int) round((microtime(true) - self::$apiT0) * 1000) : null,
                'search_term'     => null,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::registrarRechazo] ' . $e->getMessage());
        }
    }

    private function errorResponse(int $status, array $legacy, string $code, string $detail, array $headers = [])
    {
        $body = $legacy;
        $extra = [
            'success'  => false,
            'code'     => $code,
            'type'     => 'https://apiempresas.com/docs/errors/' . strtolower($code),
            'title'    => $code,
            'status'   => $status,
            'detail'   => $detail,
            'instance' => self::$apiRequestId,
        ];
        foreach ($extra as $k => $v) {
            if (!array_key_exists($k, $body)) {
                $body[$k] = $v;
            }
        }

        $response = service('response')->setStatusCode($status);
        foreach ($headers as $name => $value) {
            $response->setHeader($name, (string) $value);
        }
        if (self::$apiRequestId !== '') {
            $response->setHeader('X-Request-Id', self::$apiRequestId);
        }

        return $response->setJSON($body);
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
            return $this->errorResponse(401, ['error' => 'Falta la API key (X-API-KEY).'], 'API_KEY_MISSING', 'Falta la API key (X-API-KEY).');
        }

        // 3) Validar contra DB
        $db = \Config\Database::connect('default');

        $clientIp = $request->getIPAddress();
        $escapedIp = $db->escape($clientIp);

        $builder = $db->table('api_keys ak')
            ->select('
                ak.id AS api_key_id, 
                ak.user_id, 
                ak.is_active, 
                ak.last_used_at, 
                ak.allowed_countries, 
                u.is_active AS user_active, 
                u.email,
                uw.balance AS wallet_balance,
                us.id AS subscription_id,
                (us.id IS NOT NULL AND (us.plan_id = 1 OR us.current_period_end IS NULL OR us.current_period_end > NOW())) AS is_subscription_valid,
                ap.id AS effective_plan_id,
                ap.slug AS effective_plan_slug,
                ap.monthly_quota AS effective_monthly_quota,
                (EXISTS (SELECT 1 FROM api_whitelist_ips awp WHERE awp.user_id = ak.user_id AND awp.ip_address = ' . $escapedIp . ' LIMIT 1)) AS is_ip_whitelisted
            ')
            ->join('users u', 'u.id = ak.user_id', 'left')
            ->join('user_wallets uw', 'uw.user_id = ak.user_id', 'left');

        // Una suscripción cancelada conserva el plan hasta el final del periodo pagado:
        // es lo que promete Billing::cancel_subscription ("seguirás teniendo acceso
        // hasta el final del periodo") y lo que ya hace Solvencia. Antes solo se
        // miraba status = 'active', y quien cancelaba Pro volvía al Free en el acto
        // aunque le quedaran semanas pagadas.
        $subQuery = "(SELECT MAX(us2.id) FROM user_subscriptions us2 JOIN api_plans ap2 ON ap2.id = us2.plan_id WHERE us2.user_id = ak.user_id AND (us2.status = 'active' OR (us2.status = 'canceled' AND us2.current_period_end > NOW())) AND ap2.product_type IN ('api', 'bundle'))";
        $builder->join("user_subscriptions us", "us.id = $subQuery", 'left', false);
        $builder->join('api_plans ap', 'ap.id = CASE WHEN us.plan_id IS NOT NULL AND (us.plan_id = 1 OR us.current_period_end IS NULL OR us.current_period_end > NOW()) THEN us.plan_id ELSE 1 END', 'left', false);

        $builder->where('ak.api_key', $apiKey);

        $row = $builder->get()->getRow();

        if (!$row) {
            return $this->errorResponse(401, ['error' => 'API key inválida'], 'API_KEY_INVALID', 'API key inválida');
        }

        if ((int)$row->is_active !== 1 || (int)$row->user_active !== 1) {
            $this->registrarRechazo($request, $row, 403, 'inactive');
            return $this->errorResponse(403, ['error' => 'API key inactiva o usuario inactivo'], 'API_KEY_INACTIVE', 'API key inactiva o usuario inactivo');
        }

        // 3.1) IP Whitelist Check (Geo-Bypass)
        $isIpWhitelisted = !empty($row->is_ip_whitelisted);

        // 4.1) Resolver suscripción y plan
        $planId         = (int)$row->effective_plan_id;
        $planSlug       = $row->effective_plan_slug;
        $monthlyQuota   = isset($row->effective_monthly_quota) ? (int)$row->effective_monthly_quota : get_free_plan_limit();

        $subscriptionId = null;
        if (!empty($row->subscription_id) && !empty($row->is_subscription_valid)) {
            $subscriptionId = (int)$row->subscription_id;
        }

        // 3.5) Detección de Anomalías Geográficas (CF-IPCountry)
        $cfCountry = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? strtoupper((string)$_SERVER['HTTP_CF_IPCOUNTRY']) : null;
        
        // Si la IP está en la lista blanca o es plan free, saltamos el chequeo geográfico
        if (!$isIpWhitelisted && $cfCountry && (int)$row->user_id !== 166 && (int)$planId !== 1) {
            $allowedCountries = $row->allowed_countries ? explode(',', strtoupper(str_replace(' ', '', $row->allowed_countries))) : [];
            if (!empty($allowedCountries) && !in_array($cfCountry, $allowedCountries)) {
                // Denegar acceso sin inactivar la clave (evita caídas de servicio legítimo)
                log_message('warning', "GEO-ANOMALY DENIED: API Key {$row->api_key_id} blocked request from {$cfCountry}. User: {$row->email}");
                $msgPais = 'Acceso denegado. Petición originada desde país no autorizado (' . $cfCountry . ').';
                $this->registrarRechazo($request, $row, 403, 'country');
                return $this->errorResponse(403, ['error' => $msgPais], 'COUNTRY_NOT_ALLOWED', $msgPais);
            }
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



        // 4.1.2) Rate Limiting (Throttling per second)
        try {
            $maxRequestsPerSecond = ((int)$planId === 1) ? 2 : 20;
            
            $rateLimitKey = 'throttle_' . (int)$row->api_key_id . '_' . time();
            $requestsThisSecond = (int) cache()->get($rateLimitKey);
            
            if ($requestsThisSecond >= $maxRequestsPerSecond) {
                $this->registrarRechazo($request, $row, 429, 'rate');
                return $this->errorResponse(429, [
                    'success' => false,
                    'error'   => 'TOO_MANY_REQUESTS',
                    'message' => 'Has superado el límite de ' . $maxRequestsPerSecond . ' peticiones por segundo. Por favor, reduce la velocidad de tus peticiones o utiliza el endpoint /batch.',
                    'type'    => 'https://apiempresas.com/docs/errors/too_many_requests',
                    'title'   => 'TOO_MANY_REQUESTS',
                    'status'  => 429,
                    'detail'  => 'Has superado el límite de ' . $maxRequestsPerSecond . ' peticiones por segundo. Por favor, reduce la velocidad de tus peticiones o utiliza el endpoint /batch.',
                    'instance'=> self::$apiRequestId
                ], 'TOO_MANY_REQUESTS', 'Has superado el límite de ' . $maxRequestsPerSecond . ' peticiones por segundo.', [
                    'X-RateLimit-Limit'     => $maxRequestsPerSecond,
                    'X-RateLimit-Remaining' => '0',
                    'X-RateLimit-Reset'     => time() + 1,
                    'Retry-After'           => '1',
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

        if ($creditCost === 0 || (int) $row->user_id === self::MONITOR_USER_ID) {
            self::$apiSkipBilling = true;
        }

        $walletBalance = isset($row->wallet_balance) ? (int)$row->wallet_balance : 0;

        $subCost = 0;
        $walletCost = 0;

        // 4.2) Verificar Límites de Consumo
        try {

            $currentMonth = date('Y-m');
            $cacheKey = ((int)$planId === 1) ? "api_usage_lifetime_{$row->user_id}" : "api_usage_{$row->user_id}_{$currentMonth}";
            $currentUsage = cache()->get($cacheKey);

            if ($currentUsage === null) {
                if ((int)$planId === 1) {
                    $usageRow = $db->table('api_usage_daily')->selectSum('requests_count')->where('user_id', (int)$row->user_id)->where('date >=', self::FREE_DESDE)->get()->getRow();
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

                    // Mismo 429 y mismos campos de siempre. Lo añadido (code, RFC 7807,
                    // quota_resets_at, X-Quota-Reset) permite distinguirlo del 429 por
                    // velocidad: este NO se arregla reintentando (no lleva Retry-After).
                    // El Free no se renueva (100 consultas en total): quota_resets_at = null.
                    $quotaReset = ((int)$planId === 1) ? null : strtotime('first day of next month 00:00:00');
                    $quotaHeaders = [
                        'X-RateLimit-Limit'     => $maxRequestsPerSecond ?? 2,
                        'X-RateLimit-Remaining' => '0',
                        'X-RateLimit-Reset'     => time() + 1,
                        'X-Quota-Limit'         => $monthlyQuota,
                        'X-Quota-Remaining'     => '0',
                    ];
                    if ($quotaReset !== null) {
                        $quotaHeaders['X-Quota-Reset'] = $quotaReset;
                    }

                    $this->registrarRechazo($request, $row, 429, 'quota');
                    return $this->errorResponse(429, [
                        'success' => false,
                        'error'   => 'Quota Exceeded',
                        'message' => $errorMsg,
                        'current_usage' => $currentUsage,
                        'wallet_balance' => $walletBalance,
                        'cost_required' => $creditCost,
                        'upgrade_url' => site_url('billing'),
                        'quota_resets_at' => $quotaReset !== null ? date('c', $quotaReset) : null,
                    ] + self::enlacesCompra((int) $planId, 'api_429_quota'), 'QUOTA_EXCEEDED', $errorMsg, $quotaHeaders);
                }
            }

            // IP Limits for free plan
            // Solo cuentan las respuestas 200, como el cupo: antes contaban también los
            // errores (que no se cobran) y quien probaba con CIF mal formados podía
            // quedarse bloqueado por IP sin haber gastado ninguna consulta.
            if ((int)$planId === 1 && $walletBalance <= 0 && $db->tableExists('api_requests')) {
                $ipAddress = $request->getIPAddress();
                $subscriptionTable = $db->tableExists('user_subscriptions') ? 'user_subscriptions' : 'usersuscriptions';
                $ipUsage = $db->table('api_requests r')->join($subscriptionTable . ' us', 'us.user_id = r.user_id')->where('us.plan_id', 1)->where('us.status', 'active')->where('r.ip_address', $ipAddress)->where('r.status_code', 200)->where('r.created_at >=', self::FREE_DESDE . ' 00:00:00')->countAllResults();

                if ($ipUsage >= 100) {
                    $this->registrarRechazo($request, $row, 429, 'ip');
                    return $this->errorResponse(429, [
                        'success' => false,
                        'error'   => 'Quota Exceeded',
                        'message' => 'Límite de seguridad por IP alcanzado. Actualiza tu plan.',
                        'upgrade_url' => site_url('billing'),
                    ] + self::enlacesCompra(1, 'api_429_ip'), 'IP_LIMIT_EXCEEDED', 'Límite de seguridad por IP alcanzado. Actualiza tu plan.');
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
            // Nivel de acceso a funciones: un Free con saldo en el monedero accede como
            // Pro (datos completos, score, señales, radar). Es lo que vende la página del
            // bono. El cobro sigue yendo por plan_id; batch y Business no cambian.
            'access_slug'     => ($planSlug === 'free' && $walletBalance > 0) ? 'pro' : $planSlug,
            'request_id'      => (string)self::$apiRequestId,
            'search_term'     => $searchTerm ? (string)$searchTerm : null,
            'sub_cost'        => $subCost,
            'wallet_cost'     => $walletCost,
            'wallet_balance'  => $walletBalance,
            'rate_limit'      => $maxRequestsPerSecond ?? 2,
            'rate_remaining'  => max(0, ($maxRequestsPerSecond ?? 2) - ($requestsThisSecond ?? 0)),
            'rate_reset'      => time() + 1,
            'quota_limit'     => $monthlyQuota ?? 0,
            'quota_remaining' => max(0, ($monthlyQuota ?? 0) - ($currentUsage ?? 0)),
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
                    // GREATEST: el saldo nunca queda en negativo aunque varias peticiones
                    // simultáneas lo den por bueno a la vez
                    $db->table('user_wallets')
                       ->where('user_id', (int)$meta['user_id'])
                       ->set('balance', 'GREATEST(balance - ' . (int)$meta['wallet_cost'] . ', 0)', false)
                       ->update();
                }

                // Actualizar al momento el contador de uso en caché. Antes solo se
                // releía de la BD cada 30 s y, mientras tanto, el cupo parecía intacto:
                // con peticiones seguidas (o batch) se podía gastar varias veces el cupo.
                if ((int)$meta['sub_cost'] > 0) {
                    $claveUso = ((int)$meta['plan_id'] === 1)
                        ? 'api_usage_lifetime_' . (int)$meta['user_id']
                        : 'api_usage_' . (int)$meta['user_id'] . '_' . date('Y-m');
                    $usoCache = cache()->get($claveUso);
                    if ($usoCache !== null) {
                        cache()->save($claveUso, (int)$usoCache + (int)$meta['sub_cost'], 30);
                    }
                }
            }

            $response->setHeader('X-Request-Id', (string)$meta['request_id']);
            $response->setHeader('X-RateLimit-Limit', (string)$meta['rate_limit']);
            $response->setHeader('X-RateLimit-Remaining', (string)$meta['rate_remaining']);
            $response->setHeader('X-RateLimit-Reset', (string)$meta['rate_reset']);
            $response->setHeader('X-Quota-Limit', (string)$meta['quota_limit']);
            // quota_remaining se calcula antes de cobrar esta petición: si se ha cobrado
            // del cupo, se descuenta aquí para que la cabecera no vaya una por detrás.
            $restante = (int) $meta['quota_remaining'];
            if (!$skipBilling && (int) $meta['sub_cost'] > 0) {
                $restante = max(0, $restante - (int) $meta['sub_cost']);
            }
            $response->setHeader('X-Quota-Remaining', (string) $restante);
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
