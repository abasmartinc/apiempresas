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
        <h1 class="title">
            Gestión de Empresas 
            <?php if(isset($pager)): ?>
                <span class="text-slate font-normal" style="font-size: 1rem;">(<?= $pager->getTotal() ?> resultados)</span>
            <?php endif; ?>
        </h1>
        <div class="flex-gap-10">
            <a href="<?= site_url('admin/companies/create') ?>" class="btn">Nueva Empresa</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Buscador -->
    <div class="card mb-8 p-5">
        <form action="<?= site_url('admin/companies') ?>" method="get" class="flex-gap-10 flex-center" style="display: flex;">
            <input type="text" name="q" class="input flex-1" placeholder="Buscar por Nombre o CIF..." value="<?= esc($q) ?>">
            
            <label class="flex-gap-5 flex-center cursor-pointer select-none text-09 text-slate-500">
                <input type="checkbox" name="no_cif" value="1" <?= isset($filters['no_cif']) && $filters['no_cif'] ? 'checked' : '' ?>>
                Sin CIF
            </label>

            <button type="submit" class="btn">Buscar</button>
            <?php if ($q || (isset($filters['no_cif']) && $filters['no_cif'])): ?>
                <a href="<?= site_url('admin/companies') ?>" class="btn ghost">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>CIF</th>
                    <th>Nombre</th>
                    <th>Provincia / Municipio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                <tr>
                    <td class="font-bold-700 text-primary"><?= esc($company->cif) ?></td>
                    <td>
                        <div class="font-bold"><?= esc($company->company_name ?? '-') ?></div>
                        <div class="text-xs text-slate"><?= esc($company->cnae_label ?? '') ?></div>
                    </td>
                    <td>
                        <div class="text-sm"><?= esc($company->registro_mercantil ?? '') ?></div>
                        <div class="text-xs text-slate-lighter"><?= esc($company->municipality ?? '') ?></div>
                    </td>
                    <td>
                        <span class="pill pill-sm <?= $company->estado === 'ACTIVA' ? 'pill-green' : 'pill-slate' ?>">
                            <?= esc($company->estado ?: 'N/A') ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex-gap-5">
                            <a href="<?= site_url('admin/companies/edit/' . $company->id) ?>" class="btn ghost btn-sm" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/companies/delete/' . $company->id) ?>" class="btn ghost btn-danger-ghost" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta empresa?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($companies)): ?>
                <tr>
                    <td colspan="5" class="p-10 text-center text-slate-lighter">No se encontraron empresas.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        <?= $pager->links('default', 'admin_full') ?>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>

