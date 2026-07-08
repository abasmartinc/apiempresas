<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExportJobsTable extends Migration
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
            'user_email' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'export_type' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'filters' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'completed', 'failed'],
                'default' => 'pending',
            ],
            'download_token' => [
                'type' => 'VARCHAR',
                'constraint' => '64',
                'null' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'total_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
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
        $this->forge->createTable('export_jobs');
    }

    public function down()
    {
        $this->forge->dropTable('export_jobs');
    }
}
