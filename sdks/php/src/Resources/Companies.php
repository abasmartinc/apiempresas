<?php

namespace ApiEmpresas\Resources;

use ApiEmpresas\ApiEmpresas;

class Companies
{
    private ApiEmpresas $client;

    public function __construct(ApiEmpresas $client)
    {
        $this->client = $client;
    }

    /**
     * Obtiene los datos básicos de una empresa por su CIF.
     */
    public function get(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies?$params");
        return $response['data'] ?? [];
    }

    /**
     * Busca empresas por nombre o razón social.
     */
    public function search(string $q): array
    {
        $params = http_build_query(['q' => $q]);
        $response = $this->client->request('GET', "/companies/search?$params");
        return $response['data'] ?? [];
    }

    /**
     * Consulta múltiple de CIFs en una sola petición.
     */
    public function batch(array $cifs): array
    {
        $response = $this->client->request('POST', '/companies/batch', [
            'cifs' => $cifs
        ]);
        return [
            'meta' => $response['meta'] ?? [],
            'data' => $response['data'] ?? []
        ];
    }

    /**
     * (Pro) Obtiene el Scoring Comercial de una empresa.
     */
    public function score(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/score?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Pro) Obtiene el historial de actos del BORME de una empresa.
     */
    public function borme(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/borme?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Pro) Obtiene las señales societarias recientes de una empresa.
     */
    public function signals(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/signals?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Business) Obtiene Insights IA de una empresa.
     */
    public function insights(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/insights?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Pro) Obtiene datos de contacto y preparación de la empresa.
     */
    public function contactPrep(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/contact-prep?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Business) Obtiene la información del Radar de empresas.
     */
    public function radar(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/radar?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Business) Realiza un match avanzado con los datos de una empresa.
     */
    public function match(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/match?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Business) Obtiene la red o entramado societario (Network) de una empresa.
     */
    public function network(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/network?$params");
        return $response['data'] ?? [];
    }
}
