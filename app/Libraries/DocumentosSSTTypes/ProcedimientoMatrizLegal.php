<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoMatrizLegal
 *
 * Implementa el Procedimiento para Identificacion de Requisitos Legales del SG-SST
 * para el estandar 2.7.1 de la Resolucion 0312/2019.
 *
 * Esta clase lee la configuracion (secciones, prompts, firmantes) desde la BD
 * usando DocumentoConfigService, lo que permite modificar el documento sin cambiar codigo.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoMatrizLegal extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;
    protected ?array $configCache = null;

    /**
     * Obtiene el servicio de configuracion (lazy loading)
     */
    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    /**
     * Obtiene la configuracion completa desde BD (con cache)
     */
    protected function getConfig(): array
    {
        if ($this->configCache === null) {
            $this->configCache = $this->getConfigService()->obtenerTipoDocumento($this->getTipoDocumento());
        }
        return $this->configCache;
    }

    public function getTipoDocumento(): string
    {
        return 'procedimiento_matriz_legal';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento para Identificacion de Requisitos Legales';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia para identificar, acceder y mantener actualizados los requisitos legales aplicables en SST';
    }

    public function getEstandar(): ?string
    {
        return '2.7.1';
    }

    /**
     * Obtiene las secciones desde la BD
     */
    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (empty($seccionesBD)) {
            // Fallback si no hay configuracion en BD
            return $this->getSeccionesFallback();
        }

        // Transformar al formato esperado por la interfaz
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

    /**
     * Secciones de fallback si BD no tiene configuracion
     */
    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 5, 'nombre' => 'Metodologia de Identificacion', 'key' => 'metodologia'],
            ['numero' => 6, 'nombre' => 'Evaluacion del Cumplimiento', 'key' => 'evaluacion_cumplimiento'],
            ['numero' => 7, 'nombre' => 'Comunicacion de Requisitos', 'key' => 'comunicacion'],
            ['numero' => 8, 'nombre' => 'Actualizacion de la Matriz', 'key' => 'actualizacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        // Leer desde BD
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        // Fallback
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Obtiene el prompt para una seccion desde BD
     */
    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Intentar obtener desde BD
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);

        if (!empty($promptBD)) {
            return $promptBD;
        }

        // Fallback a prompts estaticos
        return $this->getPromptFallback($seccionKey, $estandares);
    }

    /**
     * Prompts de fallback si BD no tiene configuracion
     */
    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento para Identificacion de Requisitos Legales.
Debe mencionar:
- El proposito de identificar y mantener actualizada la normativa aplicable en SST
- Cumplimiento del Decreto 1072 de 2015 y Resolucion 0312 de 2019
- La importancia de conocer y cumplir los requisitos legales
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del procedimiento. Debe especificar:
- Aplica a todos los requisitos legales (leyes, decretos, resoluciones, circulares)
- Aplica a otros requisitos (normas tecnicas, acuerdos contractuales, requisitos de partes interesadas)
- A quien aplica (alta direccion, responsable SST, trabajadores, {$comite})
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para este procedimiento. INCLUIR OBLIGATORIAMENTE:
- Requisito Legal
- Matriz Legal / Matriz de Requisitos Legales
- Normativa Vigente
- Requisitos de otra indole
- Cumplimiento legal
- Autoridad competente
- Evaluacion de cumplimiento
CANTIDAD: 8-10 definiciones basadas en normativa colombiana.",

            'responsabilidades' => "Define las responsabilidades en la gestion de requisitos legales:

**Alta Direccion:**
- Asegurar recursos para la identificacion y cumplimiento de requisitos legales
- Aprobar la matriz de requisitos legales

**Responsable del SG-SST:**
- Identificar y actualizar los requisitos legales aplicables
- Mantener actualizada la matriz de requisitos legales
- Comunicar los requisitos a las partes interesadas
- Evaluar periodicamente el cumplimiento

**{$comite}:**
- Conocer los requisitos legales aplicables
- Participar en la evaluacion de cumplimiento

**Trabajadores:**
- Cumplir los requisitos legales que les apliquen
- Reportar posibles incumplimientos",

            'metodologia' => "Describe la metodologia para identificar requisitos legales:

**Fuentes de informacion:**
- Diario Oficial
- Paginas web de Ministerio del Trabajo, MinSalud
- Asesoria de la ARL
- Gremios y asociaciones del sector
- Actualizaciones normativas de entidades competentes

**Criterios de seleccion:**
- Aplicabilidad a la actividad economica de la empresa
- Relacion con los peligros y riesgos identificados
- Requisitos de partes interesadas

**Frecuencia de revision:**
- Revision trimestral de nuevas normas
- Actualizacion inmediata ante cambios normativos relevantes",

            'evaluacion_cumplimiento' => "Describe como se evalua el cumplimiento de los requisitos legales:

**Periodicidad:** Minimo anual, o cada vez que haya cambios significativos

**Responsable:** Responsable del SG-SST con apoyo del {$comite}

**Metodo de evaluacion:**
1. Revisar cada requisito de la matriz legal
2. Verificar evidencias de cumplimiento
3. Identificar brechas o incumplimientos
4. Documentar hallazgos

**Acciones ante incumplimiento:**
- Generar accion correctiva
- Definir responsable y fecha de cierre
- Hacer seguimiento hasta el cierre",

            'comunicacion' => "Describe como se comunican los requisitos legales:

**Medios de comunicacion:**
- Inducciones y reinducciones
- Capacitaciones especificas
- Carteleras y comunicados internos
- Reuniones del {$comite}

**Frecuencia:**
- En la induccion a nuevos trabajadores
- Cada vez que haya nuevos requisitos aplicables
- Minimo una vez al ano como refuerzo

**Registros de divulgacion:**
- Listas de asistencia
- Actas de reunion
- Evaluaciones de conocimiento",

            'actualizacion' => "Describe el proceso de actualizacion de la matriz de requisitos legales:

**Cuando se actualiza:**
- Expedicion de nueva normativa aplicable
- Derogacion o modificacion de normas existentes
- Cambios en los procesos o actividades de la empresa
- Resultados de auditorias o inspecciones
- Nuevos peligros identificados

**Quien la actualiza:** Responsable del SG-SST

**Como se registran los cambios:**
- Control de cambios con fecha y descripcion
- Historial de versiones
- Comunicacion a partes interesadas"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Procedimiento de Identificacion de Requisitos Legales segun la Resolucion 0312/2019 y el Decreto 1072/2015.";
    }

    /**
     * Contenido estatico de fallback para cuando la IA no esta disponible
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para identificar, acceder, evaluar y mantener actualizados los requisitos legales y de otra indole aplicables a {$nombreEmpresa} en materia de Seguridad y Salud en el Trabajo.\n\nEste procedimiento garantiza el cumplimiento del articulo 2.2.4.6.8 del Decreto 1072 de 2015 y el estandar 2.7.1 de la Resolucion 0312 de 2019, que exigen identificar la normativa nacional aplicable en materia de SST.",

            'alcance' => "Este procedimiento aplica a todos los requisitos legales (leyes, decretos, resoluciones, circulares y demas normas) relacionados con Seguridad y Salud en el Trabajo que sean aplicables a {$nombreEmpresa} segun su actividad economica, procesos, peligros identificados y requisitos de partes interesadas.\n\nTambien incluye otros requisitos como normas tecnicas voluntarias, requisitos contractuales con clientes y acuerdos con la ARL.",

            'definiciones' => "**Requisito Legal:** Obligacion derivada de leyes, decretos, resoluciones y demas normas expedidas por autoridades competentes.\n\n**Matriz Legal:** Herramienta que consolida todos los requisitos legales y de otra indole aplicables a la organizacion en materia de SST.\n\n**Normativa Vigente:** Conjunto de normas que se encuentran en vigor y son de obligatorio cumplimiento.\n\n**Requisitos de otra indole:** Compromisos voluntarios, normas tecnicas, acuerdos contractuales y otros que la organizacion decide adoptar.\n\n**Cumplimiento Legal:** Estado en el que la organizacion satisface todos los requisitos legales aplicables.\n\n**Evaluacion de Cumplimiento:** Proceso sistematico para verificar el grado de cumplimiento de los requisitos identificados.",

            'responsabilidades' => "**Alta Direccion:**\n- Asegurar los recursos necesarios para la identificacion y cumplimiento de requisitos legales\n- Aprobar la matriz de requisitos legales\n\n**Responsable del SG-SST:**\n- Identificar los requisitos legales aplicables a la organizacion\n- Mantener actualizada la matriz de requisitos legales\n- Comunicar los requisitos a las partes interesadas\n- Evaluar periodicamente el cumplimiento de los requisitos\n\n**{$comite}:**\n- Conocer los requisitos legales aplicables\n- Participar en la evaluacion de cumplimiento\n\n**Trabajadores:**\n- Cumplir los requisitos legales que les apliquen\n- Reportar situaciones de posible incumplimiento",

            'metodologia' => "**Fuentes de Informacion:**\n- Diario Oficial de Colombia\n- Paginas web del Ministerio del Trabajo y MinSalud\n- Asesoria de la ARL\n- Gremios y asociaciones del sector\n\n**Criterios de Seleccion:**\n- Aplicabilidad segun actividad economica\n- Relacion con peligros y riesgos identificados\n- Requisitos de partes interesadas\n\n**Frecuencia de Revision:**\n- Revision trimestral de nueva normativa\n- Actualizacion inmediata ante cambios significativos",

            'evaluacion_cumplimiento' => "**Periodicidad:** Minimo una vez al ano\n\n**Responsable:** Responsable del SG-SST\n\n**Metodo:**\n1. Revisar cada requisito de la matriz\n2. Verificar evidencias de cumplimiento\n3. Identificar brechas\n4. Documentar hallazgos\n\n**Ante incumplimiento:**\n- Generar accion correctiva\n- Definir responsable y plazo\n- Dar seguimiento",

            'comunicacion' => "**Medios:**\n- Inducciones y reinducciones\n- Capacitaciones\n- Carteleras\n- Reuniones del {$comite}\n\n**Registros:**\n- Listas de asistencia\n- Actas de reunion\n- Evaluaciones",

            'actualizacion' => "**Se actualiza cuando:**\n- Nueva normativa aplicable\n- Derogacion o modificacion de normas\n- Cambios en procesos de la empresa\n- Nuevos peligros identificados\n\n**Responsable:** Responsable del SG-SST\n\n**Registro:** Control de cambios con fecha, descripcion y responsable"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
