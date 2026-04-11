<?php
require_once __DIR__ . '/../vendor/autoload.php';
// Bootstrapping CI4 without system/bootstrap if it's new version
define('FCPATH', __DIR__ . '/../public/');
require __DIR__ . '/../app/Config/Paths.php';
$paths = new \Config\Paths();
require __DIR__ . '/../system/Constants.php';
require __DIR__ . '/../system/Common.php';

// Manual mock of what matters
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

$c = mysqli_connect('localhost', 'root', '', 'apiempresas');
$sql = "SELECT id, company_name FROM companies WHERE MATCH(company_name, cnae_label, registro_mercantil) AGAINST ('$booleanTerm' IN BOOLEAN MODE) LIMIT 5";
$res = $c->query($sql);
if (!$res) echo "Fulltext Error: " . $c->error . "\n";
else {
    echo "Fulltext results: " . $res->num_rows . "\n";
    while($row = $res->fetch_assoc()) echo "- " . $row['company_name'] . "\n";
}

// Try WITHOUT "THE"
$booleanTerm2 = "+GRILL* +LOVE* +MADRID*";
echo "\nTrying without THE: $booleanTerm2\n";
$res2 = $c->query("SELECT id, company_name FROM companies WHERE MATCH(company_name, cnae_label, registro_mercantil) AGAINST ('$booleanTerm2' IN BOOLEAN MODE) LIMIT 5");
echo "Results without THE: " . $res2->num_rows . "\n";
while($row = $res2->fetch_assoc()) echo "- " . $row['company_name'] . "\n";
