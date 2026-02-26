<?php

namespace App\Models;

use CodeIgniter\Model;

class MantenimientoModel extends Model
{
    protected $table = 'tbl_mantenimientos';
    protected $primaryKey = 'id_mantenimiento';
    protected $allowedFields = ['detalle_mantenimiento'];
}
