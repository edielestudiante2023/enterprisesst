<?php

namespace App\Models;

use CodeIgniter\Model;

class ActaNotificacionModel extends Model
{
    protected $table = 'tbl_actas_notificaciones';
    protected $primaryKey = 'id_notificacion';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_cliente',
        'tipo',
        'id_acta',
        'id_compromiso',
        'id_asistente',
        'id_miembro',
        'destinatario_email',
        'destinatario_nombre',
        'destinatario_tipo',
        'asunto',
        'cuerpo',
        'estado',
        'programado_para',
        'enviado_at',
        'error_mensaje',
        'intentos'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Programar notificación
     */
    public function programar(array $data): int|false
    {
        $data['estado'] = 'pendiente';
        $data['programado_para'] = $data['programado_para'] ?? date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Obtener notificaciones pendientes de envío
     */
    public function getPendientesEnvio(int $limite = 50): array
    {
        return $this->where('estado', 'pendiente')
                    ->where('programado_para <=', date('Y-m-d H:i:s'))
                    ->where('intentos <', 3)
                    ->orderBy('programado_para', 'ASC')
                    ->limit($limite)
                    ->findAll();
    }

    /**
     * Marcar como enviada
     */
    public function marcarEnviada(int $idNotificacion): bool
    {
        return $this->update($idNotificacion, [
            'estado' => 'enviado',
            'enviado_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marcar como fallida
     */
    public function marcarFallida(int $idNotificacion, string $error): bool
    {
        return $this->update($idNotificacion, [
            'estado' => 'fallido',
            'error_mensaje' => $error,
            'intentos' => $this->find($idNotificacion)['intentos'] + 1
        ]);
    }

    /**
     * Reintentar envío
     */
    public function reintentar(int $idNotificacion): bool
    {
        $notif = $this->find($idNotificacion);
        if (!$notif || $notif['intentos'] >= 3) {
            return false;
        }

        return $this->update($idNotificacion, [
            'estado' => 'pendiente',
            'error_mensaje' => null
        ]);
    }

    /**
     * Programar solicitud de firma
     */
    public function programarSolicitudFirma(int $idActa, int $idAsistente, string $email, string $nombre, int $idCliente, string $numeroActa): int|false
    {
        return $this->programar([
            'id_cliente' => $idCliente,
            'tipo' => 'firma_solicitada',
            'id_acta' => $idActa,
            'id_asistente' => $idAsistente,
            'destinatario_email' => $email,
            'destinatario_nombre' => $nombre,
            'destinatario_tipo' => 'miembro',
            'asunto' => "Firma requerida - Acta {$numeroActa}",
            'cuerpo' => "Se requiere su firma para el acta {$numeroActa}. Por favor ingrese al enlace para firmar."
        ]);
    }

    /**
     * Programar recordatorio de firma
     */
    public function programarRecordatorioFirma(int $idActa, int $idAsistente, string $email, string $nombre, int $idCliente, string $numeroActa): int|false
    {
        return $this->programar([
            'id_cliente' => $idCliente,
            'tipo' => 'firma_recordatorio',
            'id_acta' => $idActa,
            'id_asistente' => $idAsistente,
            'destinatario_email' => $email,
            'destinatario_nombre' => $nombre,
            'destinatario_tipo' => 'miembro',
            'asunto' => "RECORDATORIO: Firma pendiente - Acta {$numeroActa}",
            'cuerpo' => "Aún no ha firmado el acta {$numeroActa}. Por favor ingrese al enlace para completar su firma."
        ]);
    }

    /**
     * Programar notificación de tarea asignada
     */
    public function programarTareaAsignada(int $idCompromiso, string $email, string $nombre, int $idCliente, string $descripcion, string $fechaVencimiento): int|false
    {
        return $this->programar([
            'id_cliente' => $idCliente,
            'tipo' => 'tarea_asignada',
            'id_compromiso' => $idCompromiso,
            'destinatario_email' => $email,
            'destinatario_nombre' => $nombre,
            'destinatario_tipo' => 'responsable',
            'asunto' => "Nueva tarea asignada - Vence {$fechaVencimiento}",
            'cuerpo' => "Se le ha asignado una nueva tarea: {$descripcion}. Fecha límite: {$fechaVencimiento}"
        ]);
    }

    /**
     * Programar alerta de tarea por vencer
     */
    public function programarTareaPorVencer(int $idCompromiso, string $email, string $nombre, int $idCliente, string $descripcion, string $fechaVencimiento): int|false
    {
        return $this->programar([
            'id_cliente' => $idCliente,
            'tipo' => 'tarea_por_vencer',
            'id_compromiso' => $idCompromiso,
            'destinatario_email' => $email,
            'destinatario_nombre' => $nombre,
            'destinatario_tipo' => 'responsable',
            'asunto' => "ALERTA: Tarea próxima a vencer - {$fechaVencimiento}",
            'cuerpo' => "La tarea '{$descripcion}' vence el {$fechaVencimiento}. Por favor actualice su estado."
        ]);
    }

    /**
     * Programar alerta de sin acta del mes
     */
    public function programarAlertaSinActa(int $idCliente, string $email, string $nombre, string $tipoComite, int $mes, int $anio): int|false
    {
        $nombreMes = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$mes];

        return $this->programar([
            'id_cliente' => $idCliente,
            'tipo' => 'alerta_sin_acta',
            'destinatario_email' => $email,
            'destinatario_nombre' => $nombre,
            'destinatario_tipo' => 'consultor',
            'asunto' => "ALERTA: Sin acta de {$tipoComite} - {$nombreMes} {$anio}",
            'cuerpo' => "No se ha registrado el acta de {$tipoComite} correspondiente a {$nombreMes} de {$anio}."
        ]);
    }

    /**
     * Programar resumen semanal para consultor
     */
    public function programarResumenSemanal(int $idCliente, string $email, string $nombre, array $resumen): int|false
    {
        $cuerpo = "Resumen semanal de comités:\n\n";
        $cuerpo .= "- Actas pendientes de firma: {$resumen['actas_pendientes']}\n";
        $cuerpo .= "- Tareas vencidas: {$resumen['tareas_vencidas']}\n";
        $cuerpo .= "- Tareas próximas a vencer: {$resumen['tareas_por_vencer']}\n";

        return $this->programar([
            'id_cliente' => $idCliente,
            'tipo' => 'resumen_semanal',
            'destinatario_email' => $email,
            'destinatario_nombre' => $nombre,
            'destinatario_tipo' => 'consultor',
            'asunto' => "Resumen semanal de comités - " . date('d/m/Y'),
            'cuerpo' => $cuerpo
        ]);
    }

    /**
     * Cancelar notificaciones pendientes de un acta
     */
    public function cancelarPorActa(int $idActa): int
    {
        return $this->where('id_acta', $idActa)
                    ->where('estado', 'pendiente')
                    ->update(null, ['estado' => 'cancelado']);
    }

    /**
     * Obtener historial de notificaciones de un acta
     */
    public function getHistorialActa(int $idActa): array
    {
        return $this->where('id_acta', $idActa)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Contar notificaciones pendientes por cliente
     */
    public function contarPendientesPorCliente(int $idCliente): int
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('estado', 'pendiente')
                    ->countAllResults();
    }
}
