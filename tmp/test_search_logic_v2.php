<?php
$c = mysqli_connect('localhost', 'root', '', 'apiempresas');
if ($c->connect_error) die("Connection failed: " . $c->connect_error);

$term = "THE GRILL IN LOVE MADRID SL";
$cleanTerm = preg_replace('/[+\-><()~*\"@]+/', ' ', $term);
$parts = array_filter(explode(' ', $cleanTerm));
$booleanTerm = '';
foreach ($parts as $p) {
    if (mb_strlen($p) >= 3) {
        $booleanTerm .= '+' . $p . '* ';
    }
}
$booleanTerm = trim($booleanTerm);

echo "Boolean Term: $booleanTerm\n";

// Fulltext Query
$sql = "SELECT id, company_name FROM companies WHERE MATCH(company_name, cnae_label, registro_mercantil) AGAINST ('$booleanTerm' IN BOOLEAN MODE) LIMIT 5";
$res = $c->query($sql);
if (!$res) {
    echo "Fulltext Error: " . $c->error . "\n";
} else {
    echo "Fulltext results for '$booleanTerm': " . $res->num_rows . "\n";
    while($row = $res->fetch_assoc()) echo "- " . $row['company_name'] . "\n";
}

// Check if "THE" is causing issue by trying without it
$booleanTerm2 = "+GRILL* +LOVE* +MADRID*";
echo "\nTrying without 'THE': $booleanTerm2\n";
$res2 = $c->query("SELECT id, company_name FROM companies WHERE MATCH(company_name, cnae_label, registro_mercantil) AGAINST ('$booleanTerm2' IN BOOLEAN MODE) LIMIT 5");
echo "Results for '$booleanTerm2': " . $res2->num_rows . "\n";
while($row = $res2->fetch_assoc()) echo "- " . $row['company_name'] . "\n";

// Broaden search to see if any of these words alone find it
foreach(['THE', 'GRILL', 'LOVE', 'MADRID'] as $w) {
    $resW = $c->query("SELECT id, company_name FROM companies WHERE MATCH(company_name, cnae_label, registro_mercantil) AGAINST ('+$w*' IN BOOLEAN MODE) AND id=2990614");
    echo "Check if '$w' matches ID 2990614: " . ($resW->num_rows > 0 ? "YES" : "NO") . "\n";
}
