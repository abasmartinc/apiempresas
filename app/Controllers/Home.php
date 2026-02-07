<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        // SSR: Fetch latest blog posts for SEO
        $blogController = new Blog();
        // Since get_posts returns JSON, we'll extract the data part or the logic
        // Better yet: refactor the fetching logic to a shared helper or service
        // For now, I'll fetch them here to avoid breaking Blog controller's AJAX endpoint
        
        $siteUrl    = 'https://blog.apiempresas.es';
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&per_page=6';
        $requestUrl = $siteUrl . $endpoint;

        $posts = [];
        try {
            $ch = curl_init($requestUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3, // Fast timeout for home page
                CURLOPT_USERAGENT      => 'APIEmpresasHomeSSR/1.0',
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $rawPosts = json_decode($response, true);
                if (is_array($rawPosts)) {
                    foreach ($rawPosts as $post) {
                        $excerpt = strip_tags($post['excerpt']['rendered'] ?? '');
                        if (mb_strlen($excerpt, 'UTF-8') > 190) {
                            $excerpt = mb_substr($excerpt, 0, 187, 'UTF-8') . '…';
                        }
                        
                        $contentText = strip_tags($post['content']['rendered'] ?? '');
                        $wordCount   = str_word_count($contentText);
                        $minutes     = max(3, min(15, (int)ceil($wordCount / 220)));

                        $posts[] = [
                            'title'   => $post['title']['rendered'] ?? '',
                            'slug'    => $post['slug'] ?? '',
                            'excerpt' => $excerpt,
                            'date'    => isset($post['date']) ? date('d/m/Y', strtotime($post['date'])) : '',
                            'reading' => $minutes . ' min de lectura',
                            'url'     => site_url('blog/' . ($post['slug'] ?? ''))
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', '[Home::index] Blog SSR failed: ' . $e->getMessage());
        }

        // Fetch provinces for internal linking (filtered and normalized)
        $companyModel = new \App\Models\CompanyModel();
        $provincesRaw = $companyModel->builder()
            ->select('registro_mercantil as name')
            ->where('registro_mercantil IS NOT NULL')
            ->where('registro_mercantil !=', '')
            ->where('LENGTH(registro_mercantil) >', 2) // Exclude single chars like '8'
            ->groupBy('registro_mercantil')
            ->orderBy('registro_mercantil', 'ASC')
            ->get()
            ->getResultArray();
        
        // Normalization map for province variants
        $provinceMap = [
            'guipuzcoa' => 'Guipúzcoa',
            'guipúzcoa-gipuzkoa' => 'Guipúzcoa',
            'gipuzkoa' => 'Guipúzcoa',
            'vizcaya' => 'Vizcaya',
            'bizkaia' => 'Vizcaya',
            'vizcaya-bizkaia' => 'Vizcaya',
            'alava' => 'Álava',
            'álava' => 'Álava',
            'araba' => 'Álava',
            'álava-araba' => 'Álava',
            'áraba/alava' => 'Álava',
            'navarra' => 'Navarra',
            'nafarroa' => 'Navarra',
            'la coruña' => 'A Coruña',
            'coruña' => 'A Coruña',
            'a coruña' => 'A Coruña',
            'orense' => 'Ourense',
            'pontevedra' => 'Pontevedra',
            'lugo' => 'Lugo',
            'castellon' => 'Castellón',
            'castellón' => 'Castellón',
            'valencia' => 'Valencia',
            'alicante' => 'Alicante',
            'baleares' => 'Baleares',
            'islas baleares' => 'Baleares',
            'las palmas' => 'Las Palmas',
            'santa cruz de tenerife' => 'Santa Cruz De Tenerife',
            'sta. cruz de tenerife' => 'Santa Cruz De Tenerife',
        ];
        
        // Invalid province names to exclude
        $invalidProvinces = ['desconocida', 'desconocido', 'unknown', 'n/a', 'sin provincia'];
        
        // Normalize and deduplicate
        $normalized = [];
        foreach ($provincesRaw as $prov) {
            $key = mb_strtolower($prov['name'], 'UTF-8');
            
            // Skip invalid provinces
            if (in_array($key, $invalidProvinces)) {
                continue;
            }
            
            // Use mapped name if exists, otherwise use title case
            if (isset($provinceMap[$key])) {
                $canonicalName = $provinceMap[$key];
            } else {
                $canonicalName = mb_convert_case($prov['name'], MB_CASE_TITLE, 'UTF-8');
            }
            
            // Deduplicate by canonical name
            $normalized[$canonicalName] = ['name' => $canonicalName];
        }
        
        // Convert back to indexed array and sort
        $provinces = array_values($normalized);
        usort($provinces, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return view('home', [
            'latest_posts' => $posts,
            'provinces'    => $provinces
        ]);
    }
}
