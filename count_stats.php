<?php
$host = '217.61.210.127';
$db   = 'reseller3537_apiempresas';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("DB Connection Error: " . $e->getMessage());
}

echo "Calculando estadísticas totales...\n";

// Total companies
$stmt = $pdo->query("SELECT COUNT(*) FROM companies");
$totalCompanies = $stmt->fetchColumn();
echo "Total Empresas: " . number_format($totalCompanies, 0, ',', '.') . "\n";

// Total companies with at least one admin
$stmt = $pdo->query("SELECT COUNT(DISTINCT company_id) FROM company_administrators");
$totalWithAdmins = $stmt->fetchColumn();
echo "Empresas con Administradores: " . number_format($totalWithAdmins, 0, ',', '.') . "\n";

// Total companies with at least one borme post
$stmt = $pdo->query("SELECT COUNT(DISTINCT company_id) FROM borme_posts");
$totalWithBorme = $stmt->fetchColumn();
echo "Empresas con BORME: " . number_format($totalWithBorme, 0, ',', '.') . "\n";

// Total companies with NO admins AND NO borme posts
// This is slightly heavier, but we can do it efficiently
$query = "
SELECT COUNT(*) FROM companies c
WHERE NOT EXISTS (SELECT 1 FROM company_administrators a WHERE a.company_id = c.id)
  AND NOT EXISTS (SELECT 1 FROM borme_posts b WHERE b.company_id = c.id)
";
$stmt = $pdo->query($query);
$totalWithoutBoth = $stmt->fetchColumn();

echo "Empresas SIN Administradores y SIN BORME: " . number_format($totalWithoutBoth, 0, ',', '.') . "\n";
$pct = round(($totalWithoutBoth / $totalCompanies) * 100, 2);
echo "Porcentaje: $pct%\n";
