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
        .section-header__icon--green  { background: linear-gradient(135deg, #10b981, #059669); }
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
            box-shadow: inset 3px 0 0 #10b981;
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
            border-color: #10b981;
            color: #10b981;
        }
        .pagination li.active a, .pagination li.active span {
            background: #10b981;
            color: #fff;
            border: 1px solid #10b981;
            box-shadow: 0 2px 4px rgba(16,185,129,0.2);
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
    </style>
</head>
<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header') ?>
    
    <header class="dir-hero">
        <div class="container">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(16, 185, 129, 0.15); color: #34D399; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.25);">
                <span style="display: inline-block; width: 6px; height: 6px; background: #34D399; border-radius: 99px; box-shadow: 0 0 8px #34D399;"></span>
                Registros Oficiales del Estado
            </div>
            <h1>Convocatorias de <span class="grad">Subvenciones</span></h1>
            <p>Directorio oficial de convocatorias públicas. Selecciona una convocatoria para conocer el listado de empresas beneficiarias, importes y fechas de concesión.</p>

                <?php 
                    $billingService = new \App\Services\BillingService();
                    $total_subs = $billingService->countSubsidies([]);
                    $pricing = $billingService->getPublicFundsPricingDetails($total_subs);
                    $dynamic_price = $pricing['base_price'];
                    $checkoutUrl = site_url('billing/subsidies_checkout');
                ?>
                <div style="margin-top: 2rem;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: #fff; padding: 1.1rem 1.8rem; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3); transition: all 0.2s; border: 1px solid #34d399;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 35px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(16, 185, 129, 0.3)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar Base Completa de Subvenciones — <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); max-width: 480px; margin-top: 10px; margin-left: auto; margin-right: auto; line-height: 1.4;">
                        Incluye todas las subvenciones (<?= number_format($total_subs, 0, ',', '.') ?> registros) cruzadas con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
        </div>
    </header>

    <main class="dir-main">
        <div class="container dir-main-card" style="padding: 0;">
            
            <?php if (!empty($searchQuery)): ?>
            <div style="padding: 24px 16px 0; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.25rem; margin: 0; color: #0f172a;">Resultados para: <span style="color: var(--dir-primary);">"<?= esc($searchQuery) ?>"</span></h2>
                <a href="<?= site_url('subvenciones-empresas') ?>" style="color: #64748b; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Limpiar búsqueda
                </a>
            </div>
            <?php endif; ?>

            <div style="padding: 48px 12px;">
            <!-- ── STATS BAR ── -->
            <div class="dir-stats">
                <div class="dir-stat">
                    <div class="dir-stat__icon dir-stat__icon--blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div>
                        <div class="dir-stat__num"><?= number_format($total_convocatorias, 0, ',', '.') ?></div>
                        <div class="dir-stat__label">Convocatorias</div>
                    </div>
                </div>
                <div class="dir-stat">
                    <div class="dir-stat__icon dir-stat__icon--green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <div class="dir-stat__num" style="font-size: 1.4rem;"><?= number_format($global_amount, 2, ',', '.') ?> €</div>
                        <div class="dir-stat__label">Volumen Concedido</div>
                    </div>
                </div>
                <div class="dir-stat">
                    <div class="dir-stat__icon dir-stat__icon--purple">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <div class="dir-stat__num"><?= number_format($global_subsidies, 0, ',', '.') ?></div>
                        <div class="dir-stat__label">Subvenciones Públicas</div>
                    </div>
                </div>
            </div>

            <!-- ── CONVOCATORIAS ── -->
            <section class="dir-section">
                <div class="section-header" style="margin-bottom: 24px;">
                    <div class="section-header__icon section-header__icon--green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <h2>Convocatorias de Ayudas y Subvenciones</h2>
                    <div class="line"></div>
                </div>

                <!-- Toolbar: Filters & Pagination -->
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e9eef5;">
                    <form method="GET" action="<?= site_url('subvenciones-empresas') ?>" style="margin-bottom: 0px; position: relative; width: 100%;">
                        <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar por nombre de convocatoria o ministerio..." style="width: 100%; padding: 1rem 1.25rem; padding-right: 7.5rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: 0.95rem; outline: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                        <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; border-radius: 8px; background: #10b981; color: #fff; border: none; padding: 0 1.25rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            Buscar
                        </button>
                    </form>
                    
                    <div style="height: 1px; background: #e2e8f0; width: 100%;"></div>

                    <!-- Top Row: Tags -->
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                        <span style="font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 4px;">Accesos Rápidos:</span>
                        <a href="<?= site_url('empresas-mas-subvencionadas-espana') ?>" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#0f172a; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#10b981'; this.style.color='#10b981'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#0f172a'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            Ranking Top Empresas
                        </a>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach ([2022, 2023, 2024, 2025, 2026] as $yr): ?>
                            <a href="<?= site_url('subvenciones-empresas/ano-' . $yr) ?>" style="display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#475569; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#10b981'; this.style.color='#10b981'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'">
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
                                <th style="width: 55%;">Convocatoria</th>
                                <th style="width: 30%;">Volumen de Ayudas</th>
                                <th style="width: 15%; text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($convocatorias)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 48px;">No hay convocatorias registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $count = (($currentPage ?? 1) - 1) * 50;
                                foreach($convocatorias as $conv):
                                    $count++;
                                    $pct = min(100, max(2, ($conv['total_subsidies'] / $max_subsidies) * 100));
                                    $delay = (($count - 1) % 50) * 0.04;
                                    $rankClass = $count === 1 ? 'rank-1' : ($count === 2 ? 'rank-2' : ($count === 3 ? 'rank-3' : 'rank-n'));
                                ?>
                                <tr onclick="window.location='<?= site_url('subvenciones-empresas/convocatoria-' . esc($conv['slug'])) ?>'">
                                    <td style="text-align:center; padding-left:16px;">
                                        <span class="rank-badge <?= $rankClass ?>">
                                            <?= $count <= 3 ? ['🥇','🥈','🥉'][$count-1] : $count ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('subvenciones-empresas/convocatoria-' . esc($conv['slug'])) ?>" style="font-weight:700; color:#0f172a; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:color 0.2s;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#0f172a'">
                                            <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 100%;" title="<?= esc($conv['name']) ?>">
                                                <?= esc(mb_convert_case($conv['name'], MB_CASE_TITLE, "UTF-8")) ?>
                                            </div>
                                        </a>
                                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 6px; display: flex; gap: 12px;">
                                            <span><strong><?= number_format($conv['total_amount'], 2, ',', '.') ?> €</strong> adjudicados</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:14px;">
                                            <span style="font-weight:800; color:#0f172a; min-width:85px; font-size:0.9rem; letter-spacing:-0.01em;"><?= number_format($conv['total_subsidies'], 0, ',', '.') ?></span>
                                            <div class="pbar-track">
                                                <div class="pbar-fill pbar-fill--green" style="width:<?= $pct ?>%; animation-delay:<?= $delay ?>s;"></div>
                                            </div>
                                            <span style="font-size:0.78rem; color:#94a3b8; font-weight:600; min-width:36px;"><?= round($pct) ?>%</span>
                                        </div>
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="<?= site_url('subvenciones-empresas/convocatoria-' . esc($conv['slug'])) ?>" class="action-link action-link--green">
                                            Ver empresas
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <!-- Toolbar: Filters & Pagination BOTTOM -->
            <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 0; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e9eef5;">
                
                <?php if (empty($searchQuery)): ?>
                <div style="text-align: right; margin-bottom: 0px;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25); transition: all 0.2s; margin-bottom: 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(16, 185, 129, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(16, 185, 129, 0.25)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar Base Completa de Subvenciones — <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.75rem; color: #94a3b8; max-width: 320px; margin-left: auto; line-height: 1.4;">
                        Incluye todas las subvenciones (<?= number_format($total_subs, 0, ',', '.') ?> registros) cruzadas con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
                
                <div style="height: 1px; background: #e2e8f0; width: 100%;"></div>
                <?php endif; ?>

                <!-- Top Row: Tags -->
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <span style="font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 4px;">Accesos Rápidos:</span>
                    <a href="<?= site_url('empresas-mas-subvencionadas-espana') ?>" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#0f172a; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#10b981'; this.style.color='#10b981'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#0f172a'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        Ranking Top Empresas
                    </a>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ([2022, 2023, 2024, 2025, 2026] as $yr): ?>
                        <a href="<?= site_url('subvenciones-empresas/ano-' . $yr) ?>" style="display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 12px; color:#475569; text-decoration:none; font-weight:700; font-size:0.85rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#10b981'; this.style.color='#10b981'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'">
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

            <!-- SEO Content & FAQs -->
            <div style="padding: 48px; background: #fff;">
                <div style="max-width: 800px; margin: 0 auto;">
                    <h2 style="font-size: 1.5rem; color: #0f172a; margin-bottom: 24px;">Directorio Oficial de Subvenciones en España</h2>
                    <div style="color: #475569; font-size: 1rem; line-height: 1.7; margin-bottom: 32px;">
                        <p style="margin-bottom: 16px;">
                            El buscador de <strong>subvenciones a empresas</strong> de APIEmpresas recopila, organiza y facilita el acceso a la información de todas las convocatorias de ayudas públicas gestionadas en España.
                        </p>
                        <p style="margin-bottom: 16px;">
                            Nuestro directorio se actualiza diariamente con los datos procedentes del <strong>Sistema Nacional de Publicidad de Subvenciones y Ayudas Públicas (SNPSAP)</strong>. Gracias a esto, puedes buscar cualquier convocatoria (desde el Kit Digital hasta los grandes fondos Next Generation o PERTEs) y ver qué empresas han sido adjudicatarias, las cuantías y las fechas de concesión.
                        </p>
                        <p>
                            La transparencia en el reparto de los fondos públicos es fundamental, y nuestro objetivo es proporcionar una herramienta ágil y accesible para investigadores, periodistas, competencia y ciudadanos.
                        </p>
                    </div>

                    <h3 style="font-size: 1.3rem; color: #0f172a; margin-bottom: 24px;">Preguntas Frecuentes (FAQ)</h3>
                    
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; margin-bottom: 16px;">
                        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h4 style="margin: 0; font-size: 1.05rem; color: #0f172a; display: flex; gap: 12px; align-items: flex-start;">
                                <span style="color: #10b981; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                ¿De dónde proceden los datos de subvenciones?
                            </h4>
                        </div>
                        <div style="padding: 20px; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                            Todos los datos provienen del Sistema Nacional de Publicidad de Subvenciones y Ayudas Públicas, la base de datos oficial del Gobierno de España donde las administraciones (estatal, autonómica y local) están obligadas a reportar las concesiones.
                        </div>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; margin-bottom: 16px;">
                        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h4 style="margin: 0; font-size: 1.05rem; color: #0f172a; display: flex; gap: 12px; align-items: flex-start;">
                                <span style="color: #10b981; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                ¿Están incluidas las ayudas europeas (Fondos Next Generation EU)?
                            </h4>
                        </div>
                        <div style="padding: 20px; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                            Sí. Las convocatorias financiadas con el Mecanismo de Recuperación y Resiliencia (MRR), como por ejemplo el Kit Digital o los PERTE (Proyectos Estratégicos para la Recuperación y Transformación Económica), se gestionan a través de los ministerios y organismos españoles, por lo que están registradas y publicadas en nuestra base de datos.
                        </div>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;">
                        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h4 style="margin: 0; font-size: 1.05rem; color: #0f172a; display: flex; gap: 12px; align-items: flex-start;">
                                <span style="color: #10b981; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                                ¿Con qué frecuencia se actualiza el listado?
                            </h4>
                        </div>
                        <div style="padding: 20px; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                            Nuestro sistema sincroniza diariamente las nuevas concesiones publicadas. Las estadísticas globales de las convocatorias más subvencionadas se recalculan de forma automática cada madrugada para garantizar la máxima exactitud.
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
                "name": "¿De dónde proceden los datos de subvenciones?",
                "acceptedAnswer": {
                  "@type": "Answer",
                  "text": "Todos los datos provienen del Sistema Nacional de Publicidad de Subvenciones y Ayudas Públicas, la base de datos oficial del Gobierno de España."
                }
              },{
                "@type": "Question",
                "name": "¿Están incluidas las ayudas europeas (Fondos Next Generation EU)?",
                "acceptedAnswer": {
                  "@type": "Answer",
                  "text": "Sí. Las convocatorias financiadas con el Mecanismo de Recuperación y Resiliencia (MRR) están registradas y publicadas en nuestra base de datos."
                }
              },{
                "@type": "Question",
                "name": "¿Con qué frecuencia se actualiza el listado?",
                "acceptedAnswer": {
                  "@type": "Answer",
                  "text": "Nuestro sistema sincroniza diariamente las nuevas concesiones publicadas. Las estadísticas se recalculan automáticamente cada madrugada."
                }
              }]
            }
            </script>


        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
