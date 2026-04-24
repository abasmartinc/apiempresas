<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar B2B - Centro de Prospección Inteligente',
        'excerptText' => 'Identifica nuevas oportunidades de negocio en tiempo real con el Radar de APIEmpresas.',
    ]) ?>

    <!-- Leaflet.js for Radar Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">
    
    <!-- Radar Web Tour -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />
    <link rel="stylesheet" href="<?= base_url('public/css/radar-tour.css?v=' . time()) ?>" />
</head>
<body>

<?php
// isFree ya viene del controlador
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr) || $dateStr === '0000-00-00') return 'Reciente';
    $timestamp = strtotime($dateStr);
    if (!$timestamp) return 'Reciente';
    // Rechazar fechas futuras
    if ($timestamp > time()) return 'Reciente';
    $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return str_replace($mesesEn, $mesesEs, date($format, $timestamp));
};

$allCompanies = $companies ?? [];
$limitFree = 3; // Limitado a 3 empresas visibles para usuarios gratis
$visibleCompanies = $isFree ? array_slice($allCompanies, 0, $limitFree) : $allCompanies;
$lockedCompanies  = $isFree ? array_slice($allCompanies, $limitFree, 5) : [];
?>

<div class="ae-radar-page">
    <div class="ae-radar-page__shell">

        <aside class="ae-radar-page__sidebar">

            <div class="ae-radar-page__brand">
                <a href="<?=site_url() ?>" class="ae-radar-page__brand-header">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                            <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                            </filter>
                            <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                            </filter>
                            <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                                <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                            </radialGradient>
                            <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                                <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                            </linearGradient>
                        </defs>
                        <g filter="url(#ve-cardShadow)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                            <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                        </g>
                        <path d="M18 33 L28 43 L46 22"
                               stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                               fill="none" filter="url(#ve-checkShadow)"/>
                    </svg>
                    <div class="brand-text">
                        <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                        <span class="brand-tag">Verificación empresarial</span>
                    </div>
                </a>

                <small class="ae-radar-page__brand-note">
                    Inteligencia comercial en tiempo real
                </small>
            </div>

            <div class="ae-radar-page__sidebar-body">
                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Radar</span>

                    <a href="<?= site_url('radar') ?>" class="ae-radar-page__nav-link is-active">
                        <span class="ae-radar-page__nav-icon">📊</span>
                        Dashboard principal
                    </a>

                    <a href="<?= site_url('radar/favoritos') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">⭐</span>
                        Mis favoritos
                    </a>

                    <a href="<?= site_url('leads-empresas-nuevas') ?>" class="ae-radar-page__nav-link" id="radar_to_excel_cross_sell">
                        <span class="ae-radar-page__nav-icon">📥</span>
                        Descargar listado puntual
                    </a>

                    <a href="<?= site_url('api-empresas') ?>" class="ae-radar-page__nav-link" id="radar_to_api_cross_sell">
                        <span class="ae-radar-page__nav-icon">🔌</span>
                        API para desarrolladores
                    </a>

                    <a href="<?= site_url('radar/kanban') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">📋</span>
                        Embudo (Kanban)
                    </a>

                    <a href="<?= site_url('radar/trends') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">📈</span>
                        Análisis de Tendencias
                    </a>
                    
                    <a href="<?= site_url('billing/invoices') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">🧾</span>
                        Mis facturas
                    </a>
                </div>

                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Alertas</span>
                    <div class="ae-radar-page__nav-teaser">
                        <span class="ae-radar-page__nav-icon">🔔</span>
                        <span>Alertas email</span>
                        <span class="ae-radar-page__mini-badge">Próximamente</span>
                    </div>
                </div>

                <div class="ae-radar-page__roi-box">
                    <div class="ae-radar-page__roi-title">Calculadora ROI</div>
                    <div class="ae-radar-page__roi-text">La mayoría de usuarios recupera la inversión con su primer cliente. Solo 1 cierre paga <strong>5 años</strong> de Radar PRO.</div>
                    <div class="ae-radar-page__roi-stat">
                        <span>Rentabilidad estimada</span>
                        <strong>+450%</strong>
                    </div>
                </div>
            </div>

            <div class="ae-radar-page__sidebar-footer">
                <a href="<?= site_url('dashboard') ?>" class="ae-radar-page__nav-link">
                    <span class="ae-radar-page__nav-icon">🏠</span>
                    Volver al portal
                </a>

                <a href="<?= site_url('logout') ?>" class="ae-radar-page__nav-link">
                    <span class="ae-radar-page__nav-icon">🚪</span>
                    Cerrar sesión
                </a>
            </div>
        </aside>

        <main class="ae-radar-page__main">
            
            <?php 
            $source = service('request')->getGet('source');
            if (session('just_bought_excel') || $source === 'excel'): 
            ?>
            <div class="radar-welcome-banner" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 24px; border-radius: 16px; margin-bottom: 32px; border: 1px solid rgba(255,255,255,0.1); position: relative; overflow: hidden;">
                <div style="position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 24px;">
                    <div>
                        <h2 style="color: #fff; font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">¡Bienvenido al Radar PRO!</h2>
                        <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.5; margin: 0;">
                            <?php if ($source === 'excel'): ?>
                                Estas oportunidades son <strong>nuevas</strong> respecto al listado que descargaste. <br>
                                Monitoriza el mercado en tiempo real para no llegar tarde.
                            <?php else: ?>
                                Ya tienes tu listado, pero el mercado no se detiene. <br>
                                Aquí puedes ver las empresas que se han creado <strong>hoy mismo</strong> en tu sector.
                            <?php endif; ?>
                        </p>
                    </div>
                    <button onclick="this.parentElement.parentElement.style.display='none'; trackGlobalEvent('radar_banner_close');" style="background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; white-space: nowrap;"> Entendido </button>
                </div>
                <div style="position: absolute; right: -20px; top: -20px; opacity: 0.1; transform: rotate(-15deg);">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                </div>
            </div>
            <?php session()->remove('just_bought_excel'); ?>
            <?php endif; ?>
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>APIEmpresas</span>
                    <span>/</span>
                    <strong>Radar B2B</strong>
                </div>

                <div class="ae-radar-page__topbar-actions">
                    <div class="ae-radar-page__freshness">
                        <span class="ae-radar-page__freshness-dot"></span>
                        Última actualización: <strong><?= $freshness['lastUpdate'] ?></strong> 
                        <span class="ae-radar-page__freshness-sep">|</span>
                        Hoy: <strong>+<?= number_format($freshness['todayCount']) ?></strong> empresas
                    </div>

                    <?php if ($isFree) { ?>
                        <div class="ae-radar-page__pill ae-radar-page__pill--free">
                            Plan Free · Vista limitada
                        </div>

                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=' . esc($source)) ?>" class="ae-radar-page__cta-top" style="background: #2563eb; color: #ffffff; border: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);">
                            Desbloquear todas las oportunidades ahora
                        </a>
                    <?php } else { ?>
                        <?php if (isset($userPlan['status']) && $userPlan['status'] === 'canceled') { ?>
                            <a href="<?= site_url('billing') ?>" class="ae-radar-page__pill ae-radar-page__pill--live" style="text-decoration:none; background:#fef2f2; border:1px solid #fee2e2; color:#ef4444;" title="Gestionar facturación">
                                <span class="ae-radar-page__pulse" style="background:#ef4444;"></span>
                                Cancelada (Acceso hasta <?= date('d/m/Y', strtotime($userPlan['period_end'])) ?>)
                            </a>
                        <?php } else { ?>
                            <a href="<?= site_url('billing') ?>" class="ae-radar-page__pill ae-radar-page__pill--live" style="text-decoration:none;" title="Gestionar facturación">
                                <span class="ae-radar-page__pulse"></span>
                                Suscripción activa
                            </a>
                            <button type="button" class="ae-radar-page__cta-top" style="background:transparent; border:1px solid #ef4444; color:#ef4444; cursor:pointer;" onclick="cancelRadarSubscription()">
                                Cancelar PRO
                            </button>
                        <?php } ?>
                    <?php } ?>
                </div>
            </header>

            <style>
                .ae-radar-page { background: #e2e8f0 !important; }
                .ae-radar-page__content { background: transparent !important; padding: 40px !important; }
                .ae-radar-page__container { max-width: 100% !important; margin: 0 auto !important; }
            </style>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">
                    
                    <!-- ⚡ CONTADOR DE USO (MANDATORIO) -->
                    <div class="ae-usage-counter" style="margin-bottom: 24px; background: #ffffff; border-radius: 16px; padding: 20px 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">📊</div>
                            <div>
                                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #1e293b;">
                                    <?php if ($isFree) { ?>
                                        Estás viendo solo una parte — las oportunidades más valiosas están bloqueadas
                                    <?php } else { ?>
                                        Acceso completo ilimitado a las <?= number_format($pagination['total']) ?> oportunidades
                                    <?php } ?>
                                </h3>
                                <p style="margin: 4px 0 0; font-size: 13px; color: #64748b; font-weight: 500;">
                                    <?php if ($isFree) { ?>
                                        <span style="color: #ef4444; font-weight: 700;">Estás viendo <?= count($visibleCompanies) ?> de <?= number_format($pagination['total']) ?> oportunidades disponibles.</span> Estas empresas están activas ahora mismo y ya están siendo contactadas.
                                    <?php } else { ?>
                                        Explora todos los leads detectados hoy en tiempo real.
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <?php if ($isFree) { ?>
                            <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=' . esc($source)) ?>" class="ae-radar-page__cta-top" style="margin: 0; background: #0f172a; color: #ffffff;">
                                Desbloquear todas las oportunidades ahora
                            </a>
                        <?php } ?>
                    </div>
                    
                    <!-- INICIO SECCION SUPERIOR (KPIs) -->
                    <div class="ae-radar-page-top-section" style="width: 100%;">

                    <!-- BLOQUE: ACTIVIDAD DEL DÍA (NUDGE DIARIO) -->
                    <div class="ae-radar-page__daily-nudge" style="margin-bottom: 20px; background: linear-gradient(90deg, #eff6ff 0%, #ffffff 100%); border: 1px solid #dbeafe; border-radius: 12px; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.05);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 20px; animation: pulse 2s infinite;">🔥</span>
                            <div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #1e40af;">
                                    Las empresas detectadas hoy suelen ser las más valiosas (+<?= number_format($stats['hoy']) ?> detectadas hoy)
                                </h4>
                                <div style="display: flex; gap: 12px; margin-top: 2px;">
                                    <span style="font-size: 12px; color: #60a5fa; font-weight: 700;">• <?= max(1, round($stats['hoy'] * 0.7)) ?> leads de alta prioridad detectados</span>
                                    <span style="font-size: 12px; color: #10b981; font-weight: 700;">• Otros equipos comerciales ya están trabajando estas oportunidades</span>
                                </div>
                            </div>
                        </div>
                        <div style="flex-shrink: 0;">
                            <span style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; background: white; padding: 4px 10px; border-radius: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 6px; height: 6px; background: #22c55e; border-radius: 50%; display: block;"></span>
                                Sistema en vivo
                            </span>
                        </div>
                    </div>

                    <style>
                        @keyframes pulse {
                            0% { transform: scale(1); }
                            50% { transform: scale(1.1); }
                            100% { transform: scale(1); }
                        }
                        .ae-status-select-chip.status-bg-nuevo { background-color: #f1f5f9; color: #64748b; }
                        .ae-status-select-chip.status-bg-contactado { background-color: #fff7ed; color: #ea580c; border-color: #ffedd5; }
                        .ae-status-select-chip.status-bg-seguimiento { background-color: #eff6ff; color: #2563eb; border-color: #dbeafe; }
                        .ae-status-select-chip.status-bg-negociacion { background-color: #faf5ff; color: #9333ea; border-color: #f3e8ff; }
                        .ae-status-select-chip.status-bg-ganado { background-color: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
                    </style>
                    <section class="ae-radar-page__hero <?= !$isFree ? 'ae-radar-page__hero--pro' : '' ?>" style="padding: 24px 32px; min-height: auto; margin-bottom: 20px; position: relative;">
                        <div class="ae-radar-page__hero-glass"></div>
                        <div class="ae-radar-page__hero-glow"></div>
                        
                        <div class="ae-radar-page__hero-grid" style="display: grid; grid-template-columns: 1fr 340px; align-items: center; gap: 40px; position: relative; z-index: 10;">
                            <!-- Lado Izquierdo: Titular -->
                            <div>
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                    <div class="ae-radar-page__eyebrow" style="margin-bottom: 0; font-size: 11px;">
                                        Nuevas constituciones · captación B2B
                                    </div>
                                    <button class="js-radar-tour-btn js-start-radar-tour" style="display: flex; align-items: center; gap: 6px; padding: 4px 12px; background: white; border: 1px solid #e2e8f0; border-radius: 999px; color: #475569; font-size: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-transform: uppercase;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" style="width: 12px; height: 12px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                        </svg>
                                        Guía
                                    </button>
                                </div>

                                <h1 class="ae-radar-page__hero-title" style="font-size: 32px; margin-bottom: 8px; line-height: 1.1; color: #0f172a !important; font-weight: 800;">
                                    Consigue clientes nuevos antes que tu competencia
                                </h1>

                                <p class="ae-radar-page__hero-text" style="font-size: 14px; margin-bottom: 24px; max-width: 600px; color: #64748b; font-weight: 500; line-height: 1.4;">
                                    Tu pipeline de ventas inteligente: detectamos empresas en fase inicial con alta probabilidad de compra.
                                </p>

                                <!-- BLOQUE PRINCIPAL DE PIPELINE (ROI REFINADO V2) -->
                                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; margin-bottom: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); position: relative; overflow: hidden;">
                                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; position: relative; z-index: 2;">
                                        <div style="display: flex; flex-direction: column; gap: 14px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <span style="font-size: 18px;">💼</span>
                                                <span style="font-size: 14px; font-weight: 700; color: #1e293b;"><?= number_format($pipelineMetrics['total_opps']) ?> oportunidades detectadas</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <span style="font-size: 18px;">🎯</span>
                                                <span style="font-size: 14px; font-weight: 700; color: #1e293b;"><?= $pipelineMetrics['clients_label'] ?> clientes reales <span style="font-weight: 500; color: #64748b; font-size: 12px;">(según conv. media)</span></span>
                                            </div>
                                            <div style="margin-top: 4px; font-size: 12px; font-weight: 600; color: #64748b; display: flex; align-items: center; gap: 6px;">
                                                🚀 Otros equipos comerciales ya están trabajando estas oportunidades
                                            </div>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 16px; border-left: 1px solid #f1f5f9; padding-left: 24px;">
                                            <div>
                                                <span style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Potencial económico</span>
                                                <div style="font-size: 22px; font-weight: 900; color: #2563eb; letter-spacing: -0.5px; margin-top: 2px;">
                                                    💰 Estas oportunidades pueden generar entre 300.000€ y <?= $metricsService->formatCurrency($pipelineMetrics['pipeline_max']) ?> <span style="font-size: 12px; font-weight: 700; color: #64748b; display: block; margin-top: -2px;">en pipeline comercial potencial detectado</span>
                                                </div>
                                            </div>
                                            
                                            <button type="button" onclick="document.getElementById('radar-results-container').scrollIntoView({behavior:'smooth'})" 
                                                    style="background: #2563eb; color: white; height: 42px; width: 100%; border-radius: 12px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); display: flex; align-items: center; justify-content: center; gap: 8px;">
                                                Ir a las mejores oportunidades
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><path d="M7 13l5 5 5-5M7 6l5 5 5-5"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- ROI Highlight Footer -->
                                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 14px;">✅</span>
                                            <span style="font-size: 13px; font-weight: 700; color: #10b981;"><?= $pipelineMetrics['roi_message'] ?> · Con 1 cliente cubres el coste mensual</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: #ea580c; text-transform: uppercase;">
                                            <span style="width: 6px; height: 6px; background: #ea580c; border-radius: 50%; display: block; animation: pulse 2s infinite;"></span>
                                            Ventana de contacto activa
                                        </div>
                                    </div>
                                </div>

                            <?php if ($isFree) { ?>
                                <div class="ae-radar-page__hero-actions" style="margin-top: 20px;">
                                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__hero-btn ae-radar-page__hero-btn--primary" style="height: 42px; padding: 0 24px; font-size: 13px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">
                                        Desbloquear todas las oportunidades ahora
                                    </a>
                                    <div style="margin-top: 10px; font-size: 11px; font-weight: 600; color: #64748b;">
                                        ✔ Acceso inmediato · ✔ Sin permanencia · ✔ Recupera la inversión con 1 cliente
                                    </div>
                                </div>
                            <?php } ?>
                            </div>

                            <!-- Lado Derecho: Actividad Realtime -->
                            <div style="background: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 20px; border: 1px solid #ffffff; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; gap: 14px;">
                                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.7px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                                    Actividad de prospección
                                </div>
                                
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 18px;">✨</span>
                                        <span style="font-size: 13px; font-weight: 700; color: #475569;">nuevos clientes potenciales hoy</span>
                                    </div>
                                    <div style="font-size: 20px; font-weight: 800; color: #0f172a;"><?= number_format($stats['hoy']) ?></div>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 18px;">📅</span>
                                        <span style="font-size: 13px; font-weight: 700; color: #475569;">oportunidades esta semana</span>
                                    </div>
                                    <div style="font-size: 20px; font-weight: 800; color: #0f172a;"><?= number_format($stats['semana']) ?></div>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 18px;">📈</span>
                                        <span style="font-size: 13px; font-weight: 700; color: #475569;">en fase activa de compra</span>
                                    </div>
                                    <div style="font-size: 20px; font-weight: 800; color: #0f172a;"><?= number_format($stats['mes']) ?></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- BLOQUE DE PROGRESO CRM (Ajuste 1) -->
                    <div class="ae-radar-page__crm-progress" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 24px; border-radius: 20px; color: white; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2); position: relative; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <div style="position: absolute; right: -40px; top: -40px; width: 150px; height: 150px; background: rgba(37, 99, 235, 0.1); border-radius: 50%; opacity: 0.2; filter: blur(40px);"></div>
                        
                        <div style="display: flex; flex-direction: column; gap: 20px; position: relative; z-index: 2;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                                        <span>📊</span> Estás trabajando sobre clientes detectados hoy
                                    </h3>
                                    <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.7); font-weight: 500;">
                                        Gestiona tu embudo de ventas y asegura el cierre de cada oportunidad.
                                        <span style="display: block; margin-top: 6px; color: #10b981; font-weight: 800; font-size: 14px; letter-spacing: -0.2px;">💰 Valor total estimado hoy: <?= $todayMetrics['pipeline_label'] ?></span>
                                    </p>
                                </div>
                                
                                <div style="display: flex; gap: 16px; align-items: center;">
                                    <!-- Contactados -->
                                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 12px 20px; border-radius: 14px; text-align: center; min-width: 140px;">
                                        <div style="font-size: 24px; font-weight: 900; color: #10b981; line-height: 1;"><?= number_format($crmStats['contactado'] ?? 0) ?></div>
                                        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; color: rgba(16, 185, 129, 0.8);">clientes contactados</div>
                                    </div>
                                    
                                    <!-- Seguimiento -->
                                    <div style="background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(37, 99, 235, 0.3); padding: 12px 20px; border-radius: 14px; text-align: center; min-width: 140px;">
                                        <div style="font-size: 24px; font-weight: 900; color: #3b82f6; line-height: 1;"><?= number_format($crmStats['seguimiento'] ?? 0) ?></div>
                                        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; color: rgba(37, 99, 235, 0.8);">en seguimiento</div>
                                    </div>
                                    
                                    <!-- Oportunidades Pendientes -->
                                    <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); padding: 12px 20px; border-radius: 14px; text-align: center; min-width: 140px;">
                                        <div style="font-size: 24px; font-weight: 900; color: rgba(255,255,255,0.9); line-height: 1;"><?= number_format($crmStats['nuevo'] ?? 0) ?></div>
                                        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; color: rgba(255,255,255,0.4);">oportunidades disponibles</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.05); pt-4; padding-top: 12px;">
                                <div style="font-size: 13px; font-weight: 700; color: #3b82f6;">
                                    👉 Sigue con las oportunidades recomendadas para avanzar hoy
                                </div>
                                <div style="font-size: 12px; font-weight: 800; color: #10b981;">
                                    🔥 Varias de estas empresas dejarán de estar disponibles hoy. Actúa rápido.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE DE INTELIGENCIA ESTRATÉGICA -->
                    <div class="ae-radar-page__intel-stats" style="background: white; padding: 24px; border-radius: 16px; color: #1e293b; margin-bottom: 20px; position: relative; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 10px; color: #0f172a;">
                                        <span>🧠</span> Oportunidades listas para contactar hoy
                                    </h3>
                                    <p style="margin: 0; font-size: 14px; color: #64748b; font-weight: 500; max-width: 700px;">
                                        Empresas recién creadas con mayor probabilidad de contratar servicios en sus primeros días.
                                        <span style="display: block; margin-top: 4px; color: #2563eb; font-weight: 700; font-size: 13px;">💰 Valor estimado de estas oportunidades: <?= $intelMetrics['pipeline_label'] ?></span>
                                    </p>
                                </div>
                                <a href="<?= site_url('radar?' . http_build_query(array_merge($filters, ['priority_level' => 'muy_alta', 'rango' => '7', 'intel' => 'active']))) ?>#radar-results-container" 
                                   class="ae-radar-page__hero-btn ae-radar-page__hero-btn--primary" 
                                   style="height: 44px; padding: 0 24px; font-size: 13px; margin: 0; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                                    🎯 Ver oportunidades prioritarias
                                </a>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                                <div style="background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">🔥</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #475569;"><?= round(($freshness['todayCount'] ?? 250) * 0.15) ?> empresas con alta probabilidad de compra</span>
                                </div>
                                <div style="background: #fffbeb; padding: 12px 16px; border-radius: 10px; border: 1px solid #fef3c7; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">⏱</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #475569;"><?= round(($freshness['todayCount'] ?? 250) * 0.28) ?> en su mejor momento de contacto</span>
                                </div>
                                <div style="background: #eff6ff; padding: 12px 16px; border-radius: 10px; border: 1px solid #dbeafe; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">💰</span>
                                    <span style="font-size: 13px; font-weight: 700; color: #475569;">15+ con ticket estimado alto</span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px dashed #e2e8f0;">
                                <p style="margin: 0; font-size: 13px; font-weight: 700; color: #2563eb;">
                                    💡 Empieza por las empresas con mayor score para maximizar probabilidad de cierre.
                                </p>
                                <?php if (isset($_GET['intel']) && $_GET['intel'] === 'active') { ?>
                                    <div style="background: #2563eb; color: white; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 999px; display: flex; align-items: center; gap: 6px;">
                                        <span style="width: 6px; height: 6px; background: white; border-radius: 50%; display: block;"></span>
                                        Filtro activo: Mejores oportunidades
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                    <!-- BLOQUE: TOP OPORTUNIDADES -->
                    <?php if (!empty($visibleCompanies)) { ?>
                        <div class="ae-radar-page__top-picks" style="margin-top: 40px; margin-bottom: 32px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span>🎯</span> Empieza por estas empresas hoy
                            </h4>
                            <p style="margin: 0 0 24px 0; font-size: 13px; color: #64748b; font-weight: 500;">
                                Seleccionadas automáticamente por mayor score y mejor momento de contacto.
                            </p>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                                <?php 
                                                   // Sincronización Top Scoring: Asegurar que mostramos lo mejor de lo mejor basándonos en IA total
                                    $topPicks = $visibleCompanies;
                                    usort($topPicks, fn($a, $b) => ($b['lead_score_data']['numeric'] ?? 0) <=> ($a['lead_score_data']['numeric'] ?? 0));
                                    $top3 = array_slice($topPicks, 0, 3); 
                                    
                                    foreach ($top3 as $index => $co): 
                                        $scoreData = $co['lead_score_data'] ?? ['numeric' => (int)($co['score_total'] ?? 0), 'base' => (int)($co['score_total'] ?? 0)];
                                        $scoreTotal = (int)round($scoreData['numeric']);
                                        $isBoosted = ($scoreTotal > (int)round($scoreData['base']));
                                        $prioKey = $co['priority_level'] ?? 'media';
                                        
                                        // Umbrales 70/40 (Optimización de Interés Radar)
                                        $scoreColor = '#94a3b8'; $scoreProb = 'Baja probabilidad'; $scoreIcon = '⚪'; $scoreBg = 'rgba(148, 163, 184, 0.1)';
                                        if ($scoreTotal >= 70) {
                                            $scoreColor = '#10b981'; $scoreBg = 'rgba(16, 185, 129, 0.1)'; $scoreProb = 'Alta probabilidad'; $scoreIcon = '🟢';
                                        } elseif ($scoreTotal >= 40) {
                                            $scoreColor = '#f59e0b'; $scoreBg = 'rgba(245, 158, 11, 0.1)'; $scoreProb = 'Interés medio'; $scoreIcon = '🟡';
                                        }

                                        $isFirst = ($index === 0);
                                        
                                        // Micro-urgencia: Detectada hoy o hace X días
                                        $days = ($scoreTotal >= 70) ? 0 : (($scoreTotal >= 40) ? rand(1, 2) : rand(3, 5));
                                        $timingLabel = ($days == 0) ? 'Detectada hoy' : "Hace $days días";

                                        $timingText = ($days <= 2) ? '🚀 Contactar de inmediato' : "⏱ Contactar en $days días";
                                        $btnText = "🚀 Contactar ahora";
                                        
                                        $containerStyle = $isFirst 
                                            ? "background: #ffffff; border: 2px solid #2563eb; border-radius: 16px; padding: 20px; transition: all 0.2s; position: relative; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.15); display: flex; flex-direction: column; height: 100%; transform: scale(1.02); z-index: 5;"
                                            : "background: rgba(255, 255, 255, 0.6); border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; transition: all 0.2s; position: relative; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); display: flex; flex-direction: column; height: 100%;";
                                ?>
                                    <div style="<?= $containerStyle ?>">
                                        <?php if ($isFirst) { ?>
                                            <div style="position: absolute; top: -14px; left: 20px; background: #2563eb; color: white; padding: 4px 12px; border-radius: 999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);">
                                                🔥 Mejor oportunidad del día
                                            </div>
                                        <?php } ?>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b; display: flex; align-items: center; gap: 6px;">
                                                    <span style="width: 6px; height: 6px; background: <?= $scoreColor ?>; border-radius: 50%; display: block;"></span>
                                                    Sugerencia #<?= $index + 1 ?>
                                                </div>
                                                <div style="font-size: 10px; font-weight: 800; color: #10b981; background: #f0fdf4; padding: 2px 8px; border-radius: 4px; border: 1px solid #dcfce7;">
                                                    <?= $timingLabel ?>
                                                </div>
                                            </div>
                                        
                                        <h5 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.4;" title="<?= esc($co['company_name']) ?>">
                                            <?= esc($co['company_name']) ?>
                                        </h5>
                                        
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; flex-grow: 1;">
                                            <!-- SCORE PROTAGONISTA EN TARJETA -->
                                            <div style="background: <?= $scoreBg ?>; border: 1px solid <?= $scoreColor ?>; padding: 4px 10px; border-radius: 6px; display: inline-flex; width: fit-content; align-items: center; gap: 6px;">
                                                <span style="font-weight: 900; font-size: 12px; color: <?= $scoreColor ?>; letter-spacing: -0.5px;">
                                                    <?= $scoreIcon ?> <?= $scoreTotal ?>/100
                                                </span>
                                                <span style="font-size: 10px; font-weight: 800; color: <?= $scoreColor ?>; text-transform: uppercase; letter-spacing: 0.5px;">
                                                    — <?= $scoreProb ?>
                                                </span>
                                            </div>
                                            
                                            <div style="font-size: 12px; font-weight: 700; color: #374151;">
                                                <?= $timingText ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($isFree) { ?>
                                            <button type="button" 
                                                    onclick="showConversionNudge()"
                                                    style="width: 100%; background: #2563eb; border: none; border-radius: 10px; padding: 12px; font-size: 11px; font-weight: 900; color: white; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); letter-spacing: 0.02em;">
                                                <?= $btnText ?>
                                            </button>
                                        <?php } else { ?>
                                            <button type="button" 
                                                    onclick="analyzeAI('<?= $co['id'] ?>', this, '<?= esc($co['company_name']) ?>')"
                                                    style="width: 100%; background: #2563eb; border: none; border-radius: 10px; padding: 12px; font-size: 11px; font-weight: 900; color: white; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); letter-spacing: 0.02em;">
                                                <?= $btnText ?>
                                            </button>
                                        <?php } ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($isFree) { ?>
                                <div class="radar-paywall-main" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: white; padding: 64px 32px; border-radius: 28px; text-align: center; margin: 40px 0; box-shadow: 0 25px 70px rgba(0,0,0,0.5); position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); min-height: 420px; display: flex; align-items: center; justify-content: center;">
                                    <!-- Decorative Glow -->
                                    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(37,99,235,0.2) 0%, transparent 60%); pointer-events: none;"></div>
                                    
                                    <div style="position: relative; z-index: 1; width: 100%;">
                                        <div style="display: inline-block; background: rgba(37,99,235,0.2); color: #60a5fa; padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 800; margin-bottom: 20px; border: 1px solid rgba(37,99,235,0.3);">
                                            📈 +<?= number_format($freshness['todayCount'] ?? 94) ?> empresas detectadas hoy
                                        </div>

                                        <h2 style="font-size: 38px; font-weight: 900; margin-bottom: 16px; color: white !important; letter-spacing: -1.5px; line-height: 1.1;">Estas empresas están siendo contactadas ahora mismo</h2>
                                        
                                        <p style="font-size: 18px; color: rgba(255,255,255,0.8); margin-bottom: 32px; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 500;">
                                            Las empresas detectadas hoy suelen ser las primeras en contratar.<br>
                                            <span style="color: #60a5fa; font-weight: 800;">Estas oportunidades desaparecen cuando otro proveedor las contacta.</span>
                                        </p>

                                        <div style="margin-bottom: 40px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                                            <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" style="display: inline-block; background: #2563eb; color: white; padding: 20px 56px; border-radius: 18px; font-size: 20px; font-weight: 900; text-decoration: none; box-shadow: 0 10px 30px rgba(37,99,235,0.5); transition: all 0.3s; transform-origin: center;" onmouseover="this.style.transform='scale(1.05) translateY(-2px)'; this.style.boxShadow='0 15px 40px rgba(37,99,235,0.6)';" onmouseout="this.style.transform='scale(1) translateY(0)'; this.style.boxShadow='0 10px 30px rgba(37,99,235,0.5)';">
                                                Acceder ahora antes que tu competencia
                                            </a>
                                            <div style="font-size: 14px; color: #fbbf24; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                                                💰 La mayoría de usuarios recupera la inversión con su primer cliente
                                            </div>
                                        </div>
                                        </div>

                                        <div class="paywall-benefits" style="display: flex; justify-content: center; gap: 32px; margin: 0 auto; opacity: 0.7;">
                                            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700;"><span style="color: #10b981;">✔</span> Acceso completo</div>
                                            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700;"><span style="color: #10b981;">✔</span> Filtros avanzados</div>
                                            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700;"><span style="color: #10b981;">✔</span> Exportación leads</div>
                                            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700;"><span style="color: #10b981;">✔</span> Ventaja competitiva</div>
                                        </div>
                                    </div>

                                    <!-- Overlay para frustración visual del contenido inferior -->
                                    <div class="paywall-overlay" style="position: absolute; bottom: -200px; left: 0; width: 100%; height: 400px; background: linear-gradient(transparent, rgba(15, 23, 42, 0.9)); pointer-events: none; z-index: 2;"></div>
                                </div>
                            <?php } ?>
                        </div>
                        </div>
<?php } ?>
                    </div> <!-- FIN CARD SUPERIOR (KPIs) -->

                    <!-- SEPARADOR VISUAL PREMIUM -->
                    <div style="margin: 56px 0 40px 0; display: flex; align-items: center; gap: 20px; opacity: 0; animation: fadeUp 0.6s ease forwards 0.3s;">
                        <div style="flex-grow: 1; height: 1px; background: linear-gradient(to right, rgba(226, 232, 240, 0), rgba(148, 163, 184, 0.4));"></div>
                        <div style="font-size: 11px; font-weight: 900; color: #334155; text-transform: uppercase; letter-spacing: 0.15em; background: #ffffff; padding: 12px 28px; border-radius: 100px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 4px 10px -5px rgba(37, 99, 235, 0.1); display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 14px; animation: pulse 2s infinite;">🎯</span> 
                            Explorar Base de Datos
                        </div>
                        <div style="flex-grow: 1; height: 1px; background: linear-gradient(to left, rgba(226, 232, 240, 0), rgba(148, 163, 184, 0.4));"></div>
                    </div> <!-- FIN SEPARADOR VISUAL PREMIUM -->

                    <!-- INICIO FONDO BUSCADOR Y TABLA -->
                    <div style="<?= $isFree ? 'filter: blur(8px); opacity: 0.4; pointer-events: none; user-select: none; position: relative;' : '' ?> background: #ffffff; padding: 40px; border-radius: 32px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04); border: 1px solid #e1e7f0; margin-bottom: 40px;">
                        <?php if ($isFree) { ?>
                            <div style="position: absolute; inset: 0; z-index: 100; cursor: not-allowed;" onclick="showConversionNudge('Buscador bloqueado', 'Activa Radar PRO para filtrar y explorar toda la base de datos de empresas.')"></div>
                        <?php } ?>

                    <form action="<?= site_url('radar') ?>" method="GET" class="ae-radar-page__filters <?= $isFree ? 'is-locked' : '' ?>" style="padding: 16px 24px; margin-bottom: 20px;">
                        <div class="ae-radar-page__filters-grid" style="grid-template-columns: 1fr 1fr 1fr auto; gap: 20px; align-items: flex-end;">
                            <div class="ae-radar-page__field">
                                <label style="font-size: 11px; margin-bottom: 6px;">Nicho / Palabras clave</label>
                                <input type="text" name="q" 
                                       class="ae-radar-page__input" 
                                       style="height: 38px; font-size: 13px;"
                                       placeholder="Ej: energía solar..." 
                                       value="<?= esc($filters['q'] ?? '') ?>"
                                       <?= $isFree ? 'disabled' : '' ?>>
                            </div>

                            <div class="ae-radar-page__field">
                                <label style="font-size: 11px; margin-bottom: 6px;">Provincia</label>
                                <select name="provincia" class="ae-radar-page__select" style="height: 38px; font-size: 13px;" <?= $isFree ? 'disabled' : '' ?>>
                                    <option value="">Toda España</option>
                                    <?php foreach ($provinces as $p): ?>
                                        <option value="<?= url_title($p['name'], '-', true) ?>" <?= ($filters['provincia'] === url_title($p['name'], '-', true)) ? 'selected' : '' ?>>
                                            <?= esc($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ae-radar-page__field">
                                <label style="font-size: 11px; margin-bottom: 6px;">Ventana temporal</label>
                                <select name="rango" class="ae-radar-page__select" style="height: 38px; font-size: 13px;" <?= $isFree ? 'disabled' : '' ?>>
                                    <option value="hoy" <?= ($filters['rango'] === 'hoy') ? 'selected' : '' ?>>Hoy mismo</option>
                                    <option value="7" <?= ($filters['rango'] === '7') ? 'selected' : '' ?>>Últimos 7 días</option>
                                    <option value="30" <?= ($filters['rango'] === '30') ? 'selected' : '' ?>>Últimos 30 días</option>
                                    <option value="90" <?= ($filters['rango'] === '90') ? 'selected' : '' ?>>Últimos 90 días</option>
                                </select>
                            </div>

                            <div class="ae-radar-page__field">
                                <?php if ($isFree) { ?>
                                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__filters-cta ae-radar-page__filters-cta--locked" style="height: 38px; font-size: 12px; padding: 0 16px;">
                                        Activar PRO
                                    </a>
                                <?php } else { ?>
                                    <button type="submit" class="ae-radar-page__filters-cta" style="height: 38px; font-size: 13px; padding: 0 16px;">
                                        Ver oportunidades
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                    
                    <!-- SMART FILTERS / ESTRATÉGICOS REFINADOS -->
                    <div class="ae-radar-page__smart-filters" style="margin-bottom: 32px; background: #fdfdfd; padding: 24px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                        <?php 
                            $activeFilter = 'todas';
                            $filterLabel = 'Mostrando todas las empresas registradas recientemente';
                            
                            if (isset($_GET['intel']) && $_GET['intel'] === 'active') {
                                $activeFilter = 'mejores';
                                $filterLabel = '🔥 Mostrando oportunidades prioritarias (Score > 80)';
                            } elseif (isset($_GET['status']) && $_GET['status'] === 'nuevo') {
                                $activeFilter = 'sin_ver';
                                $filterLabel = '🆕 Mostrando clientes potenciales sin contactar';
                            } elseif (($filters['priority_level'] ?? '') === 'muy_alta') {
                                $activeFilter = 'alta';
                                $filterLabel = '🟢 Mostrando empresas con alta probabilidad de cierre';
                            } elseif (($filters['rango'] ?? '') === '7') {
                                $activeFilter = 'ventana';
                                $filterLabel = '⏱ Mostrando leads en momento óptimo de contacto';
                            } elseif (isset($_GET['ai']) && $_GET['ai'] === 'active') {
                                $activeFilter = 'ai';
                                $filterLabel = '🎯 Mostrando recomendaciones inteligentes del sistema';
                            }
                        ?>

                        <div style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            Filtrar por tipo de oportunidad
                        </div>

                        <div style="font-size: 12px; font-weight: 800; color: #2563eb; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; background: #eff6ff; padding: 8px 16px; border-radius: 8px; width: fit-content;">
                            <span style="font-size: 14px;">🔎</span>
                            <?= $filterLabel ?>
                        </div>

                        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                            <?php
                            $chips = [
                                'todas' => ['label' => 'Todas las oportunidades', 'icon' => '📊', 'url' => site_url('radar')],
                                'sin_ver' => ['label' => 'Sin contactar', 'icon' => '🆕', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['status' => 'nuevo', 'intel' => null, 'priority_level' => null, 'rango' => null, 'ticket' => null, 'ai' => null, 'min_score' => null])))],
                                'alta' => ['label' => 'Alta probabilidad de cierre', 'icon' => '🟢', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['priority_level' => 'muy_alta', 'intel' => null, 'rango' => null, 'ticket' => null, 'ai' => null, 'status' => null, 'min_score' => null])))],
                                'ventana' => ['label' => 'Momento óptimo de contacto', 'icon' => '⏱', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['rango' => '7', 'intel' => null, 'priority_level' => null, 'ticket' => null, 'ai' => null, 'status' => null, 'min_score' => null])))],
                                'mejores' => ['label' => 'Score alto (>80)', 'icon' => '🔥', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['min_score' => 75, 'intel' => 'active', 'priority_level' => null, 'rango' => null, 'ticket' => null, 'ai' => null, 'status' => null])))],
                                'ai' => ['label' => 'Recomendadas por el sistema', 'icon' => '🎯', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['ai' => 'active', 'min_score' => 60, 'intel' => null, 'priority_level' => null, 'rango' => null, 'ticket' => null, 'status' => null])))]
                            ];

                            foreach ($chips as $key => $chip):
                                $isActive = ($activeFilter === $key);
                                $style = $isActive 
                                    ? "background: #2563eb; color: white; border-color: #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);"
                                    : "background: white; color: #475569; border-color: #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);";
                            ?>
                                <a href="<?= $chip['url'] ?>" 
                                   class="ae-radar-page__filter-chip"
                                   style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 20px; font-size: 13px; font-weight: 700; border-radius: 12px; border: 1px solid; transition: all 0.2s; text-decoration: none; <?= $style ?>">
                                    <span style="font-size: 14px;"><?= $chip['icon'] ?></span>
                                    <?= $chip['label'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>


                        <section class="ae-radar-page__lead-wrap <?= $isFree ? 'is-paywalled' : '' ?>">
                            <div id="radar-results-container">
                                <?= view('radar/partials/results_table', array_merge($filters, [
                                    'companies' => $visibleCompanies, 
                                    'isFree' => $isFree,
                                    'pagination' => $pagination ?? null,
                                    'pager' => $pager ?? null,
                                    'filters' => $filters ?? []
                                ])) ?>
                            </div>

                            <div id="radar-map-view" style="display:none;">
                                <div id="radar-leaflet-map" style="height:600px; width:100%; border-radius:20px; border:1px solid #e2e8f0; background:#f8fafc; z-index:1;"></div>
                            </div>
                    </section>

                    </div> <!-- FIN FONDO BUSCADOR Y TABLA -->

                </div>
            </div>
            
            <footer class="ae-radar-page__footer">
                &copy; <?= date('Y') ?> APIEmpresas · Inteligencia comercial para captación B2B
            </footer>
        </main>
    </div>
</div>

    <!-- Modal QuickView -->
    <div id="ae-qv-modal" class="ae-qv-modal" style="display:none;">
        <div class="ae-qv-modal__backdrop" onclick="closeQuickView()"></div>
        <div class="ae-qv-modal__container">
            <div id="ae-qv-content" class="ae-qv-modal__content">
                <!-- Se cargará por AJAX -->
            </div>
        </div>
    </div>

    <?= view('radar/partials/ai_modal') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script>
    // Auto-scroll si el filtro inteligente está activo
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('intel') === 'active') {
            const resultsContainer = document.getElementById('radar-results-container');
            if (resultsContainer) {
                // Scroll suave al listado
                setTimeout(() => {
                    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 500);
            }
        }
    });

    /**
     * Abre el modal de QuickView por ID de empresa
     */
    function openQuickView(id) {
        if (!id) return;

        const $modal = document.getElementById('ae-qv-modal');
        const $content = document.getElementById('ae-qv-content');
        
        // Mostrar modal vacío con loading
        $content.innerHTML = '<div style="padding:100px; text-align:center; color:#64748b;"><div class="ae-spinner"></div><p style="margin-top:16px; font-weight:600;">Cargando información...</p></div>';
        $modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        fetch('<?= site_url('radar/quickview/') ?>' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Not found');
            return response.text();
        })
        .then(html => {
            $content.innerHTML = html;
        })
        .catch(err => {
            $content.innerHTML = '<div style="padding:48px; text-align:center; color:#64748b;">' +
                '<p style="font-size:18px; font-weight:700; color:#1e293b; margin-bottom:8px;">Error al cargar</p>' +
                '<p>Ha ocurrido un problema al recuperar los datos. Inténtalo de nuevo.</p>' +
                '<button type="button" class="ae-qv__btn ae-qv__btn--text" onclick="closeQuickView()" style="margin-top:24px;">Cerrar</button>' +
                '</div>';
        });
    }

    /**
     * Cierra el modal
     */
    function closeQuickView() {
        document.getElementById('ae-qv-modal').style.display = 'none';
        document.body.style.overflow = '';
    }

    /**
     * Alternar favorito (Estrella)
     */
    function toggleFavorite(btn, companyId) {
        const isActive = btn.classList.contains('is-active');
        const $svg = btn.querySelector('svg');
        
        // Optimistic UI update
        btn.classList.toggle('is-active');
        if (!isActive) {
            $svg.setAttribute('fill', 'currentColor');
            btn.title = 'Quitar de favoritos';
        } else {
            $svg.setAttribute('fill', 'none');
            btn.title = 'Guardar en favoritos';
        }

        const formData = new FormData();
        formData.append('company_id', companyId);

        fetch('<?= site_url('radar/toggle-favorite') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                // Rollback if error
                btn.classList.toggle('is-active');
                if (isActive) {
                    $svg.setAttribute('fill', 'currentColor');
                } else {
                    $svg.setAttribute('fill', 'none');
                }
                alert('No se pudo guardar en favoritos. Inténtalo de nuevo.');
            }
        })
        .catch(err => {
            console.error('Error toggling favorite:', err);
            alert('Error de conexión.');
        });
    }

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickView();
            closeAIModal();
        }
    });

    // --- Radar Map Logic ---
    let radarMap = null;
    let mapMarkers = [];
    const provinceCoords = {
        'ALAVA': [42.8467, -2.6716], 'ALBACETE': [38.9944, -1.8585], 'ALICANTE': [38.3452, -0.4815],
        'ALMERIA': [36.8340, -2.4637], 'ASTURIAS': [43.3614, -5.8593], 'AVILA': [40.6567, -4.7002],
        'BADAJOZ': [38.8794, -6.9706], 'BALEARS, ILLES': [39.5696, 2.6502], 'BARCELONA': [41.3851, 2.1734],
        'BIZKAIA': [43.2630, -2.9350], 'BURGOS': [42.3439, -3.6969], 'CACERES': [39.4753, -6.3722],
        'CADIZ': [36.5271, -6.2886], 'CANTABRIA': [43.4623, -3.8099], 'CASTELLON': [39.9864, -0.0513],
        'CEUTA': [35.8894, -5.3198], 'CIUDAD REAL': [38.9848, -3.9273], 'CORDOBA': [37.8882, -4.7794],
        'CORUNA, A': [43.3623, -8.4115], 'CUENCA': [40.0704, -2.1374], 'GIPUZKOA': [43.3128, -1.9812],
        'GIRONA': [41.9794, 2.8214], 'GRANADA': [37.1773, -3.5986], 'GUADALAJARA': [40.6327, -3.1643],
        'HUELVA': [37.2614, -6.9447], 'HUESCA': [42.1362, -0.4087], 'JAEN': [37.7796, -3.7849],
        'LEON': [42.5987, -5.5670], 'LLEIDA': [41.6176, 0.6200], 'LUGO': [43.0121, -7.5581],
        'MADRID': [40.4168, -3.7038], 'MALAGA': [36.7213, -4.4214], 'MELILLA': [35.2923, -2.9381],
        'MURCIA': [37.9922, -1.1307], 'NAVARRA': [42.8125, -1.6458], 'OURENSE': [42.3358, -7.8639],
        'PALENCIA': [42.0095, -4.5284], 'PALMAS, LAS': [28.1235, -15.4363], 'PONTEVEDRA': [42.4336, -8.6480],
        'RIOJA, LA': [42.4627, -2.4450], 'SALAMANCA': [40.9701, -5.6635], 'SANTA CRUZ DE TENERIFE': [28.4636, -16.2518],
        'SEGOVIA': [40.9429, -4.1088], 'SEVILLA': [37.3891, -5.9845], 'SORIA': [41.7640, -2.4688],
        'TARRAGONA': [41.1189, 1.2445], 'TERUEL': [40.3457, -1.1065], 'TOLEDO': [39.8628, -4.0273],
        'VALENCIA': [39.4699, -0.3763], 'VALLADOLID': [41.6523, -4.7245], 'ZAMORA': [41.5033, -5.7462],
        'ZARAGOZA': [41.6488, -0.8891], 'GUIPUZCOA': [43.3128, -1.9812], 'VIZCAYA': [43.2630, -2.9350]
    };

    function switchView(view) {
        // Actualizar UI de botones
        document.querySelectorAll('.ae-view-btn').forEach(btn => {
            btn.classList.remove('is-active');
            btn.style.background = 'transparent';
            btn.style.color = '#64748b';
            btn.style.boxShadow = 'none';
        });
        
        const activeBtn = document.querySelector(`.ae-view-btn[data-view="${view}"]`);
        if (activeBtn) {
            activeBtn.classList.add('is-active');
            activeBtn.style.background = 'white';
            activeBtn.style.color = '#2563eb';
            activeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        }

        if (view === 'list') {
            document.getElementById('radar-list-view').show ? $('#radar-list-view').show() : document.getElementById('radar-list-view').style.display = 'block';
            document.getElementById('radar-map-view').hide ? $('#radar-map-view').hide() : document.getElementById('radar-map-view').style.display = 'none';
        } else {
            document.getElementById('radar-list-view').hide ? $('#radar-list-view').hide() : document.getElementById('radar-list-view').style.display = 'none';
            document.getElementById('radar-map-view').show ? $('#radar-map-view').show() : document.getElementById('radar-map-view').style.display = 'block';
            setTimeout(initRadarMap, 100);
        }
    }

    function initRadarMap() {
        if (radarMap) {
            radarMap.invalidateSize();
            loadMapData(); // FORCE DATA REFRESH 
            return;
        }

        radarMap = L.map('radar-leaflet-map').setView([40.4168, -3.7038], 6);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(radarMap);

        loadMapData();
    }

    function loadMapData() {
        const filterForm = document.querySelector('.ae-radar-page__filters');
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        // Limpiar marcadores previos
        mapMarkers.forEach(m => radarMap.removeLayer(m));
        mapMarkers = [];

        fetch('<?= site_url('radar/map-data') ?>?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) return;

            data.forEach(item => {
                const province = item.province.toUpperCase();
                const coords = provinceCoords[province];
                
                if (coords) {
                    const radius = Math.max(12, Math.min(60, 8 + (parseInt(item.total) * 0.8)));
                    const circle = L.circleMarker(coords, {
                        radius: radius,
                        fillColor: "#2563eb",
                        color: "#fff",
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.7
                    }).addTo(radarMap);

                    circle.bindPopup(`
                        <div style="font-family: inherit; padding: 5px;">
                            <strong style="color: #1e293b; font-size: 14px;">${item.province}</strong><br>
                            <span style="color: #2563eb; font-weight: 700; font-size: 18px;">${item.total}</span> 
                            <span style="color: #64748b; font-size: 12px;">nuevas empresas</span>
                        </div>
                    `);

                    mapMarkers.push(circle);
                }
            });
        });
    }

    /**
     * Actualizar estado del Lead (Mini CRM)
     */
    function updateLeadStatus(select, companyId) {
        const status = select.value;
        const formData = new FormData();
        formData.append('company_id', companyId);
        formData.append('status', status);

        // Actualizar clase visual inmediatamente para feedback instantáneo
        select.className = 'ae-status-select-chip status-bg-' + status;
        select.style.opacity = '0.7';

        fetch('<?= site_url('radar/update-favorite-status') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            select.style.opacity = '1';
            if (data.status === 'success') {
                // Sincronización automática con Favoritos:
                // Si el estado es distinto a 'nuevo', marcar automáticamente como favorito
                const container = select.closest('.ae-radar-page__company-actions');
                if (container) {
                    const favBtn = container.querySelector('.ae-radar-page__btn-fav');
                    if (favBtn && status !== 'nuevo') {
                        if (!favBtn.classList.contains('is-active')) {
                            favBtn.classList.add('is-active');
                            const svg = favBtn.querySelector('svg');
                            if (svg) {
                                svg.setAttribute('fill', '#ffb800');
                                svg.setAttribute('stroke', '#ffb800');
                                svg.style.color = '#ffb800';
                            }
                            favBtn.title = 'Quitar de favoritos';
                        }
                    }
                }
                
                // Notificación visual premium
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        background: '#f8fafc'
                    });
                }
            }
        })
        .catch(err => {
            select.style.opacity = '1';
            console.error('Error al actualizar estado:', err);
        });
    }

    /**
     * Alternar favorito (AJAX)
     */
    function toggleFavorite(btn, companyId) {
        const isActive = btn.classList.contains('is-active');
        const $svg = btn.querySelector('svg');
        
        // Optimistic UI update
        btn.classList.toggle('is-active');
        if (!isActive) {
            $svg.setAttribute('fill', 'currentColor');
            btn.title = 'Quitar de favoritos';
        } else {
            $svg.setAttribute('fill', 'none');
            btn.title = 'Guardar en favoritos';
        }

        const formData = new FormData();
        formData.append('company_id', companyId);

        fetch('<?= site_url('radar/toggle-favorite') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                // Rollback if error
                btn.classList.toggle('is-active');
                if (isActive) {
                    $svg.setAttribute('fill', 'currentColor');
                } else {
                    $svg.setAttribute('fill', 'none');
                }
                alert('No se pudo guardar en favoritos. Inténtalo de nuevo.');
            }
        })
        .catch(err => {
            console.error('Error toggling favorite:', err);
            alert('Error de conexión.');
        });
    }

    /**
     * Modal de conversión proactivo con tracking y refuerzo de ROI
     */
    window.showConversionNudge = function(title, text, metadata = {}) {
        // Permitir UNA acción gratuita (QuickView o Contacto) para demostrar valor
        if (!localStorage.getItem('radar_free_action_used')) {
            const actionType = metadata.action || 'view';
            const companyId = metadata.id;
            
            if (companyId) {
                localStorage.setItem('radar_free_action_used', 'true');
                if (actionType === 'contact') {
                    // Si intentaba contactar, disparamos el modal real de contacto
                    if (typeof handleContactClick === 'function') {
                        handleContactClick(null, companyId, metadata.name || 'Empresa');
                        return;
                    }
                } else {
                    // Si intentaba ver, abrimos el QuickView real
                    if (typeof openQuickView === 'function') {
                        openQuickView(companyId);
                        return;
                    }
                }
            }
        }

        // Tracking de intención real
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                'event': 'contact_attempt_blocked',
                'nudge_title': title,
                'nudge_text': text,
                'metadata': metadata
            });
        }
        
        // Ocultar banner flotante si está visible para evitar solapamiento
        const banner = document.getElementById('ae-floating-banner');
        if (banner) banner.style.bottom = '-100px';

        // Log event to server for CRM tracking
        fetch('<?= site_url('radar/log-event') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `lead_id=${metadata.id || ''}&action=blocked_click&<?= csrf_token() ?>=<?= csrf_hash() ?>`
        });

        const defaultTitle = 'Esta empresa puede cerrarse con otro proveedor ahora mismo';
        const defaultText = 'Accede ahora y contacta antes que otros proveedores.<br>Esta empresa está en proceso de decisión.';
        
        // Generar ticket estimado aleatorio para el copy (simulando IA)
        const minTicket = Math.floor(Math.random() * (15000 - 3000 + 1)) + 3000;
        const maxTicket = minTicket * (Math.floor(Math.random() * 3) + 2);
        const formatMoney = (val) => new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(val);

        Swal.fire({
            title: title || defaultTitle,
            icon: 'warning',
            iconHtml: '⚡',
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            html: `
                <div style="text-align: center; padding: 0 10px;">
                    <p style="font-size: 13px; color: #ef4444; font-weight: 800; margin: -5px 0 16px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Si no actúas ahora, perderás esta oportunidad frente a tu competencia
                    </p>

                    <div style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px; border-radius: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.1);">
                        <span style="font-size: 24px;">💰</span>
                        <div style="text-align: left;">
                            <div style="font-size: 11px; font-weight: 800; color: #16a34a; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 1px;">Valor estimado que puedes perder ahora mismo</div>
                            <div style="font-size: 19px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">${formatMoney(minTicket)} - ${formatMoney(maxTicket)}</div>
                        </div>
                    </div>

                    <p style="font-size: 16px; color: #334155; line-height: 1.5; margin-bottom: 24px; font-weight: 600;">
                        ${text || defaultText}
                    </p>
                    
                    <div style="margin-bottom: 24px;">
                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=modal_nudge') ?>" style="display: block; background: #2563eb; color: #ffffff; padding: 18px; border-radius: 16px; font-weight: 950; text-decoration: none; font-size: 19px; box-shadow: 0 10px 25px rgba(37,99,235,0.4); transition: all 0.3s; opacity: 0; transform: translateY(10px); animation: ae-fade-up 0.4s forwards 0.2s;" onmouseover="this.style.transform='translateY(-2px) scale(1.02)';" onmouseout="this.style.transform='translateY(0) scale(1)';" id="ae-modal-cta">
                            Contactar antes que otros proveedores
                        </a>
                        <p style="margin-top: 12px; font-size: 12px; color: #64748b; font-weight: 800; letter-spacing: 0.2px;">
                            ⚡ Acceso inmediato · Sin tarjeta · Empieza en menos de 10 segundos
                        </p>
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 18px; display: flex; flex-direction: column; gap: 8px;">
                        <p style="margin: 0; font-size: 13px; color: #f97316; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">
                            🔥 Varias oportunidades detectadas hoy dejarán de estar disponibles en horas
                        </p>
                        <p style="margin: 0; font-size: 12px; color: #475569; font-weight: 700;">
                            La mayoría de usuarios recupera la inversión con su primer cliente
                        </p>
                    </div>
                </div>
                <style>
                    @keyframes ae-fade-up {
                        to { opacity: 1; transform: translateY(0); }
                    }
                </style>
            `,
            showConfirmButton: false, 
            showCancelButton: true,
            cancelButtonText: 'Seguir viendo solo 3 oportunidades',
            showCloseButton: true,
            focusCancel: true,
            customClass: {
                popup: 'ae-premium-modal',
                cancelButton: 'ae-modal-cancel-btn'
            }
        });
    }

    window.updateResultsWithPerPage = function(perPage) {
        const filterForm = document.querySelector('.ae-radar-page__filters');
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set('per_page', perPage);
        
        const url = filterForm.action + '?' + params.toString();
        updateResults(url);
    };

    function updateResults(url) {
        const container = document.getElementById('radar-results-container');
        if (!container) return;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';
        
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            
            // Si el mapa estaba cargado, avisarle que los datos podrían haber cambiado
            if (radarMap) loadMapData();
        })
        .catch(err => {
            console.error('Error loading results:', err);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('radar-results-container');
        const filterForm = document.querySelector('.ae-radar-page__filters');

        // Delegación de eventos para paginación (sobreviene a reemplazos de innerHTML)
        document.addEventListener('click', function(e) {
            const link = e.target.closest('#radar-results-container .ae-radar-page__pagination a');
            if (link) {
                e.preventDefault();
                updateResults(link.href);
                // Scroll suave arriba del listado
                const wrap = document.querySelector('.ae-radar-page__lead-wrap');
                if (wrap) wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const params = new URLSearchParams(formData);
                const url = this.action + '?' + params.toString();
                updateResults(url);
            });
        }

        // Manejo de clics en el sidebar (AJAX)
        document.querySelectorAll('[data-sidebar-cnae]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const cnae = this.getAttribute('data-sidebar-cnae');
                const sectorSelect = document.querySelector('select[name="cnae"]');
                
                if (sectorSelect) {
                    sectorSelect.value = cnae;
                    filterForm.dispatchEvent(new Event('submit'));
                }
                
                document.querySelectorAll('[data-sidebar-cnae]').forEach(l => l.classList.remove('is-active'));
                this.classList.add('is-active');
            });
        });
    });

    function cancelRadarSubscription() {
        Swal.fire({
            title: '¿Cancelar suscripción PRO?',
            html: 'Lamentamos que te vayas. Seguirás teniendo acceso a <strong>Radar PRO</strong> hasta el final de tu periodo de facturación actual.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar PRO',
            cancelButtonText: 'Mantener plan',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: 've-swal',
                title: 've-swal-title',
                htmlContainer: 've-swal-text',
                confirmButton: 'btn danger ve-swal-confirm',
                cancelButton: 'btn btn_header--ghost ve-swal-cancel',
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.querySelector('.ae-radar-page__cta-top');
                const originalHtml = btn.innerHTML;
                if(btn) {
                    btn.innerHTML = 'Cancelando...';
                    btn.style.opacity = '0.7';
                    btn.disabled = true;
                }

                fetch('<?= site_url("billing/cancel-subscription") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'ajax=1&<?= csrf_token() ?>=<?= csrf_hash() ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Cancelada',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                popup: 've-swal',
                                title: 've-swal-title',
                                htmlContainer: 've-swal-text',
                                confirmButton: 'btn btn_primary ve-swal-confirm',
                            },
                            buttonsStyling: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error desconocido');
                    }
                })
                .catch(err => {
                    if(btn) {
                        btn.innerHTML = originalHtml;
                        btn.style.opacity = '1';
                        btn.disabled = false;
                    }
                    Swal.fire({
                        title: 'Error', 
                        text: err.message || 'No se ha podido procesar la baja. Inténtalo de nuevo o contáctanos.', 
                        icon: 'error',
                        customClass: {
                            popup: 've-swal',
                            title: 've-swal-title',
                            htmlContainer: 've-swal-text',
                            confirmButton: 'btn btn_primary ve-swal-confirm',
                        },
                        buttonsStyling: false
                    });
                });
            }
        });
    }
    </script>
    
    <!-- Radar Web Tour -->
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <script src="<?= base_url('public/js/radar-tour.js?v=' . time()) ?>"></script>
    <!-- Modal de Intención (Trigger de Scroll Profundo) -->
    <?php if ($isFree) { ?>
    <div id="ae-intent-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.8); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px);">
        <div style="background:#ffffff; border-radius:24px; max-width:500px; width:100%; padding:40px; text-align:center; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
            <button onclick="document.getElementById('ae-intent-modal').style.display='none'" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:24px; color:#94a3b8; cursor:pointer;">&times;</button>
            <div style="font-size:48px; margin-bottom:20px;">🎯</div>
            <h3 style="font-size:24px; font-weight:800; color:#0f172a; margin-bottom:16px;">Estás explorando oportunidades reales</h3>
            <p style="font-size:16px; color:#475569; line-height:1.6; margin-bottom:32px;">Activa Radar PRO para acceder a todas y empezar a trabajar leads desde hoy.</p>
            <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=' . esc($source)) ?>" style="display:block; background:#2563eb; color:#ffffff; padding:18px; border-radius:12px; font-weight:800; text-decoration:none; font-size:16px; box-shadow:0 10px 20px rgba(37,99,235,0.2);" onclick="trackUpgradeClick('intent_modal')">Activar acceso completo (79€/mes)</a>
            <p style="margin-top:20px; font-size:13px; color:#94a3b8; font-weight:600;">Sin permanencia · Cancela cuando quieras</p>
        </div>
    </div>

    <!-- Banner Flotante (Trigger por Scroll) -->
    <div id="ae-floating-banner" style="position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%); background: #0f172a; color: white; padding: 16px 32px; border-radius: 100px; box-shadow: 0 10px 40px rgba(0,0,0,0.4); display: flex; align-items: center; gap: 20px; z-index: 9999; transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); width: fit-content; border: 1px solid rgba(255,255,255,0.1);">
        <div style="font-size: 14px; font-weight: 700; white-space: nowrap;">
            ⚡ Estás viendo oportunidades reales ahora mismo.
        </div>
        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=' . esc($source)) ?>" style="background: #2563eb; color: white; padding: 8px 20px; border-radius: 50px; text-decoration: none; font-size: 13px; font-weight: 900; white-space: nowrap;" onclick="trackUpgradeClick('floating_banner')">
            Activar acceso completo (79€/mes)
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Track View
        $(document).ready(function() {
            // No mostrar banner si ya hay un modal abierto (prevención)
            const isModalOpen = () => document.querySelector('.swal2-shown');

            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: 'radar_view',
                source: '<?= esc($source) ?>'
            });
        });

        function trackUpgradeClick(location) {
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: 'upgrade_click',
                source: '<?= esc($source) ?>',
                metadata: JSON.stringify({ location: location })
            });
        }

        // Trigger de Intención: Mostrar banner flotante al llegar al paywall
        window.addEventListener('scroll', function() {
            const banner = document.getElementById('ae-floating-banner');
            const isSwalOpen = document.body.classList.contains('swal2-shown');
            
            if (window.scrollY > 500 && !sessionStorage.getItem('floating_banner_dismissed') && !isSwalOpen) {
                banner.style.bottom = '40px';
            } else {
                banner.style.bottom = '-100px';
            }
        });

        // Trigger por interacción: Al intentar usar elementos bloqueados
        let interactionCount = 0;
        document.addEventListener('click', function(e) {
            const blockedBtn = e.target.closest('button[onclick*="showConversionNudge"]');
            const blurredRow = e.target.closest('.ae-radar-row-blurred');
            
            if (blockedBtn || blurredRow) {
                interactionCount++;
                
                // Si es el segundo intento, forzamos un mensaje más agresivo que sobrescriba el onclick individual si fuera necesario
                if (interactionCount >= 2 && !sessionStorage.getItem('intent_modal_shown_aggressive')) {
                    // Detenemos la propagación para que el onclick del botón no dispare el nudge normal
                    if (blockedBtn) e.stopImmediatePropagation();
                    
                    showConversionNudge('Otros equipos ya están trabajando estas empresas', 'Activa Radar PRO ahora para no perder estas oportunidades. La velocidad es clave en la captación B2B. Con 1 cliente cubres el coste mensual.');
                    sessionStorage.setItem('intent_modal_shown_aggressive', 'true');
                    
                    // Ocultar banner flotante si salta el modal
                    const banner = document.getElementById('ae-floating-banner');
                    if (banner) banner.style.display = 'none';
                    sessionStorage.setItem('floating_banner_dismissed', 'true');
                }
            }
        }, true); // Capturing phase para interceptar antes del onclick

    </script>
    <style>
        .ae-premium-modal {
            border-radius: 32px !important;
            padding: 24px !important;
            border: 1px solid rgba(255,255,255,0.2) !important;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3) !important;
            font-family: 'Inter', sans-serif !important;
        }
        .ae-premium-modal .swal2-title {
            font-family: 'Outfit', sans-serif !important;
            font-weight: 900 !important;
            font-size: 26px !important;
            letter-spacing: -0.03em !important;
            color: #0f172a !important;
            padding-top: 10px !important;
        }
        .ae-modal-cancel-btn {
            background: transparent !important;
            color: #64748b !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            text-decoration: underline !important;
            border: none !important;
            box-shadow: none !important;
            margin-top: 10px !important;
        }
        .ae-modal-cancel-btn:hover {
            color: #1e293b !important;
        }
    </style>
    <?php } ?>
    <script>
        $(document).ready(function() {
            $('#radar_to_excel_cross_sell').on('click', function() {
                trackGlobalEvent('radar_to_excel_click');
            });
            $('#radar_to_api_cross_sell').on('click', function() {
                trackGlobalEvent('radar_to_api_click');
            });

            function trackGlobalEvent(type, metadata = {}) {
                $.post('<?= site_url("api/tracking/event") ?>', {
                    event_type: type,
                    source: 'radar_dashboard',
                    metadata: JSON.stringify(metadata)
                });
            }
        });
    </script>
</body>
</html>