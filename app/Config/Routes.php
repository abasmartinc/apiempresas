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
$routes->get('register_sucess', 'Register::register_sucess');
$routes->post('signup', 'Register::store');
$routes->get('dashboard', 'Dashboard::index');
$routes->get('search_company', 'Search::search_company');
$routes->post('search_company', 'Search::search_company_post');
$routes->get('search', 'Search::index');
// Billing
$routes->get('billing', 'Billing::index');
$routes->get('billing/purchase_success', 'Billing::purchase_success');
$routes->get('billing/manage', 'Billing::billing_manage');
$routes->get('billing', 'Billing::index');
$routes->post('billing/checkout', 'Billing::checkout');
$routes->get('billing/success', 'Billing::success'); // callback Stripe
$routes->get('billing/cancel', 'Billing::cancel');   // cancel Stripe/PayPal
$routes->get('billing/purchase-success', 'Billing::purchase_success');
$routes->get('billing/manage', 'Billing::billing_manage');
$routes->get('billing/invoices', 'Billing::invoices');
$routes->get('billing/invoices/download/(:num)', 'Billing::invoice_download/$1');
$routes->post('billing/rotate-key', 'Billing::rotate_key');
$routes->post('billing/cancel-subscription', 'Billing::cancel_subscription');

// PayPal return
$routes->get('billing/paypal/return', 'Billing::paypalReturn');

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
    $routes->get('api/v1/companies', 'Api\V1\CompaniesByCif::index');
    $routes->get('api/v1/companies/search', 'Api\V1\CompaniesSearch::index');
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

// Admin Routes
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('users', 'Admin\Dashboard::index');
    $routes->get('users/create', 'Admin\Dashboard::create');
    $routes->post('users/store', 'Admin\Dashboard::store');
    $routes->get('users/edit/(:num)', 'Admin\Dashboard::edit/$1');
    $routes->post('users/update', 'Admin\Dashboard::update');
    $routes->get('users/delete/(:num)', 'Admin\Dashboard::delete/$1');
    $routes->get('users/email/(:num)', 'Admin\Dashboard::compose/$1');
    $routes->get('users/toggle-api-access/(:num)', 'Admin\Dashboard::toggle_api_access/$1');
    $routes->post('users/send', 'Admin\Dashboard::send');
    
    // Logs de búsqueda
    $routes->get('logs', 'Admin\Dashboard::logs');
    $routes->get('logs/toggle-included/(:num)', 'Admin\Dashboard::toggle_log_included/$1');
    $routes->get('logs/check-cif', 'Admin\Dashboard::check_cif');

    $routes->get('api-requests', 'Admin\Dashboard::api_requests');
    $routes->get('usage-daily', 'Admin\Dashboard::usage_daily');
    
    // Companies CRUD
    $routes->get('companies', 'Admin\Dashboard::companies');
    $routes->get('companies/create', 'Admin\Dashboard::company_create');
    $routes->post('companies/store', 'Admin\Dashboard::company_store');
    $routes->get('companies/edit/(:num)', 'Admin\Dashboard::company_edit/$1');
    $routes->post('companies/update', 'Admin\Dashboard::company_update');
    $routes->get('companies/delete/(:num)', 'Admin\Dashboard::company_delete/$1');

    // Plans CRUD
    $routes->get('plans', 'Admin\Dashboard::plans');
    $routes->get('plans/create', 'Admin\Dashboard::plan_create');
    $routes->post('plans/store', 'Admin\Dashboard::plan_store');
    $routes->get('plans/edit/(:num)', 'Admin\Dashboard::plan_edit/$1');
    $routes->post('plans/update', 'Admin\Dashboard::plan_update');
    $routes->get('plans/delete/(:num)', 'Admin\Dashboard::plan_delete/$1');

    // API Keys CRUD
    $routes->get('api-keys', 'Admin\Dashboard::api_keys');
    $routes->get('api-keys/create', 'Admin\Dashboard::api_key_create');
    $routes->post('api-keys/store', 'Admin\Dashboard::api_key_store');
    $routes->get('api-keys/edit/(:num)', 'Admin\Dashboard::api_key_edit/$1');
    $routes->post('api-keys/update', 'Admin\Dashboard::api_key_update');
    $routes->get('api-keys/delete/(:num)', 'Admin\Dashboard::api_key_delete/$1');

    // Subscriptions CRUD
    $routes->get('subscriptions', 'Admin\Dashboard::subscriptions');
    $routes->get('subscriptions/create', 'Admin\Dashboard::subscription_create');
    $routes->post('subscriptions/store', 'Admin\Dashboard::subscription_store');
    $routes->get('subscriptions/edit/(:num)', 'Admin\Dashboard::subscription_edit/$1');
    $routes->post('subscriptions/update', 'Admin\Dashboard::subscription_update');
    $routes->get('subscriptions/delete/(:num)', 'Admin\Dashboard::subscription_delete/$1');

    // Email Logs
    $routes->get('email-logs', 'Admin\Dashboard::email_logs');

    // Invoices
    $routes->get('invoices', 'Admin\Dashboard::invoices');
    $routes->get('invoices/download/(:num)', 'Admin\Dashboard::invoice_download/$1');
});

// Webhooks
$routes->post('webhook/stripe', 'Webhook::stripe');

// Sitemap
$routes->get('sitemap.xml', 'Sitemap::index');
$routes->get('sitemap-companies-(:num).xml', 'Sitemap::companies/$1');

// Company SEO Pages (Regex: Letter + 7 Digits + Char + optional slug)
// Must be last to avoid conflicts
$routes->get('empresa/(:num)-(:any)', 'Company::showById/$1/$2');
$routes->get('empresa/(:num)', 'Company::showById/$1');
$routes->get('([a-zA-Z][0-9]{7}[a-zA-Z0-9].*)', 'Company::show/$1');
