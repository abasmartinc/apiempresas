<?php
define('ENVIRONMENT', 'development');
require realpath(__DIR__ . '/../app/Config/Paths.php');
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$seo = new \App\Controllers\SeoController();
$data = $seo->getRadarData('España', '', 'hoy', 1);

echo "Total Context Count: " . $data['total_context_count'] . "\n";
echo "Dynamic Price: " . $data['dynamic_price']['base_price'] . "€\n";
echo "Companies found: " . count($data['companies']) . "\n";
if (count($data['companies']) > 0) {
    echo "First company: " . $data['companies'][0]['name'] . " (" . $data['companies'][0]['fecha_constitucion'] . ")\n";
}
