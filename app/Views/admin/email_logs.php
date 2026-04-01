<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --kpi-blue: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --kpi-pink: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
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
        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: #f1f5f9;
            border-radius: 100px;
            margin-top: 1rem;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--kpi-color);
            border-radius: 100px;
            transition: width 1s ease-out;
        }
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-success { background: #ecfdf5; color: #065f46; border: 1px solid #d1fae5; }
        .status-error { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
        .tracking-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .dot-placeholder { background: #f1f5f9; border: 2px solid #e2e8f0; }
        .dot-active { background: #10b981; box-shadow: 0 0 12px rgba(16, 185, 129, 0.5); border: 2px solid #fff; }
        .admin-table-wrapper { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05); overflow: hidden; }
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
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
            </div>
            <span class="kpi-label">Total Enviados</span>
            <span class="kpi-value"><?= number_format($stats['total_sent'], 0, ',', '.') ?></span>
            <span class="kpi-sub">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                Confirmados
            </span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <span class="kpi-label">Tasa de Apertura</span>
            <span class="kpi-value"><?= $stats['open_rate'] ?>%</span>
            <span class="kpi-sub"><?= number_format($stats['total_opened'], 0, ',', '.') ?> aperturas únicas</span>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $stats['open_rate'] ?>%;"></div>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
            </div>
            <span class="kpi-label">CTR (Clicks)</span>
            <span class="kpi-value"><?= $stats['click_rate'] ?>%</span>
            <span class="kpi-sub"><?= number_format($stats['total_clicked'], 0, ',', '.') ?> clics detectados</span>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $stats['click_rate'] ?>%;"></div>
            </div>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-orange);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <span class="kpi-label">Conversión</span>
            <span class="kpi-value"><?= $stats['conversion_rate'] ?>%</span>
            <span class="kpi-sub"><?= number_format($stats['total_logged'], 0, ',', '.') ?> logins registrados</span>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $stats['conversion_rate'] ?>%;"></div>
            </div>
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
