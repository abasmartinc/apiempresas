<?php
require 'public/index.php';
$db = \Config\Database::connect();
try {
    $fields = $db->getFieldNames('export_jobs');
    echo "Table export_jobs fields: \n" . implode(', ', $fields) . "\n";
} catch (\Exception $e) {
    echo "Table export_jobs does NOT exist!\n";
    $db->query("CREATE TABLE `export_jobs` (
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
    echo "Table created.\n";
}
