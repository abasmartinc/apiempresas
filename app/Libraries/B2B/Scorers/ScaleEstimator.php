<?php
namespace App\Libraries\B2B\Scorers;

use App\Libraries\B2B\FinancialParser;

class ScaleEstimator {
    public static function estimate(array $company): array {
        $ventas = FinancialParser::parse($company['ventas_raw'] ?? null);
        $capital = FinancialParser::parse($company['capital_social_raw'] ?? null);
        
        if ($ventas !== null) {
            return ['estimated_scale' => self::getBucket($ventas), 'confidence' => 0.9, 'evidence' => 'ventas', 'value' => $ventas];
        }
        
        if ($capital !== null) {
            return ['estimated_scale' => self::getBucket($capital * 10), 'confidence' => 0.4, 'evidence' => 'capital', 'value' => $capital];
        }
        
        return ['estimated_scale' => 'unknown', 'confidence' => 0.0, 'evidence' => 'none', 'value' => null];
    }
    
    private static function getBucket(float $val): string {
        if ($val < 50000) return 'micro';
        if ($val < 500000) return 'small';
        if ($val < 5000000) return 'medium';
        return 'large';
    }
}