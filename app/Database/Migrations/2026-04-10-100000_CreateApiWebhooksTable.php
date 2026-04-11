<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiWebhooksTable extends Migration
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
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'comment'    => 'new_company, company_signal_detected, etc',
            ],
            'secret' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'paused', 'failed'],
                'default'    => 'active',
            ],
            'filters' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Filter by province, sector, etc',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('api_webhooks');
    }

    public function down()
    {
        $this->forge->dropTable('api_webhooks');
    }
}
