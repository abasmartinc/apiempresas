<?php

namespace App\Services;

class BillingService
{
    public function getDirectoryPricingDetails(int $totalCount): array
    {
        if (!function_exists('calculate_directory_price')) {
            require_once APPPATH . 'Helpers/pricing_helper.php';
        }
        return calculate_directory_price($totalCount, false);
    }

    /**
     * Calcula el precio base (float) por compatibilidad
     */
    public function calculateDirectoryPrice(int $totalCount): float
    {
        return (float) $this->getDirectoryPricingDetails($totalCount)['base_price'];
    }

    /**
     * Calcula el precio para recargas de créditos API mediante bonos
     */
    public function calculateBonusPrice(int $credits): float
    {
        $price = 49;
        $tiers = [
            ['qty' => 10000, 'price' => 49],
            ['qty' => 50000, 'price' => 199],
            ['qty' => 100000, 'price' => 349],
            ['qty' => 500000, 'price' => 999],
            ['qty' => 1000000, 'price' => 1499]
        ];

        if ($credits >= 1000000) {
            return 1499.0;
        }

        for ($i = 0; $i < count($tiers) - 1; $i++) {
            if ($credits >= $tiers[$i]['qty'] && $credits <= $tiers[$i+1]['qty']) {
                $range = $tiers[$i+1]['qty'] - $tiers[$i]['qty'];
                $priceRange = $tiers[$i+1]['price'] - $tiers[$i]['price'];
                $progress = ($credits - $tiers[$i]['qty']) / $range;
                $price = (int) round($tiers[$i]['price'] + ($progress * $priceRange));
                break;
            }
        }

        return (float) $price;
    }

    /**
     * Helper paramétrico para crear el line_item de pago único en Stripe
     */
    public function buildSinglePaymentLineItem(string $name, string $description, float $amount, ?string $taxRateId = null): array
    {
        $lineItem = [
            'quantity' => 1,
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => (int)($amount * 100),
                'product_data' => [
                    'name' => $name,
                    'description' => $description
                ]
            ]
        ];

        if ($taxRateId) {
            $lineItem['tax_rates'] = [$taxRateId];
        }

        return $lineItem;
    }

    /**
     * Helper paramétrico para crear el line_item de suscripción en Stripe
     */
    public function buildSubscriptionLineItem(string $name, string $description, float $amount, string $interval = 'month', ?string $taxRateId = null): array
    {
        $lineItem = [
            'quantity' => 1,
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => (int)($amount * 100),
                'recurring' => [
                    'interval' => $interval
                ],
                'product_data' => [
                    'name' => $name,
                    'description' => $description
                ]
            ]
        ];

        if ($taxRateId) {
            $lineItem['tax_rates'] = [$taxRateId];
        }

        return $lineItem;
    }

    /**
     * Cuenta el número de empresas para una descarga de Directorio Histórico
     */
    public function countDirectoryCompanies(array $filters): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('companies');

        $prov = $filters['provincia'] ?? 'España';
        $estado = $filters['estado'] ?? '';
        $has_phone = $filters['has_phone'] ?? '';
        $date_min = $filters['date_min'] ?? '';
        $date_max = $filters['date_max'] ?? '';
        $cnae = $filters['cnae'] ?? '';
        $cnae_text = $filters['cnae_text'] ?? '';
        $municipio = trim((string) ($filters['municipio'] ?? ''));

        if ($estado !== '') {
            $builder->where('estado', $estado);
        }
        // Mismo filtro de municipio que el mapa (CompanyMapV2Controller::search)
        if ($municipio !== '') {
            $builder->like('address', $municipio, 'both');
        }
        if ($has_phone == '1') {
            $builder->groupStart()
                    ->groupStart()->where('phone IS NOT NULL', null, false)->where('phone !=', '')->groupEnd()
                    ->orGroupStart()->where('phone_mobile IS NOT NULL', null, false)->where('phone_mobile !=', '')->groupEnd()
                    ->groupEnd();
        }
        if ($date_min !== '') $builder->where('estado_fecha >=', $date_min);
        if ($date_max !== '') $builder->where('estado_fecha <=', $date_max);
        
        if ($cnae !== '') {
            $builder->where('cnae_code LIKE', $cnae . '%');
        } elseif ($cnae_text !== '') {
            $builder->like('cnae_label', $cnae_text, 'both');
        }
        
        self::filtrarProvincia($builder, (string) $prov);
        return $builder->countAllResults();
    }

    /**
     * Filtro de provincia común al recuento (lo que se cobra) y a la exportación
     * (lo que se entrega). Antes cada uno trataba Álava y Alicante a su manera.
     */
    public static function filtrarProvincia($builder, string $prov): void
    {
        $p = mb_strtolower(trim($prov), 'UTF-8');
        if ($p === '' || $p === 'españa') {
            return;
        }
        if (in_array($p, ['alicante', 'alacant', 'alicante/alacant'], true)) {
            $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
        } elseif (in_array($p, ['araba/álava', 'álava', 'álava-araba', 'araba', 'alava'], true)) {
            $builder->whereIn('registro_mercantil', ['Álava', 'ÁLAVA', 'Álava-Araba', 'Araba/Álava', 'ALAVA']);
        } else {
            $builder->where('registro_mercantil', $prov);
        }
    }

    /**
     * Cuenta el número de empresas para una descarga del Radar B2B
     */
    public function countRadarCompanies(array $filters): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('companies');
        
        $prov = $filters['provincia'] ?? 'España';
        $cnae = $filters['cnae'] ?? '';
        
        $builder->where('cnae_code LIKE', $cnae . '%');
        $builder->where('fecha_constitucion IS NOT NULL');
        
        if (strtolower($prov) !== 'españa') {
            if (in_array(strtolower($prov), ['alicante', 'alacant', 'alicante/alacant'])) {
                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
            } else {
                $builder->where('registro_mercantil', $prov);
            }
        }
        return $builder->countAllResults();
    }

    /**
     * Extrae y centraliza la lógica de consultas a base de datos y cálculos
     * de precio para descargas de listados (Radar y Directorio).
     */
    public function getExcelDownloadContext(string $plan, array $postData, array $getParams = []): array
    {
        $prov = $postData['provincia'] ?? 'España';
        $count = 0;
        $amount = 0.0;
        $context = [];
        $productName = '';
        $productDesc = '';
        $metadataPlan = '';

        if ($plan === 'directory_single') {
            // Recuento y precio SIEMPRE en el servidor. Antes se aceptaban `price` y
            // `total_count` del formulario (campos ocultos) y se podía pagar 0,50 €
            // por toda España. Los filtros son los mismos que se guardan en el
            // contexto y que usa la exportación: se cobra exactamente lo que se entrega.
            $cnae = (string) ($postData['cnae'] ?? '');
            $cnae_text = (string) ($postData['cnae_text'] ?? '');
            $sect = (string) ($postData['sector'] ?? '');
            $estado = (string) ($postData['estado'] ?? '');
            $has_phone = ((string) ($postData['has_phone'] ?? $getParams['has_phone'] ?? '')) === '1' ? '1' : '';
            $date_min = $this->fechaValida($postData['date_min'] ?? $getParams['date_min'] ?? '');
            $date_max = $this->fechaValida($postData['date_max'] ?? $getParams['date_max'] ?? '');
            $municipio = trim((string) ($postData['municipio'] ?? ''));

            $count = $this->countDirectoryCompanies([
                'provincia' => $prov,
                'municipio' => $municipio,
                'estado'    => $estado,
                'has_phone' => $has_phone,
                'date_min'  => $date_min,
                'date_max'  => $date_max,
                'cnae'      => $cnae,
                'cnae_text' => $cnae_text,
            ]);
            // Un listado vacío no se vende (antes costaba 9 €)
            $amount = $count > 0 ? $this->calculateDirectoryPrice($count) : 0.0;

            $context = [
                'type'        => 'directory_excel',
                'provincia'   => $prov,
                'municipio'   => $municipio,
                'cnae'        => $cnae,
                'cnae_text'   => $cnae_text,
                'sector'      => $sect,
                'estado'      => $estado,
                'has_phone'   => $has_phone,
                'date_min'    => $date_min,
                'date_max'    => $date_max,
                'total_count' => $count
            ];
            $zona = $municipio !== '' ? $municipio . ' (' . $prov . ')' : $prov;
            $productName = 'BBDD Histórica ' . $zona . ' (' . number_format($count, 0, ',', '.') . ' empresas)';
            $productDesc = 'Descarga en Excel del listado histórico completo.';
            $metadataPlan = 'directory_single';

        } elseif ($plan === 'subsidies_single') {
            $convocatoria = $postData['convocatoria'] ?? '';
            $convocatoriaName = $convocatoria !== '' ? $this->resolveSubsidiesConvocatoria($convocatoria) : '';
            $year = $postData['year'] ?? '';
            $count = $this->countSubsidies(['convocatoria' => $convocatoria, 'year' => $year]);
            $amount = $this->calculatePublicFundsPrice($count);

            $context = [
                'type' => 'subsidies_excel',
                'convocatoria' => $convocatoria,
                'year' => $year,
                'total_count' => $count
            ];
            
            $productName = 'BBDD Subvenciones';
            if ($convocatoriaName) $productName .= ' - ' . $this->formatSubsidiesConvocatoriaName($convocatoriaName);
            if ($year) $productName .= ' (' . $year . ')';
            $productName .= ' (' . number_format($count, 0, ',', '.') . ' registros)';
            
            $productDesc = 'Descarga en Excel de empresas subvencionadas con teléfono y CNAE.';
            $metadataPlan = 'subsidies_single';

        } elseif ($plan === 'contracts_single') {
            $year = $postData['year'] ?? '';
            $organo = $postData['organo'] ?? '';
            $organoName = $organo !== '' ? $this->resolveContractsOrgano($organo) : '';
            $count = $this->countContracts(['year' => $year, 'organo' => $organo]);
            $amount = $this->calculatePublicFundsPrice($count);

            $context = [
                'type' => 'contracts_excel',
                'year' => $year,
                'organo' => $organo,
                'total_count' => $count
            ];

            $productName = 'BBDD Licitaciones Públicas';
            if ($organoName) $productName .= ' - ' . $this->formatContractsOrganoName($organoName);
            if ($year) $productName .= ' (' . $year . ')';
            $productName .= ' (' . number_format($count, 0, ',', '.') . ' registros)';
            
            $productDesc = 'Descarga en Excel de empresas adjudicatarias con teléfono y CNAE.';
            $metadataPlan = 'contracts_single';

        } elseif ($plan === 'lookalike_single') {
            // El recuento sale de la búsqueda guardada en sesión por LookalikeController,
            // no del formulario; el precio se recalcula con la misma fórmula.
            $lookalike = (array) (session('lookalike_params') ?? []);
            $count = (int) ($lookalike['total_found'] ?? 0);
            if (!function_exists('calculate_directory_price')) {
                require_once APPPATH . 'Helpers/pricing_helper.php';
            }
            $amount = $count > 0 ? (float) calculate_directory_price($count, false)['base_price'] : 0.0;

            $context = [
                'type'        => 'lookalike_excel',
                'total_count' => $count
            ];
            
            $productName = 'Audiencia Lookalike (' . number_format($count, 0, ',', '.') . ' prospectos)';
            $productDesc = 'Descarga de clientes clonados para marketing B2B.';
            $metadataPlan = 'lookalike_single';

        } else {
            $sect = $postData['sector'] ?? '';
            $cnae = $postData['cnae'] ?? '';
            $per  = (isset($postData['period_radar']) && $postData['period_radar'] !== '')
                ? $postData['period_radar']
                : ($cnae !== '' ? 'general' : '30days');

            if ($cnae !== '') {
                $count = $this->countRadarCompanies([
                    'provincia' => $prov,
                    'cnae' => $cnae
                ]);
            } else {
                $radar = new \App\Controllers\RadarController();
                $radarData = $radar->getRadarData($prov, $sect, $per, 1);
                $count = $radarData['total_context_count'] ?? 0;
            }

            // Requires 'pricing' helper to be loaded in the caller
            $pricing = \calculate_radar_price($count); 
            $amount = $pricing['base_price'];

            $context = [
                'type'        => 'excel',
                'sector'      => $sect,
                'cnae'        => $cnae,
                'provincia'   => $prov,
                'period'      => $per,
                'total_count' => $count
            ];
            $productName = 'Descarga Listado Radar B2B (' . $count . ' empresas)';
            $productDesc = 'Listado completo de nuevas empresas constituidas.';
            $metadataPlan = 'radar_single';
        }

        return [
            'count' => $count,
            'amount' => $amount,
            'context' => $context,
            'product_name' => $productName,
            'product_desc' => $productDesc,
            'metadata_plan' => $metadataPlan
        ];
    }

    /**
     * Fecha Y-m-d válida o cadena vacía (los filtros de fecha llegan del navegador).
     */
    private function fechaValida($valor): string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return '';
        }
        $d = \DateTime::createFromFormat('Y-m-d', $valor);

        return ($d && $d->format('Y-m-d') === $valor) ? $valor : '';
    }

    public function getPublicFundsPricingDetails(int $totalCount): array
    {
        if (!function_exists('calculate_directory_price')) {
            require_once APPPATH . 'Helpers/pricing_helper.php';
        }
        $basePrice = 9.90;
        if ($totalCount <= 999) {
            $basePrice = 9.90;
        } elseif ($totalCount <= 9999) {
            $basePrice = 19.0;
        } elseif ($totalCount <= 100000) {
            $basePrice = 49.0;
        } elseif ($totalCount <= 500000) {
            $basePrice = 99.0;
        } else {
            $basePrice = 149.0;
        }

        // Sin precio tachado: antes se tachaba el precio de la fórmula de los listados
        // de empresas (p. ej. 29 € junto a 19 €), que en subvenciones y licitaciones
        // no se ha cobrado nunca. Ver la nota en pricing_helper (calculate_core_price).
        return [
            'base_price'     => $basePrice,
            'original_price' => $basePrice,
            'is_discounted'  => false,
            'precio_maximo'  => $basePrice >= 149.0,
            'tope'           => 149.0,
            'tax'            => round($basePrice * 0.21, 2),
            'total'          => $basePrice + round($basePrice * 0.21, 2)
        ];
    }

    /**
     * Calcula el precio para descargas de bases de datos de Subvenciones y Licitaciones
     */
    public function calculatePublicFundsPrice(int $totalCount): float
    {
        return (float) $this->getPublicFundsPricingDetails($totalCount)['base_price'];
    }

    /**
     * Cuenta el número de subvenciones para una descarga
     */
    public function countSubsidies(array $filters): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('company_subsidies');
        
        $convocatoria = $filters['convocatoria'] ?? '';
        $year = $filters['year'] ?? '';

        if ($convocatoria !== '') {
            $builder->where('convocatoria', $this->resolveSubsidiesConvocatoria($convocatoria));
        }
        if ($year !== '') {
            $builder->where('YEAR(fecha_concesion)', $year);
        }
        
        return $builder->countAllResults();
    }

    /**
     * Resuelve un slug SEO de convocatoria al valor real guardado en company_subsidies.
     */
    public function resolveSubsidiesConvocatoria(string $convocatoria): string
    {
        $convocatoria = trim($convocatoria);
        if ($convocatoria === '') {
            return '';
        }

        $db = \Config\Database::connect();

        try {
            $row = $db->table('seo_hub_subvenciones')
                ->select('convocatoria')
                ->groupStart()
                    ->where('slug', $convocatoria)
                    ->orWhere('convocatoria', $convocatoria)
                ->groupEnd()
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!empty($row['convocatoria'])) {
                return $row['convocatoria'];
            }
        } catch (\Throwable $e) {
            log_message('warning', '[BillingService::resolveSubsidiesConvocatoria] ' . $e->getMessage());
        }

        foreach ($this->buildSubsidiesConvocatoriaPrefixes($convocatoria) as $prefix) {
            try {
                $row = $db->table('company_subsidies')
                    ->select('convocatoria')
                    ->where('convocatoria IS NOT NULL', null, false)
                    ->where('convocatoria !=', '')
                    ->like('convocatoria', $prefix, 'after')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if (!empty($row['convocatoria'])) {
                    return $row['convocatoria'];
                }
            } catch (\Throwable $e) {
                log_message('warning', '[BillingService::resolveSubsidiesConvocatoria prefix] ' . $e->getMessage());
            }
        }

        return $convocatoria;
    }

    public function formatSubsidiesConvocatoriaName(string $convocatoria): string
    {
        $name = trim(str_replace(['-', '_'], ' ', $convocatoria));
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
        $name = str_replace(['A¤o', 'A寸'], 'Año', $name);

        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    private function buildSubsidiesConvocatoriaPrefixes(string $value): array
    {
        $phrase = trim(preg_replace('/\s+/u', ' ', str_replace(['-', '_'], ' ', $value)) ?? '');
        if ($phrase === '') {
            return [];
        }

        $prefixes = [$phrase];
        $withoutYear = trim(preg_replace('/\s+a[nñ]o\s+\d{4}\s*$/iu', '', $phrase) ?? '');
        if ($withoutYear !== '' && $withoutYear !== $phrase) {
            $prefixes[] = $withoutYear;
        }

        $parts = preg_split('/\s+/u', $withoutYear ?: $phrase, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) > 8) {
            $prefixes[] = implode(' ', array_slice($parts, 0, 8));
        }

        return array_values(array_unique($prefixes));
    }

    /**
     * Cuenta el número de contratos para una descarga
     */
    public function countContracts(array $filters): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('company_contracts');
        
        $year = $filters['year'] ?? '';
        $organo = $filters['organo'] ?? '';
        if ($year !== '') {
            $builder->where('YEAR(fecha_adjudicacion)', $year);
        }
        if ($organo !== '') {
            $builder->where('organo_contratacion', $this->resolveContractsOrgano($organo));
        }
        
        return $builder->countAllResults();
    }

    public function resolveContractsOrgano(string $organo): string
    {
        $organo = trim($organo);
        if ($organo === '') {
            return '';
        }

        $db = \Config\Database::connect();

        try {
            $row = $db->table('company_contracts')
                ->select('organo_contratacion')
                ->where('organo_contratacion', $organo)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!empty($row['organo_contratacion'])) {
                return $row['organo_contratacion'];
            }
        } catch (\Throwable $e) {
            log_message('warning', '[BillingService::resolveContractsOrgano exact] ' . $e->getMessage());
        }

        foreach ($this->buildContractsOrganoPrefixes($organo) as $prefix) {
            try {
                $row = $db->table('company_contracts')
                    ->select('organo_contratacion')
                    ->where('organo_contratacion IS NOT NULL', null, false)
                    ->where('organo_contratacion !=', '')
                    ->like('organo_contratacion', $prefix, 'after')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if (!empty($row['organo_contratacion'])) {
                    return $row['organo_contratacion'];
                }
            } catch (\Throwable $e) {
                log_message('warning', '[BillingService::resolveContractsOrgano prefix] ' . $e->getMessage());
            }
        }

        $words = $this->extractContractsOrganoWords($organo);
        if (count($words) >= 2) {
            try {
                $builder = $db->table('company_contracts')
                    ->select('organo_contratacion, COUNT(id) as total')
                    ->where('organo_contratacion IS NOT NULL', null, false)
                    ->where('organo_contratacion !=', '');

                foreach ([$words[0], $words[count($words) - 1]] as $word) {
                    $builder->like('organo_contratacion', $word, 'both');
                }

                $row = $builder
                    ->groupBy('organo_contratacion')
                    ->orderBy('total', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if (!empty($row['organo_contratacion'])) {
                    return $row['organo_contratacion'];
                }
            } catch (\Throwable $e) {
                log_message('warning', '[BillingService::resolveContractsOrgano words] ' . $e->getMessage());
            }
        }

        return $organo;
    }

    public function formatContractsOrganoName(string $organo): string
    {
        $name = trim(str_replace(['-', '_'], ' ', $organo));
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    private function buildContractsOrganoPrefixes(string $value): array
    {
        $phrase = trim(preg_replace('/\s+/u', ' ', str_replace(['-', '_'], ' ', $value)) ?? '');
        if ($phrase === '') {
            return [];
        }

        $prefixes = [$phrase];
        $parts = preg_split('/\s+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) > 3) {
            $prefixes[] = implode(' ', array_slice($parts, 0, 3));
        }

        return array_values(array_unique($prefixes));
    }

    private function extractContractsOrganoWords(string $value): array
    {
        $phrase = mb_strtolower(str_replace(['-', '_'], ' ', $value), 'UTF-8');
        $parts = preg_split('/\s+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = ['de' => true, 'del' => true, 'la' => true, 'el' => true, 'y' => true, 'a' => true, 'en' => true];
        $words = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || isset($stopWords[$part]) || mb_strlen($part, 'UTF-8') < 4) {
                continue;
            }
            $words[] = $part;
        }

        return array_values(array_unique($words));
    }
}
