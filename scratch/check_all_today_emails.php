<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);

echo "--- TODOS LOS LOGS DE HOY ---\n";
$today = date('Y-m-d');
$res = $conn->query("SELECT subject, user_id, status, created_at FROM email_logs WHERE created_at >= '$today 00:00:00' ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
