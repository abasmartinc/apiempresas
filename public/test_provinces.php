<?php
require rtrim(__DIR__, '/\\') . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '/\\') . '/bootstrap.php';

$db = \Config\Database::connect();
$query = $db->query("SELECT registro_mercantil, COUNT(*) as total FROM companies WHERE registro_mercantil IS NOT NULL GROUP BY registro_mercantil ORDER BY registro_mercantil ASC");
$results = $query->getResultArray();

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);
