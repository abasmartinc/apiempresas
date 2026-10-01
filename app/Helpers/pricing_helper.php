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

        /*
         * TOPE DE PRECIO, SIN "REBAJA".
         *
         * Antes, al llegar al tope se enseñaba tachado el precio sin tope (hasta 259 €)
         * con "Precio original" y "Con descuento". Ese precio no se había cobrado
         * nunca, y anunciar una rebaja exige que el precio tachado sea el más bajo
         * aplicado en los 30 días anteriores (art. 20 LOCM, directiva Ómnibus) y no
         * engañar sobre el precio (Ley de Competencia Desleal, también entre empresas).
         *
         * Ahora no hay descuento: `is_discounted` es siempre false y `original_price`
         * es el precio real, así que ninguna vista tacha nada. Lo que se comunica es
         * un hecho cierto: a partir del tope, el precio no sube (`precio_maximo`).
         */
        $maxCap = 149.00;
        $precioMaximo = false;
        if ($basePrice >= $maxCap) {
            $basePrice = $maxCap;
            $precioMaximo = true;
        }

        $tax = round($basePrice * 0.21, 2);

        return [
            'base_price'     => $basePrice,
            'original_price' => $basePrice,
            'is_discounted'  => false,
            'precio_maximo'  => $precioMaximo,
            'tope'           => $maxCap,
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
