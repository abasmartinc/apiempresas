<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$controller = new \App\Controllers\SeoController();

$slug = 'restaurantes-y-puestos-de-comida';
$parts = explode('-', $slug, 2);
$cnaeCode = $parts[0];

echo "Original cnaeCode: $cnaeCode\n";

if (!is_numeric($cnaeCode)) {
    // We use reflection just to test the private resolveCnaeCodes, or we can copy logic
    $reflection = new \ReflectionMethod('\App\Controllers\SeoController', 'resolveCnaeCodes');
    $reflection->setAccessible(true);
    $resolution = $reflection->invoke($controller, $slug);
    
    print_r($resolution);
    
    if ($resolution && !empty($resolution['codes'])) {
        $cnaeCode = $resolution['codes'][0];
        echo "Resolved cnaeCode: $cnaeCode\n";
    }
}

// Test what getCnaeData returns
$reflection2 = new \ReflectionMethod('\App\Controllers\SeoController', 'getCnaeData');
$reflection2->setAccessible(true);
$data = $reflection2->invoke($controller, $cnaeCode);

print_r($data);

if (!$data || empty($data['total'])) {
    echo "Would redirect! Data is null or total is empty.\n";
} else {
    echo "Success! Total is: " . $data['total'] . "\n";
}

