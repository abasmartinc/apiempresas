<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugToCompanies extends Migration
{
    public function up()
    {
        $fields = [
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'company_name'
            ],
        ];
        
        $this->forge->addColumn('companies', $fields);
        
        // Agregar índice único para búsquedas rápidas
        $this->forge->addKey('slug', false, true); // unique index
    }

    public function down()
    {
        $this->forge->dropKey('companies', 'slug');
        $this->forge->dropColumn('companies', 'slug');
    }
}
