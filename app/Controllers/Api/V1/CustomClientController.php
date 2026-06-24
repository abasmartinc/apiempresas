<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;

class CustomClientController extends BaseApiController
{
    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        helper(['api', 'company']);
    }

    public function companies(string $clientSlug)
    {
        $cif = trim((string) $this->request->getGet('cif'));

        if ($cif === '') {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'VALIDATION_ERROR',
                    'message' => 'El parámetro "cif" es obligatorio.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Limpieza de formato
        $cif = strtoupper($cif);
        $cif = preg_replace('/^ES/', '', $cif); 
        $cif = preg_replace('/[^A-Z0-9]/', '', $cif);

        if (!is_valid_cif($cif)) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'INVALID_CIF_FORMAT',
                    'message' => 'El CIF proporcionado no tiene un formato válido.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Resolve Transformer based on slug
        $transformerClass = "\\App\\Services\\Api\\Transformers\\" . ucfirst($clientSlug) . "Transformer";
        if (!class_exists($transformerClass)) {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'CLIENT_NOT_FOUND',
                    'message' => 'No se ha encontrado una configuración para el cliente: ' . $clientSlug
                ],
                ResponseInterface::HTTP_NOT_FOUND
            );
        }

        /** @var \App\Services\Api\Transformers\ClientTransformerInterface $transformer */
        $transformer = new $transformerClass();

        // Check if we have it in standard cache
        $cacheKey = 'company_by_cif_' . md5(mb_strtolower($cif, 'UTF-8'));
        $companyData = cache($cacheKey);

        if (!is_array($companyData) || empty($companyData)) {
            try {
                $companyData = $this->companyModel->getByCif($cif);
                if (!$companyData) {
                    return $this->respond(
                        [
                            'success' => false,
                            'error'   => 'COMPANY_NOT_FOUND',
                            'message' => 'Empresa no encontrada.'
                        ],
                        ResponseInterface::HTTP_NOT_FOUND
                    );
                }
                cache()->save($cacheKey, $companyData, 86400); // 24h
            } catch (\Throwable $e) {
                log_message('error', '[CustomClientController::companies] ' . $e->getMessage());
                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'SERVER_ERROR',
                        'message' => 'Error interno al consultar la empresa.'
                    ],
                    ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        }

        // Apply filtering (remove internal/unused fields like the standard endpoint does)
        $companyData = filter_company_data($companyData);

        // Apply Transformer
        try {
            $customData = $transformer->transform($companyData);
        } catch (\Throwable $e) {
            log_message('error', '[CustomClientController::transform] ' . $e->getMessage());
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'TRANSFORM_ERROR',
                    'message' => 'Error al transformar los datos para el cliente.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->respond(
            [
                'success' => true,
                'data'    => $customData,
            ],
            ResponseInterface::HTTP_OK
        );
    }
}
