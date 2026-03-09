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

$buildCheckoutUrl = site_url(
    'billing/single_checkout?provincia=' . urlencode($province ?? '') .
    '&sector=' . urlencode($sector_label ?? '') .
    '&period=' . urlencode($period === 'general' ? '30days' : ($period ?? ''))
);

$companies = $companies ?? [];
$paywall_level = $paywall_level ?? 'strong';

if ($paywall_level === 'none') {
    $freeCount = 100;
} elseif ($paywall_level === 'soft') {
    $freeCount = 20;
} else {
    $freeCount = ($period === 'hoy') ? 3 : 5;
}

$freeLeads = array_slice($companies, 0, $freeCount);
$premiumLeads = ($paywall_level === 'none') ? [] : array_slice($companies, $freeCount);
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical,
        'robots'      => 'index,follow',
    ]) ?>

    <link rel="stylesheet" href="<?= base_url('public/css/province.css?v=' . (file_exists(FCPATH . 'public/css/province.css') ? filemtime(FCPATH . 'public/css/province.css') : time())) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <main class="ae-radar-page">
        <section class="ae-radar-page__hero container">
            <div class="ae-radar-page__hero-inner">
                <span class="ae-radar-page__pill">
                    LEADS B2B • ÚLTIMAS CONSTITUCIONES
                </span>

                <h1 class="ae-radar-page__title">
                    <?php if (isset($heading_prefix)): ?>
                        <?= esc($heading_prefix) ?><?= esc($heading_suffix) ?><span class="ae-radar-page__title-grad"><?= esc($heading_highlight) ?></span><?= esc($heading_middle ?? ' en ') ?><?= esc($heading_location ?? '') ?><?= esc($heading_time) ?>
                    <?php else: ?>
                        <?php $headingRaw = esc($heading_title ?? ('Nuevas Empresas en ' . ($province ?? 'España'))); ?>
                        <span class="ae-radar-page__title-grad"><?= $headingRaw ?></span>
                    <?php endif; ?>
                    <span class="ae-radar-page__title-sub">Análisis B2B y Distribución Nacional</span>
                </h1>

                <p class="ae-radar-page__subtitle">
                    <?php if ($sector_label && $province): ?>
                        Detecta empresas nuevas de <?= mb_strtolower($sector_label) ?> en <?= ucfirst(mb_strtolower($province)) ?> y accede a oportunidades comerciales antes que tu competencia.
                    <?php elseif ($period === 'hoy' && !$province): ?>
                        Detecta nuevas empresas creadas hoy en España y accede a oportunidades comerciales antes que tu competencia.
                    <?php elseif ($period === 'semana' && !$province): ?>
                        Detecta empresas creadas esta semana en España y accede a nuevas oportunidades comerciales antes que tu competencia.
                    <?php elseif ($period === 'mes' && !$province): ?>
                        Detecta empresas creadas este mes en España y accede a nuevas oportunidades comerciales antes que tu competencia.
                    <?php else: ?>
                        Identifica los principales hubs provinciales y detecta nuevas sociedades en sus primeros 90 días de actividad.
                    <?php endif; ?>
                </p>

                <p class="ae-radar-page__hero-copy">
                    En <?= esc($heading_highlight) ?> se registran nuevas empresas de <?= esc($sector_label ?? 'diversos sectores') ?> con necesidades tempranas de software, asesoría, marketing digital, financiación y soluciones B2B. Esta página te ayuda a detectar esas oportunidades antes que tu competencia.
                </p>

                <div class="ae-radar-page__hero-actions">
                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Abrir Radar
                    </a>

                    <a href="#leads-b2b-recientes" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                        Ver muestra gratuita
                    </a>

                    <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__btn ae-radar-page__btn--soft">
                        Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                    </a>
                </div>

                <div class="ae-radar-page__hero-panel">
                    <div class="ae-radar-page__hero-panel-copy">
                        <h2 class="ae-radar-page__hero-panel-title">Accede al Radar de empresas nuevas</h2>
                        <ul class="ae-radar-page__hero-list">
                            <li>Detecta nuevas empresas cada día</li>
                            <li>Filtra por sector, provincia y periodo</li>
                            <li>Exporta leads listos para prospección</li>
                        </ul>
                    </div>

                    <div class="ae-radar-page__hero-panel-actions">
                        <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary ae-radar-page__btn--panel">
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

        <?php if (!empty($top_sectors)): ?>
        <section class="ae-radar-page__section ae-radar-page__section--impact container">
            <div class="ae-radar-page__section-head">
                <span class="ae-radar-page__section-kicker">
                    <?= $province ? 'Radar sectorial' : 'Radar territorial' ?>
                </span>

                <h2 class="ae-radar-page__section-title">
                    <?= $province ? 'Sectores con más actividad reciente' : 'Provincias con más empresas nuevas' ?>
                </h2>

                <p class="ae-radar-page__section-subtitle">
                    <?= $province
                        ? 'Distribución de nuevas constituciones por sector en ' . esc($heading_highlight)
                        : 'Distribución territorial de las nuevas constituciones detectadas en ' . esc($heading_highlight) ?>
                </p>
            </div>

            <div class="ae-radar-page__impact-grid">
                <?php foreach (array_slice($top_sectors, 0, 8) as $item): ?>
                    <?php
                    $label = $item['cnae_label'] ?? 'Sin detalle';
                    $initial = mb_strtoupper(mb_substr($label, 0, 1, 'UTF-8'), 'UTF-8');
                    ?>
                    <div class="ae-radar-page__impact-card">
                        <div class="ae-radar-page__impact-icon"><?= esc($initial) ?></div>

                        <div class="ae-radar-page__impact-body">
                            <div class="ae-radar-page__impact-label"><?= $province ? 'Sector' : 'Provincia' ?></div>
                            <div class="ae-radar-page__impact-title"><?= esc($label) ?></div>
                            <div class="ae-radar-page__impact-metric">
                                <?= number_format($item['total'], 0, ',', '.') ?> constituciones
                                <span>(90d)</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">
            <div class="ae-radar-page__leads-shell">
                <div class="ae-radar-page__leads-header">
                    <div>
                        <div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot">
                            <span class="ae-radar-page__section-kicker-dot"></span>
                            Muestra comercial en tiempo real
                        </div>

                        <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                            Leads B2B Recientes
                        </h2>

                        <p class="ae-radar-page__section-subtitle ae-radar-page__section-subtitle--left">
                            Empresas recién constituidas detectadas en BORME y listas para prospección comercial.
                            <?php if ($sector_label): ?>
                                Especialmente útiles para proveedores de <?= mb_strtolower($sector_label) ?>, asesorías, software y servicios B2B.
                            <?php else: ?>
                                Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="ae-radar-page__live-badge">
                        <span class="ae-radar-page__live-badge-dot"></span>
                        Actualizado hoy
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
                                        <span><?= esc($co['registro_mercantil'] ?? $province ?? 'España') ?></span>
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
                                        <h3>Desbloquea el listado completo de <?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas nuevas</h3>
                                        <p>
                                            Accede a todas las sociedades detectadas en <?= esc($heading_highlight) ?>, filtra por provincia, sector y periodo, y exporta leads preparados para prospección comercial.
                                        </p>

                                        <div class="ae-radar-page__premium-points">
                                            <span>Filtros por sector y provincia</span>
                                            <span>Exportación Excel / CSV</span>
                                            <span>Nuevas empresas cada día</span>
                                        </div>
                                    </div>

                                    <div class="ae-radar-page__premium-strip-actions">
                                        <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--light">
                                            Abrir Radar
                                        </a>
                                        <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--dark">
                                            Descargar Excel · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                                        </a>
                                        <p class="ae-radar-page__premium-footnote">
                                            Descarga puntual de empresas nuevas en Excel.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($premiumLeads)): ?>
                    <?php $dummyLeads = array_slice($premiumLeads, 0, 9); ?>

                    <div class="ae-radar-page__paywall-zone">
                        <div class="ae-radar-page__paywall-blur" aria-hidden="true">
                            <div class="ae-radar-page__lead-grid">
                                <?php foreach ($dummyLeads as $co): ?>
                                    <div class="ae-radar-page__lead-card ae-radar-page__lead-card--ghost">
                                        <div class="ae-radar-page__lead-card-main">
                                            <div class="ae-radar-page__lead-top-row">
                                                <div class="ae-radar-page__lead-badge">Lead premium</div>
                                                <div class="ae-radar-page__lead-date">--/--/----</div>
                                            </div>

                                            <h3 class="ae-radar-page__lead-title ae-radar-page__skeleton ae-radar-page__skeleton--title"></h3>

                                            <div class="ae-radar-page__lead-chips">
                                                <div class="ae-radar-page__lead-chip ae-radar-page__skeleton ae-radar-page__skeleton--chip"></div>
                                                <div class="ae-radar-page__lead-chip ae-radar-page__skeleton ae-radar-page__skeleton--chip ae-radar-page__skeleton--chip-short"></div>
                                            </div>

                                            <div class="ae-radar-page__lead-box">
                                                <div class="ae-radar-page__lead-box-label">Objeto social</div>
                                                <div class="ae-radar-page__skeleton ae-radar-page__skeleton--text"></div>
                                                <div class="ae-radar-page__skeleton ae-radar-page__skeleton--text ae-radar-page__skeleton--text-short"></div>
                                            </div>

                                            <div class="ae-radar-page__lead-intent">
                                                <div class="ae-radar-page__lead-intent-label">Potenciales necesidades</div>
                                                <div class="ae-radar-page__skeleton ae-radar-page__skeleton--intent"></div>
                                            </div>
                                        </div>

                                        <div class="ae-radar-page__lead-btn ae-radar-page__lead-btn--ghost">
                                            Ver ficha
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

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

                                        <div class="ae-radar-page__paywall-kicker">Acceso Premium</div>

                                        <h3 class="ae-radar-page__paywall-title">
                                            Desbloquea el listado completo de nuevas empresas
                                        </h3>

                                        <p class="ae-radar-page__paywall-subtitle">
                                            Accede al directorio completo de nuevas empresas en <?= esc($province ?? 'España') ?> y trabaja con leads filtrables por sector, provincia y fecha de constitución.
                                            <?php if ($sector_label): ?>
                                                <span class="ae-radar-page__paywall-subtitle-note">
                                                    Ideal para detectar empresas de <?= mb_strtolower($sector_label) ?> en fase temprana.
                                                </span>
                                            <?php endif; ?>
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

                                    <div class="ae-radar-page__paywall-benefits">
                                        <span>Filtros por sector y provincia</span>
                                        <span>Exportación Excel / CSV</span>
                                        <span>Nuevas empresas cada día</span>
                                    </div>

                                    <div class="ae-radar-page__paywall-highlight">
                                        Consigue ventaja competitiva y contacta con nuevos fundadores antes que el resto del mercado.
                                    </div>

                                    <div class="ae-radar-page__paywall-actions">
                                        <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                            <span>Activar Suscripción Radar</span>
                                            <span class="ae-radar-page__paywall-price-tag">79€/mes</span>
                                        </a>

                                        <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">
                                            <span>
                                                <?php
                                                if ($period === 'hoy') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Hoy)';
                                                elseif ($period === 'semana') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Semana)';
                                                elseif ($period === 'mes') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Mes)';
                                                else echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas';
                                                ?>
                                            </span>
                                            <span class="ae-radar-page__paywall-secondary-price"><?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€</span>
                                        </a>
                                    </div>

                                    <div class="ae-radar-page__paywall-divider"></div>

                                    <div class="ae-radar-page__paywall-notify">
                                        <p class="ae-radar-page__paywall-notify-title">¿Prefieres recibir avisos?</p>
                                        <p class="ae-radar-page__paywall-notify-subtitle">Recibe por email nuevas empresas similares a esta búsqueda.</p>

                                        <form action="#" method="POST" class="ae-radar-page__paywall-form" onsubmit="alert('Lead capturado.'); return false;">
                                            <input type="email" name="email" placeholder="Tu email profesional" required class="ae-radar-page__paywall-input">
                                            <button type="submit" class="ae-radar-page__paywall-submit">Avisarme</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--sectors container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Radar sectorial</span>
                <h3 class="ae-radar-page__section-title">Sectores con alta actividad en España</h3>
                <p class="ae-radar-page__section-subtitle">
                    Explora los sectores donde se están concentrando más nuevas constituciones mercantiles y detecta nichos con mayor tracción comercial.
                </p>
            </div>

            <div class="ae-radar-page__sector-pills">
                <?php foreach ($related_sectors as $index => $rs): ?>
                    <a
                        href="<?= site_url('empresas-nuevas-sector/' . url_title($rs['label'], '-', true)) ?>"
                        class="ae-radar-page__sector-pill <?= $index < 2 ? 'ae-radar-page__sector-pill--featured' : '' ?>"
                        title="<?= esc($rs['label']) ?>"
                    >
                        <span><?= esc($rs['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--excel container">
            <div class="ae-radar-page__excel-box">
                <div class="ae-radar-page__excel-content">
                    <span class="ae-radar-page__excel-kicker">Exportación directa</span>

                    <h3 class="ae-radar-page__excel-title">¿Necesitas el listado en formato Excel?</h3>

                    <p class="ae-radar-page__excel-subtitle">
                        Descarga el directorio completo de nuevas empresas en España en formato XLSX, listo para prospección comercial, análisis y carga en tu CRM.
                    </p>

                    <div class="ae-radar-page__excel-points">
                        <span>Excel / CSV</span>
                        <span>Descarga inmediata</span>
                    </div>

                    <p class="ae-radar-page__excel-includes">
                        Incluye nombre, CIF, provincia, capital inicial, administradores y CNAE.
                    </p>

                    <div class="ae-radar-page__excel-actions">
                        <a href="<?= site_url('checkout/radar-export?province=' . urlencode($province ?? '') . '&sector=' . urlencode($sectorName ?? '') . '&period=' . urlencode(empty($period) || $period === 'general' ? '30days' : $period)) ?>" class="ae-radar-page__excel-btn">
                            Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                        </a>
                    </div>

                    <p class="ae-radar-page__excel-footnote">
                        Exportación puntual en formato Excel, sin necesidad de suscripción.
                    </p>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--strategic container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Exploración rápida</span>
                <h3 class="ae-radar-page__section-title">Directorios estratégicos de empresas nuevas</h3>
                <p class="ae-radar-page__section-subtitle">
                    Accede a rutas clave por provincia, periodo y sector para descubrir nuevas oportunidades de prospección.
                </p>
            </div>

            <div class="ae-radar-page__strategic-grid">
                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Esta semana</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-semana/madrid') ?>">Empresas nuevas en Madrid</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-semana/barcelona') ?>">Empresas nuevas en Barcelona</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Este mes</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-mes/madrid') ?>">Empresas nuevas en Madrid</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-mes/barcelona') ?>">Empresas nuevas en Barcelona</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Sectores top en BCN</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-programacion-informatica-en-barcelona') ?>">Programación en Barcelona</a></li>
                        <li><a href="<?= site_url('empresas-marketing-en-barcelona') ?>">Marketing en Barcelona</a></li>
                        <li><a href="<?= site_url('empresas-consultoria-tecnologica-en-barcelona') ?>">Consultoría en Barcelona</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Tecnología nacional</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-sector/6201-programacion-informatica') ?>">Empresas de tecnología</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/6202-consultoria-informatica') ?>">Empresas de consultoría</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Nuevas empresas por sector</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-sector/6201-programacion-informatica') ?>">Empresas de tecnología</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/4110-construccion') ?>">Empresas de construcción</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/7311-publicidad-y-marketing') ?>">Empresas de marketing</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Provincias con más actividad</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas/madrid') ?>">Empresas en Madrid</a></li>
                        <li><a href="<?= site_url('empresas/barcelona') ?>">Empresas en Barcelona</a></li>
                        <li><a href="<?= site_url('empresas/valencia') ?>">Empresas en Valencia</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--seo container">
            <div class="ae-radar-page__seo-box">
                <div class="ae-radar-page__section-head ae-radar-page__section-head--left">
                    <span class="ae-radar-page__section-kicker">Contexto de mercado</span>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                        Sobre las nuevas empresas <?= $sector_label ? "de " . mb_strtolower($sector_label) : "" ?> en <?= esc(ucfirst(mb_strtolower($province ?? 'España', 'UTF-8'))) ?>
                    </h2>
                </div>

                <div class="ae-radar-page__seo-content">
                    <p>
                        <?= esc($heading_highlight) ?> es uno de los principales hubs <?= $sector_label ? "de " . esc($sector_label) : "empresariales" ?> de España. Cada mes se registran cientos de
                        <a href="<?= site_url('empresas/' . url_title($province ?? '', '-', true)) ?>">empresas en <?= esc($heading_highlight) ?></a>,
                        incluyendo nuevas sociedades creadas recientemente, lo que genera un ecosistema dinámico de oportunidades B2B.
                    </p>

                    <p>
                        Especialmente en sectores como la
                        <a href="<?= site_url('empresas-cnae/programacion-informatica') ?>">programación informática</a>
                        y los servicios digitales, estas
                        <a href="<?= site_url('empresas-nuevas') ?>">empresas nuevas en España</a>
                        suelen contratar marketing, asesoría y proveedores tecnológicos durante sus primeros meses de actividad.
                        <?php if ($sector_label): ?>
                            Descubre más
                            <a href="<?= site_url('empresas-cnae/' . url_title($sector_label, '-', true)) ?>">empresas de <?= mb_strtolower($sector_label) ?> en España</a>.
                        <?php endif; ?>
                    </p>

                    <p class="ae-radar-page__seo-highlight">
                        Con el Radar puedes detectar estas nuevas empresas antes que tu competencia y posicionarte como su proveedor desde el primer día.
                        <a href="<?= site_url('empresas-nuevas-hoy') ?>">Empresas creadas hoy</a>,
                        <a href="<?= site_url('empresas-nuevas-semana') ?>">empresas creadas esta semana</a>
                        o <a href="<?= site_url('empresas') ?>">ver todas las empresas en España</a>.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>