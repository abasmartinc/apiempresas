<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCifToBormeWatchlist extends Migration
{
    public function up()
    {
        $fields = [
            'cif' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'user_id',
            ],
        ];

        $this->forge->addColumn('borme_watchlist', $fields);
        $this->forge->addKey('cif');
    }

    public function down()
    {
        $this->forge->dropColumn('borme_watchlist', 'cif');
    }
}
