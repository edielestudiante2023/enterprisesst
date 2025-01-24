<?php

namespace App\Models;

use CodeIgniter\Model;

class PendientesModel extends Model
{
    protected $table = 'tbl_pendientes';
    protected $primaryKey = 'id_pendientes';
    protected $allowedFields = [
        'id_cliente',
        'responsable',
        'tarea_actividad',
        'fecha_asignacion',
        'fecha_cierre',
        'estado',
        'conteo_dias',
        'estado_avance',
        'evidencia_para_cerrarla',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Definir los callbacks para calcular 'conteo_dias'
    protected $beforeInsert = ['calculateConteoDias'];
    protected $beforeUpdate = ['calculateConteoDias'];

    protected $afterFind = ['formatFechaAsignacion'];

    protected function formatFechaAsignacion(array $data)
    {
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as &$row) {
                if (isset($row['fecha_asignacion'])) {
                    $row['fecha_asignacion'] = date('Y-m-d', strtotime($row['fecha_asignacion']));
                }
            }
        }
        return $data;
    }


    /**
     * Calcular 'conteo_dias' antes de insertar o actualizar
     */
    protected function calculateConteoDias(array $data)
    {
        // Obtener los datos del pendiente
        $fechaAsignacion = isset($data['data']['fecha_asignacion']) ? $data['data']['fecha_asignacion'] : null;
        $fechaCierre = isset($data['data']['fecha_cierre']) ? $data['data']['fecha_cierre'] : null;
        $estado = isset($data['data']['estado']) ? $data['data']['estado'] : null;

        if ($fechaAsignacion && $estado) {
            $asignacionDate = new \DateTime($fechaAsignacion);
            $currentDate = new \DateTime();

            if ($estado === 'ABIERTA') {
                // Calcula la diferencia en días entre fecha_asignacion y la fecha actual
                $interval = $asignacionDate->diff($currentDate);
                $conteoDias = $interval->days;
            } elseif ($estado === 'CERRADA' && $fechaCierre) {
                $cierreDate = new \DateTime($fechaCierre);
                $interval = $asignacionDate->diff($cierreDate);
                $conteoDias = $interval->days;
            } else {
                $conteoDias = 0;
            }

            $data['data']['conteo_dias'] = $conteoDias;
        }

        return $data;
    }

    /**
     * Obtener pendientes junto con el nombre del cliente
     */
    public function getPendientesWithCliente()
    {
        return $this->select('tbl_pendientes.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente')
            ->findAll();
    }
}
