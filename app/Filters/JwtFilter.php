<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = getenv('JWT_SECRET');

        // DEBUG: loguear headers y variables de servidor
        log_message('debug', 'HEADERS: ' . print_r($request->getHeaders(), true));
        log_message('debug', 'SERVER HTTP_AUTHORIZATION: ' . print_r($request->getServer('HTTP_AUTHORIZATION'), true));

        // Intento 1: cabecera normal
        $authHeader = $request->getHeaderLine('Authorization');

        // Intento 2: algunas configuraciones la exponen solo así
        if (!$authHeader) {
            $authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? '';
        }

        if (!$authHeader) {
            return Services::response()
                ->setJSON(['message' => 'Token not provided'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (stripos($authHeader, 'Bearer ') !== 0) {
            return Services::response()
                ->setJSON(['message' => 'Invalid Authorization header format', 'raw' => $authHeader])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = trim(substr($authHeader, 7));

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $request->user = (array) $decoded;
        } catch (\Exception $e) {
            return Services::response()
                ->setJSON(['message' => 'Invalid token', 'error' => $e->getMessage()])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }


    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada que hacer después
    }
}

