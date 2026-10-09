<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    protected $perPage = 10000;

    /**
     * Entrega dinámica de robots.txt según el hostname de la petición
     */
    public function robots()
    {
        $rawHost = (string) $this->request->getServer('HTTP_HOST');
        $host = strtolower(trim(explode(':', $rawHost)[0]));

        // Lista blanca explícita de dominios para evitar Host Header Injection
        if (in_array($host, ['spaincompanyapi.com', 'www.spaincompanyapi.com', 'spaincompanyapi.test', 'spaincompanyapi.local'], true) 
            || strpos($host, 'spaincompanyapi') !== false) {
            $sitemapUrl = 'https://spaincompanyapi.com/sitemap.xml';
        } else {
            $sitemapUrl = 'https://apiempresas.es/sitemap.xml';
        }

        $content = "User-agent: *" . PHP_EOL
                 . "Crawl-delay: 5" . PHP_EOL
                 . "Disallow: /api/" . PHP_EOL
                 . "Disallow: /empresa/export/" . PHP_EOL
                 . PHP_EOL
                 . "# Opcional pero recomendado: bloquear parámetros" . PHP_EOL
                 . "Disallow: /*?cif=" . PHP_EOL
                 . "Disallow: /*?" . PHP_EOL
                 . PHP_EOL
                 . "# Permitir todo lo demás" . PHP_EOL
                 . "Allow: /" . PHP_EOL
                 . PHP_EOL
                 . "# Sitemap" . PHP_EOL
                 . "Sitemap: {$sitemapUrl}" . PHP_EOL;

        return $this->response
            ->setContentType('text/plain; charset=UTF-8')
            ->setBody($content);
    }

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
            // 5a. Empresas constituidas en los últimos 90 días (09-10-2026): son las que más
            // se buscan (Search Console) y en el sitemap general quedan perdidas entre 4 M.
            $xml .= '<sitemap><loc>' . site_url('sitemap-empresas-nuevas.xml') . '</loc>'
                . '<lastmod>' . date('c') . '</lastmod></sitemap>';

            // 5b. Grupo de control de los bloques de hechos (09-10-2026): 5 % de fichas sin
            // bloques, en sitemaps aparte para comparar su indexación en Search Console.
            $ctlPages = 0;
            if (file_exists(WRITEPATH . 'sitemaps/sitemap-control-count.txt')) {
                $ctlPages = (int) file_get_contents(WRITEPATH . 'sitemaps/sitemap-control-count.txt');
            }
            for ($i = 1; $i <= $ctlPages; $i++) {
                $ctlFile = WRITEPATH . 'sitemaps/sitemap-control-' . $i . '.xml';
                $xml .= '<sitemap><loc>' . site_url("sitemap-control-{$i}.xml") . '</loc>';
                if (file_exists($ctlFile)) {
                    $xml .= '<lastmod>' . date('c', filemtime($ctlFile)) . '</lastmod>';
                }
                $xml .= '</sitemap>';
            }

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
        if ($page < 1) {
            return $this->response->setStatusCode(404);
        }

        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);
        $prefix = $isEn ? 'sitemap-en-companies-' : 'sitemap-companies-';
        
        $staticFile = WRITEPATH . 'sitemaps/' . $prefix . $page . '.xml';
        if (file_exists($staticFile)) {
            return $this->response->setContentType('application/xml')->setBody(file_get_contents($staticFile));
        }

        // Fast-404: Si el conteo de sitemaps precalculado existe y la página está fuera de rango,
        // o si el archivo estático no existe en un entorno con sitemaps pregenerados, devolver 404 inmediato.
        $maxPages = 0;
        if (file_exists(WRITEPATH . 'sitemaps/sitemap-companies-count.txt')) {
            $maxPages = (int) file_get_contents(WRITEPATH . 'sitemaps/sitemap-companies-count.txt');
        } elseif (file_exists(FCPATH . 'sitemap-companies-count.txt')) {
            $maxPages = (int) file_get_contents(FCPATH . 'sitemap-companies-count.txt');
        }

        if ($maxPages > 0) {
            return $this->response->setStatusCode(404);
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
            // Sin lastmod (08-10-2026): la fecha de hoy en todas las URL no es un dato real.
            // Este camino solo se usa si no existen los sitemaps pregenerados.
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

        // Mismas URL que el índice (App\Libraries\Sectores): antes el nombre salía solo de
        // la CNAE-2009 y muchas URL del sitemap respondían con un 301. Los códigos que no
        // existen en ninguna CNAE no se listan, como en el índice.
        foreach ($cnaes as $c) {
            $code = (string) $c['code'];
            if (\App\Libraries\Sectores::nombre($code) === null) {
                continue;
            }
            $xml .= '<url>';
            $xml .= '<loc>' . \App\Libraries\Sectores::url($code) . '</loc>';
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
        return $this->sitemapFondos(
            'sitemap_subvenciones_v2',
            ['subvenciones-empresas', 'empresas-mas-subvencionadas-espana'],
            'subvenciones-empresas/ano-',
            array_keys(\App\Libraries\FondosPublicos::anosSubvenciones()),
            'subvenciones-empresas/convocatoria-',
            array_keys(\App\Libraries\FondosPublicos::slugsConvocatorias())
        );
    }

    public function contratos()
    {
        return $this->sitemapFondos(
            'sitemap_contratos_v2',
            ['licitaciones-del-estado', 'mayores-empresas-contratistas-del-estado'],
            'licitaciones-del-estado/ano-',
            array_keys(\App\Libraries\FondosPublicos::anosContratos()),
            'licitaciones-del-estado/organo-',
            array_keys(\App\Libraries\FondosPublicos::slugsOrganos())
        );
    }

    /**
     * Sitemap de subvenciones o licitaciones. Solo años con datos y solo convocatorias
     * u órganos con alguna entidad (sin personas físicas). Se guarda un día: antes
     * recorría la tabla entera en cada petición.
     */
    private function sitemapFondos(string $clave, array $fijas, string $baseAno, array $anos, string $baseSlug, array $slugs)
    {
        $isEn = (strpos((string)$this->request->getServer('HTTP_HOST'), 'spaincompanyapi') !== false);
        if ($isEn) return $this->response->setStatusCode(404);

        $cache = \Config\Services::cache();
        $xml   = $cache->get($clave);
        if (!is_string($xml) || $xml === '') {
            $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($fijas as $u) {
                $xml .= '<url><loc>' . esc(site_url($u), 'html') . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
            }
            foreach ($anos as $ano) {
                $xml .= '<url><loc>' . esc(site_url($baseAno . $ano), 'html') . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
            }
            foreach ($slugs as $slug) {
                $xml .= '<url><loc>' . esc(site_url($baseSlug . $slug), 'html') . '</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>';
            }
            $xml .= '</urlset>';
            if ($slugs) {
                $cache->save($clave, $xml, 86400);
            }
        }

        return $this->response->setContentType('application/xml')->setBody($xml);
    }
    
    /**
     * Sitemap dinámico para Holdings (paginado en bloques de 40.000)
     */
    public function holdings($page = 1)
    {
        $page = (int) $page;
        if ($page < 1) {
            return $this->response->setStatusCode(404);
        }

        $perPage = 40000;
        $holdingModel = new \App\Models\HoldingModel();

        // Verificación rápida del rango de páginas para evitar scans de DB
        $holdingTotal = $holdingModel->countAllResults();
        $holdingPages = ceil($holdingTotal / $perPage);
        if ($holdingPages > 0 && $page > $holdingPages) {
            return $this->response->setStatusCode(404);
        }

        $offset = ($page - 1) * $perPage;
        
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
     * Empresas constituidas en los últimos 90 días (sitemap-empresas-nuevas.xml), 09-10-2026.
     *
     * Search Console (3 meses) dice que muchas de las fichas con más clics son de empresas recién
     * creadas: alguien acaba de conocerlas y busca su nombre o su CIF. En el sitemap general
     * están mezcladas con 4 millones de URLs; aquí Google las tiene juntas y las ve antes.
     *
     * - Nuevas = con un acto de "Constitución" en el BORME de los últimos 90 días (o, si no
     *   lo tiene enlazado, con fecha de constitución en ese plazo).
     * - Mismo filtro que el resto de sitemaps (shouldIndexCompany) y sin el grupo de control.
     * - lastmod = fecha de su último acto del BORME.
     * - Se calcula al pedirlo y se guarda 6 horas en caché. Máximo 50.000 URL.
     */
    public function empresasNuevas()
    {
        $cache = \Config\Services::cache();
        $clave = 'sitemap_empresas_nuevas_v1';
        $xml = $cache->get($clave);
        if (!is_string($xml) || $xml === '') {
            $xml = $this->generarEmpresasNuevas();
            $cache->save($clave, $xml, 21600);
        }

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    private function generarEmpresasNuevas(): string
    {
        helper(['text', 'seo_dynamic', 'company', 'ficha_hechos']);
        $db = \Config\Database::connect();
        $desde = date('Y-m-d', strtotime('-90 days'));
        $hoy = date('Y-m-d');

        $ids = [];
        $q = $db->query(
            "SELECT DISTINCT company_id FROM borme_posts
             WHERE borme_date >= ? AND company_id IS NOT NULL AND description LIKE 'Constituci%'",
            [$desde]
        );
        foreach ($q->getResultArray() as $r) {
            $ids[(int) $r['company_id']] = true;
        }
        $q = $db->query(
            "SELECT id FROM companies WHERE fecha_constitucion >= ? AND fecha_constitucion <= ?",
            [$desde, $hoy]
        );
        foreach ($q->getResultArray() as $r) {
            $ids[(int) $r['id']] = true;
        }
        $ids = array_slice(array_keys($ids), 0, 60000);

        $entradas = [];
        foreach (array_chunk($ids, 5000) as $trozo) {
            $lista = implode(',', array_map('intval', $trozo));
            $filas = $db->query(
                "SELECT c.id, c.cif, c.company_name AS name, c.cnae_code AS cnae, c.registro_mercantil AS province,
                        c.objeto_social AS corporate_purpose, c.fecha_constitucion, e.ai_seo_text
                 FROM companies c
                 LEFT JOIN company_enrichment e ON e.company_id = c.id
                 LEFT JOIN company_privacy_optouts o ON o.cif = c.cif COLLATE utf8mb4_general_ci
                 WHERE c.id IN ({$lista}) AND o.cif IS NULL"
            )->getResultArray();
            $admins = [];
            foreach ($db->query("SELECT company_id, COUNT(*) n FROM company_administrators WHERE company_id IN ({$lista}) GROUP BY company_id")->getResultArray() as $r) {
                $admins[$r['company_id']] = (int) $r['n'];
            }
            $borme = [];
            foreach ($db->query("SELECT company_id, COUNT(*) n, MAX(borme_date) ultima FROM borme_posts WHERE company_id IN ({$lista}) GROUP BY company_id")->getResultArray() as $r) {
                $borme[$r['company_id']] = $r;
            }
            foreach ($filas as $c) {
                $c['num_admins'] = $admins[$c['id']] ?? 0;
                $c['num_borme_posts'] = (int) ($borme[$c['id']]['n'] ?? 0);
                if (!shouldIndexCompany($c) || ficha_grupo_control($c)) {
                    continue;
                }
                $fecha = $borme[$c['id']]['ultima'] ?? $c['fecha_constitucion'] ?? null;
                $entradas[] = [company_url($c), $fecha ? date('Y-m-d', strtotime($fecha)) : null];
            }
        }

        usort($entradas, static fn ($a, $b) => strcmp((string) $b[1], (string) $a[1]));
        $entradas = array_slice($entradas, 0, 50000);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        foreach ($entradas as [$url, $fecha]) {
            $xml .= '<url><loc>' . esc($url) . '</loc>' . ($fecha ? '<lastmod>' . $fecha . '</lastmod>' : '') . '</url>' . PHP_EOL;
        }
        return $xml . '</urlset>';
    }

    /**
     * Índices para medir en Search Console (09-10-2026). Se dan de alta aparte, además de
     * sitemap.xml, y el informe de páginas se filtra por cada uno:
     *   sitemap-indice-fichas.xml   fichas CON bloques de hechos (sitemap-companies-N.xml)
     *   sitemap-indice-control.xml  fichas SIN bloques, el grupo de control (sitemap-control-N.xml)
     */
    public function indiceGrupo($grupo = 'fichas')
    {
        $prefijo = $grupo === 'control' ? 'sitemap-control-' : 'sitemap-companies-';
        $cuenta = WRITEPATH . 'sitemaps/' . $prefijo . 'count.txt';
        $paginas = file_exists($cuenta) ? (int) file_get_contents($cuenta) : 0;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        for ($i = 1; $i <= $paginas; $i++) {
            $fichero = WRITEPATH . 'sitemaps/' . $prefijo . $i . '.xml';
            $xml .= '<sitemap><loc>' . site_url("{$prefijo}{$i}.xml") . '</loc>';
            if (file_exists($fichero)) {
                $xml .= '<lastmod>' . date('c', filemtime($fichero)) . '</lastmod>';
            }
            $xml .= '</sitemap>';
        }
        $xml .= '</sitemapindex>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    /**
     * Sub-sitemap del grupo de control (sitemap-control-X.xml): fichas sin bloques de hechos.
     */
    public function control($page = 1)
    {
        $page = (int) $page;
        $staticFile = WRITEPATH . 'sitemaps/sitemap-control-' . $page . '.xml';
        if ($page >= 1 && file_exists($staticFile)) {
            return $this->response->setContentType('application/xml')->setBody(file_get_contents($staticFile));
        }

        return $this->response->setStatusCode(404);
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

