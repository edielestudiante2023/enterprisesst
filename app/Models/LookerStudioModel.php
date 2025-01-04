<?php

namespace App\Models;

use CodeIgniter\Model;

class LookerStudioModel extends Model
{
    protected $table = 'tbl_lookerstudio';
    protected $primaryKey = 'id_looker';
    protected $allowedFields = ['tipodedashboard', 'enlace', 'id_cliente', 'created_at', 'updated_at'];
    protected $useTimestamps = true; // Habilitar la gestión automática de created_at y updated_at
}
