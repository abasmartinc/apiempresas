<?php
namespace App\Libraries\B2B\Scorers;

class CompanyRiskEvaluator {
    public static function evaluate(array $company): array {
        $status = strtolower($company['estado'] ?? '');
        
        if (str_contains($status, 'extint') || str_contains($status, 'baja definitiva')) {
            return ['level' => 'hard', 'reason' => 'Empresa extinta', 'confidence' => 0.95];
        }
        if (str_contains($status, 'disolucion') || str_contains($status, 'concursal')) {
            return ['level' => 'strong', 'reason' => 'Riesgo legal/financiero alto', 'confidence' => 0.90];
        }
        if (str_contains($status, 'cierre')) {
            return ['level' => 'soft', 'reason' => 'Cierre registral', 'confidence' => 0.90];
        }
        
        return ['level' => 'none', 'reason' => null, 'confidence' => 0.95];
    }
}