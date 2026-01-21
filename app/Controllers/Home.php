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
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&per_page=4';
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

        return view('home', ['latest_posts' => $posts]);
    }


}
