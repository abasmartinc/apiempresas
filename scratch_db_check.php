<?php
require 'system/Test/bootstrap.php';
$db = \Config\Database::connect();
$query = $db->query("SHOW CREATE TABLE email_logs");
print_r($query->getRowArray());

$query = $db->query("SELECT * FROM email_logs ORDER BY id DESC LIMIT 5");
print_r($query->getResultArray());
