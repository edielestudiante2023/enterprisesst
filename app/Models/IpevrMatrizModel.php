<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class IpevrMatrizModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_ipevr_matriz';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'nombre',
        'version',
        'estado',
        'id_consultor',
        'elaborado_por',
        'revisado_por',
        'aprobado_por',
        'fecha_creacion',
        'fecha_aprobacion',
        'fecha_proxima_revision',
        'observaciones',
        'snapshot_json',
    ];

    public const ESTADOS = ['borrador','revision','aprobada','vigente','historica'];

    public function porCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function vigenteDeCliente(int $idCliente): ?array
    {
        $row = $this->where('id_cliente', $idCliente)
            ->where('estado', 'vigente')
            ->orderBy('id', 'DESC')
            ->first();
        return $row ?: null;
    }
}
