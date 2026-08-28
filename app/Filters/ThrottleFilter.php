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
        
        // --- BYPASS PARA BOTS BUENOS (Google, Bing, etc.) ---
        $userAgent = strtolower($request->getServer('HTTP_USER_AGENT') ?? '');
        if (
            strpos($userAgent, 'googlebot') !== false || 
            strpos($userAgent, 'google-inspectiontool') !== false ||
            strpos($userAgent, 'googleother') !== false ||
            strpos($userAgent, 'bingbot') !== false ||
            strpos($userAgent, 'yandexbot') !== false
        ) {
            // Permitir paso libre a rastreadores de buscadores
            return;
        }

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
        if (strpos($uri, 'api/') === 0) {
            // Límite para la API: 120 peticiones por minuto
            if ($throttler->check(md5($ip . '_api'), 120, 60) === false) {
                return Services::response()->setStatusCode(429)->setBody('Too Many Requests (API)');
            }
        } else {
            // Límite para el resto de la web: 60 peticiones por minuto
            if ($throttler->check(md5($ip . '_web'), 60, 60) === false) {
                return Services::response()->setStatusCode(429)->setBody('Too Many Requests (Web)');
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
