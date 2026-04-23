<?php
$hostname = '217.61.210.127';
$database = 'reseller3537_apiempresas';
$username = 'apiempresas_user';
$password = 'WONwyjpsmx3h3$@2';

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT COUNT(*) as total FROM tracking_events");
$row = $result->fetch_assoc();
echo "Total events: " . $row['total'] . "\n";

if ($row['total'] > 0) {
    $result = $conn->query("SELECT * FROM tracking_events ORDER BY id DESC LIMIT 5");
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
}
$conn->close();
