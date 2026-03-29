<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRadarPricesTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('radar_prices')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'min_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'max_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'base_price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
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
            $this->forge->createTable('radar_prices');

            // Initial Data (Tiers)
            $data = [
                [
                    'min_count'  => 1,
                    'max_count'  => 10,
                    'base_price' => 2.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'min_count'  => 11,
                    'max_count'  => 50,
                    'base_price' => 4.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'min_count'  => 51,
                    'max_count'  => 150,
                    'base_price' => 7.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'min_count'  => 151,
                    'max_count'  => 500,
                    'base_price' => 9.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'min_count'  => 501,
                    'max_count'  => 1500,
                    'base_price' => 12.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'min_count'  => 1501,
                    'max_count'  => 9999999,
                    'base_price' => 15.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ];
            $this->db->table('radar_prices')->insertBatch($data);
        }
    }

    public function down()
    {
        $this->forge->dropTable('radar_prices');
    }
}
