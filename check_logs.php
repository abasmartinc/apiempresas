<?php
$db = mysqli_connect('localhost', 'root', '', 'apiempresas');
$res = mysqli_query($db, "SELECT id, tracking_code, opened_at, clicked_at, created_at FROM email_logs ORDER BY id DESC LIMIT 5");
echo "LAST 5 EMAIL LOGS:\n";
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
