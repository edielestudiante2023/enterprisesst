<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase ProgramaCapacitacion
 *
 * Implementa la generación del Programa de Capacitación en SST (1.2.1)
 * Este documento se alimenta de (Arquitectura 3-partes):
 * - Fase 1: Plan de Trabajo (tbl_pta_cliente con tipo_servicio de capacitación)
 * - Fase 2: Indicadores (tbl_indicadores_sst con categoria='capacitacion')
 * - Contexto del cliente (tbl_cliente_contexto_sst)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.1
 */
class ProgramaCapacitacion extends AbstractDocumentoSST
{
    /**
     * Sobrescribe getContextoBase para incluir datos del Plan de Trabajo e Indicadores
     * CRÍTICO: Esto alimenta la IA con los datos reales de:
     * - Fase 1: Plan de Trabajo (tbl_pta_cliente)
     * - Fase 2: Indicadores (tbl_indicadores_sst)
     * - Contexto del cliente
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        // Contexto base de la empresa
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? 'No especificado';
        $numTrabajadores = $contexto['numero_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        $nivelTexto = match(true) {
            $estandares <= 7 => 'básico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (más de 50 trabajadores o riesgo IV y V)'
        };

        // =====================================================================
        // OBTENER ACTIVIDADES DEL PLAN DE TRABAJO (FASE 1)
        // =====================================================================
        $actividadesPTATexto = $this->obtenerActividadesCapacitacionPTA($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE CAPACITACIÓN (FASE 2)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresCapacitacion($idCliente);

        // Construir contexto completo
        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DE CAPACITACIÓN - PLAN DE TRABAJO (FASE 1)
Actividades REALES del Plan de Trabajo Anual:
============================================================
{$actividadesPTATexto}

============================================================
INDICADORES DE CAPACITACIÓN (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACIÓN:
============================================================
- IMPORTANTE: Usa las actividades del Plan de Trabajo como base para el documento
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El CRONOGRAMA del documento debe reflejar las actividades programadas
- Los INDICADORES del documento deben corresponder con los configurados
- Personaliza el contenido para esta empresa específica
- Ajusta la extensión y complejidad según el nivel de estándares
- Usa terminología de la normativa colombiana (Resolución 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    /**
     * Obtiene las actividades de capacitación del Plan de Trabajo Anual (tbl_pta_cliente)
     * FUENTE PRINCIPAL para Fase 1 según arquitectura 3-partes
     */
    private function obtenerActividadesCapacitacionPTA(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron actividades (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            // Consulta a tbl_pta_cliente - Plan de Trabajo Anual
            // Filtrar por tipo_servicio de capacitación
            $actividades = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->groupStart()
                    ->where('tipo_servicio', 'Programa de Capacitacion')
                    ->orWhere('tipo_servicio', 'Programa Capacitación SST')
                    ->orWhere('tipo_servicio', 'Capacitacion')
                    ->orLike('tipo_servicio', 'Capacitacion', 'both')
                    ->orLike('tipo_servicio', 'Capacitación', 'both')
                    ->orLike('actividad_plandetrabajo', 'capacitacion', 'both')
                    ->orLike('actividad_plandetrabajo', 'capacitación', 'both')
                    ->orLike('actividad_plandetrabajo', 'induccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'inducción', 'both')
                    ->orLike('actividad_plandetrabajo', 'reinduccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'reinducción', 'both')
                    ->orLike('actividad_plandetrabajo', 'formacion', 'both')
                    ->orLike('actividad_plandetrabajo', 'formación', 'both')
                    ->orLike('actividad_plandetrabajo', 'entrenamiento', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de capacitación registradas en el Plan de Trabajo para el año {$anio}";
            }

            $texto = "Total: " . count($actividades) . " actividades en Plan de Trabajo\n\n";

            foreach ($actividades as $i => $act) {
                $num = $i + 1;
                $actividad = $act['actividad_plandetrabajo'] ?? 'Sin nombre';
                $responsable = $act['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST';
                $fecha = $act['fecha_propuesta'] ?? '';
                $mes = $fecha ? $this->getNombreMes((int)date('m', strtotime($fecha))) : 'No programada';
                $estado = $act['estado_actividad'] ?? 'ABIERTA';
                $phva = $act['phva_plandetrabajo'] ?? 'HACER';
                $tipoServicio = $act['tipo_servicio'] ?? 'No especificado';

                $texto .= "{$num}. {$actividad}\n";
                $texto .= "   - Tipo servicio: {$tipoServicio}\n";
                $texto .= "   - Responsable: {$responsable}\n";
                $texto .= "   - Mes programado: {$mes}\n";
                $texto .= "   - Ciclo PHVA: {$phva}\n";
                $texto .= "   - Estado: {$estado}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo actividades capacitación PTA: " . $e->getMessage());
            return "Error al obtener actividades del Plan de Trabajo: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de capacitación configurados
     */
    private function obtenerIndicadoresCapacitacion(int $idCliente): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            // Buscar indicadores de capacitación
            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->groupStart()
                    ->where('categoria', 'capacitacion')
                    ->orWhere('categoria', 'capacitacion_sst')
                    ->orLike('nombre_indicador', 'capacitacion', 'both')
                    ->orLike('nombre_indicador', 'capacitación', 'both')
                ->groupEnd()
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                // Retornar indicadores por defecto para capacitación
                return "No hay indicadores específicos configurados.\n\nIndicadores sugeridos:\n" .
                    "1. Cobertura de Capacitación: (Trabajadores capacitados / Total trabajadores) x 100 - Meta: ≥90%\n" .
                    "2. Cumplimiento del Programa: (Capacitaciones realizadas / Capacitaciones programadas) x 100 - Meta: ≥80%\n" .
                    "3. Efectividad de Capacitación: (Evaluaciones aprobadas / Total evaluaciones) x 100 - Meta: ≥80%";
            }

            $texto = "Total: " . count($indicadores) . " indicadores\n\n";

            foreach ($indicadores as $i => $ind) {
                $num = $i + 1;
                $nombre = $ind['nombre_indicador'] ?? 'Sin nombre';
                $formula = $ind['formula'] ?? 'No definida';
                $meta = $ind['meta'] ?? 'No definida';
                $periodicidad = $ind['periodicidad'] ?? 'No definida';
                $tipo = $ind['tipo_indicador'] ?? 'No definido';

                $texto .= "{$num}. {$nombre}\n";
                $texto .= "   - Tipo: {$tipo}\n";
                $texto .= "   - Fórmula: {$formula}\n";
                $texto .= "   - Meta: {$meta}\n";
                $texto .= "   - Periodicidad: {$periodicidad}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo indicadores capacitación: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }

    /**
     * Obtiene el nombre del mes en español
     */
    private function getNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$mes] ?? 'Mes desconocido';
    }

    public function getTipoDocumento(): string
    {
        return 'programa_capacitacion';
    }

    public function getNombre(): string
    {
        return 'Programa de Capacitación en SST';
    }

    public function getDescripcion(): string
    {
        return 'Documento formal del programa de capacitación en Seguridad y Salud en el Trabajo';
    }

    public function getEstandar(): ?string
    {
        return '1.2.1'; // Estándar de capacitación
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Introducción', 'key' => 'introduccion'],
            ['numero' => 2, 'nombre' => 'Objetivo General', 'key' => 'objetivo_general'],
            ['numero' => 3, 'nombre' => 'Objetivos Específicos', 'key' => 'objetivos_especificos'],
            ['numero' => 4, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 5, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 6, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 7, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 8, 'nombre' => 'Metodología', 'key' => 'metodologia'],
            ['numero' => 9, 'nombre' => 'Cronograma de Capacitaciones', 'key' => 'cronograma'],
            ['numero' => 10, 'nombre' => 'Plan de Trabajo Anual', 'key' => 'plan_trabajo'],
            ['numero' => 11, 'nombre' => 'Indicadores', 'key' => 'indicadores'],
            ['numero' => 12, 'nombre' => 'Recursos', 'key' => 'recursos'],
            ['numero' => 13, 'nombre' => 'Evaluación y Seguimiento', 'key' => 'evaluacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        // El programa de capacitación requiere estas firmas
        return ['representante_legal', 'responsable_sst'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'introduccion' => "Genera una introducción para el Programa de Capacitación en SST. Debe incluir:
- Justificación de por qué la empresa necesita este programa
- Contexto de la actividad económica y sus riesgos
- Mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019)
- Compromiso de la alta dirección
IMPORTANTE: Ajusta la extensión según el tamaño de empresa ({$estandares} estándares)",

            'objetivo_general' => "Genera el objetivo general del Programa de Capacitación. Debe ser un objetivo SMART (específico, medible, alcanzable, relevante, temporal) relacionado con la capacitación en SST.
IMPORTANTE: El objetivo debe estar alineado con las CAPACITACIONES registradas en el cronograma del contexto.",

            'objetivos_especificos' => "Genera los objetivos específicos del programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: 2-3 objetivos básicos
- 21 estándares: 3-4 objetivos
- 60 estándares: 4-5 objetivos
IMPORTANTE: Los objetivos deben derivarse de las CAPACITACIONES listadas en el contexto.
Deben ser SMART y relacionados con los temas de capacitación registrados.",

            'alcance' => "Define el alcance del programa. Debe especificar:
- A quién aplica (trabajadores directos, contratistas si aplica)
- Áreas o procesos cubiertos
- Sedes incluidas
IMPORTANTE: Para empresas de 7 estándares, el alcance es simple. Máximo 5-6 ítems para 7 est, 8 ítems para 21 est, 10 ítems para 60 est.",

            'marco_legal' => "Lista el marco normativo aplicable al programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: MÁXIMO 4-5 normas
- 21 estándares: MÁXIMO 6-8 normas
- 60 estándares: Según aplique

PROHIBIDO: NO uses tablas Markdown. Solo usa formato de lista con viñetas o negritas.",

            'definiciones' => "Genera un glosario de términos técnicos para el programa.
CANTIDAD:
- 7 estándares: MÁXIMO 8 términos esenciales
- 21 estándares: MÁXIMO 12 términos
- 60 estándares: 12-15 términos
Definiciones basadas en normativa colombiana.",

            'responsabilidades' => "Define los roles y responsabilidades para el programa.
ROLES SEGÚN ESTÁNDARES:
- 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGÍA SST -no COPASST-, Trabajadores)
- 21 estándares: 5-6 roles (incluye COPASST)
- 60 estándares: Todos los roles necesarios
ADVERTENCIA: Si son 7 estándares, NUNCA mencionar COPASST, usar 'Vigía de SST'",

            'metodologia' => "Describe la metodología de capacitación.
IMPORTANTE: La metodología debe describir CÓMO se ejecutarán las CAPACITACIONES listadas en el contexto.
Incluye:
- Tipos de capacitación (teórica, práctica, virtual)
- Métodos de enseñanza según el tipo de capacitación
- Materiales y recursos a utilizar
- Evaluación del aprendizaje
- Proceso para las capacitaciones específicas del cronograma",

            'cronograma' => "Genera el cronograma de capacitaciones para el año.
IMPORTANTE: Usa las CAPACITACIONES REALES listadas en el contexto. NO inventes capacitaciones.
Presenta las capacitaciones con sus meses programados en formato de tabla markdown.
Columnas: Mes | Tema | Duración | Responsable | Dirigido a
Las capacitaciones deben coincidir con las registradas en el sistema.",

            'plan_trabajo' => "Resume las actividades del Plan de Trabajo Anual relacionadas con capacitación.
IMPORTANTE: Usa las CAPACITACIONES del cronograma listadas en el contexto.
Incluye distribución por ciclo PHVA y estado de avance de cada capacitación.",

            'indicadores' => "Define los indicadores de gestión para el programa.
IMPORTANTE: Usa los INDICADORES CONFIGURADOS listados en el contexto.
Si no hay indicadores en el contexto, usa los indicadores sugeridos por defecto.
Presenta cada indicador con: nombre, tipo, fórmula, meta y periodicidad.",

            'recursos' => "Identifica los recursos necesarios para el programa.
PROPORCIONALIDAD:
- 7 estándares: Recursos MÍNIMOS (tiempo del responsable, materiales básicos)
- 21 estándares: Recursos moderados
- 60 estándares: Recursos completos
Categorías: Humanos, Físicos, Financieros",

            'evaluacion' => "Define el mecanismo de seguimiento y evaluación del programa.
IMPORTANTE: Usa los INDICADORES listados en el contexto para describir cómo se evaluará el programa.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Seguimiento TRIMESTRAL o SEMESTRAL
- 21 estándares: Seguimiento BIMESTRAL o TRIMESTRAL
- 60 estándares: Según complejidad
Incluye:
- Periodicidad de medición de cada indicador
- Responsable de la medición
- Mecanismo de seguimiento a las capacitaciones del cronograma
- Proceso de revisión y mejora continua"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del Programa de Capacitación en SST.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nivel = $this->getNivelTexto($estandares);
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'introduccion' => "{$nombreEmpresa} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, específicamente la Resolución 0312 de 2019 que establece los Estándares Mínimos del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST), ha desarrollado el presente Programa de Capacitación.\n\nLa empresa aplica los estándares de nivel {$nivel}, lo cual determina los requisitos mínimos de capacitación que deben cumplirse.\n\nLa capacitación es un elemento fundamental del SG-SST que permite a los trabajadores conocer los peligros y riesgos asociados a su labor, así como las medidas de prevención y control para evitar accidentes de trabajo y enfermedades laborales.",

            'objetivo_general' => "Desarrollar competencias en Seguridad y Salud en el Trabajo en todos los niveles de {$nombreEmpresa}, mediante la ejecución de actividades de formación y capacitación que permitan la prevención de accidentes de trabajo y enfermedades laborales, cumpliendo con los requisitos legales establecidos en la Resolución 0312 de 2019.",

            'objetivos_especificos' => "- Realizar inducción y reinducción en SST a todos los trabajadores\n- Capacitar a los trabajadores sobre los peligros y riesgos asociados a sus actividades\n- Formar a los integrantes del {$comite} en sus funciones y responsabilidades",

            'alcance' => "El presente programa aplica a todos los trabajadores de {$nombreEmpresa}, incluyendo:\n- Personal administrativo\n- Personal operativo\n- Contratistas y subcontratistas (cuando aplique)\n\nCubre todas las actividades realizadas dentro de las instalaciones de la empresa.",

            'marco_legal' => "**Normativa aplicable:**\n- Ley 1562 de 2012: Por la cual se modifica el Sistema de Riesgos Laborales\n- Decreto 1072 de 2015: Decreto Único Reglamentario del Sector Trabajo\n- Resolución 0312 de 2019: Estándares Mínimos del SG-SST",

            'definiciones' => "**Capacitación:** Proceso mediante el cual se desarrollan competencias en los trabajadores.\n**Inducción:** Actividad de formación inicial para nuevos trabajadores.\n**Reinducción:** Actualización periódica de conocimientos en SST.\n**Competencia:** Habilidad demostrada para aplicar conocimientos.",

            'responsabilidades' => "**Representante Legal:**\n- Asignar recursos para el programa de capacitación\n- Aprobar el programa anual\n\n**Responsable del SG-SST:**\n- Diseñar y coordinar las capacitaciones\n- Evaluar la efectividad del programa\n\n**{$comite}:**\n- Participar en la identificación de necesidades de capacitación\n\n**Trabajadores:**\n- Asistir a las capacitaciones programadas\n- Aplicar los conocimientos adquiridos",

            'metodologia' => "Las capacitaciones se desarrollarán mediante:\n\n**Métodos de enseñanza:**\n- Charlas presenciales\n- Talleres prácticos\n- Capacitación virtual (cuando aplique)\n\n**Evaluación:**\n- Evaluaciones escritas\n- Observación del desempeño",

            'cronograma' => "El cronograma de capacitaciones para el año {$anio} incluye las capacitaciones registradas en el Sistema de Gestión de SST. Consulte el cronograma detallado en la sección correspondiente del documento generado.",

            'plan_trabajo' => "Las actividades de capacitación se integran al Plan de Trabajo Anual del SG-SST, distribuidas según el ciclo PHVA. Las capacitaciones registradas en el cronograma constituyen el plan de trabajo de formación.",

            'indicadores' => "**Indicadores de gestión del Programa de Capacitación:**\n\n1. **Cobertura de capacitación:**\n   Fórmula: (Trabajadores capacitados / Total trabajadores) x 100\n   Meta: ≥ 90%\n   Periodicidad: Trimestral\n\n2. **Cumplimiento del programa:**\n   Fórmula: (Capacitaciones realizadas / Capacitaciones programadas) x 100\n   Meta: ≥ 80%\n   Periodicidad: Trimestral\n\n3. **Efectividad de la capacitación:**\n   Fórmula: (Evaluaciones aprobadas / Total evaluaciones) x 100\n   Meta: ≥ 80%\n   Periodicidad: Por capacitación",

            'recursos' => "**Recursos humanos:**\n- Responsable del SG-SST\n- Capacitadores internos/externos\n\n**Recursos físicos:**\n- Sala de capacitación\n- Equipos audiovisuales\n\n**Recursos financieros:**\n- Presupuesto asignado según Plan de Trabajo Anual",

            'evaluacion' => "El seguimiento al programa se realizará de forma {$nivel} mediante:\n\n- Revisión del cumplimiento del cronograma\n- Análisis de indicadores\n- Evaluación de competencias adquiridas\n- Retroalimentación de los participantes"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
