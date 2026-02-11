<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Centro de Seguridad: IPs Bloqueadas</h1>
        <div style="display: flex; gap: 10px;">
            <form action="<?= site_url('admin/blocked-ips') ?>" method="get" style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="q" class="input" placeholder="Buscar IP o motivo..." value="<?= esc($q) ?>" style="padding: 8px; font-size: 0.85rem;">
                <button type="submit" class="btn primary" style="padding: 8px 15px;">Buscar</button>
            </form>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">IP Address</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Motivo del Bloqueo</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha Bloqueo</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Intentos</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Último Intento</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blocked_ips)): ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8;">No hay IPs bloqueadas actualmente.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($blocked_ips as $ip): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: 700; color: #1e293b;">
                            <?= esc($ip['ip_address']) ?>
                        </td>
                        <td style="padding: 12px; font-size: 0.85rem; color: #64748b;">
                            <?= esc($ip['reason']) ?>
                        </td>
                        <td style="padding: 12px; font-size: 0.85rem; white-space: nowrap;">
                            <?= date('d/m/Y H:i', strtotime($ip['blocked_at'])) ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span class="pill" style="background: #fee2e2; color: #991b1b; font-weight: 700;">
                                <?= $ip['request_count'] ?>
                            </span>
                        </td>
                        <td style="padding: 12px; font-size: 0.85rem; color: #64748b;">
                            <?= $ip['last_attempt_at'] ? date('d/m/Y H:i', strtotime($ip['last_attempt_at'])) : '-' ?>
                        </td>
                        <td style="padding: 12px;">
                            <a href="#" class="btn ghost" style="padding: 5px 10px; font-size: 0.75rem; color: #dc2626; border-color: #fecaca;" 
                               onclick="if(confirm('¿Desbloquear esta IP?')) { alert('Función de desbloqueo no implementada aún'); } return false;">
                                🔓 Desbloquear
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
