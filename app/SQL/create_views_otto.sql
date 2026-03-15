-- ============================================================
-- VISTAS OTTO — GRUPO 1: Core Operacional
-- Proyecto: EnterpriseSST | Base de datos: empresas_sst
-- Generado: 2026-03-15
-- ============================================================
-- Notas de diseño:
--   • tbl_pendientes.id_acta  es varchar 'AV-123'  → NO se usa para JOIN
--   • tbl_pendientes.id_acta_visita es int(11)     → JOIN correcto con tbl_acta_visita.id
--   • tbl_pta_cliente PK = id_ptacliente (no id)
--   • tbl_indicadores_sst_mediciones no tiene id_cliente → se obtiene vía id_indicador
--   • tbl_cronog_capacitacion no tiene id_consultor
-- ============================================================


-- ─── v_pta_cliente ───────────────────────────────────────────
-- Plan de Trabajo Anual + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW `v_pta_cliente` AS
SELECT
  p.id_ptacliente,
  p.id_cliente,
  p.id_documento_origen,
  p.tipo_servicio,
  p.phva_plandetrabajo,
  p.numeral_plandetrabajo,
  p.actividad_plandetrabajo,
  p.responsable_sugerido_plandetrabajo,
  p.fecha_propuesta,
  p.fecha_cierre,
  p.responsable_definido_paralaactividad,
  p.estado_actividad,
  p.porcentaje_avance,
  p.semana,
  p.observaciones,
  p.created_at,
  p.updated_at,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_pta_cliente p
LEFT JOIN tbl_clientes c    ON c.id_cliente    = p.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = c.id_consultor
;


-- ─── v_pendientes ────────────────────────────────────────────
-- Tareas/pendientes + nombre_cliente + número de acta de visita
-- NOTA: id_acta (varchar 'AV-123') se expone como dato, NO como JOIN.
--       El JOIN real usa id_acta_visita (int) → tbl_acta_visita.id
CREATE OR REPLACE VIEW `v_pendientes` AS
SELECT
  t.id_pendientes,
  t.id_cliente,
  t.responsable,
  t.tarea_actividad,
  t.fecha_asignacion,
  t.fecha_cierre,
  t.estado,
  t.estado_avance,
  t.evidencia_para_cerrarla,
  t.conteo_dias,
  t.created_at,
  t.updated_at,
  t.id_acta_visita,
  c.nombre_cliente,
  av.fecha_visita    AS fecha_acta_visita,
  av.motivo          AS motivo_acta_visita
FROM tbl_pendientes t
LEFT JOIN tbl_clientes c    ON c.id_cliente = t.id_cliente
LEFT JOIN tbl_acta_visita av ON av.id        = t.id_acta_visita
;


-- ─── v_indicadores_sst ───────────────────────────────────────
-- Indicadores SST definidos + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW `v_indicadores_sst` AS
SELECT
  i.id_indicador,
  i.id_cliente,
  i.id_documento_origen,
  i.id_actividad_pta,
  i.nombre_indicador,
  i.definicion,
  i.interpretacion,
  i.origen_datos,
  i.cargo_responsable,
  i.cargos_conocer_resultado,
  i.tipo_indicador,
  i.categoria,
  i.formula,
  i.meta,
  i.unidad_medida,
  i.periodicidad,
  i.numeral_resolucion,
  i.es_minimo_obligatorio,
  i.peso_ponderacion,
  i.phva,
  i.valor_numerador,
  i.valor_denominador,
  i.valor_resultado,
  i.fecha_medicion,
  i.cumple_meta,
  i.observaciones,
  i.acciones_mejora,
  i.analisis_datos,
  i.requiere_plan_accion,
  i.numero_accion,
  i.activo,
  i.created_at,
  i.updated_at,
  i.created_by,
  i.updated_by,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_indicadores_sst i
LEFT JOIN tbl_clientes c    ON c.id_cliente    = i.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = c.id_consultor
;


-- ─── v_indicadores_mediciones ────────────────────────────────
-- Mediciones periódicas + nombre_indicador + nombre_cliente
-- id_cliente se obtiene vía tbl_indicadores_sst (no existe en mediciones)
CREATE OR REPLACE VIEW `v_indicadores_mediciones` AS
SELECT
  m.id_medicion,
  m.id_indicador,
  m.periodo,
  m.valor_numerador,
  m.valor_denominador,
  m.valor_resultado,
  m.cumple_meta,
  m.observaciones,
  m.fecha_registro,
  m.registrado_por,
  i.nombre_indicador,
  i.meta,
  i.unidad_medida,
  i.tipo_indicador,
  i.categoria,
  i.id_cliente,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_indicadores_sst_mediciones m
LEFT JOIN tbl_indicadores_sst i ON i.id_indicador = m.id_indicador
LEFT JOIN tbl_clientes c        ON c.id_cliente    = i.id_cliente
LEFT JOIN tbl_consultor con     ON con.id_consultor = c.id_consultor
;


-- ─── v_cronog_capacitacion ───────────────────────────────────
-- Cronograma de capacitaciones + nombre capacitación + nombre_cliente
-- tbl_cronog_capacitacion NO tiene id_consultor propio
CREATE OR REPLACE VIEW `v_cronog_capacitacion` AS
SELECT
  cr.id_cronograma_capacitacion,
  cr.id_capacitacion,
  cr.id_cliente,
  cr.fecha_programada,
  cr.fecha_de_realizacion,
  cr.estado,
  cr.perfil_de_asistentes,
  cr.nombre_del_capacitador,
  cr.horas_de_duracion_de_la_capacitacion,
  cr.indicador_de_realizacion_de_la_capacitacion,
  cr.numero_de_asistentes_a_capacitacion,
  cr.numero_total_de_personas_programadas,
  cr.porcentaje_cobertura,
  cr.numero_de_personas_evaluadas,
  cr.promedio_de_calificaciones,
  cr.observaciones,
  cap.capacitacion                AS nombre_capacitacion,
  cap.objetivo_capacitacion,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_cronog_capacitacion cr
LEFT JOIN capacitaciones_sst cap ON cap.id_capacitacion = cr.id_capacitacion
LEFT JOIN tbl_clientes c         ON c.id_cliente         = cr.id_cliente
LEFT JOIN tbl_consultor con      ON con.id_consultor      = c.id_consultor
;


-- ─── v_documentos_sst ────────────────────────────────────────
-- Documentos SST + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW `v_documentos_sst` AS
SELECT
  d.id_documento,
  d.id_cliente,
  d.tipo_documento,
  d.titulo,
  d.codigo,
  d.anio,
  d.contenido,
  d.archivo_pdf,
  d.url_externa,
  d.observaciones,
  d.version,
  d.estado,
  d.fecha_aprobacion,
  d.aprobado_por,
  d.motivo_version,
  d.tipo_cambio_pendiente,
  d.created_at,
  d.updated_at,
  d.created_by,
  d.updated_by,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_documentos_sst d
LEFT JOIN tbl_clientes c    ON c.id_cliente    = d.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = c.id_consultor
;


-- ─── v_doc_versiones_sst ─────────────────────────────────────
-- Historial de versiones de documentos + titulo del documento + nombre_cliente
CREATE OR REPLACE VIEW `v_doc_versiones_sst` AS
SELECT
  v.id_version,
  v.id_documento,
  v.id_cliente,
  v.tipo_documento,
  v.codigo,
  v.titulo,
  v.anio,
  v.version,
  v.version_texto,
  v.tipo_cambio,
  v.descripcion_cambio,
  v.contenido_snapshot,
  v.estado,
  v.autorizado_por,
  v.autorizado_por_id,
  v.fecha_autorizacion,
  v.archivo_pdf,
  v.hash_documento,
  v.created_at,
  d.titulo           AS titulo_documento_actual,
  d.tipo_documento   AS tipo_documento_actual,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_doc_versiones_sst v
LEFT JOIN tbl_documentos_sst d ON d.id_documento = v.id_documento
LEFT JOIN tbl_clientes c       ON c.id_cliente    = v.id_cliente
LEFT JOIN tbl_consultor con    ON con.id_consultor = c.id_consultor
;


-- ─── v_evaluacion_inicial ────────────────────────────────────
-- Evaluación inicial SG-SST + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW `v_evaluacion_inicial` AS
SELECT
  e.id_ev_ini,
  e.id_cliente,
  e.created_at,
  e.updated_at,
  e.ciclo,
  e.estandar,
  e.detalle_estandar,
  e.estandares_minimos,
  e.numeral,
  e.numerales_del_cliente,
  e.siete,
  e.veintiun,
  e.sesenta,
  e.item_del_estandar,
  e.evaluacion_inicial,
  e.valor,
  e.puntaje_cuantitativo,
  e.item,
  e.criterio,
  e.modo_de_verificacion,
  e.calificacion,
  e.nivel_de_evaluacion,
  e.observaciones,
  c.nombre_cliente,
  con.nombre_consultor
FROM evaluacion_inicial_sst e
LEFT JOIN tbl_clientes c    ON c.id_cliente    = e.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = c.id_consultor
;


-- ─── v_cliente_estandares ────────────────────────────────────
-- Estándares asignados a clientes + nombre del estándar + nombre_cliente
CREATE OR REPLACE VIEW `v_cliente_estandares` AS
SELECT
  ce.id,
  ce.id_cliente,
  ce.id_estandar,
  ce.estado,
  ce.calificacion,
  ce.id_documento,
  ce.fecha_cumplimiento,
  ce.evidencia_path,
  ce.observaciones,
  ce.verificado_por,
  ce.fecha_verificacion,
  ce.created_at,
  ce.updated_at,
  est.nombre          AS nombre_estandar,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_cliente_estandares ce
LEFT JOIN estandares est    ON est.id_estandar  = ce.id_estandar
LEFT JOIN tbl_clientes c    ON c.id_cliente     = ce.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = c.id_consultor
;


-- ─── v_reportes ──────────────────────────────────────────────
-- Reportes + tipo de reporte + detalle de reporte + nombre_cliente
CREATE OR REPLACE VIEW `v_reportes` AS
SELECT
  r.id_reporte,
  r.id_cliente,
  r.titulo_reporte,
  r.id_detailreport,
  r.enlace,
  r.estado,
  r.observaciones,
  r.created_at,
  r.updated_at,
  r.id_report_type,
  dr.detail_report    AS detalle_reporte,
  rt.report_type      AS tipo_reporte,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_reporte r
LEFT JOIN detail_report dr  ON dr.id_detailreport = r.id_detailreport
LEFT JOIN report_type_table rt ON rt.id_report_type = r.id_report_type
LEFT JOIN tbl_clientes c    ON c.id_cliente       = r.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor   = c.id_consultor
;
-- ============================================================
-- VISTAS OTTO — GRUPO 2: Inspecciones, Visitas, ACC, Actas
-- Proyecto: EnterpriseSST | Base de datos: empresas_sst
-- Generado: 2026-03-15
-- ============================================================

-- ─── v_inspeccion_botiquin ───────────────────────────────────
CREATE OR REPLACE VIEW `v_inspeccion_botiquin` AS
SELECT
  b.*,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_inspeccion_botiquin b
LEFT JOIN tbl_clientes c   ON c.id_cliente   = b.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = b.id_consultor
;

-- ─── v_inspeccion_extintores ─────────────────────────────────
CREATE OR REPLACE VIEW `v_inspeccion_extintores` AS
SELECT
  e.*,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_inspeccion_extintores e
LEFT JOIN tbl_clientes c   ON c.id_cliente   = e.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = e.id_consultor
;

-- ─── v_inspeccion_locativa ───────────────────────────────────
CREATE OR REPLACE VIEW `v_inspeccion_locativa` AS
SELECT
  l.*,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_inspeccion_locativa l
LEFT JOIN tbl_clientes c   ON c.id_cliente   = l.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = l.id_consultor
;

-- ─── v_inspeccion_senalizacion ───────────────────────────────
-- CRÍTICO: alias "ins" — "is" es palabra reservada en MySQL
CREATE OR REPLACE VIEW `v_inspeccion_senalizacion` AS
SELECT
  ins.*,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_inspeccion_senalizacion ins
LEFT JOIN tbl_clientes c   ON c.id_cliente   = ins.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = ins.id_consultor
;

-- ─── v_acta_visita ───────────────────────────────────────────
CREATE OR REPLACE VIEW `v_acta_visita` AS
SELECT
  av.*,
  c.nombre_cliente,
  con.nombre_consultor
FROM tbl_acta_visita av
LEFT JOIN tbl_clientes c   ON c.id_cliente   = av.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = av.id_consultor
;

-- ─── v_acc_hallazgos ─────────────────────────────────────────
CREATE OR REPLACE VIEW `v_acc_hallazgos` AS
SELECT
  h.*,
  c.nombre_cliente
FROM tbl_acc_hallazgos h
LEFT JOIN tbl_clientes c ON c.id_cliente = h.id_cliente
;

-- ─── v_acc_acciones ──────────────────────────────────────────
-- Obtiene id_cliente desde tbl_acc_hallazgos (acc_acciones no tiene id_cliente)
CREATE OR REPLACE VIEW `v_acc_acciones` AS
SELECT
  a.*,
  h.titulo          AS titulo_hallazgo,
  h.id_cliente,
  c.nombre_cliente
FROM tbl_acc_acciones a
LEFT JOIN tbl_acc_hallazgos h ON h.id_hallazgo  = a.id_hallazgo
LEFT JOIN tbl_clientes c      ON c.id_cliente   = h.id_cliente
;

-- ─── v_acc_seguimientos ──────────────────────────────────────
-- Encadena: seguimiento → accion → hallazgo → cliente
CREATE OR REPLACE VIEW `v_acc_seguimientos` AS
SELECT
  s.*,
  a.tipo_accion,
  a.id_hallazgo,
  h.id_cliente,
  c.nombre_cliente
FROM tbl_acc_seguimientos s
LEFT JOIN tbl_acc_acciones  a ON a.id_accion   = s.id_accion
LEFT JOIN tbl_acc_hallazgos h ON h.id_hallazgo = a.id_hallazgo
LEFT JOIN tbl_clientes      c ON c.id_cliente  = h.id_cliente
;

-- ─── v_actas_comite ──────────────────────────────────────────
-- actas → comites → tipos_comite + clientes
CREATE OR REPLACE VIEW `v_actas_comite` AS
SELECT
  ac.*,
  tc.nombre          AS nombre_tipo_comite,
  tc.codigo          AS codigo_tipo_comite,
  c.nombre_cliente
FROM tbl_actas ac
LEFT JOIN tbl_comites     co ON co.id_comite  = ac.id_comite
LEFT JOIN tbl_tipos_comite tc ON tc.id_tipo   = co.id_tipo
LEFT JOIN tbl_clientes    c   ON c.id_cliente = ac.id_cliente
;

-- ─── v_acta_compromisos ──────────────────────────────────────
-- compromisos → actas (numero_acta) + clientes
CREATE OR REPLACE VIEW `v_acta_compromisos` AS
SELECT
  comp.*,
  a.numero_acta,
  c.nombre_cliente
FROM tbl_acta_compromisos comp
LEFT JOIN tbl_actas    a ON a.id_acta    = comp.id_acta
LEFT JOIN tbl_clientes c ON c.id_cliente = comp.id_cliente
;

-- ─── v_comite_miembros ───────────────────────────────────────
-- miembros → comites → tipos_comite + clientes
CREATE OR REPLACE VIEW `v_comite_miembros` AS
SELECT
  m.*,
  tc.nombre          AS nombre_tipo_comite,
  tc.codigo          AS codigo_tipo_comite,
  c.nombre_cliente
FROM tbl_comite_miembros m
LEFT JOIN tbl_comites      co ON co.id_comite  = m.id_comite
LEFT JOIN tbl_tipos_comite tc ON tc.id_tipo    = co.id_tipo
LEFT JOIN tbl_clientes     c  ON c.id_cliente  = m.id_cliente
;
-- ============================================================
-- VISTAS OTTO — GRUPO 3: Contexto, Contratos, Presupuesto, Matrices, Electoral
-- Proyecto: EnterpriseSST | Base de datos: empresas_sst
-- Generado: 2026-03-15
-- ============================================================

-- ─── v_cliente_contexto ──────────────────────────────────────
-- Todos los campos del contexto SST + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW `v_cliente_contexto` AS
SELECT
    ctx.id_contexto,
    ctx.id_cliente,
    ctx.sector_economico,
    ctx.codigo_ciiu_secundario,
    ctx.nivel_riesgo_arl,
    ctx.niveles_riesgo_arl,
    ctx.clase_riesgo_cotizacion,
    ctx.arl_actual,
    ctx.total_trabajadores,
    ctx.trabajadores_directos,
    ctx.trabajadores_temporales,
    ctx.contratistas_permanentes,
    ctx.numero_sedes,
    ctx.turnos_trabajo,
    ctx.id_consultor_responsable,
    ctx.responsable_sgsst_nombre,
    ctx.responsable_sgsst_cargo,
    ctx.responsable_sgsst_cedula,
    ctx.licencia_sst_numero,
    ctx.licencia_sst_vigencia,
    ctx.tiene_copasst,
    ctx.tiene_vigia_sst,
    ctx.tiene_comite_convivencia,
    ctx.tiene_brigada_emergencias,
    ctx.requiere_delegado_sst,
    ctx.delegado_sst_nombre,
    ctx.delegado_sst_cargo,
    ctx.delegado_sst_email,
    ctx.delegado_sst_cedula,
    ctx.representante_legal_nombre,
    ctx.representante_legal_cargo,
    ctx.representante_legal_email,
    ctx.representante_legal_cedula,
    ctx.peligros_identificados,
    ctx.observaciones_contexto,
    ctx.estandares_aplicables,
    ctx.created_at,
    ctx.updated_at,
    c.nombre_cliente,
    con.nombre_consultor
FROM tbl_cliente_contexto_sst ctx
LEFT JOIN tbl_clientes c ON c.id_cliente = ctx.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = ctx.id_consultor_responsable
;

-- ─── v_responsables_sst ──────────────────────────────────────
-- Todos los responsables SST de cada cliente + nombre_cliente
CREATE OR REPLACE VIEW `v_responsables_sst` AS
SELECT
    r.id_responsable,
    r.id_cliente,
    r.tipo_rol,
    r.nombre_completo,
    r.tipo_documento,
    r.numero_documento,
    r.cargo,
    r.email,
    r.telefono,
    r.licencia_sst_numero,
    r.licencia_sst_vigencia,
    r.formacion_sst,
    r.fecha_inicio,
    r.fecha_fin,
    r.acta_nombramiento,
    r.activo,
    r.observaciones,
    r.created_at,
    r.updated_at,
    r.created_by,
    r.updated_by,
    c.nombre_cliente
FROM tbl_cliente_responsables_sst r
LEFT JOIN tbl_clientes c ON c.id_cliente = r.id_cliente
;

-- ─── v_contratos ─────────────────────────────────────────────
-- Todos los campos del contrato (incluyendo firmas) + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW `v_contratos` AS
SELECT
    ct.id_contrato,
    ct.id_cliente,
    ct.numero_contrato,
    ct.fecha_inicio,
    ct.fecha_fin,
    ct.valor_contrato,
    ct.valor_mensual,
    ct.numero_cuotas,
    ct.frecuencia_visitas,
    ct.tipo_contrato,
    ct.estado,
    ct.observaciones,
    ct.clausula_cuarta_duracion,
    ct.clausula_primera_objeto,
    ct.nombre_rep_legal_cliente,
    ct.cedula_rep_legal_cliente,
    ct.direccion_cliente,
    ct.telefono_cliente,
    ct.email_cliente,
    ct.nombre_rep_legal_contratista,
    ct.cedula_rep_legal_contratista,
    ct.email_contratista,
    ct.id_consultor_responsable,
    ct.nombre_responsable_sgsst,
    ct.cedula_responsable_sgsst,
    ct.licencia_responsable_sgsst,
    ct.email_responsable_sgsst,
    ct.banco,
    ct.tipo_cuenta,
    ct.cuenta_bancaria,
    ct.contrato_generado,
    ct.fecha_generacion_contrato,
    ct.ruta_pdf_contrato,
    ct.contrato_enviado,
    ct.fecha_envio_contrato,
    ct.email_envio_contrato,
    ct.created_at,
    ct.updated_at,
    ct.token_firma,
    ct.token_firma_expiracion,
    ct.estado_firma,
    ct.firma_cliente_nombre,
    ct.firma_cliente_cedula,
    ct.firma_cliente_imagen,
    ct.firma_cliente_ip,
    ct.firma_cliente_fecha,
    c.nombre_cliente,
    con.nombre_consultor
FROM tbl_contratos ct
LEFT JOIN tbl_clientes c ON c.id_cliente = ct.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = ct.id_consultor_responsable
;

-- ─── v_presupuesto ───────────────────────────────────────────
-- Cabecera del presupuesto anual SST + nombre_cliente
-- (sin JOIN a items/detalle — usar v_presupuesto_detalle para el desglose)
CREATE OR REPLACE VIEW `v_presupuesto` AS
SELECT
    ps.id_presupuesto,
    ps.id_cliente,
    ps.anio,
    ps.mes_inicio,
    ps.estado,
    ps.firmado_por,
    ps.fecha_aprobacion,
    ps.observaciones,
    ps.created_at,
    ps.updated_at,
    ps.token_firma,
    ps.token_expiracion,
    ps.cedula_firmante,
    ps.firma_imagen,
    ps.ip_firma,
    ps.token_consulta,
    c.nombre_cliente
FROM tbl_presupuesto_sst ps
LEFT JOIN tbl_clientes c ON c.id_cliente = ps.id_cliente
;

-- ─── v_presupuesto_detalle ───────────────────────────────────
-- Detalle mes a mes de ejecución presupuestal
-- NOTA: tbl_presupuesto_sst.anio y tbl_presupuesto_detalle.anio son distintos;
--       se usa alias anio_presupuesto para el anio del encabezado del presupuesto
CREATE OR REPLACE VIEW `v_presupuesto_detalle` AS
SELECT
    pd.id_detalle,
    pd.id_item,
    pd.mes,
    pd.anio,
    pd.presupuestado,
    pd.ejecutado,
    pd.notas,
    pd.updated_at,
    -- Datos del item
    pi.id_presupuesto,
    pi.id_categoria,
    pi.codigo_item,
    pi.actividad      AS nombre_item,
    pi.descripcion    AS descripcion_item,
    pi.orden          AS orden_item,
    pi.activo         AS item_activo,
    -- Datos de categoría
    pc.codigo         AS codigo_categoria,
    pc.nombre         AS nombre_categoria,
    pc.orden          AS orden_categoria,
    -- Datos del encabezado del presupuesto (anio del presupuesto con alias para evitar ambigüedad)
    ps.id_cliente,
    ps.anio           AS anio_presupuesto,
    ps.mes_inicio,
    ps.estado         AS estado_presupuesto,
    -- Cliente
    c.nombre_cliente
FROM tbl_presupuesto_detalle pd
LEFT JOIN tbl_presupuesto_items pi   ON pi.id_item        = pd.id_item
LEFT JOIN tbl_presupuesto_categorias pc ON pc.id_categoria = pi.id_categoria
LEFT JOIN tbl_presupuesto_sst ps     ON ps.id_presupuesto = pi.id_presupuesto
LEFT JOIN tbl_clientes c             ON c.id_cliente      = ps.id_cliente
;

-- ─── v_matrices ──────────────────────────────────────────────
-- Matrices SST (peligros, legal, etc.) + nombre_cliente
CREATE OR REPLACE VIEW `v_matrices` AS
SELECT
    m.id_matriz,
    m.tipo,
    m.descripcion,
    m.observaciones,
    m.enlace,
    m.id_cliente,
    m.created_at,
    m.updated_at,
    c.nombre_cliente
FROM tbl_matrices m
LEFT JOIN tbl_clientes c ON c.id_cliente = m.id_cliente
;

-- ─── v_vencimientos_mantenimientos ───────────────────────────
-- Vencimientos de mantenimientos + detalle_mantenimiento + nombre_cliente + nombre_consultor
-- PK: id_vencimientos_mmttos
-- ENUM estado_actividad: 'sin ejecutar','ejecutado','CERRADA','CERRADA POR FIN CONTRATO'
CREATE OR REPLACE VIEW `v_vencimientos_mantenimientos` AS
SELECT
    vm.id_vencimientos_mmttos,
    vm.id_mantenimiento,
    vm.id_cliente,
    vm.id_consultor,
    vm.fecha_vencimiento,
    vm.estado_actividad,
    vm.fecha_realizacion,
    vm.observaciones,
    -- Catálogo de mantenimientos (solo tiene id + detalle)
    mnt.detalle_mantenimiento,
    -- Cliente y consultor
    c.nombre_cliente,
    con.nombre_consultor
FROM tbl_vencimientos_mantenimientos vm
LEFT JOIN tbl_mantenimientos mnt ON mnt.id_mantenimiento = vm.id_mantenimiento
LEFT JOIN tbl_clientes c         ON c.id_cliente         = vm.id_cliente
LEFT JOIN tbl_consultor con      ON con.id_consultor     = vm.id_consultor
;

-- ─── v_vigias ────────────────────────────────────────────────
-- Vigías SST + nombre_cliente
CREATE OR REPLACE VIEW `v_vigias` AS
SELECT
    v.id_vigia,
    v.nombre_vigia,
    v.cedula_vigia,
    v.periodo_texto,
    v.firma_vigia,
    v.observaciones,
    v.id_cliente,
    v.created_at,
    v.updated_at,
    c.nombre_cliente
FROM tbl_vigias v
LEFT JOIN tbl_clientes c ON c.id_cliente = v.id_cliente
;

-- ─── v_procesos_electorales ──────────────────────────────────
-- Procesos electorales COPASST/COCOLAB/BRIGADA/VIGIA + nombre_cliente
CREATE OR REPLACE VIEW `v_procesos_electorales` AS
SELECT
    pe.id_proceso,
    pe.id_cliente,
    pe.id_comite,
    pe.tipo_comite,
    pe.anio,
    pe.estado,
    pe.plazas_principales,
    pe.plazas_suplentes,
    pe.fecha_inicio_inscripcion,
    pe.fecha_fin_inscripcion,
    pe.fecha_inicio_votacion,
    pe.fecha_fin_votacion,
    pe.fecha_escrutinio,
    pe.fecha_completado,
    pe.observaciones,
    pe.token_votacion,
    pe.fecha_inicio_periodo,
    pe.fecha_fin_periodo,
    pe.id_consultor,
    pe.created_at,
    pe.updated_at,
    pe.enlace_votacion,
    pe.total_votantes,
    pe.votos_emitidos,
    c.nombre_cliente
FROM tbl_procesos_electorales pe
LEFT JOIN tbl_clientes c ON c.id_cliente = pe.id_cliente
;

-- ─── v_candidatos_comite ─────────────────────────────────────
-- Candidatos con tipo_comite traído de tbl_procesos_electorales
CREATE OR REPLACE VIEW `v_candidatos_comite` AS
SELECT
    cc.id_candidato,
    cc.id_proceso,
    cc.id_cliente,
    cc.nombres,
    cc.apellidos,
    cc.documento_identidad,
    cc.tipo_documento,
    cc.cargo,
    cc.area,
    cc.email,
    cc.telefono,
    cc.foto,
    cc.representacion,
    cc.tipo_plaza,
    cc.estado,
    cc.motivo_rechazo,
    cc.votos_obtenidos,
    cc.porcentaje_votos,
    cc.tiene_certificado_50h,
    cc.archivo_certificado_50h,
    cc.fecha_certificado_50h,
    cc.institucion_certificado,
    cc.observaciones,
    cc.inscrito_por,
    cc.fecha_inscripcion,
    cc.fecha_aprobacion,
    cc.created_at,
    cc.updated_at,
    cc.estado_miembro,
    cc.fecha_ingreso_comite,
    cc.fecha_retiro_comite,
    cc.es_recomposicion,
    cc.id_recomposicion_ingreso,
    cc.posicion_votacion,
    -- Datos del proceso electoral
    pe.tipo_comite,
    pe.anio         AS anio_proceso,
    pe.estado       AS estado_proceso,
    pe.fecha_inicio_periodo,
    pe.fecha_fin_periodo,
    -- Cliente
    c.nombre_cliente
FROM tbl_candidatos_comite cc
LEFT JOIN tbl_procesos_electorales pe ON pe.id_proceso  = cc.id_proceso
LEFT JOIN tbl_clientes c              ON c.id_cliente   = cc.id_cliente
;

-- ─── v_induccion_etapas ──────────────────────────────────────
-- Etapas del programa de inducción + nombre_cliente
CREATE OR REPLACE VIEW `v_induccion_etapas` AS
SELECT
    ie.id_etapa,
    ie.id_cliente,
    ie.numero_etapa,
    ie.nombre_etapa,
    ie.descripcion_etapa,
    ie.temas,
    ie.duracion_estimada_minutos,
    ie.responsable_sugerido,
    ie.recursos_requeridos,
    ie.es_personalizado,
    ie.anio,
    ie.estado,
    ie.fecha_aprobacion,
    ie.aprobado_por,
    ie.created_at,
    ie.updated_at,
    c.nombre_cliente
FROM tbl_induccion_etapas ie
LEFT JOIN tbl_clientes c ON c.id_cliente = ie.id_cliente
;

-- ─── v_lookerstudio ──────────────────────────────────────────
-- Dashboards Looker Studio por cliente + nombre_cliente
CREATE OR REPLACE VIEW `v_lookerstudio` AS
SELECT
    l.id_looker,
    l.tipodedashboard,
    l.enlace,
    l.id_cliente,
    l.created_at,
    l.updated_at,
    c.nombre_cliente
FROM tbl_lookerstudio l
LEFT JOIN tbl_clientes c ON c.id_cliente = l.id_cliente
;
