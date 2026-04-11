<?php
$c = mysqli_connect('localhost', 'root', '', 'apiempresas');
$q = "SELECT id, company_name, cif FROM companies WHERE company_name LIKE '%GRILL%' AND company_name LIKE '%LOVE%'";
$res = $c->query($q);
if($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo json_encode($row).PHP_EOL;
    }
} else {
    echo "No exact match with GRILL and LOVE\n";
    // Broaden search
    $res2 = $c->query("SELECT id, company_name, cif FROM companies WHERE company_name LIKE '%GRILL%' LIMIT 500");
    while($row = $res2->fetch_assoc()) {
        if (strpos($row['company_name'], 'LOVE') !== false) {
             echo "Found with LOVE: " . json_encode($row).PHP_EOL;
        }
    }
}
