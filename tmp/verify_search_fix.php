<?php
require_once __DIR__ . '/../vendor/autoload.php';
// Bootstrapping CI4
require __DIR__ . '/../app/Config/Paths.php';
$paths = new \Config\Paths();
require __DIR__ . '/../system/Boot.php';
\Config\Services::autoloader()->initialize(new \Config\Autoloader(), new \Config\Modules());
\CodeIgniter\Database\Config::connect();

use App\Models\CompanyModel;

$model = new CompanyModel();
$q = "THE GRILL IN LOVE MADRID SL";

echo "Testing Search for: $q\n";
$results = $model->searchMany($q, 10);

echo "Results found: " . count($results) . "\n";
foreach ($results as $res) {
    echo "- Name: {$res['name']} | CIF: {$res['cif']}\n";
}

$q2 = "GRILL IN LOVE";
echo "\nTesting Search for: $q2\n";
$results2 = $model->searchMany($q2, 10);
echo "Results found: " . count($results2) . "\n";
foreach ($results2 as $res) {
    echo "- Name: {$res['name']} | CIF: {$res['cif']}\n";
}
