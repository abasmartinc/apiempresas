<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title, 'excerptText' => $meta_description, 'canonical' => $canonical, 'robots' => $robots]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-primary-soft: rgba(33, 82, 255, 0.08);
            --dir-slate-900: #0f172a;
            --dir-slate-800: #1e293b;
            --dir-slate-700: #334155;
            --dir-slate-600: #475569;
            --dir-slate-400: #94a3b8;
            --dir-bg: #f1f5f9;
        }

        /* ── HERO ── */
        .dir-hero {
            padding: 60px 0 140px;
            background: linear-gradient(160deg, #060a14 0%, #0c1428 50%, #0f172a 100%);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dir-hero::before {
            content: '';
            position: absolute;
            top: -20%; right: -10%;
            width: 40%; height: 80%;
            background: radial-gradient(circle, rgba(33,82,255,0.14) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero::after {
            content: '';
            position: absolute;
            bottom: -10%; left: -5%;
            width: 35%; height: 60%;
            background: radial-gradient(circle, rgba(52,211,153,0.07) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero h1 {
            font-size: clamp(2.4rem, 4.5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #ffffff;
            margin-bottom: 1.25rem;
            line-height: 1.1;
        }
        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dir-hero p {
            font-size: 1.15rem;
            color: #cbd5e1;
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.65;
        }
        .dir-search-form input::placeholder { color: rgba(255,255,255,0.45); }
        .dir-search-form input:focus {
            border-color: #60A5FA;
            box-shadow: 0 0 0 4px rgba(96,165,250,0.2), 0 12px 35px rgba(0,0,0,0.3);
            background: rgba(255,255,255,0.12);
        }

        /* ── LAYOUT ── */
        .dir-main { padding: 0; background: var(--dir-bg); }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

        .dir-main-card {
            margin-top: -90px;
            position: relative;
            z-index: 10;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.10), 0 1px 0 rgba(255,255,255,0.8) inset;
            padding: 48px;
            margin-bottom: 60px;
            border: 1px solid #e2e8f0;
        }

        /* ── STATS BAR ── */
        .dir-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 56px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .dir-stat {
            background: #fff;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: background 0.2s;
        }
        .dir-stat:hover { background: #fafbff; }
        .dir-stat__icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .dir-stat__icon--blue  { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
        .dir-stat__icon--green { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
        .dir-stat__icon--purple{ background: linear-gradient(135deg, #faf5ff, #ede9fe); }
        .dir-stat__num {
            font-size: 1.75rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .dir-stat__label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 3px;
        }

        /* ── SECTION HEADER ── */
        .dir-section { margin-bottom: 72px; }
        .section-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
        }
        .section-header__icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .section-header__icon--indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .section-header h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.025em;
        }
        .section-header .line {
            height: 1px;
            background: linear-gradient(90deg, #e2e8f0, transparent);
            flex-grow: 1;
        }
        
        /* ── PREMIUM TABLE ── */
        .prem-table-wrap {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e9eef5;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
        }
        .prem-table {
            width: 100%;
            min-width: 580px;
            border-collapse: collapse;
            font-size: 0.92rem;
        }
        .prem-table thead tr {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e9eef5;
        }
        .prem-table thead th {
            padding: 14px 20px;
            color: #475569;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }
        .prem-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.18s ease;
            cursor: pointer;
        }
        .prem-table tbody tr:last-child { border-bottom: none; }
        .prem-table tbody tr:hover {
            background: #fafbff;
            box-shadow: inset 3px 0 0 var(--dir-primary);
        }
        .prem-table td { padding: 14px 20px; vertical-align: middle; }

        /* rank badge */
        .rank-badge {
            width: 28px; height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 800;
            flex-shrink: 0;
        }
        .rank-1 { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .rank-2 { background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #475569; }
        .rank-3 { background: linear-gradient(135deg, #fff7ed, #fed7aa); color: #9a3412; }
        .rank-n { background: #f8fafc; color: #94a3b8; }

        /* progress bar */
        .pbar-track {
            width: 140px; height: 6px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            display: inline-block;
        }
        .pbar-fill {
            height: 100%;
            border-radius: 99px;
            transform: scaleX(0);
            transform-origin: left;
            animation: pbarGrow 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        .pbar-fill--indigo { background: linear-gradient(90deg, #6366f1, #4f46e5); }
        @keyframes pbarGrow {
            from { transform: scaleX(0); }
            to   { transform: scaleX(1); }
        }

        /* action link */
        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 99px;
            border: 1.5px solid;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .action-link--indigo {
            color: #4f46e5;
            border-color: #c7d2fe;
            background: #fff;
        }
        .action-link--indigo:hover {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }
        
        .pagination-container { padding: 2rem 0 0 0; display: flex; justify-content: center; }
        /* Custom basic pagination styles */
        .pagination { display: flex; list-style: none; gap: 8px; margin: 0; padding: 0; align-items: center; flex-wrap: wrap; }
        .pagination li { display: inline-block; }
        .pagination li a, .pagination li > span { 
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 38px; height: 38px; padding: 0 14px;
            border-radius: 8px; border: 1px solid #e2e8f0; 
            color: #475569; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; 
            box-sizing: border-box;
        }
        .pagination li a:hover { border-color: #cbd5e1; background: #f8fafc; color: var(--dir-primary); }
        .pagination li.active a, .pagination li.active span { background: var(--dir-primary); color: white; border-color: var(--dir-primary); }
        .pagination li a span { border: none !important; padding: 0 !important; min-width: auto !important; height: auto !important; background: transparent !important; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .dir-hero { padding: 60px 1.5rem 120px; }
            .dir-hero h1 { font-size: 2.2rem; }
            .container { padding: 0 1.25rem; }
            .dir-main-card { padding: 24px 20px; margin-top: -70px; border-radius: 20px; }
            .dir-stats { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
            .section-header h2 { font-size: 1.4rem; }
            .prem-table-wrap { overflow-x: auto; }
            .pbar-track { width: 80px; }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>
<?= view('partials/header') ?>

<header class="dir-hero">
    <div class="container">
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(33, 82, 255, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px solid rgba(33, 82, 255, 0.25);">
            <span style="display: inline-block; width: 6px; height: 6px; background: #34D399; border-radius: 99px; box-shadow: 0 0 8px #34D399;"></span>
            Directorio y Buscador Gratuito
        </div>
        <h1>Directorio de <span class="grad">Grupos Empresariales</span></h1>
        <p>Explora el ecosistema corporativo de España. Descubre la estructura, filiales y el capital agregado de miles de grupos empresariales y corporaciones.</p>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; margin-bottom: 2rem; margin-top: 1.5rem;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.15); color: #34D399; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.25);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Datos Oficiales BORME
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(96, 165, 250, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(96, 165, 250, 0.25);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.92-10.44l5.36-1.36"></path></svg>
                Actualización Diaria
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(192, 132, 252, 0.15); color: #c084fc; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(192, 132, 252, 0.25);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Mapa de Filiales
            </span>
        </div>
        
        <form class="dir-search-form" method="GET" action="<?= site_url('listado-de-grupos-empresariales') ?>" style="max-width: 600px; margin-left: auto; margin-right: auto; position: relative;">
            <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar un grupo empresarial..." required style="width: 100%; padding: 1.1rem 1.5rem; padding-right: 5rem; border-radius: 99px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.07); color: #fff; font-size: 0.95rem; backdrop-filter: blur(12px); outline: none; transition: all 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.15); text-align: left;">
            <button type="submit" style="position: absolute; right: 8px; top: 8px; bottom: 8px; border-radius: 99px; background: var(--dir-primary); color: #fff; border: none; padding: 0 1.5rem; font-weight: 800; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem;" onmouseover="this.style.background='#1b44d3'" onmouseout="this.style.background='var(--dir-primary)'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-top: -1px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Buscar
            </button>
        </form>
    </div>
</header>

<main class="dir-main">
    <div class="container dir-main-card">

        <!-- ── STATS BAR ── -->
        <div class="dir-stats">
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num"><?= number_format($stats['totalHoldings'] ?? 0, 0, ',', '.') ?></div>
                    <div class="dir-stat__label">Grupos Indexados</div>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num"><?= number_format($stats['totalCompanies'] ?? 0, 0, ',', '.') ?></div>
                    <div class="dir-stat__label">Filiales Conectadas</div>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--purple">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num">24h</div>
                    <div class="dir-stat__label">Actualización</div>
                </div>
            </div>
        </div>

        <!-- ── GRUPOS ── -->
        <section class="dir-section" id="grupos-section" style="margin-bottom:0;">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--indigo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <h2>Grupos Corporativos</h2>
                <div class="line"></div>
                <form action="<?= site_url('listado-de-grupos-empresariales') ?>" method="GET" style="display:flex; gap:8px;">
                    <?php if (!empty($searchQuery)): ?>
                        <input type="hidden" name="q" value="<?= esc($searchQuery) ?>">
                    <?php endif; ?>
                    <select name="min_companies" onchange="this.form.submit()" style="padding: 6px 12px; border-radius: 99px; border: 1px solid #cbd5e1; outline: none; font-size: 0.85rem; font-weight: 600; color: #475569; background: #f8fafc; cursor: pointer;">
                        <option value="0" <?= ($minCompanies == 0) ? 'selected' : '' ?>>Todos los tamaños</option>
                        <option value="2" <?= ($minCompanies == 2) ? 'selected' : '' ?>>+ 2 empresas</option>
                        <option value="5" <?= ($minCompanies == 5) ? 'selected' : '' ?>>+ 5 empresas</option>
                        <option value="10" <?= ($minCompanies == 10) ? 'selected' : '' ?>>+ 10 empresas</option>
                        <option value="50" <?= ($minCompanies == 50) ? 'selected' : '' ?>>+ 50 empresas</option>
                    </select>
                </form>
            </div>

            <div class="prem-table-wrap">
                <table class="prem-table">
                    <thead>
                        <tr>
                            <th style="width:48px; text-align:center;"></th>
                            <th>Nombre del Grupo</th>
                            <th>Volumen de Filiales</th>
                            <th style="text-align:right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $page = isset($_GET['page_seo_es']) ? (int)$_GET['page_seo_es'] : 1;
                        if ($page < 1) $page = 1;
                        $offset = ($page - 1) * 50;
                        
                        $count = 0;
                        $maxComp = isset($stats['maxCompanies']) && $stats['maxCompanies'] > 0 ? $stats['maxCompanies'] : 1;
                        foreach ($holdings as $holding): 
                            $count++;
                            $rank = $offset + $count;
                            $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-n'));
                            
                            $pct = min(100, max(2, ($holding['companies_count'] / $maxComp) * 100));
                            $delay = ($count - 1) * 0.04;
                        ?>
                        <tr onclick="window.location='<?= site_url('grupos-empresariales/' . esc($holding['slug'])) ?>'">
                            <td style="text-align:center; padding-left:16px;">
                                <span class="rank-badge <?= $rankClass ?>">
                                    <?= $rank <= 3 ? ['🥇','🥈','🥉'][$rank-1] : $rank ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('grupos-empresariales/' . esc($holding['slug'])) ?>" style="font-weight:700; color:#0f172a; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:color 0.2s;" title="<?= esc($holding['name']) ?>" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#0f172a'">
                                    <span style="max-width:440px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block;"><?= esc($holding['name']) ?></span>
                                </a>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <span style="font-weight:800; color:#0f172a; min-width:60px; font-size:0.9rem; letter-spacing:-0.01em;"><?= number_format($holding['companies_count'], 0, ',', '.') ?></span>
                                    <div class="pbar-track">
                                        <div class="pbar-fill pbar-fill--indigo" style="width:<?= $pct ?>%; animation-delay:<?= $delay ?>s;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?= site_url('grupos-empresariales/' . esc($holding['slug'])) ?>" class="action-link action-link--indigo">
                                    Ver estructura
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($holdings)): ?>
                <div style="padding: 4rem 2rem; text-align: center; color: #64748b; font-weight: 600;">
                    No se encontraron grupos empresariales con estos filtros.
                </div>
                <?php endif; ?>
            </div>

            <div class="pagination-container">
                <?= $pager->links('default', 'seo_es') ?>
            </div>
        </section>

        <!-- ── CROSS SELL BANNER 2 (RADAR) ── -->
        <div style="background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid #334155; border-radius: 20px; padding: 24px 32px; margin-top: 72px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #f8fafc; margin: 0 0 8px 0;">¿Quieres alertas B2B automáticas?</h3>
                <p style="margin: 0; color: #94a3b8; font-size: 0.95rem;">Recibe avisos automáticos cuando se creen nuevas empresas o se formen nuevos grupos en España.</p>
            </div>
            <a href="<?= site_url('leads-empresas-nuevas') ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #3b82f6; color: white; font-weight: 800; padding: 12px 24px; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 14px rgba(59,130,246,0.3); flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                Prueba Radar B2B &rarr;
            </a>
        </div>

    </div>
</main>

<?= view('partials/footer') ?>

<?php
$schemaCollection = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Directorio de Grupos Empresariales y Holdings de España',
    'description' => 'Explora el listado completo de grupos empresariales y corporaciones registrados en España. Accede a su estructura de filiales y datos consolidados.',
    'url' => site_url('listado-de-grupos-empresariales'),
    'numberOfItems' => $stats['totalHoldings'] ?? 0
];
?>
<script type="application/ld+json">
<?= json_encode($schemaCollection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

</body>
</html>
