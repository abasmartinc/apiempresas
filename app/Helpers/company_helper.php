<?php

/**
 * Genera la URL correcta para una empresa.
 * Si tiene CIF válido: /CIF-slug
 * Si no tiene CIF válido: /slug
 * 
 * @param array $company Array con datos de la empresa (debe tener 'cif' y 'name' o 'company_name')
 * @return string URL completa de la empresa
 */
function company_url(array $company): string
{
    $cif = trim($company['cif'] ?? '');
    $name = $company['name'] ?? $company['company_name'] ?? '';
    
    // Validar si el CIF es válido (formato: Letra + 7 dígitos + Letra/Dígito)
    $isValidCif = preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $cif);
    
    // Generar slug del nombre
    helper('text');
    $nameClean = str_replace(['º', 'ª'], ['o', 'a'], $name);
    $slug = url_title($nameClean, '-', true);
    
    if ($isValidCif) {
        // CIF válido: usar formato /CIF-slug
        return site_url($cif . ($slug ? ('-' . $slug) : ''));
    } else {
        // Sin CIF válido: usar solo /slug
        return site_url($slug);
    }
}

/**
 * Verifica si un CIF es válido
 * 
 * @param string|null $cif
 * @return bool
 */
function is_valid_cif(?string $cif): bool
{
    if (empty($cif)) {
        return false;
    }
    
    $cif = trim($cif);
    
    // Verificar formato: Letra + 7 dígitos + Letra/Dígito
    if (!preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $cif)) {
        return false;
    }
    
    // Verificar que no sea un valor placeholder
    $invalidValues = ['no disponible', 'nodisponible', 'n/a', 'na', 'sin cif'];
    if (in_array(strtolower($cif), $invalidValues)) {
        return false;
    }
    
    return true;
}

/**
 * Group administrators by name and combine their positions
 *
 * @param array $administrators Array of administrator records
 * @return array Grouped administrators
 */
function group_administrators(array $administrators): array
{
    $grouped = [];
    foreach ($administrators as $admin) {
        $name = trim($admin['name'] ?? '');
        $position = trim($admin['position'] ?? '');
        
        if (empty($name)) continue;

        if (!isset($grouped[$name])) {
            $grouped[$name] = [
                'name' => $name,
                'positions' => []
            ];
        }
        
        if (!empty($position) && !in_array($position, $grouped[$name]['positions'])) {
            $grouped[$name]['positions'][] = $position;
        }
    }

    $result = [];
    foreach ($grouped as $admin) {
        $result[] = [
            'name' => $admin['name'],
            'position' => empty($admin['positions']) ? '' : implode(', ', $admin['positions'])
        ];
    }

    return $result;
}

/**
 * Nombre de empresa listo para mostrar: de "DENDARA SOCIEDAD LIMITADA..." a
 * "Dendara Sociedad Limitada...", con las abreviaturas societarias corregidas.
 *
 * Esta normalización vivía suelta dentro de Company::prepareViewData(), así que
 * los bloques servidos por AJAX (perfil de riesgo, desbloqueo) mostraban el
 * nombre crudo en mayúsculas mientras el resto de la ficha lo mostraba en Título.
 */
if (!function_exists('company_display_name')) {
    function company_display_name(?string $rawName, string $fallback = 'Empresa'): string
    {
        $rawName = trim((string)$rawName);
        if ($rawName === '') {
            return $fallback;
        }

        $name = mb_convert_case(mb_strtolower($rawName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        // MB_CASE_TITLE capitaliza también las partículas: "Dendara Sociedad Limitada
        // De Servicios" chirría en castellano. Se devuelven a minúscula, salvo la
        // primera palabra de la denominación.
        $particulas = [
            'de', 'del', 'la', 'las', 'el', 'los', 'y', 'e', 'en',
            'a', 'al', 'con', 'por', 'para', 'o', 'u',
        ];
        $palabras = explode(' ', $name);
        foreach ($palabras as $i => $palabra) {
            if ($i === 0) {
                continue;
            }
            if (in_array(mb_strtolower($palabra, 'UTF-8'), $particulas, true)) {
                $palabras[$i] = mb_strtolower($palabra, 'UTF-8');
            }
        }

        // Tildes. Los registros guardan todo en MAYÚSCULAS y sin acentos; en caja
        // alta no se nota, pero "Cal Climatizacion" en caja de texto se lee como
        // una errata nuestra. Solo el diccionario de palabras conocidas: la regla
        // genérica -ion → -ión destrozaría apellidos y marcas ("Rion" → "Rión").
        foreach ($palabras as $i => $palabra) {
            $minuscula = mb_strtolower($palabra, 'UTF-8');
            $conTilde  = company_restore_accents($minuscula, false);

            if ($conTilde === $minuscula) {
                continue;   // no estaba en el diccionario
            }

            // Se devuelve la capitalización que tenía (Título o minúscula).
            $palabras[$i] = ($palabra === $minuscula)
                ? $conTilde
                : mb_strtoupper(mb_substr($conTilde, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($conTilde, 1, null, 'UTF-8');
        }

        // Formas jurídicas. Ojo: esto se hacía con str_replace(' Sa', ' S.A.'), que
        // partía cualquier palabra que empezase por esas letras
        // ("Banco De Santander" -> "Banco de S.A.ntander"). Se compara token a token.
        $formas = [
            'SL'    => 'S.L.',   'SLU'  => 'S.L.U.', 'SLL'  => 'S.L.L.',
            'SLNE'  => 'S.L.N.E.',
            'SA'    => 'S.A.',   'SAU'  => 'S.A.U.', 'SAL'  => 'S.A.L.',
            'SRL'   => 'S.R.L.', 'SC'   => 'S.C.',   'SCP'  => 'S.C.P.',
            'SCOOP' => 'S.Coop.','CB'   => 'C.B.',   'AIE'  => 'A.I.E.',
            'SICAV' => 'SICAV',  'UTE'  => 'UTE',    'SAS'  => 'S.A.S.',
        ];
        $ultimo = count($palabras) - 1;
        foreach ($palabras as $i => $palabra) {
            // La forma jurídica va siempre al final. Limitarlo ahí evita destrozar
            // nombres donde esas letras son una palabra ("Grupo Sa Nostra SA").
            if ($i < $ultimo - 1) {
                continue;
            }
            // Un token puede venir como "S.L.," o "SL." — se aparta la puntuación final.
            $cola  = '';
            $limpio = $palabra;
            if (preg_match('/^(.*?)([,;:]+)$/u', $limpio, $mm)) {
                $limpio = $mm[1];
                $cola   = $mm[2];
            }
            $clave = mb_strtoupper(str_replace('.', '', $limpio), 'UTF-8');
            if ($clave !== '' && isset($formas[$clave])) {
                $palabras[$i] = $formas[$clave] . $cola;
            }
        }

        return implode(' ', $palabras);
    }
}

/**
 * Versión corta para texto corrido. Las denominaciones sociales españolas pueden
 * ser larguísimas ("... De Servicios Ambientales Urbanisticos E Inmobiliarios") y
 * dentro de una frase se comen el mensaje. Corta por palabra, nunca a mitad.
 */
if (!function_exists('company_short_name')) {
    function company_short_name(?string $name, int $max = 46): string
    {
        // Ojo: nada de rtrim('.') aquí — se comería el punto final legítimo de "S.L."
        $name = trim((string)$name);
        if ($name === '' || mb_strlen($name, 'UTF-8') <= $max) {
            return $name;
        }

        $cut = mb_substr($name, 0, $max, 'UTF-8');
        $sp  = mb_strrpos($cut, ' ', 0, 'UTF-8');
        if ($sp !== false && $sp > $max * 0.5) {
            $cut = mb_substr($cut, 0, $sp, 'UTF-8');
        }

        return rtrim($cut, " ,.-") . '…';
    }
}

/**
 * Acentos habituales del vocabulario societario. Los registros mercantiles se
 * guardan en MAYÚSCULAS y sin tildes: en caja alta eso pasa desapercibido, pero
 * al pasar a minúscula "explotacion" o "diseno" se leen como erratas.
 */
if (!function_exists('company_restore_accents')) {
    /**
     * @param bool $reglaGenerica Aplica "-ion → -ión" a cualquier palabra.
     *        Va bien en el objeto social, que es prosa común, pero NO en razones
     *        sociales: ahí hay apellidos y marcas ("Rion" acabaría como "Rión").
     *        Para nombres de empresa se usa solo el diccionario.
     */
    function company_restore_accents(string $lower, bool $reglaGenerica = true): string
    {
        // Regla general: casi toda palabra española acabada en -ion lleva tilde
        // (explotación, depuración, gestión, unión). El plural NO la lleva
        // (instalaciones), por eso el anclaje al final de palabra.
        if ($reglaGenerica) {
            $lower = preg_replace('/(\p{L}{2,})ion\b/u', '$1ión', $lower);
        }

        $words = [
            'pequena' => 'pequeña', 'pequenas' => 'pequeñas', 'pequeno' => 'pequeño', 'pequenos' => 'pequeños',
            'espana' => 'España', 'consultoria' => 'consultoría', 'asesoria' => 'asesoría',
            'gestoria' => 'gestoría', 'auditoria' => 'auditoría', 'notaria' => 'notaría',
            'categoria' => 'categoría', 'garantia' => 'garantía', 'garantias' => 'garantías',
            'mercancia' => 'mercancía', 'mercancias' => 'mercancías', 'economia' => 'economía',
            'tecnologia' => 'tecnología', 'tecnologias' => 'tecnologías', 'fotografia' => 'fotografía',
            'cirugia' => 'cirugía', 'linea' => 'línea', 'lineas' => 'líneas', 'area' => 'área', 'areas' => 'áreas',
            'aereo' => 'aéreo', 'aerea' => 'aérea', 'basico' => 'básico', 'basica' => 'básica',
            'libreria' => 'librería', 'zapateria' => 'zapatería', 'pasteleria' => 'pastelería',
            'carniceria' => 'carnicería', 'fruteria' => 'frutería', 'drogueria' => 'droguería',
            'perfumeria' => 'perfumería', 'tapiceria' => 'tapicería', 'cristaleria' => 'cristalería',
            'herreria' => 'herrería', 'cerrajeria' => 'cerrajería', 'tintoreria' => 'tintorería',
            'sastreria' => 'sastrería', 'electrodomesticos' => 'electrodomésticos',
            'diseno' => 'diseño', 'disenos' => 'diseños', 'ano' => 'año', 'anos' => 'años',
            'asi' => 'así', 'ademas' => 'además', 'tambien' => 'también', 'demas' => 'demás',
            'compania' => 'compañía', 'companias' => 'compañías', 'ensenanza' => 'enseñanza',
            'tecnico' => 'técnico', 'tecnica' => 'técnica', 'tecnicos' => 'técnicos', 'tecnicas' => 'técnicas',
            'electrico' => 'eléctrico', 'electrica' => 'eléctrica', 'electricos' => 'eléctricos', 'electricas' => 'eléctricas',
            'electronico' => 'electrónico', 'electronica' => 'electrónica', 'electronicos' => 'electrónicos', 'electronicas' => 'electrónicas',
            'hidraulico' => 'hidráulico', 'hidraulica' => 'hidráulica', 'hidraulicos' => 'hidráulicos', 'hidraulicas' => 'hidráulicas',
            'neumatico' => 'neumático', 'neumatica' => 'neumática', 'neumaticos' => 'neumáticos', 'neumaticas' => 'neumáticas',
            'informatico' => 'informático', 'informatica' => 'informática', 'informaticos' => 'informáticos', 'informaticas' => 'informáticas',
            'quimico' => 'químico', 'quimica' => 'química', 'quimicos' => 'químicos', 'quimicas' => 'químicas',
            'mecanico' => 'mecánico', 'mecanica' => 'mecánica', 'mecanicos' => 'mecánicos', 'mecanicas' => 'mecánicas',
            'automatico' => 'automático', 'automatica' => 'automática', 'automaticos' => 'automáticos', 'automaticas' => 'automáticas',
            'juridico' => 'jurídico', 'juridica' => 'jurídica', 'juridicos' => 'jurídicos', 'juridicas' => 'jurídicas',
            'economico' => 'económico', 'economica' => 'económica', 'economicos' => 'económicos', 'economicas' => 'económicas',
            'publico' => 'público', 'publica' => 'pública', 'publicos' => 'públicos', 'publicas' => 'públicas',
            'logistico' => 'logístico', 'logistica' => 'logística', 'turistico' => 'turístico', 'turistica' => 'turística',
            'agricola' => 'agrícola', 'agricolas' => 'agrícolas', 'energia' => 'energía', 'energias' => 'energías',
            'ingenieria' => 'ingeniería', 'ingenierias' => 'ingenierías', 'ganaderia' => 'ganadería',
            'carpinteria' => 'carpintería', 'fontaneria' => 'fontanería', 'panaderia' => 'panadería',
            'peluqueria' => 'peluquería', 'hosteleria' => 'hostelería', 'ferreteria' => 'ferretería',
            'cafeteria' => 'cafetería', 'joyeria' => 'joyería', 'lavanderia' => 'lavandería',
            'mensajeria' => 'mensajería', 'jardineria' => 'jardinería', 'papeleria' => 'papelería',
            'articulo' => 'artículo', 'articulos' => 'artículos', 'vehiculo' => 'vehículo', 'vehiculos' => 'vehículos',
            'credito' => 'crédito', 'creditos' => 'créditos', 'analisis' => 'análisis', 'practica' => 'práctica',
            'sanitario' => 'sanitario', 'telefono' => 'teléfono', 'telefonica' => 'telefónica',
            'maquina' => 'máquina', 'maquinas' => 'máquinas', 'organico' => 'orgánico', 'organica' => 'orgánica',
            'plastico' => 'plástico', 'plasticos' => 'plásticos', 'metalico' => 'metálico', 'metalicos' => 'metálicos',
            'farmaceutico' => 'farmacéutico', 'farmaceutica' => 'farmacéutica',
            'cinematografico' => 'cinematográfico', 'cinematografica' => 'cinematográfica',
            'geriatrico' => 'geriátrico', 'medico' => 'médico', 'medica' => 'médica',
            'domestico' => 'doméstico', 'domesticos' => 'domésticos',

            // Terminaciones en -ión. Antes las cubría la regla genérica, pero al
            // desactivarla para las razones sociales hacen falta explícitas: son
            // justo las palabras más frecuentes en una denominación social.
            // Los PLURALES no llevan tilde (instalaciones, soluciones), así que
            // no se incluyen: ya están bien tal cual.
            'climatizacion' => 'climatización', 'construccion' => 'construcción',
            'distribucion' => 'distribución', 'gestion' => 'gestión',
            'instalacion' => 'instalación', 'promocion' => 'promoción',
            'importacion' => 'importación', 'exportacion' => 'exportación',
            'produccion' => 'producción', 'fabricacion' => 'fabricación',
            'automocion' => 'automoción', 'restauracion' => 'restauración',
            'decoracion' => 'decoración', 'edificacion' => 'edificación',
            'urbanizacion' => 'urbanización', 'alimentacion' => 'alimentación',
            'formacion' => 'formación', 'comunicacion' => 'comunicación',
            'informacion' => 'información', 'administracion' => 'administración',
            'organizacion' => 'organización', 'innovacion' => 'innovación',
            'inversion' => 'inversión', 'investigacion' => 'investigación',
            'reparacion' => 'reparación', 'rehabilitacion' => 'rehabilitación',
            'refrigeracion' => 'refrigeración', 'calefaccion' => 'calefacción',
            'aplicacion' => 'aplicación', 'solucion' => 'solución',
            'navegacion' => 'navegación', 'aviacion' => 'aviación',
            'confeccion' => 'confección', 'seleccion' => 'selección',
            'direccion' => 'dirección', 'proteccion' => 'protección',
            'inspeccion' => 'inspección', 'recuperacion' => 'recuperación',
            'explotacion' => 'explotación', 'comercializacion' => 'comercialización',
            'digitalizacion' => 'digitalización', 'senalizacion' => 'señalización',
            'impermeabilizacion' => 'impermeabilización', 'mecanizacion' => 'mecanización',
            'union' => 'unión', 'division' => 'división', 'region' => 'región',
            'excavacion' => 'excavación', 'demolicion' => 'demolición',
            'depuracion' => 'depuración', 'fundicion' => 'fundición',
            'iluminacion' => 'iluminación', 'ventilacion' => 'ventilación',
            'conservacion' => 'conservación', 'renovacion' => 'renovación',
            'limpiezas' => 'limpiezas', 'aislacion' => 'aislación',
        ];

        return preg_replace_callback('/\p{L}+/u', static function ($m) use ($words) {
            return $words[$m[0]] ?? $m[0];
        }, $lower);
    }
}

/**
 * Pasa un texto en MAYÚSCULAS (objeto social, cargos, notas registrales) a caja
 * de frase legible. Si el texto ya viene con mayúsculas y minúsculas mezcladas
 * se devuelve intacto: no reescribimos lo que ya está bien.
 */
if (!function_exists('company_sentence_case')) {
    function company_sentence_case(?string $text): string
    {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }

        $letters = preg_replace('/[^\p{L}]/u', '', $text);
        if ($letters === '') {
            return $text;
        }

        $uppers = preg_replace('/[^\p{Lu}]/u', '', $letters);
        if (mb_strlen($uppers, 'UTF-8') / mb_strlen($letters, 'UTF-8') < 0.7) {
            return $text; // ya viene en caja mixta
        }

        $out = company_restore_accents(mb_strtolower($text, 'UTF-8'));

        // Mayúscula al principio y después de punto, cierre de interrogación o dos puntos
        $out = preg_replace_callback(
            '/(^|[.!?:]\s+|\n\s*)(\p{Ll})/u',
            static fn ($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8'),
            $out
        );

        // Enumeraciones tipo "a.- ", "b) " al inicio de apartado
        $out = preg_replace_callback(
            '/(^|\s)(\p{Ll})([.)]\s*-?\s)/u',
            static fn ($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8') . $m[3],
            $out
        );

        // Formas societarias y siglas que deben seguir en mayúscula
        $out = preg_replace_callback(
            '/\b(s\.?l\.?u?|s\.?a\.?u?|s\.?c\.?p?|s\.?l\.?l|c\.?b|u\.?t\.?e|a\.?i\.?e)\b\.?/iu',
            static fn ($m) => mb_strtoupper($m[0], 'UTF-8'),
            $out
        );
        $out = preg_replace_callback(
            '/\b(iva|irpf|cnae|borme|nif|cif|ue|itv|epi|i\+d|tic|pyme|pymes|ong|iae)\b/iu',
            static fn ($m) => mb_strtoupper($m[0], 'UTF-8'),
            $out
        );

        return $out;
    }
}

/**
 * Acceso corto a Config\Solvencia desde las vistas: `solvencia('precios.csv')`.
 *
 * Evita repetir `config('Solvencia')->precios['csv'] ?? '14 €'` en cada parcial
 * y, sobre todo, evita que un precio se quede escrito a mano en una vista y se
 * desincronice de las demás (era el caso del CSV: 14 € y 16 € en la misma página).
 */
if (!function_exists('solvencia')) {
    function solvencia(string $ruta, $porDefecto = null)
    {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = config('Solvencia');
        }

        return $cfg ? $cfg->get($ruta, $porDefecto) : $porDefecto;
    }
}

/**
 * Nivel de riesgo a partir del score. Los cortes salen de Config\Solvencia y
 * están alineados con el motor de scoring (>=60 ALTO, >=30 MEDIO): antes cada
 * vista los repetía a mano con 70, así que una empresa de 65 salía etiquetada
 * "ALTO" por el motor y coloreada de "MEDIO" por la vista.
 *
 * Devuelve [etiqueta, color, fondo, borde].
 */
if (!function_exists('risk_level_visual')) {
    function risk_level_visual(int $score): array
    {
        $medio = (int) solvencia('umbralMedio', 30);
        $alto  = (int) solvencia('umbralAlto', 60);

        if ($score < $medio) {
            return ['BAJO', '#16a34a', '#f0fdf4', '#bbf7d0'];
        }
        if ($score < $alto) {
            return ['MEDIO', '#b45309', '#fffbeb', '#fde68a'];
        }

        return ['ALTO', '#b91c1c', '#fef2f2', '#fecaca'];
    }
}

/**
 * Las seis dimensiones del scoring, listas para pintar.
 *
 * Ya venían calculadas dentro del JSON de `company_risk_profiles.risk_profile`
 * (el motor las serializa con asdict), pero ninguna vista las leía: por eso un
 * 80/ALTO se presentaba como un número sin nada que lo sostuviera.
 *
 * Devuelve [] si el perfil es de una versión anterior que no las traía.
 */
if (!function_exists('risk_dimensions')) {
    function risk_dimensions(array $data): array
    {
        $dims = $data['dimensions'] ?? [];
        if (!is_array($dims) || empty($dims)) {
            return [];
        }

        // Etiqueta, explicación y tope de cada dimensión, en el orden en que se
        // deben leer: primero lo que más pesa.
        $meta = [
            'legal_distress' => [
                'Situación registral', 100,
                'Cierre de hoja, concurso, disolución, liquidación o baja en el Índice de Entidades.',
            ],
            'filing_compliance' => [
                'Depósito de cuentas', 60,
                'Retraso en el depósito de cuentas anuales en el Registro Mercantil.',
            ],
            'governance_volatility' => [
                'Estabilidad del órgano', 35,
                'Concentración de nombramientos y ceses de administradores en ejercicios recientes.',
            ],
            'capital_instability' => [
                'Capital social', 30,
                'Reducciones de capital repetidas.',
            ],
            'structural_volatility' => [
                'Cambios estructurales', 25,
                'Cambios de domicilio social, fusiones o escisiones frecuentes.',
            ],
        ];

        $salida = [];
        foreach ($meta as $clave => [$titulo, $tope, $explica]) {
            if (!array_key_exists($clave, $dims)) {
                continue;
            }
            $valor = (float) $dims[$clave];
            $salida[] = [
                'clave'   => $clave,
                'titulo'  => $titulo,
                'valor'   => $valor,
                'tope'    => $tope,
                'pct'     => $tope > 0 ? min(100, round(($valor / $tope) * 100)) : 0,
                'explica' => $explica,
                'suma'    => true,
            ];
        }

        // El crédito estabilizador es la sexta y va en negativo: antigüedad,
        // cuentas al día y estado activo RESTAN puntos de riesgo.
        if (array_key_exists('stabilizing_credit', $dims)) {
            $credito = (float) $dims['stabilizing_credit'];
            $salida[] = [
                'clave'   => 'stabilizing_credit',
                'titulo'  => 'Factores estabilizadores',
                'valor'   => $credito,
                'tope'    => 25,
                'pct'     => min(100, round((abs($credito) / 25) * 100)),
                'explica' => risk_stabilizers_text($credito, $dims),
                'suma'    => false,
            ];
        }

        return $salida;
    }
}

/**
 * Qué factores estabilizadores aplican DE VERDAD a esta empresa.
 *
 * Aquí había una frase fija: "Antigüedad consolidada, cuentas depositadas al día
 * y estado activo. Restan riesgo." En una empresa cuyo factor dominante es
 * "cuentas anuales sin depositar desde hace 21 ejercicios", esa frase aparecía a
 * tres centímetros del dictamen diciendo justo lo contrario. Quien paga por un
 * scoring paga por criterio, y eso decía que no lo hay.
 *
 * El motor reparte el crédito así (risk_profile_engine.py):
 *
 *     −10  diez años o más de antigüedad
 *     −10  cuentas al día
 *     −5   estado ACTIVA
 *
 * Tres sumandos de 10, 10 y 5 sobre un total conocido no se pueden separar solos
 * (−15 es 10+5 de dos maneras), pero el retraso en el depósito SÍ se sabe: es
 * otra dimensión. Fijando ese término, el resto —0, 5, 10 o 15— solo se puede
 * descomponer de una forma, así que los tres quedan determinados.
 */
if (!function_exists('risk_stabilizers_text')) {
    function risk_stabilizers_text(float $credito, array $dims): string
    {
        $puntos = (int) round(abs($credito));

        if ($puntos <= 0) {
            return 'No consta ninguno de los factores que restan riesgo: antigüedad de diez años '
                 . 'o más, cuentas al día y estado activo.';
        }

        $retraso = array_key_exists('filing_compliance', $dims) ? (float) $dims['filing_compliance'] : null;

        // Se enumeran TODAS las combinaciones que suman el crédito recibido, en vez de
        // quedarse con la primera que cuadre. Antes se probaba "cuentas al día" primero
        // y se devolvía esa: en una empresa con el depósito al día, un −15 se presentaba
        // siempre como "cuentas al día y estado activo" cuando "antigüedad y estado
        // activo" suma exactamente lo mismo. La frase salía en tono de hecho y era una
        // moneda al aire.
        //
        // El único término que se sabe de fuera es el depósito: si hay retraso, el
        // crédito por cuentas al día es 0 seguro. Con eso, muchos casos quedan
        // determinados; los que no, se dicen como lo que son.
        $combinaciones = [];
        foreach ([true, false] as $antiguedad) {
            foreach ([true, false] as $cuentas) {
                foreach ([true, false] as $activa) {
                    if ($cuentas && $retraso !== null && $retraso > 0) {
                        continue;   // imposible: hay retraso en el depósito
                    }
                    $suma = ($antiguedad ? 10 : 0) + ($cuentas ? 10 : 0) + ($activa ? 5 : 0);
                    if ($suma === $puntos) {
                        $combinaciones[] = compact('antiguedad', 'cuentas', 'activa');
                    }
                }
            }
        }

        if (empty($combinaciones)) {
            // El motor ha cambiado el reparto: mejor no inventar el detalle.
            return 'Antigüedad, cuentas al día y estado activo restan riesgo cuando concurren.';
        }

        $nombres = [
            'antiguedad' => 'antigüedad de diez años o más',
            'cuentas'    => 'cuentas depositadas al día',
            'activa'     => 'estado activo',
        ];

        // Un factor es SEGURO si aparece en todas las combinaciones posibles, y DUDOSO
        // si aparece en unas sí y en otras no. Solo los seguros se pueden afirmar.
        $seguros = [];
        $dudosos = [];
        foreach ($nombres as $clave => $etiqueta) {
            $veces = 0;
            foreach ($combinaciones as $c) {
                if ($c[$clave]) {
                    $veces++;
                }
            }
            if ($veces === count($combinaciones)) {
                $seguros[] = $etiqueta;
            } elseif ($veces > 0) {
                $dudosos[] = $etiqueta;
            }
        }

        if (empty($dudosos)) {
            return 'Restan riesgo: ' . company_lista_natural($seguros) . '.';
        }

        $duda = company_lista_natural($dudosos, 'o');

        if (empty($seguros)) {
            return 'Restan riesgo ' . $puntos . ' puntos, por ' . $duda
                 . '. El desglose del motor no permite distinguir cuál de los dos.';
        }

        return 'Restan riesgo: ' . company_lista_natural($seguros)
             . ', y uno de estos dos: ' . $duda . '.';
    }
}

/**
 * "a, b y c" — para no dejar frases con la coma final de una lista en inglés.
 */
if (!function_exists('company_lista_natural')) {
    function company_lista_natural(array $partes, string $union = 'y'): string
    {
        $partes = array_values(array_filter($partes, static fn ($p) => trim((string) $p) !== ''));
        $total  = count($partes);

        if ($total === 0) {
            return '';
        }
        if ($total === 1) {
            return (string) $partes[0];
        }

        $ultima = array_pop($partes);

        return implode(', ', $partes) . ' ' . $union . ' ' . $ultima;
    }
}

/**
 * Punto final de frase detrás de una razón social.
 *
 * "Cal Climatizacion y Conductos S.L." ya acaba en punto, así que escribir
 * "... incidencias en <strong>{nombre}</strong>." produce "S.L..". Devuelve el
 * punto solo si hace falta. También vale para ":" u otro cierre.
 */
if (!function_exists('company_punto')) {
    function company_punto(?string $nombre, string $signo = '.'): string
    {
        $nombre = rtrim((string) $nombre);
        if ($nombre === '') {
            return $signo;
        }

        $ultimo = mb_substr($nombre, -1, 1, 'UTF-8');

        // Si ya cierra con un punto (S.L., S.A.U....), no se añade otro.
        if ($signo === '.' && $ultimo === '.') {
            return '';
        }

        return $ultimo === $signo ? '' : $signo;
    }
}
