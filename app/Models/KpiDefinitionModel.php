<?php

namespace App\Models;

use CodeIgniter\Model;

class KpiDefinitionModel extends Model
{
    protected $table = 'tbl_kpi_definition'; // Nombre de la tabla
    protected $primaryKey = 'id_kpi_definition'; // Clave primaria

    // Campos permitidos para la manipulación
    protected $allowedFields = ['name_kpi_definition', 'comment_kpi_definition', 'created_at', 'updated_at'];

    // Habilitar timestamps automáticos
    protected $useTimestamps = true;
}
