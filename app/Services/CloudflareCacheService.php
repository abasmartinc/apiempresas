<?php

namespace App\Services;

/**
 * Vaciado de la caché de Cloudflare de fichas de empresa (29-09-2026).
 *
 * Mismo mecanismo que Admin\RgpdController::execute(): API purge_cache por URL, con
 * CLOUDFLARE_ZONE_ID y CLOUDFLARE_API_TOKEN del .env. La ficha se cachea 24 h en
 * Cloudflare (s-maxage=86400), así que sin esto un dato corregido tarda un día en verse.
 */
class CloudflareCacheService
{
    /**
     * URL pública de la ficha, igual que la construye el RGPD:
     * https://apiempresas.es/{CIF}-{slug}
     */
    public function companyUrls(string $cif, string $name): array
    {
        $cif  = trim($cif);
        $name = trim($name);
        if ($cif === '' || $name === '') {
            return [];
        }
        $slug = (new \App\Models\CompanyModel())->generateSlug($name);
        return ['https://apiempresas.es/' . $cif . '-' . $slug];
    }

    /** Vacía la caché de la ficha de una empresa por su id. */
    public function purgeCompany(int $companyId): bool
    {
        $c = \Config\Database::connect()->table('companies')
            ->select('cif, company_name')->where('id', $companyId)->get()->getRowArray();
        if (!$c) {
            return false;
        }
        return $this->purgeUrls($this->companyUrls((string) $c['cif'], (string) $c['company_name']));
    }

    /**
     * true solo si Cloudflare confirma el vaciado de todas las URLs.
     */
    public function purgeUrls(array $urls): bool
    {
        $urls = array_values(array_unique(array_filter($urls)));
        if (!$urls) {
            return false;
        }

        $zoneId = env('CLOUDFLARE_ZONE_ID');
        $token  = env('CLOUDFLARE_API_TOKEN');
        if (!$zoneId || !$token) {
            log_message('warning', '[Cloudflare] Sin CLOUDFLARE_ZONE_ID / CLOUDFLARE_API_TOKEN: no se vacía la caché');
            return false;
        }

        $client = \Config\Services::curlrequest();
        $ok = true;

        // La API admite 30 URLs por llamada.
        foreach (array_chunk($urls, 30) as $chunk) {
            try {
                $res = $client->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type'  => 'application/json',
                    ],
                    'json'        => ['files' => $chunk],
                    'http_errors' => false,
                    'timeout'     => 5,
                ]);
                $body = json_decode((string) $res->getBody(), true);
                if (empty($body['success'])) {
                    $ok = false;
                    log_message('error', '[Cloudflare] purge_cache falló (' . $res->getStatusCode() . '): ' . substr((string) $res->getBody(), 0, 500));
                }
            } catch (\Throwable $e) {
                $ok = false;
                log_message('error', '[Cloudflare] Error en purge_cache: ' . $e->getMessage());
            }
        }

        return $ok;
    }
}
