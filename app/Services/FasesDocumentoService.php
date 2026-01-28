<?php

namespace App\Services;

use App\Models\CronogcapacitacionModel;
use App\Models\PtaclienteModel;
use App\Models\IndicadorSSTModel;
use App\Models\ClienteContextoSstModel;

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
                'url_modulo' => '/listcronogCapacitacion',
                'url_generar' => '/generador-ia/{cliente}',
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
                'puede_generar' => $estadoFase['estado'] !== self::ESTADO_BLOQUEADO && $estadoFase['estado'] !== self::ESTADO_COMPLETO
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
            ->where("actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE 'Capacitaci%' COLLATE utf8mb4_general_ci")
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

        // Verificar roles obligatorios
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $verificacion = $responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        if (!$verificacion['completo']) {
            return [
                'estado' => self::ESTADO_EN_PROCESO,
                'mensaje' => "Faltan " . count($verificacion['faltantes']) . " roles obligatorios",
                'cantidad' => $cantidad
            ];
        }

        return [
            'estado' => self::ESTADO_COMPLETO,
            'mensaje' => "{$cantidad} responsables con roles completos",
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
}
