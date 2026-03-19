<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$app = require 'system/bootstrap.php';
$db = \Config\Database::connect();
$query = $db->query("SHOW COLUMNS FROM users");
foreach ($query->getResult() as $row) {
    echo $row->Field . " (" . $row->Type . ")\n";
}
