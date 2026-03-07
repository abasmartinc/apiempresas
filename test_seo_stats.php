<?php
$host = '127.0.0.1';
$db   = 'apiempresas';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    $stmt = $pdo->prepare("SELECT * FROM seo_stats_cnae WHERE cnae_code = '561'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "Row in seo_stats_cnae for 561:\n";
        print_r($row);
    } else {
        echo "No row in seo_stats_cnae for 561\n";
    }

} catch (\PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
