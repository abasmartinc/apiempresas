<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateRiskPlan extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'plan:create-risk';
    protected $description = 'Crea o actualiza el plan Solvencia Pro en la tabla api_plans.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $existing = $db->table('api_plans')->where('slug', 'risk_pro')->get()->getRow();

        if (!$existing) {
            $db->table('api_plans')->insert([
                'slug'               => 'risk_pro',
                'name'               => 'Solvencia Pro',
                'monthly_quota'      => 999999,
                'rate_limit_per_min' => 60,
                'price_monthly'      => 29.00,
                'price_annual'       => 290.00,
                'max_alerts'         => 0,
                'max_companies'      => 0,
                'is_active'          => 1,
                'product_type'       => 'risk',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
            CLI::write("SUCCESS: Plan risk_pro creado con ID " . $db->insertID(), 'green');
        } else {
            $db->table('api_plans')->where('slug', 'risk_pro')->update([
                'product_type'  => 'risk',
                'price_monthly' => 29.00,
                'price_annual'  => 290.00,
                'is_active'     => 1
            ]);
            CLI::write("INFO: Plan risk_pro actualizado correctamente (ID: " . $existing->id . ")", 'yellow');
        }
    }
}
