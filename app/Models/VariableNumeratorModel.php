<?php

namespace App\Models;

use CodeIgniter\Model;

class VariableNumeratorModel extends Model
{
    protected $table = 'tbl_variable_numerator'; // Nombre de la tabla
    protected $primaryKey = 'id_numerator_variable'; // Clave primaria

    // Campos permitidos para la manipulación
    protected $allowedFields = ['numerator_variable_text', 'numerator_variable_data', 'created_at', 'updated_at'];

    // Habilitar timestamps automáticos
    protected $useTimestamps = true;
}
