<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar Seguimientos y Evidencias de Acciones
 * Bitácora de avances, evidencias documentales y cambios de estado
 */
class AccSeguimientosModel extends Model
{
    protected $table = 'tbl_acc_seguimientos';
    protected $primaryKey = 'id_seguimiento';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_accion',
        'tipo_seguimiento',
        'descripcion',
        'porcentaje_avance',
        'archivo_adjunto',
        'nombre_archivo',
        'tipo_archivo',
        'registrado_por',
        'registrado_por_nombre',
        'created_at'
    ];

    protected $useTimestamps = false; // Manejaremos created_at manualmente

    /**
     * Obtener seguimientos por acción
     */
    public function getByAccion(int $idAccion): array
    {
        return $this->select('tbl_acc_seguimientos.*, u.nombre_completo as usuario_nombre, u.email as usuario_email')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_seguimientos.registrado_por', 'left')
                    ->where('id_accion', $idAccion)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Registrar avance de una acción
     */
    public function registrarAvance(int $idAccion, string $descripcion, int $porcentaje, int $userId, ?string $userName = null): int|false
    {
        return $this->insert([
            'id_accion' => $idAccion,
            'tipo_seguimiento' => 'avance',
            'descripcion' => $descripcion,
            'porcentaje_avance' => min(100, max(0, $porcentaje)),
            'registrado_por' => $userId,
            'registrado_por_nombre' => $userName,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registrar evidencia con archivo
     */
    public function registrarEvidencia(int $idAccion, string $descripcion, array $archivo, int $userId, ?string $userName = null): int|false
    {
        return $this->insert([
            'id_accion' => $idAccion,
            'tipo_seguimiento' => 'evidencia',
            'descripcion' => $descripcion,
            'archivo_adjunto' => $archivo['ruta'] ?? null,
            'nombre_archivo' => $archivo['nombre'] ?? null,
            'tipo_archivo' => $archivo['tipo'] ?? null,
            'registrado_por' => $userId,
            'registrado_por_nombre' => $userName,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registrar cambio de estado automático
     */
    public function registrarCambioEstado(int $idAccion, string $estadoAnterior, string $estadoNuevo, int $userId, ?string $notas = null): int|false
    {
        $descripcion = "Cambio de estado: {$estadoAnterior} → {$estadoNuevo}";
        if ($notas) {
            $descripcion .= ". Notas: {$notas}";
        }

        return $this->insert([
            'id_accion' => $idAccion,
            'tipo_seguimiento' => 'cambio_estado',
            'descripcion' => $descripcion,
            'registrado_por' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registrar comentario general
     */
    public function registrarComentario(int $idAccion, string $comentario, int $userId, ?string $userName = null): int|false
    {
        return $this->insert([
            'id_accion' => $idAccion,
            'tipo_seguimiento' => 'comentario',
            'descripcion' => $comentario,
            'registrado_por' => $userId,
            'registrado_por_nombre' => $userName,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registrar recordatorio enviado
     */
    public function registrarRecordatorio(int $idAccion, string $mensaje, int $userId): int|false
    {
        return $this->insert([
            'id_accion' => $idAccion,
            'tipo_seguimiento' => 'recordatorio',
            'descripcion' => "Recordatorio enviado: {$mensaje}",
            'registrado_por' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registrar solicitud de prórroga
     */
    public function registrarSolicitudProrroga(int $idAccion, string $nuevaFecha, string $justificacion, int $userId, ?string $userName = null): int|false
    {
        return $this->insert([
            'id_accion' => $idAccion,
            'tipo_seguimiento' => 'solicitud_prorroga',
            'descripcion' => "Solicitud de prórroga a fecha: {$nuevaFecha}. Justificación: {$justificacion}",
            'registrado_por' => $userId,
            'registrado_por_nombre' => $userName,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtener último porcentaje de avance de una acción
     */
    public function getUltimoPorcentaje(int $idAccion): int
    {
        $ultimo = $this->where('id_accion', $idAccion)
                       ->where('tipo_seguimiento', 'avance')
                       ->where('porcentaje_avance IS NOT NULL')
                       ->orderBy('created_at', 'DESC')
                       ->first();

        return $ultimo['porcentaje_avance'] ?? 0;
    }

    /**
     * Contar evidencias de una acción
     */
    public function contarEvidencias(int $idAccion): int
    {
        return $this->where('id_accion', $idAccion)
                    ->where('tipo_seguimiento', 'evidencia')
                    ->where('archivo_adjunto IS NOT NULL')
                    ->countAllResults();
    }

    /**
     * Obtener evidencias de una acción (solo archivos)
     */
    public function getEvidencias(int $idAccion): array
    {
        return $this->select('id_seguimiento, descripcion, archivo_adjunto, nombre_archivo, tipo_archivo, registrado_por_nombre, created_at')
                    ->where('id_accion', $idAccion)
                    ->where('tipo_seguimiento', 'evidencia')
                    ->where('archivo_adjunto IS NOT NULL')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtener timeline de una acción (todos los seguimientos formateados)
     */
    public function getTimeline(int $idAccion): array
    {
        $seguimientos = $this->getByAccion($idAccion);

        $iconos = [
            'avance' => 'bi-graph-up-arrow',
            'evidencia' => 'bi-paperclip',
            'comentario' => 'bi-chat-dots',
            'cambio_estado' => 'bi-arrow-repeat',
            'recordatorio' => 'bi-bell',
            'solicitud_prorroga' => 'bi-calendar-plus'
        ];

        $colores = [
            'avance' => 'primary',
            'evidencia' => 'success',
            'comentario' => 'info',
            'cambio_estado' => 'warning',
            'recordatorio' => 'secondary',
            'solicitud_prorroga' => 'danger'
        ];

        foreach ($seguimientos as &$s) {
            $s['icono'] = $iconos[$s['tipo_seguimiento']] ?? 'bi-circle';
            $s['color'] = $colores[$s['tipo_seguimiento']] ?? 'secondary';
            $s['fecha_formateada'] = date('d/m/Y H:i', strtotime($s['created_at']));
        }

        return $seguimientos;
    }

    /**
     * Obtener resumen de seguimientos por acción
     */
    public function getResumen(int $idAccion): array
    {
        return [
            'total_seguimientos' => $this->where('id_accion', $idAccion)->countAllResults(),
            'total_evidencias' => $this->contarEvidencias($idAccion),
            'ultimo_porcentaje' => $this->getUltimoPorcentaje($idAccion),
            'ultimo_seguimiento' => $this->where('id_accion', $idAccion)
                                         ->orderBy('created_at', 'DESC')
                                         ->first()
        ];
    }
}
