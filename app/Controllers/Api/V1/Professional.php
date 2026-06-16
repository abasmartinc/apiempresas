<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use OpenApi\Attributes as OA;

class Professional extends BaseApiController
{


    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        helper('api');
    }

    /**
     * Step 1: Search / Autocomplete (Free)
     * GET /api/v1/professional/search?q=...
     */
    #[OA\Get(
        path: "/api/v1/professional/search",
        summary: "Búsqueda Autocompletado Profesional",
        description: "Devuelve coincidencias ligeras para autocompletado en búsquedas.",
        tags: ["2. Plan Professional"]
    )]
    #[OA\Parameter(
        name: "q",
        in: "query",
        required: true,
        description: "Término de búsqueda",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Resultados del autocompletado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
            ]
        )
    )]
    public function search()
    {
        $q = trim((string) $this->request->getGet('q'));

        if ($q === '') {
            return $this->respond([
                'success' => false,
                'error' => 'VALIDATION_ERROR',
                'message' => 'El parámetro "q" es obligatorio.'
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Ignorar búsquedas muy cortas por rendimiento (autocompletado)
        if (mb_strlen($q, 'UTF-8') < 3) {
            return $this->respond([
                'success' => true,
                'data' => []
            ], ResponseInterface::HTTP_OK);
        }
        
        $cacheKey = 'prof_search_' . md5($q);
        if ($cached = cache()->get($cacheKey)) {
            return $this->respond([
                'success' => true,
                'data' => $cached
            ], ResponseInterface::HTTP_OK);
        }

        try {
            // Utilizamos searchMany para obtener hasta 20 coincidencias (estilo autocompletado)
            $results = $this->companyModel->searchMany($q, 20);

            // Formateamos la respuesta para que sea ligera (estilo buscador)
            $formatted = array_map(function ($item) {
                $data = [
                    'name' => $item['name'] ?? ($item['company_name'] ?? ''),
                    'cif' => $item['cif'] ?? '',
                    'address' => $item['address'] ?? '',
                    'cnae' => $item['cnae'] ?? '',
                ];

                return $data;
            }, $results);

            cache()->save($cacheKey, $formatted, 3600); // Guardar por 1 hora

            return $this->respond([
                'success' => true,
                'data' => $formatted
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[Professional::search] ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'error' => 'SERVER_ERROR',
                'message' => 'Error interno en la búsqueda.'
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 2: Company Details (Billed)
     * GET /api/v1/professional/details?cif=...
     */
    #[OA\Get(
        path: "/api/v1/professional/details",
        summary: "Detalles Profesionales de Empresa",
        description: "Devuelve los datos profesionales de una empresa basados en su CIF.",
        tags: ["2. Plan Professional"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "El CIF de la empresa",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Detalles completos de la empresa",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function details()
    {
        $cif = trim((string) $this->request->getGet('cif'));

        if ($cif === '') {
            return $this->respond([
                'success' => false,
                'error' => 'VALIDATION_ERROR',
                'message' => 'El parámetro "cif" es obligatorio.'
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $company = $this->companyModel->getByCif($cif);

            if (!$company) {
                return $this->respond([
                    'success' => false,
                    'error' => 'NOT_FOUND',
                    'message' => 'No se encontró la empresa con el CIF proporcionado.'
                ], ResponseInterface::HTTP_NOT_FOUND);
            }


            // Apply filtering (remove requested fields like 'id', 'phone', etc.)
            $company = filter_company_data($company);

            return $this->respond([
                'success' => true,
                'data' => $company
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[Professional::details] ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'error' => 'SERVER_ERROR',
                'message' => 'Error al consultar los detalles.'
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
