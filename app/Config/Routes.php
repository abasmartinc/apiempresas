<?php

use CodeIgniter\Router\RouteCollection;



/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('enter', 'Login::index');
$routes->get('documentation', 'Documentation::index');
$routes->get('enter', 'Login::index');          // muestra login
$routes->get('logout', 'Login::logout');        // cierre de sesión
$routes->post('login', 'Login::authenticate');        // cierre de sesión
$routes->get('search', 'Search::index');
$routes->get('register', 'Register::index');
$routes->post('signup', 'Register::store');
$routes->get('dashboard', 'Dashboard::index');
$routes->get('search_company', 'Search::search_company');
$routes->get('billing', 'Billing::index');
$routes->get('usage', 'Usage::index');
$routes->get('prices', 'Prices::index');
$routes->get('contact', 'Contact::index');
$routes->get('blog', 'Blog::index');
$routes->get('blog/post', 'Blog::post');
$routes->get('blog/get_posts', 'Blog::get_posts');
$routes->get('blog/(:segment)', 'Blog::post/$1');
$routes->get('get-posts-grid', 'Blog::get_posts_grid');


// ----------- API ----------- //
$routes->post('authenticate', 'AuthController::authenticate');
$routes->group('', ['filter' => 'jwt'], static function($routes) {
    $routes->get('api/v1/companies', 'Api\V1\Companies::index');
});
// ----------- API ----------- //

// Swagger
$routes->cli('swagger:generate', 'App\Commands\GenerateSwaggerCommand::run');
$routes->get('documentation', 'SwaggerController::index');
