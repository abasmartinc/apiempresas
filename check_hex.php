<?php
$c = file_get_contents('app/Views/home.php');
echo "Hex dump: " . bin2hex(substr($c, 0, 10)) . "\n";
