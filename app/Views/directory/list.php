<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title, 
        'excerptText' => $meta_description,
        'robots'      => $robots ?? 'index,follow',
        'prevUrl'     => $pagination['prev'] ?? null,
        'nextUrl'     => $pagination['next'] ?? null
    ]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-emerald: #10b981;
            --dir-slate-900: #0f172a;
            --dir-slate-800: #1e293b;
            --dir-slate-600: #475569;
            --dir-slate-400: #94a3b8;
            --dir-slate-200: #e2e8f0;
            --dir-bg: #f8fafc;
        }

        /* ── Hero ── */
        .dir-hero {
            padding: 80px 0 60px;
            background: linear-gradient(160deg, #090d16 0%, #0d1a3a 60%, #0f172a 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dir-hero::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(33,82,255,0.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero h1 {
            font-size: clamp(1.9rem, 4vw, 2.9rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            line-height: 1.1;
        }
        .dir-hero h1 span { color: #60a5fa; }
        .dir-hero .hero-sub {
            font-size: 1.05rem;
            color: #94a3b8;
            max-width: 680px;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .hero-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .hero-stat {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .hero-stat__value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.03em;
        }
        .hero-stat__label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .hero-stat__divider {
            width: 1px;
            background: rgba(255,255,255,0.1);
            align-self: stretch;
        }

        /* ── Layout ── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .list-main {
            padding: 0 0 120px;
            background-color: var(--dir-bg);
            min-height: 80vh;
        }

        /* ── Breadcrumb ── */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1.25rem 0;
            font-size: 0.8rem;
            color: var(--dir-slate-400);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--dir-slate-200);
            margin-bottom: 2rem;
        }
        .breadcrumb a {
            color: var(--dir-primary);
            text-decoration: none;
        }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb .sep { color: #cbd5e1; }

        /* ── Search bar ── */
        .search-bar-wrap {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .search-bar-wrap svg {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dir-slate-400);
            pointer-events: none;
        }
        #companySearch {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 1.5px solid var(--dir-slate-200);
            border-radius: 12px;
            font-size: 0.97rem;
            font-family: inherit;
            background: #fff;
            color: var(--dir-slate-900);
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            box-sizing: border-box;
        }
        #companySearch:focus {
            border-color: var(--dir-primary);
            box-shadow: 0 0 0 3px rgba(33,82,255,0.1);
        }
        #searchCount {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--dir-slate-400);
        }

        /* ── Table ── */
        .dir-table-wrap {
            background: transparent;
            border: none;
            overflow: visible;
            box-shadow: none;
        }
        .dir-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: -10px; /* Offset the first top spacing */
        }
        .dir-table thead {
            display: none;
        }
        .dir-table tbody tr {
            background: #fff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03), 0 0 0 1px rgba(15, 23, 42, 0.05);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dir-table tbody tr.blurred-row {
            box-shadow: none;
            background: rgba(255,255,255,0.5);
            border: 1px solid var(--dir-slate-200);
        }
        .dir-table tbody tr:first-child { margin-top: 0; }
        .dir-table tbody tr:hover:not(.blurred-row) { 
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06), 0 0 0 1px rgba(15, 23, 42, 0.05);
            z-index: 10;
            position: relative;
        }
        .dir-table tbody td {
            padding: 1.2rem 1.5rem;
            vertical-align: middle;
        }
        .dir-table tbody td:first-child { border-radius: 16px 0 0 16px; }
        .dir-table tbody td:last-child { border-radius: 0 16px 16px 0; }


        .company-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--dir-slate-900);
            line-height: 1.3;
            max-width: 450px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            letter-spacing: -0.01em;
        }
        .company-cif {
            font-size: 0.8rem;
            color: var(--dir-slate-400);
            font-weight: 600;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .company-cif svg {
            color: var(--dir-slate-400);
        }

        /* Action button */
        .btn-ficha {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background: #fff;
            border: 1px solid var(--dir-slate-200);
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--dir-slate-600);
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .dir-table tbody tr:hover .btn-ficha {
            background: var(--dir-primary);
            border-color: var(--dir-primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(33,82,255,0.2);
        }
        .btn-ficha svg { flex-shrink: 0; transition: transform 0.2s; }
        .dir-table tbody tr:hover .btn-ficha svg { transform: translateX(2px); }

        .td-action { text-align: right; }

        /* Hidden row (search filter) */
        .dir-table tbody tr.hidden-row { display: none !important; }

        /* No results */
        .no-results-row td {
            text-align: center;
            padding: 4rem;
            color: var(--dir-slate-400);
            font-weight: 600;
            background: transparent !important;
            box-shadow: none !important;
            border: 2px dashed var(--dir-slate-200) !important;
            border-radius: 20px;
        }

        /* ── Pagination ── */
        .pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--dir-slate-200);
            flex-wrap: wrap;
            gap: 1rem;
        }
        .pagination-info {
            font-size: 0.85rem;
            color: var(--dir-slate-400);
            font-weight: 600;
        }
        .pagination-info strong { color: var(--dir-slate-900); }
        .pagination-btns {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .btn-page {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1.5px solid var(--dir-slate-200);
            border-radius: 10px;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--dir-slate-600);
            text-decoration: none;
            background: #fff;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-page:hover {
            border-color: var(--dir-primary);
            color: var(--dir-primary);
        }
        .btn-page.disabled {
            opacity: 0.35;
            pointer-events: none;
        }
        .page-counter {
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--dir-slate-600);
            padding: 8px 12px;
            background: #f8fafc;
            border: 1.5px solid var(--dir-slate-200);
            border-radius: 10px;
        }

        /* ── Sectors (cross-links) ── */
        .sectors-section {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid var(--dir-slate-200);
        }
        .sectors-section h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--dir-slate-900);
            margin-bottom: 1.25rem;
            letter-spacing: -0.02em;
        }
        .sector-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }
        .sector-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 7px 14px;
            background: #fff;
            border: 1.5px solid var(--dir-slate-200);
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--dir-slate-600);
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .sector-pill:hover {
            border-color: var(--dir-primary);
            color: var(--dir-primary);
            background: #eef2ff;
        }
        .sector-pill__count {
            font-size: 0.72rem;
            font-weight: 800;
            background: #f1f5f9;
            color: var(--dir-slate-400);
            border-radius: 999px;
            padding: 1px 7px;
            transition: background 0.2s, color 0.2s;
        }
        .sector-pill:hover .sector-pill__count {
            background: rgba(33,82,255,0.1);
            color: var(--dir-primary);
        }

        /* ── Radar CTA ── */
        .radar-cta {
            margin-top: 4rem;
            padding: 3rem 2.5rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
            border-radius: 24px;
            border: 1px solid #bbf7d0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .radar-cta__text h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .radar-cta__text p {
            color: #475569;
            font-size: 0.97rem;
            max-width: 500px;
            line-height: 1.6;
        }
        .radar-cta__btn {
            background: #16a34a;
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.97rem;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(22,163,74,0.25);
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .radar-cta__btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(22,163,74,0.3);
        }

        /* ── Bottom CTA ── */
        .cta-bottom {
            margin-top: 3rem;
            padding: 3.5rem 2rem;
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid var(--dir-slate-200);
            text-align: center;
        }
        .cta-bottom h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dir-slate-900);
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        .cta-bottom p {
            color: var(--dir-slate-600);
            margin-bottom: 2rem;
            max-width: 540px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        .cta-btn-row {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ── Paywall ── */
        .paywall-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.85) 25%, #f8fafc 60%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
            padding: 40px 20px;
            text-align: center;
        }
        .paywall-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1), 0 0 0 1px rgba(33,82,255,0.1);
            max-width: 500px;
            width: 100%;
        }
        .blurred-row td { filter: blur(4px); opacity: 0.4; user-select: none; pointer-events: none; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .container { padding: 0 1.25rem; }
            .dir-hero { padding: 60px 1.25rem 40px; }
            .dir-hero h1 { font-size: 1.75rem; }
            .hero-stats { gap: 1.25rem; }
            .list-main { padding: 0 0 80px; }
            .company-name { max-width: 180px; }
            .td-cif { display: none; }
            .radar-cta { flex-direction: column; text-align: center; }
            .radar-cta__text p { max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>
<?= view('partials/header') ?>

<header class="dir-hero">
    <div class="container">
        <nav class="breadcrumb" style="padding:0; border:none; margin-bottom:1.5rem; background:transparent; color:#64748b; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.06em;">
            <a href="<?= site_url() ?>" style="color:#60a5fa; text-decoration:none;">Inicio</a>
            <span style="color:#334155;">/</span>
            <a href="<?= site_url('directorio') ?>" style="color:#60a5fa; text-decoration:none;">Directorio</a>
            <span style="color:#334155;">/</span>
            <span style="color:#94a3b8;"><?= esc($province_name ?? $header) ?></span>
        </nav>

        <?php if (!empty($province_name)): ?>
            <h1><span><?= esc($total_formatted ?? count($items)) ?> empresas</span> registradas en <?= esc($province_name) ?></h1>
            <p class="hero-sub" style="margin-bottom: 0.5rem;"><?= esc($excerptText) ?></p>
            <p style="color: #94a3b8; max-width: 700px; font-size: 0.95rem; line-height: 1.5; margin-bottom: 2.5rem;">
                Consulta el listado completo de sociedades y empresas en <?= esc($province_name) ?>. 
                Accede a la ficha mercantil completa con CIF, razón social, administradores, sector CNAE y vinculaciones.
                Información actualizada diariamente desde el BORME y el Registro Mercantil Central.
            </p>
        <?php else: ?>
            <h1><?= esc($header ?? 'Directorio de Empresas') ?></h1>
            <p class="hero-sub" style="margin-bottom: 0.5rem;"><?= esc($excerptText ?? '') ?></p>
            <p style="color: #94a3b8; max-width: 700px; font-size: 0.95rem; line-height: 1.5; margin-bottom: 2.5rem;">
                <?= esc($meta_description ?? '') ?>
            </p>
        <?php endif; ?>

        <div class="hero-stats" style="align-items: center;">
            <div class="hero-stat">
                <span class="hero-stat__value"><?= esc($total_formatted ?? count($items)) ?></span>
                <span class="hero-stat__label">Empresas registradas</span>
            </div>
            <div class="hero-stat__divider"></div>
            <div class="hero-stat">
                <span class="hero-stat__value"><?= esc($pagination['total'] ?? '—') ?></span>
                <span class="hero-stat__label">Páginas disponibles</span>
            </div>
            <div class="hero-stat__divider"></div>
            <div class="hero-stat">
                <span class="hero-stat__value"><?= count($items) ?></span>
                <span class="hero-stat__label">En esta página</span>
            </div>
            
            <?php 
                if (!empty($province_name)) {
                    if (isset($cnae_code)) {
                        $checkoutUrl = site_url('billing/directory_checkout?cnae=' . esc($cnae_code) . '&sector=' . urlencode($province_name ?? ''));
                    } else {
                        $checkoutUrl = site_url('billing/directory_checkout?provincia=' . urlencode($province_name ?? 'España'));
                    }
                } else {
                    $checkoutUrl = site_url('checkout/radar-export?type=single');
                }
            ?>

            <?php if (!empty($province_name)): ?>
            <div style="margin-left: auto; text-align: right;">
                <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25); transition: all 0.2s; margin-bottom: 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(16, 185, 129, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(16, 185, 129, 0.25)';">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <?php if (isset($cnae_code)): ?>
                        Descargar CSV — <?= esc($dynamic_price ?? '9') ?>€
                    <?php else: ?>
                        Descargar CSV (<?= esc($province_name ?? 'España') ?>) — <?= esc($dynamic_price ?? '9') ?>€
                    <?php endif; ?>
                </a>
                <div style="font-size: 0.75rem; color: #94a3b8; max-width: 280px; margin-left: auto;">
                    Incluye: CIF, Razón social, Dirección, CNAE, Provincia, Fecha constitución, Capital Social, Socio Único y Cargos. <strong style="color: #64748b; font-weight: 600;">(Puede haber empresas sin teléfono)</strong>.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="list-main">
    <div class="container" style="padding-top: 2.5rem;">



        <!-- Search bar -->
        <div class="search-bar-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="search" id="companySearch" placeholder="Buscar empresa por nombre o CIF en <?= esc($province_name ?? '') ?>..." autocomplete="off" aria-label="Buscar empresa">
            <span id="searchCount"><?= count($items) ?> empresas</span>
        </div>

        <!-- Table -->
        <div class="dir-table-wrap" style="position: relative;">
            <table class="dir-table" id="companyTable">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th style="text-align:right;">Ficha</th>
                    </tr>
                </thead>
                <tbody id="companyTableBody">
                    <?php 
                        helper('company');
                        foreach($items as $company):
                        $url = company_url($company);
                    ?>
                    <tr data-name="<?= esc(strtolower($company['name'])) ?>" data-cif="<?= esc(strtolower($company['cif'] ?? '')) ?>">
                        <td>
                            <div style="display:flex; align-items:center;">
                                <div>
                                    <div class="company-name" title="<?= esc($company['name']) ?>"><?= esc($company['name']) ?></div>
                                    <div class="company-cif">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><circle cx="9" cy="10" r="2"></circle><line x1="15" y1="8" x2="17" y2="8"></line><line x1="15" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="17" y2="16"></line></svg>
                                        CIF: <?= esc($company['cif'] ?? '—') ?>
                                    </div>
                                    <?php if (!empty($company['cnae_label']) || !empty($company['founded'])): ?>
                                    <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;">
                                        <?php if (!empty($company['cnae_label'])): ?>
                                        <span style="font-size:0.75rem; background:#f1f5f9; color:#475569; padding:4px 8px; border-radius:6px; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                            <?= esc($company['cnae_label']) ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($company['founded']) && $company['founded'] !== '0000-00-00'): ?>
                                        <span style="font-size:0.75rem; background:#f0fdf4; color:#16a34a; padding:4px 8px; border-radius:6px; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            Constituida en <?= date('Y', strtotime($company['founded'])) ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="td-action">
                            <a href="<?= esc($url) ?>" class="btn-ficha">
                                Ver ficha
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (isset($paywall_level) && $paywall_level === 'soft'): ?>
                        <?php for ($i = 0; $i < 8; $i++): ?>
                        <tr class="blurred-row">
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <div>
                                        <div class="company-name">Empresa Restringida S.L.</div>
                                        <div class="company-cif">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><circle cx="9" cy="10" r="2"></circle><line x1="15" y1="8" x2="17" y2="8"></line><line x1="15" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="17" y2="16"></line></svg>
                                            CIF: A00******
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="td-action"><span class="btn-ficha">Ver ficha <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span></td>
                        </tr>
                        <?php endfor; ?>
                    <?php endif; ?>

                    <tr class="no-results-row" id="noResultsRow" style="display:none;">
                        <td colspan="2">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.75rem; display:block; opacity:0.3;"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            Sin resultados para tu búsqueda en esta página
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if (isset($paywall_level) && $paywall_level === 'soft'): ?>
                <div class="paywall-overlay">
                    <div class="paywall-card">
                        <div style="width:52px; height:52px; background:#eef2ff; color:var(--dir-primary); border-radius:14px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:20px;">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <h3 style="font-size:1.35rem; font-weight:800; color:#0f172a; margin-bottom:10px;">Viendo solo una muestra</h3>
                        <p style="color:#475569; margin-bottom:22px; line-height:1.6;">Accede a todas las empresas de <?= esc($province_name ?? '') ?> con datos de contacto y ficha completa.</p>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <?php if (!empty($province_name)): // Historical Directory flow ?>
                                <?php if(isset($checkoutUrl)): ?>
                                <a href="<?= $checkoutUrl ?>" style="background:var(--dir-primary); color:white; padding:14px 20px; border-radius:12px; font-weight:800; text-decoration:none;">Descargar CSV Completo · <?= esc($dynamic_price ?? '9') ?>€</a>
                                <?php endif; ?>
                                <a href="<?= site_url('search_company') ?>" style="background:white; color:#0f172a; border:1.5px solid #cbd5e1; padding:12px 20px; border-radius:12px; font-weight:700; text-decoration:none;">Ir al Buscador Avanzado</a>
                            <?php else: // Radar 30-Days flow ?>
                                <a href="<?= site_url('excel/preview?period=30days') ?>" style="background:var(--dir-primary); color:white; padding:14px 20px; border-radius:12px; font-weight:800; text-decoration:none;">Desbloquear acceso completo</a>
                                <?php if(isset($checkoutUrl)): ?>
                                <a href="<?= $checkoutUrl ?>" style="background:white; color:#0f172a; border:1.5px solid #cbd5e1; padding:12px 20px; border-radius:12px; font-weight:700; text-decoration:none;">Descargar CSV · <?= esc($dynamic_price ?? '39') ?>€</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div><!-- /.dir-table-wrap -->

        <!-- Pagination -->
        <?php if (!empty($pagination) && ($pagination['prev'] || $pagination['next'])): ?>
        <div class="pagination-wrap">
            <span class="pagination-info">
                Página <strong><?= esc($pagination['current']) ?></strong> de <strong><?= esc($pagination['total'] ?? '—') ?></strong>
                <?php if (!empty($total_formatted)): ?>
                    &nbsp;·&nbsp; <?= esc($total_formatted) ?> empresas en total
                <?php endif; ?>
            </span>
            <div class="pagination-btns">
                <?php if ($pagination['prev']): ?>
                    <a href="<?= esc($pagination['prev']) ?>" class="btn-page" rel="prev">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                        Anterior
                    </a>
                <?php else: ?>
                    <span class="btn-page disabled">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                        Anterior
                    </span>
                <?php endif; ?>

                <span class="page-counter"><?= esc($pagination['current']) ?> / <?= esc($pagination['total'] ?? '?') ?></span>

                <?php if ($pagination['next']): ?>
                    <a href="<?= esc($pagination['next']) ?>" class="btn-page" rel="next">
                        Siguiente
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                <?php else: ?>
                    <span class="btn-page disabled">
                        Siguiente
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sectors cross-links -->
        <?php if (!empty($cross_links) && !empty($cross_links['items'])): ?>
        <div class="sectors-section">
            <h2><?= esc($cross_links['title']) ?></h2>
            <div class="sector-pills">
                <?php foreach($cross_links['items'] as $cl): ?>
                    <?php 
                        if($cross_links['type'] === 'cnae') {
                            helper('text');
                            $cnaeSlug = url_title($cl['label'] ?: "CNAE {$cl['code']}", '-', true);
                            $clUrl  = site_url('directorio/cnae/' . $cl['code'] . '/' . $cnaeSlug);
                            $clName = $cl['label'] ?: "CNAE {$cl['code']}";
                        } else {
                            $clUrl  = site_url('directorio/provincia/' . urlencode($cl['name']));
                            $clName = $cl['name'];
                        }
                    ?>
                    <a href="<?= esc($clUrl) ?>" class="sector-pill">
                        <?= esc($clName) ?>
                        <?php if (!empty($cl['total'])): ?>
                            <span class="sector-pill__count"><?= number_format($cl['total'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Radar CTA personalizado -->
        <div class="radar-cta">
            <div class="radar-cta__text">
                <h2>¿Buscas clientes potenciales en <?= esc($province_name ?? 'España') ?>?</h2>
                <p>Nuestro Radar detecta en tiempo real las sociedades con mayor probabilidad de necesitar tus servicios ahora mismo.</p>
            </div>
            <a href="<?= site_url('radar') ?>" class="radar-cta__btn">Ver oportunidades activas →</a>
        </div>

        <!-- Bottom CTA -->
        <div class="cta-bottom">
            <h2>¿No encuentra la empresa que busca?</h2>
            <p>Nuestro buscador avanzado permite localizar cualquier sociedad en España por nombre, CIF o ubicación.</p>
            <div class="cta-btn-row">
                <a href="<?= site_url('search_company') ?>" class="btn primary" style="padding: 0.9rem 2rem;">Buscador Avanzado</a>
                <a href="<?= site_url('directorio') ?>" class="btn secondary" style="padding: 0.9rem 2rem;">Volver al Directorio</a>
            </div>
        </div>

        <!-- JSON-LD: ItemList -->
        <?php
            $itemListSchema = [
                "@context"        => "https://schema.org",
                "@type"           => "ItemList",
                "itemListElement" => []
            ];
            helper('company');
            foreach($items as $index => $company) {
                $url = company_url($company);
                $itemListSchema["itemListElement"][] = [
                    "@type"    => "ListItem",
                    "position" => $index + 1,
                    "url"      => $url,
                    "name"     => $company['name']
                ];
            }
        ?>
        <script type="application/ld+json"><?= json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

        <!-- JSON-LD: BreadcrumbList -->
        <?php
            $breadcrumbSchema = [
                "@context" => "https://schema.org",
                "@type"    => "BreadcrumbList",
                "itemListElement" => [
                    ["@type" => "ListItem", "position" => 1, "name" => "Inicio",      "item" => site_url()],
                    ["@type" => "ListItem", "position" => 2, "name" => "Directorio",  "item" => site_url('directorio')],
                    ["@type" => "ListItem", "position" => 3, "name" => $province_name ?? $header, "item" => current_url()]
                ]
            ];
        ?>
        <script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

    </div>
</main>

<?= view('partials/footer') ?>

<script>
(function() {
    const input      = document.getElementById('companySearch');
    const rows       = document.querySelectorAll('#companyTableBody tr[data-name]');
    const countEl    = document.getElementById('searchCount');
    const noResults  = document.getElementById('noResultsRow');
    const total      = rows.length;

    input.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(function(row) {
            const name = row.dataset.name || '';
            const cif  = row.dataset.cif  || '';
            const match = !q || name.includes(q) || cif.includes(q);
            row.classList.toggle('hidden-row', !match);
            if (match) visible++;
        });
        countEl.textContent = q ? visible + ' resultado' + (visible !== 1 ? 's' : '') : total + ' empresas';
        noResults.style.display = (visible === 0 && q) ? '' : 'none';
    });
})();
</script>
</body>
</html>
