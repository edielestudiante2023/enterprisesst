<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para las etapas del proceso de inducción y reinducción
 *
 * Tabla: tbl_induccion_etapas
 * Estándar: 1.2.2 - Inducción y Reinducción en SG-SST
 */
class InduccionEtapasModel extends Model
{
    protected $table = 'tbl_induccion_etapas';
    protected $primaryKey = 'id_etapa';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_cliente',
        'numero_etapa',
        'nombre_etapa',
        'descripcion_etapa',
        'temas',
        'duracion_estimada_minutos',
        'responsable_sugerido',
        'recursos_requeridos',
        'es_personalizado',
        'anio',
        'estado',
        'fecha_aprobacion',
        'aprobado_por'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'id_cliente' => 'required|integer',
        'numero_etapa' => 'required|integer|in_list[1,2,3,4,5]',
        'nombre_etapa' => 'required|max_length[100]',
        'anio' => 'required|integer|exact_length[4]'
    ];

    /**
     * Nombres de las 5 etapas del proceso de inducción
     */
    public const ETAPAS = [
        1 => 'Introducción a la Empresa',
        2 => 'Seguridad y Salud en el Trabajo',
        3 => 'Relaciones Laborales',
        4 => 'Conocimiento y Recorrido de Instalaciones',
        5 => 'Entrenamiento al Cargo'
    ];

    /**
     * Temas base por etapa (sin personalización)
     */
    public const TEMAS_BASE = [
        1 => [
            ['nombre' => 'Historia de la empresa', 'descripcion' => 'Reseña histórica y evolución de la organización'],
            ['nombre' => 'Principios y Valores', 'descripcion' => 'Valores corporativos y principios organizacionales'],
            ['nombre' => 'Misión y Visión', 'descripcion' => 'Misión, visión y objetivos estratégicos'],
            ['nombre' => 'Ubicación y objetivos', 'descripcion' => 'Ubicación geográfica y objetivos de la empresa'],
            ['nombre' => 'Organigrama', 'descripcion' => 'Estructura organizacional y líneas de mando']
        ],
        2 => [
            ['nombre' => 'Aspectos generales y legales en SST', 'descripcion' => 'Marco legal del Sistema de Gestión de SST'],
            ['nombre' => 'Política de SST', 'descripcion' => 'Política de Seguridad y Salud en el Trabajo'],
            ['nombre' => 'Política de no alcohol ni drogas', 'descripcion' => 'Política de prevención de consumo de sustancias'],
            ['nombre' => 'Reglamento de Higiene y Seguridad', 'descripcion' => 'Normas de higiene y seguridad industrial'],
            ['nombre' => 'Plan de emergencia', 'descripcion' => 'Plan de preparación y respuesta ante emergencias'],
            ['nombre' => 'Responsabilidades generales en SST', 'descripcion' => 'Roles y responsabilidades en el SG-SST'],
            ['nombre' => 'Derechos y deberes del SG-SST', 'descripcion' => 'Derechos y deberes de trabajadores y empleador']
        ],
        3 => [
            ['nombre' => 'Reglamento Interno de Trabajo', 'descripcion' => 'Normas internas de convivencia laboral'],
            ['nombre' => 'Explicación pago salarial', 'descripcion' => 'Método de pago, conceptos y fechas'],
            ['nombre' => 'Horario laboral', 'descripcion' => 'Jornadas y turnos de trabajo'],
            ['nombre' => 'Prestaciones legales y extralegales', 'descripcion' => 'Beneficios y prestaciones del trabajador']
        ],
        4 => [
            ['nombre' => 'Equipo de trabajo', 'descripcion' => 'Presentación del equipo y compañeros'],
            ['nombre' => 'Áreas Administrativas', 'descripcion' => 'Recorrido por áreas administrativas'],
            ['nombre' => 'Áreas operativas/producción', 'descripcion' => 'Recorrido por áreas operativas'],
            ['nombre' => 'Rutas de evacuación', 'descripcion' => 'Identificación de rutas de evacuación'],
            ['nombre' => 'Puntos de encuentro', 'descripcion' => 'Ubicación de puntos de encuentro en emergencias']
        ],
        5 => [
            ['nombre' => 'Entrenamiento en el puesto de trabajo', 'descripcion' => 'Capacitación específica para el cargo'],
            ['nombre' => 'Procedimientos operativos', 'descripcion' => 'Procedimientos seguros para las tareas'],
            ['nombre' => 'Uso de herramientas y equipos', 'descripcion' => 'Manejo seguro de herramientas y equipos'],
            ['nombre' => 'EPP requeridos', 'descripcion' => 'Elementos de protección personal para el cargo']
        ]
    ];

    /**
     * Temas personalizables según peligros identificados del cliente
     */
    public const TEMAS_POR_PELIGRO = [
        'trabajo en alturas' => [
            'nombre' => 'Trabajo seguro en alturas',
            'descripcion' => 'Procedimientos y medidas de seguridad para trabajo en alturas'
        ],
        'espacios confinados' => [
            'nombre' => 'Trabajo en espacios confinados',
            'descripcion' => 'Protocolos de seguridad para espacios confinados'
        ],
        'eléctrico' => [
            'nombre' => 'Riesgo eléctrico',
            'descripcion' => 'Prevención de accidentes por riesgo eléctrico'
        ],
        'químico' => [
            'nombre' => 'Manejo de sustancias químicas',
            'descripcion' => 'Manipulación segura de sustancias químicas'
        ],
        'mecánico' => [
            'nombre' => 'Seguridad en máquinas y equipos',
            'descripcion' => 'Prevención de accidentes con máquinas'
        ],
        'biomecánico' => [
            'nombre' => 'Ergonomía y manejo de cargas',
            'descripcion' => 'Prevención de lesiones musculoesqueléticas'
        ],
        'biológico' => [
            'nombre' => 'Riesgo biológico',
            'descripcion' => 'Prevención de exposición a agentes biológicos'
        ],
        'ruido' => [
            'nombre' => 'Protección auditiva',
            'descripcion' => 'Prevención de pérdida auditiva por ruido'
        ],
        'radiaciones' => [
            'nombre' => 'Protección contra radiaciones',
            'descripcion' => 'Medidas de protección contra radiaciones'
        ],
        'temperaturas extremas' => [
            'nombre' => 'Trabajo en temperaturas extremas',
            'descripcion' => 'Prevención de estrés térmico'
        ]
    ];

    /**
     * Temas condicionales según órganos de participación
     */
    public const TEMAS_CONDICIONALES = [
        'tiene_copasst' => [
            'nombre' => 'Funcionamiento del COPASST',
            'descripcion' => 'Comité Paritario de Seguridad y Salud en el Trabajo'
        ],
        'tiene_vigia_sst' => [
            'nombre' => 'Funcionamiento del Vigía de SST',
            'descripcion' => 'Rol y funciones del Vigía de SST'
        ],
        'tiene_comite_convivencia' => [
            'nombre' => 'Comité de Convivencia Laboral',
            'descripcion' => 'Prevención del acoso laboral'
        ],
        'tiene_brigada_emergencias' => [
            'nombre' => 'Brigada de Emergencias',
            'descripcion' => 'Estructura y funciones de la brigada'
        ]
    ];

    /**
     * Obtiene todas las etapas de un cliente para un año
     */
    public function getEtapasByClienteAnio(int $idCliente, int $anio): array
    {
        return $this->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->orderBy('numero_etapa', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene las etapas aprobadas de un cliente
     */
    public function getEtapasAprobadas(int $idCliente, int $anio): array
    {
        return $this->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->where('estado', 'aprobado')
            ->orderBy('numero_etapa', 'ASC')
            ->findAll();
    }

    /**
     * Cuenta las etapas por estado
     */
    public function contarPorEstado(int $idCliente, int $anio): array
    {
        $total = $this->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->countAllResults(false);

        $aprobadas = $this->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->where('estado', 'aprobado')
            ->countAllResults();

        return [
            'total' => $total,
            'aprobadas' => $aprobadas,
            'pendientes' => $total - $aprobadas
        ];
    }

    /**
     * Verifica si todas las etapas están aprobadas
     */
    public function todasAprobadas(int $idCliente, int $anio): bool
    {
        $stats = $this->contarPorEstado($idCliente, $anio);
        // Verificar que hay etapas y que todas están aprobadas
        return $stats['total'] > 0 && $stats['total'] === $stats['aprobadas'];
    }

    /**
     * Aprueba una etapa
     */
    public function aprobarEtapa(int $idEtapa, int $aprobadoPor): bool
    {
        return $this->update($idEtapa, [
            'estado' => 'aprobado',
            'fecha_aprobacion' => date('Y-m-d H:i:s'),
            'aprobado_por' => $aprobadoPor
        ]);
    }

    /**
     * Aprueba todas las etapas de un cliente
     */
    public function aprobarTodas(int $idCliente, int $anio, int $aprobadoPor): bool
    {
        return $this->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->set([
                'estado' => 'aprobado',
                'fecha_aprobacion' => date('Y-m-d H:i:s'),
                'aprobado_por' => $aprobadoPor
            ])
            ->update();
    }

    /**
     * Elimina todas las etapas de un cliente para un año (para regenerar)
     */
    public function eliminarPorClienteAnio(int $idCliente, int $anio): bool
    {
        return $this->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->delete();
    }

    /**
     * Obtiene los temas decodificados de una etapa
     */
    public function getTemasDecodificados(array $etapa): array
    {
        $temas = $etapa['temas'] ?? '[]';
        if (is_string($temas)) {
            return json_decode($temas, true) ?? [];
        }
        return $temas;
    }

    /**
     * Obtiene el total de temas en todas las etapas
     */
    public function contarTemasTotal(int $idCliente, int $anio): int
    {
        $etapas = $this->getEtapasByClienteAnio($idCliente, $anio);
        $total = 0;
        foreach ($etapas as $etapa) {
            $temas = $this->getTemasDecodificados($etapa);
            $total += count($temas);
        }
        return $total;
    }
}
