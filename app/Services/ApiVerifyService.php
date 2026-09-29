<?php

namespace App\Services;

/**
 * Verificación KYB en una llamada (28-09-2026): GET /api/v1/companies/verify.
 *
 * Junta lo que ya existe (estado normalizado y administradores vigentes de
 * ApiCompanyEnricher, perfil de riesgo del motor) y añade VIES. Devuelve comprobaciones,
 * alertas y una recomendación (pass / review / fail) que el cliente puede usar tal cual o
 * ignorar y decidir con los datos.
 *
 * Pro: comprobaciones básicas. Business: además, nivel de riesgo. Coste: 2 consultas.
 *
 * Listado de deudores de la AEAT (tabla aeat_debtors): desactivado por decisión de Adrián
 * (29-09-2026), por prudencia legal. Solo se muestra con AEAT_DEBTORS_ENABLED=true en el .env.
 */
class ApiVerifyService
{
    /** Estados con los que no conviene dar de alta a la empresa. */
    public const ESTADOS_FAIL = ['EXTINCT', 'DISSOLVED', 'IN_LIQUIDATION', 'REGISTRY_CLOSED', 'MERGED', 'INACTIVE'];
    /** Estados que piden una revisión manual. */
    public const ESTADOS_REVIEW = ['INSOLVENCY', 'UNKNOWN'];

    public const UMBRAL_NOMBRE = 80;

    /**
     * Meses que se muestra el listado de deudores de la AEAT desde su publicación. El art. 95 bis
     * LGT deja de hacerlo accesible a los tres meses; aquí se sigue ese plazo por prudencia.
     * Se cambia con AEAT_DEBTORS_VISIBLE_MONTHS en el .env (0 = sin límite) tras consulta legal.
     */
    public const AEAT_VISIBLE_MONTHS = 3;

    private const FORMAS = [
        'SOCIEDAD LIMITADA UNIPERSONAL', 'SOCIEDAD LIMITADA LABORAL', 'SOCIEDAD LIMITADA NUEVA EMPRESA', 'SOCIEDAD LIMITADA',
        'SOCIEDAD ANONIMA UNIPERSONAL', 'SOCIEDAD ANONIMA LABORAL', 'SOCIEDAD ANONIMA', 'SOCIEDAD COOPERATIVA',
        'SOCIEDAD CIVIL', 'SOCIEDAD COMANDITARIA', 'S L U', 'S L L', 'S L N E', 'S A U', 'S A L', 'S L', 'S A', 'S COOP', 'SCOOP', 'SLU', 'SLL', 'SLNE',
        'SAU', 'SAL', 'SL', 'SA', 'SC', 'SCP', 'SRL', 'COOP',
    ];

    // ------------------------------------------------------------------
    // Nombre
    // ------------------------------------------------------------------

    /** Razón social comparable: mayúsculas, sin tildes, sin signos y sin forma jurídica. */
    public static function normalizeName(string $s): string
    {
        $s = mb_strtoupper(trim($s), 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N', 'Ç' => 'C', 'À' => 'A', 'È' => 'E', 'Ò' => 'O', 'Ï' => 'I']);
        $s = preg_replace('/[^A-Z0-9 ]+/', ' ', $s);
        $s = ' ' . preg_replace('/\s+/', ' ', $s) . ' ';
        foreach (self::FORMAS as $f) {
            $s = str_replace(' ' . $f . ' ', ' ', $s);
        }
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    /** Parecido 0-100 entre el nombre dado y la razón social. */
    public static function nameScore(string $dado, string $oficial): int
    {
        $a = self::normalizeName($dado);
        $b = self::normalizeName($oficial);
        if ($a === '' || $b === '') {
            return 0;
        }
        if ($a === $b) {
            return 100;
        }
        $ta = array_values(array_unique(array_filter(explode(' ', $a), fn($t) => strlen($t) >= 2)));
        $tb = array_values(array_unique(array_filter(explode(' ', $b), fn($t) => strlen($t) >= 2)));
        $comunes = count(array_intersect($ta, $tb));
        $tokens = ($ta && $tb) ? 100 * $comunes / max(count($ta), count($tb)) : 0;
        similar_text($a, $b, $pct);
        return (int) round(max($tokens, $pct));
    }

    // ------------------------------------------------------------------
    // Persona
    // ------------------------------------------------------------------

    /**
     * ¿Es $persona uno de los administradores vigentes? El BORME escribe "APELLIDOS NOMBRE";
     * se acepta cualquier orden. Cuenta como coincidencia si todas las palabras dadas
     * (de 2 letras o más, al menos dos) están en el nombre del administrador.
     *
     * @param array $vigentes [[name, position, since], ...]
     */
    public static function matchPerson(string $persona, array $vigentes): ?array
    {
        $tp = array_values(array_filter(explode(' ', self::normalizeName($persona)), fn($t) => strlen($t) >= 2));
        if (count($tp) < 2) {
            return null;
        }
        foreach ($vigentes as $v) {
            $tv = explode(' ', self::normalizeName((string) $v['name']));
            if (count(array_diff($tp, $tv)) === 0) {
                return $v;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------
    // VIES
    // ------------------------------------------------------------------

    /**
     * NIF-IVA intracomunitario en VIES (servicio de la Comisión Europea). Caché de 24 h.
     * @return array{checked:bool, valid:?bool, source:string, error:?string}
     */
    public static function vies(string $cif): array
    {
        $cif = ApiCompanyEnricher::normalizeCif($cif);
        $key = 'api_vies_v1_' . $cif;
        $hit = cache()->get($key);
        if (is_array($hit)) {
            return $hit;
        }
        $out = ['checked' => false, 'valid' => null, 'source' => 'VIES', 'error' => null];
        try {
            $ch = curl_init('https://ec.europa.eu/taxation_customs/vies/rest-api/ms/ES/vat/' . rawurlencode($cif));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            ]);
            $body = curl_exec($ch);
            $st = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $err = curl_errno($ch) ? curl_error($ch) : null;
            curl_close($ch);
            $j = is_string($body) ? json_decode($body, true) : null;
            if ($err === null && $st === 200 && is_array($j) && array_key_exists('isValid', $j)) {
                $userError = (string) ($j['userError'] ?? 'VALID');
                if (in_array($userError, ['VALID', 'INVALID'], true)) {
                    $out = ['checked' => true, 'valid' => (bool) $j['isValid'], 'source' => 'VIES', 'error' => null];
                    cache()->save($key, $out, 86400);
                    return $out;
                }
                $out['error'] = 'VIES no disponible ahora mismo (' . $userError . ')';
            } else {
                $out['error'] = 'VIES no disponible ahora mismo';
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiVerifyService::vies] ' . $e->getMessage());
            $out['error'] = 'VIES no disponible ahora mismo';
        }
        return $out; // sin caché: se reintenta en la próxima llamada
    }

    // ------------------------------------------------------------------
    // Deudores de la AEAT (art. 95 bis LGT)
    // ------------------------------------------------------------------

    /**
     * Último listado de deudores visible y si el CIF está en él. Null si no hay listado visible
     * (tabla vacía o fuera del plazo), para que la respuesta no diga "no figura" sin haberlo mirado.
     *
     * @return array{listed:bool, list:string, published_at:string, reference_date:string, amount_eur:?float}|null
     */
    public static function aeatDebtor(string $cif, ?string $hoy = null): ?array
    {
        if (!self::aeatEnabled()) {
            return null;
        }
        $cif = ApiCompanyEnricher::normalizeCif($cif);
        $hoy = $hoy ?? date('Y-m-d');
        try {
            $db = \Config\Database::connect();
            $lista = cache()->get('api_aeat_debtors_list_v1');
            if (!is_array($lista)) {
                $lista = $db->table('aeat_debtors')->select('list_year, published_at, reference_date')
                    ->orderBy('list_year', 'DESC')->limit(1)->get()->getRowArray() ?: [];
                cache()->save('api_aeat_debtors_list_v1', $lista, 3600);
            }
            if (!$lista || !self::aeatVisible((string) $lista['published_at'], $hoy)) {
                return null;
            }
            $row = $db->table('aeat_debtors')->select('amount_eur')
                ->where('cif', $cif)->where('list_year', (int) $lista['list_year'])->get()->getRowArray();
            return [
                'listed'         => (bool) $row,
                'list'           => 'AEAT_95BIS_' . (int) $lista['list_year'],
                'published_at'   => (string) $lista['published_at'],
                'reference_date' => (string) $lista['reference_date'],
                'amount_eur'     => $row ? (float) $row['amount_eur'] : null,
            ];
        } catch (\Throwable $e) {
            log_message('error', '[ApiVerifyService::aeatDebtor] ' . $e->getMessage());
            return null;
        }
    }

    /** Desactivado salvo AEAT_DEBTORS_ENABLED=true en el .env. */
    public static function aeatEnabled(): bool
    {
        return filter_var(env('AEAT_DEBTORS_ENABLED') ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /** ¿Sigue dentro del plazo en que se muestra el listado publicado en $publicado? */
    public static function aeatVisible(string $publicado, string $hoy, ?int $meses = null): bool
    {
        if ($meses === null) {
            $env = env('AEAT_DEBTORS_VISIBLE_MONTHS');
            $meses = ($env === null || $env === '') ? self::AEAT_VISIBLE_MONTHS : (int) $env;
        }
        if ($meses <= 0) {
            return true;
        }
        $hasta = date('Y-m-d', strtotime($publicado . ' +' . $meses . ' months'));
        return $hoy < $hasta;
    }

    // ------------------------------------------------------------------
    // Decisión
    // ------------------------------------------------------------------

    /**
     * Alertas y recomendación a partir de las comprobaciones. Pura: se prueba aparte.
     *
     * @param array $in [status_code, name_score (?int), person_is_admin (?bool),
     *                   vat_valid (?bool, null si no se comprobó), risk_level (?string),
     *                   last_accounts_year (?int), aeat_debtor (?array de aeatDebtor), hoy (Y-m-d)]
     * @return array{flags: array, decision_hint: string}
     */
    public static function decide(array $in): array
    {
        $flags = [];
        $peor = 'pass';
        $sube = function (string $a) use (&$peor) {
            $orden = ['pass' => 0, 'review' => 1, 'fail' => 2];
            if ($orden[$a] > $orden[$peor]) {
                $peor = $a;
            }
        };

        $st = (string) ($in['status_code'] ?? 'UNKNOWN');
        if (in_array($st, self::ESTADOS_FAIL, true)) {
            $flags[] = ['code' => 'STATUS_' . $st, 'severity' => 'critical', 'message' => 'La sociedad no está operativa según el Registro o el BORME (' . $st . ').'];
            $sube('fail');
        } elseif ($st === 'INSOLVENCY') {
            $flags[] = ['code' => 'STATUS_INSOLVENCY', 'severity' => 'high', 'message' => 'Concurso de acreedores en curso.'];
            $sube('review');
        } elseif ($st === 'UNKNOWN') {
            $flags[] = ['code' => 'STATUS_UNKNOWN', 'severity' => 'medium', 'message' => 'No hay datos suficientes para confirmar el estado.'];
            $sube('review');
        } elseif ($st === 'PRESUMED_ACTIVE') {
            $flags[] = ['code' => 'STATUS_PRESUMED', 'severity' => 'low', 'message' => 'El Registro no da estado; el BORME no tiene hechos que la cierren.'];
        }

        if (isset($in['name_score']) && $in['name_score'] !== null && $in['name_score'] < self::UMBRAL_NOMBRE) {
            $flags[] = ['code' => 'NAME_MISMATCH', 'severity' => 'high', 'message' => 'El nombre indicado no coincide con la razón social.'];
            $sube('review');
        }

        if (array_key_exists('person_is_admin', $in) && $in['person_is_admin'] === false) {
            $flags[] = ['code' => 'PERSON_NOT_ADMIN', 'severity' => 'high', 'message' => 'La persona indicada no figura entre los administradores vigentes.'];
            $sube('review');
        }

        if (array_key_exists('vat_valid', $in) && $in['vat_valid'] === false) {
            $flags[] = ['code' => 'VAT_NOT_IN_VIES', 'severity' => 'medium', 'message' => 'El NIF no está dado de alta en VIES (no puede facturar sin IVA a otros países de la UE).'];
            $sube('review');
        }

        if (!empty($in['risk_level']) && strtoupper((string) $in['risk_level']) === 'ALTO') {
            $flags[] = ['code' => 'RISK_HIGH', 'severity' => 'high', 'message' => 'Nivel de riesgo alto según el perfil de riesgo.'];
            $sube('review');
        }

        $aeat = $in['aeat_debtor'] ?? null;
        if (is_array($aeat) && !empty($aeat['listed'])) {
            $importe = number_format((float) ($aeat['amount_eur'] ?? 0), 2, ',', '.');
            $flags[] = ['code' => 'AEAT_DEBTOR', 'severity' => 'high', 'message' => 'Figura en el listado de deudores a la Hacienda Pública (art. 95 bis LGT) publicado el ' . date('d-m-Y', strtotime((string) $aeat['published_at'])) . ', con ' . $importe . ' € pendientes a ' . date('d-m-Y', strtotime((string) $aeat['reference_date'])) . '.'];
            $sube('review');
        }

        // El año de cuentas viene de una captura antigua: se informa, no decide.
        $y = $in['last_accounts_year'] ?? null;
        $hoy = (int) substr((string) ($in['hoy'] ?? date('Y-m-d')), 0, 4);
        if ($y !== null && (int) $y > 1900 && (int) $y < $hoy - 4) {
            $flags[] = ['code' => 'OLD_ACCOUNTS_SEEN', 'severity' => 'low', 'message' => 'Las últimas cuentas que constan en nuestra base son de ' . (int) $y . '. Puede haber depósitos posteriores.'];
        }

        return ['flags' => $flags, 'decision_hint' => $peor];
    }
}
