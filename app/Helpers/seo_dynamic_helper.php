<?php

if (!function_exists('calculateCompanySeoScore')) {
    /**
     * Calcula un score de calidad SEO basado en los datos disponibles.
     * Score máximo aproximado: 9
     */
    function calculateCompanySeoScore(array $company): int
    {
        $score = 0;

        $isValid = function ($value) {
            if ($value === null) return false;
            $v = trim((string)$value);
            return !in_array(strtoupper($v), ['', '-', '00 DESCONOCIDA', 'NULL', 'UNDEFINED']);
        };

        // 1. Identificación (+2)
        if ($isValid($company['name'] ?? null)) $score += 1;
        if ($isValid($company['cif'] ?? $company['nif'] ?? null)) $score += 1;

        // 2. Datos Geográficos y Actividad (+2)
        if ($isValid($company['province'] ?? $company['provincia'] ?? null)) $score += 1;
        if ($isValid($company['cnae'] ?? $company['cnae_code'] ?? $company['cnae_label'] ?? null)) $score += 1;

        // 3. Objeto Social (+2) - Factor de peso
        if ($isValid($company['corporate_purpose'] ?? null)) $score += 2;

        // 4. Administradores (+2) - Factor de calidad humana
        if (!empty($company['num_admins']) && (int)$company['num_admins'] > 0) {
            $score += 2;
        }

        // 5. Historial BORME (+1)
        if (!empty($company['num_borme_posts']) && (int)$company['num_borme_posts'] > 0) {
            $score += 1;
        }

        // 6. Bonus Empresa Nueva (+1)
        $name = $company['name'] ?? '';
        if (strpos($name, '2024') !== false || strpos($name, '2025') !== false) {
            $score += 1;
        }

        // 7. Textos IA (+3) - Contenido original y extenso
        if (!empty($company['ai_seo_text'])) {
            $score += 3;
        }

        return $score;
    }
}

if (!function_exists('shouldIndexCompany')) {
    /**
     * Determina si una empresa debe ser indexada.
     * Umbral a 3 para incluir empresas básicas (Nombre+CIF+Provincia).
     */
    function shouldIndexCompany(array $company): bool
    {
        return calculateCompanySeoScore($company) >= 5;
    }
}

if (!function_exists('seo_ai_sin_tildes')) {
    function seo_ai_sin_tildes(string $s): string
    {
        return strtr(mb_strtolower($s, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u']);
    }
}

if (!function_exists('seo_ai_fecha')) {
    /** "2026-10-07" -> "7 de octubre de 2026". Si no es una fecha, la devuelve tal cual. */
    function seo_ai_fecha(string $f): string
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $f, $m) || (int) $m[2] < 1 || (int) $m[2] > 12) {
            return $f;
        }
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return (int) $m[3] . ' de ' . $meses[(int) $m[2] - 1] . ' de ' . $m[1];
    }
}

if (!function_exists('seo_ai_datos')) {
    /**
     * Datos de la ficha que se le pasan a la IA (09-10-2026). Solo lo que consta y es fiable:
     * nombre presentable, forma jurídica, domicilio, año, capital, CNAE fiable, objeto social
     * real y los actos del BORME. Devuelve null si no hay de qué escribir (sin objeto social ni
     * CNAE): con eso la IA solo podría inventar.
     */
    function seo_ai_datos(array $company, array $bormePosts = []): ?array
    {
        helper('company');

        $nombreBruto = trim((string) ($company['name'] ?? $company['company_name'] ?? ''));
        if ($nombreBruto === '') {
            return null;
        }
        $nombre = company_display_name($nombreBruto);

        $formas = [
            '/\b(S\.?\s?L\.?\s?U\.?|SOCIEDAD LIMITADA UNIPERSONAL)\s*$/u'  => 'sociedad limitada unipersonal',
            '/\b(S\.?\s?L\.?\s?P\.?|SOCIEDAD LIMITADA PROFESIONAL)\s*$/u'  => 'sociedad limitada profesional',
            '/\b(S\.?\s?L\.?\s?L\.?|SOCIEDAD LIMITADA LABORAL)\s*$/u'      => 'sociedad limitada laboral',
            '/\b(S\.?\s?L\.?\s?N\.?\s?E\.?|SOCIEDAD LIMITADA NUEVA EMPRESA)\s*$/u' => 'sociedad limitada nueva empresa',
            '/\b(S\.?\s?L\.?|SOCIEDAD LIMITADA|SOCIEDAD DE RESPONSABILIDAD LIMITADA)\s*$/u' => 'sociedad limitada',
            '/\b(S\.?\s?A\.?\s?U\.?|SOCIEDAD ANONIMA UNIPERSONAL)\s*$/u'   => 'sociedad anónima unipersonal',
            '/\b(S\.?\s?A\.?|SOCIEDAD ANONIMA)\s*$/u'                     => 'sociedad anónima',
            '/\bSOCIEDAD COOPERATIVA\b|\bS\.?\s?COOP\.?\b/u'                => 'sociedad cooperativa',
            '/\bSUCURSAL EN ESPA[ÑN]A\b/u'                                  => 'sucursal en España de una sociedad extranjera',
        ];
        $forma = '';
        $nombreMayus = mb_strtoupper(preg_replace('/\(.*?\)/u', '', $nombreBruto), 'UTF-8');
        foreach ($formas as $patron => $texto) {
            if (preg_match($patron, trim($nombreMayus))) {
                $forma = $texto;
                break;
            }
        }

        $cif = strtoupper(trim((string) ($company['cif'] ?? '')));
        if (!preg_match('/^[A-HJNPQRSUVW]\d{7}[0-9A-J]$/', $cif)) {
            $cif = '';
        }

        // "LAS PALMAS DE GRAN CANARIA" -> "Las Palmas de Gran Canaria"
        $titulo = static function (string $s): string {
            if ($s === '') {
                return '';
            }
            $pal = explode(' ', mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'));
            foreach ($pal as $i => $w) {
                if ($i > 0 && in_array(mb_strtolower($w, 'UTF-8'), ['de', 'del', 'la', 'las', 'el', 'los', 'y', 'i', 'e', 'en', "d'"], true)) {
                    $pal[$i] = mb_strtolower($w, 'UTF-8');
                }
            }
            return implode(' ', $pal);
        };
        $municipio = $titulo(trim((string) ($company['municipality'] ?? '')));
        $provincia = $titulo(trim((string) ($company['province'] ?? $company['provincia'] ?? '')));

        $anio = '';
        $f = trim((string) ($company['founded'] ?? ''));
        if (preg_match('/^(\d{4})-\d{2}-\d{2}/', $f, $m) && (int) $m[1] >= 1850 && (int) $m[1] <= (int) date('Y')) {
            $anio = $m[1];
        }

        $capital = trim((string) ($company['capital_social_raw'] ?? ''));
        $cnae = company_cnae_fiable($company) ? trim((string) ($company['cnae_2025_label'] ?? $company['cnae_label'] ?? '')) : '';
        if (in_array(mb_strtolower($cnae, 'UTF-8'), ['', '-', 'desconocido', 'no disponible'], true)) {
            $cnae = '';
        }

        $objeto = trim(preg_replace('/\s+/u', ' ', company_objeto_social_real($company)));
        if (mb_strlen($objeto, 'UTF-8') > 900) {
            $objeto = rtrim(mb_substr($objeto, 0, 900, 'UTF-8'), " ,;") . '…';
        }
        if ($objeto === '' && $cnae === '') {
            return null;
        }

        // getByCompanyId los devuelve del más reciente al más antiguo.
        $fechas = [];
        foreach ($bormePosts as $p) {
            $fp = substr((string) ($p['borme_date'] ?? ''), 0, 10);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fp)) {
                $fechas[] = $fp;
            }
        }
        sort($fechas);

        // Actos: los de una misma fecha se juntan (Mercadona tiene 4 anuncios el mismo día) y se
        // cuentan los tipos de TODOS los anuncios, no solo de los últimos: si no, el resumen de
        // una empresa con 300 anuncios habla solo de las últimas semanas.
        $porFecha = [];
        $tipos = [];
        foreach ($bormePosts as $p) {
            $fecha = substr((string) ($p['borme_date'] ?? ''), 0, 10);
            foreach (preg_split('/\s*,\s*/u', trim((string) ($p['act_types'] ?? ''))) as $t) {
                if ($t === '') {
                    continue;
                }
                $tipos[$t] = ($tipos[$t] ?? 0) + 1;
                if ($fecha !== '') {
                    $porFecha[$fecha][$t] = true;
                }
            }
        }
        // Anuncio de constitución del BORME (el más antiguo). Su fecha es la de PUBLICACIÓN, que
        // puede caer en el año siguiente a la constitución (10-10-2026: "2005 (es 2004)").
        $anuncioConst = '';
        foreach ($porFecha as $fecha => $ts) {
            foreach (array_keys($ts) as $t) {
                if (stripos($t, 'constituci') === 0 && ($anuncioConst === '' || $fecha < $anuncioConst)) {
                    $anuncioConst = $fecha;
                }
            }
        }
        krsort($porFecha);
        $actos = [];
        foreach (array_slice($porFecha, 0, 8, true) as $fecha => $ts) {
            $tsF = array_values(array_filter(array_keys($ts), static fn ($t) => strcasecmp($t, 'Otros') !== 0));
            $actos[] = seo_ai_fecha($fecha) . ': ' . ($tsF ? implode(', ', $tsF) : 'acto sin clasificar');
        }
        arsort($tipos);
        $resumenTipos = [];
        foreach (array_slice($tipos, 0, 9, true) as $t => $nT) {
            if (strcasecmp($t, 'Otros') !== 0) {
                $resumenTipos[] = "{$t} ({$nT})";
            }
        }

        return [
            'nombre'    => $nombre,
            'forma'     => $forma,
            'cif'       => $cif,
            'municipio' => $municipio,
            'provincia' => $provincia,
            'anio'      => $anio,
            'capital'   => $capital,
            'cnae'      => $cnae,
            'objeto'    => $objeto,
            'actos'     => $actos,
            'tipos'     => $resumenTipos,
            'num_actos' => count($bormePosts),
            'anuncio_constitucion' => $anuncioConst !== '' ? seo_ai_fecha($anuncioConst) : '',
            'anio_anuncio_constitucion' => $anuncioConst !== '' ? substr($anuncioConst, 0, 4) : '',
            'primer_acto' => $fechas ? seo_ai_fecha($fechas[0]) : '',
            'ultimo_acto' => $fechas ? seo_ai_fecha(end($fechas)) : '',
        ];
    }
}

if (!function_exists('seo_ai_generar')) {
    /**
     * Pide a la IA el texto, las FAQ, las etiquetas, la frase corta y el resumen del BORME, y
     * lo valida. NO guarda nada (09-10-2026). Devuelve el array validado o lanza una excepción.
     *
     * Por qué se rehízo el prompt:
     *  - Pedía "estilo periodístico" y "conceptos clave o servicios concretos" a partir del
     *    objeto social, y la IA lo convertía en afirmaciones comerciales ("optimiza inversiones
     *    en restauración y bienes inmobiliarios"). El objeto social dice lo que la sociedad
     *    PUEDE hacer según sus estatutos, no lo que hace.
     *  - Las FAQ eran "sobre la actividad o servicios": respuestas inventadas. Ahora solo
     *    preguntas cuya respuesta está en los datos.
     *  - Las etiquetas enlazan a /listado-de-empresas/etiqueta/…: tienen que ser genéricas y
     *    repetibles entre empresas, no frases de una sola.
     *  - La "ciudad" era la provincia del registro; ahora va municipio y provincia.
     *  - Con 800 tokens el JSON podía cortarse, y si no se podía leer se guardaba la respuesta
     *    entera como texto de la ficha. Ahora se rechaza y la cola lo reintenta.
     */
    function seo_ai_generar(array $d): array
    {
        $hayObjeto = $d['objeto'] !== '';
        $hayCnae   = $d['cnae'] !== '';

        $lineas = ["Nombre: {$d['nombre']}"];
        if ($d['forma'] !== '')     $lineas[] = "Forma jurídica: {$d['forma']}";
        if ($d['cif'] !== '')       $lineas[] = "CIF: {$d['cif']}";
        if ($d['municipio'] !== '' || $d['provincia'] !== '') {
            $lineas[] = 'Domicilio: ' . trim($d['municipio'] . ($d['municipio'] !== '' && $d['provincia'] !== '' && mb_strtolower($d['municipio']) !== mb_strtolower($d['provincia']) ? " ({$d['provincia']})" : ($d['municipio'] === '' ? $d['provincia'] : '')));
        }
        $lineas[] = 'Año de constitución: ' . ($d['anio'] !== '' ? $d['anio'] : 'NO CONSTA');
        if (($d['anuncio_constitucion'] ?? '') !== '') {
            $lineas[] = "Anuncio de constitución en el BORME: publicado el {$d['anuncio_constitucion']}. Es la fecha de PUBLICACIÓN, no la de constitución: si lo mencionas, escribe \"su constitución se publicó en el BORME el …\".";
        }
        if ($d['capital'] !== '')   $lineas[] = "Capital social: {$d['capital']}";
        $lineas[] = 'Actividad registrada (CNAE): ' . ($hayCnae ? $d['cnae'] : 'NO CONSTA');
        $lineas[] = 'Objeto social (estatutos): ' . ($hayObjeto ? $d['objeto'] : 'NO CONSTA');
        if ($d['num_actos'] === 1) {
            $lineas[] = "Anuncios publicados en el BORME (en nuestros datos): uno solo, del {$d['ultimo_acto']}"
                . ($d['actos'] ? ' (' . preg_replace('/^[^:]+:\s*/u', '', $d['actos'][0]) . ')' : '') . '.';
        } elseif ($d['num_actos'] > 1) {
            $lineas[] = "Anuncios publicados en el BORME (en nuestros datos): " . number_format((int) $d['num_actos'], 0, ',', '.')
                . ($d['primer_acto'] !== '' ? ", entre el {$d['primer_acto']} y el {$d['ultimo_acto']}. El primer anuncio de nuestros datos NO es la fecha de constitución" : '') . '.';
            if ($d['tipos']) {
                $lineas[] = 'Tipos de acto en todos esos anuncios (veces): ' . implode(', ', $d['tipos']);
            }
            $lineas[] = "Últimas fechas con anuncios:\n" . implode("\n", $d['actos']);
        } else {
            $lineas[] = 'Anuncios publicados en el BORME: ninguno en nuestros datos.';
        }
        $datos = implode("\n", $lineas);
        $n = $d['nombre'];

        // Las preguntas de ejemplo solo con los datos que hay: si no hay año, no se sugiere
        // "¿Cuándo se constituyó?" (con el BBVA respondió con la fecha del primer anuncio).
        $ejemplos = ["¿A qué se dedica {$n}?"];
        if ($d['municipio'] !== '' || $d['provincia'] !== '') $ejemplos[] = "¿Dónde tiene su domicilio {$n}?";
        if ($d['anio'] !== '')    $ejemplos[] = "¿Cuándo se constituyó {$n}?";
        if ($d['cif'] !== '')     $ejemplos[] = "¿Cuál es el CIF de {$n}?";
        if ($d['capital'] !== '') $ejemplos[] = "¿Cuál es el capital social de {$n}?";
        if ($d['num_actos'] > 0)  $ejemplos[] = "¿Qué actos de {$n} se han publicado en el BORME?";
        $ejemplos = '"' . implode('", "', $ejemplos) . '"';

        if ($hayObjeto) {
            $p1Actividad = $hayCnae ? 'y actividad registrada (CNAE)' : '(no menciones actividad registrada ni CNAE: no consta)';
            $p2 = 'las actividades que recoge su objeto social, resumidas en lenguaje claro y agrupadas en dos a cuatro líneas de actividad (no lo copies literalmente). Empieza con "Según sus estatutos, puede dedicarse a…" o "Su objeto social recoge…". Marca con <strong></strong> dos o tres actividades que aparezcan en el objeto social; nunca el nombre ni lugares.';
            $pitchEj = 'Sociedad limitada de Getafe (Madrid) cuyo objeto social es la instalación de sistemas de climatización.';
            $origenTags = 'del objeto social' . ($hayCnae ? ' o del CNAE' : '');
        } else {
            $p1Actividad = 'y actividad registrada (CNAE)';
            $p2 = 'el objeto social NO CONSTA: no escribas "objeto social" ni "estatutos" ni "puede dedicarse". Explica en lenguaje claro qué abarca su actividad registrada (CNAE) en general, sin atribuirle servicios, productos ni actividades concretas. Marca con <strong></strong> la actividad registrada.';
            $pitchEj = 'Sociedad anónima de Bilbao (Vizcaya) con actividad registrada en intermediación monetaria.';
            $origenTags = 'del CNAE (solo lo que dice su nombre; nada que no esté en él)';
        }

        $prompt = <<<TXT
Datos de la empresa (son los ÚNICOS que puedes usar):
{$datos}

Escribe en español de España, con tono informativo y neutro, como una ficha de un registro mercantil, no como publicidad.

REGLAS (obligatorias en todos los campos):
- Usa solo los datos de arriba. Lo que pone NO CONSTA no lo menciones ni lo deduzcas de otro dato.
- El objeto social es lo que la sociedad PUEDE hacer según sus estatutos, no prueba lo que hace hoy. Exprésalo como "su objeto social recoge…", "según sus estatutos, puede dedicarse a…" o "está registrada para…". Nunca "se dedica", "ofrece", "presta servicios a sus clientes". Tampoco con el CNAE: "su actividad registrada es…", no "se dedica a…".
- Resume el objeto social sin ampliarlo: no añadas explicaciones ni actividades que no estén escritas en él.
- Si la actividad registrada (CNAE) no tiene nada que ver con el objeto social (p. ej. CNAE de construcción y objeto social de producción audiovisual), no menciones el CNAE en ningún campo.
- No inventes servicios, productos, clientes, empleados, facturación, instalaciones, marcas, premios, valores, misión, años de experiencia ni ubicaciones.
- Prohibido: líder, referente, compromiso, excelencia, calidad, innovador, soluciones integrales, amplia experiencia, trayectoria, de confianza, profesionales cualificados, a medida, servicio personalizado.
- No digas si la empresa está activa, cerrada o en buena situación, ni valores su solvencia: no es un dato de arriba.
- No inventes fechas, cifras ni CIF. La fecha de constitución es solo el "Año de constitución"; la de los anuncios del BORME es otra cosa.
- Fechas en castellano ("7 de octubre de 2026" o solo el año), nunca "2026-10-07". Números con punto de miles ("1.406").
- Los datos vienen a veces en MAYÚSCULAS y sin tildes: escribe en minúscula normal y con las tildes correctas (salvo el nombre de la empresa). Los nombres de CNAE redáctalos con naturalidad ("Otra intermediación monetaria" → "intermediación monetaria").
- Escribe el nombre exactamente así: "{$n}".

CAMPOS:
1. "seo_text": dos párrafos (entre 80 y 140 palabras en total) separados por una línea en blanco (\\n\\n).
   - Párrafo 1: quién es: nombre, forma jurídica, domicilio, año de constitución, CIF {$p1Actividad}, solo con los datos que consten.
   - Párrafo 2: {$p2} Si hay anuncios del BORME, termina con una frase que diga cuántos constan en nuestros datos y la fecha del último, sin interpretarlos.
2. "faqs": exactamente 3 objetos {"q": "...", "a": "..."}. Elige 3 de estas preguntas (u otras cuya respuesta ESTÉ en los datos): {$ejemplos}. Respuestas de una o dos frases, en texto plano, con el dato concreto. A "¿A qué se dedica…?" se responde con "Según sus estatutos, puede dedicarse a…" o "Su actividad registrada es…".
3. "seo_tags": de 3 a 6 etiquetas de actividad sacadas {$origenTags}, de 1 a 3 palabras, con mayúscula inicial, genéricas y reutilizables entre empresas (por ejemplo "Construcción", "Reformas de viviendas", "Promoción inmobiliaria"). Sin nombre de la empresa, sin lugares, sin marcas, sin adjetivos.
4. "seo_pitch": una frase informativa de 80 a 150 caracteres con forma jurídica, municipio o provincia y actividad principal. Ejemplo: "{$pitchEj}" Sin adjetivos promocionales.
5. "borme_summary": una o dos frases (máximo 40 palabras) que resuman los anuncios del BORME: cuántos hay, entre qué años y qué tipos de acto predominan según el recuento. Sin interpretarlos ni deducir causas o consecuencias (un cese no es "una crisis", un nombramiento no es "una reestructuración"). Si hay un solo anuncio, dilo así ("Consta un único anuncio, del…"). No uses "Otros" como tipo de acto. Si no hay anuncios, "".

Responde solo con el objeto JSON: {"seo_text": "...", "faqs": [{"q": "...", "a": "..."}], "seo_tags": ["..."], "seo_pitch": "...", "borme_summary": "..."}
TXT;

        $ai = new \App\Services\OpenAiService();
        $respuesta = trim((string) $ai->getChatResponse([
            ['role' => 'system', 'content' => 'Eres el redactor de las fichas de un directorio mercantil español. Solo escribes lo que se deduce de los datos que recibes y respondes únicamente con un objeto JSON válido con los campos seo_text, faqs, seo_tags, seo_pitch y borme_summary.'],
            ['role' => 'user', 'content' => $prompt],
        ], [
            'response_format' => ['type' => 'json_object'],
            'max_tokens'      => 1500,
            'temperature'     => 0.3,
        ]));

        $j = json_decode($respuesta, true);
        if (!is_array($j)) {
            throw new \RuntimeException('La IA no devolvió JSON válido');
        }

        // seo_text: solo <strong>, párrafos con \n\n, longitud razonable
        $texto = trim(strip_tags((string) ($j['seo_text'] ?? ''), '<strong>'));
        $texto = preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $texto));
        $largo = mb_strlen(strip_tags($texto), 'UTF-8');
        if ($largo < 250 || $largo > 1600) {
            throw new \RuntimeException("seo_text con longitud fuera de rango ({$largo})");
        }
        $prohibidas = '/\b(l[ií]der(es)?|referentes?|excelencia|innovador[ae]?s?|soluciones integrales|amplia experiencia|trayectoria|de confianza|profesionales cualificados|servicio personalizado)\b/iu';
        $pitch = trim(strip_tags((string) ($j['seo_pitch'] ?? '')));
        // Una palabra "prohibida" que ya está en los datos (p. ej. "LIDER" en el nombre o
        // "de confianza" en el objeto social) no es lenguaje promocional de la IA.
        if (preg_match_all($prohibidas, strip_tags($texto) . ' ' . $pitch, $mm)) {
            foreach ($mm[0] as $w) {
                if (mb_stripos(seo_ai_sin_tildes($datos), seo_ai_sin_tildes($w)) === false) {
                    throw new \RuntimeException("Texto con lenguaje promocional: '{$w}'");
                }
            }
        }
        if (mb_strlen($pitch, 'UTF-8') > 160) {
            $pitch = rtrim(mb_substr($pitch, 0, 157, 'UTF-8')) . '…';
        }

        // Fechas ISO que se cuelen: se pasan a castellano en vez de rechazar.
        $iso = static fn (string $t): string => preg_replace_callback('/\b\d{4}-\d{2}-\d{2}\b/', static fn ($m) => seo_ai_fecha($m[0]), $t);
        $texto = $iso($texto);
        $pitch = $iso($pitch);
        $j['borme_summary'] = $iso((string) ($j['borme_summary'] ?? ''));
        foreach ((array) ($j['faqs'] ?? []) as $k => $f) {
            if (is_array($f)) {
                $j['faqs'][$k]['a'] = $iso((string) ($f['a'] ?? ''));
            }
        }

        // Fechas y CIF inventados: todo año o CIF que aparezca en la salida tiene que estar
        // en los datos que se le pasaron.
        $permitidos = [];
        preg_match_all('/\b(1[89]\d{2}|20\d{2})\b/', $datos, $ma);
        foreach ($ma[1] as $a) {
            $permitidos[$a] = true;
        }
        $salida = strip_tags($texto) . ' ' . $pitch . ' ' . (string) ($j['borme_summary'] ?? '') . ' ' . json_encode($j['faqs'] ?? [], JSON_UNESCAPED_UNICODE);
        preg_match_all('/\b(1[89]\d{2}|20\d{2})\b/', $salida, $ma);
        foreach ($ma[1] as $a) {
            if (!isset($permitidos[$a])) {
                throw new \RuntimeException("Año que no está en los datos: {$a}");
            }
        }
        // Año de constitución (BBVA: "constituida en 2017", que era el primer anuncio). Cada año
        // que aparezca en la misma frase que "constitu/fundad/fundación" tiene que ser el año de
        // constitución, o el de publicación del anuncio de constitución si la frase habla del
        // BORME / de la publicación. Sin año de constitución, "constitución" sin año se admite
        // (p. ej. "consta un anuncio de constitución").
        // 10-10-2026: antes la ventana era "40 caracteres que no sean dígitos" y cruzaba frases y
        // FAQ distintas; ahora se queda dentro de la frase y del campo.
        $sinBorme = strip_tags($texto) . ' ' . $pitch . ' ' . json_encode($j['faqs'] ?? [], JSON_UNESCAPED_UNICODE);
        $anioAnuncio = (string) ($d['anio_anuncio_constitucion'] ?? '');
        if (preg_match_all('/(?:constitu|fundad|fundaci)\w*([^.\d"]{0,60}?)\b(1[89]\d{2}|20\d{2})\b/iu', $sinBorme, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $a = $m[2];
                if ($d['anio'] !== '' && $a === $d['anio']) {
                    continue;
                }
                if ($anioAnuncio !== '' && $a === $anioAnuncio && preg_match('/BORME|public|anuncio/iu', $m[0])) {
                    continue;
                }
                throw new \RuntimeException($d['anio'] !== ''
                    ? "Año de constitución equivocado: {$a} (es {$d['anio']}) en '{$m[0]}'"
                    : "Habla de la constitución con un año y el año no consta: '{$m[0]}'");
            }
        }
        // Sin objeto social no puede citarlo (BBVA: "según sus estatutos, puede dedicarse a…
        // gestión de inversiones", sacado de la nada).
        if ($d['objeto'] === '' && preg_match('/objeto social|estatutos|puede dedicarse/iu', $sinBorme, $mm)) {
            throw new \RuntimeException("Cita el objeto social y no consta ('{$mm[0]}')");
        }

        preg_match_all('/\b[A-HJNPQRSUVW]-?\d{7}-?[0-9A-J]\b/u', strtoupper($salida), $mc);
        foreach ($mc[0] as $c) {
            if (str_replace('-', '', $c) !== $d['cif']) {
                throw new \RuntimeException("CIF que no es el de la empresa: {$c}");
            }
        }

        $faqs = [];
        foreach ((array) ($j['faqs'] ?? []) as $f) {
            $q = trim(strip_tags((string) ($f['q'] ?? '')));
            $a = trim(strip_tags((string) ($f['a'] ?? '')));
            if ($q !== '' && $a !== '' && !(preg_match($prohibidas, $a, $mw) && mb_stripos(seo_ai_sin_tildes($datos), seo_ai_sin_tildes($mw[0])) === false)) {
                $faqs[] = ['q' => $q, 'a' => $a];
            }
        }
        if (count($faqs) < 2) {
            throw new \RuntimeException('Menos de 2 FAQ válidas');
        }
        $faqs = array_slice($faqs, 0, 3);

        $tags = [];
        foreach ((array) ($j['seo_tags'] ?? []) as $t) {
            $t = trim(strip_tags((string) $t), " .,;");
            if ($t === '' || mb_strlen($t, 'UTF-8') > 40 || count(preg_split('/\s+/u', $t)) > 4 || preg_match($prohibidas, $t)) {
                continue;
            }
            $t = mb_strtoupper(mb_substr($t, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($t, 1, null, 'UTF-8');
            if (!in_array($t, $tags, true)) {
                $tags[] = $t;
            }
        }
        $tags = array_slice($tags, 0, 6);

        $resumen = trim(strip_tags((string) ($j['borme_summary'] ?? '')));
        if ($d['num_actos'] === 0) {
            $resumen = '';
        }

        return [
            'seo_text'      => $texto,
            'faqs'          => $faqs,
            'seo_tags'      => $tags,
            'seo_pitch'     => $pitch !== '' ? $pitch : null,
            'borme_summary' => $resumen !== '' ? $resumen : null,
        ];
    }
}

if (!function_exists('seo_ai_texto_roto')) {
    /** Texto guardado que en realidad es la respuesta cruda de la IA o un mensaje de error. */
    function seo_ai_texto_roto(string $t): bool
    {
        $t = ltrim($t);
        return $t !== '' && ($t[0] === '{' || strpos($t, '"seo_text"') !== false || strpos($t, '```') === 0 || strpos($t, 'Hubo un error') !== false);
    }
}

if (!function_exists('seo_ai_guardar')) {
    /**
     * Guarda en company_enrichment lo que devuelve seo_ai_generar (sobrescribe los 5 campos).
     */
    function seo_ai_guardar(int $companyId, array $r): void
    {
        $fila = [
            'ai_seo_text'      => $r['seo_text'],
            'ai_faqs'          => json_encode($r['faqs'], JSON_UNESCAPED_UNICODE),
            'ai_tags'          => !empty($r['seo_tags']) ? json_encode($r['seo_tags'], JSON_UNESCAPED_UNICODE) : null,
            'ai_pitch'         => $r['seo_pitch'],
            'ai_borme_summary' => $r['borme_summary'],
            'updated_at'       => date('Y-m-d H:i:s'),
        ];
        $db = \Config\Database::connect();
        if ($db->table('company_enrichment')->where('company_id', $companyId)->countAllResults() > 0) {
            $db->table('company_enrichment')->where('company_id', $companyId)->update($fila);
        } else {
            $db->table('company_enrichment')->insert($fila + ['company_id' => $companyId, 'created_at' => date('Y-m-d H:i:s')]);
        }
    }
}

if (!function_exists('getOrGenerateAiSeoData')) {
    /**
     * Devuelve los textos de IA guardados o, si no hay, los genera y los guarda en
     * company_enrichment. La llama la cola (seo:process-queue).
     *
     * 09-10-2026: no genera nada si la empresa tiene un estado adverso (la ficha no enseña el
     * texto de IA en ese caso) ni si no hay objeto social ni CNAE (no habría de qué escribir).
     * Devuelve status 'cached' | 'generated' | 'skipped' (con 'motivo') | 'error' (con 'error'),
     * o null si no hay clave de OpenAI.
     */
    function getOrGenerateAiSeoData(array $company, array $bormePosts = [], bool $regenerar = false): ?array
    {
        // $regenerar (09-10-2026): rehace un texto que ya existe. El antiguo solo se sustituye
        // si el nuevo pasa la validación; si la IA falla, la ficha se queda con el que tenía.
        if (!empty($company['ai_seo_text']) && !$regenerar) {
            $faqs = null;
            if (!empty($company['ai_faqs'])) {
                $faqs = json_decode($company['ai_faqs'], true);
            }
            return [
                'status' => 'cached',
                'text'   => $company['ai_seo_text'],
                'faqs'   => $faqs
            ];
        }

        if (empty(env('OPENAI_API_KEY'))) {
            return null;
        }

        try {
            helper('company');
            $estado = company_estado_registral($company);
            $datos  = empty($estado['incidencia']) ? seo_ai_datos($company, $bormePosts) : null;
            if ($datos === null) {
                $motivo = !empty($estado['incidencia']) ? 'estado adverso: ' . ($estado['etiqueta'] ?? '') : 'sin objeto social ni CNAE';
                // Un texto roto (la respuesta cruda de la IA guardada como texto) se borra aunque
                // no se pueda generar otro: se estaba enseñando tal cual en la ficha.
                if ($regenerar && seo_ai_texto_roto((string) ($company['ai_seo_text'] ?? ''))) {
                    \Config\Database::connect()->table('company_enrichment')->where('company_id', $company['id'])->update([
                        'ai_seo_text' => null, 'ai_faqs' => null, 'ai_tags' => null, 'ai_pitch' => null,
                        'ai_borme_summary' => null, 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $motivo .= ' (texto roto borrado)';
                }
                return ['status' => 'skipped', 'motivo' => $motivo];
            }

            $r = seo_ai_generar($datos);

            seo_ai_guardar((int) $company['id'], $r);

            return [
                'status' => 'generated',
                'text'   => $r['seo_text'],
                'faqs'   => $r['faqs'],
                'tags'   => $r['seo_tags'],
                'pitch'  => $r['seo_pitch'],
            ];
        } catch (\Throwable $e) {
            log_message('error', '[seo_dynamic_helper] Error generando texto para ID ' . ($company['id'] ?? '?') . ': ' . $e->getMessage());
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
