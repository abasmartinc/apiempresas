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

        // Restringir a 60 peticiones por minuto por IP (60 peticiones cada 60 segundos)
        // Puedes ajustar estos valores según necesites
        if ($throttler->check(md5($request->getIPAddress()), 60, 60) === false) {
            return Services::response()->setStatusCode(429)->setBody('Too Many Requests');
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
