<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;
use OpenApi\Attributes as OA;

class CompaniesSearch extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        helper('api');
    }

    #[OA\Get(
        path: "/api/v1/companies/search",
        summary: "Búsqueda de Empresas",
        description: "Busca empresas por nombre o CIF. Permite búsqueda exacta o paginada. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 1 crédito del monedero (bono prepago). Las respuestas con error (400, 404, etc.) no consumen cuota ni créditos.",
        tags: ["1. Plan Free / General"]
    )]
    #[OA\Parameter(
        name: "q",
        in: "query",
        required: true,
        description: "Nombre o CIF de la empresa a buscar",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "multiple",
        in: "query",
        required: false,
        description: "Si es true, devuelve una lista paginada de coincidencias. Si es false, devuelve la mejor coincidencia.",
        schema: new OA\Schema(type: "boolean", default: false)
    )]
    #[OA\Parameter(
        name: "limit",
        in: "query",
        required: false,
        description: "Número máximo de resultados por página (solo si multiple=true). Máximo 100.",
        schema: new OA\Schema(type: "integer", default: 20)
    )]
    #[OA\Parameter(
        name: "page",
        in: "query",
        required: false,
        description: "Número de página (solo si multiple=true).",
        schema: new OA\Schema(type: "integer", default: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Resultados de la búsqueda",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
            ]
        )
    )]
    public function index()
    {
        // Acepta name= o q=
        $name = trim((string) $this->request->getGet('name'));
        if ($name === '') {
            $name = trim((string) $this->request->getGet('q'));
        }

        if ($name === '') {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'VALIDATION_ERROR',
                    'message' => 'El parámetro "name" (o "q") es obligatorio.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $multiple = $this->request->getGet('multiple') !== null ? filter_var($this->request->getGet('multiple'), FILTER_VALIDATE_BOOLEAN) : false;
        $limitParam = $this->request->getGet('limit');
        $limit = $limitParam !== null ? (int)$limitParam : 20;

        // Hard Limit para seguridad (previene OOM errors)
        if ($limit <= 0 || $limit > 100) {
            $limit = 100;
        }

        $pageParam = $this->request->getGet('page');
        $page = $pageParam !== null ? max(1, (int)$pageParam) : 1;

        // Cache interno por query normalizada (1h)
        if ($multiple) {
            $cacheKey = 'company_search_mult_v4_' . $limit . '_p' . $page . '_' . md5(mb_strtolower($name, 'UTF-8'));
        } else {
            $cacheKey = 'company_search_best_' . md5(mb_strtolower($name, 'UTF-8'));
        }
        $cachedData = cache($cacheKey);

        if (is_array($cachedData) && !empty($cachedData)) {
            // Retrocompatibilidad con la caché antigua (antes del formato con meta)
            $isOldFormat = $multiple && !isset($cachedData['data']);
            $items = $multiple ? ($isOldFormat ? $cachedData : $cachedData['data']) : $cachedData;

            // Apply masking if Free plan
            $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
            if ((int)$planId === 1) {
                if ($multiple) {
                    foreach ($items as &$item) {
                        $item = mask_company_data($item);
                    }
                } else {
                    $items = mask_company_data($items);
                }
            }

            // Apply filtering (remove requested fields)
            if ($multiple) {
                foreach ($items as &$item) {
                    $item = filter_company_data($item);
                }
            } else {
                $items = filter_company_data($items);
            }

            $response = [
                'success' => true,
                'data'    => $items,
            ];
            
            if ($multiple && !$isOldFormat && isset($cachedData['meta'])) {
                $response['meta'] = $cachedData['meta'];
            }

            return $this->respond($response, ResponseInterface::HTTP_OK);
        }

        try {
            if ($multiple) {
                // Pasamos true como cuarto argumento para $returnMeta
                $results = $this->companyModel->searchMany($name, $limit, $page, true);

                if (empty($results['data'])) {
                    return $this->respond(
                        [
                            'success' => false,
                            'error'   => 'COMPANY_NOT_FOUND',
                            'message' => 'No se encontraron empresas similares al nombre indicado.'
                        ],
                        ResponseInterface::HTTP_NOT_FOUND
                    );
                }

                $dataToCache = $results; // array con 'data' y 'meta'
                $data = $results['data'];
            } else {
                $result = $this->companyModel->getBestByName($name);

                if (!$result) {
                    return $this->respond(
                        [
                            'success' => false,
                            'error'   => 'COMPANY_NOT_FOUND',
                            'message' => 'No se encontró ninguna empresa similar al nombre indicado.'
                        ],
                        ResponseInterface::HTTP_NOT_FOUND
                    );
                }

                // El modelo puede devolver ['data'=>..., 'meta'=>...]; aquí solo nos interesa data
                $data = $result['data'] ?? $result;
                $dataToCache = $data;
            }

            // Guardar en cache (para multiple guarda ['data'=>..., 'meta'=>...], para single guarda solo 'data')
            cache()->save($cacheKey, $dataToCache, 3600);

            // Apply masking if Free plan
            $planId = \App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1;
            if ((int)$planId === 1) {
                if ($multiple) {
                    foreach ($data as &$item) {
                        $item = mask_company_data($item);
                    }
                } else {
                    $data = mask_company_data($data);
                }
            }

            // Apply filtering (remove requested fields)
            if ($multiple) {
                foreach ($data as &$item) {
                    $item = filter_company_data($item);
                }
            } else {
                $data = filter_company_data($data);
            }

            $response = [
                'success' => true,
                'data'    => $data,
            ];
            
            if ($multiple && isset($results['meta'])) {
                $response['meta'] = $results['meta'];
            }

            return $this->respond($response, ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[CompaniesSearch::index] ' . $e->getMessage());

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al buscar la empresa.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
