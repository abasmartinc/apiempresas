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
                <select name="user_id" class="input" style="width: 100%;">
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
</body>
</html>

