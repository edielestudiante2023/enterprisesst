<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class CargoClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_cargos_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_proceso',
        'nombre_cargo',
        'num_ocupantes',
        'descripcion',
        'activo',
    ];

    public function porCliente(int $idCliente, bool $soloActivos = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivos) $q = $q->where('activo', 1);
        return $q->orderBy('nombre_cargo', 'ASC')->findAll();
    }

    public function porProceso(int $idProceso): array
    {
        return $this->where('id_proceso', $idProceso)
            ->where('activo', 1)
            ->orderBy('nombre_cargo', 'ASC')
            ->findAll();
    }
}
