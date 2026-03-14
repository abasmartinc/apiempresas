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

    public function sectorProvince($sectorSlug, $provinceSlug)
    {
        $province = $this->deSlugify($provinceSlug);
        $sector = $this->resolveCnaeCodes($sectorSlug);
        
        if (!$sector) {
            return redirect()->to(site_url("empresas-nuevas/{$provinceSlug}"));
        }

        return $this->renderRadar('general', $province, $sector);
    }

    private function getCombinedData($sectorSlug, $province)
    {
        $db = \Config\Database::connect();
        $resolution = $this->resolveCnaeCodes($sectorSlug);
        if (!$resolution) return null;

        $codes = $resolution['codes'];
        $cnaeLabel = $resolution['label'];

        $builder = $this->companyModel->builder();
        if (strtolower($province) === 'alicante') {
            $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
        } else {
            $builder->where('registro_mercantil', $province);
        }

        if (count($codes) === 1) $builder->where('cnae_code LIKE', $codes[0] . '%');
        else {
            $builder->groupStart();
            foreach ($codes as $code) $builder->orLike('cnae_code', $code, 'after');
            $builder->groupEnd();
        }

        $total = $builder->countAllResults(false);
        if ($total === 0) return null;

        $companies = $builder->select('id, company_name as name, cif, address, municipality, cnae_code, fecha_constitucion')
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit(60)
            ->get()
            ->getResultArray();

        return [
            'province' => $province,
            'sector_label' => $cnaeLabel,
            'sector_code' => $codes[0],
            'total' => $total,
            'total_formatted' => number_format($total, 0, ',', '.'),
            'companies' => $companies
        ];
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

    public function newRadarLongTail($sectorSlug, $provinceSlug)
    {
        $sector = $this->resolveCnaeCodes($sectorSlug);
        $province = $this->deSlugify($provinceSlug);

        if (!$sector) {
            return redirect()->to(site_url('empresas-nuevas/' . $provinceSlug));
        }

        return $this->renderRadar('general', $province, $sector);
    }


    public function provinceCatalog($provinceSlug)
    {
        $province = $this->deSlugify($provinceSlug);
        $data = $this->getRadarData($province, null, 'mes');

        if (!$data || empty($data['total_context_count'])) {
            return redirect()->to(site_url('empresas-nuevas'));
        }

        $data['title'] = "Empresas en {$province} | Listado y Estadísticas | APIEmpresas";
        $data['meta_description'] = "Descubre las " . number_format($data['total_context_count'], 0, ',', '.') . " empresas en {$province}. Análisis de sectores y empresas de reciente creación.";
        $data['canonical'] = site_url(uri_string());
        
        // SEO Headings
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
        $data = $this->getRadarData($province, $sector, $period);
        if (!$data) return redirect()->to(site_url('empresas-nuevas'));

        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $viewFile = 'seo/radar_new_companies_province';
        } elseif ($sector) {
            $viewFile = 'seo/radar_new_companies_sector';
        } else {
            $viewFile = ($period === 'general') ? 'seo/radar_new_companies' : 'seo/radar_new_companies_period';
        }
        return view($viewFile, $data);
    }

    public function getRadarData($province, $sectorInput, $period, $limit = 100)
    {
        $db = \Config\Database::connect();
        
        if (is_array($sectorInput)) {
            $sector = $sectorInput;
        } else {
            $slug = $sectorInput ? url_title($sectorInput, '-', true) : null;
            $sector = $slug ? $this->resolveCnaeCodes($slug) : null;
        }
        $sectorLabel = $sector ? $sector['label'] : null;

        $builder = $this->companyModel->builder();
        $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, cnae_code as cnae, registro_mercantil, objeto_social');
        
        // Province Filter
        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
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
                $builder->orLike('cnae_code', $code, 'after');
            }
            $builder->groupEnd();
        }

        // Period Filter
        if ($period === 'hoy') {
            $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
            $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
            $builder->where('fecha_constitucion', $targetDate);
        } elseif ($period === 'semana') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
        } elseif ($period === 'mes' || $period === '30days') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')));
        } else {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
            $period = 'general';
        }

        $builder->where('fecha_constitucion IS NOT NULL');
        $builder->orderBy('fecha_constitucion', 'DESC');
        $companies = $builder->get($limit)->getResultArray();

        // Stats Logic
        $statsBuilder = function() use ($db, $province, $sector) {
            $b = $db->table('companies');
            if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
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
            $b->where('fecha_constitucion IS NOT NULL');
            return $b;
        };

        $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
        $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
        
        $stats = [
            'hoy' => $statsBuilder()->where('fecha_constitucion', $targetDate)->countAllResults(),
            'semana' => $statsBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
            'mes' => $statsBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))->countAllResults(),
            '30days' => $statsBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))->countAllResults()
        ];

        // Heading Generation
        $headingTime = "";
        if ($period === 'hoy') $headingTime = " hoy";
        elseif ($period === 'semana') $headingTime = " esta semana";
        elseif ($period === 'mes' || $period === '30days') $headingTime = " este mes";

        if ($sector) {
            $headingPrefix = "Empresas nuevas";
            $headingHighlight = mb_strtolower($sector['label'], 'UTF-8');
            $headingSuffix = " de ";
            $headingMiddle = " en ";
            $headingLocation = ($province && mb_strtolower($province, 'UTF-8') !== 'españa') ? ucfirst(mb_strtolower($province, 'UTF-8')) : "España";
        } elseif ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $headingPrefix = "Nuevas empresas";
            $headingHighlight = ucfirst(mb_strtolower($province, 'UTF-8'));
            $headingSuffix = " en ";
            $headingMiddle = "";
            $headingLocation = "";
        } else {
            $headingPrefix = "Empresas nuevas";
            $headingHighlight = "";
            $headingSuffix = "";
            $headingMiddle = " en ";
            $headingLocation = "España";
        }

        $title = trim($headingPrefix . $headingTime . $headingSuffix . $headingHighlight . $headingMiddle . $headingLocation);
        $statKey = ($period === 'general' || $period === 'mes') ? 'mes' : $period;
        $totalCount = $stats[$statKey] ?? $stats['mes'];
        $dynamicPrice = calculate_radar_price($totalCount);
        $isLowResults = $totalCount < 5;

        // Sectors / Provinces Top
        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $topData = $db->table('companies')
                ->select('cnae_label, COUNT(id) as total')
                ->where('registro_mercantil', $province)
                ->where('cnae_label IS NOT NULL')->where('cnae_label !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy('cnae_label')->orderBy('total', 'DESC')->limit(12)->get()->getResultArray();
        } else {
            $topData = $db->table('companies')
                ->select('registro_mercantil as cnae_label, COUNT(id) as total')
                ->where('registro_mercantil IS NOT NULL')->where('registro_mercantil !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy('registro_mercantil')->orderBy('total', 'DESC')->limit(12)->get()->getResultArray();
        }

        $relatedSectors = $db->query("SELECT cnae_label as label FROM seo_stats_cnae WHERE LENGTH(cnae_label) < 100 ORDER BY total_companies DESC LIMIT 12")->getResultArray();

        $data = [
            'title' => $title,
            'meta_description' => "Listado de empresas recién constituidas en " . ($province ?: "España") . ". Datos oficiales del BORME.",
            'companies' => $companies,
            'stats' => $stats,
            'top_sectors' => $topData, 
            'related_sectors' => $relatedSectors,
            'province' => $province, 
            'sector' => $sector,
            'sector_label' => $sectorLabel,
            'total_context_count' => $totalCount,
            'dynamic_price' => $dynamicPrice,
            'period' => $period,
            'is_low_results' => $isLowResults,
            'robots' => $isLowResults ? 'noindex, follow' : 'index, follow',
            'canonical' => site_url(uri_string()),
            'heading_prefix' => $headingPrefix,
            'heading_suffix' => $headingSuffix,
            'heading_highlight' => $headingHighlight,
            'heading_middle' => $headingMiddle,
            'heading_location' => $headingLocation,
            'heading_time' => $headingTime,
            'paywall_level' => 'strong'
        ];

        if ($isLowResults) {
            if ($province && $sectorLabel) {
                $data['national_sector_url'] = site_url("empresas-nuevas-sector/" . url_title($sectorLabel, '-', true));
                $data['general_directory_url'] = site_url("empresas-" . url_title($sectorLabel, '-', true) . "-en-" . ($province ? url_title($province, '-', true) : 'madrid'));
            } elseif ($province) {
                $data['general_directory_url'] = site_url("empresas/" . url_title($province, '-', true));
            }
        }

        return $data;
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
        
        // Si el slug empieza por número (formato legacy: 4121-construccion), lo extraemos
        $parts = explode('-', $slug, 2);
        if (is_numeric($parts[0])) {
            $code = $parts[0];
            $row = $db->query("SELECT label_2009 as label FROM cnae_2009_2025 WHERE cnae_2009 = ? LIMIT 1", [$code])->getRowArray();
            if ($row) return ['codes' => [$code], 'label' => $this->normalizeLabel($row['label'])];
            return ['codes' => [$code], 'label' => 'Sector ' . $code];
        }

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


    private function normalizeLabel($label)
    {
        $label = mb_convert_case(mb_strtolower($label), MB_CASE_TITLE, "UTF-8");
        $label = str_replace([' De ', ' Y ', ' En ', ' Con ', ' Por ', ' Para ', ' Al ', ' La ', ' Los ', ' Las '], 
                             [' de ', ' y ', ' en ', ' con ', ' por ', ' para ', ' al ', ' la ', ' los ', ' las '], $label);
        return ucfirst($label);
    }

    /**
     * Webhook CRON (Sincronización Nocturna)
     */
    public function syncStatsWebhook($token)
    {
        $secretToken = 'sync_seo_api_2026';
        if ($token !== $secretToken) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Token inválido']);
        }

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $db = \Config\Database::connect();
        $provincesCount = 0;
        $sectorsCount = 0;

        // 1. Sincronizar Provincias
        $provinces = $db->query("SELECT DISTINCT registro_mercantil FROM companies WHERE registro_mercantil IS NOT NULL AND registro_mercantil != ''")->getResultArray();

        foreach ($provinces as $row) {
            $province = $row['registro_mercantil'];
            $totalRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE registro_mercantil = ?", [$province])->getRow();
            $total = $totalRow->total;

            $oneYearAgo = date('Y-m-d', strtotime('-1 year'));
            $newRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE registro_mercantil = ? AND fecha_constitucion >= ?", [$province, $oneYearAgo])->getRow();
            $newCount = $newRow->total;

            $growthPct = ($total > 0) ? round(($newCount / $total) * 100, 2) : 0;

            $topSectors = $db->query("
                SELECT cnae_code as cnae, cnae_label, COUNT(id) as total 
                FROM companies 
                WHERE registro_mercantil = ? AND cnae_label IS NOT NULL AND cnae_label != '' 
                AND fecha_constitucion >= ?
                GROUP BY cnae_code, cnae_label 
                ORDER BY total DESC 
                LIMIT 8
            ", [$province, date('Y-m-d', strtotime('-90 days'))])->getResultArray();

            $sql = "INSERT INTO seo_stats (province, total_companies, growth_pct, top_sectors) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        total_companies = VALUES(total_companies), 
                        growth_pct = VALUES(growth_pct), 
                        top_sectors = VALUES(top_sectors)";
            $db->query($sql, [$province, $total, $growthPct, json_encode($topSectors)]);
            $provincesCount++;
        }

        // 2. Sincronizar CNAE
        $cnaes = $db->query("SELECT DISTINCT cnae_code, cnae_label FROM companies WHERE cnae_code IS NOT NULL AND cnae_label IS NOT NULL AND cnae_label != ''")->getResultArray();

        foreach ($cnaes as $row) {
            $cnaeCode = $row['cnae_code'];
            $cnaeLabel = $row['cnae_label'];

            if (empty($cnaeCode) || strlen($cnaeCode) > 6) continue;

            $totalRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE cnae_code = ?", [$cnaeCode])->getRow();
            $total = $totalRow->total;

            if ($total == 0) continue;

            $topProvinces = $db->query("
                SELECT registro_mercantil as provincia, COUNT(id) as total 
                FROM companies 
                WHERE cnae_code = ? AND registro_mercantil IS NOT NULL AND registro_mercantil != ''
                AND fecha_constitucion >= ?
                GROUP BY registro_mercantil 
                ORDER BY total DESC 
                LIMIT 8
            ", [$cnaeCode, date('Y-m-d', strtotime('-90 days'))])->getResultArray();

            $sql = "INSERT INTO seo_stats_cnae (cnae_code, cnae_label, total_companies, top_provinces) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        cnae_label = VALUES(cnae_label),
                        total_companies = VALUES(total_companies), 
                        top_provinces = VALUES(top_provinces)";
            $db->query($sql, [$cnaeCode, $cnaeLabel, $total, json_encode($topProvinces)]);
            $sectorsCount++;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sincronización SEO completada',
            'stats' => [
                'provinces_updated' => $provincesCount,
                'sectors_updated' => $sectorsCount
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Genera un archivo CSV compatible con Excel para la descarga del listado comprado.
     */
    public function exportExcel()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para descargar el listado.');
        }

        $sector   = $this->request->getGet('sector')   ?: '';
        $province = $this->request->getGet('provincia') ?: 'España';
        $period   = $this->request->getGet('period')   ?: $this->request->getGet('rango') ?: '30days';
        $cnae     = $this->request->getGet('cnae')     ?: ''; 

        $allowedPeriods = ['7', '30', '90', 'hoy', 'semana', 'mes', '30days', 'general'];
        if (!in_array($period, $allowedPeriods, true)) {
            $period = '30days';
        }

        if ($cnae !== '') {
            $db      = \Config\Database::connect();
            $builder = $db->table('companies');
            $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, municipality, objeto_social');
            $builder->where('cnae_code LIKE', $cnae . '%');

            if ($province && strtolower($province) !== 'españa') {
                if (strtolower($province) === 'alicante') {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $builder->where('registro_mercantil', $province);
                }
            }
            $builder->orderBy('fecha_constitucion', 'DESC');
            $builder->limit(2000);
            $companies = $builder->get()->getResultArray();
            $filename  = "Directorio_" . preg_replace('/[^A-Za-z0-9_]/', '_', $cnae) . "_" . str_replace(' ', '_', $province) . ".csv";
        } else {
            // Reutilizamos el motor renderRadar pero pedimos datos crudos si fuera necesario
            // Por ahora, implementamos la lógica de fetch aquí brevemente para evitar dependencias circulares complejas
            // o simplemente llamamos a renderRadar internamente si lo adaptamos.
            // Para simplificar, usamos una versión ligera de la query del Radar:
            $db = \Config\Database::connect();
            $builder = $db->table('companies');
            $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, municipality, objeto_social');
            
            if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
                if (strtolower($province) === 'alicante') {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $builder->where('registro_mercantil', $province);
                }
            }

            if ($sector) {
                $resolution = $this->resolveCnaeCodes(url_title($sector, '-', true));
                if ($resolution) {
                    $codes = $resolution['codes'];
                    if (count($codes) === 1) $builder->where('cnae_code LIKE', $codes[0] . '%');
                    else {
                        $builder->groupStart();
                        foreach ($codes as $code) $builder->orLike('cnae_code', $code, 'after');
                        $builder->groupEnd();
                    }
                }
            }

            if ($period === 'hoy') {
                $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
                $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
                $builder->where('fecha_constitucion', $targetDate);
            } elseif ($period === 'semana' || $period === '7') {
                $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
            } elseif ($period === '90') {
                $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
            } else {
                // Default covers 'mes', '30days', '30' and 'general'
                $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
            }

            $builder->orderBy('fecha_constitucion', 'DESC');
            $builder->limit(5000);
            $companies = $builder->get()->getResultArray();
            $filename  = "Listado_Nuevas_Empresas_" . str_replace(' ', '_', $sector) . "_" . str_replace(' ', '_', $province) . ".xls";
        }

        if (ob_get_length()) ob_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Estructura HTML compatible con Excel para aplicar estilos (inline para máxima compatibilidad)
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
        
        $thStyle = 'background-color: #2563eb; color: #ffffff; font-weight: bold; border: 1px solid #000000; padding: 10px; text-align: center;';
        $tdStyle = 'border: 1px solid #cccccc; padding: 8px; vertical-align: top;';
        $textStyle = $tdStyle . ' mso-number-format:"\@";'; // Forzar formato texto
        
        echo '<table border="1">';
        echo '<thead><tr>';
        echo '<th style="' . $thStyle . '">Nombre de la Empresa</th>';
        echo '<th style="' . $thStyle . '">CIF</th>';
        echo '<th style="' . $thStyle . '">Sector CNAE</th>';
        echo '<th style="' . $thStyle . '">Provincia</th>';
        echo '<th style="' . $thStyle . '">Municipio</th>';
        echo '<th style="' . $thStyle . '">Fecha Registro</th>';
        echo '<th style="' . $thStyle . '">Objeto Social</th>';
        echo '</tr></thead><tbody>';

        foreach ($companies as $company) {
            $rawDate   = $company['fecha_constitucion'] ?? '';
            $ts        = $rawDate ? strtotime(str_replace('/', '-', $rawDate)) : false;
            $cleanDate = ($ts && $ts >= strtotime('1900-01-01') && $ts <= strtotime('2100-01-01')) ? date('d/m/Y', $ts) : '';

            echo '<tr>';
            echo '<td style="' . $tdStyle . '">' . esc($company['name'] ?? '') . '</td>';
            echo '<td style="' . $textStyle . '">' . esc($company['cif'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['cnae_label'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['registro_mercantil'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['municipality'] ?? $company['municipio'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . $cleanDate . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['objeto_social'] ?? '') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table></body></html>';
        exit();
    }
}
