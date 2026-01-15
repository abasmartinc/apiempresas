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
        <h1 class="title">Logs de Emails Enviados</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Destinatario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Asunto</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Mensaje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-size: 0.85rem; white-space: nowrap;">
                        <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($log->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($log->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px; font-weight: 600; font-size: 0.85rem;">
                        <?= esc($log->subject) ?>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($log->status === 'success'): ?>
                            <span class="pill estado--activa" style="font-size: 0.7rem;">ENVIADO</span>
                        <?php else: ?>
                            <span class="pill estado--inactiva" style="font-size: 0.7rem;" title="<?= esc($log->error_message) ?>">ERROR</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.75rem; color: #64748b; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?= esc(strip_tags($log->message)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="padding: 40px; text-align: center; color: #94a3b8;">No hay registros de correos enviados.</td>
                </tr>
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
