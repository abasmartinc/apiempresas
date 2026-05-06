<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);

$userId = 246; // Christian Flashper
echo "--- LOGS DE EMAIL PARA USER $userId ---\n";
$res = $conn->query("SELECT subject, status, error_message, created_at FROM email_logs WHERE user_id = $userId ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
