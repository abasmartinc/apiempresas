<?php

$host = 'localhost';
$db   = 'apiempresas';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$tables = ['companies', 'users', 'api_requests', 'user_subscriptions'];

echo "Table counts:\n";
foreach ($tables as $table) {
    if ($result = $mysqli->query("SELECT COUNT(*) as count FROM $table")) {
        $row = $result->fetch_assoc();
        echo "Table: $table, Count: " . $row['count'] . "\n";
    } else {
        echo "Table: $table does not exist or error: " . $mysqli->error . "\n";
    }
}

$queries = [
    'Total Companies' => "SELECT COUNT(*) FROM companies",
    'Companies Added Today' => "SELECT COUNT(*) FROM companies WHERE created_at >= '" . date('Y-m-d') . " 00:00:00'",
    'Companies without CIF' => "SELECT COUNT(*) FROM companies WHERE cif = '' OR cif IS NULL",
    'Companies without Address' => "SELECT COUNT(*) FROM companies WHERE address = '' OR address IS NULL",
    'Companies without Status' => "SELECT COUNT(*) FROM companies WHERE estado = '' OR estado IS NULL",
    'Companies without CNAE' => "SELECT COUNT(*) FROM companies WHERE cnae_code = '' OR cnae_code IS NULL",
    'Companies without Mercantile' => "SELECT COUNT(*) FROM companies WHERE registro_mercantil = '' OR registro_mercantil IS NULL",
];

echo "\nQuery Performance:\n";
foreach ($queries as $label => $sql) {
    $start = microtime(true);
    $mysqli->query($sql);
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    echo "$label: " . number_format($time, 2) . "ms\n";
}

echo "\nChecking Indexes for 'companies':\n";
if ($result = $mysqli->query("SHOW INDEX FROM companies")) {
    while ($row = $result->fetch_assoc()) {
        echo "Table: " . $row['Table'] . ", Column: " . $row['Column_name'] . ", Key: " . $row['Key_name'] . "\n";
    }
}

$mysqli->close();
