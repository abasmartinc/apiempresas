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

        if ($estado !== '') {
            $builder->where('estado', $estado);
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
            $count = (int) ($postData['total_count'] ?? 0);
            $amount = (float) ($postData['price'] ?? 0);
            $cnae = $postData['cnae'] ?? '';
            $cnae_text = $postData['cnae_text'] ?? '';
            $sect = $postData['sector'] ?? '';
            $estado = $postData['estado'] ?? '';
            $has_phone = $postData['has_phone'] ?? '';
            
            if ($count <= 0 || $amount <= 0) {
                $filters = [
                    'provincia' => $prov,
                    'estado'    => $estado,
                    'has_phone' => $has_phone,
                    'date_min'  => $getParams['date_min'] ?? '',
                    'date_max'  => $getParams['date_max'] ?? '',
                    'cnae'      => $cnae,
                    'cnae_text' => $cnae_text
                ];
                $count = $this->countDirectoryCompanies($filters);
                $amount = $this->calculateDirectoryPrice($count);
            }

            $context = [
                'type'        => 'directory_excel',
                'provincia'   => $prov,
                'cnae'        => $cnae,
                'cnae_text'   => $cnae_text,
                'sector'      => $sect,
                'estado'      => $estado,
                'has_phone'   => $has_phone,
                'total_count' => $count
            ];
            $productName = 'BBDD Histórica ' . $prov . ' (' . number_format($count, 0, ',', '.') . ' empresas)';
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
            if ($convocatoriaName) $productName .= ' - ' . mb_convert_case($convocatoriaName, MB_CASE_TITLE, 'UTF-8');
            if ($year) $productName .= ' (' . $year . ')';
            $productName .= ' (' . number_format($count, 0, ',', '.') . ' registros)';
            
            $productDesc = 'Descarga en Excel de empresas subvencionadas con teléfono y CNAE.';
            $metadataPlan = 'subsidies_single';

        } elseif ($plan === 'contracts_single') {
            $year = $postData['year'] ?? '';
            $organo = $postData['organo'] ?? '';
            $count = $this->countContracts(['year' => $year, 'organo' => $organo]);
            $amount = $this->calculatePublicFundsPrice($count);

            $context = [
                'type' => 'contracts_excel',
                'year' => $year,
                'organo' => $organo,
                'total_count' => $count
            ];

            $productName = 'BBDD Licitaciones Públicas';
            if ($organo) $productName .= ' - ' . ucfirst(str_replace('-', ' ', $organo));
            if ($year) $productName .= ' (' . $year . ')';
            $productName .= ' (' . number_format($count, 0, ',', '.') . ' registros)';
            
            $productDesc = 'Descarga en Excel de empresas adjudicatarias con teléfono y CNAE.';
            $metadataPlan = 'contracts_single';

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

    public function getPublicFundsPricingDetails(int $totalCount): array
    {
        if (!function_exists('calculate_directory_price')) {
            require_once APPPATH . 'Helpers/pricing_helper.php';
        }
        // Usamos el helper para obtener el "precio matemático" para mostrar como tachado
        $linearPricing = calculate_directory_price($totalCount, false);
        $originalPrice = $linearPricing['original_price'];

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

        $maxDisplayOriginalPrice = [
            9   => 19.0,
            19  => 29.0,
            49  => 79.0,
            99  => 129.0,
            149 => 259.0,
        ];
        $originalPrice = min($originalPrice, $maxDisplayOriginalPrice[(int) $basePrice] ?? 259.0);

        $isDiscounted = false;
        if ($originalPrice > $basePrice) {
            $isDiscounted = true;
        } else {
            $originalPrice = $basePrice;
        }

        return [
            'base_price'     => $basePrice,
            'original_price' => $originalPrice,
            'is_discounted'  => $isDiscounted,
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

        return $convocatoria;
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
            $builder->where('organo_contratacion', $organo);
        }
        
        return $builder->countAllResults();
    }
}
