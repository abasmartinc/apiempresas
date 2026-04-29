<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
$pathsPath = realpath(__DIR__ . '/app/Config/Paths.php');
require $pathsPath;
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootWeb($paths);

$db = \Config\Database::connect();
echo "Database: " . $db->getDatabase() . " (Driver: " . $db->DBDriver . ")\n";

$query = $db->query("SELECT * FROM email_logs ORDER BY id DESC LIMIT 5");
$results = $query->getResultArray();
echo "Last 5 logs:\n";
print_r($results);

$query = $db->query("SHOW CREATE TABLE email_logs");
$create = $query->getRowArray();
echo "\nCreate Table:\n";
print_r($create);
