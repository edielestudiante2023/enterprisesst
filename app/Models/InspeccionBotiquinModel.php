<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionBotiquinModel extends Model
{
    protected $table = 'tbl_inspeccion_botiquin';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion', 'ubicacion_botiquin',
        'foto_1', 'foto_2',
        'instalado_pared', 'libre_obstaculos', 'lugar_visible', 'con_senalizacion',
        'tipo_botiquin', 'estado_botiquin',
        'foto_tabla_espinal', 'obs_tabla_espinal',
        'estado_collares', 'foto_collares',
        'estado_inmovilizadores', 'foto_inmovilizadores',
        'recomendaciones', 'pendientes_generados',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_botiquin.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin.id_cliente', 'left')
            ->where('tbl_inspeccion_botiquin.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_botiquin.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_botiquin.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_botiquin.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin.id_cliente', 'left')
            ->where('tbl_inspeccion_botiquin.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_botiquin.estado', 'borrador')
            ->orderBy('tbl_inspeccion_botiquin.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_botiquin.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_botiquin.id_consultor', 'left')
            ->where('tbl_inspeccion_botiquin.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_botiquin.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
