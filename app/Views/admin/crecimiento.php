<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>

<?= $this->section('styles') ?>
<?= view('admin/partials/panel_estilos') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php use App\Libraries\Crecimiento as C; ?>
<div class="gr-root">

<div class="gr-head">
    <div>
        <h1 class="title" style="margin-bottom:4px">Crecimiento</h1>
        <p class="subtitle">Altas de la API y de Solvencia, paso a pago e ingresos, mes a mes.<?= $d ? ' Datos a ' . esc(date('d/m/Y H:i', strtotime($d['ahora']))) . '.' : '' ?></p>
    </div>
    <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
</div>

<?php if (!$d): ?>
    <div class="gr-alert"><strong>No se han podido calcular los datos.</strong> <?= esc((string) $error) ?></div>
<?php else: ?>
<?php
    $pm = $d['por_mes'];
    $ma = $d['mes_actual'];
    $mp = $d['mes_anterior'];
    $act = $d['actual'];
    $prev = $d['prevision'];
    $n = fn ($v) => number_format((float) $v, 0, ',', '.');
    $eur = fn ($v) => number_format((float) $v, 0, ',', '.') . ' €';
    $pct = fn ($a, $b) => $b > 0 ? str_replace('.', ',', (string) round($a / $b * 100, 1)) . '%' : '–';
    $chip = function ($ahora, $antes, string $sufijo = '') {
        if ($antes <= 0) {
            return '<span class="gr-chip gr-chip--flat">–</span>';
        }
        $v = round(($ahora - $antes) / $antes * 100);
        $cls = $v > 0 ? 'up' : ($v < 0 ? 'down' : 'flat');
        $flecha = $v > 0 ? '↑' : ($v < 0 ? '↓' : '→');

        return '<span class="gr-chip gr-chip--' . $cls . '">' . $flecha . ' ' . ($v > 0 ? '+' : '') . $v . '%' . $sufijo . '</span>';
    };
    // Paso redondo para el eje (1, 2, 2,5, 5 x 10^n) y maximo = pasos x paso, asi las marcas salen redondas
    $paso = function (float $max, int $pasos): int {
        $bruto = max(1, $max / $pasos);
        $pot = 10 ** floor(log10($bruto));
        foreach ([1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10] as $f) {
            if ($f * $pot >= $bruto && fmod($f * $pot, 1) == 0) {
                return (int) ($f * $pot);
            }
        }

        return (int) (10 * $pot);
    };
    // Rectangulo con las esquinas de arriba redondeadas (barras ancladas a la base)
    $barra = function (float $x, float $y, float $w, float $h, bool $redondo, string $fill) {
        if ($h <= 0.5) {
            return '';
        }
        $r = $redondo ? min(4, $h, $w / 2) : 0;
        $d = sprintf('M%.1f %.1fV%.1fQ%.1f %.1f %.1f %.1fH%.1fQ%.1f %.1f %.1f %.1fV%.1fZ',
            $x, $y + $h, $y + $r, $x, $y, $x + $r, $y, $x + $w - $r, $x + $w, $y, $x + $w, $y + $r, $y + $h);

        return '<path d="' . $d . '" fill="' . $fill . '"/>';
    };

    // ---- KPIs ----
    $mtd = $act['api']['mtd'] + $act['risk']['mtd'];
    $antesMtd = $act['api']['mismo_punto_anterior'] + $act['risk']['mismo_punto_anterior'];
    $cierre = $act['api']['cierre'] + $act['risk']['cierre'];
    $cierreLow = $act['api']['low'] + $act['risk']['low'];
    $cierreHigh = $act['api']['high'] + $act['risk']['high'];
    $subsAntes = array_sum($pm[$mp]['subs']['activas']);
    $mrrAntes = $pm[$mp]['subs']['mrr'];
    $totalUsuarios = $d['totales']['api'] + $d['totales']['risk'];
    $mad = $d['madura'];
?>

<?php
    // ---- Datos para las mini graficas de las tarjetas ----
    $ultMeses = array_slice($d['meses'], -8);
    // Usuarios acumulados a final de cada mes (total de hoy menos lo que se registro despues)
    $acum = [];
    foreach ($ultMeses as $m) {
        $despues = 0;
        foreach ($d['meses'] as $m2) {
            if ($m2 > $m) {
                $despues += $pm[$m2]['total'];
            }
        }
        $acum[] = $totalUsuarios - $despues;
    }
    $mrrSerie = array_map(fn ($m) => $pm[$m]['subs']['mrr'], $ultMeses);
    // Linea con area suave; el ultimo punto marcado
    $spark = function (array $v, string $color, string $id) {
        $w = 200; $h = 40; $pad = 3;
        $min = min($v); $max = max($v);
        $rng = max(1e-9, $max - $min);
        $pts = [];
        foreach (array_values($v) as $i => $val) {
            $pts[] = [round($pad + ($w - 2 * $pad) * $i / max(1, count($v) - 1), 1), round($pad + ($h - 2 * $pad) * (1 - ($val - $min) / $rng), 1)];
        }
        $line = implode(' ', array_map(fn ($p) => $p[0] . ',' . $p[1], $pts));
        $last = end($pts);
        $area = 'M' . $pts[0][0] . ' ' . $h . ' L' . str_replace(' ', ' L', $line) . ' L' . $last[0] . ' ' . $h . 'Z';

        return '<svg class="gr-spark" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none" aria-hidden="true">'
            . '<defs><linearGradient id="' . $id . '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $color . '" stop-opacity=".22"/><stop offset="1" stop-color="' . $color . '" stop-opacity="0"/></linearGradient></defs>'
            . '<path d="' . $area . '" fill="url(#' . $id . ')"/>'
            . '<polyline points="' . $line . '" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>'
            . '</svg>';
    };
    $icon = [
        'altas' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>',
        'prev'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'pago'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg>',
        'conv'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5 5 19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
    ];
    $acc = [
        'altas' => '--acc:#2152ff;--acc-2:#5c7cff;--acc-soft:rgba(33,82,255,.08);--acc-line:rgba(33,82,255,.35);--acc-shadow:rgba(33,82,255,.35)',
        'prev'  => '--acc:#6d4fe0;--acc-2:#9b87f5;--acc-soft:rgba(109,79,224,.09);--acc-line:rgba(109,79,224,.35);--acc-shadow:rgba(109,79,224,.35)',
        'users' => '--acc:#0e9f7e;--acc-2:#34c9a3;--acc-soft:rgba(14,159,126,.09);--acc-line:rgba(14,159,126,.35);--acc-shadow:rgba(14,159,126,.35)',
        'pago'  => '--acc:#0a8f3c;--acc-2:#3cc46b;--acc-soft:rgba(10,143,60,.09);--acc-line:rgba(10,143,60,.35);--acc-shadow:rgba(10,143,60,.35)',
        'conv'  => '--acc:#d97706;--acc-2:#f5b13d;--acc-soft:rgba(217,119,6,.09);--acc-line:rgba(217,119,6,.35);--acc-shadow:rgba(217,119,6,.35)',
    ];
    $convPct = $mad['api']['altas'] > 0 ? $mad['api']['pagan'] / $mad['api']['altas'] * 100 : 0;
    $actApi = $mad['api']['altas'] > 0 ? $mad['api']['activados'] / $mad['api']['altas'] * 100 : 0;
    $rAltasVentana = array_sum(array_map(fn ($r) => $r['risk'], $pm));
    $rAct = 0;
    foreach ($pm as $row) {
        $rAct += $row['activados']['risk'];
    }
    $actRisk = $rAltasVentana > 0 ? $rAct / $rAltasVentana * 100 : 0;
    $fmtPct = fn ($v) => str_replace('.', ',', (string) round($v, 1)) . '%';

    // ---- Ayudas: que significa cada dato (se ensenan al pasar el raton o tocar la "?") ----
    $pesoRitmo = (int) round(min(1, ($d['dia'] - 0.5) / $d['dias_mes']) * 100);
    $mesL = C::mesLargo($ma);
    $mesPL = C::mesLargo($mp);
    $H = [
        'altas' => "<strong>Qué es:</strong> cuántas cuentas nuevas se han creado en {$mesL} hasta ahora. Solo cuentan los registros para la API (<code>signup_intent = 'api'</code>) y para Solvencia (<code>'view_risk_profile'</code>). No cuentan administradores, el usuario monitor ni las altas de Radar, Alertas o Copiloto."
            . "<br><br><strong>La comparación:</strong> no se compara con {$mesPL} completo (sería injusto, el mes va por el día {$d['dia']}), sino con las altas que tenía {$mesPL} el mismo día y a la misma hora. Es como comparar dos ejecuciones de un script en el mismo paso, no una a medias con otra terminada."
            . "<br><br><strong>La barra:</strong> qué parte de esas altas viene de cada producto (azul API, naranja Solvencia).",
        'prev' => "<strong>Qué es:</strong> cuántas altas tendrá {$mesL} cuando acabe, si sigue como va."
            . "<br><br><strong>Cómo se calcula:</strong> mezcla dos estimaciones: <br>1) el <em>ritmo real</em> de este mes: altas ÷ días que han pasado × días del mes;<br>2) lo <em>normal</em>: la media de los 3 últimos meses más un poco de su tendencia.<br>Al principio del mes manda la 2 (con pocos días, el ritmo engaña); al final, casi todo es la 1. Hoy el ritmo real pesa un {$pesoRitmo}%."
            . "<br><br><strong>El rango ({$cierreLow}–{$cierreHigh}):</strong> el margen razonable. Lo más probable es que el mes cierre dentro."
            . "<br><br><strong>La barra:</strong> el relleno es lo que llevas sobre la previsión; la rayita negra marca en qué día del mes estamos. Si el relleno pasa la rayita, vas por delante.",
        'users' => "<strong>Qué es:</strong> todas las cuentas de API y de Solvencia creadas desde el principio (sin administradores ni el monitor)."
            . "<br><br><strong>+N en los últimos 30 días:</strong> altas de los 30 días anteriores a hoy (ventana móvil, no el mes natural). Por eso no coincide con 'Altas en {$mesL}'."
            . "<br><br><strong>La curva:</strong> el total acumulado al final de cada uno de los 8 últimos meses. Si se empina, crecéis más rápido; si se aplana, el ritmo de altas baja. Nunca baja porque es acumulada.",
        'pago' => "<strong>Suscriptores de pago:</strong> usuarios con una suscripción activa a un plan que cuesta dinero (Pro, Business, Professional, Solvencia Pro...). El plan Free no cuenta."
            . "<br><br><strong>MRR</strong> (<em>Monthly Recurring Revenue</em>, ingresos recurrentes mensuales): lo que entra cada mes por suscripciones si nada cambia. Es la suma del precio mensual de cada suscripción activa. Las anuales cuentan su precio ÷ 12 (una Pro anual de 182 € suma 15,17 €/mes). Sin IVA y sin pagos sueltos. Es como el 'sueldo fijo' del negocio y la métrica más importante en una empresa de suscripciones."
            . "<span class=\"ej\">Hoy: {$d['subs_hoy']} suscripciones suman " . $eur($d['mrr_hoy']) . " al mes.</span>"
            . "<br><strong>El %:</strong> cuánto ha cambiado el MRR desde el último día de {$mesPL}.<br><strong>La curva:</strong> el MRR a final de cada uno de los 8 últimos meses.<br><strong>×12:</strong> el <em>ARR</em> (ingreso anual recurrente): lo que facturarías en un año si todo se queda igual.",
        'conv' => "<strong>Conversión a pago:</strong> de cada 100 personas que se registran en la API, cuántas han pagado alguna vez (aunque luego se dieran de baja)."
            . "<br><br><strong>Por qué solo altas de más de " . C::DIAS_COHORTE_MADURA . " días:</strong> alguien que se registró ayer no ha tenido tiempo de pagar. Si lo contaras, la conversión saldría artificialmente baja. Es como medir la tasa de fallos solo en versiones que llevan un tiempo en producción."
            . "<span class=\"ej\">Hoy: {$mad['api']['pagan']} de {$mad['api']['altas']} → " . $fmtPct($convPct) . ". Solvencia aún no tiene altas con esa antigüedad, por eso no sale.</span>"
            . "<br><strong>Se activan:</strong> el % de usuarios que han usado el producto al menos una vez. En la API, hacer 1 petición; en Solvencia, abrir 1 informe. Quien se registra y nunca lo usa casi nunca paga: la activación es el paso anterior a la conversión. Si es baja, el problema está en el primer uso (onboarding, documentación), no en el precio.",
        'lectura' => "Frases que la página escribe sola con los datos de ahora mismo, para no tener que interpretar los gráficos. Flecha verde: va bien. Flecha roja: va mal. <strong>i</strong>: dato informativo. Se recalculan cada vez que abres la página.",
        'g_altas' => "<strong>Cada barra es un mes.</strong> Azul = altas de la API, naranja = Solvencia, una encima de la otra. La altura total es el total de altas del mes."
            . "<br><br><strong>Mes en curso:</strong> la parte de color fuerte es lo real; la parte clara, lo que falta hasta la previsión de cierre."
            . "<br><strong>Los 3 meses de la derecha:</strong> previsión (todo en claro). No son datos, son estimaciones."
            . "<br><strong>La línea vertical con topes:</strong> el margen probable. Cuanto más lejos en el futuro, más ancho, porque hay más incertidumbre."
            . "<br><br>Pasa el ratón por un mes para ver los números exactos.",
        'g_mrr' => "<strong>Cada punto</strong> es el MRR (ingresos recurrentes mensuales) del último día de ese mes; el último punto es hoy."
            . "<br><br><strong>La línea discontinua</strong> muestra qué pasaría si los próximos meses ganas o pierdes lo mismo que de media en los 3 últimos (hoy " . ($prev['neto_mrr_mes'] >= 0 ? '+' : '') . $eur($prev['neto_mrr_mes']) . " al mes). No es una predicción fina: sirve para ver la dirección."
            . "<br><br>Si la línea sube, el negocio crece aunque haya bajas. Si se aplana o baja, entran menos clientes de los que se van.",
        'g_mov' => "<strong>Verde, hacia arriba:</strong> suscripciones de pago que empezaron ese mes.<br><strong>Rojo, hacia abajo:</strong> las que se cancelaron ese mes (en inglés, <em>churn</em>)."
            . "<br><br>Lo que importa es el <strong>neto</strong> (nuevas − bajas). Si el rojo es tan grande como el verde, el negocio está quieto aunque entren clientes: se van tantos como llegan. Es como un cubo con un agujero: además de echar agua, hay que tapar el agujero.",
        'planes' => "Cada plan de pago con cuántos suscriptores tiene hoy y cuánto MRR (ingreso mensual) aporta. La barra compara el peso de cada plan en el MRR total. Los anuales aparecen aparte porque su MRR es el precio anual ÷ 12.",
        'arr' => "<strong>ARR</strong> (<em>Annual Recurring Revenue</em>): el MRR × 12. Lo que entraría en un año por suscripciones si no hubiera ni altas ni bajas. Es la cifra que se usa para hablar del 'tamaño' de un negocio de suscripción.",
        'arpu' => "MRR ÷ número de suscriptores: lo que paga de media cada cliente al mes (en inglés, <em>ARPU</em>). Si sube, tus clientes eligen planes más caros; si baja, entran sobre todo en el plan barato.",
        'este_mes' => "Suscripciones de pago nuevas (+) y cancelaciones (−) en lo que va de mes.",
        'facturado' => "Lo que realmente se ha cobrado: facturas pagadas, sin IVA, en los 12 meses que se ven en la página. <br><br><strong>No es lo mismo que el MRR:</strong> el MRR es lo recurrente 'en este momento'. Lo facturado incluye los anuales cobrados de golpe, pagos sueltos y devoluciones (que restan). Por eso un mes puede salir con mucho facturado y poco MRR, o al revés.",
        'prev_subs' => "Suscriptores y MRR dentro de 3 meses si sigue la media neta de los 3 últimos meses (nuevas − bajas). Es una proyección en línea recta: sirve para ver la tendencia, no para presupuestar.",
        'nota_conv' => "Altas de API que se esperan el mes que viene × la conversión actual = clientes de pago nuevos que puedes esperar al mes. Sirve para ver qué palanca mover: más altas o mejor conversión.",
        't_vs' => "Cuánto ha cambiado el total de altas frente al mes anterior, en %. En el mes en curso (*) se compara con el mismo día del mes anterior, para que sea justo.",
        't_act_api' => "De las personas que se registraron en la API <strong>ese mes</strong>, qué % ha hecho al menos 1 petición hasta hoy.<br><br>Esto es una <strong>cohorte</strong>: un grupo de usuarios agrupados por el mes en que se registraron, al que se sigue después. Es como etiquetar los usuarios por versión de alta y ver qué hace cada versión.",
        't_act_risk' => "De las personas que se registraron en Solvencia ese mes, qué % ha abierto al menos 1 informe hasta hoy.<br><br>Las visitas a informes se guardan desde el 04/09/2026, así que los registros anteriores salen con 0 % aunque sí miraran informes.",
        't_pagan' => "De las altas de ese mes, cuántas han pagado alguna vez hasta hoy, y entre paréntesis el % sobre las altas del mes.<br><br>Los meses recientes salen bajos porque esos usuarios aún no han tenido tiempo de pagar. Compara meses antiguos entre sí.",
        't_nuevas' => "Suscripciones de pago que empezaron ese mes.",
        't_bajas' => "Suscripciones de pago canceladas ese mes.",
        't_subs' => "Cuántas suscripciones de pago había activas el último día del mes (hoy, en el mes en curso).",
        't_mrr' => "MRR el último día del mes: la suma del precio mensual de las suscripciones activas en ese momento (las anuales, ÷ 12).",
        't_fact' => "Facturas pagadas de ese mes, sin IVA. Las devoluciones restan, por eso un mes puede salir en negativo.",
        'bajas' => "Cada cliente que tenía un plan de pago (API o Solvencia) y lo canceló, el más reciente primero."
            . "<br><br><strong>No cuentan como baja:</strong> los cambios de plan (cancelar Pro y contratar Business, o pasar de mensual a anual el mismo día): el cliente sigue pagando. Tampoco las filas repetidas que a veces guarda Stripe (la misma suscripción dos veces)."
            . "<br><br><strong>Fecha:</strong> el día que canceló. Si canceló al final del periodo, aún pudo usar el plan hasta que se acabó lo pagado.",
        'b_motivos' => "El motivo que eligió el cliente al cancelar <strong>desde tu web</strong> (Mi cuenta → Facturación → Cancelar), con su comentario si escribió uno."
            . "<br><br><strong>Sin motivo (cancelada en Stripe):</strong> canceló desde el portal de Stripe (el enlace de las facturas) o Stripe la canceló solo porque la tarjeta no pagaba. En esos casos nadie le pregunta el motivo. Si es la mayoría, conviene que el enlace de cancelar de Stripe lleve a tu web, o escribirles para preguntar.",
        'b_duro' => "Cuánto tiempo tuvo el plan: desde que lo contrató hasta que lo canceló.",
        'b_pago' => "Lo que llegó a pagar en total (facturas pagadas, sin IVA) y cuántas facturas fueron. Es el dinero que dejó ese cliente.",
        'b_uso' => "Cuándo usó el producto por última vez (en la API, su última petición; en Solvencia, el último informe que abrió), contado respecto al día de la baja."
            . "<br><br>Es la mejor pista del motivo real: quien deja de usar el producto semanas antes de cancelar no se va por el precio, se va porque <strong>ya no lo necesita</strong> o no lo integró. Esos clientes se pueden detectar antes de que cancelen (ver 'Paga y no usa' en Correos).",
        'b_hoy' => "Qué hace ahora: si ha vuelto a contratar un plan, si sigue usando el producto gratis después de la baja, o si no ha vuelto.",
        'b_paron' => "Clientes que no usaban el producto desde hacía más de 14 días cuando cancelaron. Si es la mayoría, el problema no es el precio: es que dejan de usarlo. Ahí ayudan los avisos de 'paga y no usa' y escribirles cuando se paran.",
        'b_mrr' => "La suma de lo que pagaba al mes cada cliente que se dio de baja (MRR perdido). Es lo que facturarías de más cada mes si no se hubieran ido.",
    ];
    $ayuda = fn (string $k) => '<button type="button" class="gr-help" aria-label="¿Qué significa?" data-help="' . esc($H[$k] ?? '', 'attr') . '">?</button>';
?>
<div class="gr-tiles">
    <div class="gr-tile" style="<?= $acc['altas'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $icon['altas'] ?></span><span class="gr-tile__label">Altas en <?= esc(C::mesLargo($ma)) ?><?= $ayuda('altas') ?></span></div>
        <div class="gr-tile__value"><?= $n($mtd) ?><small>en <?= $d['dia'] ?> días</small></div>
        <div class="gr-tile__meta"><?= $chip($mtd, $antesMtd) ?> vs. día <?= $d['dia'] ?> de <?= esc(C::mesLargo($mp)) ?> (<?= $n($antesMtd) ?>)</div>
        <div class="gr-tile__viz">
            <div class="gr-split" title="API <?= $act['api']['mtd'] ?> · Solvencia <?= $act['risk']['mtd'] ?>">
                <?php if ($mtd > 0): ?>
                    <span style="width:<?= round($act['api']['mtd'] / $mtd * 100, 1) ?>%;background:var(--api)"></span>
                    <span style="width:<?= round($act['risk']['mtd'] / $mtd * 100, 1) ?>%;background:var(--risk)"></span>
                <?php endif; ?>
            </div>
            <div class="gr-tile__foot"><span><span class="gr-dot" style="background:var(--api)"></span>API <strong><?= $n($act['api']['mtd']) ?></strong></span><span><span class="gr-dot" style="background:var(--risk)"></span>Solvencia <strong><?= $n($act['risk']['mtd']) ?></strong></span></div>
        </div>
    </div>

    <div class="gr-tile" style="<?= $acc['prev'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $icon['prev'] ?></span><span class="gr-tile__label">Previsión de cierre<?= $ayuda('prev') ?></span></div>
        <div class="gr-tile__value"><span class="approx">≈</span><?= $n($cierre) ?><small><?= $n($cierreLow) ?>–<?= $n($cierreHigh) ?></small></div>
        <div class="gr-tile__meta"><?= $chip($cierre, $pm[$mp]['total']) ?> vs. <?= esc(C::mesLargo($mp)) ?> (<?= $n($pm[$mp]['total']) ?>)</div>
        <div class="gr-tile__viz">
            <div class="gr-prog" title="Llevamos <?= $mtd ?> de ≈<?= $cierre ?>; la marca es el día del mes">
                <span style="width:<?= $cierre > 0 ? min(100, round($mtd / $cierre * 100, 1)) : 0 ?>%"></span>
                <i style="left:<?= round($d['dia'] / $d['dias_mes'] * 100, 1) ?>%"></i>
            </div>
            <div class="gr-tile__foot"><span><strong><?= $cierre > 0 ? round($mtd / $cierre * 100) : 0 ?>%</strong> de la previsión</span><span>día <strong><?= $d['dia'] ?></strong> de <?= $d['dias_mes'] ?></span></div>
        </div>
    </div>

    <div class="gr-tile" style="<?= $acc['users'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $icon['users'] ?></span><span class="gr-tile__label">Usuarios registrados<?= $ayuda('users') ?></span></div>
        <div class="gr-tile__value"><?= $n($totalUsuarios) ?></div>
        <div class="gr-tile__meta"><span class="gr-chip gr-chip--up">+<?= $n($d['ultimos30']['api'] + $d['ultimos30']['risk']) ?></span> en los últimos 30 días</div>
        <div class="gr-tile__viz">
            <?= $spark($acum, '#0e9f7e', 'spU') ?>
            <div class="gr-tile__foot"><span>API <strong><?= $n($d['totales']['api']) ?></strong></span><span>Solvencia <strong><?= $n($d['totales']['risk']) ?></strong></span></div>
        </div>
    </div>

    <div class="gr-tile" style="<?= $acc['pago'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $icon['pago'] ?></span><span class="gr-tile__label">Suscriptores de pago<?= $ayuda('pago') ?></span></div>
        <div class="gr-tile__value"><?= $n($d['subs_hoy']) ?><small><?= $eur($d['mrr_hoy']) ?>/mes</small></div>
        <div class="gr-tile__meta"><?= $chip($d['mrr_hoy'], $mrrAntes, ' MRR') ?> vs. fin de <?= esc(C::mesLargo($mp)) ?> (<?= $eur($mrrAntes) ?>)</div>
        <div class="gr-tile__viz">
            <?= $spark($mrrSerie, '#0a8f3c', 'spM') ?>
            <div class="gr-tile__foot"><span style="white-space:nowrap">MRR, 8 meses</span><span style="white-space:nowrap">×12 = <strong><?= $eur($d['mrr_hoy'] * 12) ?></strong></span></div>
        </div>
    </div>

    <div class="gr-tile" style="<?= $acc['conv'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $icon['conv'] ?></span><span class="gr-tile__label">Conversión a pago<?= $ayuda('conv') ?></span></div>
        <div class="gr-tile__value"><?= $fmtPct($convPct) ?></div>
        <div class="gr-tile__meta">Altas API de más de <?= C::DIAS_COHORTE_MADURA ?> días que han pagado (<?= $n($mad['api']['pagan']) ?> de <?= $n($mad['api']['altas']) ?>)</div>
        <div class="gr-tile__viz">
            <div class="gr-ring">
                <?php $circ = 2 * M_PI * 17; ?>
                <svg width="44" height="44" viewBox="0 0 44 44" aria-hidden="true">
                    <circle cx="22" cy="22" r="17" fill="none" stroke="#eef1f6" stroke-width="6"/>
                    <circle cx="22" cy="22" r="17" fill="none" stroke="url(#gRing)" stroke-width="6" stroke-linecap="round"
                        stroke-dasharray="<?= round($circ * min(100, $convPct) / 100, 2) ?> <?= round($circ, 2) ?>" transform="rotate(-90 22 22)"/>
                    <defs><linearGradient id="gRing" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f5b13d"/><stop offset="1" stop-color="#d97706"/></linearGradient></defs>
                </svg>
                <div class="gr-minibars">
                    <div style="font-weight:800;text-transform:uppercase;letter-spacing:.04em;font-size:.64rem">Se activan</div>
                    <div><span>API <strong><?= $fmtPct($actApi) ?></strong></span><i><b style="width:<?= round($actApi, 1) ?>%;background:var(--api)"></b></i></div>
                    <div><span>Solvencia <strong><?= $fmtPct($actRisk) ?></strong></span><i><b style="width:<?= round($actRisk, 1) ?>%;background:var(--risk)"></b></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="gr-box gr-read">
    <h3>Lectura rápida<?= $ayuda('lectura') ?></h3>
    <ul>
        <?php foreach ($d['lectura'] as $l): ?>
            <li class="t-<?= esc($l['tono'], 'attr') ?>"><span><?= $l['tono'] === 'bien' ? '↑' : ($l['tono'] === 'mal' ? '↓' : 'i') ?></span><span><?= esc($l['texto']) ?></span></li>
        <?php endforeach; ?>
    </ul>
</div>

<?php
    // ================= Grafico 1: altas por mes (apiladas) + prevision =================
    $cols = [];
    $empezado = false;
    foreach ($d['meses'] as $m) {
        $r = $pm[$m];
        if (!$empezado && $r['total'] === 0) {
            continue;
        }
        $empezado = true;
        if ($m === $ma) {
            $cols[] = ['mes' => $m, 'tipo' => 'actual',
                'api' => $act['api']['mtd'], 'risk' => $act['risk']['mtd'],
                'api_p' => max(0, $act['api']['cierre'] - $act['api']['mtd']), 'risk_p' => max(0, $act['risk']['cierre'] - $act['risk']['mtd']),
                'low' => $cierreLow, 'high' => $cierreHigh,
                'tip' => C::mesCorto($m) . " (en curso, día {$d['dia']})\nAPI: {$act['api']['mtd']} → cierre ≈ {$act['api']['cierre']}\nSolvencia: {$act['risk']['mtd']} → cierre ≈ {$act['risk']['cierre']}\nTotal previsto: ≈ {$cierre} ({$cierreLow}–{$cierreHigh})"];
        } else {
            $prevTotal = null;
            $idx = array_search($m, $d['meses'], true);
            if ($idx > 0) {
                $prevTotal = $pm[$d['meses'][$idx - 1]]['total'];
            }
            $var = $prevTotal ? (($r['total'] - $prevTotal) >= 0 ? '+' : '') . round(($r['total'] - $prevTotal) / $prevTotal * 100) . '% vs. mes anterior' : '';
            $cols[] = ['mes' => $m, 'tipo' => 'real', 'api' => $r['api'], 'risk' => $r['risk'], 'api_p' => 0, 'risk_p' => 0, 'low' => null, 'high' => null,
                'tip' => C::mesCorto($m) . "\nAPI: {$r['api']} · Solvencia: {$r['risk']}\nTotal: {$r['total']}" . ($var ? "\n{$var}" : '')];
        }
    }
    foreach ($prev['meses'] as $i => $m) {
        $pa = $prev['api'][$i];
        $pr = $prev['risk'][$i];
        $cols[] = ['mes' => $m, 'tipo' => 'prevision', 'api' => 0, 'risk' => 0, 'api_p' => $pa['base'], 'risk_p' => $pr['base'],
            'low' => $pa['low'] + $pr['low'], 'high' => $pa['high'] + $pr['high'],
            'tip' => C::mesCorto($m) . " (previsión)\nAPI ≈ {$pa['base']} ({$pa['low']}–{$pa['high']})\nSolvencia ≈ {$pr['base']} ({$pr['low']}–{$pr['high']})"];
    }
    $max = 0;
    foreach ($cols as $c) {
        $max = max($max, $c['api'] + $c['api_p'] + $c['risk'] + $c['risk_p'], (int) $c['high']);
    }
    $yMax = 4 * $paso($max * 1.05, 4);
    $W = 760; $H = 270; $L = 34; $R = 6; $T = 12; $B = 26;
    $plotH = $H - $T - $B;
    $slot = ($W - $L - $R) / max(1, count($cols));
    $bw = min(34, $slot * 0.62);
    $y = fn ($v) => $T + $plotH * (1 - $v / $yMax);
    $ultimoReal = null;
    foreach ($cols as $i => $c) {
        if ($c['tipo'] === 'real') {
            $ultimoReal = $i;
        }
    }
?>
<div class="gr-box gr-card">
    <div class="gr-card__top">
        <h3>Altas por mes<?= $ayuda('g_altas') ?></h3>
        <div class="gr-legend">
            <span><span class="gr-dot" style="background:var(--api)"></span>API</span>
            <span><span class="gr-dot" style="background:var(--risk)"></span>Solvencia</span>
            <span><span class="gr-dot" style="background:var(--api-tint)"></span><span class="gr-dot" style="background:var(--risk-tint);margin-left:-2px"></span>Previsión</span>
            <span><svg width="10" height="12" style="margin-right:4px"><path d="M5 1V11M2 1H8M2 11H8" stroke="#52514e" stroke-width="1.5" fill="none"/></svg>Margen</span>
        </div>
    </div>
    <svg class="gr-svg" viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Altas por mes de la API y de Solvencia, con previsión">
        <?php foreach ([0, .25, .5, .75, 1] as $k): $v = $yMax * $k; ?>
            <line x1="<?= $L ?>" x2="<?= $W - $R ?>" y1="<?= round($y($v), 1) ?>" y2="<?= round($y($v), 1) ?>" stroke="<?= $k == 0 ? '#cbd5e1' : '#f1f0ec' ?>" stroke-width="1"/>
            <text x="<?= $L - 6 ?>" y="<?= round($y($v) + 4, 1) ?>" text-anchor="end" font-size="10" fill="#8a8984"><?= $n($v) ?></text>
        <?php endforeach; ?>
        <?php foreach ($cols as $i => $c):
            $x = $L + $slot * $i + ($slot - $bw) / 2;
            $segs = [
                [$c['api'], 'var(--api)'], [$c['api_p'], 'var(--api-tint)'],
                [$c['risk'], 'var(--risk)'], [$c['risk_p'], 'var(--risk-tint)'],
            ];
            $segs = array_values(array_filter($segs, fn ($s) => $s[0] > 0));
            $base = $T + $plotH;
            foreach ($segs as $j => $s):
                $h = $plotH * $s[0] / $yMax;
                $gap = $j > 0 ? 2 : 0;
                echo $barra($x, $base - $h, $bw, $h - $gap, $j === count($segs) - 1, $s[1]);
                $base -= $h;
            endforeach;
            $cx = $x + $bw / 2;
            if ($c['low'] !== null && $c['high'] > $c['low']): ?>
                <path d="M<?= round($cx, 1) ?> <?= round($y($c['high']), 1) ?>V<?= round($y($c['low']), 1) ?>M<?= round($cx - 4, 1) ?> <?= round($y($c['high']), 1) ?>H<?= round($cx + 4, 1) ?>M<?= round($cx - 4, 1) ?> <?= round($y($c['low']), 1) ?>H<?= round($cx + 4, 1) ?>" stroke="#52514e" stroke-width="1.5" fill="none"/>
            <?php endif;
            // Etiquetas solo donde importan: el ultimo mes completo y la prevision del mes en curso
            $tot = $c['api'] + $c['risk'] + $c['api_p'] + $c['risk_p'];
            if ($i === $ultimoReal || $c['tipo'] === 'actual'):
                $ly = $c['tipo'] === 'actual' && $c['high'] ? $y($c['high']) - 6 : $y($tot) - 6; ?>
                <text x="<?= round($cx, 1) ?>" y="<?= round($ly, 1) ?>" text-anchor="middle" font-size="11" font-weight="700" fill="#0b0b0b"><?= $c['tipo'] === 'actual' ? '≈' : '' ?><?= $n($tot) ?></text>
            <?php endif; ?>
            <text x="<?= round($cx, 1) ?>" y="<?= $H - 8 ?>" text-anchor="middle" font-size="10" fill="<?= $c['tipo'] === 'real' ? '#52514e' : '#8a8984' ?>" font-weight="<?= $c['tipo'] === 'actual' ? '700' : '400' ?>"><?= esc(C::mesCorto($c['mes'])) ?></text>
            <rect class="gr-hit" x="<?= round($L + $slot * $i, 1) ?>" y="<?= $T ?>" width="<?= round($slot, 1) ?>" height="<?= $plotH ?>" data-tip="<?= esc($c['tip'], 'attr') ?>"/>
        <?php endforeach; ?>
    </svg>
    <p class="gr-note">La previsión es una estimación sencilla: la media de los 3 últimos meses más la mitad de la tendencia de los 6 últimos, con el margen de la variación habitual. En el mes en curso pesa cada vez más el ritmo real. Solvencia tiene poca historia, así que su margen es amplio.</p>
</div>

<?php
    // ================= Grafico 2: MRR a fin de mes + prevision, y altas/bajas de suscripciones =================
    $mc = [];
    $empezado = false;
    foreach ($d['meses'] as $m) {
        $s = $pm[$m]['subs'];
        if (!$empezado && $s['mrr'] <= 0 && array_sum($s['nuevas']) === 0) {
            continue;
        }
        $empezado = true;
        $mc[] = ['mes' => $m, 'mrr' => $s['mrr'], 'subs' => array_sum($s['activas']), 'nuevas' => array_sum($s['nuevas']), 'bajas' => array_sum($s['bajas']), 'tipo' => $m === $ma ? 'actual' : 'real'];
    }
    foreach ($prev['meses'] as $i => $m) {
        $mc[] = ['mes' => $m, 'mrr' => $prev['mrr'][$i], 'subs' => $prev['subs'][$i], 'nuevas' => null, 'bajas' => null, 'tipo' => 'prevision'];
    }
    $W2 = 560; $H2 = 200; $L2 = 46; $R2 = 40; $T2 = 14; $B2 = 24;
    $mMax = 4 * $paso(max(array_column($mc, 'mrr') ?: [1]) * 1.1, 4);
    $slot2 = ($W2 - $L2 - $R2) / max(1, count($mc));
    $x2 = fn ($i) => $L2 + $slot2 * $i + $slot2 / 2;
    $y2 = fn ($v) => $T2 + ($H2 - $T2 - $B2) * (1 - $v / $mMax);
    $real = array_filter($mc, fn ($p) => $p['tipo'] !== 'prevision');
    $ptsReal = [];
    $ptsPrev = [];
    foreach ($mc as $i => $p) {
        $pt = round($x2($i), 1) . ' ' . round($y2($p['mrr']), 1);
        if ($p['tipo'] !== 'prevision') {
            $ptsReal[] = $pt;
        }
        if ($p['tipo'] !== 'real') {
            $ptsPrev[] = $pt;
        }
    }
    $iHoy = count($real) - 1;
    $iFin = count($mc) - 1;
    // Altas / bajas de suscripciones (barras arriba / abajo)
    $H3 = 96; $mid = 48;
    $movMax = max(1, max(array_map(fn ($p) => max((int) $p['nuevas'], (int) $p['bajas']), $mc)));
    $bw2 = min(16, $slot2 * 0.42);
?>
<div class="gr-two">
    <div class="gr-box gr-card">
        <div class="gr-card__top">
            <h3>Ingresos recurrentes (MRR) a fin de mes<?= $ayuda('g_mrr') ?></h3>
            <div class="gr-legend">
                <span><svg width="18" height="8" style="margin-right:4px"><path d="M1 4H17" stroke="#2a78d6" stroke-width="2"/></svg>Real</span>
                <span><svg width="18" height="8" style="margin-right:4px"><path d="M1 4H17" stroke="#2a78d6" stroke-width="2" stroke-dasharray="4 3"/></svg>Si sigue el ritmo</span>
            </div>
        </div>
        <svg class="gr-svg" viewBox="0 0 <?= $W2 ?> <?= $H2 ?>" role="img" aria-label="MRR a fin de mes con previsión">
            <?php foreach ([0, .25, .5, .75, 1] as $k): $v = $mMax * $k; ?>
                <line x1="<?= $L2 ?>" x2="<?= $W2 - $R2 ?>" y1="<?= round($y2($v), 1) ?>" y2="<?= round($y2($v), 1) ?>" stroke="<?= $k == 0 ? '#cbd5e1' : '#f1f0ec' ?>"/>
                <text x="<?= $L2 - 6 ?>" y="<?= round($y2($v) + 4, 1) ?>" text-anchor="end" font-size="10" fill="#8a8984"><?= $eur($v) ?></text>
            <?php endforeach; ?>
            <?php if (count($ptsPrev) > 1): ?><polyline points="<?= implode(' ', $ptsPrev) ?>" fill="none" stroke="#2a78d6" stroke-width="2" stroke-dasharray="5 4" stroke-linejoin="round"/><?php endif; ?>
            <?php if (count($ptsReal) > 1): ?><polyline points="<?= implode(' ', $ptsReal) ?>" fill="none" stroke="#2a78d6" stroke-width="2" stroke-linejoin="round"/><?php endif; ?>
            <?php foreach ($mc as $i => $p): ?>
                <?php if ($p['tipo'] !== 'prevision'): ?>
                    <circle cx="<?= round($x2($i), 1) ?>" cy="<?= round($y2($p['mrr']), 1) ?>" r="4" fill="#2a78d6" stroke="#fff" stroke-width="2"/>
                <?php endif; ?>
                <?php if ($i % max(1, (int) ceil(count($mc) / 8)) === 0 || $i === $iHoy || $i === $iFin): ?>
                    <text x="<?= round($x2($i), 1) ?>" y="<?= $H2 - 6 ?>" text-anchor="middle" font-size="10" fill="#8a8984"><?= esc(C::mesCorto($p['mes'])) ?></text>
                <?php endif; ?>
            <?php endforeach; ?>
            <text x="<?= round($x2($iHoy), 1) ?>" y="<?= round($y2($mc[$iHoy]['mrr']) - 10, 1) ?>" text-anchor="middle" font-size="11" font-weight="700" fill="#0b0b0b"><?= $eur($mc[$iHoy]['mrr']) ?></text>
            <text x="<?= round($x2($iFin) + 6, 1) ?>" y="<?= round($y2($mc[$iFin]['mrr']) + 4, 1) ?>" font-size="11" font-weight="700" fill="#52514e">≈<?= $eur($mc[$iFin]['mrr']) ?></text>
            <?php foreach ($mc as $i => $p):
                $tip = C::mesCorto($p['mes']) . ($p['tipo'] === 'actual' ? ' (hoy)' : ($p['tipo'] === 'prevision' ? ' (previsión)' : ''))
                    . "\nMRR: " . ($p['tipo'] === 'prevision' ? '≈ ' : '') . $eur($p['mrr']) . "\nSuscriptores: " . ($p['tipo'] === 'prevision' ? '≈ ' : '') . $p['subs']
                    . ($p['nuevas'] !== null ? "\nNuevas: +{$p['nuevas']} · Bajas: −{$p['bajas']}" : ''); ?>
                <rect class="gr-hit" x="<?= round($L2 + $slot2 * $i, 1) ?>" y="<?= $T2 ?>" width="<?= round($slot2, 1) ?>" height="<?= $H2 - $T2 - $B2 ?>" data-tip="<?= esc($tip, 'attr') ?>"/>
            <?php endforeach; ?>
        </svg>

        <div class="gr-card__top" style="margin-top:10px">
            <h3 style="font-size:.85rem">Suscripciones nuevas y bajas<?= $ayuda('g_mov') ?></h3>
            <div class="gr-legend">
                <span><span class="gr-dot" style="background:var(--good)"></span>↑ Nuevas</span>
                <span><span class="gr-dot" style="background:var(--bad)"></span>↓ Bajas</span>
            </div>
        </div>
        <svg class="gr-svg" viewBox="0 0 <?= $W2 ?> <?= $H3 ?>" role="img" aria-label="Suscripciones nuevas y bajas por mes">
            <line x1="<?= $L2 ?>" x2="<?= $W2 - $R2 ?>" y1="<?= $mid ?>" y2="<?= $mid ?>" stroke="#cbd5e1"/>
            <?php foreach ($mc as $i => $p): if ($p['nuevas'] === null) { continue; }
                $cx = $x2($i);
                $hU = ($mid - 10) * $p['nuevas'] / $movMax;
                $hD = ($H3 - $mid - 10) * $p['bajas'] / $movMax; ?>
                <?= $barra($cx - $bw2 / 2, $mid - 1 - $hU, $bw2, $hU, true, 'var(--good)') ?>
                <?php if ($hD > 0.5): ?>
                    <path d="M<?= round($cx - $bw2 / 2, 1) ?> <?= $mid + 1 ?>V<?= round($mid + 1 + $hD - min(4, $hD), 1) ?>Q<?= round($cx - $bw2 / 2, 1) ?> <?= round($mid + 1 + $hD, 1) ?> <?= round($cx - $bw2 / 2 + min(4, $hD), 1) ?> <?= round($mid + 1 + $hD, 1) ?>H<?= round($cx + $bw2 / 2 - min(4, $hD), 1) ?>Q<?= round($cx + $bw2 / 2, 1) ?> <?= round($mid + 1 + $hD, 1) ?> <?= round($cx + $bw2 / 2, 1) ?> <?= round($mid + 1 + $hD - min(4, $hD), 1) ?>V<?= $mid + 1 ?>Z" fill="var(--bad)"/>
                <?php endif; ?>
                <?php if ($p['nuevas'] > 0): ?><text x="<?= round($cx, 1) ?>" y="<?= round($mid - 4 - $hU, 1) ?>" text-anchor="middle" font-size="9.5" fill="#52514e">+<?= $p['nuevas'] ?></text><?php endif; ?>
                <?php if ($p['bajas'] > 0): ?><text x="<?= round($cx, 1) ?>" y="<?= round($mid + 11 + $hD, 1) ?>" text-anchor="middle" font-size="9.5" fill="#52514e">−<?= $p['bajas'] ?></text><?php endif; ?>
                <rect class="gr-hit" x="<?= round($L2 + $slot2 * $i, 1) ?>" y="0" width="<?= round($slot2, 1) ?>" height="<?= $H3 ?>" data-tip="<?= esc(C::mesCorto($p['mes']) . "\nNuevas: +{$p['nuevas']}\nBajas: −{$p['bajas']}\nNeto: " . ($p['nuevas'] - $p['bajas'] >= 0 ? '+' : '') . ($p['nuevas'] - $p['bajas']), 'attr') ?>"/>
            <?php endforeach; ?>
        </svg>
        <p class="gr-note">MRR: precio mensual de cada suscripción activa (las anuales, su precio anual / 12). La línea discontinua sigue la media neta de los 3 últimos meses (<?= ($prev['neto_mrr_mes'] >= 0 ? '+' : '') . $eur($prev['neto_mrr_mes']) ?>/mes).</p>
    </div>

    <div class="gr-box gr-card">
        <h3>Planes de pago activos<?= $ayuda('planes') ?></h3>
        <?php $maxPlan = max(1, ...array_map(fn ($p) => $p['mrr'], $d['planes'] ?: [['mrr' => 1]])); ?>
        <ul class="gr-plans">
            <?php foreach ($d['planes'] as $p): ?>
                <li>
                    <div class="row"><span><?= esc($p['plan']) ?> <span class="gr-muted">· <?= $p['producto'] === 'risk' ? 'Solvencia' : 'API' ?></span></span><strong><?= $n($p['n']) ?> · <?= $eur($p['mrr']) ?></strong></div>
                    <div class="bar"><span style="width:<?= round($p['mrr'] / $maxPlan * 100) ?>%;background:<?= $p['producto'] === 'risk' ? 'var(--risk)' : 'var(--api)' ?>"></span></div>
                </li>
            <?php endforeach; ?>
            <?php if (!$d['planes']): ?><li class="gr-muted">Ninguno todavía.</li><?php endif; ?>
        </ul>
        <div class="gr-kv">
            <span>MRR hoy</span><strong><?= $eur($d['mrr_hoy']) ?></strong>
            <span>Ingreso anual recurrente (×12)<?= $ayuda('arr') ?></span><strong><?= $eur($d['mrr_hoy'] * 12) ?></strong>
            <span>Ingreso medio por suscriptor<?= $ayuda('arpu') ?></span><strong><?= $d['subs_hoy'] ? $eur($d['mrr_hoy'] / $d['subs_hoy']) : '–' ?>/mes</strong>
            <span>Este mes<?= $ayuda('este_mes') ?></span><strong>+<?= array_sum($pm[$ma]['subs']['nuevas']) ?> / −<?= array_sum($pm[$ma]['subs']['bajas']) ?></strong>
            <span>Facturado últimos 12 meses<?= $ayuda('facturado') ?></span><strong><?= $eur($d['facturado_12m']) ?></strong>
            <span>Previsión <?= esc(C::mesCorto(end($prev['meses']))) ?><?= $ayuda('prev_subs') ?></span><strong>≈ <?= $n(end($prev['subs'])) ?> subs · <?= $eur(end($prev['mrr'])) ?></strong>
        </div>
        <?php
            // Que haria falta: nuevos de pago al mes con la conversion actual y las altas previstas
            $convApi = $d['conversion']['api'];
            $altasApiProx = $prev['api'][0]['base'] ?? 0;
        ?>
        <?php if ($convApi): ?>
            <p class="gr-note" style="margin-top:12px">Con ~<?= $n($altasApiProx) ?> altas de API al mes y la conversión actual (<?= $pct($mad['api']['pagan'], $mad['api']['altas']) ?>), salen unos <?= str_replace('.', ',', (string) round($altasApiProx * $convApi, 1)) ?> clientes de pago nuevos al mes a medio plazo.<?= $ayuda('nota_conv') ?></p>
        <?php endif; ?>
    </div>
</div>

<?php
    // ================= Bajas de clientes de pago =================
    $bajas = $d['bajas'] ?? [];
    $duracion = function (int $dias): string {
        if ($dias < 1) {
            return 'el mismo día';
        }
        if ($dias < 45) {
            return $dias . ' ' . ($dias === 1 ? 'día' : 'días');
        }
        $m = (int) round($dias / 30.4);

        return $m . ' ' . ($m === 1 ? 'mes' : 'meses');
    };
    $nParon = 0;
    $mrrPerdido = 0.0;
    $diasLista = [];
    foreach ($bajas as $b) {
        $mrrPerdido += $b['mrr'];
        $diasLista[] = $b['dias'];
        if ($b['ultimo_uso'] === null || ($b['hasta'] - $b['ultimo_uso']) > 14 * 86400) {
            $nParon++;
        }
    }
    sort($diasLista);
    $mediana = $diasLista ? $diasLista[(int) floor((count($diasLista) - 1) / 2)] : 0;
    $maxMotivo = max(1, ...array_map(fn ($m) => $m['n'], $d['motivos'] ?: [['n' => 1]]));
?>
<div class="gr-box gr-card" id="bajas">
    <div class="gr-card__top">
        <h3>Bajas de clientes de pago<?= $ayuda('bajas') ?></h3>
        <span class="gr-note" style="margin:0"><?= count($bajas) ?> desde el principio · sin cambios de plan</span>
    </div>
    <?php if (!$bajas): ?>
        <p class="gr-muted" style="margin:6px 0 0">Ningún cliente de pago se ha dado de baja todavía.</p>
    <?php else: ?>
        <div class="gr-two" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr); gap: 22px; margin: 8px 0 16px;">
            <div>
                <h4 style="margin:0 0 10px;font-size:.82rem">Motivos<?= $ayuda('b_motivos') ?></h4>
                <ul class="gr-plans">
                    <?php foreach ($d['motivos'] as $m): ?>
                        <li>
                            <div class="row"><span><?= esc($m['label']) ?></span><strong><?= $m['n'] ?> <span class="gr-muted" style="font-weight:600">· <?= round($m['n'] / count($bajas) * 100) ?>%</span></strong></div>
                            <div class="bar"><span style="width:<?= round($m['n'] / $maxMotivo * 100) ?>%;background:<?= $m['motivo'] === '' ? '#b4b2ab' : '#d03b3b' ?>"></span></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="gr-kv" style="margin:0;padding:0;border:0;align-content:start">
                <span>Bajas en total</span><strong><?= count($bajas) ?></strong>
                <span>Ingresos mensuales perdidos (MRR)<?= $ayuda('b_mrr') ?></span><strong><?= $eur($mrrPerdido) ?>/mes</strong>
                <span>Tiempo típico con el plan (mediana)</span><strong><?= esc($duracion($mediana)) ?></strong>
                <span>Ya no usaban el producto al cancelar<?= $ayuda('b_paron') ?></span><strong><?= $nParon ?> de <?= count($bajas) ?> (<?= round($nParon / count($bajas) * 100) ?>%)</strong>
                <span>Han vuelto a pagar</span><strong><?= count(array_filter($bajas, fn ($b) => $b['volvio'] !== null)) ?></strong>
            </div>
        </div>
        <div class="gr-table-wrap">
            <table class="gr-table ce-bajas" style="min-width:900px">
                <thead>
                    <tr>
                        <th style="text-align:left">Baja</th>
                        <th style="text-align:left">Cliente</th>
                        <th style="text-align:left">Plan</th>
                        <th>Duró<?= $ayuda('b_duro') ?></th>
                        <th>Pagó<?= $ayuda('b_pago') ?></th>
                        <th style="text-align:left">Motivo<?= $ayuda('b_motivos') ?></th>
                        <th style="text-align:left">Último uso<?= $ayuda('b_uso') ?></th>
                        <th style="text-align:left">Hoy<?= $ayuda('b_hoy') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bajas as $b):
                    $antes = $b['ultimo_uso'] !== null ? (int) round(($b['hasta'] - $b['ultimo_uso']) / 86400) : null; ?>
                    <tr>
                        <td style="text-align:left"><?= date('d/m/Y', $b['hasta']) ?></td>
                        <td style="text-align:left;white-space:normal"><a href="<?= site_url('admin/email-logs') ?>?user_id=<?= $b['user_id'] ?>#historial" title="Ver sus correos" style="color:#0f172a;font-weight:600;text-decoration:none"><?= esc($b['email']) ?></a><?php if ($b['nombre'] !== '' && $b['nombre'] !== explode('@', $b['email'])[0]): ?><br><span class="gr-muted"><?= esc($b['nombre']) ?></span><?php endif; ?></td>
                        <td style="text-align:left"><span class="gr-dot" style="background:<?= $b['producto'] === 'risk' ? 'var(--risk)' : 'var(--api)' ?>"></span><?= esc($b['plan']) ?><?= $b['anual'] ? ' anual' : '' ?> <span class="gr-muted">· <?= $eur($b['mrr']) ?>/mes</span></td>
                        <td><?= esc($duracion($b['dias'])) ?></td>
                        <td><?= $eur($b['pagos']['total']) ?> <span class="gr-muted">(<?= $b['pagos']['n'] ?>)</span></td>
                        <td style="text-align:left;white-space:normal;max-width:240px">
                            <?php if ($b['motivo'] === ''): ?><span class="gr-muted"><?= esc($b['motivo_label']) ?></span>
                            <?php else: ?><strong style="font-weight:700"><?= esc($b['motivo_label']) ?></strong><?php endif; ?>
                            <?php if ($b['comentario'] !== ''): ?><br><em style="color:#52514e">“<?= esc($b['comentario']) ?>”</em><?php endif; ?>
                        </td>
                        <td style="text-align:left;white-space:normal">
                            <?php if ($b['ultimo_uso'] === null): ?><span style="color:#b42f2f;font-weight:700">Nunca lo usó</span>
                            <?php elseif ($antes > 14): ?><span style="color:#b45309;font-weight:700"><?= esc($duracion($antes)) ?> antes</span><br><span class="gr-muted"><?= date('d/m/Y', $b['ultimo_uso']) ?></span>
                            <?php elseif ($antes >= 0): ?>Lo usaba hasta la baja<br><span class="gr-muted"><?= date('d/m/Y', $b['ultimo_uso']) ?></span>
                            <?php else: ?>Lo usaba hasta la baja<?php endif; ?>
                        </td>
                        <td style="text-align:left;white-space:normal">
                            <?php if ($b['volvio'] !== null): ?><span class="gr-chip gr-chip--up">Volvió · <?= esc($b['volvio']) ?></span>
                            <?php elseif ($b['usa_despues']): ?>Sigue usándolo gratis<br><span class="gr-muted">última vez <?= date('d/m/Y', $b['ultimo_uso']) ?></span>
                            <?php else: ?><span class="gr-muted">No ha vuelto</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="gr-box gr-card">
    <div class="gr-card__top">
        <h3>Mes a mes</h3>
        <span class="gr-note" style="margin:0">Activados y "han pagado" son de las altas de ese mes (cohorte). Solvencia guarda las visitas a informes desde el 04/09/2026.</span>
    </div>
    <div class="gr-table-wrap">
        <table class="gr-table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="grp"><span class="gr-dot" style="background:var(--api)"></span>API</th>
                    <th><span class="gr-dot" style="background:var(--risk)"></span>Solvencia</th>
                    <th>Total</th>
                    <th>vs. mes ant.<?= $ayuda('t_vs') ?></th>
                    <th class="grp">Activados API<?= $ayuda('t_act_api') ?></th>
                    <th>Activados Solv.<?= $ayuda('t_act_risk') ?></th>
                    <th>Han pagado<?= $ayuda('t_pagan') ?></th>
                    <th class="grp">Subs nuevas<?= $ayuda('t_nuevas') ?></th>
                    <th>Bajas<?= $ayuda('t_bajas') ?></th>
                    <th>Subs a fin de mes<?= $ayuda('t_subs') ?></th>
                    <th>MRR<?= $ayuda('t_mrr') ?></th>
                    <th class="grp">Facturado<?= $ayuda('t_fact') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                $filas = array_reverse($d['meses']);
                foreach ($filas as $k => $m):
                    $r = $pm[$m];
                    if ($r['total'] === 0 && $r['subs']['mrr'] <= 0 && !$r['facturado']) {
                        continue;
                    }
                    $anterior = $filas[$k + 1] ?? null;
                    $esActual = $m === $ma;
            ?>
                <tr class="<?= $esActual ? 'actual' : '' ?>">
                    <td><strong><?= esc(C::mesCorto($m)) ?></strong><?= $esActual ? ' <span class="gr-muted">(día ' . $d['dia'] . ')</span>' : '' ?></td>
                    <td class="grp"><?= $n($r['api']) ?></td>
                    <td><?= $r['risk'] ? $n($r['risk']) : '<span class="gr-muted">–</span>' ?></td>
                    <td><strong><?= $n($r['total']) ?></strong></td>
                    <td><?= $esActual ? $chip($r['total'], $antesMtd) . ' <span class="gr-muted" title="Frente al mismo día del mes anterior">*</span>' : ($anterior ? $chip($r['total'], $pm[$anterior]['total']) : '') ?></td>
                    <td class="grp"><?= $pct($r['activados']['api'], $r['api']) ?></td>
                    <td><?= $r['risk'] ? $pct($r['activados']['risk'], $r['risk']) : '<span class="gr-muted">–</span>' ?></td>
                    <td><?= $n($r['pagan']['api'] + $r['pagan']['risk']) ?> <span class="gr-muted">(<?= $pct($r['pagan']['api'] + $r['pagan']['risk'], $r['total']) ?>)</span></td>
                    <td class="grp"><?= array_sum($r['subs']['nuevas']) ? '+' . array_sum($r['subs']['nuevas']) : '<span class="gr-muted">0</span>' ?></td>
                    <td><?= array_sum($r['subs']['bajas']) ? '−' . array_sum($r['subs']['bajas']) : '<span class="gr-muted">0</span>' ?></td>
                    <td><?= $n(array_sum($r['subs']['activas'])) ?></td>
                    <td><?= $eur($r['subs']['mrr']) ?></td>
                    <td class="grp"><?= $r['facturado'] ? $eur($r['facturado']) : '<span class="gr-muted">–</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="gr-note">* El mes en curso se compara con el mismo día del mes anterior. Facturado: facturas pagadas sin IVA (las devoluciones restan). Sin administradores ni el usuario monitor.</p>
</div>

<?= view('admin/partials/panel_js') ?>
<?php endif; ?>

</div>
<?= $this->endSection() ?>
