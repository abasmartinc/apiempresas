<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <style>
        .activity-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .activity-table th, .activity-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .activity-table th { background-color: #f8f9fa; font-weight: 600; }
        .activity-table tr:hover { background-color: #f8f9fa; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-login { background: #d1fae5; color: #065f46; }
        .badge-logout { background: #fee2e2; color: #991b1b; }
        .badge-register { background: #dbeafe; color: #1e40af; }
        .badge-plan { background: #fef3c7; color: #92400e; }
        .badge-api { background: #e0e7ff; color: #3730a3; }
        .badge-visit { background: #f3e8ff; color: #6b21a8; }
        .details { font-size: 12px; color: #666; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .back-btn { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #2152FF; font-weight: 500; }
        .back-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <?= view('partials/header_inner') ?>

    <main class="container" style="padding: 40px 20px;">
        <a href="<?= site_url('admin/activity-logs') ?>" class="back-btn">&larr; Back to All Logs</a>

        <h1>Activity Logs for User #<?= esc($user_id) ?></h1>
        <p style="color: #666; margin-bottom: 30px;">Showing recent activity for this user (Max 200)</p>

        <!-- Activity Table -->
        <table class="activity-table">
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
                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                            No activity logs found for this user.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td>
                                <strong><?= esc($log['user_name'] ?? 'Unknown') ?></strong><br>
                                <small style="color: #666;"><?= esc($log['user_email'] ?? '') ?></small><br>
                                <small style="color: #999;">ID: <?= $log['user_id'] ?></small>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'badge';
                                if (strpos($log['action'], 'login') !== false) $badgeClass .= ' badge-login';
                                elseif (strpos($log['action'], 'logout') !== false) $badgeClass .= ' badge-logout';
                                elseif (strpos($log['action'], 'register') !== false) $badgeClass .= ' badge-register';
                                elseif (strpos($log['action'], 'plan') !== false || strpos($log['action'], 'subscription') !== false) $badgeClass .= ' badge-plan';
                                elseif (strpos($log['action'], 'api') !== false) $badgeClass .= ' badge-api';
                                elseif (strpos($log['action'], 'visit') !== false) $badgeClass .= ' badge-visit';
                                ?>
                                <span class="<?= $badgeClass ?>"><?= esc($log['action']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($log['details'])): ?>
                                    <div class="details" title="<?= esc($log['details']) ?>">
                                        <?= esc($log['details']) ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($log['ip_address'] ?? '—') ?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
