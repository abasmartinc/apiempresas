<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr)) return 'Reciente';
    $dateStr = str_replace('/', '-', $dateStr);
    $timestamp = strtotime($dateStr);

    if (!$timestamp || $timestamp > strtotime('+1 year') || $timestamp < strtotime('1900-01-01')) {
        return 'Reciente';
    }

    $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    $formatted = date($format, $timestamp);
    return str_replace($mesesEn, $mesesEs, $formatted);
};

$formatCompanyName = function($name) {
    if (empty($name)) return 'Empresa sin nombre';
    $name = mb_strtolower(trim($name), 'UTF-8');
    return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
};

$getLeadBadge = function($dateStr) {
    if (empty($dateStr)) return 'Nueva empresa';
    $timestamp = strtotime(str_replace('/', '-', $dateStr));
    if (!$timestamp) return 'Nueva empresa';

    $diffDays = floor((time() - $timestamp) / 86400);

    if ($diffDays <= 0) return 'Constituida hoy';
    if ($diffDays <= 7) return 'Últimos 7 días';
    if ($diffDays <= 30) return 'Últimos 30 días';
    return 'Nueva empresa';
};

$getCommercialSignals = function($sectorData, $object) {
    $text = mb_strtolower(trim(($sectorData['label'] ?? '') . ' ' . ($object ?? '')), 'UTF-8');

    $map = [
        'software'      => ['SaaS', 'hosting', 'ciberseguridad'],
        'informática'   => ['software', 'cloud', 'marketing digital'],
        'tecnolog'      => ['software', 'consultoría IT', 'captación digital'],
        'consultor'     => ['CRM', 'asesoría', 'captación B2B'],
        'marketing'     => ['herramientas SEO', 'automatización', 'analytics'],
        'publicidad'    => ['captación digital', 'CRM', 'facturación'],
        'construcción'  => ['seguros', 'PRL', 'financiación'],
        'hosteler'      => ['TPV', 'proveedores', 'software gestión'],
        'restaur'       => ['TPV', 'reservas', 'proveedores'],
        'comercio'      => ['ERP', 'pasarela de pago', 'financiación'],
        'transporte'    => ['GPS', 'seguros', 'gestión de flotas'],
        'logística'     => ['gestión operativa', 'seguros', 'software'],
        'inmobili'      => ['CRM', 'marketing', 'firma digital'],
        'salud'         => ['software', 'cumplimiento', 'facturación'],
        'servicios'     => ['gestión comercial', 'facturación', 'presencia online'],
    ];

    foreach ($map as $needle => $signals) {
        if (str_contains($text, $needle)) {
            return implode(' · ', $signals);
        }
    }

    return 'asesoría · software · marketing';
};

$sectorLabel = $sector_label ?? ($sector['label'] ?? 'Sector');
$province = $province ?? 'España';

$buildCheckoutUrl = site_url('billing/single_checkout') . 
    '?period=30days&sector=' . urlencode($sectorLabel);

$companies = $companies ?? [];
$freeCount = 10;
$freeLeads = array_slice($companies, 0, $freeCount);
$premiumLeads = array_slice($companies, $freeCount);
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical,
        'robots'      => $robots ?? 'index,follow',
    ]) ?>

    <link rel="stylesheet" href="<?= base_url('public/css/radar_new_companies_sector.css?v=' . (file_exists(FCPATH . 'public/css/radar_new_companies_sector.css') ? filemtime(FCPATH . 'public/css/radar_new_companies_sector.css') : time())) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <main class="ae-radar-page">
        <section class="ae-radar-page__hero container">
            <div class="ae-radar-page__hero-inner">
                <span class="ae-radar-page__pill">
                    RADAR SECTORIAL • <?= esc(mb_strtoupper($sectorLabel)) ?>
                </span>

                <h1 class="ae-radar-page__title">
                    <?= esc($heading_prefix) ?>
                    <span class="ae-radar-page__title-grad"><?= esc($heading_time) ?></span>
                    <?= esc($heading_suffix) ?><?= esc($heading_highlight) ?><?= esc($heading_middle) ?><?= esc($heading_location) ?>
                </h1>

                <p class="ae-radar-page__subtitle">
                    Monitoriza la creación de nuevas sociedades en el sector de <?= esc(mb_strtolower($sectorLabel)) ?>. Accede a leads cualificados con necesidades inmediatas de servicios.
                </p>

                <div class="ae-radar-page__hero-actions">
                    <a href="<?= site_url('radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Abrir Radar Completo
                    </a>
                    <a href="#leads-sectoriales" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                        Ver muestra gratuita
                    </a>
                </div>

                <div class="ae-radar-page__hero-panel">
                    <div class="ae-radar-page__hero-panel-copy">
                        <h2 class="ae-radar-page__hero-panel-title">Accede al Radar de <?= esc($sectorLabel) ?></h2>
                        <ul class="ae-radar-page__hero-list">
                            <li>Monitoriza constituciones en <?= esc($sectorLabel) ?></li>
                            <li>Filtra por sector y periodo temporal</li>
                            <li>Exporta leads diarios para tu CRM</li>
                        </ul>
                    </div>

                    <div class="ae-radar-page__hero-panel-actions">
                        <a href="<?= site_url('radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary ae-radar-page__btn--panel">
                            Abrir Radar
                        </a>
                    </div>
                </div>

                <div class="ae-radar-page__stats">
                    <div class="ae-radar-page__stat-card ae-radar-page__stat-card--today">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Nuevas hoy</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="ae-radar-page__stat-card ae-radar-page__stat-card--week">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Últimos 7 días</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="ae-radar-page__stat-card ae-radar-page__stat-card--month">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Últimos 30 días</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="leads-sectoriales" class="ae-radar-page__section ae-radar-page__section--leads container">
            <?php if (!($is_low_results ?? false)): ?>
                <div class="ae-radar-page__leads-header">
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Oportunidades en <?= esc($sectorLabel) ?></h2>
                    <div class="ae-radar-page__live-badge">
                        <span class="ae-radar-page__live-badge-dot"></span>
                        Actualizado BORME
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($is_low_results ?? false): ?>
                <div class="ae-radar-page__empty-state">
                    <div class="ae-radar-page__empty-state-inner">
                        <div class="ae-radar-page__empty-state-header">
                            <div class="ae-radar-page__empty-state-kicker">Análisis Sectorial B2B</div>
                            <h3 class="ae-radar-page__empty-state-title-main">Sin resultados recientes</h3>
                            <p class="ae-radar-page__empty-state-subtitle">
                                No hemos detectado nuevas constituciones en <?= esc($sectorLabel) ?> en las últimas horas, pero puedes explorar el histórico nacional o activar alertas.
                            </p>
                        </div>

                        <h3 class="ae-radar-page__empty-state-title">¿Cómo quieres continuar?</h3>
                        
                        <div class="ae-radar-page__empty-grid">
                            <div class="ae-radar-page__empty-card">
                                <div class="ae-radar-page__empty-card-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                </div>
                                <h4 class="ae-radar-page__empty-card-title">Explorar Sector</h4>
                                <p class="ae-radar-page__empty-card-text">Consulta todas las empresas constituidas históricamente en esta actividad.</p>
                                <a href="<?= $general_directory_url ?? site_url('directorio') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">Ver directorio sectorial</a>
                            </div>

                            <div class="ae-radar-page__empty-card ae-radar-page__empty-card--accent" id="avisarme-seccion">
                                <div class="ae-radar-page__empty-card-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                </div>
                                <h4 class="ae-radar-page__empty-card-title">Email semanal</h4>
                                <p class="ae-radar-page__empty-card-text">Recibe un resumen con las nuevas empresas de este sector cada lunes.</p>
                                <form action="#" method="POST" onsubmit="alert('Alerta activa.'); return false;" class="ae-radar-page__empty-form">
                                    <input type="email" placeholder="Tu email profesional" required class="ae-radar-page__empty-input">
                                    <button type="submit" class="ae-radar-page__empty-submit">OK</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="ae-radar-page__lead-grid">
                <?php foreach ($freeLeads as $index => $co): ?>
                    <?php
                    $companyName = $formatCompanyName($co['name'] ?? '');
                    $leadBadge = $getLeadBadge($co['fecha_constitucion'] ?? null);
                    $leadSignals = $getCommercialSignals($sector, $co['objeto_social'] ?? '');
                    ?>
                    <article class="ae-radar-page__lead-card">
                        <div class="ae-radar-page__lead-card-main">
                            <div class="ae-radar-page__lead-top-row">
                                <div class="ae-radar-page__lead-badge"><?= esc($leadBadge) ?></div>
                                <div class="ae-radar-page__lead-date"><?= esc($formatEsDate($co['fecha_constitucion'] ?? null)) ?></div>
                            </div>

                            <h3 class="ae-radar-page__lead-title"><?= esc($companyName) ?></h3>

                            <div class="ae-radar-page__lead-chips">
                                <div class="ae-radar-page__lead-chip">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"></path>
                                    </svg>
                                    <span>Sector <?= esc($co['cnae'] ?? $sectorLabel) ?></span>
                                </div>

                                <div class="ae-radar-page__lead-chip">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span><?= esc($co['registro_mercantil'] ?? 'España') ?></span>
                                </div>
                            </div>

                            <div class="ae-radar-page__lead-box">
                                <div class="ae-radar-page__lead-box-label">Objeto social</div>
                                <div class="ae-radar-page__lead-box-text">
                                    <?= esc($co['objeto_social'] ?? 'Actividad no detallada en la publicación disponible.') ?>
                                </div>
                            </div>

                            <div class="ae-radar-page__lead-intent">
                                <div class="ae-radar-page__lead-intent-label">Potenciales necesidades</div>
                                <div class="ae-radar-page__lead-intent-text"><?= esc($leadSignals) ?></div>
                            </div>
                        </div>

                        <a href="<?= site_url($co['cif'] . '-' . url_title($co['name'], '-', true)) ?>" class="ae-radar-page__lead-btn">
                            Ver empresa
                        </a>
                    </article>

                    <?php if ($index === 4 && !empty($premiumLeads)): ?>
                        <div class="ae-radar-page__premium-strip-wrap">
                            <div class="ae-radar-page__premium-strip">
                                <div class="ae-radar-page__premium-strip-copy">
                                    <h3>Desbloquea el listado completo de <?= esc($sectorLabel) ?></h3>
                                    <p>
                                        Accede a todas las sociedades detectadas en este periodo en <?= esc($sectorLabel) ?>, filtra por provincia y exporta leads preparados para prospección comercial.
                                    </p>

                                    <div class="ae-radar-page__premium-points">
                                        <span>Filtros geográficos avanzados</span>
                                        <span>Exportación Excel directa</span>
                                        <span>Datos de BORME actualizados</span>
                                    </div>
                                </div>

                                <div class="ae-radar-page__premium-strip-actions">
                                    <a href="<?= site_url('radar') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--light">
                                        Abrir Radar
                                    </a>
                                    <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--dark">
                                        Descargar Excel · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($premiumLeads)): ?>
                <div class="ae-radar-page__paywall-zone">
                    <div class="ae-radar-page__paywall-overlay">
                        <div class="ae-radar-page__paywall-card">
                            <div class="ae-radar-page__paywall-topbar"></div>

                            <div class="ae-radar-page__paywall-body">
                                <div class="ae-radar-page__paywall-header">
                                    <div class="ae-radar-page__paywall-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                            <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                            <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                                        </svg>
                                    </div>

                                    <div class="ae-radar-page__paywall-kicker">Acceso Premium Sectorial</div>

                                    <h3 class="ae-radar-page__paywall-title">
                                        Listado completo de empresas de <?= esc($sectorLabel) ?>
                                    </h3>

                                    <p class="ae-radar-page__paywall-subtitle">
                                        Accede al directorio completo de nuevas empresas en el sector de <?= esc($sectorLabel) ?> y trabaja con leads filtrables por provincia y fecha de constitución.
                                    </p>
                                </div>

                                <div class="ae-radar-page__paywall-stats">
                                    <div class="ae-radar-page__paywall-stat">
                                        <div class="ae-radar-page__paywall-stat-value"><?= number_format($total_context_count ?? 0, 0, ',', '.') ?></div>
                                        <div class="ae-radar-page__paywall-stat-label">Empresas detectadas</div>
                                    </div>

                                    <div class="ae-radar-page__paywall-stat">
                                        <div class="ae-radar-page__paywall-stat-value"><?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="ae-radar-page__paywall-stat-label">Últimos 7 días</div>
                                    </div>

                                    <div class="ae-radar-page__paywall-stat">
                                        <div class="ae-radar-page__paywall-stat-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="ae-radar-page__paywall-stat-label">Últimos 30 días</div>
                                    </div>
                                </div>

                                <div class="ae-radar-page__paywall-actions">
                                    <a href="<?= site_url('radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                        <span>Activar Suscripción Radar</span>
                                        <span class="ae-radar-page__paywall-price-tag">79€/mes</span>
                                    </a>

                                    <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">
                                        <span>Descargar Listado Sectorial</span>
                                        <span class="ae-radar-page__paywall-secondary-price"><?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>

         <section class="ae-radar-page__section ae-radar-page__section--sectors container">
                <div class="ae-radar-page__sectors-layout">
                    <div class="ae-radar-page__sectors-intro">
                        <span class="ae-radar-page__section-kicker">Radar sectorial</span>

                        <h3 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                            Sectores con alta actividad en España
                        </h3>

                        <p class="ae-radar-page__section-subtitle ae-radar-page__section-subtitle--left">
                            Una lectura rápida de los sectores donde se están concentrando más nuevas constituciones mercantiles y donde suele existir mayor potencial de prospección B2B.
                        </p>

                        <div class="ae-radar-page__sectors-summary">
                            <div class="ae-radar-page__sectors-summary-item">
                                <strong><?= number_format(min(6, count($related_sectors ?? [])), 0, ',', '.') ?></strong>
                                <span>Sectores destacados</span>
                            </div>

                            <div class="ae-radar-page__sectors-summary-item">
                                <strong>Alta</strong>
                                <span>Actividad reciente</span>
                            </div>

                            <div class="ae-radar-page__sectors-summary-item">
                                <strong>B2B</strong>
                                <span>Enfoque comercial</span>
                            </div>
                        </div>
                    </div>

                    <div class="ae-radar-page__sectors-list">
                        <?php foreach (array_slice($related_sectors, 0, 4) as $index => $rs): ?>
                            <?php
                            $rowLabel = $rs['label'] ?? 'Sector no disponible';
                            $rowText = mb_strtolower($rowLabel, 'UTF-8');

                            $sectorMeta = [
                                'title' => $rowLabel,
                                'desc'  => 'Nuevas sociedades detectadas con potencial de prospección B2B.',
                                'tag'   => 'Radar activo',
                            ];

                            if (str_contains($sectorText, 'constru')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Demanda temprana de seguros, PRL, software y financiación.',
                                    'tag'   => 'Alta demanda B2B',
                                ];
                            } elseif (str_contains($sectorText, 'comerc')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Potencial para ERP, pagos, asesoría y digitalización comercial.',
                                    'tag'   => 'Volumen recurrente',
                                ];
                            } elseif (str_contains($sectorText, 'inmobil')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Buen encaje para CRM, marketing, firma digital y gestión documental.',
                                    'tag'   => 'Nicho comercial',
                                ];
                            } elseif (str_contains($sectorText, 'restaur') || str_contains($sectorText, 'hostel')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Necesidades frecuentes de TPV, reservas, software y proveedores.',
                                    'tag'   => 'Alta rotación',
                                ];
                            } elseif (str_contains($sectorText, 'tecnolog') || str_contains($sectorText, 'inform')) {
                                $sectorMeta = [
                                    'title' => $rowLabel,
                                    'desc'  => 'Empresas orientadas a software, cloud, servicios IT y captación digital.',
                                    'tag'   => 'Sector dinámico',
                                ];
                            } elseif (str_contains($sectorText, 'servic')) {
                                $sectorMeta = [
                                    'title' => $rowLabel,
                                    'desc'  => 'Actividad transversal con hueco para asesoría, software y automatización.',
                                    'tag'   => 'Prospección activa',
                                ];
                            }
                            ?>
                            <article class="ae-radar-page__sector-row">
                                <div class="ae-radar-page__sector-row-index">
                                    <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                                </div>

                                <div class="ae-radar-page__sector-row-main">
                                    <div class="ae-radar-page__sector-row-head">
                                        <h4 class="ae-radar-page__sector-row-title"><?= esc($sectorMeta['title']) ?></h4>
                                        <span class="ae-radar-page__sector-row-tag"><?= esc($sectorMeta['tag']) ?></span>
                                    </div>

                                    <p class="ae-radar-page__sector-row-desc"><?= esc($sectorMeta['desc']) ?></p>
                                </div>

                                <div class="ae-radar-page__sector-row-action">
                                    <a
                                        href="<?= site_url('empresas-nuevas-sector/' . url_title($rs['label'], '-', true)) ?>"
                                        class="ae-radar-page__sector-row-link"
                                        title="<?= esc($rs['label']) ?>"
                                    >
                                        Explorar
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        <?php if (!empty($companies)): ?>
        <section class="ae-radar-page__section ae-radar-page__section--excel container">
            <div class="ae-radar-page__excel-box">
                <div class="ae-radar-page__excel-content">
                    <span class="ae-radar-page__excel-kicker">Exportación directa</span>

                    <h3 class="ae-radar-page__excel-title">¿Necesitas el listado de <?= esc($sectorLabel) ?> en Excel?</h3>

                    <p class="ae-radar-page__excel-subtitle">
                        Descarga el directorio completo de nuevas empresas de <?= esc($sectorLabel) ?> en formato XLSX, listo para prospección comercial.
                    </p>

                    <div class="ae-radar-page__excel-actions">
                        <a href="<?= site_url('checkout/radar-export?sector=' . urlencode($sectorLabel ?? '') . '&period=' . urlencode(empty($period) || $period === 'general' ? '30days' : $period)) ?>" class="ae-radar-page__excel-btn">
                            Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="ae-radar-page__section ae-radar-page__section--strategic container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Exploración rápida</span>
                <h3 class="ae-radar-page__section-title">Directorios estratégicos de empresas nuevas</h3>
                <p class="ae-radar-page__section-subtitle">
                    Accede a rutas clave para descubrir nuevas oportunidades de prospección.
                </p>
            </div>

            <div class="ae-radar-page__strategic-grid">
                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Periodos nacionales</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-hoy') ?>">Constituidas hoy</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-semana') ?>">Esta semana</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-mes') ?>">Este mes</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Provincias top</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas/madrid') ?>">Empresas nuevas en Madrid</a></li>
                        <li><a href="<?= site_url('empresas-nuevas/barcelona') ?>">Empresas nuevas en Barcelona</a></li>
                        <li><a href="<?= site_url('empresas-nuevas/valencia') ?>">Empresas nuevas en Valencia</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Exploración por sector</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-sector/construccion') ?>">Nuevas en Construcción</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/hosteleria') ?>">Nuevas en Hostelería</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/tecnologia') ?>">Nuevas en Tecnología</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--seo container">
            <div class="ae-radar-page__seo-box">
                <div class="ae-radar-page__section-head ae-radar-page__section-head--left">
                    <span class="ae-radar-page__section-kicker">Contexto de mercado</span>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                        Sobre las nuevas empresas de <?= esc($sectorLabel) ?>
                    </h2>
                </div>

                <div class="ae-radar-page__seo-content">
                    <p>
                        El sector de <?= esc(mb_strtolower($sectorLabel)) ?> es un motor económico fundamental. Cada mes detectamos decenas de nuevas sociedades creadas recientemente en este ámbito en toda España.
                    </p>

                    <p>
                        Con el Radar puedes detectar estas nuevas empresas antes que tu competencia y posicionarte como su proveedor desde el primer día, ofreciendo servicios adaptados a sus necesidades iniciales.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
