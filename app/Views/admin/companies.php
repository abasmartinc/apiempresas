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
        <h1 class="title">Gestión de Empresas</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/companies/create') ?>" class="btn">Nueva Empresa</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Buscador -->
    <div class="card" style="margin-bottom: 2rem; padding: 20px;">
        <form action="<?= site_url('admin/companies') ?>" method="get" style="display: flex; gap: 10px;">
            <input type="text" name="q" class="input" placeholder="Buscar por Nombre o CIF..." value="<?= esc($q) ?>" style="flex: 1;">
            <button type="submit" class="btn">Buscar</button>
            <?php if ($q): ?>
                <a href="<?= site_url('admin/companies') ?>" class="btn ghost">Limpiar</a>
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

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">CIF</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Nombre</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Provincia / Municipio</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: 700; color: #2152ff;"><?= esc($company->cif) ?></td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;"><?= esc($company->company_name) ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($company->cnae_label) ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-size: 0.85rem;"><?= esc($company->registro_mercantil) ?></div>
                        <div style="font-size: 0.75rem; color: #94a3b8;"><?= esc($company->municipality) ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <span class="pill" style="font-size: 0.7rem; background: <?= $company->estado === 'ACTIVA' ? '#dcfce7; color: #166534;' : '#f1f5f9; color: #64748b;' ?>">
                            <?= esc($company->estado ?: 'N/A') ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 5px;">
                            <a href="<?= site_url('admin/companies/edit/' . $company->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem;" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/companies/delete/' . $company->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fee2e2;" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta empresa?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($companies)): ?>
                <tr>
                    <td colspan="5" style="padding: 40px; text-align: center; color: #94a3b8;">No se encontraron empresas.</td>
                </tr>
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

