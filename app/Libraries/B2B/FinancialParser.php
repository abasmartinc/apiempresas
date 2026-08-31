<?php
namespace App\Libraries\B2B;

class FinancialParser {
    public static function parse(?string $value): ?float {
        if ($value === null || trim($value) === '' || trim($value) === '-' || trim(strtoupper($value)) === 'N/D') return null;
        if (trim($value) === '0' || trim($value) === '0,00' || trim($value) === '0.00') return 0.0;
        $v = preg_replace('/[^0-9,.-]/', '', $value);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        $floatVal = (float)$v;
        if ($floatVal === 0.0 && !str_contains($value, '0')) return null;
        return $floatVal;
    }
}