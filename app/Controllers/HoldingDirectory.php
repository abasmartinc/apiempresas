<?php

namespace App\Controllers;

use App\Models\HoldingModel;

class HoldingDirectory extends BaseController
{
    public function index()
    {
        $holdingModel = new HoldingModel();
        
        // Capture filters
        $searchQuery = $this->request->getGet('q');
        $minCompanies = (int)$this->request->getGet('min_companies');

        if (!empty($searchQuery)) {
            $holdingModel->like('name', $searchQuery);
        }

        if ($minCompanies > 0) {
            $holdingModel->where('companies_count >=', $minCompanies);
        }

        // Order by size (most companies first), then by name
        $holdingModel->orderBy('companies_count', 'DESC');
        $holdingModel->orderBy('name', 'ASC');

        // Paginate holdings (50 per page)
        $holdings = $holdingModel->paginate(50);
        $pager = $holdingModel->pager;

        $data = [
            'holdings' => $holdings,
            'pager'    => $pager,
            'searchQuery' => $searchQuery,
            'minCompanies' => $minCompanies,
            'title'    => 'Directorio de Grupos Empresariales y Holdings de España',
            'meta_description' => 'Explora el listado completo de más de 134.000 grupos empresariales y holdings registrados en España. Accede a su estructura de filiales y datos consolidados.',
            'canonical' => site_url('listado-de-grupos-empresariales'),
            'robots'   => 'index, follow'
        ];

        return view('holding_directory', $data);
    }
}
