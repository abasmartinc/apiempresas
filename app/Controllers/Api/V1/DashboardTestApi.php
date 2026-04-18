<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use CodeIgniter\API\ResponseTrait;

class DashboardTestApi extends BaseController
{
    use ResponseTrait;

    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * Devuelve una muestra aleatoria de empresa válida (CIF + Nombre)
     * para usar en el probador del dashboard.
     */
    public function getSample()
    {
        if (!session('logged_in')) {
            return $this->failUnauthorized();
        }

        // Buscamos 50 empresas recientes para tener variedad pero relativa calidad
        $samples = $this->companyModel->asArray()
            ->select('cif, company_name as name')
            ->where('cif IS NOT NULL')
            ->where('company_name IS NOT NULL')
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->findAll();

        if (empty($samples)) {
            // Un par de fallbacks "hardcoded" de empresas reales/famosas por si la DB está vacía
            $samples = [
                ['cif' => 'A28015865', 'name' => 'TELEFONICA SA'],
                ['cif' => 'A08000143', 'name' => 'CAIXABANK SA'],
                ['cif' => 'A81948077', 'name' => 'AMAZON DATA SERVICES SPAIN SL'],
            ];
        }

        $random = $samples[array_rand($samples)];

        return $this->respond([
            'success' => true,
            'data'    => $random
        ]);
    }
}
