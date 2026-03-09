<?php
define('ENVIRONMENT', 'development');
require realpath(__DIR__ . '/../app/Config/Paths.php');
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$seo = new \App\Controllers\SeoController();
$dataHoy = $seo->getRadarData('España', '', 'hoy', 10);

echo "--- Debug SeoController::getRadarData('España', '', 'hoy') ---\n";
print_r($dataHoy);
