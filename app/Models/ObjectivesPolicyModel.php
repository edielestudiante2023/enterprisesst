<?php

namespace App\Models;

use CodeIgniter\Model;

class ObjectivesPolicyModel extends Model
{
    protected $table = 'tbl_objectives_policy'; // Nombre de la tabla
    protected $primaryKey = 'id_objectives'; // Clave primaria

    // Campos permitidos para la manipulación
    protected $allowedFields = ['name_objectives', 'comments_objectives', 'created_at', 'updated_at'];

    // Habilitar timestamps automáticos
    protected $useTimestamps = true;
}
