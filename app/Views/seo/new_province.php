<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr)) return 'Reciente';
    $dateStr = str_replace('/', '-', $dateStr);
    $timestamp = strtotime($dateStr);
    
    // Fallback if date is invalid, way in the future (typo in BORME like 2212), or too old
    if (!$timestamp || $timestamp > strtotime('+1 year') || $timestamp < strtotime('1900-01-01')) {
        return 'Reciente';
    }
    
    $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    $formatted = date($format, $timestamp);
    return str_replace($mesesEn, $mesesEs, $formatted);
};
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
    <style>
        .hero { padding: 90px 0 50px; text-align: center; }
        .hero .title { font-size: 3.2rem; font-weight: 900; line-height: 1.1; margin-bottom: 24px; letter-spacing: -0.02em; }
        .hero .subtitle { font-size: 1.25rem; color: #475569; max-width: 700px; margin: 0 auto 32px; line-height: 1.6; }
        .grad { background: linear-gradient(90deg, #133A82, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: none !important; padding-bottom: 0.1em; padding-right: 0.1em; }
        
        .value-prop-card { 
            position: relative;
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4); 
            border-radius: 24px; 
            padding: 28px; 
            text-align: left; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            overflow: hidden;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .value-prop-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 100%);
            z-index: -1;
        }
        .value-prop-card:hover { 
            transform: translateY(-8px) scale(1.02); 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .value-prop-card.v-today:hover { background: rgba(33, 82, 255, 0.03); }
        .value-prop-card.v-week:hover { background: rgba(18, 180, 138, 0.03); }
        .value-prop-card.v-month:hover { background: rgba(245, 158, 11, 0.03); }

        .v-icon-box { 
            width: 56px; 
            height: 56px; 
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            transition: all 0.3s ease;
        }
        .value-prop-card:hover .v-icon-box { transform: scale(1.1) rotate(5deg); }
        
        .v-label { font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .v-value { font-size: 1.85rem; font-weight: 950; line-height: 1; color: #0f172a; }

        .lead-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            min-height: 280px;
            box-sizing: border-box;
            transition: all 0.2s ease-in-out;
        }
        .lead-card:hover { border-color: var(--primary); box-shadow: 0 12px 24px -8px rgba(33, 82, 255, 0.15); }
        .lead-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 40px; align-items: stretch; }
        .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }

        @media (max-width: 768px) {
            /* Prevent horizontal scroll */
            body { overflow-x: hidden; }

            /* Hero */
            .hero .title { font-size: 2rem; }
            .hero { padding: 40px 0 24px; }
            .hero .subtitle { font-size: 1rem; }

            /* Stats cards — force 1 column */
            .stats-grid { grid-template-columns: 1fr !important; gap: 12px !important; }
            .stats-grid .value-prop-card { padding: 16px !important; }

            /* Lead grid */
            .lead-grid { grid-template-columns: 1fr; }

            /* Hero buttons — stack vertically */
            .hero-buttons {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
            }
            .hero-buttons a { text-align: center; justify-content: center; }

            /* Radar push block — stack vertically */
            .radar-push-block {
                flex-direction: column !important;
                gap: 16px !important;
                text-align: center !important;
                padding: 20px !important;
            }
            .radar-push-block ul { text-align: left !important; }

            /* Paywall box — reduce padding, allow scroll on tiny screens */
            .paywall-box {
                padding: 24px 16px !important;
                max-height: 90vh !important;
                overflow-y: auto !important;
                width: 92% !important;
            }

            /* Download CTA section — stack buttons */
            .cta-buttons {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }
            .cta-buttons a {
                text-align: center;
                justify-content: center !important;
                padding: 14px 16px !important;
            }

            /* Sector grid — 1 column */
            .sector-grid { grid-template-columns: 1fr !important; }

            /* Export CTA section — reduce padding */
            .export-cta { padding: 28px 20px !important; border-radius: 16px !important; }

            /* Strategic links — reduce padding, 1 col grid */
            .strategic-links { padding: 24px 16px !important; margin: 40px auto 32px !important; }
            .strategic-links-grid { grid-template-columns: 1fr !important; gap: 20px !important; }

            /* Email notify form — stack on very narrow viewports */
            .email-notify-form { flex-direction: column !important; }
            .email-notify-form input { min-width: 0 !important; width: 100% !important; }
            .email-notify-form button { width: 100% !important; }
        }

        @media (max-width: 480px) {
            .hero .title { font-size: 1.5rem; }
            .hero .subtitle { font-size: 0.95rem; }
            .v-value { font-size: 1.4rem !important; }
            .paywall-box { padding: 20px 14px !important; }
            .paywall-box h3 { font-size: 1.2rem !important; }
            .cta-buttons a { font-size: 0.9rem !important; padding: 12px 14px !important; }
            .hero-buttons a { padding: 13px 16px !important; font-size: 0.95rem !important; }
            .value-prop-card { padding: 14px !important; }
        }

    </style>
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header') ?>

    <main>
        <!-- PREMIUM HERO SECTION -->
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

                <!-- SEO CONTEXT BLOCK -->
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
                    <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province ?? '') . '&sector=' . urlencode($sector_label ?? '') . '&period=' . urlencode($period === 'general' ? '30days' : ($period ?? ''))) ?>" class="btn ghost" style="padding: 12px 24px; font-size: 0.95rem; border-radius: 10px; font-weight: 600; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b;">
                        Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · <strong>9€</strong>
                    </a>
                </div>

                <!-- RADAR PUSH BLOCK -->
                <div class="radar-push-block" style="background: linear-gradient(135deg, #f8fafc, #eff6ff); border: 2px solid #dbeafe; border-radius: 20px; padding: 28px; max-width: 850px; margin: 0 auto 48px; display: flex; align-items: center; justify-content: space-between; gap: 32px; text-align: left; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.05);">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 12px 0; font-size: 1.25rem; font-weight: 900; color: #0f172a; letter-spacing: -0.01em;">Accede al Radar de empresas nuevas</h4>
                        <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.95rem; color: #475569; display: grid; grid-template-columns: 1fr; gap: 8px;">
                            <li style="display: flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Detecta</strong> nuevas empresas cada día</li>
                            <li style="display: flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Filtra</strong> por sector, provincia y actividad</li>
                            <li style="display: flex; align-items: center; gap: 8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <strong>Exporta</strong> leads listos para prospección</li>
                        </ul>
                    </div>
                    <a href="<?= site_url('precios-radar') ?>" class="btn" style="padding: 16px 32px; font-size: 1.05rem; background: var(--primary); color: white; border-radius: 12px; font-weight: 800; white-space: nowrap; box-shadow: 0 4px 12px rgba(33,82,255,0.2);">Abrir Radar</a>
                </div>

                <nav aria-label="Breadcrumb" style="font-size: 0.85rem; color: #64748b; display: flex; gap: 8px; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <?php if (!empty($province)): ?>
                    <a href="<?= site_url('empresas-nuevas/' . url_title($province, '-', true)) ?>" style="color: inherit; text-decoration: none;">Empresas nuevas en <?= esc(ucfirst($province)) ?></a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <?php elseif (!empty($sector_label)): ?>
                    <a href="<?= site_url('empresas-cnae/buscar') ?>" style="color: inherit; text-decoration: none;">Sectores</a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <?php endif; ?>
                    <a href="<?= site_url('empresas-nuevas') ?>" style="color: inherit; text-decoration: none;">Directorio de Nuevas Entidades</a>
                </nav>
            </div>
        </section>

        <!-- STATS / VALUE PROPS -->
        <section class="container" style="margin-bottom: 60px; position: relative;">
            <!-- Subtle background decorative element -->
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(33, 82, 255, 0.05) 0%, transparent 70%); z-index: -1;"></div>

            <?php if (!empty($stats)): ?>
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                <div class="value-prop-card v-today">
                    <div class="v-icon-box" style="background: #eef2ff; color: #2152ff; box-shadow: 0 8px 16px rgba(33, 82, 255, 0.12);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <div class="v-label">Nuevas Hoy</div>
                        <div class="v-value"><?= number_format($stats['hoy'], 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="value-prop-card v-week">
                    <div class="v-icon-box" style="background: #e9fcf6; color: #12b48a; box-shadow: 0 8px 16px rgba(18, 180, 138, 0.12);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    </div>
                    <div>
                        <div class="v-label">Últimos 7 Días</div>
                        <div class="v-value"><?= number_format($stats['semana'], 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="value-prop-card v-month">
                    <div class="v-icon-box" style="background: #fff8eb; color: #f59e0b; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.12);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    </div>
                    <div>
                        <div class="v-label">Últimos 30 Días</div>
                        <div class="v-value"><?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            
            <!-- VOLUME INDICATOR -->
            <div style="margin-top: 24px; text-align: center; font-size: 0.95rem; color: #1e293b; font-weight: 700;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -2px; margin-right: 4px;"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                <?php 
                    if ($period === 'hoy') echo number_format($total_context_count ?? 0, 0, ',', '.') . ' nuevas empresas detectadas hoy en ' . esc($heading_highlight ?: 'España');
                    elseif ($period === 'semana') echo number_format($total_context_count ?? 0, 0, ',', '.') . ' nuevas empresas detectadas esta semana en ' . esc($heading_highlight ?: 'España');
                    else echo number_format($total_context_count ?? 0, 0, ',', '.') . ' nuevas empresas detectadas en ' . esc($heading_highlight ?: 'España') . ' en los últimos 30 días';
                ?>
                <?php if (!$province && !$sector_label): ?>
                    <div style="font-size: 1rem; color: var(--primary); margin-top: 8px;">Más de <?= number_format($stats['30days'] ?? 0, 0, ',', '.') ?> nuevas empresas detectadas en España en los últimos 30 días</div>
                <?php endif; ?>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 4px;">Detectadas automáticamente desde el BORME</div>
            </div>
            <?php endif; ?>
        </section>

        <!-- SECTORS / PROVINCES IMPACT GRID -->
        <?php if (!empty($top_sectors)): ?>
        <section class="container" style="margin-bottom: 80px;">
            <div style="margin-bottom: 32px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px;">
                <h2 style="font-size: 2rem; font-weight: 950; letter-spacing: -0.02em; margin: 0;">Impacto Geográfico y Sectorial</h2>
                <p style="color: #64748b; margin: 8px 0 0 0;">Los sectores con mayor actividad de empresas de nueva creación.</p>
            </div>
            
            <div class="sector-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach (($top_sectors ?? []) as $sect): 
                    if (empty($sect['cnae_label'])) continue;
                    $targetUrl = $sect['url'] ?? site_url("empresas-nuevas");
                ?>
                    <a href="<?= $targetUrl ?>" class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px; text-decoration: none; transition: all 0.2s; background: white; border: 1px solid #e2e8f0; border-radius: 16px;">
                        <div style="background: #f8fafc; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 800; border: 1px solid var(--border); font-size: 0.9rem;">
                            <?= substr(esc($sect['cnae']), 0, 2) ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text); margin: 0; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;"><?= esc($sect['cnae_label'] ?? 'Sector') ?></h3>
                            <div style="color: #64748b; font-size: 0.85rem; font-weight: 500;"><strong style="color: var(--primary);"><?= number_format($sect['total'], 0, ',', '.') ?></strong> empresas</div>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- LEAD GRID SECTION -->
        <section id="leads-b2b-recientes" class="container" style="margin-bottom: 80px;">
                <div>
                    <h2 style="font-size: 2rem; font-weight: 950; letter-spacing: -0.02em; margin: 0;">Leads B2B Recientes</h2>
                    <p style="color: #64748b; margin: 8px 0 0 0;">
                        <?php if ($sector_label && $province): ?>
                            Estas son algunas de las empresas nuevas detectadas en el sector de <?= mb_strtolower($sector_label) ?> en <?= ucfirst(mb_strtolower($province)) ?>.<br>
                            Para acceder al listado completo abre el Radar.
                        <?php elseif ($period === 'semana'): ?>
                            Estas son algunas de las empresas creadas esta semana.<br>
                            Para acceder al listado completo abre el Radar.
                        <?php elseif ($period === 'mes'): ?>
                            Estas son algunas de las empresas creadas este mes en España.<br>
                            Para acceder al listado completo abre el Radar.
                        <?php else: ?>
                            Estas son algunas de las últimas empresas detectadas.<br>
                            Para acceder al listado completo abre el Radar.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="badge--activa" style="background: #eef2ff; color: var(--primary); border: none; font-weight: 700; padding: 8px 16px; border-radius: 8px; font-size: 0.9rem;">
                    Últimas Altas: <?= date('d/m/Y') ?>
                </div>
            </div>

            <?php 
            $companies = $companies ?? []; // Fallback safety
            $paywall_level = $paywall_level ?? 'strong';
            
            // Define limits based on standardization document
            if ($paywall_level === 'none') {
                $freeCount = 100;
            } elseif ($paywall_level === 'soft') {
                $freeCount = 20; 
            } else {
                // strong level
                $freeCount = ($period === 'hoy') ? 3 : 5;
            }

            $freeLeads = array_slice($companies, 0, $freeCount);
            $premiumLeads = ($paywall_level === 'none') ? [] : array_slice($companies, $freeCount);
            ?>

            <div class="lead-grid">
                <?php foreach ($freeLeads as $index => $co): 
                    $coSlug = url_title($co['name'] ?? '', '-', true);
                    $coCif = $co['cif'] ?? '';
                    if (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/', $coCif)) {
                        $coUrl = site_url($coCif . ($coSlug ? ('-' . $coSlug) : ''));
                    } else {
                        $coUrl = site_url('empresa/' . ($co['id'] ?? 0) . ($coSlug ? ('-' . $coSlug) : ''));
                    }
                ?>
                    <div class="lead-card">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <span style="font-size: 0.75rem; font-weight: 800; background: #fef2f2; color: #ef4444; padding: 4px 8px; border-radius: 4px; border: 1px solid #fee2e2;">
                                    NUEVA ACTIVIDAD
                                </span>
                                <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">
                                    <?= $formatEsDate($co['fecha_constitucion'] ?? '', 'd/m/Y') ?>
                                </span>
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 8px 0; line-height: 1.3;">
                                <?= esc($co['name'] ?? 'Empresa') ?>
                            </h3>
                            
                            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 16px;">
                                <code style="font-size: 0.8rem; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0; font-weight: 700;">
                                    CIF: <?= esc(substr($coCif, 0, 3) . '*****') ?>
                                </code>
                                <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?= esc($co['registro_mercantil'] ?? $province ?? 'España') ?>
                                </span>
                            </div>

                             <p style="font-size: 0.9rem; color: #475569; line-height: 1.5; margin: 0 0 24px 0; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <strong>Sector CNAE:</strong> <?= esc($co['cnae_label'] ?? 'Actividad no especificada por el momento.') ?>
                            </p>
                        </div>

                        <a href="<?= esc($coUrl) ?>" style="display: block; width: 100%; text-align: center; border-radius: 8px; padding: 10px; font-size: 0.95rem; font-weight: 700; background: #f8fafc; color: var(--primary); border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'">
                            Ver empresa
                        </a>
                    </div>
                    
                    <?php if ($index === 4): ?>
                        <div style="grid-column: 1 / -1; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 16px; padding: 40px 32px; text-align: center; color: white; margin: 32px 0 60px 0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid rgba(255,255,255,0.1); position: relative; z-index: 50;">
                            <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 16px;">
                                ⚡ <?php 
                                    if ($sector_label && $province) echo 'Accede a todas las empresas nuevas de ' . mb_strtolower($sector_label) . ' en ' . ucfirst(mb_strtolower($province));
                                    elseif ($period === 'hoy') echo 'Accede a todas las empresas creadas hoy en España';
                                    elseif ($period === 'semana') echo 'Accede a todas las empresas creadas esta semana en España';
                                    elseif ($period === 'mes') echo 'Accede a todas las empresas creadas este mes en España';
                                    else echo 'Accede a todos los leads detectados en ' . esc($heading_highlight);
                                ?>
                            </h3>
                            <a href="<?= site_url('precios-radar') ?>" style="display: inline-block; background: var(--primary); color: white; font-weight: 800; padding: 14px 28px; border-radius: 8px; text-decoration: none; transition: all 0.2s;">
                                Abrir Radar completo
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($paywall_level === 'soft' && count($freeLeads) >= 50): ?>
                <!-- CTA Suave para Nivel 1 (SEO) -->
                <div style="margin: 40px 0; background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 16px; padding: 24px; text-align: center;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #1e1b4b; margin-bottom: 8px;">¿Buscas más empresas en <?= esc($province ?? $heading_highlight) ?>?</h3>
                    <p style="color: #4338ca; margin-bottom: 16px; font-weight: 500;">Accede al listado completo y detecta todas las nuevas aperturas en tiempo real con Radar.</p>
                    <a href="<?= site_url('precios-radar') ?>" style="display: inline-block; background: var(--primary); color: white; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none;">
                        Abrir Radar Premium
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($premiumLeads)): 
                // Aumentamos a 9 dummies para dar más altura al fondo y que el paywall centrado no suba tanto
                $dummyLeads = array_slice($premiumLeads, 0, 9);
            ?>
            <div style="position: relative; margin-top: 150px !important; z-index: 10;">
                <!-- Blurred Premium Background -->
                <div style="filter: blur(8px); opacity: 0.5; pointer-events: none; user-select: none;" aria-hidden="true">
                    <div class="lead-grid">
                        <?php foreach ($dummyLeads as $co): ?>
                            <div class="lead-card" style="min-height: 240px;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                        <span style="font-size: 0.75rem; font-weight: 800; background: #e2e8f0; color: #64748b; padding: 4px 8px; border-radius: 4px;">RESERVED</span>
                                        <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 600;">--/--/----</span>
                                    </div>
                                    <div style="background: #cbd5e1; height: 24px; width: 80%; border-radius: 4px; margin-bottom: 16px;"></div>
                                    <div style="background: #e2e8f0; height: 16px; width: 60%; border-radius: 4px; margin-bottom: 8px;"></div>
                                    <div style="background: #f1f5f9; height: 40px; border-radius: 4px; margin-bottom: 16px;"></div>
                                </div>
                                <div style="background: #e2e8f0; height: 42px; width: 100%; border-radius: 8px;"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Premium Paywall Overlay -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; z-index: 10;">
                    <div class="paywall-box" style="background: white; padding: 32px 24px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1), 0 0 0 1px rgba(33, 82, 255, 0.1); text-align: left; max-width: 580px; width: 92%; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #133A82, #2152ff, #12b48a);"></div>
                        
                        <div style="text-align: center; margin-bottom: 24px;">
                            <div style="width: 56px; height: 56px; background: #eef2ff; color: var(--primary); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; border: 1px solid #e0e7ff;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                             <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 12px; color: #0f172a; letter-spacing: -0.01em;">Acceso Restringido</h3>
                            <p style="color: #475569; margin-bottom: 16px; line-height: 1.6; font-size: 1.05rem;">
                                Accede al listado completo de nuevas empresas en <?= esc($province ?? 'España') ?> y detecta oportunidades comerciales antes que tu competencia. 
                                <?php if ($sector_label): ?>
                                    <br><span style="color: #64748b; font-size: 0.9rem; font-weight: 500;">Las empresas <?= mb_strtolower($sector_label) ?> recién creadas suelen contratar proveedores en sus primeros meses de actividad.</span>
                                <?php endif; ?>
                            </p>
                            <div style="font-size: 0.95rem; color: var(--primary); font-weight: 800; margin-bottom: 16px; text-transform: uppercase;">Más de <?= number_format($total_context_count ?? 0, 0, ',', '.') ?> nuevas empresas detectadas en los últimos 30 días</div>
                            <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px; font-size: 0.9rem; color: #92400e; font-weight: 600; text-align: center;">
                                Consigue ventaja competitiva y contacta con los fundadores hoy mismo.
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px;">
                            <a href="<?= site_url('precios-radar') ?>" style="background: var(--primary); color: white; padding: 16px 20px; border-radius: 12px; font-size: 1.05rem; font-weight: 800; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 8px 16px -4px rgba(33,82,255,0.3); text-decoration: none; transition: transform 0.2s;">
                                <span>Activar Suscripción Radar</span>
                                <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 8px; font-size: 0.95rem;">79€/mes</span>
                            </a>
                              <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province ?? '') . '&sector=' . urlencode($sector_label ?? '') . '&period=' . urlencode($period === 'general' ? '30days' : ($period ?? ''))) ?>" style="background: white; color: #0f172a; border: 1px solid #cbd5e1; padding: 14px 20px; border-radius: 12px; font-size: 0.95rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <span style="font-size: 0.9rem;"><?php 
                                    if ($period === 'hoy') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Hoy)';
                                    elseif ($period === 'semana') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Semana)';
                                    elseif ($period === 'mes') echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas (Mes)';
                                    else echo 'Descargar ' . number_format($total_context_count ?? 0, 0, ',', '.') . ' empresas';
                                ?></span>
                                <span style="color: #475569; font-size: 0.9rem; font-weight: 850; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">9€</span>
                            </a>
                        </div>
                        
                        <div style="padding-top: 24px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="font-size: 0.9rem; color: #475569; margin-bottom: 12px; font-weight: 700;">¿Prefieres recibir avisos?</p>
                            <form action="#" method="POST" class="email-notify-form" style="display: flex; gap: 8px; text-align: left; flex-wrap: wrap;" onsubmit="alert('Lead capturado.'); return false;">
                                <input type="email" name="email" placeholder="Recibe nuevas empresas similares por email" required style="flex: 1; min-width: 200px; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#cbd5e1'">
                                <button type="submit" style="background: #0f172a; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: background 0.2s; flex-shrink: 0;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='#0f172a'">Avisarme</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>

            <!-- RELATED SECTORS HUB (MOVED UP) -->
            <?php if (!empty($related_sectors)): ?>
            <div style="margin: 20px auto 40px; padding-top: 48px; border-top: 2px solid #f1f5f9; max-width: 1000px;">
                <h3 style="font-size: 1.25rem; font-weight: 850; margin-bottom: 24px; color: #0f172a; text-align: center; letter-spacing: -0.01em;">Sectores con alta actividad en <?= esc($heading_highlight) ?></h3>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                    <?php foreach ($related_sectors as $rs): ?>
                        <a href="<?= esc($rs['url']) ?>" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 100px; padding: 8px 18px; font-size: 0.85rem; color: #475569; font-weight: 600; text-decoration: none; transition: all 0.2s; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; box-sizing: border-box;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'; this.style.background='white'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'; this.style.background='#f8fafc'; this.style.boxShadow='none'">
                            <?= esc($rs['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- EXPORT CTA SECTION (SECONDARY) -->
            <div class="export-cta" style="margin-top: 40px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 24px; padding: 48px 40px; text-align: center; color: #0f172a;" class="container">
                <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 12px; letter-spacing: -0.01em;">¿Necesitas el listado en formato Excel?</h3>
                <p style="font-size: 1rem; color: #64748b; max-width: 600px; margin: 0 auto 24px auto; line-height: 1.6;">
                    Obtén el directorio completo de <?= esc($heading_highlight) ?> en formato .XLSX de forma individual.
                </p>
                <div class="cta-buttons" style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 24px;">
                    <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province ?? '') . '&sector=' . urlencode($sector_label ?? '') . '&period=' . urlencode($period === 'general' ? '30days' : ($period ?? ''))) ?>" style="background: white; color: #0f172a; border: 1px solid #cbd5e1; padding: 14px 32px; border-radius: 12px; font-size: 1rem; font-weight: 700; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                        Descargar listado (<?= number_format($total_context_count ?? 0, 0, ',', '.') ?> empresas) · 9€
                    </a>
                </div>
                
                <div class="trust-badges" style="font-size: 0.85rem; color: #64748b; display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 12px 16px;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Excel / CSV
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Contactos y Cargos
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Insights Financieros
                    </span>
                </div>
            </div>

            <!-- STRATEGIC INTERNAL LINKS -->
            <div class="strategic-links" style="margin: 80px auto 60px; padding: 40px; background: #fafafa; border-radius: 20px; max-width: 1000px;" class="container">
                <h3 style="font-size: 1.1rem; font-weight: 900; color: #0f172a; margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Enlaces Estratégicos</h3>
                <div class="strategic-links-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Nuevas empresas por provincia</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-nuevas/madrid') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas nuevas en Madrid</a></li>
                            <li><a href="<?= site_url('empresas-nuevas/barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas nuevas en Barcelona</a></li>
                            <li><a href="<?= site_url('empresas-nuevas/valencia') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas nuevas en Valencia</a></li>
                            <li><a href="<?= site_url('empresas-nuevas/sevilla') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas nuevas en Sevilla</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Nuevas hoy</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-nuevas-hoy/madrid') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Madrid</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-hoy/barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Barcelona</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-hoy/valencia') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Valencia</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Esta semana</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-nuevas-semana/madrid') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">En Madrid</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-semana/barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">En Barcelona</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Este mes</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-nuevas-mes/madrid') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">En Madrid</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-mes/barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">En Barcelona</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Sectores Top en BCN</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-programacion-informatica-en-barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Programación en Barcelona</a></li>
                            <li><a href="<?= site_url('empresas-marketing-en-barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Marketing en Barcelona</a></li>
                            <li><a href="<?= site_url('empresas-consultoria-tecnologica-en-barcelona') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Consultoría en Barcelona</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Tecnología Nacional</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-nuevas-sector/6201-programacion-informatica') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas de Tecnología</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-sector/6202-consultoria-informatica') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas de Consultoría</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Nuevas empresas por sector</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; line-height: 2;">
                            <li><a href="<?= site_url('empresas-nuevas-sector/6201-programacion-informatica') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas de tecnología</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-sector/4110-construccion') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas de construcción</a></li>
                            <li><a href="<?= site_url('empresas-nuevas-sector/7311-publicidad-y-marketing') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">Empresas de marketing</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- SEMANTIC SEO BLOCK -->
            <div style="margin: 40px auto 80px; max-width: 900px; padding: 0 20px; color: #475569; line-height: 1.8; font-size: 0.95rem;">
                <h3 style="font-size: 1.25rem; font-weight: 850; color: #0f172a; margin-bottom: 16px;">Sobre las nuevas empresas <?= $sector_label ? "de " . mb_strtolower($sector_label) : "" ?> en <?= esc(ucfirst(mb_strtolower($province ?? 'España', 'UTF-8'))) ?></h3>
                <p style="margin-bottom: 16px;">
                    <?= esc($heading_highlight) ?> es uno de los principales hubs <?= $sector_label ? "de " . esc($sector_label) : "empresariales" ?> de España. Cada mes se registran cientos de <a href="<?= site_url('empresas/' . url_title($province ?? '', '-', true)) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">empresas en <?= esc($heading_highlight) ?></a>, incluyendo más de 100 <a href="<?= site_url('empresas-nuevas-hoy') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">empresas creadas hoy</a>, lo que genera un ecosistema dinámico de oportunidades B2B.
                </p>
                <p style="margin-bottom: 16px;">
                    Especialmente en sectores como la <a href="<?= site_url('empresas-cnae/programacion-informatica') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">programación informática</a> y los servicios digitales, estas <a href="<?= site_url('empresas-nuevas') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">empresas nuevas en España</a> suelen contratar servicios de marketing, asesoría y proveedores tecnológicos durante sus primeros meses para establecer sus bases operativas.
                    <?php if ($sector_label): ?>
                        Descubre más <a href="<?= site_url('empresas-cnae/' . url_title($sector_label, '-', true)) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">empresas de <?= mb_strtolower($sector_label) ?> en España</a>.
                    <?php endif; ?>
                </p>
                <p style="font-weight: 600; color: #1e293b;">
                    Con el Radar puedes detectar estas nuevas empresas antes que tu competencia y posicionarte como su proveedor de confianza desde el primer día. <a href="<?= site_url('empresas-nuevas-hoy') ?>" style="color: var(--primary); text-decoration: none;">Empresas creadas hoy</a>, <a href="<?= site_url('empresas-nuevas-semana') ?>" style="color: var(--primary); text-decoration: none;">empresas creadas esta semana</a> o <a href="<?= site_url('empresas') ?>" style="color: var(--primary); text-decoration: none;">ver todas las empresas en España</a>.
                </p>
            </div>

    <?= view('partials/footer') ?>
</body>
</html>
