<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionExtintoresModel extends Model
{
    protected $table = 'tbl_inspeccion_extintores';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion', 'fecha_vencimiento_global',
        'numero_extintores_totales',
        'cantidad_abc', 'cantidad_co2', 'cantidad_solkaflam', 'cantidad_agua',
        'capacidad_libras',
        'cantidad_unidades_residenciales', 'cantidad_porteria',
        'cantidad_oficina_admin', 'cantidad_shut_basuras',
        'cantidad_salones_comunales', 'cantidad_cuarto_bombas',
        'cantidad_planta_electrica',
        'recomendaciones_generales',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_extintores.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_extintores.id_cliente', 'left')
            ->where('tbl_inspeccion_extintores.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_extintores.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_extintores.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_extintores.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_extintores.id_cliente', 'left')
            ->where('tbl_inspeccion_extintores.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_extintores.estado', 'borrador')
            ->orderBy('tbl_inspeccion_extintores.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_extintores.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_extintores.id_consultor', 'left')
            ->where('tbl_inspeccion_extintores.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_extintores.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
