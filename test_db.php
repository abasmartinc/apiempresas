<?php
$mysqli = new mysqli("217.61.210.127", "apiempresas_user", "WONwyjpsmx3h3$@2", "reseller3537_apiempresas");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$updates = [
    'A Coruña' => ['CORUÑA, A', 'LA CORUÑA'],
    'Alicante' => ['ALACANT', 'ALICANTE/ALACANT'],
    'Álava' => ['ÁLAVA', 'Araba/Álava', 'Álava-Araba'],
    'Islas Baleares' => ['Illes Balears', 'BALEARS, ILLES'],
    'Castellón' => ['CASTELLÓN/CASTELLÓ'],
    'Girona' => ['GERONA'],
    'Guipúzcoa' => ['GIPUZKOA', 'Guipúzcoa-Gipuzkoa'],
    'Lleida' => ['LERIDA'],
    'Ourense' => ['ORENSE', 'OURENSE'],
    'Las Palmas' => ['PALMAS, LAS'],
    'La Rioja' => ['RIOJA, LA'],
    'Santa Cruz de Tenerife' => ['STA. CRUZ DE TENERIFE'],
    'Valencia' => ['VALENCIA/VALÈNCIA'],
    'Vizcaya' => ['BIZKAIA', 'Vizcaya-Bizkaia']
];

$totalUpdated = 0;

foreach ($updates as $target => $variants) {
    $escapedTarget = $mysqli->real_escape_string($target);
    $escapedVariants = array_map(function($v) use ($mysqli) {
        return "'" . $mysqli->real_escape_string($v) . "'";
    }, $variants);
    
    $inClause = implode(',', $escapedVariants);
    $query = "UPDATE companies SET registro_mercantil = '$escapedTarget' WHERE registro_mercantil IN ($inClause)";
    
    if ($mysqli->query($query)) {
        $affected = $mysqli->affected_rows;
        $totalUpdated += $affected;
        echo "Updated $affected rows to '$target'\n";
    } else {
        echo "Error updating to '$target': " . $mysqli->error . "\n";
    }
}

echo "Total rows updated: $totalUpdated\n";


$mysqli->close();
