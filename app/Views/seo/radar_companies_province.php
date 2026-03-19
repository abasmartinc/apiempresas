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
    if ($diffDays <= 7) return 'Nueva empresa';
    if ($diffDays <= 30) return 'Últimos 30 días';
    return 'Nueva empresa';
};

$getCommercialSignals = function($sectorLabel, $object) {
    $text = mb_strtolower(trim(($sectorLabel ?? '') . ' ' . ($object ?? '')), 'UTF-8');
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
        if (str_contains($text, $needle)) return implode(' · ', $signals);
    }
    return 'asesoría · software · marketing';
};

$companies = $companies ?? [];
$freeCount = 10;
$freeLeads = array_slice($companies, 0, $freeCount);
$premiumLeads = array_slice($companies, $freeCount);
$slugProvince = url_title($province, '-', true);
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
    <link rel="stylesheet" href="<?= base_url('public/css/radar_companies_province.css?v=' . (file_exists(FCPATH . 'public/css/radar_companies_province.css') ? filemtime(FCPATH . 'public/css/radar_companies_province.css') : time())) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <main class="ae-radar-page">
        <section id="container_radar_header" class="ae-radar-page__hero container">
            <div class="ae-radar-page__hero-inner">
                <div class="ae-radar-page__hero-shell">
                    <span class="ae-radar-page__pill">
                        CATÁLOGO TERRITORIAL • <?= esc(mb_strtoupper($province, 'UTF-8')) ?>
                    </span>

                    <h1 class="ae-radar-page__title">
                        Empresas en <span class="ae-radar-page__title-grad"><?= esc($heading_highlight) ?></span>
                    </h1>
                    <span class="ae-radar-page__title-sub">Listado nacional de sociedades por ubicación</span>

                    <p class="ae-radar-page__subtitle">
                        Accede a la base de datos de <strong><?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas activas</strong> en la provincia de <?= esc($province) ?>. Obtén leads enriquecidos y actualizados diariamente de BORME.
                    </p>

                    <div class="ae-radar-page__hero-actions">
                        <a href="<?= site_url('radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                            Abrir Radar
                        </a>

                        <?php if (!($is_low_results ?? false)): ?>
                            <a href="<?= site_url('leads-empresas-nuevas') ?>" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                                Ver cómo captar clientes con el Radar
                            </a>

                            <a href="<?= site_url('checkout/radar-export?type=single&provincia=' . urlencode($province)) ?>" class="ae-radar-page__btn ae-radar-page__btn--soft">
                                Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                            </a>
                        <?php else: ?>
                            <a href="#avisarme-seccion" class="ae-radar-page__btn ae-radar-page__btn--soft">
                                Avisarme de nuevas empresas
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="ae-radar-page__hero-panel">
                        <div class="ae-radar-page__hero-panel-copy">
                            <h2 class="ae-radar-page__hero-panel-title">Accede al Radar de empresas en <?= esc($province) ?></h2>
                            <ul class="ae-radar-page__hero-list">
                                <li>Monitoriza constituciones diarias en <?= esc($province) ?></li>
                                <li>Filtra por sector y exporta leads para tu CRM</li>
                                <li>Analiza el crecimiento empresarial de la zona</li>
                            </ul>
                        </div>

                        <div class="ae-radar-page__hero-panel-actions">
                            <a href="<?= site_url('radar') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary ae-radar-page__btn--panel">
                                Abrir Radar
                            </a>
                        </div>
                    </div>

                    <div class="ae-radar-page__stats-wrap">
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
                                    <div class="ae-radar-page__stat-value"><?= number_format($stats['mes'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTORS HUB -->
        <?php if (!empty($top_sectors)): ?>
        <section class="ae-radar-page__section container" style="margin-bottom: 80px;">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Distribución sectorial</span>
                <h3 class="ae-radar-page__section-title">Sectores Dominantes en <?= esc($province) ?></h3>
                <p class="ae-radar-page__section-subtitle">
                    Identifica los sectores económicos con mayor actividad y presencia histórica en la provincia para orientar tu prospección.
                </p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach ($top_sectors as $sec): 
                    $sectorSlug = url_title($sec['cnae_label'] ?? '', '-', true);
                ?>
                    <a href="<?= site_url("empresas-nuevas-sector/{$sectorSlug}") ?>" class="ae-radar-page__sector-pill" style="justify-content: space-between; padding: 20px; border-radius: 16px; background: white; border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s; display: flex; align-items: center;">
                        <span style="font-weight: 800; color: #0f172a;"><?= esc($sec['cnae_label'] ?? 'Sector') ?></span>
                        <span style="color: var(--ae-primary); font-weight: 700;"><?= number_format($sec['total'] ?? 0, 0, ',', '.') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- LEADS GRID -->
        <section id="leads-b2b-recientes" class="ae-radar-page__section ae-radar-page__section--leads container">
            <div class="ae-radar-page__leads-shell">
                <?php if (!($is_low_results ?? false)): ?>
                    <div class="ae-radar-page__leads-header">
                        <div>
                            <div class="ae-radar-page__section-kicker ae-radar-page__section-kicker--with-dot">
                                <span class="ae-radar-page__section-kicker-dot"></span>
                                Muestra oficial en tiempo real
                            </div>

                            <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">
                                Últimas empresas en <?= esc($province) ?>
                            </h2>

                            <p class="ae-radar-page__section-subtitle ae-radar-page__section-subtitle--left">
                                Sociedades recién inscritas en el Registro Mercantil de <?= esc($province) ?> detectadas en BORME y listas para prospección comercial.
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
                                <div class="ae-radar-page__empty-state-kicker">Búsqueda de mercado local</div>
                                <h3 class="ae-radar-page__empty-state-title-main">Sin resultados recientes</h3>
                                <p class="ae-radar-page__empty-state-subtitle">
                                    No hemos detectado nuevas constituciones en <?= esc($province) ?> en las últimas horas, pero puedes explorar el histórico nacional o activar alertas.
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
                                    <h4 class="ae-radar-page__empty-card-title">Directorio de <?= esc($province) ?></h4>
                                    <p class="ae-radar-page__empty-card-text">Explora el listado histórico completo de todas las empresas de esta provincia.</p>
                                    <a href="<?= site_url('directorio/provincia/' . $slugProvince) ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">Ver directorio provincial</a>
                                </div>

                                <div class="ae-radar-page__empty-card ae-radar-page__empty-card--accent" id="avisarme-seccion">
                                    <div class="ae-radar-page__empty-card-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                    </div>
                                    <h4 class="ae-radar-page__empty-card-title">Alertas por email</h4>
                                    <p class="ae-radar-page__empty-card-text">Recibe un aviso el mismo día que detectemos nuevas empresas en <?= esc($province) ?>.</p>
                                    <form action="#" method="POST" onsubmit="event.preventDefault(); fetch('<?= site_url('leads/subscribe') ?>', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({email: this.querySelector('input[type=email]').value, province: '<?= esc($province ?? 'España') ?>', source: 'empty_state'})}).then(r => r.json()).then(d => { if(d.status === 'success') { Swal.fire('¡Listo!', d.message, 'success'); this.reset(); } else { Swal.fire('Error', 'Error al suscribirse.', 'error'); } }).catch(() => Swal.fire('Error', 'Error al suscribirse.', 'error'));" class="ae-radar-page__empty-form">
                                        <input type="email" placeholder="Email" required class="ae-radar-page__empty-input">
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
                        $leadSignals = $getCommercialSignals($co['cnae_label'] ?? '', $co['objeto_social'] ?? '');
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
                                        <span><?= esc($co['cnae_label'] ?? 'Sector no detallado') ?></span>
                                    </div>

                                    <div class="ae-radar-page__lead-chip">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span><?= esc($co['registro_mercantil'] ?? $province) ?></span>
                                    </div>
                                </div>

                                <div class="ae-radar-page__lead-box">
                                    <div class="ae-radar-page__lead-box-label">Objeto social</div>
                                    <div class="ae-radar-page__lead-box-text">
                                        <?= esc($co['objeto_social'] ?? 'Actividad registrada bajo ' . ($co['cnae_label'] ?? 'este sector') . '.') ?>
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
                                    <h4 class="ae-radar-page__paywall-grid-title"><?= esc($formatCompanyName($hiddenLead['name'] ?? '')) ?></h4>
                                    <div class="ae-radar-page__paywall-grid-meta">
                                        <span><?= esc($hiddenLead['cnae_label'] ?? 'Sector no detallado') ?></span>
                                        <span><?= esc($hiddenLead['registro_mercantil'] ?? $province) ?></span>
                                    </div>
                                    <div class="ae-radar-page__paywall-grid-lines"><span></span><span></span></div>
                                    <div class="ae-radar-page__paywall-grid-lock">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V8a5 5 0 0 1 10 0v3"></path></svg>
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
                                    <div class="ae-radar-page__paywall-kicker">Acceso premium territorial</div>
                                    <h3 class="ae-radar-page__paywall-title">Desbloquea el listado completo de <?= esc($province) ?></h3>
                                    <p class="ae-radar-page__paywall-subtitle">
                                        Accede a las <strong><?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas</strong> de la provincia, filtra por sectores específicos y exporta leads directos a tu CRM.
                                    </p>
                                    <div class="ae-radar-page__paywall-stats">
                                        <div class="ae-radar-page__paywall-stat">
                                            <div class="ae-radar-page__paywall-stat-value"><?= number_format($total_context_count ?? 0, 0, ',', '.') ?></div>
                                            <div class="ae-radar-page__paywall-stat-label">Empresas totales</div>
                                        </div>
                                        <div class="ae-radar-page__paywall-stat">
                                            <div class="ae-radar-page__paywall-stat-value"><?= number_format($stats['mes'] ?? 0, 0, ',', '.') ?></div>
                                            <div class="ae-radar-page__paywall-stat-label">Crecimiento mes</div>
                                        </div>
                                        <div class="ae-radar-page__paywall-stat">
                                            <div class="ae-radar-page__paywall-stat-value"><?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€</div>
                                            <div class="ae-radar-page__paywall-stat-label">Descarga puntual</div>
                                        </div>
                                    </div>
                                    <div class="ae-radar-page__paywall-benefits">
                                        <span>Filtros avanzados por sector</span>
                                        <span>Exportación Excel / CSV</span>
                                        <span>Actualización diaria oficial</span>
                                    </div>
                                    <div class="ae-radar-page__paywall-actions">
                                        <a href="<?= site_url('radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">
                                            <span>Abrir Radar</span>
                                            <span class="ae-radar-page__paywall-price-tag">79€/mes</span>
                                        </a>
                                        <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province)) ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">
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

        <!-- INTERNAL NAVIGATION -->
        <section class="ae-radar-page__section container" style="margin-top: 4rem;">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Exploración rápida</span>
                <h3 class="ae-radar-page__section-title">Directorios estratégicos de empresas</h3>
                <p class="ae-radar-page__section-subtitle">
                    Accede a nuestras rutas optimizadas para descubrir nuevas oportunidades B2B de forma segmentada.
                </p>
            </div>

            <div class="ae-radar-page__strategic-grid">
                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Radar en <?= esc($province) ?></h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas/' . $slugProvince) ?>">Nuevas constituciones hoy</a></li>
                        <li><a href="<?= site_url('directorio/provincia/' . $slugProvince) ?>">Directorio histórico completo</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Periodos nacionales</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-hoy') ?>">Empresas creadas hoy</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-semana') ?>">Creadas esta semana</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-mes') ?>">Creadas este mes</a></li>
                    </ul>
                </div>

                <div class="ae-radar-page__strategic-card">
                    <div class="ae-radar-page__strategic-card-head">
                        <span class="ae-radar-page__strategic-card-dot"></span>
                        <h3>Centros Top</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas/madrid') ?>">Empresas en Madrid</a></li>
                        <li><a href="<?= site_url('empresas/barcelona') ?>">Empresas en Barcelona</a></li>
                        <li><a href="<?= site_url('empresas/valencia') ?>">Empresas en Valencia</a></li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
