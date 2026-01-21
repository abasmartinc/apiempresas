<?php

function normalizeForSearch(string $s): string
{
    $s = mb_strtolower(trim($s), 'UTF-8');
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    $s = mb_strtolower($s, 'UTF-8');
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    $padded = ' ' . $s . ' ';
    $stop = [' sl ', ' s l ', ' sa ', ' s a ', ' slu ', ' s l u '];
    foreach ($stop as $w) {
        $padded = str_replace($w, ' ', $padded);
    }
    $padded = preg_replace('/\s+/', ' ', $padded);
    return trim($padded);
}

function tokenOverlapScore(string $needle, string $haystack): float
{
    $tokensA = array_filter(explode(' ', $needle), fn($t) => mb_strlen($t, 'UTF-8') >= 2);
    $tokensB = array_filter(explode(' ', $haystack), fn($t) => mb_strlen($t, 'UTF-8') >= 2);
    
    if (empty($tokensA) || empty($tokensB)) return 0.0;

    $matches = 0;
    foreach ($tokensA as $ta) {
        if (in_array($ta, $tokensB)) {
            $matches++;
        }
    }

    return ($matches / count($tokensA)) * 100;
}

function similarityScore(string $a, string $b): float
{
    if ($a === '' || $b === '') return 0.0;
    $pct = 0.0;
    similar_text($a, $b, $pct);
    $lev = levenshtein($a, $b);
    $maxLen = max(strlen($a), strlen($b));
    $levScore = $maxLen > 0 ? (1 - min($lev, $maxLen) / $maxLen) * 100 : 0;
    return ($pct * 0.75) + ($levScore * 0.25);
}

function checkMatch($qRaw, $cRaw) {
    $qClean = normalizeForSearch($qRaw);
    $nameNorm = normalizeForSearch($cRaw);

    $overlap = tokenOverlapScore($qClean, $nameNorm);
    
    if ($overlap < 50) {
        return "REJECTED (Overlap: $overlap% < 50%)";
    }

    $minScore = ($overlap < 100) ? 70 : 55;
    $score = similarityScore($qClean, $nameNorm);

    if ($score >= $minScore) {
        return "ACCEPTED (Score: $score >= $minScore, Overlap: $overlap%)";
    } else {
        return "REJECTED (Score: $score < $minScore, Overlap: $overlap%)";
    }
}

$cases = [
    ['Alessandro Lapo Morelli', 'ALESSANDRO IGNAZIO MARINO SL'], // BAD
    ['Alessandro Lapo', 'ALESSANDRO IGNAZIO MARINO'],           // BAD
    ['Alessandro Morelli', 'ALESSANDRO MORELLI SL'],            // GOOD
    ['Restaurante Pepe', 'RESTAURANTE PEPE HNOS'],              // GOOD
    ['Construcciones Garcia', 'CONSTRUCCIONES GARCIA SA'],      // GOOD
    ['Microsoft', 'MICROSOFT IBERICA SR'],                      // GOOD
];

foreach ($cases as $c) {
    echo "Q: '{$c[0]}' vs C: '{$c[1]}' => " . checkMatch($c[0], $c[1]) . "\n";
}
