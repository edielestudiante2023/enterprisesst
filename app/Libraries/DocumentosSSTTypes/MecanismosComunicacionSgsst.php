<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase MecanismosComunicacionSgsst
 *
 * Implementa la generación del documento de Mecanismos de Comunicación,
 * Auto Reporte en SG-SST para el estándar 2.8.1 de la Resolución 0312/2019.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class MecanismosComunicacionSgsst extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    public function getTipoDocumento(): string
    {
        return 'mecanismos_comunicacion_sgsst';
    }

    public function getNombre(): string
    {
        return 'Mecanismos de Comunicación, Auto Reporte en SG-SST';
    }

    public function getDescripcion(): string
    {
        return 'Establece los canales y procedimientos para la comunicación interna, externa y auto reporte de condiciones de trabajo y salud en el SG-SST';
    }

    public function getEstandar(): ?string
    {
        return '2.8.1';
    }

    public function getSecciones(): array
    {
        // Intentar obtener secciones desde BD
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());
        if (!empty($seccionesBD)) {
            $secciones = [];
            foreach ($seccionesBD as $s) {
                $secciones[] = [
                    'numero' => (int)($s['numero'] ?? 0),
                    'nombre' => $s['nombre'] ?? '',
                    'key' => $s['key'] ?? $s['seccion_key'] ?? ''
                ];
            }
            return $secciones;
        }

        // Fallback con secciones hardcodeadas
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 5, 'nombre' => 'Mecanismos de Comunicación Interna', 'key' => 'comunicacion_interna'],
            ['numero' => 6, 'nombre' => 'Mecanismos de Comunicación Externa', 'key' => 'comunicacion_externa'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Auto Reporte', 'key' => 'auto_reporte'],
            ['numero' => 8, 'nombre' => 'Canales de Recepción de Inquietudes', 'key' => 'canales_inquietudes'],
            ['numero' => 9, 'nombre' => 'Registro y Seguimiento', 'key' => 'registro_seguimiento'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        if ($estandares <= 10) {
            return ['responsable_sst', 'representante_legal'];
        }

        return ['responsable_sst', 'representante_legal', 'copasst'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Intentar obtener prompt desde BD
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);
        if (!empty($promptBD)) {
            return $promptBD;
        }

        $comite = $this->getTextoComite($estandares);

        $nivelTexto = match(true) {
            $estandares <= 7 => 'básico (7 estándares)',
            $estandares <= 21 => 'intermedio (21 estándares)',
            default => 'avanzado (60 estándares)'
        };

        // Fallback con prompts hardcodeados
        $prompts = [
            'objetivo' => "Genera el objetivo del procedimiento de Mecanismos de Comunicación, Auto Reporte en SG-SST.
Debe establecer:
- El propósito de garantizar canales efectivos de comunicación interna y externa
- La importancia del auto reporte de condiciones de trabajo y salud
- Referencia al cumplimiento del estándar 2.8.1 de la Resolución 0312/2019
FORMATO: Máximo 2 párrafos concisos.
TONO: Formal, técnico, en tercera persona.",

            'alcance' => "Define el alcance del procedimiento de comunicación en SST.
Debe especificar a quién aplica:
- Trabajadores directos e indirectos
- Contratistas, proveedores y partes interesadas
- Comunicación interna, externa y auto reporte
- Sedes y centros de trabajo
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: alcance simple, 3-4 ítems
- 21 estándares: alcance moderado, 5-6 ítems
- 60 estándares: alcance completo con todas las áreas y procesos
FORMATO: Lista con viñetas.",

            'definiciones' => "Define los términos clave para el procedimiento de comunicación en SST.
TÉRMINOS BASE: Comunicación interna, Comunicación externa, Auto reporte, Condiciones de trabajo, Condiciones de salud, Canal de comunicación.
CANTIDAD según estándares:
- 7 estándares: MÁXIMO 6-8 términos esenciales
- 21 estándares: MÁXIMO 10-12 términos (agregar: Buzón de sugerencias, Reporte de peligros, Partes interesadas, Retroalimentación)
- 60 estándares: 12-15 términos completos (agregar: Gestión del cambio, Comunicación ascendente/descendente, Matriz de comunicaciones)
Formato: término en **negrita** seguido de dos puntos y definición.
BASARSE en normativa colombiana (Decreto 1072, Resolución 0312).
NO usar tablas Markdown.",

            'responsabilidades' => "Define los roles y responsabilidades en el proceso de comunicación del SG-SST.
ROLES según estándares:
- 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, {$comite}, Trabajadores)
- 21 estándares: 5-6 roles (agregar Supervisores, Coordinadores de área)
- 60 estándares: Todos los roles necesarios (agregar Brigada de emergencias, Jefes de proceso)
IMPORTANTE para {$estandares} estándares:
- Si son 7 estándares: usar 'Vigía de SST', NUNCA mencionar COPASST
- Si son 21+ estándares: usar 'COPASST'
FORMATO: Rol en **negrita**, seguido de lista de responsabilidades.",

            'comunicacion_interna' => "Describe los canales y mecanismos de comunicación INTERNA del SG-SST.
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: 3 canales básicos (carteleras, reuniones, verbal)
- 21 estándares: 4-5 canales (agregar correo institucional, capacitaciones)
- 60 estándares: canales completos (agregar medios digitales, intranet, pantallas informativas)

CANALES A INCLUIR (según nivel):
**Carteleras Informativas:** Ubicación, frecuencia de actualización, contenido.
**Reuniones Periódicas:** Charlas de 5 minutos, reuniones del {$comite}.
**Correo Electrónico Institucional:** Comunicaciones oficiales del SG-SST.
**Capacitaciones:** Espacio para comunicar novedades SST.
**Medios Digitales:** Grupos de mensajería, intranet corporativa.
NO usar tablas Markdown.",

            'comunicacion_externa' => "Describe los canales y mecanismos de comunicación EXTERNA relacionados con SST.
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: 2-3 canales externos esenciales (ARL, EPS)
- 21 estándares: 4-5 canales (agregar Autoridades, Proveedores/Contratistas)
- 60 estándares: canales completos (agregar Comunidad, Organismos de control)

CANALES A INCLUIR (según nivel):
**Comunicación con la ARL:** FURAT, FUREL, asesorías.
**Comunicación con EPS/IPS:** Historias clínicas, exámenes médicos.
**Reportes a Autoridades:** Ministerio del Trabajo, Secretarías de Salud.
**Comunicación con Proveedores y Contratistas:** Requisitos SST.
**Atención a la Comunidad:** Quejas por impacto SST.
NO usar tablas Markdown.",

            'auto_reporte' => "Describe el procedimiento para que los trabajadores reporten condiciones de trabajo y salud.
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: procedimiento básico (qué reportar, cómo, a quién)
- 21 estándares: procedimiento intermedio (agregar tiempos de respuesta, confidencialidad)
- 60 estándares: procedimiento completo (agregar protección contra represalias, seguimiento, indicadores)

INCLUIR:
- Qué se puede reportar (condiciones inseguras, actos inseguros, incidentes, síntomas de salud)
- Cómo reportar (paso a paso)
- Canales disponibles (formato físico, digital, buzón, comunicación directa con el {$comite})
- Tiempos de respuesta (riesgo inminente: inmediato, condiciones inseguras: 48h, sugerencias: 15 días)
- Confidencialidad y protección contra represalias
NO usar tablas Markdown.",

            'canales_inquietudes' => "Describe los canales disponibles para recibir inquietudes, ideas y aportes de los trabajadores.
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: 2-3 canales básicos (buzón físico, reuniones del {$comite})
- 21 estándares: 3-4 canales (agregar buzón virtual/correo, formato de reporte de peligros)
- 60 estándares: canales completos (agregar línea telefónica, app digital, encuestas periódicas)

PARA CADA CANAL INCLUIR: descripción, ubicación o medio, frecuencia de revisión, responsable.

Reuniones del {$comite}: los trabajadores pueden solicitar espacio en agenda.
NO usar tablas Markdown.",

            'registro_seguimiento' => "Describe cómo se documentan, registran y gestionan las comunicaciones y reportes.
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: registro básico (formato simple, informe trimestral)
- 21 estándares: registro intermedio (radicado, estados, informes mensuales, 2-3 indicadores)
- 60 estándares: registro completo (todos los campos, indicadores de gestión, retroalimentación, conservación)

INCLUIR:
- Formato de Registro: radicado, fecha, canal, tipo, descripción, responsable, estado, cierre
- Indicadores de Gestión: tiempo promedio de respuesta, % casos cerrados, tendencia de reportes
- Informes Periódicos: mensual al Responsable SST, trimestral al {$comite}, anual a la dirección
- Retroalimentación al reportante
- Conservación de registros (20 años)
NO usar tablas Markdown. Usar listas con viñetas para los indicadores."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del documento de Mecanismos de Comunicación, Auto Reporte en SG-SST según la Resolución 0312/2019 y el Decreto 1072/2015.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer los mecanismos de comunicación interna, externa y los procedimientos de auto reporte que permitan a {$nombreEmpresa} garantizar que los trabajadores, contratistas y partes interesadas puedan recibir, documentar y responder a las comunicaciones relacionadas con la Seguridad y Salud en el Trabajo.\n\nEste procedimiento da cumplimiento al estándar 2.8.1 de la Resolución 0312 de 2019, asegurando canales efectivos para recolectar inquietudes, ideas, aportes y el reporte de condiciones de trabajo y salud por parte de los trabajadores.",

            'alcance' => "Este procedimiento aplica a:\n\n- Todos los trabajadores directos de {$nombreEmpresa}\n- Contratistas y subcontratistas\n- Proveedores con acceso a las instalaciones\n- Visitantes\n- Partes interesadas externas\n\nCubre todos los procesos de comunicación interna, comunicación externa y auto reporte relacionados con el Sistema de Gestión de Seguridad y Salud en el Trabajo, en todas las sedes y centros de trabajo de la organización.",

            'definiciones' => "**Comunicación Interna:** Intercambio de información entre los diferentes niveles y áreas de {$nombreEmpresa} relacionada con el SG-SST.\n\n**Comunicación Externa:** Intercambio de información entre {$nombreEmpresa} y partes interesadas externas (ARL, EPS, autoridades, proveedores, comunidad).\n\n**Auto Reporte:** Proceso mediante el cual el trabajador reporta por iniciativa propia las condiciones de su estado de salud y de su entorno de trabajo.\n\n**Condiciones de Trabajo:** Conjunto de variables que definen la realización de una tarea específica y el entorno en que se realiza.\n\n**Condiciones de Salud:** Estado físico, mental y social del trabajador, que puede verse afectado por las condiciones de trabajo.\n\n**Canal de Comunicación:** Medio a través del cual se transmite la información (verbal, escrito, digital).\n\n**Buzón de Sugerencias:** Medio físico o virtual para que los trabajadores depositen inquietudes, sugerencias o reportes.\n\n**Reporte de Peligros:** Comunicación formal de una condición o acto que puede generar un accidente o enfermedad laboral.\n\n**Partes Interesadas:** Personas u organizaciones que pueden afectar, verse afectadas o percibirse afectadas por las actividades de SST.",

            'responsabilidades' => "**Alta Dirección / Representante Legal:**\n- Aprobar los canales oficiales de comunicación del SG-SST\n- Asignar los recursos necesarios para el funcionamiento de los canales\n- Dar respuesta oportuna a comunicaciones de alto impacto\n- Revisar periódicamente la efectividad de los mecanismos\n\n**Responsable del SG-SST:**\n- Diseñar, implementar y mantener los canales de comunicación\n- Atender y dar trámite a los reportes recibidos\n- Generar informes periódicos de comunicación y auto reporte\n- Garantizar la confidencialidad de los reportes cuando aplique\n- Capacitar a los trabajadores en el uso de los canales\n\n**{$comite}:**\n- Canalizar las inquietudes de los trabajadores\n- Verificar que se dé respuesta oportuna a los reportes\n- Participar en la revisión de la efectividad de los canales\n- Promover el uso de los mecanismos de auto reporte\n\n**Trabajadores:**\n- Utilizar los canales oficiales de comunicación\n- Reportar oportunamente condiciones de trabajo y salud\n- Participar activamente en los espacios de comunicación\n- Atender las comunicaciones recibidas relacionadas con SST",
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
