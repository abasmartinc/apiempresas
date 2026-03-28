<?php
// c:\laragon\www\apiempresas\tmp\dump_db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'apiempresas';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "--- radar_prices ---\n";
$result = $conn->query("SELECT * FROM radar_prices");
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Min: {$row['min_count']} | Max: {$row['max_count']} | Price: {$row['base_price']}€\n";
}

echo "\n--- counts ---\n";
$last30 = date('Y-m-d', strtotime('-30 days'));
$res = $conn->query("SELECT COUNT(*) as total FROM companies WHERE fecha_constitucion >= '$last30'");
$row = $res->fetch_assoc();
echo "Mes Count (last 30d): " . $row['total'] . "\n";

$conn->close();
