<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <style>
        
        /* Activity Logs specific styles */
        .act-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #e2e8f0; color: #475569; }
        .act-badge-login { background: #d1fae5; color: #065f46; }
        .act-badge-logout { background: #fee2e2; color: #991b1b; }
        .act-badge-register { background: #dbeafe; color: #1e40af; }
        .act-badge-plan { background: #fef3c7; color: #92400e; }
        .act-badge-api { background: #e0e7ff; color: #3730a3; }
        .act-badge-visit { background: #f3e8ff; color: #6b21a8; }
        
        .filters label { display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem; }
        
        .export-btn { background: #10b981; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
        .export-btn:hover { background: #059669; }
        
        .details { font-size: 0.8rem; color: #64748b; max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        /* Table Styles matching logs.php */
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th { padding: 12px; color: #64748b; font-size: 0.85rem; text-align: left; border-bottom: 2px solid #f1f5f9; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; vertical-align: middle; }
        
        .user-info strong { color: #0f172a; font-weight: 600; }
        .user-info small { color: #64748b; display: block; line-height: 1.3; }

        /* KPI Premium Styles */
        :root {
            --kpi-indigo: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-amber: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-violet: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
        .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px;
            background: var(--kpi-color); opacity: 0.05; border-radius: 0 0 0 100%; pointer-events: none;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px; border-radius: 14px; background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;
            color: white; box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
    </style>
</head>
<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 class="title" style="margin-bottom: 4px;">Activity Logs</h1>
                <p class="subtitle" style="margin: 0;">Monitor user activity and behavior</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                 <a href="<?= site_url('admin/activity-logs/export') ?>?<?= http_build_query($filters) ?>" class="export-btn">
                    <span>Export CSV</span>
                </a>
                <a href="<?= site_url('dashboard') ?>" class="btn ghost">Back to Dashboard</a>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card" style="--kpi-color: var(--kpi-indigo);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
                <span class="kpi-label">Eventos (24h)</span>
                <span class="kpi-value"><?= number_format($stats['total_24h'], 0, ',', '.') ?></span>
                <span class="kpi-sub">Total de acciones registradas</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-emerald);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <span class="kpi-label">Logins (24h)</span>
                <span class="kpi-value"><?= number_format($stats['logins_24h'], 0, ',', '.') ?></span>
                <span class="kpi-sub">Inicios de sesión exitosos</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-violet);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <span class="kpi-label">Usuarios Activos</span>
                <span class="kpi-value"><?= number_format($stats['active_users'], 0, ',', '.') ?></span>
                <span class="kpi-sub">Usuarios con actividad hoy</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-amber);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <span class="kpi-label">Acción Top</span>
                <span class="kpi-value" style="font-size: 1.8rem; height: 1em; overflow: hidden; display: flex; align-items: center;"><?= esc(ucfirst($stats['top_action'])) ?></span>
                <span class="kpi-sub">Evento más frecuente</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <form method="get" action="<?= site_url('admin/activity-logs') ?>" class="grid" style="grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label>User</label>
                    <select name="user_id" id="user-select" class="input">
                        <option value="">All Users</option>
                        <?php if(!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>" <?= ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                                    <?= esc($user->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label>Action</label>
                    <select name="action" class="input">
                        <option value="">All actions</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?= esc($action) ?>" <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>>
                                <?= esc($action) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>From Date</label>
                    <input type="date" name="from_date" class="input" value="<?= esc($filters['from_date'] ?? '') ?>">
                </div>
                <div>
                    <label>To Date</label>
                    <input type="date" name="to_date" class="input" value="<?= esc($filters['to_date'] ?? '') ?>">
                </div>
                <div>
                    <label>Limit</label>
                    <input type="number" name="limit" class="input" value="<?= $limit ?>" min="10" max="500">
                </div>
                <div style="display: flex;">
                    <button type="submit" class="btn">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Activity Table -->
        <div class="card" style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Date/Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                No activity logs found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td style="color: #64748b;"><?= $log['id'] ?></td>
                                <td class="user-info">
                                    <strong><?= esc($log['user_name'] ?? 'Unknown') ?></strong>
                                    <small><?= esc($log['user_email'] ?? '') ?></small>
                                    <small style="font-size: 0.75rem; color: #94a3b8;">ID: <?= $log['user_id'] ?></small>
                                </td>
                                <td>
                                    <?php
                                    $actionName = $log['action'] ?? 'Unknown';
                                    $badgeClass = 'act-badge';
                                    
                                    if (empty($actionName)) {
                                        $actionName = '(empty)';
                                    }
                                    
                                    if (strpos($actionName, 'login') !== false) $badgeClass .= ' act-badge-login';
                                    elseif (strpos($actionName, 'logout') !== false) $badgeClass .= ' act-badge-logout';
                                    elseif (strpos($actionName, 'register') !== false) $badgeClass .= ' act-badge-register';
                                    elseif (strpos($actionName, 'plan') !== false || strpos($actionName, 'subscription') !== false) $badgeClass .= ' act-badge-plan';
                                    elseif (strpos($actionName, 'api') !== false) $badgeClass .= ' act-badge-api';
                                    elseif (strpos($actionName, 'visit') !== false || strpos($actionName, 'page_view') !== false) $badgeClass .= ' act-badge-visit';
                                    ?>
                                    <span class="<?= $badgeClass ?>"><?= esc($actionName) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($log['details'])): ?>
                                        <?php 
                                            $details = $log['details'];
                                            // If details is JSON, try to decode it to show something nicer
                                            if (is_string($details) && ($json = json_decode($details, true))) {
                                                if (isset($json['url'])) {
                                                    $details = $json['url'];
                                                } elseif (isset($json['path'])) {
                                                    $details = $json['path'];
                                                }
                                            }
                                        ?>
                                        <div class="details" title="<?= esc($log['details']) ?>">
                                            <?= esc($details) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #64748b; font-size: 0.85rem;"><?= esc($log['ip_address'] ?? '—') ?></td>
                                <td style="color: #64748b; font-size: 0.85rem;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p style="margin-top: 20px; color: #64748b; font-size: 0.85rem;">
            Showing <?= count($logs) ?> logs (limit: <?= $limit ?>)
        </p>
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
    });
</script>

</body>
</html>
