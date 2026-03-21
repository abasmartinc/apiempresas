<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=apiempresas", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `radar_prices` (
        `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `min_count` INT(11) UNSIGNED NOT NULL,
        `max_count` INT(11) UNSIGNED DEFAULT NULL,
        `base_price` DECIMAL(10,2) NOT NULL,
        `created_at` DATETIME DEFAULT NULL,
        `updated_at` DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table created or already exists.\n";

    // Check if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM radar_prices");
    if ($stmt->fetchColumn() == 0) {
        $data = [
            [1, 10, 2.00],
            [11, 50, 4.00],
            [51, 150, 7.00],
            [151, 500, 9.00],
            [501, 1500, 12.00],
            [1501, 9999999, 15.00],
        ];
        $stmt = $pdo->prepare("INSERT INTO radar_prices (min_count, max_count, base_price, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        foreach ($data as $row) {
            $stmt->execute($row);
        }
        echo "Data seeded.\n";
    } else {
        echo "Data already present.\n";
    }

} catch (PDOException $e) {
    die("ERROR: " . $e->getMessage());
}
