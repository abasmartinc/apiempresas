<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$dbname = 'reseller3537_apiempresas';

$conn = new mysqli($host, $user, $pass, $dbname);

$eventsToDelete = ["'scroll_depth'", "'time_on_page'", "'section_view'"];
$list = implode(',', $eventsToDelete);

echo "--- LIMPIEZA DE BASE DE DATOS ---\n";

// Contar antes
$res = $conn->query("SELECT COUNT(*) as total FROM tracking_events WHERE event_name IN ($list)");
$row = $res->fetch_assoc();
echo "Registros de ruido encontrados: " . $row['total'] . "\n";

if ($row['total'] > 0) {
    echo "Borrando...\n";
    $conn->query("DELETE FROM tracking_events WHERE event_name IN ($list)");
    echo "¡Limpieza completada con éxito!\n";
} else {
    echo "No se encontraron registros de ruido.\n";
}

// Stats finales
echo "\n--- ESTADO FINAL DE LA TABLA ---\n";
$res = $conn->query("SELECT event_name, COUNT(*) as total FROM tracking_events GROUP BY event_name ORDER BY total DESC");
while($row = $res->fetch_assoc()) {
    printf("%-35s | %d\n", $row['event_name'], $row['total']);
}
