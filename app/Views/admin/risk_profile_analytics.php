<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>

<?= $this->section('styles') ?>
    <style>
        :root {
            --kpi-amber: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --kpi-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --kpi-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        }

        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .kpi-card { 
            position: relative;
            overflow: hidden;
            background: white; 
            border-radius: 24px; 
            padding: 2rem; 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px;
            background: var(--kpi-color); opacity: 0.05; border-radius: 0 0 0 100%; pointer-events: none;
        }
        .kpi-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px; border-radius: 14px; background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            color: white; box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-trend-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.28rem 0.65rem;
            border-radius: 9999px;
            letter-spacing: 0.01em;
            line-height: 1;
        }
        .kpi-trend-badge svg { width: 13px; height: 13px; stroke-width: 2.5; }
        .kpi-trend-badge.trend-up {
            background-color: #ecfdf5; color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .kpi-trend-badge.trend-down {
            background-color: #fef2f2; color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .kpi-trend-badge.trend-neutral {
            background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.4rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }

        .analytics-grid-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        @media (max-width: 1024px) {
            .analytics-grid-two { grid-template-columns: 1fr; }
        }

        /* Funnel Styles */
        .funnel-step {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #f1f5f9;
            transition: all 0.2s;
        }
        .funnel-step:hover {
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            border-color: #e2e8f0;
        }
        .funnel-bar-wrapper {
            flex: 1;
            height: 10px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            position: relative;
        }
        .funnel-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Consumption Meter */
        .usage-dots {
            display: inline-flex;
            gap: 4px;
            align-items: center;
        }
        .usage-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #e2e8f0;
        }
        .usage-dot.filled { background: #3b82f6; }
        .usage-dot.filled-warning { background: #f59e0b; }
        .usage-dot.filled-danger { background: #ef4444; }

        .pill { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; text-align: center; }

        /* Detail Accordion */
        details summary {
            cursor: pointer;
            user-select: none;
            outline: none;
        }
        details summary::-webkit-details-marker {
            display: none;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.25rem;">
                <span style="font-size: 2rem;">🛡️</span>
                <h1 class="title" style="margin: 0;">Perfil de Riesgo & Solvencia</h1>
            </div>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">Panel de control de adquisición, activación de consultas y conversión a <strong>Solvencia Pro (29€/mes)</strong></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="<?= site_url('admin/users?signup_intent=view_risk_profile') ?>" class="btn ghost">Ver en Usuarios</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert success" style="background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; border-radius: 14px; padding: 14px 20px; margin-bottom: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert error" style="background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; border-radius: 14px; padding: 14px 20px; margin-bottom: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Selector de Periodo -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; font-weight: 700; color: #475569; margin-right: 6px;">📅 Periodo de análisis:</span>
            
            <a href="<?= site_url('admin/risk-profile?period=this_month&status_filter=' . $user_status_filter) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'this_month' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Este mes
            </a>
            <a href="<?= site_url('admin/risk-profile?period=last_month&status_filter=' . $user_status_filter) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'last_month' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Mes anterior
            </a>
            <a href="<?= site_url('admin/risk-profile?period=last_30d&status_filter=' . $user_status_filter) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'last_30d' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Últimos 30 días
            </a>
            <a href="<?= site_url('admin/risk-profile?period=this_year&status_filter=' . $user_status_filter) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'this_year' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Año <?= date('Y') ?>
            </a>
            <a href="<?= site_url('admin/risk-profile?period=all&status_filter=' . $user_status_filter) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'all' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Todo el histórico
            </a>
        </div>

        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
            Mostrando datos de: <span style="color: #1e293b; font-weight: 800;"><?= esc($period_label) ?></span>
        </div>
    </div>

    <!-- KPIs Ejecutivos -->
    <div class="kpi-grid">
        <!-- 1. Adquisición -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <?php $trendNew = $stats['trend_new_users']; ?>
                <span class="kpi-trend-badge trend-<?= $trendNew['direction'] ?>" title="Variación de altas respecto al periodo anterior">
                    <?php if ($trendNew['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trendNew['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trendNew['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Usuarios Registrados</span>
            <span class="kpi-value"><?= number_format($stats['total_users'], 0, ',', '.') ?></span>
            <span class="kpi-sub">+<?= number_format($stats['new_users'], 0, ',', '.') ?> registrados en <?= esc($period_label) ?></span>
        </div>

        <!-- 2. Tasa de Activación -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-emerald);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
                <span class="kpi-trend-badge trend-up">
                    <?= $stats['activation_rate'] ?>% Activación
                </span>
            </div>
            <span class="kpi-label">Activados (≥1 Consulta)</span>
            <span class="kpi-value"><?= $stats['activated_users'] ?> <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 500;">/ <?= $stats['total_users'] ?></span></span>
            <span class="kpi-sub"><?= $stats['inactive_users'] ?> registrados no han consultado aún</span>
        </div>

        <!-- 3. Consultas Realizadas -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <?php $trendViews = $stats['trend_views']; ?>
                <span class="kpi-trend-badge trend-<?= $trendViews['direction'] ?>" title="Variación de consultas respecto al periodo anterior">
                    <?php if ($trendViews['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trendViews['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trendViews['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Consultas de Riesgo</span>
            <span class="kpi-value"><?= number_format($stats['total_views'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Auditorías ejecutadas en el periodo</span>
        </div>

        <!-- 4. Clientes de Pago y MRR -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-amber);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <span class="kpi-trend-badge trend-up">
                    <?= $stats['conversion_rate'] ?>% Conversión
                </span>
            </div>
            <span class="kpi-label">MRR Solvencia Pro</span>
            <span class="kpi-value"><?= number_format($stats['mrr_risk'], 0, ',', '.') ?> €<span style="font-size: 1rem; color: #94a3b8; font-weight: 500;">/mes</span></span>
            <span class="kpi-sub">👑 <?= $stats['paid_subscribers'] ?> cliente(s) activo(s) &bull; <?= $stats['hot_leads_count'] ?> en paywall (3/3)</span>
        </div>
    </div>

    <!-- Sección Intermedia: Embudo de Conversión & Top Empresas -->
    <div class="analytics-grid-two">
        
        <!-- Embudo de Activación y Conversión (Funnel) -->
        <div class="card" style="padding: 1.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 1.15rem; color: #0f172a; font-weight: 800;">Embudo de Activación (Funnel)</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Comportamiento desde el registro hasta el choque con el paywall (3 consultas) y pago</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($funnel as $step): ?>
                    <div class="funnel-step">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: <?= $step['color'] ?>15; color: <?= $step['color'] ?>; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                            <?= $step['step'] ?>
                        </div>

                        <div style="flex: 2; min-width: 150px;">
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;"><?= esc($step['name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= esc($step['desc']) ?></div>
                        </div>

                        <div class="funnel-bar-wrapper">
                            <div class="funnel-bar-fill" style="width: <?= max(4, $step['pct_total']) ?>%; background: <?= $step['color'] ?>;"></div>
                        </div>

                        <div style="text-align: right; min-width: 75px;">
                            <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;"><?= $step['count'] ?></div>
                            <div style="font-size: 0.75rem; font-weight: 700; color: <?= $step['color'] ?>;"><?= $step['pct_total'] ?>%</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 1.25rem; padding: 0.85rem 1rem; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; font-size: 0.82rem; color: #92400e; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.2rem;">💡</span>
                <div>
                    <strong>Oportunidad de Negocio:</strong> Los usuarios en el paso 4 (<strong><?= $stats['hot_leads_count'] ?> usuarios</strong>) han agotado el límite mensual gratuito de 3 empresas y son los candidatos ideales para envío automático o contacto comercial con oferta de <strong>Solvencia Pro</strong>.
                </div>
            </div>
        </div>

        <!-- Top Empresas Consultadas -->
        <div class="card" style="padding: 1.75rem; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 1.15rem; color: #0f172a; font-weight: 800;">Empresas Más Investigadas</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: #64748b;">CIFs y sociedades con mayor volumen de consultas de solvencia/riesgo</p>
                </div>
            </div>

            <?php if (empty($top_companies)): ?>
                <div style="text-align: center; padding: 40px 20px; color: #94a3b8; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div style="font-size: 1.5rem; margin-bottom: 8px;">🏢</div>
                    <div>No hay consultas registradas en este periodo.</div>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; flex: 1;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b;">
                                <th style="padding: 8px 10px; font-weight: 700;">#</th>
                                <th style="padding: 8px 10px; font-weight: 700;">Empresa / CIF</th>
                                <th style="padding: 8px 10px; font-weight: 700;">Provincia</th>
                                <th style="padding: 8px 10px; font-weight: 700; text-align: center;">Consultas</th>
                                <th style="padding: 8px 10px; font-weight: 700; text-align: center;">Usuarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_companies as $idx => $comp): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 10px; font-weight: 800; color: #94a3b8; width: 25px;">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td style="padding: 10px;">
                                        <div style="font-weight: 700; color: #1e293b;"><?= esc($comp['company_name'] ?: 'Empresa ' . $comp['cif']) ?></div>
                                        <div style="font-family: monospace; font-size: 0.75rem; color: #64748b;"><?= esc($comp['cif']) ?></div>
                                    </td>
                                    <td style="padding: 10px; color: #64748b; font-size: 0.8rem;">
                                        <?= esc($comp['province'] ?: 'N/D') ?>
                                    </td>
                                    <td style="padding: 10px; text-align: center;">
                                        <span class="pill" style="background: #eff6ff; color: #2563eb; font-weight: 800; border: 1px solid #bfdbfe;">
                                            <?= $comp['total_views'] ?>
                                        </span>
                                    </td>
                                    <td style="padding: 10px; text-align: center; color: #475569; font-weight: 700;">
                                        <?= $comp['unique_users'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Panel de Usuarios con Filtro de Segmentación -->
    <div class="card" style="padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.25rem; color: #0f172a; font-weight: 800;">Usuarios de Perfil de Riesgo</h3>
                <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Seguimiento individual de consumo mensual (límite de 3 consultas) y estado de monetización</p>
            </div>

            <!-- Buscador dentro de la tabla -->
            <form action="<?= site_url('admin/risk-profile') ?>" method="get" style="display: flex; gap: 8px;">
                <input type="hidden" name="period" value="<?= esc($period) ?>">
                <input type="hidden" name="status_filter" value="<?= esc($user_status_filter) ?>">
                <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Buscar por nombre, email..." class="input" style="padding: 6px 12px; font-size: 0.85rem; width: 220px;">
                <button type="submit" class="btn primary" style="padding: 6px 14px; font-size: 0.85rem;">Buscar</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= site_url('admin/risk-profile?period=' . $period . '&status_filter=' . $user_status_filter) ?>" class="btn ghost" style="padding: 6px 10px;">🔄</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Píldoras de Segmentación -->
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; align-items: center;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-right: 4px;">Segmento:</span>

            <!-- Todos -->
            <a href="<?= site_url('admin/risk-profile?period=' . $period . '&status_filter=all&q=' . urlencode($search)) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 13px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $user_status_filter === 'all' ? 'background: #0f172a; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Todos (<?= $stats['total_users'] ?>)
            </a>

            <!-- Hot Leads: En el límite 3/3 -->
            <a href="<?= site_url('admin/risk-profile?period=' . $period . '&status_filter=limit_reached&q=' . urlencode($search)) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 13px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $user_status_filter === 'limit_reached' ? 'background: #dc2626; color: white;' : 'background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;' ?>">
               🚨 En el Límite 3/3 (Hot Leads) (<?= $stats['count_limit_reached'] ?>)
            </a>

            <!-- Activos 1-2 -->
            <a href="<?= site_url('admin/risk-profile?period=' . $period . '&status_filter=active_free&q=' . urlencode($search)) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 13px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $user_status_filter === 'active_free' ? 'background: #d97706; color: white;' : 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;' ?>">
               ⚡ Activos Free (1-2) (<?= $stats['count_active_free'] ?>)
            </a>

            <!-- Inactivos 0 -->
            <a href="<?= site_url('admin/risk-profile?period=' . $period . '&status_filter=inactive&q=' . urlencode($search)) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 13px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $user_status_filter === 'inactive' ? 'background: #64748b; color: white;' : 'background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;' ?>">
               💤 Sin consultas (0) (<?= $stats['count_inactive'] ?>)
            </a>

            <!-- Suscriptores de Pago -->
            <a href="<?= site_url('admin/risk-profile?period=' . $period . '&status_filter=paid&q=' . urlencode($search)) ?>" 
               class="pill" style="text-decoration: none; padding: 6px 13px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $user_status_filter === 'paid' ? 'background: #059669; color: white;' : 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' ?>">
               👑 Solvencia Pro (Pago) (<?= $stats['count_paid'] ?>)
            </a>
        </div>

        <!-- Barra de Acciones Masivas (Email & Selección) -->
        <div id="bulkActionBar" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 16px; padding: 12px 18px; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                    <span id="selectedCountBadge" style="background: #2563eb; color: white; padding: 2px 8px; border-radius: 99px; font-size: 0.78rem;">0</span> seleccionados
                </span>

                <button type="button" id="btnSelectAllVisible" class="btn ghost" style="padding: 4px 10px; font-size: 0.75rem;">
                    Seleccionar visibles (<?= count($users) ?>)
                </button>

                <?php if ($stats['count_limit_reached'] > 0): ?>
                    <button type="button" id="btnSelectHotLeads" class="btn ghost" style="padding: 4px 10px; font-size: 0.75rem; color: #dc2626; border-color: #fca5a5; background: #fef2f2; font-weight: 700;">
                        🚨 Seleccionar Hot Leads (3/3)
                    </button>
                <?php endif; ?>

                <button type="button" id="btnClearSelection" class="btn ghost" style="padding: 4px 10px; font-size: 0.75rem; display: none;">
                    Limpiar selección
                </button>
            </div>

            <div>
                <button type="button" id="btnOpenBulkModal" class="btn" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; padding: 8px 18px; font-size: 0.85rem; font-weight: 700; border-radius: 10px; opacity: 0.5; pointer-events: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    <span>Enviar Email Masivo</span>
                </button>
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 950px; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; text-align: left; color: #64748b;">
                        <th style="padding: 12px; width: 38px; text-align: center;">
                            <input type="checkbox" id="selectAllCheckbox" title="Seleccionar todos" style="cursor: pointer; width: 16px; height: 16px; accent-color: #2563eb;">
                        </th>
                        <th style="padding: 12px; font-weight: 700;">Usuario</th>
                        <th style="padding: 12px; font-weight: 700; text-align: center;">Consumo Este Mes</th>
                        <th style="padding: 12px; font-weight: 700; text-align: center;">Histórico</th>
                        <th style="padding: 12px; font-weight: 700;">Última Empresa Auditada</th>
                        <th style="padding: 12px; font-weight: 700;">Estado Plan</th>
                        <th style="padding: 12px; font-weight: 700; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                No se encontraron usuarios en este segmento o con estos criterios.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.15s ease;" data-user-id="<?= $u['id'] ?>" data-status="<?= $u['status_type'] ?>">
                                <!-- Checkbox Selección -->
                                <td style="padding: 14px 12px; text-align: center;">
                                    <input type="checkbox" class="user-row-checkbox" value="<?= $u['id'] ?>" 
                                           data-name="<?= esc($u['name'] ?: 'cliente') ?>" 
                                           data-email="<?= esc($u['email']) ?>" 
                                           data-status="<?= $u['status_type'] ?>" 
                                           data-views="<?= $u['month_views'] ?>" 
                                           style="cursor: pointer; width: 16px; height: 16px; accent-color: #2563eb;">
                                </td>

                                <!-- Usuario -->
                                <td style="padding: 14px 12px;">
                                    <div style="font-weight: 700; color: #0f172a;"><?= esc($u['name'] ?: 'Sin nombre') ?></div>
                                    <div style="font-size: 0.78rem; color: #64748b;"><?= esc($u['email']) ?></div>
                                    <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 3px;">
                                        Registrado: <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                    </div>
                                </td>

                                <!-- Consumo este mes -->
                                <td style="padding: 14px 12px; text-align: center;">
                                    <?php if ($u['is_paid']): ?>
                                        <span class="pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: 800;">
                                             👑 Ilimitado
                                        </span>
                                    <?php else: ?>
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                            <div class="usage-dots">
                                                <div class="usage-dot <?= $u['month_views'] >= 1 ? ($u['month_views'] >= 3 ? 'filled-danger' : 'filled') : '' ?>"></div>
                                                <div class="usage-dot <?= $u['month_views'] >= 2 ? ($u['month_views'] >= 3 ? 'filled-danger' : 'filled') : '' ?>"></div>
                                                <div class="usage-dot <?= $u['month_views'] >= 3 ? 'filled-danger' : '' ?>"></div>
                                            </div>
                                            <span style="font-size: 0.78rem; font-weight: 800; color: <?= $u['month_views'] >= 3 ? '#dc2626' : '#475569' ?>;">
                                                <?= $u['month_views'] ?> / 3
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Histórico -->
                                <td style="padding: 14px 12px; text-align: center;">
                                    <strong style="color: #1e293b; font-size: 0.95rem;"><?= $u['total_views'] ?></strong>
                                    <div style="font-size: 0.72rem; color: #94a3b8;">consultas</div>
                                </td>

                                <!-- Última Empresa Auditada -->
                                <td style="padding: 14px 12px;">
                                    <?php if (!empty($u['last_view'])): ?>
                                        <div style="font-weight: 600; color: #1e293b; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= esc($u['last_view']['company_name'] ?: 'Empresa ' . $u['last_view']['cif']) ?>
                                        </div>
                                        <div style="display: flex; gap: 8px; align-items: center; font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                            <span style="font-family: monospace;"><?= esc($u['last_view']['cif']) ?></span>
                                            <span>&bull;</span>
                                            <span><?= date('d/m/Y H:i', strtotime($u['last_view']['created_at'])) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 0.8rem; font-style: italic;">Sin consultas</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado Plan -->
                                <td style="padding: 14px 12px;">
                                    <?php if ($u['status_type'] === 'paid'): ?>
                                        <span class="pill" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 800;">
                                            👑 Solvencia Pro
                                        </span>
                                    <?php elseif ($u['status_type'] === 'limit_reached'): ?>
                                        <span class="pill" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 800;" title="Ha alcanzado las 3 consultas gratuitas este mes">
                                            🚨 Paywall (3/3)
                                        </span>
                                    <?php elseif ($u['status_type'] === 'active_free'): ?>
                                        <span class="pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 700;">
                                            Activo Free
                                        </span>
                                    <?php else: ?>
                                        <span class="pill" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600;">
                                            Inactivo (0)
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Acciones & Desglose -->
                                <td style="padding: 14px 12px; text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 6px; align-items: center;">
                                        <!-- Botón Enviar Email -->
                                        <button type="button" class="btn ghost btn-open-single-email" 
                                                data-id="<?= $u['id'] ?>" 
                                                data-name="<?= esc($u['name'] ?: 'cliente') ?>" 
                                                data-email="<?= esc($u['email']) ?>" 
                                                data-status="<?= $u['status_type'] ?>" 
                                                data-views="<?= $u['month_views'] ?>"
                                                style="padding: 5px 10px; font-size: 0.75rem; color: #2563eb; border-color: #bfdbfe; background: #eff6ff; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" 
                                                title="Enviar correo a este usuario">
                                            <span>📧</span>
                                            <span>Email</span>
                                        </button>

                                        <?php if (!empty($u['history'])): ?>
                                            <details style="position: relative; display: inline-block;">
                                                <summary class="btn ghost" style="padding: 5px 10px; font-size: 0.75rem;">
                                                    CIFs (<?= count($u['history']) ?>)
                                                </summary>
                                                <div style="position: absolute; right: 0; z-index: 50; background: white; border-radius: 12px; padding: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; min-width: 250px; text-align: left; margin-top: 6px;">
                                                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">Últimas Consultas:</div>
                                                    <?php foreach ($u['history'] as $h): ?>
                                                        <div style="padding: 4px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.78rem;">
                                                            <div style="font-weight: 600; color: #1e293b;"><?= esc($h['company_name'] ?: 'Empresa ' . $h['cif']) ?></div>
                                                            <div style="color: #64748b; font-size: 0.7rem; display: flex; justify-content: space-between;">
                                                                <span style="font-family: monospace;"><?= esc($h['cif']) ?></span>
                                                                <span><?= date('d/m H:i', strtotime($h['created_at'])) ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        <?php endif; ?>

                                        <a href="<?= site_url('admin/users?q=' . urlencode($u['email'])) ?>" class="btn ghost" style="padding: 5px 10px; font-size: 0.75rem;" title="Ver usuario en Gestión de Usuarios">
                                            Ficha ↗
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Enviar Email Individual -->
    <div id="modalSingleEmail" class="rp-modal-backdrop" style="display: none;">
        <div class="rp-modal-box">
            <div class="rp-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">📧</span>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Enviar Email a Usuario</h3>
                </div>
                <button type="button" class="rp-modal-close" id="btnCloseSingleModal">&times;</button>
            </div>

            <div class="rp-modal-recipient" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; margin: 16px 0;">
                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Destinatario:</div>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <strong id="singleRecipientName" style="color: #0f172a; font-size: 0.95rem;"></strong>
                        <span id="singleRecipientEmail" style="color: #64748b; font-size: 0.85rem; margin-left: 6px;"></span>
                    </div>
                    <span id="singleRecipientBadge" class="pill" style="font-size: 0.75rem; font-weight: 800;"></span>
                </div>
            </div>

            <form action="<?= site_url('admin/risk-profile/email-single') ?>" method="post" id="formSingleEmail" hx-boost="false">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" id="singleUserId" value="">

                <!-- Selector de Plantilla -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Cargar Plantilla Estratégica:
                    </label>
                    <select id="singleTemplateSelect" class="input" style="width: 100%; padding: 8px 12px; font-size: 0.85rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff;">
                        <option value="custom">✏️ Redactar mensaje personalizado en blanco</option>
                        <?php foreach ($email_templates as $tmpl): ?>
                            <option value="<?= esc($tmpl['id']) ?>"><?= esc($tmpl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Asunto -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Asunto del Email: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="subject" id="singleSubject" required class="input" style="width: 100%; padding: 9px 12px; font-size: 0.9rem; border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="Asunto del correo...">
                </div>

                <!-- Chips de variables dinámicas -->
                <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Variables disponibles:</span>
                    <button type="button" class="var-chip" onclick="insertVariable('singleMessage', '{NOMBRE}')">{NOMBRE}</button>
                    <button type="button" class="var-chip" onclick="insertVariable('singleMessage', '{EMPRESA}')">{EMPRESA}</button>
                    <button type="button" class="var-chip" onclick="insertVariable('singleMessage', '{SITE_URL}')">{SITE_URL}</button>
                </div>

                <!-- Mensaje -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Cuerpo del Mensaje: <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="message" id="singleMessage" rows="7" required class="input" style="width: 100%; padding: 10px 12px; font-size: 0.88rem; line-height: 1.5; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit;" placeholder="Escribe aquí el contenido del correo..."></textarea>
                </div>

                <div class="rp-modal-footer">
                    <button type="button" class="btn ghost" id="btnCancelSingleModal">Cancelar</button>
                    <button type="submit" class="btn primary" style="padding: 9px 22px; font-weight: 800; border-radius: 10px;">
                        📧 Enviar Email Ahora
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Enviar Email Masivo -->
    <div id="modalBulkEmail" class="rp-modal-backdrop" style="display: none;">
        <div class="rp-modal-box">
            <div class="rp-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">🚀</span>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Enviar Campaña de Email Masiva</h3>
                </div>
                <button type="button" class="rp-modal-close" id="btnCloseBulkModal">&times;</button>
            </div>

            <div class="rp-modal-recipient" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 18px; margin: 16px 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <strong style="color: #1e40af; font-size: 0.95rem;">
                        Destinatarios seleccionados: <span id="bulkModalCountBadge" style="background: #2563eb; color: white; padding: 2px 8px; border-radius: 99px; font-size: 0.78rem;">0</span>
                    </strong>
                    <span style="font-size: 0.75rem; color: #64748b;">(Se omitirán automáticamente las bajas voluntarias)</span>
                </div>
                <div id="bulkRecipientsPreview" style="font-size: 0.8rem; color: #475569; max-height: 48px; overflow-y: auto; line-height: 1.4;">
                </div>
            </div>

            <form action="<?= site_url('admin/risk-profile/email-bulk') ?>" method="post" id="formBulkEmail" hx-boost="false">
                <?= csrf_field() ?>
                <input type="hidden" name="user_ids" id="bulkUserIdsInput" value="">

                <!-- Selector de Plantilla -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Cargar Plantilla Estratégica:
                    </label>
                    <select id="bulkTemplateSelect" class="input" style="width: 100%; padding: 8px 12px; font-size: 0.85rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff;">
                        <option value="custom">✏️ Redactar mensaje personalizado en blanco</option>
                        <?php foreach ($email_templates as $tmpl): ?>
                            <option value="<?= esc($tmpl['id']) ?>"><?= esc($tmpl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Asunto -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Asunto del Email: <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="subject" id="bulkSubject" required class="input" style="width: 100%; padding: 9px 12px; font-size: 0.9rem; border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="Asunto del correo masivo...">
                </div>

                <!-- Chips de variables dinámicas -->
                <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Variables disponibles:</span>
                    <button type="button" class="var-chip" onclick="insertVariable('bulkMessage', '{NOMBRE}')">{NOMBRE}</button>
                    <button type="button" class="var-chip" onclick="insertVariable('bulkMessage', '{EMPRESA}')">{EMPRESA}</button>
                    <button type="button" class="var-chip" onclick="insertVariable('bulkMessage', '{SITE_URL}')">{SITE_URL}</button>
                </div>

                <!-- Mensaje -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Cuerpo del Mensaje: <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="message" id="bulkMessage" rows="7" required class="input" style="width: 100%; padding: 10px 12px; font-size: 0.88rem; line-height: 1.5; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit;" placeholder="Escribe el mensaje para la campaña masiva..."></textarea>
                </div>

                <div class="rp-modal-footer">
                    <button type="button" class="btn ghost" id="btnCancelBulkModal">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; padding: 9px 24px; font-weight: 800; border-radius: 10px; cursor: pointer;">
                        🚀 Enviar a (<span id="bulkSubmitCount">0</span>) usuarios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Estilos de Modales y Chips -->
    <style>
        .rp-modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: rpFadeIn 0.2s ease-out;
        }
        @keyframes rpFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .rp-modal-box {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 620px;
            padding: 24px 28px;
            border: 1px solid #e2e8f0;
            max-height: 90vh;
            overflow-y: auto;
        }
        .rp-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .rp-modal-close {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 1.4rem;
            line-height: 1;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .rp-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .rp-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }
        .var-chip {
            background: #f1f5f9;
            color: #2563eb;
            font-family: monospace;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .var-chip:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }
    </style>

    <!-- JavaScript para Selección y Modales -->
    <script>
    (function() {
        // Plantillas precargadas desde PHP
        const emailTemplates = <?= json_encode($email_templates ?? []) ?>;

        // Elementos UI
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const userRowCheckboxes = document.querySelectorAll('.user-row-checkbox');
        const selectedCountBadge = document.getElementById('selectedCountBadge');
        const btnOpenBulkModal = document.getElementById('btnOpenBulkModal');
        const btnClearSelection = document.getElementById('btnClearSelection');
        const btnSelectAllVisible = document.getElementById('btnSelectAllVisible');
        const btnSelectHotLeads = document.getElementById('btnSelectHotLeads');

        // Modal Individual
        const modalSingle = document.getElementById('modalSingleEmail');
        const btnCloseSingle = document.getElementById('btnCloseSingleModal');
        const btnCancelSingle = document.getElementById('btnCancelSingleModal');
        const singleUserId = document.getElementById('singleUserId');
        const singleRecipientName = document.getElementById('singleRecipientName');
        const singleRecipientEmail = document.getElementById('singleRecipientEmail');
        const singleRecipientBadge = document.getElementById('singleRecipientBadge');
        const singleTemplateSelect = document.getElementById('singleTemplateSelect');
        const singleSubject = document.getElementById('singleSubject');
        const singleMessage = document.getElementById('singleMessage');

        // Modal Masivo
        const modalBulk = document.getElementById('modalBulkEmail');
        const btnCloseBulk = document.getElementById('btnCloseBulkModal');
        const btnCancelBulk = document.getElementById('btnCancelBulkModal');
        const bulkUserIdsInput = document.getElementById('bulkUserIdsInput');
        const bulkModalCountBadge = document.getElementById('bulkModalCountBadge');
        const bulkSubmitCount = document.getElementById('bulkSubmitCount');
        const bulkRecipientsPreview = document.getElementById('bulkRecipientsPreview');
        const bulkTemplateSelect = document.getElementById('bulkTemplateSelect');
        const bulkSubject = document.getElementById('bulkSubject');
        const bulkMessage = document.getElementById('bulkMessage');

        // Función para actualizar estado de selección
        function updateSelectionState() {
            const checkedBoxes = document.querySelectorAll('.user-row-checkbox:checked');
            const count = checkedBoxes.length;

            selectedCountBadge.textContent = count;
            if (count > 0) {
                btnOpenBulkModal.style.opacity = '1';
                btnOpenBulkModal.style.pointerEvents = 'auto';
                btnClearSelection.style.display = 'inline-block';
            } else {
                btnOpenBulkModal.style.opacity = '0.5';
                btnOpenBulkModal.style.pointerEvents = 'none';
                btnClearSelection.style.display = 'none';
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = (count === userRowCheckboxes.length && count > 0);
            }
        }

        // Eventos en Checkboxes individuales
        userRowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectionState);
        });

        // Evento Checkbox Maestro (Header)
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                userRowCheckboxes.forEach(cb => {
                    cb.checked = isChecked;
                });
                updateSelectionState();
            });
        }

        // Botón Seleccionar Visibles
        if (btnSelectAllVisible) {
            btnSelectAllVisible.addEventListener('click', function() {
                userRowCheckboxes.forEach(cb => {
                    cb.checked = true;
                });
                updateSelectionState();
            });
        }

        // Botón Seleccionar Hot Leads (3/3)
        if (btnSelectHotLeads) {
            btnSelectHotLeads.addEventListener('click', function() {
                userRowCheckboxes.forEach(cb => {
                    cb.checked = (cb.getAttribute('data-status') === 'limit_reached');
                });
                updateSelectionState();
            });
        }

        // Botón Limpiar Selección
        if (btnClearSelection) {
            btnClearSelection.addEventListener('click', function() {
                userRowCheckboxes.forEach(cb => {
                    cb.checked = false;
                });
                updateSelectionState();
            });
        }

        // Helper para insertar variables dinámicas
        window.insertVariable = function(textareaId, variableText) {
            const textarea = document.getElementById(textareaId);
            if (!textarea) return;

            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const currentVal = textarea.value;

            textarea.value = currentVal.substring(0, startPos) + variableText + currentVal.substring(endPos);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = startPos + variableText.length;
        };

        // Buscar plantilla por ID
        function findTemplate(id) {
            return emailTemplates.find(t => t.id === id);
        }

        // Abrir Modal Individual
        document.querySelectorAll('.btn-open-single-email').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const status = this.getAttribute('data-status');
                const views = parseInt(this.getAttribute('data-views') || '0', 10);

                singleUserId.value = id;
                singleRecipientName.textContent = name;
                singleRecipientEmail.textContent = '(' + email + ')';

                // Configurar badge según estado
                if (status === 'limit_reached') {
                    singleRecipientBadge.textContent = '🚨 Límite 3/3 alcanzado';
                    singleRecipientBadge.style.background = '#fef2f2';
                    singleRecipientBadge.style.color = '#b91c1c';
                    singleRecipientBadge.style.border = '1px solid #fecaca';
                    // Auto-seleccionar plantilla de Hot Lead
                    singleTemplateSelect.value = 'hot_lead_limit';
                } else if (views === 0) {
                    singleRecipientBadge.textContent = '💤 0 consultas';
                    singleRecipientBadge.style.background = '#f8fafc';
                    singleRecipientBadge.style.color = '#64748b';
                    singleRecipientBadge.style.border = '1px solid #e2e8f0';
                    singleTemplateSelect.value = 'inactive_activation';
                } else {
                    singleRecipientBadge.textContent = views + '/3 consultas';
                    singleRecipientBadge.style.background = '#eff6ff';
                    singleRecipientBadge.style.color = '#1e40af';
                    singleRecipientBadge.style.border = '1px solid #bfdbfe';
                    singleTemplateSelect.value = 'product_update';
                }

                // Cargar contenido de la plantilla elegida
                loadTemplateIntoForm('single');

                modalSingle.style.display = 'flex';
            });
        });

        // Cambiar plantilla en Modal Individual
        singleTemplateSelect.addEventListener('change', function() {
            loadTemplateIntoForm('single');
        });

        // Cambiar plantilla en Modal Masivo
        bulkTemplateSelect.addEventListener('change', function() {
            loadTemplateIntoForm('bulk');
        });

        function loadTemplateIntoForm(type) {
            const selectEl = (type === 'single') ? singleTemplateSelect : bulkTemplateSelect;
            const subjectEl = (type === 'single') ? singleSubject : bulkSubject;
            const messageEl = (type === 'single') ? singleMessage : bulkMessage;

            const selectedId = selectEl.value;
            if (selectedId === 'custom') {
                subjectEl.value = '';
                messageEl.value = '';
            } else {
                const tmpl = findTemplate(selectedId);
                if (tmpl) {
                    subjectEl.value = tmpl.subject;
                    messageEl.value = tmpl.body;
                }
            }
        }

        // Cerrar Modal Individual
        function closeSingleModal() {
            modalSingle.style.display = 'none';
        }
        if (btnCloseSingle) btnCloseSingle.addEventListener('click', closeSingleModal);
        if (btnCancelSingle) btnCancelSingle.addEventListener('click', closeSingleModal);
        modalSingle.addEventListener('click', function(e) {
            if (e.target === modalSingle) closeSingleModal();
        });

        // Abrir Modal Masivo
        if (btnOpenBulkModal) {
            btnOpenBulkModal.addEventListener('click', function() {
                const checkedBoxes = Array.from(document.querySelectorAll('.user-row-checkbox:checked'));
                if (checkedBoxes.length === 0) return;

                const ids = checkedBoxes.map(cb => cb.value);
                bulkUserIdsInput.value = ids.join(',');

                const count = ids.length;
                bulkModalCountBadge.textContent = count;
                bulkSubmitCount.textContent = count;

                // Preview primeros correos
                const emailList = checkedBoxes.map(cb => cb.getAttribute('data-email'));
                const previewEmails = emailList.slice(0, 6).join(', ');
                const remainder = count - 6;
                bulkRecipientsPreview.innerHTML = '<strong>Destinatarios:</strong> ' + previewEmails + (remainder > 0 ? ' y <strong>+' + remainder + ' más</strong>.' : '.');

                // Si la mayoría son limit_reached, seleccionar esa plantilla por defecto
                const limitReachedCount = checkedBoxes.filter(cb => cb.getAttribute('data-status') === 'limit_reached').length;
                if (limitReachedCount >= (count / 2)) {
                    bulkTemplateSelect.value = 'hot_lead_limit';
                } else {
                    bulkTemplateSelect.value = 'product_update';
                }
                loadTemplateIntoForm('bulk');

                modalBulk.style.display = 'flex';
            });
        }

        // Cerrar Modal Masivo
        function closeBulkModal() {
            modalBulk.style.display = 'none';
        }
        if (btnCloseBulk) btnCloseBulk.addEventListener('click', closeBulkModal);
        if (btnCancelBulk) btnCancelBulk.addEventListener('click', closeBulkModal);
        modalBulk.addEventListener('click', function(e) {
            if (e.target === modalBulk) closeBulkModal();
        });

        // Loading feedback al enviar email individual
        const formSingleEmail = document.getElementById('formSingleEmail');
        if (formSingleEmail) {
            formSingleEmail.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '⏳ Enviando email...';
                }
            });
        }

        // Confirmación y loading al enviar campaña masiva
        const formBulkEmail = document.getElementById('formBulkEmail');
        if (formBulkEmail) {
            formBulkEmail.addEventListener('submit', function(e) {
                const count = bulkSubmitCount.textContent;
                if (!confirm('¿Estás seguro de enviar este correo a los ' + count + ' usuarios seleccionados?')) {
                    e.preventDefault();
                    return;
                }
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '⏳ Enviando campaña...';
                }
            });
        }
    })();
    </script>
<?= $this->endSection() ?>
