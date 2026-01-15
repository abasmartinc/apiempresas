<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?= site_url() ?>">
                <span class="brand-name">API<span class="grad">Empresas</span> Admin</span>
            </a>
        </div>
        <div class="desktop-only">
            <a class="btn btn_header btn_header--ghost" href="<?= site_url('logout') ?>">Salir</a>
        </div>
    </div>
</header>

<main class="container" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Logs de Búsqueda</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Canal</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Búsqueda</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Tipo</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Resultado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Empresa Encontrada</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-size: 0.85rem; white-space: nowrap;">
                        <?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?>
                    </td>
                    <td style="padding: 12px;">
                        <span class="pill" style="font-size: 0.7rem; background: <?= $log->channel === 'api' ? '#fef3c7; color: #92400e;' : '#e0f2fe; color: #075985;' ?>">
                            <?= strtoupper($log->channel) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-weight: 600;">
                        <?= esc($log->query_raw) ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.85rem; color: #64748b;">
                        <?= strtoupper($log->query_type) ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php 
                        $statusColor = '#64748b';
                        if ($log->result_status === 'ok') $statusColor = '#16a34a';
                        if ($log->result_status === 'not_found') $statusColor = '#ca8a04';
                        if ($log->result_status === 'error') $statusColor = '#dc2626';
                        ?>
                        <span style="color: <?= $statusColor ?>; font-weight: 600; font-size: 0.85rem;">
                            <?= strtoupper($log->result_status) ?> (<?= $log->http_status ?>)
                        </span>
                        <div style="font-size: 0.75rem; color: #94a3b8;"><?= $log->result_count ?> resultados</div>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log->company_cif): ?>
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($log->company_name) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= esc($log->company_cif) ?></div>
                        <?php else: ?>
                            <span style="color: #cbd5e1;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.8rem; color: #64748b;">
                        <?= esc($log->ip) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
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
