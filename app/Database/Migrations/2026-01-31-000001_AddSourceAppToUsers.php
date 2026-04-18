<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSourceAppToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('source_app', 'users')) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'source_app');
    }
}
