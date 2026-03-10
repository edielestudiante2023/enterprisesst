<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialEstandaresModel extends Model
{
    protected $table = 'historial_resumen_estandares';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_cliente',
        'nombre_cliente',
        'estandares',
        'nombre_consultor',
        'correo_consultor',
        'total_valor',
        'total_puntaje',
        'porcentaje_cumplimiento',
        'fecha_extraccion',
    ];
    protected $useTimestamps = false;
}
