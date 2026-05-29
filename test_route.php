<?php
require 'public/index.php';
$request = \Config\Services::request();
$router = \Config\Services::router();
$routes = \Config\Services::routes();
$routes->get('directorio/provincia/(:any)', 'Directory::province/$1');
// Mocking the request uri
$request->setPath('directorio/provincia/Araba/%C3%81lava');
$controller = $router->handle($request->getPath());
echo "Matched Controller: " . $controller . "\n";
print_r($router->params());
