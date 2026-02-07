<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSourceAppToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'source_app' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'apiempresas',
                'after'      => 'api_access', // Place it nicely after api_access
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'source_app');
    }
}
