<?php
$conn = mysqli_connect('localhost', 'root', '', 'apiempresas');
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM companies WHERE cif = 'B12345678'");
$row = mysqli_fetch_assoc($res);
echo "Count B12345678: " . $row['c'] . "\n";
mysqli_close($conn);
