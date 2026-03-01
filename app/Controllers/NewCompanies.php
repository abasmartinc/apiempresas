<?php

namespace App\Controllers;

use App\Models\CompanyModel;

class NewCompanies extends BaseController
{
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    public function index()
    {
        $latestCompanies = $this->companyModel->getLatestCompanies(24);

        $data = [
            'title'            => 'Empresas nuevas constituidas en España | Detecta oportunidades comerciales',
            'metaDescription'  => 'Consulta las empresas recién constituidas en tu provincia cada día. Filtra por sector, analiza y exporta antes que tu competencia.',
            'latestCompanies'  => $latestCompanies
        ];

        return view('new_companies', $data);
    }
}
