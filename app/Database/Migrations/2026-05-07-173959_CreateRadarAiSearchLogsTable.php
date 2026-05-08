<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRadarAiSearchLogsTable extends Migration
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
            'query_original' => [
                'type'       => 'TEXT',
            ],
            'filters_json' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'results_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'error_message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('radar_ai_search_logs');
    }

    public function down()
    {
        $this->forge->dropTable('radar_ai_search_logs');
    }
}
