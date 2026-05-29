<?php
$pdo = new PDO('mysql:host=localhost;dbname=apiempresas', 'root', '');
$stmt = $pdo->query("SELECT cnae_label, COUNT(*) as count FROM companies WHERE cnae_code = '6812' GROUP BY cnae_label ORDER BY count DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
