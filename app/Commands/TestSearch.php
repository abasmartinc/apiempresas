<?php
// test_search.php
$model = new \App\Models\CompanyModel();
$res = $model->getBestByName('108 padel equipment sl');
print_r($res);
