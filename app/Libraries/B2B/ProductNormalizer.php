<?php
namespace App\Libraries\B2B;

class ProductNormalizer {
    public static function normalize(string $product): string {
        $product = trim($product);
        $product = mb_strtolower($product, 'UTF-8');
        $product = preg_replace('/\s+/', ' ', $product);
        return $product;
    }
    public static function hash(string $normalized): string {
        return hash('sha256', $normalized);
    }
}