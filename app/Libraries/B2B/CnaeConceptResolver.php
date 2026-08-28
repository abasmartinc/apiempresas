<?php
namespace App\Libraries\B2B;

/**
 * Deterministic concept-to-CNAE resolver.
 * Maps LLM-generated industry concept strings to CNAE 2009 hierarchy entries.
 *
 * Design:
 * - LLM produces free-text concepts (e.g. "clínicas dentales", "Actividades odontológicas")
 * - This resolver maps them to canonical CNAE entries via multi-step matching:
 *   1. Strip "CNAE XXXX - " prefix that LLM sometimes returns
 *   2. Longest-match against static concept map (deterministic)
 * - Returns an array of matched CNAE codes with their hierarchy level.
 * - Resolution is deterministic: same input → same output, no LLM call.
 */
class CnaeConceptResolver {

    /**
     * Static concept map: normalized concept keyword → [cnae_code, label, match_level]
     * match_level: 'class' | 'group' | 'division' | 'section'
     * Ordered by decreasing specificity (longest match wins).
     */
    protected static array $conceptMap = [
        // Medical / Dental
        'actividades odontológicas'           => ['8623', 'Actividades odontológicas', 'class'],
        'actividades odontologicas'           => ['8623', 'Actividades odontológicas', 'class'],
        'clínica dental'                      => ['8623', 'Actividades odontológicas', 'class'],
        'clinica dental'                      => ['8623', 'Actividades odontológicas', 'class'],
        'odontol'                             => ['8623', 'Actividades odontológicas', 'class'],
        'dental'                              => ['8623', 'Actividades odontológicas', 'class'],
        'medicina general'                    => ['8621', 'Actividades de medicina general', 'class'],
        'medicina especializada'              => ['8622', 'Actividades de medicina especializada', 'class'],
        'sanitari'                            => ['86', 'Actividades sanitarias', 'division'],
        'salud'                               => ['86', 'Actividades sanitarias', 'division'],
        'hospital'                            => ['861', 'Actividades hospitalarias', 'group'],
        'actividades médicas'                 => ['862', 'Actividades médicas y odontológicas', 'group'],
        'actividades medicas'                 => ['862', 'Actividades médicas y odontológicas', 'group'],
        'clínica'                             => ['862', 'Actividades médicas y odontológicas', 'group'],
        'clinica'                             => ['862', 'Actividades médicas y odontológicas', 'group'],

        // Veterinary
        'actividades veterinarias'            => ['7500', 'Actividades veterinarias', 'class'],
        'veterinar'                           => ['7500', 'Actividades veterinarias', 'class'],
        'consultoría veterinaria'             => ['7500', 'Actividades veterinarias', 'class'],
        'consultoria veterinaria'             => ['7500', 'Actividades veterinarias', 'class'],
        'servicios veterinarios'              => ['7500', 'Actividades veterinarias', 'class'],

        // Legal
        'actividades jurídicas'               => ['6910', 'Actividades jurídicas', 'class'],
        'actividades juridicas'               => ['6910', 'Actividades jurídicas', 'class'],
        'servicios de asesoría jurídica'      => ['6910', 'Actividades jurídicas', 'class'],
        'servicios de asesoria juridica'      => ['6910', 'Actividades jurídicas', 'class'],
        'asesoría jurídica'                   => ['6910', 'Actividades jurídicas', 'class'],
        'asesoria juridica'                   => ['6910', 'Actividades jurídicas', 'class'],
        'despacho de abogad'                  => ['6910', 'Actividades jurídicas', 'class'],
        'abogad'                              => ['6910', 'Actividades jurídicas', 'class'],
        'notari'                              => ['6910', 'Actividades jurídicas', 'class'],
        'juríd'                               => ['6910', 'Actividades jurídicas', 'class'],
        'juridic'                             => ['6910', 'Actividades jurídicas', 'class'],

        // Accounting / Fiscal
        'actividades de contabilidad'         => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'teneduría de libros'                 => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'teneduria de libros'                 => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'asesoría fiscal'                     => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'asesoria fiscal'                     => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'auditoría'                           => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'auditoria'                           => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'contabil'                            => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'gestoría'                            => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'gestoria'                            => ['6920', 'Actividades de contabilidad, teneduría de libros, auditoría y asesoría fiscal', 'class'],
        'fiscal'                              => ['69', 'Actividades jurídicas y de contabilidad', 'division'],

        // Real Estate
        'agentes de la propiedad inmobiliaria' => ['6831', 'Agentes de la propiedad inmobiliaria', 'class'],
        'agencias inmobiliarias'              => ['6831', 'Agentes de la propiedad inmobiliaria', 'class'],
        'agencia inmobiliaria'                => ['6831', 'Agentes de la propiedad inmobiliaria', 'class'],
        'promotoras inmobiliarias'            => ['4110', 'Promoción inmobiliaria', 'class'],
        'promotora inmobiliaria'              => ['4110', 'Promoción inmobiliaria', 'class'],
        'promoción inmobiliaria'              => ['4110', 'Promoción inmobiliaria', 'class'],
        'promocion inmobiliaria'              => ['4110', 'Promoción inmobiliaria', 'class'],
        'alquiler de bienes inmobiliarios'    => ['6820', 'Alquiler de bienes inmobiliarios por cuenta propia', 'class'],
        'compraventa de bienes inmobiliarios' => ['6810', 'Compraventa de bienes inmobiliarios por cuenta propia', 'class'],
        'inmobiliaria'                        => ['68', 'Actividades inmobiliarias', 'division'],
        'inmobiliarias'                       => ['68', 'Actividades inmobiliarias', 'division'],
        'inmobi'                              => ['68', 'Actividades inmobiliarias', 'division'],

        // Restaurants / Hospitality
        'restaurantes y puestos de comidas'   => ['5610', 'Restaurantes y puestos de comidas', 'class'],
        'restaurantes'                        => ['5610', 'Restaurantes y puestos de comidas', 'class'],
        'restauran'                           => ['5610', 'Restaurantes y puestos de comidas', 'class'],
        'establecimientos de bebidas'         => ['5630', 'Establecimientos de bebidas', 'class'],
        'comida rápida'                       => ['5610', 'Restaurantes y puestos de comidas', 'class'],
        'comida rapida'                       => ['5610', 'Restaurantes y puestos de comidas', 'class'],
        'cafetería'                           => ['5630', 'Establecimientos de bebidas', 'class'],
        'cafeteria'                           => ['5630', 'Establecimientos de bebidas', 'class'],
        'hostelería'                          => ['I', 'Hostelería', 'section'],
        'hosteleria'                          => ['I', 'Hostelería', 'section'],

        // Manufacturing / Industry
        'fabricación de maquinaria y equipo'  => ['28', 'Fabricación de maquinaria y equipo n.c.o.p.', 'division'],
        'fabricacion de maquinaria y equipo'  => ['28', 'Fabricación de maquinaria y equipo n.c.o.p.', 'division'],
        'fabricación de maquinaria'           => ['28', 'Fabricación de maquinaria y equipo n.c.o.p.', 'division'],
        'fabricacion de maquinaria'           => ['28', 'Fabricación de maquinaria y equipo n.c.o.p.', 'division'],
        'máquinas herramienta para el metal'  => ['2841', 'Fabricación de máquinas herramienta para trabajar el metal', 'class'],
        'maquinas herramienta para el metal'  => ['2841', 'Fabricación de máquinas herramienta para trabajar el metal', 'class'],
        'mantenimiento y reparación de maquinaria' => ['3312', 'Reparación de maquinaria', 'class'],
        'mantenimiento y reparacion de maquinaria' => ['3312', 'Reparación de maquinaria', 'class'],
        'mantenimiento de maquinaria'         => ['3312', 'Reparación de maquinaria', 'class'],
        'reparación de maquinaria'            => ['3312', 'Reparación de maquinaria', 'class'],
        'reparacion de maquinaria'            => ['3312', 'Reparación de maquinaria', 'class'],
        'fabricación de productos eléctricos' => ['27', 'Fabricación de material y equipo eléctrico', 'division'],
        'fabricacion de productos electricos' => ['27', 'Fabricación de material y equipo eléctrico', 'division'],
        'fabricación de componentes electrónicos' => ['261', 'Fabricación de componentes electrónicos', 'group'],
        'fabricacion de componentes electronicos' => ['261', 'Fabricación de componentes electrónicos', 'group'],
        'fabricación de productos metálicos'  => ['25', 'Fabricación de productos metálicos', 'division'],
        'fabricacion de productos metalicos'  => ['25', 'Fabricación de productos metálicos', 'division'],
        'metalúrgi'                           => ['24', 'Metalurgia', 'division'],
        'metalurgi'                           => ['24', 'Metalurgia', 'division'],
        'industria manufacturera'             => ['C', 'Industria manufacturera', 'section'],
        'servicios de ingeniería'             => ['711', 'Servicios técnicos de ingeniería', 'group'],
        'servicios de ingenieria'             => ['711', 'Servicios técnicos de ingeniería', 'group'],
        'maquinaria'                          => ['28', 'Fabricación de maquinaria y equipo n.c.o.p.', 'division'],

        // Construction
        'construcción'                        => ['F', 'Construcción', 'section'],
        'construccion'                        => ['F', 'Construcción', 'section'],

        // Transport
        'transporte y almacenamiento'         => ['H', 'Transporte y almacenamiento', 'section'],
        'transporte'                          => ['H', 'Transporte y almacenamiento', 'section'],
        'logística'                           => ['H', 'Transporte y almacenamiento', 'section'],
        'logistica'                           => ['H', 'Transporte y almacenamiento', 'section'],

        // Technology (section-level, very broad)
        'tecnología'                          => ['J', 'Información y comunicaciones', 'section'],
        'tecnologia'                          => ['J', 'Información y comunicaciones', 'section'],
    ];

    /**
     * Resolve LLM-generated concept strings to CNAE entries.
     */
    public static function resolve(array $concepts): array {
        $results = [];
        $seen = [];

        foreach ($concepts as $concept) {
            $normalized = self::normalize($concept);
            $best = self::matchConceptMap($normalized);

            if ($best) {
                $key = $best['cnae_code'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $results[] = array_merge($best, ['concept' => $concept, 'normalized' => $normalized]);
                }
            }
        }

        return $results;
    }

    /**
     * Score a company's CNAE code against resolved CNAE entries.
     * Returns ['score'=>int, 'match_level'=>string|null, 'matched_cnae'=>string|null]
     */
    public static function scoreCompanyAgainstCnaes(string $companyCnaeCode, array $resolvedCnaes): array {
        if (empty($resolvedCnaes) || empty(trim($companyCnaeCode))) {
            return ['score' => null, 'match_level' => null, 'matched_cnae' => null];
        }

        // Normalize company code
        $compCode = preg_replace('/[^0-9A-Z]/', '', strtoupper(trim($companyCnaeCode)));
        if (empty($compCode) || in_array($compCode, ['0000','00','####'])) {
            return ['score' => null, 'match_level' => null, 'matched_cnae' => null];
        }

        $bestScore = 0;
        $bestLevel = null;
        $bestCnae  = null;

        foreach ($resolvedCnaes as $entry) {
            $targetCode = preg_replace('/[^0-9A-Z]/', '', strtoupper($entry['cnae_code']));
            $matchLevel = self::computeHierarchyMatch($compCode, $targetCode);

            if ($matchLevel !== null) {
                $score = self::levelToScore($matchLevel);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestLevel = $matchLevel;
                    $bestCnae  = $entry['cnae_code'];
                }
            }
        }

        if ($bestScore === 0) {
            return ['score' => 20, 'match_level' => 'unrelated', 'matched_cnae' => null];
        }

        return ['score' => $bestScore, 'match_level' => $bestLevel, 'matched_cnae' => $bestCnae];
    }

    // --- Private Helpers ---

    public static function normalize(string $s): string {
        $s = mb_strtolower(trim($s), 'UTF-8');
        // Strip "CNAE XXXX - " prefix
        $s = preg_replace('/^cnae\s+[\w\.]+\s*[-–]\s*/u', '', $s);
        return trim($s);
    }

    private static function matchConceptMap(string $normalized): ?array {
        // Sort keys by length descending (longest match wins)
        static $sortedKeys = null;
        if ($sortedKeys === null) {
            $sortedKeys = array_keys(self::$conceptMap);
            usort($sortedKeys, fn($a, $b) => strlen($b) - strlen($a));
        }

        foreach ($sortedKeys as $key) {
            if (str_contains($normalized, $key)) {
                [$code, $label, $level] = self::$conceptMap[$key];
                return ['cnae_code' => $code, 'label' => $label, 'match_level' => $level, 'matched_concept' => $key];
            }
        }
        return null;
    }

    private static function computeHierarchyMatch(string $compCode, string $targetCode): ?string {
        // Handle letter sections (A-U)
        if (strlen($targetCode) === 1 && ctype_alpha($targetCode)) {
            $sectionMap = self::getSectionMap();
            if (isset($sectionMap[$targetCode])) {
                [$divStart, $divEnd] = $sectionMap[$targetCode];
                $compDiv = (int)substr($compCode, 0, 2);
                if ($compDiv >= $divStart && $compDiv <= $divEnd) {
                    return 'section';
                }
            }
            return null;
        }

        $tLen = strlen($targetCode);
        $cLen = strlen($compCode);

        // Exact match
        if ($compCode === $targetCode) {
            return self::codeLengthToLevel($tLen);
        }

        // Company code is more specific (longer); target is ancestor
        if ($tLen < $cLen && substr($compCode, 0, $tLen) === $targetCode) {
            return self::codeLengthToLevel($tLen);
        }

        // Target is more specific; company code is ancestor — treat as section-level match
        if ($tLen > $cLen && substr($targetCode, 0, $cLen) === $compCode) {
            return self::codeLengthToLevel($cLen);
        }

        return null;
    }

    private static function codeLengthToLevel(int $len): string {
        return match(true) {
            $len >= 4 => 'class',
            $len === 3 => 'group',
            $len === 2 => 'division',
            default   => 'section',
        };
    }

    private static function levelToScore(string $level): int {
        return match($level) {
            'class'    => 90,
            'group'    => 80,
            'division' => 70,
            'section'  => 60,
            default    => 20,
        };
    }

    private static function getSectionMap(): array {
        return [
            'A' => [1,  3],  'B' => [5,  9],  'C' => [10, 33],
            'D' => [35, 35], 'E' => [36, 39], 'F' => [41, 43],
            'G' => [45, 47], 'H' => [49, 53], 'I' => [55, 56],
            'J' => [58, 63], 'K' => [64, 66], 'L' => [68, 68],
            'M' => [69, 75], 'N' => [77, 82], 'O' => [84, 84],
            'P' => [85, 85], 'Q' => [86, 88], 'R' => [90, 93],
            'S' => [94, 96], 'T' => [97, 98], 'U' => [99, 99],
        ];
    }
}