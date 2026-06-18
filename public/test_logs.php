<?php
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();
$query = $db->query('SELECT * FROM chatbot_logs ORDER BY created_at DESC LIMIT 20');
$results = $query->getResultArray();

foreach ($results as $row) {
    echo "USER: " . $row['user_message'] . "\n";
    echo "BOT: " . $row['bot_response'] . "\n";
    echo "---------------------------------\n";
}
