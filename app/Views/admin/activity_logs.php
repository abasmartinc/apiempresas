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

        <!-- Filters -->
        <div class="card" style="margin-bottom: 20px; padding: 20px;">
            <form method="get" action="<?= site_url('admin/activity-logs') ?>" class="grid" style="grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label>User</label>
                    <select name="user_id" class="input">
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
</body>
</html>
