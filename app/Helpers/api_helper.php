<?php

if (!function_exists('mask_company_data')) {
    /**
     * Obscures sensitive company data for Free plan users to incentivize Pro upgrades.
     * 
     * @param array $data The original company data
     * @return array The masked company data
     */
    function mask_company_data(array $data): array
    {
        // 2. Mask Address detail
        if (!empty($data['address'])) {
            $data['address'] = "*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]";
        }

        // 3. Mask Corporate Purpose (truncate and add upgrade message)
        if (!empty($data['corporate_purpose'])) {
            $limit = 100; // Show first 100 chars
            if (mb_strlen($data['corporate_purpose']) > $limit) {
                $data['corporate_purpose'] = mb_substr($data['corporate_purpose'], 0, $limit) . "... [ACTUALIZA A PRO PARA VER EL DETALLE COMPLETO]";
            }
        }

        // 4. Remove technical fields
        unset($data['lat'], $data['lng']);

        // 5. CIF -> The user sample shows it UNMASKED. 
        // We will keep it as is (removing the previous masking logic).

        return $data;
    }
}

if (!function_exists('filter_company_data')) {
    /**
     * Filters company data to remove specific fields and conditional null fields.
     * 
     * @param array $data The original company data
     * @return array The filtered company data
     */
    function filter_company_data(array $data): array
    {
        // Fields to ALWAYS remove
        $toRemove = [
            'id',
            'phone',
            'phone_mobile',
            'website_official',
            'email',
            'phone_enriched',
            'phone_mobile_enriched'
        ];

        foreach ($toRemove as $field) {
            unset($data[$field]);
        }

        // Fields to remove ONLY IF they are null
        if (array_key_exists('cnae_2025', $data) && $data['cnae_2025'] === null) {
            unset($data['cnae_2025']);
        }
        if (array_key_exists('cnae_2025_label', $data) && $data['cnae_2025_label'] === null) {
            unset($data['cnae_2025_label']);
        }

        return $data;
    }
}

if (!function_exists('get_free_plan_limit')) {
    /**
     * Gets the monthly quota for the free plan from the database.
     * 
     * @return int The free plan limit
     */
    function get_free_plan_limit(): int
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'api_free_plan_limit_v1';
        $limit = $cache->get($cacheKey);

        if ($limit === null) {
            try {
                $db = \Config\Database::connect();
                $freePlan = $db->table('api_plans')
                              ->where('slug', 'free')
                              ->get()
                              ->getRow();
                
                $limit = $freePlan ? (int)$freePlan->monthly_quota : 30; // Fallback to 30
                
                // Cache for 24 hours (it rarely changes)
                $cache->save($cacheKey, $limit, 86400);
            } catch (\Exception $e) {
                return 30; // Global fallback
            }
        }

        return (int)$limit;
    }
}
