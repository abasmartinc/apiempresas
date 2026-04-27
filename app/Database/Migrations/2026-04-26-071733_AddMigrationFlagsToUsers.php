<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMigrationFlagsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'migration_notice_shown' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_active'
            ],
            'migration_reset_done' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'migration_notice_shown'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'migration_notice_shown');
        $this->forge->dropColumn('users', 'migration_reset_done');
    }
}
