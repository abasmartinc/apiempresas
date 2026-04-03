<?php
/**
 * REPORT TEMPLATE - BRAND ALIGNED EDITION 🏢
 * Diseño profesional, limpio y totalmente integrado con la identidad de APIEmpresas.
 */
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

    <style>
        :root {
            --ae-primary: #133A82;
            --ae-secondary: #12b48a;
            --ae-bg: #fbfdff;
            --ae-text: #0e1320;
            --ae-muted: #5b647a;
            --ae-border: #e8ecf5;
            --ae-radius: 14px;
            --ae-shadow: 0 6px 22px rgba(18, 26, 60, .08);
        }

        body {
            background-color: var(--ae-bg);
            color: var(--ae-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }

        /* --- Refined Hero --- */
        .report-hero {
            background: var(--ae-primary);
            padding: 40px 0 60px;
            color: #fff;
            text-align: center;
            position: relative;
        }

        .report-hero__badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .report-hero__title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 900;
            color: #fff;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.02em;
            max-width: 1000px;
            margin-inline: auto;
        }

        /* --- Main Layout --- */
        .report-grid {
            margin-top: -30px;
            padding-bottom: 80px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            align-items: start;
        }

        /* --- Main Content --- */
        .report-content {
            background: #fff;
            border-radius: var(--ae-radius);
            padding: 40px;
            box-shadow: var(--ae-shadow);
            border: 1px solid var(--ae-border);
        }

        .report-body {
            font-size: 1.05rem;
            color: #2d3748;
        }

        .report-body h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--ae-primary);
            margin: 35px 0 20px;
            letter-spacing: -0.01em;
        }

        .report-body p { margin-bottom: 20px; }

        /* --- Metrics Overlay --- */
        .report-metrics {
            margin-top: 50px;
            padding: 30px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--ae-border);
        }

        .report-metrics__title {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: var(--ae-primary);
        }

        .metrics-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 15px;
        }

        .metric-item {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid var(--ae-border);
        }

        .metric-item__label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--ae-muted);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .metric-item__value {
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--ae-text);
        }

        .metric-item__value.color { color: var(--ae-secondary); }

        /* --- Sidebar Widgets --- */
        .sidebar-widget {
            background: #fff;
            border-radius: var(--ae-radius);
            padding: 25px;
            box-shadow: var(--ae-shadow);
            border: 1px solid var(--ae-border);
            margin-bottom: 20px;
        }

        .widget-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--ae-primary);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .widget-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .widget-item {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .widget-item:last-child { border-bottom: none; }

        .widget-link {
            text-decoration: none;
            color: var(--ae-muted);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s;
        }

        .widget-link:hover { color: var(--ae-primary); }

        .widget-link::before {
            content: "•";
            color: var(--ae-secondary);
            font-weight: bold;
        }

        /* --- CTA Block --- */
        .report-cta {
            margin-top: 60px;
            background: #0e1320;
            border-radius: var(--ae-radius);
            padding: 50px 30px;
            text-align: center;
            color: #fff;
        }

        .report-cta h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .report-cta p {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 30px;
            max-width: 500px;
            margin-inline: auto;
        }

        .btn-ae {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--ae-secondary);
            color: #fff;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s;
        }

        .btn-ae:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(18, 180, 138, 0.3); }

        @media (max-width: 1000px) {
            .report-grid { grid-template-columns: 1fr; }
            .report-hero { padding: 30px 0 50px; }
        }
    </style>
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <!-- HERO HEADER -->
    <header class="report-hero">
        <div class="container">
            <span class="report-hero__badge">Informe de Mercado</span>
            <h1 class="report-hero__title"><?= esc($title) ?></h1>
        </div>
    </header>

    <main class="container">
        <div class="report-grid">
            
            <!-- MAIN CONTENT -->
            <article class="report-content">
                <div class="report-body">
                    <?= $wp_content ?>
                </div>

                <!-- Dynamic Data Section: Only if we have results -->
                <?php if (($radar_data['total_context_count'] ?? 0) > 0): ?>
                    <div class="report-metrics">
                        <h3 class="report-metrics__title">KPIs de Mercado Real (Madrid)</h3>
                        <div class="metrics-row">
                            <div class="metric-item">
                                <div class="metric-item__label">Alertas Hoy</div>
                                <div class="metric-item__value color"><?= esc($radar_data['count_indicators']['today'] ?? 0) ?></div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-item__label">Últimos 7 días</div>
                                <div class="metric-item__value"><?= esc($radar_data['count_indicators']['last_7_days'] ?? 0) ?></div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-item__label">Últimos 30 días</div>
                                <div class="metric-item__value"><?= number_format($radar_data['total_context_count'] ?? 0, 0, ',', '.') ?></div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-item__label">Crecimiento</div>
                                <div class="metric-item__value color">+<?= esc($radar_data['growth_pct'] ?? '0') ?>%</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Diseño mejorado para cuando no hay datos específicos -->
                    <div class="report-metrics" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #e2e8f0; padding: 50px 30px;">
                        <h3 class="report-metrics__title" style="margin-bottom: 40px;">Potencia tu Inteligencia de Mercado</h3>
                        
                        <div class="metrics-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- Card 1 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(33, 82, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-primary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Datos 100% Oficiales</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Acceso directo fuentes registrables: BORME, AEAT e INE en tiempo real.</p>
                            </div>

                            <!-- Card 2 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(18, 180, 138, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-secondary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Segmentación Precisa</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Filtra por códigos CNAE exactos, capital social y rangos de facturación.</p>
                            </div>

                            <!-- Card 3 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(33, 82, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-primary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Leads de Calidad</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Descarga listados con emails, teléfonos y nombres de administradores.</p>
                            </div>

                            <!-- Card 4 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(18, 180, 138, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-secondary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Exportación CRM</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Exporta informes ilimitados en Excel y CSV compatibles con tu flujo de ventas.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CTA -->
                <div class="report-cta">
                    <h2>¿Necesitas el listado completo?</h2>
                    <p>Accede ahora al Radar B2B y descarga el Excel con todas estas empresas, incluyendo datos de contacto y CIF.</p>
                    <a href="<?= site_url('register') ?>" class="btn-ae">
                        Ver empresas en tiempo real →
                    </a>
                </div>
            </article>

            <!-- SIDEBAR -->
            <aside class="sidebar">
                
                <div class="sidebar-widget">
                    <h3 class="widget-title">Otras Localidades</h3>
                    <ul class="widget-list">
                        <?php 
                        // Buscar una plantilla limpia que tenga {{provincia}} (excluimos suffixes de SEO verbose)
                        $bestProvMatch = null;
                        $blacklist = ['listado', 'actualizado', 'hoy', 'semana', 'analisis'];
                        
                        foreach (($sidebar['templates'] ?? []) as $st) {
                            $stTitle = html_entity_decode($st['title']['rendered'], ENT_QUOTES, 'UTF-8');
                            $hasBlacklist = false;
                            foreach ($blacklist as $word) {
                                if (stripos($stTitle, $word) !== false) { $hasBlacklist = true; break; }
                            }
                            
                            if (!$hasBlacklist && strpos($stTitle, '{{provincia}}') !== false) {
                                if (!$bestProvMatch || strlen($stTitle) < strlen($bestProvMatch)) {
                                    $bestProvMatch = $stTitle;
                                }
                            }
                        }
                        $provBase = $bestProvMatch ?? $wp_raw_title;

                        $currentSectorFix = $radar_data['sector_label'] ?? 'General';
                        foreach (array_slice($sidebar['provinces'] ?? [], 0, 8) as $prov): 
                            $linkTitle = str_replace(['{{provincia}}', '{{sector}}'], [$prov, $currentSectorFix], $provBase);
                            $finalLinkSlug = $seoService->slugifyWithPlaceholders($linkTitle);
                        ?>
                            <li class="widget-item">
                                <a href="<?= site_url('informes/' . $finalLinkSlug) ?>" class="widget-link">
                                    Nuevas en <?= esc($prov) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3 class="widget-title">Otros Sectores</h3>
                    <ul class="widget-list">
                        <?php 
                        // Buscar una plantilla limpia que tenga {{sector}} (excluimos suffixes de SEO verbose)
                        $bestSectMatch = null;
                        foreach (($sidebar['templates'] ?? []) as $st) {
                            $stTitle = html_entity_decode($st['title']['rendered'], ENT_QUOTES, 'UTF-8');
                            $hasBlacklist = false;
                            foreach ($blacklist as $word) {
                                if (stripos($stTitle, $word) !== false) { $hasBlacklist = true; break; }
                            }
                            
                            if (!$hasBlacklist && strpos($stTitle, '{{sector}}') !== false) {
                                if (!$bestSectMatch || strlen($stTitle) < strlen($bestSectMatch)) {
                                    $bestSectMatch = $stTitle;
                                }
                            }
                        }
                        $sectBase = $bestSectMatch ?? $wp_raw_title;

                        $currentProvFix = $radar_data['province'] ?? 'España';
                        foreach (array_slice($sidebar['sectors'] ?? [], 0, 8) as $sect): 
                            $linkTitle = str_replace(['{{provincia}}', '{{sector}}'], [$currentProvFix, $sect], $sectBase);
                            $finalLinkSlug = $seoService->slugifyWithPlaceholders($linkTitle);
                        ?>
                        <li class="widget-item">
                            <a href="<?= site_url('informes/' . $finalLinkSlug) ?>" class="widget-link">
                                Sector <?= esc($sect) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3 class="widget-title">Informes Relacionados</h3>
                    <ul class="widget-list">
                        <?php 
                        $tCount = 0;
                        foreach (($sidebar['templates'] ?? []) as $t): 
                            $tTitle = html_entity_decode($t['title']['rendered'], ENT_QUOTES, 'UTF-8');
                            // Filtrar los relacionados para que tampoco tengan listado-actualizado
                            $hasBlacklist = false;
                            foreach ($blacklist as $word) {
                                if (stripos($tTitle, $word) !== false) { $hasBlacklist = true; break; }
                            }
                            if ($hasBlacklist) continue;

                            if ($tCount >= 6) break;
                            $tLinkTitle = str_replace(['{{provincia}}', '{{sector}}'], ['España', 'General'], $tTitle);
                            $tFinalSlug = $seoService->slugifyWithPlaceholders($tLinkTitle);
                        ?>
                        <li class="widget-item">
                            <a href="<?= site_url('informes/' . $tFinalSlug) ?>" class="widget-link">
                                <?= esc(mb_strimwidth(str_replace(['{{provincia}}', '{{sector}}'], ['España', 'General'], $tTitle), 0, 30, "...")) ?>
                            </a>
                        </li>
                        <?php 
                            $tCount++;
                        endforeach; ?>
                    </ul>
                </div>

            </aside>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
