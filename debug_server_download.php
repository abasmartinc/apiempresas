<?php
/**
 * Script de diagnóstico para el servidor
 * Sube este archivo a la raíz de tu proyecto y ejecútalo
 */

require_once __DIR__ . '/vendor/autoload.php';

// Simular entorno CI4 para obtener paths
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
$pathsConfig = __DIR__ . '/app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
if (!defined('WRITEPATH')) define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
if (!defined('ROOTPATH')) define('ROOTPATH', realpath(__DIR__) . DIRECTORY_SEPARATOR);

echo "<h1>Diagnóstico de Descarga de Factura</h1>";

$mysqli = new mysqli("localhost", "root", "", "apiempresas"); 
if ($mysqli->connect_error) {
    die("<p style='color:red'>Error de conexión DB: " . $mysqli->connect_error . "</p>");
}

$invoiceId = 5;
$res = $mysqli->query("SELECT * FROM invoices WHERE id = $invoiceId");
$invoice = $res->fetch_object();

if (!$invoice) {
    die("<p style='color:red'>Factura ID $invoiceId no encontrada en la base de datos.</p>");
}

echo "<ul>";
echo "<li><b>Invoice Number:</b> {$invoice->invoice_number}</li>";
echo "<li><b>PDF Path en DB:</b> {$invoice->pdf_path}</li>";

$relativePath = preg_replace('#^writable/#', '', $invoice->pdf_path);
$fullPath = WRITEPATH . $relativePath;
$altPath = ROOTPATH . $invoice->pdf_path;

echo "<li><b>WRITEPATH detected:</b> " . WRITEPATH . "</li>";
echo "<li><b>ROOTPATH detected:</b> " . ROOTPATH . "</li>";
echo "<li><b>Path 1 (WRITEPATH + relative):</b> $fullPath</li>";
echo "<li><b>Path 1 existe?:</b> " . (file_exists($fullPath) ? "<b style='color:green'>SÍ</b>" : "<b style='color:red'>NO</b>") . "</li>";
echo "<li><b>Path 2 (ROOTPATH + DB path):</b> $altPath</li>";
echo "<li><b>Path 2 existe?:</b> " . (file_exists($altPath) ? "<b style='color:green'>SÍ</b>" : "<b style='color:red'>NO</b>") . "</li>";

if (file_exists($fullPath)) {
    echo "<li><b>Permisos del archivo:</b> " . substr(sprintf('%o', fileperms($fullPath)), -4) . "</li>";
    echo "<li><b>Tamaño:</b> " . filesize($fullPath) . " bytes</li>";
}

echo "</ul>";

if (!file_exists($fullPath) && !file_exists($altPath)) {
    echo "<h3>Sugerencias:</h3>";
    echo "<p>1. Asegúrate de que la carpeta <b>writable/invoices/2026/02/</b> existe en el servidor.</p>";
    echo "<p>2. Revisa que el nombre del archivo sea exactamente <b>{$invoice->invoice_number}.pdf</b> (ojo con las mayúsculas en Linux).</p>";
    echo "<p>3. Verifica que la carpeta <b>writable</b> tenga permisos de lectura/escritura (775 o 777).</p>";
}

$mysqli->close();
