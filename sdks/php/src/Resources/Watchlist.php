<?php

namespace ApiEmpresas\Resources;

use ApiEmpresas\ApiEmpresas;

/**
 * (Pro / Business) Vigilancia de empresas: 100 en Pro, 1.000 en Business.
 * Ninguna de estas llamadas consume consultas.
 */
class Watchlist
{
    private ApiEmpresas $client;

    public function __construct(ApiEmpresas $client)
    {
        $this->client = $client;
    }

    /**
     * Empresas vigiladas. Opciones: page, limit. Devuelve ['data' => [...], 'meta' => [...]].
     */
    public function list(array $options = []): array
    {
        $query = [];
        foreach (['page', 'limit'] as $k) {
            if (isset($options[$k])) {
                $query[$k] = $options[$k];
            }
        }
        $qs = $query ? '?' . http_build_query($query) : '';
        $response = $this->client->request('GET', '/watchlist' . $qs);
        return ['data' => $response['data'] ?? [], 'meta' => $response['meta'] ?? []];
    }

    /**
     * Añade uno o varios CIF. data: added, already_watching, not_found, invalid, rejected_over_limit.
     * Si llegas al límite del plan, la respuesta trae también message (y upgrade_url en Pro).
     *
     * @param string|string[] $cifs
     */
    public function add($cifs): array
    {
        $response = $this->client->request('POST', '/watchlist', ['cifs' => array_values((array) $cifs)]);
        $out = ['data' => $response['data'] ?? [], 'meta' => $response['meta'] ?? []];
        foreach (['message', 'upgrade_url'] as $k) {
            if (isset($response[$k])) {
                $out[$k] = $response[$k];
            }
        }
        return $out;
    }

    /** Quita una empresa de la vigilancia. */
    public function remove(string $cif): array
    {
        $response = $this->client->request('DELETE', '/watchlist/' . rawurlencode($cif));
        return $response['data'] ?? [];
    }

    /**
     * Cambios en las empresas vigiladas (borme_act, status_change, risk_level_change).
     * Opciones: since (YYYY-MM-DD), types (array o texto separado por comas), cif, page, limit.
     */
    public function events(array $options = []): array
    {
        $query = [];
        foreach (['since', 'cif', 'page', 'limit'] as $k) {
            if (isset($options[$k]) && $options[$k] !== '') {
                $query[$k] = $options[$k];
            }
        }
        if (!empty($options['types'])) {
            $query['types'] = is_array($options['types']) ? implode(',', $options['types']) : (string) $options['types'];
        }
        $qs = $query ? '?' . http_build_query($query) : '';
        $response = $this->client->request('GET', '/watchlist/events' . $qs);
        return ['data' => $response['data'] ?? [], 'meta' => $response['meta'] ?? []];
    }
}
