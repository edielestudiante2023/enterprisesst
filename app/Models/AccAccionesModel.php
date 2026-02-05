<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar Acciones Correctivas, Preventivas y de Mejora (CAPA)
 * Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 - Resolución 0312 de 2019
 */
class AccAccionesModel extends Model
{
    protected $table = 'tbl_acc_acciones';
    protected $primaryKey = 'id_accion';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_hallazgo',
        'tipo_accion',
        'clasificacion_temporal',
        'descripcion_accion',
        'analisis_causa_raiz',
        'causa_raiz_identificada',
        'responsable_id',
        'responsable_nombre',
        'fecha_asignacion',
        'fecha_compromiso',
        'fecha_cierre_real',
        'recursos_requeridos',
        'costo_estimado',
        'estado',
        'notas',
        'motivo_cancelacion',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Estados válidos para transiciones
     */
    public const TRANSICIONES_VALIDAS = [
        'borrador' => ['asignada', 'cancelada'],
        'asignada' => ['en_ejecucion', 'cancelada'],
        'en_ejecucion' => ['en_revision', 'cancelada'],
        'en_revision' => ['en_ejecucion', 'en_verificacion', 'cancelada'],
        'en_verificacion' => ['cerrada_efectiva', 'cerrada_no_efectiva', 'reabierta'],
        'cerrada_efectiva' => [],
        'cerrada_no_efectiva' => ['reabierta'],
        'reabierta' => ['en_ejecucion', 'cancelada'],
        'cancelada' => []
    ];

    /**
     * Obtener acciones por hallazgo
     */
    public function getByHallazgo(int $idHallazgo): array
    {
        return $this->select('tbl_acc_acciones.*, u.nombre_completo as responsable_usuario_nombre, u.email as responsable_email')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                    ->where('id_hallazgo', $idHallazgo)
                    ->orderBy('fecha_asignacion', 'DESC')
                    ->findAll();
    }

    /**
     * Obtener acciones por cliente (a través de hallazgos)
     */
    public function getByCliente(int $idCliente, ?string $estado = null, ?string $tipoAccion = null): array
    {
        $builder = $this->select('tbl_acc_acciones.*,
                                  h.titulo as hallazgo_titulo,
                                  h.numeral_asociado,
                                  h.severidad as hallazgo_severidad,
                                  u.nombre_completo as responsable_usuario_nombre,
                                  u.email as responsable_email')
                        ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                        ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                        ->where('h.id_cliente', $idCliente);

        if ($estado) {
            $builder->where('tbl_acc_acciones.estado', $estado);
        }

        if ($tipoAccion) {
            $builder->where('tbl_acc_acciones.tipo_accion', $tipoAccion);
        }

        return $builder->orderBy('tbl_acc_acciones.fecha_compromiso', 'ASC')->findAll();
    }

    /**
     * Obtener acciones por numeral
     */
    public function getByNumeral(int $idCliente, string $numeral): array
    {
        return $this->select('tbl_acc_acciones.*,
                              h.titulo as hallazgo_titulo,
                              h.tipo_origen,
                              h.severidad,
                              u.nombre_completo as responsable_usuario_nombre')
                    ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                    ->where('h.id_cliente', $idCliente)
                    ->where('h.numeral_asociado', $numeral)
                    ->orderBy('tbl_acc_acciones.fecha_compromiso', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener acción con todos los detalles
     */
    public function getConDetalles(int $idAccion): ?array
    {
        $accion = $this->select('tbl_acc_acciones.*,
                                 h.id_cliente,
                                 h.titulo as hallazgo_titulo,
                                 h.descripcion as hallazgo_descripcion,
                                 h.tipo_origen,
                                 h.numeral_asociado,
                                 h.severidad,
                                 h.fecha_deteccion,
                                 c.nombre_cliente,
                                 c.nit_cliente,
                                 u.nombre_completo as responsable_usuario_nombre,
                                 u.email as responsable_email')
                       ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                       ->join('tbl_clientes c', 'c.id_cliente = h.id_cliente')
                       ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                       ->find($idAccion);

        if ($accion) {
            // Decodificar JSON del análisis de causa raíz
            if (!empty($accion['analisis_causa_raiz']) && is_string($accion['analisis_causa_raiz'])) {
                $accion['analisis_causa_raiz'] = json_decode($accion['analisis_causa_raiz'], true) ?? [];
            }

            // Obtener seguimientos
            $seguimientosModel = new AccSeguimientosModel();
            $accion['seguimientos'] = $seguimientosModel->getByAccion($idAccion);

            // Obtener verificaciones
            $verificacionesModel = new AccVerificacionesModel();
            $accion['verificaciones'] = $verificacionesModel->getByAccion($idAccion);

            // Calcular días restantes o vencidos
            if (!empty($accion['fecha_compromiso'])) {
                $fechaCompromiso = new \DateTime($accion['fecha_compromiso']);
                $hoy = new \DateTime();
                $diff = $hoy->diff($fechaCompromiso);
                $accion['dias_restantes'] = $fechaCompromiso > $hoy ? $diff->days : -$diff->days;
                $accion['esta_vencida'] = $fechaCompromiso < $hoy && !in_array($accion['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']);
            }
        }

        return $accion;
    }

    /**
     * Crear acción nueva
     */
    public function crearAccion(array $data): int|false
    {
        // Estado inicial
        $data['estado'] = $data['estado'] ?? 'borrador';

        // Fecha de asignación
        $data['fecha_asignacion'] = $data['fecha_asignacion'] ?? date('Y-m-d');

        // Convertir análisis de causa raíz a JSON si es array
        if (isset($data['analisis_causa_raiz']) && is_array($data['analisis_causa_raiz'])) {
            $data['analisis_causa_raiz'] = json_encode($data['analisis_causa_raiz'], JSON_UNESCAPED_UNICODE);
        }

        $idAccion = $this->insert($data);

        // Actualizar estado del hallazgo
        if ($idAccion && !empty($data['id_hallazgo'])) {
            $hallazgosModel = new AccHallazgosModel();
            $hallazgosModel->actualizarEstadoSegunAcciones($data['id_hallazgo']);
        }

        return $idAccion;
    }

    /**
     * Cambiar estado de una acción
     */
    public function cambiarEstado(int $idAccion, string $nuevoEstado, ?int $userId = null, ?string $notas = null): bool
    {
        $accion = $this->find($idAccion);
        if (!$accion) {
            return false;
        }

        // Validar transición
        $estadoActual = $accion['estado'];
        if (!in_array($nuevoEstado, self::TRANSICIONES_VALIDAS[$estadoActual] ?? [])) {
            return false;
        }

        $updateData = ['estado' => $nuevoEstado];

        // Si se cierra, registrar fecha
        if (in_array($nuevoEstado, ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])) {
            $updateData['fecha_cierre_real'] = date('Y-m-d');
        }

        // Si se cancela, requiere motivo
        if ($nuevoEstado === 'cancelada' && $notas) {
            $updateData['motivo_cancelacion'] = $notas;
        }

        $resultado = $this->update($idAccion, $updateData);

        // Registrar cambio de estado en seguimientos
        if ($resultado && $userId) {
            $seguimientosModel = new AccSeguimientosModel();
            $seguimientosModel->registrarCambioEstado($idAccion, $estadoActual, $nuevoEstado, $userId, $notas);
        }

        // Actualizar estado del hallazgo
        if ($resultado) {
            $hallazgosModel = new AccHallazgosModel();
            $hallazgosModel->actualizarEstadoSegunAcciones($accion['id_hallazgo']);
        }

        return $resultado;
    }

    /**
     * Guardar análisis de causa raíz (diálogo IA)
     */
    public function guardarAnalisisCausaRaiz(int $idAccion, array $dialogo, ?string $causaIdentificada = null): bool
    {
        $data = [
            'analisis_causa_raiz' => json_encode($dialogo, JSON_UNESCAPED_UNICODE)
        ];

        if ($causaIdentificada) {
            $data['causa_raiz_identificada'] = $causaIdentificada;
        }

        return $this->update($idAccion, $data);
    }

    /**
     * Obtener acciones vencidas
     */
    public function getVencidas(int $idCliente): array
    {
        return $this->select('tbl_acc_acciones.*, h.titulo as hallazgo_titulo, h.numeral_asociado, u.nombre_completo as responsable_usuario_nombre')
                    ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                    ->where('h.id_cliente', $idCliente)
                    ->whereNotIn('tbl_acc_acciones.estado', ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])
                    ->where('tbl_acc_acciones.fecha_compromiso <', date('Y-m-d'))
                    ->orderBy('tbl_acc_acciones.fecha_compromiso', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener acciones próximas a vencer (7 días)
     */
    public function getProximasVencer(int $idCliente, int $dias = 7): array
    {
        $fechaLimite = date('Y-m-d', strtotime("+{$dias} days"));

        return $this->select('tbl_acc_acciones.*, h.titulo as hallazgo_titulo, h.numeral_asociado, u.nombre_completo as responsable_usuario_nombre, u.email as responsable_email')
                    ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                    ->where('h.id_cliente', $idCliente)
                    ->whereNotIn('tbl_acc_acciones.estado', ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])
                    ->where('tbl_acc_acciones.fecha_compromiso >=', date('Y-m-d'))
                    ->where('tbl_acc_acciones.fecha_compromiso <=', $fechaLimite)
                    ->orderBy('tbl_acc_acciones.fecha_compromiso', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener acciones por responsable
     */
    public function getByResponsable(int $responsableId, ?string $estado = null): array
    {
        $builder = $this->select('tbl_acc_acciones.*,
                                  h.titulo as hallazgo_titulo,
                                  h.numeral_asociado,
                                  c.nombre_cliente')
                        ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                        ->join('tbl_clientes c', 'c.id_cliente = h.id_cliente')
                        ->where('tbl_acc_acciones.responsable_id', $responsableId);

        if ($estado) {
            $builder->where('tbl_acc_acciones.estado', $estado);
        }

        return $builder->orderBy('tbl_acc_acciones.fecha_compromiso', 'ASC')->findAll();
    }

    /**
     * Obtener estadísticas por cliente
     */
    public function getEstadisticas(int $idCliente, ?int $anio = null): array
    {
        $builder = $this->select('tbl_acc_acciones.*')
                        ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                        ->where('h.id_cliente', $idCliente);

        if ($anio) {
            $builder->where('YEAR(tbl_acc_acciones.fecha_asignacion)', $anio);
        }

        $acciones = $builder->findAll();

        $stats = [
            'total' => count($acciones),
            'por_estado' => [
                'borrador' => 0,
                'asignada' => 0,
                'en_ejecucion' => 0,
                'en_revision' => 0,
                'en_verificacion' => 0,
                'cerrada_efectiva' => 0,
                'cerrada_no_efectiva' => 0,
                'reabierta' => 0,
                'cancelada' => 0
            ],
            'por_tipo' => [
                'correctiva' => 0,
                'preventiva' => 0,
                'mejora' => 0
            ],
            'vencidas' => 0,
            'a_tiempo' => 0,
            'efectividad' => 0
        ];

        $cerradasTotal = 0;
        $cerradasEfectivas = 0;

        foreach ($acciones as $a) {
            // Por estado
            if (isset($stats['por_estado'][$a['estado']])) {
                $stats['por_estado'][$a['estado']]++;
            }

            // Por tipo
            if (isset($stats['por_tipo'][$a['tipo_accion']])) {
                $stats['por_tipo'][$a['tipo_accion']]++;
            }

            // Vencidas
            if (!in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                $a['fecha_compromiso'] < date('Y-m-d')) {
                $stats['vencidas']++;
            }

            // Para cálculo de efectividad
            if ($a['estado'] === 'cerrada_efectiva') {
                $cerradasEfectivas++;
                $cerradasTotal++;
            } elseif ($a['estado'] === 'cerrada_no_efectiva') {
                $cerradasTotal++;
            }
        }

        // Calcular % de cierre a tiempo
        $stats['a_tiempo'] = $stats['total'] - $stats['vencidas'];

        // Calcular efectividad
        $stats['efectividad'] = $cerradasTotal > 0
            ? round(($cerradasEfectivas / $cerradasTotal) * 100, 1)
            : 0;

        return $stats;
    }

    /**
     * Obtener acciones para notificación (vencen en N días)
     */
    public function getParaNotificacion(int $diasAntes = 3): array
    {
        $fechaObjetivo = date('Y-m-d', strtotime("+{$diasAntes} days"));

        return $this->select('tbl_acc_acciones.*,
                              h.titulo as hallazgo_titulo,
                              h.id_cliente,
                              c.nombre_cliente,
                              u.nombre_completo as responsable_usuario_nombre,
                              u.email as responsable_email')
                    ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = tbl_acc_acciones.id_hallazgo')
                    ->join('tbl_clientes c', 'c.id_cliente = h.id_cliente')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_acciones.responsable_id', 'left')
                    ->whereNotIn('tbl_acc_acciones.estado', ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])
                    ->where('tbl_acc_acciones.fecha_compromiso', $fechaObjetivo)
                    ->findAll();
    }
}
