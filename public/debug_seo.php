<?php

// Script de diagnóstico rápido
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsConfig = FCPATH . '../app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$model = new \App\Models\CompanyModel();
helper('seo_dynamic_helper');

$cifs = ['B24966525', 'B24360893', 'B23941651'];

echo "REPORTE DE DIAGNÓSTICO SEO\n";
echo "==========================\n\n";

foreach ($cifs as $cif) {
    $company = $model->getByCif($cif);
    if (!$company) {
        echo "CIF $cif: NO ENCONTRADO EN BD\n";
        continue;
    }
    
    $score = calculateCompanySeoScore($company);
    $indexable = shouldIndexCompany($company);
    
    echo "Nombre: " . ($company['name'] ?? 'N/A') . " ($cif)\n";
    echo "Provincia: " . ($company['province'] ?? 'N/A') . "\n";
    echo "CNAE: " . ($company['cnae'] ?? 'N/A') . " (" . ($company['cnae_label'] ?? 'N/A') . ")\n";
    echo "Objeto Social: " . (empty($company['corporate_purpose']) ? 'VACÍO' : 'TIENE CONTENIDO') . "\n";
    echo "Score SEO: $score / 9\n";
    echo "Indexable (Score >= 5): " . ($indexable ? 'SI' : 'NO') . "\n";
    echo "--------------------------\n";
}
