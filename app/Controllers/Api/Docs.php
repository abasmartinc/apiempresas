<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use OpenApi\Generator;
use OpenApi\Attributes as OA;

#[OA\Info(title: "API Empresas", version: "1.0.0", description: "Documentación oficial de la API de Empresas. Soporte nativo para el estándar de errores RFC 7807 (Problem Details).")]
#[OA\Server(url: "/", description: "Entorno de Producción (Consume Créditos)")]
#[OA\Server(url: "/api/sandbox/v1", description: "Entorno Sandbox de Pruebas (Gratis. Usa CIF: A15075062)")]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-API-KEY",
    description: "Introduce tu API Key privada aquí para autenticarte y poder probar los endpoints en tiempo real."
)]
#[OA\OpenApi(
    security: [["ApiKeyAuth" => []]]
)]
class Docs extends Controller
{
    public function index()
    {
        helper('radar');
        return view('api/swagger');
    }

    public function json()
    {
        // En producción, se recomienda cachear este resultado
        $generator = new Generator();
        $openapi = $generator->generate([APPPATH . 'Controllers/Api']);
        return $this->response->setJSON($openapi->toJson());
    }
}
