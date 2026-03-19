<?php
require 'system/Test/bootstrap.php';
$db = \Config\Database::connect();
$db->query("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER password_hash");
$db->query("ALTER TABLE users ADD COLUMN reset_expires DATETIME NULL AFTER reset_token");
echo "Columns added successfully";
