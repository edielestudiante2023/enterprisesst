<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ActaSolicitudMarcarAusenteModel extends Model
{
    use TenantScopedModel;

    protected $table = 'tbl_acta_solicitudes_marcar_ausente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_acta',
        'id_asistente',
        'id_cliente',
        'solicitante_nombre',
        'solicitante_email',
        'solicitante_cargo',
        'justificacion',
        'token',
        'token_expira',
        'estado',
        'aprobado_por',
        'aprobado_at',
        'rechazado_motivo'
    ];

    protected $useTimestamps = false;

    public function crearSolicitud(array $data): int|false
    {
        $data['token'] = bin2hex(random_bytes(32));
        $data['token_expira'] = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $data['estado'] = 'pendiente';

        $this->insert($data);
        $id = $this->getInsertID();

        return $id ?: false;
    }

    public function getByToken(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('estado', 'pendiente')
                    ->where('token_expira >=', date('Y-m-d H:i:s'))
                    ->first();
    }

    public function aprobar(int $id, string $aprobadoPor): bool
    {
        return $this->update($id, [
            'estado' => 'aprobada',
            'aprobado_por' => $aprobadoPor,
            'aprobado_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function rechazar(int $id, string $motivo): bool
    {
        return $this->update($id, [
            'estado' => 'rechazada',
            'rechazado_motivo' => $motivo
        ]);
    }

    /**
     * Verificar si hay solicitud pendiente para un asistente especifico
     */
    public function tieneSolicitudPendientePorAsistente(int $idAsistente): bool
    {
        return $this->where('id_asistente', $idAsistente)
                    ->where('estado', 'pendiente')
                    ->where('token_expira >=', date('Y-m-d H:i:s'))
                    ->countAllResults() > 0;
    }

    /**
     * Obtener mapa [id_asistente => true] de asistentes con solicitud pendiente para un acta
     */
    public function getMapaPendientesPorActa(int $idActa): array
    {
        $rows = $this->where('id_acta', $idActa)
                     ->where('estado', 'pendiente')
                     ->where('token_expira >=', date('Y-m-d H:i:s'))
                     ->findAll();

        $mapa = [];
        foreach ($rows as $r) {
            $mapa[$r['id_asistente']] = true;
        }
        return $mapa;
    }
}
