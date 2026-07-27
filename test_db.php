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
    
    $stmt = $pdo->query("SELECT event_type, cta_label, COUNT(*) as clicks FROM radar_demo_events GROUP BY event_type, cta_label ORDER BY clicks DESC");
    
    $results = $stmt->fetchAll();
    
    echo "--- EVENT SUMMARY ---\n";
    foreach ($results as $row) {
        echo str_pad($row['event_type'], 25) . " | " . str_pad($row['cta_label'], 30) . " | " . $row['clicks'] . "\n";
    }

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
