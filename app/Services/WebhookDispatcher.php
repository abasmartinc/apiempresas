<?php

namespace App\Services;

/**
 * Envío real de webhooks (28-09-2026).
 *
 * Dos fases, las dos en `php spark webhooks:run` (cron cada 15 min):
 *  1. enqueue(): para cada webhook activo de un cliente Business suscrito a eventos de la
 *     vigilancia, calcula los eventos recientes de sus empresas vigiladas
 *     (ApiWatchlistService::events) y los mete en webhook_deliveries. El id de cada envío
 *     sale de webhook + evento, y la tabla tiene UNIQUE (delivery_uuid): repasar una
 *     ventana ya vista no duplica nada.
 *  2. dispatch(): toma los envíos pendientes, los manda firmados y reintenta con espera
 *     creciente. Tras MAX_INTENTOS pasa a 'dead'. Un webhook que acumula FALLOS_PARA_PAUSAR
 *     fallos seguidos se desactiva (is_active = 0, disabled_at).
 *
 * Seguridad: la URL se vuelve a validar al enviar y la IP resuelta se fija en la conexión
 * (CURLOPT_RESOLVE), para que un DNS que cambie entre la validación y el envío no pueda
 * llevar la petición a una IP interna (DNS rebinding).
 *
 * Firma: cabecera X-ApiEmpresas-Signature: t=<timestamp>,v1=<hex HMAC-SHA256(secret, "t.body")>
 */
class WebhookDispatcher
{
    public const MAX_INTENTOS = 6;
    /** Espera antes de cada reintento, en segundos (1 min, 5 min, 30 min, 2 h, 6 h). */
    public const ESPERAS = [60, 300, 1800, 7200, 21600];
    public const FALLOS_PARA_PAUSAR = 50;
    /** Días hacia atrás que se repasan en cada pasada (el BORME llega con uno o dos de retraso). */
    public const DIAS_VENTANA = 3;
    public const TIMEOUT = 10;

    private const PLANES_CON_WEBHOOKS = ['business', 'enterprise'];

    // ------------------------------------------------------------------
    // 1. Encolar
    // ------------------------------------------------------------------

    /** @return array{webhooks:int, eventos:int, encolados:int} */
    public static function enqueue(?string $hoy = null): array
    {
        $db = \Config\Database::connect();
        $hoy = $hoy ?? date('Y-m-d');
        $stats = ['webhooks' => 0, 'eventos' => 0, 'encolados' => 0];

        if (!$db->tableExists('api_watchlist')) {
            return $stats;
        }

        $hooks = $db->table('api_webhooks')
            ->where('is_active', 1)
            ->where('disabled_at IS NULL', null, false)
            ->get()->getResultArray();

        $planPorUsuario = [];
        $eventosPorUsuario = [];

        foreach ($hooks as $h) {
            $tipos = WebhookEvents::watchlistTypesFor((string) $h['event']);
            if (empty($tipos)) {
                continue;
            }
            $uid = (int) $h['user_id'];
            $planPorUsuario[$uid] = $planPorUsuario[$uid] ?? self::planSlug($uid);
            if (!in_array($planPorUsuario[$uid], self::PLANES_CON_WEBHOOKS, true)) {
                continue;
            }
            $stats['webhooks']++;

            $desde = max(
                date('Y-m-d', strtotime($hoy . ' -' . self::DIAS_VENTANA . ' days')),
                substr((string) $h['created_at'], 0, 10)
            );
            $clave = $uid . '|' . $desde;
            if (!isset($eventosPorUsuario[$clave])) {
                $res = ApiWatchlistService::events($uid, $desde, array_keys(WebhookEvents::FROM_WATCHLIST), 1, 100000);
                $eventosPorUsuario[$clave] = $res['events'];
            }

            $filas = [];
            foreach ($eventosPorUsuario[$clave] as $ev) {
                if (!in_array($ev['type'], $tipos, true)) {
                    continue;
                }
                $stats['eventos']++;
                $filas[] = self::deliveryRow($h, WebhookEvents::FROM_WATCHLIST[$ev['type']], $ev);
            }
            foreach (array_chunk($filas, 200) as $lote) {
                $db->table('webhook_deliveries')->ignore(true)->insertBatch($lote);
                $stats['encolados'] += $db->affectedRows();
            }
        }

        return $stats;
    }

    /** Fila de webhook_deliveries para un evento. El uuid es estable: mismo webhook + evento = mismo uuid. */
    public static function deliveryRow(array $hook, string $evento, array $data): array
    {
        $uuid = self::stableUuid((int) $hook['id'] . '|' . ($data['id'] ?? json_encode($data)));
        $payload = [
            'id'         => $uuid,
            'event'      => $evento,
            'created_at' => date('c'),
            'data'       => $data,
        ];
        return [
            'delivery_uuid'   => $uuid,
            'webhook_id'      => (int) $hook['id'],
            'user_id'         => (int) $hook['user_id'],
            'event'           => $evento,
            'payload'         => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status'          => 'pending',
            'attempts'        => 0,
            'next_attempt_at' => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];
    }

    public static function stableUuid(string $semilla): string
    {
        $h = md5('apiempresas-webhook|' . $semilla);
        return substr($h, 0, 8) . '-' . substr($h, 8, 4) . '-' . substr($h, 12, 4) . '-' . substr($h, 16, 4) . '-' . substr($h, 20, 12);
    }

    /** Plan de API vigente del usuario (misma regla que ApiKeyFilter). */
    public static function planSlug(int $userId): string
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            "SELECT ap.slug FROM user_subscriptions us
               JOIN api_plans ap ON ap.id = us.plan_id
              WHERE us.user_id = ?
                AND (us.status = 'active' OR (us.status = 'canceled' AND us.current_period_end > NOW()))
                AND (us.plan_id = 1 OR us.current_period_end IS NULL OR us.current_period_end > NOW())
                AND ap.product_type IN ('api', 'bundle')
              ORDER BY us.id DESC LIMIT 1",
            [$userId]
        )->getRowArray();
        return strtolower((string) ($row['slug'] ?? 'free'));
    }

    // ------------------------------------------------------------------
    // 2. Enviar
    // ------------------------------------------------------------------

    /** @return array{enviados:int, entregados:int, fallidos:int, muertos:int} */
    public static function dispatch(int $limite = 200): array
    {
        $db = \Config\Database::connect();
        $stats = ['enviados' => 0, 'entregados' => 0, 'fallidos' => 0, 'muertos' => 0];

        // Envíos que se quedaron a medias (proceso cortado): vuelven a la cola.
        $db->query("UPDATE webhook_deliveries SET status = 'retry', claim_token = NULL
                     WHERE status = 'processing' AND processing_started_at < (NOW() - INTERVAL 15 MINUTE)");

        $token = bin2hex(random_bytes(16));
        $db->query("UPDATE webhook_deliveries SET status = 'processing', claim_token = ?, processing_started_at = NOW()
                     WHERE status IN ('pending', 'retry') AND next_attempt_at <= NOW()
                     ORDER BY id LIMIT " . (int) $limite, [$token]);

        $envios = $db->table('webhook_deliveries')->where('claim_token', $token)->orderBy('id')->get()->getResultArray();
        if (empty($envios)) {
            return $stats;
        }

        $ids = array_unique(array_map(fn($e) => (int) $e['webhook_id'], $envios));
        $hooks = [];
        foreach ($db->table('api_webhooks')->whereIn('id', $ids)->get()->getResultArray() as $h) {
            $hooks[(int) $h['id']] = $h;
        }

        foreach ($envios as $e) {
            $stats['enviados']++;
            $h = $hooks[(int) $e['webhook_id']] ?? null;
            if ($h === null || (int) $h['is_active'] !== 1 || !empty($h['disabled_at'])) {
                $db->table('webhook_deliveries')->where('id', $e['id'])->update([
                    'status' => 'dead', 'claim_token' => null, 'completed_at' => date('Y-m-d H:i:s'),
                    'error_message' => 'Webhook borrado o desactivado',
                ]);
                $stats['muertos']++;
                continue;
            }

            $r = self::send((string) $h['url'], (string) ($h['secret'] ?? ''), (string) $e['event'], (string) $e['delivery_uuid'], (string) $e['payload']);
            $intentos = (int) $e['attempts'] + 1;
            $ahora = date('Y-m-d H:i:s');

            if ($r['ok']) {
                $db->table('webhook_deliveries')->where('id', $e['id'])->update([
                    'status' => 'delivered', 'attempts' => $intentos, 'claim_token' => null,
                    'last_attempt_at' => $ahora, 'completed_at' => $ahora,
                    'http_status' => $r['status'], 'duration_ms' => $r['ms'], 'error_message' => null,
                ]);
                $db->table('api_webhooks')->where('id', $h['id'])->update([
                    'failure_count' => 0, 'last_delivery_at' => $ahora, 'last_success_at' => $ahora,
                    'last_status_code' => $r['status'], 'updated_at' => $ahora,
                ]);
                $stats['entregados']++;
                continue;
            }

            $muerto = $intentos >= self::MAX_INTENTOS;
            $espera = self::ESPERAS[min($intentos - 1, count(self::ESPERAS) - 1)];
            $db->table('webhook_deliveries')->where('id', $e['id'])->update([
                'status' => $muerto ? 'dead' : 'retry', 'attempts' => $intentos, 'claim_token' => null,
                'last_attempt_at' => $ahora, 'completed_at' => $muerto ? $ahora : null,
                'next_attempt_at' => date('Y-m-d H:i:s', time() + $espera),
                'http_status' => $r['status'], 'duration_ms' => $r['ms'],
                'error_message' => mb_substr((string) $r['error'], 0, 250),
            ]);
            $fallos = (int) $h['failure_count'] + 1;
            $upd = ['failure_count' => $fallos, 'last_delivery_at' => $ahora, 'last_status_code' => $r['status'], 'updated_at' => $ahora];
            if ($fallos >= self::FALLOS_PARA_PAUSAR) {
                $upd['is_active'] = 0;
                $upd['disabled_at'] = $ahora;
                log_message('warning', "[WebhookDispatcher] Webhook {$h['id']} desactivado tras {$fallos} fallos seguidos.");
            }
            $db->table('api_webhooks')->where('id', $h['id'])->update($upd);
            $hooks[(int) $h['id']]['failure_count'] = $fallos;
            $muerto ? $stats['muertos']++ : $stats['fallidos']++;
        }

        return $stats;
    }

    /** Cabecera de firma. Pura: se prueba aparte. */
    public static function signature(string $secret, int $timestamp, string $body): string
    {
        return 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    }

    /**
     * Un envío. Valida la URL, resuelve el host, comprueba que todas sus IP son públicas
     * y fija la conexión a la primera.
     * @return array{ok:bool, status:?int, ms:int, error:?string}
     */
    public static function send(string $url, string $secret, string $evento, string $uuid, string $body): array
    {
        $t0 = microtime(true);
        $fin = fn(bool $ok, ?int $st, ?string $err) => ['ok' => $ok, 'status' => $st, 'ms' => (int) round((microtime(true) - $t0) * 1000), 'error' => $err];

        if (!SafeUrlValidator::isSafeUrl($url)) {
            return $fin(false, null, 'URL no permitida (debe ser HTTPS a un servidor público)');
        }
        $host = strtolower(trim((string) parse_url($url, PHP_URL_HOST), '[]'));
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips = [$host];
        } else {
            $ips = @gethostbynamel($host) ?: [];
        }
        if (empty($ips)) {
            return $fin(false, null, 'No se pudo resolver el dominio');
        }
        foreach ($ips as $ip) {
            if (!SafeUrlValidator::isPublicIp($ip)) {
                return $fin(false, null, 'El dominio resuelve a una IP no pública');
            }
        }

        $ts = time();
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
            CURLOPT_RESOLVE        => [$host . ':443:' . $ips[0]],
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'User-Agent: APIEmpresas-Webhooks/1.0',
                'X-ApiEmpresas-Event: ' . $evento,
                'X-ApiEmpresas-Delivery: ' . $uuid,
                'X-ApiEmpresas-Signature: ' . self::signature($secret, $ts, $body),
            ],
        ]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        if ($err !== null) {
            return $fin(false, $status ?: null, $err);
        }
        $ok = $status >= 200 && $status < 300;
        return $fin($ok, $status, $ok ? null : 'Respuesta HTTP ' . $status);
    }

    /** Envío inmediato de un test.ping (POST /webhooks/{id}/test). Queda registrado. */
    public static function testPing(array $hook): array
    {
        $row = self::deliveryRow($hook, WebhookEvents::TEST_PING, [
            'id'      => 'test.ping:' . bin2hex(random_bytes(6)),
            'message' => 'Prueba de webhook de APIEmpresas.es',
        ]);
        $r = self::send((string) $hook['url'], (string) ($hook['secret'] ?? ''), WebhookEvents::TEST_PING, $row['delivery_uuid'], $row['payload']);
        $ahora = date('Y-m-d H:i:s');
        $row['status'] = $r['ok'] ? 'delivered' : 'dead';
        $row['attempts'] = 1;
        $row['last_attempt_at'] = $ahora;
        $row['completed_at'] = $ahora;
        $row['http_status'] = $r['status'];
        $row['duration_ms'] = $r['ms'];
        $row['error_message'] = $r['error'] !== null ? mb_substr($r['error'], 0, 250) : null;
        try {
            \Config\Database::connect()->table('webhook_deliveries')->insert($row);
        } catch (\Throwable $e) {
            log_message('error', '[WebhookDispatcher::testPing] ' . $e->getMessage());
        }
        return $r + ['delivery_id' => $row['delivery_uuid']];
    }
}
