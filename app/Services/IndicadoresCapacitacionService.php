<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar Indicadores del Programa de Capacitacion SST
 * Estandar 1.2.1 - Resolucion 0312/2019
 *
 * PARTE 2 del modulo de 3 partes:
 * - CONSUME las capacitaciones de Parte 1 (tbl_cronog_capacitacion)
 * - Genera indicadores para medir cumplimiento del programa de capacitacion
 * - Se guardan en tbl_indicadores_sst con categoria = 'capacitacion'
 */
class IndicadoresCapacitacionService
{
    protected IndicadorSSTModel $indicadorModel;
    protected CapacitacionSSTService $capacitacionService;

    protected const CATEGORIA = 'capacitacion';
    protected const NUMERAL = '1.2.1';

    /**
     * Limites de indicadores segun estandares
     */
    public const LIMITES_INDICADORES = [
        7 => 2,   // Basico: 2 indicadores
        21 => 3,  // Intermedio: 3 indicadores
        60 => 4   // Avanzado: 4 indicadores
    ];

    /**
     * Indicadores base para el programa de capacitacion SST
     */
    public const INDICADORES_BASE = [
        [
            'nombre' => 'Cumplimiento del Cronograma de Capacitacion',
            'tipo' => 'proceso',
            'formula' => '(Capacitaciones ejecutadas / Capacitaciones programadas) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el porcentaje de capacitaciones ejecutadas respecto a las programadas en el cronograma anual',
            'obligatorio' => true,
            'origen' => 'base'
        ],
        [
            'nombre' => 'Cobertura de Capacitacion en SST',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores capacitados / Total trabajadores convocados) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el porcentaje de trabajadores que asistieron a las capacitaciones programadas',
            'obligatorio' => true,
            'origen' => 'base'
        ],
        [
            'nombre' => 'Evaluacion de Eficacia de Capacitaciones',
            'tipo' => 'resultado',
            'formula' => '(Promedio calificaciones obtenidas / Calificacion maxima) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el aprovechamiento y aprendizaje de los trabajadores en las capacitaciones',
            'obligatorio' => false,
            'origen' => 'base'
        ],
        [
            'nombre' => 'Oportunidad en la Ejecucion de Capacitaciones',
            'tipo' => 'proceso',
            'formula' => '(Capacitaciones ejecutadas en fecha programada / Total capacitaciones ejecutadas) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el cumplimiento de las fechas programadas en el cronograma de capacitacion',
            'obligatorio' => false,
            'origen' => 'base'
        ],
        [
            'nombre' => 'Horas de Capacitacion por Trabajador',
            'tipo' => 'resultado',
            'formula' => 'Total horas de capacitacion ejecutadas / Total trabajadores capacitados',
            'meta' => 20,
            'unidad' => 'horas/trabajador',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'descripcion' => 'Mide el promedio de horas de formacion por trabajador durante el periodo',
            'obligatorio' => false,
            'origen' => 'base'
        ]
    ];

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->capacitacionService = new CapacitacionSSTService();
    }

    /**
     * Obtiene el limite de indicadores segun estandares del cliente
     */
    public function getLimiteIndicadores(int $estandares): int
    {
        if ($estandares <= 7) return self::LIMITES_INDICADORES[7];
        if ($estandares <= 21) return self::LIMITES_INDICADORES[21];
        return self::LIMITES_INDICADORES[60];
    }

    /**
     * Obtiene el resumen de indicadores de capacitacion para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->findAll();

        $total = count($indicadores);
        $medidos = 0;
        $cumplen = 0;

        foreach ($indicadores as $ind) {
            if ($ind['cumple_meta'] !== null) {
                $medidos++;
                if ($ind['cumple_meta'] == 1) {
                    $cumplen++;
                }
            }
        }

        return [
            'existentes' => $total,
            'sugeridos' => count(self::INDICADORES_BASE),
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'completo' => $total >= 2,
            'minimo' => 2
        ];
    }

    /**
     * VALIDACION OBLIGATORIA: Verificar que existan capacitaciones de Parte 1
     */
    public function verificarCapacitacionesPrevias(int $idCliente, int $anio): array
    {
        $resumen = $this->capacitacionService->getResumenCapacitaciones($idCliente, $anio);

        return [
            'tiene_capacitaciones' => $resumen['existentes'] > 0,
            'total_capacitaciones' => $resumen['existentes'],
            'minimo_requerido' => $resumen['minimo'],
            'completo' => $resumen['completo'],
            'mensaje' => $resumen['existentes'] > 0
                ? "Se encontraron {$resumen['existentes']} capacitaciones en el cronograma"
                : 'Debe completar la Parte 1 (Capacitaciones) antes de generar indicadores'
        ];
    }

    /**
     * Preview de indicadores que se generarian
     * CONSUME las capacitaciones de Parte 1 para contexto
     */
    public function previewIndicadores(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        // VALIDACION: Verificar capacitaciones previas
        $verificacion = $this->verificarCapacitacionesPrevias($idCliente, $anio);
        if (!$verificacion['tiene_capacitaciones']) {
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => true,
                'mensaje' => $verificacion['mensaje']
            ];
        }

        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $limite = $this->getLimiteIndicadores($estandares);

        // Tomar indicadores base hasta el limite
        $indicadoresBase = array_slice(self::INDICADORES_BASE, 0, $limite);

        $indicadores = [];
        foreach ($indicadoresBase as $idx => $ind) {
            $indicadores[] = [
                'indice' => $idx,
                'nombre' => $ind['nombre'],
                'tipo' => $ind['tipo'],
                'formula' => $ind['formula'],
                'meta' => $ind['meta'],
                'unidad' => $ind['unidad'],
                'periodicidad' => $ind['periodicidad'],
                'phva' => $ind['phva'],
                'descripcion' => $ind['descripcion'],
                'obligatorio' => $ind['obligatorio'] ?? false,
                'origen' => $ind['origen'] ?? 'base',
                'seleccionado' => true
            ];
        }

        // Marcar los que ya existen
        $existentes = $this->getIndicadoresCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $nombreLower = strtolower($ind['nombre']);
            foreach ($nombresExistentes as $existente) {
                if (similar_text($nombreLower, $existente) > strlen($nombreLower) * 0.6) {
                    $ind['ya_existe'] = true;
                    $ind['seleccionado'] = false;
                    break;
                }
            }
        }

        return [
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'limite' => $limite,
            'estandares' => $estandares,
            'capacitaciones_base' => $verificacion['total_capacitaciones'],
            'explicacion_ia' => $instrucciones ? "Instrucciones aplicadas: {$instrucciones}" : ''
        ];
    }

    /**
     * Genera los indicadores de capacitacion en la BD
     */
    public function generarIndicadores(int $idCliente, int $anio, ?array $indicadoresSeleccionados = null): array
    {
        // VALIDACION: Verificar capacitaciones previas
        $verificacion = $this->verificarCapacitacionesPrevias($idCliente, $anio);
        if (!$verificacion['tiene_capacitaciones']) {
            return [
                'creados' => 0,
                'existentes' => 0,
                'errores' => [$verificacion['mensaje']],
                'total' => 0
            ];
        }

        $creados = 0;
        $existentes = 0;
        $errores = [];

        // Si no se envian indicadores seleccionados, usar los base
        if (empty($indicadoresSeleccionados)) {
            $indicadoresSeleccionados = self::INDICADORES_BASE;
        }

        foreach ($indicadoresSeleccionados as $ind) {
            // Normalizar nombre del indicador
            $nombreIndicador = $ind['nombre'] ?? $ind['nombre_indicador'] ?? '';
            if (empty($nombreIndicador)) {
                continue;
            }

            // Verificar si ya existe un indicador similar
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->like('nombre_indicador', substr($nombreIndicador, 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $this->indicadorModel->insert([
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $nombreIndicador,
                    'tipo_indicador' => $ind['tipo'] ?? 'proceso',
                    'categoria' => self::CATEGORIA,
                    'formula' => $ind['formula'] ?? '',
                    'meta' => $ind['meta'] ?? 100,
                    'unidad_medida' => $ind['unidad'] ?? '%',
                    'periodicidad' => $ind['periodicidad'] ?? 'trimestral',
                    'phva' => $ind['phva'] ?? 'verificar',
                    'numeral_resolucion' => self::NUMERAL,
                    'activo' => 1
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$nombreIndicador}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($indicadoresSeleccionados)
        ];
    }

    /**
     * Obtiene los indicadores de capacitacion de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene indicadores formateados para el contexto del documento (Parte 3)
     */
    public function getIndicadoresParaContexto(int $idCliente): string
    {
        $indicadores = $this->getIndicadoresCliente($idCliente);

        if (empty($indicadores)) {
            return "No hay indicadores configurados para el programa de capacitacion.";
        }

        $texto = "Total: " . count($indicadores) . " indicadores de capacitacion\n\n";

        foreach ($indicadores as $i => $ind) {
            $texto .= ($i + 1) . ". {$ind['nombre_indicador']}\n";
            $texto .= "   - Tipo: " . ucfirst($ind['tipo_indicador']) . "\n";
            $texto .= "   - Formula: {$ind['formula']}\n";
            $texto .= "   - Meta: {$ind['meta']} {$ind['unidad_medida']}\n";
            $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
        }

        return $texto;
    }
}
