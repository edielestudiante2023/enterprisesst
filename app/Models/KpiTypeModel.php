<?php

namespace App\Models;

use CodeIgniter\Model;

class KpiTypeModel extends Model
{
    protected $table = 'tbl_kpi_type'; // Nombre de la tabla
    protected $primaryKey = 'id_kpi_type'; // Clave primaria

    // Campos permitidos para manipulación
    protected $allowedFields = ['kpi_type', 'kpi_type_comment', 'created_at', 'updated_at'];

    // Habilitar los campos de timestamps (created_at y updated_at)
    protected $useTimestamps = true;
}
