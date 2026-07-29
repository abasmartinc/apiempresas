<?php

namespace App\Controllers;


class Documentation extends BaseController
{
    public function index()
    {
        return $this->renderView('documentation');
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
            'title'       => 'Spain Company API Documentation',
            'excerptText' => 'Official REST API documentation for Spanish company data.',
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

