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
            padding: 40px 0 120px;
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
            background: radial-gradient(circle, rgba(33,82,255,0.14) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero h1 {
            font-size: clamp(2rem, 3.5vw, 2.5rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin-bottom: 1rem;
            line-height: 1.2;
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
            color: #cbd5e1;
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
                    $dynamic_price = $billingService->calculatePublicFundsPrice($total);
                ?>
                <div style="margin-top: 2rem;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2152FF; color: #fff; padding: 0.85rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; box-shadow: 0 6px 20px rgba(33, 82, 255, 0.25); transition: all 0.2s; border: 1px solid #60A5FA;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(33, 82, 255, 0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(33, 82, 255, 0.25)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar CSV Completo — <?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); max-width: 480px; margin-top: 10px; line-height: 1.4;">
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
                                <?php foreach ($contracts as $contract): 
                                    $cUrl = company_url(['cif' => $contract['company_cif'], 'name' => $contract['company_name'] ?: $contract['company_cif']]);
                                ?>
                                    <tr onclick="window.location='<?= esc($cUrl) ?>'" style="cursor: pointer;">
                                        <td>
                                            <?php if ($contract['company_name']): ?>
                                                <a href="<?= esc($cUrl) ?>" style="color: #0f172a; font-weight: 800; text-decoration: none; display: block; font-size: 0.95rem; transition: color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#0f172a'">
                                                    <?= esc($contract['company_name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #0f172a; font-weight: 800; display: block; font-size: 0.95rem;">Empresa sin nombre registrado</span>
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
                
                <?php if (empty($searchQuery) && $total > 0): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $checkoutUrl = site_url('billing/contracts_checkout?organo=' . urlencode($organName));
                    $dynamic_price = $billingService->calculatePublicFundsPrice($total);
                ?>
                <div style="padding: 24px; text-align: right; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 16px 16px;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2152FF; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; box-shadow: 0 8px 20px rgba(33, 82, 255, 0.25); transition: all 0.2s; margin-bottom: 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(33, 82, 255, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(33, 82, 255, 0.25)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar CSV Completo — <?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.75rem; color: #94a3b8; max-width: 320px; margin-left: auto; line-height: 1.4;">
                        Incluye todos los registros (<?= number_format($total, 0, ',', '.') ?>) cruzados con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($pager): ?>
                <div class="pagination-container">
                    <?= $pager ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
