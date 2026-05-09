<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class InspeccionEppModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_inspeccion_epp';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'id_miembro', 'creado_por_tipo',
        'fecha_inspeccion', 'observaciones',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_epp.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_epp.id_cliente', 'left')
            ->where('tbl_inspeccion_epp.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_epp.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_epp.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_epp.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_epp.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_epp.id_consultor', 'left')
            ->where('tbl_inspeccion_epp.estado', 'borrador')
            ->orderBy('tbl_inspeccion_epp.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_epp.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_epp.id_consultor', 'left')
            ->where('tbl_inspeccion_epp.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_epp.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
