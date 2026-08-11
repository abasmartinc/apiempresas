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

echo "Estimando volumen de empresas recuperables pre-2017...\n";

// Empresas mercantiles (S.L., S.A., etc) fundadas antes de 2017 que no tienen actos en borme_posts
// Usamos LIKE para filtrar solo entidades que estamos seguros que publican en el BORME.
$query = "
SELECT COUNT(*) 
FROM companies c
WHERE c.fecha_constitucion < '2017-01-01'
  AND c.fecha_constitucion != '0000-00-00'
  AND (c.company_name LIKE '% S.L.' 
       OR c.company_name LIKE '% SL'
       OR c.company_name LIKE '% S.A.'
       OR c.company_name LIKE '% SA'
       OR c.company_name LIKE '% SOCIEDAD LIMITADA%'
       OR c.company_name LIKE '% SOCIEDAD ANONIMA%')
  AND NOT EXISTS (
      SELECT 1 FROM borme_posts b WHERE b.company_id = c.id
  )
";

$stmt = $pdo->query($query);
$count = $stmt->fetchColumn();

echo "Empresas S.L./S.A. fundadas antes de 2017 sin publicaciones en la web actual: " . number_format($count, 0, ',', '.') . "\n";
