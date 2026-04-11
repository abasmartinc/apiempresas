<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../system/bootstrap.php';

use App\Models\CompanyModel;

$model = new CompanyModel();
$q = "THE GRILL IN LOVE MADRID SL";

echo "Searching for: $q\n";

$db = \Config\Database::connect();
$exact = $db->table('companies')->like('company_name', 'GRILL IN LOVE')->get()->getResultArray();

echo "Exact DB matches (LIKE %GRILL IN LOVE%): " . count($exact) . "\n";
foreach ($exact as $row) {
    echo "- ID: {$row['id']} | Name: {$row['company_name']}\n";
}

$results = $model->searchMany($q, 10);
echo "\nsearchMany results count: " . count($results) . "\n";
foreach ($results as $res) {
    echo "- Name: {$res['name']} | CIF: {$res['cif']}\n";
}
