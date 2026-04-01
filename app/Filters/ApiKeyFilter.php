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
        // No afecta a tu lógica actual
        $request->api_t0 = microtime(true);
        $request->api_request_id = bin2hex(random_bytes(16)); // 32 hex chars

        // 1) Leer API key (header recomendado)  [NO CAMBIADO]
        $apiKey = trim((string) $request->getHeaderLine('X-API-KEY'));

        // 2) Alternativa: permitir también "Authorization: Bearer <APIKEY>"  [NO CAMBIADO]
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

        // 3) Validar contra DB  [MISMA IDEA, solo alias para claridad]
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

        // 4) Registrar uso (opcional)  [IGUAL]
        try {
            $db->table('api_keys')
                ->where('id', (int)$row->api_key_id)
                ->update(['last_used_at' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:last_used_at] ' . $e->getMessage());
        }

        // 4.1) Resolver suscripción y plan (para api_requests / api_usage_daily)
        // - Soporta ambos nombres por si en tu proyecto conviven: user_subscriptions o usersuscriptions
        // - Si no existe, fallback plan_id = 1
        $subscriptionId = null;
        $planId = 1;

        try {
            // Intento 1: user_subscriptions
            if ($db->tableExists('user_subscriptions')) {
                $sub = $db->table('user_subscriptions')
                    ->select('id AS subscription_id, plan_id, status')
                    ->where('user_id', (int)$row->user_id)
                    ->where('status', 'active')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRow();

                if ($sub) {
                    $subscriptionId = (int)$sub->subscription_id;
                    $planId = (int)$sub->plan_id;
                }
            }
            // Intento 2: usersuscriptions (tu modelo de registro usa este nombre)
            elseif ($db->tableExists('usersuscriptions')) {
                $sub = $db->table('usersuscriptions')
                    ->select('id AS subscription_id, plan_id, status')
                    ->where('user_id', (int)$row->user_id)
                    ->where('status', 'active')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRow();

                if ($sub) {
                    $subscriptionId = (int)$sub->subscription_id;
                    $planId = (int)$sub->plan_id;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:subscription] ' . $e->getMessage());
            // fallback planId=1
        }

        // 4.2) Verificar Límites de Consumo (ENFORCEMENT)
        try {
            // Obtener cuota del plan (mensual)
            $planRow = $db->table('api_plans')
                ->select('monthly_quota')
                ->where('id', (int)$planId)
                ->get()
                ->getRow();
            
            // Fallback razonable si falla query: 100 peticiones (plan free habitual)
            $monthlyQuota = $planRow ? (int)$planRow->monthly_quota : 100;

            // Calcular consumo del mes actual
            $currentMonth = date('Y-m'); // '2023-10'
            $usageRow = $db->table('api_usage_daily')
                ->selectSum('requests_count')
                ->where('user_id', (int)$row->user_id)
                ->like('date', $currentMonth, 'after') // date comienza con Y-m
                ->get()
                ->getRow();

            $currentUsage = $usageRow ? (int)$usageRow->requests_count : 0;

            // Bloquear si excede
            // EXCEPCIÓN: El Plan Professional (ID 7) admite excedentes (overage)
            if ($currentUsage >= $monthlyQuota && (int)$planId !== 7) {
                return service('response')
                    ->setStatusCode(429) // Too Many Requests
                    ->setJSON([
                        'success' => false,
                        'error'   => 'Quota Exceeded',
                        'message' => 'Has superado el límite mensual de consultas de tu plan (' . $monthlyQuota . ').',
                        'current_usage' => $currentUsage,
                        'upgrade_url' => site_url('billing/manage') // O link directo a upgrade
                    ]);
            }

            // --- Notificación de Umbral (42.500 en últimos 30 días) para Plan Professional ---
            if ((int)$planId === 7) {
                $this->checkThresholdNotification($db, (int)$row->user_id);
            }

        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::before:limit_check] ' . $e->getMessage());
            // En caso de error de DB al chequear límites, ¿dejamos pasar o bloqueamos?
            // "Fail open" suele ser mejor para UX si es un bug nuestro, 
            // pero "Fail closed" protege la infra.
            // Dejamos pasar (fail open) logueando error.
        }

        // Capture search term (CIF, q, or name)
        $searchTerm = $request->getGet('cif');
        if (!$searchTerm) $searchTerm = $request->getGet('q');
        if (!$searchTerm) $searchTerm = $request->getGet('name');

        // Guardamos meta para usarlo en after()
        $request->api_meta = [
            'user_id'         => (int)$row->user_id,
            'api_key_id'      => (int)$row->api_key_id,
            'subscription_id' => $subscriptionId, // puede ser null
            'plan_id'         => (int)$planId,
            'request_id'      => (string)$request->api_request_id,
            'search_term'     => $searchTerm ? (string)$searchTerm : null,
        ];

        // 5) Exponer el user_id al resto de la request  [NO CAMBIADO]
        $request->setGlobal('get', array_merge($request->getGet(), [
            '__auth_user_id'    => (int)$row->user_id,
            '__auth_api_key_id' => (int)$row->api_key_id,
        ]));

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Si la request no pasó por auth OK, no hay meta -> no logueamos
        $meta = $request->api_meta ?? null;
        if (!$meta || empty($meta['user_id']) || empty($meta['api_key_id'])) {
            return;
        }

        // Nunca romper la API por logging
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

            $endpoint = (string)$request->getUri()->getPath(); // ej: /api/v1/companies
            $method   = (string)$request->getMethod();

            $ip = $request->getIPAddress();
            $ua = (string)$request->getUserAgent();
            if (strlen($ua) > 255) $ua = substr($ua, 0, 255);

            // 1) Insert en api_requests (si existe la tabla)
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

            // 2) Upsert contador diario (si existe la tabla)
            $skipBilling = $request->api_skip_billing ?? false;
            if (!$skipBilling && (strpos($endpoint, 'api/v1/professional/search') !== false)) {
                $skipBilling = true;
            }

            if ($db->tableExists('api_usage_daily') && !$skipBilling) {
                $planId = (int)($meta['plan_id'] ?? 1);

                // MySQL upsert por UNIQUE(user_id, plan_id, date)
                $sql = "
                    INSERT INTO api_usage_daily (user_id, plan_id, date, requests_count, created_at, updated_at)
                    VALUES (?, ?, ?, 1, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      requests_count = requests_count + 1,
                      updated_at = VALUES(updated_at)
                ";
                $db->query($sql, [
                    (int)$meta['user_id'],
                    $planId,
                    $today,
                    $now,
                    $now,
                ]);
            }

            // (Opcional) header útil para soporte / trazabilidad (no cambia body)
            $response->setHeader('X-Request-Id', (string)$meta['request_id']);
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::after] ' . $e->getMessage());
        }
    }

    /**
     * Verifica si se ha alcanzado el umbral de 42.500 peticiones en los últimos 30 días
     * y envía un correo de notificación.
     */
    protected function checkThresholdNotification($db, int $userId)
    {
        try {
            // 1. Calcular consumo últimos 30 días
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $usage = $db->table('api_usage_daily')
                ->selectSum('requests_count')
                ->where('user_id', $userId)
                ->where('date >=', $thirtyDaysAgo)
                ->get()
                ->getRow();

            $total = $usage ? (int)$usage->requests_count : 0;

            if ($total >= 42500) {
                // 2. Evitar spam (usamos caché simple por 24h)
                $cacheKey = "threshold_notif_sent_{$userId}";
                if (cache()->get($cacheKey)) {
                    return;
                }

                // 3. Enviar correo
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
                    // Marcar como enviado por 24 horas para no repetir en cada request
                    cache()->save($cacheKey, true, 86400);
                } else {
                    log_message('error', '[ApiKeyFilter::checkThresholdNotification] Error al enviar email: ' . $email->printDebugger(['headers']));
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiKeyFilter::checkThresholdNotification] ' . $e->getMessage());
        }
    }
}
