<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "--- ÚLTIMO USUARIO ---\n";
$res = $conn->query("SELECT id, email, name, created_at FROM users ORDER BY created_at DESC LIMIT 1");
$lastUser = $res->fetch_assoc();
print_r($lastUser);

if ($lastUser) {
    echo "\n--- AUTOMATIZACIÓN REGISTRADA ---\n";
    $userId = $lastUser['id'];
    $res = $conn->query("SELECT email_type, sent_at FROM user_email_automation WHERE user_id = $userId");
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
}
