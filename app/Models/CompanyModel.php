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
        'companies.registro_mercantil AS province',
        'companies.address',
        'companies.municipality',
        'companies.lat_num AS lat',
        'companies.lng_num AS lng',
        'companies.estado             AS status',
        'companies.phone',
        'companies.phone_mobile',
    ];

    public function getByCif(string $cif): ?array
    {
        $cif = trim($cif);
        if ($cif === '')
            return null;

        return $this->asArray()
            ->select(implode(', ', $this->selectFields))
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left')
            ->where('companies.cif', $cif)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function getById(int $id): ?array
    {
        return $this->asArray()
            ->select(implode(', ', $this->selectFields))
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left')
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
        return url_title($name, '-', true);
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

        // 1) Intento FULLTEXT (rápido)
        $fulltext = $this->tryFulltextBest($qClean);
        if ($fulltext !== null) {
            return $fulltext;
        }

        // 2) Fallback LIKE + scoring en PHP (más lento, pero acotado)
        return $this->fallbackBestByLike($qClean);
    }

    private function tryFulltextBest(string $qClean): ?array
    {
        $booleanQuery = $this->toBooleanPrefixQuery($qClean);

        // OJO: selectFields ya tiene aliases, lo podemos usar directamente en SQL
        $fields = implode(', ', $this->selectFields);

        $sql = "
            SELECT
                {$fields},
                MATCH(companies.company_name) AGAINST (? IN BOOLEAN MODE) AS score
            FROM {$this->table}
            LEFT JOIN cnae_2009_2025 ON cnae_2009_2025.cnae_2009 = companies.cnae_code
            WHERE companies.company_name IS NOT NULL
              AND MATCH(companies.company_name) AGAINST (? IN BOOLEAN MODE)
            ORDER BY score DESC
            LIMIT 1
        ";

        try {
            $row = $this->db->query($sql, [$booleanQuery, $booleanQuery])->getRowArray();
            if (!$row) {
                return null;
            }

            $rawScore = (float) ($row['score'] ?? 0.0);

            // Normalización simple del score para exponer 0..100
            $score01 = min(1.0, $rawScore / 5.0);
            $score100 = (int) round($score01 * 100);

            unset($row['score']);

            // Umbral mínimo para evitar devolver basura
            if ($score100 < 35) {
                return null;
            }

            return [
                'data' => $row,
                'meta' => [
                    'method' => 'fulltext',
                    'score' => $score100,
                ],
            ];
        } catch (\Throwable $e) {
            log_message('debug', '[CompanyModel::tryFulltextBest] ' . $e->getMessage());
            return null;
        }
    }

    private function fallbackBestByLike(string $qClean): ?array
    {
        $tokens = array_values(array_filter(explode(' ', $qClean)));
        $tokens = array_slice($tokens, 0, 4);

        $builder = $this->builder();
        $builder->select(implode(', ', $this->selectFields));
        $builder->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
        $builder->where('companies.company_name IS NOT NULL', null, false);

        // Filtro barato inicial (OR por tokens)
        $builder->groupStart();
        foreach ($tokens as $i => $t) {
            if (mb_strlen($t, 'UTF-8') < 3)
                continue;

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

        // Quitar acentos
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
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

    private function toBooleanPrefixQuery(string $qClean): string
    {
        $parts = array_values(array_filter(explode(' ', $qClean)));

        $tokens = [];
        foreach ($parts as $p) {
            if (strlen($p) >= 3)
                $tokens[] = $p;
        }
        if (empty($tokens)) {
            $tokens = $parts;
        }

        // "+token*" => requerido y prefijo
        $out = [];
        foreach (array_slice($tokens, 0, 6) as $t) {
            $out[] = '+' . $t . '*';
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

    public function getRelated(?string $cnae, ?string $province, string $excludeCif, int $limit = 20): array
    {
        $cnae = trim((string) $cnae);
        $province = trim((string) $province);

        if ($cnae === '' && $province === '') {
            return [];
        }

        $builder = $this->builder();
        $builder->select(implode(', ', $this->selectFields));
        $builder->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
        $builder->where('companies.cif !=', $excludeCif);

        // Prioridad: Mismo CNAE
        if ($cnae !== '') {
            $builder->where('companies.cnae_code', $cnae);
        }
        // Si no hay CNAE, usar provincia
        elseif ($province !== '') {
            $builder->where('companies.registro_mercantil', $province);
        }

        $builder->orderBy('id', 'DESC');
        $builder->limit($limit);

        $results = $builder->get()->getResultArray();

        // Si no encontramos suficientes por CNAE, rellenar con Provincia (si tenemos provincia)
        if (count($results) < $limit && $cnae !== '' && $province !== '') {
            $needed = $limit - count($results);

            // Nota: selectFields NO incluye ID por defecto, así que mejor excluimos por CIF que sí está
            $excludeCifs = array_column($results, 'cif');
            $excludeCifs[] = $excludeCif;

            $builder2 = $this->builder();
            $builder2->select(implode(', ', $this->selectFields));
            $builder2->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
            $builder2->whereNotIn('companies.cif', $excludeCifs);
            $builder2->where('companies.registro_mercantil', $province);
            $builder2->limit($needed);

            $more = $builder2->get()->getResultArray();
            $results = array_merge($results, $more);
        }

        return $results;
    }

    /**
     * Busca múltiples empresas por término (CIF, Nombre, CNAE o Provincia).
     * Prioriza CIF, luego FULLTEXT y finalmente LIKE.
     */
    public function searchMany(string $term, int $limit = 20): array
    {
        $term = trim($term);
        if ($term === '' || mb_strlen($term) < 2) {
            return [];
        }

        $results = [];
        $seenCifs = [];

        // 1. Priority 1: Búsqueda por CIF (Indexado, muy rápido)
        $builderCif = $this->builder();
        $builderCif->select(implode(', ', $this->selectFields));
        $builderCif->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
        $builderCif->like('companies.cif', $term, 'after');
        $builderCif->limit($limit);

        foreach ($builderCif->get()->getResultArray() as $row) {
            $results[] = $row;
            $seenCifs[$row['cif']] = true;
        }

        if (count($results) >= $limit) {
            return $results;
        }

        // 2. Priority 2: FULLTEXT por nombre y etiquetas (Muy rápida)
        try {
            $cleanTerm = preg_replace('/[+\-><()~*\"@]+/', ' ', $term);
            $parts = array_filter(explode(' ', $cleanTerm));
            $booleanTerm = '';
            foreach ($parts as $p) {
                if (mb_strlen($p) >= 3) {
                    $booleanTerm .= '+' . $p . '* ';
                }
            }
            $booleanTerm = trim($booleanTerm);

            if ($booleanTerm !== '') {
                $fields = implode(', ', $this->selectFields);
                $sql = "SELECT {$fields}, 
                        MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST (? IN BOOLEAN MODE) as score
                        FROM {$this->table}
                        LEFT JOIN cnae_2009_2025 ON cnae_2009_2025.cnae_2009 = companies.cnae_code
                        WHERE MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST (? IN BOOLEAN MODE)
                        ORDER BY score DESC
                        LIMIT ?";

                $query = $this->db->query($sql, [$booleanTerm, $booleanTerm, $limit]);
                foreach ($query->getResultArray() as $row) {
                    if (count($results) >= $limit)
                        break;
                    if (!isset($seenCifs[$row['cif']])) {
                        $results[] = $row;
                        $seenCifs[$row['cif']] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('debug', '[CompanyModel::searchMany] Fulltext falló: ' . $e->getMessage());
        }

        if (count($results) >= $limit) {
            return $results;
        }

        // 3. Fallback: B-Tree Index friendly LIKE (Evitando comodín inicial)
        if (mb_strlen($term) >= 3) {
            $builderFallback = $this->builder();
            $builderFallback->select(implode(', ', $this->selectFields));
            $builderFallback->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
            $builderFallback->like('companies.company_name', $term, 'after');

            if (!empty($seenCifs)) {
                $builderFallback->whereNotIn('companies.cif', array_keys($seenCifs));
            }

            $builderFallback->limit($limit - count($results));

            foreach ($builderFallback->get()->getResultArray() as $row) {
                if (count($results) >= $limit)
                    break;
                if (!isset($seenCifs[$row['cif']])) {
                    $results[] = $row;
                    $seenCifs[$row['cif']] = true;
                }
            }
        }

        return $results;
    }

    /**
     * Obtiene las últimas empresas constituidas.
     * Optimizado para tablas grandes: 2 pasos para evitar filesort masivo.
     */
    public function getLatestCompanies(int $limit = 10): array
    {
        // Paso 1: Obtener solo los IDs usando el índice de fecha_constitucion
        $idsRaw = $this->db->table($this->table)
            ->select('id')
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
            ->whereIn('companies.id', $ids)
            ->orderBy('companies.fecha_constitucion', 'DESC')
            ->orderBy('companies.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
