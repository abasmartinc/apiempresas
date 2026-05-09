<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

// Initialize the environment without running the app
CodeIgniter\Boot::bootTest($paths);

$db = \Config\Database::connect();

$queries = [
    'total_users' => 'SELECT COUNT(*) as count FROM users',
    'users_by_status' => 'SELECT status, COUNT(*) as count FROM users GROUP BY status',
    'users_by_plan' => 'SELECT api_plan, COUNT(*) as count FROM users GROUP BY api_plan',
    'top_events' => 'SELECT event_type, COUNT(*) as count FROM tracking_events GROUP BY event_type ORDER BY count DESC LIMIT 10',
    'top_actions' => 'SELECT action, COUNT(*) as count FROM user_activity_logs GROUP BY action ORDER BY count DESC LIMIT 10',
    'email_stats' => 'SELECT COUNT(*) as sent, COUNT(opened_at) as opened, COUNT(clicked_at) as clicked FROM email_logs',
    'automation_types' => 'SELECT email_type, COUNT(*) as count FROM user_email_automation GROUP BY email_type',
    'recent_activity' => 'SELECT DATE(created_at) as date, COUNT(*) as count FROM tracking_events GROUP BY date ORDER BY date DESC LIMIT 10',
    'api_usage' => 'SELECT SUM(request_count) as total_requests, DATE(usage_date) as date FROM api_usage_daily GROUP BY date ORDER BY date DESC LIMIT 10'
];

$results = [];
foreach ($queries as $key => $sql) {
    try {
        $results[$key] = $db->query($sql)->getResultArray();
    } catch (\Exception $e) {
        $results[$key] = ['error' => $e->getMessage()];
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
