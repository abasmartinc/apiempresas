<?php
$c = mysqli_connect('localhost', 'root', '', 'apiempresas');
if ($c->connect_error) die("Connection failed: " . $c->connect_error);
$res = $c->query("SELECT id, company_name, cif FROM companies WHERE company_name LIKE '%GRILL%'");
if(!$res) die($c->error);
while($row = $res->fetch_assoc()) {
    echo json_encode($row).PHP_EOL;
}
