<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr)) return 'Reciente';
    $dateStr = str_replace('/', '-', $dateStr);
    $timestamp = strtotime($dateStr);

    if (!$timestamp || $timestamp > time() || $timestamp < strtotime('1900-01-01')) {
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

    if ($diffDays <= 7) return 'Nueva empresa';
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

$buildCheckoutUrl = site_url('checkout/radar-export') .
    '?type=single&period=' . urlencode($period === 'general' ? '30days' : ($period ?? ''));

$companies = $companies ?? [];
$paywall_level = $paywall_level ?? 'strong';

if ($paywall_level === 'none') {
    $freeCount = 100;
} elseif ($paywall_level === 'soft') {
    $freeCount = 20;
} else {
    $freeCount = ($period === 'hoy') ? 3 : 6;
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
        'robots'      => $robots ?? 'index,follow',
    ]) ?>

    <link rel="stylesheet" href="<?= base_url('public/css/radar_new_companies_period.css?v=' . (file_exists(FCPATH . 'public/css/radar_new_companies_period.css') ? filemtime(FCPATH . 'public/css/radar_new_companies_period.css') : time())) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <main class="ae-radar-page">
        <section id="container_radar_header" class="ae-radar-page__hero container">
            <div class="ae-radar-page__hero-inner">
                <div class="ae-radar-page__hero-shell">
                    <span class="ae-radar-page__pill">
                        LEADS B2B • ÚLTIMAS CONSTITUCIONES
                    </span>

                    <style>
                        .ae-radar-page__breadcrumbs {
                            margin-bottom: 24px;
                            overflow: hidden;
                        }
                        .ae-radar-page__breadcrumbs-list {
                            list-style: none;
                            padding: 0;
                            margin: 0;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            font-size: 0.82rem;
                            color: #64748b;
                            font-weight: 700;
                            white-space: nowrap;
                            overflow-x: auto;
                            -ms-overflow-style: none;
                            scrollbar-width: none;
                            mask-image: linear-gradient(to right, black 85%, transparent 100%);
                            -webkit-mask-image: linear-gradient(to right, black 85%, transparent 100%);
                        }
                        .ae-radar-page__breadcrumbs-list::-webkit-scrollbar { display: none; }
                        .ae-radar-page__breadcrumbs-list li { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
                        .ae-radar-page__breadcrumbs-list a { color: inherit; text-decoration: none; transition: color 0.2s; }
                        .ae-radar-page__breadcrumbs-list a:hover { color: #111827; }
                        .ae-radar-page__breadcrumbs-separator { opacity: 0.4; font-weight: 400; font-size: 0.9rem; }
                        .ae-radar-page__breadcrumbs-current { color: #111827; font-weight: 850; }
                        @media (max-width: 600px) {
                            .ae-radar-page__breadcrumbs-list { font-size: 0.76rem; gap: 6px; }
                            .ae-radar-page__breadcrumbs-list li { gap: 6px; }
                        }
                    </style>

                    <nav class="ae-radar-page__breadcrumbs" aria-label="Breadcrumb">
                        <ol class="ae-radar-page__breadcrumbs-list">
                            <li><a href="<?= site_url('/') ?>">Inicio</a></li>
                            <li class="ae-radar-page__breadcrumbs-separator">/</li>
                            <li><a href="<?= site_url('empresas-nuevas') ?>">Empresas Nuevas</a></li>
                            <li class="ae-radar-page__breadcrumbs-separator">/</li>
                            <li class="ae-radar-page__breadcrumbs-current" aria-current="page"><?= esc($title) ?></li>
                        </ol>
                    </nav>

                    <h1 class="ae-radar-page__title">
                        <?= esc($heading_prefix) ?>
                        <span class="ae-radar-page__title-grad"><?= esc($heading_time) ?></span>
                        <?= esc($heading_suffix) ?><?= esc($heading_highlight) ?><?= esc($heading_middle) ?><?= esc($heading_location) ?>
                    </h1>

                    <p class="ae-radar-page__subtitle">
                        <?php if ($period === 'hoy'): ?>
                            Detecta nuevas empresas creadas hoy en España y accede a oportunidades comerciales antes que tu competencia.
                        <?php elseif ($period === 'semana'): ?>
                            Detecta empresas creadas esta semana en España y accede a nuevas oportunidades comerciales antes que tu competencia.
                        <?php else: ?>
                            Detecta empresas creadas este mes en España y accede a nuevas oportunidades comerciales antes que tu competencia.
                        <?php endif; ?>
                    </p>

                    <p class="ae-radar-page__hero-copy">
                        Cada día se registran en España nuevas empresas con necesidades tempranas de software, asesoría, marketing digital, financiación y soluciones B2B. Esta página te ayuda a detectar esas oportunidades antes que tu competencia.
                    </p>

                    <div class="ae-radar-page__hero-actions">
                        <a href="<?= site_url('radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                            Abrir Radar
                        </a>

                        <?php if (!($is_low_results ?? false)): ?>
                            <a href="<?= site_url('leads-empresas-nuevas') ?>" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                                Descubre los beneficios del Radar PRO
                            </a>

                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__btn ae-radar-page__btn--primary js-loading-btn">
                                        Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                                    </a>
                                </div>
                            <?php else: ?>
                            <a href="#avisarme-seccion" class="ae-radar-page__btn ae-radar-page__btn--soft">
                                Avisarme de nuevas empresas
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="ae-radar-page__hero-alt-downloads">
                        <span>O prefieres descargar:</span>
                        <?php if ($period !== 'hoy' && ($stats['hoy'] ?? 0) > 0): ?>
                            <a href="<?= site_url('checkout/radar-export?period=hoy') ?>" class="js-loading-btn">Nacional Hoy (<?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($prices['hoy'] ?? 2, 0) ?>€</a>
                        <?php endif; ?>
                        <?php if ($period !== 'semana' && ($stats['semana'] ?? 0) > 0): ?>
                            <a href="<?= site_url('checkout/radar-export?period=semana') ?>" class="js-loading-btn">Nacional Semana (<?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($prices['semana'] ?? 4, 0) ?>€</a>
                        <?php endif; ?>
                        <?php if ($period !== 'mes' && $period !== 'general' && ($stats['30days'] ?? 0) > 0): ?>
                            <a href="<?= site_url('checkout/radar-export?period=30days') ?>" class="js-loading-btn">Nacional Mes (<?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($prices['mes'] ?? 9, 0) ?>€</a>
                        <?php endif; ?>
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
                                <a href="<?= site_url('leads-empresas-nuevas') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary ae-radar-page__btn--panel">
                                    Conoce el Radar y capta más clientes
                                </a>
                        </div>
                    </div>

                    <div class="ae-radar-page__stats-wrap">
                        <div class="ae-radar-page__stats">
                            <a href="<?= site_url('empresas-nuevas-hoy') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--today <?= ($period === 'hoy') ? 'ae-radar-page__stat-card--active' : '' ?>">
                                <div class="ae-radar-page__stat-icon">
                                    <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <div class="ae-radar-page__stat-label">Constituidas hoy</div>
                                    <div class="ae-radar-page__stat-value"><?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                            </a>

                            <a href="<?= site_url('empresas-nuevas-semana') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--week <?= ($period === 'semana') ? 'ae-radar-page__stat-card--active' : '' ?>">
                                <div class="ae-radar-page__stat-icon">
                                    <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
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
                            </a>

                            <a href="<?= site_url('empresas-nuevas-mes') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--month <?= ($period === 'mes') ? 'ae-radar-page__stat-card--active' : '' ?>">
                                <div class="ae-radar-page__stat-icon">
                                    <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                        <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="ae-radar-page__stat-label">Últimos 30 días</div>
                                    <div class="ae-radar-page__stat-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($top_sectors)): ?>
        <section class="ae-radar-page__section ae-radar-page__section--territory container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">
                    Radar territorial
                </span>

                <h3 class="ae-radar-page__section-title">
                    Provincias con más empresas nuevas
                </h3>

                <p class="ae-radar-page__section-subtitle">
                    Ranking territorial de las provincias con mayor volumen de nuevas constituciones detectadas en España durante los últimos 90 días.
                </p>
            </div>

            <?php
            $topProvinceItems = array_slice($top_sectors, 0, 8);
            $maxProvinceTotal = 0;

            foreach ($topProvinceItems as $provinceItem) {
                $provinceTotal = (int) ($provinceItem['total'] ?? 0);
                if ($provinceTotal > $maxProvinceTotal) {
                    $maxProvinceTotal = $provinceTotal;
                }
            }
            ?>

            <div class="ae-radar-page__territory-grid">
                <?php foreach ($topProvinceItems as $index => $item): ?>
                    <?php
                    $label = $item['cnae_label'] ?? 'Sin detalle';
                    $total = (int) ($item['total'] ?? 0);
                    $percent = $maxProvinceTotal > 0 ? round(($total / $maxProvinceTotal) * 100) : 0;
                    $barWidth = $maxProvinceTotal > 0 ? max(10, $percent) : 10;
                    $rank = $index + 1;

                    $microLabel = match ($rank) {
                        1 => 'Mayor volumen',
                        2 => 'Alta actividad',
                        3 => 'Alta actividad',
                        default => 'Radar activo',
                    };
                    ?>
                    <article class="ae-radar-page__territory-card <?= $rank <= 3 ? 'ae-radar-page__territory-card--top' : '' ?>">
                        <div class="ae-radar-page__territory-top">
                            <div class="ae-radar-page__territory-rank">#<?= $rank ?></div>
                            <div class="ae-radar-page__territory-micro"><?= esc($microLabel) ?></div>
                        </div>

                        <div class="ae-radar-page__territory-main">
                            <h3 class="ae-radar-page__territory-name"><?= esc($label) ?></h3>

                            <div class="ae-radar-page__territory-value-wrap">
                                <div class="ae-radar-page__territory-value">
                                    <?= number_format($total, 0, ',', '.') ?>
                                </div>
                                <div class="ae-radar-page__territory-value-label">
                                    constituciones
                                </div>
                            </div>

                            <div class="ae-radar-page__territory-progress-meta">
                                <span>Últimos 90 días</span>
                                <span><?= $percent ?>% del líder</span>
                            </div>

                            <div class="ae-radar-page__territory-bar">
                                <span style="width: <?= esc((string) $barWidth) ?>%;"></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">
            <div class="ae-radar-page__leads-shell">
                <?php if (!($is_low_results ?? false)): ?>
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
                            Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                        </p>
                    </div>

                    <div class="ae-radar-page__live-badge">
                        <span class="ae-radar-page__live-badge-dot"></span>
                        Actualizado hoy
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($is_low_results ?? false): ?>
                    <div class="ae-radar-page__empty-state">
                        <div class="ae-radar-page__empty-state-inner">
                            <div class="ae-radar-page__empty-state-header">
                                <div class="ae-radar-page__empty-state-kicker">Búsqueda de mercado B2B</div>
                                <h3 class="ae-radar-page__empty-state-title-main">Sin resultados recientes</h3>
                                <p class="ae-radar-page__empty-state-subtitle">
                                    No hemos detectado nuevas constituciones en las últimas horas para este filtro, pero puedes explorar el histórico nacional o activar alertas.
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
                                    <h4 class="ae-radar-page__empty-card-title">Directorio General</h4>
                                    <p class="ae-radar-page__empty-card-text">Explora el listado histórico completo de todas las empresas de España.</p>
                                    <a href="<?= $general_directory_url ?? site_url('directorio') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">Ver directorio general</a>
                                </div>

                                <div class="ae-radar-page__empty-card ae-radar-page__empty-card--accent" id="avisarme-seccion">
                                    <div class="ae-radar-page__empty-card-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                    </div>
                                    <h4 class="ae-radar-page__empty-card-title">Alertas personalizadas</h4>
                                    <p class="ae-radar-page__empty-card-text">Recibe un email cada vez que detectemos una nueva constitución hoy.</p>
                                    <form action="#" method="POST" onsubmit="event.preventDefault(); fetch('<?= site_url('leads/subscribe') ?>', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({email: this.querySelector('input[type=email]').value, province: '<?= esc($province ?? 'España') ?>', source: 'empty_state'})}).then(r => r.json()).then(d => { if(d.status === 'success') { Swal.fire('¡Listo!', d.message, 'success'); this.reset(); } else { Swal.fire('Error', 'Error al suscribirse.', 'error'); } }).catch(() => Swal.fire('Error', 'Error al suscribirse.', 'error'));" class="ae-radar-page__empty-form">
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
                                        <span><?= esc($co['registro_mercantil'] ?? ' España') ?></span>
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

                            <?php 
                            helper('company');
                            $coUrl = company_url($co);
                            ?>
                            <a href="<?= $coUrl ?>" class="ae-radar-page__lead-btn">
                                Ver empresa
                            </a>
                        </article>

                        <?php if ($index === 4 && !empty($premiumLeads)): ?>
                            <div class="ae-radar-page__premium-strip-wrap">
                                <div class="ae-radar-page__premium-strip">
                                    <div class="ae-radar-page__premium-strip-copy">
                                        <h3>Desbloquea el listado completo de <?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas nuevas</h3>
                                        <p>
                                            Accede a todas las sociedades detectadas en este periodo, filtra por provincia y sector, y exporta leads preparados para prospección comercial.
                                        </p>

                                        <div class="ae-radar-page__premium-points">
                                            <span>Filtros por sector y provincia</span>
                                            <span>Exportación Excel / CSV</span>
                                            <span>Nuevas empresas cada día</span>
                                        </div>
                                    </div>

                                    <div class="ae-radar-page__premium-strip-actions">
                                        <a href="<?= site_url('radar') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--light">
                                            Abrir Radar
                                        </a>
                                        <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--dark js-loading-btn">
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
        <div class="ae-radar-page__paywall-grid" aria-hidden="true">
            <?php foreach (array_slice($premiumLeads, 0, 6) as $hiddenLead): ?>
                <article class="ae-radar-page__paywall-grid-card">
                    <div class="ae-radar-page__paywall-grid-top">
                        <span class="ae-radar-page__paywall-grid-badge"><?= esc($getLeadBadge($hiddenLead['fecha_constitucion'] ?? null)) ?></span>
                        <span class="ae-radar-page__paywall-grid-date"><?= esc($formatEsDate($hiddenLead['fecha_constitucion'] ?? null)) ?></span>
                    </div>

                    <h4 class="ae-radar-page__paywall-grid-title">
                        <?= esc($formatCompanyName($hiddenLead['name'] ?? '')) ?>
                    </h4>

                    <div class="ae-radar-page__paywall-grid-meta">
                        <span><?= esc($hiddenLead['cnae'] ?? 'Sector no detallado') ?></span>
                        <span><?= esc($hiddenLead['registro_mercantil'] ?? 'España') ?></span>
                    </div>

                    <div class="ae-radar-page__paywall-grid-lines">
                        <span></span>
                        <span></span>
                    </div>

                    <div class="ae-radar-page__paywall-grid-lock">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                            <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                        </svg>
                        <span>Lead bloqueado</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="ae-radar-page__paywall-fade"></div>

        <div class="ae-radar-page__paywall-overlay">
            <div class="ae-radar-page__paywall-card">
                <div class="ae-radar-page__paywall-topbar"></div>

                <div class="ae-radar-page__paywall-body">
                    <div class="ae-radar-page__paywall-kicker">Acceso premium</div>

                    <h3 class="ae-radar-page__paywall-title">
                        Desbloquea el listado completo de nuevas empresas
                    </h3>

                    <p class="ae-radar-page__paywall-subtitle">
                        Accede al resto de empresas detectadas en este periodo, filtra por sector y provincia, y exporta leads preparados para prospección comercial.
                    </p>

                    <div class="ae-radar-page__paywall-stats">
                        <div class="ae-radar-page__paywall-stat">
                            <div class="ae-radar-page__paywall-stat-value"><?= number_format($total_context_count ?? 0, 0, ',', '.') ?></div>
                            <div class="ae-radar-page__paywall-stat-label">Empresas detectadas</div>
                        </div>

                        <div class="ae-radar-page__paywall-stat">
                            <div class="ae-radar-page__paywall-stat-value"><?= number_format(count($premiumLeads), 0, ',', '.') ?></div>
                            <div class="ae-radar-page__paywall-stat-label">Bloqueadas ahora</div>
                        </div>

                        <div class="ae-radar-page__paywall-stat">
                            <div class="ae-radar-page__paywall-stat-value"><?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€</div>
                            <div class="ae-radar-page__paywall-stat-label">Descarga puntual</div>
                        </div>
                    </div>

                    <div class="ae-radar-page__paywall-benefits">
                        <span>Filtros por sector y provincia</span>
                        <span>Exportación Excel / CSV</span>
                        <span>Leads listos para prospección</span>
                    </div>

                    <div class="ae-radar-page__paywall-actions">
                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                            <span>Abrir Radar</span>
                            <span class="ae-radar-page__paywall-price-tag">79€/mes</span>
                        </a>

                        <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary js-loading-btn">
                            <span>Descargar listado completo</span>
                            <span class="ae-radar-page__paywall-secondary-price"><?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>       



            </div>
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
                            $sectorLabel = $rs['label'] ?? 'Sector no disponible';
                            $sectorText = mb_strtolower($sectorLabel, 'UTF-8');

                            $sectorMeta = [
                                'title' => $sectorLabel,
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
                                    'title' => $sectorLabel,
                                    'desc'  => 'Empresas orientadas a software, cloud, servicios IT y captación digital.',
                                    'tag'   => 'Sector dinámico',
                                ];
                            } elseif (str_contains($sectorText, 'servic')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
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

                    <h3 class="ae-radar-page__excel-title">¿Necesitas el listado en formato Excel?</h3>

                    <p class="ae-radar-page__excel-subtitle">
                        Descarga el directorio completo de nuevas empresas en España en formato XLSX, listo para prospección comercial, análisis y carga en tu CRM.
                    </p>

                    <div class="ae-radar-page__excel-actions">
                            <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 400px;">
                                <a href="<?= site_url('checkout/radar-export?period=' . urlencode($period) . ($sectorLabel ? '&sector=' . urlencode($sectorLabel) : '')) ?>" class="ae-radar-page__excel-btn js-loading-btn">
                                    Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                                </a>
                                <button type="button" class="ae-radar-page__excel-btn ae-radar-page__excel-btn--alt ae-email-export-btn" 
                                        style="background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #ffffff;"
                                        data-url="<?= site_url('checkout/radar-email') . '?' . http_build_query($_GET) ?>"
                                        data-total="<?= number_format($total_context_count ?? 0, 0, ',', '.') ?>">
                                    Enviar por correo
                                </button>
                            </div>

                        <div class="ae-radar-page__excel-alt-links">
                            <span class="ae-radar-page__excel-alt-label">Otras opciones:</span>
                            <?php if ($period !== 'hoy' && ($stats['hoy'] ?? 0) > 0): ?>
                                <a href="<?= site_url('checkout/radar-export?period=hoy') ?>" class="js-loading-btn">Nacional Hoy (<?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($prices['hoy'] ?? 2, 0) ?>€</a>
                            <?php endif; ?>
                            <?php if ($period !== 'semana' && ($stats['semana'] ?? 0) > 0): ?>
                                <a href="<?= site_url('checkout/radar-export?period=semana') ?>" class="js-loading-btn">Nacional Semana (<?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($prices['semana'] ?? 4, 0) ?>€</a>
                            <?php endif; ?>
                            <?php if ($period !== 'mes' && $period !== 'general' && ($stats['30days'] ?? 0) > 0): ?>
                                <a href="<?= site_url('checkout/radar-export?period=30days') ?>" class="js-loading-btn">Nacional Mes (<?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($prices['mes'] ?? 9, 0) ?>€</a>
                            <?php endif; ?>
                        </div>
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
                        Sobre las nuevas empresas en España
                    </h2>
                </div>

                <div class="ae-radar-page__seo-content">
                    <p>
                        España es uno de los principales hubs empresariales de Europa. Cada mes se registran miles de <a href="<?= site_url('directorio') ?>">empresas en España</a>, incluyendo nuevas sociedades creadas recientemente, lo que genera un ecosistema dinámico de oportunidades B2B.
                    </p>

                    <p>
                        Especialmente en sectores como la tecnología y los servicios profesionales, estas <a href="<?= site_url('empresas-nuevas') ?>">empresas nuevas</a> suelen contratar marketing, asesoría y proveedores tecnológicos durante sus primeros meses de actividad.
                    </p>

                    <p class="ae-radar-page__seo-highlight">
                        Con el Radar puedes detectar estas nuevas empresas antes que tu competencia y posicionarte como su proveedor desde el primer día.
                        <a href="<?= site_url('empresas-nuevas-hoy') ?>">Empresas creadas hoy</a>,
                        <a href="<?= site_url('empresas-nuevas-semana') ?>">esta semana</a>
                        o <a href="<?= site_url('empresas-nuevas-mes') ?>">este mes</a>.
                    </p>
                </div>
            </div>
        </section>

        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@graph": [
            {
              "@type": "BreadcrumbList",
              "itemListElement": [
                {
                  "@type": "ListItem",
                  "position": 1,
                  "name": "Inicio",
                  "item": "<?= site_url('/') ?>"
                },
                {
                  "@type": "ListItem",
                  "position": 2,
                  "name": "Empresas Nuevas",
                  "item": "<?= site_url('empresas-nuevas') ?>"
                },
                {
                  "@type": "ListItem",
                  "position": 3,
                  "name": "<?= esc($title) ?>"
                }
              ]
            },
            {
              "@type": "Product",
              "name": "Listado Excel: <?= esc($title) ?>",
              "description": "Descarga directa del listado B2B de nuevas empresas en formato Excel para prospección comercial.",
              "offers": {
                "@type": "Offer",
                "price": "<?= number_format($dynamic_price['base_price'] ?? 9, 2, '.', '') ?>",
                "priceCurrency": "EUR",
                "availability": "https://schema.org/InStock",
                "url": "<?= current_url() ?>"
              }
            }
          ]
        }
        </script>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>