<?php
$db = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'apiempresas'
];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']}", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clear and re-populate
    $pdo->exec("TRUNCATE TABLE radar_prices");

    $tiers = [
        [1, 10, 2.00],
        [11, 50, 4.00],
        [51, 150, 7.00],
        [151, 500, 9.00],
        [501, 1000, 12.00],
        [1001, 2000, 15.00],
        [2001, 5000, 20.00],
        [5001, null, 25.00]
    ];

    $stmt = $pdo->prepare("INSERT INTO radar_prices (min_count, max_count, base_price) VALUES (?, ?, ?)");
    foreach ($tiers as $tier) {
        $stmt->execute($tier);
    }

    echo "Table radar_prices updated successfully.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
