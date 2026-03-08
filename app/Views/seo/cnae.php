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
    
    $formatted = date('d M Y', $timestamp);
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
        
        .value-prop-card { background: #ffffff; border: 1px solid var(--border); border-radius: 20px; padding: 24px; text-align: left; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .value-prop-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #cbd5e1; }
        
        .lead-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            transition: all 0.2s ease-in-out;
        }
        .lead-card:hover { border-color: var(--primary); box-shadow: 0 12px 24px -8px rgba(33, 82, 255, 0.15); }
        .lead-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }

        @media (max-width: 768px) {
            .hero .title { font-size: 2.4rem; }
            .hero { padding: 50px 0 30px; }
            .lead-grid { grid-template-columns: 1fr; }
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
                   CNAE <?= esc($cnae_code) ?>
                </span>
                
                <h1 class="title">
                    Empresas de <span class="grad"><?= esc($cnae_label) ?></span><br>
                    <span style="color: #0f172a; font-size: 0.6em; font-weight: 800;">Análisis B2B y Distribución Nacional</span>
                </h1>

                <p class="subtitle">
                   Monitorizamos las <strong><?= number_format($total_companies, 0, ',', '.') ?> sociedades activas</strong> bajo el CNAE <?= esc($cnae_code) ?>. Identifica los principales hubs provinciales y accede a leads enriquecidos listos para prospección comercial.
                </p>

                <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-bottom: 40px;">
                    <a href="<?= site_url('billing/single_checkout?cnae=' . urlencode($cnae_code)) ?>" class="btn" style="background: var(--primary); color: white; padding: 16px 32px; font-size: 1.1rem; border-radius: 12px; font-weight: 700; box-shadow: 0 8px 16px rgba(33,82,255,0.2);">
                        Descargar Listado Nacional
                    </a>
                    <a href="#muestras-gratuitas" class="btn ghost" style="padding: 16px 32px; font-size: 1.1rem; border-radius: 12px; font-weight: 700;">Ver leads gratuitos ↓</a>
                </div>

                <nav aria-label="Breadcrumb" style="font-size: 0.85rem; color: #64748b; display: flex; gap: 8px; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <a href="<?= site_url('empresas-cnae/buscar') ?>" style="color: inherit; text-decoration: none;">Sectores</a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <span style="color: var(--primary); font-weight: 600;"><?= esc($cnae_label) ?></span>
                </nav>
            </div>
        </section>

        <!-- STATS / VALUE PROPS -->
        <section class="container" style="margin-bottom: 60px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                <div class="value-prop-card" style="box-shadow: none; border-color: #e2e8f0; display: flex; gap: 16px; align-items: center;">
                    <div class="icon-box" style="margin: 0; background: #eef2ff; color: var(--primary);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Total Nacional</div>
                        <div style="font-size: 1.5rem; font-weight: 900; line-height: 1;"><?= number_format($total_companies, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="value-prop-card" style="box-shadow: none; border-color: #e2e8f0; display: flex; gap: 16px; align-items: center;">
                    <div class="icon-box" style="margin: 0; background: #e9fcf6; color: #12b48a;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Provincias Activas</div>
                        <div style="font-size: 1.5rem; font-weight: 900; line-height: 1;"><?= is_array($top_provinces) ? count($top_provinces) : 0 ?></div>
                    </div>
                </div>
                <div class="value-prop-card" style="box-shadow: none; border-color: #e2e8f0; display: flex; gap: 16px; align-items: center;">
                    <div class="icon-box" style="margin: 0; background: #fff8eb; color: #f59e0b;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Mantenimiento</div>
                        <div style="font-size: 1.15rem; font-weight: 900; color: #d97706; line-height: 1.2;">ACTUALIZADO 24H</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROVINCES IMPACT GRID -->
        <section class="container" style="margin-bottom: 80px;">
            <div style="margin-bottom: 32px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px;">
                <h2 style="font-size: 2rem; font-weight: 950; letter-spacing: -0.02em; margin: 0;">Impacto Geográfico</h2>
                <p style="color: #64748b; margin: 8px 0 0 0;">Provincias líderes con mayor volumen de empresas en este sector.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach (($top_provinces ?? []) as $prov): 
                    if (empty($prov['provincia'])) continue;
                    $sectorSlug = url_title($cnae_label, '-', true);
                    $provinceSlug = url_title($prov['provincia'], '-', true);
                ?>
                    <a href="<?= site_url("empresas-{$sectorSlug}-en-{$provinceSlug}") ?>" class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px; text-decoration: none; transition: all 0.2s; background: white; border: 1px solid #e2e8f0; border-radius: 16px;">
                        <div style="background: #f8fafc; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 800; border: 1px solid var(--border); font-size: 0.9rem;">
                            <?= substr(esc($prov['provincia']), 0, 2) ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text); margin: 0;"><?= esc($prov['provincia']) ?></h3>
                            <div style="color: #64748b; font-size: 0.85rem; font-weight: 500;"><strong style="color: var(--primary);"><?= number_format($prov['total'], 0, ',', '.') ?></strong> empresas</div>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- LEAD GRID SECTION -->
        <section id="muestras-gratuitas" class="container" style="margin-bottom: 80px;">
             <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 style="font-size: 2rem; font-weight: 950; letter-spacing: -0.02em; margin: 0;">Leads y Últimas Constituciones</h2>
                    <p style="color: #64748b; margin: 8px 0 0 0;">Muestra gratuita de sociedades B2B en el sector de <?= esc($cnae_label) ?>.</p>
                </div>
                <div class="badge--activa" style="background: #eef2ff; color: var(--primary); border: none; font-weight: 700; padding: 8px 16px; border-radius: 8px; font-size: 0.9rem;">
                    Últimas Altas: <?= date('d/m/Y') ?>
                </div>
            </div>

            <?php
            $companies = $companies ?? []; // Fallback safety
            $paywall_level = $paywall_level ?? 'none';
            $freeCount = ($paywall_level === 'none' ? 100 : 50);
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
                                    SECTOR B2B
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
                                    <?= esc($co['registro_mercantil'] ?? 'España') ?>
                                </span>
                            </div>

                            <p style="font-size: 0.9rem; color: #475569; line-height: 1.5; margin: 0 0 24px 0; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <strong>Actividad Principal:</strong> <?= esc($cnae_label) ?> (CNAE <?= esc($co['cnae_code'] ?? $cnae_code) ?>)
                            </p>
                        </div>

                        <a href="<?= esc($coUrl) ?>" style="display: block; width: 100%; text-align: center; border-radius: 8px; padding: 10px; font-size: 0.95rem; font-weight: 700; background: #f8fafc; color: var(--primary); border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'">
                            Ver Datos Comerciales
                        </a>
                    </div>
                    
                    <?php if ($index === 4): ?>
                        <div style="grid-column: 1 / -1; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 16px; padding: 32px; text-align: center; color: white; margin: 16px 0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid rgba(255,255,255,0.1);">
                            <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 16px;">
                                ⚡ Accede a las <?= esc(number_format($total_companies, 0, ',', '.')) ?> empresas nacionales de <?= esc($cnae_label) ?>
                            </h3>
                            <a href="<?= site_url('register?redirect=radar') ?>" style="display: inline-block; background: var(--primary); color: white; font-weight: 800; padding: 14px 28px; border-radius: 8px; text-decoration: none; transition: all 0.2s;">
                                Desbloquear Radar Premium
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($paywall_level === 'soft' && count($freeLeads) >= 50): ?>
                <!-- CTA Suave para Nivel 1 (SEO) -->
                <div style="margin: 40px 0; background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 16px; padding: 24px; text-align: center;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #1e1b4b; margin-bottom: 8px;">¿Buscas más empresas de <?= esc($cnae_label) ?>?</h3>
                    <p style="color: #4338ca; margin-bottom: 16px; font-weight: 500;">Accede al listado completo y detecta todas las nuevas aperturas en tiempo real con Radar.</p>
                    <a href="<?= site_url('register?redirect=radar') ?>" style="display: inline-block; background: var(--primary); color: white; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none;">
                        Abrir Radar Premium
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($premiumLeads)): 
                $dummyLeads = array_slice($premiumLeads, 0, 6);
            ?>
            <div style="position: relative; margin-top: 24px;">
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
                    <div style="background: white; padding: 48px 40px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1), 0 0 0 1px rgba(33, 82, 255, 0.1); text-align: left; max-width: 480px; width: 90%; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #133A82, #2152ff, #12b48a);"></div>
                        
                        <div style="text-align: center; margin-bottom: 24px;">
                            <div style="width: 56px; height: 56px; background: #eef2ff; color: var(--primary); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; border: 1px solid #e0e7ff;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 12px; color: #0f172a; letter-spacing: -0.01em;">Acceso Restringido</h3>
                            <p style="color: #475569; margin-bottom: 0; line-height: 1.6; font-size: 1.05rem;">
                                Estás visualizando los resultados gratuitos. El Radar Premium te otorga acceso total nacionales de <?= esc($cnae_label) ?> y alertas en tiempo real.
                            </p>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px;">
                            <a href="<?= site_url('register?redirect=precios-radar') ?>" style="background: var(--primary); color: white; padding: 16px 20px; border-radius: 12px; font-size: 1.05rem; font-weight: 800; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 8px 16px -4px rgba(33,82,255,0.3); text-decoration: none; transition: transform 0.2s;">
                                <span>Contratar Radar Premium</span>
                                <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 8px; font-size: 0.95rem;">99€/mes</span>
                            </a>
                            <a href="<?= site_url('billing/single_checkout?cnae=' . urlencode($cnae_code)) ?>" style="background: white; color: #0f172a; border: 1px solid #cbd5e1; padding: 14px 20px; border-radius: 12px; font-size: 1.05rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <span>Descargar Excel Completo</span>
                                <span style="color: #475569; font-size: 0.95rem;">9€</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- EXPORT CTA SECTION -->
            <div style="margin-top: 60px; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 24px; padding: 60px 40px; text-align: center; color: white; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                <div style="display: inline-flex; justify-content: center; align-items: center; width: 64px; height: 64px; background: rgba(255,255,255,0.1); color: #cbd5e1; border-radius: 50%; margin-bottom: 24px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </div>
                <h3 style="font-size: 2rem; font-weight: 950; margin-bottom: 16px;">Base de Datos de <?= esc($cnae_label) ?></h3>
                <p style="font-size: 1.1rem; color: #94a3b8; max-width: 650px; margin: 0 auto 32px auto; line-height: 1.6;">
                    Consigue hoy mismo el listado íntegro de las <?= number_format($total_companies, 0, ',', '.') ?> empresas pertenecientes al CNAE <?= esc($cnae_code) ?>. Directorio consolidado a nivel de toda España.
                </p>
                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= site_url('billing/single_checkout?cnae=' . urlencode($cnae_code)) ?>" style="background: var(--primary); color: white; padding: 16px 40px; border-radius: 12px; font-size: 1.1rem; font-weight: 800; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);">
                        Descargar Excel Completo (9€)
                    </a>
                </div>
            </div>

            <!-- INTERNAL LINKING HUB -->
             <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid #e2e8f0;">
                <h3 style="font-size: 1.5rem; margin-bottom: 2rem; color: #0f172a; font-weight: 900; letter-spacing: -0.01em; text-align: center;">Vigilancia Competitiva</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; justify-content: center;">
                    <a href="<?= site_url('empresas-nuevas') ?>" class="value-prop-card" style="padding: 20px; text-decoration: none; border-left: 4px solid #f59e0b;">
                        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #d97706; text-transform: uppercase; margin-bottom: 4px;">Radar de Nuevas Altas</span>
                        <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Últimas empresas registradas en España</span>
                    </a>
                    <a href="<?= site_url('empresas-nuevas-sector/' . esc($cnae_code) . '-' . url_title($cnae_label, '-', true)) ?>" class="value-prop-card" style="padding: 20px; text-decoration: none; border-left: 4px solid #ef4444;">
                        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 4px;">Radar Sectorial Específico</span>
                        <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Últimas sociedades de <?= esc($cnae_label) ?></span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>

</html>
