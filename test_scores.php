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
    die("DB Connection Error: " . $e->getMessage());
}

$query = "
    SELECT 
        c.id, c.company_name as name, c.cif, c.cnae_code as cnae, 
        c.registro_mercantil as province, c.objeto_social as corporate_purpose,
        (SELECT COUNT(*) FROM company_administrators a WHERE a.company_id = c.id) as num_admins,
        (SELECT COUNT(*) FROM borme_posts b WHERE b.company_id = c.id) as num_borme_posts,
        ce.ai_seo_text
    FROM companies c
    LEFT JOIN company_enrichment ce ON c.id = ce.company_id
    LIMIT 30000
";

$stmt = $pdo->query($query);
$companies = $stmt->fetchAll();

function calcScore($company) {
    $score = 0;
    $isValid = function ($value) {
        if ($value === null) return false;
        $v = trim((string)$value);
        return !in_array(strtoupper($v), ['', '-', '00 DESCONOCIDA', 'NULL', 'UNDEFINED']);
    };
    if ($isValid($company['name'] ?? null)) $score += 1;
    if ($isValid($company['cif'] ?? null)) $score += 1;
    if ($isValid($company['province'] ?? null)) $score += 1;
    if ($isValid($company['cnae'] ?? null)) $score += 1;
    if ($isValid($company['corporate_purpose'] ?? null)) $score += 2;
    if (!empty($company['num_admins']) && (int)$company['num_admins'] > 0) $score += 2;
    if (!empty($company['num_borme_posts']) && (int)$company['num_borme_posts'] > 0) $score += 1;
    if (!empty($company['ai_seo_text'])) $score += 3;
    return $score;
}

function generateUrl($company) {
    $name = $company['name'];
    $nameClean = str_replace(['º', 'ª'], ['o', 'a'], $name);
    // Simple slug generator
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nameClean)));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    if (!empty($company['cif'])) {
        return "https://apiempresas.es/" . $company['cif'] . "-" . $slug;
    }
    return "https://apiempresas.es/" . $slug;
}

$scoreGroups = [2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => [], 13 => [], 14 => [], 15 => []];
$totalProcessed = count($companies);
$countsByScore = array_fill_keys(array_keys($scoreGroups), 0);

foreach ($companies as $c) {
    $s = calcScore($c);
    if (!isset($countsByScore[$s])) $countsByScore[$s] = 0;
    $countsByScore[$s]++;
    
    if (isset($scoreGroups[$s]) && count($scoreGroups[$s]) < 2) {
        $scoreGroups[$s][] = $c;
    }
}

echo "=== ESTADÍSTICA DE CALIDAD DE CONTENIDO (MUESTRA DE $totalProcessed EMPRESAS) ===\n\n";

foreach ($countsByScore as $score => $count) {
    $pct = round(($count / $totalProcessed) * 100, 2);
    echo "Score $score: $count empresas ($pct%)\n";
}

echo "\n\n=== EJEMPLOS REALES DE EMPRESAS ===\n\n";

$scoresToShow = [4, 6, 8, 12]; // Just varying scores

foreach ($scoresToShow as $score) {
    if (empty($scoreGroups[$score])) continue;
    
    if ($score <= 4) {
         echo ">> CATEGORÍA: POBRE (THIN CONTENT) - SCORE $score <<\n";
         echo "Google rastrea estas páginas y las ve vacías (sin objeto social, ni cargos). Las ignora.\n\n";
    } else {
         echo ">> CATEGORÍA: ALTA CALIDAD - SCORE $score <<\n";
         echo "Google indexa estas porque aportan datos ricos o texto único.\n\n";
    }

    foreach ($scoreGroups[$score] as $ex) {
        echo "Empresa: " . $ex['name'] . "\n";
        echo "URL: " . generateUrl($ex) . "\n";
        echo "Datos disponibles:\n";
        echo "- CIF: " . ($ex['cif'] ?: 'NO') . "\n";
        echo "- Provincia: " . ($ex['province'] ?: 'NO') . "\n";
        echo "- CNAE: " . ($ex['cnae'] ?: 'NO') . "\n";
        echo "- Objeto Social: " . ($ex['corporate_purpose'] ? substr($ex['corporate_purpose'], 0, 80).'...' : 'NO TIENE') . "\n";
        echo "- Administradores: " . ($ex['num_admins'] > 0 ? "SÍ ({$ex['num_admins']})" : 'NO') . "\n";
        echo "- Actos Borme: " . ($ex['num_borme_posts'] > 0 ? "SÍ ({$ex['num_borme_posts']})" : 'NO') . "\n";
        echo "- Texto Inteligencia Artificial: " . ($ex['ai_seo_text'] ? 'SÍ' : 'NO') . "\n";
        echo "---------------------------\n";
    }
    echo "\n";
}
