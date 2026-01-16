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
        <h1 class="title">Gestión de Suscripciones</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/subscriptions/create') ?>" class="btn">Nueva Suscripción</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/subscriptions') ?>" method="get" class="grid" style="grid-template-columns: 1fr 1fr auto auto; gap: 1rem; align-items: end;">
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
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Plan</label>
                <select name="plan_id" class="input" style="width: 100%;">
                    <option value="">Todos los planes</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p->id ?>" <?= $plan_id == $p->id ? 'selected' : '' ?>>
                            <?= esc($p->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Estado</label>
                <select name="status" class="input" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Activa</option>
                    <option value="trialing" <?= $status == 'trialing' ? 'selected' : '' ?>>Prueba</option>
                    <option value="past_due" <?= $status == 'past_due' ? 'selected' : '' ?>>Vencida</option>
                    <option value="canceled" <?= $status == 'canceled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="<?= site_url('admin/subscriptions') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
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
        <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Plan</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Periodo Actual</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $s): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($s->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($s->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <span class="pill" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                            <?= esc($s->plan_name ?: 'Plan #' . $s->plan_id) ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <?php 
                        $statusClass = '';
                        switch($s->status) {
                            case 'active': $statusClass = 'estado--activa'; break;
                            case 'trialing': $statusClass = 'pill'; break;
                            case 'past_due': $statusClass = 'estado--inactiva'; break;
                            case 'canceled': $statusClass = 'estado--inactiva'; break;
                        }
                        ?>
                        <span class="pill <?= $statusClass ?>" style="font-size: 0.7rem;">
                            <?= strtoupper($s->status) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 0.8rem;">
                        <div style="color: #16a34a;">Inicio: <?= date('d/m/Y', strtotime($s->current_period_start)) ?></div>
                        <div style="color: #ef4444;">Fin: <?= date('d/m/Y', strtotime($s->current_period_end)) ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 5px;">
                            <a href="<?= site_url('admin/subscriptions/edit/' . $s->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem;" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/subscriptions/delete/' . $s->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fee2e2;" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta suscripción?')">🗑️</a>
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

