<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();

$tables = ['companies', 'company_search_logs'];
foreach ($tables as $table) {
    echo "Indexes for $table:\n";
    $query = $db->query("SHOW INDEX FROM $table");
    $results = $query->getResult();
    foreach ($results as $row) {
        echo " - " . $row->Key_name . " (" . $row->Column_name . ")\n";
    }
    echo "\n";
}
