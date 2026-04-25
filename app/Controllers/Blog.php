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

        // 1. CLASIFICACIÓN AUTOMÁTICA DEL POST (RADAR vs API)
        $radarKeywords = ['clientes', 'leads', 'vender', 'captar', 'proveedores', 'oportunidades', 'comercial', 'ventas'];
        $apiKeywords   = ['API', 'integración', 'endpoint', 'JSON', 'código', 'validación CIF', 'desarrollador', 'REST'];

        $radarScore = 0;
        $apiScore   = 0;
        $contentText = strip_tags($content);

        foreach ($radarKeywords as $kw) {
            $radarScore += mb_substr_count(mb_strtolower($contentText), mb_strtolower($kw));
            $radarScore += mb_substr_count(mb_strtolower($title), mb_strtolower($kw)) * 5; // Title counts more
        }
        foreach ($apiKeywords as $kw) {
            $apiScore += mb_substr_count(mb_strtolower($contentText), mb_strtolower($kw));
            $apiScore += mb_substr_count(mb_strtolower($title), mb_strtolower($kw)) * 5;
        }

        $intent = 'MIXTO';
        if ($radarScore > 0 && $apiScore === 0) $intent = 'RADAR';
        elseif ($apiScore > 0 && $radarScore === 0) $intent = 'API';
        elseif ($radarScore > $apiScore * 1.5) $intent = 'RADAR';
        elseif ($apiScore > $radarScore * 1.5) $intent = 'API';

        // 2. CTA BLOCKS DEFINITION
        $ctaTop = '';
        $ctaMiddle = '';
        $ctaBottom = '';

        $microcopy = '
            <p class="ae-blog-cta__microcopy">
                <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Acceso inmediato</span>
                <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Sin tarjeta</span>
                <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Resultados en minutos</span>
            </p>';

        if ($intent === 'RADAR') {
            $ctaTop = '
                <div class="ae-blog-cta ae-blog-cta--radar ae-blog-cta--top">
                    <p class="ae-blog-cta__text">Accede a empresas que están buscando proveedores ahora mismo</p>
                    <a href="'.site_url('radar/preview').'" class="ae-blog-cta__btn">Ver oportunidades activas</a>
                    '.$microcopy.'
                </div>';
            $ctaMiddle = '
                <div class="ae-blog-cta ae-blog-cta--radar ae-blog-cta--middle">
                    <p class="ae-blog-cta__text">¿Prefieres trabajar con listados completos?</p>
                    <a href="'.site_url('checkout/radar-export').'" class="ae-blog-cta__btn">Descargar listado en Excel</a>
                    '.$microcopy.'
                </div>';
            $ctaBottom = '
                <div class="ae-blog-cta ae-blog-cta--radar ae-blog-cta--bottom">
                    <p class="ae-blog-cta__text">Empieza a conseguir clientes antes que tu competencia</p>
                    <a href="'.site_url('radar/preview').'" class="ae-blog-cta__btn">Acceder al radar ahora</a>
                    '.$microcopy.'
                </div>';
        } elseif ($intent === 'API') {
            $ctaTop = '
                <div class="ae-blog-cta ae-blog-cta--api ae-blog-cta--top">
                    <p class="ae-blog-cta__text">Empieza a integrar datos empresariales en minutos</p>
                    <a href="'.site_url('documentation').'" class="ae-blog-cta__btn">Ver documentación API</a>
                    '.$microcopy.'
                </div>';
            $ctaMiddle = '
                <div class="ae-blog-cta ae-blog-cta--api ae-blog-cta--middle">
                    <p class="ae-blog-cta__text">¿Necesitas datos en tiempo real?</p>
                    <a href="'.site_url('register').'" class="ae-blog-cta__btn">Probar API gratis</a>
                    '.$microcopy.'
                </div>';
            $ctaBottom = '
                <div class="ae-blog-cta ae-blog-cta--api ae-blog-cta--bottom">
                    <p class="ae-blog-cta__text">Automatiza la verificación de empresas con nuestra API</p>
                    <a href="'.site_url('register').'" class="ae-blog-cta__btn">Empezar integración</a>
                    '.$microcopy.'
                </div>';
        } else {
            // MIXTO
            $dominantType = ($radarScore >= $apiScore) ? 'RADAR' : 'API';
            if ($dominantType === 'RADAR') {
                $ctaTop = '
                    <div class="ae-blog-cta ae-blog-cta--radar ae-blog-cta--top">
                        <p class="ae-blog-cta__text">Accede a empresas que están buscando proveedores ahora mismo</p>
                        <a href="'.site_url('radar/preview').'" class="ae-blog-cta__btn">Ver oportunidades activas</a>
                        '.$microcopy.'
                    </div>';
            } else {
                $ctaTop = '
                    <div class="ae-blog-cta ae-blog-cta--api ae-blog-cta--top">
                        <p class="ae-blog-cta__text">Empieza a integrar datos empresariales en minutos</p>
                        <a href="'.site_url('documentation').'" class="ae-blog-cta__btn">Ver documentación API</a>
                        '.$microcopy.'
                    </div>';
            }
            $ctaMiddle = '
                <div class="ae-blog-cta ae-blog-cta--mixed ae-blog-cta--middle">
                    <p class="ae-blog-cta__text">¿Quieres acceder directamente a empresas que necesitan proveedores?</p>
                    <a href="'.site_url('radar/preview').'" class="ae-blog-cta__btn">Ver oportunidades en tiempo real</a>
                    '.$microcopy.'
                </div>';
            $ctaBottom = '
                <div class="ae-blog-cta ae-blog-cta--mixed ae-blog-cta--bottom">
                    <p class="ae-blog-cta__text">Impulsa tu negocio con datos oficiales y leads frescos</p>
                    <div class="ae-blog-cta__row">
                        <a href="'.site_url('radar/preview').'" class="ae-blog-cta__btn">Ver Radar</a>
                        <a href="'.site_url('register').'" class="ae-blog-cta__btn secondary">Probar API</a>
                    </div>
                    '.$microcopy.'
                </div>';
        }

        // 3. INJECT MIDDLE CTA
        $paragraphs = explode('</p>', $content);
        $count = count($paragraphs);
        if ($count > 4) {
            $middleIndex = (int)floor($count / 2);
            // We close the content div, inject CTA, and reopen for the rest of the post
            $paragraphs[$middleIndex] .= '</p></div>' . $ctaMiddle . '<div class="blog-post__content">'; 
        } else {
            $content .= $ctaMiddle;
        }
        $content = implode('</p>', $paragraphs);
        // Remove trailing empty paragraph if implode added it wrongly
        $content = str_replace('<div class="blog-post__content"></p>', '<div class="blog-post__content">', $content);

        $data = [
            'slug'         => $slug,
            'title'        => $title,
            'content'      => $content,
            'excerptText'  => $excerptText,
            'dateStr'      => $dateStr,
            'readingTime'  => $readingTime,
            'eyebrow'      => $eyebrow,
            'authorName'   => $authorName,
            'ctaTop'       => $ctaTop,
            'ctaBottom'    => $ctaBottom,
            'intent'       => $intent
        ];

        return view('post', $data);
    }

    /**
     * Devuelve los últimos posts del blog en formato HTML para inyectar por AJAX en la home.
     */
    public function get_posts()
    {
        $siteUrl    = 'https://blog.apiempresas.es';
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&per_page=6&categories=1&categories_exclude=20';
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
        $page = (int)$this->request->getGet('page');
        if ($page < 1) $page = 1;
        
        log_message('debug', 'Fetching blog posts for page: ' . $page);
        $perPage = 10; // 1 featured + 9 grid items or similar
        
        $siteUrl    = 'https://blog.apiempresas.es';
        $endpoint   = '/index.php?rest_route=/wp/v2/posts&_embed&per_page='.$perPage.'&page='.$page.'&categories=1&categories_exclude=20';
        $requestUrl = $siteUrl . $endpoint;

        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'APIEmpresasBlogGridBot/1.0 (+https://apiempresas.es)',
            CURLOPT_HEADER         => true, // We need headers for X-WP-TotalPages
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headersText = substr($response, 0, $headerSize);
        $bodyText    = substr($response, $headerSize);

        if (curl_errno($ch)) {
            $curlErrNo  = curl_errno($ch);
            $curlErrMsg = curl_error($ch);
            curl_close($ch);
            log_message('error', 'Error cURL get_posts_grid: '.$curlErrNo.' - '.$curlErrMsg);
            return $this->response->setJSON(['ok' => false, 'err' => 'Error de conexión con el blog.']);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            return $this->response->setJSON(['ok' => false, 'err' => "Error servidor ($status)"]);
        }

        // Extraer Total Pages de los headers
        $totalPages = 1;
        if (preg_match('/X-WP-TotalPages:\s*(\d+)/i', $headersText, $matches)) {
            $totalPages = (int)$matches[1];
        }

        $posts = json_decode($bodyText, true);
        if (!is_array($posts) || empty($posts)) {
            return $this->response->setJSON(['ok' => false, 'err' => 'No hay artículos disponibles.']);
        }

        $posts = array_values($posts);

        // Helper para tarjetas
        $build = function (array $post, bool $isFeatured = false): string {
            $title   = $post['title']['rendered'] ?? '';
            $slug    = $post['slug'] ?? '';
            // Usamos el contenido principal para asegurar que tenemos texto suficiente para el diseño
            $excerpt = strip_tags($post['content']['rendered'] ?? '');
            if (empty($excerpt)) {
                $excerpt = strip_tags($post['excerpt']['rendered'] ?? '');
            }
            
            $limit = $isFeatured ? 800 : 350;
            if (mb_strlen($excerpt, 'UTF-8') > $limit) {
                $excerpt = mb_substr($excerpt, 0, $limit - 3, 'UTF-8') . '…';
            }

            $dateRaw = $post['date'] ?? null;
            $dateStr = $dateRaw ? date('d M, Y', strtotime($dateRaw)) : '';
            $contentText = strip_tags($post['content']['rendered'] ?? '');
            $wordCount   = str_word_count($contentText);
            $minutes     = max(3, min(15, (int)ceil($wordCount / 220)));
            $readingStr  = $minutes . ' min';
            $url = site_url('blog/' . $slug);

            $class = $isFeatured ? 'blog-card--featured' : 'blog-card--standard';

            return '
                <article class="blog-card ' . $class . '" onclick="window.location.href=\'' . esc($url) . '\'">
                    <div class="blog-card__inner">
                        <div class="blog-card__badge">Guía técnica</div>
                        <h3 class="blog-card__title">' . esc($title) . '</h3>
                        <p class="blog-card__excerpt">' . esc($excerpt) . '</p>
                        <div class="blog-card__footer">
                            <span class="blog-card__meta">' . esc($dateStr) . ' · ' . esc($readingStr) . ' lectura</span>
                            <span class="blog-card__cta">Leer artículo →</span>
                        </div>
                    </div>
                </article>';
        };

        ob_start();
        ?>
        <div class="blog-grid-redesign">
            <?php if ($page === 1): ?>
                <div class="blog-grid__featured">
                    <?= $build($posts[0], true) ?>
                </div>
                <div class="blog-grid__others">
                    <?php for ($i = 1; $i < count($posts); $i++): ?>
                        <?= $build($posts[$i]) ?>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <div class="blog-grid__others">
                    <?php foreach ($posts as $post): ?>
                        <?= $build($post) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- PAGINACIÓN UI -->
        <?php if ($totalPages > 1): ?>
            <div class="blog-pagination">
                <?php if ($page > 1): ?>
                    <button type="button" class="blog-pagination__btn" data-page="<?= $page - 1 ?>">← Anterior</button>
                <?php endif; ?>

                <div class="blog-pagination__pages">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <span class="blog-pagination__page active"><?= $p ?></span>
                        <?php elseif ($p <= 3 || $p > $totalPages - 2 || abs($p - $page) <= 1): ?>
                            <button type="button" class="blog-pagination__page" data-page="<?= $p ?>"><?= $p ?></button>
                        <?php elseif ($p == 4 || $p == $totalPages - 2): ?>
                            <span class="blog-pagination__dots">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <button type="button" class="blog-pagination__btn" data-page="<?= $page + 1 ?>">Siguiente →</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php
        $html = trim(ob_get_clean());

        return $this->response->setJSON([
            'ok'          => true,
            'html'        => $html,
            'currentPage' => $page,
            'totalPages'  => $totalPages
        ]);
    }







}
