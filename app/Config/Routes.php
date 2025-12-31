<?php

use CodeIgniter\Router\RouteCollection;



/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('enter', 'Login::index');
$routes->get('documentation', 'Documentation::index');
$routes->get('enter', 'Login::index');          // muestra login
$routes->get('logout', 'Login::logout');        // cierre de sesión
$routes->post('login', 'Login::authenticate');        // cierre de sesión
$routes->get('register', 'Register::index');
$routes->post('signup', 'Register::store');
$routes->get('dashboard', 'Dashboard::index');
$routes->get('search_company', 'Search::search_company');
$routes->post('search_company', 'Search::search_company_post');
$routes->get('search', 'Search::index');
$routes->get('billing', 'Billing::index');
$routes->get('billing/purchase_success', 'Billing::purchase_success');
$routes->get('billing/manage', 'Billing::billing_manage');
$routes->get('usage', 'Usage::index');
$routes->get('prices', 'Prices::index');
$routes->get('contact', 'Contact::index');
$routes->get('blog', 'Blog::index');
$routes->get('blog/post', 'Blog::post');
$routes->get('blog/get_posts', 'Blog::get_posts');
$routes->get('blog/(:segment)', 'Blog::post/$1');
$routes->get('get-posts-grid', 'Blog::get_posts_grid');


// ----------- API ----------- //
$routes->group('', ['filter' => 'apikey'], static function($routes) {
    $routes->get('api/v1/companies', 'Api\V1\Companies::index');
});
// ----------- API ----------- //

// Swagger
$routes->cli('swagger:generate', 'App\Commands\GenerateSwaggerCommand::run');
$routes->get('documentation', 'SwaggerController::index');


$routes->get('map/companies', 'CompanyMapV2Controller::index');
$routes->get('api/geo/provinces', 'CompanyMapV2Controller::provinces');
$routes->get('api/geo/municipalities', 'CompanyMapV2Controller::municipalities');
$routes->get('api/cnae/sections', 'CompanyMapV2Controller::cnaeSections');
$routes->get('api/cnae/groups', 'CompanyMapV2Controller::cnaeGroups');
$routes->get('api/cnae/classes', 'CompanyMapV2Controller::cnaeClasses');
$routes->get('api/cnae/subclasses', 'CompanyMapV2Controller::cnaeSubclasses');
$routes->get('api/map/search', 'CompanyMapV2Controller::search');
$routes->get('api/map/export', 'CompanyMapV2Controller::export');

$routes->post('contact/send', 'Contact::send');
