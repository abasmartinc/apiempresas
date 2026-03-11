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

$getCommercialSignals = function($sector, $object) {
    $text = mb_strtolower(trim(($sector ?? '') . ' ' . ($object ?? '')), 'UTF-8');

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

$buildCheckoutUrl = site_url('billing/single_checkout') . '?period=30days';

$companies = $companies ?? [];
$freeCount = 10; // More leads for the national hub
$freeLeads = array_slice($companies, 0, $freeCount);
$premiumLeads = array_slice($companies, $freeCount);
$province = 'España';
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

    <link rel="stylesheet" href="<?= base_url('public/css/radar_new_companies.css?v=' . (file_exists(FCPATH . 'public/css/radar_new_companies.css') ? filemtime(FCPATH . 'public/css/radar_new_companies.css') : time())) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <main class="ae-radar-page">
        <section class="ae-radar-page__hero container">
            <div class="ae-radar-page__hero-inner">
                <span class="ae-radar-page__pill">
                    RADAR NACIONAL • ESPAÑA
                </span>

                <h1 class="ae-radar-page__title">
                    Radar de <span class="ae-radar-page__title-grad">Nuevas Empresas</span> en España
                    <span class="ae-radar-page__title-sub">Monitorización BORME en Tiempo Real</span>
                </h1>

                <p class="ae-radar-page__subtitle">
                    Descubre cada día las sociedades recién constituidas en todo el territorio nacional. El punto de partida para tu prospección comercial B2B.
                </p>

                <div class="ae-radar-page__hero-actions">
                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Abrir Radar Pro
                    </a>
                    <a href="#leads-b2b-recientes" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                        Ver últimas 10
                    </a>
                </div>

                <div class="ae-radar-page__hero-panel">
                    <div class="ae-radar-page__hero-panel-copy">
                        <h2 class="ae-radar-page__hero-panel-title">Accede al Radar Pro Nacional</h2>
                        <ul class="ae-radar-page__hero-list">
                            <li>Monitoriza todas las constituciones en España</li>
                            <li>Filtros por sector, provincia y capital social</li>
                            <li>Exportación diaria para tu CRM</li>
                        </ul>
                    </div>

                    <div class="ae-radar-page__hero-panel-actions">
                        <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary ae-radar-page__btn--panel">
                            Abrir Radar
                        </a>
                    </div>
                </div>

                <div class="ae-radar-page__stats">
                    <a href="<?= site_url('empresas-nuevas-hoy') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--today" style="text-decoration: none; color: inherit;">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label" style="color: var(--ae-accent, #4f46e5); font-weight: 600;">Ver Hoy</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </a>

                    <a href="<?= site_url('empresas-nuevas-semana') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--week" style="text-decoration: none; color: inherit;">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label" style="color: var(--ae-accent, #4f46e5); font-weight: 600;">Ver Semana</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </a>

                    <a href="<?= site_url('empresas-nuevas-mes') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--month" style="text-decoration: none; color: inherit;">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label" style="color: var(--ae-accent, #4f46e5); font-weight: 600;">Ver Mes</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--territory container">
            <div class="ae-radar-page__section-head">
                <span class="ae-radar-page__section-kicker">Distribución Territorial</span>
                <h2 class="ae-radar-page__section-title">Provincias con más constituciones</h2>
                <p class="ae-radar-page__section-subtitle">Explora los principales hubs empresariales de España en los últimos 90 días.</p>
            </div>

            <div class="ae-radar-page__territory-grid">
                <?php foreach (array_slice($top_sectors, 0, 8) as $index => $item): ?>
                    <?php
                    $maxTotal = $top_sectors[0]['total'] ?? 100;
                    $percent = min(100, round(($item['total'] / $maxTotal) * 100));
                    ?>
                    <article class="ae-radar-page__territory-card <?= $index < 3 ? 'ae-radar-page__territory-card--top' : '' ?>">
                        <div class="ae-radar-page__territory-top">
                            <div class="ae-radar-page__territory-rank">#<?= $index + 1 ?></div>
                            <div class="ae-radar-page__territory-micro">Radar Activo</div>
                        </div>

                        <div class="ae-radar-page__territory-main">
                            <h3 class="ae-radar-page__territory-name"><?= esc($item['cnae_label']) ?></h3>

                            <div class="ae-radar-page__territory-value-wrap">
                                <div class="ae-radar-page__territory-value"><?= number_format($item['total'], 0, ',', '.') ?></div>
                                <div class="ae-radar-page__territory-value-label">Nuevas empresas</div>
                            </div>

                            <div class="ae-radar-page__territory-progress">
                                <div class="ae-radar-page__territory-progress-meta">
                                    <span>Presencia nacional</span>
                                    <span><?= $percent ?>%</span>
                                </div>
                                <div class="ae-radar-page__territory-bar">
                                    <span style="width: <?= $percent ?>%;"></span>
                                </div>
                            </div>
                        </div>

                        <a href="<?= site_url('empresas-nuevas/' . url_title($item['cnae_label'], '-', true)) ?>" class="stretched-link" title="Ver empresas en <?= esc($item['cnae_label']) ?>"></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">
            <div class="ae-radar-page__leads-header">
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Últimas 10 empresas detectadas</h2>
                <div class="ae-radar-page__live-badge">
                    <span class="ae-radar-page__live-badge-dot"></span>
                    En tiempo real
                </div>
            </div>

            <div class="ae-radar-page__lead-grid">
                <?php foreach ($freeLeads as $index => $co): ?>
                    <?php
                    $companyName = $formatCompanyName($co['name'] ?? '');
                    $leadBadge = $getLeadBadge($co['fecha_constitucion'] ?? null);
                    $leadSignals = $getCommercialSignals($co['cnae'] ?? '', $co['objeto_social'] ?? '');
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
                                    <span><?= esc($co['cnae'] ?? 'Sector no detallado') ?></span>
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
                                    <h3>Desbloquea el radar completo de España</h3>
                                    <p>
                                        Accede a todas las sociedades detectadas en territorio nacional, filtra por sector y provincia y exporta leads directos a Excel.
                                    </p>

                                    <div class="ae-radar-page__premium-points">
                                        <span>Filtros avanzados</span>
                                        <span>Exportación Excel</span>
                                        <span>Alertas diarias</span>
                                    </div>
                                </div>

                                <div class="ae-radar-page__premium-strip-actions">
                                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--light">
                                        Abrir Radar
                                    </a>
                                    <a href="<?= site_url('billing/single_checkout?period=30days') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--dark">
                                        Descargar Nacional · <?= number_format($dynamic_price['base_price'] ?? 19, 0) ?>€
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

                                    <div class="ae-radar-page__paywall-kicker">Acceso Premium Nacional</div>

                                    <h3 class="ae-radar-page__paywall-title">
                                        Listado completo de empresas en España
                                    </h3>

                                    <p class="ae-radar-page__paywall-subtitle">
                                        Accede al radar de <?= number_format($total_context_count ?? 0, 0, ',', '.') ?> nuevas empresas detectadas este mes en todo el territorio nacional.
                                    </p>
                                </div>

                                <div class="ae-radar-page__paywall-stats">
                                    <div class="ae-radar-page__paywall-stat">
                                        <div class="ae-radar-page__paywall-stat-value"><?= number_format($total_context_count ?? 0, 0, ',', '.') ?></div>
                                        <div class="ae-radar-page__paywall-stat-label">Empresas este mes</div>
                                    </div>

                                    <div class="ae-radar-page__paywall-stat">
                                        <div class="ae-radar-page__paywall-stat-value"><?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="ae-radar-page__paywall-stat-label">Últimos 7 días</div>
                                    </div>

                                    <div class="ae-radar-page__paywall-stat">
                                        <div class="ae-radar-page__paywall-stat-value"><?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="ae-radar-page__paywall-stat-label">Nuevas hoy</div>
                                    </div>
                                </div>

                                <div class="ae-radar-page__paywall-actions">
                                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                        <span>Planes Radar Pro</span>
                                        <span class="ae-radar-page__paywall-price-tag">79€/mes</span>
                                    </a>

                                    <a href="<?= site_url('billing/single_checkout?period=30days') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">
                                        <span>Descargar Excel Nacional</span>
                                        <span class="ae-radar-page__paywall-secondary-price">19€</span>
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
                        Análisis de los sectores donde se están concentrando las nuevas constituciones mercantiles en todo el país.
                    </p>

                    <div class="ae-radar-page__sectors-summary">
                        <div class="ae-radar-page__sectors-summary-item">
                            <strong><?= count($related_sectors) ?></strong>
                            <span>Sectores clave</span>
                        </div>
                        <div class="ae-radar-page__sectors-summary-item">
                            <strong>Alta</strong>
                            <span>Actividad B2B</span>
                        </div>
                    </div>
                </div>

                <div class="ae-radar-page__sectors-list">
                    <?php foreach (array_slice($related_sectors, 0, 5) as $index => $rs): ?>
                        <article class="ae-radar-page__sector-row">
                            <div class="ae-radar-page__sector-row-index"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
                            <div class="ae-radar-page__sector-row-main">
                                <div class="ae-radar-page__sector-row-head">
                                    <h4 class="ae-radar-page__sector-row-title"><?= esc($rs['label']) ?></h4>
                                    <span class="ae-radar-page__sector-row-tag">Nacional</span>
                                </div>
                                <p class="ae-radar-page__sector-row-desc">Nuevas empresas detectadas en el sector de <?= mb_strtolower($rs['label']) ?>.</p>
                            </div>
                            <div class="ae-radar-page__sector-row-action">
                                <a href="<?= site_url('empresas-nuevas-sector/' . url_title($rs['label'], '-', true)) ?>" class="ae-radar-page__sector-row-link">Explorar</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--strategic container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Exploración rápida</span>
                <h3 class="ae-radar-page__section-title">Directorios estratégicos</h3>
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
                        <li><a href="<?= site_url('empresas-nuevas/madrid') ?>">Nuevas en Madrid</a></li>
                        <li><a href="<?= site_url('empresas-nuevas/barcelona') ?>">Nuevas en Barcelona</a></li>
                        <li><a href="<?= site_url('empresas-nuevas/valencia') ?>">Nuevas en Valencia</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Sectores destacados</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-sector/construccion') ?>">Construcción</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/hosteleria') ?>">Hostelería</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/programacion') ?>">Tecnología</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--seo container">
            <div class="ae-radar-page__seo-box">
                <div class="ae-radar-page__section-head ae-radar-page__section-head--left">
                    <span class="ae-radar-page__section-kicker">Contexto de mercado</span>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Sobre las nuevas empresas en España</h2>
                </div>

                <div class="ae-radar-page__seo-content">
                    <p>
                        Cada día se registran cientos de nuevas empresas en España. El Radar de APIEmpresas monitoriza el BORME para ofrecerte un listado actualizado de estas constituciones mercantiles.
                    </p>
                    <p>
                        Esta herramienta te permite detectar oportunidades de negocio B2B en el momento preciso en que surge la necesidad de nuevos proveedores.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
