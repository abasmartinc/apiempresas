<?php
$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if ($mysqli->connect_error) die('Connection failed: ' . $mysqli->connect_error);

$res = $mysqli->query('DESCRIBE admin_privacy_optouts');
if ($res) {
    while ($row = $res->fetch_assoc()) { print_r($row); }
} else {
    echo "admin_privacy_optouts does not exist or error.\n";
}

// Check if we need to create company_privacy_optouts
$mysqli->query("
CREATE TABLE IF NOT EXISTS company_privacy_optouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cif VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

echo "Tables checked/created.\n";

// Insert admins
$stmt = $mysqli->prepare("INSERT IGNORE INTO admin_privacy_optouts (slug) VALUES (?)");
$admins = ['el-jorfi-batache', 'roummani-ghadi-aya'];
foreach ($admins as $slug) {
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    echo "Inserted admin: $slug\n";
}

// Insert company
$stmt = $mysqli->prepare("INSERT IGNORE INTO company_privacy_optouts (cif) VALUES (?)");
$stmt->bind_param("s", $cif);
$cif = 'B26976241';
$stmt->execute();
echo "Inserted company: $cif\n";

$mysqli->close();
