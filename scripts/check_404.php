<?php
$host = '217.61.210.127';
$db   = 'reseller3537_apiempresas';
$user = 'apiempresas_user';
$pass = 'WONwyjpsmx3h3$@2';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$emails = [
    'compras@fenixia.tech',
    'vlliso@gmail.com',
    'rarias@gemicar.net',
    'ajalvarez@ascendiarc.com',
    'contabilidad@aiautomatiza.com',
    'wojciech.bublik@pragmago.es'
];

$in  = str_repeat('?,', count($emails) - 1) . '?';

$sql = "SELECT id, email FROM users WHERE email IN ($in)";
$stmt = $pdo->prepare($sql);
$stmt->execute($emails);
$users = $stmt->fetchAll();

echo "Analizando 404s para usuarios cancelados...\n\n";

$all_valid_cifs_not_found = [];

function isValidCif($cif) {
    $cif = strtoupper(trim($cif));
    // Valid CIF: Letter followed by 7 digits and 1 letter/digit
    // Valid NIF: 8 digits followed by 1 letter
    // Valid NIE: X, Y, Z followed by 7 digits and 1 letter
    if (preg_match('/^[A-Z0-9]{9}$/', $cif)) {
        return true;
    }
    return false;
}

foreach ($users as $u) {
    echo "==========================================================\n";
    echo "Usuario: {$u['email']} (ID: {$u['id']})\n";
    echo "==========================================================\n";
    
    // fetch 404s
    $sql_api = "SELECT endpoint, search_term, status_code FROM api_requests WHERE user_id = ? AND status_code = 404";
    $stmt_api = $pdo->prepare($sql_api);
    $stmt_api->execute([$u['id']]);
    $requests = $stmt_api->fetchAll();
    
    $total_404 = count($requests);
    $invalid_format_count = 0;
    $valid_format_count = 0;
    $valid_cifs_this_user = [];
    
    foreach ($requests as $req) {
        $term = $req['search_term'];
        
        // Sometimes the search_term might be empty and the CIF is in the endpoint
        if (empty($term) && preg_match('/\/api\/companies\/([A-Za-z0-9]+)/', $req['endpoint'], $matches)) {
            $term = $matches[1];
        }
        
        $term = trim($term);
        if (empty($term)) {
            $invalid_format_count++;
            continue;
        }
        
        if (isValidCif($term)) {
            $valid_format_count++;
            $valid_cifs_this_user[] = strtoupper($term);
            $all_valid_cifs_not_found[] = strtoupper($term);
        } else {
            $invalid_format_count++;
        }
    }
    
    echo "Total 404s: $total_404\n";
    echo "  -> Formato VÁLIDO (CIF/NIF correcto pero no encontrado): $valid_format_count\n";
    echo "  -> Formato INVÁLIDO (texto, nombres, incompleto): $invalid_format_count\n";
    
    if ($invalid_format_count > 0) {
        // Show some examples of invalid format
        $invalid_samples = [];
        foreach ($requests as $req) {
            $term = trim($req['search_term']);
            if (empty($term) && preg_match('/\/api\/companies\/([A-Za-z0-9]+)/', $req['endpoint'], $matches)) {
                $term = $matches[1];
            }
            if (!empty($term) && !isValidCif($term)) {
                $invalid_samples[] = $term;
                if (count($invalid_samples) >= 5) break;
            }
        }
        echo "  Ejemplos de búsqueda inválida: " . implode(", ", $invalid_samples) . "\n";
    }
    echo "\n";
}

echo "==========================================================\n";
echo "REPORTE GLOBAL DE CIFs NO ENCONTRADOS\n";
echo "==========================================================\n";
$unique_cifs = array_unique($all_valid_cifs_not_found);
echo "Total de CIFs únicos válidos que dieron 404: " . count($unique_cifs) . "\n";
echo "Listado:\n";
// only show first 100 to avoid huge output, but write all to a file
$cifs_out = array_slice(array_values($unique_cifs), 0, 100);
echo implode(", ", $cifs_out) . (count($unique_cifs) > 100 ? " ... (y " . (count($unique_cifs) - 100) . " más)" : "") . "\n";

file_put_contents('d:/laragon/www/apiempresas/scripts/cifs_404.txt', implode("\n", $unique_cifs));
echo "\nListado completo guardado en scripts/cifs_404.txt\n";
?>
