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
 *  - (16-10-2026) identifiers (LEI de GLEIF) y legal_form (con ISO 20275) en todos los planes;
 *    group (matrices en GLEIF) y ownership (socio único del BORME) en planes de pago o con saldo.
 */
class ApiCompanyEnricher
{
    /** Versión de las cachés de este servicio: subirla si cambia el cálculo. */
    private const CACHE_V = 'v1';
    // Solo para la caché de administradores vigentes: positionKey se amplió el 08-10-2026 y la
    // lista cacheada con la regla anterior no vale. Las demás cachés (CACHE_V) no cambian.
    // v2 (16-10-2026): cada administrador lleva url_pdf (PDF del BORME de su nombramiento vigente).
    private const CACHE_ADMINS = 'v2';

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

        // 16-10-2026: LEI y grupo (GLEIF) y socio único (BORME). Una consulta por bloque.
        $idsUsar = $idsActuales ?: $ids;
        $leis = self::loadLei($idsUsar);
        $parentCifs = $fullAccess ? self::loadParentCifs($leis) : [];
        $founded = [];
        foreach ($extras as $ex) {
            $founded[(int) $ex['id']] = (string) ($ex['fecha_constitucion'] ?? '');
        }
        $owners = self::loadOwnership($idsUsar, $founded);

        foreach ($companies as &$c) {
            $cif = self::normalizeCif((string) ($c['cif'] ?? ''));
            $ex  = $extras[$cif] ?? [];
            if (!empty($ex['id'])) {
                $c['id'] = (int) $ex['id']; // id actual (filter_company_data lo quita después)
            }
            $id  = (int) ($c['id'] ?? 0);

            // Sin la fila en BD no se puede saber: se mantiene el comportamiento de antes.
            $hayRegistro = empty($ex)
                || trim((string) ($ex['registro_mercantil'] ?? '')) !== ''
                || !empty($ex['fecha_constitucion'])
                || !empty($profiles[$cif]['events']);
            $st = self::statusFor((string) ($c['status'] ?? ''), $ex['estado_fecha'] ?? null, $profiles[$cif] ?? null, $hayRegistro);
            $c['status_code']   = $st['status_code'];
            $c['status_source'] = $st['status_source'];
            $c['status_date']   = $st['status_date'];

            $band = self::sizeBand($ex['ventas_raw'] ?? null);
            $year = isset($ex['ult_cuentas_anio']) && (int) $ex['ult_cuentas_anio'] > 1900 ? (int) $ex['ult_cuentas_anio'] : null;

            // Identificadores y forma jurídica (16-10-2026): todos los planes.
            $lei = $leis[$id] ?? null;
            $c['identifiers'] = self::identifiersFor($lei);
            $c['legal_form']  = self::legalForm((string) ($c['name'] ?? ''), $cif);

            if ($fullAccess) {
                $c['financials'] = [
                    'size_band'          => $band['code'],
                    'size_band_label'    => $band['label'],
                    'last_accounts_year' => $year,
                ];
                // Grupo (GLEIF) y socio único (BORME), 16-10-2026: Pro, Business o saldo.
                $c['group']     = self::groupFor($lei, $parentCifs);
                $c['ownership'] = self::ownershipFor($owners[$id] ?? null, (string) ($c['name'] ?? ''));
            } elseif (isset($c['upsell_opportunities']) && is_array($c['upsell_opportunities'])) {
                // Free: enseñar QUÉ hay sin darlo (campo nuevo dentro de upsell_opportunities).
                $own = self::ownershipFor($owners[$id] ?? null, (string) ($c['name'] ?? ''));
                $c['upsell_opportunities']['datos_pro'] = [
                    'tramo_facturacion'        => $band['code'] !== null,
                    'ultimo_ejercicio_cuentas' => $year !== null,
                    'administradores'          => (int) ($adminCounts[$id] ?? 0),
                    // 16-10-2026 (campos nuevos)
                    'socio_unico'              => $own['sole_shareholder'] !== null,
                    'grupo_empresarial'        => $lei !== null && (!empty($lei['direct_parent_lei']) || !empty($lei['ultimate_parent_lei'])),
                ];
            }
        }
        unset($c);

        return $companies;
    }

    // ------------------------------------------------------------------
    // Identificadores, forma jurídica, grupo y socio único (16-10-2026)
    // ------------------------------------------------------------------
    //
    // Lo que piden los procesos KYB además de "existe y está activa":
    //  - identifiers.lei: código LEI de GLEIF (company_lei, Golden Copy CC0 cargada con
    //    calidad_datos/cargar_gleif.py). Todos los planes.
    //  - legal_form: forma jurídica con su código ISO 20275 (ELF), que es como la entienden los
    //    sistemas de otros países. Todos los planes.
    //  - group: matriz directa y última que la empresa declara a GLEIF. Pro, Business o saldo.
    //  - ownership: si es sociedad unipersonal y quién es el socio único, según el último acto del
    //    BORME sobre unipersonalidad. Es el único dato de titularidad público en España: el
    //    accionariado de una sociedad con varios socios no se publica, y el titular real no es
    //    accesible al público. Pro, Business o saldo.
    // Sin EUID a propósito: el español usa un número interno del Registro
    // (ES28.RM-M28038019301), no el CIF, y no lo tenemos.

    /** Versión de la caché de socio único: subirla si cambia parseOwnership(). */
    private const CACHE_OWN = 'v2'; // v2 (16-10-2026): nombres sin saltos de línea

    /**
     * Formas jurídicas por el final de la razón social, de la más concreta a la más general.
     * [código propio, patrón sobre el nombre normalizado, etiqueta, ISO 20275, unipersonal]
     * Códigos ELF de la lista de GLEIF (Topograph usa los mismos; 5RDO, DP3Q y 1QU8 comprobados
     * en GLEIF el 16-10-2026). null: la forma no tiene código ELF propio.
     */
    private const FORMAS = [
        ['SLNE',  '/\b(SLNE|SOCIEDAD LIMITADA NUEVA EMPRESA)$/', 'Sociedad limitada nueva empresa', null, null],
        ['SLLP',  '/\b(SLLP|SOCIEDAD LIMITADA LABORAL PROFESIONAL)$/', 'Sociedad limitada laboral profesional', null, null],
        ['SLP',   '/\b(SLP|SRLP|SOCIEDAD (DE RESPONSABILIDAD )?LIMITADA PROFESIONAL)$/', 'Sociedad limitada profesional', 'JB2M', null],
        ['SLL',   '/\b(SLL|SRLL|SOCIEDAD (DE RESPONSABILIDAD )?LIMITADA LABORAL)$/', 'Sociedad limitada laboral', '1G29', null],
        ['SAD',   '/\b(SAD|SOCIEDAD ANONIMA DEPORTIVA)$/', 'Sociedad anónima deportiva', null, null],
        ['SAL',   '/\b(SAL|SOCIEDAD ANONIMA LABORAL)$/', 'Sociedad anónima laboral', 'R2L8', null],
        ['SE',    '/\b(SE|SOCIEDAD ANONIMA EUROPEA)$/', 'Sociedad anónima europea', '9FPZ', null],
        ['SICAV', '/\bSICAV$/', 'Sociedad de inversión de capital variable', '8EHB', null],
        ['SGR',   '/\b(SGR|SOCIEDAD DE GARANTIA RECIPROCA)$/', 'Sociedad de garantía recíproca', 'IAS6', null],
        ['SL',    '/\b(SLU|SRLU|SOCIEDAD (DE RESPONSABILIDAD )?LIMITADA UNIPERSONAL)$/', 'Sociedad limitada', 'DP3Q', true],
        ['SA',    '/\b(SAU|SOCIEDAD ANONIMA UNIPERSONAL)$/', 'Sociedad anónima', '5RDO', true],
        ['SL',    '/\b(SL|SRL|SOCIEDAD LIMITADA|SOCIEDAD DE RESPONSABILIDAD LIMITADA)$/', 'Sociedad limitada', 'DP3Q', null],
        ['SA',    '/\b(SA|SOCIEDAD ANONIMA)$/', 'Sociedad anónima', '5RDO', null],
        ['SCOM',  '/\b(SOCIEDAD COMANDITARIA POR ACCIONES|SCOMPA|S COM P A)$/', 'Sociedad comanditaria por acciones', 'GJL1', null],
        ['SCOM',  '/\b(SOCIEDAD COMANDITARIA( SIMPLE)?|SCOM|S EN C|S COM)$/', 'Sociedad comanditaria', 'TDD5', null],
        ['SRC',   '/\b(SOCIEDAD REGULAR COLECTIVA|SOCIEDAD COLECTIVA|SRC)$/', 'Sociedad colectiva', 'S6X7', null],
        ['AEIE',  '/\b(AEIE|AGRUPACION EUROPEA DE INTERES ECONOMICO)$/', 'Agrupación europea de interés económico', 'BI3B', null],
        ['AIE',   '/\b(AIE|AGRUPACION DE INTERES ECONOMICO)$/', 'Agrupación de interés económico', 'I2WU', null],
        ['UTE',   '/\b(UTE|UNION TEMPORAL DE EMPRESAS)\b/', 'Unión temporal de empresas', null, null],
        ['SCOOP', '/\b(COOPERATIVA DE CREDITO|CAJA RURAL)\b/', 'Cooperativa de crédito', 'SS0L', null],
        ['SCOOP', '/\b(S ?COOP|SDAD COOP|SOCIEDAD COOPERATIVA|COOPERATIVA|SCCL|COOP)\b/', 'Sociedad cooperativa', '1QU8', null],
        ['FI',    '/\bFONDO DE INVERSION\b/', 'Fondo de inversión', 'S0Z5', null],
        ['FP',    '/\bFONDO DE PENSIONES\b/', 'Fondo de pensiones', 'FH4R', null],
        ['FUNDACION', '/^FUNDACION\b|\bFUNDACION$/', 'Fundación', 'MDOL', null],
        ['SC',    '/\b(SOCIEDAD CIVIL( PARTICULAR| PROFESIONAL)?|SCP|SC)$/', 'Sociedad civil', '2W1A', null],
        ['SUCURSAL', '/\bSUCURSAL EN ESPANA$/', 'Sucursal en España de sociedad extranjera', null, null],
    ];

    /**
     * Si el nombre no dice la forma, la letra del CIF da la familia (Orden EHA/451/2008).
     * [código, etiqueta, ISO 20275]. Las letras de varias formas (D, G, N...) no llevan ISO.
     */
    private const LETRAS_CIF = [
        'A' => ['SA', 'Sociedad anónima', '5RDO'],
        'B' => ['SL', 'Sociedad limitada', 'DP3Q'],
        'C' => ['SRC', 'Sociedad colectiva', 'S6X7'],
        'D' => ['SCOM', 'Sociedad comanditaria', null],
        'E' => ['CB', 'Comunidad de bienes', null],
        'F' => ['SCOOP', 'Sociedad cooperativa', '1QU8'],
        'G' => ['ASOC_FUND', 'Asociación o fundación', null],
        'H' => ['CP', 'Comunidad de propietarios', null],
        'J' => ['SC', 'Sociedad civil', '2W1A'],
        'N' => ['EXT', 'Entidad extranjera', null],
        'P' => ['PUB', 'Corporación local', null],
        'Q' => ['PUB', 'Organismo público', null],
        'R' => ['REL', 'Congregación o institución religiosa', null],
        'S' => ['PUB', 'Órgano de la Administración', null],
        'U' => ['UTE', 'Unión temporal de empresas', null],
        'V' => ['OTRA', 'Otro tipo de entidad', null],
        'W' => ['EP', 'Establecimiento permanente de entidad no residente', null],
    ];

    /** Nombre en mayúsculas, sin tildes, puntos ni coletillas de estado ("EN LIQUIDACION"). */
    public static function normalizeName(string $name): string
    {
        $s = mb_strtoupper(trim($name), 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N', 'Ç' => 'C', 'À' => 'A', 'È' => 'E', 'Ò' => 'O']);
        $s = preg_replace('/\([^)]*\)/', ' ', $s);            // "(EXTINGUIDA)", "(R.M. MADRID)"
        // "S.L.", "S. L. U." -> "SL", "SLU": se juntan las siglas con punto (solo las que llevan
        // punto, para no pegar una inicial suelta del nombre: "TALLERES X S.L.L.")
        $s = (string) preg_replace_callback('/(?<![A-Z])(?:[A-Z]\s?\.\s?){2,}/', static fn($m) => str_replace(['.', ' '], '', $m[0]) . ' ', (string) $s);
        $s = str_replace(['.', ',', ';', '"', "'"], ' ', $s);
        $s = trim((string) preg_replace('/\s+/', ' ', $s));
        $s = (string) preg_replace('/\s+EN (LIQUIDACION|CONSTITUCION)$/', '', $s);
        return trim($s);
    }

    /**
     * Forma jurídica: por el final de la razón social y, si no, por la letra del CIF.
     * @return array{code:?string,label:?string,iso20275:?string,source:?string}
     */
    public static function legalForm(string $name, string $cif): array
    {
        $n = self::normalizeName($name);
        if ($n !== '') {
            foreach (self::FORMAS as [$code, $re, $label, $iso]) {
                if (preg_match($re, $n)) {
                    return ['code' => $code, 'label' => $label, 'iso20275' => $iso, 'source' => 'name'];
                }
            }
        }
        $letra = substr(self::normalizeCif($cif), 0, 1);
        if (isset(self::LETRAS_CIF[$letra])) {
            [$code, $label, $iso] = self::LETRAS_CIF[$letra];
            return ['code' => $code, 'label' => $label, 'iso20275' => $iso, 'source' => 'cif'];
        }
        return ['code' => null, 'label' => null, 'iso20275' => null, 'source' => null];
    }

    /** ¿El nombre dice que es unipersonal (SLU, SAU)? null si no lo dice. */
    public static function unipersonalByName(string $name): ?bool
    {
        $n = self::normalizeName($name);
        foreach (self::FORMAS as [, $re, , , $uni]) {
            if (preg_match($re, $n)) {
                return $uni;
            }
        }
        return null;
    }

    /** Filas de company_lei por company_id. [] si la tabla no existe. Caché de 24 h por empresa. */
    private static function loadLei(array $ids): array
    {
        $out = [];
        $faltan = [];
        foreach (array_unique(array_map('intval', $ids)) as $id) {
            if ($id <= 0) {
                continue;
            }
            $hit = cache()->get('api_lei_' . self::CACHE_V . '_' . $id);
            if (is_array($hit)) {
                if ($hit) {
                    $out[$id] = $hit;
                }
            } else {
                $faltan[] = $id;
            }
        }
        if (empty($faltan)) {
            return $out;
        }
        try {
            $rows = \Config\Database::connect()->table('company_lei')
                ->select('company_id, lei, registration_status, next_renewal_date, source_date, '
                       . 'direct_parent_lei, direct_parent_name, direct_parent_country, direct_parent_company_id, '
                       . 'ultimate_parent_lei, ultimate_parent_name, ultimate_parent_country, ultimate_parent_company_id')
                ->whereIn('company_id', $faltan)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::loadLei] ' . $e->getMessage());
            return $out;
        }
        $vistos = [];
        foreach ($rows as $r) {
            $id = (int) $r['company_id'];
            $out[$id] = $r;
            $vistos[$id] = true;
            cache()->save('api_lei_' . self::CACHE_V . '_' . $id, $r, 86400);
        }
        foreach ($faltan as $id) {
            if (!isset($vistos[$id])) {
                cache()->save('api_lei_' . self::CACHE_V . '_' . $id, [], 86400);
            }
        }
        return $out;
    }

    /** CIF de las matrices que tienen ficha nuestra: [company_id => cif]. */
    private static function loadParentCifs(array $leis): array
    {
        $ids = [];
        foreach ($leis as $l) {
            foreach (['direct', 'ultimate'] as $t) {
                if (!empty($l[$t . '_parent_company_id'])) {
                    $ids[] = (int) $l[$t . '_parent_company_id'];
                }
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return [];
        }
        try {
            $rows = \Config\Database::connect()->table('companies')->select('id, cif')->whereIn('id', $ids)->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::loadParentCifs] ' . $e->getMessage());
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            if (!empty($r['cif'])) {
                $out[(int) $r['id']] = strtoupper((string) $r['cif']);
            }
        }
        return $out;
    }

    private static function identifiersFor(?array $lei): array
    {
        $fecha = static fn($d) => !empty($d) ? substr((string) $d, 0, 10) : null;
        return [
            'lei'                   => $lei['lei'] ?? null,
            'lei_status'            => isset($lei['registration_status']) ? (string) $lei['registration_status'] : null,
            'lei_next_renewal_date' => $fecha($lei['next_renewal_date'] ?? null),
            'lei_source'            => $lei ? 'gleif' : null,
            'lei_as_of'             => $fecha($lei['source_date'] ?? null),
        ];
    }

    /** Matriz directa y última declaradas a GLEIF. null si la empresa no tiene LEI. */
    private static function groupFor(?array $lei, array $parentCifs): ?array
    {
        if (!$lei) {
            return null;
        }
        $parent = static function (string $t) use ($lei, $parentCifs): ?array {
            if (empty($lei[$t . '_parent_lei'])) {
                return null;
            }
            $cid = (int) ($lei[$t . '_parent_company_id'] ?? 0);
            return [
                'lei'     => (string) $lei[$t . '_parent_lei'],
                'name'    => $lei[$t . '_parent_name'] ?? null,
                'country' => $lei[$t . '_parent_country'] ?? null,
                'cif'     => $parentCifs[$cid] ?? null,
            ];
        };
        return [
            'direct_parent'   => $parent('direct'),
            'ultimate_parent' => $parent('ultimate'),
            'source'          => 'gleif',
            'as_of'           => !empty($lei['source_date']) ? substr((string) $lei['source_date'], 0, 10) : null,
        ];
    }

    /**
     * Último hecho de unipersonalidad del BORME por empresa: [company_id => parseOwnership()].
     * Caché de 12 h por empresa.
     * @param array<int,string> $founded [company_id => fecha de constitución] para descartar
     *        anuncios de otra sociedad con el mismo nombre (ficha_posts_coherentes)
     */
    private static function loadOwnership(array $ids, array $founded): array
    {
        $out = [];
        $faltan = [];
        foreach (array_unique(array_map('intval', $ids)) as $id) {
            if ($id <= 0) {
                continue;
            }
            $hit = cache()->get('api_own_' . self::CACHE_OWN . '_' . $id);
            if (is_array($hit)) {
                $out[$id] = $hit ?: null;
            } else {
                $faltan[] = $id;
            }
        }
        if (empty($faltan)) {
            return $out;
        }
        try {
            $rows = \Config\Database::connect()->table('borme_posts')
                ->select('id, company_id, borme_date, description, url_pdf')
                ->whereIn('company_id', $faltan)
                ->groupStart()
                    ->like('description', 'unipersonal', 'both', null, true)
                    ->orLike('description', 'socio', 'both', null, true)
                ->groupEnd()
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[ApiCompanyEnricher::loadOwnership] ' . $e->getMessage());
            return $out;
        }
        helper('ficha_hechos');
        $porEmpresa = [];
        foreach ($rows as $r) {
            $porEmpresa[(int) $r['company_id']][] = $r;
        }
        foreach ($faltan as $id) {
            $posts = $porEmpresa[$id] ?? [];
            if ($posts && function_exists('ficha_posts_coherentes')) {
                $posts = ficha_posts_coherentes($posts, (string) ($founded[$id] ?? ''));
            }
            $r = self::parseOwnership($posts);
            if ($r && !empty($r['name']) && function_exists('ficha_nombre_completo')) {
                // "INNGEN" -> "INNGEN.IO SEED SL" (la extracción corta en el primer punto)
                $r['name'] = ficha_nombre_completo($r['name'], $posts);
            }
            $out[$id] = $r;
            cache()->save('api_own_' . self::CACHE_OWN . '_' . $id, $r ?? [], 43200);
        }
        return $out;
    }

    /**
     * Último hecho sobre unipersonalidad de una lista de anuncios. Pura (sin BD): se prueba aparte.
     * Devuelve null si ningún anuncio habla de ello, o
     * ['unipersonal' => bool, 'name' => ?string, 'date' => ?string, 'url_pdf' => ?string].
     *
     * Textos del BORME que se leen:
     *   "Declaración de unipersonalidad. Socio único: X."      -> unipersonal, socio X
     *   "Cambio de identidad del socio único: X."              -> unipersonal, socio X
     *   "Socio único: X." (p. ej. en la constitución)          -> unipersonal, socio X
     *   "Pérdida del caracter de unipersonalidad."             -> deja de serlo
     * Si un mismo anuncio trae varios, manda el último en el texto.
     */
    public static function parseOwnership(array $posts): ?array
    {
        usort($posts, static function ($a, $b) {
            return [(string) ($b['borme_date'] ?? ''), (int) ($b['id'] ?? 0)] <=> [(string) ($a['borme_date'] ?? ''), (int) ($a['id'] ?? 0)];
        });
        $nombre = '((?:[^.]|\.(?=[^\s]))+)';
        foreach ($posts as $p) {
            $t = (string) ($p['description'] ?? '');
            $hechos = [];
            if (preg_match_all('/P[eé]rdida del car[aá]cter de unipersonalidad/iu', $t, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $x) {
                    $hechos[] = [$x[1], false, null];
                }
            }
            if (preg_match_all('/(?:Cambio de identidad del socio [úu]nico|Socio [úu]nico)\s*:\s*' . $nombre . '/iu', $t, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[1] as $x) {
                    // El texto del BORME trae saltos de línea dentro de los nombres ("MACIAS SANCHEZ\nMANUEL")
                    $hechos[] = [$x[1], true, trim((string) preg_replace('/\s+/u', ' ', $x[0]), " .;,")];
                }
            }
            if (preg_match_all('/Declaraci[oó]n de unipersonalidad/iu', $t, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $x) {
                    $hechos[] = [$x[1], true, null];
                }
            }
            if (!$hechos) {
                continue;
            }
            // El último en el texto; si una declaración y su "Socio único: X" van juntas, gana la que tiene nombre.
            usort($hechos, static fn($a, $b) => $a[0] <=> $b[0]);
            $h = end($hechos);
            if ($h[1] === true && $h[2] === null) {
                foreach (array_reverse($hechos) as $o) {
                    if ($o[1] === true && $o[2] !== null) {
                        $h = $o;
                        break;
                    }
                    if ($o[1] === false) {
                        break;
                    }
                }
            }
            $url = (string) ($p['url_pdf'] ?? '');
            return [
                'unipersonal' => $h[1],
                'name'        => ($h[2] !== null && $h[2] !== '') ? $h[2] : null,
                'date'        => !empty($p['borme_date']) ? substr((string) $p['borme_date'], 0, 10) : null,
                'url_pdf'     => strpos($url, 'https://www.boe.es/') === 0 ? $url : null,
            ];
        }
        return null;
    }

    /** ¿Persona o sociedad? Por la forma jurídica o palabras de empresa en el nombre. */
    public static function partyType(string $name): string
    {
        $n = self::normalizeName($name);
        $re = '/\b(SL|SA|SLU|SAU|SLP|SLL|SRL|SOCIEDAD|SDAD|COOP|SCOOP|COOPERATIVA|FUNDACION|ASOCIACION|AIE|UTE|'
            . 'GMBH|BV|NV|AG|SE|SAS|SARL|SPA|SRL|LTD|LIMITED|LLC|INC|PLC|CORP|CORPORATION|HOLDING|HOLDINGS|GROUP|'
            . 'GRUPO|INVERSIONES|INVESTMENTS|CAPITAL|PARTNERS|FONDO|FUND|BANCO|BANK|CAJA|AYUNTAMIENTO|DIPUTACION|'
            . 'CONSORCIO|ENTIDAD|COMPANY|CIA|COMPANIA|GESTION|SERVICIOS|TRUST)\b/';
        return preg_match($re, $n) ? 'company' : 'person';
    }

    /**
     * Bloque ownership. Fuente principal: el último acto del BORME sobre unipersonalidad. Sin
     * actos, el nombre (SLU, SAU) dice si es unipersonal pero no quién es el socio. Sin nada,
     * unipersonal = null: el accionariado de una sociedad con varios socios no se publica.
     */
    private static function ownershipFor(?array $own, string $name): array
    {
        if ($own) {
            $socio = null;
            if ($own['unipersonal'] && $own['name'] !== null) {
                $socio = [
                    'name'    => $own['name'],
                    'type'    => self::partyType($own['name']),
                    'since'   => $own['date'],
                    'url_pdf' => $own['url_pdf'],
                ];
            }
            return [
                'unipersonal'      => (bool) $own['unipersonal'],
                'sole_shareholder' => $socio,
                'source'           => 'borme',
                'as_of'            => $own['date'],
            ];
        }
        $uni = self::unipersonalByName($name);
        return [
            'unipersonal'      => $uni,
            'sole_shareholder' => null,
            'source'           => $uni !== null ? 'name' : null,
            'as_of'            => null,
        ];
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
                ->select('id, cif, ventas_raw, ult_cuentas_anio, estado_fecha, registro_mercantil, fecha_constitucion')
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

    /**
     * Cuántos administradores VIGENTES tiene cada empresa, para el gancho del Free
     * (datos_pro.administradores). Antes contaba todos los nombres que habían pasado
     * por la empresa (Inditex: 72); después, todos los cargos vigentes, apoderados
     * incluidos (Telefónica: 70, de ellos ~97 nombramientos de apoderado). Ahora solo
     * las personas con algún cargo de administración (isAdminPosition).
     */
    private static function countAdministrators(array $ids): array
    {
        $out = [];
        foreach (self::currentAdministrators($ids) as $id => $vigentes) {
            $n = 0;
            foreach ((array) $vigentes as $p) {
                foreach (explode(',', (string) ($p['position'] ?? '')) as $cargo) {
                    if (self::isAdminPosition($cargo)) {
                        $n++;
                        break;
                    }
                }
            }
            $out[(int) $id] = $n;
        }
        return $out;
    }

    /**
     * ¿Es un cargo del órgano de administración? Sí: administrador (único, solidario,
     * mancomunado), consejero (y sus variantes: independiente, dominical, delegado,
     * ejecutivo...), presidente y vicepresidente, miembro de comisión y liquidador. No:
     * apoderados, auditores, secretarios y vicesecretarios no consejeros, y lo que no se
     * reconozca. Las abreviaturas son las del BORME ("Apo.Man.Soli", "Consj.Domini",
     * "Vpre.Pres.Co", "Mmbr.Com.Del", "PresEjecutiv"...).
     */
    public static function isAdminPosition(string $cargo): bool
    {
        $s = mb_strtolower(trim($cargo), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        $s = preg_replace('/[^a-z]+/', '.', $s);
        $s = trim((string) $s, '.');
        if ($s === '') {
            return false;
        }
        if (preg_match('/^(apo|aud|sec|vsec|vicesec|v\.sec|letr|ent|rep|soc)/', $s) && !preg_match('/cons/', $s)) {
            return false;
        }
        // "Secr.Consejero": secretario que además es consejero
        return (bool) preg_match('/^(adm|con|cons|consj|pres|vpre|vpte|vicep|liq|mmbr|miem|mbro)/', $s)
            || (bool) preg_match('/(^|\.)cons/', $s);
    }

    // ------------------------------------------------------------------
    // Estado normalizado
    // ------------------------------------------------------------------

    /**
     * Valores de status_code:
     *   ACTIVE           el Registro la da como activa y el BORME no dice lo contrario
     *   PRESUMED_ACTIVE  sin estado en el Registro, pero el BORME no tiene ningún hecho
     *                    que la cierre (el motor la ve normal). Solo si la ficha tiene
     *                    algún dato del Registro ($hayRegistro): una UTE o una ficha
     *                    vacía no tiene hechos en el BORME porque no está, y sale UNKNOWN.
     *   INSOLVENCY       concurso de acreedores en curso
     *   IN_LIQUIDATION   en liquidación
     *   DISSOLVED        disuelta
     *   REGISTRY_CLOSED  hoja registral cerrada (falta de cuentas, NIF revocado...)
     *   MERGED           absorbida en una fusión
     *   INACTIVE         inactiva
     *   EXTINCT          extinguida
     *   UNKNOWN          sin datos para decidir
     */
    public static function statusFor(string $statusRaw, ?string $estadoFecha, ?array $legal, bool $hayRegistro = true): array
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
            } elseif ($hayRegistro && is_array($legal) && in_array(self::bareLegal((string) ($legal['legal_state'] ?? '')), ['NORMAL', 'RECOVERED_RESOLVED'], true)) {
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
     * Administradores y cargos vigentes por empresa: [company_id => [[name, position, since, url_pdf], ...]].
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
            $hit = cache()->get('api_admins_' . self::CACHE_ADMINS . '_' . $id);
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
                ->select('ca.company_id, ca.name, ca.position, ca.action, bp.borme_date, ca.post_id, bp.url_pdf')
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
            cache()->save('api_admins_' . self::CACHE_ADMINS . '_' . $id, $vigentes, 43200);
        }

        return $out;
    }

    /**
     * Cruza nombramientos y bajas y devuelve los vigentes agrupados por persona.
     * Pura (sin BD): se prueba aparte.
     *
     * @param array $nombramientos filas [name, position, action, borme_date, post_id, url_pdf]
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
            $eventos[$k][] = [(string) ($n['borme_date'] ?? ''), (int) ($n['post_id'] ?? 0), $tipo === 'baja' ? 0 : 1, $tipo, self::bormePdf($n['url_pdf'] ?? null)];
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
                        $eventos[$k][] = [$fecha, (int) ($a['id'] ?? 0), 0, 'baja', null];
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
            // Fecha del nombramiento vigente: el alta que sigue a la última baja, con el
            // PDF del BORME de ese anuncio (16-10-2026).
            $since = null;
            $sinceUrl = null;
            foreach ($evs as $ev) {
                if ($ev[3] === 'baja') {
                    $since = null;
                    $sinceUrl = null;
                } elseif ($since === null && $ev[0] !== '') {
                    $since = substr($ev[0], 0, 10);
                    $sinceUrl = $ev[4] ?? null;
                }
            }
            $name = $etiquetas[$k]['name'];
            $pk = self::personKey($name);
            if (!isset($porPersona[$pk])) {
                $porPersona[$pk] = ['name' => $name, 'positions' => [], 'since' => null, 'url_pdf' => null];
            }
            $pos = $etiquetas[$k]['position'];
            if ($pos !== '' && !in_array($pos, $porPersona[$pk]['positions'], true)) {
                $porPersona[$pk]['positions'][] = $pos;
            }
            if ($since !== null && ($porPersona[$pk]['since'] === null || $since < $porPersona[$pk]['since'])) {
                $porPersona[$pk]['since'] = $since;
                $porPersona[$pk]['url_pdf'] = $sinceUrl;
            }
        }

        $result = [];
        foreach ($porPersona as $p) {
            $result[] = [
                'name'     => $p['name'],
                'position' => implode(', ', $p['positions']),
                'since'    => $p['since'],
                // Campo nuevo (16-10-2026): PDF oficial del BORME del nombramiento de `since`.
                'url_pdf'  => $p['url_pdf'],
            ];
        }
        return $result;
    }

    /** URL del PDF del BORME solo si es del BOE (mismo criterio que el socio único); si no, null. */
    private static function bormePdf($url): ?string
    {
        $url = trim((string) $url);
        return strpos($url, 'https://www.boe.es/') === 0 ? $url : null;
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
            // 08-10-2026: abreviaturas que separaban el mismo cargo en dos ("Apo.Sol." y
            // "APODERAD.SOL"): el cese escrito de una forma no cancelaba el nombramiento
            // escrito de la otra. Igual que _MAPA en scripts/borme_diario/cargos.py.
            'apoderad' => 'apoderado', 'apodera' => 'apoderado', 'mancomu' => 'mancomunado', 'man' => 'mancomunado',
            'soli' => 'solidario', 'liquisoli' => 'liquidadorsolidario', 'liqsolid' => 'liquidadorsolidario',
            'liqmanc' => 'liquidadormancomunado', 'liqunico' => 'liquidadorunico', 'vicepresiden' => 'vicepresidente',
            'vicesecret' => 'vicesecretario', 'concur' => 'concursal', 'concurs' => 'concursal',
            'mro' => 'miembro', 'coms' => 'comision',
        ];
        $out = [];
        foreach ($tokens as $t) {
            $out[] = $map[$t] ?? $t;
        }
        $k = implode('', $out);
        // Mismo cargo con las palabras en otro orden ("APOD.SOL/MAN" y "Apo.Man.Soli").
        $alias = ['apoderadosolidariomancomunado' => 'apoderadomancomunadosolidario'];
        return $alias[$k] ?? $k;
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
