<?php
require 'public/index.php';
$model = new \App\Models\CompanyModel();
print_r($model->getLatestCompanies(10));
