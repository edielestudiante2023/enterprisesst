<?php

namespace App\Services;

use App\Models\CronogcapacitacionModel;
use App\Models\CapacitacionModel;
use App\Models\ClienteContextoSstModel;

/**
 * Servicio de IA para generar cronogramas de capacitación
 * según Resolución 0312/2019
 *
 * Reglas:
 * - 7 estándares: 4 capacitaciones
 * - 21 estándares: 9 capacitaciones
 * - 60 estándares: 13 capacitaciones
 */
class CronogramaIAService
{
    /**
     * Capacitaciones para 7 estándares (4 capacitaciones)
     */
    public const CAPACITACIONES_7_ESTANDARES = [
        [
            'mes' => 2,  // Febrero
            'capacitacion' => 'Inducción y Reinducción en SST',
            'objetivo' => 'Dar a conocer a los trabajadores el SG-SST, política, objetivos, riesgos y controles de la empresa',
            'perfil' => 'Todos los trabajadores nuevos y reinducción anual',
            'duracion' => 1
        ],
        [
            'mes' => 3,  // Marzo
            'capacitacion' => 'Funciones del Vigía de SST',
            'objetivo' => 'Capacitar al Vigía en sus responsabilidades según la normatividad vigente',
            'perfil' => 'Vigía de SST',
            'duracion' => 1
        ],
        [
            'mes' => 6,  // Junio
            'capacitacion' => 'Prevención de Riesgos Laborales',
            'objetivo' => 'Capacitar a los trabajadores en identificación y control de riesgos de su puesto de trabajo',
            'perfil' => 'Todos los trabajadores',
            'duracion' => 1
        ],
        [
            'mes' => 9,  // Septiembre
            'capacitacion' => 'Brigada de Emergencias y Plan de Evacuación',
            'objetivo' => 'Preparar al personal para actuar en caso de emergencias',
            'perfil' => 'Brigadistas y todos los trabajadores',
            'duracion' => 1
        ]
    ];

    /**
     * Capacitaciones para 21 estándares (9 capacitaciones)
     */
    public const CAPACITACIONES_21_ESTANDARES = [
        [
            'mes' => 2,  // Febrero
            'capacitacion' => 'Inducción y Reinducción en SST',
            'objetivo' => 'Dar a conocer a los trabajadores el SG-SST, política, objetivos, riesgos y controles de la empresa',
            'perfil' => 'Todos los trabajadores nuevos y reinducción anual',
            'duracion' => 1
        ],
        [
            'mes' => 3,  // Marzo - COPASST 1
            'capacitacion' => 'COPASST - Funciones y Responsabilidades (Sesión 1)',
            'objetivo' => 'Capacitar a los miembros del COPASST en sus funciones según el Decreto 1072/2015',
            'perfil' => 'Miembros del COPASST',
            'duracion' => 1
        ],
        [
            'mes' => 3,  // Marzo - Convivencia 1
            'capacitacion' => 'Comité de Convivencia Laboral - Funciones (Sesión 1)',
            'objetivo' => 'Capacitar al comité en prevención del acoso laboral y resolución de conflictos',
            'perfil' => 'Miembros del Comité de Convivencia',
            'duracion' => 1
        ],
        [
            'mes' => 4,  // Abril
            'capacitacion' => 'Identificación de Peligros y Control de Riesgos',
            'objetivo' => 'Capacitar a los trabajadores en identificación de peligros y medidas de control',
            'perfil' => 'Todos los trabajadores',
            'duracion' => 1
        ],
        [
            'mes' => 5,  // Mayo - Brigadistas 1
            'capacitacion' => 'Brigadistas de Emergencia (Sesión 1)',
            'objetivo' => 'Formar a la brigada en primeros auxilios, extinción de incendios y evacuación',
            'perfil' => 'Brigadistas',
            'duracion' => 1
        ],
        [
            'mes' => 7,  // Julio - COPASST 2
            'capacitacion' => 'COPASST - Inspecciones y Vigilancia (Sesión 2)',
            'objetivo' => 'Entrenar al COPASST en realización de inspecciones y seguimiento de acciones',
            'perfil' => 'Miembros del COPASST',
            'duracion' => 1
        ],
        [
            'mes' => 8,  // Agosto - Convivencia 2
            'capacitacion' => 'Comité de Convivencia - Manejo de Quejas (Sesión 2)',
            'objetivo' => 'Entrenar al comité en recepción y trámite de quejas por acoso laboral',
            'perfil' => 'Miembros del Comité de Convivencia',
            'duracion' => 1
        ],
        [
            'mes' => 10, // Octubre - Brigadistas 2
            'capacitacion' => 'Brigadistas de Emergencia - Simulacros (Sesión 2)',
            'objetivo' => 'Practicar procedimientos de emergencia mediante simulacros',
            'perfil' => 'Brigadistas',
            'duracion' => 1
        ],
        [
            'mes' => 11, // Noviembre
            'capacitacion' => 'Actualización en Riesgos y Medidas Preventivas',
            'objetivo' => 'Reforzar conocimientos en prevención de riesgos específicos del trabajo',
            'perfil' => 'Todos los trabajadores',
            'duracion' => 1
        ]
    ];

    /**
     * Capacitaciones para 60 estándares (13 capacitaciones)
     * Las 9 de 21 estándares + 4 adicionales
     */
    public const CAPACITACIONES_60_ESTANDARES = [
        // Las mismas de 21 estándares
        [
            'mes' => 2,
            'capacitacion' => 'Inducción y Reinducción en SST',
            'objetivo' => 'Dar a conocer a los trabajadores el SG-SST, política, objetivos, riesgos y controles de la empresa',
            'perfil' => 'Todos los trabajadores nuevos y reinducción anual',
            'duracion' => 1
        ],
        [
            'mes' => 3,
            'capacitacion' => 'COPASST - Funciones y Responsabilidades (Sesión 1)',
            'objetivo' => 'Capacitar a los miembros del COPASST en sus funciones según el Decreto 1072/2015',
            'perfil' => 'Miembros del COPASST',
            'duracion' => 1
        ],
        [
            'mes' => 3,
            'capacitacion' => 'Comité de Convivencia Laboral - Funciones (Sesión 1)',
            'objetivo' => 'Capacitar al comité en prevención del acoso laboral y resolución de conflictos',
            'perfil' => 'Miembros del Comité de Convivencia',
            'duracion' => 1
        ],
        [
            'mes' => 4,
            'capacitacion' => 'Identificación de Peligros y Control de Riesgos',
            'objetivo' => 'Capacitar a los trabajadores en identificación de peligros y medidas de control',
            'perfil' => 'Todos los trabajadores',
            'duracion' => 1
        ],
        [
            'mes' => 5,
            'capacitacion' => 'Brigadistas de Emergencia (Sesión 1)',
            'objetivo' => 'Formar a la brigada en primeros auxilios, extinción de incendios y evacuación',
            'perfil' => 'Brigadistas',
            'duracion' => 1
        ],
        // Adicionales para 60 estándares
        [
            'mes' => 6,
            'capacitacion' => 'COPASST - Investigación de Incidentes (Sesión 3)',
            'objetivo' => 'Entrenar al COPASST en metodología de investigación de accidentes e incidentes',
            'perfil' => 'Miembros del COPASST',
            'duracion' => 1
        ],
        [
            'mes' => 7,
            'capacitacion' => 'COPASST - Inspecciones y Vigilancia (Sesión 2)',
            'objetivo' => 'Entrenar al COPASST en realización de inspecciones y seguimiento de acciones',
            'perfil' => 'Miembros del COPASST',
            'duracion' => 1
        ],
        [
            'mes' => 8,
            'capacitacion' => 'Comité de Convivencia - Manejo de Quejas (Sesión 2)',
            'objetivo' => 'Entrenar al comité en recepción y trámite de quejas por acoso laboral',
            'perfil' => 'Miembros del Comité de Convivencia',
            'duracion' => 1
        ],
        [
            'mes' => 9,
            'capacitacion' => 'Comité de Convivencia - Seguimiento (Sesión 3)',
            'objetivo' => 'Capacitar en seguimiento de casos y elaboración de informes',
            'perfil' => 'Miembros del Comité de Convivencia',
            'duracion' => 1
        ],
        [
            'mes' => 10,
            'capacitacion' => 'Brigadistas de Emergencia - Simulacros (Sesión 2)',
            'objetivo' => 'Practicar procedimientos de emergencia mediante simulacros',
            'perfil' => 'Brigadistas',
            'duracion' => 1
        ],
        [
            'mes' => 10,
            'capacitacion' => 'Brigadistas - Atención de Emergencias (Sesión 3)',
            'objetivo' => 'Reforzar habilidades en atención de emergencias y primeros auxilios avanzados',
            'perfil' => 'Brigadistas',
            'duracion' => 1
        ],
        [
            'mes' => 11,
            'capacitacion' => 'Actualización en Riesgos y Medidas Preventivas',
            'objetivo' => 'Reforzar conocimientos en prevención de riesgos específicos del trabajo',
            'perfil' => 'Todos los trabajadores',
            'duracion' => 1
        ],
        [
            'mes' => 12,
            'capacitacion' => 'Estilos de Vida Saludable y Prevención de Enfermedades Laborales',
            'objetivo' => 'Promover hábitos saludables y prevención de enfermedades asociadas al trabajo',
            'perfil' => 'Todos los trabajadores',
            'duracion' => 1
        ]
    ];

    protected CronogcapacitacionModel $cronogramaModel;
    protected CapacitacionModel $capacitacionModel;

    public function __construct()
    {
        $this->cronogramaModel = new CronogcapacitacionModel();
        $this->capacitacionModel = new CapacitacionModel();
    }

    /**
     * Obtiene las capacitaciones predefinidas según estándares
     */
    public function getCapacitacionesPorEstandares(int $estandares): array
    {
        if ($estandares <= 7) {
            return self::CAPACITACIONES_7_ESTANDARES;
        } elseif ($estandares <= 21) {
            return self::CAPACITACIONES_21_ESTANDARES;
        } else {
            return self::CAPACITACIONES_60_ESTANDARES;
        }
    }

    /**
     * Genera el cronograma de capacitaciones para un cliente
     */
    public function generarCronograma(int $idCliente, int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        // Obtener estándares del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Obtener capacitaciones según estándares
        $capacitacionesPredefinidas = $this->getCapacitacionesPorEstandares($estandares);

        $resultado = [
            'cliente_id' => $idCliente,
            'anio' => $anio,
            'estandares' => $estandares,
            'total_capacitaciones' => count($capacitacionesPredefinidas),
            'capacitaciones' => [],
            'creadas' => 0,
            'existentes' => 0
        ];

        foreach ($capacitacionesPredefinidas as $cap) {
            // Verificar si ya existe la capacitación en el catálogo
            $capacitacionExistente = $this->buscarOCrearCapacitacion(
                $cap['capacitacion'],
                $cap['objetivo']
            );

            // Calcular fecha programada (primer día hábil del mes)
            $fechaProgramada = $this->calcularFechaProgramada($anio, $cap['mes']);

            // Verificar si ya existe en el cronograma
            $yaExiste = $this->verificarExistenciaCronograma(
                $idCliente,
                $capacitacionExistente['id_capacitacion'],
                $fechaProgramada
            );

            if ($yaExiste) {
                $resultado['existentes']++;
                $resultado['capacitaciones'][] = [
                    'capacitacion' => $cap['capacitacion'],
                    'mes' => $cap['mes'],
                    'estado' => 'existente'
                ];
                continue;
            }

            // Crear entrada en cronograma
            $datosCronograma = [
                'id_cliente' => $idCliente,
                'id_capacitacion' => $capacitacionExistente['id_capacitacion'],
                'fecha_programada' => $fechaProgramada,
                'perfil_de_asistentes' => $cap['perfil'],
                'horas_de_duracion_de_la_capacitacion' => $cap['duracion'],
                'estado' => 'Programada'
            ];

            $this->cronogramaModel->insert($datosCronograma);
            $resultado['creadas']++;

            $resultado['capacitaciones'][] = [
                'capacitacion' => $cap['capacitacion'],
                'mes' => $cap['mes'],
                'fecha' => $fechaProgramada,
                'duracion' => $cap['duracion'],
                'perfil' => $cap['perfil'],
                'estado' => 'creada'
            ];
        }

        return $resultado;
    }

    /**
     * Busca una capacitación existente o la crea
     */
    protected function buscarOCrearCapacitacion(string $nombre, string $objetivo): array
    {
        // Buscar por nombre similar - Usar SQL raw con COLLATE para evitar errores de collation
        $db = \Config\Database::connect();
        $nombreEscapado = $db->escapeLikeString($nombre);
        $existente = $db->table($this->capacitacionModel->table)
            ->where("capacitacion COLLATE utf8mb4_general_ci LIKE '%{$nombreEscapado}%' COLLATE utf8mb4_general_ci")
            ->get()
            ->getRowArray();

        if ($existente) {
            return $existente;
        }

        // Crear nueva capacitación
        $idCapacitacion = $this->capacitacionModel->insert([
            'capacitacion' => $nombre,
            'objetivo_capacitacion' => $objetivo
        ]);

        return $this->capacitacionModel->find($idCapacitacion);
    }

    /**
     * Verifica si ya existe una entrada en el cronograma
     */
    protected function verificarExistenciaCronograma(int $idCliente, int $idCapacitacion, string $fecha): bool
    {
        $anio = date('Y', strtotime($fecha));
        $mes = date('m', strtotime($fecha));

        $existente = $this->cronogramaModel
            ->where('id_cliente', $idCliente)
            ->where('id_capacitacion', $idCapacitacion)
            ->where('YEAR(fecha_programada)', $anio)
            ->where('MONTH(fecha_programada)', $mes)
            ->first();

        return $existente !== null;
    }

    /**
     * Calcula la fecha programada (segundo viernes del mes)
     */
    protected function calcularFechaProgramada(int $anio, int $mes): string
    {
        // Segundo viernes del mes
        $primerDia = new \DateTime("{$anio}-{$mes}-01");
        $primerViernes = clone $primerDia;

        // Encontrar el primer viernes
        while ($primerViernes->format('N') != 5) {
            $primerViernes->modify('+1 day');
        }

        // Segundo viernes = primer viernes + 7 días
        $segundoViernes = clone $primerViernes;
        $segundoViernes->modify('+7 days');

        return $segundoViernes->format('Y-m-d');
    }

    /**
     * Obtiene preview del cronograma sin guardar
     */
    public function previewCronograma(int $idCliente, int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $capacitaciones = $this->getCapacitacionesPorEstandares($estandares);
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $preview = [];
        foreach ($capacitaciones as $cap) {
            $fecha = $this->calcularFechaProgramada($anio, $cap['mes']);
            $preview[] = [
                'mes_numero' => $cap['mes'],
                'mes_nombre' => $meses[$cap['mes']],
                'fecha_programada' => $fecha,
                'capacitacion' => $cap['capacitacion'],
                'objetivo' => $cap['objetivo'],
                'perfil' => $cap['perfil'],
                'duracion' => $cap['duracion']
            ];
        }

        return [
            'anio' => $anio,
            'estandares' => $estandares,
            'total' => count($preview),
            'capacitaciones' => $preview
        ];
    }

    /**
     * Obtiene resumen del cronograma existente
     */
    public function getResumenCronograma(int $idCliente, int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $cronogramas = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        $total = count($cronogramas);
        $ejecutadas = 0;
        $pendientes = 0;

        foreach ($cronogramas as $c) {
            if (!empty($c['fecha_de_realizacion'])) {
                $ejecutadas++;
            } else {
                $pendientes++;
            }
        }

        return [
            'anio' => $anio,
            'total' => $total,
            'ejecutadas' => $ejecutadas,
            'pendientes' => $pendientes,
            'porcentaje_cumplimiento' => $total > 0 ? round(($ejecutadas / $total) * 100) : 0,
            'cronogramas' => $cronogramas
        ];
    }
}
