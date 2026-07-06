<?php

namespace App\Services;

class RadarService
{
    /**
     * Resuelve un slug de sector a códigos CNAE y su etiqueta oficial.
     */
    public function resolveCnaeCodes($slug)
    {
        if (!$slug) return null;

        $db = \Config\Database::connect();
        
        // Formato legacy: 4121-construccion
        $parts = explode('-', $slug, 2);
        if (is_numeric($parts[0])) {
            $code = $parts[0];
            $row = $db->query("SELECT label_2009 as label FROM cnae_2009_2025 WHERE cnae_2009 = ? LIMIT 1", [$code])->getRowArray();
            if ($row) return ['codes' => [$code], 'label' => $this->normalizeLabel($row['label'])];
            return ['codes' => [$code], 'label' => 'Sector ' . $code];
        }

        $aliases = [
            'hosteleria' => ['codes' => ['55', '56'], 'label' => 'Hostelería, Restaurantes y Catering'],
            'restaurantes' => ['codes' => ['561'], 'label' => 'Restaurantes y Puestos de Comida'],
            'programacion' => ['codes' => ['62'], 'label' => 'Programación Informática'],
            'marketing' => ['codes' => ['731'], 'label' => 'Marketing y Publicidad'],
            'construccion' => ['codes' => ['41', '42', '43'], 'label' => 'Construcción e Inmobiliaria'],
            'transporte' => ['codes' => ['49', '50', '51', '52', '53'], 'label' => 'Transporte y Logística'],
            'logistica' => ['codes' => ['52'], 'label' => 'Logística y Almacenamiento'],
            'finanzas' => ['codes' => ['64', '65', '66'], 'label' => 'Seguros y Finanzas'],
            'inmobiliaria' => ['codes' => ['68'], 'label' => 'Actividades Inmobiliarias'],
            'sanidad' => ['codes' => ['86'], 'label' => 'Actividades Sanitarias'],
            'restauracion' => ['codes' => ['56'], 'label' => 'Hostelería y Restauración'],
        ];

        $clean = strtr(mb_strtolower($slug), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
        if (isset($aliases[$clean])) return $aliases[$clean];

        $searchTerm = str_replace('-', ' ', $slug);
        $row = $db->query("SELECT cnae_2009 as code, label_2009 as label FROM cnae_2009_2025 WHERE label_2009 LIKE ? LIMIT 1", ["%$searchTerm%"])->getRowArray();
        if ($row) return ['codes' => [$row['code']], 'label' => $this->normalizeLabel($row['label'])];

        return null;
    }

    private function normalizeLabel($label)
    {
        $label = mb_convert_case(mb_strtolower($label), MB_CASE_TITLE, "UTF-8");
        $label = str_replace([' De ', ' Y ', ' En ', ' Con ', ' Por ', ' Para ', ' Al ', ' La ', ' Los ', ' Las '], 
                             [' de ', ' y ', ' en ', ' con ', ' por ', ' para ', ' al ', ' la ', ' los ', ' las '], $label);
        return ucfirst($label);
    }

    /**
     * Construye la consulta base aplicando filtros de provincia y sector
     */
    private function applyFilters($builder, $province, $sector)
    {
        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            if (strtolower($province) === 'alicante') {
                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
            } else {
                $builder->where('registro_mercantil', $province);
            }
        }

        if ($sector && !empty($sector['codes'])) {
            $builder->groupStart();
            foreach ($sector['codes'] as $code) {
                $builder->orLike('cnae_code', $code, 'after');
            }
            $builder->groupEnd();
        }

        return $builder;
    }

    /**
     * Obtiene el listado de empresas para el Radar
     */
    public function getCompaniesList($province, $sector, $period, $limit = 100)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('companies');
        $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, cnae_code as cnae, registro_mercantil, objeto_social');
        
        $builder = $this->applyFilters($builder, $province, $sector);

        if ($period === 'hoy') {
            $builder->where('fecha_constitucion', date('Y-m-d'));
        } elseif ($period === 'semana') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
        } elseif ($period === 'mes' || $period === '30days') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')));
        } else {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
        }

        $builder->where('fecha_constitucion IS NOT NULL');
        $builder->where('fecha_constitucion <=', date('Y-m-d'));
        $builder->orderBy('fecha_constitucion', 'DESC');
        
        return $builder->get($limit)->getResultArray();
    }

    /**
     * Obtiene estadísticas de conteos (hoy, semana, mes) para el contexto actual.
     */
    public function getContextStats($province, $sector)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('companies');
        
        $builder = $this->applyFilters($builder, $province, $sector);
        $builder->where('fecha_constitucion IS NOT NULL');
        
        $query = $builder->select("
            COUNT(CASE WHEN fecha_constitucion = '" . date('Y-m-d') . "' THEN 1 END) as hoy,
            COUNT(CASE WHEN fecha_constitucion >= '" . date('Y-m-d', strtotime('-7 days')) . "' THEN 1 END) as semana,
            COUNT(CASE WHEN fecha_constitucion >= '" . date('Y-m-d', strtotime('-30 days')) . "' THEN 1 END) as mes
        ")->get()->getRowArray();

        return [
            'hoy'    => (int)($query['hoy'] ?? 0),
            'semana' => (int)($query['semana'] ?? 0),
            'mes'    => (int)($query['mes'] ?? 0),
            '30days' => (int)($query['mes'] ?? 0)
        ];
    }

    /**
     * Genera la lógica A/B determinista y los textos SEO
     */
    public function getSeoMetadata($province, $sector, $period, $stats, $uriString)
    {
        $statKey = ($period === 'general' || $period === 'mes' || $period === '30days') ? 'mes' : $period;
        $totalCount = $stats[$statKey] ?? $stats['mes'];
        
        $periodLabel = "ahora";
        if ($period === 'hoy') $periodLabel = "hoy";
        if ($period === 'semana') $periodLabel = "esta semana";
        if ($period === 'mes' || $period === '30days') $periodLabel = "últimos 30 días";

        $displayCount = ($totalCount > 0) ? "+{$totalCount} " : "";
        if ($period === 'hoy' && $stats['hoy'] >= $stats['mes'] && $stats['mes'] > 0) {
            $displayCount = "";
        }

        $context = $sector ? $sector['label'] : ($province && mb_strtolower($province, 'UTF-8') !== 'españa' ? ucfirst(mb_strtolower($province, 'UTF-8')) : "España");
        
        $seoContext = $context;
        if ($sector) {
            $shortLabels = [
                'Restaurantes y Puestos de Comidas' => 'Restauración',
                'Construcción de Edificios Residenciales' => 'Construcción Residencial',
                'Comercio al por menor en establecimientos no especializados' => 'Gran Consumo',
            ];
            $seoContext = $shortLabels[$context] ?? $context;
        }

        // Deterministic Hash
        $hash = crc32($uriString);
        $weights = ['A' => 20, 'B' => 20, 'C' => 20, 'D' => 20, 'E' => 20];
        $point = $hash % array_sum($weights);
        $current = 0;
        $variantId = 'A';
        foreach ($weights as $id => $w) {
            $current += $w;
            if ($point < $current) {
                $variantId = $id;
                break;
            }
        }

        $tVariants = [
            'A' => "{$displayCount}Empresas en {$seoContext} buscando proveedores {$periodLabel}",
            'B' => "{$displayCount}Empresas en {$seoContext} contratando proveedores {$periodLabel}",
            'C' => "{$displayCount}Empresas en {$seoContext} necesitan proveedores {$periodLabel}",
            'D' => "{$displayCount}Empresas en {$seoContext} buscan proveedores {$periodLabel}",
        ];
        $seoTitle = $tVariants[$variantId] ?? $tVariants['A'];
        
        if (mb_strlen($seoTitle) > 60) {
            $seoTitle = mb_substr($seoTitle, 0, 57) . '...';
        }

        $mWeights = ['V1' => 34, 'V2' => 33, 'V3' => 33];
        $mPoint = $hash % array_sum($mWeights);
        $mCurrent = 0;
        $mVariantId = 'action';
        foreach ($mWeights as $mid => $mw) {
            $mCurrent += $mw;
            if ($mPoint < $mCurrent) {
                $mVariantId = $mid;
                break;
            }
        }

        $mVariants = [
            'V1' => "Accede a {$displayCount}empresas en {$context} que están buscando proveedores {$periodLabel}. Las primeras en contactar son las que consiguen el cliente.",
            'V2' => "Accede a {$displayCount}empresas en {$context} que buscan proveedores {$periodLabel} de forma activa. Las primeras en contactar son las que consiguen el cliente.",
            'V3' => "Accede a {$displayCount}empresas en {$context} con necesidad de proveedores {$periodLabel}. Las primeras en contactar son las que consiguen el cliente.",
        ];

        if ($period === 'hoy') {
            $mVariants = [
                'V1' => "Accede a {$displayCount}empresas en {$context} que están buscando proveedores hoy. Cada día aparecen nuevas oportunidades — las primeras en contactar son las que consiguen el cliente.",
                'V2' => "Accede a {$displayCount}empresas en {$context} que buscan proveedores hoy. Cada día aparecen nuevas oportunidades — las primeras en contactar son las que consiguen el cliente.",
                'V3' => "Accede a {$displayCount}empresas en {$context} con necesidad de proveedores hoy. Cada día aparecen nuevas oportunidades — las primeras en contactar son las que consiguen el cliente.",
            ];
        }

        // Headings Generation
        $headingTime = "";
        if ($period === 'hoy') $headingTime = " hoy";
        elseif ($period === 'semana') $headingTime = " esta semana";
        elseif ($period === 'mes' || $period === '30days') $headingTime = " en los últimos 30 días";

        if ($sector) {
            $headingPrefix = "Empresas nuevas";
            $headingHighlight = mb_strtolower($sector['label'], 'UTF-8');
            $headingSuffix = " de ";
            $headingMiddle = " en ";
            $headingLocation = ($province && mb_strtolower($province, 'UTF-8') !== 'españa') ? ucfirst(mb_strtolower($province, 'UTF-8')) : "España";
        } elseif ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $headingPrefix = "Nuevas empresas";
            $headingHighlight = ucfirst(mb_strtolower($province, 'UTF-8'));
            $headingSuffix = " en ";
            $headingMiddle = "";
            $headingLocation = "";
        } else {
            $headingPrefix = "Empresas nuevas";
            $headingHighlight = "";
            $headingSuffix = "";
            $headingMiddle = " en ";
            $headingLocation = "España";
        }

        return [
            'variant_id' => $variantId . '-' . $mVariantId,
            'title' => $seoTitle,
            'excerptText' => $mVariants[$mVariantId],
            'dynamic_subtitle' => "{$displayCount}empresas en {$context} están contratando proveedores ahora mismo",
            'heading_prefix' => $headingPrefix,
            'heading_suffix' => $headingSuffix,
            'heading_highlight' => $headingHighlight,
            'heading_middle' => $headingMiddle,
            'heading_location' => $headingLocation,
            'heading_time' => $headingTime,
            'total_context_count' => $totalCount,
        ];
    }

    /**
     * Obtiene los datos del sidebar (Top Sectores, Sectores Relacionados)
     */
    public function getTopSidebarLinks($province)
    {
        $db = \Config\Database::connect();
        
        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $topData = $db->table('companies')
                ->select('MAX(cnae_label) as cnae_label, COUNT(id) as total')
                ->where('registro_mercantil', $province)
                ->where('cnae_code IS NOT NULL')->where('cnae_code !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy('cnae_code')->orderBy('total', 'DESC')->limit(12)->get()->getResultArray();
        } else {
            $topData = $db->table('companies')
                ->select('registro_mercantil as cnae_label, COUNT(id) as total')
                ->where('registro_mercantil IS NOT NULL')->where('registro_mercantil !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy('registro_mercantil')->orderBy('total', 'DESC')->limit(12)->get()->getResultArray();
        }

        return [
            'top_sectors' => $topData,
            'related_sectors' => [
                ['label' => 'Construcción'],
                ['label' => 'Hostelería'],
                ['label' => 'Comercio al por mayor'],
                ['label' => 'Transporte y logística'],
                ['label' => 'Programación e informática'],
                ['label' => 'Actividades inmobiliarias'],
                ['label' => 'Consultoría empresarial'],
                ['label' => 'Servicios a edificios']
            ]
        ];
    }
}
