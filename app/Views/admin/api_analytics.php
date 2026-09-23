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

        .pill { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; text-align: center; }

        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 700;
        }
        .status-tag.danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .status-tag.warning { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .status-tag.info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .status-tag.neutral { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .status-tag.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .status-tag.purple { background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }

        /* Modal Styles */
        .analytics-modal-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .analytics-modal-content {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 620px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/**
 * Constructor de URLs del panel. Cada enlace montaba su query string a mano, así que
 * añadir un filtro obligaba a tocarlos todos y los de periodo se comían la búsqueda.
 * Se le pasan solo los parámetros que cambian; el resto del estado se conserva.
 */
$analyticsUrl = function (array $overrides = []) use ($period, $user_status_filter, $search, $sort, $contact_filter, $plan_filter) {
    $params = array_merge([
        'period'        => $period,
        'status_filter' => $user_status_filter,
        'q'             => $search,
        'sort'          => $sort,
        'contact'       => $contact_filter,
        'plan'          => $plan_filter,
    ], $overrides);

    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);

    return site_url('admin/api-analytics?' . http_build_query($params));
};
?>
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.25rem;">
                <span style="font-size: 2rem;">⚡</span>
                <h1 class="title" style="margin: 0;">API & Desarrolladores</h1>
            </div>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">Panel de control de adopción técnica, consumo de cuotas y conversión a <strong>Planes Pro & Business</strong></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="<?= site_url('admin/api-requests') ?>" class="btn ghost">Ver Logs HTTP</a>
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
        <div class="alert danger" style="background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; border-radius: 14px; padding: 14px 20px; margin-bottom: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div id="analyticsDashboardContainer" style="position: relative; transition: opacity 0.2s ease;">
        <?php /*
            El historial de contacto viaja DENTRO del contenedor que refresca el AJAX.
            Un <script> inyectado por innerHTML no se ejecuta, pero uno de tipo
            application/json sí se puede leer con textContent: así el mapa se
            actualiza en cada filtrado en vez de quedarse con el del primer pintado.
        */ ?>
        <script type="application/json" id="contactInfoData"><?= json_encode($contact_info, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

        <!-- Selector de Periodo -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #475569; margin-right: 6px;">📅 Periodo de análisis:</span>
                
                <a href="<?= $analyticsUrl(['period' => 'this_month']) ?>" 
                   class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'this_month' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                   Este mes
                </a>
                <a href="<?= $analyticsUrl(['period' => 'last_month']) ?>" 
                   class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'last_month' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                   Mes anterior
                </a>
                <a href="<?= $analyticsUrl(['period' => 'last_30d']) ?>" 
                   class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'last_30d' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                   Últimos 30 días
                </a>
                <a href="<?= $analyticsUrl(['period' => 'this_year']) ?>" 
                   class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'this_year' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                   Año <?= date('Y') ?>
                </a>
                <a href="<?= $analyticsUrl(['period' => 'all']) ?>" 
                   class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $period === 'all' ? 'background: #2563eb; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                   Todo el histórico
                </a>
            </div>

            <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                Mostrando datos de: <span style="color: #1e293b; font-weight: 800;"><?= esc($period_label) ?></span>
            </div>
        </div>

    <!-- KPIs Ejecutivos -->
    <div class="kpi-grid">
        <!-- 1. Desarrolladores Registrados -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>
                </div>
                <span class="kpi-trend-badge trend-<?= $trend_new_users['direction'] ?>" title="Variación de altas respecto al periodo anterior">
                    <?php if ($trend_new_users['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trend_new_users['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trend_new_users['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Desarrolladores Registrados</span>
            <span class="kpi-value"><?= number_format($total_api_users, 0, ',', '.') ?></span>
            <span class="kpi-sub">+<?= number_format($trend_new_users['current'], 0, ',', '.') ?> en <?= esc($period_label) ?></span>
        </div>

        <!-- 2. Activación Técnica -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-emerald);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <?php 
                $actPct = $total_api_users > 0 ? round(($funnel['step2_activated'] / $total_api_users) * 100, 1) : 0;
                ?>
                <span class="kpi-trend-badge trend-up">
                    <?= $actPct ?>% Activados
                </span>
            </div>
            <span class="kpi-label">Activación Técnica</span>
            <span class="kpi-value"><?= $funnel['step2_activated'] ?> <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 500;">/ <?= $total_api_users ?></span></span>
            <span class="kpi-sub"><?= $counts['inactive'] ?> registrados sin peticiones aún</span>
        </div>

        <!-- 3. Consumo Total de Peticiones -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9m9-9a9 9 0 0 0-9-9m9 9H3m9 9a9 9 0 0 1-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                </div>
                <span class="kpi-trend-badge trend-<?= $trend_requests['direction'] ?>" title="Variación de peticiones respecto al periodo anterior">
                    <?php if ($trend_requests['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trend_requests['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trend_requests['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Peticiones a la API</span>
            <span class="kpi-value"><?= number_format($trend_requests['current'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Llamadas procesadas en <?= esc($period_label) ?></span>
        </div>

        <!-- 4. Clientes de Pago y MRR -->
        <div class="kpi-card" style="--kpi-color: var(--kpi-amber);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <span class="kpi-trend-badge trend-up">
                    <?= $conversion_rate ?>% Conversión
                </span>
            </div>
            <span class="kpi-label">MRR Suscripciones API</span>
            <span class="kpi-value"><?= number_format($mrr_total, 2, ',', '.') ?> €<span style="font-size: 1rem; color: #94a3b8; font-weight: 500;">/mes</span></span>
            <span class="kpi-sub">💎 <?= $paid_subscribers ?> cliente(s) activo(s) &bull; <?= $counts['limit_reached'] ?> en límite</span>
        </div>
    </div>

    <!-- Sección Intermedia: Embudo de Adopción & Diagnóstico de Fuga -->
    <div class="analytics-grid-two">
        
        <!-- Embudo de Adopción Técnica (Funnel) -->
        <div class="card" style="padding: 1.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 1.15rem; color: #0f172a; font-weight: 800;">Embudo de Adopción Técnica y Conversión</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: #64748b;">De la creación de cuenta al consumo de los 100 créditos y salto a planes Pro</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <!-- Paso 1 -->
                <div class="funnel-step">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #3b82f615; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">1</div>
                    <div style="flex: 2; min-width: 150px;">
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">Registrados en la API</div>
                        <div style="font-size: 0.75rem; color: #64748b;">100 peticiones gratis asignadas</div>
                    </div>
                    <div class="funnel-bar-wrapper">
                        <div class="funnel-bar-fill" style="width: 100%; background: #3b82f6;"></div>
                    </div>
                    <div style="text-align: right; min-width: 60px;">
                        <span style="font-weight: 800; color: #0f172a;"><?= $funnel['step1_registered'] ?></span>
                        <span style="display: block; font-size: 0.75rem; color: #64748b;">100%</span>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="funnel-step">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #10b98115; color: #10b981; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">2</div>
                    <div style="flex: 2; min-width: 150px;">
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">Activación (1ª llamada realizada)</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Al menos 1 petición completada a la API</div>
                    </div>
                    <?php $p2 = $funnel['step1_registered'] > 0 ? round(($funnel['step2_activated'] / $funnel['step1_registered']) * 100) : 0; ?>
                    <div class="funnel-bar-wrapper">
                        <div class="funnel-bar-fill" style="width: <?= $p2 ?>%; background: #10b981;"></div>
                    </div>
                    <div style="text-align: right; min-width: 60px;">
                        <span style="font-weight: 800; color: #0f172a;"><?= $funnel['step2_activated'] ?></span>
                        <span style="display: block; font-size: 0.75rem; color: #64748b;"><?= $p2 ?>%</span>
                    </div>
                </div>

                <!-- Paso 3 -->
                <div class="funnel-step">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #8b5cf615; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">3</div>
                    <div style="flex: 2; min-width: 150px;">
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">Integración Recurrente (≥5 llamadas)</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Pruebas reales en código / CRM</div>
                    </div>
                    <?php $p3 = $funnel['step1_registered'] > 0 ? round(($funnel['step3_engaged'] / $funnel['step1_registered']) * 100) : 0; ?>
                    <div class="funnel-bar-wrapper">
                        <div class="funnel-bar-fill" style="width: <?= $p3 ?>%; background: #8b5cf6;"></div>
                    </div>
                    <div style="text-align: right; min-width: 60px;">
                        <span style="font-weight: 800; color: #0f172a;"><?= $funnel['step3_engaged'] ?></span>
                        <span style="display: block; font-size: 0.75rem; color: #64748b;"><?= $p3 ?>%</span>
                    </div>
                </div>

                <!-- Paso 4 -->
                <div class="funnel-step">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #f59e0b15; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">4</div>
                    <div style="flex: 2; min-width: 150px;">
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;">Alto Consumo (≥80 peticiones)</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Límite cercano o cuota agotada</div>
                    </div>
                    <?php $p4 = $funnel['step1_registered'] > 0 ? round(($funnel['step4_high_usage'] / $funnel['step1_registered']) * 100) : 0; ?>
                    <div class="funnel-bar-wrapper">
                        <div class="funnel-bar-fill" style="width: <?= $p4 ?>%; background: #f59e0b;"></div>
                    </div>
                    <div style="text-align: right; min-width: 60px;">
                        <span style="font-weight: 800; color: #0f172a;"><?= $funnel['step4_high_usage'] ?></span>
                        <span style="display: block; font-size: 0.75rem; color: #64748b;"><?= $p4 ?>%</span>
                    </div>
                </div>

                <!-- Paso 5 -->
                <div class="funnel-step" style="border: 2px solid #10b981; background: #f0fdf4;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">5</div>
                    <div style="flex: 2; min-width: 150px;">
                        <div style="font-weight: 800; color: #065f46; font-size: 0.9rem;">Clientes de Pago (Pro / Business)</div>
                        <div style="font-size: 0.75rem; color: #047857;">Suscripción mensual activa</div>
                    </div>
                    <?php $p5 = $funnel['step1_registered'] > 0 ? round(($funnel['step5_paid'] / $funnel['step1_registered']) * 100, 1) : 0; ?>
                    <div class="funnel-bar-wrapper">
                        <div class="funnel-bar-fill" style="width: <?= max(2, $p5) ?>%; background: #10b981;"></div>
                    </div>
                    <div style="text-align: right; min-width: 60px;">
                        <span style="font-weight: 900; color: #065f46; font-size: 1rem;"><?= $funnel['step5_paid'] ?></span>
                        <span style="display: block; font-size: 0.75rem; color: #059669; font-weight: 700;"><?= $p5 ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnóstico de Caídas (Drop-off Analysis) & Top Endpoints -->
        <div class="card" style="padding: 1.75rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.15rem; color: #0f172a; font-weight: 800;">Diagnóstico de Fugas & Oportunidades</h3>
                <p style="margin: 0 0 1.25rem 0; font-size: 0.85rem; color: #64748b;">Cuellos de botella detectados automáticamente para tomar acción comercial</p>

                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 1.5rem;">
                    <!-- Fuga 1 -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #ef4444; border-radius: 8px; padding: 10px 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: #0f172a; font-size: 0.85rem;">💤 Fuga en Onboarding (<?= $drop_off_no_usage ?>% sin llamadas)</strong>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #b91c1c;"><?= $counts['never_called'] ?? ($funnel['step1_registered'] - $funnel['step2_activated']) ?> usuarios</span>
                        </div>
                        <p style="margin: 4px 0 0 0; font-size: 0.78rem; color: #64748b; line-height: 1.4;">
                            Se registraron pero nunca realizaron una sola llamada a la API. Automatiza el envío de snippets cURL o colección de Postman a las 24h.
                        </p>
                    </div>

                    <!-- Fuga 2 -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 10px 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: #0f172a; font-size: 0.85rem;">🔥 Oportunidad Caliente en Límite (<?= $counts['limit_reached'] ?> bloqueados)</strong>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #b45309;">100% cuota agotada</span>
                        </div>
                        <p style="margin: 4px 0 0 0; font-size: 0.78rem; color: #64748b; line-height: 1.4;">
                            Usuarios que han alcanzado el tope de 100 peticiones. Envíales un email con descuento para pasar a <strong>Plan Pro (19 € / mes)</strong>.
                        </p>
                    </div>

                    <!-- Fuga 3: Errores -->
                    <?php if ($counts['errors'] > 0): ?>
                    <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-left: 4px solid #a855f7; border-radius: 8px; padding: 10px 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: #581c87; font-size: 0.85rem;">🛠️ Errores 400 en Integración (<?= $counts['errors'] ?> desarrolladores)</strong>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #7e22ce;">Fallo de sintaxis</span>
                        </div>
                        <p style="margin: 4px 0 0 0; font-size: 0.78rem; color: #6b21a8; line-height: 1.4;">
                            Están teniendo errores 400 Bad Request o autenticación. Contacta para ofrecer ayuda técnica antes de que abandonen.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Endpoints -->
            <div>
                <strong style="display: block; font-size: 0.8rem; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em;">Endpoints más consultados</strong>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <?php if (empty($top_endpoints)): ?>
                        <span style="font-size: 0.8rem; color: #94a3b8;">Sin datos de peticiones en el periodo.</span>
                    <?php else: ?>
                        <?php foreach ($top_endpoints as $ep => $cnt): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid #f1f5f9; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem;">
                                <code style="color: #2563eb; font-weight: 700;"><?= esc($ep) ?></code>
                                <span style="font-weight: 700; color: #64748b;"><?= number_format($cnt, 0, ',', '.') ?> reqs</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Pestañas de Segmentación y Listado de Desarrolladores -->
    <div class="card" style="padding: 1.75rem;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.25rem; color: #0f172a; font-weight: 800;">Desarrolladores & Clientes API</h3>
                <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Filtra por estado de consumo y contacta directamente con cualquier usuario</p>
            </div>

            <!-- Buscador y Ordenación -->
            <form action="<?= site_url('admin/api-analytics') ?>" method="get" class="ajax-search-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="period" value="<?= esc($period) ?>">
                <input type="hidden" name="status_filter" value="<?= esc($user_status_filter) ?>">
                <input type="hidden" name="contact" value="<?= esc($contact_filter) ?>">
                <input type="hidden" name="plan" value="<?= esc($plan_filter) ?>">

                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Ordenar:</span>
                    <select name="sort" class="ajax-sort-select" style="padding: 7px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; background: white; color: #0f172a; font-weight: 700; cursor: pointer;">
                        <option value="usage_desc" <?= $sort === 'usage_desc' ? 'selected' : '' ?>>🔥 Mayor consumo este mes</option>
                        <option value="usage_asc" <?= $sort === 'usage_asc' ? 'selected' : '' ?>>📉 Menor consumo este mes</option>
                        <option value="history_desc" <?= $sort === 'history_desc' ? 'selected' : '' ?>>📈 Mayor histórico total</option>
                        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>📅 Más recientes (Alta)</option>
                        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>⏳ Más antiguos (Alta)</option>
                        <option value="errors_desc" <?= $sort === 'errors_desc' ? 'selected' : '' ?>>⚠️ Con más errores (400)</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>🔤 Nombre (A - Z)</option>
                        <option value="email_asc" <?= $sort === 'email_asc' ? 'selected' : '' ?>>✉️ Sin contactar / contacto más antiguo</option>
                        <option value="email_desc" <?= $sort === 'email_desc' ? 'selected' : '' ?>>📨 Contactados más recientemente</option>
                    </select>
                </div>

                <div style="display: flex; gap: 6px; align-items: center;">
                    <input type="text" name="q" value="<?= esc($search) ?>" placeholder="Buscar desarrollador..." 
                           style="padding: 7px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; width: 200px;">
                    <button type="submit" class="btn primary" style="padding: 7px 12px; font-size: 0.82rem;">Buscar</button>
                    <?php if ($search !== ''): ?>
                        <a href="<?= $analyticsUrl(['q' => '']) ?>" class="btn ghost ajax-filter-link" style="padding: 5px 8px;">🔄</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Filtros Rápidos (Pills) -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
            <a href="<?= $analyticsUrl(['status_filter' => 'all']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'all' ? 'background: #0f172a; color: white;' : 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;' ?>">
                Todos (<?= $pill_counts['status']['all'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['status_filter' => 'limit_reached']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'limit_reached' ? 'background: #dc2626; color: white;' : 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' ?>">
                🔥 Límite Agotado (<?= $pill_counts['status']['limit_reached'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['status_filter' => 'near_limit']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'near_limit' ? 'background: #d97706; color: white;' : 'background: #fef3c7; color: #b45309; border: 1px solid #fcd34d;' ?>">
                ⚠️ Cerca del Límite (<?= $pill_counts['status']['near_limit'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['status_filter' => 'active_free']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'active_free' ? 'background: #0284c7; color: white;' : 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' ?>">
                ⚡ Activos Free (<?= $pill_counts['status']['active_free'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['status_filter' => 'inactive']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'inactive' ? 'background: #64748b; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                💤 Inactivos (<?= $pill_counts['status']['inactive'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['status_filter' => 'errors']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'errors' ? 'background: #9333ea; color: white;' : 'background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;' ?>">
                ❌ Con Errores 400 (<?= $pill_counts['status']['errors'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['status_filter' => 'paid']) ?>" 
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; <?= $user_status_filter === 'paid' ? 'background: #059669; color: white;' : 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' ?>">
                💎 Clientes de Pago (<?= $pill_counts['status']['paid'] ?>)
            </a>
        </div>

        <!-- Filtro por Plan (Free / planes de pago de la API, leídos de api_plans) -->
        <?php
            $planPillBase = 'text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; font-size: 0.82rem; ';
            $planPillOff  = 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;';
        ?>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">🏷️ Plan:</span>
            <a href="<?= $analyticsUrl(['plan' => 'all']) ?>"
               class="pill ajax-filter-link" style="<?= $planPillBase ?><?= $plan_filter === 'all' ? 'background: #0f172a; color: white;' : $planPillOff ?>">
                Todos (<?= $pill_counts['plan']['all'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['plan' => 'free']) ?>"
               class="pill ajax-filter-link" style="<?= $planPillBase ?><?= $plan_filter === 'free' ? 'background: #0284c7; color: white;' : 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' ?>">
                🆓 Free (<?= $pill_counts['plan']['free'] ?>)
            </a>
            <?php foreach ($api_paid_plans as $planSlug => $planName): ?>
                <a href="<?= $analyticsUrl(['plan' => $planSlug]) ?>"
                   class="pill ajax-filter-link" style="<?= $planPillBase ?><?= $plan_filter === $planSlug ? 'background: #059669; color: white;' : 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;' ?>">
                    💎 <?= esc($planName) ?> (<?= $pill_counts['plan'][$planSlug] ?? 0 ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Filtros de Contacto por Correo (se combinan con los de arriba) -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">✉️ Contacto:</span>
            <a href="<?= $analyticsUrl(['contact' => 'all']) ?>"
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; font-size: 0.82rem; <?= $contact_filter === 'all' ? 'background: #0f172a; color: white;' : 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;' ?>">
                Indiferente (<?= $pill_counts['contact']['all'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['contact' => 'never']) ?>"
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; font-size: 0.82rem; <?= $contact_filter === 'never' ? 'background: #0d9488; color: white;' : 'background: #ccfbf1; color: #0f766e; border: 1px solid #5eead4;' ?>">
                🆕 Sin contactar (<?= $pill_counts['contact']['never'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['contact' => 'contacted']) ?>"
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; font-size: 0.82rem; <?= $contact_filter === 'contacted' ? 'background: #4f46e5; color: white;' : 'background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;' ?>">
                📨 Ya contactados (<?= $pill_counts['contact']['contacted'] ?>)
            </a>
            <a href="<?= $analyticsUrl(['contact' => 'recent']) ?>"
               class="pill ajax-filter-link" style="text-decoration: none; padding: 6px 12px; font-weight: 700; border-radius: 8px; font-size: 0.82rem; <?= $contact_filter === 'recent' ? 'background: #b45309; color: white;' : 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;' ?>">
                🕐 Contactados hace ≤ 7 días (<?= $pill_counts['contact']['recent'] ?>)
            </a>
            <span style="font-size: 0.75rem; color: #94a3b8;">
                Los números de cada fila tienen en cuenta los filtros activos de las demás. Se cuenta el histórico completo de envíos, no el periodo seleccionado arriba. Los correos de bienvenida no cuentan como contacto. No se muestran los usuarios que han pedido no recibir correos.
            </span>
        </div>

        <!-- Barra de Acciones Masivas -->
        <div id="bulkActionsBar" style="display: none; align-items: center; justify-content: space-between; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 12px 18px; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">
                    <span id="selectedCount">0</span> desarrollador(es) seleccionado(s)
                </span>
            </div>
            <button type="button" onclick="openBulkEmailModal();" class="btn primary" style="padding: 8px 16px; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Enviar Campaña por Email a Seleccionados
            </button>
        </div>

        <!-- Tabla de Desarrolladores -->
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; text-align: left; font-size: 0.78rem; text-transform: uppercase; color: #64748b;">
                        <th style="padding: 10px 12px; width: 30px;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this);">
                        </th>
                        <th style="padding: 10px 12px;">
                            <a href="<?= $analyticsUrl(['sort' => ($sort === 'name_asc' ? 'date_desc' : 'name_asc')]) ?>" 
                               class="ajax-filter-link" 
                               style="text-decoration: none; color: <?= $sort === 'name_asc' ? '#2563eb' : 'inherit' ?>; display: inline-flex; align-items: center; gap: 4px;"
                               title="Clic para ordenar por nombre">
                                <span>Desarrollador / Empresa</span>
                                <?= $sort === 'name_asc' ? '▲' : '' ?>
                            </a>
                        </th>
                        <th style="padding: 10px 12px;">Plan & Estado</th>
                        <th style="padding: 10px 12px;">
                            <a href="<?= $analyticsUrl(['sort' => ($sort === 'usage_desc' ? 'usage_asc' : 'usage_desc')]) ?>" 
                               class="ajax-filter-link" 
                               style="text-decoration: none; color: <?= in_array($sort, ['usage_desc', 'usage_asc']) ? '#2563eb' : 'inherit' ?>; display: inline-flex; align-items: center; gap: 4px; font-weight: 800;"
                               title="Clic para alternar orden de consumo">
                                <span>Consumo Este Mes</span>
                                <?php if ($sort === 'usage_desc'): ?>
                                    <span style="font-size: 0.85rem;">▼</span>
                                <?php elseif ($sort === 'usage_asc'): ?>
                                    <span style="font-size: 0.85rem;">▲</span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.75rem;">⇅</span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="padding: 10px 12px;">
                            <a href="<?= $analyticsUrl(['sort' => ($sort === 'errors_desc' ? 'usage_desc' : 'errors_desc')]) ?>" 
                               class="ajax-filter-link" 
                               style="text-decoration: none; color: <?= $sort === 'errors_desc' ? '#2563eb' : 'inherit' ?>; display: inline-flex; align-items: center; gap: 4px;"
                               title="Clic para ordenar por errores técnicos">
                                <span>Salud Técnica</span>
                                <?= $sort === 'errors_desc' ? '▼' : '' ?>
                            </a>
                        </th>
                        <th style="padding: 10px 12px;">
                            <a href="<?= $analyticsUrl(['sort' => ($sort === 'date_desc' ? 'date_asc' : 'date_desc')]) ?>" 
                               class="ajax-filter-link" 
                               style="text-decoration: none; color: <?= in_array($sort, ['date_desc', 'date_asc']) ? '#2563eb' : 'inherit' ?>; display: inline-flex; align-items: center; gap: 4px;"
                               title="Clic para ordenar por fecha">
                                <span>Última Actividad</span>
                                <?php if ($sort === 'date_desc'): ?>
                                    <span style="font-size: 0.85rem;">▼</span>
                                <?php elseif ($sort === 'date_asc'): ?>
                                    <span style="font-size: 0.85rem;">▲</span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="padding: 10px 12px;">
                            <a href="<?= $analyticsUrl(['sort' => $sort === 'email_asc' ? 'email_desc' : 'email_asc']) ?>"
                               class="ajax-filter-link"
                               style="text-decoration: none; color: <?= in_array($sort, ['email_asc', 'email_desc']) ? '#2563eb' : 'inherit' ?>; display: inline-flex; align-items: center; gap: 4px;"
                               title="Clic para ordenar por último correo enviado">
                                <span>Contacto</span>
                                <?php if ($sort === 'email_desc'): ?>
                                    <span style="font-size: 0.85rem;">▼</span>
                                <?php elseif ($sort === 'email_asc'): ?>
                                    <span style="font-size: 0.85rem;">▲</span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.75rem;">⇅</span>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th style="padding: 10px 12px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" style="padding: 3rem; text-align: center; color: #94a3b8;">
                                No se encontraron desarrolladores que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $row): ?>
                            <?php $u = $row['user']; ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='white';">
                                <td style="padding: 12px;">
                                    <input type="checkbox" class="user-select-checkbox" value="<?= $u['id'] ?>" onchange="updateSelectedCount();">
                                </td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                                        <?= esc($u['name'] ?: 'Desarrollador #' . $u['id']) ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #64748b;"><?= esc($u['email']) ?></div>
                                    <?php if (!empty($u['company'])): ?>
                                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">🏢 <?= esc($u['company']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;">
                                    <span class="status-tag <?= $row['status_badge'] ?>">
                                        <?= esc($row['status_label']) ?>
                                    </span>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                                        Plan: <strong><?= esc($row['plan_name']) ?></strong>
                                    </div>
                                </td>
                                <td style="padding: 12px; min-width: 140px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">
                                        <span><?= number_format($row['month_requests'], 0, ',', '.') ?></span>
                                        <span style="color: #64748b;">/ <?= number_format($row['quota'], 0, ',', '.') ?></span>
                                    </div>
                                    <?php 
                                    $barBg = $row['usage_pct'] >= 100 ? '#ef4444' : ($row['usage_pct'] >= 80 ? '#f59e0b' : '#3b82f6');
                                    ?>
                                    <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden;">
                                        <div style="width: <?= $row['usage_pct'] ?>%; height: 100%; background: <?= $barBg ?>; border-radius: 99px;"></div>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">
                                        Histórico: <?= number_format($row['history_requests'], 0, ',', '.') ?> reqs
                                    </div>
                                </td>
                                <td style="padding: 12px;">
                                    <?php if ($row['has_api_key']): ?>
                                        <span style="display: inline-flex; align-items: center; gap: 4px; color: #059669; font-size: 0.75rem; font-weight: 700;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                            API Key Activa
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 0.75rem;">Sin API Key</span>
                                    <?php endif; ?>

                                    <?php if ($row['recent_errors'] > 0): ?>
                                        <div style="color: #b91c1c; font-size: 0.75rem; font-weight: 700; margin-top: 2px;">
                                            ⚠️ <?= $row['recent_errors'] ?> error(es) 400 recientes
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($row['last_endpoint'])): ?>
                                        <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px; font-family: monospace;">
                                            <?= esc(substr($row['last_endpoint'], 0, 24)) ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; font-size: 0.8rem; color: #64748b;">
                                    <?php if (!empty($row['last_request_at'])): ?>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <?= date('d/m/Y H:i', strtotime($row['last_request_at'])) ?>
                                        </div>
                                        <span style="font-size: 0.72rem; color: #94a3b8;">Última llamada</span>
                                    <?php else: ?>
                                        <div style="color: #94a3b8;">Sin actividad</div>
                                        <span style="font-size: 0.72rem; color: #94a3b8;">Alta: <?= date('d/m/Y', strtotime($u['created_at'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <!-- Contacto por correo: qué se le ha enviado ya -->
                                <td style="padding: 12px; font-size: 0.8rem; min-width: 170px;">
                                    <?php if ($row['is_unsubscribed']): ?>
                                        <div style="color: #b91c1c; font-weight: 700; font-size: 0.75rem;">🚫 Baja voluntaria</div>
                                    <?php endif; ?>

                                    <?php if ($row['emails_sent'] === 0): ?>
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 99px; background: #ccfbf1; color: #0f766e; font-size: 0.72rem; font-weight: 800;">
                                            Sin contactar
                                        </span>
                                    <?php else: ?>
                                        <?php
                                        // Semáforo por antigüedad: reciente = riesgo de repetir
                                        $d = $row['days_since_email'];
                                        if ($d !== null && $d <= 7) {
                                            $chipBg = '#fee2e2'; $chipFg = '#b91c1c';
                                        } elseif ($d !== null && $d <= 30) {
                                            $chipBg = '#fef3c7'; $chipFg = '#b45309';
                                        } else {
                                            $chipBg = '#f1f5f9'; $chipFg = '#475569';
                                        }
                                        ?>
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 99px; background: <?= $chipBg ?>; color: <?= $chipFg ?>; font-size: 0.72rem; font-weight: 800;">
                                            <?= $row['emails_sent'] ?> enviado<?= $row['emails_sent'] === 1 ? '' : 's' ?>
                                            <?php if ($d !== null): ?>
                                                · hace <?= $d === 0 ? 'hoy' : $d . ' d' ?>
                                            <?php endif; ?>
                                        </span>
                                        <div style="font-weight: 600; color: #1e293b; margin-top: 4px;">
                                            <?= date('d/m/Y H:i', strtotime($row['last_email_at'])) ?>
                                        </div>
                                        <?php if (!empty($row['last_email_subject'])): ?>
                                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;"
                                                 title="<?= esc($row['last_email_subject']) ?>">
                                                «<?= esc(mb_strimwidth($row['last_email_subject'], 0, 42, '…')) ?>»
                                            </div>
                                        <?php endif; ?>
                                        <div style="font-size: 0.72rem; margin-top: 2px; color: <?= $row['emails_opened'] > 0 ? '#047857' : '#94a3b8' ?>;">
                                            <?= $row['emails_opened'] > 0
                                                ? '👁️ Abierto (' . $row['emails_opened'] . ')'
                                                : 'Sin aperturas registradas' ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($row['emails_failed'] > 0): ?>
                                        <div style="font-size: 0.72rem; color: #b91c1c; margin-top: 2px;">
                                            ⚠️ <?= $row['emails_failed'] ?> envío(s) fallido(s)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: right;">
                                    <button type="button"
                                            onclick="openSingleEmailModal(<?= $u['id'] ?>, '<?= esc($u['name'], 'js') ?>', '<?= esc($u['email'], 'js') ?>', '<?= esc($row['status'], 'js') ?>');"
                                            class="btn ghost" 
                                            style="padding: 6px 10px; font-size: 0.8rem; border-radius: 6px;"
                                            title="Enviar correo a este desarrollador">
                                        ✉️ Contactar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div> <!-- /#analyticsDashboardContainer -->

    <!-- MODAL 1: Enviar Correo Individual -->
    <div id="singleEmailModal" class="analytics-modal-backdrop">
        <div class="analytics-modal-content">
            <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 800;">
                    ✉️ Contactar Desarrollador
                </h3>
                <button type="button" onclick="closeSingleEmailModal();" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">✕</button>
            </div>

            <form action="<?= site_url('admin/api-analytics/email-single') ?>" method="post" id="formSingleEmail" hx-boost="false">
                <input type="hidden" name="user_id" id="modalSingleUserId">
                <?= csrf_field() ?>

                <div style="padding: 24px; display: flex; flex-direction: column; gap: 14px; overflow-y: auto;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Destinatario</label>
                        <input type="text" id="modalSingleUserDisplay" readonly style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f1f5f9; font-size: 0.85rem; color: #334155;">
                    </div>

                    <!-- Historial de contacto: se pinta desde CONTACT_INFO, antes de enviar -->
                    <div id="modalSingleHistory" style="display: none; border-radius: 10px; padding: 12px 14px; font-size: 0.82rem; line-height: 1.5;"></div>

                    <!-- Aviso de asunto repetido. Solo se ve cuando el asunto del formulario
                         coincide con uno ya enviado; el envío queda bloqueado hasta confirmar. -->
                    <div id="modalSingleDupWarning" style="display: none; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 12px 14px;">
                        <div style="font-size: 0.82rem; color: #991b1b; font-weight: 700; margin-bottom: 8px;">
                            ⚠️ <span id="modalSingleDupText"></span>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #7f1d1d; font-weight: 700; cursor: pointer;">
                            <input type="checkbox" name="allow_duplicate" value="1" id="modalSingleAllowDup" onchange="refreshSingleSubmitState();">
                            Enviar de todas formas (sé que lo estoy repitiendo)
                        </label>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Cargar Plantilla Predeterminada</label>
                        <select id="modalSingleTemplateSelect" onchange="applySingleTemplate(this.value);" style="width: 100%; padding: 8px 12px; border: 1.5px solid #2563eb; border-radius: 8px; font-size: 0.85rem; background: #eff6ff; color: #1e40af; font-weight: 600;">
                            <option value="">-- Selecciona una plantilla rápida --</option>
                            <?php foreach ($email_templates as $tpl): ?>
                                <option value="<?= esc($tpl['id']) ?>"><?= esc($tpl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Asunto del Correo <span style="color: red;">*</span></label>
                        <input type="text" name="subject" id="modalSingleSubject" required oninput="refreshSingleDuplicateWarning();" placeholder="Ej: ¿Necesitas ampliar tu cuota de la API?" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Mensaje <span style="color: red;">*</span> (Soporta variables: {NOMBRE}, {EMPRESA}, {SITE_URL})</label>
                        <textarea name="message" id="modalSingleMessage" rows="9" required placeholder="Escribe tu mensaje..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; line-height: 1.5; font-family: inherit;"></textarea>
                    </div>
                </div>

                <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeSingleEmailModal();" class="btn ghost">Cancelar</button>
                    <button type="submit" id="modalSingleSubmit" class="btn primary">Enviar Correo Directo 🚀</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Enviar Correo Masivo (Bulk) -->
    <div id="bulkEmailModal" class="analytics-modal-backdrop">
        <div class="analytics-modal-content">
            <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 800;">
                    📢 Campaña por Email a Desarrolladores
                </h3>
                <button type="button" onclick="closeBulkEmailModal();" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">✕</button>
            </div>

            <form action="<?= site_url('admin/api-analytics/email-bulk') ?>" method="post" id="formBulkEmail" hx-boost="false">
                <input type="hidden" name="user_ids" id="modalBulkUserIds">
                <?= csrf_field() ?>

                <div style="padding: 24px; display: flex; flex-direction: column; gap: 14px; overflow-y: auto;">
                    <div style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; padding: 12px 16px; border-radius: 10px; font-size: 0.85rem;">
                        ℹ️ Se enviará de forma individual a <strong id="modalBulkRecipientsCount">0</strong> desarrollador(es) seleccionados.
                        Los usuarios dados de baja de marketing no recibirán correos comerciales.
                    </div>

                    <!-- Cuántos de los seleccionados ya han recibido algo -->
                    <div id="modalBulkHistory" style="display: none; border-radius: 10px; padding: 12px 14px; font-size: 0.82rem; line-height: 1.5;"></div>

                    <div id="modalBulkDupWarning" style="display: none; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 12px 14px;">
                        <div style="font-size: 0.82rem; color: #991b1b; font-weight: 700; margin-bottom: 8px;">
                            ⚠️ <span id="modalBulkDupText"></span>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #7f1d1d; font-weight: 700; cursor: pointer;">
                            <input type="checkbox" name="allow_duplicate" value="1" id="modalBulkAllowDup">
                            Enviar también a los que ya lo recibieron
                        </label>
                        <div style="font-size: 0.75rem; color: #7f1d1d; margin-top: 6px;">
                            Si lo dejas sin marcar, esos destinatarios se omiten y el resto de la campaña sale igual.
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Cargar Plantilla Predeterminada</label>
                        <select id="modalBulkTemplateSelect" onchange="applyBulkTemplate(this.value);" style="width: 100%; padding: 8px 12px; border: 1.5px solid #2563eb; border-radius: 8px; font-size: 0.85rem; background: #eff6ff; color: #1e40af; font-weight: 600;">
                            <option value="">-- Selecciona una plantilla rápida --</option>
                            <?php foreach ($email_templates as $tpl): ?>
                                <option value="<?= esc($tpl['id']) ?>"><?= esc($tpl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Asunto del Correo <span style="color: red;">*</span></label>
                        <input type="text" name="subject" id="modalBulkSubject" required oninput="refreshBulkDuplicateWarning();" placeholder="Asunto del correo masivo..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Mensaje <span style="color: red;">*</span> (Soporta variables: {NOMBRE}, {EMPRESA}, {SITE_URL})</label>
                        <textarea name="message" id="modalBulkMessage" rows="9" required placeholder="Escribe el contenido de la campaña..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; line-height: 1.5; font-family: inherit;"></textarea>
                    </div>
                </div>

                <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeBulkEmailModal();" class="btn ghost">Cancelar</button>
                    <button type="submit" class="btn primary" onclick="return confirm('¿Confirmas el envío masivo de correos a estos desarrolladores?');">Lanzar Campaña 🚀</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts de Interacción -->
    <script>
        const API_TEMPLATES = <?= json_encode($email_templates) ?>;
        const DUP_WINDOW_DAYS = <?= (int)$duplicate_window_days ?>;

        // Historial de contacto por usuario. Es lo que permite avisar ANTES de enviar;
        // el servidor vuelve a comprobarlo por su cuenta y es quien manda.
        let CONTACT_INFO = {};

        function reloadContactInfo() {
            const node = document.getElementById('contactInfoData');
            if (!node) return;
            try {
                CONTACT_INFO = JSON.parse(node.textContent) || {};
            } catch (e) {
                console.error('No se pudo leer el historial de contacto:', e);
            }
        }
        reloadContactInfo();

        let singleModalUserId = null;

        function contactInfoFor(userId) {
            return CONTACT_INFO[String(userId)] || null;
        }

        // Pinta el historial del destinatario en el modal individual
        function renderSingleHistory(userId) {
            const box = document.getElementById('modalSingleHistory');
            const info = contactInfoFor(userId);

            if (!info) {
                box.style.display = 'none';
                return;
            }

            if (info.unsubscribed) {
                box.style.background = '#fef2f2';
                box.style.border = '1.5px solid #fca5a5';
                box.style.color = '#991b1b';
                box.innerHTML = '🚫 <strong>Este usuario se dio de baja de las comunicaciones.</strong> '
                              + 'El envío será rechazado.';
                box.style.display = 'block';
                return;
            }

            if (!info.sent) {
                box.style.background = '#f0fdfa';
                box.style.border = '1.5px solid #5eead4';
                box.style.color = '#0f766e';
                box.innerHTML = '🆕 <strong>Nunca se le ha enviado ningún correo</strong> desde este panel.';
                box.style.display = 'block';
                return;
            }

            const reciente = info.days_since !== null && info.days_since <= 7;
            box.style.background = reciente ? '#fffbeb' : '#f8fafc';
            box.style.border = '1.5px solid ' + (reciente ? '#fde68a' : '#e2e8f0');
            box.style.color = reciente ? '#92400e' : '#334155';

            let cuando = info.days_since === 0 ? 'hoy'
                       : (info.days_since === 1 ? 'ayer' : 'hace ' + info.days_since + ' días');

            box.innerHTML = '📨 Ya ha recibido <strong>' + info.sent + ' correo(s)</strong>. '
                + 'El último, ' + cuando + ' (' + info.last_at + ')'
                + (info.last_subject ? ': «<strong>' + escapeHtml(info.last_subject) + '</strong>»' : '')
                + '. ' + (info.opened > 0 ? '👁️ Lo abrió.' : 'Sin aperturas registradas.');
            box.style.display = 'block';
        }

        // Aviso de asunto repetido en el modal individual
        function refreshSingleDuplicateWarning() {
            const warn = document.getElementById('modalSingleDupWarning');
            const info = contactInfoFor(singleModalUserId);
            const subject = (document.getElementById('modalSingleSubject').value || '').trim();

            let sentOn = null;
            if (info && info.recent_subjects && subject !== '') {
                sentOn = info.recent_subjects[subject] || null;
            }

            if (sentOn) {
                document.getElementById('modalSingleDupText').textContent =
                    'Ya se le envió este mismo asunto el ' + sentOn
                    + '. Se bloquea el envío para no repetirlo.';
                warn.style.display = 'block';
            } else {
                warn.style.display = 'none';
                document.getElementById('modalSingleAllowDup').checked = false;
            }

            refreshSingleSubmitState();
        }

        function refreshSingleSubmitState() {
            const warnVisible = document.getElementById('modalSingleDupWarning').style.display === 'block';
            const allowed = document.getElementById('modalSingleAllowDup').checked;
            const btn = document.getElementById('modalSingleSubmit');
            const block = warnVisible && !allowed;

            btn.disabled = block;
            btn.style.opacity = block ? '0.5' : '1';
            btn.style.cursor = block ? 'not-allowed' : 'pointer';
            btn.title = block ? 'Marca «Enviar de todas formas» para repetir este asunto' : '';
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Modales
        function openSingleEmailModal(userId, name, email, status) {
            singleModalUserId = userId;
            document.getElementById('modalSingleUserId').value = userId;
            document.getElementById('modalSingleUserDisplay').value = name + ' (' + email + ')';

            // Auto-seleccionar plantilla según estado
            let defaultTpl = 'api_near_limit';
            if (status === 'limit_reached') defaultTpl = 'api_limit_upgrade';
            else if (status === 'errors') defaultTpl = 'api_tech_support_errors';
            else if (status === 'inactive') defaultTpl = 'api_inactive_nudge';

            const select = document.getElementById('modalSingleTemplateSelect');
            select.value = defaultTpl;
            applySingleTemplate(defaultTpl);

            renderSingleHistory(userId);
            refreshSingleDuplicateWarning();

            document.getElementById('singleEmailModal').style.display = 'flex';
        }

        function closeSingleEmailModal() {
            document.getElementById('singleEmailModal').style.display = 'none';
        }

        function applySingleTemplate(tplId) {
            const tpl = API_TEMPLATES.find(t => t.id === tplId);
            if (tpl) {
                document.getElementById('modalSingleSubject').value = tpl.subject;
                document.getElementById('modalSingleMessage').value = tpl.body;
            }
            // Cambiar de plantilla cambia el asunto, así que hay que revisar el aviso
            refreshSingleDuplicateWarning();
        }

        function openBulkEmailModal() {
            const checkedBoxes = document.querySelectorAll('.user-select-checkbox:checked');
            const userIds = Array.from(checkedBoxes).map(cb => cb.value);
            if (userIds.length === 0) {
                alert('Debes seleccionar al menos un desarrollador.');
                return;
            }

            document.getElementById('modalBulkUserIds').value = userIds.join(',');
            document.getElementById('modalBulkRecipientsCount').textContent = userIds.length;

            const select = document.getElementById('modalBulkTemplateSelect');
            if (select.options.length > 1) {
                select.selectedIndex = 1;
                applyBulkTemplate(select.value);
            }

            renderBulkHistory(userIds);
            refreshBulkDuplicateWarning();

            document.getElementById('bulkEmailModal').style.display = 'flex';
        }

        function selectedUserIds() {
            const raw = document.getElementById('modalBulkUserIds').value || '';
            return raw.split(',').filter(v => v !== '');
        }

        // Resumen de contacto de los seleccionados
        function renderBulkHistory(userIds) {
            const box = document.getElementById('modalBulkHistory');
            let contactados = 0, nuevos = 0, bajas = 0;

            userIds.forEach(id => {
                const info = contactInfoFor(id);
                if (!info) { nuevos++; return; }
                if (info.unsubscribed) bajas++;
                if (info.sent > 0) contactados++; else nuevos++;
            });

            const hayContactados = contactados > 0;
            box.style.background = hayContactados ? '#fffbeb' : '#f0fdfa';
            box.style.border = '1.5px solid ' + (hayContactados ? '#fde68a' : '#5eead4');
            box.style.color = hayContactados ? '#92400e' : '#0f766e';

            let html = '<strong>' + nuevos + '</strong> sin contactar nunca · '
                     + '<strong>' + contactados + '</strong> ya han recibido algún correo';
            if (bajas > 0) {
                html += ' · <strong>' + bajas + '</strong> de baja (se omitirán)';
            }
            if (hayContactados) {
                html += '<div style="margin-top:8px;">'
                      + '<button type="button" onclick="deselectAlreadyContacted();" '
                      + 'style="background:#fff;border:1.5px solid #d97706;color:#b45309;font-weight:700;'
                      + 'font-size:0.78rem;padding:5px 10px;border-radius:8px;cursor:pointer;">'
                      + 'Quitar de la selección a los ya contactados</button></div>';
            }

            box.innerHTML = html;
            box.style.display = 'block';
        }

        // Deja seleccionados solo a los que nunca han recibido nada
        function deselectAlreadyContacted() {
            document.querySelectorAll('.user-select-checkbox:checked').forEach(cb => {
                const info = contactInfoFor(cb.value);
                if (info && info.sent > 0) cb.checked = false;
            });
            updateSelectedCount();

            const ids = Array.from(document.querySelectorAll('.user-select-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) {
                alert('No queda ningún desarrollador sin contactar en la selección. Se cierra el envío.');
                closeBulkEmailModal();
                return;
            }

            document.getElementById('modalBulkUserIds').value = ids.join(',');
            document.getElementById('modalBulkRecipientsCount').textContent = ids.length;
            renderBulkHistory(ids);
            refreshBulkDuplicateWarning();
        }

        // Aviso de asunto repetido en la campaña masiva
        function refreshBulkDuplicateWarning() {
            const warn = document.getElementById('modalBulkDupWarning');
            const subject = (document.getElementById('modalBulkSubject').value || '').trim();

            let repetidos = 0;
            if (subject !== '') {
                selectedUserIds().forEach(id => {
                    const info = contactInfoFor(id);
                    if (info && info.recent_subjects && info.recent_subjects[subject]) repetidos++;
                });
            }

            if (repetidos > 0) {
                document.getElementById('modalBulkDupText').textContent =
                    repetidos + ' de los seleccionados ya recibieron este mismo asunto en los últimos '
                    + DUP_WINDOW_DAYS + ' días.';
                warn.style.display = 'block';
            } else {
                warn.style.display = 'none';
                document.getElementById('modalBulkAllowDup').checked = false;
            }
        }

        function closeBulkEmailModal() {
            document.getElementById('bulkEmailModal').style.display = 'none';
        }

        function applyBulkTemplate(tplId) {
            const tpl = API_TEMPLATES.find(t => t.id === tplId);
            if (tpl) {
                document.getElementById('modalBulkSubject').value = tpl.subject;
                document.getElementById('modalBulkMessage').value = tpl.body;
            }
            refreshBulkDuplicateWarning();
        }

        // Selección múltiple
        function toggleSelectAll(masterCb) {
            const checkboxes = document.querySelectorAll('.user-select-checkbox');
            checkboxes.forEach(cb => cb.checked = masterCb.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.user-select-checkbox:checked').length;
            const countEl = document.getElementById('selectedCount');
            if (countEl) countEl.textContent = checked;
            const bar = document.getElementById('bulkActionsBar');
            if (bar) {
                if (checked > 0) {
                    bar.style.display = 'flex';
                } else {
                    bar.style.display = 'none';
                    const masterCb = document.getElementById('selectAllCheckbox');
                    if (masterCb) masterCb.checked = false;
                }
            }
        }

        // AJAX Engine para Filtros, Búsqueda y Periodos sin recargar la página
        let ajaxAbortCtrl = null;
        let searchDebounceTimer = null;

        function loadAnalyticsData(url, isInput = false, pushState = true) {
            const container = document.getElementById('analyticsDashboardContainer');
            if (!container) return;

            // Mantener foco y posición del cursor si se escribe en el buscador
            let cursorStart = null;
            let cursorEnd = null;
            let wasSearchActive = false;
            const activeEl = document.activeElement;
            if (isInput && activeEl && activeEl.name === 'q') {
                wasSearchActive = true;
                cursorStart = activeEl.selectionStart;
                cursorEnd = activeEl.selectionEnd;
            }

            if (ajaxAbortCtrl) {
                ajaxAbortCtrl.abort();
            }
            ajaxAbortCtrl = new AbortController();

            container.style.opacity = '0.45';
            container.style.pointerEvents = 'none';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: ajaxAbortCtrl.signal
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al obtener datos');
                return res.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('analyticsDashboardContainer');
                if (newContent) {
                    container.innerHTML = newContent.innerHTML;
                    if (pushState && window.location.href !== url) {
                        history.pushState(null, '', url);
                    }
                    reloadContactInfo();
                    initDashboardInteractions();
                    updateSelectedCount();

                    if (wasSearchActive) {
                        const newSearchInput = container.querySelector('input[name="q"]');
                        if (newSearchInput) {
                            newSearchInput.focus();
                            if (cursorStart !== null && cursorEnd !== null) {
                                newSearchInput.setSelectionRange(cursorStart, cursorEnd);
                            }
                        }
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('AJAX Analytics Error:', err);
                }
            })
            .finally(() => {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
        }

        function initDashboardInteractions() {
            const container = document.getElementById('analyticsDashboardContainer');
            if (!container) return;

            // Interceptar enlaces ajax (periodos, filtros de estado, reset)
            const links = container.querySelectorAll('a.ajax-filter-link');
            links.forEach(a => {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadAnalyticsData(this.href, false, true);
                });
            });

            // Formulario de búsqueda con submit y debounce en vivo
            const form = container.querySelector('form.ajax-search-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const params = new URLSearchParams(formData);
                    const targetUrl = this.getAttribute('action') + '?' + params.toString();
                    loadAnalyticsData(targetUrl, false, true);
                });

                const inputQ = form.querySelector('input[name="q"]');
                if (inputQ) {
                    inputQ.addEventListener('input', function() {
                        clearTimeout(searchDebounceTimer);
                        searchDebounceTimer = setTimeout(() => {
                            const formData = new FormData(form);
                            const params = new URLSearchParams(formData);
                            const targetUrl = form.getAttribute('action') + '?' + params.toString();
                            loadAnalyticsData(targetUrl, true, true);
                        }, 350);
                    });
                }
            }

            // Selector de ordenación dinámico
            const sortSelect = container.querySelector('select.ajax-sort-select');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const form = container.querySelector('form.ajax-search-form');
                    if (form) {
                        const formData = new FormData(form);
                        formData.set('sort', this.value);
                        const params = new URLSearchParams(formData);
                        const targetUrl = form.getAttribute('action') + '?' + params.toString();
                        loadAnalyticsData(targetUrl, false, true);
                    }
                });
            }
        }

        // Navegación atrás / adelante
        window.addEventListener('popstate', function() {
            loadAnalyticsData(window.location.href, false, false);
        });

        // Inicializar listeners al cargar el documento
        document.addEventListener('DOMContentLoaded', function() {
            initDashboardInteractions();
        });
    </script>
<?= $this->endSection() ?>
