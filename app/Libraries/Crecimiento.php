<?php

namespace App\Libraries;

/**
 * Datos de la pagina /admin/crecimiento: como evolucionan las altas de la API y de Solvencia (view_risk_profile),
 * la activacion, el paso a pago, las suscripciones, el MRR y lo facturado, mes a mes, con una prevision sencilla.
 *
 * Mismas definiciones que las paginas de analitica que ya existen (Admin\ApiAnalytics, Admin\RiskProfileAnalytics), para
 * que los numeros cuadren entre paginas:
 *   - Usuario API:        users.signup_intent = 'api'; Solvencia: 'view_risk_profile'. Sin administradores ni el usuario
 *                         monitor interno (376). Las otras intenciones (radar, alertas, copiloto) no entran.
 *   - Activado:           API = al menos 1 peticion (api_usage_daily); Solvencia = al menos 1 'view_risk_profile' (user_events).
 *   - Suscriptor de pago: suscripcion a un plan con precio de producto 'api' o 'risk' (Solvencia Pro). Se reconstruye cuantos
 *                         habia a final de cada mes con created_at y canceled_at.
 *   - MRR:                precio mensual del plan; los anuales (periodo de ~1 ano) cuentan su precio anual / 12.
 *   - Facturado:          invoices pagadas (base sin IVA) por mes de la factura. Incluye devoluciones (importes negativos).
 *   - Bajas:              suscripciones de pago canceladas. No cuentan como baja los cambios de plan (cancelar Pro y contratar
 *                         Business o Pro anual el mismo dia): ni como baja ni como alta nueva. Las filas duplicadas (misma
 *                         suscripcion guardada dos veces, mismo usuario, plan y minuto) cuentan una vez. El motivo solo existe
 *                         si se cancelo desde la web (Billing); si se cancelo en Stripe (portal o impago) va vacio.
 *
 * La prevision NO es un modelo: media de los 3 ultimos meses completos + la mitad de la tendencia de los 6 ultimos, con un
 * margen de +- la variacion tipica. Para el mes en curso se mezcla el ritmo que lleva con esa media (cuanto mas avanzado
 * el mes, mas pesa el ritmo real).
 */
class Crecimiento
{
    /** @deprecated Las cuentas que no cuentan estan en Config\UsuariosInternos (229 y 376). */
    public const MONITOR_USER_ID = 376;
    public const MESES = 12;              // meses que se ensenan (incluido el actual)
    public const MESES_PREVISION = 3;     // meses futuros que se estiman
    public const DIAS_COHORTE_MADURA = 60; // conversion a pago: solo altas con al menos estos dias (las nuevas aun no han tenido tiempo)

    public const PRODUCTOS = [
        'api'  => ['intent' => 'api', 'label' => 'API'],
        'risk' => ['intent' => 'view_risk_profile', 'label' => 'Solvencia'],
    ];

    /** Motivos de baja, como en Billing::cancelSubscription. */
    public const MOTIVOS = [
        'too_expensive'     => 'Precio',
        'missing_features'  => 'Faltan funcionalidades',
        'low_usage'         => 'Poco uso',
        'technical_issues'  => 'Problemas técnicos',
        'switched_solution' => 'Usa otra solución',
        'temporary_pause'   => 'Pausa temporal',
        'other'             => 'Otro motivo',
        'prefer_not_to_say' => 'Prefirió no responder',
        ''                  => 'Sin motivo (cancelada en Stripe)',
    ];

    private const MESES_ES = [1 => 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    private const MESES_LARGOS = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    private $db;
    private int $now;

    public function __construct(?int $now = null, $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->now = $now ?? time();
    }

    public static function mesCorto(string $ym): string
    {
        [$y, $m] = array_map('intval', explode('-', $ym));

        return self::MESES_ES[$m] . ' ' . substr((string) $y, 2);
    }

    /** "jul–sep" (o "nov 25–ene 26" si cambia de ano). */
    public static function rango(array $meses): string
    {
        $a = reset($meses);
        $b = end($meses);
        if (substr($a, 0, 4) === substr($b, 0, 4)) {
            return self::MESES_ES[(int) substr($a, 5, 2)] . '–' . self::MESES_ES[(int) substr($b, 5, 2)];
        }

        return self::mesCorto($a) . '–' . self::mesCorto($b);
    }

    public static function mesLargo(string $ym): string
    {
        return self::MESES_LARGOS[(int) substr($ym, 5, 2)];
    }

    /** Todo lo que necesita la pagina. */
    public function datos(): array
    {
        $mesActual = date('Y-m', $this->now);
        $meses = [];
        for ($i = self::MESES - 1; $i >= 0; $i--) {
            $meses[] = date('Y-m', strtotime("first day of -{$i} month", strtotime(date('Y-m-01', $this->now))));
        }
        $futuros = [];
        for ($i = 1; $i <= self::MESES_PREVISION; $i++) {
            $futuros[] = date('Y-m', strtotime("first day of +{$i} month", strtotime(date('Y-m-01', $this->now))));
        }

        $usuarios = $this->usuarios();
        $activados = $this->activados();
        $subs = $this->suscripciones();
        $facturado = $this->facturado();

        $pagaronAlgunaVez = [];
        foreach ($subs as $s) {
            $pagaronAlgunaVez[$s['user_id']] = true;
        }

        // ---------- Altas por mes y producto, con activacion y paso a pago de cada cohorte ----------
        $porMes = [];
        foreach ($meses as $m) {
            $porMes[$m] = ['mes' => $m, 'api' => 0, 'risk' => 0, 'total' => 0,
                'activados' => ['api' => 0, 'risk' => 0], 'pagan' => ['api' => 0, 'risk' => 0]];
        }
        $totales = ['api' => 0, 'risk' => 0];
        $madura = ['api' => ['altas' => 0, 'pagan' => 0, 'activados' => 0], 'risk' => ['altas' => 0, 'pagan' => 0, 'activados' => 0]];
        $ultimos30 = ['api' => 0, 'risk' => 0];
        $limiteMadura = $this->now - self::DIAS_COHORTE_MADURA * 86400;

        foreach ($usuarios as $u) {
            $p = $u['producto'];
            $ts = strtotime($u['created_at']);
            $totales[$p]++;
            if ($ts >= $this->now - 30 * 86400) {
                $ultimos30[$p]++;
            }
            $act = isset($activados[$p][$u['id']]);
            $paga = isset($pagaronAlgunaVez[$u['id']]);
            if ($ts <= $limiteMadura) {
                $madura[$p]['altas']++;
                $madura[$p]['pagan'] += $paga ? 1 : 0;
                $madura[$p]['activados'] += $act ? 1 : 0;
            }
            $m = date('Y-m', $ts);
            if (isset($porMes[$m])) {
                $porMes[$m][$p]++;
                $porMes[$m]['total']++;
                $porMes[$m]['activados'][$p] += $act ? 1 : 0;
                $porMes[$m]['pagan'][$p] += $paga ? 1 : 0;
            }
        }

        // ---------- Suscripciones: altas, bajas, activas y MRR a final de cada mes ----------
        foreach ($meses as $m) {
            $ini = strtotime($m . '-01 00:00:00');
            $fin = $m === $mesActual ? $this->now : strtotime(date('Y-m-t 23:59:59', $ini));
            $mov = ['nuevas' => ['api' => 0, 'risk' => 0], 'bajas' => ['api' => 0, 'risk' => 0],
                'activas' => ['api' => 0, 'risk' => 0], 'mrr' => 0.0, 'mrr_prod' => ['api' => 0.0, 'risk' => 0.0]];
            foreach ($subs as $s) {
                if ($s['desde'] >= $ini && $s['desde'] <= $fin && $s['viene_de'] === null) {
                    $mov['nuevas'][$s['producto']]++;
                }
                if ($s['hasta'] !== null && $s['hasta'] >= $ini && $s['hasta'] <= $fin && $s['cambio_a'] === null) {
                    $mov['bajas'][$s['producto']]++;
                }
                if ($s['desde'] <= $fin && ($s['hasta'] === null || $s['hasta'] > $fin)) {
                    $mov['activas'][$s['producto']]++;
                    $mov['mrr'] += $s['mrr'];
                    $mov['mrr_prod'][$s['producto']] += $s['mrr'];
                }
            }
            $porMes[$m]['subs'] = $mov;
            $porMes[$m]['facturado'] = $facturado[$m] ?? 0.0;
        }

        // ---------- Mes en curso: ritmo y comparacion con el mismo punto del mes anterior ----------
        $diasMes = (int) date('t', $this->now);
        $transcurrido = (int) date('j', $this->now) - 1 + ((int) date('G', $this->now) * 60 + (int) date('i', $this->now)) / 1440;
        $transcurrido = max(0.25, $transcurrido);
        $mesAnterior = $meses[count($meses) - 2];
        $mismoPuntoAnterior = $this->mismoPunto($usuarios, $mesAnterior);

        // ---------- Previsiones ----------
        $completos = array_slice($meses, 0, -1);
        $prevision = ['meses' => $futuros];
        $actual = [];
        foreach (['api', 'risk'] as $p) {
            $serie = $this->serieDesdeInicio(array_map(fn ($m) => $porMes[$m][$p], $completos));
            $f = $this->prever($serie, self::MESES_PREVISION + 1);
            $ritmo = $porMes[$mesActual][$p] / $transcurrido * $diasMes;
            $peso = min(1, $transcurrido / $diasMes);
            $base0 = $f[0]['base'];
            $cierre = $peso * $ritmo + (1 - $peso) * $base0;
            $margen = max($f[0]['high'] - $f[0]['base'], 1) * (1 - $peso);
            $actual[$p] = [
                'mtd' => $porMes[$mesActual][$p],
                'cierre' => max($porMes[$mesActual][$p], round($cierre)),
                'low' => max($porMes[$mesActual][$p], (int) floor($cierre - $margen)),
                'high' => (int) ceil($cierre + $margen),
                'mismo_punto_anterior' => $mismoPuntoAnterior[$p],
            ];
            $prevision[$p] = array_slice($f, 1);
        }

        // Pago: suscriptores netos y MRR (media de los 3 ultimos meses completos)
        $ult3 = array_slice($completos, -3);
        $netoSubs = 0;
        $netoMrr = 0.0;
        $prevMes = null;
        foreach (array_slice($completos, -4) as $m) {
            if ($prevMes !== null && in_array($m, $ult3, true)) {
                $netoSubs += array_sum($porMes[$m]['subs']['activas']) - array_sum($porMes[$prevMes]['subs']['activas']);
                $netoMrr += $porMes[$m]['subs']['mrr'] - $porMes[$prevMes]['subs']['mrr'];
            }
            $prevMes = $m;
        }
        $netoSubs = $netoSubs / max(1, count($ult3));
        $netoMrr = $netoMrr / max(1, count($ult3));
        $subsHoy = array_sum($porMes[$mesActual]['subs']['activas']);
        $mrrHoy = $porMes[$mesActual]['subs']['mrr'];
        $prevision['subs'] = [];
        $prevision['mrr'] = [];
        foreach ($futuros as $i => $m) {
            $prevision['subs'][] = max(0, (int) round($subsHoy + $netoSubs * ($i + 1)));
            $prevision['mrr'][] = max(0, round($mrrHoy + $netoMrr * ($i + 1), 2));
        }
        $prevision['neto_subs_mes'] = round($netoSubs, 1);
        $prevision['neto_mrr_mes'] = round($netoMrr, 2);

        // Conversion de las cohortes maduras aplicada a las altas previstas
        $conv = [];
        foreach (['api', 'risk'] as $p) {
            $conv[$p] = $madura[$p]['altas'] > 0 ? $madura[$p]['pagan'] / $madura[$p]['altas'] : null;
        }

        $planes = $this->planesActivos($subs);
        [$bajas, $motivos] = $this->bajas($subs);

        $datos = [
            'ahora' => date('Y-m-d H:i', $this->now),
            'mes_actual' => $mesActual,
            'mes_anterior' => $mesAnterior,
            'dias_mes' => $diasMes,
            'dia' => (int) date('j', $this->now),
            'meses' => $meses,
            'por_mes' => $porMes,
            'totales' => $totales,
            'ultimos30' => $ultimos30,
            'madura' => $madura,
            'conversion' => $conv,
            'actual' => $actual,
            'prevision' => $prevision,
            'subs_hoy' => $subsHoy,
            'mrr_hoy' => round($mrrHoy, 2),
            'planes' => $planes,
            'facturado_12m' => round(array_sum(array_map(fn ($m) => $porMes[$m]['facturado'], $meses)), 2),
            'bajas' => $bajas,
            'motivos' => $motivos,
        ];
        $datos['lectura'] = $this->lectura($datos);

        return $datos;
    }

    // ───────────── Consultas ─────────────

    private function usuarios(): array
    {
        $intents = array_column(self::PRODUCTOS, 'intent');
        $rows = $this->db->table('users')
            ->select('id, signup_intent, created_at')
            ->whereIn('signup_intent', $intents)
            ->where('is_admin', 0)
            ->whereNotIn('id', \Config\UsuariosInternos::IDS)
            ->where('created_at IS NOT NULL', null, false)
            ->where('created_at <=', date('Y-m-d H:i:s', $this->now))
            ->get()->getResultArray();

        $porIntent = [];
        foreach (self::PRODUCTOS as $clave => $p) {
            $porIntent[$p['intent']] = $clave;
        }
        foreach ($rows as &$r) {
            $r['id'] = (int) $r['id'];
            $r['producto'] = $porIntent[$r['signup_intent']];
        }
        unset($r);

        return $rows;
    }

    /** @return array{api: array<int, true>, risk: array<int, true>} */
    private function activados(): array
    {
        $out = ['api' => [], 'risk' => []];
        try {
            foreach ($this->db->query('SELECT DISTINCT user_id FROM api_usage_daily WHERE requests_count > 0')->getResultArray() as $r) {
                $out['api'][(int) $r['user_id']] = true;
            }
        } catch (\Throwable $e) {
        }
        try {
            foreach ($this->db->query("SELECT DISTINCT user_id FROM user_events WHERE event_type = 'view_risk_profile'")->getResultArray() as $r) {
                $out['risk'][(int) $r['user_id']] = true;
            }
        } catch (\Throwable $e) {
        }

        return $out;
    }

    /** Suscripciones de pago (API y Solvencia) con su periodo de vida, su MRR y, si terminaron, el motivo. */
    private function suscripciones(): array
    {
        $rows = $this->db->table('user_subscriptions us')
            ->select('us.id, us.user_id, us.plan_id, us.status, us.created_at, us.canceled_at, us.current_period_start, us.current_period_end,
                us.cancellation_reason, us.cancellation_feedback, u.email, u.name AS user_name, u.signup_intent,
                ap.slug, ap.name AS plan, ap.product_type, ap.price_monthly, ap.price_annual')
            ->join('api_plans ap', 'ap.id = us.plan_id')
            ->join('users u', 'u.id = us.user_id')
            ->where('u.is_admin', 0)
            ->whereNotIn('us.user_id', \Config\UsuariosInternos::IDS)
            ->where('ap.price_monthly >', 0)
            ->whereIn('ap.product_type', ['api', 'risk'])
            // Las filas antiguas con estado vacio y fecha de cancelacion son suscripciones canceladas
            ->groupStart()
                ->whereIn('us.status', ['active', 'past_due', 'canceled'])
                ->orGroupStart()->where('us.status', '')->where('us.canceled_at IS NOT NULL', null, false)->groupEnd()
            ->groupEnd()
            ->orderBy('us.id', 'ASC')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $desde = strtotime((string) $r['created_at']);
            if (!$desde) {
                continue;
            }
            // Duplicados: la misma suscripcion guardada dos veces (mismo usuario, plan y minuto). Se queda una; si una trae motivo, esa.
            $clave = $r['user_id'] . '|' . $r['plan_id'] . '|' . date('Y-m-d H:i', $desde);
            if (isset($out[$clave])) {
                if ($out[$clave]['motivo'] === '' && (string) $r['cancellation_reason'] !== '') {
                    $out[$clave]['motivo'] = (string) $r['cancellation_reason'];
                    $out[$clave]['comentario'] = trim((string) $r['cancellation_feedback']);
                }
                continue;
            }
            $hasta = null;
            if (!empty($r['canceled_at'])) {
                $hasta = strtotime($r['canceled_at']);
            } elseif ($r['status'] === 'canceled') {
                $hasta = strtotime((string) ($r['current_period_end'] ?: $r['created_at']));
            }
            // Anual: periodo de ~1 ano y el plan tiene precio anual
            $dias = ($r['current_period_start'] && $r['current_period_end'])
                ? (strtotime($r['current_period_end']) - strtotime($r['current_period_start'])) / 86400 : 0;
            $anual = $dias >= 300 && $dias <= 400 && (float) $r['price_annual'] > 0;
            $out[$clave] = [
                'id' => (int) $r['id'],
                'user_id' => (int) $r['user_id'],
                'email' => (string) $r['email'],
                'nombre' => (string) $r['user_name'],
                'intent' => (string) $r['signup_intent'],
                'producto' => $r['product_type'] === 'risk' ? 'risk' : 'api',
                'plan' => (string) $r['plan'],
                'anual' => $anual,
                'desde' => $desde,
                'hasta' => $hasta,
                'mrr' => $anual ? (float) $r['price_annual'] / 12 : (float) $r['price_monthly'],
                'motivo' => (string) $r['cancellation_reason'],
                'comentario' => trim((string) $r['cancellation_feedback']),
                'cambio_a' => null,   // termino porque cambio a este plan (no es una baja)
                'viene_de' => null,   // empezo como cambio desde este plan (no es una alta nueva)
            ];
        }
        $out = array_values($out);

        // Cambios de plan: termina una y el mismo usuario empieza otra de pago en +-1 dia (Pro -> Business, mensual -> anual)
        foreach ($out as $i => $a) {
            if ($a['hasta'] === null) {
                continue;
            }
            foreach ($out as $j => $b) {
                if ($i !== $j && $b['user_id'] === $a['user_id'] && $b['viene_de'] === null && abs($b['desde'] - $a['hasta']) <= 86400 && $b['desde'] > $a['desde']) {
                    $out[$i]['cambio_a'] = $b['plan'] . ($b['anual'] ? ' anual' : '');
                    $out[$j]['viene_de'] = $a['plan'] . ($a['anual'] ? ' anual' : '');
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * Bajas de clientes de pago (sin cambios de plan), la mas reciente primero, y el recuento por motivo.
     *
     * @return array{0: list<array<string, mixed>>, 1: list<array{motivo: string, label: string, n: int}>}
     */
    private function bajas(array $subs): array
    {
        $lista = array_values(array_filter($subs, fn ($s) => $s['hasta'] !== null && $s['hasta'] <= $this->now && $s['cambio_a'] === null));
        if (!$lista) {
            return [[], []];
        }
        $ids = array_values(array_unique(array_column($lista, 'user_id')));

        // Lo que pago cada uno (facturas pagadas, sin IVA)
        $pagos = [];
        try {
            foreach ($this->db->table('invoices')->select('user_id, COUNT(*) AS n, SUM(amount) AS total')->where('status', 'paid')->where('amount >', 0)
                ->whereIn('user_id', $ids)->groupBy('user_id')->get()->getResultArray() as $r) {
                $pagos[(int) $r['user_id']] = ['n' => (int) $r['n'], 'total' => (float) $r['total']];
            }
        } catch (\Throwable $e) {
        }
        // Ultimo uso despues de la baja: API (api_usage_daily) y Solvencia (user_events)
        $usoApi = [];
        $usoRisk = [];
        try {
            foreach ($this->db->table('api_usage_daily')->select('user_id, MAX(date) AS d')->where('requests_count >', 0)->whereIn('user_id', $ids)->groupBy('user_id')->get()->getResultArray() as $r) {
                $usoApi[(int) $r['user_id']] = strtotime($r['d'] . ' 12:00:00');
            }
            foreach ($this->db->table('user_events')->select('user_id, MAX(created_at) AS d')->where('event_type', 'view_risk_profile')->whereIn('user_id', $ids)->groupBy('user_id')->get()->getResultArray() as $r) {
                $usoRisk[(int) $r['user_id']] = strtotime($r['d']);
            }
        } catch (\Throwable $e) {
        }

        $motivos = [];
        foreach ($lista as &$b) {
            $uid = $b['user_id'];
            $b['dias'] = max(0, (int) round(($b['hasta'] - $b['desde']) / 86400));
            $b['pagos'] = $pagos[$uid] ?? ['n' => 0, 'total' => 0.0];
            // Hoy: ha vuelto a pagar (otra suscripcion empezada despues y viva), o su ultimo uso del producto
            $b['volvio'] = null;
            foreach ($subs as $s) {
                if ($s['user_id'] === $uid && $s['desde'] > $b['hasta'] && ($s['hasta'] === null || $s['hasta'] > $this->now)) {
                    $b['volvio'] = $s['plan'] . ($s['anual'] ? ' anual' : '');
                }
            }
            $uso = $b['producto'] === 'risk' ? ($usoRisk[$uid] ?? null) : ($usoApi[$uid] ?? null);
            $b['ultimo_uso'] = $uso;
            $b['usa_despues'] = $uso !== null && $uso > $b['hasta'];
            $b['motivo_label'] = self::MOTIVOS[$b['motivo']] ?? $b['motivo'];
            $motivos[$b['motivo']] = ($motivos[$b['motivo']] ?? 0) + 1;
        }
        unset($b);
        usort($lista, fn ($a, $b) => $b['hasta'] <=> $a['hasta']);
        arsort($motivos);
        $resumen = [];
        foreach ($motivos as $m => $n) {
            $resumen[] = ['motivo' => (string) $m, 'label' => self::MOTIVOS[$m] ?? (string) $m, 'n' => $n];
        }

        return [$lista, $resumen];
    }

    /** @return array<string, float> base sin IVA por mes */
    private function facturado(): array
    {
        try {
            $rows = $this->db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, SUM(amount) AS t FROM invoices WHERE status = 'paid' GROUP BY m")->getResultArray();

            return array_map('floatval', array_column($rows, 't', 'm'));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Altas del mes $ym hasta el mismo dia y hora que hoy (para comparar el mes en curso de forma justa). */
    private function mismoPunto(array $usuarios, string $ym): array
    {
        $ini = strtotime($ym . '-01 00:00:00');
        $dia = min((int) date('j', $this->now), (int) date('t', $ini));
        $corte = strtotime($ym . '-' . sprintf('%02d', $dia) . ' ' . date('H:i:s', $this->now));
        $out = ['api' => 0, 'risk' => 0];
        foreach ($usuarios as $u) {
            $ts = strtotime($u['created_at']);
            if ($ts >= $ini && $ts <= $corte) {
                $out[$u['producto']]++;
            }
        }

        return $out;
    }

    /** Planes de pago activos hoy: nombre => [n, mrr]. */
    private function planesActivos(array $subs): array
    {
        $out = [];
        foreach ($subs as $s) {
            if ($s['desde'] <= $this->now && ($s['hasta'] === null || $s['hasta'] > $this->now)) {
                $k = $s['plan'] . ($s['anual'] ? ' (anual)' : '');
                $out[$k] ??= ['plan' => $k, 'producto' => $s['producto'], 'n' => 0, 'mrr' => 0.0];
                $out[$k]['n']++;
                $out[$k]['mrr'] += $s['mrr'];
            }
        }
        usort($out, fn ($a, $b) => $b['mrr'] <=> $a['mrr']);

        return $out;
    }

    // ───────────── Prevision ─────────────

    /** Quita los ceros iniciales (meses antes de que existiera el producto): no son "0 altas", es que no habia producto. */
    private function serieDesdeInicio(array $serie): array
    {
        while ($serie && reset($serie) === 0) {
            array_shift($serie);
        }

        return array_values($serie);
    }

    /**
     * @param list<int|float> $serie meses completos, del mas antiguo al mas reciente
     * @return list<array{base: int, low: int, high: int}> los $n meses siguientes
     */
    private function prever(array $serie, int $n): array
    {
        $out = [];
        $k = count($serie);
        if ($k === 0) {
            return array_fill(0, $n, ['base' => 0, 'low' => 0, 'high' => 0]);
        }
        $ult3 = array_slice($serie, -3);
        $nivel = array_sum($ult3) / count($ult3);

        // Tendencia (minimos cuadrados) de los 6 ultimos meses, a la mitad para no exagerarla
        $ult6 = array_slice($serie, -6);
        $m = count($ult6);
        $pendiente = 0.0;
        $dispersion = 0.0;
        if ($m >= 3) {
            $xm = ($m - 1) / 2;
            $ym = array_sum($ult6) / $m;
            $num = $den = 0.0;
            foreach ($ult6 as $x => $y) {
                $num += ($x - $xm) * ($y - $ym);
                $den += ($x - $xm) ** 2;
            }
            $pendiente = $den > 0 ? $num / $den : 0.0;
            $res = 0.0;
            foreach ($ult6 as $x => $y) {
                $res += ($y - ($ym + $pendiente * ($x - $xm))) ** 2;
            }
            $dispersion = sqrt($res / max(1, $m - 2));
        } else {
            // Con 1-2 meses de historia: margen amplio (la mitad del nivel)
            $dispersion = $nivel * 0.5;
        }
        $pendiente *= 0.5;
        $dispersion = max($dispersion, $nivel * 0.15);

        // El nivel (media de 3) esta centrado en el mes k-2 (o el centro de lo que haya)
        $centro = (count($ult3) - 1) / 2;
        for ($i = 1; $i <= $n; $i++) {
            $pasos = (count($ult3) - 1 - $centro) + $i;
            $base = max(0, $nivel + $pendiente * $pasos);
            $margen = $dispersion * sqrt($i);
            $out[] = ['base' => (int) round($base), 'low' => (int) max(0, floor($base - $margen)), 'high' => (int) ceil($base + $margen)];
        }

        return $out;
    }

    // ───────────── Lectura rapida (frases) ─────────────

    private function lectura(array $d): array
    {
        $out = [];
        $pm = $d['por_mes'];
        $ma = $d['mes_actual'];
        $mp = $d['mes_anterior'];
        $act = $d['actual'];

        // 1. Ritmo del mes en curso frente al mismo punto del anterior
        $hoy = $act['api']['mtd'] + $act['risk']['mtd'];
        $antes = $act['api']['mismo_punto_anterior'] + $act['risk']['mismo_punto_anterior'];
        $cierre = $act['api']['cierre'] + $act['risk']['cierre'];
        $totalAnterior = $pm[$mp]['total'];
        if ($antes > 0) {
            $var = round(($hoy - $antes) / $antes * 100);
            $out[] = ['tono' => $var >= 0 ? 'bien' : 'mal',
                'texto' => sprintf('%s lleva %d altas en %d días: %s%d%% frente al mismo punto de %s (%d). A este ritmo cerrará con unas %d (%s tuvo %d).',
                    ucfirst(self::mesLargo($ma)), $hoy, $d['dia'], $var >= 0 ? '+' : '', $var, self::mesLargo($mp), $antes, $cierre, self::mesLargo($mp), $totalAnterior)];
        } else {
            $out[] = ['tono' => 'neutro', 'texto' => sprintf('%s lleva %d altas en %d días. A este ritmo cerrará con unas %d.', ucfirst(self::mesLargo($ma)), $hoy, $d['dia'], $cierre)];
        }

        // 2. Tendencia de la API: 3 ultimos meses completos frente a los 3 anteriores
        $completos = array_slice($d['meses'], 0, -1);
        $u3 = array_slice($completos, -3);
        $p3 = array_slice($completos, -6, 3);
        $mediaU3 = array_sum(array_map(fn ($m) => $pm[$m]['api'], $u3)) / max(1, count($u3));
        $mediaP3 = array_sum(array_map(fn ($m) => $pm[$m]['api'], $p3)) / max(1, count($p3));
        if ($mediaP3 > 0) {
            $var = round(($mediaU3 - $mediaP3) / $mediaP3 * 100);
            $out[] = ['tono' => $var >= 0 ? 'bien' : 'mal',
                'texto' => sprintf('API: media de %d altas/mes en %s, %s%d%% frente a %s (%d/mes).',
                    round($mediaU3), self::rango($u3), $var >= 0 ? '+' : '', $var, self::rango($p3), round($mediaP3))];
        }

        // 3. Solvencia: desde cuando, el ultimo mes completo y su peso en las altas
        $inicio = null;
        foreach ($completos as $m) {
            if ($pm[$m]['risk'] > 0) {
                $inicio = $m;
                break;
            }
        }
        if ($inicio !== null) {
            $ultimo = end($completos);
            $peso = $pm[$ultimo]['total'] > 0 ? round($pm[$ultimo]['risk'] / $pm[$ultimo]['total'] * 100) : 0;
            $act = $pm[$ultimo]['risk'] > 0 ? round($pm[$ultimo]['activados']['risk'] / $pm[$ultimo]['risk'] * 100) : 0;
            $out[] = ['tono' => 'neutro',
                'texto' => sprintf('Solvencia (desde %s): %d altas en %s, el %d%% de todas las altas del mes; el %d%% llegó a ver un informe.',
                    self::mesLargo($inicio), $pm[$ultimo]['risk'], self::mesLargo($ultimo), $peso, $act)];
        }

        // 4. Conversion a pago de las cohortes maduras
        $c = $d['madura'];
        if ($c['api']['altas'] > 0) {
            $pct = round($c['api']['pagan'] / $c['api']['altas'] * 100, 1);
            $txt = sprintf('De las altas de la API con más de %d días, el %s%% ha pagado alguna vez (%d de %d).',
                self::DIAS_COHORTE_MADURA, str_replace('.', ',', (string) $pct), $c['api']['pagan'], $c['api']['altas']);
            if ($c['risk']['altas'] > 0) {
                $txt .= sprintf(' Solvencia: %d de %d.', $c['risk']['pagan'], $c['risk']['altas']);
            } else {
                $txt .= ' Solvencia aún no tiene altas con esa antigüedad.';
            }
            $out[] = ['tono' => 'neutro', 'texto' => $txt];
        }

        // 5. Pago: hacia donde va
        $neto = $d['prevision']['neto_subs_mes'];
        $mrrFut = end($d['prevision']['mrr']);
        $out[] = ['tono' => $neto > 0 ? 'bien' : ($neto < 0 ? 'mal' : 'neutro'),
            'texto' => sprintf('Suscriptores de pago: %d hoy, %s%s netos al mes de media en los 3 últimos meses. Si sigue así, en %s habría unos %d y el MRR rondaría los %s €.',
                $d['subs_hoy'], $neto > 0 ? '+' : '', str_replace('.', ',', (string) $neto),
                self::mesLargo(end($d['prevision']['meses'])), end($d['prevision']['subs']), number_format($mrrFut, 0, ',', '.'))];

        return $out;
    }
}
