<?php

namespace App\Controllers;


class Documentation extends BaseController
{
    public function index()
    {
        return $this->renderView('documentation', [
            'title'       => 'Documentación de la API de empresas: endpoints y ejemplos | APIEmpresas',
            'excerptText' => 'Documentación de la API REST de empresas españolas: consulta por CIF, búsqueda por nombre, verificación KYB, administradores, BORME y contratos públicos. Ejemplos en curl, PHP, Node.js y Python.',
        ]);
    }

    public function english()
    {
        $data = [
            'lang'        => 'en-US',
            'locale'      => 'en_US',
            'title'       => 'Spain Company API Documentation | APIEmpresas.es',
            'excerptText' => 'Official REST API documentation for Spanish company data. Look up companies by CIF/NIF, search by name, get BORME history, commercial scoring, webhooks and more.',
            'canonical'   => site_url('documentation/en'),
        ];
        return $this->renderView('documentation_en', $data);
    }

    public function englishStandalone()
    {
        $data = [
            'lang'        => 'en-US',
            'locale'      => 'en_US',
            'title'       => 'Spanish Company API Documentation: Endpoints & Examples | SpainCompanyAPI',
            'excerptText' => 'REST API documentation for Spanish company data: lookup by CIF, name search, KYB verification, directors, BORME filings and public contracts. Examples in curl, PHP, Node.js and Python.',
            'canonical'   => site_url('docs'),
        ];
        return $this->renderView('documentation_en_standalone', $data);
    }

    public function error($errorCode)
    {
        $data = [
            'errorCode' => $errorCode,
            'title'     => 'Error: ' . strtoupper($errorCode)
        ];
        return $this->renderView('docs_error', $data);
    }
}

