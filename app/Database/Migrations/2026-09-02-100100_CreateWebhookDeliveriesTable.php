<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhookDeliveriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'delivery_uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => false,
            ],
            'webhook_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'payload' => [
                'type' => 'JSON',
                'null' => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processing', 'retry', 'delivered', 'dead'],
                'default'    => 'pending',
                'null'       => false,
            ],
            'attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ],
            'claim_token' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'processing_started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'next_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'http_status' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
            ],
            'error_message' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'duration_ms' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('delivery_uuid', 'uniq_delivery_uuid');
        $this->forge->addKey(['status', 'next_attempt_at'], false, false, 'idx_queue_dispatch');
        $this->forge->addKey('claim_token', false, false, 'idx_claim');
        $this->forge->addKey(['webhook_id', 'created_at'], false, false, 'idx_webhook_history');
        $this->forge->addKey(['user_id', 'created_at'], false, false, 'idx_user_history');

        $this->forge->createTable('webhook_deliveries', true);
    }

    public function down()
    {
        $this->forge->dropTable('webhook_deliveries', true);
    }
}