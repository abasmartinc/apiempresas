<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetTokenToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'reset_token'   => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true, 'after' => 'password_hash'],
            'reset_expires' => ['type' => 'DATETIME', 'null' => true, 'after' => 'reset_token'],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'reset_token');
        $this->forge->dropColumn('users', 'reset_expires');
    }
}
