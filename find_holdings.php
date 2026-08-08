<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

// Find holdings with the most companies
$query = "SELECT h.id, h.name, COUNT(ch.company_id) as total_companies 
          FROM holdings h
          JOIN company_holdings ch ON h.id = ch.holding_id
          GROUP BY h.id, h.name
          ORDER BY total_companies DESC
          LIMIT 5";

$res = $db->query($query);
if (!$res) die($db->error);

echo "Top 5 Holdings by number of companies:\n";
while($row = $res->fetch_assoc()) {
    echo "Holding: {$row['name']} (ID: {$row['id']}) - Companies: {$row['total_companies']}\n";
    
    // Get 3 companies from this holding
    $q2 = "SELECT c.company_name as name, c.cif FROM companies c JOIN company_holdings ch ON c.id = ch.company_id WHERE ch.holding_id = {$row['id']} LIMIT 3";
    $res2 = $db->query($q2);
    while($c = $res2->fetch_assoc()) {
        echo "  -> {$c['name']} (CIF: {$c['cif']})\n";
    }
    echo "\n";
}
