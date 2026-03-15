<?php

namespace App\Libraries;

/**
 * OttoTableMap — Mapa semántico de tablas para Otto (EnterpriseSST)
 *
 * TODAS las columnas clave fueron verificadas con DESCRIBE contra la BD real.
 * No hay columnas asumidas ni inferidas.
 *
 * Diferencias importantes respecto al gemelo (enterprisesstph):
 *   - tbl_pta_cliente → PK: id_ptacliente (NO id), estado: estado_actividad, actividad: actividad_plandetrabajo
 *   - tbl_pendientes  → PK: id_pendientes, campo tarea: tarea_actividad, sin estado VENCIDA
 *   - tbl_vencimientos_mantenimientos → PK: id_vencimientos_mmttos, estado: estado_actividad
 *   - tbl_mantenimientos → nombre del mantenimiento: detalle_mantenimiento
 *   - tbl_matrices → PK: id_matriz, sin campo tipo_matriz (solo tipo)
 *   - estandares → PK: id_estandar (no id)
 */
class OttoTableMap
{
    /**
     * Directiva global de filtrado adaptada a los ENUMs reales de esta BD.
     */
    public static function getGlobalDirectives(): string
    {
        return <<<TXT
## DIRECTIVA GLOBAL DE FILTRADO
- Por defecto, **todas las consultas se limitan al año actual**.
- Filtra por estados activos:
  - `tbl_pta_cliente.estado_actividad`: ABIERTA o GESTIONANDO
  - `tbl_pendientes.estado`: ABIERTA o "SIN RESPUESTA DEL CLIENTE"
  - `tbl_vencimientos_mantenimientos.estado_actividad`: "sin ejecutar"
  - `tbl_cronog_capacitacion.estado`: PROGRAMADA o REPROGRAMADA
- Si el usuario necesita registros históricos o cerrados, debe pedirlo **explícitamente**.
- Cuando apliques este filtro automático, **informa al usuario**: "Te muestro solo el año actual y registros abiertos. Si necesitas históricos, indícamelo."
TXT;
    }

    /**
     * Retorna el mapa como bloque de texto para el system prompt de OpenAI.
     */
    public static function getPromptBlock(): string
    {
        $map   = self::getMap();
        $lines = ["## MAPA SEMÁNTICO DE TABLAS DE NEGOCIO", ""];

        foreach ($map as $entry) {
            $lines[] = "### `{$entry['table']}`" . (!empty($entry['priority']) ? " ⭐ {$entry['priority']}" : "");
            $lines[] = "**Qué es:** {$entry['description']}";
            if (!empty($entry['use_for'])) {
                $lines[] = "**Úsala cuando pregunten:** " . implode(' / ', $entry['use_for']);
            }
            if (!empty($entry['key_columns'])) {
                $lines[] = "**Columnas clave:** " . implode(', ', $entry['key_columns']);
            }
            if (!empty($entry['relations'])) {
                $lines[] = "**Relaciones:** " . implode('; ', $entry['relations']);
            }
            if (!empty($entry['notes'])) {
                $lines[] = "**Notas:** {$entry['notes']}";
            }
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /**
     * Mapa completo con columnas verificadas contra BD real.
     * Ordenado por importancia de negocio.
     */
    public static function getMap(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════
            // TABLAS MAESTRAS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_clientes',
                'priority'    => '(1ª — FK raíz de todo el sistema)',
                'description' => 'Tabla maestra de empresas clientes que gestiona EnterpriseSST. Casi todas las demás tablas tienen FK hacia aquí.',
                'use_for'     => [
                    'listar clientes activos/inactivos',
                    'buscar cliente por nombre',
                    'consultor asignado a un cliente',
                    'fecha de vencimiento de contrato',
                    'datos de contacto del cliente',
                ],
                'key_columns' => [
                    'id_cliente (PK)',
                    'nombre_cliente',
                    'nit_cliente',
                    'estado (ENUM: activo, inactivo, pendiente)',
                    'id_consultor (FK → tbl_consultor)',
                    'estandares (7, 21 o 60 trabajadores)',
                    'fecha_fin_contrato',
                    'correo_cliente',
                    'frecuencia_servicio',
                    'ciudad_cliente',
                ],
                'relations'   => ['tbl_clientes.id_consultor → tbl_consultor.id_consultor'],
                'notes'       => 'Buscar siempre con LIKE \'%nombre%\' en nombre_cliente. También existe tbl_cliente (duplicado legacy) — usar tbl_clientes.',
            ],

            [
                'table'       => 'tbl_consultor',
                'priority'    => '(2ª — lista de consultores)',
                'description' => 'Consultores que atienden los clientes.',
                'use_for'     => [
                    'listar consultores',
                    '¿qué clientes tiene X consultor?',
                    '¿quién atiende a X cliente?',
                ],
                'key_columns' => [
                    'id_consultor (PK)',
                    'nombre_consultor',
                    'correo_consultor',
                    'rol (ENUM: consultant, admin)',
                ],
                'relations'   => ['tbl_clientes.id_consultor → tbl_consultor.id_consultor'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_pendientes',
                'priority'    => '(3ª — compromisos de visita)',
                'description' => 'Pendientes o compromisos del cliente registrados durante las visitas. Estados: ABIERTA, CERRADA, SIN RESPUESTA DEL CLIENTE, CERRADA POR FIN CONTRATO.',
                'use_for'     => [
                    '¿qué pendientes tiene X cliente?',
                    'compromisos de visita',
                    'seguimiento a pendientes abiertos',
                    '¿cuántos pendientes abiertos tiene X?',
                ],
                'key_columns' => [
                    'id_pendientes (PK)',
                    'id_cliente',
                    'id_acta_visita (FK → tbl_acta_visita.id)',
                    'tarea_actividad (descripción del pendiente)',
                    'responsable',
                    'estado (ENUM: ABIERTA, CERRADA, SIN RESPUESTA DEL CLIENTE, CERRADA POR FIN CONTRATO)',
                    'fecha_asignacion',
                    'fecha_cierre',
                    'conteo_dias',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_acta_visita → tbl_acta_visita.id'],
                'notes'       => 'No tiene estado VENCIDA. Usar conteo_dias para detectar pendientes críticos por antigüedad.',
            ],

            [
                'table'       => 'tbl_reporte',
                'priority'    => '(4ª — documentación subida)',
                'description' => 'Registro de reportes, soportes e informes generados o cargados para cada cliente.',
                'use_for'     => [
                    '¿ya se cargó el soporte de X?',
                    'reportes del mes',
                    '¿qué inspecciones están reportadas?',
                    'documentos cargados de X cliente',
                ],
                'key_columns' => [
                    'id_reporte (PK)',
                    'titulo_reporte',
                    'id_cliente',
                    'id_report_type',
                    'id_detailreport (subtipo: 9=actas, 10=locativa, 11=señalización, 12=extintores, 13=botiquín)',
                    'enlace',
                    'estado (ENUM: ABIERTO, GESTIONANDO, CERRADO)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'id_detailreport identifica el subtipo de reporte.',
            ],

            [
                'table'       => 'tbl_vencimientos_mantenimientos',
                'priority'    => '(5ª — mantenimientos)',
                'description' => 'Mantenimientos con sus fechas de vencimiento. Estado: "sin ejecutar", "ejecutado", "CERRADA", "CERRADA POR FIN CONTRATO".',
                'use_for'     => [
                    '¿qué mantenimientos están pendientes de X?',
                    '¿qué mantenimientos vencen pronto?',
                    'alertas de mantenimiento',
                ],
                'key_columns' => [
                    'id_vencimientos_mmttos (PK)',
                    'id_cliente',
                    'id_mantenimiento (FK → tbl_mantenimientos.id_mantenimiento)',
                    'id_consultor',
                    'fecha_vencimiento',
                    'fecha_realizacion',
                    'estado_actividad (ENUM: sin ejecutar, ejecutado, CERRADA, CERRADA POR FIN CONTRATO)',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_mantenimiento → tbl_mantenimientos.id_mantenimiento'],
                'notes'       => 'Para saber qué tipo de mantenimiento es, hacer JOIN con tbl_mantenimientos usando detalle_mantenimiento.',
            ],

            // ═══════════════════════════════════════════════════════════
            // PLAN DE TRABAJO
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_pta_cliente',
                'priority'    => '(principal del plan de trabajo)',
                'description' => 'Actividades del plan anual SG-SST de cada cliente. ATENCIÓN: PK es id_ptacliente, estado es estado_actividad, actividad es actividad_plandetrabajo.',
                'use_for'     => [
                    '¿qué actividades tiene abiertas X?',
                    'plan de trabajo',
                    'actividades pendientes / en gestión',
                    'porcentaje de avance de actividades',
                    '¿qué llevo en el plan de trabajo de X?',
                ],
                'key_columns' => [
                    'id_ptacliente (PK — NO es "id")',
                    'id_cliente',
                    'actividad_plandetrabajo (texto de la actividad)',
                    'estado_actividad (ENUM: ABIERTA, CERRADA, GESTIONANDO, CERRADA SIN EJECUCIÓN, CERRADA POR FIN CONTRATO)',
                    'porcentaje_avance',
                    'phva_plandetrabajo',
                    'numeral_plandetrabajo',
                    'responsable_definido_paralaactividad',
                    'fecha_propuesta',
                    'fecha_cierre',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Filtrar por defecto: año actual + estado_actividad IN ("ABIERTA","GESTIONANDO"). Cuando digan "mañana tengo visita", mostrar TODAS las abiertas sin filtro de fecha.',
            ],

            [
                'table'       => 'tbl_pta_cliente_audit',
                'description' => 'Auditoría de cambios en el plan de trabajo. Registra quién cambió qué y cuándo.',
                'use_for'     => [
                    '¿cuándo se cerró X actividad?',
                    'historial de cambios del plan de trabajo',
                    '¿quién modificó X actividad?',
                ],
                'key_columns' => [
                    'id_audit (PK)',
                    'id_ptacliente (FK → tbl_pta_cliente.id_ptacliente)',
                    'id_cliente',
                    'accion (ENUM: INSERT, UPDATE, DELETE, BULK_UPDATE)',
                    'campo_modificado',
                    'valor_anterior',
                    'valor_nuevo',
                    'nombre_usuario',
                    'fecha_accion',
                ],
                'relations'   => ['id_ptacliente → tbl_pta_cliente.id_ptacliente'],
                'notes'       => 'Tabla de auditoría — no tiene estado actual, solo el historial de cambios.',
            ],

            [
                'table'       => 'tbl_inventario_actividades_plandetrabajo',
                'description' => 'Catálogo maestro de actividades disponibles para incluir en el plan de trabajo.',
                'use_for'     => [
                    '¿qué actividades se pueden incluir en el plan de trabajo?',
                    'sugerir actividades para el numeral X',
                    'catálogo de actividades SG-SST',
                ],
                'key_columns' => [
                    'id_inventario_actividades_plandetrabajo (PK)',
                    'phva_plandetrabajo',
                    'numeral_plandetrabajo',
                    'actividad_plandetrabajo',
                    'responsable_sugerido_plandetrabajo',
                ],
                'relations'   => [],
                'notes'       => 'Maestro/catálogo sin id_cliente. Usar para sugerencias.',
            ],

            // ═══════════════════════════════════════════════════════════
            // ESTÁNDARES MÍNIMOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'estandares',
                'description' => 'Categorías de servicio: define frecuencia de visita según número de trabajadores.',
                'use_for'     => ['¿qué tipo de servicio tiene X cliente?', 'frecuencia de visita'],
                'key_columns' => ['id_estandar (PK)', 'nombre'],
                'relations'   => ['tbl_clientes.estandares referencia este catálogo'],
                'notes'       => 'No tiene prefijo tbl_. Valores típicos: 7, 21, 60 trabajadores.',
            ],

            [
                'table'       => 'evaluacion_inicial_sst',
                'description' => 'Calificación de los Estándares Mínimos del SG-SST para cada cliente.',
                'use_for'     => [
                    '¿cuál es la calificación de estándares mínimos de X?',
                    'porcentaje de cumplimiento SST',
                    '¿qué ítems no cumplen?',
                ],
                'key_columns' => [
                    'id_ev_ini (PK)',
                    'id_cliente',
                    'ciclo',
                    'estandar',
                    'numeral',
                    'item',
                    'calificacion',
                    'nivel_de_evaluacion',
                    'observaciones',
                    'puntaje_cuantitativo',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'No tiene prefijo tbl_. Para calificación actual usar esta tabla.',
            ],

            [
                'table'       => 'tbl_estandares_minimos',
                'description' => 'Catálogo maestro de todos los ítems de estándares mínimos SG-SST con su peso ponderado y a qué tamaño de empresa aplica.',
                'use_for'     => [
                    '¿qué ítems aplican para una empresa de 7/21/60 trabajadores?',
                    'peso ponderado de cada estándar',
                    'catálogo de estándares mínimos',
                ],
                'key_columns' => [
                    'id_estandar (PK)',
                    'item',
                    'nombre',
                    'criterio',
                    'ciclo_phva (ENUM: PLANEAR, HACER, VERIFICAR, ACTUAR)',
                    'categoria',
                    'peso_porcentual',
                    'aplica_7',
                    'aplica_21',
                    'aplica_60',
                ],
                'relations'   => [],
                'notes'       => 'Maestro/catálogo sin id_cliente.',
            ],

            // ═══════════════════════════════════════════════════════════
            // INDICADORES SST (propios de EnterpriseSST)
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_indicadores_sst',
                'priority'    => '(módulo propio de indicadores)',
                'description' => 'Indicadores SG-SST de cada cliente: estructura, proceso y resultado con su fórmula, meta y última medición.',
                'use_for'     => [
                    '¿cuáles son los indicadores de X?',
                    '¿qué indicadores no cumplen meta?',
                    'indicadores de estructura/proceso/resultado',
                    '¿cuál es el resultado del indicador X?',
                ],
                'key_columns' => [
                    'id_indicador (PK)',
                    'id_cliente',
                    'nombre_indicador',
                    'tipo_indicador (ENUM: estructura, proceso, resultado)',
                    'categoria',
                    'formula',
                    'meta',
                    'unidad_medida',
                    'periodicidad (ENUM: mensual, trimestral, semestral, anual)',
                    'valor_resultado',
                    'fecha_medicion',
                    'cumple_meta (1/0)',
                    'activo (1/0)',
                    'numeral_resolucion',
                    'phva',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_indicadores_sst_mediciones.id_indicador → id_indicador'],
                'notes'       => 'Para historial de mediciones usar tbl_indicadores_sst_mediciones.',
            ],

            [
                'table'       => 'tbl_indicadores_sst_mediciones',
                'description' => 'Historial de mediciones de cada indicador SG-SST.',
                'use_for'     => [
                    'evolución histórica de un indicador',
                    'mediciones anteriores',
                    '¿cómo ha evolucionado el indicador X?',
                ],
                'key_columns' => [
                    'id_medicion (PK)',
                    'id_indicador (FK → tbl_indicadores_sst.id_indicador)',
                    'periodo',
                    'valor_numerador',
                    'valor_denominador',
                    'valor_resultado',
                    'cumple_meta (1/0)',
                    'observaciones',
                    'fecha_registro',
                ],
                'relations'   => ['id_indicador → tbl_indicadores_sst.id_indicador'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // VISITAS Y ACTAS DE VISITA
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_acta_visita',
                'description' => 'Actas de visita realizadas a los clientes por el consultor.',
                'use_for'     => [
                    '¿cuándo fue la última visita a X?',
                    'historial de visitas',
                    'actas del mes',
                    '¿cuántas visitas se han hecho este año a X?',
                ],
                'key_columns' => [
                    'id (PK)',
                    'id_cliente',
                    'id_consultor',
                    'fecha_visita',
                    'hora_visita',
                    'modalidad (presencial/virtual)',
                    'estado (ENUM: borrador, pendiente_firma, completo)',
                    'proxima_reunion_fecha',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_acta_visita_temas.id_acta_visita → id'],
                'notes'       => 'Aplicar directiva global: año actual por defecto.',
            ],

            [
                'table'       => 'tbl_acta_visita_temas',
                'description' => 'Temas tratados en cada acta de visita.',
                'use_for'     => ['¿qué temas se trataron en la visita de X?', 'detalle de acta'],
                'key_columns' => [
                    'id (PK)',
                    'id_acta_visita (FK → tbl_acta_visita.id)',
                    'descripcion',
                    'orden',
                ],
                'relations'   => ['id_acta_visita → tbl_acta_visita.id'],
                'notes'       => 'No tiene columna "tema" — el texto va en "descripcion".',
            ],

            [
                'table'       => 'tbl_ciclos_visita',
                'description' => 'Estado del ciclo de visita mensual de cada cliente: si fue agendado, si fue visitado.',
                'use_for'     => [
                    '¿qué clientes no han sido visitados este mes?',
                    'clientes pendientes de visita',
                    'ciclo de visitas del mes',
                    '¿cuándo está agendado X?',
                ],
                'key_columns' => [
                    'id (PK)',
                    'id_cliente',
                    'id_consultor',
                    'anio',
                    'mes_esperado',
                    'estandar (frecuencia: 7/21/60)',
                    'fecha_agendada',
                    'id_agendamiento',
                    'fecha_acta',
                    'id_acta',
                    'estatus_agenda (ENUM: pendiente, cumple, incumple)',
                    'estatus_mes (ENUM: pendiente, cumple, incumple)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Esta tabla reemplaza tbl_agendamientos (que no existe en este proyecto).',
            ],

            // ═══════════════════════════════════════════════════════════
            // CAPACITACIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_cronog_capacitacion',
                'description' => 'Cronograma de capacitaciones SST programadas para cada cliente.',
                'use_for'     => [
                    '¿qué capacitaciones tiene programadas X?',
                    '¿qué capacitaciones faltan por ejecutar?',
                    'capacitaciones del año',
                    '¿se realizó la capacitación de X tema?',
                ],
                'key_columns' => [
                    'id_cronograma_capacitacion (PK)',
                    'id_cliente',
                    'id_capacitacion',
                    'fecha_programada',
                    'fecha_de_realizacion',
                    'estado (ENUM: PROGRAMADA, EJECUTADA, CANCELADA POR EL CLIENTE, REPROGRAMADA, CERRADA POR FIN CONTRATO)',
                    'perfil_de_asistentes',
                    'nombre_del_capacitador',
                    'numero_de_asistentes_a_capacitacion',
                    'numero_total_de_personas_programadas',
                    'porcentaje_cobertura',
                    'promedio_de_calificaciones',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Aplicar directiva global: año actual + PROGRAMADA/REPROGRAMADA por defecto.',
            ],

            // ═══════════════════════════════════════════════════════════
            // INSPECCIONES
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_inspeccion_locativa',
                'description' => 'Inspecciones locativas de las instalaciones del cliente.',
                'use_for'     => [
                    '¿cuándo fue la última inspección locativa de X?',
                    'estado de las instalaciones',
                    'inspecciones locativas del mes',
                ],
                'key_columns' => [
                    'id (PK)',
                    'id_cliente',
                    'id_consultor',
                    'fecha_inspeccion',
                    'observaciones',
                    'estado (ENUM: borrador, completo)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_hallazgo_locativo.id_inspeccion → id'],
                'notes'       => 'La columna fecha es fecha_inspeccion (no "fecha").',
            ],

            [
                'table'       => 'tbl_hallazgo_locativo',
                'description' => 'Hallazgos identificados durante inspecciones locativas.',
                'use_for'     => ['¿qué hallazgos se encontraron en X?', 'estado de hallazgos locativos'],
                'key_columns' => [
                    'id (PK)',
                    'id_inspeccion (FK → tbl_inspeccion_locativa.id)',
                    'descripcion',
                    'estado',
                    'fecha_hallazgo',
                    'fecha_correccion',
                    'imagen',
                    'imagen_correccion',
                    'observaciones',
                ],
                'relations'   => ['id_inspeccion → tbl_inspeccion_locativa.id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_inspeccion_extintores',
                'description' => 'Inspecciones a los extintores del cliente.',
                'use_for'     => [
                    '¿cuándo fue la última inspección de extintores de X?',
                    '¿cuántos extintores tiene X?',
                ],
                'key_columns' => [
                    'id (PK)',
                    'id_cliente',
                    'id_consultor',
                    'fecha_inspeccion',
                    'fecha_vencimiento_global',
                    'numero_extintores_totales',
                    'estado (ENUM: borrador, completo)',
                    'recomendaciones_generales',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_extintor_detalle.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_extintor_detalle',
                'description' => 'Estado detallado de cada extintor inspeccionado.',
                'use_for'     => ['detalle de cada extintor', 'extintores próximos a vencer'],
                'key_columns' => [
                    'id (PK)',
                    'id_inspeccion (FK → tbl_inspeccion_extintores.id)',
                    'fecha_vencimiento',
                    'presion',
                    'manometro',
                    'senalizacion',
                    'estado general (varios campos por condición)',
                    'foto',
                    'observaciones',
                    'orden',
                ],
                'relations'   => ['id_inspeccion → tbl_inspeccion_extintores.id'],
                'notes'       => 'Aplicar directiva para vencimientos.',
            ],

            [
                'table'       => 'tbl_inspeccion_botiquin',
                'description' => 'Inspecciones a los botiquines del cliente.',
                'use_for'     => ['inspección de botiquín', '¿cuándo se inspeccionó el botiquín de X?'],
                'key_columns' => [
                    'id (PK)',
                    'id_cliente',
                    'id_consultor',
                    'fecha_inspeccion',
                    'ubicacion_botiquin',
                    'instalado_pared (SI/NO)',
                    'con_senalizacion (SI/NO)',
                    'tipo_botiquin',
                    'recomendaciones',
                    'estado (ENUM: borrador, completo)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_elemento_botiquin.id_inspeccion → id'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_elemento_botiquin',
                'description' => 'Elementos del botiquín inspeccionados (uno por fila).',
                'use_for'     => ['¿qué le falta al botiquín de X?', 'inventario del botiquín', 'elementos vencidos del botiquín'],
                'key_columns' => [
                    'id (PK)',
                    'id_inspeccion (FK → tbl_inspeccion_botiquin.id)',
                    'clave (nombre del elemento)',
                    'cantidad',
                    'estado',
                    'fecha_vencimiento',
                ],
                'relations'   => ['id_inspeccion → tbl_inspeccion_botiquin.id'],
                'notes'       => 'Aplicar directiva para vencimientos.',
            ],

            [
                'table'       => 'tbl_inspeccion_senalizacion',
                'description' => 'Inspecciones a la señalización de seguridad.',
                'use_for'     => ['inspección de señalización', '¿cuándo se inspeccionó la señalización de X?', 'calificación señalización'],
                'key_columns' => [
                    'id (PK)',
                    'id_cliente',
                    'id_consultor',
                    'fecha_inspeccion',
                    'calificacion',
                    'descripcion_cualitativa',
                    'conteo_no_cumple',
                    'conteo_parcial',
                    'conteo_total',
                    'estado (ENUM: borrador, completo)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // DOCUMENTOS SST (propios de EnterpriseSST)
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_documentos_sst',
                'description' => 'Documentos del sistema SG-SST de cada cliente (políticas, procedimientos, programas, etc.) con su versión y estado.',
                'use_for'     => [
                    '¿qué documentos tiene X cliente?',
                    '¿está generada la política de X?',
                    'documentos en borrador',
                    'documentos pendientes de firma',
                    'documentos aprobados',
                ],
                'key_columns' => [
                    'id_documento (PK)',
                    'id_cliente',
                    'tipo_documento',
                    'titulo',
                    'codigo',
                    'anio',
                    'version',
                    'estado (ENUM: borrador, generado, en_revision, pendiente_firma, aprobado, firmado, obsoleto)',
                    'fecha_aprobacion',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Para documentos con firma electrónica ver tbl_doc_firma_solicitudes.',
            ],

            [
                'table'       => 'tbl_doc_documentos',
                'description' => 'Carpetas y documentos del sistema de gestión documental SST (módulo de carpetas digitales).',
                'use_for'     => [
                    'documentos en carpetas del cliente',
                    '¿tiene el documento X en sus carpetas?',
                ],
                'key_columns' => [
                    'id_documento (PK)',
                    'id_cliente',
                    'id_carpeta (FK → tbl_doc_carpetas.id_carpeta)',
                    'nombre',
                    'version_actual',
                    'estado (ENUM: borrador, en_revision, pendiente_firma, aprobado, obsoleto)',
                    'fecha_emision',
                    'fecha_proxima_revision',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_carpeta → tbl_doc_carpetas.id_carpeta'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // ACCIONES CORRECTIVAS Y PREVENTIVAS (propias de EnterpriseSST)
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_acc_hallazgos',
                'priority'    => '(módulo propio ACC)',
                'description' => 'Hallazgos que originan acciones correctivas/preventivas: pueden venir de auditorías, inspecciones, accidentes, ARL, COPASST, etc.',
                'use_for'     => [
                    '¿qué hallazgos abiertos tiene X?',
                    'acciones correctivas pendientes',
                    'hallazgos críticos o de alta severidad',
                    '¿se cerró el hallazgo de X?',
                ],
                'key_columns' => [
                    'id_hallazgo (PK)',
                    'id_cliente',
                    'tipo_origen (ENUM: auditoria_interna, inspeccion, indicador, investigacion_accidente, requerimiento_arl, copasst, etc.)',
                    'titulo',
                    'descripcion',
                    'area_proceso',
                    'severidad (ENUM: critica, alta, media, baja)',
                    'fecha_deteccion',
                    'fecha_limite_accion',
                    'estado (ENUM: abierto, en_tratamiento, en_verificacion, cerrado, cerrado_no_efectivo)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_acc_acciones.id_hallazgo → id_hallazgo'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_acc_acciones',
                'description' => 'Acciones correctivas, preventivas o de mejora asociadas a cada hallazgo.',
                'use_for'     => [
                    '¿qué acciones están pendientes para X hallazgo?',
                    'acciones correctivas vencidas',
                    '¿quién es responsable de X acción?',
                ],
                'key_columns' => [
                    'id_accion (PK)',
                    'id_hallazgo (FK → tbl_acc_hallazgos.id_hallazgo)',
                    'tipo_accion (ENUM: correctiva, preventiva, mejora)',
                    'descripcion_accion',
                    'responsable_nombre',
                    'fecha_compromiso',
                    'fecha_cierre_real',
                    'estado (ENUM: borrador, asignada, en_ejecucion, en_verificacion, cerrada_efectiva, cerrada_no_efectiva, reabierta, cancelada)',
                ],
                'relations'   => ['id_hallazgo → tbl_acc_hallazgos.id_hallazgo', 'tbl_acc_seguimientos.id_accion → id_accion'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_acc_seguimientos',
                'description' => 'Seguimientos o avances registrados para cada acción correctiva.',
                'use_for'     => ['¿cuál es el avance de X acción?', 'historial de seguimientos'],
                'key_columns' => [
                    'id_seguimiento (PK)',
                    'id_accion (FK → tbl_acc_acciones.id_accion)',
                    'tipo_seguimiento (ENUM: avance, evidencia, comentario, cambio_estado)',
                    'descripcion',
                    'porcentaje_avance',
                    'registrado_por_nombre',
                ],
                'relations'   => ['id_accion → tbl_acc_acciones.id_accion'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // CONTEXTO DEL CLIENTE (propio de EnterpriseSST)
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_cliente_contexto_sst',
                'description' => 'Contexto SST del cliente: ARL, número de trabajadores, nivel de riesgo, responsables SG-SST, comités existentes.',
                'use_for'     => [
                    '¿cuántos trabajadores tiene X?',
                    '¿cuál es el nivel de riesgo de X?',
                    '¿tiene COPASST X?',
                    '¿tiene vigía SST X?',
                    'responsable SG-SST de X',
                    'datos de ARL del cliente',
                ],
                'key_columns' => [
                    'id_contexto (PK)',
                    'id_cliente',
                    'nivel_riesgo_arl (ENUM: I, II, III, IV, V)',
                    'total_trabajadores',
                    'trabajadores_directos',
                    'trabajadores_temporales',
                    'arl_actual',
                    'tiene_copasst (1/0)',
                    'tiene_vigia_sst (1/0)',
                    'tiene_comite_convivencia (1/0)',
                    'tiene_brigada_emergencias (1/0)',
                    'responsable_sgsst_nombre',
                    'responsable_sgsst_cargo',
                    'licencia_sst_numero',
                    'licencia_sst_vigencia',
                    'delegado_sst_nombre',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'Un registro por cliente. Para responsables individuales ver tbl_cliente_responsables_sst.',
            ],

            [
                'table'       => 'tbl_cliente_responsables_sst',
                'description' => 'Personas con roles específicos en el SG-SST de cada cliente: rep. legal, vigía, COPASST, brigada, etc.',
                'use_for'     => [
                    '¿quién es el vigía SST de X?',
                    'miembros del COPASST de X',
                    '¿quién es el representante legal de X?',
                    'responsables de brigada de X',
                ],
                'key_columns' => [
                    'id_responsable (PK)',
                    'id_cliente',
                    'tipo_rol (ENUM: representante_legal, responsable_sgsst, vigia_sst, copasst_presidente, etc.)',
                    'nombre_completo',
                    'cargo',
                    'email',
                    'licencia_sst_numero',
                    'licencia_sst_vigencia',
                    'fecha_inicio',
                    'fecha_fin',
                    'activo (1/0)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // COMITÉS (propios de EnterpriseSST)
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_actas',
                'description' => 'Actas de los comités (COPASST, Comité de Convivencia, etc.) de cada cliente.',
                'use_for'     => [
                    '¿cuántas actas tiene el COPASST de X?',
                    'última acta de comité de X',
                    'actas pendientes de firma',
                    '¿se reunió el comité de X este mes?',
                ],
                'key_columns' => [
                    'id_acta (PK)',
                    'id_comite (FK → tbl_comites.id_comite)',
                    'id_cliente',
                    'numero_acta',
                    'tipo_acta (ENUM: ordinaria, extraordinaria, conformacion)',
                    'fecha_reunion',
                    'estado (ENUM: borrador, en_edicion, pendiente_firma, firmada, anulada)',
                    'hay_quorum (1/0)',
                    'anio',
                    'consecutivo_anual',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'id_comite → tbl_comites.id_comite'],
                'notes'       => 'Para ver los temas del acta ver la columna orden_del_dia y desarrollo.',
            ],

            [
                'table'       => 'tbl_comites',
                'description' => 'Comités SST activos de cada cliente (COPASST, Comité de Convivencia, Brigada).',
                'use_for'     => [
                    '¿tiene COPASST activo X?',
                    '¿cuándo vence el COPASST de X?',
                    'comités del cliente',
                ],
                'key_columns' => [
                    'id_comite (PK)',
                    'id_cliente',
                    'id_tipo (tipo de comité)',
                    'fecha_conformacion',
                    'fecha_vencimiento',
                    'estado (ENUM: activo, vencido, renovado, inactivo)',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_actas.id_comite → id_comite'],
                'notes'       => '',
            ],

            // ═══════════════════════════════════════════════════════════
            // MANTENIMIENTOS / PRESUPUESTO / MATRICES / CONTRATOS
            // ═══════════════════════════════════════════════════════════

            [
                'table'       => 'tbl_mantenimientos',
                'description' => 'Catálogo maestro de tipos de mantenimiento.',
                'use_for'     => ['¿qué tipos de mantenimiento existen?', 'nombre de un mantenimiento por su ID'],
                'key_columns' => [
                    'id_mantenimiento (PK)',
                    'detalle_mantenimiento (nombre/descripción del mantenimiento)',
                ],
                'relations'   => ['tbl_vencimientos_mantenimientos.id_mantenimiento → id_mantenimiento'],
                'notes'       => 'Es un maestro/catálogo. La columna se llama detalle_mantenimiento (no "nombre" ni "descripcion").',
            ],

            [
                'table'       => 'tbl_presupuesto_sst',
                'description' => 'Presupuestos SG-SST de cada cliente por año.',
                'use_for'     => ['presupuesto SST de X', '¿tiene presupuesto aprobado X?', 'valor del presupuesto de X'],
                'key_columns' => [
                    'id_presupuesto (PK)',
                    'id_cliente',
                    'anio',
                    'mes_inicio',
                    'estado (ENUM: borrador, aprobado, cerrado)',
                    'firmado_por',
                    'fecha_aprobacion',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente', 'tbl_presupuesto_items.id_presupuesto → id_presupuesto'],
                'notes'       => 'Para ver montos: JOIN con tbl_presupuesto_items y tbl_presupuesto_detalle.',
            ],

            [
                'table'       => 'tbl_presupuesto_items',
                'description' => 'Ítems o actividades del presupuesto SST.',
                'use_for'     => ['¿en qué se va a gastar el presupuesto de X?', 'ítems del presupuesto'],
                'key_columns' => [
                    'id_item (PK)',
                    'id_presupuesto (FK → tbl_presupuesto_sst.id_presupuesto)',
                    'id_categoria (FK → tbl_presupuesto_categorias.id_categoria)',
                    'actividad',
                    'descripcion',
                    'orden',
                ],
                'relations'   => ['id_presupuesto → tbl_presupuesto_sst.id_presupuesto'],
                'notes'       => 'Para ver valores presupuestados/ejecutados hacer JOIN con tbl_presupuesto_detalle.',
            ],

            [
                'table'       => 'tbl_presupuesto_detalle',
                'description' => 'Valores presupuestados y ejecutados por mes para cada ítem.',
                'use_for'     => ['¿cuánto se ha ejecutado del presupuesto de X?', 'ejecución presupuestal por mes'],
                'key_columns' => [
                    'id_detalle (PK)',
                    'id_item (FK → tbl_presupuesto_items.id_item)',
                    'mes',
                    'anio',
                    'presupuestado',
                    'ejecutado',
                    'notas',
                ],
                'relations'   => ['id_item → tbl_presupuesto_items.id_item'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_presupuesto_categorias',
                'description' => 'Categorías del presupuesto SST.',
                'use_for'     => ['categorías de presupuesto SST'],
                'key_columns' => ['id_categoria (PK)', 'codigo', 'nombre', 'orden'],
                'relations'   => [],
                'notes'       => 'Maestro/catálogo.',
            ],

            [
                'table'       => 'tbl_matrices',
                'description' => 'Matrices de riesgos, EPPs u otras matrices del cliente (archivos o enlaces cargados).',
                'use_for'     => ['¿tiene la matriz de riesgos X?', 'matrices del cliente', '¿ya se cargó la matriz de EPPs?'],
                'key_columns' => [
                    'id_matriz (PK)',
                    'id_cliente',
                    'tipo (tipo de matriz: riesgos, EPPs, etc.)',
                    'descripcion',
                    'enlace (URL o ruta al archivo)',
                    'observaciones',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => 'La columna del archivo se llama "enlace" (no "archivo"). No tiene campo "tipo_matriz" — usar "tipo".',
            ],

            [
                'table'       => 'tbl_contratos',
                'description' => 'Contratos de prestación de servicios de los clientes.',
                'use_for'     => [
                    '¿cuándo vence el contrato de X?',
                    '¿está activo el contrato de X?',
                    'valor del contrato de X',
                    'historial de contratos',
                ],
                'key_columns' => [
                    'id_contrato (PK)',
                    'id_cliente',
                    'numero_contrato',
                    'fecha_inicio',
                    'fecha_fin',
                    'valor_contrato',
                    'valor_mensual',
                    'frecuencia_visitas',
                    'tipo_contrato (ENUM: inicial, renovacion, ampliacion)',
                    'estado (ENUM: activo, vencido, cancelado)',
                    'estado_firma',
                ],
                'relations'   => ['id_cliente → tbl_clientes.id_cliente'],
                'notes'       => '',
            ],

            [
                'table'       => 'tbl_marco_normativo',
                'description' => 'Marco normativo legal SST vigente (Decreto 1072, Resolución 0312, etc.).',
                'use_for'     => ['normativa legal SST vigente', '¿qué dice la resolución 0312?', 'marco legal aplicable'],
                'key_columns' => [
                    'id (PK)',
                    'tipo_documento',
                    'marco_normativo_texto',
                    'fecha_actualizacion',
                    'activo (1/0)',
                ],
                'relations'   => [],
                'notes'       => 'Tabla global, sin id_cliente.',
            ],

        ];
    }
}
