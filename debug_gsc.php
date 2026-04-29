<?php
require_once 'vendor/autoload.php';

// Mock CodeIgniter Constants if needed
if (!defined('WRITEPATH')) define('WRITEPATH', __DIR__ . '/writable/');

use Google\Client;
use Google\Service\SearchConsole;

$credentialsPath = WRITEPATH . 'credentials/google-service-account.json';
$siteUrl = 'https://apiempresas.es/';

if (!file_exists($credentialsPath)) {
    die("Credentials file not found.\n");
}

$client = new Client();
$client->setAuthConfig($credentialsPath);
$client->addScope(SearchConsole::WEBMASTERS_READONLY);

$service = new SearchConsole($client);

try {
    echo "Querying sitemaps for: $siteUrl\n";
    $response = $service->sitemaps->listSitemaps($siteUrl);
    $sitemaps = $response->getSitemap();

    if (!$sitemaps) {
        echo "No sitemaps found.\n";
    } else {
        foreach ($sitemaps as $sitemap) {
            echo "Path: " . $sitemap->getPath() . "\n";
            echo "Type: " . $sitemap->getType() . "\n";
            $contents = $sitemap->getContents();
            if ($contents) {
                foreach ($contents as $content) {
                    echo "  - Type: " . $content->getType() . "\n";
                    echo "    Submitted: " . $content->getSubmitted() . "\n";
                    echo "    Indexed: " . $content->getIndexed() . "\n";
                }
            } else {
                echo "  - No content info available.\n";
            }
            echo "---------------------------\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
