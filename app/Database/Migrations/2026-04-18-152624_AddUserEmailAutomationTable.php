<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserEmailAutomationTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'email_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'email_type']);
        $this->forge->createTable('user_email_automation');
    }

    public function down()
    {
        $this->forge->dropTable('user_email_automation');
    }
}
