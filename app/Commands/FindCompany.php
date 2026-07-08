<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FindCompany extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'find:company';
    protected $description = 'Check available years';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write("=== Years in company_contracts ===", 'yellow');
        $years = $db->query("SELECT YEAR(fecha_adjudicacion) as yr, COUNT(*) as c, SUM(importe_adjudicacion) as total FROM company_contracts WHERE fecha_adjudicacion IS NOT NULL GROUP BY yr ORDER BY yr DESC")->getResultArray();
        foreach ($years as $y) CLI::write("  " . $y['yr'] . " => " . number_format($y['c']) . " contratos | " . number_format($y['total'],2) . " €");

        CLI::write("\n=== Years in company_subsidies ===", 'yellow');
        $years2 = $db->query("SELECT YEAR(fecha_concesion) as yr, COUNT(*) as c, SUM(importe) as total FROM company_subsidies WHERE fecha_concesion IS NOT NULL GROUP BY yr ORDER BY yr DESC")->getResultArray();
        foreach ($years2 as $y) CLI::write("  " . $y['yr'] . " => " . number_format($y['c']) . " subvenciones | " . number_format($y['total'],2) . " €");
    }
}
