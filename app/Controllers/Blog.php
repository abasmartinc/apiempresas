<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Blog extends BaseController
{
    public function index()
    {
        return view('blog');
    }

    /**
     * Página de detalle de post del blog.
     * Recibe el slug (p.ej. validar-cif-y-razon-social-en-tiempo-real-la-solucion-api-para-empresas)
     */
    public function post(string $slug)
    {
        $slug = trim($slug);
        if ($slug === '') {
            throw PageNotFoundException::forPageNotFound('Post no encontrado.');
        }

        $siteUrl  = 'https://blog.apiempresas.es';
        // Traemos el post por slug e incluimos _embed para autor/categorías
        $endpoint = '/index.php?rest_route=/wp/v2/posts&slug=' . urlencode($slug) . '&_embed=1';
        $requestUrl = $siteUrl . $endpoint;

        $ch = curl_init($requestUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'APIEmpresasBlogBot/1.0 (+https://apiempresas.es)',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
            // Si en desarrollo tuvieras problemas de SSL, podrías descomentar:
            // CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $curlErrNo  = curl_errno($ch);
            $curlErrMsg = curl_error($ch);
            curl_close($ch);

            log_message('error', 'Error cURL Blog::post: '.$curlErrNo.' - '.$curlErrMsg);
            throw PageNotFoundException::forPageNotFound('Post no disponible.');
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            log_message('error', 'HTTP '.$status.' al obtener post por slug desde '.$requestUrl);
            throw PageNotFoundException::forPageNotFound('Post no encontrado.');
        }

        $posts = json_decode($response, true);
        if (!is_array($posts) || empty($posts)) {
            throw PageNotFoundException::forPageNotFound('Post no encontrado.');
        }

        // WordPress devuelve un array, nos quedamos con el primero
        $wpPost = $posts[0];

        $title   = $wpPost['title']['rendered']   ?? '';
        $content = $wpPost['content']['rendered'] ?? '';
        $excerptHtml = $wpPost['excerpt']['rendered'] ?? '';
        $excerptText = trim(strip_tags($excerptHtml));

        // Acortar un poco el texto que usamos como subtítulo
        if (mb_strlen($excerptText, 'UTF-8') > 220) {
            $excerptText = mb_substr($excerptText, 0, 217, 'UTF-8') . '…';
        }

        $dateRaw = $wpPost['date'] ?? null;
        $dateStr = $dateRaw ? date('d/m/Y', strtotime($dateRaw)) : '';

        // Tiempo de lectura aproximado
        $contentText = strip_tags($content);
        $wordCount   = str_word_count($contentText);
        $minutes     = max(3, min(15, (int)ceil($wordCount / 220)));
        $readingTime = $minutes . ' min de lectura';

        // Eyebrow desde categoría principal, si viene
        $eyebrow = 'Guía técnica';
        if (!empty($wpPost['_embedded']['wp:term'][0][0]['name'])) {
            $eyebrow = $wpPost['_embedded']['wp:term'][0][0]['name'];
        }

        // Autor
        $authorName = 'Equipo APIEmpresas';
        if (!empty($wpPost['_embedded']['author'][0]['name'])) {
            $authorName = $wpPost['_embedded']['author'][0]['name'];
        }

        $data = [
            'slug'         => $slug,
            'title'        => $title,
            'content'      => $content,      // HTML de WP, lo mostraremos sin escapar
            'excerptText'  => $excerptText,
            'dateStr'      => $dateStr,
            'readingTime'  => $readingTime,
            'eyebrow'      => $eyebrow,
            'authorName'   => $authorName,
        ];

        return view('post', $data);
    }

    /**
     * Devuelve los últimos posts del blog en formato HTML para inyectar por AJAX en la home.
     */
    public function get_posts()
    {
        $siteUrl    = 'https://blog.apiempresas.es';
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&per_page=4';
        $requestUrl = $siteUrl . $endpoint;

        $ch = curl_init($requestUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'APIEmpresasHomeBot/1.0 (+https://apiempresas.es)',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $curlErrNo  = curl_errno($ch);
            $curlErrMsg = curl_error($ch);
            curl_close($ch);

            log_message('error', 'Error cURL get_posts: '.$curlErrNo.' - '.$curlErrMsg);

            return $this->response->setJSON([
                'ok'   => false,
                'html' => '',
                'err'  => "Error cURL ($curlErrNo): $curlErrMsg",
            ]);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            log_message('error', 'HTTP status '.$status.' al obtener posts desde '.$requestUrl);

            return $this->response->setJSON([
                'ok'   => false,
                'html' => '',
                'err'  => "HTTP $status al obtener posts desde $requestUrl",
            ]);
        }

        $posts = json_decode($response, true);
        if (!is_array($posts) || empty($posts)) {
            return $this->response->setJSON([
                'ok'   => false,
                'html' => '',
                'err'  => 'Respuesta sin posts o JSON inválido',
            ]);
        }

        $html = '';

        foreach ($posts as $post) {
            $title   = $post['title']['rendered'] ?? '';
            $slug    = $post['slug'] ?? '';
            $excerpt = strip_tags($post['excerpt']['rendered'] ?? '');

            if (mb_strlen($excerpt, 'UTF-8') > 190) {
                $excerpt = mb_substr($excerpt, 0, 187, 'UTF-8') . '…';
            }

            $dateRaw = $post['date'] ?? null;
            $dateStr = $dateRaw ? date('d/m/Y', strtotime($dateRaw)) : '';

            $contentText = strip_tags($post['content']['rendered'] ?? '');
            $wordCount   = str_word_count($contentText);
            $minutes     = max(3, min(15, (int)ceil($wordCount / 220)));
            $readingStr  = $minutes . ' min de lectura';


            $url = site_url('blog/' . $slug);

            $html .= '
                <article class="home-blog__card">
                    <a href="' . $url . '" class="home-blog__link">
                        <h3 class="home-blog__title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>
                        <p class="home-blog__excerpt">' . htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') . '</p>
                        <p class="home-blog__meta muted">
                            ' . htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') . ' · ' . htmlspecialchars($readingStr, ENT_QUOTES, 'UTF-8') . '
                        </p>
                    </a>
                </article>';
        }

        return $this->response->setJSON([
            'ok'   => true,
            'html' => $html,
        ]);
    }

    public function get_posts_grid()
    {
        $siteUrl    = 'https://blog.apiempresas.es';
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&per_page=6';
        $requestUrl = $siteUrl . $endpoint;

        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'APIEmpresasBlogGridBot/1.0 (+https://apiempresas.es)',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $curlErrNo  = curl_errno($ch);
            $curlErrMsg = curl_error($ch);
            curl_close($ch);

            log_message('error', 'Error cURL get_posts_grid: '.$curlErrNo.' - '.$curlErrMsg);

            return $this->response->setJSON([
                'ok'   => false,
                'html' => '',
                'err'  => 'Error cURL al obtener posts',
            ]);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            log_message('error', 'HTTP '.$status.' al obtener posts grid desde '.$requestUrl);

            return $this->response->setJSON([
                'ok'   => false,
                'html' => '',
                'err'  => "HTTP $status al obtener posts",
            ]);
        }

        $posts = json_decode($response, true);
        if (!is_array($posts) || empty($posts)) {
            return $this->response->setJSON([
                'ok'   => false,
                'html' => '',
                'err'  => 'Respuesta sin posts',
            ]);
        }

        // Normalizamos el array (por si WordPress devuelve key = ID)
        $posts = array_values($posts);

        // Helper para tarjetas
        $build = function (array $post, int $excerptLength = 180): array {
            $title   = $post['title']['rendered'] ?? '';
            $slug    = $post['slug'] ?? '';

            // excerpt limpio
            $excerpt = strip_tags($post['excerpt']['rendered'] ?? '');
            if (mb_strlen($excerpt, 'UTF-8') > $excerptLength) {
                $excerpt = mb_substr($excerpt, 0, $excerptLength - 3, 'UTF-8') . '…';
            }

            $dateRaw = $post['date'] ?? null;
            $dateStr = $dateRaw ? date('d M Y', strtotime($dateRaw)) : '';

            // estimar tiempo de lectura
            $contentText = strip_tags($post['content']['rendered'] ?? '');
            $wordCount   = str_word_count($contentText);
            $minutes     = max(4, min(15, (int)ceil($wordCount / 220)));
            $readingStr  = $minutes . ' min';

            $url = site_url('blog/' . $slug);

            return [
                'title'   => $title,
                'excerpt' => $excerpt,
                'date'    => $dateStr,
                'reading' => $readingStr,
                'url'     => $url,
            ];
        };

        ob_start();
        ?>
        <section class="blog-grid">
            <div class="blog-grid__list blog-grid__list--two-cols">
                <?php foreach ($posts as $index => $post): ?>
                    <?php $p = $build($post); ?>
                    <article class="blog-card blog-card--standard" data-index="<?= (int)$index ?>">
                        <a href="<?= esc($p['url']) ?>" class="blog-card__link">
                            <div class="blog-card__inner">
                                <div class="blog-card__top-row">
                                    <span class="blog-card__chip">Guía técnica</span>

                                    <div class="blog-card__meta blog-card__meta--top">
                                        <?php if ($p['date']): ?>
                                            <span class="blog-card__meta-item">
                                            <?= esc($p['date']) ?>
                                        </span>
                                            <span class="blog-card__meta-separator">·</span>
                                        <?php endif; ?>
                                        <span class="blog-card__meta-item">
                                        <?= esc($p['reading']) ?> de lectura
                                    </span>
                                    </div>
                                </div>

                                <h3 class="blog-card__title">
                                    <?= esc($p['title']) ?>
                                </h3>

                                <p class="blog-card__excerpt">
                                    <?= esc($p['excerpt']) ?>
                                </p>

                                <div class="blog-card__footer">
                                    <span class="blog-card__tag">Datos mercantiles</span>
                                    <span class="blog-card__cta">Leer artículo →</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        $html = trim(ob_get_clean());

        return $this->response->setJSON([
            'ok'   => true,
            'html' => $html,
        ]);
    }







}
