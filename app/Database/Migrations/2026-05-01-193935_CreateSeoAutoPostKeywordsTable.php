<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeoAutoPostKeywordsTable extends Migration
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
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'intent' => [
                'type'       => 'ENUM',
                'constraint' => ['informacional', 'comercial', 'mixta'],
                'default'    => 'informacional',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'generated_content' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'wordpress_post_id' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'wordpress_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 29,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'generating', 'generated', 'published', 'failed'],
                'default'    => 'pending',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'generated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'published_at' => [
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
        $this->forge->addKey('status');
        $this->forge->addUniqueKey('keyword');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('seo_auto_post_keywords');
    }

    public function down()
    {
        $this->forge->dropTable('seo_auto_post_keywords');
    }
}
