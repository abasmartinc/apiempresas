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
        <h1 class="title">Uso Diario API</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/usage-daily') ?>" method="get" class="grid" style="grid-template-columns: 1fr auto auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Usuario</label>
                <select name="user_id" id="user-select" class="input" style="width: 100%;">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u->id ?>" <?= $user_id == $u->id ? 'selected' : '' ?>>
                            <?= esc($u->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Desde</label>
                <input type="date" name="start_date" class="input" value="<?= esc($start_date) ?>">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Hasta</label>
                <input type="date" name="end_date" class="input" value="<?= esc($end_date) ?>">
            </div>
            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="<?= site_url('admin/usage-daily') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Fecha</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Plan ID</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Peticiones</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Última Actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usage as $row): ?>
                <?php $row = (object)$row; ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: 600;">
                        <?= date('d/m/Y', strtotime($row->date)) ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($row->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($row->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <span class="pill" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                            Plan #<?= $row->plan_id ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <span style="font-size: 1.1rem; font-weight: 700; color: #2152ff;">
                            <?= number_format($row->requests_count, 0, ',', '.') ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 0.8rem; color: #64748b;">
                        <?= date('d/m/Y H:i', strtotime($row->updated_at)) ?>
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

