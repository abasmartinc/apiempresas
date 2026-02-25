<?php
// Set environment variables to bypass CLI URI discovery issues
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SERVER_NAME'] = 'localhost';

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
$pathsConfig = __DIR__ . '/app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();

// Boot the framework
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Models\InvoiceModel;

$invoiceModel = new InvoiceModel();
$ym = date('Y-m');

echo "Consultando ingresos para el mes: $ym...\n";

$revenue = $invoiceModel->getMonthlyRevenue($ym);

echo "Resultado del Query:\n";
print_r($revenue);

if ($revenue && $revenue->total > 0) {
    echo "\n¡Los ingresos se están calculando correctamente en el modelo!\n";
    echo "Total: " . $revenue->total . " €\n";
    echo "\nSugerencia: El dashboard usa una caché de 24 horas ('admin_dashboard_kpis_consolidated').\n";
    echo "Si los datos en la DB son correctos, intenta limpiar la caché desde el dashboard o borrando los archivos en writable/cache/.\n";
} else {
    echo "\nError: Los ingresos siguen siendo 0 en el modelo.\n";
    echo "Verificando facturas existentes en este mes...\n";
    $db = \Config\Database::connect();
    $res = $db->query("SELECT * FROM invoices WHERE status = 'paid' AND created_at LIKE '$ym%'")->getResult();
    echo "Facturas encontradas: " . count($res) . "\n";
    foreach($res as $r) {
        echo "- ID: {$r->id}, Num: {$r->invoice_number}, Total: {$r->total_amount}, Fecha: {$r->created_at}\n";
    }
}
