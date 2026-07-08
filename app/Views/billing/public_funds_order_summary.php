<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Descarga Base de Datos ' . esc($display_name ?? '') . ' | APIEmpresas',
        'excerptText' => 'Descarga el listado completo de ' . number_format($total_count, 0, ',', '.') . ' registros de ' . esc($display_name ?? '') . ' en formato CSV.',
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
                        Base de Datos Enriquecida · CSV
                    </div>

                    <h1 style="font-size: 1.8rem; font-weight: 900; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.03em; line-height: 1.15;">
                        <?= number_format($total_count, 0, ',', '.') ?> registros de <?= esc($display_name ?? '') ?>
                    </h1>

                    <p style="font-size: 0.95rem; color: #64748b; line-height: 1.6; margin-bottom: 20px;">
                        Listado completo de empresas pertenecientes a <strong><?= esc($display_name ?? '') ?></strong>.
                        Datos enriquecidos cruzando la base de datos pública con el Registro Mercantil. Ideal para prospección B2B y cualificación de leads con liquidez.
                    </p>

                    <!-- Estadísticas clave -->
                    <div class="stat-grid">
                        <div class="stat-box">
                            <div class="stat-box-label">Registros incluidos</div>
                            <div class="stat-box-value"><?= number_format($total_count, 0, ',', '.') ?></div>
                            <div class="stat-box-sub">Historial completo</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-label">Formato</div>
                            <div class="stat-box-value">CSV (Delimitado por comas)</div>
                            <div class="stat-box-sub">Descarga tras el pago</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-label">Precio por empresa</div>
                            <div class="stat-box-value"><?= $total_count > 0 ? number_format($price / $total_count, 4, ',', '.') : '—' ?>€</div>
                            <div class="stat-box-sub">Coste unitario</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-label">Fuente</div>
                            <div class="stat-box-value">BOE / RM</div>
                            <div class="stat-box-sub">Datos oficiales + Enriquecidos</div>
                        </div>
                    </div>

                    <!-- Campos incluidos -->
                    <div style="background: #f8fafc; border-radius: 12px; padding: 16px 20px; border: 1px solid #e2e8f0;">
                        <h3 style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                            Campos incluidos en el CSV
                        </h3>
                        <div class="cols-grid">
                            <?php 
                            $fields = ($type === 'subsidies') ? 
                                ['Empresa', 'CIF', 'Convocatoria', 'Instrumento', 'Importe', 'Fecha Concesión', 'Teléfono ⚠️', 'Sector CNAE', 'Provincia', 'Dirección'] :
                                ['Empresa Adjudicataria', 'CIF', 'Órgano de Contratación', 'Título del Contrato', 'Importe', 'Fecha Adjudicación', 'Teléfono ⚠️', 'Sector CNAE', 'Provincia', 'Dirección'];
                            foreach ($fields as $field): ?>
                            <div class="col-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <?= $field ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Aviso NO/SI incluye teléfono -->
                    <?php if (isset($has_phone) && $has_phone == '1'): ?>
                    <div class="disclaimer-box" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                        ✅ <strong>Este listado SÍ incluye teléfono de contacto.</strong><br>
                        Has seleccionado el filtro exclusivo de empresas con teléfono. Contiene datos registrales oficiales (identificadores, razón social, actividad) y los números de teléfono recopilados (fijos y móviles).
                    </div>
                    <?php else: ?>
                    <div class="disclaimer-box">
                        ⚠️ <strong>Puede haber empresas en este listado sin teléfono.</strong><br>
                        Contiene datos registrales oficiales y teléfonos (cuando están disponibles).
                        Es perfecto para cruzar con otras fuentes, validar CIFs o analizar el tejido empresarial de una zona.
                    </div>
                    <?php endif; ?>

                    <div class="guarantee-box" style="margin-top: 16px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <p><strong>Garantía de datos:</strong> Extraídos directamente de boletines oficiales (BOE) y enriquecidos con datos del Registro Mercantil Central. Pago seguro 256-bit SSL gestionado por Stripe.</p>
                    </div>
                </div>

                <!-- DERECHA: RESUMEN DE PAGO -->
                <div class="order-card">
                    <h2 style="font-size: 1.1rem; font-weight: 900; margin-bottom: 16px; color: #0f172a;">Resumen del pedido</h2>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #64748b; font-size: 0.88rem;">
                        <span>BBDD <?= esc($display_name ?? '') ?> (<?= number_format($total_count, 0, ',', '.') ?> empresas)</span>
                        <span style="font-weight: 700; color: #0f172a;"><?= number_format($price, 2, ',', '.') ?> €</span>
                    </div>

                    <?php if ($total_count > 0 && $price > 0): ?>
                    <div style="text-align: right; font-size: 0.72rem; color: #10b981; font-weight: 800; margin-bottom: 12px;">
                        <span style="background: #ecfdf5; padding: 2px 6px; border-radius: 4px;">
                            Apenas <?= number_format($price / $total_count, 4, ',', '.') ?>€ por empresa
                        </span>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #64748b; font-size: 0.88rem;">
                        <span>IVA (21%)</span>
                        <span style="font-weight: 700; color: #0f172a;"><?= number_format($tax, 2, ',', '.') ?> €</span>
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

                    <form action="<?= site_url('billing/checkout') ?>" method="POST" style="margin-top: 24px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan"         value="<?= $type === 'subsidies' ? 'subsidies_single' : 'contracts_single' ?>">
                        <input type="hidden" name="period"       value="single">
                        <input type="hidden" name="type"         value="<?= esc($type) ?>">
                        <?php if(!empty($convocatoria)): ?>
                        <input type="hidden" name="convocatoria" value="<?= esc($convocatoria) ?>">
                        <?php endif; ?>
                        <?php if(!empty($year)): ?>
                        <input type="hidden" name="year"         value="<?= esc($year) ?>">
                        <?php endif; ?>
                        <?php if(!empty($organo)): ?>
                        <input type="hidden" name="organo"       value="<?= esc($organo) ?>">
                        <?php endif; ?>
                        <input type="hidden" name="total_count"  value="<?= (int) $total_count ?>">
                        <input type="hidden" name="price"        value="<?= $price ?>">

                        <button type="submit" class="btn js-loading-btn"
                            style="width: 100%; padding: 18px; font-size: 1rem; font-weight: 900; background: #10b981; color: white; border-radius: 16px; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35); text-transform: uppercase; letter-spacing: 0.01em; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.2;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 14px 30px rgba(16, 185, 129, 0.45)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(16, 185, 129, 0.35)';">
                            <span style="font-size: 1.1rem; letter-spacing: -0.01em; pointer-events: none;">
                                Pagar <?= number_format($price + $tax, 2, ',', '.') ?>€ y Descargar CSV
                            </span>
                        </button>
                    </form>

                    <p style="font-size: 0.72rem; color: #94a3b8; text-align: center; margin-top: 16px; line-height: 1.5; font-weight: 500;">
                        Al confirmar serás redirigido a la pasarela segura de Stripe.<br>Pago único, sin suscripción.
                    </p>

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
                onclick="document.querySelector('.order-card form').submit();">
                Pagar y Descargar
            </button>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
