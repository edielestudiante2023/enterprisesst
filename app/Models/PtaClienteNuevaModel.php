<?php

namespace App\Models;

use CodeIgniter\Model;

class PtaClienteNuevaModel extends Model
{
    protected $table = 'tbl_pta_cliente'; // Nombre de la tabla
    protected $primaryKey = 'id_ptacliente'; // Clave primaria

    protected $allowedFields = [
        'id_cliente',
        'tipo_servicio',
        'phva_plandetrabajo',
        'numeral_plandetrabajo',
        'actividad_plandetrabajo',
        'responsable_sugerido_plandetrabajo',
        'fecha_propuesta',
        'fecha_cierre',
        'responsable_definido_paralaactividad',
        'estado_actividad',
        'porcentaje_avance',
        'semana',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener registros filtrados.
     */
    public function getFilteredData($clienteId = null, $estado = null, $phva = null, $responsable = null, $fecha = null)
    {
        $query = $this->select('*');

        if (!empty($clienteId)) {
            $query->where('id_cliente', $clienteId);
        }

        if (!empty($estado)) {
            $query->where('estado_actividad', $estado);
        }

        if (!empty($phva)) {
            $query->where('phva_plandetrabajo', $phva);
        }

        if (!empty($responsable)) {
            $query->where('responsable_sugerido_plandetrabajo', $responsable);
        }

        if (!empty($fecha)) {
            $query->where('fecha_propuesta', $fecha);
        }

        return $query->findAll();
    }
}
