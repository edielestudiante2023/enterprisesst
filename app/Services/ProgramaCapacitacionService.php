<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\IndicadorSSTModel;
use App\Models\CronogcapacitacionModel;
use App\Models\ResponsableSSTModel;
use Config\Database;

/**
 * Servicio para generar el documento del Programa de Capacitacion
 * Utiliza datos REALES del cliente (cronograma, PTA, indicadores, responsables)
 */
class ProgramaCapacitacionService
{
    protected $db;
    protected ClientModel $clienteModel;
    protected ClienteContextoSstModel $contextoModel;
    protected IndicadorSSTModel $indicadorModel;
    protected CronogcapacitacionModel $cronogramaModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->indicadorModel = new IndicadorSSTModel();
        $this->cronogramaModel = new CronogcapacitacionModel();
    }

    /**
     * Genera el documento del Programa de Capacitacion
     */
    public function generarDocumento(int $idCliente, int $anio): array
    {
        // Obtener todos los datos necesarios
        $cliente = $this->clienteModel->find($idCliente);
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Obtener capacitaciones del cronograma con join a capacitaciones_sst
        $capacitaciones = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion as tema_capacitacion, capacitaciones_sst.objetivo_capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('tbl_cronog_capacitacion.id_cliente', $idCliente)
            ->where('YEAR(tbl_cronog_capacitacion.fecha_programada)', $anio)
            ->orderBy('tbl_cronog_capacitacion.fecha_programada', 'ASC')
            ->findAll();

        // Obtener indicadores de capacitacion
        $indicadores = $this->indicadorModel->getByCliente($idCliente, true, 'capacitacion');

        // Obtener responsable SST
        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);
        $responsableSST = null;
        foreach ($responsables as $r) {
            if ($r['tipo_rol'] === 'responsable_sgsst' && $r['activo']) {
                $responsableSST = $r;
                break;
            }
        }

        // Obtener actividades del PTA relacionadas con capacitacion
        // Usar SQL raw con COLLATE para evitar errores de collation
        $actividadesPTA = $this->db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->where("(
                actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%capacitacion%'
                OR actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%Induccion%'
                OR actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%formacion%'
            )", null, false)
            ->get()
            ->getResultArray();

        // Generar contenido del documento
        $contenido = $this->generarContenido($cliente, $contexto, $capacitaciones, $indicadores, $responsableSST, $anio, $estandares);

        // Guardar documento en la base de datos
        $documentoId = $this->guardarDocumento($idCliente, $anio, $contenido);

        return [
            'documento_id' => $documentoId,
            'documento_url' => base_url("documentacion/editar/{$documentoId}"),
            'resumen' => [
                'capacitaciones' => count($capacitaciones),
                'actividades_pta' => count($actividadesPTA),
                'indicadores' => count($indicadores)
            ]
        ];
    }

    /**
     * Genera el contenido estructurado del documento
     */
    protected function generarContenido(
        array $cliente,
        ?array $contexto,
        array $capacitaciones,
        array $indicadores,
        ?array $responsable,
        int $anio,
        int $estandares
    ): array {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Agrupar capacitaciones por mes (usando fecha_programada)
        $cronogramaPorMes = [];
        foreach ($capacitaciones as $cap) {
            $mes = !empty($cap['fecha_programada']) ? (int)date('n', strtotime($cap['fecha_programada'])) : 1;
            if (!isset($cronogramaPorMes[$mes])) {
                $cronogramaPorMes[$mes] = [];
            }
            $cronogramaPorMes[$mes][] = $cap;
        }

        return [
            'titulo' => 'PROGRAMA DE CAPACITACION EN SEGURIDAD Y SALUD EN EL TRABAJO',
            'empresa' => [
                'nombre' => $cliente['nombre_cliente'],
                'nit' => $cliente['nit_cliente'],
                'direccion' => $cliente['direccion_cliente'] ?? '',
                'ciudad' => $cliente['ciudad_cliente'] ?? '',
                'actividad_economica' => $contexto['actividad_economica'] ?? '',
                'clase_riesgo' => $contexto['clase_riesgo'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => $estandares,

            'secciones' => [
                [
                    'titulo' => '1. INTRODUCCION',
                    'contenido' => $this->generarIntroduccion($cliente, $estandares)
                ],
                [
                    'titulo' => '2. OBJETIVO GENERAL',
                    'contenido' => "Desarrollar competencias en Seguridad y Salud en el Trabajo en todos los niveles de {$cliente['nombre_cliente']}, mediante la ejecucion de actividades de formacion y capacitacion que permitan la prevencion de accidentes de trabajo y enfermedades laborales, cumpliendo con los requisitos legales establecidos en la Resolucion 0312 de 2019."
                ],
                [
                    'titulo' => '3. OBJETIVOS ESPECIFICOS',
                    'contenido' => $this->generarObjetivosEspecificos($estandares)
                ],
                [
                    'titulo' => '4. ALCANCE',
                    'contenido' => "Este programa aplica a todos los trabajadores de {$cliente['nombre_cliente']}, incluyendo trabajadores directos, contratistas, subcontratistas y visitantes que realicen actividades en las instalaciones de la empresa."
                ],
                [
                    'titulo' => '5. MARCO LEGAL',
                    'contenido' => $this->generarMarcoLegal()
                ],
                [
                    'titulo' => '6. DEFINICIONES',
                    'contenido' => $this->generarDefiniciones()
                ],
                [
                    'titulo' => '7. RESPONSABILIDADES',
                    'contenido' => $this->generarResponsabilidades($responsable, $estandares)
                ],
                [
                    'titulo' => '8. METODOLOGIA',
                    'contenido' => $this->generarMetodologia()
                ],
                [
                    'titulo' => '9. CRONOGRAMA DE CAPACITACIONES',
                    'tipo' => 'tabla',
                    'contenido' => $this->generarTablaCronograma($cronogramaPorMes, $meses, $anio)
                ],
                [
                    'titulo' => '10. INDICADORES',
                    'contenido' => $this->generarSeccionIndicadores($indicadores)
                ],
                [
                    'titulo' => '11. RECURSOS',
                    'contenido' => $this->generarRecursos()
                ],
                [
                    'titulo' => '12. EVALUACION Y SEGUIMIENTO',
                    'contenido' => $this->generarEvaluacion()
                ]
            ],

            'firma' => [
                'responsable' => $responsable ? $responsable['nombre_completo'] : '[RESPONSABLE SG-SST]',
                'cargo' => $responsable ? $responsable['cargo'] : '[CARGO]',
                'licencia' => $responsable ? ($responsable['licencia_sst_numero'] ?? '') : ''
            ],

            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    protected function generarIntroduccion(array $cliente, int $estandares): string
    {
        $nivel = $estandares <= 7 ? 'basico (hasta 10 trabajadores, riesgo I, II o III)' :
                ($estandares <= 21 ? 'intermedio (11 a 50 trabajadores, riesgo I, II o III)' :
                'avanzado (mas de 50 trabajadores o riesgo IV y V)');

        return "{$cliente['nombre_cliente']} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, especificamente la Resolucion 0312 de 2019 que establece los Estandares Minimos del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), ha desarrollado el presente Programa de Capacitacion.\n\n" .
               "La empresa aplica los estandares de nivel {$nivel}, lo cual determina los requisitos minimos de capacitacion que deben cumplirse.\n\n" .
               "La capacitacion es un elemento fundamental del SG-SST que permite a los trabajadores conocer los peligros y riesgos asociados a su labor, asi como las medidas de prevencion y control para evitar accidentes de trabajo y enfermedades laborales.";
    }

    protected function generarObjetivosEspecificos(int $estandares): string
    {
        $objetivos = [
            "Realizar induccion y reinduccion en SST a todos los trabajadores",
            "Capacitar a los trabajadores sobre los peligros y riesgos asociados a sus actividades",
            "Formar a los integrantes del COPASST/Vigia SST en sus funciones y responsabilidades",
            "Entrenar a los brigadistas de emergencias en prevencion y atencion de situaciones de emergencia"
        ];

        if ($estandares > 21) {
            $objetivos[] = "Desarrollar competencias en los trabajadores para la identificacion de peligros y valoracion de riesgos";
            $objetivos[] = "Promover estilos de vida y trabajo saludables";
        }

        $lista = "";
        foreach ($objetivos as $obj) {
            $lista .= "- {$obj}\n";
        }
        return $lista;
    }

    protected function generarMarcoLegal(): string
    {
        return "El presente programa se fundamenta en la siguiente normatividad:\n\n" .
               "- Ley 9 de 1979: Codigo Sanitario Nacional\n" .
               "- Resolucion 2400 de 1979: Disposiciones sobre vivienda, higiene y seguridad en los establecimientos de trabajo\n" .
               "- Decreto 1295 de 1994: Organizacion y administracion del Sistema General de Riesgos Profesionales\n" .
               "- Ley 1562 de 2012: Sistema de Gestion de Seguridad y Salud en el Trabajo\n" .
               "- Decreto 1072 de 2015: Decreto Unico Reglamentario del Sector Trabajo (Capitulo 6)\n" .
               "- Resolucion 0312 de 2019: Estandares Minimos del SG-SST";
    }

    protected function generarDefiniciones(): string
    {
        return "**Capacitacion:** Proceso mediante el cual se desarrollan competencias, habilidades y destrezas en los trabajadores.\n\n" .
               "**Induccion:** Capacitacion inicial que recibe el trabajador al ingresar a la empresa sobre aspectos generales y especificos de SST.\n\n" .
               "**Reinduccion:** Capacitacion periodica para actualizar conocimientos y reforzar conceptos de SST.\n\n" .
               "**Entrenamiento:** Proceso de aprendizaje practico que permite desarrollar habilidades especificas.\n\n" .
               "**Competencia:** Capacidad demostrada para aplicar conocimientos y habilidades.";
    }

    protected function generarResponsabilidades(?array $responsable, int $estandares): string
    {
        $nombreResponsable = $responsable ? $responsable['nombre_completo'] : 'el Responsable del SG-SST';
        $organo = $estandares <= 10 ? 'Vigia de SST' : 'COPASST';

        return "**Alta Direccion:**\n" .
               "- Asignar los recursos necesarios para la ejecucion del programa\n" .
               "- Garantizar la participacion de los trabajadores en las capacitaciones\n\n" .
               "**Responsable del SG-SST ({$nombreResponsable}):**\n" .
               "- Planificar y coordinar las actividades de capacitacion\n" .
               "- Realizar seguimiento al cumplimiento del cronograma\n" .
               "- Evaluar la efectividad de las capacitaciones\n" .
               "- Mantener los registros de asistencia y evaluacion\n\n" .
               "**{$organo}:**\n" .
               "- Participar en las actividades de capacitacion\n" .
               "- Proponer temas de capacitacion segun las necesidades identificadas\n" .
               "- Verificar el cumplimiento del programa\n\n" .
               "**Trabajadores:**\n" .
               "- Asistir a las capacitaciones programadas\n" .
               "- Aplicar los conocimientos adquiridos en su labor diaria\n" .
               "- Participar activamente en las actividades de formacion";
    }

    protected function generarMetodologia(): string
    {
        return "Las capacitaciones se desarrollaran utilizando las siguientes metodologias:\n\n" .
               "**Capacitaciones Teoricas:**\n" .
               "- Presentaciones interactivas\n" .
               "- Material audiovisual\n" .
               "- Documentos de apoyo\n\n" .
               "**Capacitaciones Practicas:**\n" .
               "- Talleres demostrativos\n" .
               "- Simulacros\n" .
               "- Ejercicios practicos en campo\n\n" .
               "**Evaluacion:**\n" .
               "- Evaluacion escrita al finalizar cada capacitacion\n" .
               "- Evaluacion practica cuando aplique\n" .
               "- Retroalimentacion individual";
    }

    protected function generarTablaCronograma(array $cronogramaPorMes, array $meses, int $anio): array
    {
        $filas = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            if (isset($cronogramaPorMes[$mes])) {
                foreach ($cronogramaPorMes[$mes] as $cap) {
                    $filas[] = [
                        'mes' => $meses[$mes],
                        'tema' => $cap['tema_capacitacion'] ?? $cap['capacitacion'] ?? 'Sin definir',
                        'duracion' => ($cap['horas_de_duracion_de_la_capacitacion'] ?? 1) . ' hora(s)',
                        'dirigido_a' => $cap['perfil_de_asistentes'] ?? 'Todos los trabajadores',
                        'responsable' => $cap['nombre_del_capacitador'] ?? 'Responsable SG-SST',
                        'estado' => $cap['estado'] ?? 'Programada'
                    ];
                }
            }
        }

        return [
            'encabezados' => ['Mes', 'Tema', 'Duracion', 'Dirigido a', 'Responsable', 'Estado'],
            'filas' => $filas,
            'anio' => $anio
        ];
    }

    protected function generarSeccionIndicadores(array $indicadores): string
    {
        if (empty($indicadores)) {
            return "Los indicadores del programa de capacitacion seran definidos por el responsable del SG-SST.";
        }

        $contenido = "El cumplimiento del programa se medira a traves de los siguientes indicadores:\n\n";

        foreach ($indicadores as $ind) {
            $contenido .= "**{$ind['nombre_indicador']}**\n";
            if (!empty($ind['formula'])) {
                $contenido .= "- Formula: {$ind['formula']}\n";
            }
            if (!empty($ind['meta'])) {
                $contenido .= "- Meta: {$ind['meta']}{$ind['unidad_medida']}\n";
            }
            $contenido .= "- Periodicidad: " . ucfirst($ind['periodicidad'] ?? 'trimestral') . "\n\n";
        }

        return $contenido;
    }

    protected function generarRecursos(): string
    {
        return "Para la ejecucion del programa de capacitacion se requieren los siguientes recursos:\n\n" .
               "**Recursos Humanos:**\n" .
               "- Responsable del SG-SST\n" .
               "- Capacitadores internos y/o externos\n" .
               "- ARL (Administradora de Riesgos Laborales)\n\n" .
               "**Recursos Fisicos:**\n" .
               "- Sala de capacitaciones o espacio adecuado\n" .
               "- Equipos audiovisuales (computador, proyector)\n" .
               "- Material didactico\n\n" .
               "**Recursos Financieros:**\n" .
               "- Presupuesto asignado por la alta direccion para actividades de capacitacion";
    }

    protected function generarEvaluacion(): string
    {
        return "El programa sera evaluado trimestralmente considerando:\n\n" .
               "- Cumplimiento del cronograma de capacitaciones\n" .
               "- Cobertura de trabajadores capacitados\n" .
               "- Resultados de las evaluaciones aplicadas\n" .
               "- Aplicacion de conocimientos en el trabajo\n\n" .
               "Los resultados de la evaluacion seran presentados en las reuniones del COPASST/Vigia SST y serviran para realizar ajustes al programa segun las necesidades identificadas.";
    }

    /**
     * Guarda el documento generado en la base de datos
     */
    protected function guardarDocumento(int $idCliente, int $anio, array $contenido): int
    {
        // Verificar si existe tabla de documentos SST
        $existeTabla = $this->db->tableExists('tbl_documentos_sst');

        if (!$existeTabla) {
            // Crear tabla si no existe
            $this->crearTablaDocumentos();
        }

        // Verificar si ya existe el documento
        $documentoExistente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'programa_capacitacion')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        $datos = [
            'id_cliente' => $idCliente,
            'tipo_documento' => 'programa_capacitacion',
            'titulo' => 'Programa de Capacitacion en SST',
            'anio' => $anio,
            'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'version' => $documentoExistente ? ((int)$documentoExistente['version'] + 1) : 1,
            'estado' => 'generado',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($documentoExistente) {
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $documentoExistente['id_documento'])
                ->update($datos);
            return (int)$documentoExistente['id_documento'];
        } else {
            $datos['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('tbl_documentos_sst')->insert($datos);
            return $this->db->insertID();
        }
    }

    /**
     * Crea la tabla de documentos SST si no existe
     */
    protected function crearTablaDocumentos(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tbl_documentos_sst` (
            `id_documento` INT(11) NOT NULL AUTO_INCREMENT,
            `id_cliente` INT(11) NOT NULL,
            `tipo_documento` VARCHAR(100) NOT NULL,
            `titulo` VARCHAR(255) NOT NULL,
            `anio` INT(4) NOT NULL,
            `contenido` LONGTEXT NULL,
            `version` INT(11) DEFAULT 1,
            `estado` ENUM('borrador', 'generado', 'aprobado', 'obsoleto') DEFAULT 'borrador',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_by` INT(11) NULL,
            `updated_by` INT(11) NULL,
            PRIMARY KEY (`id_documento`),
            KEY `idx_cliente` (`id_cliente`),
            KEY `idx_tipo` (`tipo_documento`),
            KEY `idx_anio` (`anio`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }
}
