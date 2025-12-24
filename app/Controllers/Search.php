<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;

class Search extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * JSON API:
     * GET /search?cif=B12345678
     */
    public function index()
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

        try {
            $company = $this->companyModel->getCompanyByCif($cif);

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

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $company,
                ],
                ResponseInterface::HTTP_OK
            );
        } catch (\Throwable $e) {
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

    /**
     * HTML (SEO-friendly):
     * GET /search_company?q=B12345678
     * GET /search_company
     *
     * Recomendación SEO: noindex,follow (búsqueda interna)
     */
    public function search_company()
    {
        $q = trim((string) $this->request->getGet('q'));

        $data = [
            'q'           => $q,
            'company'     => null,
            'errorMsg'    => null,

            // Variables que tu partial/head ya soporta
            'title'       => $q ? ("Buscar empresa: {$q} | APIEmpresas.es") : 'Buscar empresa | APIEmpresas.es',
            'excerptText' => 'Busca empresas por CIF o nombre comercial. Resultados trazables con fuentes oficiales y salida por API.',
            'canonical'   => site_url('search_company') . ($q ? ('?q=' . rawurlencode($q)) : ''),
            'robots'      => 'noindex,follow',
        ];

        // SSR: si viene q, intentamos buscar y renderizar HTML ya con resultado
        if ($q !== '') {
            try {
                $company = $this->companyModel->getCompanyByCif($q);

                if (is_object($company)) {
                    $company = (array) $company;
                }

                if (!$company) {
                    $data['errorMsg'] = 'No se encontró ninguna empresa con ese CIF.';
                } else {
                    $data['company'] = $company;
                }
            } catch (\Throwable $e) {
                $data['errorMsg'] = 'Se ha producido un error interno al consultar la empresa.';
            }
        }


        return view('search', $data);
    }

    /**
     * POST /search_company (form)
     * Redirige a GET shareable: /search_company?q=...
     */
    public function search_company_post()
    {
        $q = trim((string) $this->request->getPost('q'));

        $url = site_url('search_company') . ($q ? ('?q=' . rawurlencode($q)) : '');

        // 303: después de POST, pasa a GET (patrón recomendado)
        return redirect()->to($url)->setStatusCode(303);
    }
}
