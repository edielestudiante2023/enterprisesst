<?php

namespace App\Models;

use CodeIgniter\Model;

class PtaTransicionesModel extends Model
{
    protected $table            = 'tbl_pta_transiciones';
    protected $primaryKey       = 'id_transicion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_ptacliente',
        'id_cliente',
        'estado_anterior',
        'estado_nuevo',
        'id_usuario',
        'nombre_usuario',
        'fecha_transicion',
    ];

    protected $useTimestamps = false;

    public function getWithFilters(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->select('tbl_pta_transiciones.*, pta.actividad_plandetrabajo, pta.numeral_plandetrabajo, pta.phva_plandetrabajo, pta.responsable_sugerido_plandetrabajo, pta.estado_actividad AS estado_actual');
        $builder->join('tbl_pta_cliente pta', 'pta.id_ptacliente = tbl_pta_transiciones.id_ptacliente', 'left');

        if (!empty($filters['id_cliente'])) {
            $builder->where('tbl_pta_transiciones.id_cliente', $filters['id_cliente']);
        }
        if (!empty($filters['estado_nuevo'])) {
            $builder->where('tbl_pta_transiciones.estado_nuevo', $filters['estado_nuevo']);
        }
        if (!empty($filters['id_usuario'])) {
            $builder->where('tbl_pta_transiciones.id_usuario', $filters['id_usuario']);
        }
        if (!empty($filters['fecha_desde'])) {
            $builder->where('tbl_pta_transiciones.fecha_transicion >=', $filters['fecha_desde'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where('tbl_pta_transiciones.fecha_transicion <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        $builder->orderBy('tbl_pta_transiciones.fecha_transicion', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getStatsByEstado(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->select('estado_nuevo, COUNT(*) as cantidad');

        if (!empty($filters['id_cliente'])) {
            $builder->where('id_cliente', $filters['id_cliente']);
        }
        if (!empty($filters['fecha_desde'])) {
            $builder->where('fecha_transicion >=', $filters['fecha_desde'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where('fecha_transicion <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        $builder->groupBy('estado_nuevo');

        return $builder->get()->getResultArray();
    }
}
