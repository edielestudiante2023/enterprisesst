<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar Verificaciones de Efectividad
 * Cumplimiento del numeral 7.1.2 - Resolución 0312 de 2019
 */
class AccVerificacionesModel extends Model
{
    protected $table = 'tbl_acc_verificaciones';
    protected $primaryKey = 'id_verificacion';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_accion',
        'metodo_verificacion',
        'resultado',
        'observaciones',
        'evidencia_verificacion',
        'requiere_nueva_accion',
        'id_nueva_accion',
        'verificado_por',
        'verificado_por_nombre',
        'fecha_verificacion',
        'fecha_proxima_verificacion'
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';

    /**
     * Métodos de verificación con descripción
     */
    public const METODOS_VERIFICACION = [
        'inspeccion' => [
            'nombre' => 'Inspección de verificación',
            'descripcion' => 'Verificación en sitio mediante inspección visual o técnica',
            'icono' => 'bi-search'
        ],
        'documental' => [
            'nombre' => 'Revisión documental',
            'descripcion' => 'Revisión de documentos, registros, procedimientos actualizados',
            'icono' => 'bi-file-earmark-check'
        ],
        'indicador' => [
            'nombre' => 'Verificación por indicador',
            'descripcion' => 'Validación mediante mejora en indicadores de gestión',
            'icono' => 'bi-graph-up'
        ],
        'observacion' => [
            'nombre' => 'Observación directa',
            'descripcion' => 'Observación del comportamiento o práctica de trabajo',
            'icono' => 'bi-eye'
        ],
        'entrevista' => [
            'nombre' => 'Entrevista a trabajadores',
            'descripcion' => 'Validación mediante entrevistas al personal involucrado',
            'icono' => 'bi-people'
        ],
        'auditoria' => [
            'nombre' => 'Verificación en auditoría',
            'descripcion' => 'Confirmación durante auditoría interna del SG-SST',
            'icono' => 'bi-clipboard-check'
        ]
    ];

    /**
     * Obtener verificaciones por acción
     */
    public function getByAccion(int $idAccion): array
    {
        return $this->select('tbl_acc_verificaciones.*, u.nombre_completo as verificador_nombre, u.email as verificador_email')
                    ->join('tbl_usuarios u', 'u.id_usuario = tbl_acc_verificaciones.verificado_por', 'left')
                    ->where('id_accion', $idAccion)
                    ->orderBy('fecha_verificacion', 'DESC')
                    ->findAll();
    }

    /**
     * Obtener última verificación de una acción
     */
    public function getUltimaVerificacion(int $idAccion): ?array
    {
        return $this->where('id_accion', $idAccion)
                    ->orderBy('fecha_verificacion', 'DESC')
                    ->first();
    }

    /**
     * Registrar verificación de efectividad
     */
    public function registrarVerificacion(array $data): int|false
    {
        // Asegurar fecha
        $data['fecha_verificacion'] = $data['fecha_verificacion'] ?? date('Y-m-d');

        $idVerificacion = $this->insert($data);

        if ($idVerificacion) {
            // Actualizar estado de la acción según resultado
            $accionesModel = new AccAccionesModel();

            switch ($data['resultado']) {
                case 'efectiva':
                    $accionesModel->cambiarEstado($data['id_accion'], 'cerrada_efectiva', $data['verificado_por']);
                    break;

                case 'no_efectiva':
                    $accionesModel->cambiarEstado($data['id_accion'], 'cerrada_no_efectiva', $data['verificado_por']);
                    break;

                case 'parcialmente_efectiva':
                    // Si es parcial, programar próxima verificación
                    if (!empty($data['fecha_proxima_verificacion'])) {
                        // Mantener en verificación para seguimiento
                    }
                    break;
            }

            // Registrar en seguimientos
            $seguimientosModel = new AccSeguimientosModel();
            $seguimientosModel->registrarCambioEstado(
                $data['id_accion'],
                'en_verificacion',
                $data['resultado'] === 'efectiva' ? 'cerrada_efectiva' : ($data['resultado'] === 'no_efectiva' ? 'cerrada_no_efectiva' : 'en_verificacion'),
                $data['verificado_por'],
                "Verificación: {$data['resultado']}. Método: {$data['metodo_verificacion']}"
            );
        }

        return $idVerificacion;
    }

    /**
     * Crear nueva acción desde verificación no efectiva
     */
    public function crearAccionDesdeVerificacion(int $idVerificacion, int $userId): int|false
    {
        $verificacion = $this->find($idVerificacion);
        if (!$verificacion || $verificacion['resultado'] !== 'no_efectiva') {
            return false;
        }

        $accionesModel = new AccAccionesModel();
        $accionOriginal = $accionesModel->find($verificacion['id_accion']);

        if (!$accionOriginal) {
            return false;
        }

        // Crear nueva acción correctiva basada en la original
        $nuevaAccion = [
            'id_hallazgo' => $accionOriginal['id_hallazgo'],
            'tipo_accion' => 'correctiva',
            'clasificacion_temporal' => 'corto_plazo',
            'descripcion_accion' => "Acción adicional derivada de verificación no efectiva de acción #{$accionOriginal['id_accion']}: " . $accionOriginal['descripcion_accion'],
            'responsable_id' => $accionOriginal['responsable_id'],
            'responsable_nombre' => $accionOriginal['responsable_nombre'],
            'fecha_asignacion' => date('Y-m-d'),
            'fecha_compromiso' => date('Y-m-d', strtotime('+30 days')),
            'estado' => 'asignada',
            'notas' => "Generada automáticamente desde verificación #{$idVerificacion}",
            'created_by' => $userId
        ];

        $idNuevaAccion = $accionesModel->crearAccion($nuevaAccion);

        // Actualizar verificación con referencia a nueva acción
        if ($idNuevaAccion) {
            $this->update($idVerificacion, [
                'requiere_nueva_accion' => 1,
                'id_nueva_accion' => $idNuevaAccion
            ]);
        }

        return $idNuevaAccion;
    }

    /**
     * Obtener verificaciones pendientes (acciones en estado de verificación)
     */
    public function getAccionesPendientesVerificacion(int $idCliente): array
    {
        $accionesModel = new AccAccionesModel();
        return $accionesModel->getByCliente($idCliente, 'en_verificacion');
    }

    /**
     * Obtener estadísticas de efectividad por cliente
     */
    public function getEstadisticasEfectividad(int $idCliente, ?int $anio = null): array
    {
        $builder = $this->select('tbl_acc_verificaciones.*')
                        ->join('tbl_acc_acciones a', 'a.id_accion = tbl_acc_verificaciones.id_accion')
                        ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = a.id_hallazgo')
                        ->where('h.id_cliente', $idCliente);

        if ($anio) {
            $builder->where('YEAR(tbl_acc_verificaciones.fecha_verificacion)', $anio);
        }

        $verificaciones = $builder->findAll();

        $stats = [
            'total_verificaciones' => count($verificaciones),
            'efectivas' => 0,
            'parcialmente_efectivas' => 0,
            'no_efectivas' => 0,
            'por_metodo' => [],
            'tasa_efectividad' => 0
        ];

        foreach ($verificaciones as $v) {
            switch ($v['resultado']) {
                case 'efectiva':
                    $stats['efectivas']++;
                    break;
                case 'parcialmente_efectiva':
                    $stats['parcialmente_efectivas']++;
                    break;
                case 'no_efectiva':
                    $stats['no_efectivas']++;
                    break;
            }

            // Por método
            $metodo = $v['metodo_verificacion'];
            if (!isset($stats['por_metodo'][$metodo])) {
                $stats['por_metodo'][$metodo] = 0;
            }
            $stats['por_metodo'][$metodo]++;
        }

        // Calcular tasa de efectividad
        if ($stats['total_verificaciones'] > 0) {
            $stats['tasa_efectividad'] = round(
                ($stats['efectivas'] / $stats['total_verificaciones']) * 100,
                1
            );
        }

        return $stats;
    }

    /**
     * Obtener próximas verificaciones programadas
     */
    public function getProximasVerificaciones(int $idCliente, int $dias = 30): array
    {
        $fechaLimite = date('Y-m-d', strtotime("+{$dias} days"));

        return $this->select('tbl_acc_verificaciones.*,
                              a.descripcion_accion,
                              a.responsable_nombre,
                              h.titulo as hallazgo_titulo,
                              h.numeral_asociado')
                    ->join('tbl_acc_acciones a', 'a.id_accion = tbl_acc_verificaciones.id_accion')
                    ->join('tbl_acc_hallazgos h', 'h.id_hallazgo = a.id_hallazgo')
                    ->where('h.id_cliente', $idCliente)
                    ->where('tbl_acc_verificaciones.fecha_proxima_verificacion IS NOT NULL')
                    ->where('tbl_acc_verificaciones.fecha_proxima_verificacion <=', $fechaLimite)
                    ->where('tbl_acc_verificaciones.resultado', 'parcialmente_efectiva')
                    ->orderBy('tbl_acc_verificaciones.fecha_proxima_verificacion', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener catálogo de métodos de verificación
     */
    public function getMetodosVerificacion(): array
    {
        return self::METODOS_VERIFICACION;
    }
}
