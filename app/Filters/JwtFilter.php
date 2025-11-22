<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Config\Services;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = getenv('JWT_SECRET');
        $authHeader = $request->getServer('HTTP_AUTHORIZATION');

        if (!$authHeader) {
            return Services::response()->setJSON(['message' => 'Token not provided'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = explode(' ', $authHeader)[1];

        try {
            // Decodificar el JWT usando la clave y el algoritmo especificados
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            // Añadir el token decodificado a la solicitud para su uso en el controlador
            $request->decoded = (array) $decoded;
        } catch (\Exception $e) {
            return Services::response()->setJSON(['message' => 'Invalid token', 'error' => $e->getMessage()])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita implementación después
    }
}
