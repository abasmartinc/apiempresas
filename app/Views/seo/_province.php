<?php
$formatEsDate = function($dateStr) {
    if (empty($dateStr)) return 'Reciente';
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
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header') ?>

    <main>
        <!-- HERO (Matching Home Style) -->
        <section class="hero container">
            <div style="max-width: 800px;">
                <span class="pill top">Directorio Oficial • Datos del BORME • Actualización Diaria</span>
                
                <nav aria-label="Breadcrumb" style="margin: 1rem 0; font-size: 0.85rem; color: #64748b; display: flex; gap: 8px; align-items: center;">
                    <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <span style="color: var(--primary); font-weight: 600;"><?= esc($province) ?></span>
                </nav>

                <h1 class="title">
                    Empresas en <span class="grad"><?= esc($province) ?></span><br>
                    <span style="font-weight: 400; font-size: 0.7em; color: #475569;">Censo empresarial y radar de nuevas constituciones</span>
                </h1>

                <p class="subtitle">
                    Accede a la base de datos de las <strong><?= number_format($total_companies, 0, ',', '.') ?> empresas activas</strong> en <?= esc($province) ?>.
                    Analiza la segmentación por CNAE, consulta las últimas altas en el Registro Mercantil y monitoriza el crecimiento del sector privado local.
                </p>
            </div>
        </section>

        <!-- STATS STRIP (Custom Premium Style) -->
        <section class="container" style="margin-top: -20px; margin-bottom: 60px;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                <div class="card" style="padding: 24px; display: flex; gap: 16px; align-items: center; border-radius: 20px;">
                    <div style="background: rgba(33, 82, 255, 0.1); color: var(--primary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3l2-4h14l2 4"></path><path d="M5 21V10.85"></path><path d="M19 21V10.85"></path><path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Empresas Activas</div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: var(--text);"><?= number_format($total_companies, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 24px; display: flex; gap: 16px; align-items: center; border-radius: 20px;">
                    <div style="background: rgba(18, 180, 138, 0.1); color: #12b48a; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Crecimiento</div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: #12b48a;">+<?= number_format($growth_pct, 1) ?>%</div>
                    </div>
                </div>
                <div class="card" style="padding: 24px; display: flex; gap: 16px; align-items: center; border-radius: 20px;">
                    <div style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Última Actualización</div>
                        <div style="font-size: 1.25rem; font-weight: 900; color: var(--text);">Hoy, <?= date('H:i') ?>h</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTORS SECTION -->
        <section class="container" style="margin-bottom: 80px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
                <div>
                    <span class="eyebrow" style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; display: block; margin-bottom: 8px;">Segmentación Profesional</span>
                    <h2 style="font-size: 2rem; font-weight: 950; margin: 0; letter-spacing: -0.02em;">Sectores Estratégicos</h2>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                <?php foreach ($top_sectors as $sector): 
                    if (empty($sector['cnae_label'])) continue;
                    $sectorSlug = url_title($sector['cnae_label'], '-', true);
                    $provinceSlug = url_title($province, '-', true);
                ?>
                    <a href="<?= site_url("empresas-{$sectorSlug}-en-{$provinceSlug}") ?>" class="card" style="display: block; padding: 24px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 18px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='var(--primary)'; this.style.boxShadow='0 20px 40px rgba(15, 23, 42, 0.08)'" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border)'; this.style.boxShadow='var(--shadow)'">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <span class="badge-demo" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 10px; padding: 4px 8px;">CNAE <?= esc($sector['cnae'] ?? '-') ?></span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="color: var(--primary); opacity: 0; transition: opacity 0.3s;" class="arrow-icon"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text); margin: 0 0 8px 0; line-height: 1.3;"><?= esc($sector['cnae_label']) ?></h3>
                        <div style="color: #64748b; font-size: 0.9rem; font-weight: 500;">
                            <strong style="color: var(--primary);"><?= number_format($sector['total'], 0, ',', '.') ?></strong> empresas activas
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- RECENT COMPANIES TABLE (Clean White Table Style) -->
        <section class="container" style="margin-bottom: 80px;">
            <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 24px; overflow: hidden; box-shadow: var(--shadow);">
                <div style="padding: 32px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: linear-gradient(to right, #ffffff, #f8fafc);">
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 950; margin: 0; letter-spacing: -0.01em;">Nuevas Constituciones en <?= esc($province) ?></h2>
                        <p class="muted" style="margin: 4px 0 0 0; font-size: 0.95rem;">Empresas registradas en los últimos 30 días</p>
                    </div>
                    <a href="<?= site_url("empresas-nuevas/" . url_title($province, '-', true)) ?>" class="btn ghost" style="padding: 10px 20px; font-size: 0.9rem; border-radius: 12px;">Ver historial completo →</a>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                                <th style="padding: 20px 32px; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Razón Social / CIF</th>
                                <th style="padding: 20px 32px; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Fecha Constitución</th>
                                <th style="padding: 20px 32px; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_companies as $co): 
                                $coSlug = url_title($co['name'] ?? '', '-', true);
                                $coCif = $co['cif'] ?? '';
                                if (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/', $coCif)) {
                                    $coUrl = site_url($coCif . ($coSlug ? ('-' . $coSlug) : ''));
                                } else {
                                    $coUrl = site_url('empresa/' . ($co['id'] ?? 0) . ($coSlug ? ('-' . $coSlug) : ''));
                                }
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 24px 32px;">
                                        <div style="font-weight: 800; color: var(--text); font-size: 1.05rem; margin-bottom: 4px;"><?= esc($co['name'] ?? 'Empresa') ?></div>
                                        <div style="font-family: monospace; font-size: 0.85rem; color: #64748b; background: #f1f5f9; display: inline-block; padding: 2px 6px; border-radius: 4px;"><?= esc($coCif) ?></div>
                                    </td>
                                    <td style="padding: 24px 32px;">
                                        <div style="color: var(--text); font-weight: 600;"><?= $formatEsDate($co['fecha_constitucion'] ?? '') ?></div>
                                        <div style="font-size: 0.85rem; color: #12b48a; font-weight: 700;">REGISTRO OK</div>
                                    </td>
                                    <td style="padding: 24px 32px; text-align: right;">
                                        <a href="<?= esc($coUrl) ?>" class="btn" style="background: var(--primary); color: white; border: none; padding: 10px 20px; font-size: 0.85rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(19, 58, 130, 0.15);">Consultar Ficha</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>


    </main>

    <style>
        .hero { padding: 80px 0 60px; }
        .eyebrow { display: inline-block; }
        .grad { background: linear-gradient(90deg, #133A82, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent; }
        
        /* Interactive Sector Cards */
        .card:hover .arrow-icon { opacity: 1 !important; }
        
        @media (max-width: 768px) {
            .hero { padding: 40px 0; text-align: center; }
            .hero p { margin-inline: auto; }
            section[style*="grid-template-columns: repeat(3, 1fr)"] { grid-template-columns: 1fr !important; }
            .hero .title { font-size: 2.2rem; }
            .band { padding: 40px 20px; }
            .band h2 { font-size: 1.8rem; }
        }
    </style>

    <?= view('partials/footer') ?>
</body>

</html>
