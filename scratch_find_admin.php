<?php

// Fix for CodeIgniter CLI missing constants
if (!defined('FCPATH')) {
    define('FCPATH', __DIR__ . '/public/');
}

// Ensure the current directory is pointing to the front controller's directory
chdir(FCPATH);

require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootSpark($paths);

$db = \Config\Database::connect();

// Find an administrator who has between 2 and 5 positions
$query = $db->query("
    SELECT name, COUNT(*) as total 
    FROM company_administrators 
    GROUP BY name 
    HAVING total BETWEEN 2 AND 5 
    LIMIT 1
");

$row = $query->getRowArray();

if ($row) {
    echo "Nombre: " . $row['name'] . "\n";
    echo "Cargos: " . $row['total'] . "\n";
    
    // Create a slug from the name
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $row['name'])));
    echo "Slug sugerido: " . $slug . "\n";
} else {
    echo "No se encontraron administradores con 2-5 cargos.\n";
}
