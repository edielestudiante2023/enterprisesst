<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class TareaClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_tareas_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_proceso',
        'nombre_tarea',
        'rutinaria',
        'descripcion',
        'activo',
    ];

    public function porCliente(int $idCliente, bool $soloActivos = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivos) $q = $q->where('activo', 1);
        return $q->orderBy('nombre_tarea', 'ASC')->findAll();
    }

    public function porProceso(int $idProceso): array
    {
        return $this->where('id_proceso', $idProceso)
            ->where('activo', 1)
            ->orderBy('nombre_tarea', 'ASC')
            ->findAll();
    }
}
