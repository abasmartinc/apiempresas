<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$db   = 'reseller3537_apiempresas';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

echo "Connected to " . $db . "\n";

$result = $mysqli->query("SELECT * FROM email_logs ORDER BY id DESC LIMIT 5");
echo "Last 5 logs:\n";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

$result = $mysqli->query("SHOW CREATE TABLE email_logs");
$row = $result->fetch_assoc();
echo "\nCreate Table:\n";
print_r($row);
