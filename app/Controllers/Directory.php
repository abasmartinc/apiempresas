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
            // Obtener lista de provincias únicas con conteo simple
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
            
            $data = [
                'provinces' => $provinces,
                'cnaes'     => $cnaes,
            ];
            
            // Cache por 24 horas
            $cache->save($cacheKey, $data, 86400);
        }

        return view('directory/index', [
            'provinces' => $data['provinces'],
            'cnaes'     => $data['cnaes'],
            'title'     => 'Directorio de empresas en España | APIEmpresas.es',
            'meta_description' => 'Explore el directorio completo de empresas españolas agrupadas por provincia y sector de actividad (CNAE).'
        ]);
    }

    public function province($provinceName)
    {
        $provinceName = urldecode($provinceName);
        
        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, registro_mercantil as province')
            ->where('registro_mercantil', $provinceName)
            ->orderBy('company_name', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
            return redirect()->to(site_url('directorio'));
        }

        return view('directory/list', [
            'items'     => $companies,
            'title'     => "Empresas en {$provinceName} | APIEmpresas.es",
            'header'    => "Directorio de empresas en {$provinceName}",
            'meta_description' => "Listado de las principales empresas situadas en {$provinceName}. Consulte CIF, razón social y datos registrales."
        ]);
    }

    public function cnae($cnaeCode)
    {
        $companies = $this->companyModel->builder()
            ->select('id, cif, company_name as name, cnae_label, registro_mercantil as province')
            ->where('cnae_code', $cnaeCode)
            ->orderBy('company_name', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
            return redirect()->to(site_url('directorio'));
        }

        $cnaeLabel = $companies[0]['cnae_label'] ?? "CNAE {$cnaeCode}";

        return view('directory/list', [
            'items'     => $companies,
            'title'     => "Empresas de {$cnaeLabel} | APIEmpresas.es",
            'header'    => "Empresas en el sector: {$cnaeLabel}",
            'meta_description' => "Listado de empresas dedicadas a {$cnaeLabel}. Consulte información comercial y validación de CIF."
        ]);
    }
}
