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
    $formatted = date('d M Y', $timestamp);
    return str_replace($mesesEn, $mesesEs, $formatted);
};

$formatCompanyName = function($name) {
    if (empty($name)) return 'Empresa sin nombre';
    $name = mb_strtolower(trim($name), 'UTF-8');
    return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
};

$slugProvince = url_title($province, '-', true);
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
    <link rel="stylesheet" href="<?= base_url('public/css/radar_period.css?v=' . time()) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header') ?>

    <main class="ae-radar-page">
        <!-- PREMIUM HERO -->
        <section class="ae-radar-page__hero container">
            <div class="ae-radar-page__hero-inner">
                <span class="ae-radar-page__pill">
                    CATÁLOGO TERRITORIAL • <?= esc(mb_strtoupper($province, 'UTF-8')) ?>
                </span>

                <h1 class="ae-radar-page__title">
                    Empresas en <span class="ae-radar-page__title-grad"><?= esc($heading_highlight) ?></span>
                    <span class="ae-radar-page__title-sub">Listado nacional de sociedades registradas</span>
                </h1>

                <p class="ae-radar-page__subtitle">
                   Accede a la base de datos de las <strong><?= number_format($total, 0, ',', '.') ?> empresas activas</strong> en la provincia de <?= esc($province) ?>.
                </p>

                <div class="ae-radar-page__hero-actions" style="margin-bottom: 40px;">
                    <a href="<?= site_url('billing/single_checkout?province=' . urlencode($province)) ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Descargar Listado Completo
                    </a>
                    <a href="#leads" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                        Ver últimas aperturas
                    </a>
                </div>

                <div class="ae-radar-page__stats">
                    <div class="ae-radar-page__stat-card">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Empresas Totales</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($total, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="ae-radar-page__stat-card">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Crecimiento</div>
                            <div class="ae-radar-page__stat-value" style="color: #10b981;">+<?= $growth_pct ?>%</div>
                        </div>
                    </div>

                    <div class="ae-radar-page__stat-card">
                        <div class="ae-radar-page__stat-icon" style="background: #fff7ed; color: #f97316;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Exportación</div>
                            <div class="ae-radar-page__stat-value">EXCEL</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTORS HUB -->
        <?php if (!empty($top_sectors)): ?>
        <section class="container" style="margin-bottom: 80px;">
            <div class="ae-radar-page__leads-header">
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Sectores Dominantes</h2>
                <p style="color: #64748b; margin: 4px 0 0 0;">Actividades con mayor presencia en <?= esc($province) ?></p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach ($top_sectors as $sec): 
                    $sectorSlug = url_title($sec['label'] ?? '', '-', true);
                ?>
                    <a href="<?= site_url("empresas-{$sectorSlug}-en-{$slugProvince}") ?>" class="ae-radar-page__sector-pill" style="justify-content: space-between; padding: 20px; border-radius: 16px; background: white; border: 1px solid #e2e8f0; text-decoration: none;">
                        <span style="font-weight: 800; color: #0f172a;"><?= esc($sec['label'] ?? 'Sector') ?></span>
                        <span style="color: var(--primary); font-weight: 700;"><?= number_format($sec['total'] ?? 0, 0, ',', '.') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- LEADS GRID -->
        <section id="leads" class="ae-radar-page__section container">
             <div class="ae-radar-page__leads-header">
                <div>
                    <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Últimas Constituciones</h2>
                    <p style="color: #64748b; margin: 4px 0 0 0;">Nuevas empresas registradas recientemente en <?= esc($province) ?></p>
                </div>
                <div class="ae-radar-page__live-badge">Actualizado hoy</div>
            </div>

            <div class="ae-radar-page__lead-grid">
                <?php foreach (array_slice($companies ?? [], 0, 10) as $co): 
                    $coSlug = url_title($co['name'] ?? '', '-', true);
                    $coCif = $co['cif'] ?? '';
                    $coUrl = site_url($coCif . ($coSlug ? ('-' . $coSlug) : ''));
                ?>
                    <article class="ae-radar-page__lead-card">
                        <div class="ae-radar-page__lead-card-main">
                             <div class="ae-radar-page__lead-top-row">
                                <div class="ae-radar-page__lead-badge">NUEVA</div>
                                <div class="ae-radar-page__lead-date"><?= esc($formatEsDate($co['fecha_constitucion'] ?? null, 'd M Y')) ?></div>
                            </div>
                            <h3 class="ae-radar-page__lead-title"><?= esc($formatCompanyName($co['name'] ?? '')) ?></h3>
                            <div class="ae-radar-page__lead-chips">
                                <div class="ae-radar-page__lead-chip"><span>CIF: <?= esc(substr($coCif, 0, 3) . '*****') ?></span></div>
                                <div class="ae-radar-page__lead-chip"><span><?= esc($co['registro_mercantil'] ?? $province) ?></span></div>
                            </div>
                            <div class="ae-radar-page__lead-intent">
                                <div class="ae-radar-page__lead-intent-text">
                                    <strong>Actividad:</strong> <?= esc(mb_substr($co['objeto_social'] ?? $co['cnae_label'] ?? '', 0, 80)) ?>...
                                </div>
                            </div>
                        </div>
                        <a href="<?= esc($coUrl) ?>" class="ae-radar-page__lead-btn">Ver información comercial</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- PAYWALL ZONE -->
            <div class="ae-radar-page__paywall-zone" style="margin-top: 60px;">
                <div class="ae-radar-page__paywall-card">
                    <div class="ae-radar-page__paywall-body" style="text-align: center; padding: 60px 40px;">
                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 20px;">Acceso a la Base de Datos</span>
                        <h3 class="ae-radar-page__paywall-title">Descarga todas las empresas de <?= esc($province) ?></h3>
                        <p class="ae-radar-page__paywall-subtitle">Obtén el listado completo de las <?= number_format($total, 0, ',', '.') ?> sociedades en formato Excel exportable.</p>
                        <div class="ae-radar-page__paywall-actions" style="justify-content: center; gap: 20px;">
                            <a href="<?= site_url('billing/single_checkout?province=' . urlencode($province)) ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">Exportar Listado (9,90€)</a>
                            <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">Activar Radar Nacional</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTERNAL NAVIGATION -->
        <div class="container" style="margin-top: 4rem; padding-top: 4rem; border-top: 1px solid #e2e8f0; margin-bottom: 80px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <a href="<?= site_url('empresas-nuevas/' . $slugProvince) ?>" style="text-decoration: none; padding: 32px; border-radius: 20px; background: #eef2ff; border: 1px solid #e0e7ff; transition: all 0.2s;">
                    <div style="color: var(--primary); font-weight: 800; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em;">Filtro Temporal • Radar</div>
                    <div style="font-size: 1.4rem; font-weight: 900; color: #1e1b4b; line-height: 1.2;">Nuevas empresas registradas hoy en <?= esc($province) ?></div>
                </a>
                <a href="<?= site_url('empresas-nuevas') ?>" style="text-decoration: none; padding: 32px; border-radius: 20px; background: #fafafa; border: 1px solid #f1f1f1; transition: all 0.2s;">
                    <div style="color: #64748b; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em;">Hub Nacional</div>
                    <div style="font-size: 1.4rem; font-weight: 900; color: #1e293b; line-height: 1.2;">Ver mapa de constituciones en toda España</div>
                </a>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
