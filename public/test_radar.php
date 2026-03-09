<?php
define('ENVIRONMENT', 'development');
require realpath(__DIR__ . '/../app/Config/Paths.php');
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$request = \Config\Services::request();
$response = clone \Config\Services::response();

$seo = new \App\Controllers\SeoController();
$data = $seo->testRadarData('España', '', '30days', 1000);
echo "Companies fetched: " . count($data['companies']);
