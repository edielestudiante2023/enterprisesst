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
