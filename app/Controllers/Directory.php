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
        $cacheKey = 'directory_index_data';
        
        $data = $cache->get($cacheKey);
        
        if (!$data) {
            // Obtener lista de provincias únicas con conteo simple (original)
            $provinces = $this->companyModel->builder()
                ->select('registro_mercantil as name, COUNT(id) as total')
                ->where('registro_mercantil IS NOT NULL')
                ->groupBy('registro_mercantil')
                ->orderBy('registro_mercantil', 'ASC')
                ->get()
                ->getResultArray();

            $cnaes = $this->companyModel->builder()
                ->select('cnae_code as cnae, cnae_label as name, COUNT(id) as total')
                ->where('cnae_code IS NOT NULL')
                ->groupBy('cnae_code, cnae_label')
                ->orderBy('total', 'DESC')
                ->limit(24)
                ->get()
                ->getResultArray();

            // Últimas 10 empresas para la home del directorio (excluyendo fechas futuras erróneas)
            $latest = $this->companyModel->builder()
                ->select('id, cif, company_name as name, fecha_constitucion as date, registro_mercantil as province')
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

        return view('directory/index', [
            'provinces' => $data['provinces'],
            'cnaes'     => $data['cnaes'],
            'latest'    => $data['latest'] ?? [],
            'title'     => 'Directorio de empresas en España | APIEmpresas.es',
            'meta_description' => 'Explore el directorio completo de empresas españolas agrupadas por provincia y sector de actividad (CNAE).'
        ]);
    }

    public function province($provinceName, $page = 1)
    {
        $provinceName = urldecode($provinceName);
        
        // Pagination
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $perPage = 100; // Increased to 100 as requested
        $offset = ($page - 1) * $perPage;

        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, registro_mercantil as province')
            ->where('registro_mercantil', $provinceName)
            ->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // Count total for pagination verification (optional, but good for "Not Found" on empty pages)
        // For performance on 3M rows, maybe we skip count or cache it? 
        // Let's do a quick check if empty
        if (empty($companies)) {
             // If page > 1 and no results, redirect to page 1 or show 404? 
             // Redirecting to directory index seems too aggressive if it's just an empty high page.
             // But for now keeping behavior consistent with original code (redirect to directory)
             if ($page > 1) {
                 return redirect()->to(site_url("directorio/provincia/{$provinceName}"));
             }
             return redirect()->to(site_url('directorio'));
        }

        // Cross-pollination: Top CNAEs in this province
        $cache = \Config\Services::cache();
        $crossKey = 'cross_cnae_' . urlencode($provinceName);
        $topCnaes = $cache->get($crossKey);
        
        if (!$topCnaes) {
            $topCnaes = $this->companyModel->builder()
                ->select('cnae_code as code, cnae_label as label, COUNT(id) as total')
                ->where('registro_mercantil', $provinceName)
                ->groupBy('cnae_code, cnae_label')
                ->orderBy('total', 'DESC')
                ->limit(12)
                ->get()
                ->getResultArray();
            $cache->save($crossKey, $topCnaes, 1296000); // 15 días
        }

        return view('directory/list', [
            'items'     => $companies,
            'title'     => "Empresas en {$provinceName} | Página {$page} | APIEmpresas.es",
            'header'    => "Directorio de empresas en {$provinceName}",
            'meta_description' => "Listado de las principales empresas situadas en {$provinceName} (Página {$page}). Consulte CIF, razón social y datos registrales.",
            'cross_links' => [
                'type' => 'cnae',
                'title' => "Principales sectores en {$provinceName}",
                'items' => $topCnaes,
                'province' => $provinceName
            ],
            'pager'     => [
                'current' => $page,
                'next'    => site_url("directorio/provincia/" . urlencode($provinceName) . "/" . ($page + 1)),
                'prev'    => ($page > 1) ? site_url("directorio/provincia/" . urlencode($provinceName) . "/" . ($page - 1)) : null,
                'base'    => site_url("directorio/provincia/" . urlencode($provinceName))
            ]
        ]);
    }

    public function cnae($cnaeCode, $page = 1)
    {
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, registro_mercantil as province')
            ->where('cnae_code', $cnaeCode)
            ->orderBy('company_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
             if ($page > 1) {
                 return redirect()->to(site_url("directorio/cnae/{$cnaeCode}"));
             }
             return redirect()->to(site_url('directorio'));
        }

        $cnaeLabel = $companies[0]['cnae_label'] ?? "CNAE {$cnaeCode}";

        // Cross-pollination: Provinces for this CNAE
        $cache = \Config\Services::cache();
        $crossKey = 'cross_prov_' . $cnaeCode;
        $topProvinces = $cache->get($crossKey);

        if (!$topProvinces) {
            $topProvinces = $this->companyModel->builder()
                ->select('registro_mercantil as name, COUNT(id) as total')
                ->where('cnae_code', $cnaeCode)
                ->where('registro_mercantil IS NOT NULL')
                ->groupBy('registro_mercantil')
                ->orderBy('total', 'DESC')
                ->limit(12)
                ->get()
                ->getResultArray();
            $cache->save($crossKey, $topProvinces, 1296000); // 15 días
        }

        return view('directory/list', [
            'items'     => $companies,
            'title'     => "Empresas de {$cnaeLabel} | Página {$page} | APIEmpresas.es",
            'header'    => "Empresas en el sector: {$cnaeLabel}",
            'meta_description' => "Listado de empresas dedicadas a {$cnaeLabel} (Página {$page}). Consulte información comercial y validación de CIF.",
            'cross_links' => [
                'type' => 'province',
                'title' => "Ver {$cnaeLabel} por provincias",
                'items' => $topProvinces,
                'cnae' => $cnaeCode
            ],
            'pager'     => [
                'current' => $page,
                'next'    => site_url("directorio/cnae/{$cnaeCode}/" . ($page + 1)),
                'prev'    => ($page > 1) ? site_url("directorio/cnae/{$cnaeCode}/" . ($page - 1)) : null,
                'base'    => site_url("directorio/cnae/{$cnaeCode}")
            ]
        ]);
    }

    public function latest($page = 1)
    {
        $page = (int)$page;
        if ($page < 1) $page = 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        // Filtramos por fecha <= hoy para evitar errores de fechas futuras en el dataset
        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, registro_mercantil as province, fecha_constitucion')
            ->where('fecha_constitucion IS NOT NULL')
            ->where('fecha_constitucion <=', date('Y-m-d'))
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies) && $page > 1) {
            return redirect()->to(site_url("directorio/ultimas-empresas-registradas"));
        }

        return view('directory/list', [
            'items'     => $companies,
            'title'     => "Últimas empresas registradas en España | Página {$page} | APIEmpresas.es",
            'header'    => "Últimas Empresas Registradas",
            'meta_description' => "Consulte el listado de las sociedades de reciente creación en España. Datos actualizados del BORME y Registro Mercantil.",
            'pager'     => [
                'current' => $page,
                'next'    => site_url("directorio/ultimas-empresas-registradas/" . ($page + 1)),
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
            ->select('id, cif, company_name as name, cnae_label, registro_mercantil as province')
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
            'title'     => "Empresas de {$cnaeLabel} en {$provinceName} | Página {$page}",
            'header'    => "{$cnaeLabel} en {$provinceName}",
            'meta_description' => "Listado de empresas en el sector {$cnaeLabel} ubicadas en {$provinceName} (Página {$page}). Datos de contacto y CIF.",
            'pager'     => [
                'current' => $page,
                'next'    => site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}/" . ($page + 1)),
                'prev'    => ($page > 1) ? site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}/" . ($page - 1)) : null,
                'base'    => site_url("directorio/provincia/" . urlencode($provinceName) . "/cnae/{$cnaeCode}")
            ]
        ]);
    }
}
