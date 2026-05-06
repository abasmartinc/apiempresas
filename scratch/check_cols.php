<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);
echo "--- email_logs ---\n";
$res = $conn->query("DESCRIBE email_logs");
while($row = $res->fetch_assoc()) echo $row['Field']."\n";

echo "\n--- user_email_automation ---\n";
$res = $conn->query("DESCRIBE user_email_automation");
if($res) {
    while($row = $res->fetch_assoc()) echo $row['Field']."\n";
} else {
    echo "Table user_email_automation not found\n";
}
