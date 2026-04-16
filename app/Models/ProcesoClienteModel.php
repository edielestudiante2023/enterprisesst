<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ProcesoClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_procesos_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'nombre_proceso',
        'tipo',
        'descripcion',
        'activo',
    ];

    public function porCliente(int $idCliente, bool $soloActivos = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivos) $q = $q->where('activo', 1);
        return $q->orderBy('nombre_proceso', 'ASC')->findAll();
    }
}
