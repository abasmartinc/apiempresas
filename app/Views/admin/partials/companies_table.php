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
