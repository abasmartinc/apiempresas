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

    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=5') ?>">
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
$visibleCompanies = $isFree ? array_slice($allCompanies, 0, 10) : $allCompanies;
$lockedCompanies  = $isFree ? array_slice($allCompanies, 10, 4) : [];
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
                    <div class="ae-radar-page__roi-text">Solo 1 cliente conseguido con este radar paga <strong>5 años</strong> de suscripción.</div>
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

                    <?php if ($isFree): ?>
                        <div class="ae-radar-page__pill ae-radar-page__pill--free">
                            Plan Free · Vista limitada
                        </div>

                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__cta-top">
                            Activar Radar PRO (79€)
                        </a>
                    <?php else: ?>
                        <?php if (isset($userPlan['status']) && $userPlan['status'] === 'canceled'): ?>
                            <a href="<?= site_url('billing') ?>" class="ae-radar-page__pill ae-radar-page__pill--live" style="text-decoration:none; background:#fef2f2; border:1px solid #fee2e2; color:#ef4444;" title="Gestionar facturación">
                                <span class="ae-radar-page__pulse" style="background:#ef4444;"></span>
                                Cancelada (Acceso hasta <?= date('d/m/Y', strtotime($userPlan['period_end'])) ?>)
                            </a>
                        <?php else: ?>
                            <a href="<?= site_url('billing') ?>" class="ae-radar-page__pill ae-radar-page__pill--live" style="text-decoration:none;" title="Gestionar facturación">
                                <span class="ae-radar-page__pulse"></span>
                                Suscripción activa
                            </a>
                            <button type="button" class="ae-radar-page__cta-top" style="background:transparent; border:1px solid #ef4444; color:#ef4444; cursor:pointer;" onclick="cancelRadarSubscription()">
                                Cancelar PRO
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </header>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">

                    <section class="ae-radar-page__hero <?= !$isFree ? 'ae-radar-page__hero--pro' : '' ?>">
                        <div class="ae-radar-page__hero-glass"></div>
                        <div class="ae-radar-page__hero-glow"></div>
                        <div class="ae-radar-page__hero-grid">
                            <div>
                                <div class="ae-radar-page__eyebrow">
                                    Nuevas constituciones · captación B2B
                                </div>

                                <h1 class="ae-radar-page__hero-title">
                                    Radar de constituciones para <span class="ae-radar-page__hero-title-grad">detectar clientes</span> antes que tu competencia
                                </h1>

                                <p class="ae-radar-page__hero-text">
                                    Descubre nuevas empresas registradas en España y convierte la información societaria en oportunidades comerciales reales.
                                    <?php if ($isFree): ?>
                                        Con Radar PRO desbloqueas el acceso completo al listado, filtros avanzados y exportaciones listas para tu equipo.
                                    <?php endif; ?>
                                </p>

                                <?php if ($isFree): ?>
                                    <div class="ae-radar-page__hero-actions">
                                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__hero-btn ae-radar-page__hero-btn--primary">
                                            Desbloquear Radar PRO (79€)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($isFree): ?>
                                <div class="ae-radar-page__hero-aside">
                                    <div class="ae-radar-page__hero-aside-title">Qué desbloqueas con PRO</div>

                                    <ul class="ae-radar-page__hero-list">
                                        <li>
                                            <span class="ae-radar-page__hero-dot"></span>
                                            <span>Acceso completo al listado de empresas registradas en el periodo seleccionado.</span>
                                        </li>
                                        <li>
                                            <span class="ae-radar-page__hero-dot"></span>
                                            <span>Filtros estratégicos para encontrar oportunidades por zona y sector.</span>
                                        </li>
                                        <li>
                                            <span class="ae-radar-page__hero-dot"></span>
                                            <span>Exportación a Excel para campañas comerciales, CRM y prospección.</span>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="ae-radar-page__metrics">
                        <!-- Hoy -->
                        <article class="ae-radar-page__metric ae-radar-page__metric--today">
                            <div class="ae-radar-page__metric-glow"></div>
                            <div class="ae-radar-page__metric-glass"></div>
                            
                            <div class="ae-radar-page__metric-content">
                                <div class="ae-radar-page__metric-header">
                                    <div class="ae-radar-page__metric-icon-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path><path d="M5 3v4"></path><path d="M19 17v4"></path><path d="M3 5h4"></path><path d="M17 19h4"></path></svg>
                                    </div>
                                    <div class="ae-radar-page__metric-label">
                                        Registradas hoy
                                        <span class="ae-radar-page__pulse"></span>
                                    </div>
                                </div>
                                <div class="ae-radar-page__metric-body">
                                    <p class="ae-radar-page__metric-value"><?= number_format($stats['hoy']) ?></p>
                                    <p class="ae-radar-page__metric-help">Constituciones en tiempo real</p>
                                </div>
                            </div>
                        </article>

                        <!-- Semana -->
                        <article class="ae-radar-page__metric ae-radar-page__metric--week">
                            <div class="ae-radar-page__metric-glow"></div>
                            <div class="ae-radar-page__metric-glass"></div>
                            
                            <div class="ae-radar-page__metric-content">
                                <div class="ae-radar-page__metric-header">
                                    <div class="ae-radar-page__metric-icon-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                                    </div>
                                    <div class="ae-radar-page__metric-label">Últimos 7 días</div>
                                </div>
                                <div class="ae-radar-page__metric-body">
                                    <p class="ae-radar-page__metric-value"><?= number_format($stats['semana']) ?></p>
                                    <p class="ae-radar-page__metric-help">Volumen de prospección semanal</p>
                                </div>
                            </div>
                        </article>

                        <!-- Mes -->
                        <article class="ae-radar-page__metric ae-radar-page__metric--month">
                            <div class="ae-radar-page__metric-glow"></div>
                            <div class="ae-radar-page__metric-glass"></div>
                            
                            <div class="ae-radar-page__metric-content">
                                <div class="ae-radar-page__metric-header">
                                    <div class="ae-radar-page__metric-icon-wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
                                    </div>
                                    <div class="ae-radar-page__metric-label">Este mes</div>
                                </div>
                                <div class="ae-radar-page__metric-body">
                                    <p class="ae-radar-page__metric-value"><?= number_format($stats['mes']) ?></p>
                                    <p class="ae-radar-page__metric-help">Negocios potenciales mensuales</p>
                                </div>
                            </div>
                        </article>
                    </section>

                    <form action="<?= site_url('radar') ?>" method="GET" class="ae-radar-page__filters <?= $isFree ? 'is-locked' : '' ?>">
                        <div class="ae-radar-page__filters-head">
                            <?php if ($isFree): ?>
                                <a href="<?= site_url('checkout/radar-export?type=subscription') ?>" class="ae-radar-page__mini-chip ae-radar-page__mini-chip--locked">
                                    🔒 Filtros avanzados solo en PRO
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="ae-radar-page__filters-grid">
                            <div class="ae-radar-page__field">
                                <label>Nicho / Palabras clave</label>
                                <input type="text" name="q" 
                                       class="ae-radar-page__input" 
                                       placeholder="Ej: energía solar, fintech..." 
                                       value="<?= esc($filters['q'] ?? '') ?>"
                                       <?= $isFree ? 'disabled' : '' ?>>
                            </div>

                            <div class="ae-radar-page__field">
                                <label>Provincia</label>
                                <select name="provincia" class="ae-radar-page__select" <?= $isFree ? 'disabled' : '' ?>>
                                    <option value="">Toda España</option>
                                    <?php foreach ($provinces as $p): ?>
                                        <option value="<?= url_title($p['name'], '-', true) ?>" <?= ($filters['provincia'] === url_title($p['name'], '-', true)) ? 'selected' : '' ?>>
                                            <?= esc($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sector de actividad: Eliminado por optimización -->

                            <div class="ae-radar-page__field">
                                <label>Ventana temporal</label>
                                <select name="rango" class="ae-radar-page__select" <?= $isFree ? 'disabled' : '' ?>>
                                    <option value="hoy" <?= ($filters['rango'] === 'hoy') ? 'selected' : '' ?>>Hoy mismo</option>
                                    <option value="7" <?= ($filters['rango'] === '7') ? 'selected' : '' ?>>Últimos 7 días</option>
                                    <option value="30" <?= ($filters['rango'] === '30') ? 'selected' : '' ?>>Últimos 30 días</option>
                                    <option value="90" <?= ($filters['rango'] === '90') ? 'selected' : '' ?>>Últimos 90 días</option>
                                </select>
                            </div>

                            <div class="ae-radar-page__field">
                                <label>&nbsp;</label>
                                <?php if ($isFree): ?>
                                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__filters-cta ae-radar-page__filters-cta--locked">
                                        Activar PRO (79€) para filtrar
                                    </a>
                                <?php else: ?>
                                    <button type="submit" class="ae-radar-page__filters-cta">
                                        Aplicar filtros
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

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

                        <?php if ($isFree && !empty($lockedCompanies)): ?>
                            <div class="ae-radar-page__locked-zone">
                                <div class="ae-radar-page__locked-zone-table">
                                    <div class="ae-radar-page__table-scroll ae-radar-page__table-scroll--locked">
                                        <table class="ae-radar-page__table">
                                            <tbody>
                                                <?php foreach ($lockedCompanies as $co): ?>
                                                    <tr class="ae-radar-page__row-locked">
                                                        <td>
                                                            <div class="ae-radar-page__company">
                                                                <span class="ae-radar-page__company-name"><?= esc($co['company_name']) ?></span>
                                                                <span class="ae-radar-page__company-cif">B********</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="ae-radar-page__date"><?= $formatEsDate($co['fecha_constitucion']) ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="ae-radar-page__badge ae-radar-page__badge--province">
                                                                <?= esc($co['registro_mercantil'] ?? 'N/D') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="ae-radar-page__badge ae-radar-page__badge--sector">
                                                                <?= esc(mb_strimwidth($co['cnae_label'] ?? 'N/D', 0, 40, '...')) ?>
                                                            </span>
                                                        </td>
                                                        <td style="text-align:right;">
                                                            <span class="ae-radar-page__row-link">🔒</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="ae-radar-page__paywall">
                                    <div class="ae-radar-page__paywall-card">
                                        <div class="ae-radar-page__paywall-band"></div>

                                        <div class="ae-radar-page__paywall-inner">
                                            <div class="ae-radar-page__paywall-icon">🚀</div>

                                            <h3 class="ae-radar-page__paywall-title">
                                                Desbloquea el resto del radar y empieza a prospectar con ventaja
                                            </h3>

                                            <div class="ae-radar-page__scarcity">
                                                <span class="ae-radar-page__scarcity-fire">🔥</span>
                                                <div class="ae-radar-page__scarcity-text">
                                                    Solo quedan <strong>12 plazas PRO</strong> disponibles para este sector este mes.
                                                </div>
                                            </div>

                                            <p class="ae-radar-page__paywall-text">
                                                Ya has visto una muestra real del radar. Activa Radar PRO para consultar todas las empresas detectadas, aplicar filtros estratégicos y exportar leads a Excel para tu proceso comercial.
                                            </p>

                                            <div class="ae-radar-page__paywall-kpis">
                                                <div class="ae-radar-page__paywall-kpi">
                                                    <strong><?= number_format($stats['mes']) ?></strong>
                                                    <span>Empresas este mes</span>
                                                </div>
                                                <div class="ae-radar-page__paywall-kpi">
                                                    <strong>100%</strong>
                                                    <span>Listado desbloqueado</span>
                                                </div>
                                                <div class="ae-radar-page__paywall-kpi">
                                                    <strong>XLSX</strong>
                                                    <span>Exportación incluida</span>
                                                </div>
                                            </div>

                                            <div class="ae-radar-page__paywall-benefits">
                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Listado completo</strong>
                                                        <span>Consulta todas las empresas registradas según tus criterios comerciales.</span>
                                                    </div>
                                                </div>

                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Exportación a Excel</strong>
                                                        <span>Lleva los leads a tu CRM, campañas o equipo de ventas.</span>
                                                    </div>
                                                </div>

                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Filtros estratégicos</strong>
                                                        <span>Segmenta por provincia, actividad y rango temporal.</span>
                                                    </div>
                                                </div>

                                                <div class="ae-radar-page__paywall-benefit">
                                                    <div class="ae-radar-page__paywall-check">✓</div>
                                                    <div>
                                                        <strong>Ventaja competitiva</strong>
                                                        <span>Llega antes que otros proveedores a empresas recién constituidas.</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ae-radar-page__paywall-actions">
                                                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                                    Activar Radar PRO (79€) ahora
                                                </a>
                                            </div>

                                            <div class="ae-radar-page__paywall-meta">
                                                Sin permanencia · Activación inmediata · Acceso completo al radar
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>

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

    <!-- Modal AI Analysis -->
    <div id="ae-ai-modal" class="ae-ai-modal" style="display:none;">
        <div class="ae-ai-modal__backdrop" onclick="closeAIModal()"></div>
        <div class="ae-ai-modal__container">
            <div class="ae-ai-modal__header">
                <div class="ae-ai-modal__title">
                    <span>✨</span>
                    Análisis Inteligente Radar
                </div>
                <button type="button" class="ae-ai-modal__close" onclick="closeAIModal()">&times;</button>
            </div>
            <div id="ae-ai-content" class="ae-ai-modal__body">
                <!-- Se cargará por AJAX -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
     * Análisis IA bajo demanda
     */
    function analyzeAI(id, btn) {
        if (!id) return;

        const $modal = document.getElementById('ae-ai-modal');
        const $content = document.getElementById('ae-ai-content');
        
        // Bloquear botón y mostrar loading en modal
        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = '⏳...';
        btn.disabled = true;

        $content.innerHTML = `
            <div style="text-align:center; padding: 40px 0;">
                <div class="ae-spinner"></div>
                <p style="margin-top:20px; font-weight:700; color:#1e293b; font-size:18px;">
                    Consultando con la Inteligencia Artificial...
                </p>
                <p style="color:#64748b; font-size:14px;">
                    Analizando objeto social y extrayendo nichos estratégicos.
                </p>
            </div>
        `;
        $modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        fetch('<?= site_url('radar/ai-analyze/') ?>' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalBtnHtml;
            btn.disabled = false;

            if (data.status === 'success') {
                $content.innerHTML = `
                    <div class="ae-ai-result">
                        <div class="ae-ai-result__header-row">
                            <div class="ae-ai-result__main-info">
                                <span class="ae-ai-result__label">Nicho estratégico</span>
                                <div class="ae-ai-result__niche">${data.niche}</div>
                                <p class="ae-ai-result__summary">${data.summary}</p>
                            </div>
                            <div class="ae-ai-result__target">
                                <span class="ae-ai-result__label">Interlocutor ideal</span>
                                <div class="ae-ai-result__persona-badge">
                                    <span>👤</span> ${data.target_persona}
                                </div>
                            </div>
                        </div>

                        <div class="ae-ai-result__grid">
                            <div class="ae-ai-result__section">
                                <span class="ae-ai-result__label">Puntos de dolor (Pain Points)</span>
                                <ul class="ae-ai-result__list">
                                    ${data.pain_points.map(p => `<li>${p}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                        
                        <div class="ae-ai-result__scripts">
                            <div class="ae-ai-result__section">
                                <span class="ae-ai-result__label">Script de llamada (Apertura)</span>
                                <div class="ae-ai-result__script-box">
                                    <p id="ae-script-call" class="ae-ai-result__script-text">"${data.cold_call}"</p>
                                    <button type="button" class="ae-ai-result__copy-btn" onclick="copyToClipboard('ae-script-call', this)">
                                        <span>📋</span> Copiar script
                                    </button>
                                </div>
                            </div>

                            <div class="ae-ai-result__section">
                                <span class="ae-ai-result__label">Email Gancho</span>
                                <div class="ae-ai-result__script-box ae-ai-result__script-box--email">
                                    <div class="ae-ai-result__email-field">
                                        <strong>Asunto:</strong> <span id="ae-email-subject">${data.email_hook.subject}</span>
                                    </div>
                                    <div class="ae-ai-result__email-field">
                                        <strong>Cuerpo:</strong> <span id="ae-email-opening">"${data.email_hook.opening}"</span>
                                    </div>
                                    <button type="button" class="ae-ai-result__copy-btn" onclick="copyEmailHook(this)">
                                        <span>✉️</span> Copiar asunto y cuerpo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                throw new Error(data.message || 'Error en el análisis');
            }
        })
        .catch(err => {
            btn.innerHTML = originalBtnHtml;
            btn.disabled = false;
            $content.innerHTML = `
                <div style="text-align:center; padding: 40px 0; color:#ef4444;">
                    <p style="font-size:32px;">⚠️</p>
                    <p style="font-weight:700; margin-top:16px;">Error en el análisis</p>
                    <p style="color:#64748b;">${err.message}</p>
                    <button type="button" class="ae-radar-page__hero-btn ae-radar-page__hero-btn--secondary" 
                            onclick="closeAIModal()" style="margin-top:24px; min-height:40px;">
                        Cerrar
                    </button>
                </div>
            `;
        });
    }

    function closeAIModal() {
        document.getElementById('ae-ai-modal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function copyToClipboard(elementId, btn) {
        const text = document.getElementById(elementId).innerText.replace(/^"|"$/g, '');
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Copiado';
            btn.classList.add('is-copied');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('is-copied');
            }, 2000);
        });
    }

    function copyEmailHook(btn) {
        const subject = document.getElementById('ae-email-subject').innerText;
        const opening = document.getElementById('ae-email-opening').innerText.replace(/^"|"$/g, '');
        const fullText = `Asunto: ${subject}\n\n${opening}`;
        
        navigator.clipboard.writeText(fullText).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Copiado';
            btn.classList.add('is-copied');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('is-copied');
            }, 2000);
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
     * Actualiza los resultados cambiando solo el per_page (AJAX)
     */
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

</body>
</html>