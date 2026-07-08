<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncRankings extends BaseCommand
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
    protected $name = 'seo:sync-rankings';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Actualiza las tablas resumen de los rankings de contratistas y subvenciones.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'seo:sync-rankings';

    public function run(array $params)
    {
        CLI::write("Iniciando actualización de tablas resumen (Materialized Views)...", 'green');
        $db = \Config\Database::connect();

        // 1. Sincronizar Ranking de Subvenciones
        CLI::write("Actualizando ranking de subvenciones...", 'yellow');
        $db->query("TRUNCATE TABLE seo_ranking_subvenciones");
        $db->query("
            INSERT INTO seo_ranking_subvenciones (company_cif, company_name, total_subsidies, total_amount)
            SELECT s.company_cif, MAX(comp.company_name), COUNT(s.id), SUM(s.importe)
            FROM company_subsidies s
            LEFT JOIN companies comp ON s.company_cif = comp.cif
            WHERE s.company_cif != '' AND s.company_cif IS NOT NULL
            GROUP BY s.company_cif
        ");
        CLI::write("Ranking de subvenciones actualizado.", 'green');

        // 2. Sincronizar Ranking de Contratos
        CLI::write("Actualizando ranking de contratos...", 'yellow');
        $db->query("TRUNCATE TABLE seo_ranking_contratos");
        $db->query("
            INSERT INTO seo_ranking_contratos (company_cif, company_name, total_contracts, total_amount)
            SELECT c.company_cif, MAX(comp.company_name), COUNT(c.id), SUM(c.importe_adjudicacion)
            FROM company_contracts c
            LEFT JOIN companies comp ON c.company_cif = comp.cif
            WHERE c.company_cif != '' AND c.company_cif IS NOT NULL
            GROUP BY c.company_cif
        ");
        CLI::write("Ranking de contratos actualizado.", 'green');

        // Limpiar caché de CodeIgniter para que los listados paginados tomen los datos actualizados
        $cache = \Config\Services::cache();
        $cache->delete('seo_top_contractors_p1');
        $cache->delete('seo_top_subsidies_p1');
        
        CLI::write("Caché limpiada.", 'green');
        CLI::write("Actualización completada exitosamente.", 'green');
    }
}
