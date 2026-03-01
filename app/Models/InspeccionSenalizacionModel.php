<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionSenalizacionModel extends Model
{
    protected $table = 'tbl_inspeccion_senalizacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion', 'observaciones',
        'calificacion', 'descripcion_cualitativa',
        'conteo_no_aplica', 'conteo_no_cumple',
        'conteo_parcial', 'conteo_total',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_senalizacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_senalizacion.id_cliente', 'left')
            ->where('tbl_inspeccion_senalizacion.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_senalizacion.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_senalizacion.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_senalizacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_senalizacion.id_cliente', 'left')
            ->where('tbl_inspeccion_senalizacion.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_senalizacion.estado', 'borrador')
            ->orderBy('tbl_inspeccion_senalizacion.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_senalizacion.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_senalizacion.id_consultor', 'left')
            ->where('tbl_inspeccion_senalizacion.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_senalizacion.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
