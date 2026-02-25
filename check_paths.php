<?php
$mysqli = new mysqli("localhost", "root", "", "apiempresas");
$res = $mysqli->query("SELECT id, invoice_number, pdf_path FROM invoices ORDER BY id DESC");
while($row = $res->fetch_assoc()){
    echo "ID: {$row['id']} | Num: {$row['invoice_number']} | Path: {$row['pdf_path']}\n";
}
$mysqli->close();
