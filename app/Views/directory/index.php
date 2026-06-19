<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title, 'excerptText' => $meta_description]) ?>
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
        }
        .section-header__icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .section-header__icon--blue   { background: linear-gradient(135deg, #2152FF, #3b82f6); }
        .section-header__icon--green  { background: linear-gradient(135deg, #10b981, #059669); }
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
        .section-header__count {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 99px;
            white-space: nowrap;
            border: 1px solid #e2e8f0;
        }

        /* ── SEARCH INPUT ── */
        .dir-search-wrap {
            position: relative;
            max-width: 460px;
            margin-bottom: 1.25rem;
        }
        .dir-search-wrap__icon {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            display: inline-flex;
        }
        .dir-search-input {
            width: 100%;
            padding: 11px 16px 11px 46px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            outline: none;
            font-size: 0.92rem;
            font-weight: 500;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .dir-search-input:focus {
            border-color: var(--dir-primary);
            box-shadow: 0 0 0 3px rgba(33,82,255,0.1);
            background: #fff;
        }
        .dir-search-input::placeholder { color: #94a3b8; }

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
        .pbar-fill--blue  { background: linear-gradient(90deg, #2152FF, #60a5fa); }
        .pbar-fill--green { background: linear-gradient(90deg, #10b981, #34d399); }
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
        .action-link--blue {
            color: #2152FF;
            border-color: #bfdbfe;
            background: #fff;
        }
        .action-link--blue:hover {
            background: #2152FF;
            color: #fff;
            border-color: #2152FF;
        }
        .action-link--green {
            color: #10b981;
            border-color: #a7f3d0;
            background: #fff;
        }
        .action-link--green:hover {
            background: #10b981;
            color: #fff;
            border-color: #10b981;
        }

        /* show more button */
        .show-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 28px;
            border-radius: 99px;
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1.5px solid;
        }
        .show-more-btn--blue {
            background: #fff;
            color: var(--dir-primary);
            border-color: #bfdbfe;
            box-shadow: 0 2px 8px rgba(33,82,255,0.06);
        }
        .show-more-btn--blue:hover {
            background: var(--dir-primary);
            color: #fff;
            border-color: var(--dir-primary);
            box-shadow: 0 6px 20px rgba(33,82,255,0.25);
        }
        .show-more-btn--green {
            background: #fff;
            color: #10b981;
            border-color: #a7f3d0;
            box-shadow: 0 2px 8px rgba(16,185,129,0.06);
        }
        .show-more-btn--green:hover {
            background: #10b981;
            color: #fff;
            border-color: #10b981;
            box-shadow: 0 6px 20px rgba(16,185,129,0.22);
        }

        /* ── LATEST COMPANIES CARDS ── */
        .company-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        .co-card {
            background: #fff;
            border: 1.5px solid #e9eef5;
            border-radius: 18px;
            padding: 20px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }
        .co-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2152FF, #60a5fa);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
            border-radius: 2px 2px 0 0;
        }
        .co-card:hover { transform: translateY(-4px); border-color: #c7d7ff; box-shadow: 0 12px 32px rgba(33,82,255,0.10); }
        .co-card:hover::before { transform: scaleX(1); }
        .co-card__header { display: flex; align-items: center; gap: 12px; }
        .co-card__avatar {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #1e40af;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 1.05rem;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .co-card:hover .co-card__avatar {
            background: linear-gradient(135deg, #2152FF, #1e40af);
            color: #fff;
        }
        .co-card__name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0f172a;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .co-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .co-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 99px;
        }
        .co-chip--date  { background: #eff6ff; color: #2563eb; }
        .co-chip--prov  { background: #f0fdf4; color: #059669; }
        .co-chip--new   { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }

        /* province-row / cnae-row collapsed */
        .province-row--collapsed, .cnae-row--collapsed { display: none !important; }

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
            .company-cards { grid-template-columns: 1fr; }
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
            Base de Datos Oficial en Tiempo Real
        </div>
        <h1>Listado de <span class="grad">Empresas Españolas</span></h1>
        <p>Valide información mercantil en tiempo real con acceso a la base de datos más completa de sociedades en España.</p>

        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 1.5rem; margin-bottom: 1.25rem;">
            <div style="display: flex; color: #fbbf24;">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span style="color: #94a3b8; font-size: 0.95rem;">Usado por <strong>+2.500</strong> equipos de ventas en España</span>
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; margin-bottom: 2rem;">
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
                Fuente Oficial Registro Mercantil
            </span>
        </div>
        
        <form class="dir-search-form" method="GET" action="<?= site_url('search_company') ?>" style="max-width: 600px; margin-left: auto; margin-right: auto; position: relative;">
            <input type="text" name="q" placeholder="Buscar empresa por nombre, CIF, actividad o provincia..." required style="width: 100%; padding: 1.1rem 1.5rem; padding-right: 5rem; border-radius: 99px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.07); color: #fff; font-size: 0.95rem; backdrop-filter: blur(12px); outline: none; transition: all 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.15); text-align: left;">
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
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num"><?= number_format(array_sum(array_column($provinces, 'total')), 0, ',', '.') ?></div>
                    <div class="dir-stat__label">Empresas Indexadas</div>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num"><?= count($provinces) ?></div>
                    <div class="dir-stat__label">Provincias Cubiertas</div>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--purple">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num"><?= count($cnaes) ?></div>
                    <div class="dir-stat__label">Sectores CNAE</div>
                </div>
            </div>
        </div>

        <!-- ── PROVINCIAS ── -->
        <section class="dir-section" id="provincias-section">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h2>Empresas por Provincia</h2>
                <div class="line"></div>
                <span class="section-header__count"><?= count($provinces) ?> provincias</span>
            </div>

            <div class="dir-search-wrap">
                <span class="dir-search-wrap__icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input type="text" id="provinceSearch" class="dir-search-input" placeholder="Filtrar provincia al instante...">
            </div>

            <div class="prem-table-wrap">
                <table class="prem-table">
                    <thead>
                        <tr>
                            <th style="width:48px; text-align:center;">#</th>
                            <th>Provincia</th>
                            <th>Volumen de Empresas</th>
                            <th style="text-align:right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 0;
                        foreach($provinces as $prov):
                            $count++;
                            $collapsedClass = ($count > 12) ? 'province-row--collapsed' : '';
                            $pct = min(100, max(2, ($prov['total'] / $max_province) * 100));
                            $delay = ($count - 1) * 0.04;
                            $rankClass = $count === 1 ? 'rank-1' : ($count === 2 ? 'rank-2' : ($count === 3 ? 'rank-3' : 'rank-n'));
                        ?>
                        <tr class="province-row <?= $collapsedClass ?>" data-name="<?= esc($prov['name']) ?>">
                            <td style="text-align:center; padding-left:16px;">
                                <span class="rank-badge <?= $rankClass ?>">
                                    <?= $count <= 3 ? ['🥇','🥈','🥉'][$count-1] : $count ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('listado-de-empresas/' . urlencode($prov['name'])) ?>" style="font-weight:700; color:#0f172a; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#0f172a'">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?= esc($prov['name']) ?>
                                </a>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <span style="font-weight:800; color:#0f172a; min-width:85px; font-size:0.9rem; letter-spacing:-0.01em;"><?= number_format($prov['total'], 0, ',', '.') ?></span>
                                    <div class="pbar-track">
                                        <div class="pbar-fill pbar-fill--blue" style="width:<?= $pct ?>%; animation-delay:<?= $delay ?>s;"></div>
                                    </div>
                                    <span style="font-size:0.78rem; color:#94a3b8; font-weight:600; min-width:36px;"><?= round($pct) ?>%</span>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?= site_url('listado-de-empresas/' . urlencode($prov['name'])) ?>" class="action-link action-link--blue">
                                    Ver provincia
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align:center; margin-top:1.5rem;">
                <button id="viewMoreProvinces" class="show-more-btn show-more-btn--blue">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    Ver todas las provincias (+<?= count($provinces) - 12 ?>)
                </button>
            </div>
        </section>

        <!-- ── CNAES ── -->
        <section class="dir-section" id="cnaes-section">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <h2>Sectores de Actividad (CNAE)</h2>
                <div class="line"></div>
                <span class="section-header__count"><?= count($cnaes) ?> sectores</span>
            </div>

            <div class="dir-search-wrap">
                <span class="dir-search-wrap__icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input type="text" id="cnaeSearch" class="dir-search-input" placeholder="Filtrar sector al instante...">
            </div>

            <div class="prem-table-wrap">
                <table class="prem-table">
                    <thead>
                        <tr>
                            <th style="width:48px; text-align:center;">#</th>
                            <th>Sector de Actividad</th>
                            <th>Volumen de Empresas</th>
                            <th style="text-align:right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 0;
                        foreach($cnaes as $cnae):
                            $count++;
                            $collapsedClass = ($count > 12) ? 'cnae-row--collapsed' : '';
                            $label = $cnae['name'] ?: "CNAE {$cnae['cnae']}";
                            $pct = min(100, max(2, ($cnae['total'] / $max_cnae) * 100));
                            $delay = ($count - 1) * 0.04;
                            $rankClass = $count === 1 ? 'rank-1' : ($count === 2 ? 'rank-2' : ($count === 3 ? 'rank-3' : 'rank-n'));
                            helper('text');
                            $cnaeSlug = url_title($cnae['name'], '-', true);
                        ?>
                        <tr class="cnae-row <?= $collapsedClass ?>" data-name="<?= esc($label) ?>">
                            <td style="text-align:center; padding-left:16px;">
                                <span class="rank-badge <?= $rankClass ?>">
                                    <?= $count <= 3 ? ['🥇','🥈','🥉'][$count-1] : $count ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('listado-de-empresas/sector-' . $cnae['cnae'] . '/' . $cnaeSlug) ?>" style="font-weight:700; color:#0f172a; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:color 0.2s;" title="<?= esc($cnae['name']) ?>" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#0f172a'">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" style="flex-shrink:0;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                    <span style="max-width:440px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block;"><?= esc($label) ?></span>
                                </a>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <span style="font-weight:800; color:#0f172a; min-width:85px; font-size:0.9rem; letter-spacing:-0.01em;"><?= number_format($cnae['total'], 0, ',', '.') ?></span>
                                    <div class="pbar-track">
                                        <div class="pbar-fill pbar-fill--green" style="width:<?= $pct ?>%; animation-delay:<?= $delay ?>s;"></div>
                                    </div>
                                    <span style="font-size:0.78rem; color:#94a3b8; font-weight:600; min-width:36px;"><?= round($pct) ?>%</span>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?= site_url('listado-de-empresas/sector-' . $cnae['cnae'] . '/' . $cnaeSlug) ?>" class="action-link action-link--green">
                                    Ver sector
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align:center; margin-top:1.5rem;">
                <button id="viewMoreCnaes" class="show-more-btn show-more-btn--green">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    Ver todos los sectores (+<?= count($cnaes) - 12 ?>)
                </button>
            </div>
        </section>

        <!-- ── ÚLTIMAS EMPRESAS ── -->
        <?php if (!empty($latest)): ?>
        <section class="dir-section" style="margin-bottom:0;">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--indigo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <h2>Últimas Empresas Registradas</h2>
                <div class="line"></div>
                <a href="<?= site_url('empresas-nuevas') ?>" style="display:inline-flex; align-items:center; gap:5px; color:var(--dir-primary); font-weight:700; text-decoration:none; font-size:0.85rem; padding:6px 14px; border:1.5px solid #bfdbfe; border-radius:99px; white-space:nowrap; transition:all 0.2s;" onmouseover="this.style.background='var(--dir-primary)'; this.style.color='#fff'; this.style.borderColor='var(--dir-primary)';" onmouseout="this.style.background='transparent'; this.style.color='var(--dir-primary)'; this.style.borderColor='#bfdbfe';">
                    Ver todas
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

            <?php helper('company'); ?>
            <div class="company-cards">
                <?php foreach($latest as $company):
                    $url = company_url($company);
                    $initial = esc(mb_strtoupper(mb_substr($company['name'], 0, 1)));
                    $dateFormatted = date('d/m/Y', strtotime($company['founded']));
                    $isNew = (strtotime($company['founded']) >= strtotime('-7 days'));
                ?>
                <a href="<?= esc($url) ?>" class="co-card">
                    <div class="co-card__header">
                        <div class="co-card__avatar"><?= $initial ?></div>
                        <div class="co-card__name"><?= esc($company['name']) ?></div>
                    </div>
                    <div class="co-card__meta">
                        <?php if ($isNew): ?>
                        <span class="co-chip co-chip--new">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Nueva
                        </span>
                        <?php endif; ?>
                        <span class="co-chip co-chip--date">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?= $dateFormatted ?>
                        </span>
                        <?php if (!empty($company['province'])): ?>
                        <span class="co-chip co-chip--prov">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= esc($company['province']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ── SEO TEXT & FAQ ── -->
        <section class="dir-section" style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid #e2e8f0; margin-bottom: 2rem;">
            <div style="color: #475569; font-size: 0.95rem; line-height: 1.7;">
                <h2 style="font-size: 1.4rem; color: #0f172a; margin-bottom: 1rem; font-weight: 800;">Acerca de este Listado de Empresas Españolas</h2>
                <p style="margin-bottom: 1.25rem;">
                    Nuestro <strong>listado de empresas españolas</strong> proporciona acceso estructurado y oficial a la base de datos B2B más completa del país. Diariamente sincronizamos millones de registros procedentes de fuentes públicas como el BORME (Boletín Oficial del Registro Mercantil) y el Registro Mercantil Central para asegurar la máxima calidad y frescura de los datos societarios.
                </p>
                <h3 style="font-size: 1.15rem; color: #0f172a; margin-top: 1.5rem; margin-bottom: 0.75rem; font-weight: 700;">¿Cómo descargar la base de datos de empresas?</h3>
                <p style="margin-bottom: 1.25rem;">
                    Si eres un equipo de ventas, marketing o análisis, puedes utilizar nuestro buscador avanzado o navegar por las provincias y sectores CNAE (Clasificación Nacional de Actividades Económicas) para localizar nichos específicos. En cada sección provincial y sectorial de este listado, así como en nuestra <a href="<?= site_url('base-de-datos-de-empresas') ?>" style="color: #2152FF; text-decoration: underline; font-weight: 600;">base de datos general</a>, ofrecemos la opción de descargar los registros en formatos estructurados como Excel, perfectos para integrarse en tu CRM y listos para trabajar.
                </p>
                <h3 style="font-size: 1.15rem; color: #0f172a; margin-top: 1.5rem; margin-bottom: 0.75rem; font-weight: 700;">¿Qué incluye la ficha de cada sociedad?</h3>
                <p style="margin-bottom: 0;">
                    Al consultar cualquier mercantil dentro de este listado, accederás a un resumen oficial que incluye su NIF/CIF, domicilio social actual, provincia de registro, actividad principal (CNAE), fecha de constitución y eventos recientes publicados. Esta transparencia es clave para procesos de validación mercantil, prospección comercial (Sales Intelligence) y estudios de mercado sectoriales.
                </p>
            </div>
        </section>

    </div>
</main>

        <?php
        $breadcrumbSchema = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Inicio",
                    "item" => site_url()
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Listado de empresas",
                    "item" => site_url('listado-de-empresas')
                ]
            ]
        ];
        ?>
        <script type="application/ld+json">
            <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Provinces Accordion & Filter
                const provSearch = document.getElementById('provinceSearch');
                const provRows = document.querySelectorAll('.province-row');
                const provBtn = document.getElementById('viewMoreProvinces');
                let provExpanded = false;

                function updateProvinces() {
                    const query = provSearch.value.toLowerCase().trim();
                    if (query.length > 0) {
                        if (provBtn) provBtn.style.display = 'none';
                        provRows.forEach(row => {
                            const name = row.dataset.name.toLowerCase();
                            row.style.setProperty('display', name.includes(query) ? 'table-row' : 'none', 'important');
                        });
                    } else {
                        if (provBtn) provBtn.style.display = 'inline-flex';
                        provRows.forEach((row, index) => {
                            row.style.setProperty('display', (index < 12 || provExpanded) ? 'table-row' : 'none', 'important');
                        });
                    }
                }

                if (provSearch) provSearch.addEventListener('input', updateProvinces);
                if (provBtn) {
                    provBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        provExpanded = !provExpanded;
                        provBtn.innerHTML = provExpanded ? 'Ver menos provincias ↑' : 'Ver todas las provincias (+<?= count($provinces) - 12 ?>) ↓';
                        if (!provExpanded) document.getElementById('provincias-section').scrollIntoView({ behavior: 'smooth' });
                        updateProvinces();
                    });
                }

                // 2. CNAEs Accordion & Filter
                const cnaeSearch = document.getElementById('cnaeSearch');
                const cnaeRows = document.querySelectorAll('.cnae-row');
                const cnaeBtn = document.getElementById('viewMoreCnaes');
                let cnaeExpanded = false;

                function updateCnaes() {
                    const query = cnaeSearch.value.toLowerCase().trim();
                    if (query.length > 0) {
                        if (cnaeBtn) cnaeBtn.style.display = 'none';
                        cnaeRows.forEach(row => {
                            const name = row.dataset.name.toLowerCase();
                            row.style.setProperty('display', name.includes(query) ? 'table-row' : 'none', 'important');
                        });
                    } else {
                        if (cnaeBtn) cnaeBtn.style.display = 'inline-flex';
                        cnaeRows.forEach((row, index) => {
                            row.style.setProperty('display', (index < 12 || cnaeExpanded) ? 'table-row' : 'none', 'important');
                        });
                    }
                }

                if (cnaeSearch) cnaeSearch.addEventListener('input', updateCnaes);
                if (cnaeBtn) {
                    cnaeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        cnaeExpanded = !cnaeExpanded;
                        cnaeBtn.innerHTML = cnaeExpanded ? 'Ver menos sectores ↑' : 'Ver todos los sectores (+<?= count($cnaes) - 12 ?>) ↓';
                        if (!cnaeExpanded) document.getElementById('cnaes-section').scrollIntoView({ behavior: 'smooth' });
                        updateCnaes();
                    });
                }

                // Initial setup
                updateProvinces();
                updateCnaes();
            });
        </script>

<?= view('partials/footer') ?>
</body>
</html>
