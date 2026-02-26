<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaVisitaModel extends Model
{
    protected $table = 'tbl_acta_visita';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_visita', 'hora_visita', 'ubicacion_gps', 'motivo', 'modalidad',
        'cartera', 'observaciones',
        'proxima_reunion_fecha', 'proxima_reunion_hora',
        'firma_administrador', 'firma_vigia', 'firma_consultor',
        'soporte_lavado_tanques', 'soporte_plagas',
        'ruta_pdf', 'estado', 'agenda_id',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    /**
     * Actas de un consultor con nombre del cliente
     */
    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_acta_visita.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente', 'left')
            ->where('tbl_acta_visita.id_consultor', $idConsultor)
            ->orderBy('tbl_acta_visita.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_acta_visita.estado', $estado);
        }

        return $builder->findAll();
    }

    /**
     * Actas pendientes (borrador o pendiente_firma) de un consultor
     */
    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_acta_visita.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente', 'left')
            ->where('tbl_acta_visita.id_consultor', $idConsultor)
            ->whereIn('tbl_acta_visita.estado', ['borrador', 'pendiente_firma'])
            ->orderBy('tbl_acta_visita.updated_at', 'DESC')
            ->findAll();
    }

    /**
     * Todas las actas pendientes (para dashboard admin)
     */
    public function getAllPendientes()
    {
        return $this->select('tbl_acta_visita.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_visita.id_consultor', 'left')
            ->whereIn('tbl_acta_visita.estado', ['borrador', 'pendiente_firma'])
            ->orderBy('tbl_acta_visita.updated_at', 'DESC')
            ->findAll();
    }

    /**
     * Actas de un cliente con nombre del consultor
     */
    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_acta_visita.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_visita.id_consultor', 'left')
            ->where('tbl_acta_visita.id_cliente', $idCliente)
            ->orderBy('tbl_acta_visita.fecha_visita', 'DESC')
            ->findAll();
    }
}
