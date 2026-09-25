<?php

namespace App\Libraries;

/**
 * Eventos del embudo de la API en tracking_events (page = 'embudo').
 *
 *  - signup_completed: un alta, con la vía (element) y la intención. Antes solo
 *    quedaban registradas las altas del formulario y de Google.
 *  - first_call: primera consulta correcta (200) de la cuenta.
 *  - first_integration_call: primera consulta correcta hecha desde fuera de un
 *    navegador (curl, SDK, servidor): la integración real, no la prueba del panel.
 *  - call_10: décima consulta correcta.
 *
 * Las tres últimas las calcula `registrarLlamadas()` a partir de api_requests (lo
 * lanza email:automation cada hora), con la fecha real de la llamada. Nada de esto
 * toca la API ni el alta: cualquier fallo queda en el log y se sigue.
 */
class Embudo
{
    public const PAGE = 'embudo';

    public static function alta(int $userId, string $via, ?string $intent = null): void
    {
        if ($userId <= 0) {
            return;
        }
        try {
            $req = service('request');
            (new \App\Models\TrackingEventModel())->insert([
                'event_name'   => 'signup_completed',
                'page'         => self::PAGE,
                'user_id'      => $userId,
                'session_id'   => substr((string) session_id(), 0, 100),
                'anonymous_id' => '',
                'element'      => substr($via, 0, 255),
                'metadata'     => json_encode([
                    'intent' => (string) ($intent ?? ''),
                    'plan'   => substr(preg_replace('/[^a-z_]/', '', strtolower((string) ($req->getGetPost('plan') ?? ''))), 0, 20),
                    'source' => substr(preg_replace('/[^a-z0-9_\-]/i', '', (string) (session('email_source') ?? '')), 0, 64),
                ]),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Embudo::alta] ' . $e->getMessage());
        }
    }

    /**
     * Revisa las cuentas con consultas correctas en las últimas $horas y registra los
     * hitos que les falten. Devuelve cuántos eventos ha creado.
     */
    public static function registrarLlamadas(int $horas = 48): int
    {
        $creados = 0;
        try {
            $db    = \Config\Database::connect();
            $desde = date('Y-m-d H:i:s', strtotime('-' . $horas . ' hours'));

            $ids = array_map('intval', array_column($db->table('api_requests')
                ->distinct()->select('user_id')
                ->where('status_code', 200)
                ->where('created_at >=', $desde)
                ->where('user_id >', 0)
                ->where('user_id <>', \App\Filters\ApiKeyFilter::MONITOR_USER_ID)
                ->get()->getResultArray(), 'user_id'));
            if (empty($ids)) {
                return 0;
            }

            $hechos = [];
            foreach (array_chunk($ids, 500) as $lote) {
                foreach ($db->table('tracking_events')->select('user_id, event_name')
                            ->where('page', self::PAGE)
                            ->whereIn('event_name', ['first_call', 'first_integration_call', 'call_10'])
                            ->whereIn('user_id', $lote)
                            ->get()->getResultArray() as $h) {
                    $hechos[(int) $h['user_id'] . ':' . $h['event_name']] = true;
                }
            }

            foreach ($ids as $uid) {
                if (!isset($hechos[$uid . ':first_call'])) {
                    $r = $db->table('api_requests')->select('created_at, user_agent, endpoint')
                        ->where('user_id', $uid)->where('status_code', 200)
                        ->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();
                    if ($r) {
                        $creados += self::insertar($uid, 'first_call', $r);
                    }
                }
                if (!isset($hechos[$uid . ':first_integration_call'])) {
                    $r = $db->table('api_requests')->select('created_at, user_agent, endpoint')
                        ->where('user_id', $uid)->where('status_code', 200)
                        ->groupStart()
                            ->where('user_agent IS NULL', null, false)
                            ->orNotLike('user_agent', 'Mozilla/', 'after')
                        ->groupEnd()
                        ->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();
                    if ($r) {
                        $creados += self::insertar($uid, 'first_integration_call', $r);
                    }
                }
                if (!isset($hechos[$uid . ':call_10'])) {
                    $r = $db->table('api_requests')->select('created_at, user_agent, endpoint')
                        ->where('user_id', $uid)->where('status_code', 200)
                        ->orderBy('id', 'ASC')->limit(1, 9)->get()->getRowArray();
                    if ($r) {
                        $creados += self::insertar($uid, 'call_10', $r);
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[Embudo::registrarLlamadas] ' . $e->getMessage());
        }
        return $creados;
    }

    private static function insertar(int $userId, string $evento, array $peticion): int
    {
        $ua     = (string) ($peticion['user_agent'] ?? '');
        $origen = str_starts_with($ua, 'Mozilla/') ? 'navegador' : 'integracion';
        try {
            \Config\Database::connect()->table('tracking_events')->insert([
                'event_name'   => $evento,
                'page'         => self::PAGE,
                'user_id'      => $userId,
                'session_id'   => '',
                'anonymous_id' => '',
                'element'      => $origen,
                'metadata'     => json_encode([
                    'endpoint'   => substr((string) ($peticion['endpoint'] ?? ''), 0, 120),
                    'user_agent' => substr($ua, 0, 120),
                ]),
                'created_at'   => (string) $peticion['created_at'],
            ]);
            return 1;
        } catch (\Throwable $e) {
            log_message('error', '[Embudo::insertar] ' . $e->getMessage());
            return 0;
        }
    }
}
