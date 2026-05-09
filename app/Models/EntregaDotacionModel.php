<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class EntregaDotacionModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_entrega_dotacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'creado_por_tipo', 'id_consultor',
        'fecha_entrega', 'hora', 'lugar',
        'responsable_entrega', 'tipo_dotacion', 'observaciones',
        'token_inscripcion', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function findByTokenInscripcion(string $token): ?array
    {
        if (empty($token)) return null;
        return $this->where('token_inscripcion', $token)->first();
    }

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_entrega', 'DESC')
            ->findAll();
    }

    public function getAllPendientes(): array
    {
        return $this->select('tbl_entrega_dotacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_entrega_dotacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_entrega_dotacion.id_consultor', 'left')
            ->whereIn('tbl_entrega_dotacion.estado', ['borrador', 'esperando_firmas'])
            ->orderBy('tbl_entrega_dotacion.fecha_entrega', 'DESC')
            ->findAll();
    }
}
