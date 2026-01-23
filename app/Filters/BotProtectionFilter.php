<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\BotDetectionService;
use Config\Services;

class BotProtectionFilter implements FilterInterface
{
    /**
     * Check if the requesting IP is blocked before processing the request
     *
     * @param RequestInterface $request
     * @param mixed|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $botService = new BotDetectionService();
        $ip = $botService->getRealIp($request);
        
        // Check if IP is blocked
        if ($botService->isIpBlocked($ip)) {
            // Record this blocked attempt
            $botService->recordBlockedAttempt($ip, [
                'route'      => $request->getUri()->getPath(),
                'method'     => $request->getMethod(),
                'user_agent' => $request->getUserAgent() ? $request->getUserAgent()->getAgentString() : '',
            ]);
            
            // Return 403 Forbidden response
            $response = Services::response();
            $response->setStatusCode(403);
            
            // Check if it's an AJAX request to return JSON
            if ($request->isAJAX()) {
                $response->setJSON([
                    'success' => false,
                    'error'   => 'ACCESS_DENIED',
                    'message' => 'Tu IP ha sido bloqueada por actividad sospechosa. Contacta con soporte si crees que es un error.',
                ]);
            } else {
                // For non-AJAX requests, return HTML error page
                $response->setBody(view('errors/html/error_403', [
                    'message' => 'Tu IP ha sido bloqueada por actividad sospechosa. Contacta con soporte si crees que es un error.',
                ]));
            }
            
            return $response;
        }
        
        // IP is not blocked, continue with the request
        return null;
    }

    /**
     * After filter (not used for bot protection)
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param mixed|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the request
        return null;
    }
}

