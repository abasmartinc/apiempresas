<?php

if (!function_exists('calculateCompanySeoScore')) {
    /**
     * Calcula un score de calidad SEO basado en los datos disponibles.
     * Score máximo aproximado: 9
     */
    function calculateCompanySeoScore(array $company): int
    {
        $score = 0;

        // Función interna para validar si un campo tiene contenido real
        $isValid = function ($value) {
            if ($value === null) return false;
            $v = trim((string)$value);
            $invalidValues = ['', '-', '00 DESCONOCIDA'];
            return !in_array(strtoupper($v), $invalidValues);
        };

        // 1. Nombre (+1)
        if ($isValid($company['name'] ?? $company['nombre'] ?? null)) {
            $score += 1;
        }

        // 2. CIF (+1)
        if ($isValid($company['cif'] ?? $company['nif'] ?? null)) {
            $score += 1;
        }

        // 3. CNAE válido (+2)
        // Buscamos tanto el código como el label
        $cnae = $company['cnae'] ?? $company['cnae_code'] ?? null;
        if ($isValid($cnae)) {
            $score += 2;
        }

        // 4. Provincia (+1)
        if ($isValid($company['province'] ?? $company['provincia'] ?? null)) {
            $score += 1;
        }

        // 5. Objeto Social (+2)
        if ($isValid($company['corporate_purpose'] ?? $company['objeto_social'] ?? null)) {
            $score += 2;
        }

        // 6. Descripción generada/meta (+2)
        // Usualmente este campo se genera dinámicamente, pero si ya viene prefijado lo sumamos
        if ($isValid($company['meta_description'] ?? $company['description'] ?? null)) {
            $score += 2;
        }

        return $score;
    }
}

if (!function_exists('shouldIndexCompany')) {
    /**
     * Determina si una empresa debe ser indexada.
     * Regla: Score >= 5 -> true
     */
    function shouldIndexCompany(array $company): bool
    {
        return calculateCompanySeoScore($company) >= 5;
    }
}
