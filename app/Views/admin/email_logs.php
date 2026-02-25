<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #cbd5e1; }
        .kpi-label { font-size: 0.875rem; color: #64748b; font-weight: 500; }
        .kpi-value { font-size: 1.75rem; font-weight: 700; color: #0f172a; }
        .kpi-sub { font-size: 0.75rem; color: #94a3b8; }
        .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .tracking-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .dot-placeholder { background: #e2e8f0; border: 1px solid #cbd5e1; }
        .dot-active { background: #22c55e; box-shadow: 0 0 8px rgba(34, 197, 94, 0.4); border: 1px solid #16a34a; }
        .admin-table-wrapper { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding">
    <div class="page-header">
        <div>
            <h1 class="title" style="color: #0f172a;">KPIs de Email Marketing</h1>
            <p style="color: #64748b;">Seguimiento profesional de comunicaciones con clientes</p>
        </div>
        <div class="flex-gap-10">
            <a href="<?= site_url('admin/users') ?>" class="btn">Mandar nuevo Email</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- KPIs Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <span class="kpi-label">Total Enviados</span>
            <span class="kpi-value"><?= number_format($stats['total_sent']) ?></span>
            <span class="kpi-sub">Emails entregados con éxito</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Tasa de Apertura</span>
            <span class="kpi-value" style="color: #2152FF;"><?= $stats['open_rate'] ?>%</span>
            <span class="kpi-sub"><?= $stats['total_opened'] ?> aperturas únicas</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Click-Through Rate (CTR)</span>
            <span class="kpi-value" style="color: #12b48a;"><?= $stats['click_rate'] ?>%</span>
            <span class="kpi-sub"><?= $stats['total_clicked'] ?> clics detectados</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Conversión (Logs)</span>
            <span class="kpi-value" style="color: #f59e0b;"><?= $stats['conversion_rate'] ?>%</span>
            <span class="kpi-sub"><?= $stats['total_logged'] ?> logins desde email</span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="admin-table-wrapper">
        <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">Historial Detallado</h3>
            <span style="font-size: 0.85rem; color: #64748b;">Mostrando últimos envíos</span>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Destinatario</th>
                        <th>Asunto</th>
                        <th style="text-align: center;">Abierto</th>
                        <th style="text-align: center;">Clic</th>
                        <th style="text-align: center;">Login</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">No hay logs registrados todavía</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.85rem; color: #64748b;">
                                    <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="color: #1e293b; font-weight: 600;"><?= esc($log->user_name ?: 'Desconocido') ?></span>
                                        <span style="font-size: 0.8rem; color: #94a3b8;"><?= esc($log->user_email) ?></span>
                                    </div>
                                </td>
                                <td style="color: #334155; font-weight: 500;"><?= esc($log->subject) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($log->opened_at): ?>
                                        <span class="tracking-dot dot-active" title="Abierto el <?= date('d/m/Y H:i', strtotime($log->opened_at)) ?>"></span>
                                    <?php else: ?>
                                        <span class="tracking-dot dot-placeholder" title="No abierto"></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($log->clicked_at): ?>
                                        <span class="tracking-dot dot-active" title="Clic el <?= date('d/m/Y H:i', strtotime($log->clicked_at)) ?>"></span>
                                    <?php else: ?>
                                        <span class="tracking-dot dot-placeholder" title="Sin clics"></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($log->logged_in_at): ?>
                                        <span class="tracking-dot dot-active" title="Login el <?= date('d/m/Y H:i', strtotime($log->logged_in_at)) ?>"></span>
                                    <?php else: ?>
                                        <span class="tracking-dot dot-placeholder" title="Sin login"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $log->status ?>">
                                        <?= $log->status == 'success' ? 'Enviado' : 'Fallido' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 1.5rem; border-top: 1px solid #f1f5f9;">
            <?= $pager->links('default', 'admin_full') ?>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
