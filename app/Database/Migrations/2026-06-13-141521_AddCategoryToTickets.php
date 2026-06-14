<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'general',
                'after'      => 'subject',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tickets', 'category');
    }
}
