<?php

/**
 * Textos SEO de la ficha hechos con código, sin IA (15-10-2026).
 *
 * Por qué: a la IA ya se le daban los datos masticados (seo_ai_datos) y se le pedía rellenar una
 * plantilla; después hacían falta ~150 líneas de validación para pillar años, CIF y publicidad
 * inventados. Con código no se inventa nada, no hay rechazos y va en milisegundos por ficha
 * (la cola de IA iba a ~7 s por ficha, ~11.000 al día).
 *
 * Misma salida que seo_ai_generar(): seo_text, faqs, seo_tags, seo_pitch, borme_summary, así que
 * se guarda con seo_ai_guardar() y la ficha no cambia.
 *
 * Contra el texto repetido entre fichas:
 *  - cada frase tiene 3 variantes y se elige por crc32(CIF + frase): la misma ficha saca siempre
 *    la misma, y fichas distintas, distintas combinaciones;
 *  - las frases dependen de los datos que haya (un anuncio o muchos, con año o sin él…);
 *  - el párrafo 2 sale del objeto social de la empresa, que es propio de cada una.
 *
 * Lo único que la IA hacía mejor es resumir el objeto social en lenguaje natural. Aquí se limpia
 * (fuera la fórmula de entrada y las coletillas legales del final), se parte en actividades, se
 * pasa a minúscula con tildes (diccionario seo_tildes_datos.php, sacado de frecuencias del español)
 * y se enseñan las 2-4 primeras.
 *
 *   seo_plantilla_generar($d)   $d = seo_ai_datos($company, $posts)
 *   seo_plantilla_guardar($id, $r)
 */

if (!function_exists('seo_pl_plano')) {
    /** minúsculas sin tildes ni eñes, para buscar palabras clave */
    function seo_pl_plano(string $s): string
    {
        return strtr(mb_strtolower($s, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ò' => 'o', 'ç' => 'c']);
    }
}

if (!function_exists('seo_pl_variante')) {
    /** Variante estable por ficha y por frase: 0..n-1 */
    function seo_pl_variante(array $d, string $sal, int $n): int
    {
        if ($n <= 1) {
            return 0;
        }
        $clave = ($d['cif'] ?? '') !== '' ? $d['cif'] : ($d['nombre'] ?? '');
        return (int) (crc32($clave . '|' . $sal) % $n);
    }
}

if (!function_exists('seo_pl_limpio')) {
    /** Sin < ni > (el texto se pinta con <strong>) y sin espacios de más */
    function seo_pl_limpio(string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', str_replace(['<', '>', "''"], ['', '', "'"], $s)));
    }
}

if (!function_exists('seo_pl_mapa_tildes')) {
    /** "construccion" => "construcción" (36.500 palabras, ver seo_tildes_datos.php) */
    function seo_pl_mapa_tildes(): array
    {
        static $mapa = null;
        return $mapa ??= require __DIR__ . '/seo_tildes_datos.php';
    }
}

if (!function_exists('seo_pl_reparar')) {
    /** Carácter roto en la base ("ENSE\u{FFFD}ANZA" -> "ENSEÑANZA"): ñ o vocal con tilde, la que exista en el diccionario. */
    function seo_pl_reparar(string $t): string
    {
        if (!str_contains($t, "\u{FFFD}")) {
            return $t;
        }
        static $formas = null;
        $formas ??= array_flip(array_values(seo_pl_mapa_tildes()));
        return preg_replace_callback('/[\p{L}\x{FFFD}]*\x{FFFD}[\p{L}\x{FFFD}]*/u', static function ($m) use ($formas) {
            foreach (['ñ', 'á', 'é', 'í', 'ó', 'ú', 'ü'] as $c) {
                $prueba = str_replace("\u{FFFD}", $c, mb_strtolower($m[0], 'UTF-8'));
                if (isset($formas[$prueba])) {
                    return preg_match('/\p{Lu}/u', $m[0]) && !preg_match('/\p{Ll}/u', $m[0]) ? mb_strtoupper($prueba, 'UTF-8') : $prueba;
                }
            }
            return str_replace("\u{FFFD}", '', $m[0]);
        }, $t);
    }
}

if (!function_exists('seo_pl_lugar')) {
    /** "Alcala de Henares" -> "Alcalá de Henares", "Malaga" -> "Málaga" */
    function seo_pl_lugar(string $s): string
    {
        $mapa = seo_pl_mapa_tildes();
        // "Astillero, el" -> "El Astillero"; "Palmas de Gran Canaria, Las" -> "Las Palmas de…"
        // "Astillero, el" / "Hospitalet de Llobregat (L')" / "Castell de Guadalest el" -> artículo delante
        if (preg_match('/^(.+?)(?:\s*,\s*|\s*\(\s*)(el|la|los|las|l\'|es|sa|s\'|o|a|os|as)\s*\)?$/iu', trim($s), $m)
            || preg_match('/^(.+?)\s+(el|la|los|las)$/u', trim($s), $m)) {
            $art = mb_strtoupper(mb_substr($m[2], 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($m[2], 1, null, 'UTF-8'), 'UTF-8');
            $resto = $m[1];
            if (str_ends_with($art, "'")) {
                $resto = mb_strtoupper(mb_substr($resto, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($resto, 1, null, 'UTF-8');
            }
            $s = $art . (str_ends_with($art, "'") ? '' : ' ') . $resto;
        }
        $pal = explode(' ', $s);
        foreach ($pal as $i => $w) {
            if ($i > 0 && in_array(mb_strtolower($w, 'UTF-8'), ['de', 'del', 'la', 'las', 'el', 'los', 'y', 'i', 'e', 'en'], true)) {
                $pal[$i] = mb_strtolower($w, 'UTF-8');
            }
        }
        $s = implode(' ', $pal);
        return preg_replace_callback('/\p{L}+/u', static function ($m) use ($mapa) {
            $min = mb_strtolower($m[0], 'UTF-8');
            if (!isset($mapa[$min]) || mb_strlen($min, 'UTF-8') < 4) {
                return $m[0];
            }
            $con = $mapa[$min];
            return $m[0] === $min ? $con : mb_strtoupper(mb_substr($con, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($con, 1, null, 'UTF-8');
        }, $s);
    }
}

if (!function_exists('seo_pl_minusculas')) {
    /**
     * Texto del registro (MAYÚSCULAS sin tildes) a minúscula con tildes. Si ya viene en caja
     * mixta se deja como está.
     */
    function seo_pl_minusculas(string $s): string
    {
        $letras = preg_replace('/[^\p{L}]/u', '', $s);
        if ($letras === '') {
            return $s;
        }
        $mapa = seo_pl_mapa_tildes();
        $mayus = preg_replace('/[^\p{Lu}]/u', '', $letras);
        if (mb_strlen($mayus, 'UTF-8') / mb_strlen($letras, 'UTF-8') < 0.7) {
            // Ya viene en caja mixta, pero a menudo sin tildes ("mercancias", "asi"): se ponen
            // solo en las palabras en minúscula (las capitalizadas pueden ser nombres propios).
            return preg_replace_callback('/\b\p{Ll}+\b/u', static fn ($m) => $mapa[$m[0]] ?? $m[0], $s);
        }
        $t = mb_strtolower($s, 'UTF-8');
        $t = preg_replace_callback('/\p{L}+/u', static fn ($m) => $mapa[$m[0]] ?? $m[0], $t);
        if (function_exists('company_restore_accents')) {
            $t = company_restore_accents($t, true);
        }
        $t = preg_replace('/\bespaña\b/u', 'España', $t);
        $t = preg_replace_callback(
            '/\b(?:iva|irpf|cnae|borme|nif|cif|ue|itv|i\+d\+i|i\+d|tic|pymes?|ong|iae|pvc|led|gps|ett|sicav|ute|vtc|s\.l\.|s\.a\.)(?![\p{L}])/iu',
            static fn ($m) => mb_strtoupper($m[0], 'UTF-8'),
            $t
        );
        return $t;
    }
}

if (!function_exists('seo_pl_cnae')) {
    /** "Otra intermediación monetaria" -> "intermediación monetaria" */
    function seo_pl_cnae(string $cnae): string
    {
        $c = trim(preg_replace('/^\s*\d{2,4}\s*[-–.:]?\s*/u', '', $cnae));
        $c = preg_replace('/\s*\bn\.\s?c\.\s?o\.\s?p\.?/iu', '', $c);
        $c = seo_pl_minusculas($c);
        $c = preg_replace('/^otr[oa]s?\s+/iu', '', $c);
        $c = rtrim($c, " .;,");
        // primera letra en minúscula (va dentro de una frase), salvo siglas
        if (!preg_match('/^\p{Lu}{2,}/u', $c)) {
            $c = mb_strtolower(mb_substr($c, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($c, 1, null, 'UTF-8');
        }
        return seo_pl_limpio($c);
    }
}

if (!function_exists('seo_pl_objeto_actividades')) {
    /**
     * Objeto social -> lista de actividades legibles. Devuelve ['items' => [...], 'mas' => bool]
     * ('mas' = había más actividades de las que se enseñan).
     *
     * 15-10-2026, tras probar con 40 fichas reales: muchas constituciones de 2026 traen el objeto
     * social como lista de CNAE ("Actividad principal: 5611 / Restaurantes. Otras actividades:
     * 5630 / Servicios de bebidas", "(CNAE F4331) Revocamiento"): se quitan los rótulos y los códigos
     * y queda la actividad. seo_ai_datos corta el objeto a 900 caracteres a mitad de palabra
     * ("servicios de caterin", "en dere"): la última actividad cortada se descarta.
     */
    function seo_pl_objeto_actividades(string $obj, int $max = 4, int $maxTotal = 460): array
    {
        $t = trim($obj);
        $truncado = false;
        if (mb_substr($t, -1, 1, 'UTF-8') === '…') {   // seo_ai_datos lo corta a 900
            $t = mb_substr($t, 0, -1, 'UTF-8');
            $truncado = true;
        }
        // 15-10-2026: muchos objetos sociales están guardados cortados a 300 caracteres, sin
        // marca ("…Otras actividades: Comerc", "…y asist"). Si mide 280-300 y no acaba en punto o
        // paréntesis, la última actividad se trata como cortada.
        $largo = mb_strlen($t, 'UTF-8');
        if (!$truncado && (($largo >= 295 && $largo <= 302) || ($largo >= 280 && $largo < 295 && !preg_match('/[.)]\s*$/u', $t)))) {
            $truncado = true;
        }
        $cortado = $truncado;
        $t = str_replace(["\r", "\t"], ["\n", ' '], $t);

        $t = preg_replace('/\s*\(\s*actividad(?:es)?\s+(?:principal(?:es)?|secundari[oa]s?)[^)]{0,80}\)/iu', ' ', $t);   // "(ACTIVIDAD PRINCIPAL CNAE 9.313)"
        // Códigos y rótulos de CNAE: "(CNAE F4331)", "CNAE: 4321", "EPIGRAFE IAE 501",
        // "de la Clasificación Nacional de Actividades Económicas 2025", "n.c.o.p."
        $t = preg_replace('/\(?\s*\b(?:C\.?N\.?A\.?E\.?|I\.?A\.?E\.?|EP[IÍ]GRAFE)\b(?:\s*(?:2009|2025))?[\s:.\-nºNº°]*[A-U]?\s*[\d\.\s,y]{2,}\)?/iu', ' ', $t);
        $t = preg_replace('/\(?\s*\b(?:de\s+)?la\s+clasificaci[oó]n\s+nacional\s+de\s+actividades\s+econ[oó]micas\b(?:\s*\(?\s*C\.?N\.?A\.?E\.?\s*\)?)?(?:\s*(?:2009|2025))?\s*\)?/iu', ' ', $t);
        $t = preg_replace('/\(\s*C\.?\s?N\.?\s?A\.?\s?E\.?[^)]{0,120}\)/iu', ' ', $t);
        $t = preg_replace('/\s*,?\s*siendo\s+su\s+c[oó]digo\s+(?:C\.?\s?N\.?\s?A\.?\s?E\.?\s*)?(?:el\s+)?[\d.]+\s*/iu', '; ', $t);
        $t = preg_replace('/\s+(?=(?:y\s+como\s+)?(?:otras\s+actividades|actividad\s+principal)\s*:)/iu', "\n", $t);
        $t = preg_replace('/\s*\bn\.\s?c\.\s?o\.\s?p\.?\.?(?=\s+[\p{Lu}\d])/u', ';', $t);   // fin de un rótulo de CNAE
        $t = preg_replace('/[,;.]?\s+(?=\(?[A-U]?\d{2}\.?\d{1,2}\)?\s*[-–\/]\s*\p{L})/u', "\n", $t);   // "…, 46.62 - Comercio…"
        $t = preg_replace('/\.\s+(?=\d{4}\s+\p{Lu}{3})/u', "\n", $t);                                  // ". 8121 LIMPIEZA…"
        $t = preg_replace('/\.-\s+/u', "\n", $t);
        $t = preg_replace('/\.{2,}\s*/u', "\n", $t);                                                   // "restaurantes..La intermediación"
        $t = preg_replace('/(?<=\p{L}{3})\.(?=\p{Lu}\p{Ll}{2})/u', "\n", $t);                                    // "PUBLICIDAD.Otras actividades"
        $t = preg_replace('/\s*(?:\by\s+)?\(\s*[ivxIVX]{1,4}\s*\)\s*/u', "\n", $t);                          // "(I) … (II) … Y (III)"
        $t = preg_replace('/(?:^|\s)\d{1,2}\.[a-z]\)?\s+/u', "\n", $t);                                     // "1.a) La venta…"
        $t = preg_replace('/\s+(?=\d{2}\.\d{1,2}\s+\p{L})/u', "\n", $t);                                   // "… COMUNICACION 74.14 OTRAS…"
        $t = preg_replace('/\s+,/u', ',', $t);
        $t = preg_replace_callback('/\b(de|del|a|al|en|para|con|por)\.\s+(\p{Lu})(?=\p{Ll})/u', static fn ($m) => $m[1] . ' ' . mb_strtolower($m[2], 'UTF-8'), $t);           // "distribución de. Todo aquel…"                                                       // "diseño , creación"
        $t = seo_pl_reparar($t);
        $t = preg_replace('/\s*\bn\.\s?c\.\s?o\.\s?p\.?/iu', '', $t);

        // Fórmula de entrada: "La sociedad tiene por objeto…", "Constituye el objeto social…",
        // "El objeto social estará constituido por…"
        $intro = '/^\s*(?:(?:la|esta|dicha)\s+)?(?:sociedad\s+)?(?:(?:tiene|tendr[aá]n?|constituye|constituir[aá]n?|constituyen|es|ser[aá]|son)\b\s*(?:por|como)?\s*)?(?:el\s+|su\s+)?objeto(?:\s+social)?(?:\s+(?:de\s+la\s+(?:presente\s+)?sociedad|exclusivo|principal))?(?:\s+(?:lo\s+)?(?:(?:estar[aá]|queda|est[aá])\s+(?:constituido|integrado|formado)\s+por|es|ser[aá]n?|son|constituye|constituir[aá]n?|constituyen|consiste|consistir[aá]|comprende|comprender[aá]|abarca|incluye)\b(?:\s+en)?)?(?:\s+(?:la\s+realizaci[oó]n\s+de\s+)?(?:las\s+)?siguientes\s+actividades|\s+lo\s+siguiente)?\s*[:.\-–]*\s*/iu';
        $t = preg_replace('/^[\s.\-–:\'"«]*(?:art[ií]culo\s+\d+\W*)?(?:objeto(?:\s+social)?\s*[.:\-–]+\s*)?/iu', '', $t, 1);
        $t = preg_replace($intro, '', $t, 1);
        $t = preg_replace('/^\s*actividades\s*:\s*/iu', '', $t, 1);
        $t = preg_replace('/^.{0,200}?\b(?:de\s+)?las\s+siguientes\s+actividades\s*:\s*/iu', '', $t, 1);   // "La realización, en el marco de la Ley…, de las siguientes actividades:"
        $t = preg_replace('/^\s*(?:el\s+)?(?:desarrollo\s+de\s+)?(?:las\s+actividades\s+correspondientes\s+a\s+)?(?:los\s+)?siguientes\s+c[oó]digos[^:]{0,120}:\s*/iu', '', $t, 1);
        $t = preg_replace('/^\s*(?:esta|la)\s+(?:empresa|sociedad|entidad)\s+(?:tiene|tendr[aá])\s+(?:como|por)\s+(?:prop[oó]sito|finalidad|actividad)\s*:?\s*/iu', '', $t, 1);
        $t = preg_replace('/^\s*(?:la\/?s?\s+)?siguientes?\/?s?\s+actividad\/?(?:es)?\s*[:.]?\s*/iu', '', $t, 1);

        // Coletillas legales: desde aquí, todo fuera (no son más actividades)
        $colas = '/(?:\b(?:las\s+actividades\s+(?:integrantes|enumeradas|anteriores|antes\s+(?:citadas|mencionadas|indicadas|enumeradas|descritas)|descritas|que\s+integran|del\s+objeto|comprendidas)|tales\s+actividades|dichas\s+actividades|todas?\s+(?:estas|ellas|las\s+actividades\s+(?:anteriores|descritas|enumeradas))|quedan?\s+excluid|se\s+excluyen?|excluy[eé]ndose|si\s+(?:las\s+)?(?:disposiciones|leyes|la\s+ley)|podr[aá]n?\s+(?:ser\s+)?(?:desarrollad|ejercid|realizad)|podr[aá]n\s+realizarse|pudiendo\s+(?:al\s+efecto\s+)?(?:desarrollar|realizar|ejercer|constituir|participar)|de\s+modo\s+(?:directo|indirecto)|de\s+forma\s+(?:directa|indirecta)|cuando\s+(?:el|la)\s+(?:ejercicio|legislaci)|para\s+el\s+ejercicio\s+de\s+(?:las\s+)?actividades\s+(?:profesionales|reguladas)|la\s+sociedad\s+(?:no\s+)?podr[aá]|en\s+general,?\s+(?:cualquier|toda)|y\s+en\s+general\b|(?:y\s+)?cualquier\s+(?:otra\s+)?actividad\s+que\s+pueda|si\s+alguna\s+de\s+las\s+actividades|a\s+efectos\s+de\s+lo\s+dispuesto|tod[oa]s?\s+ell[oa]s\s+correspondientes|la\s+sociedad\s+la\s+ejercer|se\s+ejercer[aá]n?\b|ejercer[aá]n?\s+(?:[uú]nicamente|exclusivamente)))/iu';
        if (preg_match($colas, $t, $m, PREG_OFFSET_CAPTURE) && $m[0][1] >= 15) {
            $t = substr($t, 0, $m[0][1]);
            $truncado = false;   // lo cortado era la coletilla, no una actividad a medias
        }

        // Partir en actividades: ";", saltos de línea, enumeraciones "1.-", "1.- :", "a)", "b.-",
        // "a.La", "- ", y punto y seguido (no el de "S.L." ni "n.c.o.p.": solo tras 3 letras).
        $t = preg_replace('/(?:^|\s)(?:\d{1,2}\s*(?:º|°)?\s*[.)]\s*-?\s*:?\s+|[a-zA-Z]\s*\)\s*:?\s*|[a-zA-Z]\.\s*-\s*:?\s*|[IVXivx]{1,4}\s*[.)]\s*-\s*:?\s+)(?=\p{L})/u', "\n", $t);
        $t = preg_replace('/(?:^|\s)[a-zA-Z]\.(?=\p{Lu}\p{Ll})/u', "\n", $t);
        $t = preg_replace('/[\s,]+\.(?=\s|$)/u', '.', $t);   // "inmobiliaria), . Y como…"
        $partes = preg_split('/\s*;\s*|\n+|(?:(?<=\p{L}{3})|(?<=\)))\.\s+|\s+-\s+/u', $t);
        $partes = array_values(array_filter(array_map('trim', $partes), static fn ($p) => $p !== ''));

        // Rótulos sin actividad: "Actividad principal", "otras actividades", "relativo a la actividad"
        $rotulo = '/^(?:y\s+)?(?:como\s+)?(?:(?:c[oó]digo\s+)?(?:de\s+)?(?:la\s+)?actividad(?:es)?\s+(?:principal(?:es)?|secundarias?|complementarias?)|otras\s+actividades(?:\s+secundarias)?(?=\s*[:;.,]|\s*$)|c[oó]digos?\s+(?:de\s+)?cnae(?:\s+de\s+la\s+actividad\s+principal)?|principal(?:es)?|secundari[oa]s?|relativ[oa]\s+a\s+la\s+actividad|la\s+citada\s+en\s+primer\s+lugar|(?:estar[aá]|queda)\s+(?:constituido|integrado)(?:\s+por)?)\b[\s,:;.\-–]*/iu';

        $items = [];
        $nPartes = count($partes);
        foreach ($partes as $k => $p) {
            if ($truncado && $k === $nPartes - 1 && $nPartes > 1) {
                break;   // la última está cortada a media palabra
            }
            for ($i = 0; $i < 3; $i++) {   // "Actividad principal, la citada en primer lugar:"
                $p = preg_replace($rotulo, '', trim($p, " \t\n.,:;-–·•*"));
            }
            for ($i = 0; $i < 2; $i++) {
                $p = preg_replace('/^(?:(?:y|e|o|u|as[ií]\s+como|tambi[eé]n|asimismo|igualmente|adem[aá]s|se\s+dedicar[aá]\s+a(?:\s+(?:la|el|los|las))?)[\s,]+)+/iu', '', $p);
                // Códigos de CNAE delante de la actividad: "5611 / ", "F4331 ", "6811:", "(4614)"
                $p = preg_replace('/^\(?[A-U]?\s?\d{2,4}(?:\.\d{1,2})?\)?\s*(?:[\/:;.\-–]\s*)?/u', '', $p);
                $p = trim($p, " \t\n.,:;-–·•*/'\"«»");
            }
            $p = preg_replace('/^(?:(?:la|el|los|las|lo)\s+)?de\s+(?=\p{L}{4})/iu', '', $p);   // "LA DE TALLER DE…"
            // "(Promoción inmobiliaria)" entero entre paréntesis
            if (preg_match('/^\((.+)\)$/u', $p, $mp)) {
                $p = $mp[1];
            }
            // Rótulo de sección delante: "Agricultura, ganadería, silvicultura y pesca: Cultivo de…"
            if (preg_match('/^[^:]{3,80}:\s*(.{10,})$/u', $p, $mp)) {
                $p = $mp[1];
                $p = preg_replace('/^\(?[A-U]?\s?\d{2,4}(?:\.\d{1,2})?\)?\s*(?:[\/:;.\-–]\s*)?/u', '', $p);
            }
            $p = preg_replace('/^(?:la|el|los|las|lo)\s+/iu', '', $p);
            // Cola colgante tras cortar: ", pudiendo realizarse", " y", " así como", ",etc"
            $p = preg_replace('/(?:[\s,;:]+(?:y|o|e|u|as[ií]\s+como|pudiendo\s+\p{L}+|que|las?|los?|el|de|del|en|por|para|con|bien|todo|toda|etc))+\s*$/iu', '', $p);
            $p = preg_replace('/,(?=\p{L})/u', ', ', $p);
            if (substr_count($p, ')') > substr_count($p, '(')) {
                $p = str_replace(')', '', $p);
            }
            $p = trim($p, " .,:;-–");
            if (mb_strlen($p, 'UTF-8') < 5 || preg_match('/^objeto\b|\bobjeto\s+social\b|por\s+objeto\b|estatutos|\bc\.?n\.?a\.?e\b/iu', $p) || !preg_match('/\p{L}{4}/u', $p)
                || preg_match('/^se\s+incluyen?\b/iu', $p)
                || preg_match('/^(?:otr[oa]s?|etc|varios|dem[aá]s|similares|afines)(?:\s+(?:actividades|servicios|similares|afines))?$/iu', $p)) {
                continue;
            }
            $items[] = $p;
        }
        // "compraventa" suelta por un punto mal puesto: se une a la siguiente.
        // "prestación de servicios sanitarios" seguida de "prestación de servicios sanitarios y…": fuera el título.
        $unidos = [];
        $items = array_values($items);
        for ($i = 0, $n = count($items); $i < $n; $i++) {   // por índice: se modifica $items[$i + 1]
            $it = $items[$i];
            if (preg_match('/\s(?:de|del|a|al|en|para|con|por|y|e|o)$/u', $it) && isset($items[$i + 1])) {   // minúscula: "Sección A" no
                $items[$i + 1] = $it . ' ' . mb_strtolower(mb_substr($items[$i + 1], 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($items[$i + 1], 1, null, 'UTF-8');
                continue;
            }
            if (preg_match('/^(?:siguientes?|general|varios|diversos|objeto|actividades?)$/iu', trim($it))) {
                continue;
            }
            // "Explotación. Intensiva y extensiva de…": la siguiente empieza por un adjetivo -> una sola
            if (!str_contains(trim($it), ' ') && isset($items[$i + 1]) && preg_match('/^\p{L}+(?:iv[oa]s?|al(?:es)?|ic[oa]s?)\b/iu', $items[$i + 1])) {
                $items[$i + 1] = $it . ' ' . mb_strtolower($items[$i + 1], 'UTF-8');
                continue;
            }
            if (!str_contains(trim($it), ' ') && $i > 0 && !isset($items[$i + 1])) {
                continue;
            }
            if (isset($items[$i + 1]) && mb_stripos($items[$i + 1], $it, 0, 'UTF-8') === 0) {
                continue;
            }
            $unidos[] = $it;
        }
        $items = $unidos;
        if (!$items && $truncado && $partes) {   // una sola actividad y cortada: hasta la última coma
            $p = $partes[0];
            $pos = mb_strrpos($p, ',', 0, 'UTF-8');
            if ($pos !== false && $pos > 40) {
                $items[] = trim(mb_substr($p, 0, $pos, 'UTF-8')) . '…';
            }
        }

        $sel = [];
        $total = 0;
        foreach ($items as $it) {
            if (mb_strlen($it, 'UTF-8') > 220) {
                // Actividad larguísima: se corta en una coma o palabra, con puntos suspensivos.
                $c = mb_substr($it, 0, 200, 'UTF-8');
                $pos = max((int) mb_strrpos($c, ',', 0, 'UTF-8'), 0);
                $c = $pos > 90 ? mb_substr($c, 0, $pos, 'UTF-8') : mb_substr($c, 0, (int) mb_strrpos($c, ' ', 0, 'UTF-8'), 'UTF-8');
                $c = preg_replace('/(?:[\s,]+(?:y|o|e|de|del|la|el|los|las|en|para|por|con|a|al))+\s*$/iu', '', $c);
                $it = rtrim($c, " ,;") . '…';
                $cortado = true;
            }
            $len = mb_strlen($it, 'UTF-8');
            if ($sel && ($total + $len > $maxTotal || count($sel) >= $max)) {
                $cortado = true;
                break;
            }
            $sel[] = $it;
            $total += $len;
        }

        $out = [];
        foreach ($sel as $it) {
            // Rótulos de CNAE en Título ("Promoción Inmobiliaria", "Agentes de la Propiedad"):
            // a minúscula entera si la mayoría de las palabras largas empiezan en mayúscula.
            $largas = preg_split('/\s+/u', preg_replace('/[^\p{L}\s]/u', ' ', $it), -1, PREG_SPLIT_NO_EMPTY);
            $largas = array_filter($largas, static fn ($w) => mb_strlen($w, 'UTF-8') > 3);
            $capit = array_filter($largas, static fn ($w) => preg_match('/^\p{Lu}\p{Ll}/u', $w));
            if (count($largas) >= 2 && count($capit) / count($largas) >= 0.6) {
                $it = mb_strtolower($it, 'UTF-8');
            }
            // Primera letra a minúscula antes de poner tildes: en caja mixta solo se tocan las
            // palabras en minúscula, y "Consultoria" al principio se quedaba sin tilde.
            if (!preg_match('/^\p{Lu}{2,}/u', $it)) {
                $it = mb_strtolower(mb_substr($it, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($it, 1, null, 'UTF-8');
            }
            $it = seo_pl_minusculas($it);
            $it = preg_replace(['/\bespaña\b/u', '/iónes\b/u'], ['España', 'iones'], $it);
            $it = seo_pl_limpio($it);
            if ($it !== '' && !in_array($it, $out, true)) {
                $out[] = $it;
            }
        }
        return ['items' => $out, 'mas' => $cortado || count($items) > count($sel)];
    }
}

if (!function_exists('seo_pl_lista')) {
    /** ["a", "b", "c"] -> "a, b y c"; con comas dentro, "a; b, y c" */
    function seo_pl_lista(array $xs, bool $strong = false, int $maxStrong = 2): array
    {
        $con = [];
        foreach (array_values($xs) as $i => $x) {
            $con[] = ($strong && $i < $maxStrong && mb_strlen($x, 'UTF-8') <= 90) ? "<strong>{$x}</strong>" : $x;
        }
        $n = count($con);
        if ($n === 0) {
            return ['', ''];
        }
        if ($n === 1) {
            return [$con[0], $xs[0]];
        }
        $hayComas = (bool) array_filter($xs, static fn ($x) => str_contains($x, ','));
        $sep = $hayComas ? '; ' : ', ';
        $ult = array_pop($con);
        $y = preg_match('/^(?:<strong>)?h?i(?!e)/iu', $ult) ? 'e' : 'y';
        $txt = implode($sep, $con) . ($hayComas ? "; {$y} " : " {$y} ") . $ult;
        $plano = strip_tags($txt);
        return [$txt, $plano];
    }
}

if (!function_exists('seo_pl_etiquetas')) {
    /**
     * Etiquetas genéricas y repetibles entre empresas (enlazan a /listado-de-empresas/etiqueta/…).
     * Con un diccionario fijo salen siempre igual escritas: "Construcción" y no "Construcción de
     * obras" en una y "Obras de construcción" en otra, como pasaba con la IA.
     * Devuelve [[etiqueta, frase para la frase corta], …] por orden de aparición.
     */
    function seo_pl_etiquetas(string $objeto, string $cnae, int $max = 6): array
    {
        // [patrón sobre texto plano, etiqueta, frase, débil]
        static $reglas = [
            ['/\bpromocion (?:inmobiliaria|de (?:viviendas|edificaciones|edificios|inmuebles|terrenos))/', 'Promoción inmobiliaria', 'promoción inmobiliaria'],
            ['/\bconstruccion|\bedificacion|\bobras? (?:civil|publica|maritima|de construccion|de edificacion)|\bconstruir\b/', 'Construcción', 'construcción'],
            ['/\breformas?\b|\brehabilitacion/', 'Reformas', 'reformas'],
            ['/\binmobiliari|\binmuebles?\b|\bfincas? (?:rusticas|urbanas)|\bviviendas?\b|\bbienes raices/', 'Inmobiliaria', 'actividades inmobiliarias'],
            ['/\barrendamiento|\balquiler/', 'Alquiler', 'alquiler'],
            ['/\bhosteleri|\brestaurantes?\b|\bbares?\b|\bcafeteri|\bcatering|\bcomidas preparadas/', 'Hostelería', 'hostelería'],
            ['/\bhotel|\balojamientos?\b|\bapartamentos? turistic|\bhostal|\bcasas? rural|\bpensiones\b/', 'Alojamiento', 'alojamiento'],
            ['/\bagencias? de viaje|\bviajes\b|\bturismo\b|\bturistic/', 'Turismo', 'turismo'],
            ['/\bautotaxi|\btaxis?\b|\bvtc\b|\bvehiculos? con conductor/', 'Taxi y VTC', 'taxi y VTC'],
            ['/\btransportes?\b/', 'Transporte', 'transporte'],
            ['/\blogistic|\balmacenaje|\balmacenamiento|\bdeposito de mercancias/', 'Logística', 'logística'],
            ['/\bmensajeri|\bpaqueteri/', 'Mensajería', 'mensajería'],
            ['/\bmudanzas?\b/', 'Mudanzas', 'mudanzas'],
            ['/\bal por mayor|\bmayorista/', 'Comercio mayorista', 'comercio al por mayor'],
            ['/\bal por menor|\bminorista|\btiendas?\b|\bcomercio electronico|\bventa online|\bventa por internet/', 'Comercio minorista', 'comercio al por menor'],
            ['/\bimportacion|\bexportacion|\bcomercio exterior/', 'Importación y exportación', 'importación y exportación'],
            ['/\btenencia,? (?:de )?(?:administracion|gestion|disfrute)?.{0,120}(?:participaciones|acciones|valores)|\bgestion de (?:participaciones|sociedades participadas)|\bholding|\b(?:empresas|entidades|sociedades) (?:en las que participe|participadas)|\bsedes centrales/', 'Gestión de participaciones', 'gestión de participaciones'],
            ['/\bgestion (?:y administracion )?(?:de|del) (?:su )?patrimonio|\bpatrimonial|\btenencia\b.{0,80}\benajenacion/', 'Gestión patrimonial', 'gestión patrimonial'],
            ['/\bconsultori|\bconsultora\b/', 'Consultoría', 'consultoría'],
            ['/\bauditori/', 'Auditoría', 'auditoría'],
            ['/\bcontab|\bfiscal\b|\btributari/', 'Contabilidad y fiscalidad', 'asesoría contable y fiscal'],
            ['/(?<!fisicas y )\bjuridic|\babogac|\bservicios legales/', 'Servicios jurídicos', 'servicios jurídicos'],
            ['/\basesoramiento|\basesoria|\bgestoria/', 'Asesoría', 'asesoría'],
            ['/\bseguros\b|\bcorreduri|\bmediacion de seguros|\baseguradora/', 'Seguros', 'seguros'],
            ['/\bintermediacion monetaria|\bentidad de credito|\bfinanciacion|\bprestamos\b|\bcreditos?\b|\bservicios financieros|\bactividades financieras|\bactivos financieros|\brecursos financieros|\bvalores (?:mobiliarios|representativos)|\binversion(?:es)? (?:financiera|mobiliaria)/', 'Finanzas', 'actividades financieras'],
            ['/\bpublicidad|\bpublicitari|\bmarketing|\bmercadotecnia/', 'Publicidad y marketing', 'publicidad y marketing'],
            ['/\binformatic|\bsoftware|\bprogramas de ordenador|\baplicaciones (?:informaticas|moviles|web)|\bdesarrollo web|\bpaginas web|\btecnologias de la informacion|\bprogramacion/', 'Informática', 'informática'],
            ['/\btelecomunicacion|\btelefoni|\bfibra optica/', 'Telecomunicaciones', 'telecomunicaciones'],
            ['/\belectricidad|\binstalaciones? electric|\belectricistas?\b/', 'Instalaciones eléctricas', 'instalaciones eléctricas'],
            ['/\bfontaneri|\bsaneamiento/', 'Fontanería', 'fontanería'],
            ['/\bclimatizacion|\baire acondicionado|\bcalefaccion|\brefrigeracion|\bfrio industrial/', 'Climatización', 'climatización'],
            ['/\benergias? renovables|\bfotovoltaic|\benergia solar|\bplacas solares|\beolic/', 'Energías renovables', 'energías renovables'],
            ['/\bcombustibles|\bcarburantes|\bhidrocarburos|\bgasolineras?|\bestaciones de servicio/', 'Combustibles', 'combustibles'],
            ['/\bingenieri/', 'Ingeniería', 'ingeniería'],
            ['/\barquitect/', 'Arquitectura', 'arquitectura'],
            ['/\bagricol|\bagricultura|\bcultivos?\b|\bexplotaciones? agrari|\bagropecuari|\bhorticol|\bfruticol/', 'Agricultura', 'agricultura'],
            ['/\bganaderi|\bganado\b|\bavicol|\bporcino|\bbovino|\bovino|\bcaprino/', 'Ganadería', 'ganadería'],
            ['/\bpesca\b|\bpesquer|\bacuicultura|\bmariscos?\b/', 'Pesca', 'pesca'],
            ['/\bpanaderi|\bpasteleri|\bconfiteri|\bbolleria|\bobrador|\bpan(?:es)?\b/', 'Panadería y pastelería', 'panadería y pastelería'],
            ['/\bcarniceri|\bembutidos|\bproductos carnicos|\bmatadero|\bcharcuteri/', 'Productos cárnicos', 'productos cárnicos'],
            ['/\bbebidas|\bvinos?\b|\bvinicol|\bvitivinicol|\bbodegas?\b|\bcervez|\blicores/', 'Bebidas', 'bebidas'],
            ['/\balimentacion|\balimentos|\balimenticios|\bproductos alimentarios|\bagroalimentari|\bfrutas\b|\bhortalizas/', 'Alimentación', 'alimentación'],
            ['/\btextil|\bprendas de vestir|\bropa\b|\bmoda\b|\bconfeccion de prendas/', 'Textil y moda', 'textil y moda'],
            ['/\bcalzado/', 'Calzado', 'calzado'],
            ['/(?<!bienes )\bmuebles\b|\bmobiliario\b|\bcarpinteria(?! metalica)|\bmadera/', 'Madera y mueble', 'madera y mueble'],
            ['/\bcarpinteria metalica|\bmetalurgi|\bsiderurgi|\bcerrajeri|\bcalderer|\bsoldadura|\bestructuras metalicas|\bmetales\b/', 'Metal', 'trabajos del metal'],
            ['/\bmaquinaria/', 'Maquinaria', 'maquinaria'],
            ['/\bvehiculos(?! de inversion)|\bautomovil|\bautomocion|\btalleres? (?:mecanic|de reparacion)|\bconcesionari|\bneumaticos|\brecambios/', 'Automoción', 'automoción'],
            ['/\bnautic|\bembarcaciones|\bbarcos\b/', 'Náutica', 'náutica'],
            ['/\baeronautic|\baviacion|\baeronaves|\bdrones?\b/', 'Aeronáutica', 'aeronáutica'],
            ['/\bquimic/', 'Química', 'productos químicos'],
            ['/\bplastico/', 'Plásticos', 'plásticos'],
            ['/\bvidrio|\bcristaleri/', 'Vidrio', 'vidrio'],
            ['/\bpapel\b|\bcarton\b/', 'Papel y cartón', 'papel y cartón'],
            ['/\belectrodomestic|\belectronic/', 'Electrónica', 'electrónica'],
            ['/\bfarmacia|\bfarmaceutic|\bmedicamentos|\bparafarmacia/', 'Farmacia', 'productos farmacéuticos'],
            ['/\bclinicas?\b|\bsanitari|\bmedicina\b|\bmedicos?\b|\bhospital|\bfisioterap|\bodontolog|\bdental|\benfermeri|\bpsicolog|\bpodolog|\bsalud\b/', 'Sanidad', 'actividades sanitarias'],
            ['/\bveterinari/', 'Veterinaria', 'veterinaria'],
            ['/\bopticas?\b|\boptometri/', 'Óptica', 'óptica'],
            ['/\besteticas?\b|\bpeluqueri|\bbelleza|\bcosmetic|\bperfumeri/', 'Estética y belleza', 'estética y belleza'],
            ['/\bgeriatric|\bresidencias? (?:de|para) (?:mayores|ancianos|la tercera)|\btercera edad|\bayuda a domicilio|\bservicios sociales|\bpersonas dependientes/', 'Servicios sociales', 'servicios sociales'],
            ['/\bensenanza|\bformacion\b|\beducacion|\beducativ|\bacademias?\b|\bescuelas?\b|\bguarderi|\bcursos\b/', 'Formación y educación', 'formación y educación'],
            ['/\bdeportiv|\bdeportes?\b|\bgimnasios?\b|\bfitness/', 'Deporte', 'deporte'],
            ['/\bespectaculos|\bocio\b|\bentretenimiento|\borganizacion de eventos|\beventos\b/', 'Ocio y eventos', 'ocio y eventos'],
            ['/\baudiovisual|\bcinematograf|\bpeliculas|\btelevision|\bproductora/', 'Audiovisual', 'producción audiovisual'],
            ['/\bmusica\b|\bmusical/', 'Música', 'música'],
            ['/\beditorial|\bedicion de (?:libros|periodicos|revistas)|\bimprenta|\bartes graficas|\bimpresion\b/', 'Artes gráficas', 'artes gráficas y edición'],
            ['/\bfotografi/', 'Fotografía', 'fotografía'],
            ['/\bdiseno\b/', 'Diseño', 'diseño', true],
            ['/\blimpieza/', 'Limpieza', 'limpieza'],
            ['/\bjardineri|\bpaisajismo/', 'Jardinería', 'jardinería'],
            ['/\bseguridad privada|\bservicios de seguridad|\bvigilancia|\bsistemas de seguridad|\balarmas/', 'Seguridad', 'seguridad'],
            ['/\bprevencion de riesgos/', 'Prevención de riesgos', 'prevención de riesgos laborales'],
            ['/\brecursos humanos|\bseleccion de personal|\btrabajo temporal|\bempresa de trabajo/', 'Recursos humanos', 'recursos humanos'],
            ['/\bmineri|\bcanteras?\b|\baridos\b/', 'Minería y canteras', 'minería y canteras'],
            ['/\bresiduos|\breciclaje|\bmedio ?ambient|\btratamiento de aguas|\bdepuracion/', 'Medio ambiente', 'medio ambiente y residuos'],
            ['/\binvestigacion|\bi\+d\b|\bdesarrollo experimental/', 'Investigación y desarrollo', 'investigación y desarrollo'],
            ['/\bjoyeri|\brelojeri|\borfebreri/', 'Joyería', 'joyería'],
            ['/\bjuguet/', 'Juguetes', 'juguetes'],
            ['/\bjuegos? de azar|\bapuestas|\bmaquinas recreativas|\bcasinos?\b|\bbingos?\b/', 'Juego y apuestas', 'juego y apuestas'],
            ['/\bfunerari|\btanatorio/', 'Servicios funerarios', 'servicios funerarios'],
            ['/\btraduccion|\binterpretacion de idiomas/', 'Traducción', 'traducción'],
            ['/\bmantenimiento/', 'Mantenimiento', 'mantenimiento', true],
            ['/\bpintura/', 'Pintura', 'pintura', true],
            ['/\bcompraventa|\bcomercializacion|\bcomercio\b|\bventa\b/', 'Comercio', 'comercio', true],
        ];

        $buscar = static function (string $texto) use ($reglas): array {
            $plano = seo_pl_plano($texto);
            $hall = [];
            foreach ($reglas as $i => $r) {
                if (preg_match($r[0], $plano, $m, PREG_OFFSET_CAPTURE)) {
                    $hall[] = ['pos' => $m[0][1], 'i' => $i, 'tag' => $r[1], 'frase' => $r[2], 'debil' => !empty($r[3])];
                }
            }
            usort($hall, static fn ($a, $b) => $a['pos'] <=> $b['pos'] ?: $a['i'] <=> $b['i']);
            return $hall;
        };

        $todas = array_merge($buscar($objeto), $buscar($cnae));
        $out = [];
        $vistos = [];
        foreach ([false, true] as $debil) {
            foreach ($todas as $h) {
                if ($h['debil'] !== $debil || isset($vistos[$h['tag']])) {
                    continue;
                }
                if ($debil && count($out) >= 3) {
                    break 2;
                }
                $vistos[$h['tag']] = true;
                $out[] = [$h['tag'], $h['frase']];
            }
        }
        // "Comercio" sobra si ya hay "Comercio minorista/mayorista"
        if (isset($vistos['Comercio']) && (isset($vistos['Comercio minorista']) || isset($vistos['Comercio mayorista']))) {
            $out = array_values(array_filter($out, static fn ($e) => $e[0] !== 'Comercio'));
        }
        // "Inmobiliaria" sobra si ya está "Promoción inmobiliaria"
        if (isset($vistos['Promoción inmobiliaria'], $vistos['Inmobiliaria'])) {
            $out = array_values(array_filter($out, static fn ($e) => $e[0] !== 'Inmobiliaria'));
        }
        return array_slice($out, 0, $max);
    }
}

if (!function_exists('seo_pl_cnae_coherente')) {
    /**
     * ¿El CNAE tiene que ver con el objeto social? Sí si comparten etiqueta o alguna palabra
     * con contenido (raíz de 6 letras). Si ninguno de los dos da etiquetas ni palabras, se da por bueno.
     */
    function seo_pl_cnae_coherente(string $objeto, string $cnae): bool
    {
        // Etiquetas que comparten sectores sin relación ("comercio al por menor" de calzado y de
        // cosmética) no cuentan como coincidencia.
        $genericas = ['Comercio', 'Comercio minorista', 'Comercio mayorista', 'Importación y exportación', 'Alquiler', 'Mantenimiento', 'Pintura', 'Logística'];
        $tObj = array_diff(array_column(seo_pl_etiquetas($objeto, '', 20), 0), $genericas);
        $tCnae = array_diff(array_column(seo_pl_etiquetas('', $cnae, 20), 0), $genericas);
        if ($tObj && $tCnae && array_intersect($tObj, $tCnae)) {
            return true;
        }
        $raices = static function (string $s): array {
            $vacias = ['actividad', 'actividades', 'servicio', 'servicios', 'productos', 'general', 'propia', 'terceros', 'cuenta', 'otros', 'otras', 'excepto', 'establecimientos', 'especializados', 'clase', 'cualquier'];
            // raíces de verbos/sustantivos que sirven para cualquier sector
            $genericas = ['compra', 'comerc', 'explot', 'fabric', 'distri', 'gestio', 'admini', 'presta', 'realiz', 'instal', 'elabor', 'repara', 'manten', 'alquil', 'arrend', 'activi', 'servic', 'produc', 'especi', 'establ', 'predom', 'tambie', 'median'];
            preg_match_all('/\p{L}{6,}/u', seo_pl_plano($s), $m);
            $r = [];
            foreach ($m[0] as $w) {
                if (!in_array($w, $vacias, true) && !in_array(mb_substr($w, 0, 6), $genericas, true)) {
                    $r[mb_substr($w, 0, 6)] = true;
                }
            }
            return $r;
        };
        $rCnae = $raices($cnae);
        if (!$rCnae) {
            return true;
        }
        return (bool) array_intersect_key($rCnae, $raices($objeto));
    }
}

if (!function_exists('seo_plantilla_generar')) {
    /**
     * Texto, FAQ, etiquetas, frase corta y resumen del BORME a partir de seo_ai_datos().
     * Misma forma que seo_ai_generar(). No guarda nada.
     */
    function seo_plantilla_generar(array $d): array
    {
        if (function_exists('helper')) {
            helper(['company', 'ficha_hechos']);
        }
        $v = static fn (string $sal, int $n): int => seo_pl_variante($d, $sal, $n);

        $n      = seo_pl_limpio($d['nombre']);
        $cif    = $d['cif'];
        $anio   = $d['anio'];
        $cnae   = $d['cnae'] !== '' ? seo_pl_cnae($d['cnae']) : '';
        $capital = ($d['capital'] ?? '') !== '' && function_exists('ficha_euros') ? ficha_euros((string) $d['capital']) : '';

        // Forma jurídica: la del nombre o, si no, la de la letra del CIF.
        $forma = $d['forma'];
        if ($forma === '' && $cif !== '') {
            $forma = ['A' => 'sociedad anónima', 'B' => 'sociedad limitada', 'F' => 'sociedad cooperativa', 'E' => 'comunidad de bienes', 'J' => 'sociedad civil'][$cif[0]] ?? '';
        }
        if ($forma === '') {
            $forma = 'entidad';
        }
        $Forma = mb_strtoupper(mb_substr($forma, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($forma, 1, null, 'UTF-8');

        $mun = seo_pl_lugar(seo_pl_limpio($d['municipio']));
        $prov = seo_pl_lugar(seo_pl_limpio($d['provincia']));
        $lugar = $mun !== '' ? $mun . ($prov !== '' && mb_strtolower($prov, 'UTF-8') !== mb_strtolower($mun, 'UTF-8') ? " ({$prov})" : '') : $prov;

        $d['objeto'] = seo_pl_reparar($d['objeto']);
        $obj = $d['objeto'] !== '' ? seo_pl_objeto_actividades($d['objeto']) : ['items' => [], 'mas' => false];
        $acts = $obj['items'];

        // 15-10-2026: muchas fichas nuevas traen un CNAE que no tiene nada que ver con el objeto
        // social (vehículos y CNAE de compraventa de inmuebles; maquinaria y "servicios de
        // bebidas"). Como hacía la IA, ese CNAE no se menciona ni da etiquetas.
        if ($cnae !== '' && $acts && !seo_pl_cnae_coherente($d['objeto'], $d['cnae'])) {
            $cnae = '';
        }
        $tags = seo_pl_etiquetas($d['objeto'], $cnae !== '' ? $d['cnae'] : '');

        // ---------- Párrafo 1: quién es ----------
        $p1 = [];
        $cLugar = $lugar !== '';
        $cAnio = $anio !== '';
        switch ($v('p1a', 3)) {
            case 0:
                $p1[] = "{$n} es una {$forma}" . ($cLugar ? " con domicilio en {$lugar}" : '') . ($cAnio ? ", constituida en {$anio}" : '') . '.';
                break;
            case 1:
                $p1[] = ($cAnio ? "Constituida en {$anio}, {$n}" : $n) . " es una {$forma}" . ($cLugar ? " domiciliada en {$lugar}" : '') . '.';
                break;
            default:
                $p1[] = "{$n} es una {$forma}" . ($cAnio ? " constituida en {$anio}" : '') . ($cLugar ? " con domicilio social en {$lugar}" : '') . '.';
        }
        $cnaeP1 = $acts ? $cnae : '';   // sin objeto social, el CNAE va en el párrafo 2
        if ($cif !== '' && $cnaeP1 !== '') {
            $p1[] = [
                "Su CIF es {$cif} y su actividad registrada (CNAE) es {$cnae}.",
                "Tiene el CIF {$cif}; su actividad registrada en la CNAE es {$cnae}.",
                "Se identifica con el CIF {$cif} y figura en la CNAE con la actividad de {$cnae}.",
            ][$v('p1b', 3)];
        } elseif ($cif !== '') {
            $p1[] = ["Su CIF es {$cif}.", "Tiene el CIF {$cif}.", "Se identifica con el CIF {$cif}."][$v('p1b', 3)];
        } elseif ($cnaeP1 !== '') {
            $p1[] = ["Su actividad registrada (CNAE) es {$cnae}.", "Figura en la CNAE con la actividad de {$cnae}.", "Su actividad registrada en la CNAE es {$cnae}."][$v('p1b', 3)];
        }
        if ($capital !== '') {
            $p1[] = ["Su capital social es de {$capital}.", "Tiene un capital social de {$capital}.", "El capital social que consta es de {$capital}."][$v('p1c', 3)];
        }
        // "figura en la CNAE con la actividad de actividades de…" no
        $p1 = array_map(static fn ($f) => preg_replace('/con la actividad de (actividades?\b)/u', 'con $1', $f), $p1);

        // ---------- Párrafo 2: actividad y BORME ----------
        $p2 = [];
        if ($acts) {
            [$lista] = count($acts) === 1 && mb_strlen($acts[0], 'UTF-8') <= 130 ? ["<strong>{$acts[0]}</strong>"] : seo_pl_lista($acts, true);
            $mas = $obj['mas'];
            if (count($acts) === 1) {
                $p2[] = [
                    'Según sus estatutos, su objeto social comprende' . ($mas ? ', entre otras actividades' : '') . ": {$lista}.",
                    'Su objeto social recoge' . ($mas ? ', entre otras,' : '') . " la siguiente actividad: {$lista}.",
                    'Según sus estatutos, puede dedicarse' . ($mas ? ', entre otras cosas,' : '') . " a lo siguiente: {$lista}.",
                ][$v('p2a', 3)];
            } else {
                $p2[] = [
                    'Su objeto social recoge' . ($mas ? ', entre otras,' : '') . " estas actividades: {$lista}.",
                    'Según sus estatutos, puede dedicarse' . ($mas ? ', entre otras cosas,' : '') . " a lo siguiente: {$lista}.",
                    'Según los estatutos, su objeto social comprende' . ($mas ? ', entre otras actividades' : '') . ": {$lista}.",
                ][$v('p2a', 3)];
            }
        } elseif ($cnae !== '') {
            $p2[] = [
                "Su actividad registrada en la Clasificación Nacional de Actividades Económicas (CNAE) es <strong>{$cnae}</strong>.",
                "En la Clasificación Nacional de Actividades Económicas (CNAE) figura con la actividad de <strong>{$cnae}</strong>.",
                "La actividad con la que está registrada en la CNAE es <strong>{$cnae}</strong>.",
            ][$v('p2a', 3)];
            $p2[0] = preg_replace('/con la actividad de (<strong>actividades?\b)/u', 'con $1', $p2[0]);
        }
        $na = (int) $d['num_actos'];
        $numTxt = number_format($na, 0, ',', '.');
        if ($na === 1) {
            $p2[] = [
                "En nuestros datos consta un único anuncio suyo en el BORME, del {$d['ultimo_acto']}.",
                "Consta un único anuncio suyo publicado en el BORME, del {$d['ultimo_acto']}.",
                "En el BORME consta un solo anuncio suyo en nuestros datos, publicado el {$d['ultimo_acto']}.",
            ][$v('p2b', 3)];
        } elseif ($na > 1) {
            $p2[] = [
                "En el BORME constan {$numTxt} anuncios suyos en nuestros datos; el último es del {$d['ultimo_acto']}.",
                "Nuestros datos recogen {$numTxt} anuncios suyos publicados en el BORME, el último del {$d['ultimo_acto']}.",
                $d['primer_acto'] === $d['ultimo_acto']
                    ? "Constan {$numTxt} anuncios suyos en el BORME, publicados el {$d['ultimo_acto']}."
                    : "Constan {$numTxt} anuncios suyos en el BORME, publicados entre el {$d['primer_acto']} y el {$d['ultimo_acto']}.",
            ][$v('p2b', 3)];
        }

        $texto = implode(' ', $p1) . ($p2 ? "\n\n" . implode(' ', $p2) : '');

        // ---------- FAQ ----------
        $faqs = [];
        if ($acts) {
            [, $corta] = seo_pl_lista(array_slice($acts, 0, 2));
            $hayMas = count($acts) > 2 || $obj['mas'];
            $como = !$hayMas ? ' ' : (preg_match('/^actividad/u', $corta) ? ', entre otras, ' : ' actividades como ');
            $faqs[] = ['q' => "¿A qué se dedica {$n}?", 'a' => "Según sus estatutos, su objeto social incluye{$como}{$corta}."];
        } elseif ($cnae !== '') {
            $faqs[] = ['q' => "¿A qué se dedica {$n}?", 'a' => "Su actividad registrada (CNAE) es {$cnae}."];
        }
        $otras = [];
        if ($cAnio) {
            $a = "{$n} se constituyó en {$anio}.";
            $aa = (string) ($d['anio_anuncio_constitucion'] ?? '');
            if ($aa !== '' && (int) $aa >= (int) $anio && (int) $aa <= (int) $anio + 1) {
                $a .= " Su constitución se publicó en el BORME el {$d['anuncio_constitucion']}.";
            }
            $otras[] = ['q' => "¿Cuándo se constituyó {$n}?", 'a' => $a];
        }
        if ($cLugar) {
            $otras[] = ['q' => "¿Dónde tiene su domicilio {$n}?", 'a' => "Tiene su domicilio en {$lugar}."];
        }
        if ($cif !== '') {
            $otras[] = ['q' => "¿Cuál es el CIF de {$n}?", 'a' => "El CIF de {$n} es {$cif}."];
        }
        if ($capital !== '') {
            $otras[] = ['q' => "¿Cuál es el capital social de {$n}?", 'a' => "Su capital social es de {$capital}."];
        }
        if ($na > 0) {
            $otras[] = ['q' => "¿Qué actos de {$n} se han publicado en el BORME?", 'a' => null]; // se rellena abajo
        }
        // Dos de las otras (todas si no hay "a qué se dedica"), rotando por ficha.
        $quiero = 3 - count($faqs);
        if (count($otras) > $quiero) {
            $ini = $v('faq', count($otras));
            $otras = array_slice(array_merge(array_slice($otras, $ini), array_slice($otras, 0, $ini)), 0, $quiero);
        }

        // ---------- Resumen del BORME ----------
        $tipos = [];
        foreach ((array) $d['tipos'] as $t) {
            if (preg_match('/^(.*)\s\((\d+)\)$/u', $t, $m)) {
                $nom = seo_pl_minusculas(mb_strtolower(trim($m[1]), 'UTF-8'));
                $tipos[] = [$nom, (int) $m[2]];
            }
        }
        $resumen = '';
        if ($na === 1) {
            $tipo1 = '';
            if (!empty($d['actos'][0]) && preg_match('/:\s*(.+)$/u', $d['actos'][0], $m) && $m[1] !== 'acto sin clasificar') {
                $tipo1 = seo_pl_minusculas(mb_strtolower($m[1], 'UTF-8'));
            }
            $resumen = "Consta un único anuncio, del {$d['ultimo_acto']}" . ($tipo1 !== '' ? " ({$tipo1})" : '') . '.';
        } elseif ($na > 1) {
            $a1 = preg_match('/(\d{4})$/', $d['primer_acto'], $m) ? $m[1] : '';
            $a2 = preg_match('/(\d{4})$/', $d['ultimo_acto'], $m) ? $m[1] : '';
            $rango = $a1 !== '' && $a1 !== $a2 ? "entre {$a1} y {$a2}" : ($a2 !== '' ? "en {$a2}" : '');
            $resumen = "Constan {$numTxt} anuncios en el BORME" . ($rango !== '' ? " {$rango}" : '');
            if ($tipos) {
                $top = array_slice($tipos, 0, 3);
                if ($top[0][1] <= 1) {
                    // todos una vez: no hay "el que más se repite"
                    $resumen .= ', con actos de ' . seo_pl_lista(array_column($top, 0))[1];
                } else {
                    $partes = array_map(static fn ($t) => "{$t[0]} ({$t[1]})", $top);
                    $resumen .= count($top) === 1
                        ? "; el tipo de acto que más se repite es {$partes[0]}"
                        : '; los tipos de acto que más se repiten son ' . seo_pl_lista($partes)[1];
                }
            }
            $resumen .= '.';
        }
        foreach ($otras as &$o) {
            if ($o['a'] === null) {
                if ($na === 1) {
                    $o['a'] = 'En nuestros datos ' . mb_strtolower(mb_substr($resumen, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($resumen, 1, null, 'UTF-8');
                } else {
                    $o['a'] = "Constan {$numTxt} anuncios en nuestros datos, publicados "
                        . ($d['primer_acto'] === $d['ultimo_acto'] ? "el {$d['ultimo_acto']}" : "entre el {$d['primer_acto']} y el {$d['ultimo_acto']}")
                        . (!$tipos ? '' : ($tipos[0][1] > 1 ? '; los más frecuentes son de ' : ', con actos de ') . seo_pl_lista(array_column(array_slice($tipos, 0, 2), 0))[1]) . '.';
                }
            }
        }
        unset($o);
        $faqs = array_merge($faqs, $otras);

        // ---------- Etiquetas y frase corta ----------
        $tagsTxt = array_column($tags, 0);
        $frases = array_column($tags, 1);
        $donde = $mun !== '' ? " de {$mun}" . ($prov !== '' && mb_strtolower($prov, 'UTF-8') !== mb_strtolower($mun, 'UTF-8') ? " ({$prov})" : '') : ($prov !== '' ? " de {$prov}" : '');
        $candidatas = [];
        if ($frases && $acts) {
            if (count($frases) > 1 && !str_contains($frases[0] . $frases[1], ' y ')) {
                $candidatas[] = "{$Forma}{$donde} cuyo objeto social incluye " . seo_pl_lista(array_slice($frases, 0, 2))[1] . '.';
            }
            $candidatas[] = "{$Forma}{$donde} cuyo objeto social incluye {$frases[0]}.";
        }
        if ($acts && mb_strlen($acts[0], 'UTF-8') <= 80) {
            $candidatas[] = "{$Forma}{$donde} cuyo objeto social incluye {$acts[0]}.";
        }
        if ($cnae !== '') {
            $candidatas[] = preg_replace('/en (actividades?\b)/u', 'en $1', "{$Forma}{$donde} con actividad registrada en {$cnae}.");
        }
        if ($frases) {
            $candidatas[] = "{$Forma}{$donde} con actividad en {$frases[0]}.";
        }
        $candidatas[] = "{$Forma}{$donde}" . ($cAnio ? " constituida en {$anio}" : '') . '.';
        $pitch = '';
        foreach ($candidatas as $c) {
            if (mb_strlen($c, 'UTF-8') <= 150) {
                $pitch = $c;
                break;
            }
        }
        if ($pitch === '') {   // ni la última cabe (municipio larguísimo): sin provincia
            $pitch = mb_strlen($c = "{$Forma} de {$mun}.", 'UTF-8') <= 150 ? $c : "{$Forma}.";
        }

        // Actividad cortada con "…" al final de la frase: sin punto detrás.
        $texto = str_replace(['….', '…</strong>.'], ['…', '…</strong>'], $texto);
        foreach ($faqs as &$f) {
            $f['a'] = str_replace('….', '…', $f['a']);
        }
        unset($f);

        return [
            'seo_text'      => $texto,
            'faqs'          => array_slice($faqs, 0, 3),
            'seo_tags'      => $tagsTxt,
            'seo_pitch'     => $pitch,
            'borme_summary' => $resumen !== '' ? $resumen : null,
        ];
    }
}

if (!function_exists('seo_plantilla_guardar')) {
    /**
     * Guarda como seo_ai_guardar() y, si existe la columna company_enrichment.ai_origen
     * (scripts/sql/textos_plantilla.sql), la marca como 'plantilla' para poder medirlas.
     */
    function seo_plantilla_guardar(int $companyId, array $r): void
    {
        seo_ai_guardar($companyId, $r);
        static $hayOrigen = null;
        $db = \Config\Database::connect();
        if ($hayOrigen === null) {
            $hayOrigen = $db->fieldExists('ai_origen', 'company_enrichment');
        }
        if ($hayOrigen) {
            $db->table('company_enrichment')->where('company_id', $companyId)->update(['ai_origen' => 'plantilla']);
        }
    }
}
