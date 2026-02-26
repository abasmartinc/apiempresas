<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        /* Sync with Select2 styles */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding: 6px 12px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a !important;
            font-size: 0.9rem !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: #cbd5e1; }
        .kpi-label { font-size: 0.875rem; color: #64748b; font-weight: 500; }
        .kpi-value { font-size: 1.75rem; font-weight: 700; color: #0f172a; }
        .kpi-sub { font-size: 0.75rem; color: #94a3b8; }
        .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .tracking-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .dot-placeholder { background: #e2e8f0; border: 1px solid #cbd5e1; }
        .dot-active { background: #22c55e; box-shadow: 0 0 8px rgba(34, 197, 94, 0.4); border: 1px solid #16a34a; }
        .admin-table-wrapper { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding">
    <div class="page-header">
        <div>
            <h1 class="title" style="color: #0f172a;">KPIs de Email Marketing</h1>
            <p style="color: #64748b;">Seguimiento profesional de comunicaciones con clientes</p>
        </div>
        <div class="flex-gap-10">
            <a href="<?= site_url('admin/users') ?>" class="btn">Mandar nuevo Email</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- KPIs Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <span class="kpi-label">Total Enviados</span>
            <span class="kpi-value"><?= number_format($stats['total_sent']) ?></span>
            <span class="kpi-sub">Emails entregados con éxito</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Tasa de Apertura</span>
            <span class="kpi-value" style="color: #2152FF;"><?= $stats['open_rate'] ?>%</span>
            <span class="kpi-sub"><?= $stats['total_opened'] ?> aperturas únicas</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Click-Through Rate (CTR)</span>
            <span class="kpi-value" style="color: #12b48a;"><?= $stats['click_rate'] ?>%</span>
            <span class="kpi-sub"><?= $stats['total_clicked'] ?> clics detectados</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Conversión (Logs)</span>
            <span class="kpi-value" style="color: #f59e0b;"><?= $stats['conversion_rate'] ?>%</span>
            <span class="kpi-sub"><?= $stats['total_logged'] ?> logins desde email</span>
        </div>
    </div>

    </div>

    <!-- Buscador / Filtros -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <form action="<?= site_url('admin/email-logs') ?>" method="get">
            <div class="grid" style="grid-template-columns: 2fr 1.5fr 0.8fr 0.8fr 0.8fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Asunto / Texto</label>
                    <input type="text" name="q" class="input" style="width: 100%;" placeholder="Asunto del correo..." value="<?= esc($q ?? '') ?>">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Usuario</label>
                    <select name="user_id" class="input select2-user" style="width: 100%;">
                        <option value="">Todos los usuarios</option>
                        <?php foreach($all_users as $user): ?>
                            <option value="<?= $user->id ?>" <?= ($user_id ?? '') == $user->id ? 'selected' : '' ?>>
                                <?= esc($user->name) ?> (<?= esc($user->email) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Abierto</label>
                    <select name="opened" class="input" style="width: 100%;">
                        <option value="">Todos</option>
                        <option value="yes" <?= ($opened ?? '') === 'yes' ? 'selected' : '' ?>>Sí</option>
                        <option value="no" <?= ($opened ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Clic</label>
                    <select name="clicked" class="input" style="width: 100%;">
                        <option value="">Todos</option>
                        <option value="yes" <?= ($clicked ?? '') === 'yes' ? 'selected' : '' ?>>Sí</option>
                        <option value="no" <?= ($clicked ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Login</label>
                    <select name="logged" class="input" style="width: 100%;">
                        <option value="">Todos</option>
                        <option value="yes" <?= ($logged ?? '') === 'yes' ? 'selected' : '' ?>>Sí</option>
                        <option value="no" <?= ($logged ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Desde</label>
                    <input type="date" name="date_from" class="input" style="width: 100%;" value="<?= esc($date_from ?? '') ?>">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Hasta</label>
                    <input type="date" name="date_to" class="input" style="width: 100%;" value="<?= esc($date_to ?? '') ?>">
                </div>
                <div style="display: flex; gap: 5px;">
                    <button type="submit" class="btn" style="padding: 10px 20px;">Filtrar</button>
                    <a href="<?= site_url('admin/email-logs') ?>" class="btn ghost" title="Limpiar filtros" style="padding: 10px 15px;">🔄</a>
                </div>
            </div>
            
            <div style="margin-top: 1rem; display: flex; gap: 10px; align-items: center;">
                <label style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Estado de envío:</label>
                <div style="display: flex; gap: 15px;">
                    <label style="font-size: 0.85rem; color: #475569; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                        <input type="radio" name="status" value="" <?= ($status ?? '') === '' ? 'checked' : '' ?>> Todos
                    </label>
                    <label style="font-size: 0.85rem; color: #475569; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                        <input type="radio" name="status" value="success" <?= ($status ?? '') === 'success' ? 'checked' : '' ?>> Éxito
                    </label>
                    <label style="font-size: 0.85rem; color: #475569; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                        <input type="radio" name="status" value="error" <?= ($status ?? '') === 'error' ? 'checked' : '' ?>> Fallido
                    </label>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="admin-table-wrapper">
        <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">Historial Detallado</h3>
            <span style="font-size: 0.85rem; color: #64748b;">Mostrando últimos envíos</span>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Destinatario</th>
                        <th>Asunto</th>
                        <th style="text-align: center;">Abierto</th>
                        <th style="text-align: center;">Clic</th>
                        <th style="text-align: center;">Login</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">No hay logs registrados todavía</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.85rem; color: #64748b;">
                                    <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="color: #1e293b; font-weight: 600;"><?= esc($log->user_name ?: 'Desconocido') ?></span>
                                        <span style="font-size: 0.8rem; color: #94a3b8;"><?= esc($log->user_email) ?></span>
                                    </div>
                                </td>
                                <td style="color: #334155; font-weight: 500;"><?= esc($log->subject) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($log->opened_at): ?>
                                        <span class="tracking-dot dot-active" title="Abierto el <?= date('d/m/Y H:i', strtotime($log->opened_at)) ?>"></span>
                                    <?php else: ?>
                                        <span class="tracking-dot dot-placeholder" title="No abierto"></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($log->clicked_at): ?>
                                        <span class="tracking-dot dot-active" title="Clic el <?= date('d/m/Y H:i', strtotime($log->clicked_at)) ?>"></span>
                                    <?php else: ?>
                                        <span class="tracking-dot dot-placeholder" title="Sin clics"></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($log->logged_in_at): ?>
                                        <span class="tracking-dot dot-active" title="Login el <?= date('d/m/Y H:i', strtotime($log->logged_in_at)) ?>"></span>
                                    <?php else: ?>
                                        <span class="tracking-dot dot-placeholder" title="Sin login"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $log->status ?>">
                                        <?= $log->status == 'success' ? 'Enviado' : 'Fallido' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 1.5rem; border-top: 1px solid #f1f5f9;">
            <?= $pager->links('default', 'admin_full') ?>
        </div>
    </div>
</main>

<?= view('partials/footer') ?>
<!-- Scripts Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-user').select2({
        placeholder: 'Selecciona un usuario',
        allowClear: true,
        width: '100%'
    });
});
</script>
</body>
</html>
