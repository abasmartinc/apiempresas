<?php
require_once 'vendor/autoload.php';
if (!defined('WRITEPATH')) define('WRITEPATH', __DIR__ . '/writable/');
use Google\Client;
use Google\Service\SearchConsole;

$credentialsPath = WRITEPATH . 'credentials/google-service-account.json';
$client = new Client();
$client->setAuthConfig($credentialsPath);
$client->addScope(SearchConsole::WEBMASTERS_READONLY);
$service = new SearchConsole($client);

try {
    $response = $service->sites->listSites();
    $sites = $response->getSiteEntry();
    echo "Available properties:\n";
    foreach ($sites as $site) {
        echo "- " . $site->getSiteUrl() . " (Permission: " . $site->getPermissionLevel() . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
