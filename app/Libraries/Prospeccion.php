<?php

namespace App\Libraries;

/**
 * Prospección de clientes para el trabajo de freelance de Adrián (/admin/prospeccion), 16-10-2026.
 *
 * Usa los datos que ya tenemos para encontrar empresas que pueden necesitar un desarrollador:
 *  - recién constituidas (BORME),
 *  - activas sin web,
 *  - con subvención reciente (BDNS; aparte, las de digitalización: Kit Digital y similares),
 *  - que ganan contratos públicos (PLACSP/TED),
 *  - con cambios recientes en el BORME (ampliación de capital, traslado, nuevo objeto, fusión...),
 * más las licitaciones abiertas de informática (tabla propia, ver LicitacionesPlacsp) y el
 * seguimiento de cada empresa (prospect_followups).
 *
 * Reglas:
 *  - Solo lectura de las tablas existentes. Solo escribe en prospect_followups y
 *    prospect_tenders.
 *  - Fuera personas físicas y organismos públicos (por la letra del CIF; sin CIF = sociedad
 *    del BORME) y las bajas de privacidad (company_privacy_optouts).
 *  - Solo contacto de empresa: teléfono fijo, email genérico y web. Los móviles no se enseñan.
 *  - Todas las consultas van por rangos de fecha con índice y con tope de filas.
 */
class Prospeccion
{
    public const LIMITE = 300;           // filas que se pintan por pestaña
    private const CACHE_MIN = 20;        // minutos de caché del resumen

    public const PESTANAS = [
        'resumen'      => 'Resumen',
        'nuevas'       => 'Recién constituidas',
        'sin-web'      => 'Activas sin web',
        'subvenciones' => 'Con subvención',
        'contratos'    => 'Ganan contratos',
        'cambios'      => 'Cambios en el BORME',
        'licitaciones' => 'Licitaciones TI',
        'seguimiento'  => 'Mi seguimiento',
    ];

    public const SEG_ESTADOS = [
        'pending'    => 'Pendiente',
        'contacted'   => 'Contactada',
        'in_talks' => 'En conversación',
        'client'      => 'Cliente',
        'discarded'   => 'Descartada',
    ];

    public const LIC_ESTADOS = [
        'interested'   => 'Me interesa',
        'preparing' => 'Preparando oferta',
        'submitted' => 'Presentada',
        'won'     => 'Ganada',
        'lost'    => 'Perdida',
        'discarded' => 'Descartada',
    ];

    /**
     * Sectores pensados para vender desarrollo: clave => [nombre, prefijos CNAE, palabras del objeto social].
     *
     * Las palabras sirven para las empresas sin CNAE: casi todas las recién constituidas llegan
     * del BORME sin él (95 % de las de un mes en la muestra) hasta que se clasifican, pero su
     * objeto social suele decir la actividad ("Actividad principal: 56.10 / Restaurantes",
     * "Servicios de bebidas"...). Primero se busca el código en el texto y luego las palabras.
     */
    public const SECTORES = [
        'hosteleria'    => ['Hostelería y turismo', ['55', '56', '79'],
            'restaura|hosteler|cafeter|servicios? de bebidas|servicios? de comidas|catering|hotel|servicios de alojamiento|alojamientos? tur|apartamentos tur|agencias? de viaje|operadores? tur'],
        'salud'         => ['Salud, clínicas y veterinarias', ['86', '87', '88', '75'],
            'sanitari|cl[ií]nic|m[eé]dic|dental|odontol|fisioterap|psicol[oó]g|veterinar|enfermer|residencias? (de|para) (mayores|ancianos)|podolog|[oó]ptica'],
        'reformas'      => ['Construcción y reformas', ['41', '42', '43'],
            'construcci[oó]n|edificios|reformas|alba[ñn]iler|fontaner|instalaciones? el[eé]ctric|pintura y|carpinter[ií]a|obras? de|climatizaci'],
        'comercio'      => ['Comercio y tiendas', ['47'],
            'comercio al por menor|venta al por menor|minorista|tiendas?'],
        'distribucion'  => ['Mayoristas, distribución y talleres', ['45', '46'],
            'comercio al por mayor|mayorista|distribuci[oó]n de|veh[ií]culos de motor|talleres?'],
        'profesionales' => ['Asesorías, despachos e ingenierías', ['69', '70', '71', '73', '74'],
            'asesor|consultor|abogad|jur[ií]dic|contab|fiscal|gestor[ií]a|arquitect|ingenier[ií]a|publicidad|marketing|estudios de mercado|dise[ñn]o'],
        'inmobiliaria'  => ['Inmobiliarias', ['68'],
            'inmobiliari|alquiler de (bienes|inmuebles|viviendas|locales)|compraventa de (bienes|inmuebles)'],
        'educacion'     => ['Educación y formación', ['85'],
            'educaci[oó]n|ense[ñn]anza|formaci[oó]n|academia|escuela|guarder'],
        'industria'     => ['Industria y fabricación', ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33'],
            'fabricaci[oó]n|industria|manufactur|elaboraci[oó]n de|reparaci[oó]n y mantenimiento de maquinaria'],
        'transporte'    => ['Transporte y logística', ['49', '50', '51', '52', '53'],
            'transporte|log[ií]stic|mensajer|almacenamiento|mudanzas'],
        'agro'          => ['Agricultura y ganadería', ['01', '02', '03'],
            'agr[ií]col|ganader|cultivo|explotaci[oó]n agr|pesca|silvicultura'],
        'ocio'          => ['Ocio, deporte y cultura', ['90', '91', '92', '93'],
            'deport|gimnasio|ocio|espect[aá]culo|art[ií]stic|cultural|organizaci[oó]n de eventos'],
        'servicios'     => ['Servicios a empresas (limpieza, seguridad, empleo…)', ['77', '78', '80', '81', '82'],
            'limpieza|seguridad privada|vigilancia|selecci[oó]n de personal|trabajo temporal|alquiler de (maquinaria|veh[ií]culos|autom[oó]viles)|servicios administrativos|centros de llamadas'],
        'personales'    => ['Servicios personales (peluquerías, reparaciones…)', ['95', '96'],
            'peluquer|est[eé]tica|belleza|lavander|tatuaj'],
        'energia'       => ['Energía, agua y residuos', ['35', '36', '37', '38', '39'],
            'energ[ií]a|fotovolt|renovable|residuos|reciclaje'],
        'finanzas'      => ['Finanzas y seguros', ['64', '65', '66'],
            'seguros|financier|tenencia de (acciones|participaciones)|holding|gesti[oó]n de patrimonio'],
        'asociaciones'  => ['Asociaciones y entidades', ['94'],
            'asociaci[oó]n'],
        'tecnologia'    => ['Tecnología y medios (competencia o socios)', ['58', '59', '60', '61', '62', '63'],
            'inform[aá]tic|software|programaci[oó]n|desarrollo web|aplicaciones inform|telecomunicaci|audiovisual|hosting'],
    ];

    /** Actos del BORME que suelen traer trabajo: clave => [nombre, patrón en act_types] */
    public const ACTOS = [
        'ampliacion'    => ['Ampliación de capital', 'Ampliaci'],
        'domicilio'     => ['Cambio de domicilio', 'Cambio de domicilio'],
        'objeto'        => ['Cambio de objeto social', 'Cambio de objeto'],
        'denominacion'  => ['Cambio de nombre', 'denominaci'],
        'fusion'        => ['Fusión o escisión', 'Fusi|Escisi'],
        'transformacion' => ['Transformación', 'Transformaci'],
        'nombramientos' => ['Nuevos cargos', 'Nombramientos'],
    ];
    public const ACTOS_POR_DEFECTO = ['ampliacion', 'domicilio', 'objeto', 'denominacion', 'fusion', 'transformacion'];

    public const DIAS = [7 => '7 días', 30 => '30 días', 90 => '90 días', 180 => '6 meses', 365 => '1 año'];
    public const ANTIGUEDAD = [1 => 'Hasta 1 año', 3 => 'Hasta 3 años', 5 => 'Hasta 5 años', 10 => 'Hasta 10 años'];

    private const SQL_DIGITAL = "(sb.convocatoria LIKE '%digital%' OR sb.convocatoria LIKE '%red.es%')";
    private const SQL_ACTIVA = "(c.estado IS NULL OR c.estado = '' OR c.estado NOT REGEXP 'extin|disol|liquid|concurs|fusi|cerrad|baja')";
    // Sociedades, cooperativas, asociaciones, comunidades de bienes...: sin personas físicas (cifras, K/L/M/X/Y/Z) ni organismos (P/Q/S)
    private const SQL_FORMA = "(c.cif IS NULL OR c.cif = '' OR c.cif REGEXP '^[ABCDEFGHJNRUVW][0-9]')";

    private const DDL = [
        "CREATE TABLE IF NOT EXISTS prospect_followups (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            company_id INT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            note TEXT NULL,
            next_contact_date DATE NULL,
            source_tab VARCHAR(20) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_pf_company (company_id),
            KEY idx_pf_status (status, updated_at),
            KEY idx_pf_next (next_contact_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS prospect_tenders (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            feed_id VARCHAR(255) NOT NULL,
            source VARCHAR(20) NOT NULL,
            file_number VARCHAR(255) NULL,
            title TEXT NULL,
            contracting_body VARCHAR(500) NULL,
            contracting_body_nif VARCHAR(20) NULL,
            contracting_body_postal_code VARCHAR(10) NULL,
            contracting_body_city VARCHAR(255) NULL,
            province VARCHAR(60) NULL,
            execution_place VARCHAR(255) NULL,
            contract_type VARCHAR(10) NULL,
            procedure_code VARCHAR(10) NULL,
            amount_excl_vat DECIMAL(15,2) NULL,
            amount_incl_vat DECIMAL(15,2) NULL,
            estimated_value DECIMAL(15,2) NULL,
            lot_count SMALLINT UNSIGNED NULL,
            cpv_codes VARCHAR(500) NULL,
            main_cpv VARCHAR(12) NULL,
            status_code VARCHAR(10) NULL,
            published_date DATE NULL,
            deadline DATETIME NULL,
            url VARCHAR(500) NULL,
            feed_updated DATETIME NULL,
            followup_status VARCHAR(20) NULL,
            followup_note TEXT NULL,
            followup_updated_at DATETIME NULL,
            ai_score TINYINT UNSIGNED NULL,
            ai_is_dev TINYINT(1) NULL,
            ai_summary VARCHAR(500) NULL,
            ai_reasons TEXT NULL,
            ai_profile_hash CHAR(32) NULL,
            ai_evaluated_at DATETIME NULL,
            competition_json MEDIUMTEXT NULL,
            competition_at DATETIME NULL,
            ai_proposal MEDIUMTEXT NULL,
            ai_proposal_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_pt_feed (feed_id),
            KEY idx_pt_open (status_code, deadline),
            KEY idx_pt_province (province, deadline),
            KEY idx_pt_followup (followup_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Crea las dos tablas si no existen (una vez por día como mucho). Si encuentra las tablas
     * de la primera versión (prospeccion_seguimiento / prospeccion_licitaciones, con columnas en
     * español), las renombra con sus datos a las nuevas (16-10-2026: todo en inglés).
     */
    public static function asegurarTablas(): void
    {
        $cache = \Config\Services::cache();
        if ($cache->get('prospect_tables_v5')) {
            return;
        }
        $db = \Config\Database::connect();
        self::migrarTablasAntiguas($db);
        foreach (self::DDL as $sql) {
            $db->query($sql);
        }
        // Columnas de la evaluación con IA (16-10-2026) en tablas creadas antes
        $hay = array_flip($db->getFieldNames('prospect_tenders'));
        $nuevas = [
            'ai_score'        => 'TINYINT UNSIGNED NULL',
            'ai_is_dev'       => 'TINYINT(1) NULL',
            'ai_summary'      => 'VARCHAR(500) NULL',
            'ai_reasons'      => 'TEXT NULL',
            'ai_profile_hash' => 'CHAR(32) NULL',
            'ai_evaluated_at' => 'DATETIME NULL',
            'competition_json' => 'MEDIUMTEXT NULL',
            'competition_at'  => 'DATETIME NULL',
            'ai_proposal'     => 'MEDIUMTEXT NULL',
            'ai_proposal_at'  => 'DATETIME NULL',
        ];
        $add = [];
        foreach ($nuevas as $col => $def) {
            if (!isset($hay[$col])) {
                $add[] = "ADD COLUMN {$col} {$def} AFTER followup_updated_at";
            }
        }
        if ($add) {
            $db->query('ALTER TABLE prospect_tenders ' . implode(', ', array_reverse($add)));
        }
        $cache->save('prospect_tables_v5', 1, 86400);
    }

    private static function migrarTablasAntiguas($db): void
    {
        if ($db->tableExists('prospeccion_seguimiento') && !$db->tableExists('prospect_followups')) {
            $db->query("ALTER TABLE prospeccion_seguimiento
                CHANGE estado status VARCHAR(20) NOT NULL DEFAULT 'pending',
                CHANGE nota note TEXT NULL,
                CHANGE proximo_contacto next_contact_date DATE NULL,
                CHANGE origen source_tab VARCHAR(20) NULL,
                DROP INDEX idx_ps_estado, ADD KEY idx_pf_status (status, updated_at),
                DROP INDEX idx_ps_proximo, ADD KEY idx_pf_next (next_contact_date),
                DROP INDEX uq_ps_company, ADD UNIQUE KEY uq_pf_company (company_id)");
            $db->query("UPDATE prospeccion_seguimiento SET status = CASE status
                WHEN 'pendiente' THEN 'pending' WHEN 'contactada' THEN 'contacted' WHEN 'conversacion' THEN 'in_talks'
                WHEN 'cliente' THEN 'client' WHEN 'descartada' THEN 'discarded' ELSE status END");
            $db->query('RENAME TABLE prospeccion_seguimiento TO prospect_followups');
        }
        if ($db->tableExists('prospeccion_licitaciones') && !$db->tableExists('prospect_tenders')) {
            $db->query("ALTER TABLE prospeccion_licitaciones
                CHANGE fuente source VARCHAR(20) NOT NULL,
                CHANGE expediente file_number VARCHAR(255) NULL,
                CHANGE titulo title TEXT NULL,
                CHANGE organo contracting_body VARCHAR(500) NULL,
                CHANGE organo_nif contracting_body_nif VARCHAR(20) NULL,
                CHANGE organo_cp contracting_body_postal_code VARCHAR(10) NULL,
                CHANGE organo_municipio contracting_body_city VARCHAR(255) NULL,
                CHANGE provincia province VARCHAR(60) NULL,
                CHANGE lugar_ejecucion execution_place VARCHAR(255) NULL,
                CHANGE tipo_contrato contract_type VARCHAR(10) NULL,
                CHANGE procedimiento procedure_code VARCHAR(10) NULL,
                CHANGE importe_sin_iva amount_excl_vat DECIMAL(15,2) NULL,
                CHANGE importe_con_iva amount_incl_vat DECIMAL(15,2) NULL,
                CHANGE valor_estimado estimated_value DECIMAL(15,2) NULL,
                CHANGE num_lotes lot_count SMALLINT UNSIGNED NULL,
                CHANGE cpvs cpv_codes VARCHAR(500) NULL,
                CHANGE cpv_principal main_cpv VARCHAR(12) NULL,
                CHANGE estado_code status_code VARCHAR(10) NULL,
                CHANGE fecha_publicacion published_date DATE NULL,
                CHANGE fecha_limite deadline DATETIME NULL,
                CHANGE seg_estado followup_status VARCHAR(20) NULL,
                CHANGE seg_nota followup_note TEXT NULL,
                CHANGE seg_updated_at followup_updated_at DATETIME NULL,
                DROP INDEX uq_pl_feed, ADD UNIQUE KEY uq_pt_feed (feed_id),
                DROP INDEX idx_pl_abiertas, ADD KEY idx_pt_open (status_code, deadline),
                DROP INDEX idx_pl_provincia, ADD KEY idx_pt_province (province, deadline),
                DROP INDEX idx_pl_seg, ADD KEY idx_pt_followup (followup_status)");
            $db->query("UPDATE prospeccion_licitaciones SET
                followup_status = CASE followup_status WHEN 'interesa' THEN 'interested' WHEN 'preparando' THEN 'preparing'
                    WHEN 'presentada' THEN 'submitted' WHEN 'ganada' THEN 'won' WHEN 'perdida' THEN 'lost'
                    WHEN 'descartada' THEN 'discarded' ELSE followup_status END,
                status_code = IF(status_code = 'BORRADA', 'DELETED', status_code)");
            $db->query('RENAME TABLE prospeccion_licitaciones TO prospect_tenders');
        }
    }

    // ================================================================== filtros

    /** Normaliza lo que llega por GET (o de la sesión) a valores conocidos. */
    public static function filtros(array $in): array
    {
        $dias = (int) ($in['dias'] ?? 30);
        $ant = (int) ($in['antiguedad'] ?? 3);
        $actos = array_values(array_intersect((array) ($in['actos'] ?? self::ACTOS_POR_DEFECTO), array_keys(self::ACTOS)));

        return [
            'provincia'     => Provincias::canonica((string) ($in['provincia'] ?? '')) ?? '',
            'sector'        => isset(self::SECTORES[$in['sector'] ?? '']) ? (string) $in['sector'] : '',
            'dias'          => isset(self::DIAS[$dias]) ? $dias : 30,
            'antiguedad'    => isset(self::ANTIGUEDAD[$ant]) ? $ant : 3,
            'contacto'      => !empty($in['contacto']),
            'sin_web'       => !empty($in['sin_web']),
            'digital'       => !empty($in['digital']),
            'actos'         => $actos ?: self::ACTOS_POR_DEFECTO,
            'ocultar'       => in_array($in['ocultar'] ?? '', ['descartadas', 'tocadas', 'nada'], true) ? $in['ocultar'] : 'descartadas',
            'orden'         => ($in['orden'] ?? '') === 'fecha' ? 'fecha' : 'encaje',
            'followup_status'    => isset(self::SEG_ESTADOS[$in['followup_status'] ?? '']) ? (string) $in['followup_status'] : '',
            'q'             => mb_substr(trim((string) ($in['q'] ?? '')), 0, 80),
            'importe_max'   => max(0, (int) ($in['importe_max'] ?? 0)),
            'procedimiento' => array_key_exists((string) ($in['procedimiento'] ?? ''), LicitacionesPlacsp::PROCEDIMIENTOS) ? (string) $in['procedimiento'] : '',
            'cerradas'      => !empty($in['cerradas']),
            'encajan'       => !empty($in['encajan']),
        ];
    }

    /**
     * Condiciones comunes sobre companies (c), company_enrichment (e) y prospect_followups (s).
     * @return array{0: string[], 1: array}
     */
    private function condiciones(array $f, bool $conSector = true, bool $conProvincia = true): array
    {
        $w = [self::SQL_ACTIVA, self::SQL_FORMA];
        $b = [];

        $optouts = $this->optouts();
        if ($optouts) {
            $w[] = '(c.cif IS NULL OR c.cif NOT IN (' . implode(',', array_fill(0, count($optouts), '?')) . '))';
            array_push($b, ...$optouts);
        }
        if ($conProvincia && $f['provincia'] !== '') {
            $v = Provincias::variantes($f['provincia']);
            $w[] = 'c.registro_mercantil IN (' . implode(',', array_fill(0, count($v), '?')) . ')';
            array_push($b, ...$v);
        }
        if ($conSector && $f['sector'] !== '') {
            [$sq, $sb] = $this->sqlSector($f['sector']);
            $w[] = $sq;
            array_push($b, ...$sb);
        }
        // Solo empresas con email (16-10-2026): Adrián solo contacta por correo, no llama.
        $w[] = $this->sqlEmail();
        if ($f['ocultar'] === 'descartadas') {
            $w[] = "(s.status IS NULL OR s.status <> 'discarded')";
        } elseif ($f['ocultar'] === 'tocadas') {
            $w[] = 's.id IS NULL';
        }

        return [$w, $b];
    }

    /** @return array{0: string, 1: array} */
    private function sqlSector(string $clave): array
    {
        $partes = [];
        foreach (self::SECTORES[$clave][1] as $p) {
            $partes[] = "c.cnae_code LIKE '" . preg_replace('/\D/', '', $p) . "%'";
        }

        return ['(' . implode(' OR ', $partes) . " OR ((c.cnae_code IS NULL OR c.cnae_code = '') AND c.objeto_social REGEXP ?))",
            [self::patronObjeto($clave)]];
    }

    /** Código en el texto ("Actividad principal: 56.10", "CNAE: 5610") o palabras del sector */
    private static function patronCodigo(string $clave): string
    {
        return '(principal|cnae)[^0-9]{0,40}(' . implode('|', self::SECTORES[$clave][1]) . ')';
    }

    private static function patronObjeto(string $clave): string
    {
        return self::patronCodigo($clave) . '|' . self::SECTORES[$clave][2];
    }

    /** Sector deducido del objeto social, para las empresas sin CNAE (mismo orden que el SQL) */
    public static function sectorDeTexto(string $objeto): string
    {
        $objeto = trim($objeto);
        if ($objeto === '' || $objeto === '-') {
            return '';
        }
        foreach (self::SECTORES as $k => $s) {
            if (preg_match('/' . self::patronCodigo($k) . '/iu', $objeto)) {
                return $s[0];
            }
        }
        foreach (self::SECTORES as $k => $s) {
            if (preg_match('/' . $s[2] . '/iu', $objeto)) {
                return $s[0];
            }
        }

        return '';
    }

    /** CASE de SQL que da la clave de sector (CNAE, si no código en el objeto, si no palabras) */
    private function sqlCaseSector(): array
    {
        $w = [];
        $b = [];
        foreach (self::SECTORES as $k => $s) {
            $likes = array_map(static fn ($p) => "c.cnae_code LIKE '" . preg_replace('/\D/', '', $p) . "%'", $s[1]);
            $w[] = 'WHEN ' . implode(' OR ', $likes) . " THEN '{$k}'";
        }
        $sinCnae = "(c.cnae_code IS NULL OR c.cnae_code = '')";
        foreach (self::SECTORES as $k => $s) {
            $w[] = "WHEN {$sinCnae} AND c.objeto_social REGEXP ? THEN '{$k}'";
            $b[] = self::patronCodigo($k);
        }
        foreach (self::SECTORES as $k => $s) {
            $w[] = "WHEN {$sinCnae} AND c.objeto_social REGEXP ? THEN '{$k}'";
            $b[] = $s[2];
        }

        return ['CASE ' . implode(' ', $w) . " ELSE '' END", $b];
    }

    private function optouts(): array
    {
        static $lista = null;
        if ($lista === null) {
            $lista = !$this->db->tableExists('company_privacy_optouts') ? [] : array_values(array_filter(array_column(
                $this->db->table('company_privacy_optouts')->select('cif')->get()->getResultArray(), 'cif')));
        }

        return $lista;
    }

    /**
     * Columnas que existen de verdad en companies y company_enrichment (16-10-2026). La base
     * local de Laragon no tiene todas las de producción (p. ej. company_enrichment.phone_enriched),
     * así que cada columna opcional se pide solo si existe; si no, sale como NULL.
     */
    private function columnas(string $tabla): array
    {
        static $cache = [];
        if (!isset($cache[$tabla])) {
            try {
                $cache[$tabla] = array_flip($this->db->tableExists($tabla) ? $this->db->getFieldNames($tabla) : []);
            } catch (\Throwable $e) {
                $cache[$tabla] = [];
            }
        }

        return $cache[$tabla];
    }

    /** "alias.columna" si existe; si no, NULL */
    private function col(string $alias, string $columna): string
    {
        $tabla = $alias === 'e' ? 'company_enrichment' : 'companies';

        return isset($this->columnas($tabla)[$columna]) ? "{$alias}.{$columna}" : 'NULL';
    }

    private function sqlNoVacio(string $expr, bool $guion = false): string
    {
        return $expr === 'NULL' ? '0' : "({$expr} IS NOT NULL AND {$expr} <> ''" . ($guion ? " AND {$expr} <> '-'" : '') . ')';
    }

    /** Tiene un email con forma de email. Sin columna email en la base, ninguna empresa cumple. */
    private function sqlEmail(): string
    {
        $col = $this->col('e', 'email');

        return $col === 'NULL' ? '0' : "({$col} LIKE '%_@_%._%')";
    }

    public function hayColumnaEmail(): bool
    {
        return $this->col('e', 'email') !== 'NULL';
    }

    private function sqlContacto(): string
    {
        return '(' . $this->sqlNoVacio($this->col('c', 'phone'), true) . ' OR ' . $this->sqlNoVacio($this->col('e', 'phone_enriched'))
            . ' OR ' . $this->sqlNoVacio($this->col('e', 'email')) . ')';
    }

    private function sqlSinWeb(): string
    {
        return 'NOT ' . $this->sqlNoVacio($this->col('e', 'website_official'));
    }

    private function selectEmpresa(): string
    {
        $c = fn ($a, $col, $como = null) => $this->col($a, $col) . ' AS ' . ($como ?? $col);

        return implode(', ', [
            'c.id', 'c.company_name', 'c.cif', $c('c', 'cnae_code'), $c('c', 'cnae_label'), $c('c', 'registro_mercantil'),
            $c('c', 'municipality'), 'c.fecha_constitucion', $c('c', 'capital_social_raw'), $c('c', 'objeto_social'),
            $c('c', 'ult_cuentas_anio'), $c('c', 'phone'), $c('c', 'address'),
            $c('e', 'website_official', 'web'), $c('e', 'email'), $c('e', 'phone_enriched'),
            's.status AS followup_status', 's.note AS followup_note', 's.next_contact_date AS followup_next',
        ]);
    }

    private const JOINS = "JOIN company_enrichment e ON e.company_id = c.id
        LEFT JOIN prospect_followups s ON s.company_id = c.id";

    // ================================================================== pestañas de empresas

    /**
     * Filas de una pestaña de empresas.
     * @return array{filas: array, total: int, tope: bool}
     */
    public function empresas(string $pestana, array $f, int $limite = self::LIMITE): array
    {
        [$w, $b] = $this->condiciones($f);
        $hoy = date('Y-m-d');
        $desde = date('Y-m-d', strtotime('-' . $f['dias'] . ' days'));
        $sel = $this->selectEmpresa();
        $joins = self::JOINS;

        switch ($pestana) {
            case 'nuevas':
                $w[] = 'c.fecha_constitucion BETWEEN ? AND ?';
                array_push($b, $desde, $hoy);
                if ($f['sin_web']) {
                    $w[] = $this->sqlSinWeb();
                }
                $sql = "SELECT {$sel} FROM companies c {$joins} WHERE " . implode(' AND ', $w)
                    . ' ORDER BY c.fecha_constitucion DESC, c.id DESC';
                $cuenta = "SELECT COUNT(*) AS n FROM companies c {$joins} WHERE " . implode(' AND ', $w);
                break;

            case 'sin-web':
                $w[] = $this->sqlSinWeb();
                $w[] = 'c.fecha_constitucion BETWEEN ? AND ?';
                array_push($b, date('Y-m-d', strtotime('-' . $f['antiguedad'] . ' years')), $hoy);
                $sql = "SELECT {$sel} FROM companies c {$joins} WHERE " . implode(' AND ', $w)
                    . ' ORDER BY c.fecha_constitucion DESC, c.id DESC';
                $cuenta = "SELECT COUNT(*) AS n FROM (SELECT 1 FROM companies c {$joins} WHERE " . implode(' AND ', $w) . ' LIMIT 5001) x';
                break;

            case 'subvenciones':
                $w[] = 'sb.fecha_concesion BETWEEN ? AND ?';
                array_push($b, $desde, $hoy);
                $w[] = "sb.company_cif REGEXP '^[ABCDEFGHJNRUVW][0-9]'";
                if ($f['digital']) {
                    $w[] = self::SQL_DIGITAL;
                }
                $grupo = "SELECT {$sel}, COUNT(*) AS subv_n, SUM(sb.importe) AS subv_total, MAX(sb.fecha_concesion) AS subv_ultima,
                        MAX(" . self::SQL_DIGITAL . ") AS subv_digital,
                        SUBSTRING_INDEX(GROUP_CONCAT(LEFT(sb.convocatoria, 300) ORDER BY sb.fecha_concesion DESC SEPARATOR '||'), '||', 1) AS subv_conv
                    FROM company_subsidies sb JOIN companies c ON c.cif = sb.company_cif {$joins}
                    WHERE " . implode(' AND ', $w) . ' GROUP BY c.id';
                $sql = $grupo . ' ORDER BY subv_ultima DESC, subv_total DESC';
                $cuenta = "SELECT COUNT(*) AS n FROM ({$grupo} LIMIT 5001) x";
                break;

            case 'contratos':
                $w[] = 'cc.fecha_adjudicacion BETWEEN ? AND ?';
                array_push($b, $desde, $hoy);
                $grupo = "SELECT {$sel}, COUNT(*) AS con_n, SUM(cc.importe_adjudicacion) AS con_total, MAX(cc.fecha_adjudicacion) AS con_ultima,
                        SUM(cc.titulo_contrato LIKE '%[contrato menor]%') AS con_menores,
                        SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT cc.organo_contratacion ORDER BY cc.fecha_adjudicacion DESC SEPARATOR '||'), '||', 3) AS con_organos,
                        SUBSTRING_INDEX(GROUP_CONCAT(LEFT(cc.titulo_contrato, 200) ORDER BY cc.fecha_adjudicacion DESC SEPARATOR '||'), '||', 1) AS con_titulo
                    FROM company_contracts cc JOIN companies c ON c.cif = cc.company_cif {$joins}
                    WHERE " . implode(' AND ', $w) . ' GROUP BY c.id';
                $sql = $grupo . ' ORDER BY con_n DESC, con_total DESC';
                $cuenta = "SELECT COUNT(*) AS n FROM ({$grupo} LIMIT 5001) x";
                break;

            case 'cambios':
                $w[] = 'p.borme_date BETWEEN ? AND ?';
                array_push($b, $desde, $hoy);
                $w[] = 'p.act_types REGEXP ?';
                $b[] = $this->patronActos($f['actos']);
                $grupo = "SELECT {$sel}, MAX(p.borme_date) AS borme_fecha,
                        GROUP_CONCAT(DISTINCT p.act_types ORDER BY p.borme_date DESC SEPARATOR ', ') AS borme_actos
                    FROM borme_posts p JOIN companies c ON c.id = p.company_id {$joins}
                    WHERE " . implode(' AND ', $w) . ' GROUP BY c.id';
                $sql = $grupo . ' ORDER BY borme_fecha DESC';
                $cuenta = "SELECT COUNT(*) AS n FROM ({$grupo} LIMIT 5001) x";
                break;

            default:
                throw new \InvalidArgumentException('Pestaña desconocida: ' . $pestana);
        }

        $filas = $this->db->query($sql . ' LIMIT ' . (int) $limite, $b)->getResultArray();
        $total = (int) ($this->db->query($cuenta, $b)->getRow()->n ?? 0);

        $filas = $this->puntuar($filas);
        if ($f['orden'] === 'encaje') {
            usort($filas, static fn ($a, $z) => [$z['encaje'], $z['fecha_orden']] <=> [$a['encaje'], $a['fecha_orden']]);
        }

        return ['filas' => $filas, 'total' => $total, 'tope' => $total > 5000];
    }

    private function patronActos(array $actos): string
    {
        $p = [];
        foreach ($actos as $a) {
            $p[] = self::ACTOS[$a][1];
        }

        return implode('|', $p);
    }

    /**
     * Señales de los últimos 12 meses para las filas mostradas (subvenciones, contratos, cambios
     * en el BORME) y la puntuación de encaje (0-100) con sus etiquetas.
     */
    public function puntuar(array $filas): array
    {
        if (!$filas) {
            return [];
        }
        $cifs = array_values(array_unique(array_filter(array_map(static fn ($r) => strtoupper(trim((string) $r['cif'])), $filas))));
        $ids = array_map(static fn ($r) => (int) $r['id'], $filas);
        $hace12 = date('Y-m-d', strtotime('-12 months'));
        $hace90 = date('Y-m-d', strtotime('-90 days'));

        $subv = $cont = $borme = [];
        if ($cifs) {
            $in = implode(',', array_fill(0, count($cifs), '?'));
            foreach ($this->db->query("SELECT sb.company_cif AS cif, COUNT(*) AS n, MAX(" . self::SQL_DIGITAL . ") AS dig
                    FROM company_subsidies sb WHERE sb.company_cif IN ({$in}) AND sb.fecha_concesion >= ? GROUP BY sb.company_cif",
                    [...$cifs, $hace12])->getResultArray() as $r) {
                $subv[strtoupper($r['cif'])] = $r;
            }
            foreach ($this->db->query("SELECT company_cif AS cif, COUNT(*) AS n, SUM(importe_adjudicacion) AS total
                    FROM company_contracts WHERE company_cif IN ({$in}) AND fecha_adjudicacion >= ? GROUP BY company_cif",
                    [...$cifs, $hace12])->getResultArray() as $r) {
                $cont[strtoupper($r['cif'])] = $r;
            }
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        foreach ($this->db->query("SELECT company_id, MAX(borme_date) AS f FROM borme_posts
                WHERE company_id IN ({$in}) AND borme_date >= ? AND act_types REGEXP ? GROUP BY company_id",
                [...$ids, $hace90, $this->patronActos(self::ACTOS_POR_DEFECTO)])->getResultArray() as $r) {
            $borme[(int) $r['company_id']] = $r['f'];
        }
        $admins = $this->administradores($ids);

        $hoy = new \DateTimeImmutable('today');
        foreach ($filas as &$r) {
            $cif = strtoupper(trim((string) $r['cif']));
            $chips = [];
            $p = 0;

            $edad = null;
            if (!empty($r['fecha_constitucion'])) {
                $edad = (int) $hoy->diff(new \DateTimeImmutable($r['fecha_constitucion']))->days;
                if ($edad <= 30) {
                    $p += 35; $chips[] = ['Nueva (' . $edad . ' d)', 'nueva'];
                } elseif ($edad <= 90) {
                    $p += 25; $chips[] = ['Nueva (' . $edad . ' d)', 'nueva'];
                } elseif ($edad <= 365) {
                    $p += 10; $chips[] = ['Menos de 1 año', 'nueva'];
                }
            }
            $web = trim((string) ($r['web'] ?? ''));
            $tel = $this->telefono($r);
            $email = trim((string) ($r['email'] ?? ''));
            if ($web === '') {
                if (self::dominioPropio($email) !== '') {
                    $p += 15; $chips[] = ['Web sin confirmar', 'web'];
                } else {
                    $p += 25; $chips[] = ['Sin web', 'web'];
                }
            }
            // Email de dominio propio (no gmail, hotmail...): la empresa ya tiene dominio aunque no sepamos su web
            $r['dominio_email'] = self::dominioPropio($email);
            $r['admins'] = $admins[(int) $r['id']] ?? [];
            if ($r['admins']) {
                $p += 5; $chips[] = ['Administrador identificado', 'admin'];
            }
            if (isset($subv[$cif])) {
                if ((int) $subv[$cif]['dig'] === 1) {
                    $p += 15; $chips[] = ['Ayuda digitalización', 'subv'];
                } else {
                    $p += 8; $chips[] = ['Subvención', 'subv'];
                }
            }
            if (isset($cont[$cif])) {
                $n = (int) $cont[$cif]['n'];
                $p += $n >= 5 ? 15 : 10;
                $chips[] = [$n . ' contrato' . ($n === 1 ? '' : 's') . ' público' . ($n === 1 ? '' : 's'), 'contratos'];
            }
            if (isset($borme[(int) $r['id']])) {
                $p += 10; $chips[] = ['Cambio BORME', 'borme'];
            }

            $r['telefono'] = $tel;
            $r['encaje'] = min(100, $p);
            $r['chips'] = $chips;
            $r['sector'] = self::sectorDe((string) $r['cnae_code']) ?: self::sectorDeTexto((string) $r['objeto_social']);
            $r['sector_deducido'] = $r['sector'] !== '' && self::sectorDe((string) $r['cnae_code']) === '';
            $r['provincia'] = Provincias::canonica((string) $r['registro_mercantil']) ?? (string) $r['registro_mercantil'];
            $r['fecha_orden'] = (string) ($r['subv_ultima'] ?? $r['con_ultima'] ?? $r['borme_fecha'] ?? $r['fecha_constitucion'] ?? '');
        }
        unset($r);

        return $filas;
    }

    /** Teléfono fijo de la empresa (nunca el móvil) */
    private function telefono(array $r): string
    {
        foreach ([$r['phone'] ?? '', $r['phone_enriched'] ?? ''] as $t) {
            $t = trim((string) $t);
            if ($t !== '' && $t !== '-') {
                // Si hay varios números pegados, el primero
                return trim((string) preg_split('/\s*[,;\/|]\s*/', $t)[0]);
            }
        }

        return '';
    }

    public static function sectorDe(string $cnae): string
    {
        $cnae = preg_replace('/\D/', '', $cnae);
        if ($cnae === '') {
            return '';
        }
        foreach (self::SECTORES as $s) {
            foreach ($s[1] as $p) {
                if (strncmp($cnae, $p, strlen($p)) === 0) {
                    return $s[0];
                }
            }
        }

        return '';
    }

    // ================================================================== resumen

    public function resumen(array $f): array
    {
        $cache = \Config\Services::cache();
        $clave = 'prosp_resumen_' . md5(json_encode(['email', $f['provincia'], $f['sector'], $f['dias'], $f['actos']]));
        $d = $cache->get($clave);
        if (!is_array($d)) {
            $d = $this->calcularResumen($f);
            $cache->save($clave, $d, self::CACHE_MIN * 60);
        }

        // Lo del seguimiento y las licitaciones cambia al momento: sin caché
        $d['seguimiento'] = [];
        foreach ($this->db->query('SELECT status, COUNT(*) AS n FROM prospect_followups GROUP BY status')->getResultArray() as $r) {
            $d['seguimiento'][$r['status']] = (int) $r['n'];
        }
        $d['proximos'] = $this->db->query("SELECT s.*, c.company_name, c.cif FROM prospect_followups s
                JOIN companies c ON c.id = s.company_id
                WHERE s.next_contact_date IS NOT NULL AND s.next_contact_date <= ? AND s.status NOT IN ('client', 'discarded')
                ORDER BY s.next_contact_date LIMIT 8", [date('Y-m-d', strtotime('+7 days'))])->getResultArray();

        $lf = $f;
        $lf['cerradas'] = false;
        $lf['orden'] = 'fecha';
        $lf['encajan'] = false;
        $lic = $this->licitaciones($lf, 6, 'limite');
        $d['lic_abiertas'] = $lic['total'];
        $d['lic_pronto'] = $lic['filas'];
        $d['lic_7d'] = (int) ($this->db->query("SELECT COUNT(*) AS n FROM prospect_tenders
                WHERE status_code = 'PUB' AND deadline BETWEEN NOW() AND ?" . ($f['provincia'] !== '' ? ' AND provincia = ?' : ''),
                array_values(array_filter([date('Y-m-d 23:59:59', strtotime('+7 days')), $f['provincia'] ?: null])))->getRow()->n ?? 0);
        $d['lic_ultima_carga'] = $this->db->query('SELECT MAX(updated_at) AS u FROM prospect_tenders')->getRow()->u ?? null;

        return $d;
    }

    private function calcularResumen(array $f): array
    {
        $hoy = date('Y-m-d');
        $desde = date('Y-m-d', strtotime('-' . $f['dias'] . ' days'));
        $f2 = $f;
        $f2['ocultar'] = 'nada';
        $j = self::JOINS;

        // Recién constituidas: total, sin web, contactables
        [$w, $b] = $this->condiciones($f2);
        $w[] = 'c.fecha_constitucion BETWEEN ? AND ?';
        array_push($b, $desde, $hoy);
        $r = $this->db->query("SELECT COUNT(*) AS n, SUM(" . $this->sqlSinWeb() . ") AS sin_web
                FROM companies c {$j} WHERE " . implode(' AND ', $w), $b)->getRowArray();
        $d = ['ahora' => date('Y-m-d H:i:s'), 'nuevas' => (int) $r['n'], 'nuevas_sin_web' => (int) $r['sin_web']];

        // Subvenciones (y de digitalización)
        [$w, $b] = $this->condiciones($f2);
        $w[] = 'sb.fecha_concesion BETWEEN ? AND ?';
        array_push($b, $desde, $hoy);
        $w[] = "sb.company_cif REGEXP '^[ABCDEFGHJNRUVW][0-9]'";
        $r = $this->db->query("SELECT COUNT(DISTINCT c.id) AS n, COUNT(DISTINCT CASE WHEN " . self::SQL_DIGITAL . " THEN c.id END) AS dig
                FROM company_subsidies sb JOIN companies c ON c.cif = sb.company_cif {$j} WHERE " . implode(' AND ', $w), $b)->getRowArray();
        $d['subvenciones'] = (int) $r['n'];
        $d['subvenciones_digital'] = (int) $r['dig'];

        // Contratos
        [$w, $b] = $this->condiciones($f2);
        $w[] = 'cc.fecha_adjudicacion BETWEEN ? AND ?';
        array_push($b, $desde, $hoy);
        $r = $this->db->query("SELECT COUNT(DISTINCT c.id) AS n, COALESCE(SUM(cc.importe_adjudicacion), 0) AS total
                FROM company_contracts cc JOIN companies c ON c.cif = cc.company_cif {$j} WHERE " . implode(' AND ', $w), $b)->getRowArray();
        $d['contratos'] = (int) $r['n'];
        $d['contratos_importe'] = (float) $r['total'];

        // Cambios en el BORME
        [$w, $b] = $this->condiciones($f2);
        $w[] = 'p.borme_date BETWEEN ? AND ?';
        array_push($b, $desde, $hoy);
        $w[] = 'p.act_types REGEXP ?';
        $b[] = $this->patronActos($f['actos']);
        $d['cambios'] = (int) ($this->db->query("SELECT COUNT(DISTINCT c.id) AS n FROM borme_posts p JOIN companies c ON c.id = p.company_id {$j}
                WHERE " . implode(' AND ', $w), $b)->getRow()->n ?? 0);

        // Nuevas por semana (12 semanas, ISO)
        [$w, $b] = $this->condiciones($f2);
        $inicio = date('Y-m-d', strtotime('monday this week -11 weeks'));
        $w[] = 'c.fecha_constitucion BETWEEN ? AND ?';
        array_push($b, $inicio, $hoy);
        $porSemana = [];
        foreach ($this->db->query("SELECT YEARWEEK(c.fecha_constitucion, 3) AS s, COUNT(*) AS n, SUM(" . $this->sqlSinWeb() . ") AS sin_web
                FROM companies c {$j} WHERE " . implode(' AND ', $w) . ' GROUP BY s', $b)->getResultArray() as $x) {
            $porSemana[(string) $x['s']] = ['n' => (int) $x['n'], 'sin_web' => (int) $x['sin_web']];
        }
        $d['semanas'] = [];
        for ($i = 0; $i < 12; $i++) {
            $lunes = strtotime($inicio . ' +' . $i . ' weeks');
            $k = date('oW', $lunes);
            $d['semanas'][] = ['lunes' => date('Y-m-d', $lunes), 'n' => $porSemana[$k]['n'] ?? 0, 'sin_web' => $porSemana[$k]['sin_web'] ?? 0];
        }

        // Reparto de las nuevas del periodo por sector (sin filtro de sector) y por provincia (sin filtro de provincia)
        [$w, $b] = $this->condiciones($f2, false, true);
        $w[] = 'c.fecha_constitucion BETWEEN ? AND ?';
        array_push($b, $desde, $hoy);
        $porSector = [];
        $sinSector = 0;
        [$case, $cb] = $this->sqlCaseSector();
        foreach ($this->db->query("SELECT {$case} AS s, COUNT(*) AS n FROM companies c {$j} WHERE " . implode(' AND ', $w) . ' GROUP BY s',
                array_merge($cb, $b))->getResultArray() as $x) {
            if ((string) $x['s'] === '') {
                $sinSector += (int) $x['n'];
            } else {
                $porSector[$x['s']] = (int) $x['n'];
            }
        }
        arsort($porSector);
        $d['por_sector'] = $porSector;
        $d['sin_sector'] = $sinSector;

        [$w, $b] = $this->condiciones($f2, true, false);
        $w[] = 'c.fecha_constitucion BETWEEN ? AND ?';
        array_push($b, $desde, $hoy);
        $porProv = [];
        foreach ($this->db->query('SELECT c.registro_mercantil AS p, COUNT(*) AS n FROM companies c ' . $j . ' WHERE ' . implode(' AND ', $w) . ' GROUP BY p', $b)->getResultArray() as $x) {
            $p = Provincias::canonica((string) $x['p']) ?? 'Sin provincia';
            $porProv[$p] = ($porProv[$p] ?? 0) + (int) $x['n'];
        }
        arsort($porProv);
        $d['por_provincia'] = array_slice($porProv, 0, 12, true);

        return $d;
    }

    // ================================================================== licitaciones

    /** @return array{filas: array, total: int} */
    public function licitaciones(array $f, int $limite = 500, string $orden = 'limite'): array
    {
        $w = [];
        $b = [];
        if (!$f['cerradas']) {
            $w[] = "l.status_code = 'PUB' AND (l.deadline IS NULL OR l.deadline >= NOW())";
        } else {
            $w[] = "l.status_code <> 'DELETED'";
        }
        if ($f['provincia'] !== '') {
            $w[] = 'l.province = ?';
            $b[] = $f['provincia'];
        }
        if ($f['importe_max'] > 0) {
            $w[] = 'COALESCE(l.amount_excl_vat, l.estimated_value) <= ?';
            $b[] = $f['importe_max'];
        }
        if ($f['procedimiento'] !== '') {
            $w[] = 'l.procedure_code = ?';
            $b[] = $f['procedimiento'];
        }
        if ($f['q'] !== '') {
            $w[] = '(l.title LIKE ? OR l.contracting_body LIKE ? OR l.file_number LIKE ?)';
            $q = '%' . $this->db->escapeLikeString($f['q']) . '%';
            array_push($b, $q, $q, $q);
        }
        if ($f['ocultar'] === 'descartadas') {
            $w[] = "(l.followup_status IS NULL OR l.followup_status <> 'discarded')";
        } elseif ($f['ocultar'] === 'tocadas') {
            $w[] = 'l.followup_status IS NULL';
        }
        $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
        $ord = $orden === 'limite'
            ? 'ORDER BY l.deadline IS NULL, l.deadline ASC, l.id DESC'
            : 'ORDER BY l.published_date DESC, l.id DESC';

        $filas = $this->db->query("SELECT l.* FROM prospect_tenders l {$where} {$ord} LIMIT " . (int) $limite, $b)->getResultArray();
        $total = (int) ($this->db->query("SELECT COUNT(*) AS n FROM prospect_tenders l {$where}", $b)->getRow()->n ?? 0);

        $hash = self::perfilHash();
        foreach ($filas as &$l) {
            $motivos = json_decode((string) ($l['ai_reasons'] ?? ''), true);
            if (($l['ai_score'] ?? null) !== null && is_array($motivos)) {
                $l['encaje'] = (int) $l['ai_score'];
                $l['motivos'] = $motivos;
                $l['encaje_origen'] = ($l['ai_profile_hash'] ?? '') === $hash ? 'ia' : 'ia_antigua';
            } else {
                [$l['encaje'], $l['motivos']] = self::encajeLicitacion($l);
                $l['encaje_origen'] = 'reglas';
            }
        }
        unset($l);
        if (!empty($f['encajan'])) {
            $antes = count($filas);
            // Con IA: proyecto de desarrollo y encaje de 50 o más; con reglas: 45 o más
            $filas = array_values(array_filter($filas, static fn ($l) => $l['encaje_origen'] === 'reglas'
                ? $l['encaje'] >= 45
                : ((int) ($l['ai_is_dev'] ?? 0) === 1 && $l['encaje'] >= 50)));
            $total -= $antes - count($filas);
        }
        if ($orden === 'limite' && ($f['orden'] ?? '') === 'encaje') {
            // Mejor encaje primero; a igualdad, la que cierra antes
            usort($filas, static fn ($a, $z) => [$z['encaje_origen'] !== 'reglas', (int) ($z['ai_is_dev'] ?? 0), $z['encaje'], $a['deadline'] ?? '9999']
                <=> [$a['encaje_origen'] !== 'reglas', (int) ($a['ai_is_dev'] ?? 0), $a['encaje'], $z['deadline'] ?? '9999']);
        }

        return ['filas' => $filas, 'total' => $total];
    }

    /**
     * Veredicto corto para la lista: 'si' (encajas), 'quiza', 'no' o 'pendiente' (sin evaluar con IA).
     */
    public static function veredicto(array $l): string
    {
        if (($l['encaje_origen'] ?? 'reglas') === 'reglas') {
            return 'pendiente';
        }
        if ((int) ($l['ai_is_dev'] ?? 0) !== 1) {
            return 'no';
        }

        return $l['encaje'] >= 70 ? 'si' : ($l['encaje'] >= 45 ? 'quiza' : 'no');
    }

    /**
     * "Preparar oferta": de qué va el proyecto, qué habría que hacer, qué suelen pedir y un borrador
     * del texto de la oferta técnica con el perfil de Adrián. Se genera una vez con la IA y se
     * guarda en prospect_tenders.ai_proposal; solo se rehace si se pide ($rehacer).
     *
     * @return array{ok: bool, oferta?: array, guardada?: bool, error?: string}
     */
    public function prepararOferta(int $id, bool $rehacer = false): array
    {
        $l = $this->db->table('prospect_tenders')->where('id', $id)->get()->getRowArray();
        if (!$l) {
            return ['ok' => false, 'error' => 'No encuentro esa licitación.'];
        }
        $guardada = json_decode((string) ($l['ai_proposal'] ?? ''), true);
        if (!$rehacer && is_array($guardada) && !empty($guardada['texto'])) {
            return ['ok' => true, 'oferta' => $guardada, 'guardada' => true];
        }
        if (empty(env('OPENAI_API_KEY')) || !class_exists(\App\Services\OpenAiService::class)) {
            return ['ok' => false, 'error' => 'No hay OPENAI_API_KEY en el .env.'];
        }

        $c = config('Prospeccion');
        $datos = [
            'titulo'        => (string) $l['title'],
            'organismo'     => (string) $l['contracting_body'],
            'lugar'         => trim(implode(', ', array_filter([(string) $l['contracting_body_city'], (string) $l['province']]))),
            'tipo'          => LicitacionesPlacsp::TIPOS[$l['contract_type']] ?? ('código ' . $l['contract_type']),
            'procedimiento' => LicitacionesPlacsp::PROCEDIMIENTOS[$l['procedure_code']] ?? ('código ' . $l['procedure_code']),
            'importe_sin_iva' => $l['amount_excl_vat'] !== null ? (float) $l['amount_excl_vat'] : null,
            'valor_estimado'  => $l['estimated_value'] !== null ? (float) $l['estimated_value'] : null,
            'lotes'         => $l['lot_count'] ? (int) $l['lot_count'] : 1,
            'cpv'           => (string) $l['cpv_codes'],
            'fin_de_plazo'  => (string) $l['deadline'],
            'resumen_previo' => (string) ($l['ai_summary'] ?? ''),
        ];
        $sistema = "Eres un asesor de licitaciones públicas en España y ayudas a {$c->nombre}, desarrollador freelance que trabaja solo. "
            . "Su perfil: {$c->tecnologias} Ofrece: {$c->servicios} "
            . ($c->experiencia !== '' ? "Experiencia: {$c->experiencia} " : 'Experiencia: no indicada (usa huecos [ ] para que la complete). ')
            . "Su web: {$c->web}. "
            . 'Solo tienes los datos del anuncio, NO los pliegos: no inventes requisitos concretos, plazos, criterios de puntuación ni tecnologías '
            . 'que no se deduzcan del anuncio; cuando algo dependa del pliego, dilo. Escribe en español de España, claro y sin relleno. '
            . 'Devuelve solo JSON con: '
            . '"de_que_va": 2 o 3 frases sencillas sobre qué quiere contratar el organismo y para qué; '
            . '"tareas": 3 a 6 tareas que probablemente habría que hacer (frases cortas); '
            . '"encaje": 1 o 2 frases diciendo con franqueza si este freelance encaja y por qué; '
            . '"revisar_en_pliego": 3 a 6 cosas concretas que debe comprobar en los pliegos antes de presentarse '
            . '(por ejemplo tecnología del sistema actual, solvencia exigida, criterios de adjudicación, si hay que subrogar personal, plazos de entrega); '
            . '"texto": borrador de la memoria u oferta técnica (350 a 500 palabras) en primera persona, adaptado a este proyecto y a su perfil: '
            . 'presentación breve, entendimiento de la necesidad, solución y forma de trabajar (fases, comunicación, entregas), mantenimiento o soporte si aplica, '
            . 'y por qué él. Usa huecos entre corchetes para lo que debe completar ([años de experiencia], [proyecto similar], [plazo], [precio]). '
            . 'Sin títulos en markdown; separa los apartados con una línea en blanco y un título corto seguido de dos puntos.';
        $usuario = 'Licitación: ' . json_encode($datos, JSON_UNESCAPED_UNICODE);

        try {
            $txt = (new \App\Services\OpenAiService())->getChatResponse([
                ['role' => 'system', 'content' => $sistema],
                ['role' => 'user', 'content' => $usuario],
            ], ['throw' => true, 'temperature' => 0.4, 'response_format' => ['type' => 'json_object']]);
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion oferta] ' . $e->getMessage());
            return ['ok' => false, 'error' => 'La IA no ha respondido: ' . $e->getMessage()];
        }
        $j = json_decode(trim((string) $txt), true);
        if (!is_array($j) || trim((string) ($j['texto'] ?? '')) === '') {
            return ['ok' => false, 'error' => 'La IA no ha devuelto un texto válido. Prueba otra vez.'];
        }
        $lista = static fn ($v) => array_values(array_filter(array_map(static fn ($x) => trim((string) $x), (array) $v)));
        $oferta = [
            'de_que_va'         => trim((string) ($j['de_que_va'] ?? '')),
            'tareas'            => $lista($j['tareas'] ?? []),
            'encaje'            => trim((string) ($j['encaje'] ?? '')),
            'revisar_en_pliego' => $lista($j['revisar_en_pliego'] ?? []),
            'texto'             => trim(str_replace("\r", '', (string) $j['texto'])),
        ];
        $this->db->table('prospect_tenders')->where('id', $id)->update([
            'ai_proposal'    => json_encode($oferta, JSON_UNESCAPED_UNICODE),
            'ai_proposal_at' => date('Y-m-d H:i:s'),
        ]);

        return ['ok' => true, 'oferta' => $oferta, 'guardada' => false];
    }

    /** Huella del perfil de Adrián: si cambia, las licitaciones se vuelven a evaluar */
    public static function perfilHash(): string
    {
        $c = config('Prospeccion');

        return md5(implode('|', [$c->tecnologias ?? '', $c->servicios ?? '', $c->noHago ?? '', (string) ($c->importeMaximo ?? ''), $c->experiencia ?? '', 'v1']));
    }

    /** Licitaciones en plazo que la IA aún no ha evaluado con el perfil actual */
    public function pendientesIa(): int
    {
        if (!isset($this->columnasTabla('prospect_tenders')['ai_score'])) {
            return 0;
        }

        return (int) ($this->db->query("SELECT COUNT(*) AS n FROM prospect_tenders
                WHERE status_code = 'PUB' AND (deadline IS NULL OR deadline >= NOW())
                  AND (ai_evaluated_at IS NULL OR ai_profile_hash IS NULL OR ai_profile_hash <> ?)", [self::perfilHash()])->getRow()->n ?? 0);
    }

    private function columnasTabla(string $t): array
    {
        try {
            return array_flip($this->db->getFieldNames($t));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Evalúa con IA (OpenAiService) hasta $max licitaciones en plazo pendientes, de 10 en 10: si
     * son un proyecto de desarrollo, cuánto encajan con el perfil de Adrián (app/Config/Prospeccion.php),
     * qué piden en una frase y por qué. Guarda el resultado en prospect_tenders.ai_*.
     * Primero las que cierran antes.
     *
     * @return array{evaluadas: int, pendientes: int, error: ?string}
     */
    public function evaluarLicitacionesIa(int $max = 30): array
    {
        if (empty(env('OPENAI_API_KEY')) || !class_exists(\App\Services\OpenAiService::class)) {
            return ['evaluadas' => 0, 'pendientes' => $this->pendientesIa(), 'error' => 'No hay OPENAI_API_KEY en el .env'];
        }
        $hash = self::perfilHash();
        $filas = $this->db->query("SELECT id, title, contracting_body, contract_type, procedure_code, amount_excl_vat, estimated_value, lot_count, cpv_codes
                FROM prospect_tenders
                WHERE status_code = 'PUB' AND (deadline IS NULL OR deadline >= NOW())
                  AND (ai_evaluated_at IS NULL OR ai_profile_hash IS NULL OR ai_profile_hash <> ?)
                ORDER BY deadline IS NULL, deadline ASC LIMIT " . (int) $max, [$hash])->getResultArray();

        $hechas = 0;
        $error = null;
        foreach (array_chunk($filas, 10) as $lote) {
            try {
                $res = $this->evaluarLoteIa($lote);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                log_message('error', '[Prospeccion evaluar IA] ' . $error);
                break;
            }
            $ahora = date('Y-m-d H:i:s');
            foreach ($lote as $l) {
                $ev = $res[(int) $l['id']] ?? null;
                if ($ev === null) {
                    continue;
                }
                $this->db->table('prospect_tenders')->where('id', (int) $l['id'])->update([
                    'ai_score'        => $ev['encaje'],
                    'ai_is_dev'       => $ev['es_desarrollo'] ? 1 : 0,
                    'ai_summary'      => mb_substr($ev['resumen'], 0, 500),
                    'ai_reasons'      => json_encode($ev['motivos'], JSON_UNESCAPED_UNICODE),
                    'ai_profile_hash' => $hash,
                    'ai_evaluated_at' => $ahora,
                ]);
                $hechas++;
            }
        }

        return ['evaluadas' => $hechas, 'pendientes' => $this->pendientesIa(), 'error' => $error];
    }

    /** @return array<int, array{encaje: int, es_desarrollo: bool, resumen: string, motivos: array}> */
    private function evaluarLoteIa(array $lote): array
    {
        $c = config('Prospeccion');
        $lineas = [];
        foreach ($lote as $l) {
            $imp = $l['amount_excl_vat'] !== null ? number_format((float) $l['amount_excl_vat'], 0, ',', '.') . ' € sin IVA' : 'importe no indicado';
            if ($l['estimated_value'] !== null && $l['estimated_value'] != $l['amount_excl_vat']) {
                $imp .= ', valor estimado (con prórrogas) ' . number_format((float) $l['estimated_value'], 0, ',', '.') . ' €';
            }
            $lineas[] = json_encode([
                'id'            => (int) $l['id'],
                'titulo'        => (string) $l['title'],
                'organismo'     => (string) $l['contracting_body'],
                'tipo'          => LicitacionesPlacsp::TIPOS[$l['contract_type']] ?? ('código ' . $l['contract_type']),
                'procedimiento' => LicitacionesPlacsp::PROCEDIMIENTOS[$l['procedure_code']] ?? ('código ' . $l['procedure_code']),
                'importe'       => $imp,
                'lotes'         => $l['lot_count'] ? (int) $l['lot_count'] : 1,
                'cpv'           => (string) $l['cpv_codes'],
            ], JSON_UNESCAPED_UNICODE);
        }

        $sistema = 'Ayudas a un desarrollador freelance que trabaja solo, en España, a decidir a qué licitaciones públicas presentarse. '
            . "Su perfil: sabe hacer {$c->tecnologias} Ofrece: {$c->servicios} "
            . "No hace o no quiere: {$c->noHago} "
            . "Puede asumir contratos de hasta unos " . number_format((int) $c->importeMaximo, 0, ',', '.') . ' € al año sin IVA. '
            . ($c->experiencia !== '' ? "Experiencia: {$c->experiencia} " : '')
            . 'Para cada licitación decide: '
            . '"es_desarrollo": true solo si el trabajo principal es desarrollar, mantener, evolucionar o integrar software, webs, portales o aplicaciones '
            . '(a medida o sobre tecnologías abiertas); false si es reventa de licencias o suscripciones, hardware, comunicaciones, consultoría, '
            . 'formación, auditoría, contenidos o el producto cerrado de un fabricante concreto. '
            . '"encaje" de 0 a 100 para ESTE freelance: alto si es desarrollo con tecnologías que domina o fáciles de asumir y de un tamaño que puede hacer solo; '
            . 'bajo si no es desarrollo, si exige un producto ya hecho, ser partner o fabricante, certificaciones, equipo grande, o si parece el mantenimiento '
            . 'de un programa propietario de otra empresa (solo puede hacerlo quien lo hizo). Si el título no basta para saberlo, ponlo en medio y dilo. '
            . 'No tengas en cuenta el plazo (se muestra aparte). '
            . '"resumen": una frase clara, en español sencillo, de qué quieren contratar realmente (traduce si viene en catalán, euskera o gallego). '
            . '"motivos": de 2 a 4 motivos muy cortos (máximo 8 palabras), cada uno con "signo" "+" o "-". '
            . 'No inventes datos que no estén en la licitación. '
            . 'Responde solo con JSON: {"evaluaciones": [{"id": 1, "es_desarrollo": true, "encaje": 75, "resumen": "...", "motivos": [{"texto": "...", "signo": "+"}]}]}';
        $usuario = "Licitaciones (una por línea, en JSON):\n" . implode("\n", $lineas);

        $txt = (new \App\Services\OpenAiService())->getChatResponse([
            ['role' => 'system', 'content' => $sistema],
            ['role' => 'user', 'content' => $usuario],
        ], ['throw' => true, 'temperature' => 0.2, 'response_format' => ['type' => 'json_object']]);

        $j = json_decode(trim((string) $txt), true);
        if (!is_array($j) || !is_array($j['evaluaciones'] ?? null)) {
            throw new \RuntimeException('Respuesta de la IA no válida');
        }
        $res = [];
        foreach ($j['evaluaciones'] as $e) {
            $id = (int) ($e['id'] ?? 0);
            if ($id <= 0 || !isset($e['encaje'])) {
                continue;
            }
            $motivos = [];
            foreach ((array) ($e['motivos'] ?? []) as $m) {
                $t = trim((string) ($m['texto'] ?? ''));
                if ($t !== '') {
                    $motivos[] = [mb_substr($t, 0, 80), ($m['signo'] ?? '') === '-' ? '-' : '+'];
                }
            }
            usort($motivos, static fn ($a, $b) => ($a[1] === '-' ? 0 : 1) <=> ($b[1] === '-' ? 0 : 1));
            $res[$id] = [
                'encaje'        => max(0, min(100, (int) $e['encaje'])),
                'es_desarrollo' => !empty($e['es_desarrollo']),
                'resumen'       => trim((string) ($e['resumen'] ?? '')),
                'motivos'       => $motivos,
            ];
        }

        return $res;
    }

    /**
     * Cuánto encaja una licitación con un desarrollador freelance (0-100) y por qué.
     * Sube: servicios, CPV de desarrollo de software o web, palabras como desarrollo, web,
     * aplicación, plataforma, mantenimiento; importe entre 3.000 y 60.000 €; abierto simplificado.
     * Baja: suministros y licencias (reventa), hardware, formación oficial, auditorías y
     * consultoría, trabajos de contenidos, productos SaaS ya hechos, importes grandes y plazos
     * que se acaban.
     *
     * @return array{0: int, 1: array<int, array{0: string, 1: string}>} [puntuación, [[texto, '+'|'-'], ...]]
     */
    public static function encajeLicitacion(array $l): array
    {
        $p = 50;
        $m = [];
        $titulo = mb_strtolower(self::sinTildes((string) ($l['title'] ?? '')));
        $cpvs = array_filter(explode(',', (string) ($l['cpv_codes'] ?? '')));

        $tipo = (string) ($l['contract_type'] ?? '');
        if ($tipo === '2') {
            $p += 10; $m[] = ['Servicios', '+'];
        } elseif ($tipo === '1') {
            $p -= 20; $m[] = ['Suministro (comprar y entregar)', '-'];
        } elseif ($tipo === '3') {
            $p -= 30; $m[] = ['Obras', '-'];
        }

        // CPV
        $empieza = static function (array $prefijos) use ($cpvs): bool {
            foreach ($cpvs as $c) {
                foreach ($prefijos as $pre) {
                    if (strncmp($c, $pre, strlen($pre)) === 0) {
                        return true;
                    }
                }
            }
            return false;
        };
        $esLicencias = (bool) preg_match('/licencia|suscripci|renovacion/', $titulo);
        $esContenidos = (bool) preg_match('/lectura facil|comunicacion clara|redaccion|traduccion|contenidos|evaluacion|gobernanza|hoja de ruta|consultoria|asistencia tecnica/', $titulo);
        if ($esLicencias) {
            // Licencias o suscripciones: aunque el CPV sea de servicios TI, es reventa
        } elseif ($empieza(['7221', '7223', '7224', '72262', '72263', '72265', '72266', '72267', '72268', '7241', '7242'])) {
            $p += 25; $m[] = ['CPV de desarrollo de software o web', '+'];
        } elseif ($empieza(['7226', '725', '7231', '7232', '7240', '722'])) {
            $p += 10; $m[] = ['CPV de servicios informáticos', '+'];
        }
        if ($empieza(['728'])) {
            $p -= 15; $m[] = ['CPV de auditoría', '-'];
        }
        if ($cpvs && !$empieza(['72']) && $empieza(['48'])) {
            $p -= 25; $m[] = ['Solo licencias de software', '-'];
        }
        if ($cpvs && !$empieza(['72']) && $empieza(['30', '32', '31'])) {
            $p -= 25; $m[] = ['Hardware', '-'];
        }
        if (count($cpvs) >= 3) {
            $otros = count(array_filter($cpvs, static fn ($c) => strncmp($c, '72', 2) !== 0 && strncmp($c, '48', 2) !== 0));
            if ($otros > count($cpvs) / 2) {
                $p -= 10; $m[] = ['La mayoría no es informática', '-'];
            }
        }

        // Título
        if (!$esLicencias && preg_match('/desarroll|aplicaci|\bapp\b|\bweb\b|portal|plataforma|sede electronica|mantenimiento|programaci|integraci|migraci|wordpress|drupal|moodle|intranet|gestor de contenidos|accesibilidad|\bapi\b|software a medida/', $titulo)) {
            $p += $esContenidos ? 5 : 15; $m[] = ['El objeto es de desarrollo o web', '+'];
        }
        if (preg_match('/licencia|suscripci|suministro|servidor|equipos|equipamiento|hardware|microsoft|windows|autodesk|cisco|oracle|\bsap\b|vmware|adobe|figma|formacion oficial|certificaci|megafon|auditor|iso 27001|\bens\b|base de datos|renovacion/', $titulo)) {
            $p -= 30; $m[] = ['Licencias, equipos, formación oficial o auditoría', '-'];
        }
        if (preg_match('/software como servicio|\bsaas\b|en la nube/', $titulo)) {
            $p -= 30; $m[] = ['Piden un producto SaaS ya hecho', '-'];
        }
        if ($esContenidos) {
            $p -= 20; $m[] = ['Más consultoría o contenidos que desarrollo', '-'];
        }

        // Importe sin IVA (o valor estimado)
        $imp = $l['amount_excl_vat'] ?? null;
        $imp = $imp !== null && $imp !== '' ? (float) $imp : (($l['estimated_value'] ?? null) !== null ? (float) $l['estimated_value'] : null);
        if ($imp !== null) {
            if ($imp < 3000) {
                $p -= 10; $m[] = ['Importe muy pequeño', '-'];
            } elseif ($imp <= 60000) {
                $p += 10; $m[] = ['Importe asumible para un freelance', '+'];
            } elseif ($imp <= 150000) {
                $p -= 5; $m[] = ['Importe alto: pedirán solvencia', '-'];
            } else {
                $p -= 25; $m[] = ['Importe grande para un freelance', '-'];
            }
        }

        if ((string) ($l['procedure_code'] ?? '') === '9') {
            $p += 5; $m[] = ['Abierto simplificado', '+'];
        }

        if (!empty($l['deadline'])) {
            $dias = (strtotime($l['deadline']) - time()) / 86400;
            if ($dias < 3) {
                $p -= 20; $m[] = ['Cierra en menos de 3 días', '-'];
            } elseif ($dias < 6) {
                $p -= 10; $m[] = ['Poco plazo para preparar la oferta', '-'];
            }
        }

        // Primero lo que resta, que es lo que más importa para descartar
        usort($m, static fn ($a, $b) => ($a[1] === '-' ? 0 : 1) <=> ($b[1] === '-' ? 0 : 1));

        return [max(0, min(100, $p)), $m];
    }

    /**
     * Quién ha ganado antes contratos de informática con el órgano de una licitación (3 años), y
     * cuáles se parecen a lo que se licita ahora (16-10-2026).
     *
     * Se cogen los contratos de ese órgano en company_contracts (como mucho 400, los más
     * recientes; esa tabla no guarda CPV) y la IA (OpenAiService) decide cuáles son de software
     * o servicios informáticos y cuáles se parecen al objeto de la licitación: así un "programa
     * de fiestas" o un "SAI" no cuentan y salen arriba los que delatan al proveedor actual. Si la
     * IA no está o falla, se filtra por palabras del título. Se calcula una sola vez por licitación
     * (la primera vez que se abre) y se guarda en prospect_tenders.competition_json.
     *
     * @return array{filas: array, origen: string, revisados: int}
     */
    public function competencia(int $tenderId): array
    {
        $lic = $this->db->table('prospect_tenders')->select('id, contracting_body, title, competition_json')
            ->where('id', $tenderId)->get()->getRowArray();
        if (!$lic) {
            return ['filas' => [], 'origen' => 'ninguno', 'revisados' => 0];
        }
        // Ya calculada: se lee de la base, sin llamar a la IA
        $guardada = json_decode((string) ($lic['competition_json'] ?? ''), true);
        if (is_array($guardada) && isset($guardada['filas'])) {
            return $guardada;
        }
        $r = $this->calcularCompetencia(trim((string) $lic['contracting_body']), (string) $lic['title']);
        // Se guarda para siempre si la ha hecho la IA (o si el organismo no tiene contratos); si la IA
        // falló y salió por palabras clave, no se guarda, para reintentarlo con IA la próxima vez.
        if ($r['origen'] !== 'palabras' || empty(env('OPENAI_API_KEY'))) {
            $this->db->table('prospect_tenders')->where('id', $tenderId)->update([
                'competition_json' => json_encode($r, JSON_UNESCAPED_UNICODE),
                'competition_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return $r;
    }

    private function calcularCompetencia(string $organo, string $titulo): array
    {
        if ($organo === '') {
            return ['filas' => [], 'origen' => 'ninguno', 'revisados' => 0];
        }

        $contratos = $this->db->query("SELECT cc.id, cc.company_cif AS cif, cc.importe_adjudicacion AS importe, cc.fecha_adjudicacion AS fecha,
                    LEFT(cc.titulo_contrato, 220) AS titulo, c.company_name AS nombre
                FROM company_contracts cc LEFT JOIN companies c ON c.cif = cc.company_cif
                WHERE cc.organo_contratacion = ? AND cc.fecha_adjudicacion >= ?
                ORDER BY cc.fecha_adjudicacion DESC LIMIT 400",
            [$organo, date('Y-m-d', strtotime('-3 years'))])->getResultArray();
        if (!$contratos) {
            return ['filas' => [], 'origen' => 'ninguno', 'revisados' => 0];
        }

        $relevantes = null;   // id => 'alto' | 'medio'
        $origen = 'palabras';
        if (!empty(env('OPENAI_API_KEY')) && class_exists(\App\Services\OpenAiService::class)) {
            try {
                $relevantes = $this->competenciaIa($contratos, $titulo);
                $origen = 'ia';
            } catch (\Throwable $e) {
                log_message('error', '[Prospeccion competencia IA] ' . $e->getMessage());
            }
        }
        if ($relevantes === null) {
            $relevantes = [];
            foreach ($contratos as $c) {
                $t = mb_strtolower(self::sinTildes((string) $c['titulo']));
                if (preg_match('/software|aplicaci[oó]n|programa informatic|plataforma (digital|web|tecnologica|de gestion)|sede electronica|pagina web|portal web|\bweb\b|licencias? (de )?(uso|software)|desarrollo (de|del) (software|aplicac|sistema|portal)|sistemas? (de informacion|informatic)|mantenimiento (del|de los) sistemas? informatic|servicios informaticos|ciberseguridad|hosting|alojamiento web|base de datos|\bgmao\b|\berp\b|\bcrm\b/', $t)
                    && !preg_match('/\bsai\b|alimentacion ininterrumpida|concierto|actuacion|fiestas|obras? de|vehicul/', $t)) {
                    $relevantes[(int) $c['id']] = 'medio';
                }
            }
        }

        // Agrupar por empresa: primero las que tienen contratos parecidos, luego por número
        $porEmpresa = [];
        foreach ($contratos as $c) {
            $nivel = $relevantes[(int) $c['id']] ?? null;
            if ($nivel === null) {
                continue;
            }
            $k = strtoupper((string) $c['cif']);
            if (!isset($porEmpresa[$k])) {
                $porEmpresa[$k] = ['cif' => $c['cif'], 'nombre' => $c['nombre'], 'n' => 0, 'total' => null, 'ultima' => $c['fecha'],
                    'parecidos' => 0, 'titulo' => $c['titulo'], 'titulo_parecido' => null];
            }
            $e = &$porEmpresa[$k];
            $e['n']++;
            if ($c['importe'] !== null) {
                $e['total'] = (float) $e['total'] + (float) $c['importe'];
            }
            if ($c['fecha'] > $e['ultima']) {
                $e['ultima'] = $c['fecha'];
            }
            if ($nivel === 'alto') {
                $e['parecidos']++;
                $e['titulo_parecido'] ??= $c['titulo'];
            }
            unset($e);
        }
        $filas = array_values($porEmpresa);
        usort($filas, static fn ($a, $b) => [$b['parecidos'], $b['n'], (float) $b['total']] <=> [$a['parecidos'], $a['n'], (float) $a['total']]);

        return ['filas' => array_slice($filas, 0, 8), 'origen' => $origen, 'revisados' => count($contratos)];
    }

    /** La IA marca qué contratos son de informática ('medio') y cuáles se parecen a la licitación ('alto') */
    private function competenciaIa(array $contratos, string $titulo): array
    {
        $lineas = [];
        foreach ($contratos as $c) {
            $lineas[] = (int) $c['id'] . ' | ' . preg_replace('/\s+/', ' ', (string) $c['titulo']);
        }
        $sistema = 'Clasificas contratos públicos españoles. Te doy el objeto de una licitación de informática que está abierta y una lista '
            . 'de contratos que el mismo organismo adjudicó antes ("id | título"). Devuelve solo los contratos que sean de software o servicios '
            . 'informáticos: desarrollo o mantenimiento de aplicaciones, webs, portales, sedes electrónicas, plataformas, sistemas de información, '
            . 'licencias o suscripciones de software, soporte técnico informático, alojamiento, ciberseguridad, bases de datos, implantación de programas. '
            . 'NO incluyas: equipos físicos (ordenadores, servidores, SAI, impresoras, cableado, megafonía) salvo que el objeto principal sea software; '
            . 'telefonía y conectividad; obras; eventos, espectáculos o actividades culturales aunque digan "programa"; limpieza, mobiliario, formación no informática. '
            . 'Para cada contrato que incluyas, pon "alto" si trata del mismo sistema, programa, producto o finalidad que la licitación abierta '
            . '(por ejemplo el mantenimiento o la actualización del mismo programa), y "medio" si es informática pero de otra cosa. '
            . 'Responde solo con JSON: {"contratos": [{"id": 123, "parecido": "alto"}, ...]}. Si ninguno encaja: {"contratos": []}.';
        $usuario = 'Licitación abierta: ' . ($titulo !== '' ? $titulo : '(sin título)') . "\n\nContratos anteriores del organismo:\n" . implode("\n", $lineas);

        $txt = (new \App\Services\OpenAiService())->getChatResponse([
            ['role' => 'system', 'content' => $sistema],
            ['role' => 'user', 'content' => $usuario],
        ], ['throw' => true, 'temperature' => 0, 'response_format' => ['type' => 'json_object']]);

        $j = json_decode(trim((string) $txt), true);
        if (!is_array($j) || !isset($j['contratos']) || !is_array($j['contratos'])) {
            throw new \RuntimeException('Respuesta de la IA no válida');
        }
        $validos = array_flip(array_map(static fn ($c) => (int) $c['id'], $contratos));
        $res = [];
        foreach ($j['contratos'] as $x) {
            $id = (int) ($x['id'] ?? 0);
            if (isset($validos[$id])) {
                $res[$id] = ($x['parecido'] ?? '') === 'alto' ? 'alto' : 'medio';
            }
        }

        return $res;
    }

    // ================================================================== administradores y posible web

    /**
     * Administradores vigentes de cada empresa (BORME): el último acto de cada persona y cargo es
     * un nombramiento. Solo cargos que deciden (administradores, consejeros, presidente); los
     * "Socio único" salen partidos en la tabla y no se usan. Máximo 3 por empresa.
     * Son datos personales: se enseñan en pantalla para buscar a la persona una a una.
     *
     * @return array<int, array<int, array{nombre: string, cargo: string, persona: bool}>>
     */
    public function administradores(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids || !$this->db->tableExists('company_administrators')) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $filas = $this->db->query("SELECT company_id, post_id, action, position, name FROM company_administrators
                WHERE company_id IN ({$in}) AND position REGEXP '^Adm|Administrador|Consejero|Presidente|Vicepresidente'
                ORDER BY company_id, post_id, id", $ids)->getResultArray();

        $ultimo = [];
        foreach ($filas as $f) {
            $nombre = trim(preg_replace('/\s+/', ' ', (string) $f['name']));
            if (mb_strlen($nombre) < 4) {
                continue;
            }
            $ultimo[(int) $f['company_id']][mb_strtoupper($nombre) . '|' . $f['position']] = $f;
        }

        $orden = static fn ($cargo) => stripos($cargo, 'nico') !== false ? 0 : (stripos($cargo, 'Consejero Del') !== false ? 1 : (stripos($cargo, 'Presid') !== false ? 2 : 3));
        $res = [];
        foreach ($ultimo as $cid => $porClave) {
            $vistos = [];
            $lista = [];
            foreach ($porClave as $f) {
                if (stripos((string) $f['action'], 'nombram') !== 0) {
                    continue;   // cese, revocación...
                }
                $nombre = trim(preg_replace('/\s+/', ' ', (string) $f['name']));
                $k = mb_strtoupper($nombre);
                if (isset($vistos[$k])) {
                    continue;
                }
                $vistos[$k] = true;
                $esSociedad = (bool) preg_match('/\b(S\.?L\.?U?|S\.?A\.?U?|SOCIEDAD|SLP|SLU|LIMITADA|ANONIMA|AN[OÓ]NIMA)\b\.?$/iu', $nombre);
                $lista[] = [
                    'nombre'  => $esSociedad ? $nombre : mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8'),
                    'cargo'   => (string) $f['position'],
                    'persona' => !$esSociedad,
                ];
            }
            usort($lista, static fn ($a, $b) => $orden($a['cargo']) <=> $orden($b['cargo']));
            if ($lista) {
                $res[$cid] = array_slice($lista, 0, 3);
            }
        }

        return $res;
    }

    /**
     * Busca si la empresa tiene ya un dominio con su nombre (nombre.es, nombre.com, nombre-x.es...).
     * Se mira el DNS y, si responde, la portada: si sale el CIF es suya; si sale el nombre,
     * probable; si es una página de dominio aparcado o en venta, se avisa. Caché de 7 días.
     *
     * @return array{probados?: string[], encontrados?: array<int, array{dominio: string, estado: string, detalle: string}>}
     */
    public function posibleWeb(int $companyId): array
    {
        $cache = \Config\Services::cache();
        $clave = 'prosp_web_' . $companyId;
        $r = $cache->get($clave);
        if (is_array($r)) {
            return $r;
        }
        $c = $this->db->table('companies')->select('id, company_name, cif')->where('id', $companyId)->get()->getRowArray();
        if (!$c) {
            return [];
        }
        $dominios = self::dominiosCandidatos((string) $c['company_name']);
        // Primero el dominio de su email, si es propio
        if (isset($this->columnas('company_enrichment')['email'])) {
            $em = $this->db->table('company_enrichment')->select('email')->where('company_id', $companyId)->get()->getRow();
            $propio = self::dominioPropio((string) ($em->email ?? ''));
            if ($propio !== '') {
                $dominios = array_values(array_unique(array_merge([$propio], $dominios)));
            }
        }
        $vivos = [];
        foreach ($dominios as $d) {
            if (checkdnsrr($d . '.', 'A') || checkdnsrr($d . '.', 'CNAME')) {
                $vivos[] = $d;
            }
        }

        $res = [];
        $paginas = $vivos ? $this->descargarPortadas($vivos) : [];
        $cif = strtoupper(trim((string) $c['cif']));
        $palabras = self::palabrasNombre((string) $c['company_name']);
        foreach ($vivos as $d) {
            $html = $paginas[$d]['html'] ?? '';
            $codigo = $paginas[$d]['codigo'] ?? 0;
            $texto = mb_strtolower(self::sinTildes(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
            $plano = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $texto));
            if ($codigo === 0 || $codigo >= 400) {
                $res[] = ['dominio' => $d, 'estado' => 'sin_web', 'detalle' => 'Registrado, pero la web no responde' . ($codigo ? " (HTTP {$codigo})" : '')];
            } elseif ($cif !== '' && strlen($cif) === 9 && strpos($plano, $cif) !== false) {
                $res[] = ['dominio' => $d, 'estado' => 'suya', 'detalle' => 'La web muestra su CIF'];
            } elseif (preg_match('/(dominio (esta )?en venta|domain (is )?for sale|buy this domain|parked|parking|este dominio ha sido registrado|pagina en construccion|coming soon|proximamente|default web site page|welcome to nginx|it works!)/i', $texto)) {
                $res[] = ['dominio' => $d, 'estado' => 'aparcado', 'detalle' => 'Dominio aparcado, en venta o web en construcción'];
            } elseif ($palabras && count(array_filter($palabras, static fn ($p) => strpos($texto, $p) !== false)) === count($palabras)) {
                $res[] = ['dominio' => $d, 'estado' => 'probable', 'detalle' => 'La web menciona su nombre (sin CIF)'];
            } else {
                $res[] = ['dominio' => $d, 'estado' => 'otra', 'detalle' => 'Hay una web, pero no parece de esta empresa'];
            }
        }
        $res = ['probados' => $dominios, 'encontrados' => $res];
        $cache->save($clave, $res, ($res['encontrados'] ? 7 : 1) * 86400);

        return $res;
    }

    private const CORREOS_GRATIS = ['gmail.com', 'googlemail.com', 'hotmail.com', 'hotmail.es', 'outlook.com', 'outlook.es', 'live.com', 'live.es',
        'msn.com', 'yahoo.com', 'yahoo.es', 'ymail.com', 'icloud.com', 'me.com', 'mac.com', 'aol.com', 'gmx.com', 'gmx.es', 'protonmail.com',
        'proton.me', 'telefonica.net', 'movistar.es', 'terra.es', 'terra.com', 'orange.es', 'wanadoo.es', 'vodafone.es', 'ono.com', 'jazztel.es',
        'yandex.com', 'mail.com', 'zoho.com', 'telecable.es', 'euskaltel.net', 'r.telecable.es'];

    /** Dominio del email si es propio de la empresa (no un correo gratuito); si no, '' */
    public static function dominioPropio(string $email): string
    {
        if (!preg_match('/@([a-z0-9.-]+\.[a-z]{2,})$/i', trim($email), $m)) {
            return '';
        }
        $d = strtolower($m[1]);

        return in_array($d, self::CORREOS_GRATIS, true) ? '' : preg_replace('/^(mail|correo|email)\./', '', $d);
    }

    /** Dominios a probar a partir del nombre: sin forma jurídica, sin tildes; junto y con guiones; .es y .com */
    public static function dominiosCandidatos(string $nombre): array
    {
        $palabras = self::palabrasNombre($nombre, false);
        if (!$palabras) {
            return [];
        }
        $bases = [implode('', $palabras), implode('-', $palabras)];
        $sig = self::palabrasNombre($nombre);
        if (count($palabras) > 2 && count($sig) >= 2) {
            $bases[] = $sig[0] . $sig[1];
        }
        $out = [];
        foreach (array_unique($bases) as $b) {
            if (strlen($b) < 4 || strlen($b) > 60) {
                continue;
            }
            foreach (['es', 'com'] as $tld) {
                $out[] = $b . '.' . $tld;
            }
        }

        return array_values(array_unique($out));
    }

    /** Palabras del nombre sin forma jurídica (en minúsculas y sin tildes) */
    private static function palabrasNombre(string $nombre, bool $soloSignificativas = true): array
    {
        $n = mb_strtolower(self::sinTildes($nombre));
        $n = str_replace('&', ' y ', $n);
        // Forma jurídica al final, repetida si hace falta ("... sociedad limitada unipersonal", "s.l.u.")
        for ($i = 0; $i < 3; $i++) {
            $n = preg_replace('/[\s,.]+(sociedad (limitada|anonima)( (unipersonal|profesional|laboral|nueva empresa))?|sociedad cooperativa.*|s\.?\s?coop\.?.*|s\.?\s?l\.?\s?(u|p|l|ne)?\.?|s\.?\s?a\.?\s?u?\.?|slu|slp|sll|slne|c\.?\s?b\.?)\s*$/u', '', trim($n));
        }
        $n = preg_replace('/[^a-z0-9 ]+/', ' ', $n);
        $p = array_values(array_filter(explode(' ', $n), static fn ($w) => $w !== ''));
        if ($soloSignificativas) {
            $p = array_values(array_filter($p, static fn ($w) => strlen($w) >= 3 && !in_array($w, ['and', 'the', 'del', 'los', 'las', 'por', 'para'], true)));
            $p = array_slice($p, 0, 3);
        }

        return $p;
    }

    private static function sinTildes(string $s): string
    {
        return strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'ç' => 'c', 'à' => 'a', 'è' => 'e', 'ò' => 'o', 'ï' => 'i',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N', 'Ç' => 'C', 'À' => 'A', 'È' => 'E', 'Ò' => 'O', 'Ï' => 'I']);
    }

    /** Portadas en paralelo (https y, si falla, http), como mucho 400 KB y 6 s cada una */
    private function descargarPortadas(array $dominios): array
    {
        $res = [];
        foreach (['https', 'http'] as $esquema) {
            $pendientes = array_values(array_filter($dominios, static fn ($d) => ($res[$d]['codigo'] ?? 0) === 0 || ($res[$d]['codigo'] ?? 0) >= 400));
            if (!$pendientes) {
                break;
            }
            $mh = curl_multi_init();
            $hs = [];
            foreach ($pendientes as $d) {
                $ch = curl_init($esquema . '://' . $d . '/');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4,
                    CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 6, CURLOPT_ENCODING => '',
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; APIEmpresas-prospeccion/1.0)',
                    CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                    CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                    CURLOPT_SSL_VERIFYPEER => false,   // solo se lee la portada; muchas webs pequeñas tienen el certificado mal
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_NOPROGRESS => false,
                    CURLOPT_PROGRESSFUNCTION => static fn ($c, $dt, $d) => $d > 400000 ? 1 : 0,
                ]);
                curl_multi_add_handle($mh, $ch);
                $hs[$d] = $ch;
            }
            do {
                $st = curl_multi_exec($mh, $activos);
                if ($activos) {
                    curl_multi_select($mh, 1.0);
                }
            } while ($activos && $st === CURLM_OK);
            foreach ($hs as $d => $ch) {
                $res[$d] = ['codigo' => (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE), 'html' => (string) curl_multi_getcontent($ch)];
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        }

        return $res;
    }

    // ================================================================== correo personalizado

    /**
     * Redacta un correo para una empresa con lo que sabemos de ella (16-10-2026). Usa la IA del
     * proyecto (OpenAiService, gpt-4o-mini) y, si no hay clave o falla, una plantilla. No envía
     * nada: Adrián lo revisa y lo manda desde su correo.
     *
     * @return array{ok: bool, para?: string, asunto?: string, cuerpo?: string, origen?: string, error?: string}
     */
    public function redactarCorreo(int $companyId, int $variante = 0): array
    {
        $fila = $this->db->query('SELECT ' . $this->selectEmpresa() . ' FROM companies c
                LEFT JOIN company_enrichment e ON e.company_id = c.id
                LEFT JOIN prospect_followups s ON s.company_id = c.id
                WHERE c.id = ?', [$companyId])->getRowArray();
        if (!$fila) {
            return ['ok' => false, 'error' => 'No existe esa empresa.'];
        }
        $r = $this->puntuar([$fila])[0];
        $email = trim((string) $r['email']);
        if (!preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email)) {
            return ['ok' => false, 'error' => 'Esta empresa no tiene un email válido.'];
        }
        $h = $this->hechosCorreo($r);
        $cfg = config('Prospeccion');

        $origen = 'plantilla';
        $asunto = '';
        $cuerpo = '';
        if (!empty(env('OPENAI_API_KEY')) && class_exists(\App\Services\OpenAiService::class)) {
            try {
                [$asunto, $cuerpo] = $this->correoIa($h, $cfg, $variante);
                $origen = 'ia';
            } catch (\Throwable $e) {
                log_message('error', '[Prospeccion correo] ' . $e->getMessage());
            }
        }
        if ($asunto === '' || $cuerpo === '') {
            [$asunto, $cuerpo] = $this->correoPlantilla($h, $cfg, $variante);
            $origen = 'plantilla';
        }

        return ['ok' => true, 'para' => $email, 'asunto' => $asunto, 'cuerpo' => $cuerpo, 'origen' => $origen];
    }

    /** "GUSTO CANALLA SL" => "Gusto Canalla" (sin forma jurídica, para texto corrido) */
    public static function nombreCorreo(string $raw): string
    {
        helper('company');
        $n = function_exists('company_display_name') ? company_display_name($raw, $raw) : $raw;
        for ($i = 0; $i < 2; $i++) {
            $n = preg_replace('/[\s,]+(S\.?\s?L\.?\s?(U|L|N\.?E|P)?\.?|S\.?\s?A\.?\s?U?\.?|S\.?\s?Coop\.?.*|C\.?\s?B\.?|Sociedad (Limitada|An[oó]nima)( (Unipersonal|Profesional|Laboral|Nueva Empresa))?)\s*$/iu', '', trim($n));
        }

        return trim($n) !== '' ? trim($n) : trim($raw);
    }

    /** Lo que se sabe de la empresa, en frases cortas y ciertas (solo datos de la base) */
    private function hechosCorreo(array $r): array
    {
        $cif = strtoupper(trim((string) $r['cif']));
        $h = [
            'empresa'     => self::nombreCorreo((string) $r['company_name']),
            'sector'      => (string) $r['sector'],
            'actividad'   => '',
            'municipio'   => mb_convert_case(mb_strtolower(trim((string) $r['municipality'])), MB_CASE_TITLE, 'UTF-8'),
            'provincia'   => (string) $r['provincia'],
            'dias'        => null,
            'web'         => trim((string) $r['web']),
            'dominio'     => (string) ($r['dominio_email'] ?? ''),
            'subvencion'  => null,
            'digital'     => false,
            'contratos'   => null,
            'cambio'      => null,
        ];
        $obj = trim((string) $r['objeto_social']);
        if ($obj !== '' && $obj !== '-') {
            $h['actividad'] = mb_strimwidth(preg_replace('/\s+/', ' ', $obj), 0, 300, '…');
        } elseif (trim((string) $r['cnae_label']) !== '') {
            $h['actividad'] = (string) $r['cnae_label'];
        }
        if (!empty($r['fecha_constitucion'])) {
            $h['dias'] = (int) (new \DateTimeImmutable('today'))->diff(new \DateTimeImmutable($r['fecha_constitucion']))->days;
        }
        if ($cif !== '') {
            $sv = $this->db->query("SELECT fecha_concesion, LEFT(convocatoria, 220) AS conv,
                    (convocatoria LIKE '%digital%' OR convocatoria LIKE '%red.es%') AS dig
                    FROM company_subsidies WHERE company_cif = ? AND fecha_concesion >= ? ORDER BY fecha_concesion DESC LIMIT 1",
                [$cif, date('Y-m-d', strtotime('-18 months'))])->getRowArray();
            if ($sv) {
                $h['subvencion'] = trim((string) $sv['conv']);
                $h['digital'] = (int) $sv['dig'] === 1;
            }
            $ct = $this->db->query('SELECT COUNT(*) AS n, SUBSTRING_INDEX(GROUP_CONCAT(organo_contratacion ORDER BY fecha_adjudicacion DESC SEPARATOR \'||\'), \'||\', 1) AS organo,
                    SUBSTRING_INDEX(GROUP_CONCAT(LEFT(titulo_contrato, 160) ORDER BY fecha_adjudicacion DESC SEPARATOR \'||\'), \'||\', 1) AS titulo
                    FROM company_contracts WHERE company_cif = ? AND fecha_adjudicacion >= ?',
                [$cif, date('Y-m-d', strtotime('-24 months'))])->getRowArray();
            if ($ct && (int) $ct['n'] > 0) {
                $h['contratos'] = ['n' => (int) $ct['n'], 'organo' => (string) $ct['organo'], 'titulo' => (string) $ct['titulo']];
            }
        }
        $bp = $this->db->query('SELECT borme_date, act_types FROM borme_posts WHERE company_id = ? AND borme_date >= ? AND act_types REGEXP ?
                ORDER BY borme_date DESC LIMIT 1', [(int) $r['id'], date('Y-m-d', strtotime('-120 days')), $this->patronActos(self::ACTOS_POR_DEFECTO)])->getRowArray();
        if ($bp) {
            $h['cambio'] = (string) $bp['act_types'];
        }

        return $h;
    }

    private function correoIa(array $h, $cfg, int $variante): array
    {
        $trato = $cfg->tratamiento === 'usted' ? 'de usted' : 'de tú';
        $datos = ["Empresa: {$h['empresa']}"];
        if ($h['sector'] !== '') {
            $datos[] = "Sector: {$h['sector']}";
        }
        if ($h['actividad'] !== '') {
            $datos[] = "Actividad según su objeto social: {$h['actividad']}";
        }
        if ($h['municipio'] !== '' || $h['provincia'] !== '') {
            $datos[] = 'Localidad: ' . trim($h['municipio'] . ($h['provincia'] !== '' && $h['provincia'] !== $h['municipio'] ? " ({$h['provincia']})" : ''));
        }
        if ($h['dias'] !== null && $h['dias'] <= 180) {
            $datos[] = "Constituida hace {$h['dias']} días (empresa recién creada)";
        }
        if ($h['web'] !== '') {
            $datos[] = "Tiene web: {$h['web']}";
        } elseif ($h['dominio'] !== '') {
            $datos[] = "No conocemos su web, pero su email usa el dominio propio {$h['dominio']} (puede tener web o no; no lo afirmes)";
        } else {
            $datos[] = 'No le conocemos web y su email es de un proveedor gratuito (gmail, hotmail...)';
        }
        if ($h['subvencion'] !== null) {
            $datos[] = ($h['digital'] ? 'Ha recibido una ayuda pública de digitalización' : 'Ha recibido una subvención pública') . ": {$h['subvencion']}";
        }
        if ($h['contratos'] !== null) {
            $datos[] = "Ha ganado {$h['contratos']['n']} contrato(s) público(s) en 2 años; el último con {$h['contratos']['organo']}: {$h['contratos']['titulo']}";
        }
        if ($h['cambio'] !== null) {
            $datos[] = "Cambio reciente en el BORME: {$h['cambio']}";
        }

        $enfoques = [
            'el problema o la oportunidad más clara que se deduce de sus datos',
            'un ejemplo muy concreto de herramienta o mejora que le ahorraría tiempo o le traería clientes',
            'un tono más breve y directo, en cuatro o cinco frases',
        ];
        $enfoque = $enfoques[$variante % count($enfoques)];

        $sistema = "Eres {$cfg->nombre}, desarrollador freelance en España, y escribes un primer correo a una empresa que no te conoce. "
            . "Reglas: español de España, trato {$trato}, entre 80 y 140 palabras, sin listas ni negritas ni emojis. "
            . "Usa SOLO los datos que te doy; no inventes nada sobre la empresa (ni clientes, ni problemas, ni cifras). "
            . "Menciona como mucho uno o dos datos suyos, con naturalidad (nada de 'he visto en el BORME' ni 'según datos públicos'; di 'he visto que...'). "
            . "No saludes por el nombre de ninguna persona: empieza con 'Hola,' o 'Buenos días,'. "
            . "Propón una sola idea útil y concreta para su caso, tomada de estos servicios: {$cfg->servicios} "
            . ($cfg->experiencia !== '' ? "Puedes mencionar, si encaja: {$cfg->experiencia} " : '')
            . "No des precios ni prometas resultados. No propongas llamadas: termina con una pregunta sencilla para seguir por correo. "
            . "Firma con '{$cfg->nombre}' y la web {$cfg->web}. Después de la firma añade una línea: 'Si no te interesa, dímelo y no vuelvo a escribirte.' "
            . ($cfg->tratamiento === 'usted' ? "(adaptada a usted: 'Si no le interesa, dígamelo y no volveré a escribirle.') " : '')
            . 'El asunto: corto (máximo 7 palabras), concreto, sin mayúsculas de reclamo ni signos de exclamación. '
            . 'Devuelve solo un JSON: {"asunto": "...", "cuerpo": "..."} con saltos de línea \n en el cuerpo.';
        $usuario = "Datos de la empresa:\n- " . implode("\n- ", $datos) . "\n\nEnfoque de este correo: {$enfoque}.";

        $txt = (new \App\Services\OpenAiService())->getChatResponse([
            ['role' => 'system', 'content' => $sistema],
            ['role' => 'user', 'content' => $usuario],
        ], ['throw' => true, 'temperature' => 0.8, 'response_format' => ['type' => 'json_object']]);

        $j = json_decode(trim((string) $txt), true);
        if (!is_array($j) || trim((string) ($j['asunto'] ?? '')) === '' || trim((string) ($j['cuerpo'] ?? '')) === '') {
            throw new \RuntimeException('Respuesta de la IA sin asunto o cuerpo');
        }

        return [trim((string) $j['asunto']), trim(str_replace("\r", '', (string) $j['cuerpo']))];
    }

    /** Sin IA: plantilla con el dato más fuerte de la empresa */
    private function correoPlantilla(array $h, $cfg, int $variante): array
    {
        $usted = $cfg->tratamiento === 'usted';
        $donde = $h['municipio'] !== '' ? " en {$h['municipio']}" : '';
        $sector = $h['sector'] !== '' ? mb_strtolower(preg_replace('/\s*\(.*\)$/', '', $h['sector'])) : '';

        if ($h['dias'] !== null && $h['dias'] <= 120) {
            $gancho = "He visto que {$h['empresa']} se ha constituido hace poco{$donde}. ¡Enhorabuena por el arranque!";
            $asunto = 'Una idea para arrancar ' . $h['empresa'];
        } elseif ($h['digital']) {
            $gancho = $usted ? 'He visto que han recibido una ayuda de digitalización.' : 'He visto que habéis recibido una ayuda de digitalización.';
            $asunto = 'Sobre la digitalización de ' . $h['empresa'];
        } elseif ($h['contratos'] !== null) {
            $gancho = $usted ? 'He visto que trabajan con la administración pública.' : 'He visto que trabajáis con la administración pública.';
            $asunto = 'Una idea para ' . $h['empresa'];
        } elseif ($h['cambio'] !== null && stripos($h['cambio'], 'Ampliaci') !== false) {
            $gancho = $usted ? 'He visto que han ampliado capital: buena señal de crecimiento.' : 'He visto que habéis ampliado capital: buena señal de crecimiento.';
            $asunto = 'Una idea para crecer en ' . $h['empresa'];
        } else {
            $gancho = 'Estaba revisando empresas' . ($sector !== '' ? " de {$sector}" : '') . ($h['provincia'] !== '' ? " en {$h['provincia']}" : '') . ' y me encontré con la ' . ($usted ? 'suya' : 'vuestra') . '.';
            $asunto = 'Una idea para ' . $h['empresa'];
        }

        if ($h['web'] !== '') {
            $idea = ($usted ? 'He echado un vistazo a su web' : 'He echado un vistazo a vuestra web') . " ({$h['web']}) y creo que podría ayudar"
                . ($usted ? 'les' : 'os') . ' con herramientas a medida: reservas o citas online, un área de clientes o automatizar tareas que hoy se hacen a mano.';
        } elseif ($h['dominio'] !== '') {
            $idea = ($usted ? "Si necesitan poner en marcha o mejorar la web de {$h['dominio']}" : "Si necesitáis poner en marcha o mejorar la web de {$h['dominio']}")
                . ', me dedico justo a eso: webs rápidas, fáciles de gestionar y pensadas para conseguir clientes.';
        } else {
            $idea = ($usted ? 'No he encontrado su web.' : 'No he encontrado vuestra web.') . ' Hago webs rápidas y fáciles de gestionar'
                . ($sector !== '' ? " para negocios de {$sector}" : '') . ': que ' . ($usted ? 'les' : 'os') . ' encuentren en Google, con servicios, contacto y, si sirve, reservas online.';
        }
        $cta = $usted ? '¿Le cuento en un par de líneas cómo lo plantearía para su caso?' : '¿Te cuento en un par de líneas cómo lo plantearía para vuestro caso?';
        $baja = $usted ? 'Si no le interesa, dígamelo y no volveré a escribirle.' : 'Si no te interesa, dímelo y no vuelvo a escribirte.';

        $partes = [$variante % 2 === 0 ? 'Hola,' : 'Buenos días,', $gancho . "\n\nSoy {$cfg->nombre}, desarrollador freelance. " . $idea, $cta,
            "Un saludo,\n{$cfg->nombre}\n{$cfg->web}", $baja];

        return [$asunto, implode("\n\n", $partes)];
    }

    // ================================================================== seguimiento

    /** @return array{filas: array, total: int} */
    public function seguimiento(array $f): array
    {
        $w = ['1 = 1'];
        $b = [];
        if ($f['followup_status'] !== '') {
            $w[] = 's.status = ?';
            $b[] = $f['followup_status'];
        }
        if ($f['q'] !== '') {
            $w[] = '(c.company_name LIKE ? OR c.cif LIKE ? OR s.note LIKE ?)';
            $q = '%' . $this->db->escapeLikeString($f['q']) . '%';
            array_push($b, $q, $q, $q);
        }
        $filas = $this->db->query("SELECT " . $this->selectEmpresa() . ", s.updated_at AS followup_updated, s.source_tab AS followup_source
                FROM prospect_followups s JOIN companies c ON c.id = s.company_id
                LEFT JOIN company_enrichment e ON e.company_id = c.id
                WHERE " . implode(' AND ', $w) . "
                ORDER BY FIELD(s.status, 'in_talks', 'contacted', 'pending', 'client', 'discarded'),
                         s.next_contact_date IS NULL, s.next_contact_date, s.updated_at DESC
                LIMIT 1000", $b)->getResultArray();

        return ['filas' => $this->puntuar($filas), 'total' => count($filas)];
    }

    /** Guarda el estado/nota de una empresa. Estado vacío = quitar del seguimiento. */
    public function guardarSeguimiento(int $companyId, string $estado, string $nota, ?string $proximo, string $origen): array
    {
        if (!$this->db->table('companies')->select('id')->where('id', $companyId)->get()->getRow()) {
            return ['ok' => false, 'error' => 'No existe esa empresa.'];
        }
        $tabla = $this->db->table('prospect_followups');
        if ($estado === '') {
            $tabla->where('company_id', $companyId)->delete();

            return ['ok' => true, 'quitada' => true];
        }
        if (!isset(self::SEG_ESTADOS[$estado])) {
            return ['ok' => false, 'error' => 'Estado no válido.'];
        }
        $proximo = ($proximo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $proximo)) ? $proximo : null;
        $ahora = date('Y-m-d H:i:s');
        $datos = ['status' => $estado, 'note' => mb_substr($nota, 0, 5000), 'next_contact_date' => $proximo, 'updated_at' => $ahora];

        if ($this->db->table('prospect_followups')->where('company_id', $companyId)->countAllResults() > 0) {
            $this->db->table('prospect_followups')->where('company_id', $companyId)->update($datos);
        } else {
            $this->db->table('prospect_followups')->insert($datos + [
                'company_id' => $companyId,
                'source_tab' => mb_substr($origen, 0, 20),
                'created_at' => $ahora,
            ]);
        }

        return ['ok' => true];
    }

    public function guardarLicitacion(int $id, string $estado, string $nota): array
    {
        if ($estado !== '' && !isset(self::LIC_ESTADOS[$estado])) {
            return ['ok' => false, 'error' => 'Estado no válido.'];
        }
        $this->db->table('prospect_tenders')->where('id', $id)->update([
            'followup_status'     => $estado !== '' ? $estado : null,
            'followup_note'       => $nota !== '' ? mb_substr($nota, 0, 5000) : null,
            'followup_updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['ok' => $this->db->affectedRows() >= 0];
    }
}
