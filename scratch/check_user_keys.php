<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);

$userId = 246; // Christian
echo "--- ESTADO API KEY USER $userId ---\n";
$res = $conn->query("SELECT name, api_key, is_active FROM api_keys WHERE user_id = $userId");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
