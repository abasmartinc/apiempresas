<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);

echo "--- LOGS DE EMAIL RECIENTES ---\n";
$res = $conn->query("SELECT subject, email, created_at FROM email_logs ORDER BY created_at DESC LIMIT 10");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
