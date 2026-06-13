<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();
$userId = 248;
$usageSum = $db->table('api_usage_daily')
    ->selectSum('requests_count', 'total')
    ->where('user_id', $userId)
    ->where('date >=', date('Y-m-01'))
    ->get()->getRow();
$requestCount = (int)($usageSum->total ?? 0);

$ApiRequestsModel = new \App\Models\ApiRequestsModel();
$kpis = [
    'api_request_total_month' => $requestCount,
    'avg_latency' => $ApiRequestsModel->getAverageLatency(['user_id' => $userId]),
    'error_rate' => $ApiRequestsModel->getErrorRate(['user_id' => $userId])
];
print_r($kpis);
