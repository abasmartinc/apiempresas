<?php
$host = '217.61.210.127';
$db   = 'reseller3537_apiempresas';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$query = "
SELECT page, event_name, COUNT(*) as total_hits, COUNT(DISTINCT user_id) as unique_users
FROM tracking_events
WHERE page LIKE '%copilot-pro%' 
   OR page LIKE '%copiloto_ventas%'
   OR page LIKE '%api/ai/copilot%'
GROUP BY page, event_name
ORDER BY total_hits DESC
";

$stmt = $pdo->query($query);
$results = $stmt->fetchAll();

if (empty($results)) {
    echo "No se encontró ningún tráfico específico de la landing 'copilot-pro' ni uso de la API en tracking_events.\n";
} else {
    echo str_pad("PÁGINA", 50) . " | " . str_pad("EVENTO", 20) . " | HITS | USUARIOS\n";
    echo str_repeat("-", 90) . "\n";
    foreach ($results as $row) {
        $page = empty($row['page']) ? '(vacío)' : $row['page'];
        $event = empty($row['event_name']) ? '(vacío)' : $row['event_name'];
        echo str_pad($page, 50) . " | " . str_pad($event, 20) . " | " . str_pad($row['total_hits'], 4) . " | " . $row['unique_users'] . "\n";
    }
}
