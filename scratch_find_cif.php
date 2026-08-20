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

$name = "Identidad Protegida (RGPD)";

// Find companies
$query = $db->query("
    SELECT c.cif, c.company_name, c.id
    FROM company_administrators ca
    JOIN companies c ON c.id = ca.company_id
    WHERE ca.name = ?
", [$name]);

$rows = $query->getResultArray();

if (count($rows) > 0) {
    echo "Empresas encontradas para " . $name . ":\n";
    foreach ($rows as $row) {
        echo "CIF: " . $row['cif'] . " | Nombre: " . $row['company_name'] . " | ID: " . $row['id'] . "\n";
    }
} else {
    echo "No se encontraron empresas para " . $name . "\n";
}
