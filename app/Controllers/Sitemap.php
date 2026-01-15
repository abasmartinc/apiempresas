<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    protected $perPage = 1000;

    /**
     * Índice del sitemap (sitemap.xml)
     * Lista los sub-sitemaps de empresas.
     */
    public function index()
    {
        $model = new CompanyModel();
        
        // Contar total de empresas para saber cuántas páginas hay
        // Usamos builder para ser más ligeros que el modelo completo si es posible
        $total = $model->builder()->countAllResults();
        $pages = ceil($total / $this->perPage);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Añadir páginas de empresas
        for ($i = 1; $i <= $pages; $i++) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . site_url("sitemap-companies-{$i}.xml") . '</loc>';
            $xml .= '<lastmod>' . date('c') . '</lastmod>';
            $xml .= '</sitemap>';
        }

        // Podríamos añadir aquí otros sitemaps estáticos (home, contacto, etc) si existieran
        
        $xml .= '</sitemapindex>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sub-sitemap de empresas (sitemap-companies-X.xml)
     */
    public function companies($page = 1)
    {
        $page = (int) $page;
        if ($page < 1) $page = 1;

        $model = new CompanyModel();
        helper('text'); // para url_title

        // Calcular offset
        $offset = ($page - 1) * $this->perPage;

        // Obtener lote de empresas
        // Necesitamos ID, CIF y Nombre
        $companies = $model->builder()
            ->select('id, cif, company_name as name') 
            ->orderBy('id', 'ASC') // Orden consistente
            ->limit($this->perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
            return $this->response->setStatusCode(404);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($companies as $company) {
            $cif  = $company['cif'];
            $name = $company['name'];
            $id   = $company['id'];
            $slug = url_title($name, '-', true);
            
            // URL Logic:
            // 1. Si tiene CIF -> /CIF-slug
            // 2. Si NO tiene CIF -> /empresa/ID-slug
            if (!empty($cif)) {
                $loc = site_url($cif . ($slug ? ('-' . $slug) : ''));
            } else {
                $loc = site_url("empresa/{$id}" . ($slug ? ('-' . $slug) : ''));
            }
            
            // Lastmod: al no tener fecha de modificación fiable, usamos el inicio del mes o hoy
            $lastmod = date('c');

            $xml .= '<url>';
            $xml .= '<loc>' . $loc . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }
}
