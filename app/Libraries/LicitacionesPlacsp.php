<?php

namespace App\Libraries;

/**
 * Licitaciones abiertas de la Plataforma de Contratación del Sector Público (16-10-2026).
 *
 * Lee los feeds ATOM de sindicación (formato CODICE) y guarda en prospect_tenders
 * las licitaciones de informática (por CPV). Los feeds van del cambio más reciente al más
 * antiguo y cada página enlaza a la anterior con <link rel="next">; se para al llegar a
 * entradas más antiguas que la fecha pedida.
 *
 * Los nombres de las etiquetas se buscan por su nombre local (sin espacio de nombres) para
 * no depender de las URI de CODICE, que han cambiado entre versiones del esquema.
 *
 * Lo usa el comando `php spark prospeccion:licitaciones`.
 */
class LicitacionesPlacsp
{
    /** Feeds de sindicación. "placsp": perfiles alojados en la Plataforma; "agregadas": las
     *  plataformas autonómicas y locales que publican en ella (Madrid, Cataluña, Euskadi...). */
    public const FEEDS = [
        'placsp'    => 'https://contrataciondelsectorpublico.gob.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom',
        'agregadas' => 'https://contrataciondelsectorpublico.gob.es/sindicacion/sindicacion_1044/PlataformasAgregadasSinMenores.atom',
    ];

    /** CPV que interesan a un desarrollador: servicios TI (72), software (48),
     *  formación informática (80533) y marketing (79342). Se compara por el principio. */
    public const CPV_POR_DEFECTO = ['72', '48', '80533', '79342'];

    /** Solo los tres tipos seguros; el resto se enseña como "Tipo N" (las listas de códigos
     *  de la Plataforma han cambiado entre versiones y no coinciden para los demás). */
    public const TIPOS = ['1' => 'Suministros', '2' => 'Servicios', '3' => 'Obras'];

    public const PROCEDIMIENTOS = [
        '1' => 'Abierto', '2' => 'Restringido', '3' => 'Negociado sin publicidad', '4' => 'Negociado con publicidad',
        '5' => 'Diálogo competitivo', '6' => 'Normas internas', '7' => 'Derivado de acuerdo marco',
        '8' => 'Concurso de proyectos', '9' => 'Abierto simplificado', '10' => 'Asociación para la innovación',
        '11' => 'Derivado de asociación para la innovación', '12' => 'Sistema dinámico de adquisición',
        '13' => 'Licitación con negociación', '100' => 'Otros',
    ];

    /** Dominio del enlace del anuncio => plataforma donde se presenta la oferta */
    private const PLATAFORMAS = [
        'contrataciondelestado.es'            => 'Plataforma de Contratación del Estado',
        'contrataciondelsectorpublico.gob.es' => 'Plataforma de Contratación del Estado',
        'comunidad.madrid'                    => 'Portal de Contratación de la Comunidad de Madrid',
        'madrid.org'                          => 'Portal de Contratación de la Comunidad de Madrid',
        'gencat.cat'                          => 'Plataforma de Contractació Pública de Cataluña',
        'contractaciopublica.cat'             => 'Plataforma de Contractació Pública de Cataluña',
        'euskadi.eus'                         => 'Plataforma de Contratación de Euskadi',
        'juntadeandalucia.es'                 => 'Plataforma de Contratación de la Junta de Andalucía',
        'navarra.es'                          => 'Portal de Contratación de Navarra',
        'xunta.gal'                           => 'Plataforma de Contratos Públicos de Galicia',
        'contratosdegalicia.gal'              => 'Plataforma de Contratos Públicos de Galicia',
        'gva.es'                              => 'Plataforma de la Generalitat Valenciana',
        'gobiernodecanarias.org'              => 'Plataforma de Contratación de Canarias',
        'carm.es'                             => 'Plataforma de Contratación de la Región de Murcia',
        'aragon.es'                           => 'Plataforma de Contratación de Aragón',
        'jcyl.es'                             => 'Plataforma de Contratación de Castilla y León',
        'larioja.org'                         => 'Plataforma de Contratación de La Rioja',
        'asturias.es'                         => 'Plataforma de Contratación de Asturias',
        'cantabria.es'                        => 'Plataforma de Contratación de Cantabria',
        'caib.es'                             => 'Plataforma de Contratación de las Islas Baleares',
        'juntaex.es'                          => 'Plataforma de Contratación de Extremadura',
        'castillalamancha.es'                 => 'Plataforma de Contratación de Castilla-La Mancha',
    ];

    /**
     * Dónde se presenta la oferta de una licitación, según el enlace del anuncio (16-10-2026).
     * Las del feed "placsp" se presentan en la Plataforma del Estado; las "agregadas" vienen de
     * plataformas autonómicas o locales, que tienen su propio registro y su propia forma de presentar.
     *
     * @return array{nombre: string, estado: bool, dominio: string}
     */
    public static function plataforma(string $fuente, string $url): array
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        foreach (self::PLATAFORMAS as $dominio => $nombre) {
            if ($host === $dominio || str_ends_with($host, '.' . $dominio)) {
                return ['nombre' => $nombre, 'estado' => $nombre === 'Plataforma de Contratación del Estado', 'dominio' => $host];
            }
        }
        if ($fuente === 'placsp') {
            return ['nombre' => 'Plataforma de Contratación del Estado', 'estado' => true, 'dominio' => $host];
        }

        return ['nombre' => $host !== '' ? 'Otra plataforma (' . $host . ')' : 'Otra plataforma (mira el anuncio)', 'estado' => false, 'dominio' => $host];
    }

    public const ESTADOS = [
        'PRE' => 'Anuncio previo', 'PUB' => 'En plazo', 'EV' => 'Pendiente de adjudicación', 'ADJ' => 'Adjudicada',
        'RES' => 'Resuelta', 'ANUL' => 'Anulada', 'DELETED' => 'Retirada del feed',
    ];

    /** Primeras dos cifras del código postal => provincia (los 52 nombres de Provincias) */
    private const CP_PROVINCIA = [
        '01' => 'Álava', '02' => 'Albacete', '03' => 'Alicante', '04' => 'Almería', '05' => 'Ávila', '06' => 'Badajoz',
        '07' => 'Islas Baleares', '08' => 'Barcelona', '09' => 'Burgos', '10' => 'Cáceres', '11' => 'Cádiz',
        '12' => 'Castellón', '13' => 'Ciudad Real', '14' => 'Córdoba', '15' => 'A Coruña', '16' => 'Cuenca',
        '17' => 'Girona', '18' => 'Granada', '19' => 'Guadalajara', '20' => 'Guipúzcoa', '21' => 'Huelva',
        '22' => 'Huesca', '23' => 'Jaén', '24' => 'León', '25' => 'Lleida', '26' => 'La Rioja', '27' => 'Lugo',
        '28' => 'Madrid', '29' => 'Málaga', '30' => 'Murcia', '31' => 'Navarra', '32' => 'Ourense', '33' => 'Asturias',
        '34' => 'Palencia', '35' => 'Las Palmas', '36' => 'Pontevedra', '37' => 'Salamanca', '38' => 'Santa Cruz de Tenerife',
        '39' => 'Cantabria', '40' => 'Segovia', '41' => 'Sevilla', '42' => 'Soria', '43' => 'Tarragona', '44' => 'Teruel',
        '45' => 'Toledo', '46' => 'Valencia', '47' => 'Valladolid', '48' => 'Vizcaya', '49' => 'Zamora', '50' => 'Zaragoza',
        '51' => 'Ceuta', '52' => 'Melilla',
    ];

    /** NUTS-3 (y NUTS-2 de comunidades uniprovinciales) => provincia */
    private const NUTS_PROVINCIA = [
        'ES111' => 'A Coruña', 'ES112' => 'Lugo', 'ES113' => 'Ourense', 'ES114' => 'Pontevedra', 'ES120' => 'Asturias',
        'ES130' => 'Cantabria', 'ES211' => 'Álava', 'ES212' => 'Guipúzcoa', 'ES213' => 'Vizcaya', 'ES220' => 'Navarra',
        'ES230' => 'La Rioja', 'ES241' => 'Huesca', 'ES242' => 'Teruel', 'ES243' => 'Zaragoza', 'ES300' => 'Madrid',
        'ES411' => 'Ávila', 'ES412' => 'Burgos', 'ES413' => 'León', 'ES414' => 'Palencia', 'ES415' => 'Salamanca',
        'ES416' => 'Segovia', 'ES417' => 'Soria', 'ES418' => 'Valladolid', 'ES419' => 'Zamora', 'ES421' => 'Albacete',
        'ES422' => 'Ciudad Real', 'ES423' => 'Cuenca', 'ES424' => 'Guadalajara', 'ES425' => 'Toledo', 'ES431' => 'Badajoz',
        'ES432' => 'Cáceres', 'ES511' => 'Barcelona', 'ES512' => 'Girona', 'ES513' => 'Lleida', 'ES514' => 'Tarragona',
        'ES521' => 'Alicante', 'ES522' => 'Castellón', 'ES523' => 'Valencia', 'ES531' => 'Islas Baleares',
        'ES532' => 'Islas Baleares', 'ES533' => 'Islas Baleares', 'ES611' => 'Almería', 'ES612' => 'Cádiz',
        'ES613' => 'Córdoba', 'ES614' => 'Granada', 'ES615' => 'Huelva', 'ES616' => 'Jaén', 'ES617' => 'Málaga',
        'ES618' => 'Sevilla', 'ES620' => 'Murcia', 'ES630' => 'Ceuta', 'ES640' => 'Melilla', 'ES703' => 'Santa Cruz de Tenerife',
        'ES704' => 'Las Palmas', 'ES705' => 'Las Palmas', 'ES706' => 'Santa Cruz de Tenerife', 'ES707' => 'Santa Cruz de Tenerife',
        'ES708' => 'Las Palmas', 'ES709' => 'Santa Cruz de Tenerife',
        'ES12' => 'Asturias', 'ES13' => 'Cantabria', 'ES22' => 'Navarra', 'ES23' => 'La Rioja', 'ES30' => 'Madrid',
        'ES53' => 'Islas Baleares', 'ES62' => 'Murcia', 'ES63' => 'Ceuta', 'ES64' => 'Melilla',
    ];

    /** @var string[] */
    private array $cpv;

    public function __construct(?array $cpv = null)
    {
        $this->cpv = array_values(array_filter(array_map('trim', $cpv ?: self::CPV_POR_DEFECTO)));
    }

    // ------------------------------------------------------------------ lectura del feed

    /**
     * Analiza una página del feed.
     *
     * @return array{entradas: array<int, array>, borradas: array<int, array{feed_id: string, cuando: ?string}>, siguiente: ?string, mas_antigua: ?string, leidas: int}
     *         entradas: solo las que tienen algún CPV de los elegidos, ya convertidas a fila.
     */
    public function analizar(string $xml, string $fuente): array
    {
        $dom = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $ok = $dom->loadXML($xml, LIBXML_PARSEHUGE | LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (!$ok) {
            throw new \RuntimeException('El feed no es un XML válido.');
        }
        $xp = new \DOMXPath($dom);

        $siguiente = null;
        foreach ($xp->query("/*[local-name()='feed']/*[local-name()='link'][@rel='next']") as $l) {
            $siguiente = trim((string) $l->getAttribute('href')) ?: null;
        }

        $entradas = [];
        $masAntigua = null;
        $leidas = 0;
        foreach ($xp->query("/*[local-name()='feed']/*[local-name()='entry']") as $entry) {
            $leidas++;
            $fila = $this->entrada($xp, $entry, $fuente);
            if ($fila['feed_updated'] !== null && ($masAntigua === null || $fila['feed_updated'] < $masAntigua)) {
                $masAntigua = $fila['feed_updated'];
            }
            if ($this->interesa($fila['cpv_lista'])) {
                unset($fila['cpv_lista']);
                $entradas[] = $fila;
            }
        }

        $borradas = [];
        foreach ($xp->query("/*[local-name()='feed']/*[local-name()='deleted-entry']") as $d) {
            $ref = trim((string) $d->getAttribute('ref'));
            if ($ref !== '') {
                $borradas[] = ['feed_id' => $ref, 'cuando' => $this->fechaHora($d->getAttribute('when'))];
            }
        }

        return ['entradas' => $entradas, 'borradas' => $borradas, 'siguiente' => $siguiente, 'mas_antigua' => $masAntigua, 'leidas' => $leidas];
    }

    /** ¿Algún CPV empieza por uno de los elegidos? */
    public function interesa(array $cpvs): bool
    {
        foreach ($cpvs as $c) {
            foreach ($this->cpv as $p) {
                if (strncmp($c, $p, strlen($p)) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function entrada(\DOMXPath $xp, \DOMElement $e, string $fuente): array
    {
        $t = fn (string $q, ?\DOMNode $ctx = null) => $this->texto($xp, $q, $ctx ?? $e);

        $cfs = $xp->query("./*[local-name()='ContractFolderStatus']", $e)->item(0);
        $pp  = $cfs ? $xp->query("./*[local-name()='ProcurementProject']", $cfs)->item(0) : null;
        $party = $cfs ? $xp->query("./*[local-name()='LocatedContractingParty']/*[local-name()='Party']", $cfs)->item(0) : null;
        $tp  = $cfs ? $xp->query("./*[local-name()='TenderingProcess']", $cfs)->item(0) : null;

        // Todos los CPV: del proyecto y de cada lote
        $cpvs = [];
        if ($cfs) {
            foreach ($xp->query(".//*[local-name()='RequiredCommodityClassification']/*[local-name()='ItemClassificationCode']", $cfs) as $n) {
                $c = preg_replace('/\D/', '', (string) $n->textContent);
                if ($c !== '') {
                    $cpvs[$c] = true;
                }
            }
        }
        $cpvs = array_keys($cpvs);
        $cpvPrincipal = $pp ? preg_replace('/\D/', '', $t("./*[local-name()='RequiredCommodityClassification']/*[local-name()='ItemClassificationCode']", $pp)) : '';

        $nif = '';
        if ($party) {
            foreach ($xp->query("./*[local-name()='PartyIdentification']/*[local-name()='ID']", $party) as $n) {
                if (strtoupper((string) $n->getAttribute('schemeName')) === 'NIF') {
                    $nif = strtoupper(trim((string) $n->textContent));
                }
            }
        }
        $cp = $party ? $t("./*[local-name()='PostalAddress']/*[local-name()='PostalZone']", $party) : '';
        $nuts = $pp ? $t("./*[local-name()='RealizedLocation']/*[local-name()='CountrySubentityCode']", $pp) : '';

        // Fecha límite de ofertas
        $limite = null;
        if ($tp) {
            $d = $t("./*[local-name()='TenderSubmissionDeadlinePeriod']/*[local-name()='EndDate']", $tp);
            if ($d !== '') {
                $h = $t("./*[local-name()='TenderSubmissionDeadlinePeriod']/*[local-name()='EndTime']", $tp);
                $limite = $this->fechaHora(substr($d, 0, 10) . ' ' . ($h !== '' ? substr($h, 0, 8) : '23:59:59'));
            }
        }

        // Fecha de publicación: la primera IssueDate de los anuncios publicados
        $pub = null;
        if ($cfs) {
            foreach ($xp->query("./*[local-name()='ValidNoticeInfo']//*[local-name()='IssueDate']", $cfs) as $n) {
                $f = $this->fecha((string) $n->textContent);
                if ($f !== null && ($pub === null || $f < $pub)) {
                    $pub = $f;
                }
            }
        }

        $url = '';
        foreach ($xp->query("./*[local-name()='link']", $e) as $l) {
            $url = trim((string) $l->getAttribute('href'));
            if ($url !== '') {
                break;
            }
        }

        $updated = $this->fechaHora($t("./*[local-name()='updated']"));
        $titulo = $pp ? $t("./*[local-name()='Name']", $pp) : '';
        if ($titulo === '') {
            $titulo = $t("./*[local-name()='title']");
        }

        return [
            'feed_id'           => $t("./*[local-name()='id']"),
            'source'            => $fuente,
            'file_number'        => $cfs ? $this->corta($t("./*[local-name()='ContractFolderID']", $cfs), 255) : null,
            'title'            => $titulo !== '' ? $titulo : null,
            'contracting_body'            => $party ? $this->corta($t("./*[local-name()='PartyName']/*[local-name()='Name']", $party), 500) : null,
            'contracting_body_nif'        => $nif !== '' ? $this->corta($nif, 20) : null,
            'contracting_body_postal_code'         => $cp !== '' ? $this->corta($cp, 10) : null,
            'contracting_body_city'  => $party ? $this->corta($t("./*[local-name()='PostalAddress']/*[local-name()='CityName']", $party), 255) : null,
            'province'         => $this->provincia($cp, $nuts),
            'execution_place'   => $pp ? $this->corta($t("./*[local-name()='RealizedLocation']/*[local-name()='CountrySubentity']", $pp), 255) : null,
            'contract_type'     => $pp ? ($t("./*[local-name()='TypeCode']", $pp) ?: null) : null,
            'procedure_code'     => $tp ? ($t("./*[local-name()='ProcedureCode']", $tp) ?: null) : null,
            'amount_excl_vat'   => $pp ? $this->importe($t("./*[local-name()='BudgetAmount']/*[local-name()='TaxExclusiveAmount']", $pp)) : null,
            'amount_incl_vat'   => $pp ? $this->importe($t("./*[local-name()='BudgetAmount']/*[local-name()='TotalAmount']", $pp)) : null,
            'estimated_value'    => $pp ? $this->importe($t("./*[local-name()='BudgetAmount']/*[local-name()='EstimatedOverallContractAmount']", $pp)) : null,
            'lot_count'         => $cfs ? (($n = $xp->query("./*[local-name()='ProcurementProjectLot']", $cfs)->length) > 0 ? $n : null) : null,
            'cpv_codes'              => $cpvs ? $this->corta(implode(',', $cpvs), 500) : null,
            'main_cpv'     => $cpvPrincipal !== '' ? substr($cpvPrincipal, 0, 12) : ($cpvs[0] ?? null),
            'status_code'       => $cfs ? (strtoupper($t("./*[local-name()='ContractFolderStatusCode']", $cfs)) ?: null) : null,
            'published_date' => $pub ?? ($updated ? substr($updated, 0, 10) : null),
            'deadline'      => $limite,
            'url'               => $url !== '' ? $this->corta($url, 500) : null,
            'feed_updated'      => $updated,
            'cpv_lista'         => $cpvs,
        ];
    }

    private function texto(\DOMXPath $xp, string $q, \DOMNode $ctx): string
    {
        $n = $xp->query($q, $ctx)->item(0);

        return $n ? trim(preg_replace('/\s+/u', ' ', (string) $n->textContent)) : '';
    }

    private function corta(string $s, int $max): ?string
    {
        $s = trim($s);

        return $s === '' ? null : mb_substr($s, 0, $max);
    }

    private function importe(string $s): ?float
    {
        if ($s === '' || !is_numeric($s)) {
            return null;
        }
        $v = (float) $s;

        // Importes absurdos (céntimos como euros, errores de tecleo): fuera
        return ($v > 0 && $v < 1e10) ? round($v, 2) : null;
    }

    private function fecha(string $s): ?string
    {
        return preg_match('/^(\d{4}-\d{2}-\d{2})/', trim($s), $m) ? $m[1] : null;
    }

    /** "2026-10-15T09:12:44.123+02:00" o "2026-10-30 14:00:00" => "Y-m-d H:i:s" en hora de Madrid */
    private function fechaHora(?string $s): ?string
    {
        $s = trim((string) $s);
        if ($s === '') {
            return null;
        }
        try {
            $d = new \DateTimeImmutable($s, new \DateTimeZone('Europe/Madrid'));

            return $d->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function provincia(?string $cp, ?string $nuts): ?string
    {
        $cp = preg_replace('/\D/', '', (string) $cp);
        if (strlen($cp) === 5 && isset(self::CP_PROVINCIA[substr($cp, 0, 2)])) {
            return self::CP_PROVINCIA[substr($cp, 0, 2)];
        }
        $nuts = strtoupper(trim((string) $nuts));
        foreach ([$nuts, substr($nuts, 0, 5), substr($nuts, 0, 4)] as $k) {
            if ($k !== '' && isset(self::NUTS_PROVINCIA[$k])) {
                return self::NUTS_PROVINCIA[$k];
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ descarga

    public function descargar(string $url): string
    {
        if (!preg_match('#^https://[a-z0-9.-]*contrataciondel(sectorpublico|estado)\.(gob\.)?es/#i', $url)) {
            throw new \RuntimeException('URL fuera de la Plataforma de Contratación: ' . $url);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_ENCODING       => '',
            CURLOPT_USERAGENT      => 'APIEmpresas-prospeccion/1.0 (+https://apiempresas.es)',
        ]);
        $cuerpo = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($cuerpo === false || $codigo !== 200) {
            throw new \RuntimeException("No se ha podido descargar {$url} (HTTP {$codigo}) {$error}");
        }

        return (string) $cuerpo;
    }

    // ------------------------------------------------------------------ guardado

    /**
     * Inserta o actualiza. Solo pisa una fila guardada si la entrada es más reciente
     * (feed_updated), y nunca toca el seguimiento (seg_*).
     *
     * @return array{nuevas: int, actualizadas: int, iguales: int}
     */
    public function guardar(array $filas, bool $prueba = false): array
    {
        $r = ['nuevas' => 0, 'actualizadas' => 0, 'iguales' => 0];
        if (!$filas) {
            return $r;
        }
        $db = \Config\Database::connect();
        $ahora = date('Y-m-d H:i:s');

        // La misma licitación puede venir varias veces en una pasada: se queda la más reciente
        $porId = [];
        foreach ($filas as $f) {
            if (($f['feed_id'] ?? '') === '') {
                continue;
            }
            $id = $f['feed_id'];
            if (!isset($porId[$id]) || (string) $f['feed_updated'] > (string) $porId[$id]['feed_updated']) {
                $porId[$id] = $f;
            }
        }

        $existentes = [];
        foreach (array_chunk(array_keys($porId), 500) as $trozo) {
            foreach ($db->table('prospect_tenders')->select('id, feed_id, feed_updated')->whereIn('feed_id', $trozo)->get()->getResultArray() as $x) {
                $existentes[$x['feed_id']] = $x;
            }
        }

        foreach ($porId as $id => $f) {
            if (isset($existentes[$id])) {
                if ((string) $existentes[$id]['feed_updated'] >= (string) $f['feed_updated']) {
                    $r['iguales']++;
                    continue;
                }
                $r['actualizadas']++;
                if (!$prueba) {
                    $f['updated_at'] = $ahora;
                    $db->table('prospect_tenders')->where('id', (int) $existentes[$id]['id'])->update($f);
                }
            } else {
                $r['nuevas']++;
                if (!$prueba) {
                    $f['created_at'] = $ahora;
                    $f['updated_at'] = $ahora;
                    $db->table('prospect_tenders')->insert($f);
                }
            }
        }

        return $r;
    }

    /** Las entradas borradas del feed se marcan (no se borran: puede haber seguimiento) */
    public function marcarBorradas(array $borradas, bool $prueba = false): int
    {
        if (!$borradas) {
            return 0;
        }
        $db = \Config\Database::connect();
        $n = 0;
        foreach (array_chunk(array_column($borradas, 'feed_id'), 500) as $trozo) {
            $b = $db->table('prospect_tenders')->whereIn('feed_id', $trozo)->where('status_code !=', 'DELETED');
            if ($prueba) {
                $n += $b->countAllResults();
            } else {
                $b->update(['status_code' => 'DELETED', 'updated_at' => date('Y-m-d H:i:s')]);
                $n += $db->affectedRows();
            }
        }

        return $n;
    }
}
