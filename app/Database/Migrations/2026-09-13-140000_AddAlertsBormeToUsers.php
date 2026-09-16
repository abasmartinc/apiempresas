<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Preferencia propia para las alertas del BORME.
 *
 * Es un valor de TRES estados a propósito, y NULL es el importante:
 *
 *   1     → el usuario las quiere, aunque haya rechazado el marketing
 *   0     → no las quiere, aunque acepte el marketing
 *   NULL  → no se ha pronunciado: se sigue lo que diga `unsuscribe`
 *
 * Si fuese un booleano con default 1, todos los que se dieron de baja quedarían
 * dados de alta de golpe sin haberlo pedido. Con NULL, nadie cambia de estado
 * hasta que lo decide por sí mismo.
 */
class AddAlertsBormeToUsers extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('alerts_borme', 'users')) {
            return;
        }

        $this->forge->addColumn('users', [
            'alerts_borme' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => null,
                'after'      => 'unsuscribe',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('alerts_borme', 'users')) {
            $this->forge->dropColumn('users', 'alerts_borme');
        }
    }
}
