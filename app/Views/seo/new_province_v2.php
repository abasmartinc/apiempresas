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

<?= view('partials/header') ?>

<main>
    <section class="hero container">
        <div style="max-width: 900px; margin: 0 auto;">
            <span class="pill top" style="background: rgba(33, 82, 255, 0.08); color: var(--primary); border: 1px solid rgba(33, 82, 255, 0.15); font-weight: 800; letter-spacing: 0.05em;">
                LEADS B2B • ÚLTIMAS CONSTITUCIONES
            </span>

            <h1 class="title">
                <?php if (isset($heading_prefix)): ?>
                    <?= esc($heading_prefix) ?><?= esc($heading_suffix) ?><span class="grad"><?= esc($heading_highlight) ?></span><?= esc($heading_middle ?? ' en ') ?><?= esc($heading_location ?? '') ?><?= esc($heading_time) ?><br>
                <?php else: ?>
                    <?php $headingRaw = esc($heading_title ?? ('Nuevas Empresas en ' . ($province ?? 'España'))); ?>
                    <span class="grad"><?= $headingRaw ?></span><br>
                <?php endif; ?>
                <span style="color: #0f172a; font-size: 0.6em; font-weight: 800;">Análisis B2B y Distribución Nacional</span>
            </h1>

            <p class="subtitle" style="max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6;">
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

            <div style="max-width: 700px; margin: -10px auto 32px; font-size: 0.95rem; color: #64748b; line-height: 1.6; text-align: center;">
                En <?= esc($heading_highlight) ?> se registran cada mes nuevas empresas del sector <?= esc($sector_label ?? 'diversos sectores') ?>, incluyendo desarrollo de software, consultoría tecnológica y servicios digitales. Estas nuevas sociedades suelen necesitar proveedores tecnológicos, marketing digital, asesoría y soluciones SaaS durante sus primeros meses de actividad.
            </div>

            <div class="hero-buttons" style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-bottom: 30px;">
                <a href="<?= site_url('precios-radar') ?>" class="btn" style="background: var(--primary); color: white; padding: 16px 32px; font-size: 1.1rem; border-radius: 12px; font-weight: 700; box-shadow: 0 8px 16px rgba(33,82,255,0.2);">
                    Abrir Radar
                </a>
                <a href="#leads-b2b-recientes" class="btn ghost" style="padding: 16px 32px; font-size: 1.1rem; border-radius: 12px; font-weight: 700; background: white; border: 1px solid #cbd5e1; color: #475569;">
                    Ver muestra gratuita ↓
                </a>
                <a href="<?= $buildCheckoutUrl ?>" class="btn ghost" style="padding: 12px 24px; font-size: 0.95rem; border-radius: 10px; font-weight: 600; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b;">
                    Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <strong>9€</strong>
                </a>
            </div>

            <div class="radar-push-block" style="background: linear-gradient(135deg, #f8fafc, #eff6ff); border: 2px solid #dbeafe; border-radius: 20px; padding: 28px; max-width: 850px; margin: 0 auto 48px; display: flex; align-items: center; justify-content: space-between; gap: 32px; text-align: left; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.05);">
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 12px 0; font-size: 1.25rem; font-weight: 900; color: #0f172a; letter-spacing: -0.01em;">Accede al Radar de empresas nuevas</h4>
                    <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.95rem; color: #475569; display: grid; grid-template-columns: 1fr; gap: 8px;">
                        <li style="display: flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Detecta</strong> nuevas empresas cada día</li>
                        <li style="display: flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Filtra</strong> por sector, provincia y activity</li>
                        <li style="display: flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Exporta</strong> leads listos para prospección</li>
                    </ul>
                </div>
                <a href="<?= site_url('precios-radar') ?>" class="btn" style="padding: 16px 32px; font-size: 1.05rem; background: var(--primary); color: white; border-radius: 12px; font-weight: 800; white-space: nowrap; box-shadow: 0 4px 12px rgba(33,82,255,0.2);">Abrir Radar</a>
            </div>

            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 60px;">
                <div class="value-prop-card v-today">
                    <div class="v-icon-box" style="background: #eef2ff; color: #3b82f6;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div>
                        <div class="v-label">Nuevas Hoy</div>
                        <div class="v-value"><?= number_format($stats['hoy'] ?? 0, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="value-prop-card v-week">
                    <div class="v-icon-box" style="background: #ecfdf5; color: #10b981;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div>
                        <div class="v-label">Últimos 7 días</div>
                        <div class="v-value"><?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="value-prop-card v-month">
                    <div class="v-icon-box" style="background: #fffbeb; color: #f59e0b;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    </div>
                    <div>
                        <div class="v-label">Últimos 30 días</div>
                        <div class="v-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($top_sectors)): ?>
    <section class="container" style="margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="font-size: 2rem; font-weight: 950; color: #0f172a; margin-bottom: 8px; letter-spacing: -0.02em;">Impacto Geográfico y Sectorial</h2>
                <p style="color: #64748b; font-size: 1.1rem; font-weight: 500;">Distribución de las últimas constituticiones en <?= esc($heading_highlight) ?></p>
            </div>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach (array_slice($top_sectors, 0, 8) as $item): ?>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 16px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width: 48px; height: 48px; background: #f8fafc; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 800; font-size: 1.2rem; border: 1px solid #f1f5f9;">
                        <?= substr($item['cnae_label'] ?? 'E', 0, 1) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px;">
                            <?= $province ? 'Sector' : 'Provincia' ?>
                        </div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 4px;"><?= esc($item['cnae_label']) ?></div>
                        <div style="font-size: 0.95rem; color: var(--primary); font-weight: 700;"><?= number_format($item['total'], 0, ',', '.') ?> empresas <span style="font-weight: 400; color: #94a3b8; font-size: 0.85rem;">(90d)</span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section id="leads-b2b-recientes" class="container" style="margin-bottom: 40px;">
        <div class="leads-section-shell">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div class="section-kicker" style="margin-bottom: 14px;">
                        <span class="section-kicker-dot"></span>
                        Muestra comercial en tiempo real
                    </div>
                    <h2 style="font-size: 2.25rem; font-weight: 950; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.02em;">Leads B2B Recientes</h2>
                    <p style="color: #64748b; font-size: 1.05rem; font-weight: 500; max-width: 760px; line-height: 1.65; margin: 0;">
                        Empresas recién constituidas detectadas en BORME y listas para prospección comercial.
                        <?php if ($sector_label): ?>
                            Especialmente útiles para proveedores de <?= mb_strtolower($sector_label) ?>, asesorías, software y servicios B2B.
                        <?php else: ?>
                            Ideal para despachos, software, marketing, seguros, asesoría, financiación y proveedores B2B.
                        <?php endif; ?>
                    </p>
                </div>

                <div style="background: rgba(33,82,255,0.05); border: 1px solid rgba(33,82,255,0.1); border-radius: 100px; padding: 8px 16px; display: flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: block; box-shadow: 0 0 8px #10b981;"></span>
                    <span style="font-size: 0.9rem; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.05em;">Actualizado hoy</span>
                </div>
            </div>

            <?php
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

            <div class="lead-grid">
                <?php foreach ($freeLeads as $index => $co): ?>
                    <?php
                    $companyName = $formatCompanyName($co['name'] ?? '');
                    $leadBadge = $getLeadBadge($co['fecha_constitucion'] ?? null);
                    $leadSignals = $getCommercialSignals($co['cnae'] ?? '', $co['objeto_social'] ?? '');
                    ?>
                    <div class="lead-card">
                        <div>
                            <div class="lead-top-row">
                                <div class="lead-badge"><?= esc($leadBadge) ?></div>
                                <div class="lead-date"><?= esc($formatEsDate($co['fecha_constitucion'] ?? null)) ?></div>
                            </div>

                            <h3 class="lead-title"><?= esc($companyName) ?></h3>

                            <div class="lead-chips">
                                <div class="lead-chip">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"></path></svg>
                                    <span class="lead-chip-text"><?= esc($co['cnae'] ?? 'Sector no detallado') ?></span>
                                </div>

                                <div class="lead-chip">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="lead-chip-text"><?= esc($co['registro_mercantil'] ?? $province ?? 'España') ?></span>
                                </div>
                            </div>

                            <div class="lead-object-box">
                                <div class="lead-object-label">Objeto social</div>
                                <div class="lead-object-text">
                                    <?= esc($co['objeto_social'] ?? 'Actividad no detallada en la publicación disponible.') ?>
                                </div>
                            </div>

                            <div class="lead-intent">
                                <div class="lead-intent-label">Potenciales necesidades</div>
                                <div class="lead-intent-text"><?= esc($leadSignals) ?></div>
                            </div>
                        </div>

                        <a href="<?= site_url('empresa/' . url_title($co['name'], '-', true) . '-' . $co['cif']) ?>" class="lead-btn">
                            Analizar empresa
                        </a>
                    </div>

                    <?php if ($index === 4 && !empty($premiumLeads)): ?>
                        <div style="grid-column: 1 / -1;">
                            <div class="mid-premium-strip">
                                <div>
                                    <h3>
                                        Desbloquea el listado completo de <?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas nuevas
                                    </h3>
                                    <p>
                                        Accede a todas las sociedades detectadas en <?= esc($heading_highlight) ?>, filtra por provincia, sector y periodo, y exporta leads preparados para prospección comercial.
                                    </p>

                                    <div class="premium-points">
                                        <span class="premium-point">Filtros por sector y provincia</span>
                                        <span class="premium-point">Exportación Excel / CSV</span>
                                        <span class="premium-point">Nuevas empresas cada día</span>
                                    </div>
                                </div>

                                <div class="premium-cta-stack">
                                    <a href="<?= site_url('precios-radar') ?>" class="premium-primary-btn">Abrir Radar</a>
                                    <a href="<?= $buildCheckoutUrl ?>" class="premium-secondary-btn">Descargar listado · 9€</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($paywall_level === 'soft' && count($freeLeads) >= 50): ?>
                <div style="margin: 40px 0; background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 16px; padding: 24px; text-align: center;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #1e1b4b; margin-bottom: 8px;">¿Buscas más empresas en <?= esc($province ?? $heading_highlight) ?>?</h3>
                    <p style="color: #4338ca; margin-bottom: 16px; font-weight: 500;">Accede al listado completo y detecta todas las nuevas aperturas en tiempo real con Radar.</p>
                    <a href="<?= site_url('precios-radar') ?>" style="display: inline-block; background: var(--primary); color: white; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none;">
                        Abrir Radar Premium
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($premiumLeads)): ?>
                <?php $dummyLeads = array_slice($premiumLeads, 0, 9); ?>

               <div class="premium-blur-zone">
    <div class="premium-blur-bg" aria-hidden="true">
        <div class="lead-grid">
            <?php foreach ($dummyLeads as $co): ?>
                <div class="lead-card premium-ghost-card">
                    <div>
                        <div class="lead-top-row">
                            <div class="lead-badge premium-ghost-badge">Lead premium</div>
                            <div class="lead-date">--/--/----</div>
                        </div>

                        <h3 class="lead-title">
                            <div class="skeleton-line skeleton-line-title"></div>
                        </h3>

                        <div class="lead-chips">
                            <div class="lead-chip skeleton-chip">
                                <div class="skeleton-line skeleton-line-chip"></div>
                            </div>
                            <div class="lead-chip skeleton-chip skeleton-chip-short">
                                <div class="skeleton-line skeleton-line-chip"></div>
                            </div>
                        </div>

                        <div class="lead-object-box premium-ghost-box">
                            <div class="lead-object-label">Objeto social</div>
                            <div class="skeleton-line skeleton-line-text"></div>
                            <div class="skeleton-line skeleton-line-text skeleton-line-text-short"></div>
                        </div>

                        <div class="lead-intent premium-ghost-intent">
                            <div class="lead-intent-label premium-ghost-label">Potenciales necesidades</div>
                            <div class="skeleton-line skeleton-line-intent"></div>
                        </div>
                    </div>

                    <div class="lead-btn premium-ghost-btn">Analizar empresa</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="premium-dark-wash" aria-hidden="true"></div>

    <div class="paywall-overlay">
        <div class="paywall-box">
                   <div class="paywall-topbar"></div>

<div class="paywall-body">
    <div class="paywall-header">
        <div class="paywall-icon-wrap">
            <div class="paywall-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                    <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                </svg>
            </div>
        </div>

        <div class="paywall-kicker">Acceso Premium</div>

        <h3 class="paywall-title">
            Desbloquea el listado completo de nuevas empresas
        </h3>

        <p class="paywall-subtitle">
            Accede al directorio completo de nuevas empresas en <?= esc($province ?? 'España') ?> y trabaja con leads filtrables por sector, provincia y fecha de constitución.
            <?php if ($sector_label): ?>
                <span class="paywall-subtitle-note">
                    Ideal para detectar empresas de <?= mb_strtolower($sector_label) ?> en fase temprana.
                </span>
            <?php endif; ?>
        </p>
    </div>

    <div class="paywall-stat-row">
        <div class="paywall-stat">
            <div class="paywall-stat-value"><?= number_format($total_context_count ?? 0, 0, ',', '.') ?></div>
            <div class="paywall-stat-label">Empresas detectadas</div>
        </div>
        <div class="paywall-stat">
            <div class="paywall-stat-value"><?= number_format($stats['semana'] ?? 0, 0, ',', '.') ?></div>
            <div class="paywall-stat-label">Últimos 7 días</div>
        </div>
        <div class="paywall-stat">
            <div class="paywall-stat-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
            <div class="paywall-stat-label">Últimos 30 días</div>
        </div>
    </div>

    <div class="paywall-benefits">
        <span class="paywall-benefit">Filtros por sector y provincia</span>
        <span class="paywall-benefit">Exportación Excel / CSV</span>
        <span class="paywall-benefit">Nuevas empresas cada día</span>
    </div>

    <div class="paywall-highlight">
        Consigue ventaja competitiva y contacta con nuevos fundadores antes que el resto del mercado.
    </div>

    <div class="paywall-actions">
        <a href="<?= site_url('precios-radar') ?>" class="paywall-primary-cta">
            <span>Activar Suscripción Radar</span>
            <span class="paywall-price-tag">79€/mes</span>
        </a>

        <a href="<?= $buildCheckoutUrl ?>" class="paywall-secondary-cta">
            <span>
                <?php
                if ($period === 'hoy') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Hoy)';
                elseif ($period === 'semana') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Semana)';
                elseif ($period === 'mes') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Mes)';
                else echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas';
                ?>
            </span>
            <span class="paywall-secondary-price">9€</span>
        </a>
    </div>

    <div class="paywall-divider"></div>

    <div class="paywall-notify">
        <p class="paywall-notify-title">¿Prefieres recibir avisos?</p>
        <p class="paywall-notify-subtitle">Recibe por email nuevas empresas similares a esta búsqueda.</p>

        <form action="#" method="POST" class="paywall-notify-form" onsubmit="alert('Lead capturado.'); return false;">
            <input
                type="email"
                name="email"
                placeholder="Tu email profesional"
                required
                class="paywall-notify-input"
            >
            <button type="submit" class="paywall-notify-btn">Avisarme</button>
        </form>
    </div>
</div>     
        </div>
    </div>
</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="container sectors-activity-section">
    <div class="sectors-activity-header">
        <span class="sectors-activity-kicker">Radar sectorial</span>
        <h3 class="sectors-activity-title">Sectores con alta actividad en España</h3>
        <p class="sectors-activity-subtitle">
            Explora los sectores donde se están concentrando más nuevas constituciones mercantiles y detecta nichos con mayor tracción comercial.
        </p>
    </div>

    <div class="sectors-activity-tags">
        <?php foreach ($related_sectors as $index => $rs): ?>
            <a
                href="<?= site_url('empresas-nuevas-sector/' . url_title($rs['label'], '-', true)) ?>"
                class="sector-activity-pill <?= $index < 2 ? 'sector-activity-pill-featured' : '' ?>"
                title="<?= esc($rs['label']) ?>"
            >
                <span class="sector-activity-pill-text"><?= esc($rs['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

    <section class="container excel-cta-section">
    <div class="excel-cta-box">
        <div class="excel-cta-content">
            <span class="excel-cta-kicker">Exportación directa</span>
            <h3 class="excel-cta-title">¿Necesitas el listado en formato Excel?</h2>
            <p class="excel-cta-subtitle">
                Descarga el directorio completo de nuevas empresas en España en formato XLSX, listo para prospección, análisis comercial o importación en tu CRM.
            </p>

            <div class="excel-cta-points">
                <span class="excel-cta-point">Excel / CSV</span>
                <span class="excel-cta-point">Contactos y cargos</span>
                <span class="excel-cta-point">Insights financieros</span>
            </div>

            <a href="<?= site_url('billing/single_checkout?period=30days') ?>" class="excel-cta-button">
                Descargar listado (<?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?> empresas) · 9€
            </a>
        </div>
    </div>
</section>

<section class="container strategic-links-section">
    <div class="strategic-links-box">
        <div class="strategic-links-header">
            <span class="strategic-links-kicker">Exploración rápida</span>
            <h3 class="strategic-links-title">Directorios estratégicos de empresas nuevas</h3>
            <p class="strategic-links-subtitle">
                Accede a rutas clave por provincia, periodo y sector para descubrir nuevas oportunidades de prospección.
            </p>
        </div>

        <div class="strategic-links-grid strategic-links-grid-six">
            <div class="strategic-link-card">
                <div class="strategic-link-card-top">
                    <span class="strategic-link-card-dot"></span>
                    <h4 class="strategic-link-card-title">Esta semana</h4>
                </div>

                <ul class="strategic-link-list">
                    <li><a href="<?= site_url('empresas-nuevas-semana/madrid') ?>">Empresas nuevas en Madrid</a></li>
                    <li><a href="<?= site_url('empresas-nuevas-semana/barcelona') ?>">Empresas nuevas en Barcelona</a></li>
                </ul>
            </div>

            <div class="strategic-link-card">
                <div class="strategic-link-card-top">
                    <span class="strategic-link-card-dot"></span>
                    <h4 class="strategic-link-card-title">Este mes</h4>
                </div>

                <ul class="strategic-link-list">
                    <li><a href="<?= site_url('empresas-nuevas-mes/madrid') ?>">Empresas nuevas en Madrid</a></li>
                    <li><a href="<?= site_url('empresas-nuevas-mes/barcelona') ?>">Empresas nuevas en Barcelona</a></li>
                </ul>
            </div>

            <div class="strategic-link-card">
                <div class="strategic-link-card-top">
                    <span class="strategic-link-card-dot"></span>
                    <h4 class="strategic-link-card-title">Sectores top en BCN</h4>
                </div>

                <ul class="strategic-link-list">
                    <li><a href="<?= site_url('empresas-programacion-informatica-en-barcelona') ?>">Programación en Barcelona</a></li>
                    <li><a href="<?= site_url('empresas-marketing-en-barcelona') ?>">Marketing en Barcelona</a></li>
                    <li><a href="<?= site_url('empresas-consultoria-tecnologica-en-barcelona') ?>">Consultoría en Barcelona</a></li>
                </ul>
            </div>

            <div class="strategic-link-card">
                <div class="strategic-link-card-top">
                    <span class="strategic-link-card-dot"></span>
                    <h4 class="strategic-link-card-title">Tecnología nacional</h4>
                </div>

                <ul class="strategic-link-list">
                    <li><a href="<?= site_url('empresas-nuevas-sector/6201-programacion-informatica') ?>">Empresas de tecnología</a></li>
                    <li><a href="<?= site_url('empresas-nuevas-sector/6202-consultoria-informatica') ?>">Empresas de consultoría</a></li>
                </ul>
            </div>

            <div class="strategic-link-card">
                <div class="strategic-link-card-top">
                    <span class="strategic-link-card-dot"></span>
                    <h4 class="strategic-link-card-title">Nuevas empresas por sector</h4>
                </div>

                <ul class="strategic-link-list">
                    <li><a href="<?= site_url('empresas-nuevas-sector/6201-programacion-informatica') ?>">Empresas de tecnología</a></li>
                    <li><a href="<?= site_url('empresas-nuevas-sector/4110-construccion') ?>">Empresas de construcción</a></li>
                    <li><a href="<?= site_url('empresas-nuevas-sector/7311-publicidad-y-marketing') ?>">Empresas de marketing</a></li>
                </ul>
            </div>

            <div class="strategic-link-card">
                <div class="strategic-link-card-top">
                    <span class="strategic-link-card-dot"></span>
                    <h4 class="strategic-link-card-title">Provincias con más actividad</h4>
                </div>

                <ul class="strategic-link-list">
                    <li><a href="<?= site_url('empresas/madrid') ?>">Empresas en Madrid</a></li>
                    <li><a href="<?= site_url('empresas/barcelona') ?>">Empresas en Barcelona</a></li>
                    <li><a href="<?= site_url('empresas/valencia') ?>">Empresas en Valencia</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

   <section class="container seo-info-section">
    <div class="seo-info-box">
        <div class="seo-info-header">
            <span class="seo-info-kicker">Contexto de mercado</span>
            <h3 class="seo-info-title">
                Sobre las nuevas empresas <?= $sector_label ? "de " . mb_strtolower($sector_label) : "" ?> en <?= esc(ucfirst(mb_strtolower($province ?? 'España', 'UTF-8'))) ?>
            </h3>
        </div>

        <div class="seo-info-content">
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

            <p class="seo-info-highlight">
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