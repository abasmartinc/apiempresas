<?php
// Script de diagnóstico para Trends
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
$loader = require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/app/Config/Paths.php';
$config = new \Config\Paths();
$app = \Config\Services::codeigniter($config);
$app->initialize();

$db = \Config\Database::connect();

function testTrend($province = null, $sectionId = null) {
    global $db;
    echo "Testing: Prov=$province, Sect=$sectionId\n";
    
    // 1. Evolución Mensual
    $builder = $db->table('companies');
    $builder->select("DATE_FORMAT(fecha_constitucion, '%Y-%m') as month, COUNT(*) as total");
    $builder->where('fecha_constitucion >=', '2025-01-01');

    if ($province) {
        $builder->where('registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
    }

    if ($sectionId) {
        $builder->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
        $builder->join('cnae_subclasses', 'cnae_subclasses.name = cnae_2009_2025.label_2009', 'left');
        $builder->join('cnae_classes', 'cnae_classes.id = cnae_subclasses.parent_class_id', 'left');
        $builder->join('cnae_groups', 'cnae_groups.id = cnae_classes.parent_group_id', 'left');
        $builder->where('cnae_groups.parent_section_id', $sectionId);
    }

    $res = $builder->groupBy('month')->limit(5)->get()->getResultArray();
    echo "Evolution Count: " . count($res) . "\n";
    print_r($res);
    
    // 2. Sectores
    $sectorsBuilder = $db->table('cnae_sections s');
    $sectorsBuilder->select('s.name as label, COUNT(c.id) as total');
    $sectorsBuilder->join('cnae_groups g', 'g.parent_section_id = s.id');
    $sectorsBuilder->join('cnae_classes cl', 'cl.parent_group_id = g.id');
    $sectorsBuilder->join('cnae_subclasses sub', 'sub.parent_class_id = cl.id');
    // Using simple join for now to see if it works
    $sectorsBuilder->join('cnae_2009_2025 c2', 'c2.label_2009 = sub.name');
    $sectorsBuilder->join('companies c', 'c.cnae_code = c2.cnae_2009');
    $sectorsBuilder->where('c.fecha_constitucion >=', '2025-01-01');
    
    if ($province) {
        $sectorsBuilder->where('c.registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
    }
    
    $resSectors = $sectorsBuilder->groupBy('s.id, s.name')->limit(5)->get()->getResultArray();
    echo "Sectors Count: " . count($resSectors) . "\n";
    print_r($resSectors);
    echo "--------------------------\n";
}

testTrend(null, null);
testTrend('madrid', null);
testTrend('madrid', 'N'); // 'N' is likely Actividades administrativas based on screenshot value
