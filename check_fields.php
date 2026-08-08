<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

$q = "SELECT c.company_name, c.capital_social_raw, c.ventas_raw, c.registro_mercantil, c.estado 
      FROM companies c 
      JOIN company_holdings ch ON c.id = ch.company_id 
      WHERE ch.holding_id = 17263 LIMIT 10";

$res = $db->query($q);
while($row = $res->fetch_assoc()) {
    print_r($row);
}
