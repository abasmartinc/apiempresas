<?php

// Boot CodeIgniter
define('FCPATH', __DIR__ . '/../public/');
require __DIR__ . '/../vendor/autoload.php';
$app = Config\Services::codeigniter();
$app->initialize();

use App\Controllers\Company;
use Config\Services;

$controller = new Company();
$controller->initController(Services::request(), Services::response(), \Config\Services::logger());

$segment = 'B65410045-melwood-europe-sl';
echo "Testing segment: $segment\n";

try {
    $response = $controller->show($segment);
    if ($response instanceof \CodeIgniter\HTTP\RedirectResponse) {
        echo "Redirected to: " . $response->getHeaders()['Location']->getValue() . "\n";
    } else {
        echo "Response received (HTML/View)\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
