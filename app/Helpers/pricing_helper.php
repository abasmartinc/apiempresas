<?php

if (!function_exists('calculate_core_price')) {
    /**
     * Core pricing logic to ensure consistency across Radar and Directory.
     * Math:
     * - First 1,000 companies: 9€
     * - From 1,001 to 10,000: +5€ per 1,000
     * - Over 10,000: +1€ per 1,000
     * - Premium multiplier (Recent data): x1.5
     */
    function calculate_core_price(int $count, bool $isPremium): array
    {
        $basePrice = 9.00;

        if ($count > 1000) {
            $extraCount = $count - 1000;
            
            // Calculate tier 2: 1,001 to 10,000 (max 9 blocks of 1,000)
            $tier2Count = min($extraCount, 9000);
            $tier2Blocks = ceil($tier2Count / 1000);
            $basePrice += $tier2Blocks * 5.00;
            
            // Calculate tier 3: Over 10,000
            if ($extraCount > 9000) {
                $tier3Count = $extraCount - 9000;
                $tier3Blocks = ceil($tier3Count / 1000);
                $basePrice += $tier3Blocks * 1.00;
            }
        }

        if ($isPremium) {
            $basePrice = round($basePrice * 1.5, 2);
        }

        $originalPrice = $basePrice;
        $isDiscounted = false;
        
        $maxCap = 149.00;
        $maxDisplayOriginalPrice = 259.00;
        if ($basePrice > $maxCap) {
            $originalPrice = min($originalPrice, $maxDisplayOriginalPrice);
            $basePrice = $maxCap;
            $isDiscounted = true;
        }

        $tax = round($basePrice * 0.21, 2);

        return [
            'base_price'     => $basePrice,
            'original_price' => $originalPrice,
            'is_discounted'  => $isDiscounted,
            'tax'            => $tax,
            'total'          => $basePrice + $tax
        ];
    }
}

if (!function_exists('calculate_radar_price')) {
    /**
     * Calcula el precio dinámico para el radar (siempre es Premium)
     */
    function calculate_radar_price(int $count): array
    {
        return calculate_core_price($count, true);
    }
}

if (!function_exists('calculate_directory_price')) {
    /**
     * Calcula el precio dinámico para listas de directorios
     */
    function calculate_directory_price(int $count, bool $isPremium = false): array
    {
        return calculate_core_price($count, $isPremium);
    }
}
