<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase ProgramaCapacitacion
 *
 * Implementa la generación del Programa de Capacitación en SST
 * Migrado desde DocumentosSSTController para arquitectura escalable.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProgramaCapacitacion extends AbstractDocumentoSST
{
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

            'objetivo_general' => "Genera el objetivo general del Programa de Capacitación. Debe ser un objetivo SMART (específico, medible, alcanzable, relevante, temporal) relacionado con la capacitación en SST.",

            'objetivos_especificos' => "Genera los objetivos específicos del programa.
CANTIDAD SEGÚN ESTÁNDARES:
- 7 estándares: 2-3 objetivos básicos
- 21 estándares: 3-4 objetivos
- 60 estándares: 4-5 objetivos
Deben ser SMART y relacionados con los peligros identificados de la empresa.",

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

            'metodologia' => "Describe la metodología de capacitación. Incluye:
- Tipos de capacitación (teórica, práctica)
- Métodos de enseñanza
- Materiales y recursos
- Evaluación del aprendizaje",

            'cronograma' => "Genera el cronograma de capacitaciones para el año.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Actividades TRIMESTRALES o SEMESTRALES
- 21 estándares: Actividades BIMESTRALES o TRIMESTRALES
- 60 estándares: Actividades MENSUALES
Usa formato de tabla Markdown con columnas: Mes | Tema | Duración | Dirigido a",

            'plan_trabajo' => "Resume las actividades del Plan de Trabajo Anual relacionadas con capacitación. Incluye distribución por ciclo PHVA y estado de avance.",

            'indicadores' => "Define los indicadores de gestión para el programa.
CANTIDAD:
- 7 estándares: 2-3 indicadores simples
- 21 estándares: 4-5 indicadores
- 60 estándares: 6-8 indicadores completos
Incluye fórmula, meta y periodicidad para cada uno.",

            'recursos' => "Identifica los recursos necesarios para el programa.
PROPORCIONALIDAD:
- 7 estándares: Recursos MÍNIMOS (tiempo del responsable, materiales básicos)
- 21 estándares: Recursos moderados
- 60 estándares: Recursos completos
Categorías: Humanos, Físicos, Financieros",

            'evaluacion' => "Define el mecanismo de seguimiento y evaluación del programa.
FRECUENCIA SEGÚN ESTÁNDARES:
- 7 estándares: Seguimiento TRIMESTRAL o SEMESTRAL
- 21 estándares: Seguimiento BIMESTRAL o TRIMESTRAL
- 60 estándares: Según complejidad
Incluye criterios de evaluación y responsables."
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

            'cronograma' => "El cronograma de capacitaciones para el año {$anio} se desarrollará según las necesidades identificadas y los peligros prioritarios de la empresa.",

            'plan_trabajo' => "Las actividades de capacitación se integran al Plan de Trabajo Anual del SG-SST, distribuidas según el ciclo PHVA.",

            'indicadores' => "**Indicadores de gestión:**\n\n1. **Cobertura de capacitación:**\n   Fórmula: (Trabajadores capacitados / Total trabajadores) x 100\n   Meta: ≥ 90%\n\n2. **Cumplimiento del programa:**\n   Fórmula: (Capacitaciones realizadas / Capacitaciones programadas) x 100\n   Meta: ≥ 80%",

            'recursos' => "**Recursos humanos:**\n- Responsable del SG-SST\n- Capacitadores internos/externos\n\n**Recursos físicos:**\n- Sala de capacitación\n- Equipos audiovisuales\n\n**Recursos financieros:**\n- Presupuesto asignado según Plan de Trabajo Anual",

            'evaluacion' => "El seguimiento al programa se realizará de forma {$nivel} mediante:\n\n- Revisión del cumplimiento del cronograma\n- Análisis de indicadores\n- Evaluación de competencias adquiridas\n- Retroalimentación de los participantes"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
