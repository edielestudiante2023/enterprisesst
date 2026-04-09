<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaSolicitudReaperturaModel extends Model
{
    protected $table = 'tbl_acta_solicitudes_reapertura';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_acta',
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

    /**
     * Crear solicitud con token automático
     */
    public function crearSolicitud(array $data): int|false
    {
        $data['token'] = bin2hex(random_bytes(32));
        $data['token_expira'] = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $data['estado'] = 'pendiente';

        $this->insert($data);
        $id = $this->getInsertID();

        return $id ?: false;
    }

    /**
     * Obtener solicitud válida por token
     */
    public function getByToken(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('estado', 'pendiente')
                    ->where('token_expira >=', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Aprobar solicitud
     */
    public function aprobar(int $id, string $aprobadoPor): bool
    {
        return $this->update($id, [
            'estado' => 'aprobada',
            'aprobado_por' => $aprobadoPor,
            'aprobado_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Rechazar solicitud
     */
    public function rechazar(int $id, string $motivo): bool
    {
        return $this->update($id, [
            'estado' => 'rechazada',
            'rechazado_motivo' => $motivo
        ]);
    }

    /**
     * Verificar si hay solicitud pendiente para un acta
     */
    public function tieneSolicitudPendiente(int $idActa): bool
    {
        return $this->where('id_acta', $idActa)
                    ->where('estado', 'pendiente')
                    ->where('token_expira >=', date('Y-m-d H:i:s'))
                    ->countAllResults() > 0;
    }

    /**
     * Obtener última solicitud de un acta
     */
    public function getUltimaPorActa(int $idActa): ?array
    {
        return $this->where('id_acta', $idActa)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
}
