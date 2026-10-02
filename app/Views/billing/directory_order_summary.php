<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Descarga Base de Datos de Empresas en ' . esc($display_name ?? $province) . ' | APIEmpresas',
        'excerptText' => 'Descarga el listado completo de ' . number_format($total_count, 0, ',', '.') . ' empresas registradas en ' . esc($display_name ?? $province) . ' en formato CSV.',
        'robots'      => 'noindex, nofollow',
    ]) ?>
    <style>
        /* Encapsulate checkout (hide nav & footer) */
        header .nav .desktop-only,
        header .nav .mobile-nav-btn,
        header .nav .btn-enter,
        header .nav .login-btn { display: none !important; }
        footer { display: none !important; }

        .page-summary {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 24px 40px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 24px;
            align-items: start;
        }
        .main-card {
            background: white;
            border-radius: 20px;
            padding: 28px 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .order-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 28px;
            border: 2px solid #d1fae5;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
        }
        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ecfdf5;
            color: #065f46;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 800;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .stat-box-label { color: #94a3b8; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 2px; }
        .stat-box-value { font-weight: 800; color: #0f172a; font-size: 1.05rem; }
        .stat-box-sub { font-size: 0.72rem; color: #64748b; font-weight: 600; }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #e2e8f0;
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
        }
        .cols-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 10px;
        }
        .col-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .disclaimer-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #92400e;
            font-weight: 600;
            line-height: 1.5;
        }
        .guarantee-box {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            padding: 12px 16px;
            background: #f0fdf4;
            border-radius: 10px;
            border: 1px solid #dcfce7;
        }
        .guarantee-box svg { color: #16a34a; flex-shrink: 0; margin-top: 1px; }
        .guarantee-box p { font-size: 0.83rem; color: #166534; margin: 0; line-height: 1.4; }

        @media (max-width: 900px) {
            .summary-grid { grid-template-columns: 1fr; }
            .order-card { position: static; }
            .page-summary { padding: 16px 16px 32px; }
            .mobile-sticky-cta {
                position: fixed; bottom: 0; left: 0; right: 0;
                background: white; padding: 16px;
                box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
                z-index: 999; display: flex;
                align-items: center; justify-content: space-between;
                gap: 12px; border-top: 1px solid #e2e8f0;
            }
        }
        @media (max-width: 480px) {
            .main-card { padding: 18px 16px; }
            .order-card { padding: 18px 16px; }
            .summary-grid { gap: 16px; margin-bottom: 80px; }
        }
        @media (min-width: 901px) {
            .mobile-sticky-cta { display: none; }
        }
        /* Vista previa del listado */
        .summary-grid > * { min-width: 0; } /* sin esto la tabla ensancha la columna y la página */
        .preview-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 16px; }
        .preview-box h3 { font-size: 0.85rem; font-weight: 800; color: #0f172a; margin: 0 0 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .preview-sub { font-size: 0.8rem; color: #64748b; margin: 0 0 12px; line-height: 1.5; }
        .preview-scroll { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
        .preview-table { border-collapse: collapse; font-size: 0.78rem; white-space: nowrap; min-width: 100%; }
        .preview-table th { background: #f1f5f9; color: #334155; font-weight: 800; text-align: left; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .preview-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #0f172a; max-width: 260px; overflow: hidden; text-overflow: ellipsis; }
        .preview-table tr:last-child td { border-bottom: none; }
        .preview-vacio { color: #cbd5e1; }
        /* Empresas con teléfono */
        .phone-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 18px; margin-top: 16px; font-size: 0.9rem; color: #1e3a8a; line-height: 1.5; }
        .phone-box__dato { font-weight: 700; }
        .phone-box__oferta { display: inline-block; margin-top: 6px; color: #1d4ed8; text-decoration: none; }
        .phone-box__oferta:hover { text-decoration: underline; }
        /* Presupuesto por correo */
        .quote-box { margin-top: 16px; padding-top: 16px; border-top: 1px dashed #e2e8f0; }
        .quote-box label { display: block; font-size: 0.8rem; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .quote-row { display: flex; gap: 8px; }
        .quote-row input[type=email] { flex: 1; min-width: 0; height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 10px; font-size: 0.9rem; }
        .quote-row button { height: 40px; padding: 0 14px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font-weight: 800; font-size: 0.85rem; cursor: pointer; white-space: nowrap; }
        .quote-row button:hover { border-color: #10b981; color: #047857; }
        .quote-msg { font-size: 0.8rem; margin-top: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main>
        <div class="page-summary">
            <div class="summary-grid">
                <!-- IZQUIERDA: DETALLES DEL PRODUCTO -->
                <div class="main-card">
                    <div class="product-badge">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Base de Datos Histórica · CSV
                    </div>

                    <h1 style="font-size: 1.8rem; font-weight: 900; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.03em; line-height: 1.15;">
                        <?= number_format($total_count, 0, ',', '.') ?> empresas registradas en <?= esc($display_name ?? $province) ?>
                    </h1>

                    <p style="font-size: 0.95rem; color: #64748b; line-height: 1.6; margin-bottom: 20px;">
                        Listado de las sociedades de <strong><?= esc($display_name ?? $province) ?></strong> que tenemos registradas, con los datos publicados en el BORME.
                        Para prospección B2B, enriquecimiento de CRM y análisis de mercado.
                    </p>

                    <!-- Estadísticas clave -->
                    <div class="stat-grid">
                        <div class="stat-box">
                            <div class="stat-box-label">Empresas incluidas</div>
                            <div class="stat-box-value"><?= number_format($total_count, 0, ',', '.') ?></div>
                            <div class="stat-box-sub">Historial completo</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-label">Formato</div>
                            <div class="stat-box-value">CSV (se abre con Excel)</div>
                            <div class="stat-box-sub">Descarga tras el pago</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-label">Precio por empresa</div>
                            <div class="stat-box-value"><?= $total_count > 0 ? number_format($price / $total_count, 4, ',', '.') : '—' ?>€</div>
                            <div class="stat-box-sub">Coste unitario</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-label">Fuente</div>
                            <div class="stat-box-value">BORME</div>
                            <div class="stat-box-sub">Boletín Oficial del Registro Mercantil</div>
                        </div>
                    </div>

                    <?php if (!empty($preview)): ?>
                    <!-- Vista previa: filas repartidas por el listado, con contacto y nombres tapados -->
                    <div class="preview-box">
                        <h3>Así es el archivo que vas a descargar</h3>
                        <p class="preview-sub"><?= count($preview) ?> empresas de tu listado, tomadas de distintos puntos (de las más recientes a las más antiguas). Los teléfonos, las direcciones y los nombres de personas van tapados aquí; en el CSV están completos.</p>
                        <div class="preview-scroll" tabindex="0" role="region" aria-label="Vista previa del listado">
                            <table class="preview-table">
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($preview[0]) as $col): ?>
                                        <th><?= esc($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preview as $fila): ?>
                                    <tr>
                                        <?php foreach ($fila as $valor): ?>
                                        <td title="<?= esc($valor) ?>"><?= $valor !== '' ? esc(mb_strimwidth($valor, 0, 48, '…', 'UTF-8')) : '<span class="preview-vacio">—</span>' ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="preview-sub" style="margin: 8px 0 0;">Desliza la tabla hacia la derecha para ver todas las columnas. Una raya (—) significa que no tenemos ese dato de esa empresa.</p>
                    </div>
                    <?php endif; ?>

                    <!-- Campos incluidos -->
                    <div style="background: #f8fafc; border-radius: 12px; padding: 16px 20px; border: 1px solid #e2e8f0;">
                        <h3 style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                            Campos incluidos en el CSV
                        </h3>
                        <div class="cols-grid">
                            <?php /* Las 13 columnas reales del CSV (RadarController::streamExportData). Antes
                                     se anunciaba "Forma jurídica", que no existe en el archivo. */ ?>
                            <?php foreach ([
                                'Razón social',
                                'CIF',
                                'Fecha de constitución',
                                'Sector CNAE',
                                'Municipio',
                                'Provincia',
                                'Teléfono (cuando lo tenemos)',
                                'Dirección',
                                'Objeto social',
                                'Capital social',
                                'Socio único',
                                'Administradores y cargos',
                                'Estado',
                            ] as $field): ?>
                            <div class="col-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <?= $field ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ((int) $total_count > 0 && empty($has_phone)): ?>
                    <!-- Cuántas tienen teléfono y oferta de comprar solo esas (se rellena por JS:
                         Billing::recuento_telefono). Oculto hasta tener el dato. -->
                    <div class="phone-box" id="phoneBox" hidden>
                        <div class="phone-box__dato"><span id="phoneCount"></span> de las <?= number_format($total_count, 0, ',', '.') ?> empresas tienen teléfono (<span id="phonePct"></span>).</div>
                        <a class="phone-box__oferta" id="phoneOffer" href="#" rel="nofollow">
                            ¿Solo quieres las que tienen teléfono? <strong><span id="phoneCount2"></span> empresas por <span id="phonePrice"></span> € + IVA</strong> →
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Aviso NO/SI incluye teléfono -->
                    <?php if (isset($has_phone) && $has_phone == '1'): ?>
                    <div class="disclaimer-box" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                        ✅ <strong>Este listado SÍ incluye teléfono de contacto.</strong><br>
                        Has seleccionado solo empresas con teléfono. Contiene los datos publicados en el BORME (razón social, CIF, actividad, cargos) y los teléfonos que tenemos de cada una (fijo y móvil).
                    </div>
                    <?php else: ?>
                    <div class="disclaimer-box">
                        ⚠️ <strong>Puede haber empresas en este listado sin teléfono.</strong><br>
                        Contiene los datos publicados en el BORME y el teléfono cuando lo tenemos.
                        Es perfecto para cruzar con otras fuentes, validar CIFs o analizar el tejido empresarial de una zona.
                    </div>
                    <?php endif; ?>

                    <div class="guarantee-box" style="margin-top: 16px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <p><strong>Origen de los datos:</strong> anuncios publicados en el BORME. Pago seguro gestionado por Stripe.</p>
                    </div>
                </div>

                <!-- DERECHA: RESUMEN DE PAGO -->
                <div class="order-card">
                    <h2 style="font-size: 1.1rem; font-weight: 900; margin-bottom: 16px; color: #0f172a;">Resumen del pedido</h2>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #64748b; font-size: 0.88rem; gap: 12px;">
                        <span>BBDD <?= esc($display_name ?? $province) ?> (<?= number_format($total_count, 0, ',', '.') ?> empresas)</span>
                        <span style="font-weight: 700; color: #0f172a; white-space: nowrap;">
                            <?= number_format($price, 2, ',', '.') ?> €
                        </span>
                    </div>

                    <?php /* Sin "precio original" ni "con descuento": era un precio que nunca se cobró (ver pricing_helper). */ ?>
                    <?php if ($total_count > 0 && $price > 0): ?>
                    <div style="text-align: right; font-size: 0.72rem; font-weight: 800; margin-bottom: 12px; display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;">
                        <?php if (!empty($pricing['precio_maximo'])): ?>
                        <span style="background:#ecfdf5; color:#047857; padding:2px 8px; border-radius:4px;">✓ Precio máximo: nunca pagas más de <?= number_format($pricing['tope'] ?? 149, 0, ',', '.') ?> €</span>
                        <?php endif; ?>
                        <span style="background: #ecfdf5; padding: 2px 6px; border-radius: 4px; color:#10b981;">
                            Apenas <?= number_format($price / $total_count, 4, ',', '.') ?>€ por empresa
                        </span>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #64748b; font-size: 0.88rem; gap: 12px;">
                        <span>IVA (21%)</span>
                        <span style="font-weight: 700; color: #0f172a; white-space: nowrap;"><?= number_format($tax, 2, ',', '.') ?> €</span>
                    </div>

                    <div class="total-row">
                        <span>Total</span>
                        <span style="color: #10b981;"><?= number_format($price + $tax, 2, ',', '.') ?> €</span>
                    </div>

                    <div style="background: #ecfdf5; border-radius: 8px; padding: 8px 12px; margin-top: 10px; display: flex; align-items: center; gap: 8px; border: 1px solid #a7f3d0;">
                        <span style="font-size: 14px;">⚡</span>
                        <p style="font-size: 0.75rem; color: #065f46; font-weight: 800; margin: 0; line-height: 1.2;">
                            Descarga disponible inmediatamente tras el pago
                        </p>
                    </div>

                    <?php if ((int) $total_count <= 0): ?>
                    <div style="margin-top: 24px; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:14px; color:#991b1b; font-size:0.9rem; font-weight:600; line-height:1.5;">
                        No hay empresas que cumplan estos filtros, así que no hay nada que comprar. Prueba con otra provincia o sector.
                    </div>
                    <?php else: ?>
                    <form action="<?= site_url('billing/checkout') ?>" method="POST" style="margin-top: 24px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan"         value="directory_single">
                        <input type="hidden" name="period"       value="single">
                        <input type="hidden" name="provincia"    value="<?= esc($province) ?>">
                        <?php if(!empty($cnae)): ?>
                        <input type="hidden" name="cnae"         value="<?= esc($cnae) ?>">
                        <?php endif; ?>
                        <?php if(!empty($cnae_text)): ?>
                        <input type="hidden" name="cnae_text"    value="<?= esc($cnae_text) ?>">
                        <?php endif; ?>
                        <?php if(!empty($sector)): ?>
                        <input type="hidden" name="sector"       value="<?= esc($sector) ?>">
                        <?php endif; ?>
                        <?php if(!empty($estado)): ?>
                        <input type="hidden" name="estado"       value="<?= esc($estado) ?>">
                        <?php endif; ?>
                        <?php if(!empty($municipio)): ?>
                        <input type="hidden" name="municipio"    value="<?= esc($municipio) ?>">
                        <?php endif; ?>
                        <?php if(!empty($has_phone)): ?>
                        <input type="hidden" name="has_phone"    value="<?= esc($has_phone) ?>">
                        <?php endif; ?>
                        <?php if(!empty($date_min)): ?>
                        <input type="hidden" name="date_min"     value="<?= esc($date_min) ?>">
                        <?php endif; ?>
                        <?php if(!empty($date_max)): ?>
                        <input type="hidden" name="date_max"     value="<?= esc($date_max) ?>">
                        <?php endif; ?>
                        <?php /* El precio y el recuento NO viajan en el formulario: los calcula el servidor al pagar. */ ?>

                        <button type="submit" class="btn js-loading-btn" data-track-event="directory_checkout_click"
                            style="width: 100%; padding: 18px; font-size: 1rem; font-weight: 900; background: #10b981; color: white; border-radius: 16px; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35); text-transform: uppercase; letter-spacing: 0.01em; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.2;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 14px 30px rgba(16, 185, 129, 0.45)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(16, 185, 129, 0.35)';">
                            <span style="font-size: 1.1rem; letter-spacing: -0.01em; pointer-events: none;">
                                Pagar <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.65; font-size:0.85em; font-weight:500; margin-right:6px;"><?= number_format($pricing['original_price'] * 1.21, 2, ',', '.') ?>€</s><?php endif; ?><?= number_format($price + $tax, 2, ',', '.') ?>€ y Descargar CSV
                            </span>
                        </button>
                    </form>
                    <?php endif; ?>

                    <p style="font-size: 0.72rem; color: #94a3b8; text-align: center; margin-top: 16px; line-height: 1.5; font-weight: 500;">
                        Al confirmar serás redirigido a la pasarela segura de Stripe.<br>Pago único, sin suscripción.
                    </p>

                    <?php if ((int) $total_count > 0): ?>
                    <!-- Presupuesto por correo: para quien no compra ahora (Billing::presupuesto_listado) -->
                    <form class="quote-box" id="quoteForm" action="<?= site_url('billing/presupuesto-listado') ?>" method="POST">
                        <?= csrf_field() ?>
                        <?php foreach (['provincia' => $province, 'cnae' => $cnae ?? '', 'cnae_text' => $cnae_text ?? '', 'sector' => $sector ?? '', 'estado' => $estado ?? '', 'municipio' => $municipio ?? '', 'has_phone' => $has_phone ?? '', 'date_min' => $date_min ?? '', 'date_max' => $date_max ?? ''] as $k => $v): ?>
                            <?php if ((string) $v !== ''): ?><input type="hidden" name="<?= $k ?>" value="<?= esc($v) ?>"><?php endif; ?>
                        <?php endforeach; ?>
                        <label for="quoteEmail">¿Lo decides más tarde? Te enviamos este presupuesto</label>
                        <div class="quote-row">
                            <input type="email" id="quoteEmail" name="email" required placeholder="tu@empresa.com" autocomplete="email" value="<?= esc((string) (session('user_email') ?? '')) ?>">
                            <button type="submit" id="quoteBtn">Enviar</button>
                        </div>
                        <?php if (filter_var(env('TURNSTILE_ENABLED', false), FILTER_VALIDATE_BOOLEAN)): // mismo interruptor que login, registro y muestra gratuita ?>
                        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                        <div class="cf-turnstile" data-sitekey="<?= esc(env('TURNSTILE_SITE_KEY')) ?>" data-theme="light" data-appearance="interaction-only" style="margin-top: 8px;"></div>
                        <?php endif; ?>
                        <div class="quote-msg" id="quoteMsg" role="status" style="color:#64748b; font-weight:500;">Un solo correo con el enlace a este listado. No te apuntamos a ninguna lista.</div>
                    </form>
                    <script>
                    (function () {
                        var f = document.getElementById('quoteForm');
                        if (!f || !window.fetch) return;   // sin JS, el formulario se envía normal
                        f.addEventListener('submit', function (e) {
                            e.preventDefault();
                            var btn = document.getElementById('quoteBtn'), msg = document.getElementById('quoteMsg');
                            var t = window.AE_CSRF;   // token del visitante (ver partials/footer)
                            if (t) f.querySelectorAll('input[name="' + t.name + '"]').forEach(function (i) { i.value = t.hash; });
                            btn.disabled = true; btn.textContent = 'Enviando…';
                            fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                                .then(function (r) { return r.json().catch(function () { return { success: false, message: 'No hemos podido enviarlo. Recarga la página e inténtalo de nuevo.' }; }); })
                                .then(function (d) {
                                    msg.textContent = d.message || (d.success ? 'Enviado. Revisa tu bandeja de entrada.' : 'No hemos podido enviarlo.');
                                    msg.style.color = d.success ? '#047857' : '#b91c1c';
                                    msg.style.fontWeight = '700';
                                    if (d.success) { btn.textContent = 'Enviado ✓'; if (window.trackEvent) trackEvent('directory_quote_request', window.AE_LISTADO || {}); } else { btn.disabled = false; btn.textContent = 'Enviar'; if (window.turnstile && f.querySelector('.cf-turnstile')) { try { turnstile.reset(); } catch (e) {} } }
                                })
                                .catch(function () { msg.textContent = 'Error de conexión. Inténtalo de nuevo.'; msg.style.color = '#b91c1c'; btn.disabled = false; btn.textContent = 'Enviar'; });
                        });
                    })();
                    </script>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 16px; font-size: 0.65rem; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: -0.02em;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #f1f5f9; padding: 8px 4px; border-radius: 6px; white-space: nowrap;">
                            <span>🔒</span> Pago Seguro SSL
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #f1f5f9; padding: 8px 4px; border-radius: 6px; white-space: nowrap;">
                            <span>📄</span> Factura Incluida
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #f1f5f9; padding: 8px 4px; border-radius: 6px; white-space: nowrap;">
                            <span>🏛️</span> Datos del BORME
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px; background: #f1f5f9; padding: 8px 4px; border-radius: 6px; white-space: nowrap;">
                            <span>📥</span> Descarga Inmediata
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky CTA móvil -->
        <div class="mobile-sticky-cta">
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Pago único (IVA incl.)</span>
                <span style="font-weight: 900; font-size: 1.25rem; color: #0f172a; line-height: 1;"><?= number_format($price + $tax, 2, ',', '.') ?> €</span>
            </div>
            <button type="button" class="btn"
                style="background: #10b981; color: white; border-radius: 12px; font-weight: 800; padding: 14px 24px; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); cursor: pointer;"
                onclick="if (window.trackEvent) trackEvent('directory_checkout_click', { movil: 1 }); var f = document.querySelector('.order-card form'); if (f) f.submit();">
                Pagar y Descargar
            </button>
        </div>
    </main>

    <?= view('partials/footer') ?>

    <?php /* Embudo de compra (eventos en tracking_events; informe: php spark listados:embudo)
             y recuento de empresas con teléfono. Va después del pie: tracking.js ya está cargado. */ ?>
    <script>
    (function () {
        var L = window.AE_LISTADO = <?= json_encode([
            'provincia' => (string) $province,
            'municipio' => (string) ($municipio ?? ''),
            'cnae'      => (string) ($cnae ?? ''),
            'estado'    => (string) ($estado ?? ''),
            'telefono'  => !empty($has_phone) ? 1 : 0,
            'total'     => (int) $total_count,
            'precio'    => (float) $price,
            'muestra'   => isset($preview) ? count($preview) : 0,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
        if (window.trackEvent) trackEvent('directory_summary_view', L);

        var box = document.getElementById('phoneBox');
        if (!box || !window.fetch) return;
        var q = new URLSearchParams(<?= json_encode(array_filter([
            'provincia' => (string) $province, 'municipio' => (string) ($municipio ?? ''), 'cnae' => (string) ($cnae ?? ''),
            'cnae_text' => (string) ($cnae_text ?? ''), 'sector' => (string) ($sector ?? ''), 'estado' => (string) ($estado ?? ''),
            'date_min' => (string) ($date_min ?? ''), 'date_max' => (string) ($date_max ?? ''),
        ], static fn ($v) => $v !== ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);
        fetch('<?= site_url('billing/recuento-telefono') ?>?' + q.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d || !d.success || !d.total) return;
                // Miles con punto, como el resto de la página (Intl en español no agrupa los números de 4 cifras)
                var n = d.con_telefono, f = { format: function (x) { return String(x).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } };
                document.getElementById('phoneCount').textContent = f.format(n);
                document.getElementById('phonePct').textContent = (n * 100 / d.total < 1 && n > 0 ? '<1' : Math.round(n * 100 / d.total)) + ' %';
                var oferta = document.getElementById('phoneOffer');
                // La oferta solo si hay alguna con teléfono y no son todas
                if (n > 0 && n < d.total && d.precio) {
                    document.getElementById('phoneCount2').textContent = f.format(n);
                    document.getElementById('phonePrice').textContent = f.format(d.precio);
                    oferta.href = d.url;
                    oferta.addEventListener('click', function () { if (window.trackEvent) trackEvent('directory_phone_offer_click', { provincia: L.provincia, con_telefono: n, precio: d.precio }); });
                } else {
                    oferta.hidden = true;
                }
                box.hidden = false;
            })
            .catch(function () {});
    })();
    </script>
</body>
</html>
