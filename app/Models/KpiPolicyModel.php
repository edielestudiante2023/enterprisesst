<?php

namespace App\Models;

use CodeIgniter\Model;

class KpiPolicyModel extends Model
{
    protected $table = 'tbl_kpi_policy'; // Nombre de la tabla
    protected $primaryKey = 'id_kpi_policy'; // Clave primaria

    // Campos permitidos para la manipulación
    protected $allowedFields = ['policy_kpi_definition', 'policy_kpi_comments', 'created_at', 'updated_at'];

    // Habilitar timestamps automáticos
    protected $useTimestamps = true;
}
