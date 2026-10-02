<?php

if (!function_exists('calculate_core_price')) {
    /**
     * Core pricing logic to ensure consistency across Radar and Directory.
     * Math:
     * - First 1,000 companies: 9€
     * - From 1,001 to 10,000: +0.005€ per company (5€ per 1,000), rounded up to the euro
     * - Over 10,000: +0.001€ per company (1€ per 1,000), rounded up to the euro
     * - Cap: 149€
     * - Premium multiplier (Recent data): x1.5
     */
    function calculate_core_price(int $count, bool $isPremium): array
    {
        // Precio por tramos SUAVES (02-10-2026). Antes cada bloque de 1.000 empresas
        // empezado se cobraba entero: de 1.000 a 1.001 empresas el precio saltaba de 9 €
        // a 14 €. Ahora sube 1 € cada 200 empresas hasta 10.000 y 1 € cada 1.000 después.
        // En los múltiplos de 1.000 cuesta lo mismo que antes y nunca cuesta más.
        // Se calcula en milésimas de euro (enteros) y se redondea al euro hacia arriba.
        $milesimas = 9000;
        if ($count > 1000) {
            $extra      = $count - 1000;
            $milesimas += min($extra, 9000) * 5;      // 5 € por cada 1.000 hasta 10.000
            if ($extra > 9000) {
                $milesimas += ($extra - 9000) * 1;    // 1 € por cada 1.000 a partir de 10.000
            }
        }
        $basePrice = (float) intdiv($milesimas + 999, 1000);

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
