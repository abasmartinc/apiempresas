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

$buildCheckoutUrl = site_url('excel/preview') . 
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
                        <li class="ae-radar-page__breadcrumbs-current" aria-current="page"><?= esc($sectorLabel) ?></li>
                    </ol>
                </nav>

                <h1 class="ae-radar-page__title">
                    <?= esc($heading_prefix) ?>
                    <span class="ae-radar-page__title-grad"><?= esc($heading_time) ?></span>
                    <?= esc($heading_suffix) ?><?= esc($heading_highlight) ?><?= esc($heading_middle) ?><?= esc($heading_location) ?>
                </h1>

                <p class="ae-radar-page__subtitle" style="font-size: 1.25rem; font-weight: 600; margin-top: 1rem; color: #1e293b;">
                +<?= number_format($conversion_count ?? 0, 0, ',', '.') ?> empresas en <?= esc($heading_location ?? 'España') ?> listas para convertirse en clientes ahora mismo
            </p>

                <p class="ae-radar-page__hero-copy" style="margin-top: 1rem; opacity: 0.8; font-size: 1.1rem; color: #64748b; max-width: 600px;">
                    Si no actúas hoy, otro proveedor cerrará estas ventas antes que tú
                </p>

                <div class="ae-radar-page__hero-actions">
                <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                    Ver estas empresas antes que tu competencia
                </a>
            </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.75rem; font-weight: 500; text-align: center;">La mayoría de usuarios consigue su primer cliente en días</p>

                <!-- Bloque Económico (Conversion Tier) -->
                <div class="ae-radar-page__economic-block" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 2.5rem; border-radius: 1.25rem; color: white; margin: 3rem 0; text-align: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                    <p style="font-size: 1.25rem; margin-bottom: 0.75rem; opacity: 0.9; font-weight: 500;">Se han detectado <strong style="color: #4ade80;"><?= number_format($conversion_count ?? 0, 0, ',', '.') ?></strong> empresas nuevas <?= $conversion_label ?? 'recientemente' ?> con potencial comercial en este sector</p>
                    <h3 style="font-size: 1.85rem; font-weight: 900; line-height: 1.2; background: linear-gradient(to right, #4ade80, #22d3ee); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Estas empresas pueden generarte entre <?= $potential_revenue_min ?? '900' ?>€ y <?= $potential_revenue_max ?? '4.500' ?>€ en ventas reales en los próximos días</h3>
                    <p style="color: #94a3b8; font-size: 1.1rem; margin-top: 1rem; font-weight: 500;">Con 1 cliente cubres el coste mensual</p>
                </section>

<section id="leads-sectoriales" class="ae-radar-page__section ae-radar-page__section--leads container">

            <div class="ae-radar-page__leads-shell" style="background: #f8fbff !important; border: 1px solid rgba(59,130,246,0.15) !important; border-radius: 2rem !important; margin-top: 2.5rem !important; padding: 3rem 2.5rem !important; position: relative !important; overflow: hidden !important; box-shadow: 0 10px 50px -12px rgba(59,130,246,0.12), 0 4px 12px rgba(0,0,0,0.02) !important;">
            <style>
                @keyframes shine { to { background-position: 200% center; } } 
                @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
                .ae-radar-page__lead-card.is-blurred {
                    filter: blur(8px) grayscale(0.2) !important;
                    transform: scale(0.98) !important;
                    opacity: 0.55;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.3s ease;
                    box-shadow: 0 20px 40px -8px rgba(59,130,246,0.35), 0 8px 16px -4px rgba(99,102,241,0.25) !important;
                    border-color: rgba(59,130,246,0.3) !important;
                }
            </style>
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6); background-size: 200% auto; animation: shine 3s linear infinite;"></div>
            
            <div style="padding-bottom: 1.75rem; position: relative;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 0.3rem 0.8rem; border-radius: 999px;">
                            <span style="width: 6px; height: 6px; background: #3b82f6; border-radius: 50%; display: inline-block; animation: pulse 2s infinite;"></span>
                            Muestra comercial en tiempo real
                        </span>
                    </div>
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #10b981; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); padding: 0.3rem 0.8rem; border-radius: 999px;">
                        <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block;"></span>
                        Actualizado hoy
                    </span>
                </div>
                <h2 style="font-size: 2rem; font-weight: 900; color: #0f172a; margin: 0 0 0.6rem; letter-spacing: -0.025em; line-height: 1.15;">
                    
                        Empresas activas en <span style="background: linear-gradient(135deg, #3b82f6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?= esc($sectorLabel) ?></span>
                    
                </h2>
                <p style="color: #64748b; font-size: 1rem; margin: 0; line-height: 1.65; max-width: 680px;">
                    Empresas recién constituidas detectadas en BORME y listas para prospección comercial. Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                </p>
            </div>
<?php if ($is_low_results ?? false): ?>
                <div class="ae-radar-page__empty-state">
                    <div class="ae-radar-page__empty-state-inner">
                        <div class="ae-radar-page__empty-state-header">
                            <div class="ae-radar-page__empty-state-kicker">Análisis Sectorial B2B</div>
                            <h3 class="ae-radar-page__empty-state-title-main">Sin resultados recientes</h3>
                            <p class="ae-radar-page__empty-state-subtitle">
                                No hemos detectado nuevas constituciones en <?= esc($sectorLabel) ?> en las últimas horas, pero puedes contactar el histórico nacional o activar alertas.
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
                                <h4 class="ae-radar-page__empty-card-title">Cerrar Sector</h4>
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
                                <form action="#" method="POST" onsubmit="event.preventDefault(); fetch('<?= site_url('leads/subscribe') ?>', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({email: this.querySelector('input[type=email]').value, province: '<?= esc($province ?? 'España') ?>', source: 'empty_state'})}).then(r => r.json()).then(d => { if(d.status === 'success') { Swal.fire('¡Listo!', d.message, 'success'); this.reset(); } else { Swal.fire('Error', 'Error al suscribirse.', 'error'); } }).catch(() => Swal.fire('Error', 'Error al suscribirse.', 'error'));" class="ae-radar-page__empty-form">
                                    <input type="email" placeholder="Tu email profesional" required class="ae-radar-page__empty-input">
                                    <button type="submit" class="ae-radar-page__empty-submit">OK</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <style>
                .ae-radar-page__lead-card.is-blurred {
                    filter: blur(8px) grayscale(0.2) !important;
                    transform: scale(0.98) !important;
                    filter: blur(5px);
                    opacity: 0.55;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.3s ease;
                    box-shadow: 0 20px 40px -8px rgba(59,130,246,0.35), 0 8px 16px -4px rgba(99,102,241,0.25);
                    border-color: rgba(59,130,246,0.3) !important;
                }
                .ae-radar-page__lead-grid-wrap { position: relative; padding-bottom: 3rem; }
                .ae-radar-page__lead-overlay-cta {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    height: 250px;
                    background: linear-gradient(to top, #f5f8ff 40%, rgba(245,248,255,0) 100%);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: flex-end;
                    padding-bottom: 2rem;
                    z-index: 20;
                    text-align: center;
                }
            </style>

            
        <!-- Bloque de Urgencia -->
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-left: 5px solid #ef4444; padding: 1.25rem 1.5rem; border-radius: 0.5rem; color: #991b1b; margin: 0 0 2rem 0; display: flex; align-items: center; gap: 1rem; box-shadow: none;">
            <span style="font-size: 2rem;">⚠️</span>
            <div>
                <strong style="font-size: 1.2rem; display: block; margin-bottom: 0.25rem;">Varias de estas empresas dejarán de estar disponibles en las próximas horas</strong>
                <p style="margin: 0; opacity: 0.9;">Algunas ya están siendo asignadas a otros proveedores</p>
            </div>
        </div>
    
            <div class="ae-radar-page__lead-grid-wrap">
                <div class="ae-radar-page__lead-grid">
                    <?php foreach (array_slice($freeLeads, 0, 6) as $index => $co): ?>
                        <?php
                        $companyName = $formatCompanyName($co['name'] ?? '');
                        $leadBadge = $getLeadBadge($co['fecha_constitucion'] ?? null);
                        $leadSignals = $getCommercialSignals($sector, $co['objeto_social'] ?? '');
                        ?>
                        <article class="ae-radar-page__lead-card <?= $index >= 3 ? 'is-blurred' : '' ?>">
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
                                        <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__premium-btn ae-radar-page__premium-btn--light">
                                            Acceder antes que tu competencia
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($premiumLeads)): ?>
                <div class="ae-radar-page__lead-overlay-cta" style="position: absolute; bottom: 0; left: 0; right: 0; height: 350px; background: linear-gradient(to top, rgba(245,248,255,1) 30%, rgba(245,248,255,0.9) 60%, transparent); display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 2rem; z-index: 10;">
        <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1.1rem 3rem; font-size: 1.15rem; font-weight: 800; border-radius: 100px; background: linear-gradient(135deg, #3b82f6, #6366f1); border: none; box-shadow: 0 8px 24px rgba(99,102,241,0.45), 0 2px 8px rgba(59,130,246,0.3); color: white; text-decoration: none; display: inline-block; transition: all 0.25s ease;">
            Ver estas empresas antes que tu competencia
        </a>
        <p style="font-weight: 700; color: #1e293b; margin-top: 1rem; margin-bottom: 0; font-size: 1rem; text-align: center;">Estas empresas están activas ahora mismo — accede antes de que otros proveedores las contacten</p>
    </div>
                <?php endif; ?>
            </div>

            

            <?php if (!empty($premiumLeads)): ?>
                <div class="ae-radar-page__paywall-zone">
                    <div class="ae-radar-page__paywall-overlay">
                        <div class="ae-radar-page__paywall-card" style="border: none !important; box-shadow: 0 40px 100px -20px rgba(15,23,42,0.3), 0 20px 40px -15px rgba(59,130,246,0.2) !important; background: rgba(255,255,255,0.98) !important; backdrop-filter: blur(10px) !important; border-radius: 2rem !important;">
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

                                <div class="ae-radar-page__paywall-actions" style="display: flex; justify-content: center; margin-top: 0.5rem;">
                                    <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary" style="background: linear-gradient(135deg, #3b82f6, #6366f1); border: none; padding: 1.1rem 3rem; font-size: 1.15rem; font-weight: 800; border-radius: 100px; box-shadow: 0 8px 24px rgba(99,102,241,0.45), 0 2px 8px rgba(59,130,246,0.3); letter-spacing: -0.01em; transition: all 0.25s ease; display: inline-block; min-width: 280px; text-align: center;">
                                        <span>Entrar al Radar ahora</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>

<section class="ae-radar-page__section container" style="background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin-top: 3rem !important;"><div class="ae-radar-page__stats">
                    <a href="<?= site_url('empresas-nuevas-hoy') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--today">
                        <div class="ae-radar-page__stat-icon">
                            <svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Nuevas hoy</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </a>

                    <a href="<?= site_url('empresas-nuevas-semana') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--week">
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

                    <a href="<?= site_url('empresas-nuevas-mes') ?>" class="ae-radar-page__stat-card ae-radar-page__stat-card--month">
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

                            if (str_contains($rowText, 'constru')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Demanda temprana de seguros, PRL, software y financiación.',
                                    'tag'   => 'Alta demanda B2B',
                                ];
                            } elseif (str_contains($rowText, 'comerc')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Potencial para ERP, pagos, asesoría y digitalización comercial.',
                                    'tag'   => 'Volumen recurrente',
                                ];
                            } elseif (str_contains($rowText, 'inmobil')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Buen encaje para CRM, marketing, firma digital y gestión documental.',
                                    'tag'   => 'Nicho comercial',
                                ];
                            } elseif (str_contains($rowText, 'restaur') || str_contains($rowText, 'hostel')) {
                                $sectorMeta = [
                                    'title' => $sectorLabel,
                                    'desc'  => 'Necesidades frecuentes de TPV, reservas, software y proveedores.',
                                    'tag'   => 'Alta rotación',
                                ];
                            } elseif (str_contains($rowText, 'tecnolog') || str_contains($rowText, 'inform')) {
                                $sectorMeta = [
                                    'title' => $rowLabel,
                                    'desc'  => 'Empresas orientadas a software, cloud, servicios IT y captación digital.',
                                    'tag'   => 'Sector dinámico',
                                ];
                            } elseif (str_contains($rowText, 'servic')) {
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
                                        Contactar
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

        <?php if (!empty($companies)): ?>
        <section class="ae-radar-page__section ae-radar-page__section--excel container" style="background: linear-gradient(135deg, #122347 0%, #19366d 55%, #2d57c7 100%) !important; border-radius: 2rem !important; overflow: hidden !important; border: 1px solid rgba(255,255,255,0.1) !important; box-shadow: 0 24px 60px -30px rgba(15, 23, 42, 0.34) !important;">
            
                <div class="ae-radar-page__excel-content">
                    <span class="ae-radar-page__excel-kicker">Exportación directa</span>

                    <h3 class="ae-radar-page__excel-title">¿Necesitas el listado de <?= esc($sectorLabel) ?> en Excel?</h3>

                    <p class="ae-radar-page__excel-subtitle">
                        Descarga el directorio completo de nuevas empresas de <?= esc($sectorLabel) ?> en formato XLSX, listo para prospección comercial.
                    </p>

                    <div class="ae-radar-page__excel-actions">
                        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 700px;">
                            <a href="<?= site_url('excel/preview?sector=' . urlencode($sectorLabel ?? '') . '&period=' . urlencode(empty($period) || $period === 'general' ? '30days' : $period)) ?>" class="ae-radar-page__excel-btn js-loading-btn" style="white-space: nowrap !important;">
                                Descargar listado de <?= esc($sectorLabel) ?> (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($dynamic_price['base_price'] ?? 9, 0) ?>€
                            </a>
                        </div>

                        <div class="ae-radar-page__excel-alt-links">
                            <span class="ae-radar-page__excel-alt-label">Otras opciones:</span>
                            <?php if (($national_stats['hoy'] ?? 0) > 0): ?>
                                <a href="<?= site_url('excel/preview?period=hoy') ?>" class="js-loading-btn">Nacional Hoy (<?= number_format($national_stats['hoy'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($national_prices['hoy'] ?? 2, 0) ?>€</a>
                            <?php endif; ?>
                            <?php if (($national_stats['semana'] ?? 0) > 0): ?>
                                <a href="<?= site_url('excel/preview?period=semana') ?>" class="js-loading-btn">Nacional Semana (<?= number_format($national_stats['semana'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($national_prices['semana'] ?? 4, 0) ?>€</a>
                            <?php endif; ?>
                            <?php if (($national_stats['30days'] ?? 0) > 0): ?>
                                <a href="<?= site_url('excel/preview?period=30days') ?>" class="js-loading-btn">Nacional Mes (<?= number_format($national_stats['30days'] ?? 0, 0, ',', '.') ?> empresas) · <?= number_format($national_prices['mes'] ?? 9, 0) ?>€</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
        <?php endif; ?>

        <section class="ae-radar-page__section ae-radar-page__section--strategic container">
            <div class="ae-radar-page__section-head ae-radar-page__section-head--center">
                <span class="ae-radar-page__section-kicker">Cierre rápido</span>
                <h3 class="ae-radar-page__section-title">Directorios estratégicos de empresas nuevas</h3>
                <p class="ae-radar-page__section-subtitle">
                    Accede a rutas clave para contactar nuevas oportunidades de prospección.
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
                        <h3>Clientes por sector</h3>
                    </div>
                    <ul>
                        <li><a href="<?= site_url('empresas-nuevas-sector/construccion') ?>">Nuevas en Construcción</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/hosteleria') ?>">Nuevas en Hostelería</a></li>
                        <li><a href="<?= site_url('empresas-nuevas-sector/tecnologia') ?>">Nuevas en Tecnología</a></li>
                    </ul>
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
            </section>

        <!-- Final CTA Section -->
<section class="ae-radar-page__section container" style="background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin-top: 3rem !important;" style="margin-top: 4rem; padding-bottom: 6rem;">
        <div style="background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 1.5rem; padding: 4rem 2rem; text-align: center; color: white; position: relative; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="position: relative; z-index: 2;">
                <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 1.5rem; letter-spacing: -0.02em; color: white;">Si no accedes ahora, estos clientes desaparecerán en horas</h2>
                <p style="font-size: 1.25rem; opacity: 0.8; max-width: 700px; margin: 0 auto 1.5rem; color: white;">Más de 40 empresas nuevas aparecen cada día — las primeras en contactar son las que cierran</p>
                <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="padding: 1.25rem 3.5rem; font-size: 1.25rem; font-weight: 700; border-radius: 100px; background: white; color: #0f172a; box-shadow: 0 0 30px rgba(255,255,255,0.2);">
                    Ver estas empresas antes que tu competencia
                </a>
                    <p style="font-size: 0.95rem; color: rgba(255,255,255,0.7); margin-top: 1.25rem; font-weight: 500;">La mayoría de usuarios consigue su primer cliente en días</p>
            </div>
            <!-- Subtle background glow -->
            <div style="position: absolute; top: -50%; left: -20%; width: 100%; height: 200%; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%);"></div>
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
                  "name": "<?= esc($sectorLabel) ?>",
                  "item": "<?= current_url() ?>"
                }
              ]
            },
            {
              "@type": "Product",
              "name": "Listado Excel: <?= esc($title) ?>",
              "description": "Descarga directa del listado B2B de nuevas empresas en el sector de <?= esc($sectorLabel) ?> en formato Excel para prospección comercial.",
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
    
    <!-- Modal Conversion -->
    <div id="radarConversionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); z-index: 99999; backdrop-filter: blur(8px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: white; border-radius: 1.25rem; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 600px; width: 90%; position: relative; text-align: center; transform: translateY(20px); transition: transform 0.3s ease;" class="radar-modal-content">
            <button onclick="closeRadarModal()" style="position: absolute; top: 1rem; right: 1rem; background: transparent; border: none; font-size: 1.75rem; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
            
            <div style="width: 56px; height: 56px; background: #fef2f2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V8a5 5 0 0 1 10 0v3"></path></svg>
            </div>
            
            <p style="font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; font-size: 1.5rem; letter-spacing: -0.02em;">Estas empresas están activas ahora mismo</p>
            <p style="color: #475569; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 500;">Otros proveedores ya están cerrando estas oportunidades</p>
            <p style="color: #dc2626; margin-bottom: 0.75rem; font-size: 1.1rem; font-weight: 700;">Si no actúas ahora, perderás estos clientes</p>
            <p style="color: #dc2626; margin-bottom: 2rem; font-size: 1.05rem; font-weight: 600;">Cada minuto que pasa, aumenta la probabilidad de perder estos clientes</p>
            <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="width: 100%; justify-content: center; padding: 1.15rem; font-size: 1.2rem; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); border-radius: 0.75rem;">
                Acceder ahora y contactar antes que otros proveedores
            </a>
            
            <p style="font-size: 0.95rem; color: #64748b; margin-top: 1.25rem; font-weight: 500;">La mayoría de usuarios consigue su primer cliente en días</p>
        </div>
    </div>

    <script>
    let modalTriggered = false;
    function showRadarModal() {
        if (modalTriggered) return;
        modalTriggered = true;
        const modal = document.getElementById('radarConversionModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.radar-modal-content').style.transform = 'translateY(0)';
            }, 10);
        }
    }

    function closeRadarModal() {
        const modal = document.getElementById('radarConversionModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.radar-modal-content').style.transform = 'translateY(20px)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Intercept clicks on blurred cards
        const blurredCards = document.querySelectorAll('.ae-radar-page__lead-card.is-blurred a');
        blurredCards.forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                setTimeout(showRadarModal, 300);
            });
        });

        // Intercept clicks on primary CTAs to show modal instead of direct redirect?
        // Wait, the user said: "Mostrar modal SOLO cuando: el usuario hace clic en CTA principal".
        // If they click the primary CTA, it should show the modal instead of navigating immediately?
        // Yes, that adds friction but increases the "pressure".
        const mainCtas = document.querySelectorAll('.ae-radar-page__btn--primary:not(#radarConversionModal .ae-radar-page__btn--primary)');
        mainCtas.forEach(cta => {
            cta.addEventListener('click', (e) => {
                if(!modalTriggered) {
                    e.preventDefault();
                    setTimeout(showRadarModal, 300);
                }
            });
        });

        // Scroll > 60%
        window.addEventListener('scroll', () => {
            const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent > 60) {
                setTimeout(showRadarModal, 300);
            }
        });
    });
    </script>
    
</main>

    <?= view('partials/footer') ?>
    
</body>
</html>
