<?php

/**
 * Genera la URL correcta para una empresa.
 * Si tiene CIF válido: /CIF-slug
 * Si no tiene CIF válido: /slug
 * 
 * @param array $company Array con datos de la empresa (debe tener 'cif' y 'name' o 'company_name')
 * @return string URL completa de la empresa
 */
function company_url(array $company): string
{
    $cif = trim($company['cif'] ?? '');
    $name = $company['name'] ?? $company['company_name'] ?? '';
    
    // Validar si el CIF es válido (formato: Letra + 7 dígitos + Letra/Dígito)
    $isValidCif = preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $cif);
    
    // Generar slug del nombre
    helper('text');
    $slug = url_title($name, '-', true);
    
    if ($isValidCif) {
        // CIF válido: usar formato /CIF-slug
        return site_url($cif . ($slug ? ('-' . $slug) : ''));
    } else {
        // Sin CIF válido: usar solo /slug
        return site_url($slug);
    }
}

/**
 * Verifica si un CIF es válido
 * 
 * @param string|null $cif
 * @return bool
 */
function is_valid_cif(?string $cif): bool
{
    if (empty($cif)) {
        return false;
    }
    
    $cif = trim($cif);
    
    // Verificar formato: Letra + 7 dígitos + Letra/Dígito
    if (!preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $cif)) {
        return false;
    }
    
    // Verificar que no sea un valor placeholder
    $invalidValues = ['no disponible', 'nodisponible', 'n/a', 'na', 'sin cif'];
    if (in_array(strtolower($cif), $invalidValues)) {
        return false;
    }
    
    return true;
}
