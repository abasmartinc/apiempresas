<?php
$host = 'localhost';
$db   = 'apiempresas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'wizard_completed'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN wizard_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
        echo "Column wizard_completed added successfully to local DB.\n";
    } else {
        echo "Column wizard_completed already exists in local DB.\n";
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}
