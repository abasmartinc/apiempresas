<?php
/**
 * /validar-nif-clientes-facturacion — Guía + comprobador (12-10-2026).
 *
 * Búsquedas: "comprobar NIF cliente factura", "validar NIF Verifactu", "error 2001 Verifactu",
 * "factura electrónica obligatoria NIF". No compite con la home ni con /api-empresas.
 *
 * Fechas comprobadas el 12-10-2026 (actualizar si cambian):
 *  - Verifactu: 1-1-2027 (contribuyentes del Impuesto sobre Sociedades) y 1-7-2027 (resto),
 *    Real Decreto-ley 15/2025, que modifica el RD 1007/2023.
 *  - Factura electrónica B2B: RD 238/2026 (BOE 31-3-2026) + Orden HAC/1028/2026 (BOE 5-10-2026,
 *    en vigor el 6-10-2026): 6-10-2027 (volumen de operaciones > 8 M€) y 6-10-2028 (resto).
 *    Fuente: calendario de implantación de la sede de la AEAT.
 *  - Contenido de la factura: art. 6.1 c), d) y e) del RD 1619/2012.
 *  - Error 2001 de Verifactu: "El NIF del bloque Destinatarios no está identificado en el censo
 *    de la AEAT" (registro aceptado con errores).
 */
$faqs = [
    ['¿Es obligatorio poner el NIF del cliente en la factura?',
     'En una factura completa por una operación hecha en España por un empresario establecido aquí, sí: el Reglamento de facturación (art. 6.1.d del Real Decreto 1619/2012) exige el NIF del destinatario, además de su nombre o razón social completa y su domicilio. En las facturas simplificadas (tiques) no hace falta, salvo que el cliente lo pida.'],
    ['¿Qué pasa si pongo mal el NIF del cliente en una factura?',
     'La factura tiene un dato erróneo y hay que corregirla con una factura rectificativa. Con Verifactu, además, la Agencia Tributaria compara el NIF del cliente con su censo: si no lo encuentra, el registro se acepta con el error 2001 y hay que subsanarlo.'],
    ['¿Qué significa el error 2001 de Verifactu?',
     'Que el NIF del cliente (bloque Destinatarios) no está identificado en el censo de la Agencia Tributaria. El registro queda aceptado con errores: hay que comprobar el NIF con el cliente y corregirlo. Si el cliente es un particular con DNI o NIE válido que no figura en el censo, se identifica con otro tipo de documento.'],
    ['¿Cuándo es obligatorio Verifactu?',
     'Según el Real Decreto-ley 15/2025, desde el 1 de enero de 2027 para los contribuyentes del Impuesto sobre Sociedades y desde el 1 de julio de 2027 para el resto (autónomos en IRPF, entre otros). Consulta siempre la sede de la Agencia Tributaria por si cambian.'],
    ['¿Cuándo es obligatoria la factura electrónica entre empresas?',
     'La Orden HAC/1028/2026 entró en vigor el 6 de octubre de 2026 y abrió los plazos del Real Decreto 238/2026: desde el 6 de octubre de 2027 para empresas con un volumen de operaciones de más de 8 millones de euros y desde el 6 de octubre de 2028 para el resto de empresas y autónomos.'],
    ['¿Cómo compruebo el NIF de un cliente de otro país de la UE?',
     'En VIES, el sistema de la Comisión Europea, que dice si un NIF-IVA está dado de alta para operaciones dentro de la UE. Para una entrega intracomunitaria exenta, el NIF-IVA del cliente es obligatorio en la factura.'],
    ['¿Esta página consulta el censo de Hacienda?',
     'No. Comprueba el formato del CIF y si la empresa consta en el Registro Mercantil, con su razón social y su estado según el BORME. Una sociedad activa en el Registro casi siempre está en el censo, pero no es lo mismo: para el censo, usa la comprobación de NIF de la sede de la Agencia Tributaria (con certificado electrónico o Cl@ve).'],
];

$fechas = [
    ['Verifactu (software de facturación)', 'Contribuyentes del Impuesto sobre Sociedades', '1 de enero de 2027', 'Real Decreto-ley 15/2025'],
    ['Verifactu (software de facturación)', 'Resto: autónomos en IRPF y otros', '1 de julio de 2027', 'Real Decreto-ley 15/2025'],
    ['Factura electrónica entre empresas', 'Volumen de operaciones de más de 8 millones de euros', '6 de octubre de 2027', 'RD 238/2026 y Orden HAC/1028/2026'],
    ['Factura electrónica entre empresas', 'Resto de empresas y autónomos', '6 de octubre de 2028', 'RD 238/2026 y Orden HAC/1028/2026'],
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
    'headline'      => 'Cómo validar el NIF de tus clientes antes de facturar',
    'datePublished' => '2026-10-12',
    'dateModified'  => '2026-10-12',
    'inLanguage'    => 'es-ES',
    'mainEntityOfPage' => site_url('validar-nif-clientes-facturacion'),
    'author'        => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
    'publisher'     => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
];
$registro = site_url('register?intent=api&plan=free&source=guia_nif_facturacion');
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Validar el NIF de tus clientes antes de facturar (Verifactu) | APIEmpresas',
        'excerptText' => 'Cómo comprobar el NIF y la razón social de tus clientes antes de emitir la factura: qué exige el Reglamento de facturación, el error 2001 de Verifactu y las fechas de Verifactu y la factura electrónica.',
        'canonical'   => site_url('validar-nif-clientes-facturacion'),
    ]) ?>
    <script type="application/ld+json"><?= json_encode($ldArticle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <script type="application/ld+json"><?= json_encode($ldFaq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <style>
        .nf-wrap { background: #f8fafc; padding: 56px 16px 40px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .nf { max-width: 880px; margin: 0 auto; color: #0f172a; }
        .nf h1 { font-size: clamp(1.9rem, 4.6vw, 2.8rem); font-weight: 900; letter-spacing: -0.03em; line-height: 1.12; margin: 0 0 14px; text-align: center; }
        .nf h1 span { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nf-sub { font-size: 1.12rem; color: #475569; text-align: center; max-width: 700px; margin: 0 auto 30px; line-height: 1.6; }
        .nf-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08); margin-bottom: 28px; }
        .nf-resumen { border-left: 4px solid #2563eb; }
        .nf h2 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 14px; }
        .nf h3 { font-size: 1.08rem; font-weight: 800; margin: 22px 0 8px; }
        .nf p, .nf li { color: #334155; line-height: 1.65; }
        .nf a { color: #2563eb; }
        .nf code { font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; background: #f1f5f9; padding: 1px 6px; border-radius: 5px; font-size: .9em; }
        .nf table { width: 100%; border-collapse: collapse; font-size: .93rem; }
        .nf th, .nf td { text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .nf th { background: #f1f5f9; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .nf td:first-child { font-weight: 700; color: #0f172a; }
        .nf-fecha { white-space: nowrap; font-weight: 800; color: #1d4ed8 !important; }
        .nf-note { font-size: .88rem; color: #64748b; }
        .nf-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .nf-form input { flex: 1; min-width: 200px; font-size: 1.2rem; padding: 14px 16px; border: 2px solid #cbd5e1; border-radius: 12px; font-family: 'JetBrains Mono', 'Fira Code', monospace; text-transform: uppercase; }
        .nf-form input:focus { outline: none; border-color: #2563eb; }
        .nf-btn { background: #2563eb; color: #fff; border: 0; border-radius: 12px; padding: 14px 26px; font-weight: 800; font-size: 1rem; cursor: pointer; }
        .nf-btn:hover { background: #1d4ed8; }
        .nf-btn[disabled] { opacity: .6; cursor: default; }
        .nf-res { margin-top: 18px; display: none; }
        .nf-row { display: flex; gap: 10px; align-items: flex-start; padding: 11px 0; border-top: 1px solid #f1f5f9; line-height: 1.5; }
        .nf-row:first-child { border-top: 0; }
        .nf-ico { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: .85rem; }
        .nf-ok .nf-ico { background: #dcfce7; color: #15803d; }
        .nf-ko .nf-ico { background: #fee2e2; color: #b91c1c; }
        .nf-warn .nf-ico { background: #fef3c7; color: #b45309; }
        .nf-info .nf-ico { background: #e0e7ff; color: #3730a3; }
        .nf-row strong { display: block; }
        .nf-row small { color: #64748b; }
        .nf-factura { margin-top: 10px; padding: 16px; border-radius: 12px; background: #f8fafc; border: 1px dashed #94a3b8; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: .9rem; line-height: 1.7; }
        .nf-factura b { font-family: 'Inter', sans-serif; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; display: block; }
        .nf-api { margin-top: 16px; padding: 16px 18px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; font-size: .95rem; line-height: 1.5; }
        .nf-api a { color: #1d4ed8; font-weight: 800; }
        .nf pre { margin: 10px 0; background: #0f172a; color: #e2e8f0; border-radius: 12px; padding: 16px 18px; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; font-size: .84rem; line-height: 1.6; white-space: pre-wrap; word-break: break-all; }
        .nf details { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .nf summary { cursor: pointer; font-weight: 700; font-size: 1.02rem; }
        .nf details p { margin: 10px 0 0; }
        .nf-cta { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 20px; padding: 36px 28px; text-align: center; margin-bottom: 28px; }
        .nf-cta h2 { color: #fff; }
        .nf-cta p { color: #cbd5e1; max-width: 620px; margin: 0 auto 22px; }
        .nf a.nf-btn, .nf a.nf-btn:hover { display: inline-block; text-decoration: none; color: #fff; }
        .nf-cta .nf-links { margin-top: 16px; font-size: .92rem; }
        .nf-cta .nf-links a { color: #93c5fd; font-weight: 700; }
        @media (max-width: 640px) {
            .nf-card { padding: 20px; }
            .nf-tabla thead { display: none; }
            .nf-tabla, .nf-tabla tbody, .nf-tabla tr, .nf-tabla td { display: block; width: auto; }
            .nf-tabla tr { padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
            .nf-tabla td { border: 0; padding: 2px 0; }
        }
    </style>
</head>
<body>
<?= view('partials/header') ?>

<main class="nf-wrap">
<div class="nf">

    <h1>Validar el NIF de tus clientes <span>antes de facturar</span></h1>
    <p class="nf-sub">Qué datos del cliente tiene que llevar la factura, cómo comprobarlos y qué cambia con Verifactu y la factura electrónica obligatoria. Con un comprobador gratis de CIF y razón social.</p>

    <!-- 1. Respuesta corta -->
    <section class="nf-card nf-resumen">
        <h2>La respuesta corta</h2>
        <p>Antes de emitir la primera factura a un cliente empresa, comprueba tres cosas:</p>
        <ol>
            <li><strong>Que el NIF está bien formado</strong> (el dígito de control cuadra).</li>
            <li><strong>Que corresponde a una empresa que existe y sigue activa</strong>, y copia su razón social tal como consta, no como te la escribió el cliente.</li>
            <li><strong>Que el NIF está dado de alta en Hacienda</strong>, o en VIES si el cliente es de otro país de la UE.</li>
        </ol>
        <p style="margin-bottom:0;">Con Verifactu, la Agencia Tributaria comprueba el NIF de cada cliente al recibir el registro de la factura: si no lo encuentra en su censo, lo marca con el error 2001 y hay que subsanarlo. Comprobarlo al dar de alta al cliente evita tener que rectificar facturas después.</p>
    </section>

    <!-- 2. Comprobador -->
    <section class="nf-card" aria-labelledby="nf-titulo">
        <h2 id="nf-titulo">Comprueba el CIF de un cliente</h2>
        <form class="nf-form" id="nf-form" autocomplete="off">
            <label for="nf-cif" style="position:absolute;left:-9999px;">CIF del cliente</label>
            <input id="nf-cif" type="text" maxlength="15" placeholder="Ej.: A46103834" required>
            <button class="nf-btn" type="submit" id="nf-go">Comprobar</button>
        </form>
        <div class="nf-res" id="nf-res" aria-live="polite"></div>
        <p class="nf-note" style="margin-bottom:0;">Comprueba el formato y si la empresa consta en el Registro Mercantil, con su razón social y su estado según el BORME. No consulta el censo de Hacienda.</p>
    </section>

    <!-- 3. Qué exige la factura -->
    <section class="nf-card">
        <h2>Qué datos del cliente tiene que llevar la factura</h2>
        <p>El Reglamento de facturación (Real Decreto 1619/2012, artículo 6.1) pide en la factura completa:</p>
        <ul>
            <li><strong>Nombre o razón social completa</strong> del cliente (letra c).</li>
            <li><strong>Su NIF</strong>, entre otros casos, cuando la operación se hace en España y quien factura está establecido aquí, en las entregas intracomunitarias exentas y cuando el cliente es quien declara el IVA (inversión del sujeto pasivo) (letra d).</li>
            <li><strong>Su domicilio</strong> (letra e).</li>
        </ul>
        <p style="margin-bottom:0;">Un error en cualquiera de estos datos obliga a emitir una factura rectificativa. La razón social es lo que más se equivoca: el cliente escribe el nombre comercial («Mercadona») y la factura tiene que llevar la denominación registrada («MERCADONA SA»).</p>
    </section>

    <!-- 4. Fechas -->
    <section class="nf-card">
        <h2>Fechas de Verifactu y de la factura electrónica obligatoria</h2>
        <table class="nf-tabla">
            <thead><tr><th>Obligación</th><th>A quién</th><th>Desde</th><th>Norma</th></tr></thead>
            <tbody>
            <?php foreach ($fechas as [$o, $q, $f, $n]): ?>
                <tr><td><?= esc($o) ?></td><td><?= esc($q) ?></td><td class="nf-fecha"><?= esc($f) ?></td><td><?= esc($n) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="nf-note">Son dos obligaciones distintas. Verifactu afecta al programa con el que emites las facturas, que tiene que generar y, en su caso, enviar a Hacienda un registro de cada una. La factura electrónica obliga a emitir y recibir las facturas entre empresas en formato electrónico. Revisado el 12 de octubre de 2026: consulta la sede de la Agencia Tributaria por si cambian.</p>

        <h3>Qué comprueba Hacienda del NIF del cliente con Verifactu</h3>
        <p style="margin-bottom:0;">Cuando el registro de una factura llega a la Agencia Tributaria, se compara el NIF del cliente con su censo. Si no está, el registro se acepta, pero con el error <code>2001</code> («El NIF del bloque Destinatarios no está identificado en el censo de la AEAT»), y hay que corregirlo: revisar el NIF con el cliente o, si es un particular con DNI o NIE válido que no está en el censo, identificarlo con otro tipo de documento.</p>
    </section>

    <!-- 5. Herramientas -->
    <section class="nf-card">
        <h2>Dónde comprobar cada cosa</h2>
        <table class="nf-tabla">
            <thead><tr><th>Qué quieres saber</th><th>Dónde</th><th>Coste</th></tr></thead>
            <tbody>
                <tr><td>¿El CIF está bien escrito?</td><td>El <a href="<?= site_url('validar-cif') ?>">validador de CIF</a> o la <a href="<?= site_url('validar-cif-excel') ?>">fórmula para Excel</a>, si tienes una lista.</td><td>Gratis</td></tr>
                <tr><td>¿La empresa existe y sigue activa? ¿Cuál es su razón social exacta?</td><td>El comprobador de esta página o la guía de <a href="<?= site_url('como-saber-si-una-empresa-esta-activa') ?>">cómo saber si una empresa está activa</a>.</td><td>Gratis</td></tr>
                <tr><td>¿El NIF está en el censo de Hacienda?</td><td>La comprobación de NIF de la sede de la Agencia Tributaria, con certificado electrónico o Cl@ve.</td><td>Gratis</td></tr>
                <tr><td>¿El NIF-IVA de un cliente de otro país de la UE es válido?</td><td>VIES, de la Comisión Europea.</td><td>Gratis</td></tr>
                <tr><td>Todo esto dentro de tu programa de facturación</td><td>La <a href="<?= site_url() ?>">API de empresas</a>: razón social, estado y domicilio por CIF, y verificación con VIES en el plan Pro.</td><td>100 consultas gratis</td></tr>
            </tbody>
        </table>
    </section>

    <!-- 6. En tu software -->
    <section class="nf-card">
        <h2>Hacerlo automático al dar de alta un cliente</h2>
        <p>Si tienes un programa de facturación, un ERP o un formulario de alta, puedes pedir solo el CIF y rellenar el resto con la API:</p>
        <pre>curl "https://apiempresas.es/api/v1/companies?cif=A46103834" \
  -H "X-API-KEY: TU_API_KEY"</pre>
        <ul>
            <li>Si la respuesta es <code>COMPANY_NOT_FOUND</code> o <code>INVALID_CIF_FORMAT</code>, avisa al usuario antes de guardar el cliente. Estas respuestas no gastan consultas.</li>
            <li>Si el estado es extinguida o está disuelta, no la des de alta sin revisarla.</li>
            <li>Guarda la razón social que devuelve la API, no la que se escribió a mano. Con el plan Pro también recibes el domicilio completo.</li>
            <li>Para clientes de otros países de la UE, <code>/companies/verify</code> (Pro) comprueba además su NIF-IVA en VIES.</li>
        </ul>
        <p style="margin-bottom:0;">Hay SDK para PHP, Node.js y Python y ejemplos en la <a href="<?= site_url('documentation') ?>">documentación</a>.</p>
    </section>

    <section class="nf-cta">
        <h2>Comprueba a tus clientes desde tu programa</h2>
        <p>Pide el CIF y la API te devuelve la razón social exacta, el estado y la provincia en JSON, antes de emitir la primera factura. Empiezas con 100 consultas gratis, sin tarjeta.</p>
        <a class="nf-btn" href="<?= $registro ?>" data-track-click="guia_nif_facturacion" data-track-element="cta_api">Obtener API key gratis</a>
        <div class="nf-links"><a href="<?= site_url() ?>">API de empresas</a> · <a href="<?= site_url('api-empresas') ?>">Verificación de empresas (KYB)</a></div>
    </section>

    <section class="nf-card">
        <h2>Preguntas frecuentes</h2>
        <?php foreach ($faqs as [$q, $a]): ?>
            <details><summary><?= esc($q) ?></summary><p><?= esc($a) ?></p></details>
        <?php endforeach; ?>
    </section>

    <p class="nf-note">Fuentes: Real Decreto 1619/2012, Reglamento de facturación (art. 6); Real Decreto 1007/2023 (Verifactu) y Real Decreto-ley 15/2025; Real Decreto 238/2026 y Orden HAC/1028/2026 (BOE de 5 de octubre de 2026); calendario de implantación de la factura electrónica y lista de errores de Verifactu de la Agencia Tributaria. Esta página es informativa y no sustituye el asesoramiento fiscal. Última revisión: 12 de octubre de 2026.</p>

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
        return '<div class="nf-row ' + cls + '"><span class="nf-ico">' + ico + '</span><div><strong>' + titulo + '</strong>' + (texto ? '<small>' + texto + '</small>' : '') + '</div></div>';
    }
    function track(d) { try { if (window.trackEvent) window.trackEvent('nif_invoice_check', d); } catch (e) {} }
    function limpiar(v) { return String(v || '').toUpperCase().replace(/[\s.\-\/]/g, ''); }

    /** Formato y dígito de control (misma lógica que /validar-cif). */
    function analizar(c) {
        if (/^(\d{8}|[XYZ]\d{7})[A-Z]$/.test(c)) {
            var n = parseInt(c.replace(/^X/, '0').replace(/^Y/, '1').replace(/^Z/, '2').slice(0, 8), 10);
            var ok = 'TRWAGMYFPDXBNJZSQVHLCKE'.charAt(n % 23) === c.charAt(8);
            return { tipo: 'persona', valido: ok };
        }
        if (!/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/.test(c)) return { tipo: 'formato', valido: false };
        var s = 0;
        for (var i = 0; i < 7; i++) { var x = parseInt(c.charAt(i + 1), 10); if (i % 2 === 0) { x *= 2; if (x > 9) x -= 9; } s += x; }
        var d = (10 - s % 10) % 10, dl = 'JABCDEFGHI'.charAt(d), k = c.charAt(8), l = c.charAt(0);
        var ok2 = 'ABEH'.indexOf(l) >= 0 ? k === String(d) : 'NPQRSW'.indexOf(l) >= 0 ? k === dl : (k === String(d) || k === dl);
        return { tipo: 'cif', valido: ok2, esperado: 'ABEH'.indexOf(l) >= 0 ? String(d) : 'NPQRSW'.indexOf(l) >= 0 ? dl : String(d) + ' o ' + dl };
    }

    var form = document.getElementById('nf-form'), input = document.getElementById('nf-cif'),
        res = document.getElementById('nf-res'), btn = document.getElementById('nf-go');

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var c = limpiar(input.value), a = analizar(c);
        res.style.display = 'block';
        if (a.tipo === 'persona') {
            res.innerHTML = fila(a.valido ? 'nf-ok' : 'nf-ko', a.valido ? '✓' : '✕', a.valido ? 'DNI o NIE bien formado' : 'La letra del DNI o NIE no cuadra', 'Es un particular o un autónomo: no figura en el Registro Mercantil. Pídele sus datos completos y, si dudas, comprueba el NIF en la sede de la Agencia Tributaria.');
            track({ tipo: 'persona', valido: a.valido }); return;
        }
        if (!a.valido) {
            res.innerHTML = fila('nf-ko', '✕', a.tipo === 'formato' ? 'No tiene formato de CIF' : 'El dígito de control no cuadra',
                a.tipo === 'formato' ? 'Un CIF tiene 9 caracteres: una letra, 7 números y un carácter de control. Pide al cliente que lo revise.' : 'Debería ser ' + esc(a.esperado) + '. Seguramente hay un error al copiarlo: no factures con este CIF.');
            track({ tipo: a.tipo, valido: false }); return;
        }
        res.innerHTML = fila('nf-ok', '✓', 'Formato correcto: ' + esc(c), '') + '<div id="nf-emp">' + fila('nf-info', '…', 'Buscando la empresa…', '') + '</div>';
        btn.disabled = true;
        fetch(CSRF, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (t) {
                var body = new URLSearchParams(); body.set('q', c); body.set(t.name, t.hash);
                return fetch(SEARCH, { method: 'POST', credentials: 'same-origin', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); })
            .then(function (r) {
                var box = document.getElementById('nf-emp');
                var e = r.json && r.json.data;
                if (r.status === 200 && e && limpiar(e.cif) === c) {
                    var st = String(e.status || '').toUpperCase();
                    var cerrada = /EXTIN|DISUEL|DISOLU|LIQUID|CONCURSO|CERRAD/.test(st);
                    box.innerHTML = fila('nf-ok', '✓', 'La empresa consta en el Registro Mercantil', '')
                        + fila(cerrada ? 'nf-warn' : (st ? 'nf-ok' : 'nf-info'), cerrada ? '!' : (st ? '✓' : 'i'),
                            st ? 'Estado: ' + esc(e.status) : 'Sin estado calculado',
                            cerrada ? 'Revisa la situación antes de facturar: puede estar disuelta, en liquidación, en concurso o extinguida.' : 'Estado calculado a partir del BORME; no es un certificado.')
                        + '<div class="nf-factura"><b>Datos para la factura</b>Razón social: ' + esc(e.name || '') + '<br>NIF: ' + esc(c)
                        + (e.municipality || e.province ? '<br>Localidad: ' + esc([e.municipality, e.province].filter(Boolean).join(', ')) : '') + '</div>'
                        + '<p class="nf-note" style="margin:8px 0 0;">Copia la razón social tal cual. El domicilio completo, en la <a href="' + BASE + encodeURIComponent(c) + '">ficha de la empresa</a> o, para hacerlo automático, con la <a href="' + BASE + '">API de empresas</a> (<a href="' + REG + '">100 consultas gratis</a>).</p>';
                    track({ tipo: 'cif', valido: true, existe: true, cerrada: cerrada });
                } else if (r.status === 200 || r.status === 404) {
                    box.innerHTML = fila('nf-warn', '?', 'No consta en el Registro Mercantil', 'El CIF está bien formado, pero no hay ninguna sociedad inscrita con él. Puede ser una asociación, una comunidad de bienes u otra entidad que no se inscribe, o un CIF que no existe. Compruébalo en la sede de la Agencia Tributaria antes de facturar.');
                    track({ tipo: 'cif', valido: true, existe: false });
                } else if (r.status === 429) {
                    box.innerHTML = fila('nf-info', '!', 'Demasiadas consultas seguidas', 'Espera un minuto y vuelve a probar.');
                } else {
                    box.innerHTML = fila('nf-info', '!', 'No se ha podido comprobar', 'Inténtalo de nuevo en un momento.');
                }
            })
            .catch(function () {
                var box = document.getElementById('nf-emp');
                if (box) box.innerHTML = fila('nf-info', '!', 'No se ha podido comprobar', 'Inténtalo de nuevo en un momento.');
            })
            .then(function () { btn.disabled = false; });
    });
})();
</script>

<?= view('partials/footer') ?>
</body>
</html>
