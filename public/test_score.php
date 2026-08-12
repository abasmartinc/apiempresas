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
        WHERE companies.registro_mercantil IS NULL OR companies.registro_mercantil = ''
        LIMIT 50
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $failed = 0;
    foreach ($rows as $row) {
        $score = calculateCompanySeoScore($row);
        if ($score < 5) {
            echo "FAILED: ID {$row['id']} - Score: $score\n";
            print_r($row);
            $failed++;
            if ($failed >= 3) break;
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
