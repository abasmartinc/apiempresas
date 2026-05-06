<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);

echo "--- TIPOS DE EVENTOS EN DB (Últimos 30 días) ---\n";
$res = $conn->query("SELECT event_name, COUNT(*) as total FROM tracking_events WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY event_name ORDER BY total DESC");
while($row = $res->fetch_assoc()) {
    printf("%-35s | %d\n", $row['event_name'], $row['total']);
}
