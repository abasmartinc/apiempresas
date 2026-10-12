<?php
/**
 * /como-saber-si-una-empresa-esta-activa — Guía + buscador (12-10-2026).
 *
 * Búsquedas: "cómo saber si una empresa está activa", "cómo saber si una empresa existe",
 * "quién es el administrador de una empresa". No compite con la home ("API de empresas")
 * ni con /api-empresas (KYB): enlaza a las dos y lleva al registro con source=guia_empresa_activa.
 * El buscador usa el mismo POST /search que la home y /validar-cif (CIF exacto o mejor
 * coincidencia por nombre), con token CSRF fresco de /csrftoken porque la página puede ir cacheada.
 */
$faqs = [
    ['¿Cómo saber si una empresa está activa gratis?',
     'Busca su CIF o su nombre en el buscador de esta página: te dice si la empresa consta, su estado calculado a partir del BORME y te lleva a su ficha con los administradores y los actos publicados. También puedes leer sus anuncios en el BORME, en boe.es, que es gratuito. La nota simple del Registro Mercantil es de pago.'],
    ['¿Cómo saber si una empresa existe con el CIF?',
     'Comprueba primero que el CIF está bien formado (el validador de CIF calcula el dígito de control) y después búscalo: si consta una sociedad con ese CIF, verás su razón social y su estado. Si no consta, puede ser un CIF inventado o mal escrito, o una entidad que no se inscribe en el Registro Mercantil, como una asociación o una comunidad de bienes.'],
    ['¿Cómo saber quién es el administrador de una empresa?',
     'En la ficha de la empresa, en el apartado de administradores y cargos, que se construye con los nombramientos y ceses publicados en el BORME. Si necesitas el dato con valor oficial, pide una nota simple o una certificación al Registro Mercantil.'],
    ['¿Qué significa que una empresa tenga la hoja registral cerrada?',
     'Que el Registro Mercantil no le inscribe nuevos actos, salvo excepciones, hasta que corrija la causa. Las más habituales son no depositar las cuentas anuales, la baja provisional en el Índice de Entidades de Hacienda y la revocación del NIF. La sociedad no desaparece, pero es una señal de alerta.'],
    ['¿Una empresa disuelta sigue existiendo?',
     'Sí, hasta que se liquida y se inscribe su extinción. La disolución abre la liquidación: la sociedad sigue existiendo para vender sus bienes, cobrar y pagar deudas, y la representan los liquidadores. Cuando termina la liquidación y se inscribe la extinción, deja de existir.'],
    ['¿Cómo saber si una empresa está en concurso de acreedores?',
     'Las declaraciones de concurso se publican en el BOE y en el Registro Público Concursal (publicidadconcursal.es), y se anotan en el Registro Mercantil. En la ficha de APIEmpresas el estado aparece como "en concurso" cuando consta en el BORME.'],
    ['¿La información de esta página es oficial?',
     'Los datos proceden del BORME, que es oficial, y se actualizan con cada boletín. El estado lo calcula APIEmpresas a partir de esas publicaciones, así que es una referencia, no un certificado. Para un trámite que exija prueba, pide una certificación al Registro Mercantil.'],
    ['¿Puedo saber si un autónomo está dado de alta?',
     'No con estas fuentes. Los autónomos no se inscriben en el Registro Mercantil (salvo excepciones) y su alta en Hacienda o en la Seguridad Social no es pública. Puedes pedirle un certificado de situación censal, o comprobar su NIF-IVA en VIES si opera con otros países de la UE.'],
];

$formas = [
    ['Buscador de APIEmpresas (esta página)', 'Gratis', 'Si consta, estado calculado, administradores vigentes e historial del BORME en la ficha.', 'Es una referencia calculada, no un certificado.'],
    ['BORME (boe.es)', 'Gratis', 'Los anuncios publicados: constitución, nombramientos y ceses, disolución, concurso, extinción.', 'Hay que leer los anuncios uno a uno y deducir la situación actual.'],
    ['Nota simple del Registro Mercantil (registradores.org)', 'De pago', 'Datos vigentes según el Registro: administradores, domicilio, capital, situación y cierres de hoja.', 'Es informativa; no sirve como prueba.'],
    ['Certificación del Registro Mercantil', 'De pago', 'Lo mismo que la nota simple, con valor de prueba.', 'Más cara y lenta; solo si un trámite la exige.'],
    ['Hacienda (sede de la AEAT) y VIES', 'Gratis', 'Si un NIF está identificado (con certificado o Cl@ve) y si un NIF-IVA está dado de alta para operaciones dentro de la UE (VIES).', 'No dicen si la sociedad está disuelta, en concurso o quién la administra.'],
];

$estados = [
    ['Activa', 'Consta inscrita y no hay publicado ningún acto que la cierre. Es lo que quieres ver en un cliente o proveedor.'],
    ['Sin estado claro / presunta activa', 'Hay datos de la sociedad pero ningún acto reciente en el BORME que confirme o cierre su actividad. Mira cuándo publicó por última vez y si deposita cuentas.'],
    ['En concurso', 'Un juez ha declarado el concurso de acreedores. Puede seguir operando, pero con limitaciones y con un administrador concursal. Riesgo alto de impago.'],
    ['Disuelta o en liquidación', 'Ha acordado disolverse. Sigue existiendo mientras se liquida, representada por los liquidadores, pero ya no va a seguir con su actividad.'],
    ['Hoja registral cerrada', 'El Registro no le inscribe actos nuevos hasta que corrija la causa: no depositar cuentas anuales, baja provisional en el Índice de Entidades de Hacienda o NIF revocado. Señal de alerta.'],
    ['Absorbida o fusionada', 'Se ha integrado en otra sociedad, que es la que sigue con su actividad y sus contratos.'],
    ['Extinguida', 'Se ha inscrito su extinción: ya no existe y no puede contratar ni facturar.'],
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
$ldArticle = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => 'Cómo saber si una empresa está activa y quién es su administrador',
    'datePublished' => '2026-10-12',
    'dateModified'  => '2026-10-12',
    'inLanguage'    => 'es-ES',
    'mainEntityOfPage' => site_url('como-saber-si-una-empresa-esta-activa'),
    'author'        => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
    'publisher'     => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
];
$registro = site_url('register?intent=api&plan=free&source=guia_empresa_activa');
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Cómo saber si una empresa está activa y quién es su administrador | APIEmpresas',
        'excerptText' => 'Comprueba gratis si una empresa existe, si sigue activa o está disuelta, en concurso o extinguida, y quién la administra. Buscador por CIF o nombre y guía para interpretar el BORME.',
        'canonical'   => site_url('como-saber-si-una-empresa-esta-activa'),
    ]) ?>
    <script type="application/ld+json"><?= json_encode($ldArticle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <script type="application/ld+json"><?= json_encode($ldFaq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <style>
        .ea-wrap { background: #f8fafc; padding: 56px 16px 40px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .ea { max-width: 880px; margin: 0 auto; color: #0f172a; }
        .ea h1 { font-size: clamp(1.9rem, 4.6vw, 2.8rem); font-weight: 900; letter-spacing: -0.03em; line-height: 1.12; margin: 0 0 14px; text-align: center; }
        .ea h1 span { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .ea-sub { font-size: 1.12rem; color: #475569; text-align: center; max-width: 690px; margin: 0 auto 30px; line-height: 1.6; }
        .ea-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08); margin-bottom: 28px; }
        .ea-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .ea-form input { flex: 1; min-width: 200px; font-size: 1.15rem; padding: 14px 16px; border: 2px solid #cbd5e1; border-radius: 12px; }
        .ea-form input:focus { outline: none; border-color: #2563eb; }
        .ea-btn { background: #2563eb; color: #fff; border: 0; border-radius: 12px; padding: 14px 26px; font-weight: 800; font-size: 1rem; cursor: pointer; }
        .ea-btn:hover { background: #1d4ed8; }
        .ea-btn[disabled] { opacity: .6; cursor: default; }
        .ea-res { margin-top: 20px; display: none; }
        .ea-row { display: flex; gap: 10px; align-items: flex-start; padding: 12px 0; border-top: 1px solid #f1f5f9; line-height: 1.5; }
        .ea-row:first-child { border-top: 0; }
        .ea-ico { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: .85rem; }
        .ea-ok .ea-ico { background: #dcfce7; color: #15803d; }
        .ea-ko .ea-ico { background: #fee2e2; color: #b91c1c; }
        .ea-warn .ea-ico { background: #fef3c7; color: #b45309; }
        .ea-info .ea-ico { background: #e0e7ff; color: #3730a3; }
        .ea-row strong { display: block; }
        .ea-row small { color: #64748b; }
        .ea-empresa { margin-top: 8px; padding: 16px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; line-height: 1.6; }
        .ea-empresa a { color: #2563eb; font-weight: 700; text-decoration: none; }
        .ea-api { margin-top: 18px; padding: 16px 18px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; font-size: .95rem; line-height: 1.5; }
        .ea-api a { color: #1d4ed8; font-weight: 800; }
        .ea-resumen { border-left: 4px solid #2563eb; }
        .ea h2 { font-size: 1.55rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 14px; }
        .ea h3 { font-size: 1.1rem; font-weight: 800; margin: 22px 0 8px; }
        .ea p, .ea li { color: #334155; line-height: 1.65; }
        .ea a { color: #2563eb; }
        .ea table { width: 100%; border-collapse: collapse; font-size: .93rem; }
        .ea th, .ea td { text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .ea th { background: #f1f5f9; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .ea td:first-child { font-weight: 700; color: #0f172a; }
        .ea-tag { display: inline-block; font-size: .75rem; font-weight: 800; padding: 3px 9px; border-radius: 99px; white-space: nowrap; }
        .ea-tag--free { background: #dcfce7; color: #166534; }
        .ea-tag--pago { background: #fef3c7; color: #92400e; }
        .ea code { font-family: 'JetBrains Mono', 'Fira Code', monospace; background: #f1f5f9; padding: 1px 6px; border-radius: 5px; font-size: .9em; }
        .ea details { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .ea summary { cursor: pointer; font-weight: 700; font-size: 1.02rem; }
        .ea details p { margin: 10px 0 0; }
        .ea-cta { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 20px; padding: 36px 28px; text-align: center; margin-bottom: 28px; }
        .ea-cta h2 { color: #fff; }
        .ea-cta p { color: #cbd5e1; max-width: 620px; margin: 0 auto 22px; }
        .ea-cta a.ea-btn { display: inline-block; text-decoration: none; }
        .ea-cta .ea-cta-links { margin-top: 16px; font-size: .92rem; }
        .ea-cta .ea-cta-links a { color: #93c5fd; font-weight: 700; }
        .ea-fuentes { font-size: .88rem; color: #64748b; }
        @media (max-width: 640px) {
            .ea-card { padding: 20px; }
            .ea-tabla thead { display: none; }
            .ea-tabla, .ea-tabla tbody, .ea-tabla tr, .ea-tabla td { display: block; width: auto; }
            .ea-tabla tr { padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
            .ea-tabla td { border: 0; padding: 2px 0; }
        }
    </style>
</head>
<body>
<?= view('partials/header') ?>

<main class="ea-wrap">
<div class="ea">

    <h1>Cómo saber si una empresa está activa <span>y quién la administra</span></h1>
    <p class="ea-sub">Busca la empresa por su CIF o su nombre y comprueba si existe, si sigue activa o está disuelta, en concurso o extinguida, y quiénes son sus administradores. Gratis y sin registro.</p>

    <!-- 1. Buscador -->
    <section class="ea-card" aria-labelledby="ea-titulo">
        <h2 id="ea-titulo" style="font-size:1.2rem; margin-bottom:12px;">Comprueba una empresa</h2>
        <form class="ea-form" id="ea-form" autocomplete="off">
            <label for="ea-q" style="position:absolute;left:-9999px;">CIF o nombre de la empresa</label>
            <input id="ea-q" name="q" type="text" maxlength="120" placeholder="CIF o nombre (ej.: A46103834 o Mercadona)" required>
            <button class="ea-btn" type="submit" id="ea-go">Comprobar</button>
        </form>
        <div class="ea-res" id="ea-res" aria-live="polite"></div>
    </section>

    <!-- 2. Respuesta corta -->
    <section class="ea-card ea-resumen">
        <h2>La respuesta corta</h2>
        <p>Para saber si una empresa está activa, busca su CIF o su razón social en una fuente del Registro Mercantil y fíjate en tres cosas:</p>
        <ol>
            <li><strong>Que consta</strong> una sociedad con ese CIF y ese nombre.</li>
            <li><strong>Que no tiene publicada</strong> su disolución, su extinción, un concurso de acreedores ni el cierre de su hoja registral.</li>
            <li><strong>Que sigue dando señales de vida</strong>: nombramientos, cambios o depósito de cuentas en los últimos años.</li>
        </ol>
        <p style="margin-bottom:0;">Las fuentes gratuitas son el BORME y buscadores como el de esta página. Si necesitas un documento con valor oficial, pide una nota simple o una certificación al Registro Mercantil.</p>
    </section>

    <!-- 3. Formas de comprobarlo -->
    <section class="ea-card">
        <h2>Dónde comprobar si una empresa existe y está activa</h2>
        <table class="ea-tabla">
            <thead><tr><th>Fuente</th><th>Coste</th><th>Qué te dice</th><th>Límite</th></tr></thead>
            <tbody>
            <?php foreach ($formas as [$f, $c, $q, $l]): ?>
                <tr>
                    <td><?= esc($f) ?></td>
                    <td><span class="ea-tag <?= $c === 'Gratis' ? 'ea-tag--free' : 'ea-tag--pago' ?>"><?= esc($c) ?></span></td>
                    <td><?= esc($q) ?></td>
                    <td><?= esc($l) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-bottom:0;">Si solo quieres saber si un CIF está bien escrito, usa el <a href="<?= site_url('validar-cif') ?>">validador de CIF</a>: calcula el dígito de control, pero eso no dice si la empresa existe.</p>
    </section>

    <!-- 4. Estados -->
    <section class="ea-card">
        <h2>Qué significa cada estado de una empresa</h2>
        <table class="ea-tabla">
            <thead><tr><th>Estado</th><th>Qué significa en la práctica</th></tr></thead>
            <tbody>
            <?php foreach ($estados as [$e, $d]): ?>
                <tr><td><?= esc($e) ?></td><td><?= esc($d) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Señales de alerta aunque figure como activa</h3>
        <ul style="margin-bottom:0;">
            <li>No ha depositado las cuentas anuales de los últimos ejercicios.</li>
            <li>Cambios de administrador o de domicilio muy seguidos.</li>
            <li>Ningún acto publicado en el BORME desde hace muchos años.</li>
            <li>Capital social mínimo y actividad que no encaja con lo que te ofrece.</li>
        </ul>
    </section>

    <!-- 5. Administradores -->
    <section class="ea-card">
        <h2>Cómo saber quién es el administrador de una empresa</h2>
        <p>Los nombramientos y los ceses de administradores se inscriben en el Registro Mercantil y se publican en el BORME. Tienes tres formas de verlo:</p>
        <ol>
            <li><strong>En la ficha de la empresa</strong> en APIEmpresas (búscala arriba): el apartado de administradores y cargos muestra los que siguen vigentes, cruzando nombramientos y ceses, con la fecha de cada uno.</li>
            <li><strong>En el BORME</strong>: busca los anuncios de la sociedad y lee los apartados «Nombramientos» y «Ceses/Dimisiones». El administrador actual es el último nombrado que no ha cesado.</li>
            <li><strong>En una nota simple</strong> del Registro Mercantil, si necesitas el dato con respaldo del Registro (por ejemplo, antes de firmar un contrato importante).</li>
        </ol>
        <h3>Tipos de cargo que vas a encontrar</h3>
        <ul>
            <li><strong>Administrador único</strong>: una sola persona (o sociedad) gestiona y firma.</li>
            <li><strong>Administradores solidarios</strong>: varios, y cualquiera puede firmar solo.</li>
            <li><strong>Administradores mancomunados</strong>: varios, y tienen que firmar juntos (al menos dos, según los estatutos).</li>
            <li><strong>Consejo de administración</strong>: presidente, consejeros y, a menudo, un consejero delegado con poderes para el día a día.</li>
            <li><strong>Liquidador</strong>: representa a la sociedad cuando está disuelta.</li>
            <li><strong>Apoderado</strong>: puede firmar lo que digan sus poderes, pero no es administrador.</li>
        </ul>
        <p style="margin-bottom:0;">Si quieres enterarte cuando cambie el administrador de una empresa, en su ficha puedes pedir que te avisemos el día que el BORME publique algo nuevo de ella (gratis, con una cuenta). Más detalles en <a href="<?= site_url('blog/como-saber-cambio-administrador') ?>">cómo saber si una empresa ha cambiado de administrador</a>.</p>
    </section>

    <!-- 6. CTA API -->
    <section class="ea-cta">
        <h2>¿Lo compruebas a menudo?</h2>
        <p>Si das de alta clientes o proveedores, revisas una cartera o validas empresas en un formulario, la API de empresas hace esta comprobación con una llamada: estado, administradores vigentes e historial del BORME en JSON. Hasta 100 CIF por petición. Empiezas con 100 consultas gratis, sin tarjeta.</p>
        <a class="ea-btn" href="<?= $registro ?>" data-track-click="guia_empresa_activa" data-track-element="cta_api">Obtener API key gratis</a>
        <div class="ea-cta-links">
            <a href="<?= site_url() ?>">API de empresas</a> · <a href="<?= site_url('api-empresas') ?>">Verificación de empresas (KYB)</a>
        </div>
    </section>

    <!-- 7. Preguntas frecuentes -->
    <section class="ea-card">
        <h2>Preguntas frecuentes</h2>
        <?php foreach ($faqs as [$q, $a]): ?>
            <details><summary><?= esc($q) ?></summary><p><?= esc($a) ?></p></details>
        <?php endforeach; ?>
    </section>

    <p class="ea-fuentes">Fuentes: Boletín Oficial del Registro Mercantil (BORME); Ley de Sociedades de Capital (art. 282, cierre por falta de depósito de cuentas); Ley 27/2014 del Impuesto sobre Sociedades (art. 119, baja en el Índice de Entidades); Real Decreto 249/2023 (revocación del NIF por no depositar cuentas cuatro ejercicios seguidos). Última revisión: 12 de octubre de 2026.</p>

</div>
</main>

<script>
(function () {
    'use strict';
    var SEARCH = <?= json_encode(site_url('search')) ?>;
    var CSRF   = <?= json_encode(site_url('csrftoken')) ?>;
    var BASE   = <?= json_encode(rtrim(site_url(), '/') . '/') ?>;
    var REG    = <?= json_encode($registro) ?>;

    function esc(t) { var e = document.createElement('div'); e.textContent = t == null ? '' : String(t); return e.innerHTML; }
    function fila(cls, ico, titulo, texto) {
        return '<div class="ea-row ' + cls + '"><span class="ea-ico">' + ico + '</span><div><strong>' + titulo + '</strong>' + (texto ? '<small>' + texto + '</small>' : '') + '</div></div>';
    }
    function track(datos) { try { if (window.trackEvent) window.trackEvent('company_status_check', datos); } catch (e) {} }

    /** Traduce el estado calculado (texto en español) a una explicación. */
    function explicar(estado) {
        var s = String(estado || '').toUpperCase();
        if (!s) return { cls: 'ea-info', ico: 'i', titulo: 'Sin estado calculado', texto: 'No hay actos en el BORME de los que deducir su estado. Es habitual en entidades que no son sociedades. Revisa la ficha.' };
        if (/INACTIV/.test(s)) return { cls: 'ea-warn', ico: '!', titulo: 'Inactiva', texto: 'No consta actividad reciente. Revisa en la ficha cuándo publicó por última vez.' };
        if (/EXTIN/.test(s)) return { cls: 'ea-ko', ico: '✕', titulo: 'Extinguida', texto: 'Consta su extinción: la empresa ya no existe.' };
        if (/CONCURSO/.test(s)) return { cls: 'ea-ko', ico: '!', titulo: 'En concurso de acreedores', texto: 'Puede seguir operando con limitaciones. Riesgo alto de impago.' };
        if (/LIQUID|DISUEL|DISOLU/.test(s)) return { cls: 'ea-ko', ico: '!', titulo: 'Disuelta o en liquidación', texto: 'Ha acordado disolverse: sigue existiendo mientras se liquida, pero ya no continúa su actividad.' };
        if (/CERRAD|CIERRE|HOJA/.test(s)) return { cls: 'ea-warn', ico: '!', titulo: 'Hoja registral cerrada', texto: 'El Registro no le inscribe actos nuevos hasta que corrija la causa (cuentas sin depositar, baja en Hacienda o NIF revocado).' };
        if (/ABSORB|FUSI/.test(s)) return { cls: 'ea-warn', ico: '→', titulo: 'Absorbida o fusionada', texto: 'Se ha integrado en otra sociedad.' };
        if (/ACTIV/.test(s)) return { cls: 'ea-ok', ico: '✓', titulo: 'Activa', texto: 'Consta inscrita y no hay publicado ningún acto que la cierre.' };
        return { cls: 'ea-info', ico: 'i', titulo: 'Estado: ' + esc(estado), texto: 'Revisa la ficha para ver los últimos actos publicados.' };
    }

    var form = document.getElementById('ea-form'), input = document.getElementById('ea-q'),
        res = document.getElementById('ea-res'), btn = document.getElementById('ea-go');

    function consultar(q) {
        return fetch(CSRF, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (t) {
                var body = new URLSearchParams();
                body.set('q', q);
                body.set(t.name, t.hash);
                return fetch(SEARCH, { method: 'POST', credentials: 'same-origin', body: body,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var q = input.value.trim();
        if (q.length < 3) { res.innerHTML = fila('ea-info', 'i', 'Escribe al menos 3 caracteres', ''); res.style.display = 'block'; return; }
        var pareceCif = /^[A-Z]\d{7}[0-9A-Z]$/i.test(q.replace(/[\s.\-\/]/g, ''));
        res.innerHTML = fila('ea-info', '…', 'Buscando…', ''); res.style.display = 'block';
        btn.disabled = true;
        consultar(q).then(function (r) {
            if (r.status === 200 && r.json && r.json.success && r.json.data) {
                var e = r.json.data, x = explicar(e.status), cif = e.cif || '';
                var datos = [cif ? 'CIF ' + cif : '', e.province, e.cnae_label, e.founded ? 'constituida el ' + e.founded.split('-').reverse().join('/') : ''].filter(Boolean).map(esc).join(' · ');
                res.innerHTML = fila('ea-ok', '✓', 'La empresa consta: ' + esc(e.name || ''), pareceCif ? '' : 'Es la coincidencia más cercana a tu búsqueda. Si no es la que buscas, prueba con su CIF.')
                    + fila(x.cls, x.ico, x.titulo, x.texto + ' Estado calculado por APIEmpresas a partir del BORME; no es un certificado del Registro Mercantil.')
                    + '<div class="ea-empresa"><strong>' + esc(e.name || '') + '</strong><br>' + datos
                    + '<br><a href="' + BASE + encodeURIComponent(cif) + '">Ver administradores y actos del BORME en su ficha →</a></div>'
                    + '<div class="ea-api">¿Tienes que comprobar muchas empresas? La <a href="' + BASE + '">API de empresas</a> te da el estado y los administradores en JSON, hasta 100 CIF por petición. <a href="' + REG + '">100 consultas gratis</a>.</div>';
                track({ encontrada: true, por_cif: pareceCif, estado: x.titulo });
            } else if (r.status === 404) {
                res.innerHTML = fila('ea-info', '?', 'No consta ninguna empresa con ese CIF o nombre',
                    pareceCif ? 'Comprueba que el CIF está bien escrito con el <a href="' + BASE + 'validar-cif">validador de CIF</a>. Si es correcto, puede ser una entidad que no se inscribe en el Registro Mercantil (asociación, comunidad de bienes, autónomo…).'
                              : 'Prueba con otra forma del nombre (sin S.L. o S.A.) o con su CIF.');
                track({ encontrada: false, por_cif: pareceCif });
            } else if (r.status === 429) {
                res.innerHTML = fila('ea-info', '!', 'Demasiadas búsquedas seguidas', 'Espera un minuto y vuelve a probar.');
            } else {
                res.innerHTML = fila('ea-info', '!', 'No se ha podido hacer la búsqueda', 'Inténtalo de nuevo en un momento.');
            }
        }).catch(function () {
            res.innerHTML = fila('ea-info', '!', 'No se ha podido hacer la búsqueda', 'Inténtalo de nuevo en un momento.');
        }).then(function () { btn.disabled = false; });
    });
})();
</script>

<?= view('partials/footer') ?>
</body>
</html>
