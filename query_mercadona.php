<?php
$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
$res = $mysqli->query("SELECT cif, company_name FROM companies WHERE company_name LIKE '%MERCADONA%' LIMIT 1");
$row = $res->fetch_assoc();
echo "MERCADONA CIF: " . $row['cif'] . "\n";
