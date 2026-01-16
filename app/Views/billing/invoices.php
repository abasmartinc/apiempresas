<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <style>
        .invoice-container { padding: 40px 20px; max-width: 1000px; margin: 0 auto; min-height: 70vh; }
        .invoice-header { margin-bottom: 30px; }
        .invoice-header h1 { font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .invoice-header p { color: #64748b; }
        
        .invoice-card { background: white; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); padding: 24px; border: 1px solid #f1f5f9; }
        
        .invoice-table { width: 100%; border-collapse: collapse; }
        .invoice-table th { text-align: left; padding: 16px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.025em; }
        .invoice-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .invoice-num { font-weight: 700; color: #1e293b; }
        .invoice-date { color: #64748b; font-size: 0.9rem; }
        .invoice-amount { font-weight: 700; color: #2152ff; }
        
        .status-pill { padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .status-pill--paid { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        
        .btn-download { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #f8fafc; color: #475569; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.2s; border: 1px solid #e2e8f0; }
        .btn-download:hover { background: #f1f5f9; color: #1e293b; border-color: #cbd5e1; transform: translateY(-1px); }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state svg { width: 64px; height: 64px; color: #cbd5e1; margin-bottom: 16px; }
        .empty-state h3 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .empty-state p { color: #64748b; margin-bottom: 24px; }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #64748b; text-decoration: none; font-size: 0.9rem; margin-bottom: 20px; transition: color 0.2s; }
        .back-link:hover { color: #2152ff; }

        /* Pagination Styles */
        .pagination { display: flex; list-style: none; padding: 0; gap: 8px; justify-content: center; margin-top: 20px; }
        .pagination li a, .pagination li span { display: block; padding: 8px 16px; border-radius: 8px; background: white; border: 1px solid #e2e8f0; color: #475569; text-decoration: none; transition: all 0.2s; }
        .pagination li a:hover { border-color: #2152ff; color: #2152ff; background: #f0f4ff; }
        .pagination li.active span { background: #2152ff; color: white; border-color: #2152ff; }
        .pagination li.disabled span { color: #cbd5e1; cursor: not-allowed; }
    </style>
</head>
<body style="background-color: #f8fafc;">

    <?= view('partials/header_inner') ?>

    <div class="invoice-container">
        <a href="<?= site_url('dashboard') ?>" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver al Dashboard
        </a>

        <div class="invoice-header">
            <h1>Mis Facturas</h1>
            <p>Historial de pagos y descarga de facturas oficiales.</p>
        </div>

        <div class="invoice-card">
            <?php if (empty($invoices)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3>No hay facturas todavía</h3>
                    <p>Cuando realices tu primer pago, aparecerá aquí para que puedas descargarla.</p>
                    <a href="<?= site_url('billing') ?>" class="btn btn_primary">Ver planes</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><span class="invoice-num"><?= esc($inv->invoice_number) ?></span></td>
                                    <td><span class="invoice-date"><?= date('d/m/Y', strtotime($inv->created_at)) ?></span></td>
                                    <td><span class="invoice-amount"><?= number_format($inv->total_amount, 2, ',', '.') ?> <?= esc($inv->currency) ?></span></td>
                                    <td>
                                        <span class="status-pill status-pill--paid">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Pagada
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= site_url('billing/invoices/download/' . $inv->id) ?>" class="btn-download">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Descargar PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px;">
                    <?= $pager->links('default', 'admin_full') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?= view('partials/footer') ?>

</body>
</html>
