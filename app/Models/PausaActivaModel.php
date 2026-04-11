<?php

namespace App\Models;

use CodeIgniter\Model;

class PausaActivaModel extends Model
{
    protected $table = 'tbl_pausas_activas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'id_miembro', 'creado_por_tipo',
        'fecha_actividad', 'observaciones',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_pausas_activas.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_pausas_activas.id_consultor', 'left')
            ->where('tbl_pausas_activas.id_cliente', $idCliente)
            ->orderBy('tbl_pausas_activas.fecha_actividad', 'DESC')
            ->findAll();
    }
}
