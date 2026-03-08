<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr)) return 'Reciente';
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
        .grad { background: linear-gradient(90deg, #133A82, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent; }
        
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
            body { overflow-x: hidden; }
            .hero .title { font-size: 2rem; }
            .hero { padding: 40px 0 24px; }
            .hero .subtitle { font-size: 1rem; }
            .lead-grid { grid-template-columns: 1fr; }

            /* Hero buttons — stack vertically */
            .hero-buttons {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
            }
            .hero-buttons a { text-align: center; justify-content: center !important; }

            /* Distribution block — stack vertically */
            .distribution-block {
                flex-direction: column !important;
                gap: 16px !important;
            }
            .distribution-block > div:first-child { min-width: 0 !important; }
            .distribution-stats { justify-content: center !important; }

            /* Value prop cards grid */
            .value-prop-grid { grid-template-columns: 1fr !important; }

            /* Paywall box */
            .paywall-box {
                padding: 24px 16px !important;
                max-height: 90vh !important;
                overflow-y: auto !important;
                width: 92% !important;
                max-width: 92% !important;
            }

            /* Email form — wrap on narrow */
            .email-notify-form {
                flex-direction: column !important;
            }
            .email-notify-form input { min-width: 0 !important; width: 100% !important; }
            .email-notify-form button { width: 100% !important; padding: 12px !important; }

            /* Export CTA */
            .export-cta-dark { padding: 32px 20px !important; border-radius: 16px !important; }
            .export-cta-dark h3 { font-size: 1.4rem !important; }
            .export-cta-btns {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            .export-cta-btns a { text-align: center; }

            /* Internal linking grids */
            .internal-links-grid { grid-template-columns: 1fr !important; gap: 12px !important; }
        }

        @media (max-width: 480px) {
            .hero .title { font-size: 1.55rem; }
            .hero .subtitle { font-size: 0.95rem; }
            .paywall-box { padding: 20px 14px !important; }
            .paywall-box h3 { font-size: 1.2rem !important; }
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
                    Empresas de <span class="grad"><?= esc($sector_label) ?></span><br>
                    <span style="color: #0f172a;">en <?= esc($province) ?></span><br>
                    <span style="color: #64748b; font-size: 0.5em; font-weight: 800; vertical-align: middle;">(Directorio completo)</span>
                </h1>

                <p class="subtitle">
                   Encuentra las principales empresas de <strong><?= esc($sector_label) ?></strong> en <?= esc($province) ?> y accede a datos comerciales listos para prospección B2B. Directorio actualizado con más de <?= number_format($total, 0, ',', '.') ?> entidades.
                </p>

                <div class="hero-buttons" style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-bottom: 40px;">
                    <a href="<?= site_url('register?redirect=radar') ?>" class="btn" style="background: var(--primary); color: white; padding: 16px 32px; font-size: 1.1rem; border-radius: 12px; font-weight: 700; box-shadow: 0 8px 16px rgba(33,82,255,0.2);">
                        Abrir Radar
                    </a>
                    <a href="#muestras-gratuitas" class="btn ghost" style="padding: 16px 32px; font-size: 1.1rem; border-radius: 12px; font-weight: 700; background: white; border: 1px solid #cbd5e1; color: #475569;">
                        Ver muestra gratuita ↓
                    </a>
                    <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province) . '&cnae=' . urlencode($sector_code)) ?>" class="btn ghost" style="padding: 12px 24px; font-size: 0.95rem; border-radius: 10px; font-weight: 600; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b;">
                        Descargar listado (<?= number_format($total, 0, ',', '.') ?> empresas) · <strong>9€</strong>
                    </a>
                </div>

                <nav aria-label="Breadcrumb" style="font-size: 0.85rem; color: #64748b; display: flex; gap: 8px; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <a href="<?= site_url('empresas/' . url_title($province, '-', true)) ?>" style="color: inherit; text-decoration: none;"><?= esc($province) ?></a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <span style="color: var(--primary); font-weight: 600;"><?= esc($sector_label) ?></span>
                </nav>
            </div>
        </section>

        <!-- VALUE PROPOSITION CARDS -->
        <section class="container" style="margin-bottom: 60px;">
            <div class="value-prop-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                <div class="value-prop-card">
                    <div class="icon-box" style="background: #eef2ff; color: var(--primary);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 12px;">Pre-Venta Efectiva</h3>
                    <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5; margin: 0;">Filtra empresas por sector y provincia para optimizar tus campañas de outbound marketing y llamadas en frío.</p>
                </div>
                <div class="value-prop-card">
                    <div class="icon-box" style="background: #e9fcf6; color: #12b48a;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 12px;">Datos Directos del BORME</h3>
                    <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5; margin: 0;">Información oficial procesada diariamente. Accede a los últimos registros mercantiles sin intermediarios.</p>
                </div>
                <div class="value-prop-card">
                    <div class="icon-box" style="background: #fff8eb; color: #f59e0b;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 12px;">Enriquecido con CNAE</h3>
                    <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5; margin: 0;">Clasificación por sectores para asegurar que tus leads corresponden exactamente a tu mercado objetivo.</p>
                </div>
            </div>
        </section>

        <!-- DISTRIBUTION BLOCK (NEW) -->
        <section class="container" style="margin-bottom: 60px;">
            <div class="distribution-block" style="background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; display: flex; align-items: center; justify-content: space-between; gap: 32px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 0;">
                    <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin-bottom: 12px;">Distribución de <?= esc($sector_label) ?> en <?= esc($province) ?></h2>
                    <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 0;">
                        El sector de <?= esc($sector_label) ?> mantiene una presencia estratégica en <strong><?= esc($province) ?></strong>, con concentraciones clave en el hub metropolitano y zonas de actividad comercial como Gandía, Torrente o Sagunto. Detecta agencias locales y competidores de forma segmentada.
                    </p>
                </div>
                <div class="distribution-stats" style="display: flex; gap: 24px;">
                    <div style="text-align: center;">
                        <span style="display: block; font-size: 1.75rem; font-weight: 900; color: #0f172a;"><?= number_format($total, 0, ',', '.') ?></span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Empresas Activas</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- LEAD GRID SECTION (Matching new_province style) -->
        <section id="muestras-gratuitas" class="container" style="margin-bottom: 80px;">
             <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 style="font-size: 2rem; font-weight: 950; letter-spacing: -0.02em; margin: 0;">Muestra de Leads Gratuitos</h2>
                    <p style="color: #64748b; margin: 8px 0 0 0;">Visualiza los primeros resultados de entre las <?= number_format($total, 0, ',', '.') ?> empresas encontradas.</p>
                </div>
                <div class="badge--activa" style="background: #eef2ff; color: var(--primary); border: none; font-weight: 700; padding: 8px 16px; border-radius: 8px; font-size: 0.9rem;">
                    Últimas Altas: <?= date('d/m/Y') ?>
                </div>
            </div>

            <?php 
            $companies = $companies ?? []; // Fallback safety
            $paywall_level = $paywall_level ?? 'soft';
            $freeCount = 20; // Sector + Provincia -> paywall suave (20 items)
            $freeLeads = array_slice($companies, 0, $freeCount);
            $premiumLeads = array_slice($companies, $freeCount);
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
                                    SECTOR: <?= esc($sector_code) ?>
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
                                    <?= esc($co['municipality'] ?? $province) ?>
                                </span>
                            </div>

                            <p style="font-size: 0.9rem; color: #475569; line-height: 1.5; margin: 0 0 24px 0; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <strong>Actividad Principal:</strong> <?= esc($sector_label) ?> (CNAE <?= esc($sector_code) ?>)
                            </p>
                        </div>

                        <a href="<?= esc($coUrl) ?>" style="display: block; width: 100%; text-align: center; border-radius: 8px; padding: 10px; font-size: 0.95rem; font-weight: 700; background: #f8fafc; color: var(--primary); border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'">
                            Ver empresa
                        </a>
                    </div>
                    
                    <?php if ($index === 4): ?>
                        <div style="grid-column: 1 / -1; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 16px; padding: 32px; text-align: center; color: white; margin: 16px 0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid rgba(255,255,255,0.1);">
                            <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 16px;">
                                ⚡ Accede a todas las empresas de <?= esc($sector_label) ?> en <?= esc($province) ?>
                            </h3>
                            <a href="<?= site_url('register?redirect=radar') ?>" style="display: inline-block; background: var(--primary); color: white; font-weight: 800; padding: 14px 28px; border-radius: 8px; text-decoration: none; transition: all 0.2s;">
                                Desbloquear Radar Premium
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

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
                    <div class="paywall-box" style="background: white; padding: 48px 40px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1), 0 0 0 1px rgba(33, 82, 255, 0.1); text-align: left; max-width: 600px; width: 90%; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #133A82, #2152ff, #12b48a);"></div>
                        
                        <div style="text-align: center; margin-bottom: 24px;">
                            <div style="width: 56px; height: 56px; background: #eef2ff; color: var(--primary); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; border: 1px solid #e0e7ff;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                             <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 12px; color: #0f172a; letter-spacing: -0.01em;">Acceso Restringido</h3>
                            <p style="color: #475569; margin-bottom: 0; line-height: 1.6; font-size: 1.05rem;">
                                Accede al listado completo de empresas de <?= esc($sector_label) ?> en <?= esc($province) ?> y detecta nuevas oportunidades comerciales.
                            </p>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px;">
                            <a href="<?= site_url('register?redirect=precios-radar') ?>" style="background: var(--primary); color: white; padding: 16px 20px; border-radius: 12px; font-size: 1.05rem; font-weight: 800; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 8px 16px -4px rgba(33,82,255,0.3); text-decoration: none; transition: transform 0.2s;">
                                <span>Contratar Radar</span>
                                <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 8px; font-size: 0.95rem;">99€/mes</span>
                            </a>
                            <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province) . '&cnae=' . urlencode($sector_code)) ?>" style="background: white; color: #0f172a; border: 1px solid #cbd5e1; padding: 14px 20px; border-radius: 12px; font-size: 1.05rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <span>Descargar listado (<?= number_format($total, 0, ',', '.') ?> empresas) en Excel</span>
                                <span style="color: #475569; font-size: 0.95rem;">9€</span>
                            </a>
                        </div>
                        
                        <div style="padding-top: 24px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 12px; font-weight: 600;">¿Quieres seguir viendo antes de decidir?</p>
                            <form action="#" method="POST" class="email-notify-form" style="display: flex; gap: 8px; text-align: left; flex-wrap: wrap;" onsubmit="alert('Lead capturado.'); return false;">
                                <input type="email" name="email" placeholder="Tu email profesional" required style="flex: 1; min-width: 200px; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none;">
                                <button type="submit" style="background: #0f172a; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; white-space: nowrap; flex-shrink: 0;">Ver 10 más</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- EXPORT CTA SECTION -->
            <div class="export-cta-dark" style="margin-top: 60px; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 24px; padding: 60px 40px; text-align: center; color: white; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                <div style="display: inline-flex; justify-content: center; align-items: center; width: 64px; height: 64px; background: rgba(255,255,255,0.1); color: #cbd5e1; border-radius: 50%; margin-bottom: 24px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </div>
                <h3 style="font-size: 2rem; font-weight: 950; margin-bottom: 16px;">Base de Datos de <?= esc($sector_label) ?></h3>
                <p style="font-size: 1.1rem; color: #94a3b8; max-width: 650px; margin: 0 auto 32px auto; line-height: 1.6;">
                    Consigue hoy mismo el listado completo de las <?= number_format($total, 0, ',', '.') ?> empresas ubicadas en <?= esc($province) ?>. Datos enriquecidos para prospección comercial.
                </p>
                <div class="export-cta-btns" style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= site_url('billing/single_checkout?provincia=' . urlencode($province) . '&cnae=' . urlencode($sector_code)) ?>" style="background: var(--primary); color: white; padding: 16px 40px; border-radius: 12px; font-size: 1.1rem; font-weight: 800; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);">
                        Comprar directorio completo en Excel (9€)
                    </a>
                </div>
            </div>

            <!-- INTERNAL LINKING HUB (Premium Look) -->
             <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid #e2e8f0;">
                <h3 style="font-size: 1.5rem; margin-bottom: 2rem; color: #0f172a; font-weight: 900; letter-spacing: -0.01em; text-align: center;">Explorar directorios vinculados al sector</h3>
                
                <div class="internal-links-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px;">
                    <?php if (!empty($national_hubs)): ?>
                        <?php foreach ($national_hubs as $hub): ?>
                            <a href="<?= esc($hub['url']) ?>" class="value-prop-card" style="padding: 20px; text-decoration: none; border-left: 4px solid #f1f5f9;">
                                <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Hub Nacional</span>
                                <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #0f172a;"><?= esc($sector_label) ?> en <?= esc($hub['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h3 style="font-size: 1.5rem; margin-bottom: 2rem; color: #0f172a; font-weight: 900; letter-spacing: -0.01em; text-align: center;">Explorar directorios regionales</h3>
                
                <div class="internal-links-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <a href="<?= site_url('empresas/' . url_title($province, '-', true)) ?>" class="value-prop-card" style="padding: 20px; text-decoration: none;">
                        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 4px;">Directorio Completo</span>
                        <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Todas las empresas en <?= esc($province) ?></span>
                    </a>
                    <a href="<?= site_url('empresas-nuevas/' . url_title($province, '-', true)) ?>" class="value-prop-card" style="padding: 20px; text-decoration: none; border-left: 4px solid #f59e0b;">
                        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #d97706; text-transform: uppercase; margin-bottom: 4px;">Radar de Nuevas Altas</span>
                        <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Nuevas empresas <?= esc($province) ?></span>
                    </a>
                    <a href="<?= site_url('empresas-nuevas/' . url_title($sector_label, '-', true) . '-en-' . url_title($province, '-', true)) ?>" class="value-prop-card" style="padding: 20px; text-decoration: none; border-left: 4px solid #ef4444;">
                        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 4px;">Radar Sectorial Específico</span>
                        <span style="display: block; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Últimas de <?= esc($sector_label) ?></span>
                    </a>
                </div>
            </div>

            <!-- SEMANTIC SEO BLOCK (NEW) -->
            <div style="margin: 40px auto 80px; max-width: 900px; padding: 0 20px; color: #475569; line-height: 1.8; font-size: 0.95rem;">
                <h3 style="font-size: 1.25rem; font-weight: 850; color: #0f172a; margin-bottom: 16px;">Sobre las empresas de <?= esc($sector_label) ?> en <?= esc($province) ?></h3>
                <p style="margin-bottom: 16px;">
                    <?= esc($province) ?> es uno de los principales hubs empresariales del Mediterráneo. El sector de <?= esc($sector_label) ?> incluye una amplia variedad de perfiles, desde agencias creativas y tecnológicas hasta consultoras digitales y especialistas en marketing online avanzados.
                </p>
                <p style="margin-bottom: 16px;">
                    Con nuestro directorio empresarial puedes identificar agencias activas y encontrar potenciales partners comerciales. Detecta <a href="<?= site_url('empresas/' . url_title($province, '-', true)) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">empresas en <?= esc($province) ?></a> o consulta el ranking nacional de <a href="<?= site_url('empresas-cnae/' . url_title($sector_label, '-', true)) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">empresas de <?= esc($sector_label) ?> en España</a>.
                </p>
                <p style="font-weight: 600; color: #1e293b;">
                    Mantén tu base de datos actualizada y segmentada por actividad y localización para maximizar el impacto de tus acciones comerciales.
                </p>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>

</html>
