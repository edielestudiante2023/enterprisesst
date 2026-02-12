<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoEvaluacionProveedores
 *
 * Implementa el Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas
 * para el estandar 2.10.1 de la Resolucion 0312/2019.
 *
 * Pregunta del auditor: "¿Estan considerados los aspectos de SST y el cumplimiento
 * de estandares minimos en el procedimiento de evaluacion y seleccion de proveedores
 * y contratistas?"
 *
 * TIPO A: Solo Parte 3 (documento formal con IA), sin actividades PTA ni indicadores.
 * Variante B: Generacion con IA personalizada por empresa.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoEvaluacionProveedores extends AbstractDocumentoSST
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
        return 'procedimiento_evaluacion_proveedores';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece los criterios y metodologia para evaluar y seleccionar proveedores y contratistas considerando aspectos de SST y cumplimiento de estandares minimos';
    }

    public function getEstandar(): ?string
    {
        return '2.10.1';
    }

    /**
     * Obtiene las secciones desde la BD
     */
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

    /**
     * Secciones de fallback si BD no tiene configuracion
     */
    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Criterios de Evaluacion SST', 'key' => 'criterios_evaluacion_sst'],
            ['numero' => 7, 'nombre' => 'Proceso de Seleccion', 'key' => 'proceso_seleccion'],
            ['numero' => 8, 'nombre' => 'Requisitos SST para Contratistas', 'key' => 'requisitos_sst_contratistas'],
            ['numero' => 9, 'nombre' => 'Seguimiento y Reevaluacion', 'key' => 'seguimiento_reevaluacion'],
            ['numero' => 10, 'nombre' => 'Registros y Formatos', 'key' => 'registros_formatos'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['consultor_sst', 'responsable_sst', 'representante_legal'];
    }

    /**
     * Obtiene el prompt para una seccion desde BD
     */
    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);

        if (!empty($promptBD)) {
            return $promptBD;
        }

        return $this->getPromptFallback($seccionKey, $estandares);
    }

    /**
     * Prompts de fallback si BD no tiene configuracion
     */
    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas en materia de SST.
Debe mencionar:
- Garantizar que los proveedores y contratistas cumplan con los requisitos de SST y estandares minimos
- Cumplimiento del Decreto 1072 de 2015 (articulo 2.2.4.6.28) y Resolucion 0312 de 2019 (estandar 2.10.1)
- Proteccion de los trabajadores propios y contratistas dentro de las instalaciones
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del procedimiento. Debe especificar:
- Aplica a todos los proveedores, contratistas y subcontratistas que presten servicios a la organizacion
- Incluye proveedores de bienes y servicios que impliquen riesgos para la SST
- Aplica desde la seleccion hasta la finalizacion del contrato o servicio
- Responsables: alta direccion, responsable SST, area de compras/contratacion, {$comite}
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para este procedimiento. INCLUIR OBLIGATORIAMENTE:
- Proveedor
- Contratista
- Subcontratista
- Evaluacion de proveedores
- Estandares Minimos de SST (Resolucion 0312/2019)
- Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST)
- Riesgo laboral
- Actividades de alto riesgo (Decreto 2090 de 2003)
CANTIDAD: 8-10 definiciones basadas en normativa colombiana.",

            'marco_legal' => "Lista el marco legal aplicable a la evaluacion de proveedores y contratistas en SST:

**Normativa principal:**
- Decreto 1072 de 2015: Articulo 2.2.4.6.28 (Contratacion - verificar cobertura y cumplimiento del SG-SST por parte de contratistas y subcontratistas)
- Resolucion 0312 de 2019: Estandar 2.10.1 (Evaluacion y seleccion de proveedores y contratistas)
- Ley 1562 de 2012: Definicion de accidente de trabajo para contratistas
- Decreto 723 de 2013: Afiliacion al SGRL de contratistas
- Ley 1010 de 2006: Prevencion del acoso laboral (aplicable a contratistas en las instalaciones)

Organizar por tipo de norma (Leyes, Decretos, Resoluciones).",

            'responsabilidades' => "Define las responsabilidades en la evaluacion y seleccion de proveedores:

**Alta Direccion:**
- Garantizar recursos para la gestion de SST con contratistas
- Aprobar el procedimiento de evaluacion y seleccion

**Responsable del SG-SST:**
- Verificar el cumplimiento de requisitos SST por parte de proveedores y contratistas
- Evaluar la documentacion SST presentada
- Realizar seguimiento al desempeno en SST durante la ejecucion del contrato

**Area de Compras/Contratacion:**
- Incluir los criterios de SST en los procesos de seleccion
- Solicitar la documentacion SST requerida
- Comunicar los requisitos SST a los proveedores y contratistas

**{$comite}:**
- Participar en la verificacion de condiciones de SST de contratistas
- Reportar incumplimientos observados

**Contratistas y Proveedores:**
- Cumplir con todos los requisitos de SST establecidos
- Presentar la documentacion requerida
- Reportar accidentes e incidentes",

            'criterios_evaluacion_sst' => "Describe los criterios de evaluacion SST que deben cumplir los proveedores y contratistas:

**Criterios obligatorios (eliminatorios):**
1. Afiliacion vigente al Sistema General de Riesgos Laborales (ARL)
2. Pago de aportes a seguridad social (pension, salud, ARL)
3. Cumplimiento de estandares minimos de SST segun Resolucion 0312/2019

**Criterios de evaluacion (ponderables):**
4. Existencia de un SG-SST documentado e implementado
5. Matriz de peligros y evaluacion de riesgos para las actividades a ejecutar
6. Programa de capacitacion en SST (incluir trabajo en alturas si aplica)
7. Entrega de EPP adecuados a sus trabajadores
8. Registro de accidentalidad (indicadores de AT, EL)
9. Procedimientos de trabajo seguro para actividades criticas
10. Plan de emergencias coordinado con la organizacion contratante

**Criterios especiales para actividades de alto riesgo:**
- Certificaciones especificas (ej: trabajo en alturas, espacios confinados)
- Permisos de trabajo
- Planes de rescate

Presentar como una lista de verificacion / checklist con puntaje.",

            'proceso_seleccion' => "Describe paso a paso el proceso de seleccion de proveedores y contratistas:

**Fase 1: Solicitud de documentacion SST**
- Formulario de evaluacion inicial
- Lista de documentos requeridos

**Fase 2: Evaluacion documental**
- Revision de certificados de ARL, pagos de seguridad social
- Verificacion de cumplimiento de estandares minimos (autoevaluacion o certificacion)
- Revision de politica y plan de trabajo SST del proveedor

**Fase 3: Calificacion y puntaje**
- Asignar puntaje segun criterios definidos
- Clasificar: Aprobado (>80%), Aprobado condicionado (60-80%), No aprobado (<60%)

**Fase 4: Comunicacion de resultados**
- Notificar resultado al proveedor
- Para aprobados condicionados: definir plan de mejora y plazo

**Fase 5: Registro en base de proveedores calificados**
- Actualizar listado de proveedores aprobados
- Vigencia de la evaluacion (anual)",

            'requisitos_sst_contratistas' => "Describe los requisitos de SST que deben cumplir los contratistas durante la ejecucion del contrato:

**Antes de iniciar actividades:**
- Presentar certificados de afiliacion al SGRL vigentes
- Entregar copia del SG-SST o plan de trabajo SST
- Participar en induccion SST de la organizacion contratante
- Presentar competencias del personal (certificados de alturas, etc.)

**Durante la ejecucion:**
- Cumplir las normas y procedimientos de SST de la organizacion
- Usar los EPP requeridos
- Reportar condiciones inseguras, incidentes y accidentes
- Participar en simulacros y actividades de emergencia
- Permitir inspecciones de SST por parte de la organizacion

**Al finalizar:**
- Entregar informe de SST (indicadores, incidentes, acciones correctivas)
- Certificar estado de salud de los trabajadores que participaron

Segun Decreto 1072, art. 2.2.4.6.28: el contratante debe verificar que contratistas y subcontratistas afilien a sus trabajadores al SGRL y cumplan con el SG-SST.",

            'seguimiento_reevaluacion' => "Describe el proceso de seguimiento y reevaluacion periodica de proveedores y contratistas:

**Seguimiento durante el contrato:**
- Inspecciones periodicas de SST (frecuencia segun nivel de riesgo)
- Revision de indicadores de accidentalidad
- Verificacion de pagos de seguridad social mensual
- Reuniones de seguimiento (para contratos de larga duracion)

**Reevaluacion periodica:**
- Frecuencia: Anual o al renovar contrato
- Criterios: Cumplimiento historico, indicadores SST, hallazgos de inspecciones
- Resultado: Mantener, mejorar o retirar del listado de proveedores calificados

**Acciones ante incumplimiento:**
1. Amonestacion escrita (primer incumplimiento menor)
2. Suspension temporal de actividades (incumplimiento grave)
3. Terminacion del contrato (incumplimiento reiterado o accidente grave por negligencia)
4. Exclusion del listado de proveedores calificados

**Registros del seguimiento:**
- Actas de inspeccion
- Reportes de hallazgos
- Planes de accion correctiva",

            'registros_formatos' => "Lista los registros y formatos asociados a este procedimiento:

**Formatos de evaluacion:**
- FT-SST-EP01: Formato de Evaluacion Inicial de Proveedores/Contratistas
- FT-SST-EP02: Lista de Verificacion de Requisitos SST
- FT-SST-EP03: Formato de Reevaluacion Periodica

**Registros de seguimiento:**
- FT-SST-EP04: Acta de Inspeccion SST a Contratistas
- FT-SST-EP05: Registro de Induccion SST a Contratistas
- FT-SST-EP06: Control de Documentacion SST de Proveedores

**Control del procedimiento:**
- Responsable: Responsable del SG-SST
- Lugar de archivo: Archivo documental del SG-SST
- Tiempo de retencion: Minimo 20 anos (segun Decreto 1072)
- Formato de archivo: Digital y/o fisico

Incluir tabla resumen con: Codigo | Nombre | Responsable | Frecuencia"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas segun la Resolucion 0312/2019 estandar 2.10.1 y el Decreto 1072/2015 articulo 2.2.4.6.28.";
    }

    /**
     * Contenido estatico de fallback para cuando la IA no esta disponible
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer los criterios y la metodologia para evaluar y seleccionar proveedores y contratistas que presten servicios a {$nombreEmpresa}, garantizando que cumplan con los requisitos de Seguridad y Salud en el Trabajo y los estandares minimos establecidos en la normativa colombiana.\n\nEste procedimiento da cumplimiento al articulo 2.2.4.6.28 del Decreto 1072 de 2015 y al estandar 2.10.1 de la Resolucion 0312 de 2019, asegurando la proteccion de los trabajadores propios y contratistas dentro de las instalaciones de la organizacion.",

            'alcance' => "Este procedimiento aplica a todos los proveedores, contratistas y subcontratistas que presten servicios a {$nombreEmpresa}, desde la fase de seleccion hasta la finalizacion del contrato o servicio. Incluye proveedores de bienes y servicios que impliquen riesgos para la Seguridad y Salud en el Trabajo.\n\nSon responsables de su aplicacion: la alta direccion, el responsable del SG-SST, el area de compras o contratacion, el {$comite} y los propios contratistas y proveedores.",

            'definiciones' => "**Proveedor:** Persona natural o juridica que suministra bienes o servicios a la organizacion.\n\n**Contratista:** Persona natural o juridica que ejecuta actividades o presta servicios en las instalaciones o bajo la direccion de la organizacion contratante.\n\n**Subcontratista:** Persona natural o juridica contratada por un contratista para ejecutar parte de las actividades del contrato.\n\n**Evaluacion de proveedores:** Proceso sistematico de verificacion del cumplimiento de requisitos de SST por parte de proveedores y contratistas.\n\n**Estandares Minimos de SST:** Conjunto de normas, requisitos y procedimientos de obligatorio cumplimiento para la implementacion del SG-SST, establecidos en la Resolucion 0312 de 2019.\n\n**Sistema de Gestion de SST (SG-SST):** Proceso logico y por etapas que permite la mejora continua de las condiciones de trabajo y salud.\n\n**Riesgo laboral:** Probabilidad de que un trabajador sufra un dano derivado de su actividad laboral.\n\n**Actividades de alto riesgo:** Actividades que por su naturaleza o condiciones de trabajo implican la exposicion a factores de riesgo que pueden generar accidentes graves o mortales.",

            'marco_legal' => "**Leyes:**\n- Ley 1562 de 2012: Modifica el Sistema General de Riesgos Laborales. Define accidente de trabajo incluyendo contratistas.\n- Ley 1010 de 2006: Prevencion del acoso laboral, aplicable a contratistas en las instalaciones.\n\n**Decretos:**\n- Decreto 1072 de 2015: Articulo 2.2.4.6.28 - Contratacion. Obligacion de verificar cobertura y cumplimiento del SG-SST por parte de contratistas y subcontratistas.\n- Decreto 723 de 2013: Afiliacion al SGRL de contratistas con contrato formal de prestacion de servicios.\n- Decreto 2090 de 2003: Actividades de alto riesgo.\n\n**Resoluciones:**\n- Resolucion 0312 de 2019: Estandar 2.10.1 - Evaluacion y seleccion de proveedores y contratistas.\n- Resolucion 1409 de 2012: Trabajo seguro en alturas (aplicable a contratistas que ejecuten trabajo en alturas).",

            'responsabilidades' => "**Alta Direccion:**\n- Garantizar recursos para la gestion de SST con contratistas\n- Aprobar el procedimiento de evaluacion y seleccion\n\n**Responsable del SG-SST:**\n- Verificar el cumplimiento de requisitos SST por parte de proveedores y contratistas\n- Evaluar la documentacion SST presentada\n- Realizar seguimiento al desempeno en SST durante la ejecucion del contrato\n\n**Area de Compras/Contratacion:**\n- Incluir los criterios de SST en los procesos de seleccion\n- Solicitar la documentacion SST requerida\n\n**{$comite}:**\n- Participar en la verificacion de condiciones de SST de contratistas\n- Reportar incumplimientos observados\n\n**Contratistas y Proveedores:**\n- Cumplir con todos los requisitos de SST establecidos\n- Presentar la documentacion requerida\n- Reportar accidentes e incidentes",

            'criterios_evaluacion_sst' => "**Criterios obligatorios (eliminatorios):**\n1. Afiliacion vigente al Sistema General de Riesgos Laborales (ARL)\n2. Pago de aportes a seguridad social (pension, salud, ARL)\n3. Cumplimiento de estandares minimos de SST segun Resolucion 0312/2019\n\n**Criterios de evaluacion (ponderables):**\n4. Existencia de un SG-SST documentado e implementado\n5. Matriz de peligros y evaluacion de riesgos\n6. Programa de capacitacion en SST\n7. Entrega de EPP adecuados\n8. Registro de accidentalidad\n9. Procedimientos de trabajo seguro para actividades criticas\n10. Plan de emergencias coordinado con la organizacion",

            'proceso_seleccion' => "**Fase 1: Solicitud de documentacion SST**\nSe envia al proveedor o contratista el formulario de evaluacion inicial junto con la lista de documentos requeridos.\n\n**Fase 2: Evaluacion documental**\nSe revisan certificados de ARL, pagos de seguridad social, cumplimiento de estandares minimos y plan de trabajo SST.\n\n**Fase 3: Calificacion y puntaje**\nSe asigna puntaje segun criterios: Aprobado (>80%), Aprobado condicionado (60-80%), No aprobado (<60%).\n\n**Fase 4: Comunicacion de resultados**\nSe notifica al proveedor. Para aprobados condicionados se define plan de mejora.\n\n**Fase 5: Registro en base de proveedores calificados**\nSe actualiza el listado con vigencia anual.",

            'requisitos_sst_contratistas' => "**Antes de iniciar actividades:**\n- Certificados de afiliacion al SGRL vigentes\n- Copia del SG-SST o plan de trabajo SST\n- Participar en induccion SST de la organizacion\n- Certificados de competencias (alturas, espacios confinados, etc.)\n\n**Durante la ejecucion:**\n- Cumplir normas y procedimientos de SST de la organizacion\n- Usar los EPP requeridos\n- Reportar condiciones inseguras, incidentes y accidentes\n- Participar en simulacros y actividades de emergencia\n\n**Al finalizar:**\n- Entregar informe de SST con indicadores\n- Certificar estado de salud de los trabajadores participantes",

            'seguimiento_reevaluacion' => "**Seguimiento durante el contrato:**\n- Inspecciones periodicas de SST segun nivel de riesgo\n- Verificacion mensual de pagos de seguridad social\n- Reunion de seguimiento para contratos de larga duracion\n\n**Reevaluacion periodica:**\n- Frecuencia: Anual o al renovar contrato\n- Criterios: Cumplimiento historico, indicadores SST, hallazgos\n- Resultado: Mantener, mejorar o retirar del listado\n\n**Acciones ante incumplimiento:**\n1. Amonestacion escrita\n2. Suspension temporal de actividades\n3. Terminacion del contrato\n4. Exclusion del listado de proveedores calificados",

            'registros_formatos' => "| Codigo | Nombre | Responsable | Frecuencia |\n|--------|--------|-------------|------------|\n| FT-SST-EP01 | Evaluacion Inicial de Proveedores | Responsable SST | Por proveedor nuevo |\n| FT-SST-EP02 | Lista de Verificacion Requisitos SST | Responsable SST | Por contrato |\n| FT-SST-EP03 | Reevaluacion Periodica | Responsable SST | Anual |\n| FT-SST-EP04 | Acta de Inspeccion SST a Contratistas | Responsable SST | Segun riesgo |\n| FT-SST-EP05 | Registro de Induccion SST a Contratistas | Responsable SST | Por ingreso |\n| FT-SST-EP06 | Control de Documentacion SST Proveedores | Area Compras | Permanente |\n\n**Tiempo de retencion:** Minimo 20 anos segun Decreto 1072 de 2015.\n**Lugar de archivo:** Archivo documental del SG-SST."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
