<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ZonaClienteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_zonas_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_sede',
        'nombre_zona',
        'descripcion',
        'activo',
    ];

    public function porCliente(int $idCliente, bool $soloActivos = true): array
    {
        $q = $this->where('id_cliente', $idCliente);
        if ($soloActivos) $q = $q->where('activo', 1);
        return $q->orderBy('nombre_zona', 'ASC')->findAll();
    }

    public function porSede(int $idSede): array
    {
        return $this->where('id_sede', $idSede)
            ->where('activo', 1)
            ->orderBy('nombre_zona', 'ASC')
            ->findAll();
    }
}
