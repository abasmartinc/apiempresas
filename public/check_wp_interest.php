<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();
$builder = $db->table('radar_demo_events');
$count = $builder->where('source', 'marketing_wp_plugin')
                 ->orWhere('event_type', 'click_cta_coming_soon')
                 ->countAllResults();

echo "Total WP Plugin CTA clicks: " . $count . "\n";

// Let's get some details about the clicks (e.g., dates)
$events = $builder->select('created_at, ip_address')
                  ->where('source', 'marketing_wp_plugin')
                  ->orWhere('event_type', 'click_cta_coming_soon')
                  ->orderBy('created_at', 'DESC')
                  ->get()
                  ->getResultArray();

foreach ($events as $e) {
    echo $e['created_at'] . " - IP: " . $e['ip_address'] . "\n";
}
