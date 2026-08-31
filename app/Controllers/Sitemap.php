<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    protected $perPage = 10000;

    /**
     * Índice del sitemap (sitemap.xml)
     */
    public function index()
    {
        $pages = 0;
        if (file_exists(WRITEPATH . 'sitemaps/sitemap-companies-count.txt')) {
            $pages = (int) file_get_contents(WRITEPATH . 'sitemaps/sitemap-companies-count.txt');
        } elseif (file_exists(FCPATH . 'sitemap-companies-count.txt')) {
            $pages = (int) file_get_contents(FCPATH . 'sitemap-companies-count.txt');
        } else {
            // Fallback just in case
            $model = new CompanyModel();
            $total = $model->builder()->countAllResults();
            $pages = ceil($total / $this->perPage);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);

        // 1. Sitemap Estático
        $xml .= '<sitemap><loc>' . site_url("sitemap-static.xml") . '</loc></sitemap>';

        if (!$isEn) {
            // 2. Sitemap del Blog
            $xml .= '<sitemap><loc>' . site_url("sitemap-blog.xml") . '</loc></sitemap>';

            // 3. Sitemap de Directorios (Provincias y CNAE)
            $xml .= '<sitemap><loc>' . site_url("sitemap-directories.xml") . '</loc></sitemap>';

            // 4. Sitemap de Informes SEO (Legacy + WordPress Dinámico)
            $xml .= '<sitemap><loc>' . site_url("sitemap-informes-provincias.xml") . '</loc></sitemap>';
            $xml .= '<sitemap><loc>' . site_url("sitemap-informes-sectores.xml") . '</loc></sitemap>';
            $xml .= '<sitemap><loc>' . site_url("sitemap-informes-wp.xml") . '</loc></sitemap>';

            // 5. Sitemap de Subvenciones y Contratos Públicos
            $xml .= '<sitemap><loc>' . site_url("sitemap-subvenciones.xml") . '</loc></sitemap>';
            $xml .= '<sitemap><loc>' . site_url("sitemap-contratos.xml") . '</loc></sitemap>';
        }

        $prefix = $isEn ? 'sitemap-en-companies-' : 'sitemap-companies-';

        // 5. Páginas de empresas
        for ($i = 1; $i <= $pages; $i++) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . site_url("{$prefix}{$i}.xml") . '</loc>';
            
            // Añadimos el lastmod basado en la fecha de creación del sitemap estático
            $staticFile = WRITEPATH . 'sitemaps/' . $prefix . $i . '.xml';
            if (file_exists($staticFile)) {
                $xml .= '<lastmod>' . date('c', filemtime($staticFile)) . '</lastmod>';
            } else {
                $xml .= '<lastmod>' . date('c') . '</lastmod>';
            }
            
            $xml .= '</sitemap>';
        }

        if (!$isEn) {
            // 6. Páginas de Holdings (40.000 por página)
            $holdingModel = new \App\Models\HoldingModel();
            // Better to just use a fast count. 134505 / 40000 = 4 pages
            $holdingTotal = $holdingModel->countAllResults();
            $holdingPages = ceil($holdingTotal / 40000);
            
            for ($i = 1; $i <= $holdingPages; $i++) {
                $xml .= '<sitemap>';
                $xml .= '<loc>' . site_url("sitemap-holdings-{$i}.xml") . '</loc>';
                $xml .= '</sitemap>';
            }

            // 7. Sitemap VIP de Empresas Enriquecidas con IA
            $aiPages = 0;
            if (file_exists(WRITEPATH . 'sitemaps/sitemap-ai-ready-count.txt')) {
                $aiPages = (int) file_get_contents(WRITEPATH . 'sitemaps/sitemap-ai-ready-count.txt');
            } elseif (file_exists(FCPATH . 'sitemap-ai-ready-count.txt')) {
                $aiPages = (int) file_get_contents(FCPATH . 'sitemap-ai-ready-count.txt');
            }
            
            for ($i = 1; $i <= $aiPages; $i++) {
                $xml .= '<sitemap>';
                $xml .= '<loc>' . site_url("sitemap-ai-ready-{$i}.xml") . '</loc>';
                
                $staticAiFile = WRITEPATH . 'sitemaps/sitemap-ai-ready-' . $i . '.xml';
                if (file_exists($staticAiFile)) {
                    $xml .= '<lastmod>' . date('c', filemtime($staticAiFile)) . '</lastmod>';
                } else {
                    $xml .= '<lastmod>' . date('c') . '</lastmod>';
                }
                
                $xml .= '</sitemap>';
            }
        }
        
        $xml .= '</sitemapindex>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sitemap de páginas estáticas
     */
    public function static()
    {
        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);

        if ($isEn) {
            $urls = [
                ['loc' => site_url('/'), 'priority' => '1.0', 'freq' => 'daily'],
                ['loc' => site_url('docs'), 'priority' => '0.9', 'freq' => 'weekly'],
                ['loc' => site_url('spanish-company-data-api'), 'priority' => '0.8', 'freq' => 'monthly'],
                ['loc' => site_url('enter'), 'priority' => '0.5', 'freq' => 'monthly'],
                ['loc' => site_url('register'), 'priority' => '0.5', 'freq' => 'monthly'],
            ];
        } else {
            $urls = [
                ['loc' => site_url('/'), 'priority' => '1.0', 'freq' => 'daily'],
                ['loc' => site_url('base-de-datos-de-empresas'), 'priority' => '1.0', 'freq' => 'daily'],
                ['loc' => site_url('leads-empresas-nuevas'), 'priority' => '0.8', 'freq' => 'monthly'],
                ['loc' => site_url('contact'), 'priority' => '0.5', 'freq' => 'monthly'],
                ['loc' => site_url('documentation'), 'priority' => '0.9', 'freq' => 'weekly'],
                ['loc' => site_url('search_company'), 'priority' => '0.9', 'freq' => 'daily'],
                ['loc' => site_url('blog'), 'priority' => '0.8', 'freq' => 'daily'],
                ['loc' => site_url('empresas-nuevas'), 'priority' => '1.0', 'freq' => 'daily'],
                ['loc' => site_url('listado-de-grupos-empresariales'), 'priority' => '1.0', 'freq' => 'daily'],
            ];
        }

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
        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);
        if ($isEn) return $this->response->setStatusCode(404);

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

        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);
        $prefix = $isEn ? 'sitemap-en-companies-' : 'sitemap-companies-';
        
        $staticFile = WRITEPATH . 'sitemaps/' . $prefix . $page . '.xml';
        if (file_exists($staticFile)) {
            return $this->response->setContentType('application/xml')->setBody(file_get_contents($staticFile));
        }

        $model = new CompanyModel();
        helper(['text', 'seo_dynamic_helper', 'company']); // para url_title, scoring y urls de empresa

        // Calcular offset
        $offset = ($page - 1) * $this->perPage;

        // Obtener lote de empresas
        // Necesitamos campos extra para el cálculo del score SEO (shouldIndexCompany)
        $builder = $model->builder()
            ->select('companies.id, companies.cif, companies.company_name as name, companies.cnae_code as cnae, companies.registro_mercantil as province, companies.objeto_social as corporate_purpose, company_enrichment.ai_seo_text, (SELECT COUNT(id) FROM company_administrators WHERE company_administrators.company_id = companies.id) AS num_admins, (SELECT COUNT(id) FROM borme_posts WHERE borme_posts.company_id = companies.id) AS num_borme_posts', false) 

            ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left')
            ->join('company_privacy_optouts cpo', 'cpo.cif = companies.cif COLLATE utf8mb4_general_ci', 'left', false)
            ->where('cpo.cif IS NULL');
            
        $companies = $builder->orderBy('companies.id', 'ASC') // Orden consistente
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

        $cnaes = $model->builder()
            ->select('cnae_code as code')
            ->where('cnae_code IS NOT NULL')
            ->groupBy('cnae_code')
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

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home del directorio
        $xml .= '<url><loc>' . site_url('listado-de-empresas') . '</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>';

        foreach ($provinces as $p) {
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('listado-de-empresas/' . urlencode($p['name'])) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq><priority>0.8</priority>';
            $xml .= '</url>';
        }

        helper('text');
        foreach ($cnaes as $c) {
            $label = $cnaeMap[$c['code']] ?? "CNAE {$c['code']}";
            $slug = url_title($label, '-', true);
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('listado-de-empresas/sector-' . $c['code'] . '/' . $slug) . '</loc>';
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
    public function subvenciones()
    {
        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);
        if ($isEn) return $this->response->setStatusCode(404);

        $db = \Config\Database::connect();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $urls = [
            site_url('subvenciones-empresas'),
            site_url('empresas-mas-subvencionadas-espana'),
        ];
        foreach ([2020, 2021, 2022, 2023, 2024, 2025, 2026] as $year) {
            $urls[] = site_url('subvenciones-empresas/ano-' . $year);
        }

        foreach ($urls as $u) {
            $xml .= '<url><loc>' . $u . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
        }

        helper('text');
        $convocatorias = $db->query("SELECT DISTINCT convocatoria FROM company_subsidies WHERE convocatoria IS NOT NULL AND convocatoria != ''")->getResultArray();
        
        foreach ($convocatorias as $c) {
            $slug = url_title($c['convocatoria'], '-', true);
            $xml .= '<url><loc>' . site_url('subvenciones-empresas/convocatoria-' . $slug) . '</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    public function contratos()
    {
        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);
        if ($isEn) return $this->response->setStatusCode(404);

        $db = \Config\Database::connect();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $urls = [
            site_url('licitaciones-del-estado'),
            site_url('mayores-empresas-contratistas-del-estado'),
        ];
        foreach ([2020, 2021, 2022, 2023, 2024, 2025, 2026] as $year) {
            $urls[] = site_url('licitaciones-del-estado/ano-' . $year);
        }

        foreach ($urls as $u) {
            $xml .= '<url><loc>' . $u . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
        }

        helper('text');
        $organos = $db->query("SELECT DISTINCT organo_contratacion FROM company_contracts WHERE organo_contratacion IS NOT NULL AND organo_contratacion != ''")->getResultArray();
        
        foreach ($organos as $o) {
            $slug = url_title($o['organo_contratacion'], '-', true);
            $xml .= '<url><loc>' . site_url('licitaciones-del-estado/organo-' . $slug) . '</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }
    
    /**
     * Sitemap dinámico para Holdings (paginado en bloques de 40.000)
     */
    public function holdings($page = 1)
    {
        $perPage = 40000;
        $offset = ($page - 1) * $perPage;

        $holdingModel = new \App\Models\HoldingModel();
        
        // Optimize query by selecting only what we need
        $holdings = $holdingModel->select('slug, updated_at')
                                 ->orderBy('id', 'ASC')
                                 ->limit($perPage, $offset)
                                 ->findAll();

        if (empty($holdings)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($holdings as $holding) {
            $lastMod = !empty($holding['updated_at']) ? date('c', strtotime($holding['updated_at'])) : date('c');
            
            $xml .= '<url>';
            $xml .= '<loc>' . site_url('grupos-empresariales/' . $holding['slug']) . '</loc>';
            $xml .= '<lastmod>' . $lastMod . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sub-sitemap de empresas con IA (sitemap-ai-ready-X.xml)
     */
    public function aiReady($page = 1)
    {
        $page = (int) $page;
        if ($page < 1) $page = 1;

        $staticFile = WRITEPATH . 'sitemaps/sitemap-ai-ready-' . $page . '.xml';
        if (file_exists($staticFile)) {
            return $this->response->setContentType('application/xml')->setBody(file_get_contents($staticFile));
        }

        return $this->response->setStatusCode(404);
    }
}

