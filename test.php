<?php
// Boot CI4 to test CompanyModel
define('FCPATH', __DIR__ . '/public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$model = new \App\Models\CompanyModel();
$results = $model->searchMany('restaurante', 100, 1, true);
echo "Count: " . count($results['data']) . "\n";
echo "Has more: " . ($results['meta']['has_more'] ? 'true' : 'false') . "\n";
