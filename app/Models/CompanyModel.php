<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table      = 'empresia_company_details';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Campos a devolver (misma salida que antes)
     */
    private array $selectFields = [
        'company_name       AS name',
        'cif                AS cif',
        'cnae_code          AS cnae',
        'cnae_label         AS cnae_label',
        'objeto_social      AS corporate_purpose',
        'fecha_constitucion AS founded',
        'registro_mercantil AS province',
        'estado             AS status',
    ];

    public function getByCif(string $cif): ?array
    {
        $cif = trim($cif);
        if ($cif === '') return null;

        return $this->asArray()
            ->select(implode(', ', $this->selectFields))
            ->where('cif', $cif)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
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
        if ($name === '') return null;

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
                MATCH(company_name) AGAINST (? IN BOOLEAN MODE) AS score
            FROM {$this->table}
            WHERE company_name IS NOT NULL
              AND MATCH(company_name) AGAINST (? IN BOOLEAN MODE)
            ORDER BY score DESC
            LIMIT 1
        ";

        try {
            $row = $this->db->query($sql, [$booleanQuery, $booleanQuery])->getRowArray();
            if (!$row) {
                return null;
            }

            $rawScore = (float)($row['score'] ?? 0.0);

            // Normalización simple del score para exponer 0..100
            $score01  = min(1.0, $rawScore / 5.0);
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
                    'score'  => $score100,
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
        $builder->where('company_name IS NOT NULL', null, false);

        // Filtro barato inicial (OR por tokens)
        $builder->groupStart();
        foreach ($tokens as $i => $t) {
            if (mb_strlen($t, 'UTF-8') < 3) continue;

            if ($i === 0) {
                $builder->like('company_name', $t, 'both');
            } else {
                $builder->orLike('company_name', $t, 'both');
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
            $candidateName = (string)($row['name'] ?? '');
            $nameNorm = $this->normalizeForSearch($candidateName);

            $score = $this->similarityScore($qClean, $nameNorm); // 0..100

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        // Umbral para evitar resultados irrelevantes
        if ($best === null || $bestScore < 55) {
            return null;
        }

        return [
            'data' => $best,
            'meta' => [
                'method' => 'fallback',
                'score'  => (int) round($bestScore),
            ],
        ];
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
            if (strlen($p) >= 3) $tokens[] = $p;
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
        if ($a === '' || $b === '') return 0.0;

        $pct = 0.0;
        similar_text($a, $b, $pct);

        $lev = levenshtein($a, $b);
        $maxLen = max(strlen($a), strlen($b));
        $levScore = $maxLen > 0 ? (1 - min($lev, $maxLen) / $maxLen) * 100 : 0;

        return ($pct * 0.75) + ($levScore * 0.25);
    }
}
