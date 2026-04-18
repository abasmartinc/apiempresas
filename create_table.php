<?php
define('FCPATH', __DIR__ . '/public/');
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
$db = \Config\Database::connect();
$sql = 'CREATE TABLE IF NOT EXISTS user_email_automation (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    user_id INT(11) UNSIGNED, 
    email_type VARCHAR(50), 
    sent_at DATETIME, 
    created_at DATETIME, 
    INDEX (user_id, email_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

try {
    $db->query($sql);
    echo "Table created successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
unlink(__FILE__);
