<?php

namespace App\Libraries;

/**
 * Datos de /admin/email-logs: si los correos (API y Solvencia) se estan enviando de verdad, cuantos, cuales y con que resultado.
 *
 * Fuentes:
 *   - email_logs: cada envio real (lo que acepto o rechazo el servidor SMTP), con template_slug desde el 24-09-2026 (antes, y
 *     en los envios manuales del admin, va vacio), tracking_code y clicked_at / opened_at.
 *   - user_email_automation: lo que el cron `email:automation` decidio enviar (una fila por correo). Cruzandolo con email_logs
 *     se ve si lo decidido salio de verdad. Los nombres no siempre coinciden (no_requests_day1 -> quick_start), asi que se
 *     cruza por usuario y hora (+-10 min), no por nombre.
 *   - users: altas recientes, para ver que todas reciben su bienvenida.
 *
 * Grupos de cada correo (por plantilla; los antiguos/manuales sin plantilla, por la intencion de alta del destinatario):
 *   api | risk (Solvencia) | cuenta (contrasena, facturas, cobros: transaccionales de cualquier producto) | interno (avisos al admin) | otros
 *
 * El usuario monitor (376, status_monitor@apiempresas.es: cuenta de pruebas) no sale en ninguna parte de la pagina: ni en las
 * comprobaciones, ni en los numeros, ni en el historial. Ver sinMonitor().
 */
class CorreosSalud
{
    public const MONITOR_USER_ID = 376;
    public const GRUPOS = [
        'api'     => 'API',
        'risk'    => 'Solvencia',
        'cuenta'  => 'Cuenta y pagos',
        'interno' => 'Avisos internos',
        'otros'   => 'Otros / sin plantilla',
    ];
    public const POR_PAGINA = 30;

    private const CUENTA = ['set_password', 'reset_password', 'login_link', 'user_invoice', 'payment_notification', 'payment_failed',
        'subscription_canceled', 'massive_export_ready', 'api_key_blocked'];

    /** Nombre en castellano de cada correo, para que la tabla se entienda sin conocer los slugs. */
    public const NOMBRES = [
        'welcome_email' => 'Bienvenida API', 'welcome_risk' => 'Bienvenida Solvencia', 'set_password' => 'Crear contraseña (alta rápida)',
        'reset_password' => 'Recuperar contraseña', 'login_link' => 'Enlace de acceso', 'user_invoice' => 'Factura',
        'payment_notification' => 'Pago recibido', 'payment_failed' => 'Cobro fallido', 'subscription_canceled' => 'Suscripción cancelada',
        'admin_registration' => 'Aviso de nuevo registro (a ti)', 'no_requests_15min' => '0 llamadas: a los 15 min',
        'quick_start' => '0 llamadas: día 1', 'inactivity_reminder' => '0 llamadas: día 3', 'no_requests_day14' => '0 llamadas: día 14',
        'no_requests_day30' => '0 llamadas: día 30 (despedida)', 'first_request_success' => 'Primera llamada hecha',
        'one_request_inactive_1h' => '1 llamada y se paró', 'reached_5_requests' => 'Llegó a 5 llamadas', 'reached_80_requests' => '80 % del Free',
        'reached_100_percent_quota' => 'Free agotado', 'api_exhausted_3d' => 'Agotó y no compra: día 3', 'api_exhausted_10d' => 'Agotó y no compra: día 10',
        'api_stalled_7d' => 'Dejó de usar la API: 7 días', 'api_stalled_30d' => 'Dejó de usar la API: 30 días', 'monthly_report' => 'Informe mensual (Free)',
        'bad_request_help' => 'Ayuda por errores 400', 'paid_monthly_summary' => 'Resumen mensual (pago)', 'paid_mid_cycle' => 'Mitad de ciclo (pago)',
        'paid_low_usage' => 'Paga y no usa', 'paid_quota_80' => 'Pago: 80 % del cupo', 'paid_quota_100' => 'Pago: cupo agotado',
        'quota_warning' => 'Aviso de cupo (pago)', 'api_plan_welcome' => 'Bienvenida a plan de pago API', 'api_winback' => 'Recuperar ex-cliente API',
        'risk_first_query_nudge' => 'Solvencia: tras la 1.ª consulta', 'risk_unused_credits_48h' => 'Solvencia: informes sin usar',
        'risk_paywall_abandoned' => 'Solvencia: llegó al límite gratis', 'risk_cartera_resumen' => 'Solvencia: resumen de cartera',
        'risk_monthly_renewal' => 'Solvencia: nuevo mes de informes', 'risk_dormido_14' => 'Solvencia: dormido 14 días',
        'risk_dormido_30' => 'Solvencia: dormido 30 días', 'risk_watch_full' => 'Solvencia: vigilancia llena', 'risk_pro_welcome' => 'Bienvenida Solvencia Pro',
        'risk_pack_welcome' => 'Compra de pack', 'risk_credits_low_upsell' => 'Pocos créditos', 'risk_winback' => 'Recuperar ex-Pro Solvencia',
        'borme_alert' => 'Alerta BORME de vigiladas',
    ];

    private $db;
    private int $now;

    public function __construct(?int $now = null, $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->now = $now ?? time();
    }

    /** Condicion SQL para dejar fuera los correos del usuario monitor (los que no tienen usuario se quedan). */
    private static function sinMonitor(string $col = 'user_id'): string
    {
        return '(' . $col . ' IS NULL OR ' . $col . ' <> ' . self::MONITOR_USER_ID . ')';
    }

    public static function grupo(?string $slug, ?string $intent = null): string
    {
        $slug = (string) $slug;
        if ($slug === '') {
            return $intent === 'view_risk_profile' ? 'risk' : ($intent === 'api' ? 'api' : 'otros');
        }
        if (str_starts_with($slug, 'admin_')) {
            return 'interno';
        }
        if (in_array($slug, self::CUENTA, true)) {
            return 'cuenta';
        }
        if (str_starts_with($slug, 'risk_') || str_starts_with($slug, 'pdf_') || $slug === 'welcome_risk' || $slug === 'borme_alert') {
            return 'risk';
        }

        return 'api';
    }

    public static function nombre(?string $slug): string
    {
        return self::NOMBRES[(string) $slug] ?? (string) $slug;
    }

    private function fecha(int $ts): string
    {
        return date('Y-m-d H:i:s', $ts);
    }

    /** Todo lo de arriba de la pagina (comprobaciones, KPIs, grafico, tabla por correo). */
    public function datos(): array
    {
        $hace30 = $this->now - 30 * 86400;
        $hace7 = $this->now - 7 * 86400;
        $hace14 = $this->now - 14 * 86400;

        // Envios de los ultimos 30 dias (y unos pocos datos del destinatario)
        $rows = $this->db->table('email_logs l')
            ->select('l.id, l.user_id, l.template_slug, l.status, l.created_at, l.clicked_at, l.opened_at, l.tracking_code, l.subject, LEFT(l.error_message, 300) AS error_message, u.signup_intent, u.email', false)
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where('l.created_at >=', $this->fecha($hace30))
            ->where('l.created_at <=', $this->fecha($this->now))
            ->where(self::sinMonitor('l.user_id'), null, false)
            ->orderBy('l.id', 'ASC')
            ->get()->getResultArray();

        $dias = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', $this->now - $i * 86400);
            $dias[$d] = ['dia' => $d, 'api' => 0, 'risk' => 0, 'cuenta' => 0, 'interno' => 0, 'otros' => 0, 'error' => 0];
        }
        $kpi = [];
        foreach (array_keys(self::GRUPOS) as $g) {
            $kpi[$g] = ['7d' => 0, 'prev7d' => 0, '30d' => 0, 'error7d' => 0, 'clics30' => 0, 'trk30' => 0, 'ultimo' => null];
        }
        $tot = ['7d' => 0, 'prev7d' => 0, '30d' => 0, 'error7d' => 0, 'error30d' => 0, 'clics30' => 0, 'trk30' => 0, 'aperturas30' => 0];
        $porPlantilla = [];
        $errores = [];
        $logsPorUsuario = [];

        foreach ($rows as $r) {
            $ts = strtotime($r['created_at']);
            $g = self::grupo($r['template_slug'], $r['signup_intent']);
            $ok = $r['status'] === 'success';
            $d = date('Y-m-d', $ts);
            if (isset($dias[$d])) {
                $dias[$d][$ok ? $g : 'error']++;
            }
            $k = &$kpi[$g];
            $tot['30d']++;
            $k['30d']++;
            if ($ts >= $hace7) {
                $tot['7d']++;
                $k['7d']++;
                if (!$ok) {
                    $tot['error7d']++;
                    $k['error7d']++;
                }
            } elseif ($ts >= $hace14) {
                $tot['prev7d']++;
                $k['prev7d']++;
            }
            if (!$ok) {
                $tot['error30d']++;
                $errores[] = $r;
            }
            if ($ok && $r['tracking_code']) {
                $k['trk30']++;
                $tot['trk30']++;
                if ($r['clicked_at']) {
                    $k['clics30']++;
                    $tot['clics30']++;
                }
            }
            if ($r['opened_at']) {
                $tot['aperturas30']++;
            }
            if ($ok) {
                $k['ultimo'] = max((int) $k['ultimo'], $ts);
            }
            unset($k);

            $slug = $r['template_slug'] ?: '';
            $clave = $slug !== '' ? $slug : '(sin plantilla)';
            $porPlantilla[$clave] ??= ['slug' => $slug, 'nombre' => $slug !== '' ? self::nombre($slug) : 'Sin plantilla (manuales o anteriores al 24/09)',
                'grupo' => $slug !== '' ? $g : 'otros', '7d' => 0, '30d' => 0, 'error' => 0, 'trk' => 0, 'clics' => 0, 'ultimo' => 0];
            $p = &$porPlantilla[$clave];
            $p['30d']++;
            if ($ts >= $hace7) {
                $p['7d']++;
            }
            if (!$ok) {
                $p['error']++;
            } else {
                $p['ultimo'] = max($p['ultimo'], $ts);
                if ($r['tracking_code']) {
                    $p['trk']++;
                    $p['clics'] += $r['clicked_at'] ? 1 : 0;
                }
            }
            unset($p);

            if ($r['user_id']) {
                $logsPorUsuario[(int) $r['user_id']][] = [$ts, $ok, $slug];
            }
        }

        // Correos que se enviaron alguna vez (con plantilla) pero no en los 30 dias: puede ser normal (son raros) o un disparador roto
        $antes = $this->db->query('SELECT template_slug, MAX(created_at) AS ultimo, COUNT(*) AS n FROM email_logs
            WHERE template_slug IS NOT NULL AND template_slug <> \'\' AND status = \'success\' AND created_at < ? AND ' . self::sinMonitor() . ' GROUP BY template_slug', [$this->fecha($hace30)])->getResultArray();
        foreach ($antes as $a) {
            if (!isset($porPlantilla[$a['template_slug']])) {
                $porPlantilla[$a['template_slug']] = ['slug' => $a['template_slug'], 'nombre' => self::nombre($a['template_slug']),
                    'grupo' => self::grupo($a['template_slug']), '7d' => 0, '30d' => 0, 'error' => 0, 'trk' => 0, 'clics' => 0,
                    'ultimo' => strtotime($a['ultimo'])];
            }
        }
        $orden = array_flip(array_keys(self::GRUPOS));
        uasort($porPlantilla, fn ($a, $b) => [$orden[$a['grupo']], -$a['30d'], $a['nombre']] <=> [$orden[$b['grupo']], -$b['30d'], $b['nombre']]);

        // ---------- Comprobacion 1: el cron sigue vivo ----------
        $ultimoCron = $this->db->query('SELECT MAX(sent_at) AS t FROM user_email_automation WHERE sent_at <= ? AND ' . self::sinMonitor(), [$this->fecha($this->now)])->getRow()->t ?? null;
        $ultimoCronTs = $ultimoCron ? strtotime($ultimoCron) : null;
        $horasCron = $ultimoCronTs ? ($this->now - $ultimoCronTs) / 3600 : null;
        // Horas del dia con algun envio del cron en los ultimos 7 dias (para decir "suele enviar cada X h")
        $porHora = $this->db->query('SELECT COUNT(DISTINCT DATE_FORMAT(sent_at, \'%Y-%m-%d %H\')) AS h FROM user_email_automation WHERE sent_at >= ? AND sent_at <= ? AND ' . self::sinMonitor(),
            [$this->fecha($hace7), $this->fecha($this->now)])->getRow()->h ?? 0;

        // ---------- Comprobacion 4: lo que el cron decidio, salio ----------
        $decididos = $this->db->table('user_email_automation')
            ->select('user_id, email_type, sent_at')
            ->where('sent_at >=', $this->fecha($hace7))
            ->where('sent_at <=', $this->fecha($this->now - 600))
            ->where(self::sinMonitor(), null, false)
            ->orderBy('sent_at', 'DESC')
            ->get()->getResultArray();
        $sinEnvio = [];
        $conError = 0;
        foreach ($decididos as $a) {
            $ts = strtotime($a['sent_at']);
            $match = null;
            foreach ($logsPorUsuario[(int) $a['user_id']] ?? [] as $l) {
                if (abs($l[0] - $ts) <= 600) {
                    $match = $l;
                    break;
                }
            }
            if ($match === null) {
                $sinEnvio[] = $a;
            } elseif (!$match[1]) {
                $conError++;
            }
        }
        $nDecididos = count($decididos);

        // ---------- Comprobacion 3: cada alta recibe su bienvenida ----------
        $altas = $this->db->table('users')
            ->select('id, email, signup_intent, created_at')
            ->where('is_admin', 0)
            ->where('id !=', self::MONITOR_USER_ID)
            ->whereIn('signup_intent', ['api', 'view_risk_profile'])
            ->where('created_at >=', $this->fecha($hace7))
            ->where('created_at <=', $this->fecha($this->now - 900)) // 15 min de margen para que salga
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
        $bienvenida = ['api' => ['altas' => 0, 'ok' => 0], 'risk' => ['altas' => 0, 'ok' => 0]];
        $sinBienvenida = [];
        foreach ($altas as $u) {
            $p = $u['signup_intent'] === 'api' ? 'api' : 'risk';
            $bienvenida[$p]['altas']++;
            $recibio = false;
            foreach ($logsPorUsuario[(int) $u['id']] ?? [] as $l) {
                if ($l[1] && in_array($l[2], ['welcome_email', 'welcome_risk', 'set_password'], true)) {
                    $recibio = true;
                    break;
                }
            }
            if ($recibio) {
                $bienvenida[$p]['ok']++;
            } else {
                $sinBienvenida[] = $u;
            }
        }

        // ---------- Bajas de correo ----------
        $bajas = $this->db->query("SELECT signup_intent, SUM(unsuscribe = 1) AS bajas, COUNT(*) AS n FROM users
            WHERE is_admin = 0 AND id <> ? AND signup_intent IN ('api', 'view_risk_profile') GROUP BY signup_intent", [self::MONITOR_USER_ID])->getResultArray();
        $bajasMap = [];
        foreach ($bajas as $b) {
            $bajasMap[$b['signup_intent'] === 'api' ? 'api' : 'risk'] = ['bajas' => (int) $b['bajas'], 'n' => (int) $b['n']];
        }

        // ---------- Semaforo ----------
        $checks = [];
        $checks['cron'] = [
            'estado' => $horasCron === null ? 'mal' : ($horasCron <= 12 ? 'bien' : ($horasCron <= 24 ? 'aviso' : 'mal')),
            'titulo' => 'El envío automático está funcionando',
            'valor' => $horasCron === null ? 'Sin envíos registrados' : 'Último envío hace ' . self::hace($horasCron * 3600),
            'detalle' => $porHora . ' de las últimas 168 horas tuvieron algún envío automático.',
        ];
        $pctErr = $tot['7d'] > 0 ? $tot['error7d'] / $tot['7d'] * 100 : 0;
        $checks['smtp'] = [
            'estado' => $tot['7d'] === 0 ? 'aviso' : ($pctErr == 0 ? 'bien' : ($pctErr < 5 ? 'aviso' : 'mal')),
            'titulo' => 'El servidor de correo acepta los envíos',
            'valor' => $tot['error7d'] . ' fallos de ' . $tot['7d'] . ' en 7 días',
            'detalle' => $tot['error30d'] > 0 ? $tot['error30d'] . ' fallos en 30 días.' : 'Ningún fallo en 30 días.',
        ];
        $nAltas = $bienvenida['api']['altas'] + $bienvenida['risk']['altas'];
        $nOk = $bienvenida['api']['ok'] + $bienvenida['risk']['ok'];
        $pctBienv = $nAltas > 0 ? $nOk / $nAltas * 100 : 100;
        $checks['bienvenida'] = [
            'estado' => $nAltas === 0 ? 'aviso' : ($pctBienv >= 100 ? 'bien' : ($pctBienv >= 90 ? 'aviso' : 'mal')),
            'titulo' => 'Cada alta recibe su bienvenida',
            'valor' => $nAltas === 0 ? 'Sin altas en 7 días' : "{$nOk} de {$nAltas} altas en 7 días",
            'detalle' => "API {$bienvenida['api']['ok']}/{$bienvenida['api']['altas']} · Solvencia {$bienvenida['risk']['ok']}/{$bienvenida['risk']['altas']}",
        ];
        $pctCoh = $nDecididos > 0 ? ($nDecididos - count($sinEnvio) - $conError) / $nDecididos * 100 : 100;
        $checks['coherencia'] = [
            'estado' => $nDecididos === 0 ? 'aviso' : ($pctCoh >= 98 ? 'bien' : ($pctCoh >= 90 ? 'aviso' : 'mal')),
            'titulo' => 'Lo que decide el cron, sale',
            'valor' => $nDecididos === 0 ? 'El cron no decidió nada en 7 días' : ($nDecididos - count($sinEnvio) - $conError) . " de {$nDecididos} enviados (" . str_replace('.', ',', (string) round($pctCoh, 1)) . '%)',
            'detalle' => count($sinEnvio) . ' sin envío registrado · ' . $conError . ' con error SMTP.',
        ];
        $peor = 'bien';
        foreach ($checks as $c) {
            if ($c['estado'] === 'mal' || ($c['estado'] === 'aviso' && $peor === 'bien')) {
                $peor = $c['estado'];
            }
        }

        return [
            'ahora' => $this->now,
            'dias' => array_values($dias),
            'kpi' => $kpi,
            'tot' => $tot,
            'por_plantilla' => array_values($porPlantilla),
            'errores' => $this->agruparErrores($errores),
            'checks' => $checks,
            'estado_general' => $peor,
            'sin_envio' => array_slice($sinEnvio, 0, 10),
            'n_sin_envio' => count($sinEnvio),
            'sin_bienvenida' => $sinBienvenida,
            'bienvenida' => $bienvenida,
            'bajas' => $bajasMap,
            'decididos' => $nDecididos,
        ];
    }

    /**
     * Fallos agrupados por correo + mensaje del servidor (suelen repetirse: el mismo fallo cada hora), los mas recientes primero.
     *
     * @return list<array{nombre: string, slug: string, n: int, ultimo: int, primero: int, destinatarios: list<string>, mensaje: string}>
     */
    private function agruparErrores(array $errores): array
    {
        $g = [];
        foreach ($errores as $e) {
            $msg = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['<br>', '\\n', '\n'], ' ', (string) $e['error_message']))));
            $k = ($e['template_slug'] ?: $e['subject']) . '|' . mb_substr($msg, 0, 80);
            $ts = strtotime($e['created_at']);
            $g[$k] ??= ['nombre' => $e['template_slug'] ? self::nombre($e['template_slug']) : ((string) $e['subject'] ?: 'Sin asunto'),
                'slug' => (string) $e['template_slug'], 'n' => 0, 'ultimo' => 0, 'primero' => PHP_INT_MAX, 'destinatarios' => [], 'mensaje' => $msg];
            $g[$k]['n']++;
            $g[$k]['ultimo'] = max($g[$k]['ultimo'], $ts);
            $g[$k]['primero'] = min($g[$k]['primero'], $ts);
            $dest = (string) ($e['email'] ?: ('#' . $e['user_id']));
            if (!in_array($dest, $g[$k]['destinatarios'], true)) {
                $g[$k]['destinatarios'][] = $dest;
            }
        }
        usort($g, fn ($a, $b) => $b['ultimo'] <=> $a['ultimo']);

        return array_slice($g, 0, 5);
    }

    /**
     * Historial de envios con filtros (los de la pagina de siempre + grupo y plantilla).
     *
     * @return array{rows: array, total: int}
     */
    public function historial(array $f, int $page): array
    {
        $b = $this->db->table('email_logs l')
            ->select('l.id, l.user_id, l.subject, l.status, l.template_slug, l.created_at, l.opened_at, l.clicked_at, l.logged_in_at, LEFT(l.error_message, 300) AS error_message, u.name AS user_name, u.email AS user_email, u.signup_intent', false)
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where(self::sinMonitor('l.user_id'), null, false);

        if (($f['q'] ?? '') !== '') {
            $b->groupStart()->like('u.name', $f['q'])->orLike('u.email', $f['q'])->orLike('l.subject', $f['q'])->groupEnd();
        }
        if (in_array($f['status'] ?? '', ['success', 'error'], true)) {
            $b->where('l.status', $f['status']);
        }
        if (!empty($f['user_id'])) {
            $b->where('l.user_id', (int) $f['user_id']);
        }
        foreach (['opened' => 'opened_at', 'clicked' => 'clicked_at', 'logged' => 'logged_in_at'] as $k => $col) {
            if (($f[$k] ?? '') === 'yes') {
                $b->where("l.{$col} IS NOT NULL", null, false);
            } elseif (($f[$k] ?? '') === 'no') {
                $b->where("l.{$col} IS NULL", null, false);
            }
        }
        if (($f['plantilla'] ?? '') !== '') {
            if ($f['plantilla'] === '-') {
                $b->groupStart()->where('l.template_slug IS NULL', null, false)->orWhere('l.template_slug', '')->groupEnd();
            } else {
                $b->where('l.template_slug', $f['plantilla']);
            }
        }
        if (isset(self::GRUPOS[$f['grupo'] ?? ''])) {
            $this->filtroGrupo($b, $f['grupo']);
        }
        if (!empty($f['date_from']) && strtotime($f['date_from'])) {
            $b->where('l.created_at >=', date('Y-m-d 00:00:00', strtotime($f['date_from'])));
        }
        if (!empty($f['date_to']) && strtotime($f['date_to'])) {
            $b->where('l.created_at <=', date('Y-m-d 23:59:59', strtotime($f['date_to'])));
        }

        $total = (clone $b)->countAllResults();
        $rows = $b->orderBy('l.id', 'DESC')->limit(self::POR_PAGINA, max(0, ($page - 1) * self::POR_PAGINA))->get()->getResultArray();

        return ['rows' => $rows, 'total' => $total];
    }

    /** El mismo criterio que grupo(), en SQL. */
    private function filtroGrupo($b, string $g): void
    {
        $cuenta = "'" . implode("','", self::CUENTA) . "'";
        $sinSlug = "(l.template_slug IS NULL OR l.template_slug = '')";
        $esRisk = "(l.template_slug LIKE 'risk\\_%' OR l.template_slug LIKE 'pdf\\_%' OR l.template_slug IN ('welcome_risk', 'borme_alert'))";
        $sql = match ($g) {
            'interno' => "l.template_slug LIKE 'admin\\_%'",
            'cuenta'  => "l.template_slug IN ({$cuenta})",
            'risk'    => "({$esRisk} OR ({$sinSlug} AND u.signup_intent = 'view_risk_profile'))",
            'otros'   => "({$sinSlug} AND (u.signup_intent IS NULL OR u.signup_intent NOT IN ('api', 'view_risk_profile')))",
            default   => "((NOT {$sinSlug} AND l.template_slug NOT LIKE 'admin\\_%' AND l.template_slug NOT IN ({$cuenta}) AND NOT {$esRisk}) OR ({$sinSlug} AND u.signup_intent = 'api'))",
        };
        $b->where($sql, null, false);
    }

    /** Plantillas que aparecen en el historial, para el filtro. */
    public function plantillas(): array
    {
        $rows = $this->db->query("SELECT DISTINCT template_slug FROM email_logs WHERE template_slug IS NOT NULL AND template_slug <> '' AND " . self::sinMonitor())->getResultArray();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['template_slug']] = self::nombre($r['template_slug']);
        }
        asort($out);

        return $out;
    }

    public static function hace(float $seg): string
    {
        $seg = max(0, $seg);
        if ($seg < 3600) {
            return max(1, (int) round($seg / 60)) . ' min';
        }
        if ($seg < 86400 * 2) {
            return (int) round($seg / 3600) . ' h';
        }

        return (int) round($seg / 86400) . ' días';
    }
}
