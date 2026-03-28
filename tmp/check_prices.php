<?php
// /tmp/check_prices.php

// Bootstrap CI4
define('FCPATH', __DIR__ . '/../public/'); 
require_once __DIR__ . '/../system/Test/bootstrap.php';

use App\Models\RadarPriceModel;
use Config\Database;

$db = Database::connect();

echo "--- RADAR PRICES TABLE ---\n";
$prices = $db->table('radar_prices')->get()->getResultArray();
foreach ($prices as $p) {
    echo "ID: {$p['id']} | Min: {$p['min_count']} | Max: {$p['max_count']} | Price: {$p['base_price']}€\n";
}

echo "\n--- CURRENT COUNTS (ESTIMATED) ---\n";
$last7 = date('Y-m-d', strtotime('-7 days'));
$last30 = date('Y-m-d', strtotime('-30 days'));

$semanaCount = $db->table('companies')->where('fecha_constitucion >=', $last7)->countAllResults();
$mesCount = $db->table('companies')->where('fecha_constitucion >=', $last30)->countAllResults();

echo "Semana Count: $semanaCount\n";
echo "Mes Count: $mesCount\n";

$model = new RadarPriceModel();
echo "\n--- MODEL CALCULATION ---\n";
echo "Semana Price: " . $model->getBasePrice($semanaCount) . "€\n";
echo "Mes Price: " . $model->getBasePrice($mesCount) . "€\n";

// Check Cache Status
$cache = \Config\Services::cache();
$cacheKey = 'radar_data_period_semana__'; 
$cachedData = $cache->get($cacheKey);

if ($cachedData) {
    echo "\n--- CACHE STATUS ---\n";
    echo "Cache Found for 'semana'.\n";
    echo "Cached Price (semana): " . ($cachedData['prices']['semana'] ?? 'N/A') . "€\n";
    echo "Cached Price (mes): " . ($cachedData['prices']['mes'] ?? 'N/A') . "€\n";
} else {
    echo "\nCache NOT Found.\n";
}
