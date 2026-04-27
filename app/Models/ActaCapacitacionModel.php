<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ActaCapacitacionModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_acta_capacitacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_comite', 'creado_por_tipo', 'id_miembro', 'id_consultor',
        'tema', 'fecha_capacitacion', 'hora_inicio', 'hora_fin',
        'dictada_por', 'nombre_capacitador', 'entidad_capacitadora',
        'modalidad', 'enlace_grabacion', 'objetivos', 'contenido', 'observaciones',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_capacitacion', 'DESC')
            ->findAll();
    }
}
