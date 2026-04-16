<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class RegistroAsistenciaModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_registro_asistencia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_sesion', 'tema', 'lugar', 'objetivo',
        'capacitador', 'tipo_reunion', 'material', 'tiempo_horas', 'observaciones',
        'ruta_pdf_asistencia', 'estado',
    ];
    protected $useTimestamps = true;

    public function getAllPendientes()
    {
        return $this->select('tbl_registro_asistencia.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_registro_asistencia.id_cliente', 'left')
            ->where('tbl_registro_asistencia.estado', 'borrador')
            ->orderBy('tbl_registro_asistencia.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_registro_asistencia.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_registro_asistencia.id_consultor', 'left')
            ->where('tbl_registro_asistencia.id_cliente', $idCliente)
            ->orderBy('tbl_registro_asistencia.fecha_sesion', 'DESC')
            ->findAll();
    }
}
