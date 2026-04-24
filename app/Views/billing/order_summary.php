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
                            Desbloquea ahora las empresas que están contratando proveedor
                        </h1>

                        <p style="font-size: 1.05rem; color: #1e293b; line-height: 1.5; margin-bottom: 8px; font-weight: 700;">
                            Estás a un paso de acceder a oportunidades activas en este momento
                        </p>

                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.5; margin-bottom: 20px; border-left: 3px solid #2563eb; padding-left: 14px; font-style: italic;">
                            Estas son las mismas oportunidades que acabas de ver — desbloquea el acceso completo para contactar antes que otros proveedores
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
                                <?php foreach (['Acceso total al Radar', 'Todos los filtros (Sector/Prov)', 'Exportación ilimitada', 'Detección diaria de leads', 'Sin permanencia', 'Cancelación en cualquier momento', 'Soporte prioritario', 'Acceso inmediato tras pago'] as $item): ?>
                                <li class="col-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $item ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div style="margin-top: 24px; padding: 16px; background: #fff1f2; border-radius: 12px; border: 1px solid #ffe4e6; color: #e11d48; font-size: 0.9rem; font-weight: 700;">
                            ⚠️ Estás viendo solo una parte — el resto de oportunidades siguen activas ahora mismo
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

                        <div class="alert-box" style="background: #f0fdf4; border-color: #bbf7d0; color: #166534; display: flex; flex-direction: column; gap: 8px; padding: 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <span style="font-weight: 800; font-size: 1rem;">Oportunidad de Negocio Estimada</span>
                            </div>
                            <p style="margin: 0; font-size: 1.25rem; font-weight: 900; letter-spacing: -0.01em;">
                                <?= number_format($total_count * 500, 0, ',', '.') ?>€ – <?= number_format($total_count * 2000, 0, ',', '.') ?>€
                            </p>
                            <p style="margin: 0; font-size: 0.8rem; opacity: 0.8; font-weight: 600;">
                                Valor potencial en ventas para estas <?= number_format($total_count, 0, ',', '.') ?> empresas.
                            </p>
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

                        <div style="background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; margin-top: 20px;">
                            <h3 style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Excel vs Radar PRO</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div style="font-size: 0.8rem;">
                                    <span style="display: block; font-weight: 800; color: #64748b; margin-bottom: 4px;">Tu Excel</span>
                                    <ul style="list-style: none; padding: 0; margin: 0; color: #64748b;">
                                        <li>✔ Descarga única</li>
                                        <li>✔ Datos estáticos</li>
                                    </ul>
                                </div>
                                <div style="font-size: 0.8rem; border-left: 1px solid #e2e8f0; padding-left: 16px;">
                                    <span style="display: block; font-weight: 800; color: #2563eb; margin-bottom: 4px;">Radar PRO</span>
                                    <ul style="list-style: none; padding: 0; margin: 0; color: #2563eb;">
                                        <li>✔ Nuevas cada día</li>
                                        <li>✔ Acceso ilimitado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="guarantee-box" style="margin-top: 24px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <p><strong>Garantía:</strong> Datos oficiales del BORME. Pago seguro 256-bit SSL gestionado por Stripe.</p>
                    </div>
                </div>

                <!-- DERECHA: RESUMEN DE PAGO -->
                <div class="order-card">
                    <h2 style="font-size: 1.1rem; font-weight: 900; margin-bottom: 16px; color: #0f172a;">Tu acceso comienza ahora</h2>

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

                    <?php if ($type === 'subscription'): ?>
                    <div style="background: #f0fdf4; border-radius: 8px; padding: 8px 12px; margin-top: 10px; display: flex; align-items: center; gap: 8px; border: 1px solid #dcfce7;">
                        <span style="font-size: 14px;">💡</span>
                        <p style="font-size: 0.75rem; color: #166534; font-weight: 800; margin: 0; line-height: 1.2;">
                            Con 1 cliente cubres el coste mensual
                        </p>
                    </div>
                    <?php endif; ?>

                    <form action="<?= site_url('billing/checkout') ?>" method="POST" style="margin-top: 24px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="radar">
                        <input type="hidden" name="period" value="<?= $type === 'subscription' ? 'monthly' : 'single' ?>">
                        <input type="hidden" name="provincia" value="<?= esc($province) ?>">
                        <input type="hidden" name="sector" value="<?= esc($sector) ?>">
                        <input type="hidden" name="cnae" value="<?= esc($cnae ?? '') ?>">
                        <input type="hidden" name="period_radar" value="<?= esc($period) ?>">

                        <div style="position: relative;">
                            <?php if ($type === 'subscription'): ?>
                                <div style="position: absolute; top: -12px; right: 12px; background: #ef4444; color: white; font-size: 10px; padding: 3px 10px; border-radius: 20px; font-weight: 900; z-index: 20; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2); text-transform: uppercase; pointer-events: none; white-space: nowrap;">
                                    Acceso en &lt; 10s
                                </div>
                            <?php endif; ?>
                            <button type="submit" class="btn js-loading-btn" style="width: 100%; padding: 18px; font-size: 1rem; font-weight: 950; background: #2563eb; color: white; border-radius: 16px; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); text-transform: uppercase; letter-spacing: 0.01em; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.2;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(37, 99, 235, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(37, 99, 235, 0.4)';">
                                <span style="font-size: 0.95rem; pointer-events: none;">Contactar antes que</span>
                                <span style="font-size: 1.1rem; letter-spacing: -0.01em; pointer-events: none;">otros proveedores</span>
                            </button>
                        </div>
                    </form>

                    <?php if ($type === 'subscription'): ?>
                    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="font-size: 16px;">⚠️</span>
                            <p style="font-size: 0.8rem; color: #b91c1c; font-weight: 800; margin: 0; line-height: 1.4;">
                                Varias de estas empresas pueden cerrar proveedor en las próximas horas
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="font-size: 16px; color: #10b981;">✔</span>
                            <p style="font-size: 0.8rem; color: #475569; font-weight: 700; margin: 0; line-height: 1.4;">
                                +1.200 profesionales ya usan este sistema cada semana
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <p style="font-size: 0.72rem; color: #94a3b8; text-align: center; margin-top: 24px; line-height: 1.5; font-weight: 500;">
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
