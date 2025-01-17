<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardItemModel extends Model
{
    protected $table      = 'dashboard_items';
    protected $primaryKey = 'id';
    protected $allowedFields = ['rol', 'tipo_proceso', 'detalle', 'descripcion', 'accion_url', 'orden'];
}
