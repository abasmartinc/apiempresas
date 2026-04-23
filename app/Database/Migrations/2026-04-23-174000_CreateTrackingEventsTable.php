<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrackingEventsTable extends Migration
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
            'event_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'page' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'anonymous_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'element' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_name');
        $this->forge->addKey('user_id');
        $this->forge->addKey('anonymous_id');
        $this->forge->addKey('session_id');
        $this->forge->createTable('tracking_events');
    }

    public function down()
    {
        $this->forge->dropTable('tracking_events');
    }
}
