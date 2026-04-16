<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class VencimientosMantenimientoModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_vencimientos_mantenimientos';
    protected $primaryKey = 'id_vencimientos_mmttos';
    protected $allowedFields = [
        'id_mantenimiento',
        'id_cliente',
        'id_consultor',
        'fecha_vencimiento',
        'estado_actividad',
        'fecha_realizacion',
        'observaciones'
    ];

    /**
     * Obtener mantenimientos proximos a vencer en menos de 30 dias.
     */
    public function getUpcomingVencimientos()
    {
        $currentDate = date('Y-m-d');
        $dateThreshold = date('Y-m-d', strtotime('+30 days'));

        return $this->where('estado_actividad', 'sin ejecutar')
                     ->where('fecha_vencimiento <=', $dateThreshold)
                     ->findAll();
    }
}
