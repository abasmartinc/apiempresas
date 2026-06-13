<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();
try {
    $ipAddress = '127.0.0.1';
    $subscriptionTable = $db->tableExists('user_subscriptions') ? 'user_subscriptions' : 'usersuscriptions';
    $ipUsage = $db->table('api_requests r')
        ->join($subscriptionTable . ' us', 'us.user_id = r.user_id')
        ->where('us.plan_id', 1)
        ->where('us.status', 'active')
        ->where('r.ip_address', $ipAddress)
        ->where('r.created_at >=', '2026-05-28 00:00:00')
        ->countAllResults();
    echo "Success: $ipUsage";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
