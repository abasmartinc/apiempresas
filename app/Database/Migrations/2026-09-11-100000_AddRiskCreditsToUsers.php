<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRiskCreditsToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('risk_credits', 'users')) {
            $this->forge->addColumn('users', [
                'risk_credits' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'api_access',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('risk_credits', 'users')) {
            $this->forge->dropColumn('users', 'risk_credits');
        }
    }
}
