<?php
$env = file_get_contents('.env');
preg_match('/database.default.hostname = (.*)/', $env, $host);
preg_match('/database.default.database = (.*)/', $env, $db);
preg_match('/database.default.username = (.*)/', $env, $user);
preg_match('/database.default.password = (.*)/', $env, $pass);

$host = trim($host[1] ?? 'localhost', ' "');
$db = trim($db[1] ?? '', ' "');
$user = trim($user[1] ?? '', ' "');
$pass = trim($pass[1] ?? '', ' "');

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $stmt = $pdo->query("DESCRIBE api_requests");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) { echo $e->getMessage(); }
