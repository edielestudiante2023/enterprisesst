<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class HistorialPlanTrabajoModel extends Model
{
    use TenantScopedModel;

    protected $table = 'historial_resumen_plan_trabajo';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_cliente',
        'nombre_cliente',
        'estandares',
        'nombre_consultor',
        'correo_consultor',
        'total_actividades',
        'actividades_abiertas',
        'porcentaje_abiertas',
        'fecha_extraccion',
    ];
    protected $useTimestamps = false;
}
