<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

helper(['text', 'seo_dynamic', 'company']);

$db = \Config\Database::connect();
$db->saveQueries = false; 
$builder = $db->table('companies');
$builder->select('companies.id, companies.cif, companies.company_name as name, companies.cnae_code as cnae, companies.registro_mercantil as province, companies.objeto_social as corporate_purpose, company_enrichment.ai_seo_text')
        ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left');

$lastId = 0;
$batchSize = 10000;
$totalProcessed = 0;
$totalIncluded = 0;

$companies = $builder->where('companies.id >', $lastId)
                     ->orderBy('companies.id', 'ASC')
                     ->limit($batchSize)
                     ->get()
                     ->getResultArray();
                     
foreach ($companies as $company) {
    $lastId = $company['id'];
    $totalProcessed++;

    if (!shouldIndexCompany($company)) {
        continue;
    }
    $totalIncluded++;
}

echo "Included $totalIncluded out of $totalProcessed in first batch.\n";
