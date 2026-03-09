<?php
define('ENVIRONMENT', 'development');
require realpath(__DIR__ . '/../app/Config/Paths.php');
$paths = new \Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$seo = new \App\Controllers\SeoController();
$data = $seo->getRadarData('España', '', 'hoy', 1);

header('Content-Type: application/json');
echo json_encode([
    'total_context_count' => $data['total_context_count'] ?? 0,
    'stats_hoy' => $data['stats']['hoy'] ?? 0,
    'target_date_check' => date('Y-m-d'),
    'companies_count' => count($data['companies'] ?? []),
    'province_received' => $data['province'] ?? 'N/A',
    'period_received' => $data['period'] ?? 'N/A'
], JSON_PRETTY_PRINT);
