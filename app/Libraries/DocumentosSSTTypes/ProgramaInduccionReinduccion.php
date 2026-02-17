<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProgramaInduccionReinduccion
 *
 * Implementa la generación del Programa de Inducción y Reinducción en SG-SST (1.2.2)
 * Este documento se alimenta de:
 * - Etapas de inducción configuradas (Fase 1)
 * - Actividades de Inducción del Plan de Trabajo (Fase 2)
 * - Indicadores de Inducción configurados (Fase 3)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProgramaInduccionReinduccion extends AbstractDocumentoSST
{
    private ?DocumentoConfigService $configService = null;

    public function getTipoDocumento(): string
    {
        return 'programa_induccion_reinduccion';
    }

    public function getNombre(): string
    {
        return 'Programa de Inducción y Reinducción en SG-SST';
    }

    public function getDescripcion(): string
    {
        return 'Programa que establece el proceso de inducción y reinducción para todos los trabajadores, incluyendo identificación de peligros, evaluación de riesgos y controles para prevención de ATEL.';
    }

    public function getEstandar(): ?string
    {
        return '1.2.2';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Requisitos Generales', 'key' => 'requisitos_generales'],
            ['numero' => 4, 'nombre' => 'Contenido: Esquema General del Proceso', 'key' => 'contenido_esquema'],
            ['numero' => 5, 'nombre' => 'Etapa 1: Introducción a la Empresa', 'key' => 'etapa_introduccion'],
            ['numero' => 6, 'nombre' => 'Etapa 2: Seguridad y Salud en el Trabajo', 'key' => 'etapa_sst'],
            ['numero' => 7, 'nombre' => 'Etapa 3: Relaciones Laborales', 'key' => 'etapa_relaciones'],
            ['numero' => 8, 'nombre' => 'Etapa 4: Conocimiento y Recorrido de Instalaciones', 'key' => 'etapa_recorrido'],
            ['numero' => 9, 'nombre' => 'Etapa 5: Entrenamiento al Cargo', 'key' => 'etapa_entrenamiento'],
            ['numero' => 10, 'nombre' => 'Entrega de Memorias', 'key' => 'entrega_memorias'],
            ['numero' => 11, 'nombre' => 'Evaluación y Control', 'key' => 'evaluacion_control'],
            ['numero' => 12, 'nombre' => 'Indicadores del Programa', 'key' => 'indicadores'],
            ['numero' => 13, 'nombre' => 'Cronograma de Actividades', 'key' => 'cronograma'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de etapas, actividades e indicadores
     * CRÍTICO: Esto alimenta la IA con los datos reales de las fases previas
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        // Contexto base de la empresa
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? $contexto['sector_economico'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? $contexto['nivel_riesgo_arl'] ?? 'No especificado';
        $numTrabajadores = $contexto['numero_trabajadores'] ?? $contexto['total_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        // Estructuras SST
        $tieneCopasst = $contexto['tiene_copasst'] ?? 0;
        $tieneVigia = $contexto['tiene_vigia_sst'] ?? 0;
        $tieneBrigada = $contexto['tiene_brigada_emergencias'] ?? 0;
        $tieneComiteConvivencia = $contexto['tiene_comite_convivencia'] ?? 0;

        // Peligros identificados
        $peligrosTexto = '';
        if (!empty($contexto['peligros_identificados'])) {
            $peligrosArray = is_array($contexto['peligros_identificados'])
                ? $contexto['peligros_identificados']
                : json_decode($contexto['peligros_identificados'], true);
            if (is_array($peligrosArray)) {
                $peligrosTexto = implode(', ', $peligrosArray);
            }
        }

        $nivelTexto = match(true) {
            $estandares <= 7 => 'básico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (más de 50 trabajadores o riesgo IV y V)'
        };

        $comite = $tieneCopasst ? 'COPASST' : ($tieneVigia ? 'Vigía de SST' : 'Sin comité definido');

        // =====================================================================
        // OBTENER ETAPAS DE INDUCCIÓN (FASE 1)
        // =====================================================================
        $etapasTexto = $this->obtenerEtapasInduccion($idCliente, $anio);

        // =====================================================================
        // OBTENER ACTIVIDADES DE INDUCCIÓN DEL PLAN DE TRABAJO (FASE 2)
        // =====================================================================
        $actividadesTexto = $this->obtenerActividadesInduccion($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE INDUCCIÓN (FASE 3)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresInduccion($idCliente);

        // Construir contexto completo
        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})
- Comité/Vigía: {$comite}
- Tiene Brigada de Emergencias: " . ($tieneBrigada ? 'Sí' : 'No') . "
- Tiene Comité de Convivencia: " . ($tieneComiteConvivencia ? 'Sí' : 'No') . "
- Peligros identificados: {$peligrosTexto}

============================================================
ETAPAS DEL PROCESO DE INDUCCIÓN (FASE 1)
Estas son las etapas CONFIGURADAS para el proceso de inducción:
============================================================
{$etapasTexto}

============================================================
ACTIVIDADES DE INDUCCIÓN EN EL PLAN DE TRABAJO (FASE 2)
Estas son las actividades REALES registradas en el PTA:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DE INDUCCIÓN (FASE 3)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACIÓN:
============================================================
- IMPORTANTE: Usa las etapas, actividades e indicadores listados arriba como base para el documento
- El contenido de cada ETAPA debe reflejar los TEMAS configurados en la Fase 1
- El CRONOGRAMA debe basarse en las ACTIVIDADES del Plan de Trabajo
- Los INDICADORES del documento deben corresponder con los configurados en la Fase 3
- Personaliza el contenido para {$nombreEmpresa} usando sus datos reales
- Si tiene peligros específicos (ej: trabajo en alturas, químicos), inclúyelos en la Etapa 2
- Si tiene COPASST menciona el COPASST, si tiene Vigía menciona el Vigía de SST
- Ajusta la extensión según el nivel de estándares ({$estandares})
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    /**
     * Obtiene metadata de las consultas realizadas a BD para confirmar al usuario
     * IMPORTANTE: Esto permite mostrar al usuario qué datos se usaron para generar
     */
    public function getMetadataConsultas(array $cliente, ?array $contexto): array
    {
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');
        $metadata = [
            'tablas_consultadas' => [],
            'resumen' => '',
            'total_registros' => 0
        ];

        if ($idCliente <= 0) {
            $metadata['resumen'] = 'Cliente no identificado - no se consultaron tablas';
            return $metadata;
        }

        try {
            $db = \Config\Database::connect();

            // 1. Consultar etapas de inducción
            $etapas = $db->table('tbl_induccion_etapas')
                ->where('id_cliente', $idCliente)
                ->where('anio', $anio)
                ->countAllResults(false);

            $etapasData = $db->table('tbl_induccion_etapas')
                ->select('nombre_etapa')
                ->where('id_cliente', $idCliente)
                ->where('anio', $anio)
                ->orderBy('numero_etapa', 'ASC')
                ->get()
                ->getResultArray();

            $nombresEtapas = array_column($etapasData, 'nombre_etapa');

            $metadata['tablas_consultadas'][] = [
                'tabla' => 'tbl_induccion_etapas',
                'descripcion' => 'Etapas de Inducción (Fase 1)',
                'registros' => count($etapasData),
                'datos' => $nombresEtapas,
                'icono' => 'bi-list-check'
            ];
            $metadata['total_registros'] += count($etapasData);

            // 2. Consultar actividades del PTA
            $actividades = $db->table('tbl_pta_cliente')
                ->select('actividad_plandetrabajo')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->groupStart()
                    ->like('tipo_servicio', 'Induccion', 'both')
                    ->orLike('tipo_servicio', 'Reinduccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'Induccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'Reinduccion', 'both')
                ->groupEnd()
                ->get()
                ->getResultArray();

            $nombresActividades = array_map(function($a) {
                $nombre = $a['actividad_plandetrabajo'] ?? '';
                return strlen($nombre) > 50 ? substr($nombre, 0, 47) . '...' : $nombre;
            }, $actividades);

            $metadata['tablas_consultadas'][] = [
                'tabla' => 'tbl_pta_cliente',
                'descripcion' => 'Actividades del Plan de Trabajo (Fase 2)',
                'registros' => count($actividades),
                'datos' => $nombresActividades,
                'icono' => 'bi-calendar-check'
            ];
            $metadata['total_registros'] += count($actividades);

            // 3. Consultar indicadores (solo del numeral 1.2.2 para evitar duplicados)
            $indicadores = $db->table('tbl_indicadores_sst')
                ->distinct()
                ->select('nombre_indicador')
                ->where('id_cliente', $idCliente)
                ->where('numeral_resolucion', '1.2.2')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            $nombresIndicadores = array_column($indicadores, 'nombre_indicador');

            $metadata['tablas_consultadas'][] = [
                'tabla' => 'tbl_indicadores_sst',
                'descripcion' => 'Indicadores de Inducción (Fase 3)',
                'registros' => count($indicadores),
                'datos' => $nombresIndicadores,
                'icono' => 'bi-graph-up'
            ];
            $metadata['total_registros'] += count($indicadores);

            // 4. Contexto del cliente
            $tieneContexto = !empty($contexto);
            $metadata['tablas_consultadas'][] = [
                'tabla' => 'tbl_cliente_contexto_sst',
                'descripcion' => 'Contexto SST del Cliente',
                'registros' => $tieneContexto ? 1 : 0,
                'datos' => $tieneContexto ? ['Peligros, estándares, comités configurados'] : [],
                'icono' => 'bi-building'
            ];
            if ($tieneContexto) {
                $metadata['total_registros'] += 1;
            }

            // Generar resumen
            $metadata['resumen'] = sprintf(
                "✅ BD consultada: %d etapas, %d actividades PTA, %d indicadores, contexto SST",
                count($etapasData),
                count($actividades),
                count($indicadores)
            );

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo metadata de consultas: " . $e->getMessage());
            $metadata['resumen'] = 'Error al consultar bases de datos';
        }

        return $metadata;
    }

    /**
     * Obtiene las etapas de inducción configuradas (Fase 1)
     */
    private function obtenerEtapasInduccion(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron etapas (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $etapas = $db->table('tbl_induccion_etapas')
                ->where('id_cliente', $idCliente)
                ->where('anio', $anio)
                ->orderBy('numero_etapa', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($etapas)) {
                return "No hay etapas de inducción configuradas para el año {$anio}.\nSe usarán las etapas estándar del proceso de inducción.";
            }

            $texto = "Total: " . count($etapas) . " etapas configuradas\n\n";

            foreach ($etapas as $etapa) {
                $num = $etapa['numero_etapa'];
                $nombre = $etapa['nombre_etapa'] ?? 'Sin nombre';
                $descripcion = $etapa['descripcion_etapa'] ?? '';
                $duracion = $etapa['duracion_estimada_minutos'] ?? 30;
                $responsable = $etapa['responsable_sugerido'] ?? 'Responsable SST';
                $estado = $etapa['estado'] ?? 'borrador';

                // Decodificar temas
                $temas = [];
                if (!empty($etapa['temas'])) {
                    $temasArray = is_array($etapa['temas'])
                        ? $etapa['temas']
                        : json_decode($etapa['temas'], true);
                    if (is_array($temasArray)) {
                        foreach ($temasArray as $tema) {
                            $temas[] = $tema['nombre'] ?? $tema;
                        }
                    }
                }

                $texto .= "**ETAPA {$num}: {$nombre}**\n";
                if ($descripcion) {
                    $texto .= "Descripción: {$descripcion}\n";
                }
                $texto .= "Duración estimada: {$duracion} minutos\n";
                $texto .= "Responsable: {$responsable}\n";
                $texto .= "Estado: {$estado}\n";
                if (!empty($temas)) {
                    $texto .= "Temas a desarrollar:\n";
                    foreach ($temas as $i => $tema) {
                        $texto .= "   " . ($i + 1) . ". {$tema}\n";
                    }
                }
                $texto .= "\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo etapas de inducción: " . $e->getMessage());
            return "Error al obtener etapas: " . $e->getMessage();
        }
    }

    /**
     * Obtiene las actividades de Inducción del Plan de Trabajo (Fase 2)
     */
    private function obtenerActividadesInduccion(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron actividades (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            // Buscar actividades de inducción en el PTA
            $actividades = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->groupStart()
                    ->like('tipo_servicio', 'Induccion', 'both')
                    ->orLike('tipo_servicio', 'Reinduccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'Induccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'Reinduccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'proceso de induccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'programa de induccion', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de inducción registradas en el PTA para el año {$anio}";
            }

            $texto = "Total: " . count($actividades) . " actividades de inducción\n\n";

            foreach ($actividades as $i => $act) {
                $num = $i + 1;
                $actividad = $act['actividad_plandetrabajo'] ?? 'Sin nombre';
                $responsable = $act['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST';
                $fecha = $act['fecha_propuesta'] ?? '';
                $mes = $fecha ? $this->getMesEnEspanol(date('n', strtotime($fecha))) . ' ' . date('Y', strtotime($fecha)) : 'No programada';
                $estado = $act['estado_actividad'] ?? 'ABIERTA';
                $phva = $act['phva_plandetrabajo'] ?? 'HACER';
                $observaciones = $act['observaciones'] ?? '';

                $texto .= "{$num}. {$actividad}\n";
                $texto .= "   - Responsable: {$responsable}\n";
                $texto .= "   - Mes programado: {$mes}\n";
                $texto .= "   - Ciclo PHVA: {$phva}\n";
                $texto .= "   - Estado: {$estado}\n";
                if ($observaciones) {
                    $texto .= "   - Observaciones: {$observaciones}\n";
                }
                $texto .= "\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo actividades de inducción: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de Inducción configurados (Fase 3)
     */
    private function obtenerIndicadoresInduccion(int $idCliente): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            // Buscar indicadores de inducción (solo los del numeral 1.2.2)
            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('numeral_resolucion', '1.2.2')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores de inducción configurados.\nSe recomienda configurar al menos: Cobertura de Inducción, Cumplimiento del Programa, Oportunidad de Inducción.";
            }

            $texto = "Total: " . count($indicadores) . " indicadores\n\n";

            foreach ($indicadores as $i => $ind) {
                $num = $i + 1;
                $nombre = $ind['nombre_indicador'] ?? 'Sin nombre';
                $formula = $ind['formula'] ?? 'No definida';
                $meta = $ind['meta'] ?? 'No definida';
                $periodicidad = $ind['periodicidad'] ?? 'No definida';
                $tipo = $ind['tipo_indicador'] ?? 'No definido';
                $unidad = $ind['unidad_medida'] ?? '%';

                $texto .= "{$num}. {$nombre}\n";
                $texto .= "   - Tipo: {$tipo}\n";
                $texto .= "   - Fórmula: {$formula}\n";
                $texto .= "   - Meta: {$meta}{$unidad}\n";
                $texto .= "   - Periodicidad: {$periodicidad}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo indicadores de inducción: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }

    /**
     * Convierte número de mes a nombre en español
     */
    private function getMesEnEspanol(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$mes] ?? 'Mes desconocido';
    }

    /**
     * Obtiene el prompt para una sección desde la BD
     * Usa DocumentoConfigService para leer los prompts configurados
     */
    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        try {
            // Inicializar el servicio si no existe
            if ($this->configService === null) {
                $this->configService = new DocumentoConfigService();
            }

            // Obtener prompt desde la BD
            $prompt = $this->configService->obtenerPromptSeccion(
                $this->getTipoDocumento(),
                $seccionKey
            );

            if (!empty($prompt)) {
                // Reemplazar variables en el prompt
                $prompt = str_replace('{ESTANDARES}', (string)$estandares, $prompt);
                return $prompt;
            }

        } catch (\Exception $e) {
            log_message('warning', "Error obteniendo prompt de BD para {$seccionKey}: " . $e->getMessage());
        }

        // Fallback a prompts estáticos si no hay en BD
        return $this->getPromptEstatico($seccionKey, $estandares);
    }

    /**
     * Prompts estáticos de fallback específicos para inducción
     */
    private function getPromptEstatico(string $seccionKey, int $estandares): string
    {
        $comite = $estandares <= 10 ? 'Vigía de SST' : 'COPASST';

        $prompts = [
            'objetivo' => "Genera el objetivo del Programa de Inducción y Reinducción.
IMPORTANTE: Debe mencionar que busca facilitar el conocimiento global de la empresa y el SG-SST al trabajador.
Incluir: objetivos, metas, reglamentaciones, procedimientos y valores corporativos.
Usa los datos del contexto del cliente y las etapas configuradas.
Máximo 2 párrafos.",

            'alcance' => "Define el alcance del programa de inducción. Debe aplicarse a:
1. Personal nuevo antes de iniciar labores después de vinculación legal
2. Personal con cambio de cargo o área
3. Personal que requiera reinducción por actualización del SG-SST
4. Personal que reingresa después de incapacidad prolongada por accidente de trabajo
Personaliza según el número de trabajadores y tipo de empresa del contexto.",

            'requisitos_generales' => "Describe los requisitos generales del proceso de inducción.
Mencionar que es complemento del proceso de selección y el inicio de la socialización.
Usa el nombre de la empresa del contexto.
Incluir que la inducción debe realizarse ANTES de que el trabajador inicie sus labores.",

            'contenido_esquema' => "Genera una descripción del esquema general del proceso de inducción.
IMPORTANTE: Usa las ETAPAS CONFIGURADAS que aparecen en el contexto.
Describe brevemente cada etapa y su propósito.
NO inventes etapas - usa las que están en el contexto.",

            'etapa_introduccion' => "Genera el contenido de la Etapa 1 - Introducción a la Empresa.
IMPORTANTE: Usa los TEMAS CONFIGURADOS para esta etapa en el contexto.
Temas típicos: Historia de la empresa, Principios y Valores, Misión y Visión, Ubicación y objetivos, Organigrama.
Personaliza usando: razón social, sector económico, ciudad del cliente.",

            'etapa_sst' => "Genera el contenido de la Etapa 2 - Seguridad y Salud en el Trabajo.
IMPORTANTE:
- Incluye los TEMAS BASE de SST configurados en el contexto
- Si hay PELIGROS IDENTIFICADOS (ver contexto), incluye temas específicos
- Si el cliente tiene trabajo en alturas, incluye ese tema
- Si tiene riesgo químico, incluye manejo de sustancias
- Menciona el {$comite} según corresponda
- Incluye: Política SST, Reglamento higiene, Plan emergencia, Derechos y deberes",

            'etapa_relaciones' => "Genera el contenido de la Etapa 3 - Relaciones Laborales.
Incluye: Reglamento Interno de Trabajo, Explicación de pago salarial, Horario laboral, Prestaciones legales y extralegales.
Personaliza según los turnos de trabajo del contexto si están disponibles.",

            'etapa_recorrido' => "Genera el contenido de la Etapa 4 - Conocimiento y Recorrido de Instalaciones.
Incluye: Presentación del equipo de trabajo, Áreas administrativas, Áreas operativas/producción, Rutas de evacuación, Puntos de encuentro.
Si el cliente tiene múltiples sedes (ver contexto), menciona que el recorrido se hace en la sede asignada.",

            'etapa_entrenamiento' => "Genera el contenido de la Etapa 5 - Entrenamiento al Cargo.
Describe el proceso de entrenamiento en el puesto de trabajo y área específica.
Menciona: funciones del cargo, procedimientos operativos, uso de herramientas/equipos.
Incluir EPP requeridos según los PELIGROS IDENTIFICADOS del contexto.",

            'entrega_memorias' => "Genera la sección de entrega de memorias/documentación.
Documentos digitales típicos: Política SST, Política no alcohol/drogas, Reglamento higiene, Responsabilidades SST, Derechos y deberes, Reglamento Interno.
Documentos físicos: Copia contrato, Afiliación EPS, Carné ARL.
Ajusta según el nivel de estándares ({$estandares}).",

            'evaluacion_control' => "Genera la sección de evaluación y control del proceso de inducción.
Menciona: Formato de Control y Evaluación del Proceso de Inducción.
Incluir: responsable de evaluar, archivo en hoja de vida, entrega de copia al empleado.
Indicar que sirve para el indicador de cobertura de inducción.",

            'indicadores' => "Define los indicadores del Programa de Inducción.
IMPORTANTE: Usa los INDICADORES CONFIGURADOS listados en el contexto de la Fase 3.
NO inventes indicadores si hay configurados.
Para cada indicador presenta: nombre, tipo, fórmula, meta y periodicidad.
Si no hay indicadores configurados, sugiere: Cobertura de Inducción, Cumplimiento del Programa, Oportunidad de Inducción.",

            'cronograma' => "Genera el cronograma de actividades del Programa de Inducción.
IMPORTANTE: Usa las ACTIVIDADES REALES del Plan de Trabajo listadas en el contexto de la Fase 2.
NO inventes actividades - usa las que están registradas en el PTA.
Presenta las actividades con sus meses programados.
Si no hay actividades, indica que se deben programar según necesidad de contratación."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del Programa de Inducción y Reinducción.
Usa los datos del contexto de la empresa y las etapas, actividades e indicadores configurados.";
    }

    /**
     * Contenido estático de fallback
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "{$nombreEmpresa} establece el presente Programa de Inducción y Reinducción con el objetivo de facilitar el conocimiento global de la empresa y del Sistema de Gestión de Seguridad y Salud en el Trabajo a todos los trabajadores.\n\nEste programa busca que el trabajador conozca los objetivos, metas, reglamentaciones, procedimientos y valores corporativos desde el inicio de sus labores, garantizando su integración adecuada a la organización y la prevención de accidentes de trabajo y enfermedades laborales.",

            'alcance' => "El presente programa aplica a todo el personal de {$nombreEmpresa} en las siguientes situaciones:\n\n1. **Trabajadores nuevos:** Antes de iniciar labores después de vinculación legal\n2. **Cambio de cargo o área:** Personal que asume nuevas responsabilidades\n3. **Reinducción por actualización:** Cuando se actualiza el SG-SST o normatividad aplicable\n4. **Reingreso post-incapacidad:** Personal que retorna después de incapacidad prolongada por accidente de trabajo",

            'requisitos_generales' => "El proceso de inducción en {$nombreEmpresa} es parte fundamental de la formación y desarrollo del personal, constituyendo el complemento del proceso de selección y el inicio de la etapa de socialización del trabajador.\n\n**Requisitos:**\n- La inducción debe realizarse ANTES de que el trabajador inicie sus labores\n- Debe documentarse mediante registro de asistencia firmado\n- Incluye evaluación de conocimientos adquiridos\n- Se archiva evidencia en la hoja de vida del trabajador",

            'contenido_esquema' => "El proceso de inducción en {$nombreEmpresa} se desarrolla en las siguientes etapas:\n\n**Etapa 1 - Introducción a la Empresa:** Conocimiento de la organización, historia, misión, visión y valores.\n\n**Etapa 2 - Seguridad y Salud en el Trabajo:** Política SST, peligros, riesgos, plan de emergencias, derechos y deberes.\n\n**Etapa 3 - Relaciones Laborales:** Reglamento interno, aspectos contractuales, horarios y prestaciones.\n\n**Etapa 4 - Recorrido de Instalaciones:** Conocimiento físico de las áreas, rutas de evacuación y puntos de encuentro.\n\n**Etapa 5 - Entrenamiento al Cargo:** Capacitación específica en las funciones y procedimientos del puesto de trabajo.",

            'etapa_introduccion' => "**Etapa 1: Introducción a la Empresa**\n\nEn esta etapa el trabajador conocerá:\n\n1. **Historia de la empresa:** Reseña histórica y evolución de {$nombreEmpresa}\n2. **Principios y Valores:** Valores corporativos que guían el actuar de la organización\n3. **Misión y Visión:** Propósito y proyección de la empresa\n4. **Ubicación y Objetivos:** Contexto geográfico y objetivos estratégicos\n5. **Organigrama:** Estructura organizacional y líneas de mando",

            'etapa_sst' => "**Etapa 2: Seguridad y Salud en el Trabajo**\n\nContenido del SG-SST:\n\n1. **Aspectos generales y legales:** Marco normativo del Sistema de Gestión\n2. **Política de SST:** Compromiso de la alta dirección con la seguridad\n3. **Política de no alcohol ni drogas:** Prevención del consumo de sustancias\n4. **Reglamento de Higiene y Seguridad:** Normas de seguridad industrial\n5. **Plan de Emergencia:** Preparación y respuesta ante emergencias\n6. **Responsabilidades en SST:** Roles y funciones en el sistema\n7. **Derechos y Deberes:** Derechos y obligaciones de empleador y trabajadores\n8. **Funcionamiento del {$comite}:** Participación de los trabajadores en SST",

            'etapa_relaciones' => "**Etapa 3: Relaciones Laborales**\n\nAspectos laborales importantes:\n\n1. **Reglamento Interno de Trabajo:** Normas de convivencia y disciplina\n2. **Explicación de pago salarial:** Fechas, conceptos y método de pago\n3. **Horario laboral:** Jornadas, turnos y pausas establecidas\n4. **Prestaciones:** Beneficios legales y extralegales para el trabajador",

            'etapa_recorrido' => "**Etapa 4: Conocimiento y Recorrido de Instalaciones**\n\nRecorrido por las instalaciones:\n\n1. **Equipo de trabajo:** Presentación de compañeros y jefe inmediato\n2. **Áreas Administrativas:** Ubicación de oficinas y servicios de apoyo\n3. **Áreas Operativas:** Conocimiento de las áreas de producción o servicio\n4. **Rutas de Evacuación:** Identificación de salidas y recorridos de emergencia\n5. **Puntos de Encuentro:** Ubicación de zonas seguras ante emergencias",

            'etapa_entrenamiento' => "**Etapa 5: Entrenamiento al Cargo**\n\nCapacitación específica del puesto:\n\n1. **Funciones del cargo:** Descripción detallada de responsabilidades\n2. **Procedimientos operativos:** Instrucciones de trabajo seguro\n3. **Uso de herramientas y equipos:** Manejo correcto de recursos asignados\n4. **EPP requeridos:** Elementos de protección personal para el cargo\n5. **Riesgos específicos:** Peligros asociados al puesto de trabajo",

            'entrega_memorias' => "**Entrega de Documentación**\n\nAl finalizar la inducción se entrega al trabajador:\n\n**Documentos digitales o impresos:**\n- Política de Seguridad y Salud en el Trabajo\n- Política de prevención de consumo de alcohol y drogas\n- Reglamento de Higiene y Seguridad Industrial\n- Responsabilidades frente al SG-SST\n- Derechos y deberes en SST\n- Extracto del Reglamento Interno de Trabajo\n\n**Documentos contractuales:**\n- Copia del contrato de trabajo\n- Afiliación a seguridad social (EPS, AFP, ARL)\n- Carné de la ARL",

            'evaluacion_control' => "**Evaluación y Control del Proceso**\n\nMecanismo de verificación:\n\n1. **Formato de Control:** Se diligencia el formato de Control y Evaluación del Proceso de Inducción\n2. **Evaluación de conocimientos:** Se aplica evaluación sobre temas críticos de SST\n3. **Responsable:** El Responsable del SG-SST o delegado evalúa y cierra el proceso\n4. **Archivo:** La evidencia se archiva en la hoja de vida del trabajador\n5. **Entrega de copia:** Se entrega copia del registro al empleado\n6. **Indicador:** Alimenta el indicador de cobertura de inducción",

            'indicadores' => "**Indicadores del Programa**\n\n1. **Cobertura de Inducción**\n   - Fórmula: (Trabajadores con inducción completa / Total trabajadores nuevos) x 100\n   - Meta: 100%\n   - Periodicidad: Mensual\n\n2. **Cumplimiento del Programa**\n   - Fórmula: (Temas de inducción ejecutados / Temas programados) x 100\n   - Meta: 100%\n   - Periodicidad: Trimestral\n\n3. **Oportunidad de Inducción**\n   - Fórmula: (Inducciones realizadas antes del inicio de labores / Total inducciones) x 100\n   - Meta: 90%\n   - Periodicidad: Mensual",

            'cronograma' => "**Cronograma de Actividades**\n\nLas actividades de inducción se programan según la necesidad de contratación:\n\n- **Inducción general:** Al ingreso de cada trabajador nuevo\n- **Reinducción anual:** Una vez al año para todo el personal\n- **Reinducción por actualización:** Cuando se actualice el SG-SST\n- **Inducción específica al cargo:** Al asumir nuevas funciones\n\nEl cronograma detallado se establece en el Plan de Trabajo Anual del SG-SST."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
