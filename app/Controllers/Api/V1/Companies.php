<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;

class Companies extends ResourceController
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
        // 1) Leer parámetro CIF
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

        try {
            // 2) Llamar al modelo
            $company = $this->companyModel->getCompanyByCif($cif);

            // 3) Si no se encuentra, 404
            if (!$company) {
                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'COMPANY_NOT_FOUND',
                        'message' => 'No se encontró ninguna empresa con ese CIF.'
                    ],
                    ResponseInterface::HTTP_NOT_FOUND
                );
            }

            // 4) Devolver datos en formato estándar
            return $this->respond(
                [
                    'success' => true,
                    'data'    => $company,
                ],
                ResponseInterface::HTTP_OK
            );
        } catch (\Throwable $e) {
            // Loguear si quieres, y devolver error genérico
            log_message('error', '[Companies::index] ' . $e->getMessage());

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al consultar la empresa.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
