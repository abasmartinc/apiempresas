<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;

class CompaniesSearch extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

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

        // Cache interno por query normalizada (1h)
        $cacheKey = 'company_search_best_' . md5(mb_strtolower($name, 'UTF-8'));
        $cachedData = cache($cacheKey);

        if (is_array($cachedData) && !empty($cachedData)) {
            return $this->respond(
                [
                    'success' => true,
                    'data'    => $cachedData,
                ],
                ResponseInterface::HTTP_OK
            );
        }

        try {
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

            // Guardar SOLO data en cache
            cache()->save($cacheKey, $data, 3600);

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $data,
                ],
                ResponseInterface::HTTP_OK
            );
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
