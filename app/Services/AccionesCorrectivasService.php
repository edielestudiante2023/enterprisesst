<?php

namespace App\Services;

use App\Models\AccHallazgosModel;
use App\Models\AccAccionesModel;
use App\Models\AccSeguimientosModel;
use App\Models\AccVerificacionesModel;
use App\Models\ClientModel;

/**
 * Servicio para gestionar el módulo de Acciones Correctivas
 * Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 - Resolución 0312 de 2019
 */
class AccionesCorrectivasService
{
    protected AccHallazgosModel $hallazgosModel;
    protected AccAccionesModel $accionesModel;
    protected AccSeguimientosModel $seguimientosModel;
    protected AccVerificacionesModel $verificacionesModel;

    public function __construct()
    {
        $this->hallazgosModel = new AccHallazgosModel();
        $this->accionesModel = new AccAccionesModel();
        $this->seguimientosModel = new AccSeguimientosModel();
        $this->verificacionesModel = new AccVerificacionesModel();
    }

    /**
     * Obtener datos completos para el dashboard de un cliente
     */
    public function getDashboardData(int $idCliente): array
    {
        $anioActual = (int) date('Y');

        return [
            'cliente' => $this->getCliente($idCliente),
            'estadisticas_hallazgos' => $this->hallazgosModel->getEstadisticas($idCliente, $anioActual),
            'estadisticas_acciones' => $this->accionesModel->getEstadisticas($idCliente, $anioActual),
            'estadisticas_efectividad' => $this->verificacionesModel->getEstadisticasEfectividad($idCliente, $anioActual),
            'hallazgos_recientes' => $this->hallazgosModel->getParaDashboard($idCliente, 5),
            'acciones_vencidas' => $this->accionesModel->getVencidas($idCliente),
            'acciones_proximas_vencer' => $this->accionesModel->getProximasVencer($idCliente, 7),
            'verificaciones_pendientes' => $this->verificacionesModel->getAccionesPendientesVerificacion($idCliente),
            'kpis' => $this->calcularKPIs($idCliente, $anioActual)
        ];
    }

    /**
     * Obtener datos para vista de carpeta (filtrado por numeral)
     */
    public function getDatosPorNumeral(int $idCliente, string $numeral): array
    {
        $hallazgos = $this->hallazgosModel->getByCliente($idCliente, $numeral);
        $acciones = $this->accionesModel->getByNumeral($idCliente, $numeral);

        // Calcular estadísticas específicas del numeral
        $stats = [
            'total_hallazgos' => count($hallazgos),
            'total_acciones' => count($acciones),
            'acciones_abiertas' => count(array_filter($acciones, fn($a) =>
                !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])
            )),
            'acciones_vencidas' => count(array_filter($acciones, fn($a) =>
                !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                $a['fecha_compromiso'] < date('Y-m-d')
            ))
        ];

        return [
            'numeral' => $numeral,
            'nombre_numeral' => $this->getNombreNumeral($numeral),
            'descripcion_numeral' => $this->getDescripcionNumeral($numeral),
            'hallazgos' => $hallazgos,
            'acciones' => $acciones,
            'estadisticas' => $stats,
            'tipos_origen_numeral' => $this->getTiposOrigenPorNumeral($numeral)
        ];
    }

    /**
     * Calcular KPIs del módulo
     */
    public function calcularKPIs(int $idCliente, ?int $anio = null): array
    {
        $anio = $anio ?? (int) date('Y');
        $statsAcciones = $this->accionesModel->getEstadisticas($idCliente, $anio);
        $statsEfectividad = $this->verificacionesModel->getEstadisticasEfectividad($idCliente, $anio);

        // KPI 1: Tasa de cierre a tiempo
        $cerradas = $statsAcciones['por_estado']['cerrada_efectiva'] + $statsAcciones['por_estado']['cerrada_no_efectiva'];
        $totalConFecha = $statsAcciones['total'] - $statsAcciones['por_estado']['borrador'] - $statsAcciones['por_estado']['cancelada'];
        $tasaCierreATiempo = $totalConFecha > 0
            ? round((($totalConFecha - $statsAcciones['vencidas']) / $totalConFecha) * 100, 1)
            : 100;

        // KPI 2: Efectividad de acciones
        $efectividad = $statsEfectividad['tasa_efectividad'];

        // KPI 3: Promedio días de cierre
        $diasPromedio = $this->calcularDiasPromedioCierre($idCliente, $anio);

        // KPI 4: Reincidencia (acciones reabiertas / total cerradas)
        $reabiertas = $statsAcciones['por_estado']['reabierta'];
        $reincidencia = $cerradas > 0 ? round(($reabiertas / $cerradas) * 100, 1) : 0;

        return [
            'cierre_a_tiempo' => [
                'valor' => $tasaCierreATiempo,
                'meta' => 85,
                'unidad' => '%',
                'nombre' => 'Cierre a tiempo',
                'icono' => 'bi-clock-history',
                'color' => $tasaCierreATiempo >= 85 ? 'success' : ($tasaCierreATiempo >= 70 ? 'warning' : 'danger')
            ],
            'efectividad' => [
                'valor' => $efectividad,
                'meta' => 80,
                'unidad' => '%',
                'nombre' => 'Efectividad',
                'icono' => 'bi-check-circle',
                'color' => $efectividad >= 80 ? 'success' : ($efectividad >= 60 ? 'warning' : 'danger')
            ],
            'dias_promedio' => [
                'valor' => $diasPromedio,
                'meta' => 30,
                'unidad' => 'días',
                'nombre' => 'Días promedio cierre',
                'icono' => 'bi-calendar-check',
                'color' => $diasPromedio <= 30 ? 'success' : ($diasPromedio <= 45 ? 'warning' : 'danger')
            ],
            'reincidencia' => [
                'valor' => $reincidencia,
                'meta' => 10,
                'unidad' => '%',
                'nombre' => 'Reincidencia',
                'icono' => 'bi-arrow-repeat',
                'color' => $reincidencia <= 10 ? 'success' : ($reincidencia <= 20 ? 'warning' : 'danger')
            ]
        ];
    }

    /**
     * Calcular días promedio de cierre de acciones
     */
    protected function calcularDiasPromedioCierre(int $idCliente, int $anio): int
    {
        $db = \Config\Database::connect();

        $result = $db->query("
            SELECT AVG(DATEDIFF(a.fecha_cierre_real, a.fecha_asignacion)) as promedio
            FROM tbl_acc_acciones a
            JOIN tbl_acc_hallazgos h ON h.id_hallazgo = a.id_hallazgo
            WHERE h.id_cliente = ?
              AND YEAR(a.fecha_asignacion) = ?
              AND a.fecha_cierre_real IS NOT NULL
        ", [$idCliente, $anio])->getRow();

        return (int) ($result->promedio ?? 0);
    }

    /**
     * Obtener catálogo de orígenes
     */
    public function getCatalogoOrigenes(): array
    {
        return $this->hallazgosModel->getCatalogoOrigenes();
    }

    /**
     * Obtener tipos de origen por numeral
     */
    public function getTiposOrigenPorNumeral(string $numeral): array
    {
        $catalogoCompleto = $this->getCatalogoOrigenes();

        return array_filter($catalogoCompleto, fn($c) => $c['numeral_default'] === $numeral);
    }

    /**
     * Obtener nombre del numeral
     */
    public function getNombreNumeral(string $numeral): string
    {
        $nombres = [
            '7.1.1' => 'Acciones con base en resultados del SG-SST',
            '7.1.2' => 'Acciones según efectividad de medidas de prevención',
            '7.1.3' => 'Acciones de investigación ATEL',
            '7.1.4' => 'Acciones por requerimiento ARL/Autoridades'
        ];

        return $nombres[$numeral] ?? 'Acciones Correctivas';
    }

    /**
     * Obtener descripción del numeral
     */
    public function getDescripcionNumeral(string $numeral): string
    {
        $descripciones = [
            '7.1.1' => 'Definición de acciones de mejora, preventivas y/o correctivas con base en los resultados del SG-SST (auditorías, revisión por la dirección, inspecciones, indicadores).',
            '7.1.2' => 'Definición de acciones correctivas, preventivas y de mejora según la efectividad de las medidas de prevención y control implementadas.',
            '7.1.3' => 'Ejecución de acciones preventivas, correctivas y de mejora como resultado de la investigación de incidentes, accidentes de trabajo y enfermedad laboral.',
            '7.1.4' => 'Implementación de medidas y acciones correctivas solicitadas por autoridades administrativas (MinTrabajo) y por la ARL.'
        ];

        return $descripciones[$numeral] ?? '';
    }

    /**
     * Obtener cliente
     */
    protected function getCliente(int $idCliente): ?array
    {
        $clientModel = new ClientModel();
        return $clientModel->find($idCliente);
    }

    /**
     * Generar prompt para análisis de causa raíz con IA
     * Metodología: Indagación socrática estructurada
     */
    public function generarPromptAnalisisCausaRaiz(array $hallazgo, array $historialDialogo = []): string
    {
        $contexto = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu rol es ayudar a identificar la CAUSA RAÍZ de un hallazgo mediante preguntas de profundización progresiva.

METODOLOGÍA: Indagación socrática estructurada (similar a los 5 Porqués)
- No aceptes la primera respuesta como causa raíz
- Profundiza hasta llegar a un hecho observable, una decisión concreta o una acción verificable
- Máximo 5-7 preguntas de profundización
- Cuando identifiques la causa raíz, indícalo claramente

HALLAZGO A ANALIZAR:
- Título: {$hallazgo['titulo']}
- Descripción: {$hallazgo['descripcion']}
- Tipo de origen: {$hallazgo['tipo_origen']}
- Área/Proceso: {$hallazgo['area_proceso']}
- Severidad: {$hallazgo['severidad']}
";

        if (!empty($historialDialogo)) {
            $contexto .= "\n\nHISTORIAL DEL DIÁLOGO:\n";
            foreach ($historialDialogo as $turno) {
                $rol = $turno['rol'] === 'ia' ? 'Analista IA' : 'Usuario';
                $contexto .= "{$rol}: {$turno['mensaje']}\n";
            }
            $contexto .= "\nContinúa el análisis con la siguiente pregunta de profundización, o indica si ya se identificó la causa raíz.";
        } else {
            $contexto .= "\n\nInicia el análisis con la primera pregunta de profundización. Pregunta por qué ocurrió este hallazgo.";
        }

        return $contexto;
    }

    /**
     * Evaluar si el diálogo ha llegado a la causa raíz
     */
    public function evaluarCausaRaizIdentificada(array $historialDialogo): bool
    {
        // Si hay más de 4 turnos y el último mensaje de la IA contiene indicadores de causa raíz
        if (count($historialDialogo) >= 4) {
            $ultimoMensajeIA = '';
            foreach (array_reverse($historialDialogo) as $turno) {
                if ($turno['rol'] === 'ia') {
                    $ultimoMensajeIA = strtolower($turno['mensaje']);
                    break;
                }
            }

            $indicadoresCausaRaiz = [
                'causa raíz identificada',
                'causa raiz identificada',
                'la causa raíz es',
                'la causa raiz es',
                'hemos identificado',
                'se ha identificado',
                'causa fundamental'
            ];

            foreach ($indicadoresCausaRaiz as $indicador) {
                if (strpos($ultimoMensajeIA, $indicador) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Generar reporte de acciones para auditoría (datos para PDF)
     */
    public function generarReporteAuditoria(int $idCliente, ?int $anio = null): array
    {
        $anio = $anio ?? (int) date('Y');
        $cliente = $this->getCliente($idCliente);

        // Obtener todas las acciones con datos del hallazgo
        $todasAcciones = $this->accionesModel->getByCliente($idCliente);

        // Separar acciones abiertas y cerradas
        $estadosAbiertos = ['asignada', 'en_ejecucion', 'en_revision', 'en_verificacion', 'reabierta'];
        $estadosCerrados = ['cerrada_efectiva', 'cerrada_no_efectiva'];

        $acciones_abiertas = array_filter($todasAcciones, fn($a) => in_array($a['estado'], $estadosAbiertos));
        $acciones_cerradas = array_filter($todasAcciones, fn($a) => in_array($a['estado'], $estadosCerrados));

        // Obtener estadísticas
        $statsHallazgos = $this->hallazgosModel->getEstadisticas($idCliente, $anio);
        $statsAcciones = $this->accionesModel->getEstadisticas($idCliente, $anio);
        $statsEfectividad = $this->verificacionesModel->getEstadisticasEfectividad($idCliente, $anio);

        // Preparar KPIs en formato esperado por la vista PDF
        $kpis = [
            'total_hallazgos' => $statsHallazgos['total'] ?? 0,
            'total_acciones' => $statsAcciones['total'] ?? 0,
            'acciones_vencidas' => $statsAcciones['vencidas'] ?? 0,
            'efectividad' => $statsEfectividad['tasa_efectividad'] ?? 0
        ];

        // Preparar estadísticas por numeral en formato esperado por la vista
        $estadisticas_por_numeral = [];
        foreach (['7.1.1', '7.1.2', '7.1.3', '7.1.4'] as $numeral) {
            $datos = $this->getDatosPorNumeral($idCliente, $numeral);
            $cerradas = count(array_filter($datos['acciones'] ?? [], fn($a) => in_array($a['estado'], $estadosCerrados)));
            $pendientes = count(array_filter($datos['acciones'] ?? [], fn($a) => in_array($a['estado'], $estadosAbiertos)));

            $estadisticas_por_numeral[$numeral] = [
                'hallazgos' => $datos['estadisticas']['total_hallazgos'] ?? 0,
                'acciones' => $datos['estadisticas']['total_acciones'] ?? 0,
                'cerradas' => $cerradas,
                'pendientes' => $pendientes
            ];
        }

        return [
            'cliente' => $cliente,
            'anio' => $anio,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'hallazgos' => $this->hallazgosModel->getByCliente($idCliente),
            'acciones' => $todasAcciones,
            'acciones_abiertas' => array_values($acciones_abiertas),
            'acciones_cerradas' => array_values($acciones_cerradas),
            'estadisticas_hallazgos' => $statsHallazgos,
            'estadisticas_acciones' => $statsAcciones,
            'estadisticas_efectividad' => $statsEfectividad,
            'kpis' => $kpis,
            'estadisticas_por_numeral' => $estadisticas_por_numeral,
            'resumen_por_numeral' => [
                '7.1.1' => $this->getDatosPorNumeral($idCliente, '7.1.1'),
                '7.1.2' => $this->getDatosPorNumeral($idCliente, '7.1.2'),
                '7.1.3' => $this->getDatosPorNumeral($idCliente, '7.1.3'),
                '7.1.4' => $this->getDatosPorNumeral($idCliente, '7.1.4')
            ]
        ];
    }

    /**
     * Obtener acciones para notificación por email
     */
    public function getAccionesParaNotificacion(int $diasAntes = 3): array
    {
        return $this->accionesModel->getParaNotificacion($diasAntes);
    }

    /**
     * Validar permisos de acceso a cliente
     */
    public function validarAccesoCliente(int $idCliente, int $idUsuario, string $rolUsuario): bool
    {
        // Admins tienen acceso a todo
        if (in_array($rolUsuario, ['admin', 'superadmin', 'Administrador'])) {
            return true;
        }

        // Consultores solo a sus clientes
        if ($rolUsuario === 'consultant') {
            $clientModel = new ClientModel();
            $cliente = $clientModel->find($idCliente);
            return $cliente && $cliente['id_consultor'] == $idUsuario;
        }

        return false;
    }

    /**
     * Obtener resumen rápido para widget en dashboard
     */
    public function getResumenWidget(int $idCliente): array
    {
        $statsAcciones = $this->accionesModel->getEstadisticas($idCliente);
        $statsHallazgos = $this->hallazgosModel->getEstadisticas($idCliente);

        $activas = $statsAcciones['por_estado']['asignada'] +
                   $statsAcciones['por_estado']['en_ejecucion'] +
                   $statsAcciones['por_estado']['en_revision'] +
                   $statsAcciones['por_estado']['en_verificacion'];

        return [
            'total_activas' => $activas,
            'vencidas' => $statsAcciones['vencidas'],
            'cerradas_mes' => $this->contarCerradasMesActual($idCliente),
            'efectividad' => $this->verificacionesModel->getEstadisticasEfectividad($idCliente)['tasa_efectividad'],
            // Campos adicionales para el dashboard
            'total_hallazgos' => $statsHallazgos['total'] ?? 0,
            'total_acciones' => $statsAcciones['total'] ?? 0,
            'acciones_vencidas' => $statsAcciones['vencidas'] ?? 0,
            'hallazgos_abiertos' => ($statsHallazgos['por_estado']['abierto'] ?? 0) + ($statsHallazgos['por_estado']['en_tratamiento'] ?? 0)
        ];
    }

    /**
     * Contar acciones cerradas en el mes actual
     */
    protected function contarCerradasMesActual(int $idCliente): int
    {
        $db = \Config\Database::connect();

        $result = $db->query("
            SELECT COUNT(*) as total
            FROM tbl_acc_acciones a
            JOIN tbl_acc_hallazgos h ON h.id_hallazgo = a.id_hallazgo
            WHERE h.id_cliente = ?
              AND a.estado IN ('cerrada_efectiva', 'cerrada_no_efectiva')
              AND YEAR(a.fecha_cierre_real) = YEAR(CURRENT_DATE)
              AND MONTH(a.fecha_cierre_real) = MONTH(CURRENT_DATE)
        ", [$idCliente])->getRow();

        return (int) $result->total;
    }
}
