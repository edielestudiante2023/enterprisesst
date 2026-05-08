<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_socializaciones - registra envios de socializacion (PDF + email)
 * a colaboradores de un cliente, para socializar miembros del comite o cronograma.
 */
class SocializacionModel extends Model
{
    protected $table = 'tbl_socializaciones';
    protected $primaryKey = 'id_socializacion';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_cliente', 'id_comite', 'id_proceso',
        'tipo_socializacion', 'tipo_comite',
        'id_documento_sst', 'id_documento_evidencia',
        'asunto_email', 'cuerpo_email',
        'destinatarios_json',
        'total_destinatarios', 'enviados_ok', 'fallidos',
        'estado', 'contenido_snapshot',
        'created_by',
    ];

    public function getByProceso(int $idProceso): array
    {
        return $this->where('id_proceso', $idProceso)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
