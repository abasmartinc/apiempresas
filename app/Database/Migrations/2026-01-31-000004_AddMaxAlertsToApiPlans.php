<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaxAlertsToApiPlans extends Migration
{
    public function up()
    {
        $fields = [
            'max_alerts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1, // Default to 1 (Free tier hook)
                'after'      => 'price_annual',
            ],
        ];

        $this->forge->addColumn('api_plans', $fields);

        // Set limits for known plans (IDs are assumed based on standard seeding, but we use update logic)
        $db = \Config\Database::connect();
        
        // Free / Hacker (ID 1 usually)
        $db->table('api_plans')->where('id', 1)->update(['max_alerts' => 1]);
        
        // Basic (ID 2 usually)
        $db->table('api_plans')->where('id', 2)->update(['max_alerts' => 20]);
        
        // Pro / Enterprise (ID 3, 4...)
        $db->table('api_plans')->where('id', 3)->update(['max_alerts' => 100]);
        $db->table('api_plans')->where('id', 4)->update(['max_alerts' => 500]);
    }

    public function down()
    {
        $this->forge->dropColumn('api_plans', 'max_alerts');
    }
}
