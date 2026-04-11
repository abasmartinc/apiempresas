<?php
$c = mysqli_connect('localhost', 'root', '', 'apiempresas');

$term = "THE GRILL IN LOVE MADRID SL";
$cleanTerm = preg_replace('/[+\-><()~*\"@]+/', ' ', $term);
$parts = array_filter(explode(' ', $cleanTerm));
$booleanTerm = '';
foreach ($parts as $p) {
    $len = mb_strlen($p, 'UTF-8');
    if ($len >= 4) {
        $booleanTerm .= '+' . $p . '* ';
    } elseif ($len >= 2) {
        $booleanTerm .= $p . '* ';
    }
}
$booleanTerm = trim($booleanTerm);

echo "New Boolean Term: $booleanTerm\n";

$sql = "SELECT id, company_name FROM companies WHERE MATCH(company_name, cnae_label, registro_mercantil) AGAINST ('$booleanTerm' IN BOOLEAN MODE) LIMIT 5";
$res = $c->query($sql);
if (!$res) die($c->error);

echo "Results found: " . $res->num_rows . "\n";
while($row = $res->fetch_assoc()) {
    echo "- " . $row['company_name'] . "\n";
}
