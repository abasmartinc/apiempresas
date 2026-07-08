<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncHubSubvenciones extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:sync-hub-subvenciones';
    protected $description = 'Crea/actualiza la tabla resumen seo_hub_subvenciones para velocidad máxima en el hub.';
    protected $usage       = 'seo:sync-hub-subvenciones';

    public function run(array $params)
    {
        CLI::write("Iniciando sync de hub subvenciones...", 'green');
        $db = \Config\Database::connect();

        // 1. Create table if not exists
        $db->query("
            CREATE TABLE IF NOT EXISTS seo_hub_subvenciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                convocatoria TEXT NOT NULL,
                slug VARCHAR(600) NOT NULL,
                total_subsidies INT NOT NULL DEFAULT 0,
                total_companies INT NOT NULL DEFAULT 0,
                total_amount DECIMAL(20,2) NOT NULL DEFAULT 0.00,
                INDEX idx_slug (slug(200)),
                INDEX idx_total (total_subsidies)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // 2. Rebuild data
        CLI::write("Vaciando tabla...", 'yellow');
        $db->query("TRUNCATE TABLE seo_hub_subvenciones");

        CLI::write("Insertando datos agrupados (esto puede tardar unos segundos)...", 'yellow');
        $db->query("
            INSERT INTO seo_hub_subvenciones (convocatoria, slug, total_subsidies, total_companies, total_amount)
            SELECT 
                convocatoria,
                '' as slug,
                COUNT(id) as total_subsidies,
                COUNT(DISTINCT company_cif) as total_companies,
                SUM(importe) as total_amount
            FROM company_subsidies
            WHERE convocatoria IS NOT NULL AND convocatoria != ''
            GROUP BY convocatoria
            ORDER BY total_subsidies DESC
        ");
        CLI::write("Datos insertados. Generando slugs...", 'yellow');

        // 3. Generate slugs in PHP (more reliable than MySQL string replace chain)
        helper('text');
        $rows = $db->query("SELECT id, convocatoria FROM seo_hub_subvenciones")->getResultArray();
        $batch = [];
        foreach ($rows as $row) {
            $slug = url_title($row['convocatoria'], '-', true);
            $batch[] = ['id' => $row['id'], 'slug' => $slug ?: 'convocatoria-' . $row['id']];
        }

        // Update slugs in batches
        $builder = $db->table('seo_hub_subvenciones');
        $builder->updateBatch($batch, 'id');

        // 4. Clear cache
        $cache = \Config\Services::cache();
        $cache->delete('seo_subsidies_hub_page1_v2');
        CLI::write("Caché limpiada.", 'green');

        $count = $db->query("SELECT COUNT(*) as c FROM seo_hub_subvenciones")->getRow()->c;
        CLI::write("Completado. {$count} convocatorias en tabla resumen.", 'green');
    }
}
