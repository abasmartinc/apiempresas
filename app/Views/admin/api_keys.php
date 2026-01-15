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
        <h1 class="title">Gestión de API Keys</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/api-keys/create') ?>" class="btn">Nueva API Key</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fecaca;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Nombre / Key</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Último Uso</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys as $k): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;"><?= esc($k->name ?: 'Sin nombre') ?></div>
                        <code style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= esc($k->api_key) ?></code>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($k->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($k->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($k->is_active): ?>
                            <span class="pill estado--activa" style="font-size: 0.7rem;">Activa</span>
                        <?php else: ?>
                            <span class="pill estado--inactiva" style="font-size: 0.7rem;">Inactiva</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.85rem; color: #64748b;">
                        <?= $k->last_used_at ? date('d/m/Y H:i', strtotime($k->last_used_at)) : 'Nunca' ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 5px;">
                            <a href="<?= site_url('admin/api-keys/edit/' . $k->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem;" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/api-keys/delete/' . $k->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fee2e2;" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta API Key?')">🗑️</a>
                        </div>
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
