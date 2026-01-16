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
        <h1 class="title">Planes de la API</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/plans/create') ?>" class="btn">Nuevo Plan</a>
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
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Nombre / Slug</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Cuota Mensual</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Rate Limit (min)</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Precio</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;"><?= esc($plan->name) ?></div>
                        <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;"><?= esc($plan->slug) ?></div>
                    </td>
                    <td style="padding: 12px; font-weight: 600;">
                        <?= number_format($plan->monthly_quota, 0, ',', '.') ?> reqs
                    </td>
                    <td style="padding: 12px; color: #475569;">
                        <?= $plan->rate_limit_per_min ?> req/min
                    </td>
                    <td style="padding: 12px;">
                        <span style="font-weight: 700; color: #16a34a;">
                            <?= number_format($plan->price_monthly, 2, ',', '.') ?> €
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <?php if ($plan->is_active): ?>
                            <span class="pill estado--activa" style="font-size: 0.7rem;">Activo</span>
                        <?php else: ?>
                            <span class="pill estado--inactiva" style="font-size: 0.7rem;">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 5px;">
                            <a href="<?= site_url('admin/plans/edit/' . $plan->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem;" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/plans/delete/' . $plan->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fee2e2;" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este plan?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>

