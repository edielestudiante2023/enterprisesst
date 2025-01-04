<?php

namespace App\Models;

use CodeIgniter\Model;

class DataOwnerModel extends Model
{
    protected $table = 'tbl_data_owner'; // Nombre de la tabla
    protected $primaryKey = 'id_data_owner'; // Clave primaria

    // Campos permitidos para la manipulación
    protected $allowedFields = ['data_owner', 'comment_data_owner', 'created_at', 'updated_at'];

    // Habilitar timestamps automáticos
    protected $useTimestamps = true;
}
