<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\UsersuscriptionsModel;

class RadarController extends BaseController
{
    protected $companyModel;
    protected $subscriptionModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->subscriptionModel = new UsersuscriptionsModel();
        helper(['company', 'pricing']);
    }

    public function index()
    {
        return $this->renderRadar('general');
    }

    public function today()
    {
        return $this->renderRadar('hoy');
    }

    public function week()
    {
        return $this->renderRadar('semana');
    }

    public function month()
    {
        return $this->renderRadar('mes');
    }

    public function sector($sectorSlug)
    {
        $sector = $this->resolveCnaeCodes($sectorSlug);
        if (!$sector) {
            return redirect()->to(site_url('empresas-nuevas'));
        }
        return $this->renderRadar('general', null, $sector);
    }

    public function cnae($slug)
    {
        // El slug suele ser código-nombre
        $parts = explode('-', $slug, 2);
        $cnaeCode = $parts[0];

        if (!is_numeric($cnaeCode)) {
            $resolution = $this->resolveCnaeCodes($slug);
            if ($resolution && !empty($resolution['codes'])) {
                $cnaeCode = $resolution['codes'][0];
            } else {
                return redirect()->to(site_url('empresas-nuevas'));
            }
        }

        $data = $this->getCnaeData($cnaeCode);
        if (!$data) {
            return redirect()->to(site_url('empresas-nuevas'));
        }

        // Prefer shorter label from resolution if possible
        $slugName = (is_numeric($parts[0]) && isset($parts[1])) ? $parts[1] : $slug;
        $resolution = $this->resolveCnaeCodes($slugName);
        if ($resolution) {
            $data['cnae_label'] = $resolution['label'];
        }

        $data['title'] = "Empresas de {$data['cnae_label']} en España | Directorio CNAE {$cnaeCode}";
        $data['meta_description'] = "Listado completo de {$data['total_formatted']} empresas de {$data['cnae_label']}. Vea las provincias con más actividad.";
        $data['canonical'] = site_url(uri_string());
        $data['paywall_level'] = 'none';

        return view('seo/radar_companies_sector', $data);
    }

    public function provinceCatalog($provinceSlug)
    {
        $province = $this->deSlugify($provinceSlug);
        $data = $this->getProvinceData($province);

        if (!$data || empty($data['total'])) {
            return redirect()->to(site_url('empresas-nuevas'));
        }

        $data['title'] = "Empresas en {$province} | Listado y Estadísticas | APIEmpresas";
        $data['meta_description'] = "Descubre las {$data['total_formatted']} empresas en {$province}. Análisis de sectores y empresas de reciente creación.";
        $data['canonical'] = site_url(uri_string());
        $data['paywall_level'] = 'none';

        // SEO Headings for the view
        $data['heading_highlight'] = ucfirst(mb_strtolower($province, 'UTF-8'));
        $data['heading_title'] = "Empresas en " . $data['heading_highlight'];

        return view('seo/radar_companies_province', $data);
    }

    public function province($provinceSlug)
    {
        $province = $this->deSlugify($provinceSlug);
        return $this->renderRadar('mes', $province);
    }

    private function renderRadar($period, $province = null, $sector = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $this->companyModel->builder();
        $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, cnae_code as cnae, registro_mercantil, objeto_social');
        
        $locationLabel = $province ? ucfirst(mb_strtolower($province)) : "España";
        if ($sector) {
            $locationLabel = $sector['label'] . ($province ? " en " . ucfirst(mb_strtolower($province)) : " en España");
        }

        // Province Filter
        if ($province) {
            if (strtolower($province) === 'alicante') {
                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
            } else {
                $builder->where('registro_mercantil', $province);
            }
        }

        // Sector Filter
        if ($sector && !empty($sector['codes'])) {
            $builder->groupStart();
            foreach ($sector['codes'] as $code) {
                $builder->orLike('cnae_code', $code, 'after'); // Using cnae_code for better performance
            }
            $builder->groupEnd();
        }

        $title = "";
        $metaDesc = "";
        $locationLabel = $province ? ucfirst(mb_strtolower($province)) : "España";
        
        if ($period === 'hoy') {
            $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
            $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
            $builder->where('fecha_constitucion', $targetDate);
            $title = "Empresas Nuevas de $locationLabel Creadas Hoy";
            $metaDesc = "Listado de empresas recién constituidas hoy en $locationLabel. Accede a datos de BORME en tiempo real.";
        } elseif ($period === 'semana') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
            $title = "Nuevas Empresas de $locationLabel esta Semana";
            $metaDesc = "Descubre las sociedades constituidas en los últimos 7 días en $locationLabel. Datos actualizados para prospección comercial.";
        } elseif ($period === 'mes') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')));
            $title = "Nuevas Empresas de $locationLabel este Mes";
            $metaDesc = "Análisis de empresas de $locationLabel constituidas recientemente. Listado completo de nuevas sociedades.";
        } else {
            // General Hub / Sector Hub
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
            $period = 'general';
            $title = ($sector ? "Empresas de " : "Radar de ") . "$locationLabel";
            $metaDesc = "Accede al radar de constituciones societarias de $locationLabel. Las últimas empresas dadas de alta listas para contactar.";
        }

        $builder->orderBy('fecha_constitucion', 'DESC');
        $companies = $builder->get(100)->getResultArray();

        // Stats Logic
        $statsBuilder = function() use ($db, $province, $sector) {
            $b = $db->table('companies');
            if ($province) {
                if (strtolower($province) === 'alicante') {
                    $b->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $b->where('registro_mercantil', $province);
                }
            }
            if ($sector && !empty($sector['codes'])) {
                $b->groupStart();
                foreach ($sector['codes'] as $code) $b->orLike('cnae_code', $code, 'after');
                $b->groupEnd();
            }
            return $b;
        };

        $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
        $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
        
        $stats = [
            'hoy' => $statsBuilder()->where('fecha_constitucion', $targetDate)->countAllResults(),
            'semana' => $statsBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
            '30days' => $statsBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))->countAllResults()
        ];

        // Impact/Sectors Section
        if ($province) {
            // Top sections in province (using sector stats)
            $topData = $db->query("SELECT cnae_label, total_companies as total FROM seo_stats_cnae ORDER BY total_companies DESC LIMIT 8")->getResultArray();
        } else {
            // Provinces top
            $topData = $db->query("SELECT province as cnae_label, total_companies as total FROM seo_stats ORDER BY total_companies DESC LIMIT 8")->getResultArray();
        }

        $relatedSectors = $db->query("SELECT cnae_label as label FROM seo_stats_cnae ORDER BY total_companies DESC LIMIT 12")->getResultArray();

        $statKey = ($period === 'mes' || $period === 'general') ? '30days' : $period;
        $totalCount = $stats[$statKey] ?? $stats['30days'];
        
        $dynamicPrice = calculate_radar_price($totalCount);
        
        $isLowResults = $totalCount < 5;

        // Heading Variables
        $heading_highlight = $province ? $locationLabel : "Hoy";
        $heading_prefix = $province ? "Nuevas Empresas en " : "Nuevas Empresas ";
        $heading_location = $province ? "" : "España";
        $heading_middle = "";

        if (!$province) {
            if ($period === 'semana') {
                $heading_prefix = "Nuevas Empresas esta ";
                $heading_highlight = "Semana";
            } elseif ($period === 'mes') {
                $heading_prefix = "Nuevas Empresas este ";
                $heading_highlight = "Mes";
            }
        }

        $data = [
            'title' => $title,
            'meta_description' => $metaDesc,
            'companies' => $companies,
            'stats' => $stats,
            'top_sectors' => $topData, 
            'related_sectors' => $relatedSectors,
            'province' => $province, 
            'sector' => $sector,
            'total_context_count' => $totalCount,
            'dynamic_price' => $dynamicPrice,
            'period' => $period,
            'is_low_results' => $isLowResults,
            'robots' => $isLowResults ? 'noindex, follow' : 'index, follow',
            'canonical' => site_url(uri_string()),
            
            'heading_prefix' => $heading_prefix,
            'heading_highlight' => $heading_highlight,
            'heading_location' => $heading_location,
            'heading_middle' => $heading_middle,
            'paywall_level' => 'strong'
        ];

        if ($province) {
            $viewFile = 'seo/radar_new_companies_province';
        } elseif ($sector) {
            $viewFile = 'seo/radar_new_companies_sector';
        } else {
            $viewFile = ($period === 'general') ? 'seo/radar_new_companies' : 'seo/radar_new_companies_period';
        }
        return view($viewFile, $data);
    }

    private function deSlugify($slug)
    {
        $provinces = [
            'madrid' => 'MADRID',
            'barcelona' => 'BARCELONA',
            'valencia' => 'VALENCIA',
            'sevilla' => 'SEVILLA',
            'alicante' => 'ALICANTE',
            'alacant' => 'ALICANTE',
            'malaga' => 'MALAGA',
            'murcia' => 'MURCIA',
            'cadiz' => 'CADIZ',
            'vizcaya' => 'VIZCAYA',
            'coruna' => 'A CORUNA',
            'asturias' => 'ASTURIAS',
            'zaragoza' => 'ZARAGOZA',
            'pontevedra' => 'PONTEVEDRA',
            'granada' => 'GRANADA',
            'tarragona' => 'TARRAGONA',
            'cordoba' => 'CORDOBA',
            'girona' => 'GIRONA',
            'almeria' => 'ALMERIA',
            'toledo' => 'TOLEDO',
            'badajoz' => 'BADAJOZ',
            'navarra' => 'NAVARRA',
            'jaen' => 'JAEN',
            'cantabria' => 'CANTABRIA',
            'castellon' => 'CASTELLON',
            'huelva' => 'HUELVA',
            'valladolid' => 'VALLADOLID',
            'ciudad-real' => 'CIUDAD REAL',
            'leon' => 'LEON',
            'lleida' => 'LLEIDA',
            'caceres' => 'CACERES',
            'alava' => 'ALAVA',
            'lugo' => 'LUGO',
            'salamanca' => 'SALAMANCA',
            'burgos' => 'BURGOS',
            'albacete' => 'ALBACETE',
            'orense' => 'OURENSE',
            'ourense' => 'OURENSE',
            'larioja' => 'LA RIOJA',
            'rioja' => 'LA RIOJA',
            'guipuzcoa' => 'GUIPUZCOA',
            'huesca' => 'HUESCA',
            'cuenca' => 'CUENCA',
            'zamora' => 'ZAMORA',
            'palencia' => 'PALENCIA',
            'avila' => 'AVILA',
            'segovia' => 'SEGOVIA',
            'teruel' => 'TERUEL',
            'guadalajara' => 'GUADALAJARA',
            'soria' => 'SORIA',
            'islas-baleares' => 'BALEARES',
            'baleares' => 'BALEARES',
            'las-palmas' => 'LAS PALMAS',
            'santa-cruz-de-tenerife' => 'STA CRUZ TENERIFE',
            'tenerife' => 'STA CRUZ TENERIFE',
            'ceuta' => 'CEUTA',
            'melilla' => 'MELILLA',
        ];

        $key = strtolower($slug);
        return $provinces[$key] ?? strtoupper(str_replace('-', ' ', $slug));
    }

    private function resolveCnaeCodes($slug)
    {
        $db = \Config\Database::connect();
        $aliases = [
            'hosteleria' => ['codes' => ['55', '56'], 'label' => 'Hostelería, Restaurantes y Catering'],
            'restaurantes' => ['codes' => ['561'], 'label' => 'Restaurantes y Puestos de Comida'],
            'programacion' => ['codes' => ['62'], 'label' => 'Programación Informática'],
            'marketing' => ['codes' => ['731'], 'label' => 'Marketing y Publicidad'],
            'construccion' => ['codes' => ['41', '42', '43'], 'label' => 'Construcción e Inmobiliaria'],
            'transporte' => ['codes' => ['49', '50', '51', '52', '53'], 'label' => 'Transporte y Logística'],
            'logistica' => ['codes' => ['52'], 'label' => 'Logística y Almacenamiento'],
            'finanzas' => ['codes' => ['64', '65', '66'], 'label' => 'Seguros y Finanzas'],
            'inmobiliaria' => ['codes' => ['68'], 'label' => 'Actividades Inmobiliarias'],
            'sanidad' => ['codes' => ['86'], 'label' => 'Actividades Sanitarias'],
        ];

        $clean = strtr(mb_strtolower($slug), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
        if (isset($aliases[$clean])) return $aliases[$clean];

        $searchTerm = str_replace('-', ' ', $slug);
        $row = $db->query("SELECT cnae_2009 as code, label_2009 as label FROM cnae_2009_2025 WHERE label_2009 LIKE ? LIMIT 1", ["%$searchTerm%"])->getRowArray();
        if ($row) return ['codes' => [$row['code']], 'label' => $this->normalizeLabel($row['label'])];

        $statRow = $db->query("SELECT cnae_code, cnae_label FROM seo_stats_cnae WHERE cnae_label LIKE ? LIMIT 1", ["%$searchTerm%"])->getRowArray();
        if ($statRow) return ['codes' => [$statRow['cnae_code']], 'label' => $this->normalizeLabel($statRow['cnae_label'])];

        return null;
    }

    private function getProvinceData($province)
    {
        $db = \Config\Database::connect();
        $row = $db->query("SELECT * FROM seo_stats WHERE province = ?", [$province])->getRowArray();

        if (!$row) {
            $total = $this->companyModel->builder()->where('registro_mercantil', $province)->countAllResults();
            if ($total === 0) return null;

            $latest = $this->companyModel->builder()
                ->select('id, company_name as name, cif, fecha_constitucion, cnae_label, objeto_social')
                ->where('registro_mercantil', $province)
                ->where('fecha_constitucion IS NOT NULL')
                ->orderBy('fecha_constitucion', 'DESC')
                ->limit(100)
                ->get()->getResultArray();
        } else {
            $total = $row['total_companies'];
            $latest = $this->companyModel->builder()
                ->select('id, company_name as name, cif, fecha_constitucion, cnae_label, objeto_social')
                ->where('registro_mercantil', $province)
                ->where('fecha_constitucion IS NOT NULL')
                ->orderBy('fecha_constitucion', 'DESC')
                ->limit(100)
                ->get()->getResultArray();
        }

        // Stats growth
        $baseBuilder = function () use ($province, $db) {
            $b = $db->table('companies');
            if (mb_strtolower($province, 'UTF-8') === 'alicante') {
                $b->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
            } else {
                $b->where('registro_mercantil', $province);
            }
            $b->where('fecha_constitucion IS NOT NULL');
            return $b;
        };

        $docsToday = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d'))->countAllResults();
        $docsWeek = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();
        $docsMonth = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-01'))->countAllResults();

        return [
            'province' => $row['province'] ?? $province,
            'total' => $total,
            'total_formatted' => number_format($total, 0, ',', '.'),
            'growth_pct' => $row['growth_pct'] ?? 0,
            'top_sectors' => json_decode($row['top_sectors'] ?? '[]', true),
            'companies' => $latest,
            'stats' => [
                'hoy' => $docsToday,
                'semana' => $docsWeek,
                'mes' => $docsMonth
            ]
        ];
    }

    private function getCnaeData($cnaeCode)
    {
        $db = \Config\Database::connect();
        $row = $db->query("SELECT * FROM seo_stats_cnae WHERE cnae_code = ?", [$cnaeCode])->getRowArray();

        if (!$row) {
            $total = $this->companyModel->builder()->where('cnae_code', $cnaeCode)->countAllResults();
            if ($total === 0) {
                $total = $this->companyModel->builder()->where('cnae_code LIKE', $cnaeCode . '%')->countAllResults();
            }
            if ($total === 0) return null;

            $topProvincesFallback = $this->companyModel->builder()
                ->select('registro_mercantil as provincia, COUNT(*) as total')
                ->where('cnae_code LIKE', $cnaeCode . '%')
                ->where('registro_mercantil IS NOT NULL')
                ->where('registro_mercantil !=', '')
                ->groupBy('registro_mercantil')
                ->orderBy('total', 'DESC')
                ->limit(8)
                ->get()->getResultArray();

            return [
                'cnae_code' => $cnaeCode,
                'cnae_label' => "Sector $cnaeCode",
                'total_companies' => $total,
                'total_formatted' => number_format($total, 0, ',', '.'),
                'top_provinces' => $topProvincesFallback,
                'companies' => $this->getLatestCompaniesForCnae($cnaeCode)
            ];
        }

        return [
            'cnae_code' => $row['cnae_code'],
            'cnae_label' => $row['cnae_label'],
            'total_companies' => $row['total_companies'],
            'total_formatted' => number_format($row['total_companies'], 0, ',', '.'),
            'top_provinces' => json_decode($row['top_provinces'] ?? '[]', true),
            'companies' => $this->getLatestCompaniesForCnae($cnaeCode)
        ];
    }

    private function getLatestCompaniesForCnae($cnaeCode)
    {
        return $this->companyModel->builder()
            ->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, objeto_social')
            ->where('cnae_code LIKE', $cnaeCode . '%')
            ->where('fecha_constitucion IS NOT NULL')
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
    }

    private function normalizeLabel($label)
    {
        $label = mb_convert_case(mb_strtolower($label), MB_CASE_TITLE, "UTF-8");
        $label = str_replace([' De ', ' Y ', ' En ', ' Con ', ' Por ', ' Para ', ' Al ', ' La ', ' Los ', ' Las '], 
                             [' de ', ' y ', ' en ', ' con ', ' por ', ' para ', ' al ', ' la ', ' los ', ' las '], $label);
        return ucfirst($label);
    }
}
