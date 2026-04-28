<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ActaCapacitacionModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_acta_capacitacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_comite', 'creado_por_tipo', 'id_miembro', 'id_consultor',
        'tema', 'fecha_capacitacion', 'hora_inicio', 'hora_fin',
        'dictada_por', 'nombre_capacitador', 'entidad_capacitadora',
        'modalidad', 'enlace_grabacion', 'objetivos', 'contenido', 'observaciones',
        'ruta_pdf', 'estado',
        'token_inscripcion',
        'created_at', 'updated_at',
    ];

    public function findByTokenInscripcion(string $token): ?array
    {
        if (empty($token)) return null;
        return $this->where('token_inscripcion', $token)->first();
    }
    protected $useTimestamps = true;

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_capacitacion', 'DESC')
            ->findAll();
    }

    /**
     * Actas en estado borrador o pendiente_firma (para dashboard de inspecciones)
     */
    public function getAllPendientes(): array
    {
        return $this->select('tbl_acta_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->whereIn('tbl_acta_capacitacion.estado', ['borrador', 'pendiente_firma'])
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();
    }
}
