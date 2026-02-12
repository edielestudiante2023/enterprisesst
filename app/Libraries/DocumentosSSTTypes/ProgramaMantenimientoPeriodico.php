<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProgramaMantenimientoPeriodico
 *
 * Implementa el Programa de Mantenimiento Periodico de Instalaciones,
 * Equipos, Maquinas y Herramientas para el estandar 4.2.5 de la Resolucion 0312/2019.
 *
 * TIPO B: Programa de 3 partes (Actividades PTA -> Indicadores -> Documento IA)
 * - Parte 1: Actividades de Mantenimiento en PTA (tipo_servicio = TIPO_SERVICIO)
 * - Parte 2: Indicadores de Mantenimiento (categoria = CATEGORIA)
 * - Parte 3: Documento formal generado con IA alimentado por Partes 1 y 2
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class ProgramaMantenimientoPeriodico extends AbstractDocumentoSST
{
    /** Valor de tipo_servicio en tbl_pta_cliente para este modulo */
    public const TIPO_SERVICIO = 'Mantenimiento Periodico';

    /** Valor de categoria en tbl_indicadores_sst para este modulo */
    public const CATEGORIA = 'mantenimiento_periodico';

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
        return 'programa_mantenimiento_periodico';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Programa de Mantenimiento Periodico de Instalaciones, Equipos, Maquinas y Herramientas';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece el programa de mantenimiento preventivo, correctivo y periodico de las instalaciones, equipos, maquinas y herramientas de la organizacion';
    }

    public function getEstandar(): ?string
    {
        return '4.2.5';
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
            ['numero' => 6, 'nombre' => 'Inventario de Activos y Equipos', 'key' => 'inventario_activos'],
            ['numero' => 7, 'nombre' => 'Tipos y Frecuencias de Mantenimiento', 'key' => 'tipos_frecuencias_mantenimiento'],
            ['numero' => 8, 'nombre' => 'Indicadores y Seguimiento', 'key' => 'indicadores_seguimiento'],
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

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);

        if (!empty($promptBD)) {
            return $promptBD;
        }

        return $this->getPromptFallback($seccionKey, $estandares);
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Programa de Mantenimiento Periodico de Instalaciones, Equipos, Maquinas y Herramientas.
Debe mencionar:
- Establecer un programa sistematico de mantenimiento preventivo, correctivo y periodico
- Garantizar condiciones seguras de operacion de instalaciones, equipos, maquinas y herramientas
- Prevenir accidentes laborales derivados de fallas en equipos e instalaciones
- Cumplimiento del Decreto 1072 de 2015 y Resolucion 0312 de 2019 (estandar 4.2.5)
- Mantener la disponibilidad operativa y la vida util de los activos
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del programa. Debe especificar:
- Aplica a todas las instalaciones fisicas, equipos, maquinas y herramientas de la organizacion
- Incluye equipos propios, arrendados y de contratistas que operen en la empresa
- Cubre mantenimiento preventivo, correctivo y predictivo
- Incluye instalaciones electricas, hidraulicas, sanitarias, locativas y estructurales
- Aplica a equipos de emergencia (extintores, gabinetes, alarmas, senalizacion)
- A quien aplica: alta direccion, responsable SST, area de mantenimiento, {$comite}, trabajadores
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para este programa. INCLUIR OBLIGATORIAMENTE:
- Mantenimiento preventivo
- Mantenimiento correctivo
- Mantenimiento predictivo
- Mantenimiento periodico
- Ficha tecnica de equipo
- Hoja de vida de equipo
- Orden de trabajo de mantenimiento
- Inspeccion de seguridad
- Equipo critico
- Vida util
- Calibracion
- Condicion subestandar
CANTIDAD: 12-14 definiciones basadas en normativa colombiana.",

            'marco_legal' => "Genera el marco legal aplicable. INCLUIR:
- Decreto 1072 de 2015, articulo 2.2.4.6.24 (Medidas de prevencion y control)
- Resolucion 0312 de 2019, estandar 4.2.5 (Mantenimiento periodico de instalaciones, equipos, maquinas, herramientas)
- Resolucion 2400 de 1979 (Disposiciones sobre vivienda, higiene y seguridad en establecimientos de trabajo)
- Ley 9 de 1979 (Codigo Sanitario Nacional)
- RETIE - Reglamento Tecnico de Instalaciones Electricas (Resolucion 90708 de 2013)
- NTC 2050 (Codigo Electrico Colombiano)
- Resolucion 1409 de 2012 (Trabajo en alturas - aplica a mantenimiento)
- NTC-ISO 55000 (Gestion de activos - referencia)
Presentar en formato tabla con numero de norma, ano y descripcion breve.",

            'responsabilidades' => "Define las responsabilidades en el programa:

**Alta Direccion / Representante Legal:**
- Aprobar el programa y asignar presupuesto para mantenimiento
- Garantizar los recursos tecnicos, humanos y financieros
- Asegurar que el mantenimiento se realice por personal competente

**Responsable del SG-SST:**
- Disenar, implementar y hacer seguimiento al programa
- Coordinar con el area de mantenimiento el cronograma
- Realizar inspecciones de seguridad a instalaciones
- Verificar el cumplimiento del programa y sus indicadores
- Reportar condiciones subestandar identificadas

**Area de Mantenimiento (si aplica):**
- Ejecutar el mantenimiento preventivo y correctivo programado
- Mantener actualizadas las fichas tecnicas y hojas de vida de equipos
- Registrar todas las intervenciones en las ordenes de trabajo
- Reportar necesidades de reposicion de equipos

**{$comite}:**
- Participar en las inspecciones de seguridad a instalaciones
- Reportar condiciones inseguras relacionadas con equipos o instalaciones
- Hacer seguimiento a las acciones correctivas derivadas de inspecciones

**Trabajadores:**
- Realizar inspeccion preoperacional de sus equipos y herramientas
- Reportar fallas, danos o condiciones inseguras inmediatamente
- Usar los equipos segun instrucciones del fabricante
- No intervenir equipos sin autorizacion ni competencia",

            'inventario_activos' => "Genera la seccion de inventario de activos y equipos:

**Clasificacion de activos por tipo:**
Describir las categorias de activos susceptibles de mantenimiento segun el contexto de la empresa:

1. **Instalaciones fisicas:** Estructura, techos, pisos, paredes, puertas, ventanas, areas comunes
2. **Instalaciones electricas:** Tableros, cableado, interruptores, tomas, iluminacion, planta electrica
3. **Instalaciones hidraulicas y sanitarias:** Tuberias, griferias, tanques, bombas, banos
4. **Equipos de oficina (si aplica):** Computadores, impresoras, proyectores, UPS
5. **Equipos de climatizacion:** Aires acondicionados, ventiladores, extractores
6. **Maquinaria y equipos de produccion (si aplica):** Segun actividad economica de la empresa
7. **Vehiculos y equipos de transporte (si aplica):** Vehiculos, montacargas, carretillas
8. **Equipos de emergencia:** Extintores, gabinetes contra incendio, sistemas de deteccion, senalizacion, luces de emergencia
9. **Herramientas manuales y electricas:** Herramientas de uso comun en la operacion

**Ficha tecnica de equipo:**
Cada equipo debe contar con ficha tecnica que incluya:
- Nombre del equipo, marca, modelo, serial
- Ubicacion y area responsable
- Fecha de adquisicion y vida util estimada
- Frecuencia de mantenimiento recomendada
- Proveedor de mantenimiento (interno o externo)
- Documentos asociados (manual, certificados, garantias)

**Clasificacion por criticidad:**
- **Alta:** Equipos cuya falla genera riesgo inmediato para la salud o la vida
- **Media:** Equipos cuya falla afecta la operacion pero no genera riesgo inmediato
- **Baja:** Equipos de soporte cuya falla es tolerable temporalmente

IMPORTANTE: Usa los datos del inventario de activos proporcionado por la empresa para personalizar esta seccion.",

            'tipos_frecuencias_mantenimiento' => "Genera la seccion de tipos y frecuencias de mantenimiento:

**Tipos de mantenimiento:**

1. **Mantenimiento Preventivo:**
   - Se ejecuta segun cronograma programado
   - Basado en recomendaciones del fabricante y experiencia operativa
   - Incluye: lubricacion, limpieza, ajuste, reemplazo de piezas de desgaste
   - Frecuencia: segun ficha tecnica de cada equipo (diaria, semanal, mensual, trimestral, semestral, anual)

2. **Mantenimiento Correctivo:**
   - Se ejecuta cuando se presenta una falla o averia
   - Requiere orden de trabajo y registro de la intervencion
   - Debe incluir analisis de causa raiz para evitar recurrencia
   - Priorizacion segun criticidad del equipo y riesgo para la seguridad

3. **Mantenimiento Predictivo (cuando aplique):**
   - Basado en monitoreo de condiciones (vibracion, temperatura, desgaste)
   - Permite anticipar fallas antes de que ocurran
   - Aplica a equipos criticos de alta inversion

**Inspecciones de seguridad:**
- Inspecciones locativas: trimestral
- Inspecciones electricas: semestral
- Inspecciones de equipos de emergencia: trimestral
- Inspecciones preoperacionales: diarias por el operador

**Cronograma anual de mantenimiento:**
Presentar el cronograma basado en las actividades registradas en el Plan de Trabajo Anual, organizadas por trimestre.

**Orden de trabajo:**
Describir el formato de orden de trabajo que debe incluir:
- Equipo intervenido, tipo de mantenimiento, descripcion del trabajo
- Responsable de ejecucion, fecha, repuestos utilizados
- Estado final del equipo y observaciones",

            'indicadores_seguimiento' => "Genera los indicadores de gestion y seguimiento del programa:

**Indicadores de Proceso:**
- Porcentaje de cumplimiento del programa de mantenimiento preventivo (meta >= 90%)
- Porcentaje de equipos con ficha tecnica actualizada (meta = 100%)
- Cumplimiento de inspecciones de seguridad a instalaciones (meta = 100%)

**Indicadores de Resultado:**
- Numero de fallas por mantenimiento inadecuado (meta <= 2/trimestre)
- Disponibilidad operativa de equipos criticos (meta >= 95%)
- Tasa de accidentes relacionados con equipos o instalaciones (meta = 0%)

Cada indicador debe tener: nombre, formula, meta, frecuencia de medicion, responsable.

**Seguimiento y evaluacion:**
- Revision trimestral del cumplimiento del cronograma
- Evaluacion semestral de indicadores
- Informe anual de resultados del programa
- Acciones de mejora basadas en hallazgos y tendencias
- Actualizacion del inventario y fichas tecnicas segun cambios

**Registros del programa:**
- Inventario actualizado de activos y equipos
- Fichas tecnicas y hojas de vida de equipos
- Ordenes de trabajo de mantenimiento
- Informes de inspecciones de seguridad
- Certificados de calibracion y revision tecnica
- Cronograma anual de mantenimiento con seguimiento
- Informes trimestrales y anual del programa"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Programa de Mantenimiento Periodico segun la Resolucion 0312/2019 (estandar 4.2.5) y el Decreto 1072/2015. El programa debe cubrir mantenimiento preventivo, correctivo y periodico de instalaciones, equipos, maquinas y herramientas.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer un programa sistematico de mantenimiento preventivo, correctivo y periodico para las instalaciones, equipos, maquinas y herramientas de {$nombreEmpresa}, garantizando condiciones seguras de operacion, previniendo accidentes laborales derivados de fallas y cumpliendo con los requisitos del estandar 4.2.5 de la Resolucion 0312 de 2019.\n\nEste programa busca mantener la disponibilidad operativa, prolongar la vida util de los activos y asegurar que todos los equipos e instalaciones cumplan con las condiciones de seguridad establecidas en el Decreto 1072 de 2015 y la normativa tecnica aplicable.",

            'alcance' => "Este programa aplica a todas las instalaciones fisicas, equipos, maquinas y herramientas de {$nombreEmpresa}, incluyendo equipos propios, arrendados y de contratistas que operen en las instalaciones de la organizacion. Cubre el mantenimiento preventivo, correctivo y periodico de instalaciones electricas, hidraulicas, sanitarias, locativas, estructurales y equipos de emergencia.\n\nEs de obligatorio cumplimiento para la alta direccion, el responsable del SG-SST, el area de mantenimiento (si aplica), el {$comite} y todos los trabajadores que operen equipos o herramientas.",

            'definiciones' => "**Mantenimiento preventivo:** Conjunto de actividades programadas de inspeccion, limpieza, lubricacion, ajuste y reemplazo de piezas, realizadas segun cronograma para evitar fallas.\n\n**Mantenimiento correctivo:** Actividades realizadas para restaurar el funcionamiento de un equipo o instalacion despues de una falla o averia.\n\n**Mantenimiento periodico:** Actividades de mantenimiento ejecutadas a intervalos regulares de tiempo o uso, independientemente del estado del equipo.\n\n**Ficha tecnica:** Documento que contiene las especificaciones del equipo: marca, modelo, serial, capacidad, manuales y recomendaciones del fabricante.\n\n**Hoja de vida de equipo:** Registro historico de todas las intervenciones de mantenimiento realizadas a un equipo.\n\n**Orden de trabajo:** Documento que autoriza y registra una intervencion de mantenimiento.\n\n**Inspeccion de seguridad:** Evaluacion sistematica de las condiciones de seguridad de instalaciones, equipos y areas de trabajo.\n\n**Equipo critico:** Equipo cuya falla puede generar un riesgo inmediato para la seguridad o la salud de los trabajadores.\n\n**Calibracion:** Proceso de verificar y ajustar la precision de un instrumento de medicion contra un patron conocido.\n\n**Condicion subestandar:** Estado de un equipo, instalacion o herramienta que no cumple con los requisitos minimos de seguridad.",

            'marco_legal' => "**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.24: Medidas de prevencion y control.\n\n**Resolucion 0312 de 2019:**\n- Estandar 4.2.5: Mantenimiento periodico de instalaciones, equipos, maquinas, herramientas.\n\n**Resolucion 2400 de 1979:**\n- Disposiciones sobre vivienda, higiene y seguridad en establecimientos de trabajo.\n\n**Ley 9 de 1979:**\n- Codigo Sanitario Nacional.\n\n**RETIE (Resolucion 90708 de 2013):**\n- Reglamento Tecnico de Instalaciones Electricas.\n\n**NTC 2050:**\n- Codigo Electrico Colombiano.\n\n**Resolucion 1409 de 2012:**\n- Trabajo en alturas (aplica a mantenimiento en alturas).",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Aprobar el programa y asignar presupuesto\n- Garantizar recursos tecnicos y financieros\n- Asegurar mantenimiento por personal competente\n\n**Responsable del SG-SST:**\n- Disenar, implementar y hacer seguimiento al programa\n- Coordinar cronograma de mantenimiento\n- Realizar inspecciones de seguridad\n- Verificar cumplimiento de indicadores\n\n**{$comite}:**\n- Participar en inspecciones de seguridad\n- Reportar condiciones inseguras\n- Hacer seguimiento a acciones correctivas\n\n**Trabajadores:**\n- Realizar inspeccion preoperacional de equipos\n- Reportar fallas y condiciones inseguras\n- Usar equipos segun instrucciones del fabricante",

            'inventario_activos' => "**Clasificacion de activos:**\n1. Instalaciones fisicas (estructura, techos, pisos)\n2. Instalaciones electricas (tableros, cableado, iluminacion)\n3. Instalaciones hidraulicas y sanitarias\n4. Equipos de oficina\n5. Equipos de climatizacion\n6. Equipos de emergencia (extintores, gabinetes, deteccion)\n7. Herramientas manuales y electricas\n\n**Ficha tecnica:** Nombre, marca, modelo, serial, ubicacion, frecuencia de mantenimiento, proveedor.\n\n**Criticidad:** Alta (riesgo inmediato), Media (afecta operacion), Baja (tolerable temporalmente).",

            'tipos_frecuencias_mantenimiento' => "**Mantenimiento Preventivo:** Segun cronograma programado, basado en recomendaciones del fabricante.\n\n**Mantenimiento Correctivo:** Ante fallas o averias, con orden de trabajo y analisis de causa.\n\n**Inspecciones:**\n- Locativas: trimestral\n- Electricas: semestral\n- Equipos de emergencia: trimestral\n- Preoperacionales: diarias",

            'indicadores_seguimiento' => "**Indicadores de proceso:**\n- Cumplimiento del programa de mantenimiento preventivo (meta >= 90%)\n- Equipos con ficha tecnica actualizada (meta = 100%)\n- Cumplimiento de inspecciones (meta = 100%)\n\n**Indicadores de resultado:**\n- Fallas por mantenimiento inadecuado (meta <= 2/trimestre)\n- Disponibilidad operativa de equipos criticos (meta >= 95%)\n- Accidentes por fallas en equipos (meta = 0%)\n\n**Seguimiento:** Revision trimestral, evaluacion semestral, informe anual."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de actividades e indicadores
     * CRITICO: Esto alimenta la IA con los datos reales de las fases previas (Parte 1 y Parte 2)
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? $contexto['nivel_riesgo'] ?? 'No especificado';
        $numTrabajadores = $contexto['total_trabajadores'] ?? $contexto['numero_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        $nivelTexto = match(true) {
            $estandares <= 7 => 'basico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (mas de 50 trabajadores o riesgo IV y V)'
        };

        // =====================================================================
        // OBTENER ACTIVIDADES DE MANTENIMIENTO PERIODICO DEL PTA (FASE 1)
        // =====================================================================
        $actividadesTexto = $this->obtenerActividadesMantenimiento($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE MANTENIMIENTO PERIODICO (FASE 2)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresMantenimiento($idCliente);

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Numero de trabajadores: {$numTrabajadores}
- Estandares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DE MANTENIMIENTO PERIODICO (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DE MANTENIMIENTO PERIODICO (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACION:
============================================================
- IMPORTANTE: Usa las actividades e indicadores listados arriba como base para el documento
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El INVENTARIO DE ACTIVOS debe reflejar los equipos e instalaciones reales de la empresa
- Los INDICADORES del documento deben corresponder con los configurados
- Personaliza el contenido para esta empresa especifica y su actividad economica
- Si la empresa es administrativa, enfoca en instalaciones, equipos de oficina y emergencia
- Si la empresa es industrial, enfoca en maquinaria, equipos de produccion y riesgos mecanicos
- Ajusta la extension y complejidad segun el nivel de estandares
- Usa terminologia de la normativa colombiana (Resolucion 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique especificamente
- Manten un tono profesional y tecnico";
    }

    /**
     * Obtiene las actividades de Mantenimiento Periodico del Plan de Trabajo
     */
    private function obtenerActividadesMantenimiento(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron actividades (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $actividades = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->groupStart()
                    ->where('tipo_servicio', self::TIPO_SERVICIO)
                    ->orLike('tipo_servicio', 'Mantenimiento', 'both')
                    ->orLike('actividad_plandetrabajo', 'mantenimiento preventivo', 'both')
                    ->orLike('actividad_plandetrabajo', 'mantenimiento correctivo', 'both')
                    ->orLike('actividad_plandetrabajo', 'mantenimiento periodico', 'both')
                    ->orLike('actividad_plandetrabajo', 'inspeccion de seguridad', 'both')
                    ->orLike('actividad_plandetrabajo', 'fichas tecnicas', 'both')
                    ->orLike('actividad_plandetrabajo', 'inventario de equipos', 'both')
                    ->orLike('actividad_plandetrabajo', 'calibracion', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de Mantenimiento Periodico registradas para el ano {$anio}";
            }

            $texto = "Total: " . count($actividades) . " actividades\n\n";

            foreach ($actividades as $i => $act) {
                $num = $i + 1;
                $actividad = $act['actividad_plandetrabajo'] ?? 'Sin nombre';
                $responsable = $act['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST';
                $fecha = $act['fecha_propuesta'] ?? '';
                $mes = $fecha ? date('F Y', strtotime($fecha)) : 'No programada';
                $estado = $act['estado_actividad'] ?? 'ABIERTA';
                $phva = $act['phva_plandetrabajo'] ?? 'HACER';

                $texto .= "{$num}. {$actividad}\n";
                $texto .= "   - Responsable: {$responsable}\n";
                $texto .= "   - Mes programado: {$mes}\n";
                $texto .= "   - Ciclo PHVA: {$phva}\n";
                $texto .= "   - Estado: {$estado}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo actividades Mantenimiento: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de Mantenimiento Periodico configurados
     */
    private function obtenerIndicadoresMantenimiento(int $idCliente): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', self::CATEGORIA)
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores de Mantenimiento Periodico configurados";
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
                $texto .= "   - Formula: {$formula}\n";
                $texto .= "   - Meta: {$meta}\n";
                $texto .= "   - Periodicidad: {$periodicidad}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo indicadores Mantenimiento: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }
}
