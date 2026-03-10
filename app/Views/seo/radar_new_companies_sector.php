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

$sectorLabel = $sector['label'] ?? 'Sector';
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

    <link rel="stylesheet" href="<?= base_url('public/css/radar_period.css?v=' . (file_exists(FCPATH . 'public/css/radar_period.css') ? filemtime(FCPATH . 'public/css/radar_period.css') : time())) ?>" />
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
                    Nuevas empresas de <span class="ae-radar-page__title-grad"><?= esc($sectorLabel) ?></span> en España
                    <span class="ae-radar-page__title-sub">Análisis de competitividad y tracción</span>
                </h1>

                <p class="ae-radar-page__subtitle">
                    Monitoriza la creación de nuevas sociedades en el sector de <?= esc(mb_strtolower($sectorLabel)) ?>. Accede a leads cualificados con necesidades inmediatas de servicios.
                </p>

                <div class="ae-radar-page__hero-actions">
                    <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Abrir Radar Completo
                    </a>
                    <a href="#leads-sectoriales" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                        Ver muestra gratuita
                    </a>
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
            <div class="ae-radar-page__leads-header">
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Oportunidades en <?= esc($sectorLabel) ?></h2>
                <div class="ae-radar-page__live-badge">
                    <span class="ae-radar-page__live-badge-dot"></span>
                    Actualizado BORME
                </div>
            </div>

            <div class="ae-radar-page__lead-grid">
                <?php foreach ($freeLeads as $co): ?>
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
                                <div class="ae-radar-page__lead-chip"><span> Sector <?= esc($co['cnae'] ?? $sectorLabel) ?></span></div>
                                <div class="ae-radar-page__lead-chip"><span><?= esc($co['registro_mercantil'] ?? 'España') ?></span></div>
                            </div>
                            <div class="ae-radar-page__lead-intent">
                                <div class="ae-radar-page__lead-intent-text"><?= esc($leadSignals) ?></div>
                            </div>
                        </div>
                        <a href="<?= site_url($co['cif'] ?? '') ?>" class="ae-radar-page__lead-btn">Ver empresa</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="ae-radar-page__paywall-zone" style="margin-top: 40px;">
                <div class="ae-radar-page__paywall-card">
                    <div class="ae-radar-page__paywall-body" style="text-align: center; padding: 60px 40px;">
                        <h3 class="ae-radar-page__paywall-title">Desbloquea el radar completo de <?= esc($sectorLabel) ?></h3>
                        <p class="ae-radar-page__paywall-subtitle">Accede a las <?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas detectadas en este sector y filtra por provincias para tu prospección.</p>
                        <div class="ae-radar-page__paywall-actions" style="justify-content: center;">
                            <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">Ver Planes Radar</a>
                            <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">Descargar Excel · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ae-radar-page__section ae-radar-page__section--sectors container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Otros Sectores</span>
                <h3 class="ae-radar-page__section-title">Explora más industrias</h3>
            </div>
            <div class="ae-radar-page__sector-pills">
                <?php foreach ($related_sectors as $rs): ?>
                    <a href="<?= site_url('empresas-nuevas-sector/' . url_title($rs['label'], '-', true)) ?>" class="ae-radar-page__sector-pill">
                        <span><?= esc($rs['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
