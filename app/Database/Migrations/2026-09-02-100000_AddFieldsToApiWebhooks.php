<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToApiWebhooks extends Migration
{
    public function up()
    {
        $fields = [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'filters',
            ],
            'failure_count' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'is_active',
            ],
            'last_delivery_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'failure_count',
            ],
            'last_success_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'last_delivery_at',
            ],
            'last_status_code' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'last_success_at',
            ],
            'disabled_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'last_status_code',
            ],
        ];

        $this->forge->addColumn('api_webhooks', $fields);

        // Add performance indexes for background processing and user lookup
        $this->db->query("CREATE INDEX `idx_user_active` ON `api_webhooks` (`user_id`, `is_active`)");
        $this->db->query("CREATE INDEX `idx_event_active` ON `api_webhooks` (`event`, `is_active`)");
    }

    public function down()
    {
        // Drop added indexes
        $this->db->query("DROP INDEX `idx_user_active` ON `api_webhooks`");
        $this->db->query("DROP INDEX `idx_event_active` ON `api_webhooks`");

        // Drop added columns
        $this->forge->dropColumn('api_webhooks', [
            'is_active',
            'failure_count',
            'last_delivery_at',
            'last_success_at',
            'last_status_code',
            'disabled_at',
        ]);
    }
}