<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// See the structure of the table to know if there's an error message column
echo "--- TABLE STRUCTURE ---\n";
$res = $db->query("DESCRIBE seo_generation_queue");
if ($res) {
    while($r = $res->fetch_assoc()) {
        echo $r['Field'] . "\n";
    }
}

// Get the latest failed jobs
echo "\n--- LATEST FAILED JOBS ---\n";
$res = $db->query("SELECT * FROM seo_generation_queue WHERE status = 'failed' ORDER BY id DESC LIMIT 5");
if ($res) {
    while($r = $res->fetch_assoc()) {
        print_r($r);
    }
}

// Get counts
echo "\n--- STATS ---\n";
$res = $db->query("SELECT status, COUNT(*) as count FROM seo_generation_queue GROUP BY status");
if ($res) {
    while($r = $res->fetch_assoc()) {
        echo $r['status'] . ": " . $r['count'] . "\n";
    }
}

$db->close();
