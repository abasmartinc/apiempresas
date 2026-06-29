<?php
$envPath = __DIR__ . '/../.env';
$envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($envLines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) == 2) {
        $env[trim($parts[0])] = trim($parts[1], " \t\n\r\0\x0B\"'");
    }
}

$host = $env['database.default.hostname'] ?? 'localhost';
$db   = $env['database.default.database'] ?? 'apiempresas';
$user = $env['database.default.username'] ?? 'root';
$pass = $env['database.default.password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Total users
    $stmt = $pdo->query("SELECT count(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
    
    // Total active PRO subscriptions
    // We consider "pro" any active subscription where plan is not free or specifically named "pro"
    // Let's get the active subscriptions joined with plans
    $stmt = $pdo->query("
        SELECT s.id, s.user_id, s.status, s.created_at, p.name as plan_name, p.slug as plan_slug 
        FROM user_subscriptions s 
        JOIN api_plans p ON s.plan_id = p.id 
        WHERE s.status = 'active'
    ");
    $activeSubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all subscriptions to see history
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(s.created_at, '%Y-%m') as month, count(*) as new_subs
        FROM user_subscriptions s
        JOIN api_plans p ON s.plan_id = p.id
        WHERE p.price_monthly > 0 OR p.slug LIKE '%pro%' OR p.slug LIKE '%business%' OR p.slug LIKE '%radar%'
        GROUP BY month
        ORDER BY month ASC
    ");
    $monthlySubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total accumulated active paid subscriptions currently
    $stmt = $pdo->query("
        SELECT count(*) 
        FROM user_subscriptions s
        JOIN api_plans p ON s.plan_id = p.id
        WHERE s.status = 'active' AND (p.price_monthly > 0 OR p.slug LIKE '%pro%' OR p.slug LIKE '%business%')
    ");
    $currentActiveProSubs = $stmt->fetchColumn();
    
    echo "Total Users: $totalUsers\n";
    echo "Current Active PRO/Paid Subs: $currentActiveProSubs\n";
    echo "Monthly New Paid Subs History:\n";
    foreach ($monthlySubs as $row) {
        echo $row['month'] . ": " . $row['new_subs'] . "\n";
    }
    
    // User growth
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as new_users
        FROM users
        GROUP BY month
        ORDER BY month ASC
    ");
    $monthlyUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nMonthly New Users History:\n";
    foreach ($monthlyUsers as $row) {
        echo $row['month'] . ": " . $row['new_users'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
