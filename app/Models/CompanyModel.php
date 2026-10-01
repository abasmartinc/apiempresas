<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Campos a devolver (misma salida que antes)
     */
    private array $selectFields = [
        'companies.id                AS id',
        'companies.company_name       AS name',
        'companies.cif                AS cif',
        'companies.cnae_code          AS cnae',
        'companies.cnae_label         AS cnae_label',
        'cnae_2009_2025.cnae_2025  AS cnae_2025',
        'cnae_2009_2025.label_2025 AS cnae_2025_label',
        'companies.objeto_social      AS corporate_purpose',
        'companies.fecha_constitucion AS founded',
        'companies.capital_social_raw AS capital_social_raw',
        'companies.registro_mercantil AS province',
        'companies.address',
        'companies.municipality',
        'companies.lat_num AS lat',
        'companies.lng_num AS lng',
        'companies.estado             AS status',
        'companies.phone',
        'companies.phone_mobile',
        'companies.updated_at         AS updated_at',
        'company_enrichment.website_official',
        'company_enrichment.email',
        'company_enrichment.phone_enriched',
        'company_enrichment.phone_mobile_enriched',
        'company_enrichment.ai_seo_text',
        'company_enrichment.ai_faqs',
        'company_enrichment.ai_tags',
        'company_enrichment.ai_pitch',
        'company_enrichment.ai_borme_summary',
        'company_enrichment.notes         AS company_notes',
    ];

    /**
     * Proyección exclusiva para API (Optimización)
     * Elimina todos los blobs pesados de IA y PII que la API descarta.
     */
    public array $apiSelectFields = [
        'companies.id                AS id', // Requerido internamente para ?admin=true
        'companies.company_name       AS name',
        'companies.cif                AS cif',
        'companies.cnae_code          AS cnae',
        'companies.cnae_label         AS cnae_label',
        'cnae_2009_2025.cnae_2025  AS cnae_2025',
        'cnae_2009_2025.label_2025 AS cnae_2025_label',
        'companies.objeto_social      AS corporate_purpose',
        'companies.fecha_constitucion AS founded',
        'companies.capital_social_raw AS capital_social_raw',
        'companies.registro_mercantil AS province',
        'companies.address',
        'companies.municipality',
        'companies.lat_num AS lat',
        'companies.lng_num AS lng',
        'companies.estado             AS status',
    ];

    public function getByCif(string $cif, bool $forApi = false): ?array
    {
        $cif = strtoupper(trim($cif));
        if ($cif === '')
            return null;

        $builder = $this->asArray()
            ->select(implode(', ', $forApi ? $this->apiSelectFields : $this->selectFields))
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
            
        if (!$forApi) {
            $builder->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left');
        }

        $result = $builder->where('companies.cif', $cif)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;

        if ($result) {
            return $result;
        }

        return null;
    }

    public function getByCifs(array $cifs, bool $forApi = false): array
    {
        $cifs = array_map('trim', $cifs);
        $cifs = array_filter($cifs);
        if (empty($cifs)) {
            return [];
        }

        $builder = $this->asArray()
            ->select(implode(', ', $forApi ? $this->apiSelectFields : $this->selectFields))
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
            
        if (!$forApi) {
            $builder->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left');
        }

        return $builder->whereIn('companies.cif', $cifs)
            ->limit(100)
            ->get()
            ->getResultArray();
    }

    public function getById(int $id): ?array
    {
        return $this->asArray()
            ->select(implode(', ', $this->selectFields))
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left')
            ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left')
            ->where('companies.id', $id)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * Busca empresa por slug.
     * El slug se genera a partir del company_name.
     */
    public function getBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '')
            return null;

        // Convertir slug a nombre: "serviraibe-sl" -> "serviraibe sl"
        $searchName = str_replace('-', ' ', $slug);

        // Usar getBestByName para encontrar la mejor coincidencia
        $result = $this->getBestByName($searchName);

        if (!$result || !isset($result['data'])) {
            return null;
        }

        // Verificar que el slug generado coincida con el buscado
        $company = $result['data'];
        $generatedSlug = $this->generateSlug($company['name'] ?? '');

        // Permitir coincidencia exacta o muy cercana (para tolerancia)
        if ($generatedSlug === $slug || similar_text($generatedSlug, $slug) > (strlen($slug) * 0.9)) {
            return $company;
        }

        return null;
    }

    /**
     * Genera un slug único a partir del nombre de la empresa.
     */
    public function generateSlug(string $name): string
    {
        helper('text');
        $nameClean = str_replace(['º', 'ª'], ['o', 'a'], $name);
        return url_title($nameClean, '-', true);
    }

    /**
     * Best match por nombre.
     * Retorna:
     * [
     *   'data' => (row),
     *   'meta' => ['method' => 'fulltext|fallback', 'score' => 0..100]
     * ]
     */
    public function getBestByName(string $name): ?array
    {
        $name = trim($name);
        if ($name === '')
            return null;

        $qClean = $this->normalizeForSearch($name);
        if (mb_strlen($qClean, 'UTF-8') < 3) {
            return null;
        }

        // 0) Nombre que EMPIEZA por lo buscado: usa el índice normal de company_name y
        //    es casi instantáneo. El FULLTEXT tiene que puntuar todas las coincidencias
        //    (miles con "Telefónica": ~2-3 s), así que solo se usa si esto no da una
        //    sociedad con datos del Registro.
        $prefijo = $this->tryPrefixBest($qClean);
        if ($prefijo !== null) {
            return $prefijo;
        }

        // 1) Intento FULLTEXT
        $fulltext = $this->tryFulltextBest($qClean);
        if ($fulltext !== null) {
            return $fulltext;
        }

        // 3) Fallback LIKE + scoring en PHP (más lento, pero acotado)
        $likeResult = $this->fallbackBestByLike($qClean);
        if ($likeResult !== null) {
            return $likeResult;
        }

        return null;
    }

    /**
     * Sociedades cuyo nombre empieza por la búsqueda (LIKE 'texto%' sobre el índice
     * company_name; la colación es _unicode_ci, así que "telefonica" encuentra
     * "TELEFÓNICA"). De las más cortas a las más largas, y se elige con las mismas reglas
     * que el FULLTEXT. Solo devuelve algo si la elegida es una sociedad con datos del
     * Registro (nivel 0); si no, que decida el FULLTEXT.
     */
    private function tryPrefixBest(string $qClean): ?array
    {
        if (mb_strlen($qClean, 'UTF-8') < 4 || !preg_match('/^[a-z0-9 ]+$/', $qClean)) {
            return null;
        }

        try {
            $rows = $this->db->query("
                SELECT id, company_name AS name, cif, registro_mercantil AS province,
                       fecha_constitucion AS founded, estado AS status
                FROM {$this->table}
                WHERE company_name LIKE ?
                LIMIT 300
            ", [$qClean . '%'])->getResultArray();
            if (!$rows) {
                return null;
            }
            // Sin ORDER BY en SQL: con un prefijo muy común ("construcciones") ordenar
            // por longitud obligaría a leer decenas de miles de filas. Se toman 300 en el
            // orden del índice y se ordenan aquí.
            usort($rows, static fn ($a, $b) => mb_strlen((string) $a['name'], 'UTF-8') <=> mb_strlen((string) $b['name'], 'UTF-8'));
            $rows = array_slice($rows, 0, 25);
            foreach ($rows as &$r) {
                $r['score'] = 1.0; // todas empatan: deciden el nivel y el nombre más corto
            }
            unset($r);

            $elegida = $this->pickBestCandidate($rows, $qClean);
            if ($this->nivelCandidato($elegida, $qClean) !== 0) {
                return null;
            }

            $row = $this->db->table($this->table)
                ->select(implode(', ', $this->selectFields))
                ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left')
                ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left')
                ->where('companies.id', (int) $elegida['id'])
                ->limit(1)
                ->get()->getRowArray();
            if (!$row) {
                return null;
            }

            return ['data' => $row, 'meta' => ['method' => 'prefix', 'score' => 100]];
        } catch (\Throwable $e) {
            log_message('error', '[CompanyModel::tryPrefixBest] ' . $e->getMessage());
            return null;
        }
    }

    private function tryFulltextBest(string $qClean): ?array
    {
        $booleanQuery = $this->toBooleanPrefixQuery($qClean);

        // Dos pasos. 1) Candidatos solo con lo necesario para elegir (sin JOIN ni
        // columnas de texto largo): antes se ordenaban TODAS las coincidencias con la
        // ficha completa (objeto social, texto SEO, FAQ...) y con "Telefónica" (miles de
        // filas) la consulta pasaba de 6 s. 2) La ficha completa solo de la elegida.
        $sql = "
            SELECT
                companies.id                 AS id,
                companies.company_name       AS name,
                companies.cif                AS cif,
                companies.registro_mercantil AS province,
                companies.fecha_constitucion AS founded,
                companies.estado             AS status,
                MATCH(companies.company_name) AGAINST (? IN BOOLEAN MODE) AS score
            FROM {$this->table}
            WHERE MATCH(companies.company_name) AGAINST (? IN BOOLEAN MODE)
              AND companies.company_name IS NOT NULL
            ORDER BY score DESC
            LIMIT 25
        ";

        try {
            // Primero las palabras exactas, que es una búsqueda directa en el índice;
            // el comodín (telefonica*) obliga a recorrer todas las palabras que empiezan
            // así y es lo lento (unos 2 s con "Telefónica"). Solo si con las exactas no
            // sale nada se prueba con comodín (nombres a medio escribir, plurales).
            $exacta = $this->toBooleanPrefixQuery($qClean, false);
            $rows = $this->db->query($sql, [$exacta, $exacta])->getResultArray();
            if (!$rows && $exacta !== $booleanQuery) {
                $rows = $this->db->query($sql, [$booleanQuery, $booleanQuery])->getResultArray();
            }
            if (!$rows) {
                return null;
            }

            // El umbral se mira sobre la MEJOR puntuación (como antes, cuando solo se
            // pedía una fila): decide si la búsqueda ha encontrado algo. La fila elegida
            // puede puntuar algo menos (p. ej. la sociedad frente a su UTE) y no por eso
            // la búsqueda es basura.
            $maxRaw = 0.0;
            foreach ($rows as $r) {
                $maxRaw = max($maxRaw, (float) ($r['score'] ?? 0.0));
            }
            if ((int) round(min(1.0, $maxRaw / 5.0) * 100) < 35) {
                return null;
            }

            $elegida  = $this->pickBestCandidate($rows, $qClean);
            $rawScore = (float) ($elegida['score'] ?? 0.0);

            $row = $this->db->table($this->table)
                ->select(implode(', ', $this->selectFields))
                ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left')
                ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left')
                ->where('companies.id', (int) $elegida['id'])
                ->limit(1)
                ->get()->getRowArray();
            if (!$row) {
                return null;
            }

            // Normalización simple del score para exponer 0..100
            $score01 = min(1.0, $rawScore / 5.0);
            $score100 = (int) round($score01 * 100);

            unset($row['score']);

            return [
                'data' => $row,
                'meta' => [
                    'method' => 'fulltext',
                    'score' => $score100,
                ],
            ];
        } catch (\Throwable $e) {
            // 'error' y no 'debug': si esto falla, la búsqueda cae al LIKE, que es lento
            log_message('error', '[CompanyModel::tryFulltextBest] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Elige el mejor candidato del FULLTEXT sin fiarse solo de la puntuación.
     *
     * Con "Telefónica" la puntuación más alta era una UTE vacía (U75760280): el
     * nombre repite la palabra y MATCH la premia. Se ordena primero por lo fiable
     * que es la ficha y después por puntuación y por nombre más corto (el nombre
     * más corto suele ser la sociedad y no sus filiales o UTE):
     *   0  CIF de sociedad válido con datos del Registro
     *   1  CIF de sociedad válido sin datos del Registro
     *   2  UTE (U...), CIF enmascarado o que no es un CIF, salvo que se busque "UTE"
     * El umbral de 35 se aplica después sobre la fila elegida.
     */
    private function pickBestCandidate(array $rows, string $qClean): array
    {
        $nivel = fn (array $r): int => $this->nivelCandidato($r, $qClean);

        $maxScore = (float) ($rows[0]['score'] ?? 0.0);
        usort($rows, static function (array $a, array $b) use ($nivel): int {
            return [$nivel($a), -(float) ($a['score'] ?? 0), mb_strlen((string) ($a['name'] ?? ''), 'UTF-8')]
                <=> [$nivel($b), -(float) ($b['score'] ?? 0), mb_strlen((string) ($b['name'] ?? ''), 'UTF-8')];
        });
        $best = $rows[0];

        // Si la elegida puntúa menos de la mitad que la mejor, la búsqueda iba por
        // otra ficha: gana la de más puntuación, pero nunca una UTE ni un CIF roto
        // (nivel 2) si hay alternativa.
        // (0.45 y no 0.5: una palabra repetida en el nombre duplica justo la puntuación,
        // que es el caso de las UTE, y no debe colarse por un redondeo)
        if ($maxScore > 0 && (float) ($best['score'] ?? 0) < $maxScore * 0.45) {
            foreach ($rows as $r) {
                if ($nivel($r) < 2 && (float) ($r['score'] ?? 0) > (float) ($best['score'] ?? 0)) {
                    $best = $r;
                }
            }
        }

        return $best;
    }

    /** Nivel de fiabilidad de una ficha candidata (ver pickBestCandidate). */
    private function nivelCandidato(array $r, string $qClean): int
    {
        $cif = strtoupper(trim((string) ($r['cif'] ?? '')));
        if (!preg_match('/^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/', $cif)) {
            return 2;
        }
        if ($cif[0] === 'U') {
            // Las UTE no están en el Registro: solo cuentan si se busca "UTE"
            return preg_match('/\\bute\\b/i', $qClean) ? 0 : 2;
        }
        $hayRegistro = trim((string) ($r['province'] ?? '')) !== ''
            || !empty($r['founded'])
            || trim((string) ($r['status'] ?? '')) !== '';
        return $hayRegistro ? 0 : 1;
    }

    private function fallbackBestByLike(string $qClean): ?array
    {
        // Desactivado (01-10-2026). Era un LIKE '%palabra%' sin índice sobre 8,5 M de
        // filas: cuando no encontraba nada recorría la tabla entera (13 s con "BAUMER
        // AUTOMACION IBERICA S.L.U."). El prefijo y el FULLTEXT ya cubren lo que encuentra
        // por palabras enteras o por el principio; lo que solo hallaba esto (trozos del
        // medio de una palabra) no compensa ese coste. Se deja el código por si se quiere
        // recuperar con un límite de tiempo.
        if (true) {
            return null;
        }

        $tokens = array_values(array_filter(explode(' ', $qClean)));

        // Filtrar primero por longitud mínima (3 caracteres) para evitar grupos vacíos ()
        $validTokens = array_filter($tokens, fn($t) => mb_strlen($t, 'UTF-8') >= 3);
        $validTokens = array_values(array_slice($validTokens, 0, 4));

        if (empty($validTokens)) {
            return null;
        }

        $builder = $this->builder();
        $builder->select(implode(', ', $this->selectFields));
        $builder->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
        $builder->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left');
        $builder->where('companies.company_name IS NOT NULL', null, false);

        // Filtro barato inicial (OR por tokens)
        $builder->groupStart();
        foreach ($validTokens as $i => $t) {
            if ($i === 0) {
                $builder->like('companies.company_name', $t, 'both');
            } else {
                $builder->orLike('companies.company_name', $t, 'both');
            }
        }
        $builder->groupEnd();

        $builder->limit(40);

        $candidates = $builder->get()->getResultArray();
        if (empty($candidates)) {
            return null;
        }

        $best = null;
        $bestScore = -1.0;

        // Como ya seleccionamos con alias, el nombre viene como "name"
        foreach ($candidates as $row) {
            $candidateName = (string) ($row['name'] ?? '');
            $nameNorm = $this->normalizeForSearch($candidateName);

            // 1) Calcular overlap de tokens (0..100)
            $overlap = $this->tokenOverlapScore($qClean, $nameNorm);

            // REGLA 1: Si no hay al menos la mitad de tokens coincidentes, descartar
            // Evita que "Alessandro" haga match con "Alessandro Ignazio..." si busco "Alessandro Lapo Morelli"
            if ($overlap < 50) {
                continue;
            }

            // REGLA 2: Umbral adaptativo
            // Si el overlap no es total, exigimos más similitud visual (70)
            // Si el overlap es total (todas mis palabras están), somos más tolerantes (55 - igual que antes)
            $minScore = ($overlap < 100) ? 70 : 55;

            $score = $this->similarityScore($qClean, $nameNorm); // 0..100

            if ($score >= $minScore && $score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        if ($best === null) {
            return null;
        }

        return [
            'data' => $best,
            'meta' => [
                'method' => 'fallback',
                'score' => (int) round($bestScore),
            ],
        ];
    }

    /**
     * Calcula qué porcentaje de tokens de $needle están presentes en $haystack
     */
    private function tokenOverlapScore(string $needle, string $haystack): float
    {
        $tokensA = array_filter(explode(' ', $needle), fn($t) => mb_strlen($t, 'UTF-8') >= 2);
        $tokensB = array_filter(explode(' ', $haystack), fn($t) => mb_strlen($t, 'UTF-8') >= 2);

        if (empty($tokensA) || empty($tokensB))
            return 0.0;

        $matches = 0;
        foreach ($tokensA as $ta) {
            if (in_array($ta, $tokensB)) {
                $matches++;
            }
        }

        return ($matches / count($tokensA)) * 100;
    }

    private function normalizeForSearch(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');

        // Quitar acentos con una tabla fija. Antes iconv(ASCII//TRANSLIT), que depende
        // del sistema: en Windows (Laragon) "ó" sale como "'o" y "Telefónica" acababa en
        // "telef onica", que no encuentra nada, cae al LIKE (lento) y da 404.
        $s = strtr($s, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c', 'ª' => 'a', 'º' => 'o', 'l·l' => 'll',
        ]);
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s; // por si queda algo raro
        $s = mb_strtolower($s, 'UTF-8');

        // Quitar signos
        $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        // Opcional: limpia sufijos típicos
        $padded = ' ' . $s . ' ';
        $stop = [' sl ', ' s l ', ' sa ', ' s a ', ' slu ', ' s l u '];
        foreach ($stop as $w) {
            $padded = str_replace($w, ' ', $padded);
        }

        $padded = preg_replace('/\s+/', ' ', $padded);
        return trim($padded);
    }

    private function toBooleanPrefixQuery(string $qClean, bool $comodin = true): string
    {
        $parts = array_values(array_filter(explode(' ', $qClean)));

        $tokens = [];
        foreach ($parts as $p) {
            if (mb_strlen($p, 'UTF-8') >= 2)
                $tokens[] = $p;
        }
        if (empty($tokens)) {
            $tokens = $parts;
        }

        // "+token*" => requerido y prefijo
        $out = [];
        foreach (array_slice($tokens, 0, 6) as $t) {
            $len = mb_strlen($t, 'UTF-8');
            $fin = $comodin ? '*' : '';
            if ($len >= 4) {
                $out[] = '+' . $t . $fin;
            } else {
                $out[] = $t . $fin; // Not required if short/stopword
            }
        }
        return implode(' ', $out);
    }

    private function similarityScore(string $a, string $b): float
    {
        if ($a === '' || $b === '')
            return 0.0;

        $pct = 0.0;
        similar_text($a, $b, $pct);

        $lev = levenshtein($a, $b);
        $maxLen = max(strlen($a), strlen($b));
        $levScore = $maxLen > 0 ? (1 - min($lev, $maxLen) / $maxLen) * 100 : 0;

        return ($pct * 0.75) + ($levScore * 0.25);
    }

    /**
     * Empresas relacionadas, de más a menos cercana.
     *
     * Orden (24-09-2026):
     *   1. Mismo CNAE y misma provincia.
     *   2. Mismo CNAE en el resto de España.
     *   3. Misma provincia, cualquier sector.
     *
     * Antes el paso 1 no existía: con CNAE se buscaba en toda España ordenando por
     * id, así que a una asesoría de Valencia le salían asesorías de cualquier sitio
     * y casi nunca una de su provincia, que es lo primero que busca quien compara.
     *
     * Sin CNAE (o con uno descartado por company_cnae_fiable) se va directo al 3.
     */
    public function getRelated(?string $cnae, ?string $province, string $excludeCif, int $limit = 20): array
    {
        $cnae = trim((string) $cnae);
        $province = trim((string) $province);

        if ($cnae === '' && $province === '') {
            return [];
        }

        // Alicante está guardada con los dos nombres en registro_mercantil.
        $provincias = ($province !== '' && strcasecmp($province, 'Alicante') === 0)
            ? ['Alicante', 'Alicante/Alacant']
            : ($province !== '' ? [$province] : []);

        $results = [];
        $vistos  = [$excludeCif];

        $pedir = function (?string $conCnae, array $enProvincias, int $cuantos) use (&$vistos): array {
            if ($cuantos <= 0) {
                return [];
            }
            $builder = $this->builder();
            $builder->select(implode(', ', $this->selectFields));
            $builder->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
            $builder->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left');
            $builder->whereNotIn('companies.cif', $vistos);
            if ($conCnae !== null) {
                $builder->where('companies.cnae_code', $conCnae);
            }
            if (!empty($enProvincias)) {
                $builder->whereIn('companies.registro_mercantil', $enProvincias);
            }
            $builder->orderBy('companies.id', 'DESC');
            $builder->limit($cuantos);

            $filas = $builder->get()->getResultArray();
            foreach ($filas as $f) {
                if (!empty($f['cif'])) {
                    $vistos[] = $f['cif'];
                }
            }
            return $filas;
        };

        if ($cnae !== '' && !empty($provincias)) {
            $results = array_merge($results, $pedir($cnae, $provincias, $limit));
        }
        if ($cnae !== '') {
            $results = array_merge($results, $pedir($cnae, [], $limit - count($results)));
        }
        if (!empty($provincias)) {
            $results = array_merge($results, $pedir(null, $provincias, $limit - count($results)));
        }

        return $results;
    }

    /**
     * Busca múltiples empresas por término (CIF, Nombre, CNAE o Provincia).
     * Prioriza CIF, luego FULLTEXT y finalmente LIKE.
     */
    public function searchMany(string $term, int $limit = 20, int $page = 1, bool $returnMeta = false, bool $forApi = false): array
    {
        $term = trim($term);
        if ($term === '' || mb_strlen($term) < 2) {
            return $returnMeta ? ['data' => [], 'meta' => ['page' => $page, 'limit' => $limit, 'has_more' => false]] : [];
        }

        $results = [];
        $seenCifs = [];
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        // Fetch one extra item to determine has_more
        $fetchLimit = $limit > 0 ? $limit + ($returnMeta ? 1 : 0) : 0;
        $targetFetch = $fetchLimit > 0 ? $offset + $fetchLimit : 0;
        $hasLimit = $targetFetch > 0;

        // 1. Priority 1: Búsqueda por CIF (Indexado, muy rápido)
        $builderCif = $this->builder();
        $builderCif->select('companies.id, companies.cif');
        $builderCif->like('companies.cif', $term, 'after');
        if ($hasLimit) {
            $builderCif->limit($targetFetch);
        }

        foreach ($builderCif->get()->getResultArray() as $row) {
            $results[] = $row;
            $seenCifs[$row['cif']] = true;
        }

        // 2. Nombres que EMPIEZAN por el término (índice company_name, sin ordenar: es una
        //    lectura de rango y vuelve en milisegundos). Es lo que espera quien escribe
        //    "Telef" o "Mari" en un buscador, y evita el FULLTEXT con comodín, que tiene que
        //    puntuar todas las coincidencias ("Can": 24 s, "Mari": 20 s en septiembre).
        if (mb_strlen($term, 'UTF-8') >= 2 && (!$hasLimit || count($results) < $targetFetch)) {
            try {
                $builderPrefijo = $this->builder();
                $builderPrefijo->select('companies.id, companies.cif');
                $builderPrefijo->like('companies.company_name', $term, 'after');
                if ($hasLimit) {
                    $builderPrefijo->limit($targetFetch);
                }
                foreach ($builderPrefijo->get()->getResultArray() as $row) {
                    if ($hasLimit && count($results) >= $targetFetch) {
                        break;
                    }
                    $clave = $row['cif'] ?: ('id:' . $row['id']);
                    if (!isset($seenCifs[$clave])) {
                        $results[] = $row;
                        $seenCifs[$clave] = true;
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', '[CompanyModel::searchMany] Prefijo falló: ' . $e->getMessage());
            }
        }

        // 3. FULLTEXT (palabras sueltas en cualquier posición del nombre, CNAE o
        //    provincia). Solo si faltan resultados y el término tiene alguna palabra de 4
        //    letras o más: con palabras cortas el índice devuelve demasiado y tarda.
        $tienePalabraLarga = (bool) preg_match('/[\p{L}\p{N}]{4,}/u', $term);
        if ($tienePalabraLarga && (!$hasLimit || count($results) < $targetFetch)) {
            try {
                $cleanTerm = preg_replace('/[+\-><()~*\"@]+/', ' ', $term);
                $parts = array_filter(explode(' ', $cleanTerm));
                $booleanTerm = '';
                foreach ($parts as $p) {
                    $len = mb_strlen($p, 'UTF-8');
                    if ($len >= 4) {
                        $booleanTerm .= '+' . $p . '* ';
                    } elseif ($len >= 2) {
                        $booleanTerm .= '+' . $p . ' '; // Solo coincidencia exacta para palabras cortas
                    }
                }
                $booleanTerm = trim($booleanTerm);

                if ($booleanTerm !== '') {
                    $sql = "SELECT companies.id, companies.cif, 
                            MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST (? IN BOOLEAN MODE) as score
                            FROM {$this->table}
                            WHERE MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST (? IN BOOLEAN MODE)
                            ORDER BY score DESC";

                    // Primero las palabras exactas (búsqueda directa en el índice) y solo si
                    // no llega, con comodín (recorre todas las palabras que empiezan así)
                    $exacto = trim(str_replace('* ', ' ', $booleanTerm . ' '));
                    $filasFt = [];
                    foreach (array_unique([$exacto, $booleanTerm]) as $intento) {
                        $q = $sql . ($hasLimit ? ' LIMIT ' . (int) $targetFetch : '');
                        $filasFt = $this->db->query($q, [$intento, $intento])->getResultArray();
                        if (!$hasLimit || count($results) + count($filasFt) >= $targetFetch) {
                            break;
                        }
                    }

                    foreach ($filasFt as $row) {
                        if ($hasLimit && count($results) >= $targetFetch)
                            break;
                        $clave = $row['cif'] ?: ('id:' . $row['id']);
                        if (!isset($seenCifs[$clave])) {
                            $results[] = $row;
                            $seenCifs[$clave] = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', '[CompanyModel::searchMany] Fulltext falló: ' . $e->getMessage());
            }
        }

        // (El antiguo paso 3, LIKE 'término%', es ahora el paso 2.)
        if (false) {
            if (mb_strlen($term) >= 3) {
                $builderFallback = $this->builder();
                $builderFallback->select('companies.id, companies.cif');
                $builderFallback->like('companies.company_name', $term, 'after'); // Cambiado a 'after' para usar B-Tree Index

                if (!empty($seenCifs)) {
                    $builderFallback->whereNotIn('companies.cif', array_keys($seenCifs));
                }

                if ($hasLimit) {
                    $builderFallback->limit($targetFetch - count($results));
                }

                foreach ($builderFallback->get()->getResultArray() as $row) {
                    if ($hasLimit && count($results) >= $targetFetch)
                        break;
                    if (!isset($seenCifs[$row['cif']])) {
                        $results[] = $row;
                        $seenCifs[$row['cif']] = true;
                    }
                }
            }
        }

        if ($offset > 0) {
            $results = array_slice($results, $offset, $fetchLimit);
        } else if ($hasLimit) {
            $results = array_slice($results, 0, $fetchLimit);
        }

        $finalData = [];
        if (!empty($results)) {
            $ids = array_column($results, 'id');

            $builderData = $this->builder();
            $builderData->select(implode(', ', $forApi ? $this->apiSelectFields : $this->selectFields));
            $builderData->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
            if (!$forApi) {
                $builderData->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left');
            }
            $builderData->whereIn('companies.id', $ids);

            $fetchedRows = $builderData->get()->getResultArray();

            // Reordenar para que coincidan con el orden de búsqueda original (que respeta el score)
            $rowMap = [];
            foreach ($fetchedRows as $row) {
                $rowMap[$row['id']] = $row;
            }

            foreach ($ids as $id) {
                if (isset($rowMap[$id])) {
                    $finalData[] = $rowMap[$id];
                }
            }
        }

        if ($returnMeta) {
            $hasMore = count($results) > $limit;
            if ($hasMore) {
                array_pop($finalData);
            }
            return [
                'data' => $finalData,
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'has_more' => $hasMore
                ]
            ];
        }

        return $finalData;
    }

    /**
     * Obtiene las últimas empresas constituidas.
     * Optimizado para tablas grandes: 2 pasos para evitar filesort masivo.
     */
    public function getLatestCompanies(int $limit = 10): array
    {
        $today = date('Y-m-d');

        // Paso 1: Obtener solo los IDs usando el índice de fecha_constitucion
        $idsRaw = $this->db->table($this->table)
            ->select('id')
            ->where('fecha_constitucion IS NOT NULL')
            ->where('fecha_constitucion >=', '1900-01-01') // Ignorar fechas inválidas o 0000-00-00
            ->where('fecha_constitucion <=', $today) // Excluir fechas futuras
            ->orderBy('fecha_constitucion', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $ids = array_column($idsRaw, 'id');

        if (empty($ids)) {
            return [];
        }

        // Paso 2: Traer los datos completos solo de esos IDs
        return $this->asArray()
            ->select(implode(', ', $this->selectFields))
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left')
            ->join('company_enrichment', 'company_enrichment.company_id = companies.id', 'left')
            ->whereIn('companies.id', $ids)
            ->orderBy('companies.fecha_constitucion', 'DESC')
            ->orderBy('companies.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
