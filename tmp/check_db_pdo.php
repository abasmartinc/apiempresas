<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=apiempresas", "root", "");
    $stmt = $pdo->query("SHOW TABLES LIKE 'radar_prices'");
    if ($stmt->fetch()) {
        echo "TABLE_EXISTS\n";
        $stmt = $pdo->query("SELECT * FROM radar_prices");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "COUNT:" . count($results) . "\n";
        foreach ($results as $row) {
             echo "ID:{$row['id']} MIN:{$row['min_count']} MAX:{$row['max_count']} PRICE:{$row['base_price']}\n";
        }
    } else {
        echo "TABLE_NOT_FOUND\n";
    }
} catch (PDOException $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
