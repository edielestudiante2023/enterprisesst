<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVisitaPtaModel extends Model
{
    protected $table = 'tbl_acta_visita_pta';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_acta_visita',
        'id_ptacliente',
        'cerrada',
        'justificacion_no_cierre',
        'created_at',
    ];

    /**
     * Todas las actividades PTA vinculadas a un acta (con JOIN a tbl_pta_cliente)
     */
    public function getByActa(int $idActa): array
    {
        return $this->select('tbl_acta_visita_pta.*, tbl_pta_cliente.numeral_plandetrabajo, tbl_pta_cliente.actividad_plandetrabajo, tbl_pta_cliente.fecha_propuesta, tbl_pta_cliente.estado_actividad')
            ->join('tbl_pta_cliente', 'tbl_pta_cliente.id_ptacliente = tbl_acta_visita_pta.id_ptacliente', 'left')
            ->where('tbl_acta_visita_pta.id_acta_visita', $idActa)
            ->orderBy('tbl_pta_cliente.numeral_plandetrabajo', 'ASC')
            ->findAll();
    }

    /**
     * Solo las actividades marcadas como cerradas en esta acta (para PDF y vista)
     */
    public function getCerradasByActa(int $idActa): array
    {
        return $this->select('tbl_acta_visita_pta.*, tbl_pta_cliente.numeral_plandetrabajo, tbl_pta_cliente.actividad_plandetrabajo, tbl_pta_cliente.fecha_propuesta, tbl_pta_cliente.estado_actividad')
            ->join('tbl_pta_cliente', 'tbl_pta_cliente.id_ptacliente = tbl_acta_visita_pta.id_ptacliente', 'left')
            ->where('tbl_acta_visita_pta.id_acta_visita', $idActa)
            ->where('tbl_acta_visita_pta.cerrada', 1)
            ->orderBy('tbl_pta_cliente.numeral_plandetrabajo', 'ASC')
            ->findAll();
    }
}
