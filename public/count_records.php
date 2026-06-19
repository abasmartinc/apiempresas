<?php
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();
$count = $db->table('company_enrichment')
            ->where('ai_seo_text IS NOT NULL')
            ->where('ai_pitch IS NULL')
            ->countAllResults();

echo "COUNT_RECORDS: " . $count . "\n";
