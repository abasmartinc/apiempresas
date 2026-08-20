<?php
namespace App\Controllers;

class TestCif extends BaseController
{
    public function index()
    {
        $cifs = ['B22827737', 'B91366344', 'B05378195', 'B41374380', 'J91982967'];

        $model = new \App\Models\CompanyModel();
        helper(['api', 'company']);

        foreach ($cifs as $cif) {
            echo "Testing $cif...\n";
            try {
                $company = $model->getByCif($cif, true);
                if (!$company) {
                    echo "Not found\n";
                    continue;
                }
                $masked = mask_company_data($company);
                $companyId = $company['id'] ?? null;
                $filtered = filter_company_data($masked);
                
                echo "Success, length of JSON: " . strlen(json_encode($filtered)) . "\n";
            } catch (\Throwable $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
                echo $e->getTraceAsString() . "\n";
            }
        }
    }
}
