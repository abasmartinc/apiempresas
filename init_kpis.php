<?php

/**
 * Script de Inicialización de KPIs Pesados
 * 
 * Este script debe ejecutarse:
 * 1. La primera vez que se despliega en producción
 * 2. Periódicamente mediante un cron job (recomendado: cada 5-10 minutos)
 * 
 * Uso:
 *   php init_kpis.php
 * 
 * Cron job recomendado (cada 5 minutos):
 *   */5 * * * * cd /path/to/apiempresas && php init_kpis.php >> /path/to/logs/kpis.log 2>&1
 */

// Cargar CodeIgniter
require __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$pathsConfig = APPPATH . 'Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

// Obtener instancia de la aplicación
$app->initialize();

// Cargar modelos necesarios
$companyModel = new \App\Models\CompanyAdminModel();
$systemStatsModel = new \App\Models\SystemStatsModel();

echo "[" . date('Y-m-d H:i:s') . "] Iniciando actualización de KPIs pesados...\n";

$startTime = microtime(true);

try {
    $midnight = date('Y-m-d') . ' 00:00:00';
    
    echo "  - Calculando total de empresas...\n";
    $total = $companyModel->countAllResults();
    
    echo "  - Calculando empresas activas...\n";
    $companiesActive = $companyModel->where('estado', 'ACTIVA')->countAllResults();
    
    echo "  - Calculando empresas sin CIF...\n";
    $sinCif = $companyModel->groupStart()->where('cif', '')->orWhere('cif', null)->groupEnd()->countAllResults();
    
    echo "  - Calculando empresas sin dirección...\n";
    $sinDireccion = $companyModel->groupStart()->where('address', '')->orWhere('address', null)->groupEnd()->countAllResults();
    
    echo "  - Calculando empresas sin estado...\n";
    $sinEstado = $companyModel->groupStart()->where('estado', '')->orWhere('estado', null)->groupEnd()->countAllResults();
    
    echo "  - Calculando empresas sin CNAE...\n";
    $sinCnae = $companyModel->groupStart()->where('cnae_code', '')->orWhere('cnae_code', null)->groupEnd()->countAllResults();
    
    echo "  - Calculando empresas sin registro mercantil...\n";
    $sinRegistroMercantil = $companyModel->groupStart()->where('registro_mercantil', '')->orWhere('registro_mercantil', null)->groupEnd()->countAllResults();
    
    echo "  - Calculando empresas añadidas hoy...\n";
    $addedToday = $companyModel->where('created_at >=', $midnight)->countAllResults();
    
    $heavyData = [
        'total' => number_format($total, 0, ',', '.'),
        'companies_active' => number_format($companiesActive, 0, ',', '.'),
        'sin_cif' => number_format($sinCif, 0, ',', '.'),
        'sin_direccion' => number_format($sinDireccion, 0, ',', '.'),
        'sin_estado' => number_format($sinEstado, 0, ',', '.'),
        'sin_cnae' => number_format($sinCnae, 0, ',', '.'),
        'sin_registro_mercantil' => number_format($sinRegistroMercantil, 0, ',', '.'),
        'added_today' => number_format($addedToday, 0, ',', '.'),
    ];
    
    echo "  - Guardando en system_stats...\n";
    $systemStatsModel->setStat('heavy_kpis', $heavyData);
    
    // Limpiar caché del dashboard
    cache()->delete('admin_dashboard_kpis_consolidated');
    
    $duration = round(microtime(true) - $startTime, 2);
    
    echo "\n✓ KPIs actualizados correctamente en {$duration}s\n";
    echo "  Total empresas: " . $heavyData['total'] . "\n";
    echo "  Empresas activas: " . $heavyData['companies_active'] . "\n";
    echo "  Sin CIF: " . $heavyData['sin_cif'] . "\n";
    echo "  Sin dirección: " . $heavyData['sin_direccion'] . "\n";
    echo "  Sin estado: " . $heavyData['sin_estado'] . "\n";
    echo "  Sin CNAE: " . $heavyData['sin_cnae'] . "\n";
    echo "  Sin registro mercantil: " . $heavyData['sin_registro_mercantil'] . "\n";
    echo "  Añadidas hoy: " . $heavyData['added_today'] . "\n";
    
    exit(0);
    
} catch (\Exception $e) {
    $duration = round(microtime(true) - $startTime, 2);
    echo "\n✗ Error actualizando KPIs después de {$duration}s:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
