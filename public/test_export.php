<?php
define('ENVIRONMENT', 'development');
require realpath(__DIR__ . '/../app/Config/Paths.php');
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$db = \Config\Database::connect();
$builder = $db->table('companies');
$builder->select('id, company_name as name, cif, fecha_constitucion, cnae_code as cnae, cnae_label, registro_mercantil, municipality, objeto_social');

$province = 'España';
if ($province && strtolower($province) !== 'españa') {
    if (strtolower($province) === 'alicante') {
        $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
    } else {
        $builder->where('registro_mercantil', $province);
    }
}

$period = '30days';
if ($period === 'hoy') {
    $builder->where('fecha_constitucion', date('Y-m-d'));
} elseif ($period === 'semana') {
    $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))
            ->where('fecha_constitucion <=', date('Y-m-d'));
} elseif ($period === 'mes') {
    $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
            ->where('fecha_constitucion <=', date('Y-m-d'));
} elseif ($period === '30days') {
    $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
            ->where('fecha_constitucion <=', date('Y-m-d'));
} elseif ($period === 'general') {
    $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
            ->where('fecha_constitucion <=', date('Y-m-d'));
}

$builder->where('fecha_constitucion IS NOT NULL');
$builder->orderBy('fecha_constitucion', 'DESC');
// We don't limit for export
$companies = $builder->get()->getResultArray();

echo "Count: " . count($companies);
