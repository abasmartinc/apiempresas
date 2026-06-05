<?php
require 'public/index.php';
$db = \Config\Database::connect();
$tables = $db->listTables();
print_r($tables);
