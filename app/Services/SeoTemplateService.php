<?php
namespace App\Services;

use Config\SectorContent;

class SeoTemplateService
{
    protected $sectorConfig;

    public function __construct()
    {
        $this->sectorConfig = new SectorContent();
    }

    public function replacePlaceholders(string $content, array $variables): string
    {
        // 1. Enriquecer contenido si no tiene los nuevos tags
        if (strpos($content, '{{sector_needs}}') === false) {
            $content = $this->enrichContent($content, $variables);
        }

        // 2. Reemplazo estándar
        $search = [];
        $replace = [];

        foreach ($variables as $key => $value) {
            $search[] = '{{' . $key . '}}';
            $replace[] = $value ?? '';
        }

        $content = str_replace($search, $replace, $content);
        
        // 3. Segunda pasada para placeholders anidados (ej: intro_variant que contiene {{sector_label}})
        $content = str_replace($search, $replace, $content);

        return $content;
    }

    /**
     * Inserta un bloque dinámico automáticamente.
     */
    protected function enrichContent(string $content, array $variables): string
    {
        $intro = '<p>{{intro_variant}}</p>' . "\n";
        
        $block = "\n" . '<h2>Qué necesitan las nuevas empresas de {{sector_label}} en {{provincia}}</h2>' . "\n" .
                 '<p>Las nuevas empresas de {{sector_label}} en {{provincia}} suelen necesitar {{sector_needs}}. Esto representa una oportunidad estratégica para proveedores que buscan detectar negocios en fase inicial.</p>' . "\n";

        if (!empty($variables['total_empresas']) && $variables['total_empresas'] !== '0') {
            $block .= '<p>Actualmente se han detectado {{total_empresas}} nuevas empresas de {{sector_label}} en {{provincia}}.</p>' . "\n";
        }

        $block .= '<p>Entre los servicios más demandados en este sector destacan {{sector_services}}.</p>' . "\n" .
                  '<p>Uno de los principales retos de estas empresas es {{sector_pain}}. Por eso, detectar en una etapa temprana puede marcar la diferencia frente a la competencia.</p>' . "\n" .
                  '<p>{{province_context}}</p>' . "\n";

        // Prepend intro
        $content = $intro . $content;

        // Intentamos insertar el bloque después del primer párrafo (que ahora es el intro si no existía)
        $pos = strpos($content, '</p>');
        if ($pos !== false) {
            return substr_replace($content, $block, $pos + 4, 0);
        }

        return $block . $content;
    }




    /**
     * Resuelve todas las variables dinámicas necesarias para el SEO.
     */
    public function resolveVariables(array $data, array $context = []): array
    {
        $province = $data['province'] ?? 'España';
        if (strtoupper($province) === 'ESPANA' || strtoupper($province) === 'ESPAÑA') {
            $province = 'España';
        }
        $sector = $data['sector_label'] ?? ($data['sector']['label'] ?? '');
        $total = $data['total_context_count'] ?? 0;
        $period = $data['period'] ?? 'mes';
        
        $provinceSlug = $context['province_slug'] ?? null;
        $sectorSlug = $context['sector_slug'] ?? null;
        
        $urlRadar = site_url('empresas-nuevas');
        if ($provinceSlug && $sectorSlug) {
            $urlRadar = site_url("empresas-nuevas/{$sectorSlug}-en-{$provinceSlug}");
        } elseif ($provinceSlug) {
            $urlRadar = site_url("empresas-nuevas/{$provinceSlug}");
        } elseif ($sectorSlug) {
             $urlRadar = site_url("empresas-nuevas-sector/{$sectorSlug}");
        }

        $vars = [
            'provincia'          => $province,
            'sector'             => $sector,
            'total_empresas'     => number_format($total, 0, ',', '.'),
            'fecha_actualizacion'=> date('d/m/Y'),
            'periodo'            => $period,
            'periodo_texto'      => $this->getPeriodText($period),
            'url_destino'        => $data['canonical'] ?? '',
            'url_radar'          => $urlRadar,
            'keyword_principal'  => $this->generateKeyword($province, $sector, $period),
            'cta_radar'          => '<a href="' . $urlRadar . '" class="btn btn-primary">Ver todas las nuevas empresas de ' . $province . '</a>',
        ];

        // --- ENRIQUECIMIENTO POR SECTOR ---
        $sectorKey = $sectorSlug ?? 'default';
        $sectorData = $this->sectorConfig->sectors[$sectorKey] ?? $this->sectorConfig->sectors['default'];

        $vars['sector_label']       = $sectorData['label'] === '{{sector}}' ? $sector : $sectorData['label'];
        $vars['sector_needs']       = $sectorData['needs'];
        $vars['sector_services']    = $sectorData['services'];
        $vars['sector_pain']        = $sectorData['pain'];
        $vars['sector_opportunity'] = $sectorData['opportunity'];
        $vars['sector_buyer_intent']= $sectorData['buyer_intent'];

        // --- CONTEXTO PROVINCIAL ---
        $provinceUpper = mb_strtoupper($province, 'UTF-8');
        $provinceCtx = $this->sectorConfig->provinceContext[$provinceUpper] ?? $this->sectorConfig->provinceContext['default'];
        $vars['province_context'] = str_replace('{{provincia}}', $province, $provinceCtx);

        // --- VARIACIÓN SEMÁNTICA ESTABLE ---
        $slugForHash = ($sectorSlug ?? '') . '-' . ($provinceSlug ?? '');
        $index = abs(crc32($slugForHash)) % count($this->sectorConfig->introVariants);
        $vars['intro_variant'] = $this->sectorConfig->introVariants[$index];


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
