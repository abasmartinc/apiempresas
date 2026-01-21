<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    protected $perPage = 1000;

    /**
     * Índice del sitemap (sitemap.xml)
     */
    public function index()
    {
        $model = new CompanyModel();
        
        $total = $model->builder()->countAllResults();
        $pages = ceil($total / $this->perPage);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Sitemap Estático
        $xml .= '<sitemap>';
        $xml .= '<loc>' . site_url("sitemap-static.xml") . '</loc>';
        $xml .= '<lastmod>' . date('c') . '</lastmod>';
        $xml .= '</sitemap>';

        // 2. Sitemap del Blog (WP)
        $xml .= '<sitemap>';
        $xml .= '<loc>' . site_url("sitemap-blog.xml") . '</loc>';
        $xml .= '<lastmod>' . date('c') . '</lastmod>';
        $xml .= '</sitemap>';

        // 3. Páginas de empresas
        for ($i = 1; $i <= $pages; $i++) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . site_url("sitemap-companies-{$i}.xml") . '</loc>';
            $xml .= '<lastmod>' . date('c') . '</lastmod>';
            $xml .= '</sitemap>';
        }
        
        $xml .= '</sitemapindex>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap de páginas estáticas
     */
    public function static()
    {
        $urls = [
            ['loc' => site_url('/'), 'priority' => '1.0', 'freq' => 'daily'],
            ['loc' => site_url('prices'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['loc' => site_url('contact'), 'priority' => '0.5', 'freq' => 'monthly'],
            ['loc' => site_url('documentation'), 'priority' => '0.9', 'freq' => 'weekly'],
            ['loc' => site_url('search_company'), 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => site_url('blog'), 'priority' => '0.8', 'freq' => 'daily'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . $url['loc'] . '</loc>';
            $xml .= '<lastmod>' . date('c') . '</lastmod>';
            $xml .= '<changefreq>' . $url['freq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap dinámico del blog (Fetch desde WP)
     */
    public function blog()
    {
        $siteUrl    = 'https://blog.apiempresas.es';
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&per_page=100&fields=slug,date';
        $requestUrl = $siteUrl . $endpoint;

        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'APIEmpresasSitemapBot/1.0',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $posts = json_decode($response, true) ?: [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('blog/' . ($post['slug'] ?? '')) . '</loc>';
            $xml .= '<lastmod>' . date('c', strtotime($post['date'] ?? 'now')) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
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
