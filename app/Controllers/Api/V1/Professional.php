<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;

class Professional extends ResourceController
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

    /**
     * Step 1: Search / Autocomplete (Free)
     * GET /api/v1/professional/search?q=...
     */
    public function search()
    {
        $q = trim((string) $this->request->getGet('q'));

        if ($q === '') {
            return $this->respond([
                'success' => false,
                'error'   => 'VALIDATION_ERROR',
                'message' => 'El parámetro "q" es obligatorio.'
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            // Utilizamos searchMany para obtener hasta 20 coincidencias (estilo autocompletado)
            $results = $this->companyModel->searchMany($q, 20);

            // Formateamos la respuesta para que sea ligera (estilo buscador)
            $formatted = array_map(function($item) {
                return [
                    'name'    => $item['name'] ?? ($item['company_name'] ?? ''),
                    'cif'     => $item['cif'] ?? '',
                    'address' => $item['address'] ?? '',
                    'cnae'    => $item['cnae'] ?? '',
                ];
            }, $results);

            return $this->respond([
                'success' => true,
                'data'    => $formatted
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[Professional::search] ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'error'   => 'SERVER_ERROR',
                'message' => 'Error interno en la búsqueda.'
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Step 2: Company Details (Billed)
     * GET /api/v1/professional/details?cif=...
     */
    public function details()
    {
        $cif = trim((string) $this->request->getGet('cif'));

        if ($cif === '') {
            return $this->respond([
                'success' => false,
                'error'   => 'VALIDATION_ERROR',
                'message' => 'El parámetro "cif" es obligatorio.'
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $company = $this->companyModel->getByCif($cif);

            if (!$company) {
                return $this->respond([
                    'success' => false,
                    'error'   => 'NOT_FOUND',
                    'message' => 'No se encontró la empresa con el CIF proporcionado.'
                ], ResponseInterface::HTTP_NOT_FOUND);
            }

            // Nota: El ApiKeyFilter se encargará de registrar el consumo en el after() 
            // ya que este endpoint NO está en la lista de excepciones (skipBilling).

            return $this->respond([
                'success' => true,
                'data'    => $company
            ], ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', '[Professional::details] ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'error'   => 'SERVER_ERROR',
                'message' => 'Error al consultar los detalles.'
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
