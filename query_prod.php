<?php
$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
$res = $mysqli->query('SELECT * FROM radar_demo_events LIMIT 5');
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
