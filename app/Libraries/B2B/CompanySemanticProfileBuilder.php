<?php
namespace App\Libraries\B2B;

class CompanySemanticProfileBuilder {
    public static function build(array $company): string {
        $cnae = $company['cnae_label'] ?? '';
        $obj = $company['objeto_social'] ?? '';
        
        $text = "CNAE: $cnae. Objeto social: $obj.";
        return trim(preg_replace('/\s+/', ' ', $text));
    }
    
    public static function hash(string $text): string {
        return hash('sha256', $text);
    }
}