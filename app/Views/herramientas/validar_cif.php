<?php
/**
 * /validar-cif — Validador gratuito de CIF (12-10-2026).
 *
 * Búsqueda propia ("validar CIF online", "comprobar CIF", "validador de CIF"), distinta
 * de la home ("API de empresas"). Dos niveles:
 *  1. Formato y dígito de control, en el navegador (también varios CIF a la vez).
 *  2. Si la empresa existe y su estado, con el mismo POST /search que usa el buscador
 *     de la home (token CSRF fresco de /csrftoken, porque la página puede ir cacheada).
 * Lleva a la API con "API de empresas" (home) y al registro con source=validar_cif.
 */
$faqs = [
    ['¿Cómo sé si un CIF es válido?',
     'Un CIF tiene 9 caracteres: una letra que indica la forma jurídica, 7 números y un carácter de control (número o letra) que se calcula a partir de los 7 números. Si el control no cuadra, el CIF está mal escrito. Este validador hace ese cálculo y, si el formato es correcto, comprueba además si la empresa consta en el Registro Mercantil.'],
    ['¿Un CIF válido significa que la empresa existe?',
     'No. Que el dígito de control cuadre solo indica que el número está bien formado. Para saber si la empresa existe y si sigue activa hay que consultarlo en una fuente de datos, como hace el segundo paso de esta herramienta.'],
    ['¿Qué diferencia hay entre CIF y NIF?',
     'Desde 2008 el CIF se llama oficialmente NIF (número de identificación fiscal) también para las empresas, aunque se sigue usando "CIF" para las sociedades. El NIF de una persona física es su DNI con letra, o el NIE si es extranjera.'],
    ['¿Qué significa la primera letra del CIF?',
     'La forma jurídica: A para sociedades anónimas, B para sociedades limitadas, F para cooperativas, G para asociaciones, U para uniones temporales de empresas, etc. La tabla de esta página recoge todas.'],
    ['¿Cómo saber si una empresa está activa?',
     'Con el CIF, consulta su estado. APIEmpresas lo calcula a partir de lo publicado en el BORME (constitución, disolución, extinción, concurso…). Es una referencia, no un certificado del Registro Mercantil.'],
    ['¿Puedo validar muchos CIF a la vez?',
     'El formato, sí: pega la lista en el validador múltiple de esta página (hasta 500). Para comprobar a la vez si existen y están activas, la API permite consultar hasta 100 CIF por petición.'],
    ['¿Puedo validar CIF desde mi web, Excel o ERP?',
     'Sí, con la API de empresas: una llamada por CIF devuelve la razón social, el estado y el resto de datos en JSON. Tiene un plan gratis de 100 consultas, sin tarjeta.'],
];

$letras = [
    ['A', 'Sociedad anónima'],
    ['B', 'Sociedad de responsabilidad limitada'],
    ['C', 'Sociedad colectiva'],
    ['D', 'Sociedad comanditaria'],
    ['E', 'Comunidad de bienes, herencia yacente y otras entidades sin personalidad jurídica'],
    ['F', 'Sociedad cooperativa'],
    ['G', 'Asociación'],
    ['H', 'Comunidad de propietarios en régimen de propiedad horizontal'],
    ['J', 'Sociedad civil, con o sin personalidad jurídica'],
    ['N', 'Entidad extranjera'],
    ['P', 'Corporación local'],
    ['Q', 'Organismo público'],
    ['R', 'Congregación o institución religiosa'],
    ['S', 'Órgano de la Administración del Estado y de las comunidades autónomas'],
    ['U', 'Unión temporal de empresas'],
    ['V', 'Otros tipos no definidos'],
    ['W', 'Establecimiento permanente de entidad no residente en España'],
];

$ldFaq = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(static fn ($f) => [
        '@type' => 'Question',
        'name'  => $f[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
    ], $faqs),
];
$ldApp = [
    '@context'            => 'https://schema.org',
    '@type'               => 'WebApplication',
    'name'                => 'Validador de CIF de APIEmpresas',
    'url'                 => site_url('validar-cif'),
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem'     => 'Web',
    'offers'              => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
];
$registro = site_url('register?intent=api&plan=free&source=validar_cif');
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Validar CIF online gratis: comprueba si un CIF es válido | APIEmpresas',
        'excerptText' => 'Comprueba gratis si un CIF de empresa es válido (dígito de control y letra), qué forma jurídica indica y si la empresa existe y está activa. Sin registro.',
        'canonical'   => site_url('validar-cif'),
    ]) ?>
    <script type="application/ld+json"><?= json_encode($ldApp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script type="application/ld+json"><?= json_encode($ldFaq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <style>
        .vc-wrap { background: #f8fafc; padding: 56px 16px 40px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .vc { max-width: 880px; margin: 0 auto; color: #0f172a; }
        .vc h1 { font-size: clamp(2rem, 5vw, 3rem); font-weight: 900; letter-spacing: -0.03em; line-height: 1.1; margin: 0 0 14px; text-align: center; }
        .vc h1 span { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .vc .vc-sub { font-size: 1.15rem; color: #475569; text-align: center; max-width: 680px; margin: 0 auto 32px; line-height: 1.6; }
        .vc-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08); margin-bottom: 28px; }
        .vc-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .vc-form input { flex: 1; min-width: 200px; font-size: 1.25rem; padding: 14px 16px; border: 2px solid #cbd5e1; border-radius: 12px; font-family: 'JetBrains Mono', 'Fira Code', monospace; text-transform: uppercase; }
        .vc-form input:focus { outline: none; border-color: #2563eb; }
        .vc-btn { background: #2563eb; color: #fff; border: 0; border-radius: 12px; padding: 14px 26px; font-weight: 800; font-size: 1rem; cursor: pointer; }
        .vc-btn:hover { background: #1d4ed8; }
        .vc-btn[disabled] { opacity: .6; cursor: default; }
        .vc-res { margin-top: 20px; display: none; }
        .vc-row { display: flex; gap: 10px; align-items: flex-start; padding: 12px 0; border-top: 1px solid #f1f5f9; line-height: 1.5; }
        .vc-row:first-child { border-top: 0; }
        .vc-ico { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: .85rem; }
        .vc-ok .vc-ico { background: #dcfce7; color: #15803d; }
        .vc-ko .vc-ico { background: #fee2e2; color: #b91c1c; }
        .vc-info .vc-ico { background: #e0e7ff; color: #3730a3; }
        .vc-row strong { display: block; }
        .vc-row small { color: #64748b; }
        .vc-empresa { margin-top: 8px; padding: 16px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .vc-empresa a { color: #2563eb; font-weight: 700; text-decoration: none; }
        .vc-api { margin-top: 18px; padding: 16px 18px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; font-size: .95rem; line-height: 1.5; }
        .vc-api a { color: #1d4ed8; font-weight: 800; }
        .vc h2 { font-size: 1.6rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 14px; }
        .vc p, .vc li { color: #334155; line-height: 1.65; }
        .vc textarea { width: 100%; min-height: 140px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: .95rem; padding: 12px; border: 2px solid #cbd5e1; border-radius: 12px; box-sizing: border-box; }
        .vc table { width: 100%; border-collapse: collapse; font-size: .95rem; }
        .vc th, .vc td { text-align: left; padding: 9px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .vc th { background: #f1f5f9; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .vc td code, .vc code { font-family: 'JetBrains Mono', 'Fira Code', monospace; background: #f1f5f9; padding: 1px 6px; border-radius: 5px; }
        .vc-lote td:first-child { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
        .vc details { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .vc summary { cursor: pointer; font-weight: 700; font-size: 1.02rem; }
        .vc details p { margin: 10px 0 0; }
        .vc-cta { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 20px; padding: 36px 28px; text-align: center; margin-bottom: 28px; }
        .vc-cta h2 { color: #fff; }
        .vc-cta p { color: #cbd5e1; max-width: 600px; margin: 0 auto 22px; }
        .vc-cta a.vc-btn { display: inline-block; text-decoration: none; }
    </style>
</head>
<body>
<?= view('partials/header') ?>

<main class="vc-wrap">
<div class="vc">

    <h1>Validar CIF <span>online y gratis</span></h1>
    <p class="vc-sub">Comprueba si un CIF de empresa está bien formado, qué tipo de entidad indica y si la empresa existe y sigue activa. Sin registro.</p>

    <!-- 1. Validador -->
    <section class="vc-card" aria-labelledby="vc-titulo">
        <h2 id="vc-titulo" style="font-size:1.2rem; margin-bottom:12px;">Introduce el CIF</h2>
        <form class="vc-form" id="vc-form" autocomplete="off">
            <label for="vc-cif" class="sr-only" style="position:absolute;left:-9999px;">CIF o NIF</label>
            <input id="vc-cif" name="cif" type="text" inputmode="text" maxlength="15" placeholder="Ej.: A46103834" required>
            <button class="vc-btn" type="submit" id="vc-go">Validar CIF</button>
        </form>
        <div class="vc-res" id="vc-res" aria-live="polite"></div>
    </section>

    <!-- 2. Varios a la vez (solo formato, en el navegador) -->
    <section class="vc-card">
        <h2>Validar varios CIF a la vez</h2>
        <p style="margin-top:0;">Pega una lista (uno por línea, o separados por comas). Se comprueba el formato y el dígito de control de cada uno, sin enviarlos a ningún sitio. Hasta 500. Si tu lista está en una hoja de cálculo, usa la <a href="<?= site_url('validar-cif-excel') ?>">fórmula para validar CIF en Excel y Google Sheets</a>.</p>
        <textarea id="vc-lista" placeholder="A46103834&#10;B12345678&#10;..."></textarea>
        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <button class="vc-btn" type="button" id="vc-lote-go">Validar lista</button>
            <span id="vc-lote-resumen" style="color:#475569; font-weight:600;"></span>
        </div>
        <div id="vc-lote" style="margin-top:14px; max-height:360px; overflow:auto;"></div>
    </section>

    <!-- 3. Contenido -->
    <section class="vc-card">
        <h2>Cómo se valida un CIF</h2>
        <p>El CIF (hoy NIF de persona jurídica) tiene 9 caracteres:</p>
        <ul>
            <li><strong>Una letra</strong> que indica la forma jurídica (tabla de abajo).</li>
            <li><strong>Siete números</strong>. Los dos primeros solían indicar la provincia, pero en los CIF recientes ya no es fiable.</li>
            <li><strong>Un carácter de control</strong>, número o letra, que se calcula con los siete números.</li>
        </ul>
        <p>El cálculo del control:</p>
        <ol>
            <li>Suma los números en posición par (2.º, 4.º y 6.º).</li>
            <li>Multiplica por 2 cada número en posición impar (1.º, 3.º, 5.º y 7.º) y suma las cifras de cada resultado (por ejemplo, 7 × 2 = 14 → 1 + 4 = 5).</li>
            <li>Suma los dos totales y quédate con la última cifra. Si es 0, el control es 0; si no, es 10 menos esa cifra.</li>
            <li>Si el control va en letra, es la que ocupa esa posición en <code>JABCDEFGHI</code> (0 = J, 1 = A, 2 = B…).</li>
        </ol>
        <p>Las sociedades anónimas y limitadas (A, B) y las comunidades (E, H) llevan control numérico; las entidades públicas, extranjeras y religiosas (N, P, Q, R, S, W), letra; el resto puede llevar cualquiera de los dos.</p>
        <p>Un CIF bien formado no garantiza que la empresa exista: para eso hay que consultarlo, como hace este validador o la <a href="<?= site_url() ?>">API de empresas</a> desde tu software. Si quieres saber además si sigue activa y quién la administra, mira <a href="<?= site_url('como-saber-si-una-empresa-esta-activa') ?>">cómo saber si una empresa está activa</a>. Si lo compruebas para facturar, mira <a href="<?= site_url('validar-nif-clientes-facturacion') ?>">qué datos del cliente exige la factura y qué comprueba Verifactu</a>.</p>
    </section>

    <section class="vc-card">
        <h2>Qué significa la letra del CIF</h2>
        <table>
            <thead><tr><th>Letra</th><th>Tipo de entidad</th></tr></thead>
            <tbody>
            <?php foreach ($letras as [$l, $d]): ?>
                <tr><td><code><?= $l ?></code></td><td><?= esc($d) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="vc-cta">
        <h2>Valida CIF desde tu web, Excel o ERP</h2>
        <p>Con la API, una llamada por CIF te devuelve si la empresa existe, su estado, razón social, CNAE y provincia en JSON. 100 consultas gratis, sin tarjeta.</p>
        <a class="vc-btn" href="<?= $registro ?>" data-track-click="cif_tool" data-track-element="cta_api">Obtener API key gratis</a>
    </section>

    <section class="vc-card">
        <h2>Preguntas frecuentes sobre el CIF</h2>
        <?php foreach ($faqs as [$q, $a]): ?>
            <details><summary><?= esc($q) ?></summary><p><?= esc($a) ?></p></details>
        <?php endforeach; ?>
    </section>

</div>
</main>

<script>
(function () {
    'use strict';
    var LETRAS = <?= json_encode(array_column($letras, 1, 0), JSON_UNESCAPED_UNICODE) ?>;
    var SEARCH = <?= json_encode(site_url('search')) ?>;
    var CSRF   = <?= json_encode(site_url('csrftoken')) ?>;
    var BASE   = <?= json_encode(rtrim(site_url(), '/') . '/') ?>;
    var REG    = <?= json_encode($registro) ?>;

    function limpiar(v) { return String(v || '').toUpperCase().replace(/[\s.\-\/]/g, ''); }

    /** Analiza un CIF/NIF. Devuelve {tipo, valido, motivo, esperado}. */
    function analizar(v) {
        var c = limpiar(v);
        if (!c) return { tipo: 'vacio', valido: false, motivo: 'Escribe un CIF.' };
        // Persona física: DNI (8 números + letra), NIE (X/Y/Z) o NIF especial (K/L/M)
        var mPersona = c.match(/^()(\d{8})([A-Z])$/) || c.match(/^([XYZ])(\d{7})([A-Z])$/) || c.match(/^([KLM])(\d{7})([A-Z])$/);
        if (mPersona) {
            var num = mPersona[1] === 'X' ? '0' : mPersona[1] === 'Y' ? '1' : mPersona[1] === 'Z' ? '2' : '';
            var n = parseInt((/[KLM]/.test(mPersona[1]) ? '' : num) + mPersona[2], 10);
            var letra = 'TRWAGMYFPDXBNJZSQVHLCKE'.charAt(n % 23);
            var ok = letra === mPersona[3];
            return { tipo: 'persona', valido: ok, esperado: letra,
                motivo: ok ? 'Es un NIF de persona física (DNI o NIE) bien formado.'
                           : 'Parece un NIF de persona física, pero la letra no cuadra: debería ser ' + letra + '.' };
        }
        if (!/^[A-Z]\d{7}[0-9A-Z]$/.test(c)) {
            return { tipo: 'formato', valido: false, motivo: 'Un CIF tiene 9 caracteres: una letra, 7 números y un número o letra de control.' };
        }
        var l = c.charAt(0);
        if (!LETRAS[l]) return { tipo: 'formato', valido: false, motivo: 'La letra "' + l + '" no corresponde a ningún tipo de entidad.' };
        var d = c.substr(1, 7), suma = 0;
        for (var i = 0; i < 7; i++) {
            var x = parseInt(d.charAt(i), 10);
            if (i % 2 === 0) { x = x * 2; x = x > 9 ? x - 9 : x; }
            suma += x;
        }
        var ctrl = (10 - (suma % 10)) % 10;
        var ctrlL = 'JABCDEFGHI'.charAt(ctrl);
        var dado = c.charAt(8);
        var soloNum = 'ABEH'.indexOf(l) >= 0, soloLetra = 'NPQRSW'.indexOf(l) >= 0;
        var ok2 = soloNum ? dado === String(ctrl) : soloLetra ? dado === ctrlL : (dado === String(ctrl) || dado === ctrlL);
        var esperado = soloNum ? String(ctrl) : soloLetra ? ctrlL : (String(ctrl) + ' o ' + ctrlL);
        return { tipo: 'cif', valido: ok2, letra: l, entidad: LETRAS[l], cif: c, esperado: esperado,
            motivo: ok2 ? 'El dígito de control es correcto.' : 'El dígito de control no cuadra: debería ser ' + esperado + '.' };
    }

    function fila(cls, ico, titulo, texto) {
        return '<div class="vc-row ' + cls + '"><span class="vc-ico">' + ico + '</span><div><strong>' + titulo + '</strong>' + (texto ? '<small>' + texto + '</small>' : '') + '</div></div>';
    }
    function esc(t) { var e = document.createElement('div'); e.textContent = t == null ? '' : String(t); return e.innerHTML; }
    function track(datos) { try { if (window.trackEvent) window.trackEvent('cif_tool_check', datos); } catch (e) {} }

    var form = document.getElementById('vc-form'), input = document.getElementById('vc-cif'),
        res = document.getElementById('vc-res'), btn = document.getElementById('vc-go');

    function consultar(cif) {
        return fetch(CSRF, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (t) {
                var body = new URLSearchParams();
                body.set('q', cif);
                body.set(t.name, t.hash);
                return fetch(SEARCH, { method: 'POST', credentials: 'same-origin', body: body,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var a = analizar(input.value);
        var html = '';
        if (a.tipo === 'persona') {
            html = fila(a.valido ? 'vc-ok' : 'vc-ko', a.valido ? '✓' : '✕', a.valido ? 'NIF de persona física válido' : 'NIF de persona física incorrecto', a.motivo)
                 + fila('vc-info', 'i', 'No es una empresa', 'Los autónomos y particulares no figuran en el Registro Mercantil, así que no se puede consultar su estado aquí.');
            res.innerHTML = html; res.style.display = 'block'; track({ tipo: 'persona', valido: a.valido }); return;
        }
        if (!a.valido) {
            res.innerHTML = fila('vc-ko', '✕', 'CIF no válido', a.motivo); res.style.display = 'block';
            track({ tipo: a.tipo, valido: false }); return;
        }
        html = fila('vc-ok', '✓', 'CIF válido: ' + esc(a.cif), a.motivo)
             + fila('vc-info', a.letra, 'Tipo de entidad', esc(a.entidad));
        res.innerHTML = html + '<div id="vc-emp">' + fila('vc-info', '…', 'Comprobando si la empresa existe…', '') + '</div>';
        res.style.display = 'block';
        btn.disabled = true;
        consultar(a.cif).then(function (r) {
            var box = document.getElementById('vc-emp');
            if (r.status === 200 && r.json && r.json.success && r.json.data) {
                var e = r.json.data, cifE = e.cif || e.nif || a.cif;
                if (limpiar(cifE) !== a.cif) {
                    box.innerHTML = fila('vc-info', '?', 'No consta ninguna empresa con este CIF', 'Puede ser una entidad que no publica en el BORME (asociaciones, comunidades de bienes, organismos públicos…) o un CIF que no existe.');
                    track({ tipo: 'cif', valido: true, existe: false }); return;
                }
                var estado = String(e.status || '').trim();
                var activa = /^activa$/i.test(estado) || e.status_code === 'ACTIVE';
                var clase = activa ? 'vc-ok' : (estado ? 'vc-ko' : 'vc-info'), icono = activa ? '✓' : (estado ? '!' : 'i');
                box.innerHTML = fila(clase, icono, 'La empresa existe' + (estado ? ' · estado: ' + esc(estado) : ' · sin estado calculado'),
                        estado ? 'Estado calculado por APIEmpresas a partir del BORME; no es un certificado del Registro Mercantil.'
                               : 'No hay publicaciones en el BORME de las que deducir su estado (es habitual en asociaciones y otras entidades que no son sociedades).')
                    + '<div class="vc-empresa"><strong>' + esc(e.name || '') + '</strong><br>'
                    + [e.province, e.cnae_label, e.founded ? 'constituida el ' + e.founded : ''].filter(Boolean).map(esc).join(' · ')
                    + '<br><a href="' + BASE + encodeURIComponent(cifE) + '">Ver la ficha completa de la empresa →</a></div>'
                    + '<div class="vc-api">¿Necesitas hacer esta comprobación en tu formulario, ERP o Excel? La <a href="' + BASE + '">API de empresas</a> la devuelve en JSON con una llamada. <a href="' + REG + '">100 consultas gratis</a>.</div>';
                track({ tipo: 'cif', valido: true, existe: true, activa: activa });
            } else if (r.status === 404) {
                box.innerHTML = fila('vc-info', '?', 'No consta ninguna empresa con este CIF', 'Puede ser una entidad que no publica en el BORME (asociaciones, comunidades de bienes, organismos públicos…) o un CIF que no existe.');
                track({ tipo: 'cif', valido: true, existe: false });
            } else if (r.status === 429) {
                box.innerHTML = fila('vc-info', '!', 'Demasiadas consultas seguidas', 'Espera un minuto y vuelve a probar.');
            } else {
                box.innerHTML = fila('vc-info', '!', 'No se ha podido comprobar si existe', 'Inténtalo de nuevo en un momento.');
            }
        }).catch(function () {
            var box = document.getElementById('vc-emp');
            if (box) box.innerHTML = fila('vc-info', '!', 'No se ha podido comprobar si existe', 'Inténtalo de nuevo en un momento.');
        }).then(function () { btn.disabled = false; });
    });

    document.getElementById('vc-lote-go').addEventListener('click', function () {
        var lista = document.getElementById('vc-lista').value.split(/[\n,;\t]+/).map(limpiar).filter(Boolean).slice(0, 500);
        var ok = 0, filas = lista.map(function (c) {
            var a = analizar(c);
            if (a.valido) ok++;
            var tipo = a.tipo === 'cif' ? esc(a.entidad) : a.tipo === 'persona' ? 'Persona física' : '—';
            return '<tr><td>' + esc(c) + '</td><td style="color:' + (a.valido ? '#15803d' : '#b91c1c') + '; font-weight:700;">' + (a.valido ? 'Válido' : 'No válido') + '</td><td>' + tipo + '</td><td>' + esc(a.valido ? '' : a.motivo) + '</td></tr>';
        });
        document.getElementById('vc-lote-resumen').textContent = lista.length ? ok + ' válidos de ' + lista.length : '';
        document.getElementById('vc-lote').innerHTML = lista.length
            ? '<table class="vc-lote"><thead><tr><th>CIF</th><th>Resultado</th><th>Tipo</th><th>Motivo</th></tr></thead><tbody>' + filas.join('') + '</tbody></table>'
              + '<div class="vc-api">Para saber además si existen y están activas, la <a href="' + BASE + '">API de empresas</a> consulta hasta 100 CIF por petición. <a href="' + REG + '">Prueba gratis</a>.</div>'
            : '';
        track({ tipo: 'lote', total: lista.length, validos: ok });
    });
})();
</script>

<?= view('partials/footer') ?>
</body>
</html>
