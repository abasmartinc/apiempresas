<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$schemas = [];
foreach (['user_email_automation', 'email_logs', 'users', 'api_usage_daily'] as $table) {
    if (in_array($table, $tables)) {
        $res = $conn->query("DESCRIBE $table");
        $fields = [];
        while ($f = $res->fetch_assoc()) {
            $fields[] = $f;
        }
        $schemas[$table] = $fields;
    }
}

echo json_encode(['tables' => $tables, 'schemas' => $schemas], JSON_PRETTY_PRINT);
