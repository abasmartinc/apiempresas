<?php

if (!function_exists('calculateCompanySeoScore')) {
    /**
     * Calcula un score de calidad SEO basado en los datos disponibles.
     * Score máximo aproximado: 9
     */
    function calculateCompanySeoScore(array $company): int
    {
        $score = 0;

        $isValid = function ($value) {
            if ($value === null) return false;
            $v = trim((string)$value);
            return !in_array(strtoupper($v), ['', '-', '00 DESCONOCIDA', 'NULL', 'UNDEFINED']);
        };

        // 1. Identificación (+2)
        if ($isValid($company['name'] ?? null)) $score += 1;
        if ($isValid($company['cif'] ?? null)) $score += 1;

        // 2. Datos Geográficos y Actividad (+2)
        if ($isValid($company['province'] ?? null)) $score += 1;
        if ($isValid($company['cnae'] ?? null)) $score += 1;

        // 3. Objeto Social (+2) - Factor de peso
        if ($isValid($company['corporate_purpose'] ?? null)) $score += 2;

        // 4. Administradores (+2) - Factor de calidad humana
        if (!empty($company['num_admins']) && (int)$company['num_admins'] > 0) {
            $score += 2;
        }

        // 5. Historial BORME (+1)
        if (!empty($company['num_borme_posts']) && (int)$company['num_borme_posts'] > 0) {
            $score += 1;
        }

        // 6. Bonus Empresa Nueva (+1)
        $name = $company['name'] ?? '';
        if (strpos($name, '2024') !== false || strpos($name, '2025') !== false) {
            $score += 1;
        }

        return $score;
    }
}

if (!function_exists('shouldIndexCompany')) {
    /**
     * Determina si una empresa debe ser indexada.
     * Subimos el umbral a 6 para ser más estrictos con el contenido.
     */
    function shouldIndexCompany(array $company): bool
    {
        return calculateCompanySeoScore($company) >= 6;
    }
}
