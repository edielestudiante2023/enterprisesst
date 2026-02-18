<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCerradaFinContrato extends Migration
{
    public function up()
    {
        // tbl_pta_cliente: agregar 'CERRADA POR FIN CONTRATO' al ENUM de estado_actividad
        $this->db->query("
            ALTER TABLE tbl_pta_cliente
            MODIFY COLUMN estado_actividad
                ENUM('ABIERTA','CERRADA','GESTIONANDO','CERRADA POR FIN CONTRATO')
                NOT NULL DEFAULT 'ABIERTA'
        ");

        // tbl_cronog_capacitacion: agregar 'CERRADA POR FIN CONTRATO' al ENUM de estado
        $this->db->query("
            ALTER TABLE tbl_cronog_capacitacion
            MODIFY COLUMN estado
                ENUM('PROGRAMADA','EJECUTADA','CANCELADA POR EL CLIENTE','REPROGRAMADA','CERRADA POR FIN CONTRATO')
                NOT NULL DEFAULT 'PROGRAMADA'
        ");

        // tbl_pendientes: agregar 'CERRADA POR FIN CONTRATO' al ENUM de estado
        $this->db->query("
            ALTER TABLE tbl_pendientes
            MODIFY COLUMN estado
                ENUM('ABIERTA','CERRADA','SIN RESPUESTA DEL CLIENTE','CERRADA POR FIN CONTRATO')
                NOT NULL DEFAULT 'ABIERTA'
        ");
    }

    public function down()
    {
        // Revertir: solo posible si no hay filas con 'CERRADA POR FIN CONTRATO'
        $this->db->query("
            ALTER TABLE tbl_pta_cliente
            MODIFY COLUMN estado_actividad
                ENUM('ABIERTA','CERRADA','GESTIONANDO')
                NOT NULL DEFAULT 'ABIERTA'
        ");

        $this->db->query("
            ALTER TABLE tbl_cronog_capacitacion
            MODIFY COLUMN estado
                ENUM('PROGRAMADA','EJECUTADA','CANCELADA POR EL CLIENTE','REPROGRAMADA')
                NOT NULL DEFAULT 'PROGRAMADA'
        ");

        $this->db->query("
            ALTER TABLE tbl_pendientes
            MODIFY COLUMN estado
                ENUM('ABIERTA','CERRADA','SIN RESPUESTA DEL CLIENTE')
                NOT NULL DEFAULT 'ABIERTA'
        ");
    }
}
