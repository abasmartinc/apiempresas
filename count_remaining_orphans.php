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

$query = "SELECT COUNT(*) FROM borme_posts WHERE company_id = 0 OR company_id IS NULL";
$stmt = $pdo->query($query);
echo "BORME huérfanos restantes: " . $stmt->fetchColumn() . "\n";
