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
        // Recuentos en App\Libraries\IndiceDirectorio (los recalcula cada noche
        // `php spark directorio:calentar`; si no corre, se calculan aquí como antes).
        $data = \App\Libraries\IndiceDirectorio::datos();
        $data['latest'] = \App\Libraries\IndiceDirectorio::ultimas();

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

        // Una sola URL por provincia (03-10-2026): tras normalizar la base de datos,
        // /Bizkaia, /Illes Balears o /MADRID mostraban la misma página que /Vizcaya,
        // /Islas Baleares o /Madrid, cada una indexable. Se redirigen a la canónica.
        $canonica = \App\Libraries\Provincias::canonica($provinceName);
        if ($canonica !== null && $canonica !== $provinceName) {
            return redirect()->to(site_url('listado-de-empresas/' . urlencode($canonica) . ($page > 1 ? '/' . $page : '')), 301);
        }
        
        // Pagination
        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $baseUrl = site_url("listado-de-empresas/" . urlencode($provinceName));
        if ($page > self::MAX_PAGINAS) {
            return redirect()->to($baseUrl, 301);
        }

        // Mismo filtro de provincia que el recuento, el precio y la descarga
        $builder = $this->companyModel->builder()
            ->select('id, cif, company_name as name, registro_mercantil as province, cnae_label, fecha_constitucion as founded');
        \App\Services\BillingService::filtrarProvincia($builder, $provinceName);

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
        $countKey = 'prov_total_v6_' . md5($provinceName);
        $totalCompanies = $cache->get($countKey);
        if ($totalCompanies === null) {
            $countBuilder = $this->companyModel->builder();
            \App\Services\BillingService::filtrarProvincia($countBuilder, $provinceName);
            $totalCompanies = (int) $countBuilder->countAllResults();
            $cache->save($countKey, $totalCompanies, 1296000); // 15 días
        }

        // Cross-pollination: Top CNAEs in this province
        $crossKey = 'cross_cnae_v6_' . md5($provinceName);
        $topCnaes = $cache->get($crossKey);
        
        if (!$topCnaes) {
            $invalidNames = [
                '', ' ', '  ', '-', '.', '..', '...', '8', 'N/A', 'NULL', 'UNDEFINED', 
                '00 DESCONOCIDA', 'desconocido', 'desconocida', 'no disponible', 'n/a', 'unknown', 'sin especificar',
                'ÍNDICE ALFABÉTICO DE SOCIEDADES', 'No Detectado'
            ];
            $cnaeBuilder = $this->companyModel->builder()
                ->select('cnae_code as code, cnae_label as label, COUNT(id) as total');
            \App\Services\BillingService::filtrarProvincia($cnaeBuilder, $provinceName);

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
        $topCnaes = $this->sectoresCanonicos($topCnaes);

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
            'h1_sufijo'       => "en {$provinceName}",
            'robots'          => ($page > 1) ? 'noindex, follow' : 'index, follow',
            // Cada página es su propia canónica: con noindex y canónica a la 1 a la vez
            // Google recibía dos señales que se contradicen.
            'canonical'       => $page > 1 ? "{$baseUrl}/{$page}" : $baseUrl,
            'title'           => "{$totalFormatted} Empresas en {$provinceName} | Listado" . ($page > 1 ? " · Página {$page}" : ''),
            'excerptText'     => "Consulta el listado de {$totalFormatted} empresas registradas en {$provinceName}, con los datos publicados en el BORME.",
            'header'          => "Listado de empresas en {$provinceName}",
            'meta_description'=> "Listado de {$totalFormatted} empresas en {$provinceName}. Busca por nombre, consulta CIF y accede a la ficha de cada sociedad.",
            'cross_links' => [
                'type'     => 'cnae',
                'title'    => "Principales sectores en {$provinceName}",
                'items'    => $topCnaes,
                'province' => $provinceName
            ],
            'pagination' => $this->paginacion($baseUrl, $page, $totalCompanies, $perPage),
        ]);
    }

    public function cnae(...$args)
    {
        if (empty($args)) {
            return redirect()->to(site_url('listado-de-empresas'));
        }

        $cnaeCode = (string) $args[0];
        if (!preg_match('/^[0-9][0-9.]*$/', $cnaeCode)) { // "6201" (o "41.20" hasta normalizar)
            return redirect()->to(site_url('listado-de-empresas'));
        }
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

        // Nombre del sector: el mismo que el índice y sus enlaces (App\Libraries\Sectores).
        // Antes era el nombre más repetido entre las empresas, el slug no coincidía y
        // 7 de cada 40 enlaces del índice pasaban por un 301. Las empresas solo deciden
        // el nombre si el código no está en ninguna de las dos CNAE.
        $cache = \Config\Services::cache();
        $cnaeLabel = \App\Libraries\Sectores::nombre($cnaeCode);
        $labelKey = 'cnae_label_v5_' . $cnaeCode;
        if ($cnaeLabel === null) {
            $cnaeLabel = $cache->get($labelKey);
        }
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

        $correctSlug = \App\Libraries\Sectores::slug($cnaeCode, $cnaeLabel);
        $baseUrl = site_url("listado-de-empresas/sector-{$cnaeCode}/{$correctSlug}");

        // Redirect if slug is missing or incorrect
        if ($page > self::MAX_PAGINAS) {
            return redirect()->to($baseUrl, 301);
        }
        // Comparación tolerante (codificación y forma Unicode de las tildes) y nunca un 301
        // a la misma URL: en producción había sectores en bucle de redirecciones
        // (ERR_TOO_MANY_REDIRECTS), p. ej. sector-4322 con el slug del nombre de empresa.
        if ($slug !== $correctSlug && self::slugIgual($slug, $correctSlug)) {
            $slug = $correctSlug;
        }
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

        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, fecha_constitucion as founded, registro_mercantil as province')
            ->where('cnae_code', $cnaeCode)
            ->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to($baseUrl);
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
            'h1_sufijo'       => "del sector {$cnaeLabel}",
            'cnae_code'       => $cnaeCode,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'canonical' => $page > 1 ? "{$baseUrl}/{$page}" : $baseUrl,
            'title'     => "{$totalFormatted} Empresas de {$cnaeLabel} | Listado por sector" . ($page > 1 ? " · Página {$page}" : ''),
            'excerptText' => "Listado de {$totalFormatted} empresas del sector {$cnaeLabel} en España, con CIF, provincia y fecha de constitución.",
            'header'    => "Empresas en el sector: {$cnaeLabel}",
            'meta_description' => "Listado de {$totalFormatted} empresas del sector {$cnaeLabel} en España. Consulta el CIF, la provincia y la ficha de cada sociedad.",
            'cross_links' => [
                'type' => 'province',
                'title' => "Ver {$cnaeLabel} por provincias",
                'items' => $topProvinces,
                'cnae' => $cnaeCode
            ],
            'pagination' => $this->paginacion($baseUrl, $page, $totalCompanies, $perPage),
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

        $canonica = \App\Libraries\Provincias::canonica($provinceName);
        if ($canonica !== null && $canonica !== $provinceName) {
            return redirect()->to(site_url('listado-de-empresas/' . urlencode($canonica) . "/sector-{$cnaeCode}" . ($page > 1 ? '/' . $page : '')), 301);
        }

        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;
        $baseUrl = site_url("listado-de-empresas/" . urlencode($provinceName) . "/sector-{$cnaeCode}");
        if ($page > self::MAX_PAGINAS) {
            return redirect()->to($baseUrl, 301);
        }

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

        $cnaeLabel = \App\Libraries\Sectores::nombre($cnaeCode) ?? ($companies[0]['cnae_label'] ?: "CNAE {$cnaeCode}");

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
            'h1_sufijo'        => "del sector {$cnaeLabel} en {$provinceName}",
            'cnae_code'        => $cnaeCode,
            'provincia_compra' => $provinceName,
            'sector_compra'    => $cnaeLabel,
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'canonical' => $page > 1 ? "{$baseUrl}/{$page}" : $baseUrl,
            'title'     => "{$totalFormatted} empresas de {$cnaeLabel} en {$provinceName} | Listado" . ($page > 1 ? " · Página {$page}" : ''),
            'excerptText' => "Listado de {$totalFormatted} empresas de {$cnaeLabel} en {$provinceName}, con los datos publicados en el BORME.",
            'header'    => "{$cnaeLabel} en {$provinceName}",
            'meta_description' => "Listado de {$totalFormatted} empresas de {$cnaeLabel} en {$provinceName}. Consulta CIF, fecha de constitución y ficha de cada sociedad.",
            'pagination' => $this->paginacion($baseUrl, $page, $totalCompanies, $perPage),
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
        $baseUrl = site_url("listado-de-empresas/etiqueta/{$tagSlug}");
        if ($page > self::MAX_PAGINAS) {
            return redirect()->to($baseUrl, 301);
        }

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
        $companies = $builder->orderBy('companies.company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to($baseUrl);
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
            'h1_sufijo'       => "con la etiqueta {$titleTag}",
            'robots'    => ($page > 1) ? 'noindex, follow' : 'index, follow',
            'canonical' => $page > 1 ? "{$baseUrl}/{$page}" : $baseUrl,
            'title'     => "{$totalFormatted} Empresas etiquetadas como {$titleTag} | Listado" . ($page > 1 ? " · Página {$page}" : ''),
            'excerptText' => "Descubre nuestro listado de {$totalFormatted} empresas relacionadas con {$titleTag}.",
            'header'    => "Empresas de " . $titleTag,
            'meta_description' => "Accede al listado de {$totalFormatted} empresas con la etiqueta {$titleTag}. Consulta la ficha de cada sociedad.",
            'pagination' => $this->paginacion($baseUrl, $page, $totalCompanies, $perPage),
        ]);
    }

    /** Mismo slug aunque llegue codificado o con las tildes en otra forma Unicode (NFC/NFD) */
    private static function slugIgual(?string $a, string $b): bool
    {
        if ($a === null) {
            return false;
        }
        $norm = static function (string $s): string {
            $s = rawurldecode($s);
            if (class_exists(\Normalizer::class)) {
                $s = \Normalizer::normalize($s, \Normalizer::FORM_C) ?: $s;
            }

            return mb_strtolower($s, 'UTF-8');
        };

        return $norm($a) === $norm($b);
    }

    /**
     * Páginas públicas por listado. Madrid tenía ~8.500 páginas de 100: OFFSET profundo
     * (cada página más lenta que la anterior) y rastreo de páginas que nadie visita.
     * El listado completo es la descarga.
     */
    private const MAX_PAGINAS = 20;

    /**
     * Enlaces de paginación. La página 1 es la URL base, sin "/1" (antes la página 2
     * enlazaba a /Madrid/1, un duplicado de /Madrid).
     */
    private function paginacion(string $base, int $page, int $total, int $perPage): array
    {
        $paginasReales = max(1, (int) ceil($total / $perPage));
        $paginas = min(self::MAX_PAGINAS, $paginasReales);
        $url = static fn (int $p): string => $p <= 1 ? $base : "{$base}/{$p}";

        return [
            'current'  => $page,
            'total'    => $paginas,
            'next'     => $page < $paginas ? $url($page + 1) : null,
            'prev'     => $page > 1 ? $url($page - 1) : null,
            'base'     => $base,
            // Hay más empresas de las que se pueden ver: la última página lo dice
            'truncada' => $paginasReales > $paginas,
            'visibles' => $paginas * $perPage,
        ];
    }

    /**
     * "Principales sectores": nombre canónico (Sectores) y una sola píldora por código.
     * La consulta agrupa por código y nombre de empresa, y un mismo código salía repetido.
     */
    private function sectoresCanonicos(array $filas): array
    {
        $porCodigo = [];
        foreach ($filas as $f) {
            $code = (string) ($f['code'] ?? '');
            if ($code === '') {
                continue;
            }
            if (!isset($porCodigo[$code])) {
                $porCodigo[$code] = [
                    'code'  => $code,
                    'label' => \App\Libraries\Sectores::nombre($code) ?? ($f['label'] ?? ''),
                    'total' => 0,
                ];
            }
            $porCodigo[$code]['total'] += (int) ($f['total'] ?? 0);
        }
        $out = array_values($porCodigo);
        usort($out, static fn ($a, $b) => $b['total'] <=> $a['total']);

        return $out;
    }
}

