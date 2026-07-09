<?php
$pdo = new PDO('mysql:host=localhost;dbname=apiempresas', 'root', '');
$stmt = $pdo->query("DESCRIBE invoices");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
