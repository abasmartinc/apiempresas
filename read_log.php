<?php
$env = file_get_contents('.env');
preg_match('/DB_PASSWORD\s*=\s*(.+)/', $env, $m);
$pass = trim($m[1] ?? '');
preg_match('/DB_HOSTNAME\s*=\s*(.+)/', $env, $mh);
$host = trim($mh[1] ?? '217.61.210.127');
preg_match('/DB_USERNAME\s*=\s*(.+)/', $env, $mu);
$user = trim($mu[1] ?? 'apiempresas_user');
preg_match('/DB_DATABASE\s*=\s*(.+)/', $env, $md);
$db_name = trim($md[1] ?? 'reseller3537_apiempresas');

$db = new mysqli($host, $user, $pass, $db_name, 3306);
if ($db->connect_error) {
    die("Connect error: " . $db->connect_error . "\n");
}

$res = $db->query("SELECT id, filters_json FROM radar_ai_search_logs ORDER BY id DESC LIMIT 3");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    $j = json_decode($row['filters_json'], true);
    echo json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}
