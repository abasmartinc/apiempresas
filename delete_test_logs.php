<?php
$host = '217.61.210.127';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$db   = 'reseller3537_apiempresas';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die('Error');

$sql = "DELETE FROM email_logs WHERE subject = 'Novedades sobre tu acceso a la API' AND created_at >= '2026-04-29'";
if ($mysqli->query($sql)) {
    echo "Deleted " . $mysqli->affected_rows . " test logs.\n";
} else {
    echo "Error deleting: " . $mysqli->error . "\n";
}
