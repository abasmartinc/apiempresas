<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <style>
        .container-admin { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .admin-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 20px; margin-top: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th { text-align: left; padding: 12px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-weight: 600; }
        .admin-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .pill { padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; }
        .pill--paid { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .btn-download { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f1f5f9; color: #475569; border-radius: 6px; text-decoration: none; font-size: 0.8rem; transition: all 0.2s; }
        .btn-download:hover { background: #e2e8f0; color: #1e293b; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input { flex: 1; padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; }
        .search-btn { padding: 10px 20px; background: #2152ff; color: white; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body style="background-color: #f8fafc;">

    <?= view('partials/header_admin') ?>

    <div class="container-admin">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b;">Gestión de Facturas</h1>
        </div>

        <div class="admin-card">
            <form action="<?= site_url('admin/invoices') ?>" method="get" class="search-bar">
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Buscar por número, nombre o email..." class="search-input">
                <button type="submit" class="search-btn">Buscar</button>
            </form>

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

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">No se encontraron facturas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><strong><?= esc($inv->invoice_number) ?></strong></td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;"><?= esc($inv->billing_name) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?= esc($inv->billing_email) ?></div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($inv->created_at)) ?></td>
                                <td><strong><?= number_format($inv->total_amount, 2, ',', '.') ?> <?= esc($inv->currency) ?></strong></td>
                                <td>
                                    <span class="pill pill--paid">Pagada</span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= site_url('admin/invoices/download/' . $inv->id) ?>" class="btn-download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <?= $pager->links() ?>
            </div>
        </div>
    </div>

</body>
</html>
