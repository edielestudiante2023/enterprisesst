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
        return ['responsable_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Intentar obtener prompt desde BD
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);
        if (!empty($promptBD)) {
            return $promptBD;
        }

        $comite = $this->getTextoComite($estandares);

        // Fallback con prompts hardcodeados
        $prompts = [
            'objetivo' => "Genera el objetivo del procedimiento de Mecanismos de Comunicación, Auto Reporte en SG-SST.
Debe establecer:
- El propósito de garantizar canales efectivos de comunicación interna y externa
- La importancia del auto reporte de condiciones de trabajo y salud
- Referencia al cumplimiento del estándar 2.8.1 de la Resolución 0312/2019
Máximo 2 párrafos concisos.",

            'alcance' => "Define el alcance del procedimiento de comunicación en SST.
Debe especificar:
- Que aplica a todos los trabajadores directos e indirectos
- Incluye contratistas, proveedores y partes interesadas
- Cubre comunicación interna, externa y auto reporte
- Aplica a todas las sedes y centros de trabajo
Máximo 2 párrafos.",

            'definiciones' => "Define los términos clave para el procedimiento de comunicación en SST.
INCLUIR OBLIGATORIAMENTE:
- Comunicación interna
- Comunicación externa
- Auto reporte
- Condiciones de trabajo
- Condiciones de salud
- Canal de comunicación
- Buzón de sugerencias
- Reporte de peligros
- Partes interesadas
- Retroalimentación
Formato: término en negrita seguido de definición. Máximo 10 definiciones.",

            'responsabilidades' => "Define las responsabilidades de cada actor en el proceso de comunicación del SG-SST.

**Alta Dirección:**
- Aprobar los canales oficiales de comunicación
- Asignar recursos para el funcionamiento de los canales
- Dar respuesta oportuna a comunicaciones de alto impacto

**Responsable del SG-SST:**
- Gestionar y mantener los canales de comunicación
- Atender y dar trámite a los reportes recibidos
- Generar informes de comunicación y auto reporte
- Garantizar la confidencialidad cuando aplique

**{$comite}:**
- Canalizar inquietudes de los trabajadores
- Verificar que se dé respuesta a los reportes
- Participar en la revisión de comunicaciones

**Trabajadores:**
- Utilizar los canales oficiales establecidos
- Reportar condiciones de trabajo y salud
- Participar activamente en los mecanismos de comunicación",

            'comunicacion_interna' => "Describe los canales y mecanismos de comunicación INTERNA del SG-SST.

INCLUIR los siguientes canales con detalle de cada uno:

**Carteleras Informativas:**
- Ubicación en áreas de alta circulación
- Actualización: mínimo mensual
- Contenido: indicadores SST, alertas, reconocimientos

**Correo Electrónico Institucional:**
- Para comunicaciones oficiales del SG-SST
- Difusión de políticas, programas y alertas
- Confirmación de lectura cuando se requiera

**Reuniones Periódicas:**
- Charlas de 5 minutos (diarias o semanales)
- Reuniones del {$comite}
- Comités de seguridad por área

**Capacitaciones:**
- Espacio para comunicar novedades SST
- Retroalimentación directa con trabajadores

**Medios Digitales:**
- Grupos de WhatsApp o Teams
- Intranet corporativa
- Pantallas informativas",

            'comunicacion_externa' => "Describe los canales y mecanismos de comunicación EXTERNA relacionados con SST.

**Comunicación con la ARL:**
- Reporte de accidentes de trabajo (FURAT)
- Reporte de enfermedades laborales (FUREL)
- Solicitud de asesorías y capacitaciones
- Canal: plataforma ARL, correo, teléfono
- Responsable: Responsable del SG-SST

**Comunicación con EPS/IPS:**
- Solicitud de historias clínicas ocupacionales
- Coordinación de exámenes médicos
- Reporte de ausentismo por enfermedad común

**Reportes a Autoridades:**
- Ministerio del Trabajo (cuando aplique)
- Secretarías de Salud
- FURAT en casos de accidente grave o mortal

**Comunicación con Proveedores y Contratistas:**
- Requisitos SST para contratación
- Verificación de cumplimiento
- Reporte de incidentes en sus actividades

**Atención a la Comunidad:**
- Canal para quejas relacionadas con impacto SST
- Respuesta a requerimientos de vecinos o comunidad",

            'auto_reporte' => "Describe el procedimiento detallado para que los trabajadores reporten condiciones de trabajo y salud.

**¿Qué se puede reportar?**
- Condiciones inseguras en el lugar de trabajo
- Actos inseguros observados
- Peligros no identificados en la matriz
- Incidentes y casi accidentes
- Síntomas o condiciones de salud relacionadas con el trabajo
- Sugerencias de mejora en SST

**¿Cómo reportar?**
1. Identificar la situación a reportar
2. Diligenciar el formato de auto reporte (físico o digital)
3. Entregar al supervisor inmediato o depositar en buzón
4. Reportes urgentes: comunicar verbalmente de inmediato

**Canales disponibles:**
- Formato físico (disponible en cada área)
- Formato digital (correo o aplicación)
- Buzón de sugerencias SST
- Comunicación directa con el {$comite}

**Tiempos de respuesta:**
- Situaciones de riesgo inminente: inmediato
- Condiciones inseguras: máximo 48 horas para evaluación
- Sugerencias de mejora: máximo 15 días para respuesta

**Confidencialidad:**
- Los reportes pueden ser anónimos si el trabajador lo prefiere
- Se garantiza la no represalia por reportar

**Protección contra represalias:**
- Ningún trabajador será sancionado por reportar condiciones
- Los reportes de buena fe están protegidos por la política de SST",

            'canales_inquietudes' => "Describe los canales disponibles para recibir inquietudes, ideas y aportes de los trabajadores.

**Buzón Físico de Sugerencias SST:**
- Ubicación: [área de descanso/recepción/comedor]
- Revisión: semanal por el Responsable del SG-SST
- Seguimiento: registro en bitácora con número de radicado

**Buzón Virtual / Correo Electrónico:**
- Correo: sst@[empresa].com
- Respuesta automática de confirmación de recepción
- Tiempo de respuesta: máximo 5 días hábiles

**Línea Telefónica o Extensión:**
- Extensión interna: [número]
- Horario de atención: lunes a viernes 8am-5pm
- Atención: Responsable del SG-SST o delegado

**Reuniones del {$comite}:**
- Los trabajadores pueden solicitar espacio en la agenda
- Presentación de inquietudes colectivas
- Acta con compromisos y seguimiento

**Formato de Reporte de Peligros:**
- Disponible en físico en cada área
- Disponible en digital en la intranet
- Campos: descripción, ubicación, fecha, quien reporta (opcional)",

            'registro_seguimiento' => "Describe cómo se documentan, registran y gestionan las comunicaciones y reportes.

**Formato de Registro de Comunicaciones:**
- Número de radicado consecutivo
- Fecha y hora de recepción
- Canal utilizado
- Tipo de comunicación (reporte, sugerencia, queja)
- Descripción del contenido
- Responsable asignado
- Estado (pendiente, en trámite, cerrado)
- Fecha de cierre y respuesta dada

**Indicadores de Gestión:**
| Indicador | Meta | Frecuencia |
|-----------|------|------------|
| Tiempo promedio de respuesta | ≤ 5 días | Mensual |
| % de casos cerrados | ≥ 90% | Mensual |
| Reportes de auto reporte | Tendencia creciente | Trimestral |
| Satisfacción con respuestas | ≥ 80% | Semestral |

**Informes Periódicos:**
- Informe mensual al Responsable del SG-SST
- Informe trimestral al {$comite}
- Informe anual en la revisión por la dirección

**Retroalimentación al Reportante:**
- Notificación de recepción del reporte
- Comunicación de acciones tomadas
- Cierre formal con agradecimiento

**Conservación de Registros:**
- Tiempo de retención: 20 años
- Medio: digital con backup
- Acceso: restringido al personal autorizado"
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
