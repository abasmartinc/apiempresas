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
            background: radial-gradient(circle, rgba(16,185,129,0.14) 0%, transparent 70%);
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
            background: linear-gradient(135deg, #34D399 0%, #10b981 100%);
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
            color: #34D399;
            text-decoration: none;
            transition: color 0.2s;
        }
        .dir-breadcrumbs a:hover { color: #10b981; }
        
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
            box-shadow: inset 3px 0 0 #10b981;
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
                <a href="<?= site_url('subvenciones-empresas') ?>">Subvenciones a Empresas</a>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                <span>Convocatoria Oficial</span>
            </nav>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(16, 185, 129, 0.15); color: #34D399; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.25rem; border: 1px solid rgba(16, 185, 129, 0.25);">
                <span style="display: inline-block; width: 6px; height: 6px; background: #34D399; border-radius: 99px; box-shadow: 0 0 8px #34D399;"></span>
                <?= number_format($total, 0, ',', '.') ?> subvenciones registradas
            </div>
            <h1><?= esc($convTitle) ?></h1>
            <p>Listado oficial de entidades y empresas que han recibido fondos correspondientes a esta convocatoria. Datos obtenidos directamente de las fuentes estatales.</p>

                <?php if (empty($searchQuery) && $total > 0): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $checkoutUrl = site_url('billing/subsidies_checkout?convocatoria=' . urlencode($slug));
                    $pricing = $billingService->getPublicFundsPricingDetails($total);
                    $dynamic_price = $pricing['base_price'];
                ?>
                <div style="margin-top: 2rem; display: flex; align-items: center; justify-content: flex-start; gap: 16px; flex-wrap: wrap;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 16px 32px; border-radius: 14px; font-weight: 800; font-size: 1.1rem; text-decoration: none; box-shadow: 0 12px 32px rgba(16,185,129,0.3); transition: all 0.2s; border: 1px solid rgba(255,255,255,0.1);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 16px 40px rgba(16,185,129,0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 12px 32px rgba(16,185,129,0.3)';">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Descargar CSV completo
                    </a>
                    <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px 20px; border-radius: 14px; text-align: left; display: flex; flex-direction: column; justify-content: center;">
                        <div style="font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: -0.04em; line-height: 1;">
                            <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="color:rgba(255,255,255,0.5); font-size:0.9rem; font-weight:600; margin-right:6px; text-decoration-thickness: 2px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85rem; font-weight: 700; color: #34d399;">+ IVA</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px;">Pago único · <?= number_format($total, 0, ',', '.') ?> registros</div>
                    </div>
                </div>
                <?php endif; ?>
        </div>
    </header>

    <main class="dir-main">
        <div class="container">
            <div class="dir-main-card">
                <?php helper('company'); ?>

                <form method="GET" action="<?= site_url('subvenciones-empresas/convocatoria-' . $slug) ?>" style="margin-bottom: 24px; position: relative;">
                    <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar por nombre de empresa o CIF..." style="width: 100%; padding: 1rem 1.25rem; padding-right: 7.5rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: 0.95rem; outline: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                    <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; border-radius: 8px; background: #10b981; color: #fff; border: none; padding: 0 1.25rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Buscar
                    </button>
                </form>

                <?php if (!empty($searchQuery)): ?>
                <div style="padding: 16px 24px; background: #ecfdf5; border-bottom: 1px solid #a7f3d0; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.95rem; color: #065f46; font-weight: 700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -3px; margin-right:4px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <?= number_format($total, 0, ',', '.') ?> resultado<?= $total !== 1 ? 's' : '' ?> para &ldquo;<?= esc($searchQuery) ?>&rdquo;
                    </span>
                    <a href="<?= site_url('subvenciones-empresas/convocatoria-' . $slug) ?>" style="font-size: 0.85rem; color: #64748b; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Limpiar
                    </a>
                </div>
                <?php endif; ?>
                <div class="prem-table-wrap">
                    <table class="prem-table">
                        <thead>
                            <tr>
                                <th>Empresa / Beneficiario</th>
                                <th>Fecha Concesión</th>
                                <th style="text-align: right;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subsidies)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">No hay subvenciones registradas para esta convocatoria en esta página.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $rowNum = 0;
                                foreach ($subsidies as $sub):
                                    $rowNum++;
                                    $hasCompany = !empty($sub['company_name']);
                                    $cUrl = ($hasCompany) ? company_url(['cif' => $sub['company_cif'], 'name' => $sub['company_name']]) : '#';
                                ?>
                                    <tr<?= ($hasCompany) ? ' onclick="window.location=\'' . esc($cUrl) . '\'" style="cursor: pointer;"' : '' ?>>
                                        <td>
                                            <?php if ($hasCompany): ?>
                                                <a href="<?= esc($cUrl) ?>" style="color: #0f172a; font-weight: 800; text-decoration: none; display: block; font-size: 0.95rem; transition: color 0.2s;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#0f172a'">
                                                    <?= esc($sub['company_name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <?php if (!empty($sub['raw_beneficiario'])): ?>
                                                    <span style="color: #0f172a; font-weight: 800; display: block; font-size: 0.95rem;"><?= esc($sub['raw_beneficiario']) ?></span>
                                                <?php else: ?>
                                                    <span style="color: #0f172a; font-weight: 800; display: block; font-size: 0.95rem;">Empresa sin nombre registrado</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <span class="cif-badge">CIF: <?= esc($sub['company_cif']) ?></span>
                                        </td>

                                        <td style="color: #64748b; font-size: 0.95rem; white-space: nowrap; font-weight: 500;">
                                            <?= date('d/m/Y', strtotime($sub['fecha_concesion'])) ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="amount-badge">
                                                <?= number_format($sub['importe'], 2, ',', '.') ?> €
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
                <div class="pagination-container" style="padding: 16px 28px; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: center; border-radius: 0 0 16px 16px;">
                    <?php 
                        $pagerLinks = $pager; 
                    ?>
                    <?= $pagerLinks ?>
                </div>
                <?php endif; ?>



                <!-- ── BLOQUE SIGUIENTE PASO: CSV vs RADAR ── -->
                <?php if (empty($searchQuery)): ?>
                <div style="margin: 0; padding: 40px 24px 32px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-top: 1px solid #e2e8f0; border-radius: 0 0 16px 16px;">
                    <p style="text-align: center; font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 8px;">¿Qué quieres hacer ahora?</p>
                    <p style="text-align: center; font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 32px; letter-spacing: -0.02em;">Elige cómo usar estos datos</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; max-width: 800px; margin: 0 auto;">

                        <!-- OPCIÓN A: Descarga única -->
                        <div style="background: #fff; border: 2px solid #10b981; border-radius: 18px; padding: 24px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 4px 16px rgba(16,185,129,0.08);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 38px; height: 38px; background: #ecfdf5; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    </div>
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; white-space: nowrap;">Descargar listado</div>
                                </div>
                                <span style="background: #10b981; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Pago único</span>
                            </div>
                            <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">Exporta el CSV completo de empresas subvencionadas con datos de contacto: teléfono, CNAE, dirección y provincia. Listo para tu CRM.</p>
                            <?php if (!empty($checkoutUrl) && !empty($pricing)): ?>
                            <div style="margin-top: 4px;">
                                <a href="<?= $checkoutUrl ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #10b981; color: #fff; padding: 12px 16px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); transition: all 0.2s; width: 100%;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Descargar CSV completo
                                </a>
                                <div style="text-align: center; margin-top: 8px; font-size: 0.82rem; font-weight: 800; color: #10b981; letter-spacing: -0.01em;"><?= number_format($dynamic_price, 2, ',', '') ?>€ + IVA &nbsp;·&nbsp; <span style="font-weight: 600; color: #64748b;">pago único</span></div>
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
