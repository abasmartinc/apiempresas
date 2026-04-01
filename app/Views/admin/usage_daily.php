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
        .pill { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; text-align: center; }
        .filter-container { background: white; border-radius: 20px; padding: 1.5rem; border: 1px solid #e2e8f0; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .filter-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1.5rem; align-items: end; }
        .input-group { position: relative; }
        .input-group svg { position: absolute; left: 12px; top: 38px; color: #94a3b8; pointer-events: none; }
        .input-group .input, .input-group .select2-container--default .select2-selection--single { padding-left: 40px !important; }
        .quick-filters { display: flex; gap: 8px; margin-top: 1rem; flex-wrap: wrap; }
        .btn-quick { 
            padding: 6px 12px; 
            font-size: 0.75rem; 
            border-radius: 100px; 
            background: #f1f5f9; 
            color: #475569; 
            border: 1px solid #e2e8f0; 
            font-weight: 600; 
            transition: all 0.2s; 
            cursor: pointer;
        }
        .btn-quick:hover { background: #e2e8f0; color: #1e293b; }
        .btn-quick.active { background: #2152ff; color: white; border-color: #2152ff; }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Uso Diario API</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"></path></svg>
            </div>
            <span class="kpi-label">Consumo (30d)</span>
            <span class="kpi-value"><?= number_format($stats['total_30d'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Total peticiones último mes</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </div>
            <span class="kpi-label">Media Diaria</span>
            <span class="kpi-value"><?= number_format($stats['avg_daily'], 1, ',', '.') ?></span>
            <span class="kpi-sub">Promedio de peticiones/día</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-orange);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
            </div>
            <span class="kpi-label">Pico Máximo</span>
            <span class="kpi-value"><?= number_format($stats['peak_daily'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Récord de peticiones en un día</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <span class="kpi-label">Este Mes</span>
            <span class="kpi-value"><?= number_format($stats['current_month'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Acumulado desde el día 1</span>
        </div>
    </div>

    <!-- Filtros Mejorados -->
    <div class="filter-container">
        <form action="<?= site_url('admin/usage-daily') ?>" method="get" id="filter-form">
            <div class="filter-row">
                <div class="input-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.025em;">Usuario Principal</label>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <select name="user_id" id="user-select" class="input" style="width: 100%;">
                        <option value="">Todos los usuarios</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u->id ?>" <?= $user_id == $u->id ? 'selected' : '' ?>>
                                <?= esc($u->name) ?> (<?= esc($u->email) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.025em;">Fecha Inicio</label>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <input type="date" name="start_date" id="start_date" class="input" value="<?= esc($start_date) ?>" style="width: 100%;">
                </div>

                <div class="input-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.025em;">Fecha Fin</label>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <input type="date" name="end_date" id="end_date" class="input" value="<?= esc($end_date) ?>" style="width: 100%;">
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn" style="height: 42px; padding: 0 25px;">Filtrar</button>
                    <a href="<?= site_url('admin/usage-daily') ?>" class="btn ghost" style="height: 42px; width: 42px; display: flex; align-items: center; justify-content: center; padding: 0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    </a>
                </div>
            </div>
            
            <div class="quick-filters">
                <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-right: 8px; align-self: center;">Accesos:</span>
                <button type="button" class="btn-quick" data-range="today">Hoy</button>
                <button type="button" class="btn-quick" data-range="7d">Últimos 7 días</button>
                <button type="button" class="btn-quick" data-range="30d">Últimos 30 días</button>
                <button type="button" class="btn-quick" data-range="month">Este Mes</button>
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

        // JS para Filtros Rápidos
        const quickButtons = document.querySelectorAll('.btn-quick');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const filterForm = document.getElementById('filter-form');

        quickButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const range = this.getAttribute('data-range');
                const today = new Date();
                let start = new Date();
                let end = new Date();

                switch(range) {
                    case 'today':
                        break;
                    case '7d':
                        start.setDate(today.getDate() - 7);
                        break;
                    case '30d':
                        start.setDate(today.getDate() - 30);
                        break;
                    case 'month':
                        start = new Date(today.getFullYear(), today.getMonth(), 1);
                        break;
                }

                startDateInput.value = start.toISOString().split('T')[0];
                endDateInput.value = end.toISOString().split('T')[0];
                
                filterForm.submit();
            });
        });
    });
</script>

</body>
</html>

