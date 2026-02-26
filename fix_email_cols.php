<?php
$db = mysqli_connect('localhost', 'root', '', 'apiempresas');
if (!$db) die("Connection failed: " . mysqli_connect_error());

$sql = "ALTER TABLE email_logs 
        ADD COLUMN tracking_code VARCHAR(255) NULL AFTER error_message,
        ADD COLUMN opened_at DATETIME NULL AFTER tracking_code,
        ADD COLUMN clicked_at DATETIME NULL AFTER opened_at,
        ADD COLUMN logged_in_at DATETIME NULL AFTER clicked_at";

if (mysqli_query($db, $sql)) {
    echo "Columns added successfully to email_logs table.\n";
} else {
    echo "Error adding columns: " . mysqli_error($db) . "\n";
}
