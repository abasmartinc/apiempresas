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
            --dir-primary: #10b981;
            --dir-primary-soft: rgba(16, 185, 129, 0.08);
            --dir-bg: #f1f5f9;
        }

        /* ── HERO ── */
        .dir-hero {
            padding: 60px 0 140px;
            background: linear-gradient(160deg, #060a14 0%, #071a12 50%, #0f172a 100%);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dir-hero::before { content: ''; position: absolute; top: -20%; right: -10%; width: 40%; height: 80%; background: radial-gradient(circle, rgba(16,185,129,0.14) 0%, transparent 70%); pointer-events: none; }
        .dir-hero::after  { content: ''; position: absolute; bottom: -10%; left: -5%; width: 35%; height: 60%; background: radial-gradient(circle, rgba(52,211,153,0.07) 0%, transparent 70%); pointer-events: none; }
        .dir-hero h1 { font-size: clamp(2.4rem, 4.5vw, 3.5rem); font-weight: 800; letter-spacing: -0.03em; color: #fff; margin-bottom: 1.25rem; line-height: 1.1; }
        .dir-hero .grad { background: linear-gradient(135deg, #34D399 0%, #10b981 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
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
        .dir-breadcrumbs a { color: #34D399; text-decoration: none; transition: color 0.2s; }
        .dir-breadcrumbs a:hover { color: #fff; }

        /* ── SECTION HEADER ── */
        .dir-section { margin-bottom: 0; }
        .section-header { display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.75rem; }
        .section-header__icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .section-header__icon--green { background: linear-gradient(135deg, #10b981, #059669); }
        .section-header h2 { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.025em; }
        .section-header .line { height: 1px; background: linear-gradient(90deg, #e2e8f0, transparent); flex-grow: 1; }
        .section-header__count { font-size: 0.8rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 4px 12px; border-radius: 99px; white-space: nowrap; border: 1px solid #e2e8f0; }

        /* ── PREMIUM TABLE ── */
        .prem-table-wrap { background: #fff; border-radius: 20px; border: 1px solid #e9eef5; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .prem-table { width: 100%; min-width: 700px; border-collapse: collapse; font-size: 0.92rem; }
        .prem-table thead tr { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 2px solid #e9eef5; }
        .prem-table thead th { padding: 14px 20px; color: #475569; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; text-align: left; }
        .prem-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: all 0.18s ease; cursor: pointer; }
        .prem-table tbody tr:last-child { border-bottom: none; }
        .prem-table tbody tr:hover { background: #f0fdf4; box-shadow: inset 3px 0 0 #10b981; }
        .prem-table td { padding: 14px 20px; vertical-align: middle; }

        .cif-badge { display: inline-block; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; margin-top: 4px; border: 1px solid #e2e8f0; }
        .amount-badge { display: inline-block; background: #ecfdf5; color: #059669; padding: 5px 14px; border-radius: 99px; font-weight: 800; font-size: 0.9rem; border: 1px solid #a7f3d0; white-space: nowrap; }

        .pagination-container { padding: 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; background: #f8fafc; border-radius: 0 0 20px 20px; }
        .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 6px; }
        .pagination li a, .pagination li span { display: flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.2s; }
        .pagination li a { background: #fff; color: #475569; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .pagination li a:hover { border-color: #10b981; color: #10b981; }
        .pagination li.active a, .pagination li.active span { background: #10b981; color: #fff; border: 1px solid #10b981; box-shadow: 0 2px 4px rgba(16,185,129,0.2); }
        .pagination li.disabled span { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; cursor: not-allowed; }

        @media (max-width: 768px) {
            .dir-hero { padding: 60px 1.5rem 120px; } .dir-hero h1 { font-size: 2.2rem; }
            .container { padding: 0 1.25rem; } .dir-main-card { padding: 24px 20px; margin-top: -70px; border-radius: 20px; }
            .prem-table-wrap { overflow-x: auto; }
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
                <span>Subvenciones <?= $year ?></span>
            </nav>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(16, 185, 129, 0.15); color: #34D399; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.25);">
                <span style="display: inline-block; width: 6px; height: 6px; background: #34D399; border-radius: 99px; box-shadow: 0 0 8px #34D399;"></span>
                <?= number_format($total, 0, ',', '.') ?> subvenciones registradas
            </div>
            <h1>Subvenciones a Empresas <span class="grad"><?= $year ?></span></h1>
            <p>Listado oficial de subvenciones y ayudas públicas concedidas a empresas en <?= $year ?>. Importe total concedido: <strong><?= number_format($total_amount, 0, ',', '.') ?> €</strong>.</p>

                <?php if (empty($searchQuery) && $total > 0): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $checkoutUrl = site_url('billing/subsidies_checkout?ano=' . urlencode($year));
                    $dynamic_price = $billingService->calculatePublicFundsPrice($total);
                ?>
                <div style="margin-top: 2rem;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: #fff; padding: 1.1rem 1.8rem; border-radius: 12px; font-weight: 800; font-size: 1rem; text-decoration: none; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3); transition: all 0.2s; border: 1px solid #34d399;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 35px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(16, 185, 129, 0.3)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar CSV Completo — <?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7); max-width: 480px; margin-top: 10px; margin-left: auto; margin-right: auto; line-height: 1.4;">
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

                <form method="GET" action="<?= site_url('subvenciones-empresas/ano-' . $year) ?>" style="margin-bottom: 24px; position: relative;">
                    <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="Buscar por nombre de empresa o CIF..." style="width: 100%; padding: 1rem 1.25rem; padding-right: 7.5rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0f172a; font-size: 0.95rem; outline: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                    <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; border-radius: 8px; background: #10b981; color: #fff; border: none; padding: 0 1.25rem; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Buscar
                    </button>
                </form>

                <?php if (!empty($searchQuery)): ?>
                <div style="margin-bottom: 32px; padding: 16px 24px; background: #ecfdf5; border-radius: 14px; border: 1px solid #a7f3d0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.95rem; color: #065f46; font-weight: 700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -3px; margin-right: 4px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <?= number_format($total, 0, ',', '.') ?> resultado<?= $total !== 1 ? 's' : '' ?> para &ldquo;<?= esc($searchQuery) ?>&rdquo;
                    </span>
                    <a href="<?= site_url('subvenciones-empresas/ano-' . $year) ?>" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Limpiar
                    </a>
                </div>
                <?php endif; ?>

                <section class="dir-section">
                    <div class="section-header">
                        <div class="section-header__icon section-header__icon--green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                        <h2>Subvenciones Concedidas</h2>
                        <span class="section-header__count"><?= number_format($total, 0, ',', '.') ?> subvenciones</span>
                        <div class="line"></div>
                    </div>

                    <div class="prem-table-wrap">
                        <table class="prem-table">
                            <thead>
                                <tr>
                                    <th>Empresa Beneficiaria</th>
                                    <th>Convocatoria</th>
                                    <th>Fecha Concesión</th>
                                    <th style="text-align: right;">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($subsidies)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">No hay subvenciones registradas para <?= $year ?>.</td></tr>
                            <?php else: ?>
                                <?php foreach($subsidies as $sub):
                                    $cUrl = company_url(['cif' => $sub['company_cif'], 'name' => $sub['company_name'] ?: $sub['raw_beneficiario'] ?: $sub['company_cif']]);
                                ?>
                                <tr onclick="window.location='<?= esc($cUrl) ?>'">
                                    <td>
                                        <?php $displayName = $sub['company_name'] ?: $sub['raw_beneficiario'] ?: null; ?>
                                        <?php if ($displayName): ?>
                                            <a href="<?= esc($cUrl) ?>" style="color: #0f172a; font-weight: 700; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#0f172a'"><?= esc($displayName) ?></a>
                                        <?php else: ?>
                                            <span style="font-weight: 700; color: #0f172a;">Empresa sin nombre</span>
                                        <?php endif; ?>
                                        <br><span class="cif-badge">CIF: <?= esc($sub['company_cif']) ?></span>
                                    </td>
                                    <td style="color: #475569; font-size: 0.85rem; max-width: 300px;">
                                        <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= esc(mb_convert_case($sub['convocatoria'], MB_CASE_TITLE, 'UTF-8')) ?></div>
                                    </td>
                                    <td style="white-space: nowrap; color: #64748b; font-size: 0.9rem;"><?= $sub['fecha_concesion'] ? date('d/m/Y', strtotime($sub['fecha_concesion'])) : '—' ?></td>
                                    <td style="text-align: right;">
                                        <span class="amount-badge"><?= number_format($sub['importe'], 2, ',', '.') ?> €</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <?php if (empty($searchQuery) && $total > 0): ?>
                <?php 
                    $billingService = new \App\Services\BillingService();
                    $checkoutUrl = site_url('billing/subsidies_checkout?year=' . urlencode($year));
                    $dynamic_price = $billingService->calculatePublicFundsPrice($total);
                ?>
                <div style="padding: 24px; text-align: right; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 16px 16px;">
                    <a href="<?= $checkoutUrl ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25); transition: all 0.2s; margin-bottom: 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(16, 185, 129, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(16, 185, 129, 0.25)';">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Descargar CSV Completo (<?= $year ?>) — <?= number_format($dynamic_price, 2, ',', '') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>
                    </a>
                    <div style="font-size: 0.75rem; color: #94a3b8; max-width: 320px; margin-left: auto; line-height: 1.4;">
                        Incluye todos los registros (<?= number_format($total, 0, ',', '.') ?>) cruzados con los datos del Registro Mercantil: Sector CNAE, Dirección, Provincia y Teléfono (cuando esté disponible).
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($pager): ?>
                <div class="pagination-container"><?= $pager ?></div>
                <?php endif; ?>

                <!-- SEO Content -->
                <section style="margin-top: 48px; padding-top: 40px; border-top: 1px solid #e2e8f0;">
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 0.75rem; letter-spacing: -0.025em;">Subvenciones a Empresas en España: Año <?= $year ?></h2>
                    <p style="color: #475569; font-size: 0.95rem; line-height: 1.7; margin-bottom: 1.5rem;">Este listado recoge todas las subvenciones y ayudas públicas concedidas a empresas en <?= $year ?>, extraídas de la Base de Datos Nacional de Subvenciones (BDNS). La transparencia en la concesión de fondos públicos es un derecho de todos los ciudadanos españoles, garantizado por la Ley 19/2013 de Transparencia.</p>
                    <div style="display: grid; gap: 24px;">
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">¿Qué es la Base de Datos Nacional de Subvenciones (BDNS)?</h3>
                            <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">La BDNS es el sistema público donde se registran todas las convocatorias y concesiones de subvenciones y ayudas financiadas con fondos públicos en España. Es gestionada por el Ministerio de Hacienda y es de acceso libre y gratuito.</p>
                        </div>
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">¿Cómo puedo ver el historial de subvenciones de una empresa?</h3>
                            <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">Haz clic en el nombre de cualquier empresa de este listado para acceder a su ficha mercantil completa en apiempresas.es, donde encontrarás el historial detallado de todas las subvenciones recibidas, su actividad empresarial y mucho más.</p>
                        </div>
                    </div>
                </section>
                <!-- /SEO Content -->

            </div>
        </div>
    </main>

    <!-- JSON-LD Dataset Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Dataset",
      "name": "Subvenciones a Empresas Concedidas en <?= $year ?>",
      "description": "Listado oficial de <?= number_format($total, 0, ',', '.') ?> subvenciones concedidas a empresas en <?= $year ?> en España. Fuente: Base de Datos Nacional de Subvenciones (BDNS).",
      "url": "<?= site_url('subvenciones-empresas/ano-' . $year) ?>",
      "creator": {
        "@type": "Organization",
        "name": "apiempresas.es"
      },
      "temporalCoverage": "<?= $year ?>",
      "spatialCoverage": "España",
      "license": "https://creativecommons.org/licenses/by/4.0/"
    }
    </script>

    <?= view('partials/footer') ?>
</body>
</html>
