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

// echo "1. Convirtiendo huérfanos con IDs fantasmas a company_id = 0...\n";
// // Ponemos a 0 los company_id que apuntan a empresas borradas
// $pdo->exec("UPDATE borme_posts b LEFT JOIN companies c ON b.company_id = c.id SET b.company_id = 0 WHERE c.id IS NULL AND b.company_id > 0");


echo "2. Procesando publicaciones huérfanas...\n";

$totalUpdated = 0;
$totalProcessed = 0;
$lastId = 0;
$batchSize = 5000;

// Sentencias preparadas para máximo rendimiento
$findStmt = $pdo->prepare("SELECT id FROM companies WHERE company_name = :name");
$updateStmt = $pdo->prepare("UPDATE borme_posts SET company_id = ?, association_processed = 1 WHERE id = ?");
$markFailedStmt = $pdo->prepare("UPDATE borme_posts SET association_processed = 2 WHERE id = ?");

while (true) {
    // Obtenemos un bloque de registros huérfanos (que no hayan fallado definitivamente con estado 2)
    $stmt = $pdo->prepare("SELECT id, company_name FROM borme_posts WHERE (company_id = 0 OR company_id IS NULL) AND id > ? AND association_processed != 2 ORDER BY id ASC LIMIT ?");
    $stmt->execute([$lastId, $batchSize]);
    $rows = $stmt->fetchAll();
    
    if (empty($rows)) {
        break; // Fin del procesamiento
    }
    
    foreach ($rows as $row) {
        $lastId = $row['id'];
        $bormeName = trim($row['company_name']);
        $totalProcessed++;
        
        if (empty($bormeName)) {
            $markFailedStmt->execute([$row['id']]);
            continue;
        }

        // Buscamos coincidencia exacta por nombre
        $findStmt->execute(['name' => $bormeName]);
        $matches = $findStmt->fetchAll();
        
        if (count($matches) === 1) {
            // ¡ÉXITO! Hay una coincidencia exacta y única
            $newCompanyId = $matches[0]['id'];
            $updateStmt->execute([$newCompanyId, $row['id']]);
            $totalUpdated++;
        } else {
            // O no existe ninguna empresa con ese nombre, o hay varias (ej. homónimos en distintas provincias).
            // Marcamos association_processed = 2 para no volver a intentarlo en bucle en el futuro y evitar asociaciones erróneas.
            $markFailedStmt->execute([$row['id']]);
        }
        
        if ($totalProcessed % 5000 === 0) {
            echo "Procesados: " . number_format($totalProcessed, 0, ',', '.') . " | Re-asociados: " . number_format($totalUpdated, 0, ',', '.') . "\n";
        }
    }
}

echo "\n¡Proceso Terminado!\n";
echo "Total de registros analizados: " . number_format($totalProcessed, 0, ',', '.') . "\n";
echo "Total de registros re-asociados con éxito: " . number_format($totalUpdated, 0, ',', '.') . "\n";
