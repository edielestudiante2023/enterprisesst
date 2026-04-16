<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class InspeccionLocativaModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_inspeccion_locativa';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'id_miembro', 'creado_por_tipo',
        'fecha_inspeccion', 'observaciones',
        'ruta_pdf', 'estado', 'id_documento_sst',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_locativa.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_locativa.id_cliente', 'left')
            ->where('tbl_inspeccion_locativa.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_locativa.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_locativa.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_locativa.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_locativa.id_cliente', 'left')
            ->where('tbl_inspeccion_locativa.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_locativa.estado', 'borrador')
            ->orderBy('tbl_inspeccion_locativa.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_locativa.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_locativa.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_locativa.id_consultor', 'left')
            ->where('tbl_inspeccion_locativa.estado', 'borrador')
            ->orderBy('tbl_inspeccion_locativa.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_locativa.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_locativa.id_consultor', 'left')
            ->where('tbl_inspeccion_locativa.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_locativa.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
