<?php
require 'public/index.php';
$db = \Config\Database::connect();
$count = $db->query('SELECT COUNT(*) as c FROM company_contracts')->getRow()->c;
echo "Contratos: " . $count . "\n";
