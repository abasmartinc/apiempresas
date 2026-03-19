<?php
$mysqli = new mysqli("localhost", "root", "", "apiempresas");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql1 = "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER password_hash";
$sql2 = "ALTER TABLE users ADD COLUMN reset_expires DATETIME NULL AFTER reset_token";

if ($mysqli->query($sql1) === TRUE) {
    echo "Column 'reset_token' added successfully\n";
} else {
    echo "Error adding column 'reset_token': " . $mysqli->error . "\n";
}

if ($mysqli->query($sql2) === TRUE) {
    echo "Column 'reset_expires' added successfully\n";
} else {
    echo "Error adding column 'reset_expires': " . $mysqli->error . "\n";
}

$mysqli->close();
?>
