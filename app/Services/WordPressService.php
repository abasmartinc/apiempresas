<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Services;

class WordPressService
{
    protected $siteUrl = 'https://blog.apiempresas.es';
    protected $cache;
    protected $cacheTTL = 86400; // 24 hours

    public function __construct()
    {
        $this->cache = Services::cache();
    }

    /**
     * Obtiene posts de una categoría específica (ej. 20 para plantillas SEO).
     */
    public function getTemplatesByCategory(int $categoryId): array
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'wp_templates_cat_' . $categoryId;
        
        // Permitimos forzar la limpieza del caché vía URL para desarrollo
        $forceNoCache = service('request')->getGet('nocache') === '1';
        if ($forceNoCache) {
            $cache->delete($cacheKey);
        }

        $cached = $cache->get($cacheKey);
        if ($cached !== null && !$forceNoCache) {
            return $cached;
        }

        $endpoint = "/index.php?rest_route=/wp/v2/posts&categories={$categoryId}&per_page=100&_embed=1";
        $response = $this->makeRequest($endpoint);

        if ($response) {
            $this->cache->save($cacheKey, $response, $this->cacheTTL);
            return $response;
        }

        return [];
    }

    /**
     * Obtiene un post específico por su slug.
     */
    public function getTemplateBySlug(string $slug): ?array
    {
        $cacheKey = "wp_template_slug_" . md5($slug);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $endpoint = "/index.php?rest_route=/wp/v2/posts&slug=" . urlencode($slug) . "&_embed=1";
        $response = $this->makeRequest($endpoint);

        if (is_array($response) && !empty($response)) {
            $template = $response[0];
            $this->cache->save($cacheKey, $template, $this->cacheTTL);
            return $template;
        }

        return null;
    }

    /**
     * Realiza la petición cURL a WordPress.
     */
    protected function makeRequest(string $endpoint): ?array
    {
        $url = $this->siteUrl . $endpoint;
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'APIEmpresasSEOBot/1.0 (+https://apiempresas.es)',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
            // Descomentar si hay problemas de SSL en dev
            // CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            log_message('error', 'WordPressService cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            log_message('error', "WordPressService HTTP Error {$status} on URL: {$url}");
            return null;
        }

        return json_decode($response, true);
    }
}
