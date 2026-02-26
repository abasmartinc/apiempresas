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
        <h1 class="title">Gestión de API Keys</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/api-keys/create') ?>" class="btn">Nueva API Key</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/api-keys') ?>" method="get" class="grid" style="grid-template-columns: 1fr 1fr 1fr auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Búsqueda</label>
                <input type="text" name="q" class="input" style="width: 100%;" placeholder="Ej: Nombre o API Key..." value="<?= esc($q ?? '') ?>">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Usuario</label>
                <select name="user_id" id="user-select" class="input" style="width: 100%;">
                    <option value="">Todos los usuarios</option>
                    <?php if (isset($users)): foreach ($users as $u): ?>
                        <option value="<?= $u->id ?>" <?= ($user_id ?? '') == $u->id ? 'selected' : '' ?>>
                            <?= esc($u->name) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Estado</label>
                <select name="is_active" class="input" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="1" <?= ($is_active ?? '') === '1' ? 'selected' : '' ?>>Activa</option>
                    <option value="0" <?= ($is_active ?? '') === '0' ? 'selected' : '' ?>>Inactiva</option>
                </select>
            </div>
            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="<?= site_url('admin/api-keys') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
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

<!-- Select2 para hacer el select buscable -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajustes para Select2 encaje con el .input */
    .select2-container--default .select2-selection--single {
        border-radius: 8px;
        height: 42px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a;
        font-size: 0.85rem;
        line-height: normal;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        font-size: 0.85rem;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #2152ff;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('user-select')) {
            $('#user-select').select2({
                placeholder: "Buscar usuario...",
                width: '100%'
            });
        }
    });
</script>

</body>
</html>

