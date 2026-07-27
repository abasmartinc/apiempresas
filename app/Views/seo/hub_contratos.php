<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => $title,
        'excerptText' => $meta_description,
        'canonical' => $canonical
    ]) ?>
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
            padding: 48px 0 120px;
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
            background: radial-gradient(circle, rgba(33,82,255,0.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero::after {
            content: '';
            position: absolute;
            bottom: -10%; left: -5%;
            width: 35%; height: 60%;
            background: radial-gradient(circle, rgba(52,211,153,0.09) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero h1 {
            font-size: clamp(2.6rem, 5vw, 4rem);
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #ffffff;
            margin-bottom: 1rem;
            line-height: 1.05;
        }
        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dir-hero p {
            font-size: 1.1rem;
            color: #94a3b8;
            max-width: 580px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        /* ── LAYOUT ── */
        .dir-main { padding: 0; background: var(--dir-bg); }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; }

        .dir-main-card {
            margin-top: -90px;
            position: relative;
            z-index: 10;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.10), 0 1px 0 rgba(255,255,255,0.8) inset;
            padding: 48px 16px;
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
            margin-bottom: 0;
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
            font-size: 2rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.05em;
            line-height: 1;
        }
        .dir-stat__label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 4px;
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
        .section-header__icon--blue  { background: linear-gradient(135deg, #2152FF, #3b82f6); }
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
            padding: 14px 10px;
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
        }
        .prem-table tbody tr:last-child { border-bottom: none; }
        .prem-table tbody tr:hover {
            background: #fafbff;
            box-shadow: inset 3px 0 0 #2152FF;
        }
        .prem-table td { padding: 14px 10px; vertical-align: middle; }

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
        .pbar-fill--blue { background: linear-gradient(90deg, #2152FF, #60a5fa); }
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

        /* quick links box */
        .quick-links-box {
            display: flex;
            flex-wrap: nowrap;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #e9eef5;
            overflow-x: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .quick-links-box::-webkit-scrollbar {
            display: none;
        }
        .quick-links-box > div {
            flex-shrink: 0;
        }

        .pagination-container {
            padding: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
        }
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 6px;
        }
        .pagination li a, .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .pagination li a {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .pagination li a:hover {
            border-color: #2152FF;
            color: #2152FF;
        }
        .pagination li.active a, .pagination li.active span {
            background: #2152FF;
            color: #fff;
            border: 1px solid #2152FF;
            box-shadow: 0 2px 4px rgba(33,82,255,0.2);
        }
        .pagination li.disabled span {
            background: #f1f5f9;
            color: #94a3b8;
            border: 1px solid #e2e8f0;
            cursor: not-allowed;
        }

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
    .paywall-row {
        filter: blur(6px);
        position: relative;
    }

    .paywall-cta-banner {
        margin: 0;
        padding: 20px 28px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-top: 1px solid #bfdbfe;
        border-bottom: 1px solid #bfdbfe;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .paywall-cta-banner p {
        margin: 0;
        font-size: 0.92rem;
        color: #1e3a8a;
        font-weight: 700;
    }
    .paywall-cta-banner a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #2152FF;
        color: #fff;
        padding: 10px 22px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.9rem;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 4px 14px rgba(33,82,255,0.3);
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .paywall-cta-banner a:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(33,82,255,0.4);
    }

    /* hero checklist pills */
    .hero-pills {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 14px;
    }
    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: rgba(255,255,255,0.75);
        font-weight: 600;
    }
    .hero-pill svg { flex-shrink: 0; opacity: 0.8; }

    /* use-case cards hover */
    .use-case-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        transition: all 0.2s;
    }
    .use-case-card:hover {
        background: #fff;
        border-color: #bfdbfe;
        box-shadow: 0 4px 16px rgba(33,82,255,0.07);
        transform: translateY(-2px);
    }
</style>
</head>
<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header') ?>
    
    <header class="dir-hero">
        <div class="container">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(96,165,250,0.12); color: #60A5FA; padding: 6px 16px; border-radius: 99px; font-size: 0.8rem; font-weight: 700; margin-bottom: 1.25rem; border: 1px solid rgba(96,165,250,0.2); letter-spacing: 0.02em;">
                <span style="display: inline-block; width: 7px; height: 7px; background: #60A5FA; border-radius: 99px; box-shadow: 0 0 8px #60A5FA; animation: pulse 2s ease-in-out infinite;"></span>
                Plataforma de Contratación del Sector Público
            </div>
            <h1>Licitaciones del <span class="grad">Estado</span></h1>
            <p>Qué empresas ganan los contratos públicos en España. Por Ministerio, Ayuntamiento u Órgano — con datos de contacto del Registro Mercantil.</p>

                <?php 
                    $billingService = new \App\Services\BillingService();
                    $total_contracts = $billingService->countContracts([]);
                    $pricing = $billingService->getPublicFundsPricingDetails($total_contracts);
                    $dynamic_price = $pricing['base_price'];
                    $checkoutUrl = site_url('billing/contracts_checkout');
                ?>
                <div style="margin-top: 2rem; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; justify-content: center;">
                        <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #2152FF, #3b82f6); color: #fff; padding: 1rem 2rem; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 10px 30px rgba(33,82,255,0.35); transition: all 0.2s; border: 1px solid rgba(96,165,250,0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 14px 40px rgba(33,82,255,0.45)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(33,82,255,0.35)';">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Descargar base completa
                        </a>
                        <div style="text-align: left;">
                            <div style="font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: -0.04em; line-height: 1;"><?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="font-size: 0.75em; opacity: 0.5; margin-right: 4px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€</div>
                            <div style="font-size: 0.72rem; color: rgba(255,255,255,0.55); font-weight: 600; margin-top: 2px;">+ IVA · pago único</div>
                        </div>
                    </div>
                    <div class="hero-pills">
                        <span class="hero-pill">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <?= number_format($total_contracts, 0, ',', '.') ?> registros incluidos
                        </span>
                        <span style="color: rgba(255,255,255,0.2); font-size: 0.8rem;">|</span>
                        <span class="hero-pill">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Teléfono, CNAE, dirección, provincia
                        </span>
                        <span style="color: rgba(255,255,255,0.2); font-size: 0.8rem;">|</span>
                        <span class="hero-pill">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Descarga inmediata en CSV
                        </span>
                    </div>
                </div>
        </div>
    </header>

    <main class="dir-main">
        <div class="container dir-main-card" style="padding: 0;">
            
            <?php if (!empty($searchQuery)): ?>
            <div style="padding: 24px 16px 0; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.25rem; margin: 0; color: #0f172a;">Resultados para: <span style="color: var(--dir-primary);">"<?= esc($searchQuery) ?>"</span></h2>
                <a href="<?= site_url('licitaciones-del-estado') ?>" style="color: #64748b; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Limpiar búsqueda
                </a>
            </div>
            <?php endif; ?>

            <!-- ── PARA QUIÉN ES ESTO ── -->
            <?php if (empty($searchQuery)): ?>
            <div style="padding: 28px 28px 24px; border-bottom: 1px solid #f1f5f9;">
                <p style="font-size: 0.72rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="#94a3b8"><circle cx="12" cy="12" r="10"/></svg>
                    ¿Quién compra esto?
                </p>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">

                    <div class="use-case-card">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 5px;">Proveedores de la Administración</div>
                            <div style="font-size: 0.78rem; color: #64748b; line-height: 1.45;">Identifican qué empresas trabajan con Ministerios y Ayuntamientos para venderles como subcontrata o proveedor.</div>
                        </div>
                    </div>

                    <div class="use-case-card">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #faf5ff, #ede9fe); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 5px;">Análisis de Mercado</div>
                            <div style="font-size: 0.78rem; color: #64748b; line-height: 1.45;">Analizan qué empresas acumulan más adjudicaciones y en qué sectores concentra su actividad la Administración.</div>
                        </div>
                    </div>

                    <div class="use-case-card">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 5px;">Prospección B2B</div>
                            <div style="font-size: 0.78rem; color: #64748b; line-height: 1.45;">Construyen listas de empresas con experiencia en contratación pública para campañas comerciales.</div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endif; ?>

            <div style="padding: 0 12px 48px;">
            <!-- ── STATS BAR ── -->
            <div class="dir-stats">
                <div class="dir-stat">
                    <div class="dir-stat__icon dir-stat__icon--blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div>
                        <div class="dir-stat__num"><?= number_format($total_organs, 0, ',', '.') ?></div>
                        <div class="dir-stat__label">Órganos Oficiales</div>
                    </div>
                </div>
                <div class="dir-stat">
                    <div class="dir-stat__icon dir-stat__icon--green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <div class="dir-stat__num" style="font-size: 1.4rem;"><?= number_format($global_amount, 2, ',', '.') ?> €</div>
                        <div class="dir-stat__label">Volumen Adjudicado</div>
                    </div>
                </div>
                <div class="dir-stat">
                    <div class="dir-stat__icon dir-stat__icon--purple">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div>
                        <div class="dir-stat__num"><?= number_format($global_contracts, 0, ',', '.') ?></div>
                        <div class="dir-stat__label">Contratos Públicos</div>
                    </div>
                </div>
            </div>

            <!-- ── CONVOCATORIAS ── -->
            <section class="dir-section">
                <div class="section-header" style="margin-bottom: 24px;">
                    <div class="section-header__icon section-header__icon--blue">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <h2>Órganos de Contratación</h2>
                    <div class="line"></div>
                </div>

                <!-- Toolbar: Filters & Pagination -->
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e9eef5;">
                    <form method="GET" action="<?= site_url('licitaciones-del-estado') ?>" style="margin-bottom: 0px; position: relative; width: 100%;">
                        <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar por Ministerio, Ayuntamiento, Consejería..." style="width: 100%; padding: 1rem 1.25rem; padding-right: 7.5rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: 0.95rem; outline: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='#2152FF'; this.style.boxShadow='0 0 0 3px rgba(33,82,255,0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                        <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; border-radius: 8px; background: #2152FF; color: #fff; border: none; padding: 0 1.25rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            Buscar
                        </button>
                    </form>
                    
                    <div style="height: 1px; background: #e2e8f0; width: 100%;"></div>

                    <!-- Top Row: Tags -->
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                        <span style="font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 4px;">Accesos Rápidos:</span>
                        <a href="<?= site_url('mayores-empresas-contratistas-del-estado') ?>" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#0f172a; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#2152FF'; this.style.color='#2152FF'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#0f172a'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            Ranking Top Empresas
                        </a>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach ([2020, 2021, 2022, 2023, 2024, 2025, 2026] as $yr): ?>
                            <a href="<?= site_url('licitaciones-del-estado/ano-' . $yr) ?>" style="display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#475569; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#2152FF'; this.style.color='#2152FF'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'">
                                <?= $yr ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (isset($pager) && $pager): ?>
                    <!-- Divider -->
                    <div style="height: 1px; background: #e2e8f0; width: 100%;"></div>
                    
                    <!-- Bottom Row: Pagination -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Navegación de resultados</span>
                        <div style="margin: 0; padding: 0;">
                            <?= str_replace('<ul class="pagination"', '<ul class="pagination" style="margin:0;"', $pager) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="prem-table-wrap">
                    <table class="prem-table">
                        <thead>
                            <tr>
                                <th style="width:48px; text-align:center;">#</th>
                                <th style="width: 55%;">Órgano de Contratación</th>
                                <th style="width: 30%;">Volumen de Contratos</th>
                                <th style="width: 15%; text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                                 <?php if (empty($organs)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 48px;">No hay licitaciones registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $count = (($currentPage ?? 1) - 1) * 50;
                                $ctaBannerShown = false;
                                foreach($organs as $organ):
                                    $count++;
                                    $rowNum = ($rowNum ?? 0) + 1;
                                    $pct = min(100, max(2, ($organ['total_contracts'] / $max_contracts) * 100));
                                    $delay = (($count - 1) % 50) * 0.04;
                                    $rankClass = $count === 1 ? 'rank-1' : ($count === 2 ? 'rank-2' : ($count === 3 ? 'rank-3' : 'rank-n'));
                                ?>
                                <?php if ($rowNum === 11 && !$ctaBannerShown && empty($searchQuery)): $ctaBannerShown = true; ?>
                                <tr>
                                    <td colspan="4" style="padding: 0;">
                                        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #2152FF 100%); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                                            <div>
                                                <p style="margin: 0 0 3px; font-size: 0.9rem; color: rgba(255,255,255,0.9); font-weight: 800;">📥 Descarga las empresas de todos estos órganos</p>
                                                <p style="margin: 0; font-size: 0.78rem; color: rgba(255,255,255,0.65); font-weight: 600;">CSV con nombre, CIF, teléfono, CNAE, dirección y provincia · Del Registro Mercantil</p>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 14px; flex-shrink: 0;">
                                                <div style="text-align: right;">
                                                    <div style="font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: -0.03em;"><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">+ IVA</span></div>
                                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.6); font-weight: 600;">pago único</div>
                                                </div>
                                                <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #fff; color: #2152FF; padding: 11px 22px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; white-space: nowrap; box-shadow: 0 4px 14px rgba(0,0,0,0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.25)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.2)'">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    Descargar CSV
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr onclick="window.location='<?= site_url('licitaciones-del-estado/organo-' . esc($organ['slug'])) ?>'" style="cursor: pointer;">
                                    <td style="text-align:center; padding-left:16px;">
                                        <span class="rank-badge <?= $rankClass ?>">
                                            <?= $count <= 3 ? ['🥇','🥈','🥉'][$count-1] : $count ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('licitaciones-del-estado/organo-' . esc($organ['slug'])) ?>" style="font-weight:700; color:#0f172a; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#0f172a'">
                                            <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 100%;" title="<?= esc($organ['name']) ?>">
                                                <?= esc(mb_convert_case($organ['name'], MB_CASE_TITLE, "UTF-8")) ?>
                                            </div>
                                        </a>
                                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 6px; display: flex; gap: 12px;">
                                            <span><strong><?= number_format($organ['total_amount'], 2, ',', '.') ?> €</strong> adjudicados a empresas</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:14px;">
                                            <span style="font-weight:800; color:#0f172a; min-width:85px; font-size:0.9rem; letter-spacing:-0.01em;"><?= number_format($organ['total_contracts'], 0, ',', '.') ?></span>
                                            <div class="pbar-track">
                                                <div class="pbar-fill pbar-fill--blue" style="width:<?= $pct ?>%; animation-delay:<?= $delay ?>s;"></div>
                                            </div>
                                            <span style="font-size:0.78rem; color:#94a3b8; font-weight:600; min-width:36px;"><?= round($pct) ?>%</span>
                                        </div>
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="<?= site_url('licitaciones-del-estado/organo-' . esc($organ['slug'])) ?>" class="action-link action-link--blue">
                                            Ver adjudicaciones
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
<?php if ($total_organs > 10): ?>
<div class="paywall-cta-banner">
    <div>
        <p>📥 <strong><?= number_format($total_organs - 10, 0, ',', '.') ?> órganos más</strong> disponibles en el directorio. Descarga la base completa de empresas adjudicatarias con datos de contacto del Registro Mercantil.</p>
    </div>
    <a href="<?= $checkoutUrl ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Descargar CSV — <?= number_format($dynamic_price, 2, ',', '') ?>€ + IVA
    </a>
</div>
<?php endif; ?>
            <!-- Toolbar: Filters & Pagination BOTTOM -->
            <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 0; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e9eef5;">
                
                <?php if (empty($searchQuery)): ?>
                <div style="text-align: center; margin-bottom: 16px;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #2152FF, #3b82f6); color: #fff; padding: 14px 32px; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 8px 24px rgba(33, 82, 255, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 32px rgba(33, 82, 255, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 24px rgba(33, 82, 255, 0.3)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar Base Completa de Contratos — <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:4px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 10px; line-height: 1.4;">
                        <?= number_format($total_contracts, 0, ',', '.') ?> registros · Teléfono, CNAE, dirección y provincia incluidos
                    </div>
                </div>
                
                <div style="height: 1px; background: #e2e8f0; width: 100%;"></div>
                <?php endif; ?>

                <!-- Top Row: Tags -->
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <span style="font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 4px;">Accesos Rápidos:</span>
                    <a href="<?= site_url('mayores-empresas-contratistas-del-estado') ?>" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#0f172a; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#2152FF'; this.style.color='#2152FF'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#0f172a'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        Ranking Top Empresas
                    </a>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ([2020, 2021, 2022, 2023, 2024, 2025, 2026] as $yr): ?>
                        <a href="<?= site_url('licitaciones-del-estado/ano-' . $yr) ?>" style="display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#475569; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#2152FF'; this.style.color='#2152FF'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'">
                            <?= $yr ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (isset($pager) && $pager): ?>
                <!-- Divider -->
                <div style="height: 1px; background: #e2e8f0; width: 100%;"></div>
                
                <!-- Bottom Row: Pagination -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Navegación de resultados</span>
                    <div style="margin: 0; padding: 0;">
                        <?= str_replace('<ul class="pagination"', '<ul class="pagination" style="margin:0;"', $pager) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            </section>

            </div> <!-- padding 48px -->

            <!-- ── SIGUIENTE PASO: CSV vs RADAR (hub) ── -->
            <?php if (empty($searchQuery)): ?>
            <div style="padding: 32px 32px 40px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-top: 1px solid #e2e8f0;">
                <p style="text-align: center; font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 8px;">¿Qué quieres hacer ahora?</p>
                <p style="text-align: center; font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 32px; letter-spacing: -0.02em;">Elige cómo usar estos datos</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; max-width: 800px; margin: 0 auto;">

                    <!-- OPCIÓN A: Descarga única -->
                    <div style="background: #fff; border: 2px solid #2152FF; border-radius: 18px; padding: 24px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 4px 16px rgba(33,82,255,0.08);">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; white-space: nowrap;">Descargar listado</div>
                            </div>
                            <span style="background: #2152FF; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Pago único</span>
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">Exporta el CSV completo de empresas adjudicatarias con datos de contacto: teléfono, CNAE, dirección y provincia. Listo para tu CRM.</p>
                        <div style="margin-top: 4px;">
                            <a href="<?= $checkoutUrl ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #2152FF; color: #fff; padding: 12px 16px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; box-shadow: 0 4px 12px rgba(33,82,255,0.3); transition: all 0.2s; width: 100%;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Descargar CSV completo
                            </a>
                            <div style="text-align: center; margin-top: 8px; font-size: 0.82rem; font-weight: 800; color: #2152FF; letter-spacing: -0.01em;"><?= number_format($dynamic_price, 2, ',', '') ?>€ + IVA &nbsp;·&nbsp; <span style="font-weight: 600; color: #64748b;">pago único</span></div>
                        </div>
                    </div>

                    <!-- OPCIÓN B: Radar -->
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1.5px solid #86efac; border-radius: 18px; padding: 24px; display: flex; flex-direction: column; gap: 12px; position: relative;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; background: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(16,185,129,0.2);">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                </div>
                                <div style="font-weight: 800; color: #065f46; font-size: 0.9rem; white-space: nowrap;">Prospección continua</div>
                            </div>
                            <span style="background: #10b981; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em;">Diario</span>
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: #065f46; line-height: 1.5; opacity: 0.85;">Detecta cada día empresas recién constituidas antes que tu competencia, con scoring de IA y CRM integrado.</p>
                        <div style="margin-top: 4px;">
                            <a href="<?= site_url('leads-empresas-nuevas') ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #10b981; color: #fff; padding: 12px 16px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); transition: all 0.2s; width: 100%;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                Conocer el Radar
                            </a>
                            <div style="text-align: center; margin-top: 8px; font-size: 0.82rem; font-weight: 800; color: #10b981; letter-spacing: -0.01em;">79,00€ / mes &nbsp;·&nbsp; <span style="font-weight: 600; color: #065f46; opacity: 0.7;">sin permanencia</span></div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endif; ?>

            <!-- SEO Content & FAQs -->
            <div style="padding: 48px; background: #fff;">
                <div style="max-width: 800px; margin: 0 auto;">
                    <h2 style="font-size: 1.5rem; color: #0f172a; margin-bottom: 24px;">Directorio de Licitaciones y Contratos Públicos en España</h2>
                    <div style="color: #475569; font-size: 1rem; line-height: 1.7; margin-bottom: 32px;">
                        <p style="margin-bottom: 16px;">
                            El buscador de <strong>licitaciones y contratos del Estado</strong> de APIEmpresas recopila toda la información sobre adjudicaciones públicas de la Administración General del Estado, Comunidades Autónomas y Entidades Locales.
                        </p>
                        <p style="margin-bottom: 16px;">
                            Nuestro directorio se nutre de los datos de la <strong>Plataforma de Contratación del Sector Público (PLACSP)</strong>. Te permitimos consultar el listado completo de órganos de contratación (ayuntamientos, consejerías, ministerios) y ver qué empresas están ganando los concursos públicos, por qué importe y en qué fechas.
                        </p>
                        <p>
                            Conocer a dónde va el dinero público a través de la contratación estatal es esencial. Nuestro objetivo es aportar inteligencia de negocio (Business Intelligence) y transparencia para que conozcas las dinámicas del mercado de compra pública.
                        </p>
                    </div>

                    <h3 style="font-size: 1.3rem; color: #0f172a; margin-bottom: 24px;">Preguntas Frecuentes (FAQ)</h3>
                    
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; margin-bottom: 16px;">
                        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h4 style="margin: 0; font-size: 1.05rem; color: #0f172a; display: flex; gap: 12px; align-items: flex-start;">
                                <span style="color: #2152FF; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                ¿Cuál es el origen de estos contratos públicos?
                            </h4>
                        </div>
                        <div style="padding: 20px; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                            La fuente oficial de los datos es la Plataforma de Contratación del Sector Público (PLACSP), gestionada por el Ministerio de Hacienda, que centraliza todas las licitaciones públicas de España.
                        </div>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; margin-bottom: 16px;">
                        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h4 style="margin: 0; font-size: 1.05rem; color: #0f172a; display: flex; gap: 12px; align-items: flex-start;">
                                <span style="color: #2152FF; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                ¿Qué tipos de licitaciones se incluyen?
                            </h4>
                        </div>
                        <div style="padding: 20px; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                            Se incluyen contratos de obras, suministros, servicios y concesiones adjudicados por cualquier organismo del sector público (Ministerios, Ayuntamientos, Diputaciones, Empresas Públicas, etc.).
                        </div>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;">
                        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h4 style="margin: 0; font-size: 1.05rem; color: #0f172a; display: flex; gap: 12px; align-items: flex-start;">
                                <span style="color: #2152FF; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                ¿Incluye contratos menores?
                            </h4>
                        </div>
                        <div style="padding: 20px; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                            Sí, se incluyen los contratos menores adjudicados directamente (obras de valor inferior a 40.000 euros, y servicios/suministros de valor inferior a 15.000 euros), siempre y cuando la administración contratante los publique de acuerdo a la Ley de Contratos del Sector Público.
                        </div>
                    </div>
                </div>
            </div>
            
            <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "FAQPage",
              "mainEntity": [{
                "@type": "Question",
                "name": "¿Cuál es el origen de estos contratos públicos?",
                "acceptedAnswer": {
                  "@type": "Answer",
                  "text": "La fuente oficial de los datos es la Plataforma de Contratación del Sector Público (PLACSP), gestionada por el Ministerio de Hacienda."
                }
              },{
                "@type": "Question",
                "name": "¿Qué tipos de licitaciones se incluyen?",
                "acceptedAnswer": {
                  "@type": "Answer",
                  "text": "Se incluyen contratos de obras, suministros, servicios y concesiones adjudicados por cualquier organismo del sector público."
                }
              },{
                "@type": "Question",
                "name": "¿Incluye contratos menores?",
                "acceptedAnswer": {
                  "@type": "Answer",
                  "text": "Sí, se incluyen los contratos menores adjudicados directamente, siempre y cuando la administración contratante los publique."
                }
              }]
            }
            </script>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
