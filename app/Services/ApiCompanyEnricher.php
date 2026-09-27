<?php

namespace App\Services;

/**
 * Datos añadidos a las respuestas de empresa de la API (26-09-2026).
 *
 * Regla de oro de la API: solo se AÑADEN campos. Nada de lo que ya devolvía
 * /companies cambia de nombre, tipo ni valor. Lo único que cambia de contenido es
 * `administrators` con admin=true, que prometía "administradores actuales" y
 * devolvía a todo el que alguna vez fue nombrado (ver currentAdministrators()).
 *
 * Qué aporta:
 *  - Bajas por privacidad: la ficha web da 404 a los CIF de company_privacy_optouts
 *    y la API los seguía sirviendo.
 *  - status_code / status_source / status_date: el estado normalizado. companies.estado
 *    llega en muchas grafías ("Activa", "ACTIVA", "Extinción"...) y vacío en ~1,96 M de
 *    empresas. Se combina con el estado legal del motor de riesgo, con la misma regla
 *    de pesos que la ficha (company_estado_registral()).
 *  - financials: tramo de facturación orientativo (ventas_raw) y último ejercicio
 *    depositado que hemos visto (ult_cuentas_anio). Solo planes de pago o con saldo.
 *  - administrators vigentes: nombramientos de company_administrators (fechados por su
 *    acto del BORME) menos los ceses, dimisiones y revocaciones que el BORME publica en
 *    el texto de los actos.
 */
class ApiCompanyEnricher
{
    /** Versión de las cachés de este servicio: subirla si cambia el cálculo. */
    private const CACHE_V = 'v1';

    // ------------------------------------------------------------------
    // Bajas por privacidad
    // ------------------------------------------------------------------

    public static function normalizeCif(string $cif): string
    {
        $c = strtoupper(trim($cif));
        $c = preg_replace('/^ES/', '', $c);
        return (string) preg_replace('/[^A-Z0-9]/', '', $c);
    }

    public static function isOptedOut(string $cif): bool
    {
        $cif = self::normalizeCif($cif);
        if ($cif === '') {
            return false;
        }
        $key = 'api_optout_' . self::CACHE_V . '_' . $cif;
        $hit = cache()->get($key);
        if ($hit !== null) {
            return (bool) $hit;
        }
        try {
            $db = \Config\Database::connect();
            $out = $db->table('company_privacy_optouts')->where('cif', $cif)->countAllResults() > 0;
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::isOptedOut] ' . $e->getMessage());
            return false;
        }
        cache()->save($key, $out ? 1 : 0, 3600);
        return $out;
    }

    /**
     * Quita de una lista de empresas las que han pedido la baja. Una sola consulta.
     * @param array<int,array> $companies filas con 'cif'
     */
    public static function withoutOptedOut(array $companies): array
    {
        $cifs = [];
        foreach ($companies as $c) {
            if (!empty($c['cif'])) {
                $cifs[] = self::normalizeCif((string) $c['cif']);
            }
        }
        if (empty($cifs)) {
            return $companies;
        }
        try {
            $db = \Config\Database::connect();
            $rows = $db->table('company_privacy_optouts')->select('cif')->whereIn('cif', array_unique($cifs))->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::withoutOptedOut] ' . $e->getMessage());
            return $companies;
        }
        if (empty($rows)) {
            return $companies;
        }
        $fuera = array_flip(array_map(fn($r) => self::normalizeCif((string) $r['cif']), $rows));
        $out = [];
        foreach ($companies as $k => $c) {
            if (isset($fuera[self::normalizeCif((string) ($c['cif'] ?? ''))])) {
                continue;
            }
            $out[$k] = $c;
        }
        return array_values($out);
    }

    // ------------------------------------------------------------------
    // Enriquecimiento de las fichas
    // ------------------------------------------------------------------

    /**
     * Añade los campos nuevos a una lista de fichas (con 'id', 'cif' y 'status').
     * Se llama DESPUÉS del enmascarado del Free y ANTES de filter_company_data()
     * (que quita 'id').
     *
     * @param array<int,array> $companies
     * @param bool $fullAccess plan de pago o saldo en el monedero
     */
    public static function enrich(array $companies, bool $fullAccess): array
    {
        $ids = [];
        $cifs = [];
        foreach ($companies as $c) {
            if (!empty($c['id'])) {
                $ids[] = (int) $c['id'];
            }
            if (!empty($c['cif'])) {
                $cifs[] = self::normalizeCif((string) $c['cif']);
            }
        }
        if (empty($ids)) {
            return $companies;
        }

        // Por CIF y no por id: la ficha puede venir de una caché de 30 días con un id
        // antiguo (BD recargada, filas renumeradas). El CIF es único y estable.
        $extras   = self::loadExtras($cifs);
        $profiles = self::loadLegalStates($cifs);
        $idsActuales = [];
        foreach ($extras as $ex) {
            $idsActuales[] = (int) $ex['id'];
        }
        $adminCounts = $fullAccess ? [] : self::countAdministrators($idsActuales ?: $ids);

        foreach ($companies as &$c) {
            $cif = self::normalizeCif((string) ($c['cif'] ?? ''));
            $ex  = $extras[$cif] ?? [];
            if (!empty($ex['id'])) {
                $c['id'] = (int) $ex['id']; // id actual (filter_company_data lo quita después)
            }
            $id  = (int) ($c['id'] ?? 0);

            $st = self::statusFor((string) ($c['status'] ?? ''), $ex['estado_fecha'] ?? null, $profiles[$cif] ?? null);
            $c['status_code']   = $st['status_code'];
            $c['status_source'] = $st['status_source'];
            $c['status_date']   = $st['status_date'];

            $band = self::sizeBand($ex['ventas_raw'] ?? null);
            $year = isset($ex['ult_cuentas_anio']) && (int) $ex['ult_cuentas_anio'] > 1900 ? (int) $ex['ult_cuentas_anio'] : null;

            if ($fullAccess) {
                $c['financials'] = [
                    'size_band'          => $band['code'],
                    'size_band_label'    => $band['label'],
                    'last_accounts_year' => $year,
                ];
            } elseif (isset($c['upsell_opportunities']) && is_array($c['upsell_opportunities'])) {
                // Free: enseñar QUÉ hay sin darlo (campo nuevo dentro de upsell_opportunities).
                $c['upsell_opportunities']['datos_pro'] = [
                    'tramo_facturacion'        => $band['code'] !== null,
                    'ultimo_ejercicio_cuentas' => $year !== null,
                    'administradores'          => (int) ($adminCounts[$id] ?? 0),
                ];
            }
        }
        unset($c);

        return $companies;
    }

    /** id, ventas_raw, ult_cuentas_anio y estado_fecha por CIF (índice único). */
    private static function loadExtras(array $cifs): array
    {
        $cifs = array_values(array_unique(array_filter($cifs)));
        if (empty($cifs)) {
            return [];
        }
        try {
            $db = \Config\Database::connect();
            $rows = $db->table('companies')
                ->select('id, cif, ventas_raw, ult_cuentas_anio, estado_fecha')
                ->whereIn('cif', $cifs)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::loadExtras] ' . $e->getMessage());
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $out[self::normalizeCif((string) $r['cif'])] = $r;
        }
        return $out;
    }

    /** Estado legal y eventos de estado del motor, por CIF. Caché de 12 h por CIF. */
    private static function loadLegalStates(array $cifs): array
    {
        $out = [];
        $faltan = [];
        foreach (array_unique($cifs) as $cif) {
            if ($cif === '') {
                continue;
            }
            $hit = cache()->get('api_legal_' . self::CACHE_V . '_' . $cif);
            if (is_array($hit)) {
                $out[$cif] = $hit ?: null;
            } else {
                $faltan[] = $cif;
            }
        }
        if (empty($faltan)) {
            return $out;
        }
        try {
            $db = \Config\Database::connect();
            $rows = $db->table('company_risk_profiles')
                ->select('cif, risk_profile')
                ->whereIn('cif', $faltan)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::loadLegalStates] ' . $e->getMessage());
            return $out;
        }
        $vistos = [];
        foreach ($rows as $r) {
            $cif = self::normalizeCif((string) $r['cif']);
            $p = json_decode((string) $r['risk_profile'], true);
            $slim = [];
            if (is_array($p)) {
                $slim['legal_state'] = isset($p['legal_state']) ? (string) $p['legal_state'] : null;
                $slim['events'] = [];
                foreach (($p['canonical_events'] ?? []) as $ev) {
                    $code = strtoupper(trim((string) ($ev['code'] ?? '')));
                    if (strpos($code, 'LEGAL_STATE_') === 0) {
                        $slim['events'][] = ['code' => $code, 'date' => $ev['date'] ?? ($ev['event_date'] ?? null)];
                    }
                }
            }
            $out[$cif] = $slim ?: null;
            $vistos[$cif] = true;
            cache()->save('api_legal_' . self::CACHE_V . '_' . $cif, $slim, 43200);
        }
        foreach ($faltan as $cif) {
            if (!isset($vistos[$cif])) {
                $out[$cif] = null;
                cache()->save('api_legal_' . self::CACHE_V . '_' . $cif, [], 43200);
            }
        }
        return $out;
    }

    /** Número de personas distintas con algún nombramiento (para el gancho del Free). */
    private static function countAdministrators(array $ids): array
    {
        try {
            $db = \Config\Database::connect();
            $rows = $db->table('company_administrators')
                ->select('company_id, COUNT(DISTINCT name) AS n', false)
                ->whereIn('company_id', array_unique($ids))
                ->groupBy('company_id')
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::countAdministrators] ' . $e->getMessage());
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['company_id']] = (int) $r['n'];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Estado normalizado
    // ------------------------------------------------------------------

    /**
     * Valores de status_code:
     *   ACTIVE           el Registro la da como activa y el BORME no dice lo contrario
     *   PRESUMED_ACTIVE  sin estado en el Registro, pero el BORME no tiene ningún hecho
     *                    que la cierre (el motor la ve normal)
     *   INSOLVENCY       concurso de acreedores en curso
     *   IN_LIQUIDATION   en liquidación
     *   DISSOLVED        disuelta
     *   REGISTRY_CLOSED  hoja registral cerrada (falta de cuentas, NIF revocado...)
     *   MERGED           absorbida en una fusión
     *   INACTIVE         inactiva
     *   EXTINCT          extinguida
     *   UNKNOWN          sin datos para decidir
     */
    public static function statusFor(string $statusRaw, ?string $estadoFecha, ?array $legal): array
    {
        helper('company');

        $events = [];
        if (is_array($legal)) {
            foreach (($legal['events'] ?? []) as $ev) {
                $events[] = $ev;
            }
            $ls = strtoupper(trim((string) ($legal['legal_state'] ?? '')));
            if ($ls !== '') {
                $events[] = ['code' => strpos($ls, 'LEGAL_STATE_') === 0 ? $ls : 'LEGAL_STATE_' . $ls, 'date' => null];
            }
        }

        $motor    = company_estado_registral(['status' => ''], ['data' => ['canonical_events' => $events]]);
        $registro = company_estado_registral(['status' => $statusRaw], null);
        $final    = company_estado_registral(['status' => $statusRaw], ['data' => ['canonical_events' => $events]]);

        $map = [
            'extinguida'   => 'EXTINCT',
            'concurso'     => 'INSOLVENCY',
            'liquidacion'  => 'IN_LIQUIDATION',
            'disuelta'     => 'DISSOLVED',
            'hoja_cerrada' => 'REGISTRY_CLOSED',
            'activa'       => 'ACTIVE',
        ];

        $clave = $final['clave'];
        $code = $map[$clave] ?? null;
        $source = null;
        $date = null;

        if ($code !== null && $clave !== 'activa') {
            // Un hecho adverso: ¿lo aporta el motor o el Registro?
            if ($motor['clave'] === $clave) {
                $source = 'borme_analysis';
                foreach ($events as $ev) {
                    if (!empty($ev['date']) && self::legalCodeToClave((string) $ev['code']) === $clave) {
                        $date = substr((string) $ev['date'], 0, 10);
                        break;
                    }
                }
            } else {
                $source = 'registry';
                $date = $estadoFecha ?: null;
            }
        } elseif ($clave === 'activa') {
            $source = 'registry';
            $date = $estadoFecha ?: null;
        } else {
            $s = mb_strtolower($statusRaw, 'UTF-8');
            if (strpos($s, 'fusi') !== false) {
                $code = 'MERGED';
                $source = 'registry';
                $date = $estadoFecha ?: null;
            } elseif (strpos($s, 'inactiv') !== false) {
                $code = 'INACTIVE';
                $source = 'registry';
                $date = $estadoFecha ?: null;
            } elseif (is_array($legal) && in_array(self::bareLegal((string) ($legal['legal_state'] ?? '')), ['NORMAL', 'RECOVERED_RESOLVED'], true)) {
                $code = 'PRESUMED_ACTIVE';
                $source = 'borme_analysis';
            } else {
                $code = 'UNKNOWN';
            }
        }

        return ['status_code' => $code, 'status_source' => $source, 'status_date' => $date];
    }

    private static function bareLegal(string $s): string
    {
        $s = strtoupper(trim($s));
        return strpos($s, 'LEGAL_STATE_') === 0 ? substr($s, 12) : $s;
    }

    private static function legalCodeToClave(string $code): ?string
    {
        $b = self::bareLegal($code);
        if ($b === 'EXTINTA') return 'extinguida';
        if ($b === 'CONCURSO_ACTIVO') return 'concurso';
        if ($b === 'LIQUIDACION') return 'liquidacion';
        if ($b === 'DISUELTA') return 'disuelta';
        if (strpos($b, 'REGISTRY_CLOSURE') === 0) return 'hoja_cerrada';
        return null;
    }

    // ------------------------------------------------------------------
    // Tramo de facturación
    // ------------------------------------------------------------------

    /**
     * ventas_raw viene de un scrapeo antiguo en formato corto ('0.5M €', '1M €',
     * '2.5M €', '-'). Comprobado el 26-09-2026: Mercadona, Inditex, Telefónica,
     * Santander y BBVA salen todas con '2.5M €', el valor más alto. La escala está
     * cortada arriba: '2.5M €' incluye todo lo que pasa de 1 M€. Por eso el tramo más
     * alto se publica como "más de 1 M€" y no como "1-2,5 M€". Es orientativo.
     */
    public static function sizeBand(?string $raw): array
    {
        $none = ['code' => null, 'label' => null];
        if ($raw === null) {
            return $none;
        }
        $r = trim($raw);
        if ($r === '' || $r === '-') {
            return $none;
        }
        $low = mb_strtolower($r, 'UTF-8');
        if (strpos($low, 'sin ventas') !== false) {
            return ['code' => 'NO_REVENUE', 'label' => 'Sin ventas'];
        }
        if (!preg_match('/(\d+(?:[.,]\d+)?)/', $r, $m)) {
            return $none;
        }
        $n = (float) str_replace(',', '.', $m[1]);
        $esMillones = (bool) preg_match('/\bm\b|m\s*€|m\.€|mill/i', $r);
        if (!$esMillones) {
            // Importe en euros ("0 €", "350000 €"): pasar a millones.
            $n = $n / 1000000;
        }
        if ($n <= 0) {
            return ['code' => 'NO_REVENUE', 'label' => 'Sin ventas'];
        }
        if ($n <= 0.5) {
            return ['code' => 'LT_500K', 'label' => 'Menos de 0,5 M€'];
        }
        if ($n <= 1) {
            return ['code' => '500K_1M', 'label' => 'Entre 0,5 y 1 M€'];
        }
        return ['code' => 'GT_1M', 'label' => 'Más de 1 M€'];
    }

    // ------------------------------------------------------------------
    // Administradores vigentes
    // ------------------------------------------------------------------

    /**
     * Administradores y cargos vigentes por empresa: [company_id => [[name, position, since], ...]].
     * Caché de 12 h por empresa.
     */
    public static function currentAdministrators(array $companyIds): array
    {
        $out = [];
        $faltan = [];
        foreach (array_unique(array_map('intval', $companyIds)) as $id) {
            if ($id <= 0) {
                continue;
            }
            $hit = cache()->get('api_admins_' . self::CACHE_V . '_' . $id);
            if (is_array($hit)) {
                $out[$id] = $hit;
            } else {
                $faltan[] = $id;
            }
        }
        if (empty($faltan)) {
            return $out;
        }

        try {
            $db = \Config\Database::connect();
            $nombramientos = $db->table('company_administrators ca')
                ->select('ca.company_id, ca.name, ca.position, ca.action, bp.borme_date, ca.post_id')
                ->join('borme_posts bp', 'bp.id = ca.post_id', 'left')
                ->whereIn('ca.company_id', $faltan)
                ->get()->getResultArray();

            $actos = $db->table('borme_posts')
                ->select('id, company_id, borme_date, description')
                ->whereIn('company_id', $faltan)
                ->groupStart()
                    ->like('description', 'Ceses', 'both', null, true)
                    ->orLike('description', 'Dimisiones', 'both', null, true)
                    ->orLike('description', 'Revocaciones', 'both', null, true)
                ->groupEnd()
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::currentAdministrators] ' . $e->getMessage());
            return $out;
        }

        $porEmpresa = [];
        foreach ($nombramientos as $n) {
            $porEmpresa[(int) $n['company_id']]['nom'][] = $n;
        }
        foreach ($actos as $a) {
            $porEmpresa[(int) $a['company_id']]['act'][] = $a;
        }

        foreach ($faltan as $id) {
            $vigentes = self::resolveCurrent($porEmpresa[$id]['nom'] ?? [], $porEmpresa[$id]['act'] ?? []);
            $out[$id] = $vigentes;
            cache()->save('api_admins_' . self::CACHE_V . '_' . $id, $vigentes, 43200);
        }

        return $out;
    }

    /**
     * Cruza nombramientos y bajas y devuelve los vigentes agrupados por persona.
     * Pura (sin BD): se prueba aparte.
     *
     * @param array $nombramientos filas [name, position, action, borme_date, post_id]
     * @param array $actos filas de borme_posts [id, borme_date, description]
     */
    public static function resolveCurrent(array $nombramientos, array $actos): array
    {
        // Eventos por persona+cargo: [fecha, orden, tipo]. En un mismo acto el BORME
        // lista los ceses antes que los nombramientos, y un cese y nombramiento el
        // mismo día es un cambio de cargo o una renovación: el nombramiento gana.
        $eventos = [];
        $etiquetas = [];

        foreach ($nombramientos as $n) {
            $name = trim((string) ($n['name'] ?? ''));
            $pos  = trim((string) ($n['position'] ?? ''));
            if ($name === '') {
                continue;
            }
            $accion = self::classifyHeading((string) ($n['action'] ?? 'Nombramientos'));
            $tipo = in_array($accion, ['cese', 'revocacion'], true) ? 'baja' : 'alta';
            $k = self::personKey($name) . '|' . self::positionKey($pos);
            $eventos[$k][] = [(string) ($n['borme_date'] ?? ''), (int) ($n['post_id'] ?? 0), $tipo === 'baja' ? 0 : 1, $tipo];
            if (!isset($etiquetas[$k])) {
                $etiquetas[$k] = ['name' => $name, 'position' => $pos];
            }
        }

        foreach ($actos as $a) {
            $fecha = (string) ($a['borme_date'] ?? '');
            foreach (self::parseOfficerActs((string) ($a['description'] ?? '')) as $acto) {
                if (!in_array($acto['section'], ['cese', 'revocacion'], true)) {
                    continue;
                }
                $pk = self::positionKey($acto['position']);
                foreach ($acto['names'] as $nm) {
                    $k = self::personKey($nm) . '|' . $pk;
                    if (isset($etiquetas[$k])) {
                        $eventos[$k][] = [$fecha, (int) ($a['id'] ?? 0), 0, 'baja'];
                    }
                }
            }
        }

        $porPersona = [];
        foreach ($eventos as $k => $evs) {
            usort($evs, function ($x, $y) {
                return [$x[0], $x[1], $x[2]] <=> [$y[0], $y[1], $y[2]];
            });
            $ultimo = end($evs);
            if ($ultimo[3] !== 'alta') {
                continue;
            }
            // Fecha del nombramiento vigente: el alta que sigue a la última baja.
            $since = null;
            foreach ($evs as $ev) {
                if ($ev[3] === 'baja') {
                    $since = null;
                } elseif ($since === null && $ev[0] !== '') {
                    $since = substr($ev[0], 0, 10);
                }
            }
            $name = $etiquetas[$k]['name'];
            $pk = self::personKey($name);
            if (!isset($porPersona[$pk])) {
                $porPersona[$pk] = ['name' => $name, 'positions' => [], 'since' => null];
            }
            $pos = $etiquetas[$k]['position'];
            if ($pos !== '' && !in_array($pos, $porPersona[$pk]['positions'], true)) {
                $porPersona[$pk]['positions'][] = $pos;
            }
            if ($since !== null && ($porPersona[$pk]['since'] === null || $since < $porPersona[$pk]['since'])) {
                $porPersona[$pk]['since'] = $since;
            }
        }

        $result = [];
        foreach ($porPersona as $p) {
            $result[] = [
                'name'     => $p['name'],
                'position' => implode(', ', $p['positions']),
                'since'    => $p['since'],
            ];
        }
        return $result;
    }

    /**
     * Lee de un acto del BORME las listas "Cargo: NOMBRE; NOMBRE." y la sección a la
     * que pertenecen ("Ceses/Dimisiones.", "Nombramientos.", "Revocaciones.",
     * "Reelecciones."). Ejemplo real:
     *   "Ceses/Dimisiones. Adm. Unico: LLORET GISBERT EDUARDO. Nombramientos. Adm. Unico: LEYVA CRIADO DIANA."
     * Lo que no se entiende se descarta: una baja solo cuenta si coincide con un
     * nombramiento conocido de la misma persona y cargo.
     *
     * @return array<int,array{section:string,position:string,names:string[]}>
     */
    public static function parseOfficerActs(string $desc): array
    {
        $desc = trim(preg_replace('/\s+/u', ' ', $desc));
        if ($desc === '' || strpos($desc, ':') === false) {
            return [];
        }
        $parts = explode(':', $desc);
        $out = [];
        $section = 'otro';
        $label = $parts[0];
        $n = count($parts);
        for ($i = 1; $i < $n; $i++) {
            [$namesPart, $nextLabel] = self::splitNamesAndLabel($parts[$i]);
            [$section, $position] = self::consumeHeadings($label, $section);
            if ($section !== 'otro' && $position !== '') {
                $names = [];
                foreach (explode(';', $namesPart) as $nm) {
                    $nm = trim(rtrim(trim($nm), '.'));
                    if (mb_strlen($nm, 'UTF-8') >= 3) {
                        $names[] = $nm;
                    }
                }
                if ($names) {
                    $out[] = ['section' => $section, 'position' => $position, 'names' => $names];
                }
            }
            $label = $nextLabel;
        }
        return $out;
    }

    /** "NOMBRE; NOMBRE. Siguiente cargo" → ["NOMBRE; NOMBRE", "Siguiente cargo"]. */
    private static function splitNamesAndLabel(string $chunk): array
    {
        if (preg_match('/\.\s+(?=[A-ZÁÉÍÓÚÑ][a-záéíóúñ\/])/u', $chunk, $m, PREG_OFFSET_CAPTURE)) {
            $p = $m[0][1];
            return [substr($chunk, 0, $p), trim(substr($chunk, $p + strlen($m[0][0])))];
        }
        return [$chunk, ''];
    }

    /** Quita los encabezados de sección del principio de una etiqueta y devuelve [sección, cargo]. */
    private static function consumeHeadings(string $label, string $section): array
    {
        $label = trim($label);
        while (preg_match('/^([A-ZÁÉÍÓÚÑ][^.:]{5,}?)\.\s+(\S.*)$/us', $label, $m)) {
            $section = self::classifyHeading($m[1]);
            $label = trim($m[2]);
        }
        // Una etiqueta que es solo un encabezado conocido ("Nombramientos.") no es un cargo.
        $solo = rtrim($label, '. ');
        $cls = self::classifyHeading($solo);
        if ($cls !== 'otro' && mb_strlen($solo, 'UTF-8') > 8) {
            return [$cls, ''];
        }
        return [$section, trim($label, ' .')];
    }

    private static function classifyHeading(string $h): string
    {
        $h = mb_strtolower(strtr($h, ['Ó' => 'o', 'ó' => 'o', 'É' => 'e', 'é' => 'e', 'Í' => 'i', 'í' => 'i']), 'UTF-8');
        if (strpos($h, 'cese') !== false || strpos($h, 'dimis') !== false) return 'cese';
        if (strpos($h, 'revoca') !== false) return 'revocacion';
        if (strpos($h, 'nombram') !== false) return 'nombramiento';
        if (strpos($h, 'reelec') !== false) return 'reeleccion';
        return 'otro';
    }

    public static function personKey(string $name): string
    {
        $s = mb_strtoupper(trim($name), 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N', 'Ç' => 'C']);
        return (string) preg_replace('/[^A-Z0-9]/', '', $s);
    }

    /** "Adm. Unico" y "Administrador único" dan la misma clave. */
    public static function positionKey(string $pos): string
    {
        $s = mb_strtolower(trim($pos), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        $tokens = preg_split('/[^a-z]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
        $map = [
            'adm' => 'administrador', 'admin' => 'administrador', 'administ' => 'administrador',
            'solid' => 'solidario', 'sol' => 'solidario', 'solidar' => 'solidario',
            'mancom' => 'mancomunado', 'manc' => 'mancomunado',
            'con' => 'consejero', 'cons' => 'consejero', 'consej' => 'consejero',
            'del' => 'delegado', 'deleg' => 'delegado', 'delegad' => 'delegado',
            'pres' => 'presidente', 'presid' => 'presidente',
            'vicepres' => 'vicepresidente', 'vicepresid' => 'vicepresidente', 'vpte' => 'vicepresidente',
            'sec' => 'secretario', 'secr' => 'secretario', 'secret' => 'secretario',
            'vsecr' => 'vicesecretario', 'vicesec' => 'vicesecretario',
            'apo' => 'apoderado', 'apod' => 'apoderado', 'apoder' => 'apoderado',
            'liquid' => 'liquidador', 'liq' => 'liquidador',
            'aud' => 'auditor', 'supl' => 'suplente', 'sup' => 'suplente',
            'unic' => 'unico', 'uni' => 'unico',
            'miem' => 'miembro', 'com' => 'comision', 'ej' => 'ejecutiva', 'ejec' => 'ejecutiva',
            'mco' => 'mancomunado',
        ];
        $out = [];
        foreach ($tokens as $t) {
            $out[] = $map[$t] ?? $t;
        }
        return implode('', $out);
    }

    // ------------------------------------------------------------------
    // Ganchos de venta en los 403 (26-09-2026)
    // ------------------------------------------------------------------

    /** Máximo de ganchos con datos por usuario y día. Pasado el tope, el 403 sale como antes. */
    public const TEASERS_POR_DIA = 25;

    /**
     * Un 403 no se cobra: sin tope, el gancho serviría para sacar gratis el nivel de
     * riesgo o los contratos de miles de empresas. Cuenta y dice si aún quedan.
     */
    public static function teaserAllowed(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }
        $key = 'api_teaser_' . self::CACHE_V . '_' . $userId . '_' . date('Ymd');
        $n = (int) (cache()->get($key) ?? 0);
        if ($n >= self::TEASERS_POR_DIA) {
            return false;
        }
        cache()->save($key, $n + 1, 90000);
        return true;
    }

    /** Número e importe total de contratos públicos adjudicados a un CIF. */
    public static function contractsSummary(string $cif): ?array
    {
        $cif = self::normalizeCif($cif);
        if ($cif === '') {
            return null;
        }
        try {
            $db = \Config\Database::connect();
            $row = $db->table('company_contracts')
                ->select('COUNT(*) AS n, COALESCE(SUM(importe_adjudicacion), 0) AS total, MAX(fecha_adjudicacion) AS ultima', false)
                ->where('company_cif', $cif)
                ->get()->getRowArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::contractsSummary] ' . $e->getMessage());
            return null;
        }
        return [
            'total_contracts' => (int) ($row['n'] ?? 0),
            'total_amount'    => round((float) ($row['total'] ?? 0), 2),
            'last_award_date' => !empty($row['ultima']) ? substr((string) $row['ultima'], 0, 10) : null,
        ];
    }

    /** Nivel de riesgo guardado (sin puntuación ni detalle). */
    public static function riskLevel(string $cif): ?string
    {
        $cif = self::normalizeCif($cif);
        if ($cif === '') {
            return null;
        }
        try {
            $db = \Config\Database::connect();
            $row = $db->table('company_risk_profiles')->select('risk_level')->where('cif', $cif)->get()->getRowArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::riskLevel] ' . $e->getMessage());
            return null;
        }
        return !empty($row['risk_level']) ? (string) $row['risk_level'] : null;
    }
}
