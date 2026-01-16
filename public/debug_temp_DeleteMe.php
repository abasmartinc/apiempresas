<?php
// Load CodeIgniter's bootstrap file to access the framework
// Adjust the path to where the main index.php or bootstrap logic is if necessary.
// Actually, simpler to just use raw PHP PDO if we can gets credentials, but using CI is better.
// We can just rely on the fact that this file is in public/ and try to bootstrap CI?
// Bootstrapping CI externally is hard.

// Let's try to just use CodeIgniter's entry point but hook into it? No.

// Simplest way: A controller method.
// I can add a temporary route to the already existing Routes.php -> a temporary Controller method.

// But I can't easily add a controller method without modifying existing files significantly.
// Let's modify `ActivityLogs.php` controller temporarily to print the schema?
// Add a method `debug_schema` and a route.

/*
Modification to Admin/ActivityLogs.php
Add:
public function debug_schema() {
    $db = \Config\Database::connect();
    $query = $db->query("SHOW COLUMNS FROM user_activity_logs");
    $results = $query->getResultArray();
    echo "<pre>";
    print_r($results);
    exit;
}
*/
