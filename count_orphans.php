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

echo "Calculando registros huérfanos en borme_posts...\n";

// Borme posts with no valid company_id (either 0/null or pointing to a deleted/non-existent company)
$query = "
SELECT COUNT(*) 
FROM borme_posts b 
LEFT JOIN companies c ON b.company_id = c.id 
WHERE c.id IS NULL
";

$stmt = $pdo->query($query);
$orphanedBorme = $stmt->fetchColumn();

// Check if it's because company_id is NULL/0 or because of missing company
$queryNull = "SELECT COUNT(*) FROM borme_posts WHERE company_id IS NULL OR company_id = 0";
$stmtNull = $pdo->query($queryNull);
$nullBorme = $stmtNull->fetchColumn();

// Total borme posts
$queryTotal = "SELECT COUNT(*) FROM borme_posts";
$stmtTotal = $pdo->query($queryTotal);
$totalBorme = $stmtTotal->fetchColumn();


echo "Total de actos en borme_posts: " . number_format($totalBorme, 0, ',', '.') . "\n";
echo "Publicaciones sin empresa (company_id = 0 o NULL): " . number_format($nullBorme, 0, ',', '.') . "\n";
echo "Publicaciones huérfanas totales (empresa no existe en la BD): " . number_format($orphanedBorme, 0, ',', '.') . "\n";

