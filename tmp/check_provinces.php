<?php
require 'app/Config/Paths.php';
require 'vendor/autoload.php';
$app = Config\Services::codeigniter(new Config\App());
$app->initialize();
$db = \Config\Database::connect();

echo "--- BARCELONA SECTORS (90 DAYS) ---\n";
$res = $db->table('companies')
    ->select('cnae_label, count(*) as total')
    ->where('registro_mercantil', 'Barcelona')
    ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
    ->groupBy('cnae_label')
    ->orderBy('total', 'DESC')
    ->get()->getResultArray();
echo "Total Sectors: " . count($res) . "\n";
print_r($res);
