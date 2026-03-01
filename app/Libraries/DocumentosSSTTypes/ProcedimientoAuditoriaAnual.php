<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoAuditoriaAnual
 *
 * Implementa el Procedimiento para la Realizacion de la Auditoria Anual del SG-SST.
 * Estandar 6.1.2 de la Resolucion 0312/2019.
 *
 * TIPO A: Solo Parte 3 (documento formal con IA), sin actividades PTA ni indicadores propios.
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class ProcedimientoAuditoriaAnual extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;
    protected ?array $configCache = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    protected function getConfig(): array
    {
        if ($this->configCache === null) {
            $this->configCache = $this->getConfigService()->obtenerTipoDocumento($this->getTipoDocumento());
        }
        return $this->configCache;
    }

    public function getTipoDocumento(): string
    {
        return 'procedimiento_auditoria_anual';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento para la Realizacion de la Auditoria Anual del SG-SST';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia y los pasos para planificar, ejecutar y documentar la auditoria anual del Sistema de Gestion de Seguridad y Salud en el Trabajo';
    }

    public function getEstandar(): ?string
    {
        return '6.1.2';
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (empty($seccionesBD)) {
            return $this->getSeccionesFallback();
        }

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

    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Planificacion de la Auditoria', 'key' => 'planificacion_auditoria'],
            ['numero' => 7, 'nombre' => 'Ejecucion de la Auditoria', 'key' => 'ejecucion_auditoria'],
            ['numero' => 8, 'nombre' => 'Criterios y Metodologia de Auditoria', 'key' => 'criterios_metodologia'],
            ['numero' => 9, 'nombre' => 'Informe de Resultados', 'key' => 'informe_resultados'],
            ['numero' => 10, 'nombre' => 'Seguimiento y Acciones Correctivas', 'key' => 'seguimiento_acciones'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['responsable_sst', 'representante_legal'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para planificar, ejecutar y documentar la auditoria anual del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) de {$nombreEmpresa}, con el fin de verificar el cumplimiento de los requisitos legales aplicables, evaluar la eficacia de las medidas de prevencion y control, e identificar oportunidades de mejora continua.\n\nEste procedimiento da cumplimiento al articulo 2.2.4.6.29 del Decreto 1072 de 2015 y al estandar 6.1.2 de la Resolucion 0312 de 2019, que establece que la empresa debe adelantar auditoria por lo menos una vez al ano.",

            'alcance' => "Este procedimiento aplica a todos los elementos del Sistema de Gestion de Seguridad y Salud en el Trabajo de {$nombreEmpresa}, incluyendo: politica, organizacion, planificacion, aplicacion, evaluacion, auditoria y mejora, conforme al alcance definido en el articulo 2.2.4.6.30 del Decreto 1072 de 2015.\n\nInvolucra a la alta direccion, el responsable del SG-SST, el {$comite}, los auditores internos o externos designados, y todos los trabajadores como fuentes de informacion durante el proceso de auditoria.",

            'definiciones' => "**Auditoria:** Proceso sistematico, independiente y documentado para obtener evidencias y evaluarlas de manera objetiva con el fin de determinar el grado en que se cumplen los criterios de auditoria.\n\n**Auditor:** Persona con la competencia para llevar a cabo una auditoria. Debe ser independiente del area auditada.\n\n**Criterios de auditoria:** Conjunto de politicas, procedimientos, requisitos legales o normas contra los cuales se compara la evidencia.\n\n**Evidencia de auditoria:** Registros, declaraciones de hechos o cualquier otra informacion verificable pertinente para los criterios de auditoria.\n\n**Hallazgo de auditoria:** Resultado de la evaluacion de la evidencia recopilada frente a los criterios de auditoria.\n\n**No conformidad:** Incumplimiento de un requisito legal, normativo o del propio sistema de gestion.\n\n**Accion correctiva:** Accion tomada para eliminar la causa de una no conformidad detectada.",

            'marco_legal' => "**Normativa principal:**\n- Decreto 1072 de 2015, articulo 2.2.4.6.29: Auditoria de cumplimiento del SG-SST\n- Decreto 1072 de 2015, articulo 2.2.4.6.30: Alcance de la auditoria de cumplimiento\n- Resolucion 0312 de 2019, estandar 6.1.2: La empresa adelanta auditoria por lo menos una vez al ano\n\n**Normativa complementaria:**\n- Decreto 1072 de 2015, articulo 2.2.4.6.31: Revision por la alta direccion\n- Resolucion 0312 de 2019, estandar 6.1.1: Indicadores estructura, proceso y resultado del SG-SST\n- ISO 19011:2018: Directrices para la auditoria de sistemas de gestion (como referencia tecnica)",

            'responsabilidades' => "**Alta Direccion:**\n- Disponer los recursos necesarios para la ejecucion de la auditoria\n- Designar al auditor interno o contratar auditor externo\n- Revisar y aprobar los resultados de la auditoria\n- Aprobar el plan de accion correctivo\n\n**Responsable del SG-SST:**\n- Planificar el programa anual de auditoria\n- Coordinar la logistica y acompanar el proceso\n- Elaborar el plan de accion correctivo con base en los hallazgos\n- Hacer seguimiento al cierre de acciones correctivas\n\n**{$comite}:**\n- Participar activamente en el proceso de auditoria\n- Verificar que se auditen todos los aspectos del SG-SST\n- Analizar los resultados y emitir recomendaciones\n\n**Auditor:**\n- Ejecutar la auditoria conforme al plan aprobado\n- Aplicar criterios objetivos e imparciales\n- Emitir informe con hallazgos y recomendaciones\n\n**Trabajadores:**\n- Facilitar informacion veraz durante el proceso\n- Participar en entrevistas cuando sea requerido",

            'planificacion_auditoria' => "**Programa anual de auditoria:**\nSe debe programar al menos una auditoria al ano del SG-SST. El periodo recomendado es el segundo semestre del ano para permitir evaluar la gestion realizada.\n\n**Seleccion del equipo auditor:**\nEl auditor debe ser independiente (no puede auditar su propio trabajo), competente y con conocimiento de la normativa SST colombiana. Puede ser un auditor interno capacitado o un auditor externo contratado.\n\n**Plan de auditoria:**\nDebe incluir: objetivo, alcance, criterios de auditoria, areas y procesos a auditar, cronograma de actividades, recursos necesarios y responsables.\n\n**Documentos de trabajo:**\nPreparar listas de verificacion basadas en los 11 aspectos del articulo 2.2.4.6.30 del Decreto 1072 de 2015 y los estandares de la Resolucion 0312 aplicables al nivel de riesgo y tamano de la empresa.",

            'ejecucion_auditoria' => "**Reunion de apertura:**\nPresentacion del equipo auditor, confirmacion del plan, definicion de acuerdos logisticos y canales de comunicacion.\n\n**Recopilacion de evidencias:**\nMediante revision documental, entrevistas a trabajadores y directivos, observacion directa de procesos y verificacion de registros.\n\n**Evaluacion de hallazgos:**\nCada hallazgo se clasifica como: Conformidad, No conformidad mayor, No conformidad menor, Observacion u Oportunidad de mejora.\n\n**Reunion de cierre:**\nPresentacion de hallazgos preliminares, acuerdos sobre plazos para plan de accion y firma de actas.",

            'criterios_metodologia' => "**Criterios de auditoria:**\nSe evalua el cumplimiento contra: Decreto 1072 de 2015, Resolucion 0312 de 2019, politica y objetivos del SG-SST, procedimientos internos documentados.\n\n**Metodologia de calificacion:**\nCada aspecto se evalua como: Cumple, Cumple parcialmente, No cumple o No aplica.\n\n**Clasificacion de no conformidades:**\n- Mayor: Incumplimiento sistematico o que genera riesgo inmediato para la seguridad y salud\n- Menor: Desviacion puntual sin impacto critico en el sistema\n\n**Porcentaje de cumplimiento:**\nSe calcula como: (Aspectos que cumplen / Total aspectos aplicables) x 100%",

            'informe_resultados' => "**Estructura del informe:**\n1. Datos generales (fecha, auditor, periodo auditado, areas cubiertas)\n2. Resumen ejecutivo (porcentaje de cumplimiento, fortalezas, principales hallazgos)\n3. Detalle de hallazgos por aspecto auditado\n4. Conclusiones del auditor\n5. Recomendaciones\n6. Anexos (listas de verificacion, evidencias, registros)\n\nEl informe debe comunicarse a la alta direccion y al {$comite} conforme al articulo 2.2.4.6.29 del Decreto 1072 de 2015.",

            'seguimiento_acciones' => "**Plan de accion correctivo:**\nPara cada hallazgo se debe definir: accion correctiva, responsable, plazo de implementacion, recursos necesarios e indicador de cierre.\n\n**Priorizacion:**\n- No conformidades mayores: accion inmediata, maximo 30 dias\n- No conformidades menores: maximo 90 dias\n- Observaciones: siguiente periodo de auditoria\n\n**Seguimiento:**\nEl responsable del SG-SST verifica la implementacion de acciones y el auditor confirma la eficacia.\n\n**Conservacion de registros:**\nMinimo 20 anos conforme al articulo 2.2.4.6.13 del Decreto 1072 de 2015."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
