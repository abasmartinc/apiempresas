<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('enter', 'Login::index');
$routes->get('documentation', 'Documentation::index');
$routes->get('register', 'Register::index');
$routes->get('logout', 'Login::logout');

$routes->post('login', 'AuthController::login');
$routes->get('search', 'Search::index');

// Grupo protegido: requiere JWT + DBGROUP
$routes->group('', ['filter' => 'jwt'], static function($routes) {
    $routes->get('api/v1/companies', 'Api\V1\Companies::index');
});

// Swagger
$routes->cli('swagger:generate', 'App\Commands\GenerateSwaggerCommand::run');
$routes->get('documentation', 'SwaggerController::index');
