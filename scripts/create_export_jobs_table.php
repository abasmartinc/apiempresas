<?php
$pdo = new PDO('mysql:host=localhost;dbname=apiempresas', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmt = $pdo->query("SELECT 1 FROM export_jobs LIMIT 1");
    echo "Table export_jobs exists!\n";
} catch (Exception $e) {
    echo "Table export_jobs does NOT exist. Creating...\n";
    $pdo->exec("CREATE TABLE `export_jobs` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int(11) unsigned NOT NULL,
      `type` varchar(50) NOT NULL,
      `context` json NOT NULL,
      `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
      `file_path` varchar(255) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table created successfully.\n";
}
