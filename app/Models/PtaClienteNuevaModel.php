<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class PtaClienteNuevaModel extends Model
{
    use TenantScopedModel;

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

    /**
     * Actividades ABIERTAS de un cliente para el mes de la visita
     * Incluye actividades rezagadas (meses anteriores del mismo año)
     */
    public function getAbiertosByClienteYMes(int $idCliente, string $fechaVisita): array
    {
        $mes = (int) date('m', strtotime($fechaVisita));
        $anio = (int) date('Y', strtotime($fechaVisita));

        return $this->where('id_cliente', $idCliente)
            ->where('estado_actividad', 'ABIERTA')
            ->where('YEAR(fecha_propuesta)', $anio)
            ->where('MONTH(fecha_propuesta) <=', $mes)
            ->orderBy('numeral_plandetrabajo', 'ASC')
            ->findAll();
    }
}
