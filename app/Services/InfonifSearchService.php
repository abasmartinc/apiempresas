<?php

namespace App\Services;

use Config\Database;

class InfonifSearchService
{
    protected string $csrfToken;
    protected $db;

    public function __construct()
    {
        $this->csrfToken = env('INFONIF_CSRF_TOKEN') ?: '';
        $this->db = Database::connect();
    }

    /**
     * Searches Infonif API by name. If found and match is highly probable, returns the CIF.
     * Caches negative results for 24h.
     * 
     * @param string $name
     * @return string|null CIF if found and matched, otherwise null
     */
    public function searchForCif(string $name): ?string
    {
        $name = trim($name);
        if (empty($name) || empty($this->csrfToken)) {
            return null;
        }

        $queryName = $this->cleanNameForSearch($name);
        if (empty($queryName)) {
            $queryName = $name;
        }

        // Check negative cache
        $cache = \Config\Services::cache();
        $cacheKey = 'infonif_not_found_' . md5(strtolower($queryName));
        if ($cache->get($cacheKey)) {
            log_message('debug', "[InfonifSearchService] Bypassed name '$name': marked as not found in cache.");
            return null;
        }

        log_message('info', "[InfonifSearchService] Starting name search for: '$name' (cleaned: '$queryName')");

        $url = "https://infonif.economia3.com/api/buscador/buscar.asp?q=" . urlencode($queryName) . "&CSRF=" . urlencode($this->csrfToken);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Max 5 seconds
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "X-Requested-With: XMLHttpRequest",
            "Referer: https://infonif.economia3.com/"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            log_message('error', "[InfonifSearchService] HTTP Error. Code: $httpCode. Error: $error");
            // Don't cache negative on transient errors like 503
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data['empresas'])) {
            log_message('warning', "[InfonifSearchService] No results for name: '$name'");
            $cache->save($cacheKey, true, 86400); // 24h cache
            return null;
        }

        // Find best match
        $bestMatch = null;
        foreach ($data['empresas'] as $emp) {
            $resultName = $emp['rs'] ?? $emp['n'] ?? $emp['nombre'] ?? '';
            if (empty($resultName)) {
                continue;
            }

            if ($this->isSameCompany($name, $resultName)) {
                $bestMatch = $emp;
                break; // Take the first solid match
            }
        }

        if (!$bestMatch || empty($bestMatch['nif'])) {
            log_message('warning', "[InfonifSearchService] No solid match found among " . count($data['empresas']) . " results for name: '$name'");
            $cache->save($cacheKey, true, 86400); // 24h cache
            return null;
        }

        $cif = strtoupper(trim($bestMatch['nif']));
        $apiName = $bestMatch['rs'] ?? $bestMatch['n'] ?? $bestMatch['nombre'] ?? $name;

        // Check if CIF already exists in DB
        $existing = $this->db->table('companies')->select('id')->where('cif', $cif)->get()->getRowArray();

        if (!$existing) {
            helper('text');
            $slug = url_title($this->cleanNameForSearch($apiName) ?: $apiName, '-', true);

            $dataToInsert = [
                'company_name'       => $apiName,
                'cif'                => $cif,
                'slug'               => $slug,
                'registro_mercantil' => $bestMatch['p'] ?? $bestMatch['r'] ?? null,
                'municipality'       => $bestMatch['loc'] ?? null,
                'address'            => $bestMatch['dir'] ?? null,
                'postal_code'        => $bestMatch['cp'] ?? null,
                'estado'             => 'Activa',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            // Some tables might not have postal_code, we use ignore or try/catch if column missing, 
            // but the python script checked for postal_code. Let's just insert it and if it fails, fallback without it.
            try {
                $this->db->table('companies')->insert($dataToInsert);
                log_message('info', "[InfonifSearchService] Inserted basic data for '$name' -> CIF: $cif");
            } catch (\Exception $e) {
                unset($dataToInsert['postal_code']); // Fallback if postal_code doesn't exist
                $this->db->table('companies')->insert($dataToInsert);
                log_message('info', "[InfonifSearchService] Inserted basic data (no postal_code) for '$name' -> CIF: $cif");
            }
        } else {
            log_message('info', "[InfonifSearchService] Found match for '$name' -> CIF: $cif (Already in DB)");
        }
        
        return $cif;
    }

    /**
     * Cleans the name for a better search query.
     */
    protected function cleanNameForSearch(string $name): string
    {
        // Remove content in parentheses
        $name = preg_replace('/\(.*?\)/', '', $name);

        $suffixes = [
            '/\bSOCIEDAD LIMITADA UNIPERSONAL\b/i',
            '/\bSOCIEDAD ANONIMA UNIPERSONAL\b/i',
            '/\bSOCIEDAD LIMITADA\b/i',
            '/\bSOCIEDAD ANONIMA\b/i',
            '/\bS\.L\.U\.\b/i',
            '/\bS\.A\.U\.\b/i',
            '/\bS\.L\.\b/i',
            '/\bS\.A\.\b/i',
            '/\bSLU\b/i',
            '/\bSAU\b/i',
            '/\bSL\b/i',
            '/\bSA\b/i',
        ];

        foreach ($suffixes as $suffix) {
            $name = preg_replace($suffix, '', $name);
        }

        // Clean extra spaces
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Normalizes a string for comparison.
     */
    protected function normalizeForComparison(string $name): string
    {
        $name = strtolower($name);
        $name = $this->cleanNameForSearch($name);
        $name = strtolower($name);
        // Keep only alphanumeric chars
        return preg_replace('/[^a-z0-9]/', '', $name);
    }

    /**
     * Checks if original query matches the result name closely enough.
     */
    protected function isSameCompany(string $original, string $target): bool
    {
        $normOrig = $this->normalizeForComparison($original);
        $normTarget = $this->normalizeForComparison($target);

        if (empty($normOrig) || empty($normTarget)) {
            return false;
        }

        // Substring match is generally safe since we removed all common suffixes
        return (strpos($normTarget, $normOrig) !== false) || (strpos($normOrig, $normTarget) !== false);
    }
}
