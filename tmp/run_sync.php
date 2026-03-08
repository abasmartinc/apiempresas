<?php
require 'app/Config/Paths.php';
require 'vendor/autoload.php';
$app = Config\Services::codeigniter(new Config\App());
$app->initialize();

$c = new \App\Controllers\SeoController();
// Mocking request if needed, but syncStatsWebhook doesn't use it for logic other than response
$c->syncStatsWebhook('sync_seo_api_2026');
echo "Sync completed successfully.\n";
