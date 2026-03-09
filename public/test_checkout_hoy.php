<?php
define('ENVIRONMENT', 'development');
require realpath(__DIR__ . '/../app/Config/Paths.php');
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$seo = new \App\Controllers\SeoController();
$dataHoy = $seo->getRadarData('España', '', 'hoy', 10);
$dataSemana = $seo->getRadarData('España', '', 'semana', 10);

echo "Total Hoy: " . print_r($dataHoy['total_context_count'] ?? 0, true) . "\n";
echo "Total Semana: " . print_r($dataSemana['total_context_count'] ?? 0, true) . "\n";
