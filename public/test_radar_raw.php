<?php
$conn = new mysqli('localhost', 'root', '', 'apiempresas');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()");
$row = $result->fetch_assoc();
$last_date = $row['last_date'];

echo "LAST DATE: $last_date\n";

$countQuery = $conn->query("SELECT COUNT(*) as c FROM companies WHERE fecha_constitucion = '$last_date'");
$cRow = $countQuery->fetch_assoc();
echo "COUNT: " . $cRow['c'] . "\n";
