<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'tbl_pta_cliente';
    protected $primaryKey = 'id_ptacliente';
    protected $allowedFields = [
        'id_cliente',
        'id_plandetrabajo',
        'phva_plandetrabajo',
        'numeral_plandetrabajo',
        'actividad_plandetrabajo',
        'responsable_sugerido_plandetrabajo',
        'observaciones',
        'fecha_propuesta'
    ];
    protected $useTimestamps = true; // Para manejar created_at y updated_at automáticamente
}
