<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoAdquisiciones
 *
 * Implementa el Procedimiento de Adquisiciones para identificacion y evaluacion
 * de especificaciones en SST de las compras de productos y servicios.
 * Estandar 2.9.1 de la Resolucion 0312/2019.
 *
 * TIPO A: Solo Parte 3 (documento formal con IA), sin actividades PTA ni indicadores propios.
 * Variante B: Generacion con IA personalizada por empresa.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoAdquisiciones extends AbstractDocumentoSST
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
        return 'procedimiento_adquisiciones';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento de Adquisiciones en SST';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece los criterios de seguridad y salud en el trabajo para la adquisicion de productos y contratacion de servicios';
    }

    public function getEstandar(): ?string
    {
        return '2.9.1';
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
            ['numero' => 6, 'nombre' => 'Criterios SST para Adquisiciones de Productos', 'key' => 'criterios_adquisiciones_productos'],
            ['numero' => 7, 'nombre' => 'Criterios SST para Contratacion de Servicios', 'key' => 'criterios_contratacion_servicios'],
            ['numero' => 8, 'nombre' => 'Procedimiento de Evaluacion y Seleccion', 'key' => 'procedimiento_evaluacion_seleccion'],
            ['numero' => 9, 'nombre' => 'Seguimiento y Control', 'key' => 'seguimiento_control'],
            ['numero' => 10, 'nombre' => 'Registros y Evidencias', 'key' => 'registros_evidencias'],
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

    /**
     * Prompts de fallback si BD no tiene configuracion
     */
    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Adquisiciones en SST para una empresa asesorada por una consultora experta en SG-SST.
Debe mencionar:
- El proposito de establecer criterios de seguridad y salud en el trabajo para la adquisicion de productos y contratacion de servicios
- Garantizar que las compras y contrataciones cumplan con las especificaciones en SST
- Cumplimiento del Decreto 1072 de 2015 (articulo 2.2.4.6.27) y Resolucion 0312 de 2019 (estandar 2.9.1)
- La responsabilidad de la organizacion en la cadena de adquisiciones
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del procedimiento de adquisiciones en SST. Debe especificar:
- Aplica a todas las compras de productos, equipos, materiales, insumos y sustancias quimicas
- Aplica a la contratacion de servicios, contratistas y subcontratistas
- Involucra a todas las areas que participen en procesos de compras y contratacion
- A quien aplica: alta direccion, area de compras, responsable SST, {$comite}, contratistas y proveedores
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para este procedimiento de adquisiciones en SST. INCLUIR OBLIGATORIAMENTE:
- Adquisicion
- Especificaciones tecnicas en SST
- Proveedor
- Contratista
- Subcontratista
- Ficha de Datos de Seguridad (FDS/SDS/MSDS)
- Elementos de Proteccion Personal (EPP)
- Evaluacion de proveedores
- Sustancia quimica peligrosa
- Gestion del cambio
CANTIDAD: 10-12 definiciones basadas en normativa colombiana SST.",

            'marco_legal' => "Genera el marco legal aplicable al procedimiento de adquisiciones en SST en Colombia. INCLUIR:

**Normativa principal:**
- Decreto 1072 de 2015, articulo 2.2.4.6.27 (Adquisiciones)
- Resolucion 0312 de 2019, estandar 2.9.1
- Decreto 1072 de 2015, articulo 2.2.4.6.28 (Contratacion)

**Normativa complementaria:**
- Ley 55 de 1993 (Seguridad en la utilizacion de productos quimicos)
- Decreto 1496 de 2018 (Sistema Globalmente Armonizado - SGA)
- Resolucion 0773 de 2021 (EPP)
- Resolucion 2400 de 1979 (Disposiciones sobre higiene y seguridad)
- Decreto 1607 de 2002 (Clasificacion de actividades economicas)

Presentar en formato de tabla con: Norma, Articulo, Descripcion.",

            'responsabilidades' => "Define las responsabilidades en el procedimiento de adquisiciones SST:

**Alta Direccion / Gerencia:**
- Aprobar el procedimiento y asignar recursos
- Garantizar que las adquisiciones cumplan especificaciones SST
- Incluir criterios SST en la politica de compras

**Responsable del SG-SST:**
- Definir las especificaciones tecnicas en SST para productos y servicios
- Verificar el cumplimiento SST de proveedores y contratistas
- Mantener actualizada la lista de productos y servicios criticos en SST
- Capacitar al area de compras en requisitos SST

**Area de Compras / Adquisiciones:**
- Incluir las especificaciones SST en las ordenes de compra y contratos
- Solicitar fichas tecnicas, FDS y certificaciones SST
- Verificar que los proveedores cumplan requisitos SST antes de la compra
- Mantener el registro de evaluacion de proveedores

**{$comite}:**
- Participar en la revision de criterios SST para adquisiciones
- Reportar condiciones inseguras relacionadas con productos adquiridos

**Contratistas y Proveedores:**
- Cumplir con las especificaciones SST establecidas
- Entregar documentacion SST requerida (FDS, certificados, fichas tecnicas)
- Garantizar que sus trabajadores cumplan las normas SST de la organizacion",

            'criterios_adquisiciones_productos' => "Genera los criterios de SST que deben cumplir las adquisiciones de productos. Organizar por categoria:

**Equipos y herramientas:**
- Certificaciones de seguridad (normas tecnicas aplicables)
- Manuales de uso seguro en espanol
- Fichas tecnicas con especificaciones de seguridad
- Garantia y soporte tecnico

**Elementos de Proteccion Personal (EPP):**
- Cumplimiento de normas tecnicas colombianas o internacionales equivalentes
- Certificado de conformidad o ficha tecnica
- Vida util y condiciones de almacenamiento
- Compatibilidad con los peligros identificados en la matriz de peligros

**Sustancias quimicas:**
- Ficha de Datos de Seguridad (FDS) actualizada segun SGA/GHS
- Etiquetado conforme al Decreto 1496 de 2018
- Compatibilidad de almacenamiento
- Condiciones de transporte seguro
- Plan de emergencia para derrames

**Materiales e insumos generales:**
- Condiciones de manipulacion y almacenamiento seguro
- Disposicion final y manejo ambiental
- Compatibilidad con los procesos existentes

Incluir para cada categoria un checklist de verificacion SST.",

            'criterios_contratacion_servicios' => "Genera los criterios SST para la contratacion de servicios, contratistas y subcontratistas:

**Requisitos previos a la contratacion:**
- Verificacion del SG-SST del contratista (si aplica)
- Afiliacion al Sistema de Seguridad Social (ARL, EPS, AFP)
- Certificaciones y licencias vigentes del personal
- Matriz de peligros y evaluacion de riesgos de la actividad a contratar
- Plan de trabajo con analisis de riesgos

**Requisitos contractuales:**
- Clausulas SST obligatorias en contratos
- Responsabilidades SST del contratista
- Obligacion de cumplir el reglamento interno de SST
- Obligacion de reportar accidentes e incidentes
- Participacion en inducciones SST de la organizacion

**Requisitos para trabajos de alto riesgo:**
- Permisos de trabajo especificos
- Certificaciones vigentes (trabajo en alturas, espacios confinados, etc.)
- EPP especializado verificado
- Supervision durante la ejecucion

**Seguimiento durante la ejecucion:**
- Inspecciones periodicas de cumplimiento SST
- Reporte de condiciones y actos inseguros
- Participacion en simulacros si aplica",

            'procedimiento_evaluacion_seleccion' => "Genera el procedimiento paso a paso para evaluar y seleccionar proveedores y contratistas con criterios SST:

**Etapa 1: Identificacion de necesidades**
1. Definir el producto o servicio requerido
2. Identificar los peligros y riesgos SST asociados
3. Establecer las especificaciones tecnicas en SST

**Etapa 2: Busqueda y preseleccion**
1. Solicitar documentacion SST al proveedor/contratista
2. Verificar certificaciones y cumplimiento normativo
3. Evaluar antecedentes en SST (accidentalidad, sanciones)

**Etapa 3: Evaluacion**
1. Aplicar formato de evaluacion con criterios SST ponderados
2. Verificar fichas tecnicas, FDS, certificaciones
3. Realizar visita de verificacion si es critico
4. Puntuar y clasificar (Apto, Apto con condiciones, No apto)

**Etapa 4: Seleccion y contratacion**
1. Seleccionar al proveedor/contratista con mejor cumplimiento integral
2. Incluir clausulas SST en contrato u orden de compra
3. Registrar en base de datos de proveedores aprobados

**Etapa 5: Reevaluacion periodica**
1. Reevaluar proveedores y contratistas minimo anualmente
2. Actualizar estado segun desempeno SST
3. Retirar de base aprobada si persisten incumplimientos

Incluir flujograma simplificado del proceso.",

            'seguimiento_control' => "Genera los mecanismos de seguimiento y control del procedimiento de adquisiciones SST:

**Indicadores de gestion:**
- Porcentaje de compras con especificaciones SST verificadas
- Porcentaje de contratistas con SG-SST verificado
- Numero de no conformidades SST en adquisiciones
- Tiempo de respuesta ante incumplimientos SST de proveedores

**Actividades de verificacion:**
- Inspeccion de productos al momento de la recepcion
- Verificacion de documentacion SST antes de pagos
- Auditorias a proveedores/contratistas criticos
- Revision periodica de la lista de proveedores aprobados

**Acciones ante incumplimiento:**
- Devolucion de productos que no cumplan especificaciones SST
- Suspension de contratista por incumplimiento grave
- Registro en base de datos de proveedores no conformes
- Accion correctiva con plazo definido

**Revision del procedimiento:**
- Minimo una vez al ano o cuando haya cambios significativos
- Despues de accidentes o incidentes relacionados con adquisiciones
- Ante cambios normativos que afecten requisitos SST",

            'registros_evidencias' => "Genera la lista de registros y evidencias que se deben mantener para demostrar la implementacion del procedimiento:

**Registros de adquisiciones:**
- Formato de especificaciones SST para compras
- Ordenes de compra con clausulas SST
- Fichas tecnicas y FDS de productos adquiridos
- Certificados de conformidad de EPP
- Actas de recepcion con verificacion SST

**Registros de contratacion:**
- Formato de evaluacion SST de contratistas
- Contratos con clausulas SST
- Verificacion de afiliaciones al SGRL
- Registro de inducciones SST a contratistas
- Permisos de trabajo para actividades de alto riesgo

**Registros de seguimiento:**
- Informes de inspecciones SST a proveedores/contratistas
- Base de datos de proveedores aprobados (actualizada)
- Registro de no conformidades SST en adquisiciones
- Actas de reevaluacion periodica

**Tiempo de retencion:** Minimo 20 anos conforme al Decreto 1072 de 2015, articulo 2.2.4.6.13.

Presentar en formato de tabla con: Nombre del registro, Responsable, Frecuencia, Ubicacion."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Procedimiento de Adquisiciones en SST segun la Resolucion 0312/2019 estandar 2.9.1 y el Decreto 1072/2015 articulo 2.2.4.6.27. La empresa es asesorada por una consultora experta en SG-SST.";
    }

    /**
     * Contenido estatico de fallback para cuando la IA no esta disponible
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);
        $actividadEconomica = $contexto['actividad_economica_principal']
            ?? $contexto['sector_economico']
            ?? $cliente['codigo_actividad_economica']
            ?? 'su actividad economica';

        $contenidos = [
            'objetivo' => "Establecer los criterios y lineamientos de Seguridad y Salud en el Trabajo que debe aplicar {$nombreEmpresa} en la adquisicion de productos, equipos, materiales, insumos y sustancias quimicas, asi como en la contratacion de servicios, contratistas y subcontratistas.\n\nEste procedimiento garantiza el cumplimiento del articulo 2.2.4.6.27 del Decreto 1072 de 2015 y el estandar 2.9.1 de la Resolucion 0312 de 2019, asegurando que todas las adquisiciones incluyan la identificacion y evaluacion de las especificaciones en SST.",

            'alcance' => "Este procedimiento aplica a todas las compras de productos, equipos, herramientas, materiales, insumos, sustancias quimicas y Elementos de Proteccion Personal (EPP) que realice {$nombreEmpresa}, asi como a la contratacion de servicios, contratistas y subcontratistas que desarrollen actividades dentro de sus instalaciones o en representacion de la organizacion.\n\nInvolucra a la alta direccion, el area de compras, el responsable del SG-SST, el {$comite}, los trabajadores, contratistas y proveedores.",

            'definiciones' => "**Adquisicion:** Proceso de obtencion de productos, bienes o servicios necesarios para el funcionamiento de la organizacion.\n\n**Especificaciones tecnicas en SST:** Requisitos de seguridad y salud en el trabajo que deben cumplir los productos adquiridos o los servicios contratados.\n\n**Proveedor:** Persona natural o juridica que suministra productos o materiales a la organizacion.\n\n**Contratista:** Persona natural o juridica que presta servicios a la organizacion bajo un contrato, asumiendo los riesgos inherentes a la actividad.\n\n**Ficha de Datos de Seguridad (FDS):** Documento que proporciona informacion detallada sobre las propiedades de una sustancia quimica, sus peligros, medidas de proteccion y procedimientos de emergencia.\n\n**Elementos de Proteccion Personal (EPP):** Dispositivos, accesorios y vestimenta destinados a proteger al trabajador de peligros que puedan amenazar su seguridad o salud.\n\n**Evaluacion de proveedores:** Proceso sistematico para determinar la capacidad de un proveedor para cumplir con los requisitos establecidos, incluyendo los de SST.",

            'marco_legal' => "**Normativa principal:**\n- Decreto 1072 de 2015, articulo 2.2.4.6.27: La organizacion debe establecer un procedimiento para la adquisicion de bienes y la contratacion de servicios que garantice el cumplimiento de la normativa en SST.\n- Resolucion 0312 de 2019, estandar 2.9.1: Identificacion, evaluacion para adquisicion de productos y servicios en SST.\n- Decreto 1072 de 2015, articulo 2.2.4.6.28: Contratacion.\n\n**Normativa complementaria:**\n- Ley 55 de 1993: Seguridad en la utilizacion de productos quimicos.\n- Decreto 1496 de 2018: Adopcion del Sistema Globalmente Armonizado (SGA).\n- Resolucion 0773 de 2021: Elementos de Proteccion Personal.\n- Resolucion 2400 de 1979: Disposiciones sobre higiene y seguridad industrial.",

            'responsabilidades' => "**Alta Direccion:**\n- Aprobar el procedimiento de adquisiciones en SST\n- Garantizar que las compras cumplan especificaciones SST\n- Asignar recursos para la implementacion\n\n**Responsable del SG-SST:**\n- Definir las especificaciones tecnicas en SST para productos y servicios\n- Verificar el cumplimiento SST de proveedores y contratistas\n- Mantener actualizada la lista de productos y servicios criticos\n\n**Area de Compras:**\n- Incluir las especificaciones SST en ordenes de compra y contratos\n- Solicitar fichas tecnicas, FDS y certificaciones SST\n- Verificar que los proveedores cumplan requisitos SST\n\n**{$comite}:**\n- Participar en la revision de criterios SST para adquisiciones\n- Reportar condiciones inseguras relacionadas con productos adquiridos\n\n**Contratistas y Proveedores:**\n- Cumplir con las especificaciones SST establecidas\n- Entregar documentacion SST requerida",

            'criterios_adquisiciones_productos' => "**Equipos y herramientas:**\n- Certificaciones de seguridad aplicables\n- Manuales de uso seguro en espanol\n- Fichas tecnicas con especificaciones de seguridad\n\n**Elementos de Proteccion Personal (EPP):**\n- Cumplimiento de normas tecnicas colombianas o internacionales\n- Certificado de conformidad\n- Compatibilidad con peligros identificados en la matriz\n\n**Sustancias quimicas:**\n- Ficha de Datos de Seguridad (FDS) actualizada segun SGA\n- Etiquetado conforme al Decreto 1496 de 2018\n- Compatibilidad de almacenamiento\n\n**Materiales e insumos generales:**\n- Condiciones de manipulacion y almacenamiento seguro\n- Disposicion final y manejo ambiental",

            'criterios_contratacion_servicios' => "**Requisitos previos a la contratacion:**\n- Verificacion del SG-SST del contratista\n- Afiliacion al Sistema de Seguridad Social (ARL, EPS, AFP)\n- Certificaciones y licencias vigentes del personal\n\n**Requisitos contractuales:**\n- Clausulas SST obligatorias en contratos\n- Obligacion de cumplir el reglamento interno de SST\n- Obligacion de reportar accidentes e incidentes\n- Participacion en inducciones SST\n\n**Requisitos para trabajos de alto riesgo:**\n- Permisos de trabajo especificos\n- Certificaciones vigentes (trabajo en alturas, espacios confinados)\n- EPP especializado verificado",

            'procedimiento_evaluacion_seleccion' => "**Etapa 1 - Identificacion de necesidades:** Definir el producto o servicio, identificar peligros SST asociados y establecer especificaciones tecnicas.\n\n**Etapa 2 - Busqueda y preseleccion:** Solicitar documentacion SST, verificar certificaciones y cumplimiento normativo.\n\n**Etapa 3 - Evaluacion:** Aplicar formato de evaluacion con criterios SST ponderados, verificar fichas tecnicas y FDS, clasificar como Apto, Apto con condiciones o No apto.\n\n**Etapa 4 - Seleccion y contratacion:** Seleccionar proveedor con mejor cumplimiento integral, incluir clausulas SST en contrato.\n\n**Etapa 5 - Reevaluacion periodica:** Reevaluar minimo anualmente, actualizar estado segun desempeno SST.",

            'seguimiento_control' => "**Indicadores de gestion:**\n- Porcentaje de compras con especificaciones SST verificadas\n- Porcentaje de contratistas con SG-SST verificado\n- Numero de no conformidades SST en adquisiciones\n\n**Actividades de verificacion:**\n- Inspeccion de productos al momento de la recepcion\n- Verificacion de documentacion SST antes de pagos\n- Auditorias a proveedores criticos\n\n**Acciones ante incumplimiento:**\n- Devolucion de productos que no cumplan especificaciones SST\n- Suspension de contratista por incumplimiento grave\n- Accion correctiva con plazo definido",

            'registros_evidencias' => "**Registros de adquisiciones:**\n- Formato de especificaciones SST para compras\n- Ordenes de compra con clausulas SST\n- Fichas tecnicas y FDS de productos\n- Certificados de conformidad de EPP\n\n**Registros de contratacion:**\n- Formato de evaluacion SST de contratistas\n- Contratos con clausulas SST\n- Verificacion de afiliaciones al SGRL\n- Registro de inducciones SST a contratistas\n\n**Registros de seguimiento:**\n- Base de datos de proveedores aprobados\n- Registro de no conformidades SST en adquisiciones\n- Actas de reevaluacion periodica\n\n**Tiempo de retencion:** Minimo 20 anos conforme al articulo 2.2.4.6.13 del Decreto 1072 de 2015."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
