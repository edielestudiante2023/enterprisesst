<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioActividadesModel extends Model
{
    protected $table = 'tbl_inventario_actividades_plandetrabajo';
    protected $primaryKey = 'id_inventario_actividades_plandetrabajo';
    protected $allowedFields = [
        'phva_plandetrabajo',
        'numeral_plandetrabajo',
        'actividad_plandetrabajo',
        'responsable_sugerido_plandetrabajo'
    ];

    // No usar timestamps ya que la tabla no tiene estos campos
    protected $useTimestamps = false;

    // Configurar el modelo para que devuelva objetos
    protected $returnType = 'object';

    // Validaciones
    protected $validationRules = [
        'phva_plandetrabajo' => 'required|string',
        'numeral_plandetrabajo' => 'required|string',
        'actividad_plandetrabajo' => 'required|string'
    ];

    // Desactivar validación automática
    protected $skipValidation = false;
}
