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

$buildCheckoutUrl = site_url('billing/single_checkout?cnae=' . urlencode($cnae_code));
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
                    CATÁLOGO SECTORIAL • CNAE <?= esc($cnae_code) ?>
                </span>

                <h1 class="ae-radar-page__title">
                    Empresas de <span class="ae-radar-page__title-grad"><?= esc($cnae_label) ?></span> en España
                    <span class="ae-radar-page__title-sub">Directorio nacional actualizado y verificado</span>
                </h1>

                <p class="ae-radar-page__subtitle">
                   Monitorizamos las <strong><?= number_format($total_companies, 0, ',', '.') ?> sociedades activas</strong> en este sector. Identifica a tu competencia y accede a leads enriquecidos.
                </p>

                <div class="ae-radar-page__hero-actions">
                    <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__btn ae-radar-page__btn--primary">
                        Descargar Listado Completo
                    </a>
                    <a href="#leads-gratuitos" class="ae-radar-page__btn ae-radar-page__btn--ghost">
                        Ver muestra gratuita
                    </a>
                </div>

                <div class="ae-radar-page__stats">
                    <div class="ae-radar-page__stat-card">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Total Nacional</div>
                            <div class="ae-radar-page__stat-value"><?= number_format($total_companies, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="ae-radar-page__stat-card">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Provincias</div>
                            <div class="ae-radar-page__stat-value"><?= count($top_provinces) ?></div>
                        </div>
                    </div>

                    <div class="ae-radar-page__stat-card">
                        <div class="ae-radar-page__stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <div>
                            <div class="ae-radar-page__stat-label">Actualización</div>
                            <div class="ae-radar-page__stat-value">DIARIA</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- GEOGRAPHIC IMPACT -->
        <section class="container" style="margin-bottom: 80px;">
            <div class="ae-radar-page__leads-header">
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Impacto Geográfico</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach (($top_provinces ?? []) as $prov): 
                    $sectorSlug = url_title($cnae_label, '-', true);
                    $provinceSlug = url_title($prov['provincia'] ?? '', '-', true);
                ?>
                    <a href="<?= site_url("empresas-{$sectorSlug}-en-{$provinceSlug}") ?>" class="ae-radar-page__sector-pill" style="justify-content: space-between; padding: 20px; border-radius: 16px; background: white; border: 1px solid #e2e8f0; text-decoration: none;">
                        <span style="font-weight: 800; color: #0f172a;"><?= esc($prov['provincia'] ?? 'Provincia') ?></span>
                        <span style="color: var(--primary); font-weight: 700;"><?= number_format($prov['total'] ?? 0, 0, ',', '.') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- LEADS GRID -->
        <section id="leads-gratuitos" class="ae-radar-page__section container">
             <div class="ae-radar-page__leads-header">
                <h2 class="ae-radar-page__section-title ae-radar-page__section-title--left">Empresas Destacadas</h2>
                <div class="ae-radar-page__live-badge">Activas</div>
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
                                <div class="ae-radar-page__lead-badge">B2B</div>
                                <div class="ae-radar-page__lead-date"><?= esc($formatEsDate($co['fecha_constitucion'] ?? null, 'M Y')) ?></div>
                            </div>
                            <h3 class="ae-radar-page__lead-title"><?= esc($formatCompanyName($co['name'] ?? '')) ?></h3>
                            <div class="ae-radar-page__lead-chips">
                                <div class="ae-radar-page__lead-chip"><span>CIF: <?= esc(substr($coCif, 0, 3) . '*****') ?></span></div>
                                <div class="ae-radar-page__lead-chip"><span><?= esc($co['registro_mercantil'] ?? 'España') ?></span></div>
                            </div>
                            <div class="ae-radar-page__lead-intent">
                                <div class="ae-radar-page__lead-intent-text">
                                    <strong>Actividad:</strong> <?= esc(mb_substr($co['objeto_social'] ?? '', 0, 80)) ?>...
                                </div>
                            </div>
                        </div>
                        <a href="<?= esc($coUrl) ?>" class="ae-radar-page__lead-btn">Ver ficha completa</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- PAYWALL -->
            <div class="ae-radar-page__paywall-zone" style="margin-top: 40px;">
                <div class="ae-radar-page__paywall-card">
                    <div class="ae-radar-page__paywall-body" style="text-align: center; padding: 60px 40px;">
                        <h3 class="ae-radar-page__paywall-title">Descarga el Directorio Nacional Completo</h3>
                        <p class="ae-radar-page__paywall-subtitle">Obtén hoy mismo el listado de las <?= number_format($total_companies, 0, ',', '.') ?> empresas de <?= esc($cnae_label) ?>.</p>
                        <div class="ae-radar-page__paywall-actions" style="justify-content: center;">
                            <a href="<?= $buildCheckoutUrl ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--primary">Descargar Excel (9,90€)</a>
                            <a href="<?= site_url('precios-radar') ?>" class="ae-radar-page__paywall-btn ae-radar-page__paywall-btn--secondary">Suscribirse al Radar</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
