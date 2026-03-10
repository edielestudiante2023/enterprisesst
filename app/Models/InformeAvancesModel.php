<?php

namespace App\Models;

use CodeIgniter\Model;

class InformeAvancesModel extends Model
{
    protected $table = 'tbl_informe_avances';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_desde', 'fecha_hasta', 'anio',
        'puntaje_anterior', 'puntaje_actual', 'diferencia_neta', 'estado_avance',
        'indicador_plan_trabajo', 'indicador_capacitacion',
        'img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion',
        'metricas_desglose_json',
        'resumen_avance', 'observaciones', 'actividades_abiertas', 'actividades_cerradas_periodo',
        'enlace_dashboard', 'acta_visita_url',
        'soporte_1_texto', 'soporte_1_imagen',
        'soporte_2_texto', 'soporte_2_imagen',
        'soporte_3_texto', 'soporte_3_imagen',
        'soporte_4_texto', 'soporte_4_imagen',
        'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_informe_avances.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_informe_avances.id_cliente', 'left')
            ->where('tbl_informe_avances.id_consultor', $idConsultor)
            ->orderBy('tbl_informe_avances.fecha_hasta', 'DESC');

        if ($estado) {
            $builder->where('tbl_informe_avances.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_informe_avances.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_informe_avances.id_cliente', 'left')
            ->where('tbl_informe_avances.id_consultor', $idConsultor)
            ->where('tbl_informe_avances.estado', 'borrador')
            ->orderBy('tbl_informe_avances.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_informe_avances.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_informe_avances.id_cliente', 'left')
            ->where('tbl_informe_avances.estado', 'borrador')
            ->orderBy('tbl_informe_avances.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_informe_avances.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_informe_avances.id_consultor', 'left')
            ->where('tbl_informe_avances.id_cliente', $idCliente)
            ->orderBy('tbl_informe_avances.fecha_hasta', 'DESC')
            ->findAll();
    }

    public function getUltimoByCliente(int $idCliente)
    {
        return $this->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_hasta', 'DESC')
            ->first();
    }
}
