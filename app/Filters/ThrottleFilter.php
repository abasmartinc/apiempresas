<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ThrottleFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();
        
        $uri = $request->getUri()->getPath();
        $ip = $request->getIPAddress();
        
        // --- LÍMITES ESTRICTOS (Exportaciones y Administradores) ---
        // 1 petición por minuto
        if (strpos($uri, 'empresa/export') !== false) {
            if ($throttler->check(md5($ip . '_export'), 1, 60) === false) {
                return Services::response()->setStatusCode(429)->setBody('Too Many Requests (Exports)');
            }
        }

        // 5 peticiones por minuto
        if (strpos($uri, 'administrador/') !== false) {
            if ($throttler->check(md5($ip . '_admin'), 5, 60) === false) {
                return Services::response()->setStatusCode(429)->setBody('Too Many Requests (Admin)');
            }
        }

        // --- LÍMITES GENERALES (Separación API vs WEB) ---
        $path = ltrim($uri, '/');
        if ($path === 'api/v1' || strpos($path, 'api/v1/') === 0) {
            // La API comercial autenticada (/api/v1/*) se autolimita por API Key en ApiKeyFilter (2 o 20 RPS)
            return;
        }

        if (strpos($path, 'api/') === 0) {
            // Límite para el resto de la API (sandbox, anónima): 120 peticiones por minuto
            if ($throttler->check(md5($ip . '_api'), 120, 60) === false) {
                return Services::response()->setStatusCode(429)->setBody('Too Many Requests (API)');
            }
        } else {
            // --- WEB PÚBLICA (Separación Usuario vs Rastreadores en Rutas SEO) ---
            $method = strtoupper((string) $request->getMethod());
            $isReadMethod = in_array($method, ['GET', 'HEAD'], true);

            $userAgent = strtolower($request->getServer('HTTP_USER_AGENT') ?? '');
            $isCrawlerUserAgent = (
                strpos($userAgent, 'googlebot') !== false || 
                strpos($userAgent, 'google-inspectiontool') !== false ||
                strpos($userAgent, 'googleother') !== false ||
                strpos($userAgent, 'bingbot') !== false ||
                strpos($userAgent, 'yandexbot') !== false
            );

            // Whitelist estricta de rutas de contenido público indexable
            $isSeoPublicPath = (
                $path === '' ||
                $path === 'robots.txt' ||
                (preg_match('/^sitemap(?:-(?:static|blog|directories|informes-(?:provincias|sectores|wp)|subvenciones|contratos|(?:en-)?companies-\d+|holdings-\d+|ai-ready-\d+))?\.xml$/i', $path) === 1) ||
                (preg_match('/^[a-zA-Z][0-9]{7}[a-zA-Z0-9].*$/', $path) === 1) ||
                (preg_match('/^empresa\/\d+(?:-.*)?$/', $path) === 1) ||
                strpos($path, 'informacion-empresa/') === 0 ||
                strpos($path, 'grupos-empresariales/') === 0 ||
                $path === 'listado-de-grupos-empresariales' ||
                $path === 'listado-de-empresas' ||
                strpos($path, 'listado-de-empresas/') === 0 ||
                strpos($path, 'directorios-de-empresas/') === 0 ||
                strpos($path, 'directorio-de-empresas/') === 0 ||
                $path === 'empresas-nuevas' ||
                strpos($path, 'empresas-nuevas/') === 0 ||
                strpos($path, 'empresas-nuevas-') === 0 ||
                (preg_match('/^empresas-.+-en-.+$/', $path) === 1) ||
                strpos($path, 'empresas/') === 0 ||
                $path === 'licitaciones-del-estado' ||
                strpos($path, 'licitaciones-del-estado/') === 0 ||
                $path === 'subvenciones-empresas' ||
                strpos($path, 'subvenciones-empresas/') === 0 ||
                $path === 'mayores-empresas-contratistas-del-estado' ||
                $path === 'empresas-mas-subvencionadas-espana' ||
                strpos($path, 'informes/') === 0 ||
                $path === 'base-de-datos-de-empresas' ||
                $path === 'blog' ||
                strpos($path, 'blog/') === 0 ||
                in_array($path, ['docs', 'documentation', 'documentation/en', 'api-docs'], true) ||
                in_array($path, ['planes/free', 'planes/pro', 'planes/business'], true) ||
                in_array($path, ['api-empresas', 'spanish-company-api', 'spanish-company-data-api', 'leads-empresas-nuevas', 'radar-demo', 'copilot-pro', 'autocompletado-cif-empresas', 'plugin-wordpress-buscador-empresas'], true)
            );

            if ($isReadMethod && $isCrawlerUserAgent && $isSeoPublicPath) {
                // Límite acotado para rastreo de contenido SEO público (GET/HEAD): 300 peticiones por minuto
                if ($throttler->check(md5($ip . '_crawler'), 300, 60) === false) {
                    return Services::response()->setStatusCode(429)->setBody('Too Many Requests (Crawler)');
                }
            } else {
                // Límite para el resto de la web (búsqueda, auth, tracking, account, escritura): 60 peticiones por minuto
                if ($throttler->check(md5($ip . '_web'), 60, 60) === false) {
                    return Services::response()->setStatusCode(429)->setBody('Too Many Requests (Web)');
                }
            }
        }
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the request
    }
}
