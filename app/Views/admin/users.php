<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding">
    <div class="page-header">
        <h1 class="title">Usuarios Registrados</h1>
        <div class="flex-gap-10">
            <a href="<?= site_url('admin/users/create') ?>" class="btn">Nuevo Usuario</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/users') ?>" method="get" class="grid admin-filter-form">
            <div>
                <label class="input-label">Buscar por nombre, email o empresa</label>
                <input type="text" name="q" class="input w-full" placeholder="Ej: Juan Pérez..." value="<?= esc($q) ?>">
            </div>
            <div>
                <label class="input-label">Estado</label>
                <select name="is_active" class="input w-full">
                    <option value="">Todos</option>
                    <option value="1" <?= $is_active === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $is_active === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div>
                <label class="input-label">Rol</label>
                <select name="is_admin" class="input w-full">
                    <option value="">Todos</option>
                    <option value="1" <?= $is_admin === '1' ? 'selected' : '' ?>>Admin</option>
                    <option value="0" <?= $is_admin === '0' ? 'selected' : '' ?>>Usuario</option>
                </select>
            </div>
                <a href="<?= site_url('admin/users') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
            </div>
            <?php if (isset($pager) && $pager->getTotal() > 0): ?>
            <div class="bulk-actions">
                <form action="<?= site_url('admin/users/email/bulk') ?>" method="post" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="q" value="<?= esc($q) ?>">
                    <input type="hidden" name="is_active" value="<?= esc($is_active) ?>">
                    <input type="hidden" name="is_admin" value="<?= esc($is_admin) ?>">
                    <input type="hidden" name="select_all_filtered" value="1">
                    <button type="submit" class="btn secondary btn-sm">
                        ✉️ Enviar Email a toda la lista (<?= $pager->getTotal() ?> usuarios)
                    </button>
                    <span class="text-xs text-slate ml-2">
                        (Incluye a todos los usuarios de todas las páginas que coincidan con el filtro)
                    </span>
                </form>
            </div>
            <?php endif; ?>
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

    <div class="card admin-table-wrapper">
        <form id="bulk-action-form" action="<?= site_url('admin/users/email/bulk') ?>" method="post">
            <?= csrf_field() ?>
            <table class="admin-table">
            <thead>
                <tr>
                    <th style="padding: 12px; width: 40px;"><input type="checkbox" id="select-all"></th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">ID</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Nombre</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Email</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><input type="checkbox" name="user_ids[]" value="<?= $user->id ?>" class="user-checkbox"></td>
                    <td class="font-bold">#<?= $user->id ?></td>
                    <td>
                        <div class="font-bold"><?= esc($user->name) ?></div>
                        <div class="text-xs text-slate"><?= esc($user->company ?: '-') ?></div>
                        <?php if (isset($user->source_app) && $user->source_app === 'alertaempresas'): ?>
                            <span class="pill pill-sm" style="background: #fff1f2; color: #be123c; border: 1px solid #fda4af;">Alertas 🔔</span>
                        <?php else: ?>
                            <span class="pill pill-sm" style="background: #fdf4ff; color: #86198f; border: 1px solid #f0abfc;">API 🚀</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-slate-darker"><?= esc($user->email) ?></td>
                    <td>
                        <?php if ($user->is_admin ?? false): ?>
                            <span class="pill pill-admin">Admin</span>
                        <?php endif; ?>
                        <?php if ($user->is_active): ?>
                            <span class="pill estado--activa pill-sm">Activo</span>
                        <?php else: ?>
                            <span class="pill estado--inactiva pill-sm">Inactivo</span>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/users/toggle-api-access/' . $user->id) ?>" class="no-underline" title="Alternar Acceso API">
                            <?php if ($user->api_access ?? false): ?>
                                <span class="pill pill-api-ok">API OK ✅</span>
                            <?php else: ?>
                                <span class="pill pill-api-off">API OFF ❌</span>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td style="padding: 12px;">
                        <div class="flex-gap-5">
                            <a href="<?= site_url('admin/users/impersonate/' . $user->id) ?>" class="btn secondary btn-impersonate btn-impersonate-style" data-name="<?= esc($user->name) ?>" title="Entrar como este usuario (Impersonate)">🔑</a>
                            <a href="<?= site_url('admin/users/email/' . $user->id) ?>" class="btn secondary btn-sm" title="Enviar Email">✉️</a>
                            <a href="<?= site_url('admin/users/edit/' . $user->id) ?>" class="btn ghost btn-sm" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/users/delete/' . $user->id) ?>" class="btn ghost btn-danger-ghost" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </form>
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

        // Bulk Selection Logic
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const bulkForm = document.getElementById('bulk-action-form');
        
        // Create floating action bar
        const actionContainer = document.createElement('div');
        actionContainer.className = 'bulk-floating-bar';
        actionContainer.innerHTML = `
            <span class="font-bold text-slate-darker"><span id="selected-count">0</span> seleccionados</span>
            <button type="button" class="btn" id="btn-bulk-email">✉️ Enviar a seleccionados</button>
        `;
        document.body.appendChild(actionContainer);

        function updateState() {
            const selected = document.querySelectorAll('.user-checkbox:checked').length;
            document.getElementById('selected-count').innerText = selected;
            if (selected > 0) {
                actionContainer.style.display = 'flex';
            } else {
                actionContainer.style.display = 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateState);
        });

        const btnBulk = document.getElementById('btn-bulk-email');
        if(btnBulk){
             btnBulk.addEventListener('click', function() {
                bulkForm.submit();
            });
        }
    });
</script>

<?= view('partials/footer') ?>
</body>
</html>

