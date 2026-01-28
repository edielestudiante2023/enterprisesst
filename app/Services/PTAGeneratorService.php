<?php

namespace App\Services;

use App\Models\CronogcapacitacionModel;
use App\Models\PtaclienteModel;
use App\Models\ClienteContextoSstModel;

/**
 * Servicio para generar Plan de Trabajo Anual (PTA)
 * a partir del cronograma de capacitaciones y otras fuentes
 */
class PTAGeneratorService
{
    protected CronogcapacitacionModel $cronogramaModel;
    protected PtaclienteModel $ptaModel;

    /**
     * Tipos de servicio/documento para el PTA
     * Identifica a qué programa/documento SST pertenece cada actividad
     */
    public const TIPOS_SERVICIO = [
        'PROGRAMA_CAPACITACION'  => 'Programa de Capacitacion',
        'PROGRAMA_MANTENIMIENTO' => 'Programa de Mantenimiento',
        'PLAN_EMERGENCIAS'       => 'Plan de Emergencias',
        'PROGRAMA_VIGILANCIA'    => 'Programa de Vigilancia Epidemiologica',
        'PROGRAMA_RIESGO_PSICO'  => 'Programa Riesgo Psicosocial',
        'PLAN_TRABAJO_ANUAL'     => 'Plan de Trabajo Anual',
        'GESTION_SST'            => 'Gestion SG-SST',
    ];

    /**
     * Mapeo de tipos de actividad a numerales de la Res. 0312/2019
     */
    public const NUMERALES_ACTIVIDAD = [
        'capacitacion' => '2.11.1',
        'induccion' => '1.1.1',
        'copasst' => '1.1.3',
        'comite_convivencia' => '1.1.4',
        'brigada' => '5.1.1',
        'simulacro' => '5.1.2'
    ];

    /**
     * Actividades base del PTA según estándares
     */
    public const ACTIVIDADES_BASE = [
        7 => [
            ['numeral' => '1.1.1', 'actividad' => 'Asignación de persona que diseña el SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.1.2', 'actividad' => 'Asignación de responsabilidades en SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.1.3', 'actividad' => 'Asignación de recursos para el SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.1.4', 'actividad' => 'Afiliación al Sistema de Seguridad Social', 'phva' => 'PLANEAR', 'responsable' => 'Recursos Humanos'],
            ['numeral' => '1.1.6', 'actividad' => 'Conformación y funcionamiento del Vigía SST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.1.7', 'actividad' => 'Capacitación del Vigía SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.2.1', 'actividad' => 'Programa de capacitación anual en SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST']
        ],
        21 => [
            ['numeral' => '1.1.1', 'actividad' => 'Asignación de persona que diseña el SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.1.2', 'actividad' => 'Asignación de responsabilidades en SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.1.3', 'actividad' => 'Asignación de recursos para el SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.1.4', 'actividad' => 'Afiliación al Sistema de Seguridad Social', 'phva' => 'PLANEAR', 'responsable' => 'Recursos Humanos'],
            ['numeral' => '1.1.5', 'actividad' => 'Identificación de peligros y evaluación de riesgos', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.1.6', 'actividad' => 'Conformación y funcionamiento del COPASST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.1.7', 'actividad' => 'Capacitación de los integrantes del COPASST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.1.8', 'actividad' => 'Conformación del Comité de Convivencia Laboral', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '1.2.1', 'actividad' => 'Programa de capacitación anual en SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.2.2', 'actividad' => 'Inducción y reinducción en SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '2.1.1', 'actividad' => 'Política de SST documentada', 'phva' => 'PLANEAR', 'responsable' => 'Alta Dirección'],
            ['numeral' => '2.2.1', 'actividad' => 'Objetivos del SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '2.3.1', 'actividad' => 'Evaluación inicial del SG-SST', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '2.4.1', 'actividad' => 'Plan de trabajo anual en SST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '2.5.1', 'actividad' => 'Archivo y retención documental del SG-SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '3.1.1', 'actividad' => 'Descripción sociodemográfica', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '3.1.2', 'actividad' => 'Actividades de medicina del trabajo', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '3.1.3', 'actividad' => 'Información al médico de los perfiles de cargo', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '3.1.4', 'actividad' => 'Realización de evaluaciones médicas', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '3.1.5', 'actividad' => 'Custodia de historias clínicas', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '3.1.6', 'actividad' => 'Restricciones y recomendaciones médicas', 'phva' => 'HACER', 'responsable' => 'Responsable SST']
        ],
        60 => [
            // Incluir todas las de 21 más actividades adicionales
            // Se agregan dinámicamente
        ]
    ];

    public function __construct()
    {
        $this->cronogramaModel = new CronogcapacitacionModel();
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Genera el PTA a partir del cronograma de capacitaciones
     * @param int $idCliente ID del cliente
     * @param int|null $anio Año del cronograma
     * @param string|null $tipoServicio Tipo de servicio/documento origen (ej: "Programa de Capacitacion")
     */
    public function generarDesdeCronograma(int $idCliente, ?int $anio = null, ?string $tipoServicio = null): array
    {
        $anio = $anio ?? (int)date('Y');
        $tipoServicio = $tipoServicio ?? self::TIPOS_SERVICIO['PROGRAMA_CAPACITACION'];

        // Obtener cronogramas del cliente
        $cronogramas = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion, capacitaciones_sst.objetivo_capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        $resultado = [
            'anio' => $anio,
            'cliente_id' => $idCliente,
            'tipo_servicio' => $tipoServicio,
            'actividades_creadas' => 0,
            'actividades_existentes' => 0,
            'actividades' => []
        ];

        foreach ($cronogramas as $cron) {
            // Determinar el numeral según el tipo de capacitación
            $numeral = $this->determinarNumeral($cron['capacitacion']);

            // Verificar si ya existe la actividad
            $existeActividad = $this->verificarExistenciaActividad(
                $idCliente,
                $cron['capacitacion'],
                $cron['fecha_programada']
            );

            if ($existeActividad) {
                $resultado['actividades_existentes']++;
                $resultado['actividades'][] = [
                    'actividad' => $cron['capacitacion'],
                    'estado' => 'existente'
                ];
                continue;
            }

            // Calcular semana del año
            $semana = (int)date('W', strtotime($cron['fecha_programada']));

            // Crear actividad en el PTA con el tipo de servicio del módulo que lo invoca
            $datosActividad = [
                'id_cliente' => $idCliente,
                'tipo_servicio' => $tipoServicio,
                'phva_plandetrabajo' => 'HACER',
                'numeral_plandetrabajo' => $numeral,
                'actividad_plandetrabajo' => 'Capacitación: ' . $cron['capacitacion'],
                'responsable_sugerido_plandetrabajo' => 'Responsable SST',
                'fecha_propuesta' => $cron['fecha_programada'],
                'estado_actividad' => 'ABIERTA',
                'porcentaje_avance' => 0,
                'semana' => $semana,
                'observaciones' => $cron['objetivo_capacitacion'] ?? ''
            ];

            $this->ptaModel->insert($datosActividad);
            $resultado['actividades_creadas']++;

            $resultado['actividades'][] = [
                'actividad' => $cron['capacitacion'],
                'fecha' => $cron['fecha_programada'],
                'numeral' => $numeral,
                'estado' => 'creada'
            ];
        }

        return $resultado;
    }

    /**
     * Genera PTA completo con actividades base según estándares
     */
    public function generarPTACompleto(int $idCliente, int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        // Obtener estándares del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Determinar nivel
        $nivel = $estandares <= 7 ? 7 : ($estandares <= 21 ? 21 : 60);

        $resultado = [
            'anio' => $anio,
            'cliente_id' => $idCliente,
            'estandares' => $estandares,
            'actividades_base_creadas' => 0,
            'actividades_capacitacion_creadas' => 0
        ];

        // 1. Crear actividades base según estándares
        $actividadesBase = self::ACTIVIDADES_BASE[$nivel] ?? self::ACTIVIDADES_BASE[7];

        foreach ($actividadesBase as $index => $act) {
            // Calcular fecha: distribuir a lo largo del año
            $mes = min(12, (int)ceil(($index + 1) * 12 / count($actividadesBase)));
            $fechaPropuesta = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            // Verificar si ya existe
            $existe = $this->verificarExistenciaActividad($idCliente, $act['actividad'], $fechaPropuesta);

            if (!$existe) {
                $semana = (int)date('W', strtotime($fechaPropuesta));

                $this->ptaModel->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => self::TIPOS_SERVICIO['PLAN_TRABAJO_ANUAL'],
                    'phva_plandetrabajo' => $act['phva'],
                    'numeral_plandetrabajo' => $act['numeral'],
                    'actividad_plandetrabajo' => $act['actividad'],
                    'responsable_sugerido_plandetrabajo' => $act['responsable'],
                    'fecha_propuesta' => $fechaPropuesta,
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'semana' => $semana
                ]);

                $resultado['actividades_base_creadas']++;
            }
        }

        // 2. Agregar actividades desde el cronograma de capacitaciones
        // Cuando se genera desde PTA completo, las capacitaciones se marcan como "Programa de Capacitacion"
        $resultadoCronograma = $this->generarDesdeCronograma($idCliente, $anio, self::TIPOS_SERVICIO['PROGRAMA_CAPACITACION']);
        $resultado['actividades_capacitacion_creadas'] = $resultadoCronograma['actividades_creadas'];

        $resultado['total_creadas'] = $resultado['actividades_base_creadas'] + $resultado['actividades_capacitacion_creadas'];

        return $resultado;
    }

    /**
     * Determina el numeral de la resolución según el tipo de capacitación
     */
    protected function determinarNumeral(string $nombreCapacitacion): string
    {
        $nombre = strtolower($nombreCapacitacion);

        if (strpos($nombre, 'inducción') !== false || strpos($nombre, 'reinducción') !== false) {
            return '1.2.2';
        }

        if (strpos($nombre, 'copasst') !== false) {
            return '1.1.7';
        }

        if (strpos($nombre, 'convivencia') !== false) {
            return '1.1.8';
        }

        if (strpos($nombre, 'brigada') !== false || strpos($nombre, 'emergencia') !== false) {
            return '5.1.1';
        }

        if (strpos($nombre, 'simulacro') !== false) {
            return '5.1.2';
        }

        if (strpos($nombre, 'vigía') !== false) {
            return '1.1.7';
        }

        if (strpos($nombre, 'riesgo') !== false || strpos($nombre, 'peligro') !== false) {
            return '4.1.2';
        }

        // Por defecto, capacitación general
        return '2.11.1';
    }

    /**
     * Verifica si ya existe una actividad similar en el PTA
     */
    protected function verificarExistenciaActividad(int $idCliente, string $actividad, string $fecha): bool
    {
        $anio = date('Y', strtotime($fecha));

        // Usar query directa con COLLATE para evitar errores de collation
        $db = \Config\Database::connect();
        $actividadEscapada = $db->escapeLikeString($actividad);
        $existente = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where("actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%{$actividadEscapada}%'", null, false)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->get()
            ->getRowArray();

        return $existente !== null;
    }

    /**
     * Obtiene preview del PTA que se generaría
     */
    public function previewPTA(int $idCliente, int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $nivel = $estandares <= 7 ? 7 : ($estandares <= 21 ? 21 : 60);

        // Obtener actividades base
        $actividadesBase = self::ACTIVIDADES_BASE[$nivel] ?? self::ACTIVIDADES_BASE[7];

        // Obtener cronogramas
        $cronogramas = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        $actividadesCapacitacion = [];
        foreach ($cronogramas as $cron) {
            $actividadesCapacitacion[] = [
                'numeral' => $this->determinarNumeral($cron['capacitacion']),
                'actividad' => 'Capacitación: ' . $cron['capacitacion'],
                'phva' => 'HACER',
                'responsable' => 'Responsable SST',
                'fecha' => $cron['fecha_programada']
            ];
        }

        return [
            'anio' => $anio,
            'estandares' => $estandares,
            'actividades_base' => $actividadesBase,
            'actividades_capacitacion' => $actividadesCapacitacion,
            'total_actividades' => count($actividadesBase) + count($actividadesCapacitacion)
        ];
    }

    /**
     * Obtiene resumen del PTA existente
     * @param int $idCliente ID del cliente
     * @param int|null $anio Año del PTA
     * @param string|null $tipoServicio Filtrar por tipo de servicio/documento (ej: "Programa de Capacitacion")
     */
    public function getResumenPTA(int $idCliente, ?int $anio = null, ?string $tipoServicio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $builder = $this->ptaModel
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio);

        // Filtrar por tipo de servicio si se especifica
        if ($tipoServicio !== null) {
            $builder->where('tipo_servicio', $tipoServicio);
        }

        $actividades = $builder
            ->orderBy('fecha_propuesta', 'ASC')
            ->findAll();

        $total = count($actividades);
        $cerradas = 0;
        $enProceso = 0;
        $abiertas = 0;

        $porPhva = [
            'PLANEAR' => 0,
            'HACER' => 0,
            'VERIFICAR' => 0,
            'ACTUAR' => 0
        ];

        foreach ($actividades as $act) {
            $estado = strtoupper($act['estado_actividad'] ?? 'ABIERTA');
            if ($estado === 'CERRADA') {
                $cerradas++;
            } elseif ($estado === 'GESTIONANDO') {
                $enProceso++;
            } else {
                $abiertas++;
            }

            $phva = strtoupper($act['phva_plandetrabajo'] ?? 'HACER');
            if (isset($porPhva[$phva])) {
                $porPhva[$phva]++;
            }
        }

        return [
            'anio' => $anio,
            'total' => $total,
            'cerradas' => $cerradas,
            'en_proceso' => $enProceso,
            'abiertas' => $abiertas,
            'porcentaje_avance' => $total > 0 ? round(($cerradas / $total) * 100) : 0,
            'por_phva' => $porPhva,
            'actividades' => $actividades
        ];
    }
}
