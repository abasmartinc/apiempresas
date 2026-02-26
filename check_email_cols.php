<?php
$db = mysqli_connect('localhost', 'root', '', 'apiempresas');
if (!$db) die("Connection failed: " . mysqli_connect_error());

$res = mysqli_query($db, "DESCRIBE email_logs");
echo "COLUMNS IN email_logs:\n";
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
