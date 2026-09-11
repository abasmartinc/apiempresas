<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToApiWebhooks extends Migration
{
    public function up()
    {
        $fields = [
            'failure_count' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'filters',
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

        $fieldsToAdd = [];
        foreach ($fields as $fieldName => $fieldDef) {
            if (!$this->db->fieldExists($fieldName, 'api_webhooks')) {
                $fieldsToAdd[$fieldName] = $fieldDef;
            }
        }
        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('api_webhooks', $fieldsToAdd);
        }

        // Add performance indexes for background processing and user lookup
        $existingIndexes = array_column($this->db->getIndexData('api_webhooks'), 'name');
        if (!in_array('idx_user_active', $existingIndexes)) {
            $this->db->query("CREATE INDEX `idx_user_active` ON `api_webhooks` (`user_id`, `is_active`)");
        }
        if (!in_array('idx_event_active', $existingIndexes)) {
            $this->db->query("CREATE INDEX `idx_event_active` ON `api_webhooks` (`event`, `is_active`)");
        }
    }

    public function down()
    {
        $existingIndexes = array_column($this->db->getIndexData('api_webhooks'), 'name');
        if (in_array('idx_user_active', $existingIndexes)) {
            $this->db->query("DROP INDEX `idx_user_active` ON `api_webhooks`");
        }
        if (in_array('idx_event_active', $existingIndexes)) {
            $this->db->query("DROP INDEX `idx_event_active` ON `api_webhooks`");
        }

        // Drop added columns
        $colsToDrop = [];
        $candidates = ['is_active', 'failure_count', 'last_delivery_at', 'last_success_at', 'last_status_code', 'disabled_at'];
        foreach ($candidates as $col) {
            if ($this->db->fieldExists($col, 'api_webhooks')) {
                $colsToDrop[] = $col;
            }
        }
        if (!empty($colsToDrop)) {
            $this->forge->dropColumn('api_webhooks', $colsToDrop);
        }
    }
}