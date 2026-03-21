<?php
use App\Models\RadarPriceModel;

if (!function_exists('calculate_radar_price')) {
    /**
     * Calcula el precio dinámico según la escala de volumen
     * Escala:
     * 1 – 10: 2€
     * 11 – 50: 4€
     * 51 – 150: 7€
     * 151 – 500: 9€
     * 501 – 1000: 12€
     * 1001 – 2000: 15€
     * 2001 – 5000: 20€
     * 5000+: 25€
     */
    function calculate_radar_price(int $count): array
    {
        try {
            $model = new RadarPriceModel();
            $basePrice = $model->getBasePrice($count);
        } catch (\Exception $e) {
            // Fallback hardcoded logic if database is unavailable
            if ($count <= 10) {
                $basePrice = 2.00;
            } elseif ($count <= 50) {
                $basePrice = 4.00;
            } elseif ($count <= 150) {
                $basePrice = 7.00;
            } elseif ($count <= 500) {
                $basePrice = 9.00;
            } elseif ($count <= 1000) {
                $basePrice = 12.00;
            } elseif ($count <= 2000) {
                $basePrice = 15.00;
            } elseif ($count <= 5000) {
                $basePrice = 20.00;
            } else {
                $basePrice = 25.00;
            }
        }

        $tax = round($basePrice * 0.21, 2);

        return [
            'base_price' => $basePrice,
            'tax'        => $tax,
            'total'      => $basePrice + $tax
        ];
    }
}
