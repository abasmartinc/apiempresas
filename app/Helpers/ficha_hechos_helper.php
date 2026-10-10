<?php

/**
 * Bloques de hechos de la ficha de empresa (09-10-2026).
 *
 * Por qué: la ficha enseñaba los datos sueltos (administradores, contratos, lista de actos del
 * BORME) y un texto de IA genérico. Estos bloques los juntan en frases con datos concretos, que
 * cambian en cada empresa y responden a lo que busca quien escribe su nombre: quién es, quién
 * está detrás y si es fiable. No usan IA: todo sale de la base y se calcula al pintar la ficha.
 * Ver claude/seo-criterio-indexacion-fichas.md.
 *
 *   ficha_grupo_control()      5 % de fichas sin bloques, para medir el efecto en Search Console
 *   ficha_resumen_hechos()     "En resumen": párrafo de hechos al principio de la ficha
 *   ficha_historia_borme()     historia de la empresa contada a partir de sus actos del BORME
 *   ficha_red_administradores() otras empresas de cada administrador (consulta, con caché)
 *   ficha_contexto_sector()    su sitio entre las empresas del sector en la provincia (consulta, con caché)
 *   ficha_nombre_completo()    repara nombres de administradores cortados en el punto ("INNGEN"
 *                              por "INNGEN.IO SEED SL") con el texto del propio BORME
 *
 * Reglas comunes:
 *   - Solo hechos que constan. Si un dato falta o no se puede leer con seguridad, la frase no sale.
 *   - Nada del dictamen de riesgo: está tras el registro (decisión del 17-09-2026, ver
 *     claude/bloque-comprobaciones-ficha.md). Solo lo que ya es público en la ficha.
 *   - Hechos, no interpretación (09-10-2026): se dice QUÉ consta ("baja en el Índice de Entidades",
 *     "7 oficios de Juzgados de lo Social"), no qué significa ni qué riesgo supone. Eso es lo que
 *     vende el dictamen. Junto a un hecho adverso, la vista enlaza al dictamen y a "Avísame si cambia".
 *   - Devuelven texto plano o arrays; el HTML y el escapado se hacen en la vista.
 */

if (!function_exists('ficha_grupo_control')) {
    /**
     * 1 de cada 20 fichas (por CIF, o por id si no tiene) queda sin bloques nuevos. Es el grupo
     * de comparación: si los bloques ayudan a indexar, en Search Console se verá la diferencia.
     * Estable: la misma ficha siempre cae en el mismo grupo.
     */
    function ficha_grupo_control(array $company): bool
    {
        $clave = trim((string) ($company['cif'] ?? '')) ?: ('id' . (int) ($company['id'] ?? 0));
        return (crc32(strtoupper($clave)) % 20) === 7;
    }
}

if (!function_exists('ficha_mes_anio')) {
    function ficha_mes_anio($fecha): string
    {
        $ts = is_numeric($fecha) ? (int) $fecha : strtotime((string) $fecha);
        if (!$ts) {
            return '';
        }
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                  'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return $meses[(int) date('n', $ts) - 1] . ' de ' . date('Y', $ts);
    }
}

if (!function_exists('ficha_euros')) {
    /** "3.006,00" / "3006" / 3006.0 -> "3.006 €" ('' si no es un importe) */
    function ficha_euros($valor): string
    {
        if (is_string($valor)) {
            $v = trim(str_replace(['€', 'Euros', 'euros', ' '], '', $valor));
            if ($v === '' || !preg_match('/\d/', $v)) {
                return '';
            }
            if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $v)) {      // 1.234.567,89
                $v = str_replace(['.', ','], ['', '.'], $v);
            } elseif (preg_match('/^\d+(,\d+)?$/', $v)) {               // 1234,5
                $v = str_replace(',', '.', $v);
            }
            if (!is_numeric($v)) {
                return '';
            }
            $valor = (float) $v;
        }
        $n = (float) $valor;
        if ($n <= 0) {
            return '';
        }
        $dec = ($n < 1000 && floor($n) != $n) ? 2 : 0;
        return number_format($n, $dec, ',', '.') . ' €';
    }
}

if (!function_exists('ficha_limpia_nombre')) {
    /** Nombre de persona o sociedad del BORME, legible: "MURRIA MARTIN JOAQUIN" -> "Murria Martin Joaquin" */
    function ficha_limpia_nombre(string $n): string
    {
        $n = trim(preg_replace('/\s+/', ' ', $n), " .;,");
        if ($n === '') {
            return '';
        }
        if (function_exists('company_display_name')) {
            return company_display_name($n, $n);
        }
        return mb_convert_case(mb_strtolower($n, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('ficha_nombre_completo')) {
    /**
     * "INNGEN" -> "INNGEN.IO SEED SL" si el BORME de la empresa lo escribe completo.
     * La extracción de cargos corta el nombre en el primer punto; aquí se busca en el texto
     * del BORME ese mismo nombre seguido de ".algo" y una forma jurídica.
     */
    function ficha_nombre_completo(string $nombre, array $bormePosts): string
    {
        $nombre = trim($nombre);
        if ($nombre === '' || strlen($nombre) < 2) {
            return $nombre;
        }
        $re = '/\b' . preg_quote($nombre, '/') . '(\.[A-Z0-9][A-Z0-9\.\-]*(?:\s+[A-Z0-9&\.\-]+){0,5}?\s+'
            . '(?:S\.?L\.?U?\.?|S\.?A\.?U?\.?|S\.?L\.?P\.?|SOCIEDAD LIMITADA|SOCIEDAD ANONIMA))(?=[\s\.;,]|$)/u';
        foreach ($bormePosts as $p) {
            if (preg_match($re, (string) ($p['description'] ?? ''), $m)) {
                return $nombre . rtrim($m[1], '.');
            }
        }
        return $nombre;
    }
}

if (!function_exists('ficha_cargo_legible')) {
    function ficha_cargo_legible(string $cargo): string
    {
        $c = mb_strtolower(trim($cargo, " .:"), 'UTF-8');
        $c = strtr($c, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        $c = preg_replace('/\s*\.\s*/', '. ', $c);   // "adm.solid" -> "adm. solid"
        $c = rtrim($c, ' .');
        $mapa = [
            'adm. unico' => 'administrador único', 'adm. solid' => 'administrador solidario',
            'adm. solidar' => 'administrador solidario', 'adm. mancom' => 'administrador mancomunado',
            'adm. conjunto' => 'administrador mancomunado', 'administrador unico' => 'administrador único',
            'administrador solidario' => 'administrador solidario', 'apo. sol' => 'apoderado solidario',
            'apo. man' => 'apoderado mancomunado', 'apoderado' => 'apoderado', 'liquisoli' => 'liquidador solidario',
            'liquidador unico' => 'liquidador único', 'liquidador' => 'liquidador', 'cons. del' => 'consejero delegado',
            'con. delegado' => 'consejero delegado', 'consejero' => 'consejero', 'presidente' => 'presidente',
            'vicepresid' => 'vicepresidente', 'secretario' => 'secretario', 'auditor' => 'auditor',
            'audit. cuent' => 'auditor', 'socio unico' => 'socio único',
        ];
        foreach ($mapa as $k => $v) {
            if ($c === $k || strpos($c, $k) === 0) {
                return $v;
            }
        }
        return $c;
    }
}

if (!function_exists('ficha_cargos_de')) {
    /**
     * Pares (cargo, nombre) de un bloque "Nombramientos. Adm. Unico: X. Apoderado: Y;Z."
     * Devuelve [['cargo' => 'administrador único', 'nombre' => 'X'], ...]
     */
    function ficha_cargos_de(string $texto): array
    {
        $out = [];
        if (!preg_match_all('/([A-ZÁÉÍÓÚ][A-Za-zÁÉÍÓÚáéíóú\.\s]{2,25}?):\s*([^:]+?)(?=\.\s+[A-ZÁÉÍÓÚ][A-Za-zÁÉÍÓÚáéíóú\.\s]{2,25}?:|\.\s*(?:Datos registrales|Otros conceptos|Ceses|Nombramientos|Revocaciones|Reelecciones)|\.\s*$|$)/u', $texto, $m, PREG_SET_ORDER)) {
            return $out;
        }
        foreach ($m as $x) {
            $cargo = ficha_cargo_legible($x[1]);
            if (in_array($cargo, ['datos registrales', 'otros conceptos', 'capital', 'objeto social', 'domicilio',
                                  'comienzo de operaciones', 'resultante suscrito', 'importe reduccion'], true)) {
                continue;
            }
            foreach (preg_split('/;/', $x[2]) as $nombre) {
                $nombre = trim(preg_split('/\.\s+(?=[A-ZÁÉÍÓÚ][a-záéíóú])/u', trim($nombre))[0]);
                if ($nombre !== '' && !preg_match('/\d{3,}/', $nombre) && mb_strlen($nombre) <= 90) {
                    $out[] = ['cargo' => $cargo, 'nombre' => $nombre];
                }
            }
        }
        return $out;
    }
}

if (!function_exists('ficha_trozo')) {
    /** Texto de un acto dentro del anuncio: desde su encabezado hasta el siguiente encabezado */
    function ficha_trozo(string $texto, string $encabezado): string
    {
        $enc = ['Constitución', 'Nombramientos', 'Ceses/Dimisiones', 'Revocaciones', 'Reelecciones',
                'Cambio de domicilio social', 'Ampliación de capital', 'Reducción de capital',
                'Declaración de unipersonalidad', 'Sociedad unipersonal', 'Pérdida del caracter de unipersonalidad',
                'Cambio de objeto social', 'Ampliación del objeto social', 'Cambio de denominación social',
                'Disolución', 'Extinción', 'Fusión', 'Escisión', 'Transformación', 'Situación concursal',
                'Modificaciones estatutarias', 'Otros conceptos', 'Datos registrales', 'Reapertura hoja registral',
                'Cierre provisional', 'Depósito de cuentas'];
        $i = mb_stripos($texto, $encabezado . '.');
        if ($i === false) {
            $i = mb_stripos($texto, $encabezado);
        }
        if ($i === false) {
            return '';
        }
        $resto = mb_substr($texto, $i + mb_strlen($encabezado));
        $fin = mb_strlen($resto);
        foreach ($enc as $e) {
            if (strcasecmp($e, $encabezado) === 0) {
                continue;
            }
            $j = mb_stripos($resto, $e);
            if ($j !== false && $j > 0 && $j < $fin) {
                $fin = $j;
            }
        }
        return trim(mb_substr($resto, 0, $fin), " .:");
    }
}

if (!function_exists('ficha_posts_coherentes')) {
    /**
     * Quita los anuncios que no pueden ser de esta empresa: una "Constitución." publicada más
     * de un año después de su fecha de constitución, o antes de ella, es de otra sociedad con el
     * mismo nombre (enlace erróneo). Sin fecha de constitución no se filtra nada.
     */
    function ficha_posts_coherentes(array $bormePosts, string $fundada = ''): array
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fundada)) {
            return $bormePosts;
        }
        $f = strtotime(substr($fundada, 0, 10));
        return array_values(array_filter($bormePosts, static function ($p) use ($f) {
            if (!preg_match('/\bConstituci[oó]n\./u', (string) ($p['description'] ?? ''))) {
                return true;
            }
            $d = strtotime((string) ($p['borme_date'] ?? ''));
            return !$d || ($d >= $f - 86400 * 31 && $d <= $f + 86400 * 400);
        }));
    }
}

if (!function_exists('ficha_historia_borme')) {
    /**
     * Historia de la empresa a partir de sus actos, en orden cronológico.
     * Devuelve [['fecha' => 'YYYY-MM-DD', 'texto' => '...'], ...] (como mucho $max, los más
     * recientes si hay más) o [] si no hay al menos 2 hechos legibles.
     */
    function ficha_historia_borme(array $bormePosts, int $max = 12, string $fundada = ''): array
    {
        $posts = array_values(array_filter(ficha_posts_coherentes($bormePosts, $fundada), static function ($p) {
            return !empty($p['borme_date']) && trim((string) ($p['description'] ?? '')) !== '';
        }));
        usort($posts, static fn($a, $b) => strcmp((string) $a['borme_date'], (string) $b['borme_date']) ?: ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0)));

        $hechos = [];
        foreach ($posts as $p) {
            $t = preg_replace('/\s+/', ' ', (string) $p['description']);
            $frases = [];

            if (preg_match('/\bConstituci[oó]n\./u', $t)) {
                $cap = preg_match('/Capital:\s*([\d\.,]+)\s*Euros/iu', $t, $m) ? ficha_euros($m[1]) : '';
                $frases[] = 'Se constituye' . ($cap ? " con {$cap} de capital" : '');
            }
            if (preg_match('/Cambio de denominaci[oó]n social\.\s*([^.]{2,120}(?:\.[A-Z]{1,3}\.?)*)/u', $t, $m)) {
                $frases[] = 'Cambia su denominación a «' . ficha_limpia_nombre($m[1]) . '»';
            }
            if (preg_match('/Cambio de domicilio social\.\s*.*?\(([^)]{2,60})\)/u', $t, $m)) {
                $frases[] = 'Traslada su domicilio a ' . ficha_limpia_nombre($m[1]);
            }
            if (preg_match('/Ampliaci[oó]n de capital\./u', $t)) {
                $res = preg_match('/Resultante Suscrito:\s*([\d\.,]+)\s*Euros/iu', $t, $m) ? ficha_euros($m[1]) : '';
                $frases[] = 'Amplía capital' . ($res ? " hasta {$res}" : '');
            }
            if (preg_match('/Reducci[oó]n de capital\./u', $t)) {
                $res = preg_match('/Resultante Suscrito:\s*([\d\.,]+)\s*Euros/iu', $t, $m) ? ficha_euros($m[1]) : '';
                $frases[] = 'Reduce capital' . ($res ? " hasta {$res}" : '');
            }
            if (preg_match('/Declaraci[oó]n de unipersonalidad\.\s*Socio [úu]nico:\s*((?:[^.]|\.(?=[^\s]))+)/u', $t, $m)) {
                $frases[] = 'Pasa a ser sociedad unipersonal, con ' . ficha_limpia_nombre(ficha_nombre_completo(trim($m[1]), [$p])) . ' como socio único';
            } elseif (preg_match('/Cambio de identidad del socio [úu]nico:\s*((?:[^.]|\.(?=[^\s]))+)/u', $t, $m)) {
                $frases[] = 'Su socio único pasa a ser ' . ficha_limpia_nombre(ficha_nombre_completo(trim($m[1]), [$p]));
            }
            if (preg_match('/P[eé]rdida del car[aá]cter de unipersonalidad/u', $t)) {
                $frases[] = 'Deja de ser sociedad unipersonal';
            }
            $ceses = preg_match('/Ceses\/Dimisiones\./u', $t) ? ficha_cargos_de(ficha_trozo($t, 'Ceses/Dimisiones')) : [];
            $noms  = preg_match('/Nombramientos\./u', $t) ? ficha_cargos_de(ficha_trozo($t, 'Nombramientos')) : [];
            $admin = static fn($x) => strpos($x['cargo'], 'administrador') !== false || strpos($x['cargo'], 'consejero') !== false
                                    || strpos($x['cargo'], 'presidente') !== false || strpos($x['cargo'], 'liquidador') !== false;
            $nomAdm = array_values(array_filter($noms, $admin));
            $cesAdm = array_values(array_filter($ceses, $admin));
            if ($nomAdm) {
                $x = $nomAdm[0];
                $mas = count($nomAdm) > 1 ? ' (y ' . (count($nomAdm) - 1) . ' cargo' . (count($nomAdm) > 2 ? 's' : '') . ' más)' : '';
                $nombre  = ficha_limpia_nombre(ficha_nombre_completo($x['nombre'], [$p]));
                $mismo   = array_values(array_filter($cesAdm, static fn($c) => mb_strtoupper(trim($c['nombre'])) === mb_strtoupper(trim($x['nombre']))));
                if ($mismo && $mismo[0]['cargo'] === $x['cargo']) {
                    $frases[] = 'Renueva a ' . $nombre . ' como ' . $x['cargo'] . $mas;
                } elseif ($mismo) {
                    $frases[] = 'Cambia su órgano de administración: ' . $nombre . ' pasa de ' . $mismo[0]['cargo'] . ' a ' . $x['cargo'];
                } else {
                    $mismoCargo = array_values(array_filter($nomAdm, static fn($y) => $y['cargo'] === $x['cargo']));
                    if (count($mismoCargo) === 2 && count($nomAdm) === 2) {
                        $plural = preg_replace(['/or (\w+)$/u', '/or$/u'], ['ores $1s', 'ores'], $x['cargo']);
                        $frases[] = 'Nombra ' . $plural . ' a ' . $nombre . ' y '
                                  . ficha_limpia_nombre(ficha_nombre_completo($mismoCargo[1]['nombre'], [$p]))
                                  . ($cesAdm ? ', en sustitución de ' . ficha_limpia_nombre($cesAdm[0]['nombre']) : '');
                    } else {
                        $frases[] = 'Nombra ' . $x['cargo'] . ' a ' . $nombre . $mas
                                  . ($cesAdm ? ', en sustitución de ' . ficha_limpia_nombre($cesAdm[0]['nombre']) : '');
                    }
                }
            } elseif ($cesAdm) {
                $frases[] = 'Cesa ' . ficha_limpia_nombre($cesAdm[0]['nombre']) . ' como ' . $cesAdm[0]['cargo'];
            }
            $apod = array_values(array_filter($noms, static fn($x) => strpos($x['cargo'], 'apoderado') !== false));
            if ($apod && !$nomAdm) {
                $frases[] = count($apod) === 1 ? 'Nombra un apoderado' : 'Nombra ' . count($apod) . ' apoderados';
            }
            if (preg_match('/Ampliaci[oó]n del objeto social|Cambio de objeto social/u', $t)) {
                $frases[] = preg_match('/Ampliaci[oó]n del objeto social/u', $t) ? 'Amplía su objeto social' : 'Cambia su objeto social';
            }
            if (preg_match('/Transformaci[oó]n de sociedad/u', $t)) {
                $frases[] = 'Se transforma en otro tipo de sociedad';
            }
            if (preg_match('/Fusi[oó]n por absorci[oó]n\.\s*Sociedades absorbidas/u', $t)) {
                $frases[] = 'Absorbe a otra sociedad en una fusión';
            } elseif (preg_match('/\bFusi[oó]n\b/u', $t) && preg_match('/\bExtinci[oó]n\./u', $t)) {
                $frases[] = 'Se extingue al ser absorbida en una fusión';
            }
            if (preg_match('/Escisi[oó]n/u', $t)) {
                $frases[] = 'Participa en una escisión';
            }
            if (preg_match('/Situaci[oó]n concursal/u', $t)) {
                if (preg_match('/conclusi[oó]n/iu', $t)) {
                    $frases[] = 'Se publica la conclusión de su concurso de acreedores';
                } else {
                    $frases[] = 'Se publica una situación concursal';
                }
            }
            if (preg_match('/\bDisoluci[oó]n\./u', $t)) {
                $frases[] = 'Se disuelve' . (preg_match('/Disoluci[oó]n\.\s*Voluntaria/u', $t) ? ' voluntariamente' : '');
            }
            if (preg_match('/\bExtinci[oó]n\./u', $t) && !preg_match('/\bFusi[oó]n\b/u', $t)) {
                $frases[] = 'Se extingue';
            }
            if (preg_match('/Cierre provisional hoja registral/iu', $t)) {
                if (preg_match('/[ÍI]ndice de Entidades/iu', $t)) {
                    $frases[] = 'Se cierra provisionalmente su hoja registral por baja en el Índice de Entidades';
                } elseif (preg_match('/dep[oó]sito de (las )?cuentas/iu', $t)) {
                    $frases[] = 'Se cierra provisionalmente su hoja registral por no depositar las cuentas';
                } else {
                    $frases[] = 'Se cierra provisionalmente su hoja registral';
                }
            }
            if (preg_match('/Reapertura hoja registral/iu', $t)) {
                $frases[] = 'Se reabre su hoja registral';
            }
            if (preg_match('/Juzgado de lo Social/iu', $t)) {
                $frases[] = 'El Registro anota un oficio de un Juzgado de lo Social';
            }

            if ($frases) {
                $texto = implode('. ', array_unique($frases));
                $adverso = (bool) preg_match('/concursal|Se disuelve|Se extingue|Se cierra provisionalmente|Juzgado/u', $texto)
                           && strpos($texto, 'conclusión de su concurso') === false;
                $hechos[] = ['fecha' => substr((string) $p['borme_date'], 0, 10), 'texto' => $texto, 'adverso' => $adverso];
            }
        }

        // Oficios de juzgado seguidos (sin otros actos entre medias): una sola línea con el rango
        $agrupados = [];
        $oficio = 'El Registro anota un oficio de un Juzgado de lo Social';
        foreach ($hechos as $h) {
            $k = count($agrupados) - 1;
            if ($h['texto'] === $oficio && $k >= 0 && !empty($agrupados[$k]['oficios'])) {
                $agrupados[$k]['oficios']++;
                $agrupados[$k]['hasta'] = substr($h['fecha'], 0, 4);
                continue;
            }
            if ($h['texto'] === $oficio) {
                $h['oficios'] = 1;
                $h['hasta'] = substr($h['fecha'], 0, 4);
            }
            $agrupados[] = $h;
        }
        foreach ($agrupados as &$g) {
            if (!empty($g['oficios']) && $g['oficios'] > 1) {
                $desde = substr($g['fecha'], 0, 4);
                $g['texto'] = ($desde === $g['hasta'] ? "En {$desde}" : "Entre {$desde} y {$g['hasta']}")
                            . ' el Registro anota ' . $g['oficios'] . ' oficios de Juzgados de lo Social';
            }
            unset($g['oficios'], $g['hasta']);
        }
        unset($g);

        if (count($agrupados) < 2) {
            return [];
        }
        if (count($agrupados) > $max) {
            // En una empresa grande, decenas de "Nombra un apoderado" tapan lo importante.
            // Se quitan primero esos hechos (solo apoderados) y se dice cuántos fueron.
            $soloApod = static fn($h) => (bool) preg_match('/^Nombra (un apoderado|\d+ apoderados)$/u', $h['texto']);
            $nApod = 0;
            foreach ($agrupados as $h) {
                if ($soloApod($h)) {
                    $nApod += preg_match('/^Nombra (\d+)/u', $h['texto'], $m) ? (int) $m[1] : 1;
                }
            }
            $resto = array_values(array_filter($agrupados, static fn($h) => !$soloApod($h)));
            if (count($resto) >= 2) {
                $agrupados = array_slice($resto, -($max - 1));
                if ($nApod > 0) {
                    $ult = end($agrupados);
                    $agrupados[] = ['fecha' => $ult['fecha'], 'texto' => 'Además, en estos años ha nombrado ' . $nApod . ' apoderado' . ($nApod > 1 ? 's' : ''), 'adverso' => false];
                }
                return $agrupados;
            }
        }
        return array_slice($agrupados, -$max);
    }
}

if (!function_exists('ficha_resumen_hechos')) {
    /**
     * "En resumen": frases con hechos de la empresa. Devuelve un array de frases (texto plano)
     * o [] si no hay al menos 3 hechos (una ficha casi vacía no gana nada con un resumen).
     *
     * $estadoReg: salida de company_estado_registral(). $riskProfile: fila de company_risk_profiles
     * (solo se usa si la puntuación es 0, para decir "sin incidencias", que ya es público).
     */
    function ficha_resumen_hechos(array $company, array $administrators, array $bormePosts,
                                  array $contracts, array $subsidies, ?array $holdingData,
                                  array $estadoReg = [], ?array $riskProfile = null): array
    {
        $frases = [];
        $nombre = (string) ($company['name'] ?? '');
        $nUp = strtoupper($nombre);
        $forma = '';
        if (preg_match('/\b(SLP|S\.L\.P\.?|SOCIEDAD LIMITADA PROFESIONAL)\b/', $nUp)) {
            $forma = 'Sociedad limitada profesional';
        } elseif (preg_match('/\b(SLU|S\.L\.U\.?|SOCIEDAD LIMITADA UNIPERSONAL)\b/', $nUp)) {
            $forma = 'Sociedad limitada unipersonal';
        } elseif (preg_match('/\b(SL|S\.L\.?|SOCIEDAD LIMITADA|SOCIEDAD DE RESPONSABILIDAD LIMITADA|SRL)\b/', $nUp)) {
            $forma = 'Sociedad limitada';
        } elseif (preg_match('/\b(SA|S\.A\.?|SAU|S\.A\.U\.?|SOCIEDAD ANONIMA|SOCIEDAD ANÓNIMA)\b/u', $nUp)) {
            $forma = 'Sociedad anónima';
        } elseif (preg_match('/\bCOOP/', $nUp)) {
            $forma = 'Cooperativa';
        }

        // 1. Qué es, desde cuándo y dónde
        $fund = trim((string) ($company['founded'] ?? ''));
        $anio = (preg_match('/^(\d{4})-\d{2}-\d{2}/', $fund, $m) && (int) $m[1] > 1850 && (int) $m[1] <= (int) date('Y')) ? (int) $m[1] : 0;
        $edad = $anio ? ((int) date('Y') - $anio) : 0;
        $lugar = trim((string) ($company['municipality'] ?? ''));
        $prov  = trim((string) ($company['province'] ?? ''));
        $lugarTxt = '';
        if ($lugar !== '' && !preg_match('/^\W*$/', $lugar)) {
            $lugarTxt = ficha_limpia_nombre($lugar) . (($prov && mb_strtolower($prov) !== mb_strtolower($lugar)) ? " ({$prov})" : '');
        } elseif ($prov !== '') {
            $lugarTxt = $prov;
        }
        $estadoTxt = '';
        if (!empty($estadoReg['incidencia'])) {
            $estadoTxt = ($estadoReg['frase'] ?? '');
        } elseif (strtoupper((string) ($company['status'] ?? '')) === 'ACTIVA') {
            $estadoTxt = 'activa';
        }
        $quien = $forma ?: 'Empresa';
        $f1 = $quien;
        if ($estadoTxt === 'activa' && $anio) {
            $f1 .= " activa desde {$anio}" . ($edad >= 2 ? " ({$edad} años)" : '');
        } elseif ($anio) {
            $f1 .= " constituida en {$anio}";
            if ($estadoTxt) {
                $f1 .= " que {$estadoTxt}";
            }
        } elseif ($estadoTxt) {
            $f1 .= $estadoTxt === 'activa' ? ' activa' : " que {$estadoTxt}";
        }
        if ($lugarTxt) {
            $f1 .= ", con domicilio en {$lugarTxt}";
        }
        $hechos = 0;
        if ($anio || $estadoTxt) {
            $frases[] = $f1;
            $hechos++;
            if ($lugarTxt) {
                $hechos++;
            }
        }

        // 2. Capital (último que consta en el BORME; si no, el de la ficha)
        $capital = '';
        $bormePosts = ficha_posts_coherentes($bormePosts, $fund);
        $porFecha = $bormePosts;
        usort($porFecha, static fn($a, $b) => strcmp((string) $b['borme_date'], (string) $a['borme_date']));
        foreach ($porFecha as $p) {
            $t = (string) ($p['description'] ?? '');
            if (preg_match('/Resultante Suscrito:\s*([\d\.,]+)\s*Euros/iu', $t, $m) || preg_match('/Capital:\s*([\d\.,]+)\s*Euros/iu', $t, $m)) {
                $capital = ficha_euros($m[1]);
                break;
            }
        }
        if ($capital === '' && !empty($company['capital_social_raw'])) {
            $capital = ficha_euros((string) $company['capital_social_raw']);
        }
        if ($capital !== '') {
            $frases[] = "Capital social: {$capital}";
            $hechos++;
        }

        // 3. Quién la controla o administra
        $socio = '';
        foreach ($porFecha as $p) {
            $t = (string) ($p['description'] ?? '');
            if (preg_match('/P[eé]rdida del car[aá]cter de unipersonalidad/u', $t)) {
                break;  // lo más reciente es que dejó de ser unipersonal
            }
            if (preg_match('/Cambio de identidad del socio [úu]nico:\s*((?:[^.]|\.(?=[^\s]))+)/u', $t, $m)
                || preg_match('/Socio [úu]nico:\s*((?:[^.]|\.(?=[^\s]))+)/u', $t, $m)) {
                $socio = ficha_limpia_nombre(ficha_nombre_completo(trim($m[1]), $bormePosts));
                $desde = ficha_mes_anio($p['borme_date']);
                break;
            }
        }
        $admUnico = '';
        foreach ($administrators as $a) {
            $pos = mb_strtolower((string) ($a['position'] ?? ''), 'UTF-8');
            if (strpos($pos, 'adm') !== false && (strpos($pos, 'nico') !== false || strpos($pos, 'único') !== false)) {
                $admUnico = ficha_limpia_nombre(ficha_nombre_completo((string) $a['name'], $bormePosts));
                break;
            }
        }
        if ($socio !== '') {
            $f = "Su socio único es {$socio}" . (!empty($desde) ? " (desde {$desde})" : '');
            if ($admUnico !== '' && mb_strtolower($admUnico) === mb_strtolower($socio)) {
                $f .= ', que también es su administrador único';
            } elseif ($admUnico !== '') {
                $f .= "; su administrador único es {$admUnico}";
            }
            $frases[] = $f;
            $hechos++;
        } elseif ($admUnico !== '') {
            $frases[] = "Su administrador único es {$admUnico}";
            $hechos++;
        } else {
            $adms = [];
            foreach ($administrators as $a) {
                $pos = mb_strtolower((string) ($a['position'] ?? ''), 'UTF-8');
                if (preg_match('/adm|consej|presid|liquid/u', $pos)) {
                    $adms[] = ['n' => ficha_limpia_nombre(ficha_nombre_completo((string) $a['name'], $bormePosts)),
                               'c' => ficha_cargo_legible(explode(',', (string) $a['position'])[0])];
                }
            }
            if ($adms) {
                $cargo = $adms[0]['c'];
                $mismo = count(array_unique(array_column($adms, 'c'))) === 1;
                $nombres = array_slice(array_column($adms, 'n'), 0, 3);
                $lista = count($nombres) > 1 ? implode(', ', array_slice($nombres, 0, -1)) . ' y ' . end($nombres) : $nombres[0];
                $resto = count($adms) > 3 ? ' (y ' . (count($adms) - 3) . ' más)' : '';
                if (count($adms) === 1) {
                    $frases[] = "Su {$cargo} es {$lista}";
                } elseif ($mismo) {
                    $plural = preg_replace(['/or (\w+)$/u', '/or$/u', '/o$/u'], ['ores $1s', 'ores', 'os'], $cargo);
                    $frases[] = "Sus {$plural} son {$lista}{$resto}";
                } else {
                    $frases[] = "Sus administradores son {$lista}{$resto}";
                }
                $hechos++;
            }
        }

        // 4. Grupo empresarial
        if (!empty($holdingData['name'])) {
            $frases[] = 'Forma parte del grupo ' . ficha_limpia_nombre((string) $holdingData['name']);
            $hechos++;
        }

        // 5. Contratos públicos y subvenciones
        if ($contracts) {
            $total = 0.0;
            $organos = [];
            foreach ($contracts as $c) {
                $total += (float) ($c['importe_adjudicacion'] ?? 0);
                $o = trim((string) ($c['organo_contratacion'] ?? ''));
                if ($o !== '' && stripos($o, 'desconocido') === false) {
                    $organos[$o] = ($organos[$o] ?? 0) + 1;
                }
            }
            arsort($organos);
            $n = count($contracts);
            $f = 'Contratista del sector público: ' . $n . ' contrato' . ($n > 1 ? 's' : '') . ' adjudicado' . ($n > 1 ? 's' : '');
            if ($total > 0) {
                $f .= ' por ' . ficha_euros($total);
            }
            if ($organos) {
                $f .= ($n > 1 ? ', sobre todo de ' : ', de ') . array_key_first($organos);
            }
            $frases[] = $f;
            $hechos++;
        }
        if ($subsidies) {
            $total = array_sum(array_map(static fn($s) => (float) ($s['importe'] ?? 0), $subsidies));
            $n = count($subsidies);
            $f = 'Ha recibido ' . $n . ' subvenci' . ($n > 1 ? 'ones' : 'ón');
            if ($total > 0) {
                $f .= ' por ' . ficha_euros($total) . ' en total';
            }
            $frases[] = $f;
            $hechos++;
        }

        // 6. Último movimiento en el BORME
        if ($porFecha) {
            $ult = $porFecha[0];
            $h = ficha_historia_borme([$ult, $ult], 1);   // truco: reutiliza el lector de actos
            if ($h && !empty($h[0]['texto'])) {
                $txt = str_replace('. ', '; ', $h[0]['texto']);
                $txt = mb_strtolower(mb_substr($txt, 0, 1)) . mb_substr($txt, 1);
                $frases[] = 'Su último acto publicado en el BORME es de ' . ficha_mes_anio($ult['borme_date']) . ': ' . $txt;
                $hechos++;
            }
        }

        // 7. Sin incidencias (solo si es un 0: ya es público en el titular de la ficha)
        if (empty($estadoReg['incidencia']) && $riskProfile !== null && (int) ($riskProfile['risk_score'] ?? -1) === 0) {
            $frases[] = 'No constan incidencias en su historial del BORME';
        }

        return $hechos >= 3 ? $frases : [];
    }
}

if (!function_exists('ficha_red_administradores')) {
    /**
     * Otras empresas de cada administrador: ['NOMBRE' => ['total' => n, 'activas' => n,
     * 'cerradas' => n, 'muestra' => [['name' =>, 'url' =>], ...]]]
     * La vista solo enseña el total y los enlaces. El desglose activas/cerradas es una señal de
     * riesgo ("vinculaciones") y se reserva al dictamen: no se pinta en la ficha gratuita.
     * Solo administradores con al menos 1 empresa más. Caché de 7 días por empresa.
     */
    function ficha_red_administradores(array $administrators, int $companyId): array
    {
        if (!$administrators || $companyId <= 0) {
            return [];
        }
        $clave = 'ficha_red_adm_v1_' . $companyId;
        try {
            $c = cache($clave);
            if (is_array($c)) {
                return $c;
            }
        } catch (\Throwable $e) {
        }

        $nombres = array_slice(array_values(array_unique(array_filter(array_map(
            static fn($a) => trim((string) ($a['name'] ?? '')), $administrators)))), 0, 8);
        $red = [];
        if ($nombres) {
            $db = \Config\Database::connect();
            $filas = $db->table('company_administrators ca')
                ->select('ca.name, c.id, c.cif, c.company_name, c.estado')
                ->join('companies c', 'c.id = ca.company_id')
                ->whereIn('ca.name', $nombres)
                ->where('ca.company_id !=', $companyId)
                ->limit(600)
                ->get()->getResultArray();
            $vistas = [];
            foreach ($filas as $f) {
                $n = $f['name'];
                if (isset($vistas[$n][$f['id']])) {
                    continue;
                }
                $vistas[$n][$f['id']] = true;
                $e = mb_strtolower((string) $f['estado'], 'UTF-8');
                $cerrada = (bool) preg_match('/extin|fusi|disol|disuel|liquid|cierre|concurso/u', $e);
                $red[$n] = $red[$n] ?? ['total' => 0, 'activas' => 0, 'cerradas' => 0, 'muestra' => []];
                $red[$n]['total']++;
                if ($cerrada) {
                    $red[$n]['cerradas']++;
                } elseif ($e === 'activa') {
                    $red[$n]['activas']++;
                }
                if (count($red[$n]['muestra']) < 4) {
                    $red[$n]['muestra'][] = [
                        'name' => function_exists('company_display_name') ? company_display_name($f['company_name']) : $f['company_name'],
                        'url'  => function_exists('company_url') ? company_url(['cif' => $f['cif'], 'name' => $f['company_name'], 'id' => $f['id']]) : '',
                    ];
                }
            }
            // Un nombre que aparece en cientos de empresas no es una persona: es un cargo
            // genérico mal extraído (o un despacho). Se descarta.
            foreach ($red as $n => $r) {
                if ($r['total'] > 150) {
                    unset($red[$n]);
                }
            }
        }
        try {
            cache()->save($clave, $red, 7 * 86400);
        } catch (\Throwable $e) {
        }
        return $red;
    }
}

if (!function_exists('ficha_contexto_sector')) {
    /**
     * Su sitio entre las empresas del mismo sector (CNAE de 4 cifras) en la misma provincia.
     * ['total' => n, 'activas' => n, 'puesto' => n|null, 'con_contratos' => n] o null.
     * Recuentos por sector y provincia cacheados 7 días; el puesto, 30 días por empresa.
     */
    function ficha_contexto_sector(array $company): ?array
    {
        $prov = trim((string) ($company['province'] ?? ''));
        $cnae = substr(preg_replace('/\D/', '', (string) ($company['cnae'] ?? $company['cnae_code'] ?? '')), 0, 4);
        if ($prov === '' || strlen($cnae) < 4) {
            return null;
        }
        $db = \Config\Database::connect();
        $kSector = 'ficha_sector_v1_' . md5($prov . '|' . $cnae);
        $sector = null;
        try {
            $sector = cache($kSector);
        } catch (\Throwable $e) {
        }
        if (!is_array($sector)) {
            $row = $db->query(
                "SELECT COUNT(*) AS total, SUM(estado = 'ACTIVA') AS activas FROM companies
                 WHERE registro_mercantil = ? AND cnae_code LIKE ?", [$prov, $cnae . '%'])->getRowArray();
            $conContratos = 0;
            if ((int) ($row['total'] ?? 0) > 0 && (int) $row['total'] <= 50000) {
                $r2 = $db->query(
                    "SELECT COUNT(DISTINCT c.id) AS n FROM companies c
                     JOIN company_contracts k ON k.company_cif = c.cif
                     WHERE c.registro_mercantil = ? AND c.cnae_code LIKE ?", [$prov, $cnae . '%'])->getRowArray();
                $conContratos = (int) ($r2['n'] ?? 0);
            }
            $sector = ['total' => (int) ($row['total'] ?? 0), 'activas' => (int) ($row['activas'] ?? 0), 'con_contratos' => $conContratos];
            try {
                cache()->save($kSector, $sector, 7 * 86400);
            } catch (\Throwable $e) {
            }
        }
        if ($sector['total'] < 5) {
            return null;
        }
        $puesto = null;
        $fund = (string) ($company['founded'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $fund)) {
            $kPuesto = 'ficha_puesto_v1_' . (int) ($company['id'] ?? 0);
            try {
                $puesto = cache($kPuesto);
            } catch (\Throwable $e) {
            }
            if (!is_int($puesto)) {
                $r = $db->query(
                    "SELECT COUNT(*) AS n FROM companies WHERE registro_mercantil = ? AND cnae_code LIKE ?
                     AND fecha_constitucion IS NOT NULL AND fecha_constitucion < ?", [$prov, $cnae . '%', substr($fund, 0, 10)])->getRowArray();
                $puesto = (int) ($r['n'] ?? 0) + 1;
                try {
                    cache()->save($kPuesto, $puesto, 30 * 86400);
                } catch (\Throwable $e) {
                }
            }
        }
        return $sector + ['puesto' => $puesto, 'provincia' => $prov, 'sector' => (string) ($company['cnae_label'] ?? '')];
    }
}

if (!function_exists('ficha_hechos_fiables')) {
    /**
     * Cuántos datos propios y fiables tiene la ficha (para el sitemap y el criterio de
     * indexación). Cuenta: historia del BORME con 2+ hechos, administradores, contratos,
     * subvenciones, grupo, cuentas de 2021+, capital, fecha de constitución.
     */
    function ficha_hechos_fiables(array $company, array $administrators, array $bormePosts,
                                   array $contracts, array $subsidies, ?array $holdingData): int
    {
        $n = 0;
        $n += count(ficha_historia_borme($bormePosts, 12, (string) ($company['founded'] ?? ''))) >= 2 ? 1 : 0;
        $n += $administrators ? 1 : 0;
        $n += $contracts ? 1 : 0;
        $n += $subsidies ? 1 : 0;
        $n += !empty($holdingData) ? 1 : 0;
        $n += ((int) ($company['ult_cuentas_anio'] ?? 0) >= 2021) ? 1 : 0;
        $n += preg_match('/^\d{4}-\d{2}-\d{2}/', (string) ($company['founded'] ?? '')) ? 1 : 0;
        return $n;
    }
}

/*
 * Evolución del capital y domicilios (10-10-2026).
 *
 * Dos hechos propios que casi ninguna web enseña y que ya están en el texto de los anuncios:
 * el capital que resulta de cada constitución, ampliación o reducción ("Resultante Suscrito",
 * legible en el 99 % de los casos), y la dirección de cada constitución o cambio de domicilio.
 * Mismas reglas que el resto: solo lo que consta, con la fecha del anuncio, y nada si no se lee.
 */

if (!function_exists('ficha_importe')) {
    /** "3.006,00" -> 3006.0; null si no es un importe razonable */
    function ficha_importe(string $v): ?float
    {
        $v = trim(str_replace(['€', ' '], '', $v));
        if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $v)) {
            $v = str_replace(['.', ','], ['', '.'], $v);
        } elseif (preg_match('/^\d+(,\d+)?$/', $v)) {
            $v = str_replace(',', '.', $v);
        }
        if (!is_numeric($v)) {
            return null;
        }
        $n = (float) $v;
        return ($n > 0 && $n < 1e11) ? $n : null;
    }
}

if (!function_exists('ficha_evolucion_capital')) {
    /**
     * [['fecha' => 'YYYY-MM-DD', 'importe' => float, 'texto' => '3.000 €', 'tipo' => 'Constitución'|'Ampliación'|'Reducción'], ...]
     * en orden cronológico. [] si hay menos de 2 importes (con uno solo no hay evolución).
     */
    function ficha_evolucion_capital(array $bormePosts, string $fundada = ''): array
    {
        $posts = ficha_posts_coherentes($bormePosts, $fundada);
        usort($posts, static fn($a, $b) => strcmp((string) ($a['borme_date'] ?? ''), (string) ($b['borme_date'] ?? '')) ?: ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0)));
        $puntos = [];
        foreach ($posts as $p) {
            $fecha = substr((string) ($p['borme_date'] ?? ''), 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                continue;
            }
            $t = preg_replace('/\s+/', ' ', (string) ($p['description'] ?? ''));
            $tipo = null;
            $importe = null;
            if (preg_match('/\bConstituci[oó]n\./u', $t) && preg_match('/\bCapital:\s*([\d\.,]+)\s*Euros/iu', $t, $m)) {
                $tipo = 'Constitución';
                $importe = ficha_importe($m[1]);
            }
            if (preg_match('/Ampliaci[oó]n de capital\.|Reducci[oó]n de capital\./u', $t)
                && preg_match_all('/Resultante Suscrito:\s*([\d\.,]+)\s*Euros/iu', $t, $mm)) {
                $amp = (bool) preg_match('/Ampliaci[oó]n de capital\./u', $t);
                $red = (bool) preg_match('/Reducci[oó]n de capital\./u', $t);
                $tipo = ($amp && $red) ? 'Reducción y ampliación' : ($amp ? 'Ampliación' : 'Reducción');
                $importe = ficha_importe(end($mm[1]));   // el último resultante del anuncio es el final
            }
            if ($tipo === null || $importe === null) {
                continue;
            }
            $ultimo = end($puntos);
            if ($ultimo && $ultimo['fecha'] === $fecha && abs($ultimo['importe'] - $importe) < 0.01) {
                continue;   // el mismo dato publicado dos veces
            }
            $puntos[] = ['fecha' => $fecha, 'importe' => $importe, 'texto' => ficha_euros($importe), 'tipo' => $tipo];
        }
        return count($puntos) >= 2 ? $puntos : [];
    }
}

if (!function_exists('ficha_historial_domicilios')) {
    /**
     * Domicilios que ha tenido, del más reciente al más antiguo:
     * [['desde' => 'YYYY-MM-DD', 'hasta' => 'YYYY-MM-DD'|'' (actual), 'direccion' => 'C/ Mayor 1', 'municipio' => 'Getafe'], ...]
     * Sale de la constitución ("Domicilio: …") y de cada "Cambio de domicilio social. …". [] si no
     * consta al menos un cambio (con un solo domicilio no hay historial).
     */
    function ficha_historial_domicilios(array $bormePosts, string $fundada = ''): array
    {
        $posts = ficha_posts_coherentes($bormePosts, $fundada);
        usort($posts, static fn($a, $b) => strcmp((string) ($a['borme_date'] ?? ''), (string) ($b['borme_date'] ?? '')) ?: ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0)));
        $lista = [];
        foreach ($posts as $p) {
            $fecha = substr((string) ($p['borme_date'] ?? ''), 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                continue;
            }
            $t = preg_replace('/\s+/', ' ', (string) ($p['description'] ?? ''));
            $dom = null;
            if (preg_match('/Cambio de domicilio social\.\s*(.{3,160}?)\(([^()]{2,60})\)/u', $t, $m)) {
                $dom = $m;
            } elseif (preg_match('/\bConstituci[oó]n\./u', $t) && preg_match('/\bDomicilio:\s*(.{3,160}?)\(([^()]{2,60})\)/u', $t, $m)) {
                $dom = $m;
            }
            if (!$dom) {
                continue;
            }
            $direccion = trim($dom[1], " .,;:");
            $municipio = trim($dom[2], " .,;:");
            if ($direccion === '' || $municipio === '' || preg_match('/\d{2}\.\d{2}\.\d{2}/', $municipio)) {
                continue;   // "(22.09.21)" es una fecha de inscripción, no un municipio
            }
            $titulo = static function (string $s): string {
                $s = mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                return preg_replace_callback('/\b(De|Del|La|Las|El|Los|Y|En|A)\b/u', static fn($x) => mb_strtolower($x[1], 'UTF-8'), $s);
            };
            $entrada = ['desde' => $fecha, 'hasta' => '', 'direccion' => $titulo($direccion), 'municipio' => $titulo($municipio)];
            $ultimo = end($lista);
            if ($ultimo && mb_strtolower($ultimo['direccion'] . $ultimo['municipio']) === mb_strtolower($entrada['direccion'] . $entrada['municipio'])) {
                continue;   // mismo domicilio repetido
            }
            if ($lista) {
                $lista[count($lista) - 1]['hasta'] = $fecha;
            }
            $lista[] = $entrada;
        }
        return count($lista) >= 2 ? array_reverse($lista) : [];
    }
}
