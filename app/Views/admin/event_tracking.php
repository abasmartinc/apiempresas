<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>
<?= $this->section('styles') ?>

    <style>
        :root {
            --kpi-blue: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
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
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        
        .kpi-icon-wrapper {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.2rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.8rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }

        .progress-bar-container { width: 100%; height: 6px; background: #f1f5f9; border-radius: 100px; margin-top: 1rem; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: var(--kpi-color); border-radius: 100px; transition: width 1s ease-out; }

        .admin-table-wrapper { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 3rem; }
        
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .status-critico { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
        .status-alto { background: #fffbeb; color: #92400e; border: 1px solid #fef3c7; }
        .status-medio { background: #f0f9ff; color: #075985; border: 1px solid #e0f2fe; }
        .status-bajo { background: #f8fafc; color: #64748b; border: 1px solid #f1f5f9; }

        .why-tag { font-size: 0.8rem; color: #475569; font-weight: 500; display: block; margin-top: 4px; }
        .btn-action-sm { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; background: #6366f1; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-action-sm:hover { opacity: 0.9; transform: scale(1.02); }

        .momentum-up { color: #10b981; font-weight: 800; font-size: 0.75rem; }

        /* Modal Overlay Minimal */
        #contactModal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
        <div class="page-header">
            <div>
                <h1 class="title" style="color: #0f172a;">Conversión & Leads API</h1>
                <p style="color: #64748b;">Seguimiento operativo de activación y monetización</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <div style="background: white; padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                    <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);"></span>
                    <span style="font-weight: 700; font-size: 0.9rem;"><?= $activeUsers ?> usuarios activos</span>
                </div>
                <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver</a>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <span class="kpi-label">Total Usuarios</span>
                <span class="kpi-value"><?= number_format($kpis['total_registros']) ?></span>
                <span class="kpi-sub">Usuarios registrados</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                </div>
                <span class="kpi-label">Uso API (Hoy)</span>
                <span class="kpi-value"><?= number_format($kpis['total_requests_today'] ?? 0) ?></span>
                <span class="kpi-sub">Peticiones totales hoy</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a4.5 4.5 0 1 0 0 9h5a4.5 4.5 0 1 1 0 9H6"></path></svg>
                </div>
                <span class="kpi-label">Tasa Conversión</span>
                <span class="kpi-value"><?= $kpis['conversion_rate'] ?>%</span>
                <span class="kpi-sub"><?= $kpis['paid_users'] ?> usuarios de pago</span>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?= $kpis['conversion_rate'] ?>%;"></div>
                </div>
            </div>

        </div>

        <!-- New General Behavior KPIs Section -->
        <div style="margin-bottom: 3rem;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; color: #0f172a; font-weight: 800;">Comportamiento General Web (30 Días)</h2>
                <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 800;">GLOBAL</span>
            </div>

            <!-- Developer Journey Funnel -->
            <h3 style="font-size: 1rem; color: #64748b; margin-bottom: 1rem; font-weight: 700; text-transform: uppercase;">Viaje del Desarrollador</h3>
            <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; overflow-x: auto; margin-bottom: 2rem; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);">
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Copias API Key</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #1e293b;"><?= number_format($developerKpis['api_keys_copied']) ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">Veces que un dev ha copiado su clave de acceso.</div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Copias cURL</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #1e293b;"><?= number_format($developerKpis['curl_copied']) ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">Intentos de usar un código de ejemplo en su terminal.</div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #e0e7ff; border-radius: 16px; border: 1px solid #c7d2fe;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #4338ca; text-transform: uppercase;">Búsquedas Dashboard</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #3730a3;"><?= number_format($developerKpis['dashboard_searches']) ?></div>
                    <div style="font-size: 0.75rem; color: #6366f1; margin-top: 8px; opacity: 0.8;">Búsquedas de empresas reales hechas desde el panel.</div>
                </div>
            </div>

            <!-- Monetization Funnel -->
            <h3 style="font-size: 1rem; color: #64748b; margin-bottom: 1rem; font-weight: 700; text-transform: uppercase;">Intención de Compra</h3>
            <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; overflow-x: auto; margin-bottom: 2rem; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);">
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Clics Precios/Planes</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #1e293b;"><?= number_format($pricingKpis['pricing_clicks']) ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">Interés mostrado haciendo clic en botones de "Ver precios" o seleccionando un plan.</div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Vistas Checkout</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #1e293b;"><?= number_format($pricingKpis['checkout_views']) ?></div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">Usuarios que aterrizaron en la pantalla final de pago.</div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #ecfdf5; border-radius: 16px; border: 1px solid #bbf7d0;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #059669; text-transform: uppercase;">Checkouts Iniciados</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #065f46;"><?= number_format($pricingKpis['checkout_started']) ?></div>
                    <div style="font-size: 0.75rem; color: #10b981; margin-top: 8px; opacity: 0.8;">Usuarios que comenzaron a escribir sus datos de tarjeta en Stripe.</div>
                </div>
            </div>

            <!-- Other Engagement KPIs -->
            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="kpi-card" style="--kpi-color: #f59e0b; padding: 1.5rem;">
                    <span class="kpi-label">Uso de Demo (Web)</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($engagementKpis['demo_starts']) ?></span>
                    <span class="kpi-sub"><?= number_format($engagementKpis['demo_results']) ?> Resultados Mostrados</span>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 12px; line-height: 1.3;">Interacciones de visitantes buscando empresas en el buscador interactivo (demo).</div>
                </div>
                
                <div class="kpi-card" style="--kpi-color: #8b5cf6; padding: 1.5rem;">
                    <span class="kpi-label">Interacción (CTAs)</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($engagementKpis['ctas_clicked']) ?></span>
                    <span class="kpi-sub">De <?= number_format($engagementKpis['page_views']) ?> page views</span>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 12px; line-height: 1.3;">Total de clics en llamadas a la acción relevantes (botones principales, registros) en la web.</div>
                </div>

                <div class="kpi-card" style="--kpi-color: #10b981; padding: 1.5rem;">
                    <span class="kpi-label">Emails Transaccionales</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($emailKpis['engaged'] + $emailKpis['no_usage']) ?></span>
                    <span class="kpi-sub">Enviados por actividad</span>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 12px; line-height: 1.3;">Muestra los correos lanzados automáticamente para empujar la adopción o retener usuarios inactivos.</div>
                </div>
            </div>
        </div>

        <!-- Radar B2B Funnel Section -->
        <div style="margin-bottom: 3rem;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; color: #0f172a; font-weight: 800;">Embudo Radar B2B (Hoy)</h2>
                <span style="background: #eff6ff; color: #3b82f6; padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 800;">REAL-TIME</span>
            </div>

            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="kpi-card" style="--kpi-color: #3b82f6; padding: 1.5rem;">
                    <span class="kpi-label">Tráfico Landing</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($radarKpis['landing_visits']) ?></span>
                    <span class="kpi-sub">Visitas /empresas-nuevas</span>
                </div>
                
                <div class="kpi-card" style="--kpi-color: #8b5cf6; padding: 1.5rem;">
                    <span class="kpi-label">Vistas Preview</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($radarKpis['preview_views']) ?></span>
                    <span class="kpi-sub">CTR: <strong style="color:#1e293b;"><?= $radarKpis['ctr_preview'] ?>%</strong></span>
                    <div class="progress-bar-container" style="height: 4px;">
                        <div class="progress-bar-fill" style="width: <?= $radarKpis['ctr_preview'] ?>%;"></div>
                    </div>
                </div>

                <div class="kpi-card" style="--kpi-color: #10b981; padding: 1.5rem;">
                    <span class="kpi-label">Leads Capturados</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($radarKpis['email_submits']) ?></span>
                    <span class="kpi-sub">Conv: <strong style="color:#1e293b;"><?= $radarKpis['conv_rate'] ?>%</strong></span>
                    <div class="progress-bar-container" style="height: 4px;">
                        <div class="progress-bar-fill" style="width: <?= $radarKpis['conv_rate'] ?>%;"></div>
                    </div>
                </div>

                <div class="kpi-card" style="--kpi-color: #f59e0b; padding: 1.5rem;">
                    <span class="kpi-label">Excel Ventas</span>
                    <span class="kpi-value" style="font-size: 1.8rem;"><?= number_format($radarKpis['excel_sales']) ?></span>
                    <span class="kpi-sub"><?= number_format($radarKpis['excel_previews']) ?> Previews</span>
                </div>
            </div>

            <!-- Funnel Visualization (CSS Simple) -->
            <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem; overflow-x: auto; margin-bottom: 2rem;">
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Landing</div>
                    <div style="font-size: 1.2rem; font-weight: 900; color: #1e293b;"><?= number_format($radarKpis['landing_visits']) ?></div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Modal View</div>
                    <div style="font-size: 1.2rem; font-weight: 900; color: #1e293b;"><?= number_format($radarKpis['modal_views']) ?></div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 16px;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Preview</div>
                    <div style="font-size: 1.2rem; font-weight: 900; color: #1e293b;"><?= number_format($radarKpis['preview_views']) ?></div>
                </div>
                <div style="color: #cbd5e1;">➔</div>
                <div style="flex: 1; min-width: 150px; text-align: center; padding: 1rem; background: #ecfdf5; border-radius: 16px; border: 1px solid #bbf7d0;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #059669; text-transform: uppercase;">Lead (Email)</div>
                    <div style="font-size: 1.2rem; font-weight: 900; color: #065f46;"><?= number_format($radarKpis['email_submits']) ?></div>
                </div>
            </div>

            <!-- Detailed Insights & Leads -->
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <!-- Engagement Insights -->
                <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Insights de Engagement
                    </h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <p style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Triggers del Modal</p>
                        <?php foreach($radarInsights['triggers'] as $t): ?>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px;">
                                <span style="color: #475569;"><?= esc(str_replace(['"', 'click_'], '', $t['trigger_name'])) ?></span>
                                <span style="font-weight: 700;"><?= $t['total'] ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($radarInsights['triggers'])): ?>
                            <p style="font-size: 0.8rem; color: #94a3b8; font-style: italic;">Sin datos aún hoy</p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <p style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Top Provincias Buscadas</p>
                        <?php foreach($radarInsights['top_provinces'] as $p): ?>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px;">
                                <span style="color: #475569;"><?= esc(str_replace('"', '', $p['province'])) ?></span>
                                <span style="font-weight: 700;"><?= $p['total'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Latest Radar Leads -->
                <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Últimos Leads Radar B2B
                    </h3>
                    
                    <div class="table-responsive">
                        <table class="admin-table" style="font-size: 0.85rem;">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Contexto</th>
                                    <th style="text-align: right;">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($radarLeads as $l): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #1e293b;"><?= esc($l['email']) ?></td>
                                    <td style="color: #64748b;"><?= esc($l['context']) ?></td>
                                    <td style="text-align: right; color: #94a3b8;"><?= date('H:i', strtotime($l['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($radarLeads)): ?>
                                    <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 2rem;">No hay leads registrados hoy</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-table-wrapper" id="leads-table">
            <div style="padding: 2rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; color: #1e293b; font-weight: 800;">Leads Prioritarios & Tareas</h3>
                    <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">Ranking dinámico por Urgencia e Intención</p>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button id="btnBulkEmail" class="btn-action-sm" style="display:none; background: #10b981; border:none; padding: 8px 16px;" onclick="openBulkContactModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        Enviar a <span id="selectedCount">0</span> seleccionados
                    </button>
                    <span class="status-badge status-critico" style="padding: 6px 12px;">CRÍTICO: <?= $summary['ready'] ?></span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th width="40" style="text-align: center;"><input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;"></th>
                            <th width="60">#</th>
                            <th>Usuario</th>
                            <th>Motivo (Estratégico)</th>
                            <th style="text-align: center;">Uso API</th>
                            <th style="text-align: center;">Urgencia</th>
                            <th style="text-align: right;">Acción Recomendada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($userList as $idx => $u): 
                            $statusClass = strtolower(str_replace(' ', '-', $u['status_label']));
                            $reqToday = (int)($u['req_24h'] ?? 0);
                            $icon = '❄️';
                            if ($reqToday >= 5) $icon = '🔥';
                            elseif ($reqToday >= 3) $icon = '⚡';
                            elseif ($reqToday >= 1) $icon = '⚪';
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" class="user-checkbox" value="<?= $u['id'] ?>" data-email="<?= esc($u['email']) ?>" data-case="<?= $u['case_type'] ?>" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
                            <td style="font-weight: 900; color: #cbd5e1; font-size: 1.2rem;">
                                <?= $icon ?>
                                #<?= $idx + 1 ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="color: #0f172a; font-weight: 700;"><?= esc($u['email']) ?></span>
                                    <span style="font-size: 0.75rem; color: #94a3b8;"><?= $u['plan_name'] ?: 'Plan Free' ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="why-tag" style="<?= $u['urgency_score'] >= 75 ? 'color: #ef4444; font-weight: 700;' : 'color: #334155; font-weight: 600;' ?>">
                                    <?= $u['why'] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <span style="font-weight: 800; font-size: 1rem; color: #1e293b;">Total: <?= $u['total_requests'] ?></span>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #6366f1;">Hoy: <?= $u['req_24h'] ?></span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge status-<?= $statusClass ?>">
                                    <?= $u['status_label'] ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn-action-sm" onclick="openContactModal('<?= $u['id'] ?>', '<?= esc($u['email']) ?>', '<?= esc($u['why']) ?>', '<?= $u['case_type'] ?>')">
                                    <?= $u['recommended_action'] ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h3 style="margin: 0; font-size: 1rem; color: #1e293b; font-weight: 800;">🛠️ Alertas Técnicas / Bloqueos Onboarding</h3>
            </div>
            <table class="admin-table">
                <tbody>
                    <?php foreach($problematicUsers as $u): ?>
                    <tr>
                        <td style="text-align: center; width: 40px;">
                            <input type="checkbox" class="user-checkbox" value="<?= $u['id'] ?>" data-email="<?= esc($u['email']) ?>" data-case="onboarding" style="width: 18px; height: 18px; cursor: pointer;">
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($u['email']) ?></div>
                            <div style="font-size: 0.7rem; color: #94a3b8;">ID #<?= $u['id'] ?></div>
                        </td>
                        <td style="font-size: 0.8rem; color: #475569; font-weight: 600;"><?= $u['why'] ?></td>
                        <td style="text-align: right;">
                            <button onclick="openContactModal('<?= $u['id'] ?>', '<?= esc($u['email']) ?>', '<?= esc($u['why']) ?>', 'onboarding')" style="background: none; border: none; color: #6366f1; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; cursor: pointer;">Soporte</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Modal Requerido por el Usuario -->
    <div id="contactModal">
        <div class="modal-box">
            <h3 style="margin-top:0;">Contactar usuario</h3>
            <p><strong>Email:</strong> <span id="modalUserEmail"></span></p>
            <p><strong>Motivo:</strong> <span id="modalReason"></span></p>

            <input type="text" id="modalSubject" placeholder="Asunto del correo" style="width:100%; padding:10px; border-radius:10px; border:1px solid #e2e8f0; margin-top:10px;" value="Novedades sobre tu acceso a la API">
            <textarea id="modalMessage" style="width:100%; height:150px; padding:10px; border-radius:10px; border:1px solid #e2e8f0; margin-top:10px;"></textarea>

            <br><br>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button onclick="closeModal()" style="padding:10px 20px; border-radius:8px; border:none; background:#f1f5f9; cursor:pointer;">Cancelar</button>
                <button onclick="sendMessage()" id="btnSend" style="padding:10px 20px; border-radius:8px; border:none; background:#6366f1; color:white; cursor:pointer;">Enviar Mensaje</button>
            </div>
        </div>
    </div>

    
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });

        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkButton);
        });

        function updateBulkButton() {
            const selected = document.querySelectorAll('.user-checkbox:checked');
            const btn = document.getElementById('btnBulkEmail');
            const countSpan = document.getElementById('selectedCount');
            
            if (selected.length > 0) {
                btn.style.display = 'inline-block';
                countSpan.innerText = selected.length;
            } else {
                btn.style.display = 'none';
            }
        }

        function openContactModal(userId, email, reason, caseType) {
            const modal = document.getElementById('contactModal');
            document.getElementById('modalUserEmail').innerText = email;
            document.getElementById('modalReason').innerText = reason;

            const message = generateMessage(caseType);
            document.getElementById('modalMessage').value = message;

            modal.dataset.userIds = JSON.stringify([userId]);
            modal.style.display = 'flex';
        }

        function openBulkContactModal() {
            const selected = document.querySelectorAll('.user-checkbox:checked');
            const userIds = Array.from(selected).map(cb => cb.value);
            const emails = Array.from(selected).map(cb => cb.dataset.email);
            
            const modal = document.getElementById('contactModal');
            document.getElementById('modalUserEmail').innerText = emails.length > 3 
                ? `${emails.slice(0, 3).join(', ')} y ${emails.length - 3} más`
                : emails.join(', ');
            document.getElementById('modalReason').innerText = 'Envío masivo a usuarios seleccionados';

            // Detectar el tipo de caso predominante o usar general
            const caseTypes = Array.from(selected).map(cb => cb.dataset.case);
            const mostFrequentCase = getMostFrequent(caseTypes) || 'general_followup';

            const message = generateMessage(mostFrequentCase);
            document.getElementById('modalMessage').value = message;

            modal.dataset.userIds = JSON.stringify(userIds);
            modal.style.display = 'flex';
        }

        function getMostFrequent(arr) {
            const hashmap = arr.reduce((acc, val) => {
                acc[val] = (acc[val] || 0) + 1;
                return acc;
            }, {});
            return Object.keys(hashmap).reduce((a, b) => hashmap[a] > hashmap[b] ? a : b);
        }

        function closeModal() {
            document.getElementById('contactModal').style.display = 'none';
        }

        function generateMessage(type) {
            switch(type) {
                case 'active_high':
                    return `Hola,\n\nhe visto que hoy estás usando bastante la API 👀\n¿todo bien por ahí?\n\nSi estás probando algo en producción o necesitas más volumen, dime y te ayudo.`;
                case 'pricing_missing':
                    return `Hola,\n\nveo que ya estás usando la API bastante 👍\n\nSi te interesa escalar tu integración o necesitas un plan con más volumen, dime y te ayudo a elegir el que mejor se adapte.`;
                case 'reactivation':
                    return `Hola,\n\nhace unos días estuviste usando la API bastante.\n¿sigues con ese proyecto?\n\nSi lo retomaste, te ayudo.`;
                case 'onboarding':
                    return `Hola,\n\nvi que generaste tu API key pero no hiciste llamadas.\n\nSi quieres, te paso un ejemplo listo para probar.`;
                default:
                    return "Hola, ¿en qué puedo ayudarte?";
            }
        }

        function sendMessage() {
            const modal = document.getElementById('contactModal');
            const userIds = JSON.parse(modal.dataset.userIds);
            const message = document.getElementById('modalMessage').value;
            const subject = document.getElementById('modalSubject').value;
            const btn = document.getElementById('btnSend');

            btn.disabled = true;
            btn.innerText = 'Enviando...';

            fetch('<?= site_url('admin/send-message') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({ user_ids: userIds, message: message, subject: subject })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                }
                
                Swal.fire({
                    icon: data.status === 'success' ? 'success' : 'error',
                    title: data.status === 'success' ? '¡Hecho!' : 'Error',
                    text: data.message,
                    confirmButtonColor: '#6366f1'
                });

                if (data.status === 'success') {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#6366f1'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Enviar Mensaje';
            });
        }

        // Close on Escape
        window.onkeydown = function(event) {
            if (event.key === 'Escape') closeModal();
        }
    </script>
<?= $this->endSection() ?>

