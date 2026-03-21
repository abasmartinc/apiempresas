<?php
require 'app/Config/Database.php';
$db = \Config\Database::connect();
$query = $db->query("SHOW TABLES LIKE 'radar_prices'");
if ($query->getRowArray()) {
    echo "TABLE_EXISTS\n";
    $data = $db->table('radar_prices')->get()->getResultArray();
    echo "COUNT:" . count($data) . "\n";
    foreach ($data as $row) {
        echo "ID:{$row['id']} MIN:{$row['min_count']} MAX:{$row['max_count']} PRICE:{$row['base_price']}\n";
    }
} else {
    echo "TABLE_NOT_FOUND\n";
}
