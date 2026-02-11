<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => 'Gestión de Facturas | APIEmpresas']) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Gestión de Facturas</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/invoices') ?>" method="get" style="display: flex; gap: 1rem;">
            <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Buscar por número, nombre o email..." class="input" style="flex: 1;">
            <button type="submit" class="btn primary">Buscar Factura</button>
            <?php if ($search): ?>
                <a href="<?= site_url('admin/invoices') ?>" class="btn ghost" title="Limpiar búsqueda">🔄</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (session('message')): ?>
        <div style="padding: 12px; background: #f0fdf4; color: #166534; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
            <?= session('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div style="padding: 12px; background: #fef2f2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Número</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Cliente</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Total</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">No se encontraron facturas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px;"><strong style="color: #1e293b;"><?= esc($inv->invoice_number) ?></strong></td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 600; color: #1e293b;"><?= esc($inv->billing_name) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= esc($inv->billing_email) ?></div>
                            </td>
                            <td style="padding: 12px; font-size: 0.85rem; color: #64748b;"><?= date('d/m/Y', strtotime($inv->created_at)) ?></td>
                            <td style="padding: 12px;"><strong style="color: #1e293b;"><?= number_format($inv->total_amount, 2, ',', '.') ?> <?= esc($inv->currency) ?></strong></td>
                            <td style="padding: 12px;">
                                <span class="pill" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 700;">Pagada</span>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <a href="<?= site_url('admin/invoices/download/' . $inv->id) ?>" class="btn ghost" style="padding: 6px 12px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 6px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    PDF
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem;">
        <?= $pager->links('default', 'admin_full') ?>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
