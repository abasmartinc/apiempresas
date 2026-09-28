<?php

namespace App\Services;

/**
 * Vigilancia de empresas por API (27-09-2026, piloto).
 *
 * El cliente da de alta CIF en su lista (tabla api_watchlist, separada de la de
 * Solvencia) y consulta los cambios desde una fecha. No hay cron ni tabla de eventos:
 * los eventos se calculan al consultar a partir de lo que ya tenemos.
 *   - borme_act          actos publicados en el BORME (borme_posts)
 *   - status_change      estado del Registro con fecha (companies.estado_fecha)
 *   - risk_level_change  cambio de nivel en el motor de riesgo (histórico + perfil actual)
 *
 * Webhooks: segunda fase, cuando haya uso. Consultar y vigilar no gasta cupo.
 */
class ApiWatchlistService
{
    /** Empresas vigiladas por plan (slug). Un plan que no esté aquí no tiene vigilancia. */
    public const LIMITS = [
        'pro'        => 100,
        'business'   => 1000,
        'enterprise' => 1000,
    ];

    public const MAX_DIAS_ATRAS = 90;
    public const DIAS_POR_DEFECTO = 7;
    public const MAX_POR_PAGINA = 500;
    public const MAX_ALTA_POR_PETICION = 500;

    public const TIPOS = ['borme_act', 'status_change', 'risk_level_change'];

    public static function limitFor(string $planSlug): int
    {
        return self::LIMITS[strtolower($planSlug)] ?? 0;
    }

    public static function tableReady(): bool
    {
        try {
            return \Config\Database::connect()->tableExists('api_watchlist');
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Lista
    // ------------------------------------------------------------------

    public static function count(int $userId): int
    {
        return \Config\Database::connect()->table('api_watchlist')->where('user_id', $userId)->countAllResults();
    }

    /** @return array{items: array, total: int} */
    public static function listFor(int $userId, int $page, int $perPage): array
    {
        $db = \Config\Database::connect();
        $total = $db->table('api_watchlist')->where('user_id', $userId)->countAllResults();
        $rows = $db->table('api_watchlist w')
            ->select('w.cif, w.created_at, c.company_name')
            ->join('companies c', 'c.cif = w.cif', 'left')
            ->where('w.user_id', $userId)
            ->orderBy('w.id', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'cif'      => $r['cif'],
                'name'     => $r['company_name'],
                'added_at' => $r['created_at'],
            ];
        }
        return ['items' => $items, 'total' => $total];
    }

    /**
     * Da de alta una lista de CIF. Devuelve qué pasó con cada uno.
     * Los CIF de empresas con baja por privacidad se tratan como no encontrados.
     */
    public static function add(int $userId, array $cifsRaw, int $limit): array
    {
        helper('company');
        $res = ['added' => [], 'already_watching' => [], 'not_found' => [], 'invalid' => [], 'rejected_over_limit' => []];

        $validos = [];
        foreach ($cifsRaw as $raw) {
            if (!is_string($raw) && !is_numeric($raw)) {
                continue;
            }
            $c = ApiCompanyEnricher::normalizeCif((string) $raw);
            if ($c === '' || !is_valid_cif($c)) {
                $res['invalid'][] = (string) $raw;
                continue;
            }
            $validos[$c] = true;
        }
        $validos = array_keys($validos);
        if (empty($validos)) {
            return $res;
        }

        $db = \Config\Database::connect();

        $ya = array_column($db->table('api_watchlist')->select('cif')->where('user_id', $userId)->whereIn('cif', $validos)->get()->getResultArray(), 'cif');
        $ya = array_flip(array_map([ApiCompanyEnricher::class, 'normalizeCif'], $ya));

        $existen = array_column($db->table('companies')->select('cif')->whereIn('cif', $validos)->get()->getResultArray(), 'cif');
        $existen = array_flip(array_map([ApiCompanyEnricher::class, 'normalizeCif'], $existen));

        // Los que quedan tras quitar las bajas por privacidad.
        $permitidos = [];
        foreach (ApiCompanyEnricher::withoutOptedOut(array_map(fn($c) => ['cif' => $c], array_keys($existen))) as $row) {
            $permitidos[$row['cif']] = true;
        }

        $hueco = max(0, $limit - self::count($userId));
        $ahora = date('Y-m-d H:i:s');
        $insertar = [];

        foreach ($validos as $c) {
            if (isset($ya[$c])) {
                $res['already_watching'][] = $c;
            } elseif (!isset($existen[$c]) || !isset($permitidos[$c])) {
                $res['not_found'][] = $c;
            } elseif (count($insertar) >= $hueco) {
                $res['rejected_over_limit'][] = $c;
            } else {
                $insertar[] = ['user_id' => $userId, 'cif' => $c, 'created_at' => $ahora];
                $res['added'][] = $c;
            }
        }

        if ($insertar) {
            // INSERT IGNORE: si dos peticiones a la vez dan de alta el mismo CIF, la
            // UNIQUE (user_id, cif) evita el duplicado sin romper la petición.
            $db->table('api_watchlist')->ignore(true)->insertBatch($insertar);
        }

        return $res;
    }

    public static function remove(int $userId, string $cif): bool
    {
        $cif = ApiCompanyEnricher::normalizeCif($cif);
        $db = \Config\Database::connect();
        $db->table('api_watchlist')->where('user_id', $userId)->where('cif', $cif)->delete();
        return $db->affectedRows() > 0;
    }

    // ------------------------------------------------------------------
    // Eventos
    // ------------------------------------------------------------------

    /**
     * Eventos de las empresas vigiladas desde $since (incluido), ordenados por fecha.
     * @param string[] $tipos subconjunto de TIPOS
     * @return array{events: array, total: int}
     */
    public static function events(int $userId, string $since, array $tipos, int $page, int $perPage, ?string $soloCif = null): array
    {
        $db = \Config\Database::connect();

        $q = $db->table('api_watchlist w')
            ->select('w.cif, c.id, c.company_name, c.estado, c.estado_fecha')
            ->join('companies c', 'c.cif = w.cif', 'left')
            ->where('w.user_id', $userId);
        if ($soloCif !== null) {
            $q->where('w.cif', ApiCompanyEnricher::normalizeCif($soloCif));
        }
        $vigiladas = ApiCompanyEnricher::withoutOptedOut($q->get()->getResultArray());
        if (empty($vigiladas)) {
            return ['events' => [], 'total' => 0];
        }

        $porId = [];
        $porCif = [];
        foreach ($vigiladas as $v) {
            $cif = ApiCompanyEnricher::normalizeCif((string) $v['cif']);
            $porCif[$cif] = $v;
            if (!empty($v['id'])) {
                $porId[(int) $v['id']] = $cif;
            }
        }

        $eventos = [];

        if (in_array('borme_act', $tipos, true) && $porId) {
            foreach (array_chunk(array_keys($porId), 500) as $ids) {
                $actos = $db->table('borme_posts')
                    ->select('id, company_id, borme_date, act_types, description, url_pdf')
                    ->whereIn('company_id', $ids)
                    ->where('borme_date >=', $since)
                    ->get()->getResultArray();
                foreach ($actos as $a) {
                    $cif = $porId[(int) $a['company_id']] ?? null;
                    if ($cif === null) {
                        continue;
                    }
                    $eventos[] = [
                        'id'           => 'borme_act:' . $a['id'],
                        'type'         => 'borme_act',
                        'date'         => substr((string) $a['borme_date'], 0, 10),
                        'cif'          => $cif,
                        'company_name' => $porCif[$cif]['company_name'] ?? null,
                        'data'         => [
                            'act_types'   => $a['act_types'],
                            'description' => $a['description'],
                            'url_pdf'     => $a['url_pdf'],
                        ],
                    ];
                }
            }
        }

        if (in_array('status_change', $tipos, true)) {
            foreach ($porCif as $cif => $v) {
                $f = !empty($v['estado_fecha']) ? substr((string) $v['estado_fecha'], 0, 10) : null;
                if ($f === null || $f < $since) {
                    continue;
                }
                $st = ApiCompanyEnricher::statusFor((string) ($v['estado'] ?? ''), $f, null);
                $eventos[] = [
                    'id'           => 'status_change:' . $cif . ':' . $f,
                    'type'         => 'status_change',
                    'date'         => $f,
                    'cif'          => $cif,
                    'company_name' => $v['company_name'] ?? null,
                    'data'         => [
                        'status'      => $v['estado'],
                        'status_code' => $st['status_code'],
                    ],
                ];
            }
        }

        if (in_array('risk_level_change', $tipos, true)) {
            foreach (array_chunk(array_keys($porCif), 500) as $cifs) {
                $hist = $db->table('company_risk_profiles_history')
                    ->select('cif, risk_level, calculated_at, model_config_hash')
                    ->whereIn('cif', $cifs)
                    ->orderBy('calculated_at', 'ASC')
                    ->get()->getResultArray();
                $actual = $db->table('company_risk_profiles')
                    ->select('cif, risk_level, updated_at')
                    ->whereIn('cif', $cifs)
                    ->get()->getResultArray();
                $histPorCif = [];
                foreach ($hist as $h) {
                    $histPorCif[ApiCompanyEnricher::normalizeCif((string) $h['cif'])][] = $h;
                }
                foreach ($actual as $a) {
                    $cif = ApiCompanyEnricher::normalizeCif((string) $a['cif']);
                    foreach (self::riskEvents($histPorCif[$cif] ?? [], $a, $since) as $ev) {
                        $ev['cif'] = $cif;
                        $ev['company_name'] = $porCif[$cif]['company_name'] ?? null;
                        $ev['id'] = 'risk_level_change:' . $cif . ':' . $ev['date'];
                        $eventos[] = $ev;
                    }
                }
            }
        }

        $eventos = self::sortEvents($eventos);
        $total = count($eventos);
        return [
            'events' => array_slice($eventos, ($page - 1) * $perPage, $perPage),
            'total'  => $total,
        ];
    }

    /**
     * Cambios de nivel de riesgo a partir del histórico (versiones anteriores, con su
     * fecha de cálculo) y del perfil actual. Pura: se prueba aparte.
     *
     * model_change: true cuando el cambio coincide con un cambio de configuración del
     * modelo (recalculo masivo), para que el cliente pueda distinguirlo de un cambio en
     * la empresa; null si no se puede saber.
     */
    public static function riskEvents(array $historial, ?array $actual, string $since): array
    {
        $serie = [];
        foreach ($historial as $h) {
            $serie[] = [
                'level' => strtoupper((string) $h['risk_level']),
                'date'  => substr((string) $h['calculated_at'], 0, 10),
                'hash'  => $h['model_config_hash'] ?? null,
            ];
        }
        if ($actual !== null && !empty($actual['risk_level'])) {
            $serie[] = [
                'level' => strtoupper((string) $actual['risk_level']),
                'date'  => substr((string) ($actual['updated_at'] ?? ''), 0, 10),
                'hash'  => null,
            ];
        }
        usort($serie, fn($a, $b) => strcmp($a['date'], $b['date']));

        $out = [];
        for ($i = 1, $n = count($serie); $i < $n; $i++) {
            $prev = $serie[$i - 1];
            $cur  = $serie[$i];
            if ($cur['level'] === $prev['level'] || $cur['date'] === '' || $cur['date'] < $since) {
                continue;
            }
            $modelChange = null;
            if ($prev['hash'] !== null && $cur['hash'] !== null) {
                $modelChange = $prev['hash'] !== $cur['hash'];
            }
            $out[] = [
                'type' => 'risk_level_change',
                'date' => $cur['date'],
                'data' => [
                    'from'         => $prev['level'],
                    'to'           => $cur['level'],
                    'model_change' => $modelChange,
                ],
            ];
        }
        return $out;
    }

    /** Orden estable: fecha, tipo, id. */
    public static function sortEvents(array $eventos): array
    {
        usort($eventos, function ($a, $b) {
            return [$a['date'], $a['type'], $a['id']] <=> [$b['date'], $b['type'], $b['id']];
        });
        return $eventos;
    }

    /** Normaliza ?since=: por defecto 7 días atrás; nunca más de 90. Devuelve null si no es una fecha. */
    public static function parseSince(?string $raw, ?string $hoy = null): ?string
    {
        $hoy = $hoy ?? date('Y-m-d');
        $min = date('Y-m-d', strtotime($hoy . ' -' . self::MAX_DIAS_ATRAS . ' days'));
        if ($raw === null || trim($raw) === '') {
            return date('Y-m-d', strtotime($hoy . ' -' . self::DIAS_POR_DEFECTO . ' days'));
        }
        $raw = trim($raw);
        $d = \DateTime::createFromFormat('!Y-m-d', substr($raw, 0, 10));
        if (!$d || $d->format('Y-m-d') !== substr($raw, 0, 10)) {
            return null;
        }
        $f = $d->format('Y-m-d');
        return $f < $min ? $min : $f;
    }
}
