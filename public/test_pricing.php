<?php
// A simple test script inside Laragon web root
require '../app/Helpers/pricing_helper.php';

$count = 2021;
$res = calculate_radar_price($count);
echo "Count: $count | Price: " . $res['base_price'] . "\n";
