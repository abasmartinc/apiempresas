<?php
require 'public/index.php';
$db = \Config\Database::connect();
$start = microtime(true);
$count = $db->table('companies')->countAllResults();
echo "Total companies: " . $count . "\n";

$page = 466;
$perPage = 10000;
$offset = ($page - 1) * $perPage;

$startQuery = microtime(true);
$companies = $db->table('companies')
    ->select('id, cif, company_name as name, cnae_code as cnae, registro_mercantil as province, objeto_social as corporate_purpose') 
    ->orderBy('id', 'ASC')
    ->limit($perPage, $offset)
    ->get()
    ->getResultArray();
$endQuery = microtime(true);

echo "Fetched " . count($companies) . " companies\n";
echo "Query time: " . ($endQuery - $startQuery) . " seconds\n";
