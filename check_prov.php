<?php
chdir('d:/laragon/www/apiempresas');
define('FCPATH', 'd:/laragon/www/apiempresas/public/');
require 'd:/laragon/www/apiempresas/vendor/autoload.php';
require 'd:/laragon/www/apiempresas/app/Config/Boot.php';
$db = \Config\Database::connect();
$rows = $db->query("SELECT DISTINCT registro_mercantil FROM companies WHERE registro_mercantil LIKE '%adrid%' LIMIT 5")->getResultArray();
foreach ($rows as $r) {
    echo json_encode($r) . "\n";
}
