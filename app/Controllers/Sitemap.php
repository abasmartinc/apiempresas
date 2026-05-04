<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    protected $perPage = 40000;

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
        $xml .= '<sitemap><loc>' . site_url("sitemap-static.xml") . '</loc></sitemap>';

        // 2. Sitemap del Blog
        $xml .= '<sitemap><loc>' . site_url("sitemap-blog.xml") . '</loc></sitemap>';

        // 3. Sitemap de Directorios (Provincias y CNAE)
        $xml .= '<sitemap><loc>' . site_url("sitemap-directories.xml") . '</loc></sitemap>';

        // 4. Sitemap de Informes SEO (Legacy + WordPress Dinámico)
        $xml .= '<sitemap><loc>' . site_url("sitemap-informes-provincias.xml") . '</loc></sitemap>';
        $xml .= '<sitemap><loc>' . site_url("sitemap-informes-sectores.xml") . '</loc></sitemap>';
        $xml .= '<sitemap><loc>' . site_url("sitemap-informes-wp.xml") . '</loc></sitemap>';

        // 5. Páginas de empresas
        for ($i = 1; $i <= $pages; $i++) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . site_url("sitemap-companies-{$i}.xml") . '</loc>';
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
            ['loc' => site_url('leads-empresas-nuevas'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['loc' => site_url('contact'), 'priority' => '0.5', 'freq' => 'monthly'],
            ['loc' => site_url('documentation'), 'priority' => '0.9', 'freq' => 'weekly'],
            ['loc' => site_url('search_company'), 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => site_url('blog'), 'priority' => '0.8', 'freq' => 'daily'],
            ['loc' => site_url('empresas-nuevas'), 'priority' => '1.0', 'freq' => 'daily'],
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
        helper(['text', 'seo_dynamic_helper', 'company']); // para url_title, scoring y urls de empresa

        // Calcular offset
        $offset = ($page - 1) * $this->perPage;

        // Obtener lote de empresas
        // Necesitamos campos extra para el cálculo del score SEO (shouldIndexCompany)
        $companies = $model->builder()
            ->select('id, cif, company_name as name, cnae_code as cnae, registro_mercantil as province, objeto_social as corporate_purpose') 
            ->orderBy('id', 'ASC') // Orden consistente
            ->limit($this->perPage, $offset)
            ->get()
            ->getResultArray();

        if (empty($companies)) {
            return $this->response->setStatusCode(404);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $included = 0;
        $excluded = 0;

        foreach ($companies as $company) {
            // FILTRO SEO: Saltar empresas que no cumplan el mínimo de calidad
            if (!shouldIndexCompany($company)) {
                $excluded++;
                continue;
            }

            $url = company_url($company);
            $score = calculateCompanySeoScore($company);
            $priority = ($score >= 7) ? '0.8' : '0.6';
            
            $xml .= '<url>' . PHP_EOL;
            $xml .= '  <loc>' . esc($url) . '</loc>' . PHP_EOL;
            $xml .= '  <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
            $xml .= '  <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '  <priority>' . $priority . '</priority>' . PHP_EOL;
            $xml .= '</url>' . PHP_EOL;
            
            $included++;
        }

        $xml .= '</urlset>';

        // Log opcional para monitorear el ratio de indexación (puedes comentarlo si no lo necesitas)
        log_message('debug', "Sitemap Companies Page {$page}: Included {$included}, Excluded {$excluded}");

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap de Provincias y Sectores (Directorios)
     */
    public function directories()
    {
        $model = new CompanyModel();
        
        // Provincias (original)
        $provinces = $model->builder()
            ->select('registro_mercantil as name')
            ->where('registro_mercantil IS NOT NULL')
            ->groupBy('registro_mercantil')
            ->get()
            ->getResultArray();

        // CNAEs principales
        $cnaes = $model->builder()
            ->select('cnae_code as code')
            ->where('cnae_code IS NOT NULL')
            ->groupBy('cnae_code')
            ->get()
            ->getResultArray();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home del directorio
        $xml .= '<url><loc>' . site_url('directorio') . '</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>';

        foreach ($provinces as $p) {
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('directorio/provincia/' . urlencode($p['name'])) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq><priority>0.8</priority>';
            $xml .= '</url>';
        }

        foreach ($cnaes as $c) {
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('directorio/cnae/' . $c['code']) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq><priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap de Informes por Provincias (Legacy)
     */
    public function informesProvincias()
    {
        $provinces = [
            'madrid', 'barcelona', 'valencia', 'sevilla', 'alicante', 'malaga', 'murcia', 'cadiz',
            'vizcaya', 'coruna', 'asturias', 'zaragoza', 'pontevedra', 'granada', 'tarragona',
            'cordoba', 'girona', 'almeria', 'toledo', 'badajoz', 'navarra', 'jaen', 'cantabria',
            'castellon', 'huelva', 'valladolid', 'ciudad-real', 'leon', 'lleida', 'caceres',
            'alava', 'lugo', 'salamanca', 'burgos', 'albacete', 'orense', 'rioja', 'guipuzcoa',
            'huesca', 'cuenca', 'zamora', 'palencia', 'avila', 'segovia', 'teruel', 'guadalajara',
            'soria', 'baleares', 'las-palmas', 'tenerife'
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($provinces as $p) {
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('informes/nuevas-empresas-en-' . $p) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap de Informes por Sectores (Legacy)
     */
    public function informesSectores()
    {
        $sectors = [
            'hosteleria', 'programacion', 'marketing', 'construccion', 'transporte', 'transporte-mercancias',
            'inmobiliaria', 'sanidad', 'tecnologia', 'comercio', 'educacion', 'turismo'
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($sectors as $s) {
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('informes/nuevas-empresas-sector-' . $s) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap de Informes Dinámicos (WordPress Cat 20)
     */
    public function informesWp()
    {
        $wpService = new \App\Services\WordPressService();
        $seoService = new \App\Services\SeoTemplateService();
        $templates = $wpService->getTemplatesByCategory(20);
        $blacklist = ['listado', 'actualizado', 'hoy', 'semana', 'analisis'];

        $provinces = ['madrid', 'barcelona', 'valencia', 'sevilla', 'malaga']; // Top
        $sectors   = ['hosteleria', 'construccion', 'tecnologia', 'comercio']; // Top

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($templates as $t) {
            $tplTitle = html_entity_decode($t['title']['rendered'] ?? '', ENT_QUOTES, 'UTF-8');
            
            // FILTRO SANEADO (sin listado-actualizado)
            $hasBlacklist = false;
            foreach ($blacklist as $word) if (stripos($tplTitle, $word) !== false) { $hasBlacklist = true; break; }
            if ($hasBlacklist) continue;

            $tplSlug = $seoService->slugifyWithPlaceholders($tplTitle);
            
            // 1. España (Nacional)
            $finalSlug = str_replace(['{{provincia}}', '{{sector}}'], ['espana', 'general'], $tplSlug);
            $xml .= '<url><loc>' . site_url('informes/' . $finalSlug) . '</loc><lastmod>' . date('c') . '</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>';

            // 2. Por Provincia (Top 5)
            if (strpos($tplTitle, '{{provincia}}') !== false) {
                foreach ($provinces as $p) {
                    $fs = str_replace(['{{provincia}}', '{{sector}}'], [$p, 'general'], $tplSlug);
                    $xml .= '<url><loc>' . site_url('informes/' . $fs) . '</loc><lastmod>' . date('c') . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>';
                }
            }

            // 3. Por Sector (Top 4)
            if (strpos($tplTitle, '{{sector}}') !== false) {
                foreach ($sectors as $s) {
                    $fs = str_replace(['{{provincia}}', '{{sector}}'], ['espana', $s], $tplSlug);
                    $xml .= '<url><loc>' . site_url('informes/' . $fs) . '</loc><lastmod>' . date('c') . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>';
                }
            }
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }
}
