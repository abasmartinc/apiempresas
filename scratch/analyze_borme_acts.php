<?php
require 'vendor/autoload.php';

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
     
     // Tomamos una muestra de 10 registros para ver el formato
     $stmt = $pdo->query("SELECT act_types FROM borme_posts LIMIT 10");
     $rows = $stmt->fetchAll();
     
     echo "Sample act_types:\n";
     foreach ($rows as $row) {
         echo "- " . $row['act_types'] . "\n";
     }
     
     // Ahora intentamos sacar los únicos de una muestra más grande
     echo "\nAnalyzing 200,000 records...\n";
     $stmt = $pdo->query("SELECT act_types FROM borme_posts LIMIT 200000");


     $unique_acts = [];
     
     while ($row = $stmt->fetch()) {
         $acts = explode(',', $row['act_types']); // Suponiendo que están separados por comas
         foreach ($acts as $act) {
             $act = trim($act);
             if ($act !== '') {
                 if (!isset($unique_acts[$act])) {
                     $unique_acts[$act] = 0;
                 }
                 $unique_acts[$act]++;
             }
         }
     }
     
     arsort($unique_acts);
     
     echo "\nUnique Act Types found (count):\n";
     foreach ($unique_acts as $act => $count) {
         echo "$count: $act\n";
     }

} catch (\PDOException $e) {
     echo "Error: " . $e->getMessage();
}
