<?php
require_once 'app/Helpers/seo_dynamic_helper.php';
try {
    $pdo = new PDO('mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4', 'apiempresas_user', 'WONwyjpsmx3h3$@2');
    $stmt = $pdo->query("
        SELECT companies.id, companies.cif, companies.company_name as name, companies.cnae_code as cnae, 
               companies.registro_mercantil as province, companies.objeto_social as corporate_purpose, 
               company_enrichment.ai_seo_text
        FROM companies 
        LEFT JOIN company_enrichment ON company_enrichment.company_id = companies.id
        ORDER BY companies.id ASC
        LIMIT 50000
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $included = 0;
    foreach ($rows as $row) {
        if (calculateCompanySeoScore($row) >= 5) {
            $included++;
        }
    }
    echo "Included $included out of " . count($rows) . "\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
