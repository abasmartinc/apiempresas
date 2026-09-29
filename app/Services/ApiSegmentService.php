<?php

namespace App\Services;

/**
 * Segmentos de empresas (29-09-2026): GET /api/v1/companies/filter.
 *
 * - Recuento (count_only=true): gratis y para todos los planes. Es el gancho: "3.412 empresas
 *   encajan con tu búsqueda".
 * - Filas: solo Business. 5 consultas por fila (ROW_COST), hasta 1.000 por petición. A ese
 *   precio no compite con el CSV del directorio (9 € por 1.000) ni con los pedidos a medida.
 *
 * Nunca se devuelve el teléfono: solo has_phone. Las bajas por privacidad se quitan de las filas.
 */
class ApiSegmentService
{
    public const ROW_COST    = 5;
    public const MAX_LIMIT   = 1000;
    public const DEF_LIMIT   = 100;
    public const MAX_CNAE    = 20;
    public const COUNT_TTL   = 21600; // 6 h
    public const STATUSES    = ['active', 'active_or_unknown', 'any'];
    public const SIZE_BANDS  = ['NO_REVENUE', 'LT_500K', '500K_1M', 'GT_1M'];

    /**
     * Valida y normaliza los parámetros. Pura: se prueba aparte.
     *
     * @return array{0: ?array, 1: ?string} [filtros, mensaje de error]
     */
    public static function parse(array $q): array
    {
        $f = [];

        $cnae = trim((string) ($q['cnae'] ?? ''));
        if ($cnae !== '') {
            $pref = [];
            foreach (explode(',', $cnae) as $c) {
                $c = preg_replace('/[^0-9]/', '', $c);
                if ($c !== '' && strlen($c) <= 4) {
                    $pref[$c] = true;
                }
            }
            if (!$pref) {
                return [null, 'El parámetro "cnae" debe llevar prefijos numéricos de 1 a 4 cifras, separados por comas (ej: 62 o 4711,4719).'];
            }
            if (count($pref) > self::MAX_CNAE) {
                return [null, 'Como máximo ' . self::MAX_CNAE . ' prefijos CNAE por petición.'];
            }
            $f['cnae'] = array_map('strval', array_keys($pref)); // las claves numéricas llegan como int
            sort($f['cnae'], SORT_STRING);
        }

        $prov = trim((string) ($q['province'] ?? ''));
        if ($prov !== '') {
            $f['province'] = mb_strtoupper($prov, 'UTF-8');
        }
        $mun = trim((string) ($q['municipality'] ?? ''));
        if ($mun !== '') {
            $f['municipality'] = mb_strtoupper($mun, 'UTF-8');
        }

        if (empty($f['cnae']) && empty($f['province']) && empty($f['municipality'])) {
            return [null, 'Indica al menos uno de estos filtros: cnae, province o municipality.'];
        }

        $status = strtolower(trim((string) ($q['status'] ?? 'active')));
        if (!in_array($status, self::STATUSES, true)) {
            return [null, 'El parámetro "status" admite: ' . implode(', ', self::STATUSES) . '.'];
        }
        $f['status'] = $status;

        foreach (['founded_from', 'founded_to'] as $k) {
            $v = trim((string) ($q[$k] ?? ''));
            if ($v === '') {
                continue;
            }
            $d = \DateTime::createFromFormat('!Y-m-d', $v);
            if (!$d || $d->format('Y-m-d') !== $v) {
                return [null, 'El parámetro "' . $k . '" debe ser una fecha YYYY-MM-DD.'];
            }
            $f[$k] = $v;
        }
        if (isset($f['founded_from'], $f['founded_to']) && $f['founded_from'] > $f['founded_to']) {
            return [null, '"founded_from" no puede ser posterior a "founded_to".'];
        }

        if (isset($q['has_phone']) && $q['has_phone'] !== '') {
            $hp = filter_var($q['has_phone'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hp === null) {
                return [null, 'El parámetro "has_phone" debe ser true o false.'];
            }
            if ($hp) {
                $f['has_phone'] = true;
            }
        }

        $sb = trim((string) ($q['size_band'] ?? ''));
        if ($sb !== '') {
            $bands = [];
            foreach (explode(',', strtoupper($sb)) as $b) {
                $b = trim($b);
                if (!in_array($b, self::SIZE_BANDS, true)) {
                    return [null, 'El parámetro "size_band" admite: ' . implode(', ', self::SIZE_BANDS) . ' (separados por comas).'];
                }
                $bands[$b] = true;
            }
            $f['size_band'] = array_keys($bands);
            sort($f['size_band']);
        }

        $may = trim((string) ($q['min_accounts_year'] ?? ''));
        if ($may !== '') {
            if (!ctype_digit($may) || (int) $may < 1990 || (int) $may > (int) date('Y')) {
                return [null, 'El parámetro "min_accounts_year" debe ser un año entre 1990 y ' . date('Y') . '.'];
            }
            $f['min_accounts_year'] = (int) $may;
        }

        return [$f, null];
    }

    /** Clave estable del recuento (mismos filtros = misma clave, en cualquier orden). */
    public static function countKey(array $f): string
    {
        ksort($f);
        return 'api_segment_count_v1_' . md5(json_encode($f));
    }

    /** Paginación por id: el cursor es el último id devuelto. */
    public static function encodeCursor(int $lastId): string
    {
        return rtrim(strtr(base64_encode(json_encode(['id' => $lastId])), '+/', '-_'), '=');
    }

    public static function decodeCursor(?string $c): ?int
    {
        if ($c === null || $c === '') {
            return null;
        }
        $j = json_decode((string) base64_decode(strtr($c, '-_', '+/')), true);
        return (is_array($j) && isset($j['id']) && is_int($j['id']) && $j['id'] > 0) ? $j['id'] : null;
    }

    /**
     * Cuántas filas se pueden cobrar con lo que queda: cupo del mes primero, luego monedero.
     * @return array{rows:int, sub_cost:int, wallet_cost:int}
     */
    public static function affordable(int $wanted, int $monthlyRemaining, int $walletBalance): array
    {
        $rows = min($wanted, intdiv(max(0, $monthlyRemaining) + max(0, $walletBalance), self::ROW_COST));
        $cost = $rows * self::ROW_COST;
        $sub = min($cost, max(0, $monthlyRemaining));
        return ['rows' => $rows, 'sub_cost' => $sub, 'wallet_cost' => $cost - $sub];
    }

    // ------------------------------------------------------------------
    // Consultas
    // ------------------------------------------------------------------

    /** Valores de ventas_raw de cada tramo (se calculan con sizeBand; caché de 24 h). */
    public static function rawValuesForBands(array $bands): array
    {
        $map = cache()->get('api_segment_ventas_raw_v1');
        if (!is_array($map)) {
            $map = [];
            try {
                $rows = \Config\Database::connect()->query('SELECT DISTINCT ventas_raw FROM companies WHERE ventas_raw IS NOT NULL AND ventas_raw <> \'\'')->getResultArray();
                foreach ($rows as $r) {
                    $code = ApiCompanyEnricher::sizeBand((string) $r['ventas_raw'])['code'];
                    if ($code !== null) {
                        $map[$code][] = (string) $r['ventas_raw'];
                    }
                }
                cache()->save('api_segment_ventas_raw_v1', $map, 86400);
            } catch (\Throwable $e) {
                log_message('error', '[ApiSegmentService::rawValuesForBands] ' . $e->getMessage());
            }
        }
        $out = [];
        foreach ($bands as $b) {
            foreach ($map[$b] ?? [] as $v) {
                $out[] = $v;
            }
        }
        return $out;
    }

    /** Aplica los filtros a un builder sobre companies. */
    public static function apply($b, array $f): void
    {
        if (!empty($f['cnae'])) {
            $b->groupStart();
            foreach ($f['cnae'] as $c) {
                $b->orLike('companies.cnae_code', $c, 'after');
            }
            $b->groupEnd();
        }
        if (!empty($f['province'])) {
            // Alicante está guardada con los dos nombres (igual que CompanyModel::getRelated).
            $prov = in_array($f['province'], ['ALICANTE', 'ALACANT', 'ALICANTE/ALACANT'], true)
                ? ['Alicante', 'Alicante/Alacant'] : [$f['province']];
            $b->whereIn('companies.registro_mercantil', $prov);
        }
        if (!empty($f['municipality'])) {
            $b->where('companies.municipality', $f['municipality']);
        }
        if ($f['status'] === 'active') {
            $b->where('companies.estado', 'ACTIVA');
        } elseif ($f['status'] === 'active_or_unknown') {
            $b->groupStart()
                ->where('companies.estado', 'ACTIVA')
                ->orWhere('companies.estado IS NULL', null, false)
                ->orWhere('companies.estado', '')
                ->groupEnd();
        }
        if (!empty($f['founded_from'])) {
            $b->where('companies.fecha_constitucion >=', $f['founded_from']);
        }
        if (!empty($f['founded_to'])) {
            $b->where('companies.fecha_constitucion <=', $f['founded_to']);
        }
        if (!empty($f['has_phone'])) {
            $b->groupStart()
                ->groupStart()->where('companies.phone IS NOT NULL', null, false)->where('companies.phone <>', '')->groupEnd()
                ->orGroupStart()->where('companies.phone_mobile IS NOT NULL', null, false)->where('companies.phone_mobile <>', '')->groupEnd()
                ->groupEnd();
        }
        if (!empty($f['size_band'])) {
            $vals = self::rawValuesForBands($f['size_band']);
            if ($vals) {
                $b->whereIn('companies.ventas_raw', $vals);
            } else {
                $b->where('1 = 0', null, false);
            }
        }
        if (!empty($f['min_accounts_year'])) {
            $b->where('companies.ult_cuentas_anio >=', (int) $f['min_accounts_year']);
        }
    }

    /** Recuento con caché de 6 h por combinación de filtros. */
    public static function count(array $f): array
    {
        $key = self::countKey($f);
        $hit = cache()->get($key);
        if (is_array($hit)) {
            return $hit + ['cached' => true];
        }
        $b = \Config\Database::connect()->table('companies');
        self::apply($b, $f);
        $out = ['total' => (int) $b->countAllResults(), 'counted_at' => date('c')];
        cache()->save($key, $out, self::COUNT_TTL);
        return $out + ['cached' => false];
    }

    /**
     * Una página de filas, por id ascendente. Devuelve [filas, hay_más].
     * Pide una fila de más para saber si hay página siguiente.
     */
    public static function rows(array $f, int $limit, ?int $afterId): array
    {
        $b = \Config\Database::connect()->table('companies');
        $b->select('companies.id, companies.cif, companies.company_name AS name, companies.cnae_code AS cnae, companies.cnae_label, companies.registro_mercantil AS province, companies.municipality, companies.fecha_constitucion AS founded, companies.estado AS status, companies.phone, companies.phone_mobile');
        self::apply($b, $f);
        if ($afterId !== null) {
            $b->where('companies.id >', $afterId);
        }
        $b->orderBy('companies.id', 'ASC')->limit($limit + 1);
        $rows = $b->get()->getResultArray();
        $more = count($rows) > $limit;
        return [array_slice($rows, 0, $limit), $more];
    }

    /** Forma pública de una fila: sin teléfono ni id. */
    public static function publicRow(array $r): array
    {
        $hasPhone = trim((string) ($r['phone'] ?? '')) !== '' || trim((string) ($r['phone_mobile'] ?? '')) !== '';
        return [
            'cif'           => $r['cif'],
            'name'          => $r['name'],
            'cnae'          => $r['cnae'] ?? null,
            'cnae_label'    => $r['cnae_label'] ?? null,
            'province'      => $r['province'] ?? null,
            'municipality'  => $r['municipality'] ?? null,
            'founded'       => $r['founded'] ?? null,
            'status'        => $r['status'] ?? null,
            'status_code'   => $r['status_code'] ?? null,
            'status_source' => $r['status_source'] ?? null,
            'financials'    => $r['financials'] ?? null,
            'has_phone'     => $hasPhone,
        ];
    }
}
