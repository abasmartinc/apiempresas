<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical
    ]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-primary-soft: rgba(33, 82, 255, 0.08);
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
        .dir-hero::before { content: ''; position: absolute; top: -20%; right: -10%; width: 40%; height: 80%; background: radial-gradient(circle, rgba(33,82,255,0.14) 0%, transparent 70%); pointer-events: none; }
        .dir-hero::after  { content: ''; position: absolute; bottom: -10%; left: -5%; width: 35%; height: 60%; background: radial-gradient(circle, rgba(52,211,153,0.07) 0%, transparent 70%); pointer-events: none; }
        .dir-hero h1 { font-size: clamp(2.4rem, 4.5vw, 3.5rem); font-weight: 800; letter-spacing: -0.03em; color: #fff; margin-bottom: 1.25rem; line-height: 1.1; }
        .dir-hero .grad { background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .dir-hero p { font-size: 1.15rem; color: #cbd5e1; max-width: 650px; margin: 0 auto; line-height: 1.65; }

        /* ── LAYOUT ── */
        .dir-main { padding: 0; background: var(--dir-bg); }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        .dir-main-card {
            margin-top: -90px; position: relative; z-index: 10;
            background: #fff; border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.10), 0 1px 0 rgba(255,255,255,0.8) inset;
            padding: 48px; margin-bottom: 60px; border: 1px solid #e2e8f0;
        }

        /* ── BREADCRUMBS ── */
        .dir-breadcrumbs { font-size: 0.85rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; color: #94a3b8; justify-content: center; }
        .dir-breadcrumbs a { color: #60A5FA; text-decoration: none; transition: color 0.2s; }
        .dir-breadcrumbs a:hover { color: #fff; }

        /* ── STATS BAR ── */
        .dir-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: #e2e8f0; border-radius: 20px; overflow: hidden; margin-bottom: 56px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
        .dir-stat { background: #fff; padding: 24px 28px; display: flex; align-items: center; gap: 16px; transition: background 0.2s; }
        .dir-stat:hover { background: #fafbff; }
        .dir-stat__icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dir-stat__icon--blue  { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
        .dir-stat__icon--green { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
        .dir-stat__icon--purple{ background: linear-gradient(135deg, #faf5ff, #ede9fe); }
        .dir-stat__num { font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.04em; line-height: 1; }
        .dir-stat__label { font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 3px; }

        /* ── SECTION HEADER ── */
        .dir-section { margin-bottom: 0; }
        .section-header { display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.75rem; }
        .section-header__icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .section-header__icon--blue { background: linear-gradient(135deg, #2152FF, #3b82f6); }
        .section-header h2 { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.025em; }
        .section-header .line { height: 1px; background: linear-gradient(90deg, #e2e8f0, transparent); flex-grow: 1; }
        .section-header__count { font-size: 0.8rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 4px 12px; border-radius: 99px; white-space: nowrap; border: 1px solid #e2e8f0; }

        /* ── PREMIUM TABLE ── */
        .prem-table-wrap { background: #fff; border-radius: 20px; border: 1px solid #e9eef5; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .prem-table { width: 100%; min-width: 580px; border-collapse: collapse; font-size: 0.92rem; }
        .prem-table thead tr { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 2px solid #e9eef5; }
        .prem-table thead th { padding: 14px 20px; color: #475569; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; text-align: left; }
        .prem-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: all 0.18s ease; cursor: pointer; }
        .prem-table tbody tr:last-child { border-bottom: none; }
        .prem-table tbody tr:hover { background: #fafbff; box-shadow: inset 3px 0 0 #2152FF; }
        .prem-table td { padding: 14px 20px; vertical-align: middle; }

        .rank-badge { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 800; flex-shrink: 0; }
        .rank-1 { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .rank-2 { background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #475569; }
        .rank-3 { background: linear-gradient(135deg, #fff7ed, #fed7aa); color: #9a3412; }
        .rank-n { background: #f8fafc; color: #94a3b8; }

        .pbar-track { width: 140px; height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden; display: inline-block; }
        .pbar-fill { height: 100%; border-radius: 99px; transform: scaleX(0); transform-origin: left; animation: pbarGrow 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        .pbar-fill--blue { background: linear-gradient(90deg, #2152FF, #60a5fa); }
        @keyframes pbarGrow { from { transform: scaleX(0); } to { transform: scaleX(1); } }

        .cif-badge { display: inline-block; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; margin-top: 4px; border: 1px solid #e2e8f0; }
        .amount-badge { display: inline-block; background: #eff6ff; color: #1d4ed8; padding: 5px 14px; border-radius: 99px; font-weight: 800; font-size: 0.9rem; border: 1px solid #bfdbfe; white-space: nowrap; }

        .pagination-container { padding: 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; background: #f8fafc; border-radius: 0 0 20px 20px; }
        .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 6px; }
        .pagination li a, .pagination li span { display: flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.2s; }
        .pagination li a { background: #fff; color: #475569; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .pagination li a:hover { border-color: #2152FF; color: #2152FF; }
        .pagination li.active a, .pagination li.active span { background: #2152FF; color: #fff; border: 1px solid #2152FF; box-shadow: 0 2px 4px rgba(33,82,255,0.2); }
        .pagination li.disabled span { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; cursor: not-allowed; }

        @media (max-width: 768px) {
            .dir-hero { padding: 60px 1.5rem 120px; } .dir-hero h1 { font-size: 2.2rem; }
            .container { padding: 0 1.25rem; } .dir-main-card { padding: 24px 20px; margin-top: -70px; border-radius: 20px; }
            .dir-stats { grid-template-columns: 1fr; } .prem-table-wrap { overflow-x: auto; } .pbar-track { width: 80px; }
        }
    </style>
</head>
<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header') ?>

    <header class="dir-hero">
        <div class="container">
            <nav class="dir-breadcrumbs">
                <a href="<?= site_url('licitaciones-del-estado') ?>">Licitaciones del Estado</a>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                <span>Ranking de Contratistas</span>
            </nav>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(33, 82, 255, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px solid rgba(33, 82, 255, 0.25);">
                <span style="display: inline-block; width: 6px; height: 6px; background: #60A5FA; border-radius: 99px; box-shadow: 0 0 8px #60A5FA;"></span>
                Plataforma de Contratación del Sector Público
            </div>
            <h1>Mayores Contratistas del <span class="grad">Estado</span></h1>
            <p>Ranking oficial de empresas españolas ordenadas por volumen total adjudicado en contratación pública. Datos extraídos directamente de la Plataforma de Contratación del Sector Público.</p>

                <?php if (empty($searchQuery)): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $total_contracts = $billingService->countContracts([]);
                    $pricing = $billingService->getPublicFundsPricingDetails($total_contracts);
                    $dynamic_price = $pricing['base_price'];
                    $checkoutUrl = site_url('billing/contracts_checkout');
                ?>
                <div style="margin-top: 2rem;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2152FF; color: #fff; padding: 1.1rem 1.8rem; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 10px 30px rgba(33, 82, 255, 0.3); transition: all 0.2s; border: 1px solid #60A5FA;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 35px rgba(33, 82, 255, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(33, 82, 255, 0.3)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar CSV Completo — <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); max-width: 480px; margin-top: 10px; margin-left: auto; margin-right: auto; line-height: 1.4;">
                        Incluye todos los registros (<?= number_format($total_contracts, 0, ',', '.') ?>) cruzados con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
                <?php endif; ?>
        </div>
    </header>

    <main class="dir-main">
        <div class="container">
            <div class="dir-main-card">
                
                <form method="GET" action="<?= site_url('mayores-empresas-contratistas-del-estado') ?>" style="margin-bottom: 24px; position: relative;">
                    <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar por nombre de empresa o CIF..." style="width: 100%; padding: 1rem 1.25rem; padding-right: 7.5rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: 0.95rem; outline: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='#2152FF'; this.style.boxShadow='0 0 0 3px rgba(33,82,255,0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                    <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; border-radius: 8px; background: #2152FF; color: #fff; border: none; padding: 0 1.25rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Buscar
                    </button>
                </form>

                <?php if (!empty($searchQuery)): ?>
                <div style="margin-bottom: 32px; padding: 16px 24px; background: #eff6ff; border-radius: 14px; border: 1px solid #bfdbfe; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.95rem; color: #1d4ed8; font-weight: 700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -3px; margin-right: 4px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <?= number_format($total, 0, ',', '.') ?> empresa<?= $total !== 1 ? 's' : '' ?> para &ldquo;<?= esc($searchQuery) ?>&rdquo;
                    </span>
                    <a href="<?= site_url('mayores-empresas-contratistas-del-estado') ?>" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Limpiar
                    </a>
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="dir-stats">
                    <div class="dir-stat">
                        <div class="dir-stat__icon dir-stat__icon--blue">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <div class="dir-stat__num"><?= number_format($total, 0, ',', '.') ?></div>
                            <div class="dir-stat__label">Empresas Contratistas</div>
                        </div>
                    </div>
                    <div class="dir-stat">
                        <div class="dir-stat__icon dir-stat__icon--green">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <div>
                            <div class="dir-stat__num" style="font-size: 1.35rem;"><?= number_format($global_amount, 0, ',', '.') ?> €</div>
                            <div class="dir-stat__label">Volumen Total Adjudicado</div>
                        </div>
                    </div>
                    <div class="dir-stat">
                        <div class="dir-stat__icon dir-stat__icon--purple">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <div>
                            <div class="dir-stat__num"><?= number_format($global_contracts, 0, ',', '.') ?></div>
                            <div class="dir-stat__label">Contratos Públicos</div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <section class="dir-section">
                    <div class="section-header">
                        <div class="section-header__icon section-header__icon--blue">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        </div>
                        <h2>Ranking de Contratistas del Estado</h2>
                        <span class="section-header__count"><?= number_format($total, 0, ',', '.') ?> empresas</span>
                        <div class="line"></div>
                    </div>

                    <div class="prem-table-wrap">
                        <table class="prem-table">
                            <thead>
                                <tr>
                                    <th style="width: 48px; text-align: center;">#</th>
                                    <th>Empresa Adjudicataria</th>
                                    <th>Contratos</th>
                                    <th style="text-align: right;">Volumen Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($companies)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">No se encontraron resultados.</td></tr>
                            <?php else: ?>
                                <?php
                                $maxAmount = !empty($companies) ? (float)$companies[0]['total_amount'] : 1;
                                $count = (($currentPage ?? 1) - 1) * 50;
                                foreach($companies as $co):
                                    $count++;
                                    $pct = $maxAmount > 0 ? min(100, max(2, ($co['total_amount'] / $maxAmount) * 100)) : 2;
                                    $delay = (($count - 1) % 50) * 0.04;
                                    $rankClass = $count === 1 ? 'rank-1' : ($count === 2 ? 'rank-2' : ($count === 3 ? 'rank-3' : 'rank-n'));
                                    $hasCompany = !empty($co['company_name']);
                                    $cUrl = $hasCompany ? site_url($co['company_cif'] . '-' . url_title(str_replace(['º','ª'],['o','a'],$co['company_name']), '-', true)) : '#';
                                ?>
                                <tr<?= $hasCompany ? ' onclick="window.location=\'' . esc($cUrl) . '\'"' : ' style="cursor: default;"' ?>>
                                    <td style="text-align: center; padding-left: 16px;">
                                        <span class="rank-badge <?= $rankClass ?>"><?= $count <= 3 ? ['🥇','🥈','🥉'][$count-1] : $count ?></span>
                                    </td>
                                    <td>
                                        <?php if ($hasCompany): ?>
                                        <a href="<?= esc($cUrl) ?>" style="font-weight: 700; color: #0f172a; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#0f172a'">
                                            <?= esc($co['company_name']) ?>
                                        </a>
                                        <?php else: ?>
                                        <span style="font-weight: 700; color: #0f172a;">Empresa <?= esc($co['company_cif']) ?></span>
                                        <?php endif; ?>
                                        <span class="cif-badge"><?= esc($co['company_cif']) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 6px;"><?= number_format($co['total_contracts'], 0, ',', '.') ?></span>
                                        <div class="pbar-track"><div class="pbar-fill pbar-fill--blue" style="width: <?= $pct ?>%; animation-delay: <?= $delay ?>s;"></div></div>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="amount-badge"><?= number_format($co['total_amount'], 2, ',', '.') ?> €</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <?php if (empty($searchQuery)): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $total_contracts = $billingService->countContracts([]);
                    $pricing = $billingService->getPublicFundsPricingDetails($total_contracts);
                    $dynamic_price = $pricing['base_price'];
                    $checkoutUrl = site_url('billing/contracts_checkout');
                ?>
                <div style="padding: 24px; text-align: right; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 16px 16px;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2152FF; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; box-shadow: 0 8px 20px rgba(33, 82, 255, 0.25); transition: all 0.2s; margin-bottom: 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(33, 82, 255, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(33, 82, 255, 0.25)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar BBDD Completa de Contratos — <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.75rem; color: #94a3b8; max-width: 320px; margin-left: auto; line-height: 1.4;">
                        Incluye todos los contratos (<?= number_format($total_contracts, 0, ',', '.') ?> registros) cruzados con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
                <?php endif; ?>

            <?php if ($pager): ?>
                <div class="pagination-container"><?= $pager ?></div>
                <?php endif; ?>

                <!-- SEO Content / FAQs -->
                <section style="margin-top: 48px; padding-top: 40px; border-top: 1px solid #e2e8f0;">
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; letter-spacing: -0.025em;">Preguntas Frecuentes sobre el Ranking de Contratistas</h2>
                    <div style="display: grid; gap: 24px;">
                        <div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">¿Cómo se calcula este ranking de empresas?</h3>
                            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0;">El ranking se calcula sumando el importe total de adjudicación de todos los contratos públicos ganados por cada empresa (CIF). Posicionamos en los primeros lugares a aquellas entidades que mayor volumen de capital público han acumulado.</p>
                        </div>
                        <div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">¿De dónde provienen estos datos oficiales?</h3>
                            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0;">Toda la información se extrae de forma directa y automatizada desde la Plataforma de Contratación del Sector Público (PLACSP). Garantizamos total transparencia al mostrar datos agregados de acceso público.</p>
                        </div>
                        <div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">¿Cada cuánto tiempo se actualizan las adjudicaciones?</h3>
                            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0;">Nuestras tablas resumen se mantienen sincronizadas de forma recurrente. A medida que el Estado publica nuevas adjudicaciones en los boletines oficiales, actualizamos los contadores y las posiciones del ranking.</p>
                        </div>
                    </div>
                </section>
                <!-- /SEO Content -->

            </div>
        </div>
    </main>

    <!-- JSON-LD FAQ Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [{
        "@type": "Question",
        "name": "¿Cómo se calcula este ranking de empresas?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "El ranking se calcula sumando el importe total de adjudicación de todos los contratos públicos ganados por cada empresa (CIF). Posicionamos en los primeros lugares a aquellas entidades que mayor volumen de capital público han acumulado."
        }
      },{
        "@type": "Question",
        "name": "¿De dónde provienen estos datos oficiales?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Toda la información se extrae de forma directa y automatizada desde la Plataforma de Contratación del Sector Público (PLACSP)."
        }
      },{
        "@type": "Question",
        "name": "¿Cada cuánto tiempo se actualizan las adjudicaciones?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Nuestras tablas resumen se mantienen sincronizadas de forma recurrente. A medida que el Estado publica nuevas adjudicaciones, actualizamos los contadores."
        }
      }]
    }
    </script>

    <?= view('partials/footer') ?>
</body>
</html>
