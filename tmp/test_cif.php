<?php
// tmp/test_cif.php
require 'index.php'; // Boot CodeIgniter
$model = new \App\Models\CompanyModel();
$cif = 'B65410045';
$result = $model->getByCif($cif);

if ($result) {
    echo "Found: " . $result['name'] . " (ID: " . $result['id'] . ")\n";
    print_r($result);
} else {
    echo "Not found CIF: $cif\n";
    
    // Check if it exists in DB at all without model
    $db = \Config\Database::connect();
    $raw = $db->query("SELECT * FROM companies WHERE cif = ?", [$cif])->getRowArray();
    if ($raw) {
        echo "Raw DB found ID: " . $raw['id'] . "\n";
        echo "CNAE code: " . $raw['cnae_code'] . "\n";
    } else {
        echo "Raw DB also not found CIF: $cif\n";
    }
}
