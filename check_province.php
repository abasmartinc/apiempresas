<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/app/Config/Boot.php';

$db = \Config\Database::connect();
$results = $db->query("SELECT DISTINCT registro_mercantil FROM companies WHERE registro_mercantil LIKE '%adrid%' LIMIT 10")->getResultArray();
foreach ($results as $r) {
    echo $r['registro_mercantil'] . "\n";
}
echo "\n--- url_title de Madrid ---\n";
echo url_title('Madrid', '-', true) . "\n";
echo "\n--- strtoupper(str_replace(-,  , madrid)) ---\n";
echo strtoupper(str_replace('-', ' ', 'madrid')) . "\n";
