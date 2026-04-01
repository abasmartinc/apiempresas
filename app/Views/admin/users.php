<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        :root {
            --kpi-blue: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
        .kpi-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); 
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100px;
            background: var(--kpi-color);
            opacity: 0.05;
            border-radius: 0 0 0 100%;
            pointer-events: none;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: #f1f5f9;
            border-radius: 100px;
            margin-top: 1rem;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--kpi-color);
            border-radius: 100px;
            transition: width 1s ease-out;
        }
    </style>
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

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <span class="kpi-label">Total Usuarios</span>
            <span class="kpi-value"><?= number_format($stats['total_users'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios registrados en la plataforma</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="16" y1="11" x2="22" y2="11"></line></svg>
            </div>
            <span class="kpi-label">Nuevos (Este Mes)</span>
            <span class="kpi-value">+<?= number_format($stats['new_users_month'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Altas registradas desde el día 1</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </div>
            <span class="kpi-label">Activos (30d)</span>
            <span class="kpi-value"><?= number_format($stats['active_users_30d'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios con login reciente</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-rose);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <span class="kpi-label">Administradores</span>
            <span class="kpi-value"><?= number_format($stats['admin_users'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Cuentas con acceso total</span>
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

