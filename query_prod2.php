<?php
$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if ($mysqli->connect_error) { die('Connect Error: ' . $mysqli->connect_error); }

$res = $mysqli->query("SELECT cta_label, COUNT(*) as clicks FROM radar_demo_events WHERE event_type LIKE '%coming_soon%' GROUP BY cta_label ORDER BY clicks DESC");
echo "RESULTS:\n";
while ($row = $res->fetch_assoc()) {
    echo $row['cta_label'] . ' | ' . $row['clicks'] . "\n";
}
