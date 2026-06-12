<?php

namespace App\Controllers;

use App\Models\CompanyModel;

class Directory extends BaseController
{
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    public function index()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'directory_index_data_v5';
        
        $data = $cache->get($cacheKey);
        
        if (!$data) {
            // Exclusiones de provincias y CNAEs no válidos
            $invalidNames = [
                '', ' ', '  ', '-', '.', '..', '...', '8', 'N/A', 'NULL', 'UNDEFINED', 
                '00 DESCONOCIDA', 'desconocido', 'desconocida', 'no disponible', 'n/a', 'unknown', 'sin especificar',
                'ÍNDICE ALFABÉTICO DE SOCIEDADES', 'No Detectado'
            ];

            // Obtener lista de provincias únicas con conteo simple
            $provincesData = $this->companyModel->builder()
                ->select('registro_mercantil as name, COUNT(id) as total')
                ->where('registro_mercantil IS NOT NULL')
                ->where('registro_mercantil >=', 'A')
                ->whereNotIn('registro_mercantil', $invalidNames)
                ->groupBy('registro_mercantil')
                ->orderBy('registro_mercantil', 'ASC')
                ->get()
                ->getResultArray();

            $provinces = $provincesData;
            usort($provinces, function($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            $cnaes = $this->companyModel->builder()
                ->select('cnae_code as cnae, COUNT(id) as total')
                ->where('cnae_code IS NOT NULL')
                ->where('cnae_code >=', '0100')
                ->groupBy('cnae_code')
                ->orderBy('total', 'DESC')
                ->get()
                ->getResultArray();

            $db = \Config\Database::connect();
            $cnaeLabels = $db->table('cnae_2009_2025')
                ->select('cnae_2009 as cnae, label_2009 as label')
                ->get()
                ->getResultArray();
                
            $cnaeMap = [];
            foreach ($cnaeLabels as $row) {
                $cnaeMap[$row['cnae']] = $row['label'];
            }

            foreach ($cnaes as &$cnae) {
                $cnae['name'] = $cnaeMap[$cnae['cnae']] ?? "CNAE {$cnae['cnae']}";
            }
            unset($cnae);

            // Últimas 10 empresas para la home del directorio (excluyendo fechas futuras erróneas)
            $latest = $this->companyModel->builder()
                ->select('id, cif, company_name as name, fecha_constitucion as founded, cnae_label, registro_mercantil as province')
                ->where('fecha_constitucion IS NOT NULL')
                ->where('fecha_constitucion <=', date('Y-m-d'))
                ->orderBy('fecha_constitucion', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
            
            $data = [
                'provinces' => $provinces,
                'cnaes'     => $cnaes,
                'latest'    => $latest
            ];
            
            // Cache por 24 horas
            $cache->save($cacheKey, $data, 1296000); // 15 días
        }

        // Calcular máximos dinámicos para barras de densidad
        $maxProvince = !empty($data['provinces']) ? max(array_column($data['provinces'], 'total')) : 1;
        $maxCnae     = !empty($data['cnaes'])     ? max(array_column($data['cnaes'],     'total')) : 1;
        $totalAll    = array_sum(array_column($data['provinces'], 'total'));
        $totalFormatted = number_format($totalAll, 0, ',', '.');
        $numProvinces   = count($data['provinces']);

        helper('pricing');
        $priceData = calculate_directory_price($totalAll);
        $dynamicPrice = $priceData['base_price'];

        return view('directory/index', [
            'provinces'        => $data['provinces'],
            'cnaes'            => $data['cnaes'],
            'latest'           => $data['latest'] ?? [],
            'max_province'     => $maxProvince,
            'max_cnae'         => $maxCnae,
            'dynamic_price'    => $dynamicPrice,
            'title'            => "Directorio de Empresas en España | {$totalFormatted} Sociedades Registradas",
            'meta_description' => "Directorio completo de {$totalFormatted} empresas españolas organizadas por {$numProvinces} provincias y sectores CNAE. Datos oficiales actualizados del Registro Mercantil.",
            'excerptText'      => "Directorio completo de {$totalFormatted} empresas españolas organizadas por {$numProvinces} provincias y sectores CNAE. Datos oficiales actualizados del Registro Mercantil.",
            'canonical'        => site_url('directorio'),
        ]);
    }

    public function province(...$args)
    {
        // Reconstruct province name if it was split by a slash in the URL (e.g., Araba/Álava)
        $page = 1;
        if (count($args) > 1 && is_numeric(end($args))) {
            $page = (int) array_pop($args);
        } elseif (count($args) === 2 && !is_numeric($args[1]) && in_array(strtolower($args[0]), ['araba', 'alicante', 'alacant'])) {
            // Handle cases where the second part is not a number but part of the province name
        }
        
        $provinceName = urldecode(implode('/', $args));
        
        // Pagination
        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = $this->companyModel->builder()
            ->select('id, cif, company_name as name, registro_mercantil as province, cnae_label, fecha_constitucion as founded');
            
        if (in_array(strtolower($provinceName), ['alicante', 'alacant', 'alicante/alacant'])) {
            $builder->where('registro_mercantil', 'Alicante');
        } elseif (in_array(mb_strtolower($provinceName, 'UTF-8'), ['araba/álava', 'álava', 'álava-araba', 'araba', 'alava'])) {
            $builder->where('registro_mercantil', 'Álava');
        } else {
            $builder->where('registro_mercantil', $provinceName);
        }
        
        $companies = $builder->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to(site_url("directorio/provincia/{$provinceName}"));
             }
             return redirect()->to(site_url('directorio'));
        }

        // Total count (cached per province)
        $cache = \Config\Services::cache();
        $countKey = 'prov_total_v5_' . urlencode($provinceName);
        $totalCompanies = $cache->get($countKey);
        if ($totalCompanies === null) {
            $countBuilder = $this->companyModel->builder()->selectCount('id', 'total');
            if (in_array(strtolower($provinceName), ['alicante', 'alacant', 'alicante/alacant'])) {
                $countBuilder->where('registro_mercantil', 'Alicante');
            } elseif (in_array(mb_strtolower($provinceName, 'UTF-8'), ['araba/álava', 'álava', 'álava-araba', 'araba', 'alava'])) {
                $countBuilder->where('registro_mercantil', 'Álava');
            } else {
                $countBuilder->where('registro_mercantil', $provinceName);
            }
            $totalCompanies = (int) $countBuilder->get()->getRowArray()['total'];
            $cache->save($countKey, $totalCompanies, 1296000); // 15 días
        }
        $totalPages = max(1, (int) ceil($totalCompanies / $perPage));

        // Cross-pollination: Top CNAEs in this province
        $crossKey = 'cross_cnae_v5_' . urlencode($provinceName);
        $topCnaes = $cache->get($crossKey);
        
        if (!$topCnaes) {
            $invalidNames = [
                '', ' ', '  ', '-', '.', '..', '...', '8', 'N/A', 'NULL', 'UNDEFINED', 
                '00 DESCONOCIDA', 'desconocido', 'desconocida', 'no disponible', 'n/a', 'unknown', 'sin especificar',
                'ÍNDICE ALFABÉTICO DE SOCIEDADES', 'No Detectado'
            ];
            $cnaeBuilder = $this->companyModel->builder()
                ->select('cnae_code as code, cnae_label as label, COUNT(id) as total');
                
            if (in_array(strtolower($provinceName), ['alicante', 'alacant', 'alicante/alacant'])) {
                $cnaeBuilder->where('registro_mercantil', 'Alicante');
            } elseif (in_array(mb_strtolower($provinceName, 'UTF-8'), ['araba/álava', 'álava', 'álava-araba', 'araba', 'alava'])) {
                $cnaeBuilder->where('registro_mercantil', 'Álava');
            } else {
                $cnaeBuilder->where('registro_mercantil', $provinceName);
            }
            
            $topCnaes = $cnaeBuilder->where('cnae_code IS NOT NULL')
                ->where('cnae_label >=', 'A')
                ->whereNotIn('cnae_label', $invalidNames)
                ->groupBy('cnae_code, cnae_label')
                ->orderBy('total', 'DESC')
                ->limit(12)
                ->get()
                ->getResultArray();
            $cache->save($crossKey, $topCnaes, 1296000); // 15 días
        }

        $totalFormatted = number_format($totalCompanies, 0, ',', '.');
        helper('pricing');
        $priceData = calculate_directory_price($totalCompanies);
        $dynamicPrice = $priceData['base_price'];

        return view('directory/list', [
            'items'           => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $dynamicPrice,
            'province_name'   => $provinceName,
            'robots'          => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'canonical'       => site_url("directorio/provincia/" . urlencode($provinceName)), // siempre pág 1
            'title'           => "{$totalFormatted} Empresas en {$provinceName} | Directorio Oficial",
            'excerptText'     => "Consulta el directorio completo de {$totalFormatted} empresas registradas en {$provinceName}. Datos oficiales actualizados.",
            'header'          => "Directorio de empresas en {$provinceName}",
            'meta_description'=> "Directorio de {$totalFormatted} empresas en {$provinceName}. Busca por nombre, consulta CIF y accede a la ficha oficial de cada sociedad.",
            'cross_links' => [
                'type'     => 'cnae',
                'title'    => "Principales sectores en {$provinceName}",
                'items'    => $topCnaes,
                'province' => $provinceName
            ],
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("directorio/provincia/" . urlencode($provinceName) . "/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("directorio/provincia/" . urlencode($provinceName) . "/" . ($page - 1)) : null,
                'base'    => site_url("directorio/provincia/" . urlencode($provinceName))
            ]
        ]);
    }

    public function cnae(...$args)
    {
        if (empty($args)) {
            return redirect()->to(site_url('directorio'));
        }

        $cnaeCode = $args[0];
        $slug = null;
        $page = 1;

        if (count($args) === 1) {
            // URL: /directorio/cnae/6920
        } elseif (count($args) === 2) {
            if (is_numeric($args[1])) {
                $page = (int)$args[1];
            } else {
                $slug = $args[1];
            }
        } elseif (count($args) >= 3) {
            $slug = $args[1];
            $page = (int)$args[2];
        }

        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        // Get the most frequent CNAE label early to validate the slug
        $cache = \Config\Services::cache();
        $labelKey = 'cnae_label_v5_' . $cnaeCode;
        $cnaeLabel = $cache->get($labelKey);
        if (!$cnaeLabel) {
            $labelRow = $this->companyModel->builder()
                ->select('cnae_label, COUNT(*) as count')
                ->where('cnae_code', $cnaeCode)
                ->where('cnae_label !=', '')
                ->groupBy('cnae_label')
                ->orderBy('count', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            $cnaeLabel = $labelRow['cnae_label'] ?? "CNAE {$cnaeCode}";
            // Clean up typical garbage
            if (strlen($cnaeLabel) > 100) {
                $cnaeLabel = substr($cnaeLabel, 0, 100) . '...';
            }
            $cache->save($labelKey, $cnaeLabel, 1296000); // 15 days
        }

        helper('text');
        $correctSlug = url_title($cnaeLabel, '-', true);

        // Redirect if slug is missing or incorrect
        if ($slug !== $correctSlug) {
            $redirectUrl = "directorio/cnae/{$cnaeCode}/{$correctSlug}";
            if ($page > 1) {
                $redirectUrl .= "/{$page}";
            }
            return redirect()->to(site_url($redirectUrl), 301);
        }

        $countKey = 'cnae_total_v5_' . $cnaeCode;
        $totalCompanies = $cache->get($countKey);
        
        if ($totalCompanies === null) {
            $totalCompanies = (int) $this->companyModel->builder()
                ->where('cnae_code', $cnaeCode)
                ->countAllResults();
            $cache->save($countKey, $totalCompanies, 1296000); // 15 días
        }

        $totalPages = max(1, (int) ceil($totalCompanies / $perPage));

        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, fecha_constitucion as founded, registro_mercantil as province')
            ->where('cnae_code', $cnaeCode)
            ->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to(site_url("directorio/cnae/{$cnaeCode}/{$correctSlug}"));
             }
             return redirect()->to(site_url('directorio'));
        }

        // Cross-pollination: Provinces for this CNAE
        $cache = \Config\Services::cache();
        $crossKey = 'cross_prov_v5_' . $cnaeCode;
        $topProvinces = $cache->get($crossKey);

        if (!$topProvinces) {
            $invalidNames = [
                '', ' ', '  ', '-', '.', '..', '...', '8', 'N/A', 'NULL', 'UNDEFINED', 
                '00 DESCONOCIDA', 'desconocido', 'desconocida', 'no disponible', 'n/a', 'unknown', 'sin especificar',
                'ÍNDICE ALFABÉTICO DE SOCIEDADES', 'No Detectado'
            ];
            $topProvincesData = $this->companyModel->builder()
                ->select('registro_mercantil as name, COUNT(id) as total')
                ->where('cnae_code', $cnaeCode)
                ->where('registro_mercantil IS NOT NULL')
                ->where('registro_mercantil >=', 'A')
                ->whereNotIn('registro_mercantil', $invalidNames)
                ->groupBy('registro_mercantil')
                ->orderBy('total', 'DESC')
                ->get()
                ->getResultArray();
                
            $topProvinces = $topProvincesData;
            usort($topProvinces, fn($a, $b) => $b['total'] <=> $a['total']);
            $topProvinces = array_slice($topProvinces, 0, 12);
            $cache->save($crossKey, $topProvinces, 1296000); // 15 días
        }

        $totalFormatted = number_format($totalCompanies, 0, ',', '.');
        helper('pricing');
        $priceData = calculate_directory_price($totalCompanies);
        $dynamicPrice = $priceData['base_price'];

        return view('directory/list', [
            'items'     => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $dynamicPrice,
            'province_name'   => $cnaeLabel, // reusing this variable for the excel download label
            'cnae_code'       => $cnaeCode,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'title'     => "{$totalFormatted} Empresas de {$cnaeLabel} hoy | Leads listos para contactar",
            'excerptText' => "Accede al listado completo de {$totalFormatted} empresas del sector {$cnaeLabel} hoy. Detectadas en tiempo real y listas para contactar antes que otros proveedores.",
            'header'    => "Empresas en el sector: {$cnaeLabel}",
            'meta_description' => "Accede al listado de {$totalFormatted} empresas del sector {$cnaeLabel} hoy. Detectadas en tiempo real y listas para contactar antes que otros proveedores.",
            'cross_links' => [
                'type' => 'province',
                'title' => "Ver {$cnaeLabel} por provincias",
                'items' => $topProvinces,
                'cnae' => $cnaeCode
            ],
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("directorio/cnae/{$cnaeCode}/{$correctSlug}/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("directorio/cnae/{$cnaeCode}/{$correctSlug}/" . ($page - 1)) : null,
                'base'    => site_url("directorio/cnae/{$cnaeCode}/{$correctSlug}")
            ]
        ]);
    }

    public function latest($page = 1)
    {
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $countKey = 'latest_total_companies_v4_30days';
        $cache = \Config\Services::cache();
        $totalCompanies = $cache->get($countKey);
        
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        if ($totalCompanies === null) {
            $totalCompanies = $this->companyModel->builder()
                ->where('fecha_constitucion IS NOT NULL')
                ->where('fecha_constitucion <=', date('Y-m-d'))
                ->where('fecha_constitucion >=', $thirtyDaysAgo)
                ->countAllResults();
            $cache->save($countKey, $totalCompanies, 86400); // 1 día
        }
        $totalPages = max(1, (int) ceil($totalCompanies / $perPage));

        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, fecha_constitucion as founded, registro_mercantil as province')
            ->where('fecha_constitucion IS NOT NULL')
            ->where('fecha_constitucion <=', date('Y-m-d'))
            ->where('fecha_constitucion >=', $thirtyDaysAgo)
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies) && $page > 1) {
            return redirect()->to(site_url("directorio/ultimas-empresas-registradas"));
        }

        helper('pricing');
        $totalFormatted = number_format($totalCompanies, 0, ',', '.');
        $priceData = calculate_radar_price($totalCompanies);
        $dynamicPrice = $priceData['base_price'];

        return view('directory/list', [
            'items'     => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $dynamicPrice,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'paywall_level' => 'soft',
            'title'     => "Últimas empresas registradas en España (Últimos 30 días)",
            'excerptText' => "Listado cronológico de las nuevas sociedades constituidas en todo el país durante los últimos 30 días.",
            'header'    => "Últimas empresas registradas en los últimos 30 días",
            'meta_description' => "Consulta las nuevas sociedades registradas en España en los últimos 30 días y accede a información básica de cada empresa.",
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("directorio/ultimas-empresas-registradas/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("directorio/ultimas-empresas-registradas/" . ($page - 1)) : null,
                'base'    => site_url("directorio/ultimas-empresas-registradas")
            ]
        ]);
    }

    public function provinceCnae($provinceName, $cnaeCode, $page = 1)
    {
        $provinceName = urldecode($provinceName);
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, fecha_constitucion as founded, registro_mercantil as province')
            ->where('registro_mercantil', $provinceName)
            ->where('cnae_code', $cnaeCode)
            ->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to(site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}"));
             }
             return redirect()->to(site_url("directorio/provincia/" . urlencode($provinceName)));
        }

        $cnaeLabel = $companies[0]['cnae_label'] ?? "CNAE {$cnaeCode}";

        return view('directory/list', [
            'items'     => $companies,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'title'     => "Empresas de {$cnaeLabel} en {$provinceName} hoy | +50 oportunidades",
            'excerptText' => "Descubre empresas de {$cnaeLabel} en {$provinceName} detectadas hoy. Oportunidades reales listas para contactar antes que tu competencia.",
            'header'    => "{$cnaeLabel} en {$provinceName}",
            'meta_description' => "Descubre empresas de {$cnaeLabel} en {$provinceName} detectadas hoy. Oportunidades reales listas para contactar antes que tu competencia.",
            'pagination' => [
                'current' => $page,
                'next'    => site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}/" . ($page + 1)),
                'prev'    => ($page > 1) ? site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}/" . ($page - 1)) : null,
                'base'    => site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}")
            ]
        ]);
    }
}
