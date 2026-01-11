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

        // Guardamos meta para usarlo en after()
        $request->api_meta = [
            'user_id'         => (int)$row->user_id,
            'api_key_id'      => (int)$row->api_key_id,
            'subscription_id' => $subscriptionId, // puede ser null
            'plan_id'         => (int)$planId,
            'request_id'      => (string)$request->api_request_id,
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
                    'created_at'      => $now,
                ]);
            }

            // 2) Upsert contador diario (si existe la tabla)
            if ($db->tableExists('api_usage_daily')) {
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
            log_message('error', '[ApiKeyFilter::after:usage_log] ' . $e->getMessage());
        }
    }
}
