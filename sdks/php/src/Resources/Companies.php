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
    public function get(string $cif, array $options = []): array
    {
        $query = ['cif' => $cif];
        if (!empty($options['admin'])) {
            $query['admin'] = 'true';
        }
        $params = http_build_query($query);
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
     * Búsqueda con varios resultados (multiple=true). Devuelve ['data' => [...], 'meta' => [...]];
     * para la página siguiente, pasa meta['next_cursor'] como 'cursor'.
     * Opciones: limit, page, cursor.
     */
    public function searchMultiple(string $q, array $options = []): array
    {
        $query = ['q' => $q, 'multiple' => 'true'];
        foreach (['limit', 'page', 'cursor'] as $k) {
            if (isset($options[$k])) {
                $query[$k] = $options[$k];
            }
        }
        $params = http_build_query($query);
        $response = $this->client->request('GET', "/companies/search?$params");
        return [
            'data' => $response['data'] ?? [],
            'meta' => $response['meta'] ?? [],
        ];
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
    public function radar($cifOrOptions = []): array
    {
        // Compatibilidad: antes se pasaba un CIF, que el Radar no usa. Ahora se pasan
        // los filtros: ['province' => ..., 'priority' => ..., 'range' => ...].
        $query = is_array($cifOrOptions)
            ? array_filter($cifOrOptions, static fn ($v) => $v !== null && $v !== '')
            : ['cif' => (string) $cifOrOptions];
        $params = http_build_query($query);
        $response = $this->client->request('GET', "/companies/radar?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Business) Realiza un match avanzado con los datos de una empresa.
     */
    public function match(string $cif, ?string $sellerSector = null): array
    {
        // seller_sector es obligatorio en la API: sin él responde 400.
        $query = ['cif' => $cif];
        if ($sellerSector !== null && $sellerSector !== '') {
            $query['seller_sector'] = $sellerSector;
        }
        $params = http_build_query($query);
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

    /**
     * (Business) Obtiene los contratos públicos y licitaciones adjudicadas a una empresa.
     */
    public function contracts(string $cif, int $page = 1, int $limit = 20): array
    {
        $params = http_build_query([
            'cif' => $cif,
            'page' => $page,
            'limit' => $limit,
        ]);
        $response = $this->client->request('GET', "/companies/contracts?$params");
        return $response['data'] ?? [];
    }

    /**
     * (Business) Obtiene el perfil de riesgo corporativo y solvencia de una empresa.
     */
    public function riskProfile(string $cif): array
    {
        $params = http_build_query(['cif' => $cif]);
        $response = $this->client->request('GET', "/companies/risk-profile?$params");
        return $response['data'] ?? [];
    }
}

