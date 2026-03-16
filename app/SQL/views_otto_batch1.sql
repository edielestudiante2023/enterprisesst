-- ============================================================
-- BATCH 1: Core operacional — PTA, Pendientes, Indicadores,
--          Capacitaciones, Documentos, Estándares
-- Generado: 2026-03-16
-- BD: empresas_sst
-- ============================================================


-- ─── v_pta_cliente ───────────────────────────────────────────
-- Tablas: tbl_pta_cliente (principal) + tbl_clientes + tbl_consultor
-- JOIN: tbl_pta_cliente.id_cliente → tbl_clientes.id_cliente
--       tbl_clientes.id_consultor  → tbl_consultor.id_consultor
CREATE OR REPLACE VIEW v_pta_cliente AS
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
    c.nit_cliente,
    c.ciudad_cliente,
    c.estado                 AS estado_cliente,
    con.nombre_consultor
FROM tbl_pta_cliente p
LEFT JOIN tbl_clientes  c   ON c.id_cliente   = p.id_cliente
LEFT JOIN tbl_consultor con ON con.id_consultor = c.id_consultor;


-- ─── v_pendientes ────────────────────────────────────────────
-- Tablas: tbl_pendientes (principal) + tbl_clientes + tbl_acta_visita
-- JOIN clave: tbl_pendientes.id_acta_visita → tbl_acta_visita.id
--   (NO usar id_acta que es varchar 'AV-123'; id_acta_visita es int FK)
CREATE OR REPLACE VIEW v_pendientes AS
SELECT
    p.id_pendientes,
    p.id_cliente,
    p.responsable,
    p.tarea_actividad,
    p.fecha_asignacion,
    p.fecha_cierre,
    p.estado,
    p.estado_avance,
    p.evidencia_para_cerrarla,
    p.conteo_dias,
    p.created_at,
    p.updated_at,
    p.id_acta_visita,
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente,
    av.fecha_visita          AS acta_fecha_visita,
    av.motivo                AS acta_motivo,
    av.modalidad             AS acta_modalidad,
    av.estado                AS acta_estado
FROM tbl_pendientes p
LEFT JOIN tbl_clientes   c  ON c.id_cliente  = p.id_cliente
LEFT JOIN tbl_acta_visita av ON av.id         = p.id_acta_visita;


-- ─── v_indicadores_sst ───────────────────────────────────────
-- Tablas: tbl_indicadores_sst (principal) + tbl_clientes
CREATE OR REPLACE VIEW v_indicadores_sst AS
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
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_indicadores_sst i
LEFT JOIN tbl_clientes c ON c.id_cliente = i.id_cliente;


-- ─── v_indicadores_mediciones ────────────────────────────────
-- Tablas: tbl_indicadores_sst_mediciones (principal)
--         + tbl_indicadores_sst (nombre_indicador, meta, unidad_medida)
--         + tbl_clientes (via tbl_indicadores_sst.id_cliente)
-- Nota: tbl_indicadores_sst_mediciones NO tiene id_cliente directo
--       → se obtiene a través del indicador padre
CREATE OR REPLACE VIEW v_indicadores_mediciones AS
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
    i.tipo_indicador,
    i.categoria,
    i.meta                   AS indicador_meta,
    i.unidad_medida          AS indicador_unidad_medida,
    i.periodicidad           AS indicador_periodicidad,
    i.cargo_responsable      AS indicador_cargo_responsable,
    i.id_cliente,
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_indicadores_sst_mediciones m
LEFT JOIN tbl_indicadores_sst i ON i.id_indicador = m.id_indicador
LEFT JOIN tbl_clientes        c ON c.id_cliente   = i.id_cliente;


-- ─── v_cronog_capacitacion ───────────────────────────────────
-- Tablas: tbl_cronog_capacitacion (principal)
--         + capacitaciones_sst (nombre y objetivo)
--         + tbl_clientes
CREATE OR REPLACE VIEW v_cronog_capacitacion AS
SELECT
    cc.id_cronograma_capacitacion,
    cc.id_capacitacion,
    cc.id_cliente,
    cc.fecha_programada,
    cc.fecha_de_realizacion,
    cc.estado,
    cc.perfil_de_asistentes,
    cc.nombre_del_capacitador,
    cc.horas_de_duracion_de_la_capacitacion,
    cc.indicador_de_realizacion_de_la_capacitacion,
    cc.numero_de_asistentes_a_capacitacion,
    cc.numero_total_de_personas_programadas,
    cc.porcentaje_cobertura,
    cc.numero_de_personas_evaluadas,
    cc.promedio_de_calificaciones,
    cc.observaciones,
    cap.capacitacion         AS nombre_capacitacion,
    cap.objetivo_capacitacion,
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_cronog_capacitacion cc
LEFT JOIN capacitaciones_sst cap ON cap.id_capacitacion = cc.id_capacitacion
LEFT JOIN tbl_clientes       c   ON c.id_cliente        = cc.id_cliente;


-- ─── v_evaluacion_inicial ────────────────────────────────────
-- Tablas: evaluacion_inicial_sst (principal) + tbl_clientes
-- Nota: columna `numeral` en esta tabla es varchar(50), NO la misma
--       que numeral_resolucion de indicadores. No hay conflicto.
CREATE OR REPLACE VIEW v_evaluacion_inicial AS
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
    c.nit_cliente,
    c.ciudad_cliente
FROM evaluacion_inicial_sst e
LEFT JOIN tbl_clientes c ON c.id_cliente = e.id_cliente;


-- ─── v_cliente_estandares ────────────────────────────────────
-- Tablas: tbl_cliente_estandares (principal)
--         + estandares (nombre del estándar)
--         + tbl_clientes
-- Nota: estandares solo tiene dos columnas: id_estandar, nombre
CREATE OR REPLACE VIEW v_cliente_estandares AS
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
    est.nombre               AS nombre_estandar,
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_cliente_estandares ce
LEFT JOIN estandares    est ON est.id_estandar = ce.id_estandar
LEFT JOIN tbl_clientes  c   ON c.id_cliente    = ce.id_cliente;


-- ─── v_reportes ──────────────────────────────────────────────
-- Tablas: tbl_reporte (principal)
--         + detail_report  (descripción del detalle)
--         + report_type_table (tipo de reporte)
--         + tbl_clientes
CREATE OR REPLACE VIEW v_reportes AS
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
    dr.detail_report         AS descripcion_detalle,
    rt.report_type           AS tipo_reporte,
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_reporte r
LEFT JOIN detail_report      dr ON dr.id_detailreport = r.id_detailreport
LEFT JOIN report_type_table  rt ON rt.id_report_type  = r.id_report_type
LEFT JOIN tbl_clientes       c  ON c.id_cliente        = r.id_cliente;


-- ─── v_documentos_sst ────────────────────────────────────────
-- Tablas: tbl_documentos_sst (principal) + tbl_clientes
-- Nota: columna `anio` existe solo en tbl_documentos_sst, sin conflicto
CREATE OR REPLACE VIEW v_documentos_sst AS
SELECT
    d.id_documento,
    d.id_cliente,
    d.tipo_documento,
    d.titulo,
    d.codigo,
    d.anio,
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
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_documentos_sst d
LEFT JOIN tbl_clientes c ON c.id_cliente = d.id_cliente;


-- ─── v_doc_versiones_sst ─────────────────────────────────────
-- Tablas: tbl_doc_versiones_sst (principal) + tbl_clientes
-- Nota: tbl_doc_versiones_sst tiene id_cliente directo → JOIN simple
--       columna `anio` solo en esta tabla, sin conflicto
CREATE OR REPLACE VIEW v_doc_versiones_sst AS
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
    v.estado,
    v.autorizado_por,
    v.autorizado_por_id,
    v.fecha_autorizacion,
    v.archivo_pdf,
    v.hash_documento,
    v.created_at,
    c.nombre_cliente,
    c.nit_cliente,
    c.ciudad_cliente
FROM tbl_doc_versiones_sst v
LEFT JOIN tbl_clientes c ON c.id_cliente = v.id_cliente;


-- ============================================================
-- DECISIONES Y ALIAS APLICADOS
-- ============================================================
--
-- JOINS y FKs resueltos:
--   tbl_pta_cliente.id_cliente          → tbl_clientes.id_cliente
--   tbl_clientes.id_consultor           → tbl_consultor.id_consultor
--   tbl_pendientes.id_acta_visita (int) → tbl_acta_visita.id
--     (id_acta de tbl_pendientes NO existe; la FK correcta es id_acta_visita)
--   tbl_indicadores_sst_mediciones NO tiene id_cliente → se obtiene
--     via tbl_indicadores_sst.id_cliente (doble LEFT JOIN)
--   tbl_cronog_capacitacion.id_capacitacion → capacitaciones_sst.id_capacitacion
--   tbl_cliente_estandares.id_estandar  → estandares.id_estandar
--   tbl_reporte.id_detailreport         → detail_report.id_detailreport
--   tbl_reporte.id_report_type          → report_type_table.id_report_type
--   tbl_doc_versiones_sst.id_cliente    → tbl_clientes.id_cliente (directo)
--
-- Alias aplicados para evitar columnas duplicadas:
--   v_pta_cliente:
--     tbl_clientes.estado              → estado_cliente
--   v_pendientes:
--     tbl_acta_visita.fecha_visita     → acta_fecha_visita
--     tbl_acta_visita.motivo           → acta_motivo
--     tbl_acta_visita.modalidad        → acta_modalidad
--     tbl_acta_visita.estado           → acta_estado
--   v_indicadores_mediciones:
--     tbl_indicadores_sst.meta         → indicador_meta
--     tbl_indicadores_sst.unidad_medida→ indicador_unidad_medida
--     tbl_indicadores_sst.periodicidad → indicador_periodicidad
--     tbl_indicadores_sst.cargo_responsable → indicador_cargo_responsable
--   v_cronog_capacitacion:
--     capacitaciones_sst.capacitacion  → nombre_capacitacion
--
-- Columnas omitidas (datos sensibles / no útiles para Otto):
--   tbl_clientes: password, logo, firma_representante_legal,
--     rut_archivo, camara_comercio_archivo, cedula_rep_legal_archivo,
--     oferta_comercial_archivo, estandares (blob texto)
--   tbl_consultor: password, foto_consultor, firma_consultor
--   tbl_documentos_sst/tbl_doc_versiones_sst: contenido_snapshot / contenido
--     (longtext pesado — se consulta directamente en tbl_* si se necesita)
--   tbl_acta_visita: firma_*, soporte_*, ruta_pdf (binarios/rutas de archivo)
--
-- Regla de solo lectura:
--   Todas las vistas v_* son SELECT únicamente.
--   Para INSERT/UPDATE usar siempre la tbl_* correspondiente.
-- ============================================================
