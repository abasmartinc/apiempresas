<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttemptsToSeoQueue extends Migration
{
    public function up()
    {
        $fields = [
            'attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'status',
            ],
            'last_error' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
                'after'      => 'attempts',
            ],
            'processing_started_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'last_error',
            ],
        ];

        $this->forge->addColumn('seo_generation_queue', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('seo_generation_queue', ['attempts', 'last_error', 'processing_started_at']);
    }
}
