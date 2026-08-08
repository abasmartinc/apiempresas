<?php

namespace App\Controllers;

use App\Models\HoldingModel;

class HoldingDirectory extends BaseController
{
    public function index()
    {
        $holdingModel = new HoldingModel();
        
        $cache = \Config\Services::cache();
        $cacheKey = 'holdings_directory_stats_v1';
        $stats = $cache->get($cacheKey);
        
        if (!$stats) {
            $db = \Config\Database::connect();
            $totalHoldings = $db->table('holdings')->countAllResults();
            $totalCompaniesRow = $db->table('holdings')->selectSum('companies_count')->get()->getRow();
            $totalCompanies = $totalCompaniesRow ? $totalCompaniesRow->companies_count : 0;
            
            $maxCompaniesRow = $db->table('holdings')->selectMax('companies_count')->get()->getRow();
            $maxCompanies = $maxCompaniesRow ? $maxCompaniesRow->companies_count : 1;
            
            $stats = [
                'totalHoldings' => $totalHoldings,
                'totalCompanies' => $totalCompanies,
                'maxCompanies' => $maxCompanies ?: 1
            ];
            $cache->save($cacheKey, $stats, 86400 * 7); // 7 days cache
        }
        
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

        $page = (int)$this->request->getGet('page') ?: 1;
        $canonicalUrl = site_url('listado-de-grupos-empresariales');
        if ($page > 1) {
            $canonicalUrl .= '?page=' . $page;
        }

        $data = [
            'holdings' => $holdings,
            'pager'    => $pager,
            'searchQuery' => $searchQuery,
            'minCompanies' => $minCompanies,
            'stats'    => $stats,
            'title'    => 'Directorio de Grupos Empresariales y Holdings de España' . ($page > 1 ? " - Página $page" : ''),
            'meta_description' => 'Explora el listado completo de más de 134.000 grupos empresariales y holdings registrados en España. Accede a su estructura de filiales y datos consolidados.',
            'canonical' => $canonicalUrl,
            'robots'   => 'index, follow'
        ];

        return view('holding_directory', $data);
    }
}
