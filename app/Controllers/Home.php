<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        // SSR: Fetch latest blog posts for SEO (Cached 1h)
        $cache = \Config\Services::cache();
        $cacheKey = 'home_blog_posts_v1';
        $posts = $cache->get($cacheKey);

        if ($posts === null) {
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
                
                // Cache the results for 1 hour
                $cache->save($cacheKey, $posts, 3600);
            } catch (\Exception $e) {
                log_message('error', '[Home::index] Blog SSR failed: ' . $e->getMessage());
                $posts = [];
            }
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

        // Review Modal Logic
        $showReviewModal = false;
        $db = \Config\Database::connect();
        $ip = $this->request->getIPAddress();
        
        // Check if user already submitted a review
        $alreadyReviewed = $db->table('user_reviews')
                              ->where('ip_address', $ip)
                              ->countAllResults();
                              
        if ($alreadyReviewed == 0) {
            // Check if user has >= 3 searches
            $searchCount = $db->table('company_search_logs')
                              ->where('ip', $ip)
                              ->countAllResults();
                              
            if ($searchCount >= 3) {
                $showReviewModal = true;
            }
        }

        // Dynamic Social Proof Counter (with short cache)
        $cache = \Config\Services::cache();
        $cacheKey = 'home_social_proof_text_v3';
        $socialProofText = $cache->get($cacheKey);

        if ($socialProofText === null) {
            $apiRequestsModel = new \App\Models\ApiRequestsModel();
            $searchLogModel   = new \App\Models\SearchLogModel();
            $today            = date('Y-m-d');
            
            $apiValidationsToday = $apiRequestsModel->countRequestsForDay($today);
            $webValidationsToday = $searchLogModel->countLogsForDay($today);
            $totalReal           = $apiValidationsToday + $webValidationsToday;

            if ($totalReal <= 0) {
                $socialProofText = ''; // No data, hide block
            } elseif ($totalReal < 50) {
                $socialProofText = "Más de 100 empresas validadas hoy automáticamente";
            } elseif ($totalReal < 200) {
                $roundedTotal = ceil($totalReal / 50) * 50;
                $socialProofText = "Más de " . number_format($roundedTotal, 0, ',', '.') . " empresas validadas hoy automáticamente";
            } else {
                $socialProofText = "Hoy se han validado " . number_format($totalReal, 0, ',', '.') . " empresas automáticamente";
            }
            
            // Save to cache for 5 minutes (short cache)
            $cache->save($cacheKey, $socialProofText, 300);
        }

        // Fetch Free Plan Limit
        $apiPlanModel = new \App\Models\ApiPlanModel();
        $freePlan = $apiPlanModel->where('slug', 'free')->first();
        $freeLimit = $freePlan ? (int)$freePlan->monthly_quota : 15;

        return view('home', [
            'latest_posts'    => $posts,
            'provinces'       => $provinces,
            'showReviewModal' => $showReviewModal,
            'socialProofText' => $socialProofText,
            'freeLimit'       => $freeLimit
        ]);
    }


    public function submitReview()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $rating = (int) $this->request->getPost('rating');
        $comment = (string) $this->request->getPost('comment');
        
        if ($rating < 1 || $rating > 5) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid rating']);
        }

        $ip = $this->request->getIPAddress();
        $db = \Config\Database::connect();

        // Check if already reviewed
        $alreadyReviewed = $db->table('user_reviews')
                              ->where('ip_address', $ip)
                              ->countAllResults();
                              
        if ($alreadyReviewed > 0) {
            return $this->response->setJSON(['success' => true]);
        }

        // Insert review
        $data = [
            'ip_address' => $ip,
            'rating'     => $rating,
            'comment'    => $comment ? esc($comment) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->table('user_reviews')->insert($data);

        // Send email notification
        try {
            $emailService = \Config\Services::email();
            $emailService->setTo('papelo.amh@gmail.com');
            $emailService->setSubject('Nueva reseña en APIEmpresas');
            $emailService->setMessage("¡Se ha recibido una nueva reseña en la web!\n\nEstrellas: {$rating}/5\nComentario: " . ($comment ?: 'Sin comentarios') . "\nIP: {$ip}");
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Error sending review email: ' . $e->getMessage());
        }

        return $this->response->setJSON(['success' => true]);
    }
}
