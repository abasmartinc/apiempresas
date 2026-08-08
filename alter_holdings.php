<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

$db->query("ALTER TABLE holdings ADD COLUMN companies_count INT(11) NOT NULL DEFAULT 0 AFTER slug");
$db->query("ALTER TABLE holdings ADD COLUMN total_capital DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER companies_count");
$db->query("ALTER TABLE holdings ADD INDEX(companies_count)");

echo "Columns added.\n";

// Update counts (batch query)
// Doing an UPDATE JOIN with GROUP BY is faster in MySQL if not too huge.
$sql = "UPDATE holdings h
        JOIN (
            SELECT holding_id, COUNT(company_id) as count 
            FROM company_holdings 
            GROUP BY holding_id
        ) ch ON h.id = ch.holding_id
        SET h.companies_count = ch.count";

$db->query($sql);
echo "Counts updated: " . $db->affected_rows . "\n";
