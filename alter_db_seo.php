<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

$query = "ALTER TABLE company_enrichment ADD COLUMN indexing_api_submitted_at DATETIME NULL DEFAULT NULL";
if ($db->query($query) === TRUE) {
    echo "Column indexing_api_submitted_at added successfully.\n";
} else {
    echo "Error: " . $db->error . "\n";
}
