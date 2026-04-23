<?php
$hostname = '217.61.210.127';
$database = 'reseller3537_apiempresas';
$username = 'apiempresas_user';
$password = 'WONwyjpsmx3h3$@2';

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("DESCRIBE tracking_events");
if (!$result) {
    echo "Error: Table 'tracking_events' does not exist or cannot be accessed.\n";
} else {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
}
$conn->close();
