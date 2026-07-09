<?php
$db = new mysqli('localhost', 'root', '', 'apiempresas');

$r = $db->query("SELECT endpoint, created_at FROM api_requests WHERE user_id=166 AND endpoint LIKE '%professional/details%' ORDER BY created_at DESC");
echo "=== Llamadas a /professional/details ===\n";
while ($row = $r->fetch_assoc()) {
    echo $row['created_at'] . " | " . $row['endpoint'] . "\n";
}

$r2 = $db->query("SELECT endpoint, created_at FROM api_requests WHERE user_id=166 ORDER BY created_at DESC LIMIT 10");
echo "\n=== Ultimas 10 llamadas en api_requests ===\n";
while ($row = $r2->fetch_assoc()) {
    echo $row['created_at'] . " | " . $row['endpoint'] . "\n";
}
