<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioActividadesModel extends Model
{
    protected $table = 'tbl_inventario_actividades_plandetrabajo';  // Nombre de la tabla
    protected $primaryKey = 'id_inventario_actividades_plandetrabajo';  // Llave primaria
    protected $allowedFields = [
        'phva_plandetrabajo',
        'numeral_plandetrabajo',
        'actividad_plandetrabajo',
        'responsable_sugerido_plandetrabajo' // Este campo no es obligatorio
    ];

    // Para que los campos de created_at y updated_at se actualicen automáticamente (si los tienes en la tabla)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Configurar el modelo para que devuelva objetos
    protected $returnType = 'object';

    // Validaciones
    protected $validationRules = [
        'phva_plandetrabajo' => 'required|string',
        'numeral_plandetrabajo' => 'required|string',
        'actividad_plandetrabajo' => 'required|string',
        // El campo responsable_sugerido_plandetrabajo no es obligatorio, por lo tanto no lo validamos aquí.
    ];

    // Mensajes de validación personalizados (opcional)
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

    // Desactivar validación automática
    protected $skipValidation = false;
}
