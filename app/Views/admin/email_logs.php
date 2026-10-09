<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>

<?= $this->section('styles') ?>
<?= view('admin/partials/panel_estilos') ?>
<style>
    .gr-root { --cuenta: #1baf7a; --otros: #b4b2ab; }
    .ce-estado { display: flex; align-items: center; gap: 16px; padding: 18px 22px; margin-bottom: 14px; border-radius: 18px; border: 1px solid; }
    .ce-estado__icon { flex: none; width: 46px; height: 46px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; font-weight: 900; }
    .ce-estado h2 { margin: 0 0 2px; font-size: 1.15rem; font-weight: 900; color: #0f172a; }
    .ce-estado p { margin: 0; font-size: .86rem; color: var(--ink-2); }
    .ce-estado--bien { background: linear-gradient(90deg, #ecfdf3, #fff 70%); border-color: #bbf7d0; }
    .ce-estado--bien .ce-estado__icon { background: linear-gradient(135deg, #3cc46b, #0a8f3c); }
    .ce-estado--aviso { background: linear-gradient(90deg, #fffbeb, #fff 70%); border-color: #fde68a; }
    .ce-estado--aviso .ce-estado__icon { background: linear-gradient(135deg, #f5b13d, #d97706); }
    .ce-estado--mal { background: linear-gradient(90deg, #fef2f2, #fff 70%); border-color: #fecaca; }
    .ce-estado--mal .ce-estado__icon { background: linear-gradient(135deg, #f87171, #d03b3b); }
    .ce-checks { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; margin-bottom: 14px; }
    .ce-check { position: relative; padding: 16px 18px 14px 18px; border-radius: 16px; background: #fff; border: 1px solid var(--line); }
    .ce-check__top { display: flex; align-items: flex-start; gap: 10px; padding-right: 20px; }
    .ce-check__dot { flex: none; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .78rem; font-weight: 900; color: #fff; margin-top: 1px; }
    .ce-check--bien .ce-check__dot { background: #0ca30c; }
    .ce-check--aviso .ce-check__dot { background: #d97706; }
    .ce-check--mal .ce-check__dot { background: #d03b3b; }
    .ce-check--mal { border-color: #fecaca; background: #fffafa; }
    .ce-check--aviso { border-color: #fde68a; background: #fffdf7; }
    .ce-check__title { font-size: .84rem; font-weight: 800; color: #0f172a; line-height: 1.35; }
    .ce-check__val { font-size: 1.05rem; font-weight: 800; margin: 10px 0 3px; color: #0f172a; font-variant-numeric: tabular-nums; }
    .ce-check__det { font-size: .76rem; color: var(--ink-2); line-height: 1.45; }
    .ce-check .gr-help { position: absolute; top: 14px; right: 14px; margin: 0; }
    .ce-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
    .ce-list li { font-size: .82rem; line-height: 1.45; padding: 8px 10px; border-radius: 10px; background: #f8fafc; }
    .ce-list li .when { color: var(--ink-3); font-size: .74rem; }
    .ce-list .err { font-family: ui-monospace, Consolas, monospace; font-size: .72rem; color: #991b1b; word-break: break-all; margin-top: 3px; }
    .ce-ok { display: flex; gap: 8px; align-items: center; font-size: .86rem; color: #166534; background: #f0fdf4; border-radius: 10px; padding: 10px 12px; }
    .ce-grp td { background: #f8fafc; font-weight: 800; font-size: .78rem !important; color: var(--ink-2); text-align: left !important; letter-spacing: .03em; text-transform: uppercase; }
    .ce-name { font-weight: 600; color: #0f172a; }
    .ce-slug { font-family: ui-monospace, Consolas, monospace; font-size: .72rem; color: var(--ink-3); }
    .ce-old { color: #b45309; font-weight: 700; }
    .ce-filters { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; align-items: end; margin: 6px 0 14px; }
    .ce-filters .ancho { grid-column: span 2; }
    .ce-filters .ce-in { width: 100%; box-sizing: border-box; }
    .ce-filters label { display: block; font-size: .7rem; font-weight: 700; color: var(--ink-2); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .03em; }
    .ce-in { border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; font-size: .85rem; height: 36px; background: #fff; color: #0f172a; font-family: inherit; }
    .ce-btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font-weight: 700; font-size: .85rem; cursor: pointer; text-decoration: none; font-family: inherit; }
    .ce-btn--pri { background: #2152ff; border-color: #2152ff; color: #fff; }
    .ce-hist td { text-align: left !important; white-space: normal !important; vertical-align: top; }
    .ce-hist th { text-align: left !important; }
    .ce-hist td.c, .ce-hist th.c { text-align: center !important; }
    .ce-badge { display: inline-block; font-size: .68rem; font-weight: 800; padding: 2px 8px; border-radius: 99px; white-space: nowrap; }
    .ce-badge--ok { background: #e7f6e7; color: #0a7a0a; }
    .ce-badge--err { background: #fbeaea; color: #b42f2f; }
    .ce-yes { color: #0a8f3c; font-weight: 900; }
    .ce-no { color: #cbd5e1; }
    .ce-modal { position: fixed; inset: 0; z-index: 1200; display: none; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, .55); backdrop-filter: blur(2px); }
    .ce-modal.open { display: flex; }
    .ce-modal__box { width: 100%; max-width: 880px; height: min(88vh, 900px); background: #fff; border-radius: 18px; box-shadow: 0 30px 80px -20px rgba(15,23,42,.5); display: flex; flex-direction: column; overflow: hidden; }
    .ce-modal__head { display: flex; gap: 12px; align-items: flex-start; padding: 16px 20px 12px; border-bottom: 1px solid var(--rule); }
    .ce-modal__head h3 { margin: 0; font-size: 1.02rem; font-weight: 800; color: #0f172a; line-height: 1.35; flex: 1; word-break: break-word; }
    .ce-modal__close { flex: none; width: 34px; height: 34px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; font-size: 1rem; cursor: pointer; color: #475569; }
    .ce-modal__close:hover { background: #f1f5f9; }
    .ce-modal__meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 8px 18px; padding: 12px 20px; font-size: .8rem; background: #f8fafc; border-bottom: 1px solid var(--rule); }
    .ce-modal__meta > div > span { display: block; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-3); margin-bottom: 1px; }
    .ce-modal__meta > div > strong { color: #0f172a; font-weight: 600; word-break: break-word; }
    .ce-modal__body { flex: 1; min-height: 0; position: relative; background: #f1f5f9; }
    .ce-modal__body iframe { width: 100%; height: 100%; border: 0; background: #fff; display: block; }
    .ce-modal__msg { padding: 24px; font-size: .88rem; line-height: 1.6; color: var(--ink-2); overflow: auto; height: 100%; box-sizing: border-box; }
    .ce-modal__msg .aviso { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 14px 16px; margin-bottom: 14px; }
    .ce-modal__msg .resto { font-family: ui-monospace, Consolas, monospace; font-size: .75rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; color: #475569; white-space: pre-wrap; }
    .ce-modal__err { margin: 0 20px; padding: 10px 12px; border-radius: 10px; background: #fef2f2; color: #991b1b; font-family: ui-monospace, Consolas, monospace; font-size: .74rem; word-break: break-all; }
    .ce-pager { display: flex; gap: 10px; align-items: center; justify-content: center; margin-top: 14px; font-size: .85rem; color: var(--ink-2); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php use App\Libraries\CorreosSalud as CS; ?>
<div class="gr-root">

<div class="gr-head">
    <div>
        <h1 class="title" style="margin-bottom:4px">Correos</h1>
        <p class="subtitle">¿Se están enviando los correos de la API y de Solvencia? Comprobaciones, volumen, resultado por correo e historial.<?= $d ? ' Datos a ' . date('d/m/Y H:i', $d['ahora']) . '.' : '' ?></p>
    </div>
    <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
</div>

<?php if (!$d): ?>
    <div class="gr-alert"><strong>No se han podido calcular los datos.</strong> <?= esc((string) $error) ?></div>
<?php else: ?>
<?php
    $n = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($a, $b) => $b > 0 ? str_replace('.', ',', (string) round($a / $b * 100, 1)) . '%' : '–';
    $hace = fn ($ts) => $ts ? 'hace ' . CS::hace($d['ahora'] - $ts) : 'nunca';
    $chip = function ($ahora, $antes) {
        if ($antes <= 0) {
            return '<span class="gr-chip gr-chip--flat">–</span>';
        }
        $v = round(($ahora - $antes) / $antes * 100);
        $cls = $v > 0 ? 'up' : ($v < 0 ? 'down' : 'flat');

        return '<span class="gr-chip gr-chip--' . $cls . '">' . ($v > 0 ? '↑ +' : ($v < 0 ? '↓ ' : '→ ')) . $v . '%</span>';
    };
    $tot = $d['tot'];
    $kpi = $d['kpi'];
    $colores = ['api' => 'var(--api)', 'risk' => 'var(--risk)', 'cuenta' => 'var(--cuenta)', 'interno' => 'var(--otros)', 'otros' => 'var(--otros)'];

    // ---- Textos de ayuda ----
    $H = [
        'estado' => "Resumen de las 4 comprobaciones de abajo. Verde si todas están bien; ámbar si alguna merece un vistazo; rojo si alguna indica que los correos no están saliendo.",
        'c_cron' => "<strong>Qué mira:</strong> cuándo envió el último correo el proceso automático (<code>php spark email:automation</code>, que corre cada hora con el cron del servidor). Sale de la tabla <code>user_email_automation</code>, donde ese proceso apunta cada correo que decide mandar."
            . "<br><br><strong>Cómo leerlo:</strong> verde si envió algo en las últimas 12 h; ámbar entre 12 y 24 h; rojo si lleva más de un día sin enviar nada. Con el volumen actual siempre hay algo que mandar cada pocas horas, así que un día entero sin envíos casi seguro es que el cron se ha parado (servidor reiniciado, cron borrado, el comando falla...)."
            . "<br><br><strong>Si sale rojo:</strong> mira el crontab del servidor y lanza el comando a mano para ver si da error.",
        'c_smtp' => "<strong>Qué mira:</strong> de los correos enviados en 7 días, cuántos rechazó el servidor de correo (SMTP) en el momento de enviar. Cada envío queda en <code>email_logs</code> con <code>status = success</code> o <code>error</code> y el mensaje del servidor."
            . "<br><br><strong>Importante:</strong> \"enviado\" significa que nuestro servidor de correo lo <em>aceptó</em>. No garantiza que llegue a la bandeja de entrada: si luego rebota (dirección que no existe) o Gmail lo manda a spam, aquí no se ve."
            . "<br><br>Verde: 0 fallos. Ámbar: menos del 5 %. Rojo: 5 % o más (contraseña SMTP cambiada, servidor caído, límite de envío...).",
        'c_bienv' => "<strong>Qué mira:</strong> que cada persona que se ha registrado en los últimos 7 días (API o Solvencia) haya recibido su correo de bienvenida (o el de crear contraseña, si se registró con el alta rápida). Es la prueba más directa de que el envío funciona: toda alta debe tener uno."
            . "<br><br>Se dejan 15 minutos de margen para las altas muy recientes. Si falta alguna, aparece en el bloque \"Para revisar\" con su email.",
        'c_coh' => "<strong>Qué mira:</strong> el proceso automático apunta en <code>user_email_automation</code> cada correo que <em>decide</em> enviar; <code>email_logs</code> guarda cada correo que <em>sale de verdad</em>. Aquí se cruzan las dos: por cada decisión de los últimos 7 días se busca un envío a ese usuario en ±10 minutos."
            . "<br><br>Si el cron decide 100 correos pero solo salen 60, algo falla entre medias (la plantilla no existe, el usuario no tiene email, una excepción al montar el correo...). Es como comparar los <em>jobs</em> encolados con los terminados."
            . "<br><br>Verde: 98 % o más. Ámbar: 90-98 %. Rojo: menos del 90 %.",
        'k_total' => "Todos los correos que ha enviado la web en los últimos 7 días (automáticos, de cuenta, avisos internos y manuales), comparados con los 7 días anteriores. La curva es el total por día del último mes."
            . "<br><br>Un cambio grande no es malo de por sí: los envíos masivos manuales o los resúmenes de principio de mes hacen picos.",
        'k_api' => "Correos de la API enviados en 7 días: bienvenida, secuencia de 0 llamadas, avisos de cupo, informes mensuales, resúmenes de clientes de pago..."
            . "<br><br><strong>Último envío:</strong> cuándo salió el último correo de este grupo.<br><strong>Clics:</strong> de los correos con seguimiento de los últimos 30 días, en qué % alguien pinchó un enlace. Es la mejor señal de que el correo llega a la bandeja y se lee (un 2-5 % es normal en correos automáticos).",
        'k_risk' => "Correos de Solvencia (perfil de riesgo) enviados en 7 días: bienvenida, informes sin usar, resumen de cartera, nuevo mes de informes, dormidos, alertas BORME..."
            . "<br><br><strong>Último envío</strong> y <strong>clics</strong>: igual que en la API.",
        'k_fallos' => "Correos que el servidor SMTP rechazó en los últimos 7 días, y el % sobre lo enviado. Abajo, los de los últimos 30 días. Los mensajes de error están en el bloque \"Últimos fallos de envío\".",
        'k_bajas' => "Usuarios que han pulsado \"darse de baja\" de los correos y ya no reciben los comerciales (sí los de cuenta y facturas). % sobre todos los usuarios de cada producto, desde siempre."
            . "<br><br>Un % alto indica que se les escribe demasiado o con correos que no les interesan. Por encima del 20-25 % conviene revisar la frecuencia.",
        'g_dia' => "Correos enviados cada día durante los últimos 30 días, apilados por grupo:<br>• <strong>API</strong> y <strong>Solvencia</strong>: los correos de cada producto.<br>• <strong>Cuenta y pagos</strong>: contraseñas, facturas, cobros (transaccionales, de los dos productos).<br>• <strong>Internos y otros</strong>: avisos que te llegan a ti (nuevo registro) y envíos sin plantilla."
            . "<br><br>Un punto rojo encima de un día significa que ese día hubo fallos de envío (el número). Pasa el ratón por un día para ver el detalle.",
        'revisar' => "Lo que las comprobaciones han encontrado y conviene mirar: altas sin bienvenida, correos que el cron decidió y no salieron, y los últimos fallos del servidor de correo. Si está todo vacío, no hay nada pendiente.",
        't_correo' => "Cada correo automático (plantilla). El nombre en castellano y debajo su identificador (<code>template_slug</code>), por si quieres buscarlo en el código o en Plantillas de email.",
        't_7d' => "Envíos correctos en los últimos 7 días.",
        't_30d' => "Envíos (correctos y fallidos) en los últimos 30 días.",
        't_fallos' => "Envíos rechazados por el servidor de correo en 30 días.",
        't_clics' => "De los envíos con seguimiento, en qué % alguien pinchó un enlace (y cuántos). Es la mejor medida de que el correo se lee. Las aperturas no se usan: Gmail y Apple descargan las imágenes solos y las falsean, y casi todos los automáticos no llevan el píxel de apertura.",
        't_ultimo' => "Cuándo salió el último envío correcto de ese correo. En naranja si hace más de 14 días: puede ser normal (hay correos que solo se mandan en casos raros, como 'cobro fallido') o que su disparador ya no funcione. Si un correo que antes salía a menudo lleva semanas sin salir, revísalo.",
        'h_hist' => "Cada correo enviado, el más reciente primero. Los filtros se combinan. \"Ver\" abre el correo tal como lo recibió el usuario (sin contar como apertura ni clic).",
        'h_abierto' => "Si se cargó la imagen invisible de seguimiento. Poco fiable: Gmail y Apple Mail cargan las imágenes solos (cuenta como abierto sin que nadie lo lea) y otros clientes las bloquean. Además, un clic también lo marca como abierto. Mejor fijarse en los clics.",
        'h_clic' => "Si el usuario pinchó algún enlace del correo (los enlaces pasan por <code>/e/c/...</code> para contarlo).",
        'h_login' => "Si después de pinchar el correo el usuario inició sesión en la web. Indica que el correo le llevó a usar el producto.",
    ];
    $ayuda = fn (string $k) => '<button type="button" class="gr-help" aria-label="¿Qué significa?" data-help="' . esc($H[$k] ?? '', 'attr') . '">?</button>';

    $estadoTxt = [
        'bien' => ['✓', 'Los correos se están enviando con normalidad', 'Las 4 comprobaciones están bien.'],
        'aviso' => ['!', 'Hay algo que conviene revisar', 'Alguna comprobación está en ámbar: mira el detalle abajo.'],
        'mal' => ['✕', 'Hay un problema con los envíos', 'Alguna comprobación está en rojo: mira el detalle abajo y el bloque "Para revisar".'],
    ][$d['estado_general']];
    $iconCheck = ['bien' => '✓', 'aviso' => '!', 'mal' => '✕'];
    $helpCheck = ['cron' => 'c_cron', 'smtp' => 'c_smtp', 'bienvenida' => 'c_bienv', 'coherencia' => 'c_coh'];
?>

<!-- ====== Estado general + comprobaciones ====== -->
<div class="ce-estado ce-estado--<?= $d['estado_general'] ?>">
    <span class="ce-estado__icon"><?= $estadoTxt[0] ?></span>
    <div style="flex:1">
        <h2><?= esc($estadoTxt[1]) ?><?= $ayuda('estado') ?></h2>
        <p><?= esc($estadoTxt[2]) ?></p>
    </div>
</div>

<div class="ce-checks">
    <?php foreach ($d['checks'] as $k => $c): ?>
        <div class="ce-check ce-check--<?= $c['estado'] ?>">
            <?= $ayuda($helpCheck[$k]) ?>
            <div class="ce-check__top"><span class="ce-check__dot"><?= $iconCheck[$c['estado']] ?></span><span class="ce-check__title"><?= esc($c['titulo']) ?></span></div>
            <div class="ce-check__val"><?= esc($c['valor']) ?></div>
            <div class="ce-check__det"><?= esc($c['detalle']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<?php
    // ---- Tarjetas ----
    $serie = array_map(fn ($x) => $x['api'] + $x['risk'] + $x['cuenta'] + $x['interno'] + $x['otros'] + $x['error'], $d['dias']);
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
            . '<polyline points="' . $line . '" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/></svg>';
    };
    $acc = [
        'total' => '--acc:#2152ff;--acc-2:#5c7cff;--acc-soft:rgba(33,82,255,.08);--acc-line:rgba(33,82,255,.35);--acc-shadow:rgba(33,82,255,.35)',
        'api'   => '--acc:#2a78d6;--acc-2:#6da7ec;--acc-soft:rgba(42,120,214,.08);--acc-line:rgba(42,120,214,.35);--acc-shadow:rgba(42,120,214,.35)',
        'risk'  => '--acc:#eb6834;--acc-2:#f5a07c;--acc-soft:rgba(235,104,52,.09);--acc-line:rgba(235,104,52,.35);--acc-shadow:rgba(235,104,52,.35)',
        'fallo' => '--acc:#d03b3b;--acc-2:#f07c7c;--acc-soft:rgba(208,59,59,.08);--acc-line:rgba(208,59,59,.35);--acc-shadow:rgba(208,59,59,.35)',
        'bajas' => '--acc:#6d4fe0;--acc-2:#9b87f5;--acc-soft:rgba(109,79,224,.09);--acc-line:rgba(109,79,224,.35);--acc-shadow:rgba(109,79,224,.35)',
    ];
    $ico = [
        'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>',
        'api' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/></svg>',
        'risk' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'fallo' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>',
        'bajas' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/></svg>',
    ];
    $bajas = $d['bajas'];
    $bApi = ($bajas['api']['n'] ?? 0) > 0 ? $bajas['api']['bajas'] / $bajas['api']['n'] * 100 : 0;
    $bRisk = ($bajas['risk']['n'] ?? 0) > 0 ? $bajas['risk']['bajas'] / $bajas['risk']['n'] * 100 : 0;
    $fmtP = fn ($v) => str_replace('.', ',', (string) round($v, 1)) . '%';
?>
<div class="gr-tiles">
    <div class="gr-tile" style="<?= $acc['total'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $ico['mail'] ?></span><span class="gr-tile__label">Enviados en 7 días<?= $ayuda('k_total') ?></span></div>
        <div class="gr-tile__value"><?= $n($tot['7d']) ?></div>
        <div class="gr-tile__meta"><?= $chip($tot['7d'], $tot['prev7d']) ?> vs. 7 días anteriores (<?= $n($tot['prev7d']) ?>)</div>
        <div class="gr-tile__viz">
            <?= $spark($serie, '#2152ff', 'spT') ?>
            <div class="gr-tile__foot"><span>Por día, 30 días</span><span><strong><?= $n($tot['30d']) ?></strong> en total</span></div>
        </div>
    </div>
    <?php foreach (['api' => 'Correos de la API', 'risk' => 'Correos de Solvencia'] as $g => $label): $k = $kpi[$g]; ?>
        <div class="gr-tile" style="<?= $acc[$g] ?>">
            <div class="gr-tile__head"><span class="gr-tile__icon"><?= $ico[$g] ?></span><span class="gr-tile__label"><?= $label ?><?= $ayuda('k_' . $g) ?></span></div>
            <div class="gr-tile__value"><?= $n($k['7d']) ?><small>en 7 días</small></div>
            <div class="gr-tile__meta"><?= $chip($k['7d'], $k['prev7d']) ?> vs. 7 días anteriores (<?= $n($k['prev7d']) ?>)</div>
            <div class="gr-tile__viz">
                <div class="gr-tile__foot" style="margin-top:0"><span>Último envío</span><strong><?= esc($hace($k['ultimo'])) ?></strong></div>
                <div class="gr-tile__foot"><span>Clics (30 días)</span><strong><?= $pct($k['clics30'], $k['trk30']) ?> <span class="gr-muted" style="font-weight:600">· <?= $k['clics30'] ?> de <?= $k['trk30'] ?></span></strong></div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="gr-tile" style="<?= $acc['fallo'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $ico['fallo'] ?></span><span class="gr-tile__label">Fallos de envío<?= $ayuda('k_fallos') ?></span></div>
        <div class="gr-tile__value"><?= $n($tot['error7d']) ?><small><?= $pct($tot['error7d'], $tot['7d']) ?> en 7 días</small></div>
        <div class="gr-tile__meta"><?= $tot['error7d'] === 0 ? '<span class="gr-chip gr-chip--up">✓ sin fallos</span>' : '<span class="gr-chip gr-chip--down">revisar</span>' ?></div>
        <div class="gr-tile__viz"><div class="gr-tile__foot" style="margin-top:0"><span>Últimos 30 días</span><strong><?= $n($tot['error30d']) ?> fallos</strong></div></div>
    </div>
    <div class="gr-tile" style="<?= $acc['bajas'] ?>">
        <div class="gr-tile__head"><span class="gr-tile__icon"><?= $ico['bajas'] ?></span><span class="gr-tile__label">Se han dado de baja<?= $ayuda('k_bajas') ?></span></div>
        <div class="gr-tile__value"><?= $n(($bajas['api']['bajas'] ?? 0) + ($bajas['risk']['bajas'] ?? 0)) ?><small>usuarios</small></div>
        <div class="gr-tile__meta">De los correos comerciales</div>
        <div class="gr-tile__viz">
            <div class="gr-minibars">
                <div><span>API <strong><?= $fmtP($bApi) ?></strong></span><i><b style="width:<?= round($bApi, 1) ?>%;background:var(--api)"></b></i></div>
                <div><span>Solvencia <strong><?= $fmtP($bRisk) ?></strong></span><i><b style="width:<?= round($bRisk, 1) ?>%;background:var(--risk)"></b></i></div>
            </div>
        </div>
    </div>
</div>

<?php
    // ================= Grafico: envios por dia (30 dias), apilados por grupo =================
    $grupos = ['api' => 'API', 'risk' => 'Solvencia', 'cuenta' => 'Cuenta y pagos', 'otros' => 'Internos y otros'];
    $cols = [];
    foreach ($d['dias'] as $x) {
        $cols[] = ['dia' => $x['dia'], 'api' => $x['api'], 'risk' => $x['risk'], 'cuenta' => $x['cuenta'], 'otros' => $x['interno'] + $x['otros'], 'error' => $x['error']];
    }
    $max = max(1, ...array_map(fn ($c) => $c['api'] + $c['risk'] + $c['cuenta'] + $c['otros'], $cols));
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
    $yMax = 4 * $paso($max * 1.08, 4);
    $W = 760; $Hh = 230; $L = 34; $R = 6; $T = 16; $B = 24;
    $plotH = $Hh - $T - $B;
    $slot = ($W - $L - $R) / count($cols);
    $bw = max(4, min(18, $slot * 0.66));
    $y = fn ($v) => $T + $plotH * (1 - $v / $yMax);
    $barra = function (float $x, float $yy, float $w, float $h, bool $redondo, string $fill) {
        if ($h <= 0.5) {
            return '';
        }
        $r = $redondo ? min(3, $h, $w / 2) : 0;

        return sprintf('<path d="M%.1f %.1fV%.1fQ%.1f %.1f %.1f %.1fH%.1fQ%.1f %.1f %.1f %.1fV%.1fZ" fill="%s"/>',
            $x, $yy + $h, $yy + $r, $x, $yy, $x + $r, $yy, $x + $w - $r, $x + $w, $yy, $x + $w, $yy + $r, $yy + $h, $fill);
    };
    $diasSem = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
?>
<div class="gr-box gr-card">
    <div class="gr-card__top">
        <h3>Correos enviados por día<?= $ayuda('g_dia') ?></h3>
        <div class="gr-legend">
            <?php foreach ($grupos as $g => $label): ?><span><span class="gr-dot" style="background:<?= $colores[$g] ?>"></span><?= $label ?></span><?php endforeach; ?>
            <span><span class="gr-dot" style="background:#d03b3b;border-radius:50%"></span>Fallos</span>
        </div>
    </div>
    <svg class="gr-svg" viewBox="0 0 <?= $W ?> <?= $Hh ?>" role="img" aria-label="Correos enviados por día en los últimos 30 días">
        <?php foreach ([0, .25, .5, .75, 1] as $k): $v = $yMax * $k; ?>
            <line x1="<?= $L ?>" x2="<?= $W - $R ?>" y1="<?= round($y($v), 1) ?>" y2="<?= round($y($v), 1) ?>" stroke="<?= $k == 0 ? '#cbd5e1' : '#f1f0ec' ?>"/>
            <text x="<?= $L - 6 ?>" y="<?= round($y($v) + 4, 1) ?>" text-anchor="end" font-size="10" fill="#8a8984"><?= $n($v) ?></text>
        <?php endforeach; ?>
        <?php foreach ($cols as $i => $c):
            $x = $L + $slot * $i + ($slot - $bw) / 2;
            $segs = array_values(array_filter([[$c['api'], $colores['api']], [$c['risk'], $colores['risk']], [$c['cuenta'], $colores['cuenta']], [$c['otros'], $colores['otros']]], fn ($s) => $s[0] > 0));
            $base = $T + $plotH;
            foreach ($segs as $j => $s) {
                $h = $plotH * $s[0] / $yMax;
                echo $barra($x, $base - $h, $bw, $h - ($j > 0 ? 1.5 : 0), $j === count($segs) - 1, $s[1]);
                $base -= $h;
            }
            $total = $c['api'] + $c['risk'] + $c['cuenta'] + $c['otros'];
            $ts = strtotime($c['dia']);
            if ($c['error'] > 0): ?>
                <circle cx="<?= round($x + $bw / 2, 1) ?>" cy="<?= round($y($total) - 9, 1) ?>" r="6" fill="#d03b3b" stroke="#fff" stroke-width="2"/>
                <text x="<?= round($x + $bw / 2, 1) ?>" y="<?= round($y($total) - 6, 1) ?>" text-anchor="middle" font-size="8" font-weight="800" fill="#fff"><?= $c['error'] > 9 ? '9+' : $c['error'] ?></text>
            <?php endif;
            if ($i % 5 === 4 || $i === count($cols) - 1): ?>
                <text x="<?= round($x + $bw / 2, 1) ?>" y="<?= $Hh - 6 ?>" text-anchor="middle" font-size="10" fill="#8a8984"><?= date('d/m', $ts) ?></text>
            <?php endif;
            $tip = $diasSem[(int) date('w', $ts)] . ' ' . date('d/m', $ts) . ($i === count($cols) - 1 ? ' (hoy, hasta ahora)' : '')
                . "\nAPI: {$c['api']} · Solvencia: {$c['risk']}\nCuenta y pagos: {$c['cuenta']} · Internos y otros: {$c['otros']}\nTotal enviados: {$total}" . ($c['error'] ? "\nFallos: {$c['error']}" : ''); ?>
            <rect class="gr-hit" x="<?= round($L + $slot * $i, 1) ?>" y="<?= $T ?>" width="<?= round($slot, 1) ?>" height="<?= $plotH ?>" data-tip="<?= esc($tip, 'attr') ?>"/>
        <?php endforeach; ?>
    </svg>
</div>

<!-- ====== Para revisar ====== -->
<div class="gr-box gr-card">
    <div class="gr-card__top"><h3>Para revisar<?= $ayuda('revisar') ?></h3></div>
    <div class="gr-two" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
        <div>
            <h4 style="margin:6px 0 8px;font-size:.82rem">Altas de 7 días sin bienvenida</h4>
            <?php if (!$d['sin_bienvenida']): ?>
                <div class="ce-ok">✓ Todas las altas (<?= $d['bienvenida']['api']['altas'] + $d['bienvenida']['risk']['altas'] ?>) recibieron su bienvenida.</div>
            <?php else: ?>
                <ul class="ce-list">
                    <?php foreach ($d['sin_bienvenida'] as $u): ?>
                        <li><strong><?= esc($u['email']) ?></strong> · <?= $u['signup_intent'] === 'api' ? 'API' : 'Solvencia' ?><br><span class="when">Alta <?= date('d/m H:i', strtotime($u['created_at'])) ?> · <a href="<?= site_url('admin/email-logs') ?>?user_id=<?= (int) $u['id'] ?>#historial">ver sus correos</a></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div>
            <h4 style="margin:6px 0 8px;font-size:.82rem">Decididos por el cron y no enviados (7 días)</h4>
            <?php if (!$d['sin_envio']): ?>
                <div class="ce-ok">✓ Todo lo que decidió el cron (<?= $d['decididos'] ?>) salió.</div>
            <?php else: ?>
                <ul class="ce-list">
                    <?php foreach ($d['sin_envio'] as $a): ?>
                        <li><strong><?= esc(CS::nombre($a['email_type'])) ?></strong> <span class="ce-slug"><?= esc($a['email_type']) ?></span><br><span class="when"><?= date('d/m H:i', strtotime($a['sent_at'])) ?> · usuario #<?= (int) $a['user_id'] ?> · <a href="<?= site_url('admin/email-logs') ?>?user_id=<?= (int) $a['user_id'] ?>#historial">ver sus correos</a></span></li>
                    <?php endforeach; ?>
                    <?php if ($d['n_sin_envio'] > count($d['sin_envio'])): ?><li class="gr-muted">y <?= $d['n_sin_envio'] - count($d['sin_envio']) ?> más</li><?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div>
            <h4 style="margin:6px 0 8px;font-size:.82rem">Últimos fallos de envío (30 días)</h4>
            <?php if (!$d['errores']): ?>
                <div class="ce-ok">✓ Ningún fallo en 30 días.</div>
            <?php else: ?>
                <ul class="ce-list">
                    <?php foreach ($d['errores'] as $e): ?>
                        <li><strong><?= esc($e['nombre']) ?></strong> <span class="ce-badge ce-badge--err"><?= $e['n'] ?> <?= $e['n'] === 1 ? 'fallo' : 'fallos' ?></span><br>
                            <span class="when"><?= $e['n'] > 1 ? 'Del ' . date('d/m H:i', $e['primero']) . ' al ' . date('d/m H:i', $e['ultimo']) : date('d/m H:i', $e['ultimo']) ?> · <?= esc(implode(', ', array_slice($e['destinatarios'], 0, 2))) ?><?= count($e['destinatarios']) > 2 ? ' y ' . (count($e['destinatarios']) - 2) . ' más' : '' ?></span>
                            <div class="err"><?= esc(mb_strimwidth($e['mensaje'], 0, 160, '…')) ?></div>
                            <?php if ($e['slug'] !== ''): ?><a class="when" href="<?= site_url('admin/email-logs') ?>?status=error&plantilla=<?= urlencode($e['slug']) ?>#historial">ver en el historial</a><?php endif; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ====== Resultado por correo ====== -->
<div class="gr-box gr-card">
    <div class="gr-card__top">
        <h3>Resultado por correo</h3>
        <span class="gr-note" style="margin:0">Últimos 30 días. Pincha un correo para ver sus envíos en el historial.</span>
    </div>
    <div class="gr-table-wrap">
        <table class="gr-table" style="min-width:760px">
            <thead>
                <tr>
                    <th>Correo<?= $ayuda('t_correo') ?></th>
                    <th>7 días<?= $ayuda('t_7d') ?></th>
                    <th>30 días<?= $ayuda('t_30d') ?></th>
                    <th>Fallos<?= $ayuda('t_fallos') ?></th>
                    <th>Clics<?= $ayuda('t_clics') ?></th>
                    <th>Último envío<?= $ayuda('t_ultimo') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $grupoActual = null; foreach ($d['por_plantilla'] as $p):
                if ($p['grupo'] !== $grupoActual): $grupoActual = $p['grupo']; ?>
                    <tr class="ce-grp"><td colspan="6"><span class="gr-dot" style="background:<?= $colores[$p['grupo']] ?>"></span><?= esc(CS::GRUPOS[$p['grupo']]) ?></td></tr>
                <?php endif;
                $viejo = $p['ultimo'] && ($d['ahora'] - $p['ultimo']) > 14 * 86400;
                $link = site_url('admin/email-logs') . '?plantilla=' . urlencode($p['slug'] !== '' ? $p['slug'] : '-') . '#historial'; ?>
                <tr>
                    <td><a href="<?= esc($link, 'attr') ?>" class="ce-name" style="text-decoration:none"><?= esc($p['nombre']) ?></a><?php if ($p['slug'] !== ''): ?><br><span class="ce-slug"><?= esc($p['slug']) ?></span><?php endif; ?></td>
                    <td><?= $p['7d'] ? $n($p['7d']) : '<span class="gr-muted">0</span>' ?></td>
                    <td><?= $p['30d'] ? $n($p['30d']) : '<span class="gr-muted">0</span>' ?></td>
                    <td><?= $p['error'] ? '<span class="ce-badge ce-badge--err">' . $p['error'] . '</span>' : '<span class="gr-muted">0</span>' ?></td>
                    <td><?= $p['trk'] ? $pct($p['clics'], $p['trk']) . ' <span class="gr-muted">(' . $p['clics'] . ')</span>' : '<span class="gr-muted">–</span>' ?></td>
                    <td class="<?= $viejo ? 'ce-old' : '' ?>"><?= $p['ultimo'] ? esc($hace($p['ultimo'])) : '<span class="gr-muted">–</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ====== Historial ====== -->
<?php
    $ayuda ??= fn (string $k) => '';
    $H ??= [];
    $qs = fn (array $extra = []) => '?' . http_build_query(array_filter(array_merge($f, $extra), fn ($v) => $v !== '' && $v !== null));
?>
<div class="gr-box gr-card" id="historial">
    <div class="gr-card__top">
        <h3>Historial de envíos<?= $ayuda('h_hist') ?></h3>
        <span class="gr-note" style="margin:0"><?= number_format($hist['total'], 0, ',', '.') ?> correos con estos filtros</span>
    </div>
    <form method="get" action="<?= site_url('admin/email-logs') ?>#historial" class="ce-filters">
        <div class="ancho"><label>Buscar</label><input type="search" name="q" class="ce-in" style="width:100%" placeholder="Email, nombre o asunto" value="<?= esc($f['q'], 'attr') ?>"></div>
        <div><label>Grupo</label>
            <select name="grupo" class="ce-in"><option value="">Todos</option>
                <?php foreach (CS::GRUPOS as $k => $label): ?><option value="<?= $k ?>" <?= $f['grupo'] === $k ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?>
            </select></div>
        <div class="ancho"><label>Correo</label>
            <select name="plantilla" class="ce-in"><option value="">Todos</option><option value="-" <?= $f['plantilla'] === '-' ? 'selected' : '' ?>>Sin plantilla (manuales/antiguos)</option>
                <?php foreach ($plantillas as $slug => $nombre): ?><option value="<?= esc($slug, 'attr') ?>" <?= $f['plantilla'] === $slug ? 'selected' : '' ?>><?= esc($nombre) ?></option><?php endforeach; ?>
            </select></div>
        <div><label>Estado</label>
            <select name="status" class="ce-in"><option value="">Todos</option><option value="success" <?= $f['status'] === 'success' ? 'selected' : '' ?>>Enviado</option><option value="error" <?= $f['status'] === 'error' ? 'selected' : '' ?>>Fallido</option></select></div>
        <?php foreach (['clicked' => 'Clic', 'logged' => 'Login', 'opened' => 'Abierto'] as $k => $label): ?>
            <div><label><?= $label ?></label><select name="<?= $k ?>" class="ce-in"><option value="">–</option><option value="yes" <?= $f[$k] === 'yes' ? 'selected' : '' ?>>Sí</option><option value="no" <?= $f[$k] === 'no' ? 'selected' : '' ?>>No</option></select></div>
        <?php endforeach; ?>
        <div><label>Desde</label><input type="date" name="date_from" class="ce-in" value="<?= esc($f['date_from'], 'attr') ?>"></div>
        <div><label>Hasta</label><input type="date" name="date_to" class="ce-in" value="<?= esc($f['date_to'], 'attr') ?>"></div>
        <?php if ($f['user_id'] !== ''): ?><input type="hidden" name="user_id" value="<?= esc($f['user_id'], 'attr') ?>"><?php endif; ?>
        <div style="display:flex;gap:8px"><button type="submit" class="ce-btn ce-btn--pri" style="flex:1;justify-content:center">Filtrar</button><a href="<?= site_url('admin/email-logs') ?>#historial" class="ce-btn" title="Quitar filtros">✕</a></div>
    </form>
    <?php if ($f['user_id'] !== ''): ?>
        <p class="gr-note" style="margin:-4px 0 12px">Solo los correos de <strong><?= esc($usuarioFiltro ? ($usuarioFiltro['email'] ?: $usuarioFiltro['name']) : '#' . $f['user_id']) ?></strong> · <a href="<?= esc(site_url('admin/email-logs') . $qs(['user_id' => '', 'page' => '']), 'attr') ?>#historial">quitar</a></p>
    <?php endif; ?>
    <div class="gr-table-wrap">
        <table class="gr-table ce-hist" style="min-width:880px">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Destinatario</th>
                    <th>Correo</th>
                    <th>Grupo</th>
                    <th class="c">Abierto<?= $ayuda('h_abierto') ?></th>
                    <th class="c">Clic<?= $ayuda('h_clic') ?></th>
                    <th class="c">Login<?= $ayuda('h_login') ?></th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($hist['rows'] as $r): $g = CS::grupo($r['template_slug'], $r['signup_intent']); ?>
                <tr>
                    <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($r['created_at'])) ?><br><span class="gr-muted"><?= date('H:i:s', strtotime($r['created_at'])) ?></span></td>
                    <td><?php if ($r['user_id']): ?><a href="<?= esc(site_url('admin/email-logs') . $qs(['user_id' => (int) $r['user_id'], 'page' => '']), 'attr') ?>#historial" style="text-decoration:none;color:#0f172a;font-weight:600"><?= esc($r['user_email'] ?: ('#' . $r['user_id'])) ?></a><?php if ($r['user_name']): ?><br><span class="gr-muted"><?= esc($r['user_name']) ?></span><?php endif; ?><?php else: ?><span class="gr-muted">–</span><?php endif; ?></td>
                    <td><?php if ($r['template_slug']): ?><span class="ce-name"><?= esc(CS::nombre($r['template_slug'])) ?></span><br><?php endif; ?><span class="gr-muted"><?= esc(mb_strimwidth((string) $r['subject'], 0, 90, '…')) ?></span>
                        <?php if ($r['status'] === 'error' && $r['error_message']): ?><div class="ce-list"><div class="err"><?= esc(mb_strimwidth(trim(strip_tags(str_replace(['<br>', '\n'], ' ', (string) $r['error_message']))), 0, 140, '…')) ?></div></div><?php endif; ?></td>
                    <td><span class="gr-dot" style="background:<?= $colores[$g] ?? 'var(--otros)' ?>"></span><?= esc(CS::GRUPOS[$g]) ?></td>
                    <td class="c"><?= $r['opened_at'] ? '<span class="ce-yes" title="' . esc($r['opened_at'], 'attr') . '">✓</span>' : '<span class="ce-no">·</span>' ?></td>
                    <td class="c"><?= $r['clicked_at'] ? '<span class="ce-yes" title="' . esc($r['clicked_at'], 'attr') . '">✓</span>' : '<span class="ce-no">·</span>' ?></td>
                    <td class="c"><?= $r['logged_in_at'] ? '<span class="ce-yes" title="' . esc($r['logged_in_at'], 'attr') . '">✓</span>' : '<span class="ce-no">·</span>' ?></td>
                    <td><?= $r['status'] === 'success' ? '<span class="ce-badge ce-badge--ok">Enviado</span>' : '<span class="ce-badge ce-badge--err">Fallido</span>' ?></td>
                    <td><button type="button" class="ce-btn ce-ver" data-url="<?= site_url('admin/email-logs/' . (int) $r['id'] . '/ver') ?>" style="height:28px;padding:0 10px;font-size:.78rem">Ver</button></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$hist['rows']): ?><tr><td colspan="9" class="gr-muted" style="text-align:center !important;padding:28px">No hay correos con estos filtros.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
        <div class="ce-pager">
            <?php if ($page > 1): ?><a class="ce-btn" style="height:30px" href="<?= esc(site_url('admin/email-logs') . $qs(['page' => $page - 1]), 'attr') ?>#historial">‹</a><?php endif; ?>
            <span>Página <?= $page ?> de <?= $pages ?></span>
            <?php if ($page < $pages): ?><a class="ce-btn" style="height:30px" href="<?= esc(site_url('admin/email-logs') . $qs(['page' => $page + 1]), 'attr') ?>#historial">›</a><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: el correo enviado -->
<div class="ce-modal" id="ceModal" role="dialog" aria-modal="true" aria-labelledby="ceModalTitle">
    <div class="ce-modal__box">
        <div class="ce-modal__head"><h3 id="ceModalTitle">Cargando…</h3><button type="button" class="ce-modal__close" data-cerrar aria-label="Cerrar">✕</button></div>
        <div class="ce-modal__meta" id="ceModalMeta"></div>
        <div id="ceModalErr"></div>
        <div class="ce-modal__body" id="ceModalBody"></div>
    </div>
</div>
<script>
    (function () {
        const modal = document.getElementById('ceModal');
        if (!modal) return;
        // Fuera del contenido que cambia htmx, para que no quede debajo de otras capas
        if (modal.parentNode !== document.body) {
            const viejo = document.body.querySelector(':scope > #ceModal');
            if (viejo && viejo !== modal) viejo.remove();
            document.body.appendChild(modal);
        }
        const $ = (id) => document.getElementById(id);
        const esc = (t) => String(t == null ? '' : t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const cerrar = function () { modal.classList.remove('open'); $('ceModalBody').innerHTML = ''; document.body.style.overflow = ''; };
        const abrir = async function (url) {
            $('ceModalTitle').textContent = 'Cargando…';
            $('ceModalMeta').innerHTML = '';
            $('ceModalErr').innerHTML = '';
            $('ceModalBody').innerHTML = '<div class="ce-modal__msg">Cargando el correo…</div>';
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
            let d;
            try {
                const r = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                d = await r.json();
                if (!r.ok) throw new Error(d && d.error ? d.error : 'Error ' + r.status);
            } catch (e) {
                $('ceModalTitle').textContent = 'No se ha podido abrir';
                $('ceModalBody').innerHTML = '<div class="ce-modal__msg">' + esc(e.message) + '</div>';
                return;
            }
            $('ceModalTitle').textContent = d.asunto || '(sin asunto)';
            const si = (v) => v ? '✓ ' + esc(v) : '<span style="color:#94a3b8">No</span>';
            $('ceModalMeta').innerHTML =
                '<div><span>Para</span><strong>' + esc(d.para || '–') + (d.nombre ? '<br><small style="color:#64748b">' + esc(d.nombre) + '</small>' : '') + '</strong></div>' +
                '<div><span>Enviado</span><strong>' + esc(d.fecha) + '</strong></div>' +
                '<div><span>Correo</span><strong>' + esc(d.correo) + (d.slug ? '<br><small style="color:#94a3b8;font-family:ui-monospace,Consolas,monospace">' + esc(d.slug) + '</small>' : '') + '</strong></div>' +
                '<div><span>Grupo · estado</span><strong>' + esc(d.grupo) + ' · ' + (d.estado === 'success' ? '<span style="color:#0a7a0a">Enviado</span>' : '<span style="color:#b42f2f">Fallido</span>') + '</strong></div>' +
                '<div><span>Abierto</span><strong>' + si(d.abierto) + '</strong></div>' +
                '<div><span>Clic</span><strong>' + si(d.clic) + '</strong></div>' +
                '<div><span>Login después</span><strong>' + si(d.login) + '</strong></div>';
            if (d.error) $('ceModalErr').innerHTML = '<div class="ce-modal__err" style="margin-top:12px">' + esc(d.error) + '</div>';
            const body = $('ceModalBody');
            if (d.vacio) {
                body.innerHTML = '<div class="ce-modal__msg">Este envío no guardó el contenido del correo.</div>';
            } else if (d.recortado) {
                body.innerHTML = '<div class="ce-modal__msg"><div class="aviso"><strong>Este correo no se puede ver entero.</strong> Hasta el 08/10/2026 solo se guardaban los 1.000 primeros caracteres de cada correo, y en un correo HTML eso es solo la cabecera técnica (estilos, título), sin el texto que leyó el usuario. Los correos enviados desde ahora se guardan completos.</div>' +
                    (d.texto ? '<div style="margin-bottom:6px;font-weight:700;color:#0f172a">Lo que quedó guardado (texto):</div><div class="resto">' + esc(d.texto) + '</div>' : '') +
                    (d.slug ? '<p style="margin-top:14px">Para ver cómo es este correo hoy, abre su plantilla en <a href="<?= site_url('admin/email-templates') ?>">Plantillas de email</a>.</p>' : '') + '</div>';
            } else {
                const f = document.createElement('iframe');
                // Sin allow-scripts: el correo no puede ejecutar nada. allow-popups: sus enlaces se abren en otra pestana.
                f.setAttribute('sandbox', 'allow-popups allow-popups-to-escape-sandbox');
                f.setAttribute('title', 'Contenido del correo');
                f.srcdoc = d.html;
                body.innerHTML = '';
                body.appendChild(f);
            }
        };
        window.__ceModal = { abrir: abrir, cerrar: cerrar };
        if (!window.__ceModalGlobal) {
            window.__ceModalGlobal = true;
            document.addEventListener('click', function (e) {
                const b = e.target.closest && e.target.closest('.ce-ver');
                if (b) { e.preventDefault(); window.__ceModal.abrir(b.getAttribute('data-url')); return; }
                const m = document.getElementById('ceModal');
                if (m && m.classList.contains('open') && (e.target === m || (e.target.closest && e.target.closest('[data-cerrar]')))) window.__ceModal.cerrar();
            });
            document.addEventListener('keydown', function (e) {
                const m = document.getElementById('ceModal');
                if (e.key === 'Escape' && m && m.classList.contains('open')) window.__ceModal.cerrar();
            });
        }
    })();
</script>

<?= view('admin/partials/panel_js') ?>
</div>
<?= $this->endSection() ?>
