<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class SimplePendientesModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_pendientes'; // Nombre de la tabla
    protected $primaryKey = 'id_pendientes'; // Llave primaria
    protected $allowedFields = [ // Campos que se pueden insertar
        'id_cliente',
        'responsable',
        'tarea_actividad',
        'fecha_cierre',
        'estado'
    ];

    // Desactivar validación automática para eliminar restricciones
    protected $skipValidation = true;
}
