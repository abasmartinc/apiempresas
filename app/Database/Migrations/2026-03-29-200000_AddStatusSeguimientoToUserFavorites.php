<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusSeguimientoToUserFavorites extends Migration
{
    public function up()
    {
        // Add 'seguimiento' to the ENUM status
        // Note: Raw SQL is safer for modifying ENUMs in some DBs
        $this->db->query("ALTER TABLE user_favorites MODIFY COLUMN status ENUM('nuevo', 'contactado', 'negociacion', 'ganado', 'seguimiento') DEFAULT 'nuevo'");
    }

    public function down()
    {
        // Revert to original ENUM (be careful with data loss if 'seguimiento' was used)
        $this->db->query("ALTER TABLE user_favorites MODIFY COLUMN status ENUM('nuevo', 'contactado', 'negociacion', 'ganado') DEFAULT 'nuevo'");
    }
}
