<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';
$conn = new mysqli($host, $user, $pass, $dbname);
$res = $conn->query("DESCRIBE tracking_events");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
