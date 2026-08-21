<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(__DIR__);
require 'public/index.php';
$db = \Config\Database::connect();
$row = $db->table('company_risk_profiles')->where('risk_score', 15)->get()->getRowArray();
print_r($row);
