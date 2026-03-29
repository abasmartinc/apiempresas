<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadFollowupsTables extends Migration
{
    public function up()
    {
        // Table: lead_followups
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'company_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['seguimiento', 'contactado', 'cerrado'],
                'default'    => 'seguimiento',
            ],
            'notify_when_contact' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'prepared_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'contacted_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey(['user_id', 'company_id']);
        $this->forge->createTable('lead_followups');

        // Table: lead_prepared_messages
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'company_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'message_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'initial_contact',
            ],
            'message_body' => [
                'type' => 'TEXT',
            ],
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'ia_modal',
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
        $this->forge->addUniqueKey(['user_id', 'company_id', 'message_type']);
        $this->forge->createTable('lead_prepared_messages');
    }

    public function down()
    {
        $this->forge->dropTable('lead_followups');
        $this->forge->dropTable('lead_prepared_messages');
    }
}
