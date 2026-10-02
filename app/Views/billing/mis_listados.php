<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?php /* Listados comprados por el usuario (Billing::mis_listados). Mismo aspecto que "Mis Facturas". */ ?>
<?= $this->section('styles') ?>
    <style>
        .ml-container { padding: 40px 20px; max-width: 1000px; margin: 0 auto; min-height: 70vh; }
        .ml-header { margin-bottom: 30px; }
        .ml-header h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .ml-header p { color: #64748b; }
        .ml-card { background: white; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); padding: 24px; border: 1px solid #f1f5f9; }
        .ml-table { width: 100%; border-collapse: collapse; }
        .ml-table th { text-align: left; padding: 16px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.025em; }
        .ml-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .ml-titulo { font-weight: 700; color: #1e293b; }
        .ml-ref { color: #94a3b8; font-size: 0.8rem; }
        .ml-fecha { color: #64748b; font-size: 0.9rem; white-space: nowrap; }
        .ml-importe { font-weight: 700; color: #1e293b; white-space: nowrap; }
        .ml-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; background: #10b981; color: #fff; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 700; white-space: nowrap; }
        .ml-btn:hover { background: #059669; color: #fff; }
        .ml-caducado { color: #94a3b8; font-size: 0.85rem; }
        .ml-nota { color: #64748b; font-size: 0.78rem; margin-top: 4px; }
        .ml-empty { text-align: center; padding: 60px 20px; }
        .ml-empty h3 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .ml-empty p { color: #64748b; margin-bottom: 24px; }
        .ml-back { display: inline-flex; align-items: center; gap: 8px; color: #64748b; text-decoration: none; font-size: 0.9rem; margin-bottom: 20px; }
        .ml-back:hover { color: #2152ff; }
        @media (max-width: 640px) {
            .ml-table thead { display: none; }
            .ml-table, .ml-table tbody, .ml-table tr, .ml-table td { display: block; width: 100%; }
            .ml-table td { padding: 4px 0; border: none; }
            .ml-table tr { padding: 16px 0; border-bottom: 1px solid #f1f5f9; }
            .ml-table td:last-child { text-align: left !important; padding-top: 10px; }
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="ml-container">
        <a href="<?= site_url('dashboard') ?>" class="ml-back">← Volver al Dashboard</a>

        <div class="ml-header">
            <h1>Mis listados</h1>
            <p>Los listados de empresas que has comprado. Puedes descargarlos las veces que necesites durante 30 días desde la compra.</p>
        </div>

        <div class="ml-card">
            <?php if (empty($listados)): ?>
                <div class="ml-empty">
                    <h3>Todavía no hay listados</h3>
                    <p>Los listados que compres con tu cuenta aparecerán aquí. Si compraste sin iniciar sesión, usa el enlace del correo que te enviamos al pagar.</p>
                    <a href="<?= site_url('base-de-datos-de-empresas') ?>" class="ml-btn">Buscar un listado</a>
                </div>
            <?php else: ?>
                <table class="ml-table">
                    <thead>
                        <tr>
                            <th>Listado</th>
                            <th>Comprado</th>
                            <th>Total</th>
                            <th style="text-align: right;">Descarga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listados as $l): ?>
                        <tr>
                            <td>
                                <div class="ml-titulo"><?= esc($l['titulo']) ?></div>
                                <div class="ml-ref"><?= esc($l['ref']) ?></div>
                            </td>
                            <td><span class="ml-fecha"><?= esc($l['fecha']) ?></span></td>
                            <td><span class="ml-importe"><?= $l['importe'] !== null ? number_format($l['importe'], 2, ',', '.') . ' €' : '—' ?></span></td>
                            <td style="text-align: right;">
                                <?php if ($l['vigente']): ?>
                                    <a href="<?= esc($l['url'], 'attr') ?>" class="ml-btn">Descargar CSV</a>
                                    <?php if (!empty($l['url_xlsx'])): ?>
                                    <a href="<?= esc($l['url_xlsx'], 'attr') ?>" class="ml-btn" style="background:#fff;color:#047857;border:1px solid #a7f3d0;margin-left:6px;">Excel (.xlsx)</a>
                                    <?php endif; ?>
                                    <div class="ml-nota">Disponible hasta el <?= esc($l['caduca']) ?></div>
                                <?php else: ?>
                                    <span class="ml-caducado">Descarga caducada el <?= esc($l['caduca']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <p class="ml-nota" style="margin-top: 16px;">
            Las facturas están en <a href="<?= site_url('billing/invoices') ?>">Mis Facturas</a>.
            Aquí aparecen las compras hechas con tu cuenta desde el 2 de octubre de 2026; para las anteriores, usa el enlace del correo de la compra.
        </p>
    </div>

<?= $this->endSection() ?>
