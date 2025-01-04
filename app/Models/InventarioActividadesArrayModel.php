<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioActividadesArrayModel extends Model
{
    protected $table = 'tbl_inventario_actividades_plandetrabajo';  // Nombre de la tabla
    protected $primaryKey = 'id_inventario_actividades_plandetrabajo';  // Llave primaria
    protected $allowedFields = [
        'phva_plandetrabajo',
        'numeral_plandetrabajo',
        'actividad_plandetrabajo',
        'responsable_sugerido_plandetrabajo' // Este campo no es obligatorio
    ];

    // Usar timestamps si es necesario
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Este modelo devolverá los resultados como arrays
    protected $returnType = 'array';

    // Validaciones
    protected $validationRules = [
        'phva_plandetrabajo' => 'required|string',
        'numeral_plandetrabajo' => 'required|string',
        'actividad_plandetrabajo' => 'required|string',
    ];

    // Mensajes de validación
    protected $validationMessages = [
        'phva_plandetrabajo' => [
            'required' => 'El campo PHVA es obligatorio.',
        ],
        'numeral_plandetrabajo' => [
            'required' => 'El campo numeral es obligatorio.',
        ],
        'actividad_plandetrabajo' => [
            'required' => 'El campo actividad es obligatorio.',
        ],
    ];

    protected $skipValidation = false;
}
