<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlockedIpsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
                'comment' => 'IP address (supports IPv4 and IPv6)',
            ],
            'reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'comment' => 'Reason for blocking (e.g., rate_limit_exceeded, non_ajax_request)',
            ],
            'blocked_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => 'When the IP was first blocked',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'User agent string for analysis',
            ],
            'request_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
                'comment' => 'Number of blocked attempts since blocking',
            ],
            'last_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Last time this IP tried to access',
            ],
            'meta' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional metadata (request patterns, endpoints accessed, etc.)',
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
        $this->forge->addUniqueKey('ip_address', 'unique_ip');
        $this->forge->addKey('blocked_at', false, false, 'idx_blocked_at');

        $this->forge->createTable('blocked_ips', true);
    }

    public function down()
    {
        $this->forge->dropTable('blocked_ips', true);
    }
}
