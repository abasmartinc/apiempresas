<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => 'Gestión de Facturas | APIEmpresas']) ?>
    <style>
        :root {
            --kpi-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --kpi-violet: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --kpi-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .kpi-card { 
            position: relative;
            overflow: hidden;
            background: white; 
            border-radius: 24px; 
            padding: 2rem; 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px;
            background: var(--kpi-color); opacity: 0.05; border-radius: 0 0 0 100%; pointer-events: none;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px; border-radius: 14px; background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;
            color: white; box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .pill { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; text-align: center; }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Gestión de Facturas</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-emerald);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <span class="kpi-label">Facturado (Mes)</span>
            <span class="kpi-value"><?= number_format($stats['revenue_month'], 0, ',', '.') ?>€</span>
            <span class="kpi-sub">Ingresos netos este mes</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <span class="kpi-label">Facturas (Mes)</span>
            <span class="kpi-value"><?= number_format($stats['count_month'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Documentos generados</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-violet);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 6l-9.5 9.5-5-5L1 18"></path><polyline points="17 6 23 6 23 12"></polyline></svg>
            </div>
            <span class="kpi-label">Ticket Medio</span>
            <span class="kpi-value"><?= number_format($stats['avg_ticket'], 1, ',', '.') ?>€</span>
            <span class="kpi-sub">Valor promedio por factura</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-rose);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <span class="kpi-label">Pendientes</span>
            <span class="kpi-value"><?= number_format($stats['pending_count'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Esperando pago o fallidas</span>
        </div>
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
