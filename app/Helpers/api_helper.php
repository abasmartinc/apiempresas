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
        // 1. Mask Phone Numbers (show only prefix and last 2 digits)
        if (!empty($data['phone'])) {
            $data['phone'] = mask_string($data['phone'], 3, 2);
        }
        if (!empty($data['phone_mobile'])) {
            $data['phone_mobile'] = mask_string($data['phone_mobile'], 3, 2);
        }

        // 2. Mask Address detail (keep municipality and province, hide street)
        if (!empty($data['address'])) {
            // Usually addresses are like "C/ STREET NAME, 12, MADRID"
            // We want to hide "STREET NAME, 12" and keep "MADRID" and "(MADRID)"
            // Simplest approach: mask first 60% of the string but keep it looks like an address
            $len = mb_strlen($data['address']);
            $maskLen = (int) ($len * 0.7);
            if ($maskLen > 0) {
                $data['address'] = str_repeat('*', $maskLen) . mb_substr($data['address'], $maskLen);
            }
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

        return $data;
    }
}

if (!function_exists('mask_string')) {
    /**
     * Helper to mask a string keeping some chars at the start and end.
     */
    function mask_string($str, $startVisible = 3, $endVisible = 2)
    {
        $str = (string) $str;
        $len = mb_strlen($str);
        if ($len <= ($startVisible + $endVisible)) {
            return str_repeat('*', $len);
        }
        
        $start = mb_substr($str, 0, $startVisible);
        $end = mb_substr($str, -$endVisible);
        $mask = str_repeat('*', $len - $startVisible - $endVisible);
        
        return $start . $mask . $end;
    }
}
