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
        .dir-hero h1 {
            font-size: clamp(2.6rem, 5vw, 4rem);
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #ffffff;
            margin-bottom: 1rem;
            line-height: 1.05;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #2152FF 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dir-hero p {
            font-size: 1.1rem;
            color: #94a3b8;
            max-width: 800px;
            line-height: 1.6;
        }

        /* ── BREADCRUMBS ── */
        .dir-breadcrumbs {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
        }
        .dir-breadcrumbs a {
            color: #60A5FA;
            text-decoration: none;
            transition: color 0.2s;
        }
        .dir-breadcrumbs a:hover { color: #2152FF; }
        
        /* ── LAYOUT ── */
        .dir-main { padding: 0; background: var(--dir-bg); padding-bottom: 80px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

        .dir-main-card {
            margin-top: -60px;
            position: relative;
            z-index: 10;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.08), 0 1px 0 rgba(255,255,255,0.8) inset;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* ── PREMIUM TABLE ── */
        .prem-table-wrap {
            background: #fff;
            width: 100%;
            overflow-x: auto;
        }

        /* ── PAYWALL BLUR ── */
        .blurred-amount {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .blurred-amount .amount-text {
            filter: blur(5px);
            user-select: none;
            color: #059669;
            font-weight: 800;
            font-size: 0.95rem;
        }
        .blurred-amount .lock-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #2152FF, #3b82f6);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(33,82,255,0.3);
            transition: all 0.2s;
        }
        .blurred-amount .lock-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(33,82,255,0.4);
        }
        .paywall-row td { background: #fafbff !important; }
        .paywall-cta-banner {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 20px 28px;
            margin: 16px 24px 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .paywall-cta-banner p { margin: 0; font-size: 0.9rem; color: #1e3a8a; font-weight: 600; }
        .paywall-cta-banner a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #2152FF;
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.9rem;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(33,82,255,0.3);
            flex-shrink: 0;
        }
        .prem-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
            font-size: 0.92rem;
        }
        .prem-table thead tr {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e9eef5;
        }
        .prem-table thead th {
            padding: 16px 24px;
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
        .prem-table td { 
            padding: 16px 24px; 
            vertical-align: middle; 
            color: #0f172a;
        }
        
        .cif-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-top: 6px;
            border: 1px solid #e2e8f0;
        }
        
        .link-badge {
            display: inline-flex; 
            align-items: center; 
            gap: 4px; 
            background: #e0e7ff; 
            color: #3730a3; 
            padding: 4px 10px; 
            border-radius: 6px; 
            font-size: 0.8rem; 
            font-weight: 700;
            text-decoration: none;
            margin-top: 8px;
            transition: all 0.2s;
        }
        .link-badge:hover {
            background: #c7d2fe;
        }

        .amount-badge {
            display: inline-block; 
            background: #ecfdf5; 
            color: #059669; 
            padding: 6px 14px; 
            border-radius: 999px; 
            font-weight: 800; 
            font-size: 0.95rem; 
            border: 1px solid #a7f3d0;
        }

        /* ── PAGINATION ── */
        .pagination-container {
            padding: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
            background: #f8fafc;
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
            .dir-hero { padding: 30px 1.5rem 100px; }
            .dir-hero h1 { font-size: 1.8rem; }
            .container { padding: 0 1.25rem; }
            .dir-main-card { margin-top: -50px; border-radius: 16px; }
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
                <span>Órgano de Contratación</span>
            </nav>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(33, 82, 255, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.25rem; border: 1px solid rgba(33, 82, 255, 0.25);">
                <span style="display: inline-block; width: 6px; height: 6px; background: #60A5FA; border-radius: 99px; box-shadow: 0 0 8px #60A5FA;"></span>
                <?= number_format($total, 0, ',', '.') ?> contratos registrados
            </div>
            <h1><?= esc($organTitle) ?></h1>
            <p>Historial completo de empresas adjudicatarias que han ganado licitaciones de este órgano de contratación público.</p>

                <?php if (empty($searchQuery) && $total > 0): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $checkoutUrl = site_url('billing/contracts_checkout?organo=' . urlencode($slug));
                    $pricing = $billingService->getPublicFundsPricingDetails($total);
                    $dynamic_price = $pricing['base_price'];
                ?>
                <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                        <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #2152FF, #3b82f6); color: #fff; padding: 1rem 2rem; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 10px 30px rgba(33,82,255,0.35); transition: all 0.2s; border: 1px solid rgba(96,165,250,0.4);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 14px 40px rgba(33,82,255,0.45)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(33,82,255,0.35)';">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Descargar base completa
                        </a>
                        <div style="text-align: left;">
                            <div style="font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: -0.04em; line-height: 1;"><?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="font-size: 0.75em; opacity: 0.5; margin-right: 4px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€</div>
                            <div style="font-size: 0.72rem; color: rgba(255,255,255,0.55); font-weight: 600; margin-top: 2px;">+ IVA · pago único</div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); max-width: 480px; margin-top: 6px; line-height: 1.4;">
                        Incluye todos los registros (<?= number_format($total, 0, ',', '.') ?>) cruzados con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
                <?php endif; ?>
        </div>
    </header>

    <main class="dir-main">
        <div class="container">
            <div class="dir-main-card">
                <?php helper('company'); ?>

                <form method="GET" action="<?= site_url('licitaciones-del-estado/organo-' . $slug) ?>" style="margin-bottom: 24px; position: relative;">
                    <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar por nombre de empresa o CIF..." style="width: 100%; padding: 1rem 1.25rem; padding-right: 7.5rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: 0.95rem; outline: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='#2152FF'; this.style.boxShadow='0 0 0 3px rgba(33,82,255,0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                    <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; border-radius: 8px; background: #2152FF; color: #fff; border: none; padding: 0 1.25rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Buscar
                    </button>
                </form>

                <?php if (!empty($searchQuery)): ?>
                <div style="padding: 16px 24px; background: #eff6ff; border-bottom: 1px solid #bfdbfe; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.95rem; color: #1d4ed8; font-weight: 700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -3px; margin-right:4px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <?= number_format($total, 0, ',', '.') ?> resultado<?= $total !== 1 ? 's' : '' ?> para &ldquo;<?= esc($searchQuery) ?>&rdquo;
                    </span>
                    <a href="<?= site_url('licitaciones-del-estado/organo-' . $slug) ?>" style="font-size: 0.85rem; color: #64748b; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Limpiar
                    </a>
                </div>
                <?php endif; ?>
                <div class="prem-table-wrap">
                    <table class="prem-table">
                        <thead>
                            <tr>
                                <th>Empresa Adjudicataria</th>
                                <th>Título del Contrato</th>
                                <th>Fecha Adjudicación</th>
                                <th style="text-align: right;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contracts)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">No hay contratos registrados para este órgano.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $rowNum = 0;
                                $ctaBannerShown = false;
                                $radarUrl = site_url('radar');
                                $_billingLock = new \App\Services\BillingService();
                                $_checkoutLock = site_url('billing/contracts_checkout?organo=' . urlencode($organName ?? $slug));
                                foreach ($contracts as $contract):
                                    $rowNum++;
                                    $hasCompany = !empty($contract['company_name']);
                                    $cUrl = ($hasCompany) ? company_url(['cif' => $contract['company_cif'], 'name' => $contract['company_name']]) : '#';
                                ?>
                                <?php if ($rowNum === 11 && !$ctaBannerShown && empty($searchQuery)): $ctaBannerShown = true; ?>
                                <tr>
                                    <td colspan="4" style="padding: 0;">
                                        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #2152FF 100%); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                                            <div>
                                                <p style="margin: 0 0 3px; font-size: 0.9rem; color: rgba(255,255,255,0.9); font-weight: 800;">📥 Descarga todas las adjudicaciones en CSV</p>
                                                <p style="margin: 0; font-size: 0.78rem; color: rgba(255,255,255,0.65); font-weight: 600;">CSV con nombre, CIF, teléfono, CNAE, dirección y provincia de cada adjudicatario</p>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 14px; flex-shrink: 0;">
                                                <div style="text-align: right;">
                                                    <div style="font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: -0.03em;"><?= number_format($_billingLock->getPublicFundsPricingDetails($total)['base_price'] ?? 99, 2, ',', '') ?>€ <span style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">+ IVA</span></div>
                                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.6); font-weight: 600;">pago único</div>
                                                </div>
                                                <a href="<?= $_checkoutLock ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #fff; color: #2152FF; padding: 11px 22px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; white-space: nowrap; box-shadow: 0 4px 14px rgba(0,0,0,0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.25)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.2)'">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    Descargar CSV
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                    <tr<?= ($hasCompany) ? ' onclick="window.location=\'' . esc($cUrl) . '\'" style="cursor: pointer;"' : '' ?>>
                                        <td>
                                            <?php if ($hasCompany): ?>
                                                <a href="<?= esc($cUrl) ?>" style="color: #0f172a; font-weight: 800; text-decoration: none; display: block; font-size: 0.95rem; transition: color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#0f172a'">
                                                    <?= esc($contract['company_name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #0f172a; font-weight: 800; display: block; font-size: 0.95rem;">Empresa <?= esc($contract['company_cif']) ?></span>
                                            <?php endif; ?>
                                            <span class="cif-badge">CIF: <?= esc($contract['company_cif']) ?></span>
                                        </td>
                                        <td style="color: #475569; font-size: 0.9rem; line-height: 1.5; max-width: 400px;">
                                            <?= esc($contract['titulo_contrato']) ?>
                                            <?php if (!empty($contract['enlace_licitacion'])): ?>
                                                <div>
                                                    <a href="<?= esc($contract['enlace_licitacion']) ?>" target="_blank" rel="nofollow noopener" class="link-badge" onclick="event.stopPropagation();">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                        Ver fuente oficial
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: #64748b; font-size: 0.95rem; white-space: nowrap; font-weight: 500;">
                                            <?= date('d/m/Y', strtotime($contract['fecha_adjudicacion'])) ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="amount-badge">
                                                <?= number_format($contract['importe_adjudicacion'], 2, ',', '.') ?> €
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pager): ?>
                <div class="pagination-container" style="padding: 16px 28px; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: center;">
                    <?= $pager ?>
                </div>
                <?php endif; ?>

                <!-- ── PARA QUIÉN ES ESTO ── -->
                <?php if (empty($searchQuery)): ?>
                <div style="padding: 28px 28px 24px; border-bottom: 1px solid #f1f5f9; border-top: 1px solid #e2e8f0; margin-top: 16px;">
                    <p style="font-size: 0.72rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="#94a3b8"><circle cx="12" cy="12" r="10"/></svg>
                        ¿Quién compra esto?
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <div class="use-case-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s;">
                            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 5px;">Proveedores</div>
                                <div style="font-size: 0.78rem; color: #64748b; line-height: 1.45;">Identifican qué empresas trabajan con este órgano para venderles como subcontrata.</div>
                            </div>
                        </div>
                        <div class="use-case-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s;">
                            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #faf5ff, #ede9fe); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 5px;">Análisis de Mercado</div>
                                <div style="font-size: 0.78rem; color: #64748b; line-height: 1.45;">Analizan qué empresas acumulan más adjudicaciones de este órgano en concreto.</div>
                            </div>
                        </div>
                        <div class="use-case-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s;">
                            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 5px;">Prospección B2B</div>
                                <div style="font-size: 0.78rem; color: #64748b; line-height: 1.45;">Construyen listas de adjudicatarios con experiencia vendiendo a este organismo.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ── BLOQUE SIGUIENTE PASO: CSV vs RADAR ── -->
                <div style="margin: 0; padding: 32px 24px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <p style="text-align: center; font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 20px;">¿Qué haces después?</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; max-width: 720px; margin: 0 auto;">

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
                            <?php if (!empty($checkoutUrl) && !empty($pricing)): ?>
                            <div style="margin-top: 4px;">
                                <a href="<?= $checkoutUrl ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #2152FF; color: #fff; padding: 12px 16px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; box-shadow: 0 4px 12px rgba(33,82,255,0.3); transition: all 0.2s; width: 100%;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Descargar CSV completo
                                </a>
                                <div style="text-align: center; margin-top: 8px; font-size: 0.82rem; font-weight: 800; color: #2152FF; letter-spacing: -0.01em;"><?= number_format($dynamic_price, 2, ',', '') ?>€ + IVA &nbsp;·&nbsp; <span style="font-weight: 600; color: #64748b;">pago único</span></div>
                            </div>
                            <?php endif; ?>
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

            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
