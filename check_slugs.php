<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

$res = $db->query("SELECT COUNT(*) as c FROM holdings WHERE slug IS NULL OR slug = ''");
$row = $res->fetch_assoc();
echo "Missing slugs: " . $row['c'] . "\n";
