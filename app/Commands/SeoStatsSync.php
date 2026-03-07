<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;

class SeoStatsSync extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'SEO';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'seo:sync-stats';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Sincroniza y pre-calcula los datos de agregación (total empresas, cnae) en tablas rápidas para el SEO.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'seo:sync-stats';

    public function run(array $params)
    {
        CLI::write("Iniciando sincronización de pre-agregaciones para SEO...", 'green');
        $db = \Config\Database::connect();
        
        $this->syncProvinces($db);
        $this->syncSectors($db);
        
        CLI::write("Sincronización completada exitosamente.", 'green');
    }

    private function syncProvinces($db)
    {
        CLI::write("1/2 Procesando vistas Provinciales...", 'yellow');
        
        // Obtener provincias únicas
        $provinces = $db->query("SELECT DISTINCT registro_mercantil FROM companies WHERE registro_mercantil IS NOT NULL AND registro_mercantil != ''")->getResultArray();
        
        $totalProvinces = count($provinces);
        CLI::write("Encontradas {$totalProvinces} provincias.", 'white');

        foreach ($provinces as $index => $row) {
            $province = $row['registro_mercantil'];
            
            // 1. Total
            $totalRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE registro_mercantil = ?", [$province])->getRow();
            $total = $totalRow->total;
            
            // 2. Crecimiento (últimos 12 meses)
            $oneYearAgo = date('Y-m-d', strtotime('-1 year'));
            $newRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE registro_mercantil = ? AND fecha_constitucion >= ?", [$province, $oneYearAgo])->getRow();
            $newCount = $newRow->total;
            
            $growthPct = ($total > 0) ? round(($newCount / $total) * 100, 2) : 0;
            
            // 3. Sectores Top (Top 5 CNAE por provincia)
            $topSectors = $db->query("
                SELECT cnae_code as cnae, cnae_label, COUNT(id) as total 
                FROM companies 
                WHERE registro_mercantil = ? AND cnae_label IS NOT NULL AND cnae_label != '' 
                GROUP BY cnae_code, cnae_label 
                ORDER BY total DESC 
                LIMIT 5
            ", [$province])->getResultArray();

            // Insertar o actualizar (Upsert)
            $sql = "INSERT INTO seo_stats (province, total_companies, growth_pct, top_sectors) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        total_companies = VALUES(total_companies), 
                        growth_pct = VALUES(growth_pct), 
                        top_sectors = VALUES(top_sectors)";
                        
            $db->query($sql, [$province, $total, $growthPct, json_encode($topSectors)]);
            
            if ($index % 10 === 0) {
                 CLI::newLine();
                 CLI::write("Progreso de Provincias: " . ($index+1) . " / " . $totalProvinces, 'white');
            } else {
                 CLI::print('.', 'white');
            }
        }
        CLI::newLine();
    }

    private function syncSectors($db)
    {
        CLI::write("2/2 Procesando vistas por CNAE...", 'yellow');
        
        // Extraer listado de códigos CNAE limpios
        $cnaes = $db->query("SELECT DISTINCT cnae_code, cnae_label FROM companies WHERE cnae_code IS NOT NULL AND cnae_label IS NOT NULL AND cnae_label != ''")->getResultArray();
        $totalCnaes = count($cnaes);
        
        CLI::write("Encontrados {$totalCnaes} grupos CNAE.", 'white');

        foreach ($cnaes as $index => $row) {
            $cnaeCode = $row['cnae_code'];
            $cnaeLabel = $row['cnae_label'];
            
            // Evitar procesar CNAE numéricos corruptos o muy largos/nulos
            if (empty($cnaeCode) || strlen($cnaeCode) > 6) continue;

            $totalRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE cnae_code = ?", [$cnaeCode])->getRow();
            $total = $totalRow->total;
            
            if ($total == 0) continue;

            // Top Provincias para este CNAE
            $topProvinces = $db->query("
                SELECT registro_mercantil as provincia, COUNT(id) as total 
                FROM companies 
                WHERE cnae_code = ? AND registro_mercantil IS NOT NULL AND registro_mercantil != ''
                GROUP BY registro_mercantil 
                ORDER BY total DESC 
                LIMIT 10
            ", [$cnaeCode])->getResultArray();

            $sql = "INSERT INTO seo_stats_cnae (cnae_code, cnae_label, total_companies, top_provinces) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        cnae_label = VALUES(cnae_label),
                        total_companies = VALUES(total_companies), 
                        top_provinces = VALUES(top_provinces)";
                        
            $db->query($sql, [$cnaeCode, $cnaeLabel, $total, json_encode($topProvinces)]);
            
             if ($index > 0 && $index % 50 === 0) {
                 CLI::newLine();
                 CLI::write("Progreso de Sectores: " . $index . " / " . $totalCnaes, 'white');
            } else {
                 CLI::print('.', 'white');
            }
        }
        CLI::newLine();
    }
}
