<?php

namespace App\Services;

use App\Models\CronogcapacitacionModel;
use App\Models\PtaclienteModel;
use App\Models\IndicadorSSTModel;
use App\Models\ClienteContextoSstModel;
use App\Models\InduccionEtapasModel;

/**
 * Servicio para verificar el estado de las fases previas
 * antes de permitir la generación de documentos con IA
 *
 * Flujo obligatorio:
 * 1. Cronograma de Capacitaciones →
 * 2. Plan de Trabajo Anual →
 * 3. Indicadores →
 * 4. Documento IA (solo si 1,2,3 están completos)
 */
class FasesDocumentoService
{
    /**
     * Estados posibles de cada fase
     */
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_PROCESO = 'en_proceso';
    public const ESTADO_COMPLETO = 'completo';
    public const ESTADO_BLOQUEADO = 'bloqueado';

    /**
     * Configuración de fases por tipo de documento/carpeta
     * Cada carpeta puede tener diferentes dependencias
     */
    public const FASES_POR_CARPETA = [
        // 2.4. Capacitación SST
        'capacitacion_sst' => [
            'cronograma' => [
                'nombre' => 'Cronograma',
                'descripcion' => 'Programacion anual de capacitaciones',
                'url_modulo' => '/generador-ia/{cliente}/capacitacion-sst',
                'url_generar' => '/generador-ia/{cliente}/capacitacion-sst',
                'orden' => 1
            ],
            'pta' => [
                'nombre' => 'Plan de Trabajo',
                'descripcion' => 'Actividades derivadas del cronograma',
                'url_modulo' => '/pta-cliente-nueva/list',
                'url_generar' => '/generador-ia/{cliente}',
                'orden' => 2,
                'depende_de' => 'cronograma'
            ],
            'indicadores' => [
                'nombre' => 'Indicadores',
                'descripcion' => 'Indicadores para medir cumplimiento',
                'url_modulo' => '/indicadores-sst/{cliente}',
                'url_generar' => '/indicadores-sst/{cliente}',
                'orden' => 3,
                'depende_de' => 'pta'
            ]
        ],
        // 1.4. Plan de Trabajo Anual
        'plan_trabajo' => [
            'pta' => [
                'nombre' => 'Plan de Trabajo',
                'descripcion' => 'Actividades del SG-SST',
                'url_modulo' => '/pta-cliente-nueva/list',
                'url_generar' => '/generador-ia/{cliente}',
                'orden' => 1
            ],
            'indicadores' => [
                'nombre' => 'Indicadores',
                'descripcion' => 'Indicadores para medir cumplimiento',
                'url_modulo' => '/indicadores-sst/{cliente}',
                'url_generar' => '/indicadores-sst/{cliente}',
                'orden' => 2,
                'depende_de' => 'pta'
            ]
        ],
        // 1.1. Responsables SST
        'responsables_sst' => [
            'responsables' => [
                'nombre' => 'Responsables',
                'descripcion' => 'Definicion de responsables y roles',
                'url_modulo' => '/responsables-sst/{cliente}',
                'url_generar' => null,
                'orden' => 1
            ]
        ],
        // 1.2.2. Inducción y Reinducción en SG-SST
        'induccion_reinduccion' => [
            'etapas_induccion' => [
                'nombre' => 'Etapas del Proceso',
                'descripcion' => 'Etapas y temas de induccion personalizados segun peligros',
                'url_modulo' => '/induccion-etapas/{cliente}',
                'url_generar' => '/induccion-etapas/{cliente}/generar',
                'orden' => 1
            ],
            'pta_induccion' => [
                'nombre' => 'Plan de Trabajo',
                'descripcion' => 'Actividades derivadas de las etapas de induccion',
                'url_modulo' => '/pta-cliente-nueva/list',
                'url_generar' => '/induccion-etapas/{cliente}/generar-pta',
                'orden' => 2,
                'depende_de' => 'etapas_induccion'
            ],
            'indicadores_induccion' => [
                'nombre' => 'Indicadores',
                'descripcion' => 'Indicadores de cobertura y cumplimiento de induccion',
                'url_modulo' => '/indicadores-sst/{cliente}',
                'url_generar' => '/induccion-etapas/{cliente}/generar-indicadores',
                'orden' => 3,
                'depende_de' => 'pta_induccion'
            ]
        ],
        // 3.1.2. Programa de Promoción y Prevención en Salud
        'promocion_prevencion_salud' => [
            'pta_pyp_salud' => [
                'nombre' => 'Actividades PyP Salud',
                'descripcion' => 'Actividades de promoción y prevención en salud para el PTA',
                'url_modulo' => '/pta-cliente-nueva/list/{cliente}',
                'url_generar' => '/generador-ia/{cliente}/pyp-salud',
                'orden' => 1
            ],
            'indicadores_pyp_salud' => [
                'nombre' => 'Indicadores PyP Salud',
                'descripcion' => 'Indicadores para medir el programa de promoción y prevención',
                'url_modulo' => '/indicadores-sst/{cliente}',
                'url_generar' => '/generador-ia/{cliente}/indicadores-pyp-salud',
                'orden' => 2,
                'depende_de' => 'pta_pyp_salud'
            ]
        ],
        // 2.2.1. Objetivos definidos, claros, medibles, cuantificables con metas
        'plan_objetivos_metas' => [
            'objetivos_sgsst' => [
                'nombre' => 'Objetivos SG-SST',
                'descripcion' => 'Objetivos del Sistema de Gestión con metas medibles',
                'url_modulo' => '/generador-ia/{cliente}/objetivos-sgsst',
                'url_generar' => '/generador-ia/{cliente}/objetivos-sgsst',
                'orden' => 1
            ],
            'indicadores_objetivos' => [
                'nombre' => 'Indicadores de Objetivos',
                'descripcion' => 'Indicadores para medir el cumplimiento de objetivos',
                'url_modulo' => '/generador-ia/{cliente}/indicadores-objetivos',
                'url_generar' => '/generador-ia/{cliente}/indicadores-objetivos',
                'orden' => 2,
                'depende_de' => 'objetivos_sgsst'
            ]
        ]
    ];

    protected CronogcapacitacionModel $cronogramaModel;
    protected PtaclienteModel $ptaModel;
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->cronogramaModel = new CronogcapacitacionModel();
        $this->ptaModel = new PtaclienteModel();
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Verifica el estado completo de todas las fases para una carpeta
     */
    public function verificarFases(int $idCliente, string $tipoCarpeta, ?int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $fases = self::FASES_POR_CARPETA[$tipoCarpeta] ?? [];

        if (empty($fases)) {
            // Si la carpeta no tiene fases definidas, está desbloqueada
            return [
                'tiene_fases' => false,
                'todas_completas' => true,
                'puede_generar_documento' => true,
                'fases' => []
            ];
        }

        $resultado = [];
        $todasCompletas = true;
        $faseBloqueada = false;

        foreach ($fases as $key => $config) {
            // Verificar si esta fase depende de otra
            $dependenciaCompleta = true;
            if (isset($config['depende_de']) && isset($resultado[$config['depende_de']])) {
                $dependenciaCompleta = $resultado[$config['depende_de']]['estado'] === self::ESTADO_COMPLETO;
            }

            // Obtener estado de la fase
            $estadoFase = $this->obtenerEstadoFase($idCliente, $key, $anio);

            // Si la dependencia no está completa, esta fase está bloqueada
            if (!$dependenciaCompleta || $faseBloqueada) {
                $estadoFase['estado'] = self::ESTADO_BLOQUEADO;
                $estadoFase['mensaje'] = 'Complete primero: ' . ($fases[$config['depende_de'] ?? '']['nombre'] ?? 'fase anterior');
                $faseBloqueada = true;
            }

            // Reemplazar placeholders en URLs
            $urlModulo = str_replace('{cliente}', $idCliente, $config['url_modulo'] ?? '');
            $urlGenerar = $config['url_generar'] ? str_replace('{cliente}', $idCliente, $config['url_generar']) : null;

            $resultado[$key] = [
                'key' => $key,
                'nombre' => $config['nombre'],
                'descripcion' => $config['descripcion'],
                'orden' => $config['orden'],
                'estado' => $estadoFase['estado'],
                'mensaje' => $estadoFase['mensaje'],
                'cantidad' => $estadoFase['cantidad'],
                'url_modulo' => $urlModulo,
                'url_generar' => $urlGenerar,
                'puede_generar' => $estadoFase['estado'] !== self::ESTADO_BLOQUEADO && $estadoFase['estado'] !== self::ESTADO_COMPLETO,
                'roles_faltantes' => $estadoFase['roles_faltantes'] ?? [],
                'detalle_faltantes' => $estadoFase['detalle_faltantes'] ?? ''
            ];

            if ($estadoFase['estado'] !== self::ESTADO_COMPLETO) {
                $todasCompletas = false;
            }
        }

        // Ordenar por orden
        uasort($resultado, fn($a, $b) => $a['orden'] <=> $b['orden']);

        return [
            'tiene_fases' => true,
            'todas_completas' => $todasCompletas,
            'puede_generar_documento' => $todasCompletas,
            'fases' => $resultado,
            'siguiente_fase' => $this->obtenerSiguienteFase($resultado)
        ];
    }

    /**
     * Obtiene el estado de una fase específica
     */
    protected function obtenerEstadoFase(int $idCliente, string $fase, int $anio): array
    {
        switch ($fase) {
            case 'cronograma':
                return $this->verificarCronograma($idCliente, $anio);

            case 'pta':
                return $this->verificarPTA($idCliente, $anio);

            case 'indicadores':
                return $this->verificarIndicadores($idCliente);

            case 'responsables':
                return $this->verificarResponsables($idCliente);

            case 'etapas_induccion':
                return $this->verificarEtapasInduccion($idCliente, $anio);

            case 'pta_induccion':
                return $this->verificarPTAInduccion($idCliente, $anio);

            case 'indicadores_induccion':
                return $this->verificarIndicadoresInduccion($idCliente);

            case 'pta_pyp_salud':
                return $this->verificarPTAPyPSalud($idCliente, $anio);

            case 'indicadores_pyp_salud':
                return $this->verificarIndicadoresPyPSalud($idCliente);

            case 'objetivos_sgsst':
                return $this->verificarObjetivosSgsst($idCliente, $anio);

            case 'indicadores_objetivos':
                return $this->verificarIndicadoresObjetivos($idCliente);

            default:
                return [
                    'estado' => self::ESTADO_PENDIENTE,
                    'mensaje' => 'Fase no configurada',
                    'cantidad' => 0
                ];
        }
    }

    /**
     * Verifica estado del cronograma de capacitaciones
     */
    protected function verificarCronograma(int $idCliente, int $anio): array
    {
        $cantidad = $this->cronogramaModel
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay capacitaciones programadas para ' . $anio,
                'cantidad' => 0
            ];
        }

        // Verificar si tiene el mínimo según estándares
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $minimo = $estandares <= 7 ? 4 : ($estandares <= 21 ? 9 : 13);

        if ($cantidad < $minimo) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} de {$minimo} capacitaciones requeridas",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} capacitaciones programadas",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado del Plan de Trabajo Anual
     */
    protected function verificarPTA(int $idCliente, int $anio): array
    {
        $cantidad = $this->ptaModel
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay actividades en el Plan de Trabajo para ' . $anio,
                'cantidad' => 0
            ];
        }

        // Verificar si tiene actividades de capacitación (derivadas del cronograma)
        // Usar COLLATE para evitar errores de collation entre diferentes tablas
        $db = \Config\Database::connect();
        $capacitaciones = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->where("actividad_plandetrabajo LIKE 'Capacitaci%' COLLATE utf8mb4_general_ci", null, false)
            ->countAllResults();

        if ($capacitaciones === 0) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} actividades pero ninguna de capacitación",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} actividades ({$capacitaciones} de capacitación)",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado de los indicadores
     */
    protected function verificarIndicadores(int $idCliente): array
    {
        $cantidad = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay indicadores definidos',
                'cantidad' => 0
            ];
        }

        // Verificar mínimo según estándares
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $minimo = $estandares <= 7 ? 2 : ($estandares <= 21 ? 3 : 4);

        if ($cantidad < $minimo) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} de {$minimo} indicadores recomendados",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} indicadores configurados",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado de los responsables SST
     *
     * IMPORTANTE: Para generar documentos solo se requiere el Representante Legal,
     * ya que es el único con autoridad para delegar responsabilidades del SG-SST.
     * Los demás roles (COPASST, COCOLAB, Brigada) son obligatorios para el SG-SST
     * completo pero NO bloquean la generación de documentos.
     */
    protected function verificarResponsables(int $idCliente): array
    {
        $responsableModel = new \App\Models\ResponsableSSTModel();
        $cantidad = $responsableModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay responsables definidos',
                'cantidad' => 0
            ];
        }

        // Verificar si existe el Representante Legal (único rol requerido para generar documentos)
        $tieneRepLegal = $responsableModel
            ->where('id_cliente', $idCliente)
            ->where('tipo_rol', 'representante_legal')
            ->where('activo', 1)
            ->countAllResults() > 0;

        if (!$tieneRepLegal) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => 'Falta el Representante Legal',
                'cantidad' => $cantidad,
                'roles_faltantes' => [['rol' => 'representante_legal', 'nombre' => 'Representante Legal']],
                'detalle_faltantes' => 'Representante Legal'
            ];
        }

        // Representante Legal existe = fase COMPLETA para generar documentos
        // Pero informamos sobre otros roles faltantes del SG-SST (sin bloquear)
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $verificacion = $responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        if (!$verificacion['completo']) {
            // Hay roles faltantes del SG-SST, pero NO bloqueamos la generación
            $nombresFaltantes = array_column($verificacion['faltantes'], 'nombre');
            $listaFaltantes = implode(', ', $nombresFaltantes);

            return [
                'estado' => self::ESTADO_COMPLETO, // COMPLETO porque tiene Rep Legal
                'mensaje' => "{$cantidad} responsables (faltan " . count($verificacion['faltantes']) . " roles del SG-SST)",
                'cantidad' => $cantidad,
                'roles_faltantes_sgsst' => $verificacion['faltantes'], // Info adicional, no bloquea
                'detalle_faltantes_sgsst' => $listaFaltantes
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} responsables con todos los roles",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Obtiene la siguiente fase a completar
     */
    protected function obtenerSiguienteFase(array $fases): ?array
    {
        foreach ($fases as $fase) {
            if ($fase['estado'] === self::ESTADO_PENDIENTE || $fase['estado'] === self::ESTADO_EN_PROCESO) {
                return $fase;
            }
        }
        return null;
    }

    /**
     * Verifica si se puede generar un documento específico
     */
    public function puedeGenerarDocumento(int $idCliente, string $codigoDocumento): array
    {
        // Mapeo de documentos a tipo de carpeta
        $mapeoDocumentos = [
            'PRG-CAP' => 'capacitacion_sst',
            'FOR-ASI' => 'capacitacion_sst',
            'PLA-PTA' => 'plan_trabajo',
            'PRO-IPVR' => 'plan_trabajo',
            'PRG-IND' => 'induccion_reinduccion',
            'PRG-PPS' => 'promocion_prevencion_salud',
        ];

        $tipoCarpeta = $mapeoDocumentos[$codigoDocumento] ?? null;

        if (!$tipoCarpeta) {
            // Documento sin dependencias específicas
            return [
                'puede_generar' => true,
                'mensaje' => 'Documento sin dependencias',
                'fases' => []
            ];
        }

        $verificacion = $this->verificarFases($idCliente, $tipoCarpeta);

        return [
            'puede_generar' => $verificacion['puede_generar_documento'],
            'mensaje' => $verificacion['puede_generar_documento']
                ? 'Todas las fases completadas'
                : 'Complete las fases previas',
            'fases' => $verificacion['fases'],
            'siguiente_fase' => $verificacion['siguiente_fase'] ?? null
        ];
    }

    /**
     * Obtiene el resumen de fases para mostrar en la UI
     */
    public function getResumenFases(int $idCliente, string $tipoCarpeta): array
    {
        $verificacion = $this->verificarFases($idCliente, $tipoCarpeta);

        $iconos = [
            self::ESTADO_COMPLETO => '✅',
            self::ESTADO_EN_PROCESO => '🔄',
            self::ESTADO_PENDIENTE => '⏳',
            self::ESTADO_BLOQUEADO => '🔒'
        ];

        $colores = [
            self::ESTADO_COMPLETO => 'success',
            self::ESTADO_EN_PROCESO => 'warning',
            self::ESTADO_PENDIENTE => 'secondary',
            self::ESTADO_BLOQUEADO => 'dark'
        ];

        foreach ($verificacion['fases'] as &$fase) {
            $fase['icono'] = $iconos[$fase['estado']] ?? '❓';
            $fase['color'] = $colores[$fase['estado']] ?? 'secondary';
        }

        return $verificacion;
    }

    /**
     * Verifica estado de las etapas de inducción
     * NOTA: El número de etapas es FLEXIBLE (el usuario elige cuáles incluir)
     */
    protected function verificarEtapasInduccion(int $idCliente, int $anio): array
    {
        $induccionModel = new InduccionEtapasModel();
        $stats = $induccionModel->contarPorEstado($idCliente, $anio);

        if ($stats['total'] === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay etapas de induccion definidas para ' . $anio,
                'cantidad' => 0
            ];
        }

        // Verificar si hay al menos 1 etapa aprobada (flexible, no requiere 5)
        if ($stats['aprobadas'] === 0) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "{$stats['total']} etapas en borrador, pendientes de aprobar",
                'cantidad' => $stats['total']
            ];
        }

        // Si todas las etapas generadas están aprobadas, está completo
        if ($stats['aprobadas'] === $stats['total']) {
            return [
                'estado' => self::ESTADO_COMPLETO,
                'mensaje' => "{$stats['aprobadas']} etapas configuradas y aprobadas",
                'cantidad' => $stats['aprobadas']
            ];
        }

        // Algunas aprobadas, otras pendientes
        return [
            'estado' => self::ESTADO_EN_PROCESO,
            'mensaje' => "{$stats['aprobadas']} de {$stats['total']} etapas aprobadas",
            'cantidad' => $stats['total']
        ];
    }

    /**
     * Verifica estado del PTA para inducción
     */
    protected function verificarPTAInduccion(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        // Contar actividades de inducción en el PTA
        $cantidad = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->like('tipo_servicio', 'Induccion', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'Induccion', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'Reinduccion', 'both', true, true)
            ->groupEnd()
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay actividades de induccion en el PTA para ' . $anio,
                'cantidad' => 0
            ];
        }

        // Con al menos 1 actividad ya está completo (flexible)
        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} actividades de induccion en el PTA",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado de los indicadores de inducción
     */
    protected function verificarIndicadoresInduccion(int $idCliente): array
    {
        $cantidad = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'induccion')
                ->orLike('nombre_indicador', 'induccion', 'both', true, true)
                ->orLike('nombre_indicador', 'cobertura', 'both', true, true)
            ->groupEnd()
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay indicadores de induccion definidos',
                'cantidad' => 0
            ];
        }

        // Con al menos 1 indicador ya está completo (flexible)
        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} indicadores de induccion configurados",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado del PTA para Promoción y Prevención en Salud
     */
    protected function verificarPTAPyPSalud(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        // Contar actividades de PyP Salud en el PTA
        $cantidad = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->like('tipo_servicio', 'PyP Salud', 'both', true, true)
                ->orLike('tipo_servicio', 'Promocion', 'both', true, true)
                ->orLike('tipo_servicio', 'Prevencion', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'examen medico', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'exámenes médicos', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'pausas activas', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'campaña de salud', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'semana de la salud', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'vacunacion', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'promocion de la salud', 'both', true, true)
            ->groupEnd()
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay actividades de PyP Salud en el PTA para ' . $anio,
                'cantidad' => 0
            ];
        }

        // Mínimo recomendado: al menos 5 actividades
        if ($cantidad < 5) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} actividades de PyP Salud (mínimo 5)",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} actividades de PyP Salud en el PTA",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado de los indicadores de Promoción y Prevención en Salud
     */
    protected function verificarIndicadoresPyPSalud(int $idCliente): array
    {
        $cantidad = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pyp_salud')
                ->orWhere('categoria', 'promocion_prevencion')
                ->orLike('nombre_indicador', 'examen', 'both', true, true)
                ->orLike('nombre_indicador', 'enfermedad', 'both', true, true)
                ->orLike('nombre_indicador', 'salud', 'both', true, true)
                ->orLike('nombre_indicador', 'medico', 'both', true, true)
                ->orLike('nombre_indicador', 'ausentismo', 'both', true, true)
            ->groupEnd()
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay indicadores de PyP Salud definidos',
                'cantidad' => 0
            ];
        }

        // Mínimo 3 indicadores para PyP Salud
        if ($cantidad < 3) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} de 3 indicadores recomendados",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} indicadores de PyP Salud configurados",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado de los Objetivos del SG-SST
     */
    protected function verificarObjetivosSgsst(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        // Contar objetivos en el PTA con tipo_servicio = 'Objetivos SG-SST'
        $cantidad = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->where('tipo_servicio', 'Objetivos SG-SST')
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay objetivos SG-SST definidos para ' . $anio,
                'cantidad' => 0
            ];
        }

        // Mínimos según estándares: 7 → 3, 21 → 4, 60 → 6
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $minimo = $estandares <= 7 ? 3 : ($estandares <= 21 ? 4 : 6);

        if ($cantidad < $minimo) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} de {$minimo} objetivos requeridos",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} objetivos SG-SST definidos",
            'cantidad' => $cantidad
        ];
    }

    /**
     * Verifica estado de los indicadores de Objetivos SG-SST
     */
    protected function verificarIndicadoresObjetivos(int $idCliente): array
    {
        $cantidad = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'objetivos_sgsst')
                ->orLike('nombre_indicador', 'objetivo', 'both', true, true)
                ->orLike('nombre_indicador', 'meta', 'both', true, true)
                ->orLike('nombre_indicador', 'cumplimiento', 'both', true, true)
            ->groupEnd()
            ->countAllResults();

        if ($cantidad === 0) {
            return [
                'estado' => self::ESTADO_PENDIENTE,
                'mensaje' => 'No hay indicadores de objetivos definidos',
                'cantidad' => 0
            ];
        }

        // Mínimos según estándares: 7 → 5, 21 → 8, 60 → 10
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $minimo = $estandares <= 7 ? 5 : ($estandares <= 21 ? 8 : 10);

        if ($cantidad < $minimo) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Tiene {$cantidad} de {$minimo} indicadores recomendados",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} indicadores de objetivos configurados",
            'cantidad' => $cantidad
        ];
    }
}
