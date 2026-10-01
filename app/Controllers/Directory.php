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
        // v6 (01-10-2026): nombres de la CNAE-2025 y sin códigos que no existen. Tras pasar
        // calidad_datos/normalizar_datos.py, las provincias ya son las 52 canónicas.
        $cacheKey = 'directory_index_data_v6';
        
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

            // Nombres de sector: CNAE-2009 y, si el código es de la CNAE-2025 (las altas
            // nuevas), su nombre de 2025. Antes solo se miraba 2009 y salían "CNAE 6812"
            // (164.219 empresas, el tercer sector) y otros 2025 sin nombre.
            $db = \Config\Database::connect();
            $cnaeMap = [];
            foreach ($db->table('cnae_2009_2025')->select('cnae_2009 as cnae, label_2009 as label')->get()->getResultArray() as $row) {
                if ($row['cnae'] !== null && $row['cnae'] !== '' && !empty($row['label'])) {
                    $cnaeMap[(string) $row['cnae']] = $row['label'];
                }
            }
            foreach ($db->table('cnae_2009_2025')->select('cnae_2025 as cnae, label_2025 as label')->get()->getResultArray() as $row) {
                $code = (string) ($row['cnae'] ?? '');
                if ($code !== '' && !empty($row['label']) && !isset($cnaeMap[$code])) {
                    $cnaeMap[$code] = $row['label'];
                }
            }

            // Solo se listan (y se cuentan en "Sectores CNAE") los códigos que existen en
            // alguna de las dos clasificaciones. Los demás eran códigos sueltos y erróneos
            // (6046, 9848…, casi todos con 1 empresa) que inflaban el KPI hasta 1.222.
            $cnaes = array_values(array_filter($cnaes, static fn ($c) => isset($cnaeMap[(string) $c['cnae']])));
            foreach ($cnaes as &$cnae) {
                $cnae['name'] = $cnaeMap[(string) $cnae['cnae']];
            }
            unset($cnae);

            $data = [
                'provinces' => $provinces,
                'cnaes'     => $cnaes,
            ];

            $cache->save($cacheKey, $data, 1296000); // 15 días (recuentos pesados, cambian poco)
        }

        // "Últimas empresas registradas" va aparte con caché de 1 hora. Antes iba en la de
        // 15 días de arriba (el comentario decía 24 h) y la lista se quedaba congelada.
        $latest = $cache->get('directory_latest_v1');
        if (!is_array($latest)) {
            $latest = $this->companyModel->builder()
                ->select('id, cif, company_name as name, fecha_constitucion as founded, cnae_label, registro_mercantil as province')
                ->where('fecha_constitucion IS NOT NULL')
                ->where('fecha_constitucion <=', date('Y-m-d'))
                ->orderBy('fecha_constitucion', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
            $cache->save('directory_latest_v1', $latest, 3600);
        }
        $data['latest'] = $latest;

        // Calcular máximos dinámicos para barras de densidad
        $maxProvince = !empty($data['provinces']) ? max(array_column($data['provinces'], 'total')) : 1;
        $maxCnae     = !empty($data['cnaes'])     ? max(array_column($data['cnaes'],     'total')) : 1;
        $totalAll    = array_sum(array_column($data['provinces'], 'total'));
        $totalFormatted = number_format($totalAll, 0, ',', '.');
        $numProvinces   = count($data['provinces']);

        helper('pricing');
        if (!function_exists('calculate_directory_price')) {
            $helperPath = APPPATH . 'Helpers/pricing_helper.php';
            if (file_exists($helperPath)) require_once $helperPath;
        }
        $priceData = calculate_directory_price($totalAll);
        $dynamicPrice = $priceData['base_price'];

        return view('directory/index', [
            'provinces'        => $data['provinces'],
            'cnaes'            => $data['cnaes'],
            'latest'           => $data['latest'] ?? [],
            'max_province'     => $maxProvince,
            'max_cnae'         => $maxCnae,
            'dynamic_price'    => $dynamicPrice,
            'pricing'          => $priceData,
            'title'            => "Listado de Empresas en España | {$totalFormatted} Sociedades Registradas",
            'meta_description' => "Listado de {$totalFormatted} empresas españolas organizadas por las 52 provincias y por sector CNAE, con los datos publicados en el BORME.",
            'excerptText'      => "Listado de {$totalFormatted} empresas españolas organizadas por las 52 provincias y por sector CNAE, con los datos publicados en el BORME.",
            'canonical'        => site_url('listado-de-empresas'),
        ]);
    }

    /**
     * Nombre real de provincia (registro_mercantil) a partir de un slug o una
     * grafía distinta, o null. Compara sin tildes, sin signos y sin importar el
     * orden de las palabras: "a-coruña" = "A Coruña" = "Coruña (A)",
     * "arabaálava" = "Araba/Álava".
     */
    private function provinciaDesdeSlug(string $texto): ?string
    {
        $buscado = self::claveProvincia($texto);
        if ($buscado === '') {
            return null;
        }

        $cache = \Config\Services::cache();
        $nombres = $cache->get('dir_provincias_nombres_v2');
        if (!is_array($nombres)) {
            $indice = $cache->get('directory_index_data_v6');
            if (is_array($indice) && !empty($indice['provinces'])) {
                $nombres = array_column($indice['provinces'], 'name');
            } else {
                $nombres = array_column($this->companyModel->builder()
                    ->select('registro_mercantil as name')
                    ->where('registro_mercantil >=', 'A')
                    ->groupBy('registro_mercantil')
                    ->get()->getResultArray(), 'name');
            }
            $cache->save('dir_provincias_nombres_v2', $nombres, 1296000); // 15 días
        }

        $sinOrden = self::claveProvincia($texto, true);
        foreach ($nombres as $nombre) {
            $nombre = (string) $nombre;
            if (self::claveProvincia($nombre) === $buscado || self::claveProvincia($nombre, true) === $sinOrden) {
                return $nombre;
            }
        }

        return null;
    }

    /**
     * "A Coruña" → "acoruna"; con $palabras, ordena las palabras: "Coruña (A)" → "a coruna".
     */
    private static function claveProvincia(string $s, bool $palabras = false): string
    {
        $s = mb_strtolower(trim(urldecode($s)), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ñ' => 'n', 'ç' => 'c', 'l·l' => 'll']);
        if (!$palabras) {
            return preg_replace('/[^a-z0-9]/', '', $s) ?? '';
        }
        $w = preg_split('/[^a-z0-9]+/', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        sort($w);

        return implode(' ', $w);
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

        // (Aquí había un log_message('error', 'PROVINCE_DEBUG …') en cada visita.)

        if (empty($companies)) {
             // Enlaces en formato URL (las páginas del Radar enlazan a
             // /listado-de-empresas/a-coruña, arabaálava…): si equivale a una
             // provincia real, 301 a su URL buena en vez de mandar al índice.
             $real = $this->provinciaDesdeSlug($provinceName);
             if ($real !== null && $real !== $provinceName) {
                 $destino = 'listado-de-empresas/' . urlencode($real) . ($page > 1 ? '/' . $page : '');
                 return redirect()->to(site_url($destino), 301);
             }
             if ($page > 1) {
                 return redirect()->to(site_url("listado-de-empresas/{$provinceName}"));
             }
             return redirect()->to(site_url('listado-de-empresas'));
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
        // Precio con el mismo recuento que el pago (provincia con sus alias)
        $buyKey = 'prov_compra_v1_' . md5($provinceName);
        $totalCompra = $cache->get($buyKey);
        if ($totalCompra === null) {
            $totalCompra = (new \App\Services\BillingService())->countDirectoryCompanies(['provincia' => $provinceName]);
            $cache->save($buyKey, $totalCompra, 1296000); // 15 días
        }
        $priceData = calculate_directory_price((int) $totalCompra);
        $dynamicPrice = $priceData['base_price'];

        return view('directory/list', [
            'items'           => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $dynamicPrice,
            'pricing'         => $priceData,
            'province_name'   => $provinceName,
            'robots'          => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'canonical'       => site_url("listado-de-empresas/" . urlencode($provinceName)), // siempre pág 1
            'title'           => "{$totalFormatted} Empresas en {$provinceName} | Listado",
            'excerptText'     => "Consulta el listado de {$totalFormatted} empresas registradas en {$provinceName}, con los datos publicados en el BORME.",
            'header'          => "Listado de empresas en {$provinceName}",
            'meta_description'=> "Listado de {$totalFormatted} empresas en {$provinceName}. Busca por nombre, consulta CIF y accede a la ficha de cada sociedad.",
            'cross_links' => [
                'type'     => 'cnae',
                'title'    => "Principales sectores en {$provinceName}",
                'items'    => $topCnaes,
                'province' => $provinceName
            ],
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("listado-de-empresas/" . urlencode($provinceName) . "/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("listado-de-empresas/" . urlencode($provinceName) . "/" . ($page - 1)) : null,
                'base'    => site_url("listado-de-empresas/" . urlencode($provinceName))
            ]
        ]);
    }

    public function cnae(...$args)
    {
        if (empty($args)) {
            return redirect()->to(site_url('listado-de-empresas'));
        }

        $cnaeCode = $args[0];
        $slug = null;
        $page = 1;

        if (count($args) === 1) {
            // URL: /listado-de-empresas/sector-6920
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
            $redirectUrl = "listado-de-empresas/sector-{$cnaeCode}/{$correctSlug}";
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

        // Recuento de lo que se COMPRA desde esta página: el pago cuenta y exporta
        // `cnae_code LIKE 'X%'` (BillingService::countDirectoryCompanies), y aquí el
        // precio salía del recuento exacto: el botón enseñaba un precio y se cobraba
        // otro. La paginación sigue con el recuento exacto, que es lo que se lista.
        $buyKey = 'cnae_compra_v1_' . $cnaeCode;
        $totalCompra = $cache->get($buyKey);
        if ($totalCompra === null) {
            $totalCompra = (new \App\Services\BillingService())->countDirectoryCompanies(['cnae' => $cnaeCode]);
            $cache->save($buyKey, $totalCompra, 1296000); // 15 días
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
                 return redirect()->to(site_url("listado-de-empresas/sector-{$cnaeCode}/{$correctSlug}"));
             }
             return redirect()->to(site_url('listado-de-empresas'));
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
        $priceData = calculate_directory_price((int) $totalCompra); // mismo recuento que el pago
        $dynamicPrice = $priceData['base_price'];

        return view('directory/list', [
            'items'     => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $dynamicPrice,
            'pricing'         => $priceData,
            'province_name'   => $cnaeLabel, // reusing this variable for the excel download label
            'cnae_code'       => $cnaeCode,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'title'     => "{$totalFormatted} Empresas de {$cnaeLabel} | Listado por sector",
            'excerptText' => "Listado de {$totalFormatted} empresas del sector {$cnaeLabel} en España, con CIF, provincia y fecha de constitución.",
            'header'    => "Empresas en el sector: {$cnaeLabel}",
            'meta_description' => "Listado de {$totalFormatted} empresas del sector {$cnaeLabel} en España. Consulta el CIF, la provincia y la ficha de cada sociedad.",
            'cross_links' => [
                'type' => 'province',
                'title' => "Ver {$cnaeLabel} por provincias",
                'items' => $topProvinces,
                'cnae' => $cnaeCode
            ],
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("listado-de-empresas/sector-{$cnaeCode}/{$correctSlug}/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("listado-de-empresas/sector-{$cnaeCode}/{$correctSlug}/" . ($page - 1)) : null,
                'base'    => site_url("listado-de-empresas/sector-{$cnaeCode}/{$correctSlug}")
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
            return redirect()->to(site_url("empresas-nuevas"));
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
            'pricing'         => $priceData,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'paywall_level' => 'soft',
            'title'     => "Últimas empresas registradas en España (Últimos 30 días)",
            'excerptText' => "Listado cronológico de las nuevas sociedades constituidas en todo el país durante los últimos 30 días.",
            'header'    => "Últimas empresas registradas en los últimos 30 días",
            'meta_description' => "Consulta las nuevas sociedades registradas en España en los últimos 30 días y accede a información básica de cada empresa.",
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("empresas-nuevas/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("empresas-nuevas/" . ($page - 1)) : null,
                'base'    => site_url("empresas-nuevas")
            ]
        ]);
    }

    /**
     * /listado-de-empresas/{provincia}/sector-{cnae}[/{página}]
     *
     * Variádico porque la provincia puede llevar barra (Araba/Álava) y CodeIgniter
     * parte los parámetros por '/': llegan [...provincia, cnae] o
     * [...provincia, cnae, página]. Antes esta ruta no se alcanzaba nunca (la de
     * provincia iba delante y se la tragaba).
     */
    public function provinceCnae(...$args)
    {
        $page = 1;
        $n = count($args);
        if ($n >= 3 && ctype_digit((string) $args[$n - 1]) && ctype_digit((string) $args[$n - 2])) {
            $page = (int) array_pop($args);
        }
        $cnaeCode = (string) array_pop($args);
        $provinceName = urldecode(implode('/', $args));

        if ($provinceName === '' || !ctype_digit($cnaeCode)) {
            return redirect()->to(site_url('listado-de-empresas'));
        }

        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;
        $baseUrl = site_url("listado-de-empresas/" . urlencode($provinceName) . "/sector-{$cnaeCode}");

        $builder = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, fecha_constitucion as founded, registro_mercantil as province')
            ->where('cnae_code', $cnaeCode);
        \App\Services\BillingService::filtrarProvincia($builder, $provinceName);
        $companies = $builder->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to($baseUrl);
             }
             return redirect()->to(site_url("listado-de-empresas/" . urlencode($provinceName)));
        }

        $cnaeLabel = $companies[0]['cnae_label'] ?? "CNAE {$cnaeCode}";

        // Recuento para la paginación (antes "siguiente" no acababa nunca) y el de
        // lo que se compra desde aquí: ese CNAE en esa provincia, igual que el pago.
        $cache = \Config\Services::cache();
        $countKey = 'prov_cnae_total_v1_' . md5($provinceName . '|' . $cnaeCode);
        $totalCompanies = $cache->get($countKey);
        if ($totalCompanies === null) {
            $countBuilder = $this->companyModel->builder()->where('cnae_code', $cnaeCode);
            \App\Services\BillingService::filtrarProvincia($countBuilder, $provinceName);
            $totalCompanies = (int) $countBuilder->countAllResults();
            $cache->save($countKey, $totalCompanies, 1296000); // 15 días
        }
        $buyKey = 'prov_cnae_compra_v1_' . md5($provinceName . '|' . $cnaeCode);
        $totalCompra = $cache->get($buyKey);
        if ($totalCompra === null) {
            $totalCompra = (new \App\Services\BillingService())->countDirectoryCompanies(['provincia' => $provinceName, 'cnae' => $cnaeCode]);
            $cache->save($buyKey, $totalCompra, 1296000);
        }
        $totalPages = max(1, (int) ceil($totalCompanies / $perPage));

        helper('pricing');
        $priceData = calculate_directory_price((int) $totalCompra);
        $totalFormatted = number_format($totalCompanies, 0, ',', '.');

        return view('directory/list', [
            'items'     => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $priceData['base_price'],
            'pricing'         => $priceData,
            // Botón de compra: ese CNAE en esa provincia
            'province_name'    => "{$cnaeLabel} en {$provinceName}",
            'cnae_code'        => $cnaeCode,
            'provincia_compra' => $provinceName,
            'sector_compra'    => $cnaeLabel,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'canonical' => $baseUrl,
            'title'     => "{$totalFormatted} empresas de {$cnaeLabel} en {$provinceName} | Listado",
            'excerptText' => "Listado de {$totalFormatted} empresas de {$cnaeLabel} en {$provinceName}, con los datos publicados en el BORME.",
            'header'    => "{$cnaeLabel} en {$provinceName}",
            'meta_description' => "Listado de {$totalFormatted} empresas de {$cnaeLabel} en {$provinceName}. Consulta CIF, fecha de constitución y ficha de cada sociedad.",
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? $baseUrl . '/' . ($page + 1) : null,
                'prev'    => ($page > 1) ? $baseUrl . '/' . ($page - 1) : null,
                'base'    => $baseUrl
            ]
        ]);
    }

    public function tag($tagSlug, $page = 1)
    {
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        // Limpiar el slug para la búsqueda y mostrarlo
        $tagName = str_replace('-', ' ', $tagSlug);

        // Usamos un builder nativo conectando el modelo base a la tabla de enrichment
        $builder = $this->companyModel->builder('companies')
            ->select('companies.id, companies.cif, companies.company_name as name, companies.cnae_label, companies.fecha_constitucion as founded, companies.registro_mercantil as province, company_enrichment.ai_tags')
            ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'inner')
            ->like('company_enrichment.ai_tags', $tagName, 'both');

        // Recuento en caché: es un LIKE sobre company_enrichment y la página la
        // enlazan todas las fichas de empresa (ahora que la ruta funciona, la
        // visitarán los rastreadores).
        $cache = \Config\Services::cache();
        $countKey = 'tag_total_v1_' . md5($tagName);
        $totalCompanies = $cache->get($countKey);
        if ($totalCompanies === null) {
            $countBuilder = clone $builder;
            $totalCompanies = (int) $countBuilder->countAllResults(false);
            $cache->save($countKey, $totalCompanies, 1296000); // 15 días
        }
        $totalPages = max(1, (int) ceil($totalCompanies / $perPage));

        $companies = $builder->orderBy('companies.company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to(site_url("listado-de-empresas/etiqueta/{$tagSlug}"));
             }
             return redirect()->to(site_url('listado-de-empresas'));
        }

        $totalFormatted = number_format($totalCompanies, 0, ',', '.');
        helper('pricing');
        $priceData = calculate_directory_price($totalCompanies);
        $dynamicPrice = $priceData['base_price'];

        $titleTag = ucwords($tagName);

        return view('directory/list', [
            'items'     => $companies,
            'total_companies' => $totalCompanies,
            'total_formatted' => $totalFormatted,
            'dynamic_price'   => $dynamicPrice,
            'pricing'         => $priceData,
            'province_name'   => $titleTag,
            // Sin botón de compra: no hay descarga por etiqueta. Antes el botón
            // mandaba la etiqueta como provincia (0 empresas).
            'sin_compra'      => true,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'title'     => "{$totalFormatted} Empresas etiquetadas como {$titleTag} | Listado",
            'excerptText' => "Descubre nuestro listado de {$totalFormatted} empresas relacionadas con {$titleTag}.",
            'header'    => "Empresas de " . $titleTag,
            'meta_description' => "Accede al listado de {$totalFormatted} empresas con la etiqueta {$titleTag}. Consulta la ficha de cada sociedad.",
            'pagination' => [
                'current' => $page,
                'total'   => $totalPages,
                'next'    => ($page < $totalPages) ? site_url("listado-de-empresas/etiqueta/{$tagSlug}/" . ($page + 1)) : null,
                'prev'    => ($page > 1) ? site_url("listado-de-empresas/etiqueta/{$tagSlug}/" . ($page - 1)) : null,
                'base'    => site_url("listado-de-empresas/etiqueta/{$tagSlug}")
            ]
        ]);
    }
}

