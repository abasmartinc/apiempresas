<?php
// tmp/check_sub.php

require_once __DIR__ . '/../vendor/autoload.php';
define('ROOTPATH', __DIR__ . '/../');
require_once __DIR__ . '/../app/Config/Constants.php';

// Mock CI4 environment
define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . '/../public/');
define('APPPATH', __DIR__ . '/../app/');
define('SYSTEMPATH', __DIR__ . '/../system/');
define('WRITEPATH', __DIR__ . '/../writable/');

// We can't easily boot CI4 here due to the bootstrap.php error seen before.
// We'll use a direct PDO connection using the .env file.

$env = file_get_contents(__DIR__ . '/../.env');
preg_match('/database.default.hostname = (.*)/', $env, $host);
preg_match('/database.default.database = (.*)/', $env, $db);
preg_match('/database.default.username = (.*)/', $env, $user);
preg_match('/database.default.password = (.*)/', $env, $pass);

$host = trim($host[1]);
$db = trim($db[1]);
$user = trim($user[1]);
$pass = trim($pass[1]);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- All Active Subscriptions ---\n";
    $stmt = $pdo->query("SELECT s.*, p.product_type FROM user_subscriptions s JOIN api_plans p ON s.plan_id = p.id WHERE s.status = 'active'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "\n--- Recent API Keys Used ---\n";
    $stmt = $pdo->query("SELECT * FROM api_keys ORDER BY last_used_at DESC LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
