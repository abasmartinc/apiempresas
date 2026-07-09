<?php
require 'public/index.php';
$db = \Config\Database::connect();
$start = microtime(true);
$res = $db->query("SELECT DISTINCT convocatoria FROM subsidies_grants WHERE convocatoria IS NOT NULL AND convocatoria != ''")->getResultArray();
echo 'Subvenciones Count: ' . count($res) . ' Time: ' . (microtime(true) - $start) . "s\n";

$start = microtime(true);
$res = $db->query("SELECT DISTINCT organo_contratacion FROM company_contracts WHERE organo_contratacion IS NOT NULL AND organo_contratacion != ''")->getResultArray();
echo 'Contratos Count: ' . count($res) . ' Time: ' . (microtime(true) - $start) . "s\n";
