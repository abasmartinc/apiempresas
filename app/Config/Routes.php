<?php

use CodeIgniter\Router\RouteCollection;



/** @var RouteCollection $routes */
// --- MÁXIMA PRIORIDAD PARA DEPURACIÓN ---
$routes->get('informes/(:any)', 'SeoReportController::handleReport/$1');

$routes->get('/', 'Home::index');

$routes->post('submit-review', 'Home::submitReview');
$routes->get('billing/export-excel', 'RadarController::exportExcel');
$routes->get('enter', 'Login::index');          // muestra login
$routes->get('logout', 'Login::logout');        // cierre de sesión
$routes->post('login', 'Login::authenticate');        // cierre de sesión

// Forgot Password Flow
$routes->get('forgot-password', 'Login::forgotPassword');
$routes->post('forgot-password', 'Login::sendResetLink');
$routes->get('reset-password/(:any)', 'Login::resetPassword/$1');
$routes->post('reset-password', 'Login::updatePassword');

$routes->get('register', 'Register::index');
$routes->get('register/quick', 'Register::quick');
$routes->get('register_sucess', 'Register::register_sucess');
$routes->post('signup', 'Register::store');
$routes->post('register/quick_store', 'Register::quick_store');
$routes->get('dashboard', 'Dashboard::index');
// $routes->get('empresas-nuevas', 'NewCompanies::index'); // Deprecated route interfering with SeoController::newRadarHub (line 194)
$routes->get('dashboard/kpis', 'Dashboard::kpis_ajax');
$routes->get('dashboard/test-sample', 'Api\V1\DashboardTestApi::getSample');
$routes->get('search_company', 'Search::search_company');
$routes->post('search_company', 'Search::search_company_post');
$routes->match(['get', 'post'], 'search', 'Search::index');
// Billing
$routes->get('billing', 'Billing::index');
$routes->get('billing/purchase_success', 'Billing::purchase_success');
$routes->get('billing/manage', 'Billing::billing_manage');
$routes->get('billing', 'Billing::index');
$routes->match(['GET', 'POST'], 'billing/checkout', 'Billing::checkout');
$routes->get('billing/single_checkout', 'Billing::single_checkout');
$routes->get('checkout/radar-export', 'Billing::order_summary');
$routes->get('billing/success', 'Billing::success'); // callback Stripe
$routes->get('billing/cancel', 'Billing::cancel');   // cancel Stripe/PayPal
$routes->get('billing/purchase-success', 'Billing::purchase_success');
$routes->get('billing/manage', 'Billing::billing_manage');
$routes->get('billing/invoices', 'Billing::invoices');
$routes->get('billing/invoices/download/(:num)', 'Billing::invoice_download/$1');
$routes->get('billing/portal', 'Billing::portal');
$routes->post('billing/rotate-key', 'Billing::rotate_key');
$routes->post('billing/cancel-subscription', 'Billing::cancel_subscription');

// PayPal return
$routes->get('billing/paypal/return', 'Billing::paypalReturn');

$routes->get('consumption', 'Usage::index');
$routes->get('api-empresas', 'ApiPrices::index');
// $routes->get('radar', 'RadarPrices::index'); // Movido a Radar::index para manejar invitados vs usuarios logueados
$routes->get('plugin-wordpress-buscador-empresas', 'Plugin::index');
$routes->get('obtener-plugin-wordpress', 'Plugin::get_plugin');
$routes->get('descargar/plugin-wp', 'Plugin::download');
$routes->get('leads-empresas-nuevas', 'RadarPrices::index');
$routes->get('radar', 'Radar::index'); // Radar Dashboard (Handles login/guest internally)
$routes->get('radar/quickview/(:num)', 'Radar::quickView/$1');
$routes->get('radar/favoritos', 'Radar::favorites');
$routes->get('radar/exportar', 'Radar::export');
$routes->post('radar/toggle-favorite', 'Radar::toggleFavorite');
$routes->post('radar/save-note', 'Radar::saveNote');
$routes->get('radar/map-data', 'Radar::mapData');
$routes->get('radar/kanban', 'Radar::kanban', ['filter' => 'subscription:radar']);
$routes->post('radar/update-favorite-status', 'Radar::updateFavoriteStatus', ['filter' => 'subscription:radar']);
$routes->get('radar/trends', 'Radar::trends', ['filter' => 'subscription:radar']);
$routes->get('radar/trend-data', 'Radar::getTrendData', ['filter' => 'subscription:radar']);
$routes->get('radar/ai-analyze/(:num)', 'Radar::aiAnalyze/$1', ['filter' => 'subscription:radar']);
$routes->post('radar/prepare-contact/(:num)', 'Radar::prepareContact/$1', ['filter' => 'subscription:radar']);
$routes->post('radar/log-event', 'Radar::logEvent', ['filter' => 'subscription:radar']);
$routes->get('contact', 'Contact::index');
$routes->post('contact/send', 'Contact::send');
$routes->get('blog', 'Blog::index');
$routes->get('blog/post', 'Blog::post');
$routes->get('blog/get_posts', 'Blog::get_posts');
$routes->get('blog/(:segment)', 'Blog::post/$1');
$routes->get('get-posts-grid', 'Blog::get_posts_grid');


// Email Tracking
$routes->get('e/o/(:any)', 'EmailTracking::open/$1');
$routes->get('e/c/(:any)', 'EmailTracking::click/$1');

// ----------- API ----------- //
$routes->group('', ['filter' => ['apikey', 'subscription:api']], static function ($routes) {
    $routes->get('api/v1/companies', 'Api\V1\CompaniesByCif::index');
    $routes->get('api/v1/companies/search', 'Api\V1\CompaniesSearch::index');

    // New Commercial Endpoints (Expansion)
    $routes->get('api/v1/companies/score', 'Api\V1\CompanyEnrichmentController::score');
    $routes->get('api/v1/companies/signals', 'Api\V1\CompanyEnrichmentController::signals');
    $routes->get('api/v1/companies/insights', 'Api\V1\CompanyEnrichmentController::insights');
    $routes->get('api/v1/companies/contact-prep', 'Api\V1\CompanyEnrichmentController::contactPrep');
    $routes->get('api/v1/companies/radar', 'Api\V1\RadarApiController::index');

    // Webhooks CRUD
    $routes->get('api/v1/webhooks', 'Api\V1\WebhookController::index');
    $routes->post('api/v1/webhooks', 'Api\V1\WebhookController::create');
    $routes->delete('api/v1/webhooks/(:num)', 'Api\V1\WebhookController::delete/$1');

    // Dedicated Professional Plan routes
    $routes->get('api/v1/professional/search', 'Api\V1\Professional::search');
    $routes->get('api/v1/professional/details', 'Api\V1\Professional::details');
    $routes->get('api/v1/usage', 'Api\V1\UsageController::index');
});
// ----------- API ----------- //

// Triggers & Usage API (Session Auth)
$routes->get('api/user/usage-status', 'Api\UsageStatus::index');
$routes->post('api/user/log-event', 'Api\EventTracker::log');

// Swagger
$routes->cli('swagger:generate', 'App\Commands\GenerateSwaggerCommand::run');
$routes->get('documentation', 'Documentation::index');


$routes->get('map/companies', 'CompanyMapV2Controller::index');
$routes->get('api/geo/provinces', 'CompanyMapV2Controller::provinces');
$routes->get('api/geo/municipalities', 'CompanyMapV2Controller::municipalities');
$routes->get('api/cnae/sections', 'CompanyMapV2Controller::cnaeSections');
$routes->get('api/cnae/groups', 'CompanyMapV2Controller::cnaeGroups');
$routes->get('api/cnae/classes', 'CompanyMapV2Controller::cnaeClasses');
$routes->get('api/cnae/subclasses', 'CompanyMapV2Controller::cnaeSubclasses');
$routes->get('api/map/search', 'CompanyMapV2Controller::search');
$routes->get('api/map/export', 'CompanyMapV2Controller::export');


// Alerts
$routes->get('alerts/confirm/(:any)', 'Alerts::confirm/$1');
$routes->post('alerts/add', 'Alerts::add');

// Leads
$routes->post('leads/subscribe', 'Leads::subscribe');

// Admin Routes
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('metrics', 'Admin\MetricsController::index');
    $routes->get('users', 'Admin\Dashboard::index');
    $routes->get('users/create', 'Admin\Dashboard::create');
    $routes->post('users/store', 'Admin\Dashboard::store');
    $routes->get('users/edit/(:num)', 'Admin\Dashboard::edit/$1');
    $routes->post('users/update', 'Admin\Dashboard::update');
    $routes->get('users/delete/(:num)', 'Admin\Dashboard::delete/$1');
    $routes->get('users/email/(:num)', 'Admin\Dashboard::compose/$1');
    $routes->post('users/send', 'Admin\Dashboard::send');
    $routes->get('users/impersonate/(:num)', 'Admin\Dashboard::impersonate/$1');

    // Bulk Email
    $routes->match(['GET', 'POST'], 'users/email/bulk', 'Admin\Dashboard::compose_bulk');
    $routes->post('users/email/send-bulk', 'Admin\Dashboard::send_bulk');

    // Logs de búsqueda
    $routes->get('logs', 'Admin\Dashboard::logs');
    $routes->get('logs/toggle-included/(:num)', 'Admin\Dashboard::toggle_log_included/$1');
    $routes->get('logs/check-cif', 'Admin\Dashboard::check_cif');
    $routes->get('api-requests', 'Admin\Dashboard::api_requests');
    $routes->get('usage-daily', 'Admin\Dashboard::usage_daily');
    $routes->get('blocked-ips', 'Admin\Dashboard::blocked_ips');
    $routes->get('clear-cache', 'Admin\Dashboard::clear_cache');

    // $routes->get('usage', 'Admin\Dashboard::usage_daily'); // Removed incorrect alias

    // Companies CRUD
    $routes->get('companies', 'Admin\Dashboard::companies');
    $routes->get('companies/kpi/(:any)', 'Admin\Dashboard::company_kpi_ajax/$1');
    $routes->get('kpis-all', 'Admin\Dashboard::all_kpis_ajax');
    $routes->get('kpis-refresh', 'Admin\Dashboard::refresh_kpis_ajax');
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

    // Stripe Test Page
    $routes->get('stripe-test', 'Admin\StripeTest::index');
    $routes->post('stripe-test/checkout', 'Admin\StripeTest::checkout');

    // Email Logs
    $routes->get('email-logs', 'Admin\Dashboard::email_logs');

    // Invoices
    $routes->get('invoices', 'Admin\Dashboard::invoices');
    $routes->get('invoices/download/(:num)', 'Admin\Dashboard::invoice_download/$1');

    // Activity Logs
    $routes->get('activity-logs', 'Admin\ActivityLogs::index');
    $routes->get('activity-logs/user/(:num)', 'Admin\ActivityLogs::user/$1');
    $routes->get('activity-logs/export', 'Admin\ActivityLogs::export');

    // IA Marketing
    $routes->get('ia-marketing', 'Admin\Dashboard::ia_marketing');
    $routes->get('email-history/(:num)', 'Admin\Dashboard::email_history_ajax/$1');

    // Google Search Console
    $routes->get('search-console', 'Admin\Dashboard::search_console');
    $routes->get('search-console/kpis', 'Admin\Dashboard::search_console_kpis');
    $routes->get('search-console/sitemaps', 'Admin\Dashboard::search_console_sitemaps');
    $routes->post('search-console/inspect', 'Admin\Dashboard::search_console_inspect');

    // Email Sender (Manual Assisted)
    $routes->post('send-message', 'Admin\MetricsController::sendMessage');

    // SEO Auto Posts
    $routes->get('seo-auto-posts', 'Admin\SeoAutoPostsController::index');
    $routes->post('seo-auto-posts/store', 'Admin\SeoAutoPostsController::storeKeyword');
    $routes->post('seo-auto-posts/generate-one/(:num)', 'Admin\SeoAutoPostsController::generateOne/$1');
    $routes->post('seo-auto-posts/generate-pending', 'Admin\SeoAutoPostsController::generatePending');
    $routes->post('seo-auto-posts/generate-batch', 'Admin\SeoAutoPostsController::generateBatch');
    $routes->post('seo-auto-posts/retry/(:num)', 'Admin\SeoAutoPostsController::retry/$1');
});

// Webhooks & AI API
$routes->post('webhook/stripe', 'Webhook::stripe');
$routes->post('api/chat', 'AiChat::sendMessage');
$routes->post('api/chat/reset', 'AiChat::resetChat');

// Sitemap
$routes->get('sitemap.xml', 'Sitemap::index');
$routes->get('sitemap-static.xml', 'Sitemap::static');
$routes->get('sitemap-blog.xml', 'Sitemap::blog');
$routes->get('sitemap-directories.xml', 'Sitemap::directories');
$routes->get('sitemap-informes-provincias.xml', 'Sitemap::informesProvincias');
$routes->get('sitemap-informes-sectores.xml', 'Sitemap::informesSectores');
$routes->get('sitemap-informes-wp.xml', 'Sitemap::informesWp');
$routes->get('sitemap-companies-(:num).xml', 'Sitemap::companies/$1');

// --- Webhook CRON SEO ---
$routes->get('cron/seo-sync/(:any)', 'RadarController::syncStatsWebhook/$1');
$routes->get('cron/radar-cache-clear/(:any)', 'RadarController::clearRadarCache/$1');

// --- Export Routes ---
$routes->get('excel/preview', 'RadarController::excel_preview');
$routes->post('excel/unlock', 'RadarController::excel_unlock');
$routes->post('api/quick-unlock', 'Api\QuickUnlock::index');
$routes->post('checkout/radar-email', 'RadarController::sendExportEmail');

// --- Programmatic SEO Routes ---
$routes->get('empresas/(:any)', 'RadarController::provinceCatalog/$1');

// Radar Demo (Conversion Page)
$routes->get('radar-demo', 'Radar::demo');
$routes->get('radar/preview', 'Radar::preview');
$routes->post('radar/preview', 'Radar::preview_store');
$routes->post('tracking/radar-demo-event', 'TrackingController::processRadarEvent');

// Radar Hub (New Companies Strategy)
$routes->get('empresas-nuevas', 'RadarController::index'); // Central Hub
$routes->get('empresas-nuevas-hoy', 'RadarController::today');
$routes->get('empresas-nuevas-semana', 'RadarController::week');
$routes->get('empresas-nuevas-mes', 'RadarController::month');
$routes->get('empresas-nuevas-sector/(:any)', 'RadarController::sector/$1');

// Combinations (e.g. /empresas-nuevas/hosteleria-en-madrid) -> NEW
$routes->get('empresas-nuevas/(:any)-en-(:any)', 'RadarController::newRadarLongTail/$1/$2');

$routes->get('empresas-nuevas/(:any)', 'RadarController::province/$1'); // Province Radar

// Combination Sector + Province (e.g. /empresas-programacion-en-madrid)
// This regex matches "empresas-[anything]-en-[anything]" // MUST BE AFTER RADAR LONG-TAIL
$routes->get('empresas-(:any)-en-(:any)', 'RadarController::sectorProvince/$1/$2');
// --- Programmatic SEO Routes ---

// Directorios SEO (Legacy)
$routes->get('directorio', 'Directory::index');
$routes->get('directorio/provincia/(:any)', 'Directory::province/$1');
$routes->get('directorio/provincia/(:any)/(:num)', 'Directory::province/$1/$2');
$routes->get('directorio/cnae/(:any)', 'Directory::cnae/$1');
$routes->get('directorio/cnae/(:any)/(:num)', 'Directory::cnae/$1/$2');
$routes->get('directorio/ultimas-empresas-registradas', 'Directory::latest');
$routes->get('directorio/ultimas-empresas-registradas/(:num)', 'Directory::latest/$1');
$routes->get('directorio/provincia/(:any)/cnae/(:any)', 'Directory::provinceCnae/$1/$2');
$routes->get('directorio/provincia/(:any)/cnae/(:any)/(:num)', 'Directory::provinceCnae/$1/$2/$3');

// Company SEO Pages (Regex: Letter + 7 Digits + Char + optional slug)
// Must be last to avoid conflicts
$routes->get('empresa/export/(:num)', 'Company::exportPdf/$1');
$routes->get('empresa/(:num)-(:any)', 'Company::showById/$1/$2');
$routes->get('empresa/(:num)', 'Company::showById/$1');
$routes->get('test-pdf', 'TestPdf::index');

// Fallback for broken "no disponible" links
$routes->get('no%20disponible(:any)', 'Company::handleBrokenCif/$1');
$routes->get('no disponible(:any)', 'Company::handleBrokenCif/$1');

// Company Suggestions (Professional Landing)
$routes->get('autocompletado-cif-empresas', 'CompanySuggestions::index');
$routes->get('autocompletado-cif-empresas/get', 'CompanySuggestions::getSuggestions');
$routes->addRedirect('company-suggestions', 'autocompletado-cif-empresas');
$routes->addRedirect('company-suggestions/get', 'autocompletado-cif-empresas/get');


// Traffic Advice (Chrome prefetching)
$routes->get('.well-known/traffic-advice', static function () {
    return service('response')->setJSON([
        'advice' => [
            'prefetch' => true,
            'prerender' => true
        ]
    ]);
});

$routes->group('api', function($routes) {
    $routes->post('tracking/event', 'TrackingController::logEvent');
});

// Admin Tracking de Eventos
$routes->get('admin/event-tracking', 'Admin\MetricsController::eventTracking');
$routes->get('admin/event-tracking/table', 'Admin\MetricsController::getTable');
$routes->get('admin/event-tracking/ai-analyze', 'Admin\MetricsController::getAiAnalysis');

// Company pages: CIF-based and Slug-based URLs (must be last)
$routes->get('([a-zA-Z][0-9]{7}[a-zA-Z0-9].*)', 'Company::show/$1');
$routes->get('(:segment)', 'Company::show/$1');
