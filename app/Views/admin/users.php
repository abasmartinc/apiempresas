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
        <h1 class="title">Usuarios Registrados</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/users/create') ?>" class="btn">Nuevo Usuario</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/users') ?>" method="get" class="grid" style="grid-template-columns: 1fr auto auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Buscar por nombre, email o empresa</label>
                <input type="text" name="q" class="input" style="width: 100%;" placeholder="Ej: Juan Pérez..." value="<?= esc($q) ?>">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Estado</label>
                <select name="is_active" class="input" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="1" <?= $is_active === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $is_active === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Rol</label>
                <select name="is_admin" class="input" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="1" <?= $is_admin === '1' ? 'selected' : '' ?>>Admin</option>
                    <option value="0" <?= $is_admin === '0' ? 'selected' : '' ?>>Usuario</option>
                </select>
            </div>
            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="<?= site_url('admin/users') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
            </div>
        </form>
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
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">ID</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Nombre</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Email</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: 600;">#<?= $user->id ?></td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;"><?= esc($user->name) ?></div>
                        <div style="font-size: 0.8rem; color: #64748b;"><?= esc($user->company ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px; color: #475569;"><?= esc($user->email) ?></td>
                    <td style="padding: 12px;">
                        <?php if ($user->is_admin ?? false): ?>
                            <span class="pill" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.7rem;">Admin</span>
                        <?php endif; ?>
                        <?php if ($user->is_active): ?>
                            <span class="pill estado--activa" style="font-size: 0.7rem;">Activo</span>
                        <?php else: ?>
                            <span class="pill estado--inactiva" style="font-size: 0.7rem;">Inactivo</span>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/users/toggle-api-access/' . $user->id) ?>" style="text-decoration: none;" title="Alternar Acceso API">
                            <?php if ($user->api_access ?? false): ?>
                                <span class="pill" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.7rem; cursor: pointer;">API OK ✅</span>
                            <?php else: ?>
                                <span class="pill" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.7rem; cursor: pointer;">API OFF ❌</span>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 5px;">
                            <a href="<?= site_url('admin/users/impersonate/' . $user->id) ?>" class="btn secondary btn-impersonate" data-name="<?= esc($user->name) ?>" style="padding: 4px 8px; font-size: 0.75rem; background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;" title="Entrar como este usuario (Impersonate)">🔑</a>
                            <a href="<?= site_url('admin/users/email/' . $user->id) ?>" class="btn secondary" style="padding: 4px 8px; font-size: 0.75rem;" title="Enviar Email">✉️</a>
                            <a href="<?= site_url('admin/users/edit/' . $user->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem;" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/users/delete/' . $user->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fee2e2;" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">🗑️</a>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const impButtons = document.querySelectorAll('.btn-impersonate');
        impButtons.forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const name = this.getAttribute('data-name');
                
                Swal.fire({
                    title: '¿Entrar como ' + name + '?',
                    html: 'Verás la plataforma exactamente como este usuario. <br>Para volver, usa el enlace en la cabecera.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, entrar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        popup: 've-swal',
                        title: 've-swal-title',
                        htmlContainer: 've-swal-text',
                        confirmButton: 'btn ve-swal-confirm',
                        cancelButton: 'btn btn_header--ghost ve-swal-cancel',
                        icon: 've-swal-icon'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    });
</script>

<?= view('partials/footer') ?>
</body>
</html>

