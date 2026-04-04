<?php

namespace App\Services;

class SeoTemplateService
{
    /**
     * Reemplaza los placeholders en el contenido HTML.
     */
    public function replacePlaceholders(string $content, array $variables): string
    {
        $search = [];
        $replace = [];

        foreach ($variables as $key => $value) {
            $search[] = '{{' . $key . '}}';
            $replace[] = $value ?? '';
        }

        return str_replace($search, $replace, $content);
    }

    /**
     * Resuelve todas las variables dinámicas necesarias para el SEO.
     */
    public function resolveVariables(array $data): array
    {
        $province = $data['province'] ?? 'España';
        if (strtoupper($province) === 'ESPANA' || strtoupper($province) === 'ESPAÑA') {
            $province = 'España';
        }
        $sector = $data['sector_label'] ?? ($data['sector']['label'] ?? '');
        $total = $data['total_context_count'] ?? 0;
        $period = $data['period'] ?? 'mes';
        
        $vars = [
            'provincia'          => $province,
            'sector'             => $sector,
            'total_empresas'     => number_format($total, 0, ',', '.'),
            'fecha_actualizacion'=> date('d/m/Y'),
            'periodo'            => $period,
            'periodo_texto'      => $this->getPeriodText($period),
            'url_destino'        => $data['canonical'] ?? '',
            'keyword_principal'  => $this->generateKeyword($province, $sector, $period),
            'cta_radar'          => '<a href="' . site_url('register') . '" class="btn btn-primary">Ver todas las nuevas empresas de ' . $province . '</a>',
        ];

        // Top 3 sectores (si es página de provincia)
        if (!empty($data['top_sectors'])) {
            $topSectors = array_slice($data['top_sectors'], 0, 3);
            $sectorLabels = array_column($topSectors, 'cnae_label');
            $vars['top_3_sectores'] = implode(', ', $sectorLabels);
            
            if (!empty($topSectors[0])) {
                $vars['sector_top'] = $topSectors[0]['cnae_label'];
                $vars['sector_top_total'] = number_format($topSectors[0]['total'], 0, ',', '.');
            }
        }

        // Provincias top (si es página de sector)
        if (!empty($data['top_provinces'])) {
            $topProvinces = array_slice($data['top_provinces'], 0, 3);
            $provinceLabels = array_column($topProvinces, 'provincia');
            $vars['provincias_top'] = implode(', ', $provinceLabels);
        }

        return $vars;
    }

    /**
     * Slugifica un texto manteniendo los placeholders {{provincia}} y {{sector}}.
     */
    public function slugifyWithPlaceholders(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        
        // Transliteración manual básica para español y caracteres comunes
        $search  = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'];
        $replace = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'u', 'n'];
        $text = str_replace($search, $replace, $text);

        // Protegemos los placeholders para que no se pierdan en el slugify
        // Usamos palabras seguras que no tengan caracteres especiales
        $text = str_replace(
            ['{{provincia}}', '{{sector}}'], 
            ['placeholderprovincia', 'placeholdersector'], 
            $text
        );
        
        // Eliminamos caracteres especiales excepto guiones y espacios
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        // Convertimos espacios y guiones múltiples en un solo guión
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        // Restauramos los placeholders
        return str_replace(
            ['placeholderprovincia', 'placeholdersector'], 
            ['{{provincia}}', '{{sector}}'], 
            $text
        );
    }

    /**
     * Convierte un slug de plantilla en una expresión regular para capturar variables.
     * Ejemplo: "nuevas-empresas-en-{{provincia}}" -> "/^nuevas-empresas-en-(?P<provincia>.+)$/i"
     */
    public function templateToPattern(string $templateSlug): string
    {
        // Escapamos el slug pero respetamos los placeholders protegidos
        $escaped = preg_quote($templateSlug, '/');
        
        $pattern = str_replace(
            [preg_quote('{{provincia}}', '/'), preg_quote('{{sector}}', '/')],
            ['(?P<provincia>[a-z0-9-]+)', '(?P<sector>[a-z0-9-]+)'],
            $escaped
        );

        // Flexibilidad: Permitimos variaciones comunes como "este-mes-de" incluso si no están en el título de WP
        // RESTRICCIÓN: Solo permitimos palabras de tiempo específicas, no cualquier secuencia de palabras.
        $pattern = str_replace(
            ['\-de\-', '\-en\-'],
            ['\-(?:(?:este\-mes|esta\-semana|hoy)\-)?de\-', '\-(?:(?:este\-mes|esta\-semana|hoy)\-)?en\-'],
            $pattern
        );
        
        return '/^' . $pattern . '$/i';
    }

    protected function getPeriodText(string $period): string
    {
        $map = [
            'hoy'     => 'hoy',
            'semana'  => 'esta semana',
            'mes'     => 'este mes',
            'general' => 'últimamente',
        ];
        return $map[$period] ?? 'este mes';
    }

    protected function generateKeyword(?string $province, ?string $sector, string $period): string
    {
        $p = ($province && strtolower($province) !== 'españa') ? " en {$province}" : "";
        $s = $sector ? " de {$sector}" : "";
        
        if ($sector && $province) {
            return "empresas nuevas de {$sector} en {$province}";
        }
        if ($sector) {
            return "empresas nuevas de {$sector}";
        }
        if ($province) {
            return "empresas nuevas en {$province}";
        }
        
        return "empresas nuevas";
    }
}
