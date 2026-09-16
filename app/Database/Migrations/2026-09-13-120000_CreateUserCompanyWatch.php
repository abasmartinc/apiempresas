<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Vigilancia de empresas por usuario.
 *
 * La lista se alimenta sola de user_events (cada empresa desbloqueada entra en
 * vigilancia), pero necesita tabla propia por dos motivos:
 *
 *  - `last_borme_id` es la marca de agua que evita avisar dos veces del mismo acto.
 *    borme_posts no tiene fecha de inserción fiable (useTimestamps = false), así que
 *    el id autoincremental es el único marcador de "esto es nuevo para este usuario".
 *  - `active` deja el sitio hecho para el día que haya un botón de dejar de vigilar.
 */
class CreateUserCompanyWatch extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('user_company_watch')) {
            return;
        }

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
                'null'       => false,
            ],
            'cif' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            // 'auto' = entró por haber consultado la empresa; 'manual' = la marcó el usuario
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'auto',
            ],
            // Último borme_posts.id ya conocido por el usuario: solo se avisa de ids mayores
            'last_borme_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'last_notified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        // Una sola fila por usuario y empresa: la sincronización puede correr mil veces
        $this->forge->addUniqueKey(['user_id', 'cif']);
        $this->forge->addKey(['company_id', 'active']);
        $this->forge->createTable('user_company_watch');
    }

    public function down()
    {
        if ($this->db->tableExists('user_company_watch')) {
            $this->forge->dropTable('user_company_watch');
        }
    }
}
