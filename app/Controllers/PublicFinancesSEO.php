<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PublicFinancesSEO extends BaseController
{
    private function getContractsSlugMap()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'seo_contracts_slugmap_v2';
        $map = $cache->get($cacheKey);
        
        if (!$map) {
            $db = \Config\Database::connect();
            $organsData = $db->query("SELECT DISTINCT organo_contratacion as name FROM company_contracts WHERE organo_contratacion IS NOT NULL AND organo_contratacion != ''")->getResultArray();
            helper('text');
            $map = [];
            foreach ($organsData as $org) {
                $slug = url_title($org['name'], '-', true);
                if (!empty($slug)) {
                    $map[$slug] = $org['name'];
                }
            }
            $cache->save($cacheKey, $map, 86400 * 7);
        }
        return $map;
    }

    public function contractsHub()
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q = trim($this->request->getVar('q') ?? '');
        
        $db = \Config\Database::connect();
        
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $whereClause = "organo_contratacion IS NOT NULL AND organo_contratacion != ''";
        $params = [];
        if ($q !== '') {
            $whereClause .= " AND organo_contratacion LIKE ?";
            $params[] = '%' . $q . '%';
        }

        // Cache the first page (no search)
        $cache = \Config\Services::cache();
        $cacheKey = 'seo_contracts_hub_page1_v2';
        $data = null;
        
        if ($page === 1 && $q === '') {
            $data = $cache->get($cacheKey);
        }
        
        if (!$data) {
            $organsData = $db->query("
                SELECT organo_contratacion as name, COUNT(id) as total_contracts, SUM(importe_adjudicacion) as total_amount, COUNT(DISTINCT company_cif) as total_companies
                FROM company_contracts
                WHERE $whereClause
                GROUP BY organo_contratacion
                ORDER BY total_contracts DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$perPage, $offset]))->getResultArray();

            helper('text');
            $organs = [];
            foreach ($organsData as $org) {
                $org['slug'] = url_title($org['name'], '-', true);
                if (!empty($org['slug'])) {
                    $organs[] = $org;
                }
            }
            
            $totalRow = $db->query("SELECT COUNT(DISTINCT organo_contratacion) as total FROM company_contracts WHERE $whereClause", $params)->getRow();
            $total = $totalRow->total ?? 0;
            
            $statsRow = $db->query("SELECT COUNT(id) as total_c, SUM(importe_adjudicacion) as total_a FROM company_contracts WHERE $whereClause", $params)->getRow();
            $global_contracts = $statsRow->total_c ?? 0;
            $global_amount = $statsRow->total_a ?? 0;
            
            // Get absolute max for progress bar
            $maxRow = $db->query("SELECT COUNT(id) as c FROM company_contracts WHERE organo_contratacion IS NOT NULL AND organo_contratacion != '' GROUP BY organo_contratacion ORDER BY c DESC LIMIT 1")->getRow();
            $max_contracts = $maxRow->c ?? 1;

            $data = [
                'organs' => $organs,
                'total' => $total,
                'max_contracts' => $max_contracts,
                'global_contracts' => $global_contracts,
                'global_amount' => $global_amount
            ];
            
            if ($page === 1 && $q === '') {
                $cache->save($cacheKey, $data, 86400 * 7);
            }
        }

        // Pagination links
        $pager = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $data['total'], 'seo_es');

        return view('seo/hub_contratos', [
            'organs' => $data['organs'],
            'total_organs' => $data['total'],
            'global_contracts' => $data['global_contracts'],
            'global_amount' => $data['global_amount'],
            'max_contracts' => $data['max_contracts'],
            'pager' => $pagination,
            'currentPage' => $page,
            'searchQuery' => $q,
            'title' => "Licitaciones del Estado: Buscador de Adjudicatarias",
            'meta_description' => "Descubre qué empresas ganan las licitaciones públicas. Base de datos con el historial de adjudicaciones del Estado y los mayores contratistas públicos.",
            'canonical' => site_url('licitaciones-del-estado') . ($page > 1 ? '?page=' . $page : '')
        ]);
    }

    public function contractsByOrgan($slug)
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q    = trim($this->request->getVar('q') ?? '');
        
        $slugMap = $this->getContractsSlugMap();
        $organName = $slugMap[$slug] ?? null;
        
        if (!$organName) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Órgano no encontrado.");
        }

        $db = \Config\Database::connect();
        
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        // Build WHERE clause
        $where  = 'c.organo_contratacion = ?';
        $params = [$organName];
        if ($q !== '') {
            $where   .= ' AND (c.company_cif LIKE ? OR comp.company_name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $contracts = $db->query("
            SELECT c.*, comp.company_name
            FROM company_contracts c
            LEFT JOIN companies comp ON c.company_cif = comp.cif
            WHERE $where
            ORDER BY c.fecha_adjudicacion DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]))->getResultArray();

        // Count for total (need separate query for name filter via JOIN)
        $countWhere  = 'c.organo_contratacion = ?';
        $countParams = [$organName];
        if ($q !== '') {
            $countWhere   .= ' AND (c.company_cif LIKE ? OR comp.company_name LIKE ?)';
            $countParams[] = '%' . $q . '%';
            $countParams[] = '%' . $q . '%';
        }
        $totalRow = $db->query("
            SELECT COUNT(c.id) as total
            FROM company_contracts c
            LEFT JOIN companies comp ON c.company_cif = comp.cif
            WHERE $countWhere
        ", $countParams)->getRow();
        $total = $totalRow->total ?? 0;

        $pager      = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $total, 'seo_es');

        $organTitle = mb_convert_case($organName, MB_CASE_TITLE, "UTF-8");

        return view('seo/listado_organo', [
            'organName'   => $organName,
            'organTitle'  => $organTitle,
            'contracts'   => $contracts,
            'pager'       => $pagination,
            'currentPage' => $page,
            'total'       => $total,
            'searchQuery' => $q,
            'slug'        => $slug,
            'title'       => "Licitaciones y Contratos de {$organTitle} | Empresas Adjudicatarias",
            'meta_description' => "Listado de empresas que han ganado contratos públicos y licitaciones del {$organTitle}. Importes, fechas e historial de contratistas.",
            'canonical'   => site_url('licitaciones-del-estado/organo-' . $slug . ($page > 1 ? '/' . $page : ''))
        ]);
    }

    private function getSubsidiesSlugMap()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'seo_subsidies_slugmap_v2';
        $map = $cache->get($cacheKey);
        
        if (!$map) {
            $db = \Config\Database::connect();
            $convData = $db->query("SELECT DISTINCT convocatoria as name FROM company_subsidies WHERE convocatoria IS NOT NULL AND convocatoria != ''")->getResultArray();
            helper('text');
            $map = [];
            foreach ($convData as $conv) {
                $slug = url_title($conv['name'], '-', true);
                if (!empty($slug)) {
                    $map[$slug] = $conv['name'];
                }
            }
            $cache->save($cacheKey, $map, 86400 * 7);
        }
        return $map;
    }

    public function subsidiesHub()
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q = trim($this->request->getVar('q') ?? '');
        
        $db = \Config\Database::connect();
        
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $cache = \Config\Services::cache();
        $cacheKey = 'seo_subsidies_hub_page1_v2';
        $data = null;
        
        if ($page === 1 && $q === '') {
            $data = $cache->get($cacheKey);
        }
        
        if (!$data) {
            $where  = '1=1';
            $params = [];
            if ($q !== '') {
                $where   .= ' AND convocatoria LIKE ?';
                $params[] = '%' . $q . '%';
            }

            $convocatoriasData = $db->query("
                SELECT convocatoria, slug, total_subsidies, total_companies, total_amount
                FROM seo_hub_subvenciones
                WHERE $where
                ORDER BY total_subsidies DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$perPage, $offset]))->getResultArray();

            helper('text');
            $convocatorias = [];
            foreach ($convocatoriasData as $conv) {
                $conv['name'] = $conv['convocatoria'];
                $convocatorias[] = $conv;
            }

            $statsRow = $db->query("
                SELECT COUNT(*) as total_convocatorias, SUM(total_subsidies) as total_s, MAX(total_subsidies) as max_s, SUM(total_amount) as total_a
                FROM seo_hub_subvenciones
                WHERE $where
            ", $params)->getRow();

            $data = [
                'convocatorias'    => $convocatorias,
                'total'            => $statsRow->total_convocatorias ?? 0,
                'max_subsidies'    => $statsRow->max_s ?? 1,
                'global_subsidies' => $statsRow->total_s ?? 0,
                'global_amount'    => $statsRow->total_a ?? 0,
            ];
            
            if ($page === 1 && $q === '') {
                $cache->save($cacheKey, $data, 86400 * 7);
            }
        }

        $pager = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $data['total'], 'seo_es');

        return view('seo/hub_subvenciones', [
            'convocatorias'    => $data['convocatorias'],
            'total_convocatorias' => $data['total'],
            'global_subsidies' => $data['global_subsidies'],
            'global_amount'    => $data['global_amount'],
            'max_subsidies'    => $data['max_subsidies'],
            'pager'            => $pagination,
            'currentPage'      => $page,
            'searchQuery'      => $q,
            'title'            => "Directorio de Subvenciones a Empresas | Buscador Oficial",
            'meta_description' => "Listado de las mayores convocatorias de subvenciones de España. Descubre qué fondos europeos, ayudas estatales y autonómicas reciben las empresas.",
            'canonical'        => site_url('subvenciones-empresas') . ($page > 1 ? '?page=' . $page : '')
        ]);
    }

    public function subsidiesByConvocatoria($slug)
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q    = trim($this->request->getVar('q') ?? '');
        
        $slugMap = $this->getSubsidiesSlugMap();
        $convName = $slugMap[$slug] ?? null;
        
        if (!$convName) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Convocatoria no encontrada.");
        }

        $db = \Config\Database::connect();
        
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $where  = 's.convocatoria = ?';
        $params = [$convName];
        if ($q !== '') {
            $where   .= ' AND (s.company_cif LIKE ? OR comp.company_name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $subsidies = $db->query("
            SELECT s.*, comp.company_name
            FROM company_subsidies s
            LEFT JOIN companies comp ON s.company_cif = comp.cif
            WHERE $where
            ORDER BY s.fecha_concesion DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]))->getResultArray();

        $countWhere  = 's.convocatoria = ?';
        $countParams = [$convName];
        if ($q !== '') {
            $countWhere   .= ' AND (s.company_cif LIKE ? OR comp.company_name LIKE ?)';
            $countParams[] = '%' . $q . '%';
            $countParams[] = '%' . $q . '%';
        }
        $totalRow = $db->query("
            SELECT COUNT(s.id) as total
            FROM company_subsidies s
            LEFT JOIN companies comp ON s.company_cif = comp.cif
            WHERE $countWhere
        ", $countParams)->getRow();
        $total = $totalRow->total ?? 0;

        $pager      = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $total, 'seo_es');
        
        $convTitle = mb_convert_case($convName, MB_CASE_TITLE, "UTF-8");

        return view('seo/listado_convocatoria', [
            'convocatoriaName' => $convName,
            'convTitle'        => $convTitle,
            'subsidies'        => $subsidies,
            'pager'            => $pagination,
            'currentPage'      => $page,
            'total'            => $total,
            'searchQuery'      => $q,
            'slug'             => $slug,
            'title'            => "Empresas Beneficiarias: {$convTitle}",
            'meta_description' => "Listado oficial de empresas y entidades que han recibido la subvención {$convTitle}. Importes y fechas de concesión.",
            'canonical'        => site_url('subvenciones-empresas/convocatoria-' . $slug . ($page > 1 ? '/' . $page : ''))
        ]);
    }

    // ── TOP CONTRACTORS ──────────────────────────────────────────────────────
    public function topContractors()
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q    = trim($this->request->getVar('q') ?? '');

        $db      = \Config\Database::connect();
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $cache = \Config\Services::cache();
        $cacheKey = 'seo_top_contractors_p1';
        $cachedData = null;

        if ($page === 1 && $q === '') {
            $cachedData = $cache->get($cacheKey);
        }

        if (!$cachedData) {
            $where  = '1=1';
            $params = [];
            if ($q !== '') {
                $where   .= ' AND (company_cif LIKE ? OR company_name LIKE ?)';
                $params[] = '%' . $q . '%';
                $params[] = '%' . $q . '%';
            }

            $rows = $db->query("
                SELECT *
                FROM seo_ranking_contratos
                WHERE $where
                ORDER BY total_amount DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$perPage, $offset]))->getResultArray();

            $totalRow = $db->query("
                SELECT COUNT(*) as total
                FROM seo_ranking_contratos
                WHERE $where
            ", $params)->getRow();
            $total = $totalRow->total ?? 0;

            $statsRow = $db->query("SELECT SUM(total_amount) as total_a, SUM(total_contracts) as total_c FROM seo_ranking_contratos")->getRow();
            
            $cachedData = [
                'rows' => $rows,
                'total' => $total,
                'total_a' => $statsRow->total_a ?? 0,
                'total_c' => $statsRow->total_c ?? 0,
            ];

            if ($page === 1 && $q === '') {
                $cache->save($cacheKey, $cachedData, 86400 * 7);
            }
        }

        $rows = $cachedData['rows'];
        $total = $cachedData['total'];
        $global_amount = $cachedData['total_a'];
        $global_contracts = $cachedData['total_c'];

        $pager      = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $total, 'seo_es');

        return view('seo/ranking_contratistas', [
            'companies'       => $rows,
            'total'           => $total,
            'global_amount'   => $global_amount,
            'global_contracts'=> $global_contracts,
            'pager'           => $pagination,
            'currentPage'     => $page,
            'searchQuery'     => $q,
            'title'           => 'Mayores Empresas Contratistas del Estado | Ranking Oficial',
            'meta_description'=> 'Ranking de las empresas españolas que más contratos públicos acumulan. Volumen adjudicado, número de contratos y datos oficiales del Estado.',
            'canonical'       => site_url('mayores-empresas-contratistas-del-estado') . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }

    // ── TOP SUBSIDY RECIPIENTS ───────────────────────────────────────────────
    public function topSubsidyRecipients()
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q    = trim($this->request->getVar('q') ?? '');

        $db      = \Config\Database::connect();
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $cache = \Config\Services::cache();
        $cacheKey = 'seo_top_subsidies_p1';
        $cachedData = null;

        if ($page === 1 && $q === '') {
            $cachedData = $cache->get($cacheKey);
        }

        if (!$cachedData) {
            $where  = '1=1';
            $params = [];
            if ($q !== '') {
                $where   .= ' AND (company_cif LIKE ? OR company_name LIKE ?)';
                $params[] = '%' . $q . '%';
                $params[] = '%' . $q . '%';
            }

            $rows = $db->query("
                SELECT *
                FROM seo_ranking_subvenciones
                WHERE $where
                ORDER BY total_amount DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$perPage, $offset]))->getResultArray();

            $totalRow = $db->query("
                SELECT COUNT(*) as total
                FROM seo_ranking_subvenciones
                WHERE $where
            ", $params)->getRow();
            $total = $totalRow->total ?? 0;

            $statsRow = $db->query("SELECT SUM(total_amount) as total_a, SUM(total_subsidies) as total_s FROM seo_ranking_subvenciones")->getRow();
            
            $cachedData = [
                'rows' => $rows,
                'total' => $total,
                'total_a' => $statsRow->total_a ?? 0,
                'total_s' => $statsRow->total_s ?? 0,
            ];

            if ($page === 1 && $q === '') {
                $cache->save($cacheKey, $cachedData, 86400 * 7);
            }
        }

        $rows = $cachedData['rows'];
        $total = $cachedData['total'];
        $global_amount = $cachedData['total_a'];
        $global_subsidies = $cachedData['total_s'];

        $pager      = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $total, 'seo_es');

        return view('seo/ranking_subvencionadas', [
            'companies'        => $rows,
            'total'            => $total,
            'global_amount'    => $global_amount,
            'global_subsidies' => $global_subsidies,
            'pager'            => $pagination,
            'currentPage'      => $page,
            'searchQuery'      => $q,
            'title'            => 'Empresas más Subvencionadas de España | Ranking Oficial',
            'meta_description' => 'Descubre qué empresas han recibido más subvenciones públicas en España. Ranking oficial por importe total de ayudas concedidas.',
            'canonical'        => site_url('empresas-mas-subvencionadas-espana') . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }

    // ── CONTRACTS BY YEAR ────────────────────────────────────────────────────
    public function contractsByYear($year)
    {
        $year = (int) $year;
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q    = trim($this->request->getVar('q') ?? '');

        $db      = \Config\Database::connect();
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $where  = 'YEAR(c.fecha_adjudicacion) = ?';
        $params = [$year];
        if ($q !== '') {
            $where   .= ' AND (c.company_cif LIKE ? OR comp.company_name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $contracts = $db->query("
            SELECT c.*, comp.company_name
            FROM company_contracts c
            LEFT JOIN companies comp ON c.company_cif = comp.cif
            WHERE $where
            ORDER BY c.importe_adjudicacion DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]))->getResultArray();

        $totalRow = $db->query("
            SELECT COUNT(c.id) as total, SUM(c.importe_adjudicacion) as total_amount
            FROM company_contracts c
            LEFT JOIN companies comp ON c.company_cif = comp.cif
            WHERE $where
        ", $params)->getRow();
        $total        = $totalRow->total ?? 0;
        $total_amount = $totalRow->total_amount ?? 0;

        $pager      = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $total, 'seo_es');

        return view('seo/listado_ano_contratos', [
            'year'         => $year,
            'contracts'    => $contracts,
            'total'        => $total,
            'total_amount' => $total_amount,
            'pager'        => $pagination,
            'currentPage'  => $page,
            'searchQuery'  => $q,
            'title'        => "Contratos Públicos Adjudicados en {$year} | Licitaciones del Estado",
            'meta_description' => "Listado oficial de contratos públicos adjudicados en {$year}. Empresas adjudicatarias, importes y órganos de contratación.",
            'canonical'    => site_url("licitaciones-del-estado/ano-{$year}") . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }

    // ── SUBSIDIES BY YEAR ────────────────────────────────────────────────────
    public function subsidiesByYear($year)
    {
        $year = (int) $year;
        $page = (int) ($this->request->getVar('page') ?? 1);
        $q    = trim($this->request->getVar('q') ?? '');

        $db      = \Config\Database::connect();
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $where  = 'YEAR(s.fecha_concesion) = ?';
        $params = [$year];
        if ($q !== '') {
            $where   .= ' AND (s.company_cif LIKE ? OR comp.company_name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $subsidies = $db->query("
            SELECT s.*, comp.company_name
            FROM company_subsidies s
            LEFT JOIN companies comp ON s.company_cif = comp.cif
            WHERE $where
            ORDER BY s.importe DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]))->getResultArray();

        $totalRow = $db->query("
            SELECT COUNT(s.id) as total, SUM(s.importe) as total_amount
            FROM company_subsidies s
            LEFT JOIN companies comp ON s.company_cif = comp.cif
            WHERE $where
        ", $params)->getRow();
        $total        = $totalRow->total ?? 0;
        $total_amount = $totalRow->total_amount ?? 0;

        $pager      = \Config\Services::pager();
        $pagination = $pager->makeLinks($page, $perPage, $total, 'seo_es');

        return view('seo/listado_ano_subvenciones', [
            'year'         => $year,
            'subsidies'    => $subsidies,
            'total'        => $total,
            'total_amount' => $total_amount,
            'pager'        => $pagination,
            'currentPage'  => $page,
            'searchQuery'  => $q,
            'title'        => "Subvenciones Concedidas en {$year} | Directorio Oficial",
            'meta_description' => "Listado oficial de subvenciones a empresas concedidas en {$year}. Beneficiarios, importes y convocatorias.",
            'canonical'    => site_url("subvenciones-empresas/ano-{$year}") . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }
}

