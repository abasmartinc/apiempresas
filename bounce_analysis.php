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
    echo "Connection failed: " . $e->getMessage();
    exit;
}

$query = "
SELECT u.email
FROM tracking_events te
JOIN users u ON te.user_id = u.id
JOIN user_subscriptions us ON u.id = us.user_id
WHERE (te.page LIKE '%billing%' OR te.page LIKE '%checkout%')
  AND us.plan_id = 1
  AND te.user_id > 0
GROUP BY u.id, u.email
";

$stmt = $pdo->query($query);
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

$domains = [];
$total_freelance = 0;
$total_corporate = 0;

$freelance_domains = ['gmail.com', 'hotmail.com', 'yahoo.es', 'yahoo.com', 'outlook.com', 'icloud.com', 'live.com'];

foreach ($emails as $email) {
    $parts = explode('@', $email);
    if (count($parts) === 2) {
        $domain = strtolower($parts[1]);
        if (!isset($domains[$domain])) {
            $domains[$domain] = 0;
        }
        $domains[$domain]++;
        
        if (in_array($domain, $freelance_domains)) {
            $total_freelance++;
        } else {
            $total_corporate++;
        }
    }
}

arsort($domains);

echo "DOMINIOS DE USUARIOS QUE ABANDONARON BILLING (Plan Free):\n";
echo "-------------------------------------------------------\n";
$i = 0;
foreach ($domains as $domain => $count) {
    echo str_pad($domain, 25) . " : " . $count . " usuarios\n";
    $i++;
    if ($i >= 15) break;
}
echo "-------------------------------------------------------\n";
echo "Total abandonos: " . count($emails) . "\n";
echo "Correos Genéricos (B2C/Freelance): " . $total_freelance . " (" . round(($total_freelance/max(1, count($emails)))*100) . "%)\n";
echo "Correos Corporativos (B2B): " . $total_corporate . " (" . round(($total_corporate/max(1, count($emails)))*100) . "%)\n";
