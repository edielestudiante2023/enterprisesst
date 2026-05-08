<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ListaAsistenciaModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_lista_asistencia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_comite', 'creado_por_tipo', 'id_miembro', 'id_consultor',
        'motivo', 'fecha_actividad', 'hora_inicio', 'hora_fin',
        'modalidad', 'convocada_por', 'lugar', 'agenda',
        'enlace_grabacion', 'observaciones',
        'ruta_pdf', 'estado',
        'token_inscripcion',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function findByTokenInscripcion(string $token): ?array
    {
        if (empty($token)) return null;
        return $this->where('token_inscripcion', $token)->first();
    }

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_actividad', 'DESC')
            ->findAll();
    }

    /**
     * Listas en estado borrador o esperando_firmas (para dashboard de inspecciones)
     */
    public function getAllPendientes(): array
    {
        return $this->select('tbl_lista_asistencia.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_lista_asistencia.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_lista_asistencia.id_consultor', 'left')
            ->whereIn('tbl_lista_asistencia.estado', ['borrador', 'esperando_firmas'])
            ->orderBy('tbl_lista_asistencia.fecha_actividad', 'DESC')
            ->findAll();
    }
}
