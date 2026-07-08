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
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$emails = [
    'compras@fenixia.tech',
    'vlliso@gmail.com',
    'rarias@gemicar.net',
    'ajalvarez@ascendiarc.com',
    'contabilidad@aiautomatiza.com',
    'wojciech.bublik@pragmago.es'
];

$in  = str_repeat('?,', count($emails) - 1) . '?';

$sql = "SELECT id, email, created_at FROM users WHERE email IN ($in)";
$stmt = $pdo->prepare($sql);
$stmt->execute($emails);
$users = $stmt->fetchAll();

foreach ($users as $u) {
    echo "=================================================\n";
    echo "User: {$u['email']} (ID: {$u['id']}, Created: {$u['created_at']})\n";
    echo "=================================================\n";
    
    // api_requests
    $sql_api = "SELECT status_code, COUNT(*) as cnt FROM api_requests WHERE user_id = ? GROUP BY status_code";
    $stmt_api = $pdo->prepare($sql_api);
    $stmt_api->execute([$u['id']]);
    $api_logs = $stmt_api->fetchAll();
    echo "-> API Requests:\n";
    if (count($api_logs) > 0) {
        foreach ($api_logs as $l) {
            echo "   Status {$l['status_code']}: {$l['cnt']}\n";
        }
    } else {
         echo "   (No API requests found)\n";
    }

    // company_search_logs
    $sql_search = "SELECT COUNT(*) as cnt FROM company_search_logs WHERE user_id = ?";
    $stmt_search = $pdo->prepare($sql_search);
    $stmt_search->execute([$u['id']]);
    $search_logs = $stmt_search->fetch();
    echo "-> Company Searches: " . $search_logs['cnt'] . "\n";

    // radar_ai_search_logs
    $sql_radar = "SELECT COUNT(*) as cnt FROM radar_ai_search_logs WHERE user_id = ?";
    $stmt_radar = $pdo->prepare($sql_radar);
    $stmt_radar->execute([$u['id']]);
    $radar_logs = $stmt_radar->fetch();
    echo "-> Radar AI Searches: " . $radar_logs['cnt'] . "\n";
    
    // tracking_events (to see what pages they visit)
    $sql_tracking = "SELECT event_name, COUNT(*) as cnt FROM tracking_events WHERE user_id = ? GROUP BY event_name ORDER BY cnt DESC LIMIT 10";
    $stmt_tracking = $pdo->prepare($sql_tracking);
    $stmt_tracking->execute([$u['id']]);
    $tracking_logs = $stmt_tracking->fetchAll();
    echo "-> Top 10 Tracking Events (Visits/Actions):\n";
    if (count($tracking_logs) > 0) {
        foreach ($tracking_logs as $l) {
            echo "   {$l['event_name']}: {$l['cnt']}\n";
        }
    } else {
        echo "   (No tracking events found)\n";
    }
    
    echo "\n";
}
?>
