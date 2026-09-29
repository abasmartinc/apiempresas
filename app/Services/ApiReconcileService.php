<?php

namespace App\Services;

/**
 * Nombre → CIF (29-09-2026): POST /api/v1/companies/reconcile.
 *
 * Para cada nombre busca candidatos con el índice FULLTEXT (CompanyModel::searchMany), los
 * puntúa con ApiVerifyService::nameScore (sin tildes, signos ni forma jurídica) y decide:
 *   match      el mejor tiene MATCH_MIN o más y le saca GAP_MIN al segundo (o la provincia desempata)
 *   ambiguous  hay candidatos plausibles (AMBIGUOUS_MIN o más) pero no uno claro
 *   no_match   ninguno llega a AMBIGUOUS_MIN
 * Pro y Business (y Free con saldo). Coste: 1 consulta por cada "match"; el resto, gratis.
 */
class ApiReconcileService
{
    public const MAX_ITEMS     = 100;
    public const MATCH_MIN     = 85;
    public const AMBIGUOUS_MIN = 70;
    public const GAP_MIN       = 10;
    public const CANDIDATES    = 10;
    public const SHOW          = 3;
    public const CACHE_TTL     = 86400;

    /**
     * Normaliza la entrada: {"items":[{"name":..,"province":..}]} o {"names":[..]}.
     * @return array{0: ?array, 1: ?string} [items, error]
     */
    public static function parseInput($json): array
    {
        if (!is_array($json)) {
            return [null, 'Envía un JSON con "items" ([{"name": "...", "province": "..."}]) o "names" (["..."]).'];
        }
        $raw = $json['items'] ?? null;
        if ($raw === null && isset($json['names']) && is_array($json['names'])) {
            $raw = array_map(fn($n) => ['name' => $n], $json['names']);
        }
        if (!is_array($raw) || !$raw) {
            return [null, 'Envía un JSON con "items" ([{"name": "...", "province": "..."}]) o "names" (["..."]).'];
        }
        if (count($raw) > self::MAX_ITEMS) {
            return [null, 'Máximo ' . self::MAX_ITEMS . ' nombres por petición.'];
        }
        $items = [];
        foreach (array_values($raw) as $it) {
            if (is_string($it)) {
                $it = ['name' => $it];
            }
            $name = is_array($it) ? trim((string) ($it['name'] ?? '')) : '';
            $prov = is_array($it) ? trim((string) ($it['province'] ?? '')) : '';
            $items[] = ['name' => $name, 'province' => $prov !== '' ? $prov : null];
        }
        return [$items, null];
    }

    /** Provincia comparable: mayúsculas y sin tildes; "Alicante/Alacant" cuenta como Alicante. */
    public static function normProvince(?string $p): string
    {
        $p = mb_strtoupper(trim((string) $p), 'UTF-8');
        $p = strtr($p, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
        $p = preg_replace('/\s*\/.*$/', '', $p); // "ALICANTE/ALACANT" → "ALICANTE"
        $alias = ['ALACANT' => 'ALICANTE', 'GIRONA' => 'GERONA', 'LLEIDA' => 'LERIDA', 'OURENSE' => 'ORENSE', 'A CORUNA' => 'LA CORUNA', 'CORUNA' => 'LA CORUNA', 'GIPUZKOA' => 'GUIPUZCOA', 'BIZKAIA' => 'VIZCAYA', 'ARABA' => 'ALAVA', 'ILLES BALEARS' => 'BALEARES', 'ISLAS BALEARES' => 'BALEARES'];
        return $alias[$p] ?? $p;
    }

    /**
     * Veredicto a partir de los candidatos ya puntuados. Pura: se prueba aparte.
     *
     * @param array $cands [['cif','name','province','score', ...], ...]
     * @return array{status:string, best:?array, candidates:array}
     */
    public static function decide(array $cands, ?string $province = null): array
    {
        usort($cands, fn($a, $b) => [$b['score'], $b['tiebreak'] ?? 0] <=> [$a['score'], $a['tiebreak'] ?? 0]);
        $plaus = array_values(array_filter($cands, fn($c) => $c['score'] >= self::AMBIGUOUS_MIN));
        if (!$plaus) {
            return ['status' => 'no_match', 'best' => null, 'candidates' => []];
        }

        // Con provincia: si alguno de los plausibles es de esa provincia, solo cuentan esos.
        if ($province !== null && $province !== '') {
            $np = self::normProvince($province);
            $enProv = array_values(array_filter($plaus, fn($c) => self::normProvince($c['province'] ?? '') === $np));
            if ($enProv) {
                $plaus = $enProv;
            }
        }

        $best = $plaus[0];
        $second = $plaus[1]['score'] ?? 0;
        if ($best['score'] >= self::MATCH_MIN && ($best['score'] - $second) >= self::GAP_MIN) {
            return ['status' => 'match', 'best' => $best, 'candidates' => []];
        }
        // Empate en la razón social normalizada (p. ej. "X SL" y "X SA"): decide la forma jurídica exacta.
        if ($best['score'] >= self::MATCH_MIN && ($best['tiebreak'] ?? 0) === 100 && (($plaus[1]['tiebreak'] ?? 0) < 100)) {
            return ['status' => 'match', 'best' => $best, 'candidates' => []];
        }
        return ['status' => 'ambiguous', 'best' => null, 'candidates' => array_slice($plaus, 0, self::SHOW)];
    }

    /** Puntúa candidatos para un nombre (score normalizado y desempate por nombre completo). */
    public static function score(string $name, array $rows): array
    {
        $full = self::fullName($name);
        $out = [];
        foreach ($rows as $r) {
            $n = (string) ($r['name'] ?? '');
            $out[] = $r + [
                'score'    => ApiVerifyService::nameScore($name, $n),
                'tiebreak' => self::fullName($n) === $full ? 100 : 0,
            ];
        }
        return $out;
    }

    private static function fullName(string $s): string
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
        return trim(preg_replace('/[^A-Z0-9]+/', '', $s));
    }

    /** Candidatos de la base para un nombre (caché de 24 h por nombre normalizado). */
    public static function candidates(string $name): array
    {
        $key = 'api_reconcile_cands_v1_' . md5(ApiVerifyService::normalizeName($name));
        $hit = cache()->get($key);
        if (is_array($hit)) {
            return $hit;
        }
        // Se busca con el nombre normalizado: searchMany exige cada palabra (+palabra en FULLTEXT)
        // y "S.L." o "S.A." como palabras obligatorias pueden dejar la búsqueda sin resultados.
        $term = self::searchTerm($name);
        if ($term === '') {
            return [];
        }
        $rows = (new \App\Models\CompanyModel())->searchMany($term, self::CANDIDATES, 1, false, true);
        $rows = ApiCompanyEnricher::withoutOptedOut($rows);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'       => (int) ($r['id'] ?? 0),
                'cif'      => $r['cif'] ?? null,
                'name'     => $r['name'] ?? null,
                'province' => $r['province'] ?? null,
                'status'   => $r['status'] ?? null,
            ];
        }
        cache()->save($key, $out, self::CACHE_TTL);
        return $out;
    }

    /** Palabras útiles para FULLTEXT: 3 letras o más y sin relleno (DE, Y, CIA...). */
    public static function searchTerm(string $name): string
    {
        $stop = ['DEL', 'LOS', 'LAS', 'CIA', 'COMPANIA', 'THE', 'AND', 'PARA', 'POR', 'CON'];
        $words = [];
        foreach (explode(' ', ApiVerifyService::normalizeName($name)) as $w) {
            if (strlen($w) >= 3 && !in_array($w, $stop, true)) {
                $words[] = $w;
            }
        }
        return implode(' ', array_slice($words, 0, 6));
    }

    /** Forma pública de un candidato. */
    public static function publicCandidate(array $c): array
    {
        return [
            'cif'         => $c['cif'] ?? null,
            'name'        => $c['name'] ?? null,
            'province'    => $c['province'] ?? null,
            'status'      => $c['status'] ?? null,
            'status_code' => $c['status_code'] ?? null,
            'score'       => (int) ($c['score'] ?? 0),
        ];
    }
}
