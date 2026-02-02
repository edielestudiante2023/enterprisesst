<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdResponsableToMiembros extends Migration
{
    public function up()
    {
        // Agregar columna id_responsable a tbl_comite_miembros
        $this->forge->addColumn('tbl_comite_miembros', [
            'id_responsable' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'default' => null,
                'after' => 'id_cliente'
            ]
        ]);

        // Agregar índice
        $this->db->query('ALTER TABLE tbl_comite_miembros ADD INDEX idx_id_responsable (id_responsable)');
    }

    public function down()
    {
        // Eliminar índice
        $this->db->query('ALTER TABLE tbl_comite_miembros DROP INDEX idx_id_responsable');

        // Eliminar columna
        $this->forge->dropColumn('tbl_comite_miembros', 'id_responsable');
    }
}
