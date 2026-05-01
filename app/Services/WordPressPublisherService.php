<?php

namespace App\Services;

class WordPressPublisherService
{
    protected $apiUrl;
    protected $username;
    protected $password;
    protected $defaultCategoryId;

    public function __construct()
    {
        $this->apiUrl            = env('WP_BLOG_API_URL');
        $this->username          = env('WP_BLOG_USERNAME');
        // WordPress Application Passwords work without spaces; CI4 may include surrounding quotes
        $rawPassword             = env('WP_BLOG_APP_PASSWORD', '');
        $this->password          = str_replace(['"', "'", ' '], '', $rawPassword);
        $this->defaultCategoryId = (int) env('WP_BLOG_DEFAULT_CATEGORY_ID', 29);
    }

    /**
     * Publishes a post to WordPress.
     * 
     * @param array $data [title, content, slug, meta_description, category_id]
     * @return array|null [wordpress_post_id, wordpress_url] or null on failure.
     */
    public function publish(array $data): ?array
    {
        if (empty($this->apiUrl) || empty($this->username) || empty($this->password)) {
            log_message('error', 'WordPress credentials or API URL missing in .env');
            return null;
        }

        $payload = [
            'title'      => $data['title'],
            'content'    => $data['content'],
            'status'     => 'publish',
            'slug'       => $data['slug'],
            'excerpt'    => $data['meta_description'] ?? '',
            'categories' => [$data['category_id'] ?? $this->defaultCategoryId],
        ];

        $ch = curl_init($this->apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POSTREDIR      => 3,
            // Pasar credenciales directamente a cURL (evita que Apache elimine el header Authorization)
            CURLOPT_USERPWD        => $this->username . ':' . $this->password,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'User-Agent: APIEmpresas-Publisher/1.0',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', "WordPress cURL Error: " . $error);
            return [
                'error' => true,
                'message' => "Error de conexión (cURL): " . $error
            ];
        }

        curl_close($ch);

        // Detectar si es HTML (posible página de error o login)
        if (strpos($contentType, 'text/html') !== false) {
            log_message('error', "WordPress devolvió HTML en lugar de JSON. Response: " . substr($response, 0, 500));
            return [
                'error' => true,
                'message' => "El servidor devolvió una página HTML (posible firewall o error 404/500 camuflado)."
            ];
        }

        $decoded = json_decode($response, true);
        
        if ($httpCode === 201 && isset($decoded['id'])) {
            return [
                'wordpress_post_id' => $decoded['id'],
                'wordpress_url'     => $decoded['link'],
            ];
        }

        // Si llegamos aquí es que no se creó el post (no es 201)
        $errorMessage = $decoded['message'] ?? 'Error desconocido';
        
        // Si el código es 200, es que WordPress nos devolvió otra cosa (posiblemente una lista de posts o HTML)
        if ($httpCode === 200) {
            $errorMessage = "El servidor devolvió 200 OK pero NO creó el post. Comprueba que el usuario tiene permisos de editor.";
        }
        
        $errorCode = $decoded['code'] ?? 'no_code';
        log_message('error', "WordPress API Error ({$httpCode}): {$errorMessage} | Response: " . substr($response, 0, 500));
        
        return [
            'error'   => true,
            'message' => "WordPress ({$httpCode}): {$errorMessage}",
            'code'    => $errorCode
        ];
    }
}
