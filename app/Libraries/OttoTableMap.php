<?php

namespace App\Libraries;

/**
 * OttoTableMap — Mapa semántico compacto para Otto (EnterpriseSST)
 * Formato: v_vista(SELECT)/tbl_tabla(WRITE): cols — descripción [estados]
 * Columnas verificadas con DESCRIBE contra BD real (empresas_sst).
 *
 * Diferencias clave vs gemelo enterprisesstph:
 *   tbl_pta_cliente → PK: id_ptacliente, estado: estado_actividad, actividad: actividad_plandetrabajo
 *   tbl_pendientes  → PK: id_pendientes, tarea: tarea_actividad, sin estado VENCIDA
 *   tbl_vencimientos_mantenimientos → PK: id_vencimientos_mmttos, estado: estado_actividad
 *   tbl_mantenimientos → nombre: detalle_mantenimiento
 *   tbl_matrices → PK: id_matriz, campo tipo (no tipo_matriz)
 *   estandares → PK: id_estandar (sin prefijo tbl_)
 */
class OttoTableMap
{
    /**
     * Directiva global de filtrado — versión compacta para system prompt.
     */
    public static function getGlobalDirectives(): string
    {
        return 'FILTROS POR DEFECTO: '
             . 'v_pta_cliente: estado_actividad IN (\'ABIERTA\',\'GESTIONANDO\') = activas. '
             . 'v_pendientes: estado = \'ABIERTA\' = abiertas. '
             . 'v_vencimientos_mantenimientos: estado_actividad = \'sin ejecutar\' = pendientes. '
             . 'v_cronog_capacitacion: estado IN (\'PROGRAMADA\',\'REPROGRAMADA\') = activas. '
             . 'v_indicadores_sst: activo = 1 (sin filtro de año — fecha_medicion puede ser NULL). '
             . 'NO filtres por año si la tabla no tiene columna anio o fecha relevante.';
    }

    /**
     * Genera el bloque de texto compacto: UNA LÍNEA por tabla/vista.
     */
    public static function getPromptBlock(): string
    {
        $lines = [];
        foreach (self::getMap() as $group => $entries) {
            $lines[] = "# {$group}";
            foreach ($entries as $e) {
                if (isset($e['view']) && isset($e['table'])) {
                    $prefix = "v_{$e['view']}(SELECT)/tbl_{$e['table']}(WRITE)";
                } elseif (isset($e['view'])) {
                    $prefix = "v_{$e['view']}(SELECT)";
                } elseif (isset($e['raw'])) {
                    $prefix = $e['raw'];
                } else {
                    $prefix = "tbl_{$e['table']}";
                }
                $cols = implode(',', $e['cols']);
                $lines[] = "{$prefix}: {$cols} — {$e['desc']}";
            }
        }
        return implode("\n", $lines);
    }

    /**
     * Mapa semántico completo. Estrategia dual: v_*(SELECT) / tbl_*(WRITE).
     */
    public static function getMap(): array
    {
        return [

            // ─────────────────────────────────────────────────────────────
            'Maestros' => [
                [
                    'raw'   => 'tbl_clientes',
                    'cols'  => ['id_cliente','nombre_cliente','id_consultor','estandares','actividad_economica_principal','sector_economico','estado'],
                    'desc'  => 'Tabla maestra de empresas clientes (FK raíz de todo el sistema)',
                ],
                [
                    'raw'   => 'tbl_consultor',
                    'cols'  => ['id_consultor','nombre_consultor','correo_consultor','telefono_consultor','rol'],
                    'desc'  => 'Consultores que atienden clientes',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Plan de Trabajo' => [
                [
                    'view'  => 'pta_cliente',
                    'table' => 'pta_cliente',
                    'cols'  => ['id_ptacliente','id_cliente','nombre_cliente','actividad_plandetrabajo','phva_plandetrabajo',
                                'numeral_plandetrabajo','estado_actividad','fecha_propuesta','fecha_cierre',
                                'responsable_definido_paralaactividad','porcentaje_avance','observaciones'],
                    'desc'  => 'Actividades del plan anual SG-SST por cliente [estados: ABIERTA|GESTIONANDO|CERRADA|CERRADA SIN EJECUCIÓN|CERRADA POR FIN CONTRATO]',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Pendientes' => [
                [
                    'view'  => 'pendientes',
                    'table' => 'pendientes',
                    'cols'  => ['id_pendientes','id_cliente','nombre_cliente','tarea_actividad','responsable',
                                'estado','estado_avance','fecha_asignacion','fecha_cierre','conteo_dias','id_acta_visita'],
                    'desc'  => 'Compromisos/tareas del cliente registrados en visitas [estados: ABIERTA|CERRADA|SIN RESPUESTA DEL CLIENTE|CERRADA POR FIN CONTRATO]',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Indicadores SST' => [
                [
                    'view'  => 'indicadores_sst',
                    'table' => 'indicadores_sst',
                    'cols'  => ['id_indicador','id_cliente','nombre_cliente','nombre_indicador','tipo_indicador',
                                'categoria','formula','meta','unidad_medida','periodicidad','valor_resultado',
                                'cumple_meta','fecha_medicion','activo'],
                    'desc'  => 'Indicadores SG-SST: estructura, proceso y resultado con meta y medición',
                ],
                [
                    'view'  => 'indicadores_mediciones',
                    'table' => 'indicadores_sst_mediciones',
                    'cols'  => ['id_medicion','id_indicador','nombre_indicador','id_cliente','nombre_cliente',
                                'periodo','valor_numerador','valor_denominador','valor_resultado','cumple_meta','fecha_registro'],
                    'desc'  => 'Historial de mediciones periódicas por indicador',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Capacitaciones' => [
                [
                    'view'  => 'cronog_capacitacion',
                    'table' => 'cronog_capacitacion',
                    'cols'  => ['id_cronograma_capacitacion','id_cliente','nombre_cliente','nombre_capacitacion',
                                'fecha_programada','fecha_de_realizacion','estado','perfil_de_asistentes',
                                'numero_de_asistentes_a_capacitacion','porcentaje_cobertura','promedio_de_calificaciones'],
                    'desc'  => 'Cronograma de capacitaciones SST [estados: PROGRAMADA|REPROGRAMADA|REALIZADA|CANCELADA]',
                ],
                [
                    'raw'   => 'capacitaciones_sst',
                    'cols'  => ['id_capacitacion','capacitacion','objetivo_capacitacion'],
                    'desc'  => 'Catálogo maestro de temas de capacitación disponibles',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Documentos SST' => [
                [
                    'view'  => 'documentos_sst',
                    'table' => 'documentos_sst',
                    'cols'  => ['id_documento','id_cliente','nombre_cliente','tipo_documento','titulo','codigo',
                                'anio','version','estado','fecha_aprobacion','aprobado_por'],
                    'desc'  => 'Documentos SST del cliente (políticas, procedimientos, programas) [estados: borrador|vigente|obsoleto]',
                ],
                [
                    'view'  => 'doc_versiones_sst',
                    'table' => 'doc_versiones_sst',
                    'cols'  => ['id_version','id_documento','id_cliente','nombre_cliente','titulo','version',
                                'version_texto','tipo_cambio','descripcion_cambio','estado','autorizado_por','fecha_autorizacion'],
                    'desc'  => 'Historial de versiones de documentos SST',
                ],
                [
                    'raw'   => 'tbl_doc_tipo_configuracion',
                    'cols'  => ['id_tipo_config','tipo_documento','nombre','flujo','activo'],
                    'desc'  => 'Catálogo de tipos de documentos SST configurados en el sistema',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Evaluación y Estándares' => [
                [
                    'view'  => 'evaluacion_inicial',
                    'table' => 'evaluacion_inicial_sst',
                    'cols'  => ['id_ev_ini','id_cliente','nombre_cliente','ciclo','estandar','item_del_estandar',
                                'calificacion','puntaje_cuantitativo','nivel_de_evaluacion','observaciones'],
                    'desc'  => 'Calificación de estándares mínimos SG-SST por cliente',
                ],
                [
                    'view'  => 'cliente_estandares',
                    'table' => 'cliente_estandares',
                    'cols'  => ['id','id_cliente','nombre_cliente','id_estandar','nombre_estandar',
                                'estado','calificacion','fecha_cumplimiento','observaciones'],
                    'desc'  => 'Estándares asignados a cada cliente con su estado de cumplimiento',
                ],
                [
                    'raw'   => 'tbl_estandares_minimos',
                    'cols'  => ['id_estandar','item','nombre','ciclo_phva','peso_porcentual','siete','veintiun','sesenta'],
                    'desc'  => 'Catálogo de 60 ítems de estándares mínimos SG-SST con peso ponderado',
                ],
                [
                    'raw'   => 'estandares',
                    'cols'  => ['id_estandar','nombre'],
                    'desc'  => 'Catálogo de categorías de servicio (7, 21, 60 trabajadores)',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Inspecciones' => [
                [
                    'view'  => 'inspeccion_locativa',
                    'table' => 'inspeccion_locativa',
                    'cols'  => ['id','id_cliente','nombre_cliente','id_consultor','fecha_inspeccion','estado','observaciones'],
                    'desc'  => 'Inspecciones locativas de instalaciones [estados: borrador|completo]',
                ],
                [
                    'view'  => 'inspeccion_extintores',
                    'table' => 'inspeccion_extintores',
                    'cols'  => ['id','id_cliente','nombre_cliente','id_consultor','fecha_inspeccion',
                                'numero_extintores_totales','fecha_vencimiento_global','estado','recomendaciones_generales'],
                    'desc'  => 'Inspecciones de extintores [estados: borrador|completo]',
                ],
                [
                    'view'  => 'inspeccion_botiquin',
                    'table' => 'inspeccion_botiquin',
                    'cols'  => ['id','id_cliente','nombre_cliente','id_consultor','fecha_inspeccion',
                                'ubicacion_botiquin','tipo_botiquin','estado','recomendaciones'],
                    'desc'  => 'Inspecciones de botiquines [estados: borrador|completo]',
                ],
                [
                    'view'  => 'inspeccion_senalizacion',
                    'table' => 'inspeccion_senalizacion',
                    'cols'  => ['id','id_cliente','nombre_cliente','id_consultor','fecha_inspeccion',
                                'calificacion','descripcion_cualitativa','conteo_no_cumple','conteo_parcial','estado'],
                    'desc'  => 'Inspecciones de señalización de seguridad [estados: borrador|completo]',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Visitas' => [
                [
                    'view'  => 'acta_visita',
                    'table' => 'acta_visita',
                    'cols'  => ['id','id_cliente','nombre_cliente','id_consultor','fecha_visita','hora_visita',
                                'motivo','modalidad','estado','proxima_reunion_fecha'],
                    'desc'  => 'Actas de visita al cliente [estados: borrador|pendiente_firma|completo]',
                ],
                [
                    'view'  => 'reportes',
                    'table' => 'reporte',
                    'cols'  => ['id_reporte','id_cliente','nombre_cliente','titulo_reporte','tipo_reporte',
                                'detalle_reporte','enlace','estado','created_at'],
                    'desc'  => 'Reportes, soportes e informes generados o cargados por cliente',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Acciones Correctivas' => [
                [
                    'view'  => 'acc_hallazgos',
                    'table' => 'acc_hallazgos',
                    'cols'  => ['id_hallazgo','id_cliente','nombre_cliente','titulo','descripcion',
                                'tipo_origen','severidad','estado','fecha_deteccion','fecha_limite_accion'],
                    'desc'  => 'Hallazgos ACC: no conformidades, incidentes, riesgos [estados: abierto|en_tratamiento|en_verificacion|cerrado|cerrado_no_efectivo]',
                ],
                [
                    'view'  => 'acc_acciones',
                    'table' => 'acc_acciones',
                    'cols'  => ['id_accion','id_hallazgo','titulo_hallazgo','id_cliente','nombre_cliente',
                                'tipo_accion','descripcion_accion','responsable_nombre','fecha_compromiso','fecha_cierre_real','estado'],
                    'desc'  => 'Acciones correctivas, preventivas y de mejora [estados: borrador|asignada|en_ejecucion|cerrada_efectiva|cerrada_no_efectiva]',
                ],
                [
                    'view'  => 'acc_seguimientos',
                    'table' => 'acc_seguimientos',
                    'cols'  => ['id_seguimiento','id_accion','tipo_accion','estado_accion','id_hallazgo',
                                'titulo_hallazgo','id_cliente','nombre_cliente','tipo_seguimiento',
                                'descripcion','porcentaje_avance','registrado_por_nombre','created_at'],
                    'desc'  => 'Seguimientos periódicos a acciones correctivas',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Comités y Actas' => [
                [
                    'view'  => 'actas_comite',
                    'table' => 'actas',
                    'cols'  => ['id_acta','id_cliente','nombre_cliente','id_comite','nombre_tipo_comite',
                                'codigo_tipo_comite','numero_acta','anio','tipo_acta','fecha_reunion',
                                'lugar','modalidad','estado','total_firmantes','firmantes_completados'],
                    'desc'  => 'Actas de comités COPASST/COCOLAB/BRIGADA/VIGIA [estados: borrador|en_edicion|pendiente_firma|firmada|anulada]',
                ],
                [
                    'view'  => 'acta_compromisos',
                    'table' => 'acta_compromisos',
                    'cols'  => ['id_compromiso','id_acta','numero_acta','fecha_reunion_acta','id_cliente','nombre_cliente',
                                'descripcion','responsable_nombre','responsable_email','fecha_compromiso',
                                'fecha_vencimiento','estado','porcentaje_avance','prioridad'],
                    'desc'  => 'Compromisos adquiridos en actas de comité [estados: pendiente|en_proceso|cumplido|vencido|cancelado]',
                ],
                [
                    'view'  => 'comite_miembros',
                    'table' => 'comite_miembros',
                    'cols'  => ['id_miembro','id_comite','id_cliente','nombre_cliente','nombre_tipo_comite',
                                'nombre_completo','cargo','representacion','rol_comite','estado','fecha_ingreso','fecha_retiro'],
                    'desc'  => 'Miembros activos de comités por cliente [estados: activo|inactivo|retirado]',
                ],
                [
                    'raw'   => 'tbl_acta_asistentes',
                    'cols'  => ['id_asistente','id_acta','id_miembro','nombre_completo','cargo','email','tipo_asistente','asistio','estado_firma'],
                    'desc'  => 'Asistentes registrados en actas de comité',
                ],
                [
                    'raw'   => 'tbl_tipos_comite',
                    'cols'  => ['id_tipo','nombre','codigo'],
                    'desc'  => 'Catálogo de tipos de comité [valores: COPASST|COCOLAB|BRIGADA|VIGIA]',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Contexto del Cliente' => [
                [
                    'view'  => 'cliente_contexto',
                    'table' => 'cliente_contexto_sst',
                    'cols'  => ['id_contexto','id_cliente','nombre_cliente','sector_economico','nivel_riesgo_arl',
                                'total_trabajadores','trabajadores_directos','numero_sedes','arl_actual',
                                'responsable_sgsst_nombre','tiene_copasst','tiene_vigia_sst','peligros_identificados'],
                    'desc'  => 'Contexto SST del cliente: trabajadores, riesgo, responsables, comités',
                ],
                [
                    'view'  => 'responsables_sst',
                    'table' => 'cliente_responsables_sst',
                    'cols'  => ['id_responsable','id_cliente','nombre_cliente','tipo_rol','nombre_completo',
                                'cargo','email','licencia_sst_numero','licencia_sst_vigencia','activo'],
                    'desc'  => 'Responsables SST del cliente (RLSST, delegado, coordinador)',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Contratos' => [
                [
                    'view'  => 'contratos',
                    'table' => 'contratos',
                    'cols'  => ['id_contrato','id_cliente','nombre_cliente','numero_contrato','fecha_inicio',
                                'fecha_fin','valor_contrato','valor_mensual','tipo_contrato','estado','estado_firma'],
                    'desc'  => 'Contratos de servicio SST por cliente [estados: activo|vencido|cancelado]',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Presupuesto SST' => [
                [
                    'view'  => 'presupuesto',
                    'table' => 'presupuesto_sst',
                    'cols'  => ['id_presupuesto','id_cliente','nombre_cliente','anio','mes_inicio',
                                'estado','firmado_por','fecha_aprobacion'],
                    'desc'  => 'Cabecera del presupuesto anual SST por cliente [estados: borrador|aprobado|ejecutado]',
                ],
                [
                    'view'  => 'presupuesto_detalle',
                    'table' => 'presupuesto_detalle',
                    'cols'  => ['id_detalle','id_item','mes','anio','presupuestado','ejecutado',
                                'nombre_item','nombre_categoria','id_cliente','nombre_cliente','anio_presupuesto'],
                    'desc'  => 'Detalle mes a mes de ejecución presupuestal por ítem y categoría',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Matrices' => [
                [
                    'view'  => 'matrices',
                    'table' => 'matrices',
                    'cols'  => ['id_matriz','id_cliente','nombre_cliente','tipo','descripcion','enlace','observaciones'],
                    'desc'  => 'Matrices SST del cliente (peligros, legal, etc.) con enlace al archivo',
                ],
                [
                    'raw'   => 'matriz_legal',
                    'cols'  => ['id_norma_legal','sector','tema','tipo_norma','descripcion_norma','estado'],
                    'desc'  => 'Catálogo de 491 normas legales SST Colombia',
                ],
                [
                    'raw'   => 'tbl_marco_normativo',
                    'cols'  => ['id','tipo_documento','marco_normativo_texto'],
                    'desc'  => 'Marco normativo SST general de referencia',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Mantenimientos' => [
                [
                    'view'  => 'vencimientos_mantenimientos',
                    'table' => 'vencimientos_mantenimientos',
                    'cols'  => ['id_vencimientos_mmttos','id_cliente','nombre_cliente','id_mantenimiento',
                                'detalle_mantenimiento','fecha_vencimiento','estado_actividad','fecha_realizacion','observaciones'],
                    'desc'  => 'Vencimientos de mantenimientos por cliente [estados: sin ejecutar|ejecutado|CERRADA|CERRADA POR FIN CONTRATO]',
                ],
                [
                    'raw'   => 'tbl_mantenimientos',
                    'cols'  => ['id_mantenimiento','detalle_mantenimiento'],
                    'desc'  => 'Catálogo de tipos de mantenimiento disponibles',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Procesos Electorales' => [
                [
                    'view'  => 'procesos_electorales',
                    'table' => 'procesos_electorales',
                    'cols'  => ['id_proceso','id_cliente','nombre_cliente','tipo_comite','anio','estado',
                                'plazas_principales','plazas_suplentes','fecha_inicio_inscripcion','fecha_fin_votacion',
                                'fecha_completado','total_votantes','votos_emitidos'],
                    'desc'  => 'Procesos electorales COPASST/COCOLAB/BRIGADA/VIGIA [estados: en_curso|completado|cancelado]',
                ],
                [
                    'view'  => 'candidatos_comite',
                    'table' => 'candidatos_comite',
                    'cols'  => ['id_candidato','id_proceso','id_cliente','nombre_cliente','tipo_comite',
                                'nombres','apellidos','documento_identidad','cargo','representacion',
                                'tipo_plaza','estado','votos_obtenidos','estado_miembro'],
                    'desc'  => 'Candidatos y miembros electos de comités [estados: pendiente|aprobado|rechazado|elegido]',
                ],
                [
                    'view'  => 'vigias',
                    'table' => 'vigias',
                    'cols'  => ['id_vigia','id_cliente','nombre_cliente','nombre_vigia','cedula_vigia','periodo_texto'],
                    'desc'  => 'Vigías SST designados por cliente',
                ],
                [
                    'raw'   => 'tbl_miembros_comite',
                    'cols'  => ['id_miembro','id_proceso','nombres','apellidos','documento_identidad','tipo_plaza','estado'],
                    'desc'  => 'Miembros del proceso electoral de comités',
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            'Otros Módulos' => [
                [
                    'view'  => 'induccion_etapas',
                    'table' => 'induccion_etapas',
                    'cols'  => ['id_etapa','id_cliente','nombre_cliente','numero_etapa','nombre_etapa',
                                'temas','duracion_estimada_minutos','estado','anio','aprobado_por'],
                    'desc'  => 'Etapas del programa de inducción SST por cliente',
                ],
                [
                    'view'  => 'lookerstudio',
                    'table' => 'lookerstudio',
                    'cols'  => ['id_looker','id_cliente','nombre_cliente','tipodedashboard','enlace'],
                    'desc'  => 'Dashboards Looker Studio por cliente',
                ],
            ],


        ];
    }
}
