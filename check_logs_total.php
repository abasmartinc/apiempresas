<?php
$db = mysqli_connect('localhost', 'root', '', 'apiempresas');
$res = mysqli_query($db, "SELECT COUNT(*) as total FROM email_logs");
echo "TOTAL LOGS: " . mysqli_fetch_assoc($res)['total'] . "\n";

$res = mysqli_query($db, "SELECT * FROM email_logs ORDER BY id DESC LIMIT 2");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
