<?php
$c = mysqli_connect('localhost', 'root', '', 'apiempresas');

$name = "THE GRILL IN LOVE MADRID SL";
$qClean = mb_strtolower(trim($name), 'UTF-8');
$qClean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $qClean) ?: $qClean;
$qClean = mb_strtolower($qClean, 'UTF-8');
$qClean = preg_replace('/[^a-z0-9\s]/', ' ', $qClean);
$qClean = preg_replace('/\s+/', ' ', $qClean);

$parts = array_values(array_filter(explode(' ', $qClean)));
$tokens = [];
foreach ($parts as $p) {
    if (mb_strlen($p, 'UTF-8') >= 2) $tokens[] = $p;
}
$out = [];
foreach (array_slice($tokens, 0, 6) as $t) {
    $len = mb_strlen($t, 'UTF-8');
    if ($len >= 4) $out[] = '+' . $t . '*';
    else $out[] = $t . '*';
}
$booleanQuery = implode(' ', $out);

echo "Final Boolean Query: $booleanQuery\n";

$sql = "SELECT id, company_name FROM companies WHERE MATCH(company_name) AGAINST ('$booleanQuery' IN BOOLEAN MODE) LIMIT 5";
$res = $c->query($sql);
if (!$res) die($c->error);

echo "Results found: " . $res->num_rows . "\n";
while($row = $res->fetch_assoc()) {
    echo "- " . $row['company_name'] . "\n";
}
