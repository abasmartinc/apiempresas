<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Confirmación de tu Listado - APIEmpresas',
        'excerptText' => 'Resumen de tu descarga del Radar B2B.',
    ]) ?>
    <style>
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
            border: 2px solid #eef2ff;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
        }
        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef2ff;
            color: var(--primary);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 800;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
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
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 14px;
        }
        .cols-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
        .alert-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #92400e;
            font-weight: 600;
            line-height: 1.4;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .summary-grid { grid-template-columns: 1fr; }
            .order-card { position: static; }
            .page-summary { padding: 16px 16px 32px; }
            .cols-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .main-card { padding: 18px 16px; }
            .order-card { padding: 18px 16px; }
            .cols-grid { grid-template-columns: 1fr 1fr; gap: 6px; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
            .summary-grid { gap: 16px; }
        }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main>
        <div class="page-summary">
            <div class="summary-grid">
                <!-- IZQUIERDA: DETALLES Y CONFIANZA -->
                <div class="main-card">
                    <?php if ($type === 'subscription'): ?>
                        <div class="product-badge" style="background: #fff7ed; color: #c2410c;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                            Plan Profesional · Acceso Total
                        </div>

                        <h1 style="font-size: 1.8rem; font-weight: 900; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.03em; line-height: 1.15;">
                            Radar PRO: Acceso ilimitado a nuevas empresas
                        </h1>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.5; margin-bottom: 14px;">
                            La herramienta definitiva para detectar leads B2B antes que nadie. Activa tu acceso hoy mismo.
                        </p>

                        <div class="stat-grid">
                            <div class="stat-box">
                                <div class="stat-box-label">Actualización</div>
                                <div class="stat-box-value">Diaria (BORME)</div>
                                <div class="stat-box-sub">Nuevos registros cada 24h</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Exportación</div>
                                <div class="stat-box-value">Ilimitada</div>
                                <div class="stat-box-sub">Excel y CSV sin restricciones</div>
                            </div>
                        </div>

                        <div>
                            <h3 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Ventajas incluidas en tu suscripción:</h3>
                            <ul class="cols-grid" style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach (['Acceso total al Radar', 'Todos los filtros (Sector/Prov)', 'Exportación ilimitada', 'Detección diaria de leads', 'Sin permanencia', 'Soporte prioritario'] as $item): ?>
                                <li class="col-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $item ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="product-badge">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Descarga Inmediata · Excel
                        </div>

                        <h1 style="font-size: 1.8rem; font-weight: 900; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.03em; line-height: 1.15;">
                            <?php if ($sector): ?>
                                Nuevas empresas de <?= mb_strtolower($sector) ?> en <?= ucfirst($province) ?>
                            <?php else: ?>
                                Nuevas empresas en <?= ucfirst($province) ?>
                            <?php endif; ?>
                        </h1>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.5; margin-bottom: 14px;">
                            Listado oficial BORME procesado y listo para descargar en Excel.
                        </p>

                        <div class="alert-box">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="vertical-align: -3px; margin-right: 5px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            Las empresas recién creadas suelen contratar proveedores durante sus primeros meses de actividad.
                        </div>

                        <div class="stat-grid">
                            <div class="stat-box">
                                <div class="stat-box-label">Empresas</div>
                                <div class="stat-box-value"><?= number_format($total_count ?? 0, 0, ',', '.') ?></div>
                                <div class="stat-box-sub">
                                    <?php
                                    if ($period === 'hoy') echo 'Detectadas hoy';
                                    elseif ($period === 'semana') echo 'Detectadas esta semana';
                                    elseif ($period === 'mes') echo 'Detectadas este mes';
                                    else echo 'Detectadas en los últimos 30 días';
                                    ?>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Formato</div>
                                <div class="stat-box-value">Excel .xlsx</div>
                                <div class="stat-box-sub">Descarga directa tras el pago</div>
                            </div>
                        </div>

                        <div>
                            <h3 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Columnas incluidas en el listado:</h3>
                            <ul class="cols-grid" style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach (['Nombre empresa', 'CIF', 'Sector CNAE', 'Provincia', 'Municipio', 'Fecha reg.', 'Objeto social'] as $item): ?>
                                <li class="col-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $item ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="guarantee-box" style="margin-top: 24px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <p><strong>Garantía:</strong> Datos oficiales del BORME. Pago seguro 256-bit SSL gestionado por Stripe.</p>
                    </div>
                </div>

                <!-- DERECHA: RESUMEN DE PAGO -->
                <div class="order-card">
                    <h2 style="font-size: 1.1rem; font-weight: 900; margin-bottom: 16px; color: #0f172a;">Resumen del pedido</h2>

                    <?php if ($type === 'subscription'): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748b; font-size: 0.88rem;">
                            <span>Suscripción Radar PRO (Mensual)</span>
                            <span style="font-weight: 700; color: #0f172a;"><?= number_format($price, 2, ',', '.') ?> €</span>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748b; font-size: 0.88rem;">
                            <span>Listado Excel (<?= number_format($total_count ?? 0, 0, ',', '.') ?> empresas)</span>
                            <span style="font-weight: 700; color: #0f172a;"><?= number_format($price, 2, ',', '.') ?> €</span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #64748b; font-size: 0.88rem;">
                        <span>IVA (21%)</span>
                        <span style="font-weight: 700; color: #0f172a;"><?= number_format($tax, 2, ',', '.') ?> €</span>
                    </div>

                    <div class="total-row">
                        <span>Total</span>
                        <span style="color: var(--primary);"><?= number_format($price + $tax, 2, ',', '.') ?> €</span>
                    </div>

                    <form action="<?= site_url('billing/checkout') ?>" method="POST" style="margin-top: 20px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="radar">
                        <input type="hidden" name="period" value="<?= $type === 'subscription' ? 'monthly' : 'single' ?>">
                        <input type="hidden" name="provincia" value="<?= esc($province) ?>">
                        <input type="hidden" name="sector" value="<?= esc($sector) ?>">
                        <input type="hidden" name="cnae" value="<?= esc($cnae ?? '') ?>">
                        <input type="hidden" name="period_radar" value="<?= esc($period) ?>">

                        <button type="submit" class="btn js-loading-btn" style="width: 100%; padding: 17px; font-size: 1.05rem; font-weight: 900; background: var(--primary); color: white; border-radius: 14px; border: none; cursor: pointer; box-shadow: 0 8px 16px -4px rgba(33, 82, 255, 0.4); text-transform: uppercase; letter-spacing: 0.02em; transition: transform 0.15s; display: flex; align-items: center; justify-content: center; gap: 10px;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="opacity: 0.9;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <?= $type === 'subscription' ? 'Suscribirse Ahora' : 'Confirmar y Pagar' ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="opacity: 0.8;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </form>

                    <p style="font-size: 0.72rem; color: #94a3b8; text-align: center; margin-top: 10px; line-height: 1.4;">
                        Al confirmar, serás redirigido a la pasarela segura de Stripe.
                        <?php if ($type === 'subscription'): ?>
                            <br>Suscripción mensual, cancela cuando quieras.
                        <?php endif; ?>
                    </p>

                    <div class="trust-badges">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" style="height: 18px; opacity: 0.4;">
                    </div>

                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 18px 0 14px;">

                    <div style="text-align: center;">
                        <?php if ($type === 'subscription'): ?>
                            <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">¿Necesitas solo un listado?</div>
                            <a href="<?= site_url('checkout/radar-export?type=single') ?>" style="color: var(--primary); font-weight: 800; text-decoration: none; font-size: 0.9rem;">
                                Comprar listado puntual →
                            </a>
                        <?php else: ?>
                            <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">O elige acceso total</div>
                            <a href="<?= site_url('checkout/radar-export?type=subscription') ?>" style="color: var(--primary); font-weight: 800; text-decoration: none; font-size: 0.9rem;">
                                Radar Ilimitado por 79€/mes →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
