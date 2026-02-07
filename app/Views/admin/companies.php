<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .kpi-card {
            position: relative;
            overflow: hidden;
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
        
        .kpi-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .kpi-value {
            font-size: 1.8rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1.2;
        }

        .text-red { color: #ef4444; }
        .text-green { color: #10b981; }
        .text-primary { color: #2152ff; }
    </style>
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

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="color: #6366f1;">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
                Total Empresas
            </div>
            <div class="kpi-value text-primary"><?= number_format($kpis['total'], 0, ',', '.') ?></div>
        </div>

        <div class="kpi-card" style="color: #ef4444;">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                Sin CIF
            </div>
            <div class="kpi-value <?= $kpis['sin_cif'] > 0 ? 'text-red' : 'text-green' ?>"><?= number_format($kpis['sin_cif'], 0, ',', '.') ?></div>
        </div>

        <div class="kpi-card" style="color: #f59e0b;">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Sin Dirección
            </div>
            <div class="kpi-value <?= $kpis['sin_direccion'] > 0 ? 'text-red' : 'text-green' ?>"><?= number_format($kpis['sin_direccion'], 0, ',', '.') ?></div>
        </div>

        <div class="kpi-card" style="color: #10b981;">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Sin Estado
            </div>
            <div class="kpi-value <?= $kpis['sin_estado'] > 0 ? 'text-red' : 'text-green' ?>"><?= number_format($kpis['sin_estado'], 0, ',', '.') ?></div>
        </div>

        <div class="kpi-card" style="color: #8b5cf6;">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Sin CNAE
            </div>
            <div class="kpi-value <?= $kpis['sin_cnae'] > 0 ? 'text-red' : 'text-green' ?>"><?= number_format($kpis['sin_cnae'], 0, ',', '.') ?></div>
        </div>

        <div class="kpi-card" style="color: #ec4899;">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0 1 12 10.5c-1.393 0-2.671-.587-3.575-1.532V21m10.5 0h.75a.75.75 0 0 0 .75-.75V11.25a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75V21m-10.5 0h-.75a.75.75 0 0 1-.75-.75V11.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75V21" />
                </svg>
                Sin Reg. Mercantil
            </div>
            <div class="kpi-value <?= $kpis['sin_registro_mercantil'] > 0 ? 'text-red' : 'text-green' ?>"><?= number_format($kpis['sin_registro_mercantil'], 0, ',', '.') ?></div>
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

