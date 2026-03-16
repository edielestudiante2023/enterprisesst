-- ============================================================
-- BATCH 2: Inspecciones, Visitas, Acciones Correctivas, Actas
-- Generado: 2026-03-16
-- BD: empresas_sst
-- ============================================================


-- ─── v_inspeccion_botiquin ───────────────────────────────────
-- tbl_inspeccion_botiquin + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW v_inspeccion_botiquin AS
SELECT
    ib.id,
    ib.id_cliente,
    cl.nombre_cliente,
    ib.id_consultor,
    co.nombre_consultor,
    ib.fecha_inspeccion,
    ib.ubicacion_botiquin,
    ib.instalado_pared,
    ib.libre_obstaculos,
    ib.lugar_visible,
    ib.con_senalizacion,
    ib.tipo_botiquin,
    ib.estado_botiquin,
    ib.estado_collares,
    ib.estado_inmovilizadores,
    ib.obs_tabla_espinal,
    ib.recomendaciones,
    ib.pendientes_generados,
    ib.estado,
    ib.ruta_pdf,
    ib.id_documento_sst,
    ib.created_at,
    ib.updated_at
FROM tbl_inspeccion_botiquin ib
LEFT JOIN tbl_clientes      cl ON cl.id_cliente    = ib.id_cliente
LEFT JOIN tbl_consultor     co ON co.id_consultor  = ib.id_consultor;


-- ─── v_inspeccion_extintores ─────────────────────────────────
-- tbl_inspeccion_extintores + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW v_inspeccion_extintores AS
SELECT
    ie.id,
    ie.id_cliente,
    cl.nombre_cliente,
    ie.id_consultor,
    co.nombre_consultor,
    ie.fecha_inspeccion,
    ie.fecha_vencimiento_global,
    ie.numero_extintores_totales,
    ie.cantidad_abc,
    ie.cantidad_co2,
    ie.cantidad_solkaflam,
    ie.cantidad_agua,
    ie.capacidad_libras,
    ie.cantidad_unidades_residenciales,
    ie.cantidad_porteria,
    ie.cantidad_oficina_admin,
    ie.cantidad_shut_basuras,
    ie.cantidad_salones_comunales,
    ie.cantidad_cuarto_bombas,
    ie.cantidad_planta_electrica,
    ie.recomendaciones_generales,
    ie.estado,
    ie.ruta_pdf,
    ie.id_documento_sst,
    ie.created_at,
    ie.updated_at
FROM tbl_inspeccion_extintores ie
LEFT JOIN tbl_clientes cl ON cl.id_cliente   = ie.id_cliente
LEFT JOIN tbl_consultor co ON co.id_consultor = ie.id_consultor;


-- ─── v_inspeccion_locativa ───────────────────────────────────
-- tbl_inspeccion_locativa + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW v_inspeccion_locativa AS
SELECT
    il.id,
    il.id_cliente,
    cl.nombre_cliente,
    il.id_consultor,
    co.nombre_consultor,
    il.fecha_inspeccion,
    il.observaciones,
    il.estado,
    il.ruta_pdf,
    il.id_documento_sst,
    il.created_at,
    il.updated_at
FROM tbl_inspeccion_locativa il
LEFT JOIN tbl_clientes  cl ON cl.id_cliente   = il.id_cliente
LEFT JOIN tbl_consultor co ON co.id_consultor = il.id_consultor;


-- ─── v_inspeccion_senalizacion ───────────────────────────────
-- tbl_inspeccion_senalizacion + nombre_cliente + nombre_consultor
-- ALIAS de tabla = ins  (NO is — is es palabra reservada en MySQL)
CREATE OR REPLACE VIEW v_inspeccion_senalizacion AS
SELECT
    ins.id,
    ins.id_cliente,
    cl.nombre_cliente,
    ins.id_consultor,
    co.nombre_consultor,
    ins.fecha_inspeccion,
    ins.calificacion,
    ins.descripcion_cualitativa,
    ins.conteo_no_aplica,
    ins.conteo_no_cumple,
    ins.conteo_parcial,
    ins.conteo_total,
    ins.observaciones,
    ins.estado,
    ins.ruta_pdf,
    ins.created_at,
    ins.updated_at
FROM tbl_inspeccion_senalizacion ins
LEFT JOIN tbl_clientes  cl ON cl.id_cliente   = ins.id_cliente
LEFT JOIN tbl_consultor co ON co.id_consultor = ins.id_consultor;


-- ─── v_acta_visita ───────────────────────────────────────────
-- tbl_acta_visita + nombre_cliente + nombre_consultor
CREATE OR REPLACE VIEW v_acta_visita AS
SELECT
    av.id,
    av.id_cliente,
    cl.nombre_cliente,
    av.id_consultor,
    co.nombre_consultor,
    av.fecha_visita,
    av.hora_visita,
    av.ubicacion_gps,
    av.motivo,
    av.modalidad,
    av.cartera,
    av.observaciones,
    av.proxima_reunion_fecha,
    av.proxima_reunion_hora,
    av.estado,
    av.agenda_id,
    av.ruta_pdf,
    av.id_documento_sst,
    av.created_at,
    av.updated_at
FROM tbl_acta_visita av
LEFT JOIN tbl_clientes  cl ON cl.id_cliente   = av.id_cliente
LEFT JOIN tbl_consultor co ON co.id_consultor = av.id_consultor;


-- ─── v_acc_hallazgos ─────────────────────────────────────────
-- tbl_acc_hallazgos + nombre_cliente
CREATE OR REPLACE VIEW v_acc_hallazgos AS
SELECT
    h.id_hallazgo,
    h.id_cliente,
    cl.nombre_cliente,
    h.tipo_origen,
    h.numeral_asociado,
    h.titulo,
    h.descripcion,
    h.area_proceso,
    h.severidad,
    h.fecha_deteccion,
    h.fecha_limite_accion,
    h.estado,
    h.evidencia_inicial,
    h.created_at,
    h.updated_at
FROM tbl_acc_hallazgos h
LEFT JOIN tbl_clientes cl ON cl.id_cliente = h.id_cliente;


-- ─── v_acc_acciones ──────────────────────────────────────────
-- tbl_acc_acciones + titulo/descripcion hallazgo + nombre_cliente
-- Nota: ambas tablas tienen columna "estado" → alias estado_hallazgo
CREATE OR REPLACE VIEW v_acc_acciones AS
SELECT
    ac.id_accion,
    ac.id_hallazgo,
    h.titulo                    AS titulo_hallazgo,
    h.descripcion               AS descripcion_hallazgo,
    h.severidad                 AS severidad_hallazgo,
    h.estado                    AS estado_hallazgo,
    h.id_cliente,
    cl.nombre_cliente,
    ac.tipo_accion,
    ac.clasificacion_temporal,
    ac.descripcion_accion,
    ac.causa_raiz_identificada,
    ac.responsable_nombre,
    ac.fecha_asignacion,
    ac.fecha_compromiso,
    ac.fecha_cierre_real,
    ac.recursos_requeridos,
    ac.costo_estimado,
    ac.estado,
    ac.notas,
    ac.created_at,
    ac.updated_at
FROM tbl_acc_acciones ac
LEFT JOIN tbl_acc_hallazgos h  ON h.id_hallazgo = ac.id_hallazgo
LEFT JOIN tbl_clientes      cl ON cl.id_cliente  = h.id_cliente;


-- ─── v_acc_seguimientos ──────────────────────────────────────
-- tbl_acc_seguimientos + tipo_accion/estado de accion + titulo hallazgo + nombre_cliente
CREATE OR REPLACE VIEW v_acc_seguimientos AS
SELECT
    sg.id_seguimiento,
    sg.id_accion,
    ac.tipo_accion,
    ac.estado                   AS estado_accion,
    ac.id_hallazgo,
    h.titulo                    AS titulo_hallazgo,
    h.id_cliente,
    cl.nombre_cliente,
    sg.tipo_seguimiento,
    sg.descripcion,
    sg.porcentaje_avance,
    sg.nombre_archivo,
    sg.tipo_archivo,
    sg.registrado_por_nombre,
    sg.created_at
FROM tbl_acc_seguimientos sg
LEFT JOIN tbl_acc_acciones  ac ON ac.id_accion   = sg.id_accion
LEFT JOIN tbl_acc_hallazgos h  ON h.id_hallazgo  = ac.id_hallazgo
LEFT JOIN tbl_clientes      cl ON cl.id_cliente   = h.id_cliente;


-- ─── v_actas_comite ──────────────────────────────────────────
-- tbl_actas + nombre_cliente + nombre del comité (tbl_comites) + tipo_comite (tbl_tipos_comite)
-- Columnas con posible colisión:
--   tbl_actas.anio          (year)
--   tbl_actas.estado        vs tbl_comites.estado → alias estado_comite
--   tbl_tipos_comite.nombre → alias nombre_tipo_comite
CREATE OR REPLACE VIEW v_actas_comite AS
SELECT
    a.id_acta,
    a.id_cliente,
    cl.nombre_cliente,
    a.id_comite,
    tc.nombre                   AS nombre_tipo_comite,
    tc.codigo                   AS codigo_tipo_comite,
    cm.estado                   AS estado_comite,
    a.numero_acta,
    a.codigo_documento,
    a.version_documento,
    a.consecutivo_anual,
    a.anio,
    a.tipo_acta,
    a.fecha_reunion,
    a.hora_inicio,
    a.hora_fin,
    a.lugar,
    a.modalidad,
    a.quorum_requerido,
    a.quorum_presente,
    a.hay_quorum,
    a.conclusiones,
    a.observaciones,
    a.proxima_reunion_fecha,
    a.estado,
    a.total_firmantes,
    a.firmantes_completados,
    a.fecha_cierre,
    a.created_at,
    a.updated_at
FROM tbl_actas a
LEFT JOIN tbl_clientes      cl ON cl.id_cliente = a.id_cliente
LEFT JOIN tbl_comites       cm ON cm.id_comite  = a.id_comite
LEFT JOIN tbl_tipos_comite  tc ON tc.id_tipo    = cm.id_tipo;


-- ─── v_acta_compromisos ──────────────────────────────────────
-- tbl_acta_compromisos + nombre_cliente + numero_acta de tbl_actas
-- Columnas con posible colisión:
--   tbl_acta_compromisos.estado  vs tbl_actas implícito → no hay colisión directa
--   tbl_acta_compromisos.id_cliente ya está en la tabla principal (no hace falta alias)
CREATE OR REPLACE VIEW v_acta_compromisos AS
SELECT
    ac.id_compromiso,
    ac.id_acta,
    a.numero_acta,
    a.fecha_reunion             AS fecha_reunion_acta,
    a.tipo_acta,
    ac.id_cliente,
    cl.nombre_cliente,
    ac.id_comite,
    ac.numero_compromiso,
    ac.descripcion,
    ac.punto_orden_del_dia,
    ac.responsable_nombre,
    ac.responsable_email,
    ac.fecha_compromiso,
    ac.fecha_vencimiento,
    ac.fecha_cierre_efectiva,
    ac.estado,
    ac.porcentaje_avance,
    ac.prioridad,
    ac.evidencia_descripcion,
    ac.created_at,
    ac.updated_at
FROM tbl_acta_compromisos ac
LEFT JOIN tbl_clientes cl ON cl.id_cliente = ac.id_cliente
LEFT JOIN tbl_actas    a  ON a.id_acta     = ac.id_acta;


-- ─── v_comite_miembros ───────────────────────────────────────
-- tbl_comite_miembros + nombre_cliente + tipo_comite via tbl_comites → tbl_tipos_comite
-- Columnas con posible colisión:
--   tbl_comite_miembros.estado  vs tbl_comites.estado → alias estado_comite
--   tbl_tipos_comite.nombre     → alias nombre_tipo_comite
CREATE OR REPLACE VIEW v_comite_miembros AS
SELECT
    m.id_miembro,
    m.id_comite,
    tc.nombre                   AS nombre_tipo_comite,
    tc.codigo                   AS codigo_tipo_comite,
    cm.estado                   AS estado_comite,
    cm.fecha_conformacion,
    cm.fecha_vencimiento        AS fecha_vencimiento_comite,
    m.id_cliente,
    cl.nombre_cliente,
    m.nombre_completo,
    m.tipo_documento,
    m.numero_documento,
    m.cargo,
    m.area_dependencia,
    m.email,
    m.telefono,
    m.representacion,
    m.tipo_miembro,
    m.rol_comite,
    m.puede_crear_actas,
    m.puede_cerrar_actas,
    m.fecha_ingreso,
    m.fecha_retiro,
    m.estado,
    m.created_at,
    m.updated_at
FROM tbl_comite_miembros m
LEFT JOIN tbl_clientes     cl ON cl.id_cliente = m.id_cliente
LEFT JOIN tbl_comites      cm ON cm.id_comite  = m.id_comite
LEFT JOIN tbl_tipos_comite tc ON tc.id_tipo    = cm.id_tipo;


-- ============================================================
-- DECISIONES DE ALIAS APLICADAS
-- ============================================================
--
-- 1. tbl_inspeccion_senalizacion → alias de tabla = ins
--    (is es palabra reservada en MySQL — causaría syntax error)
--
-- 2. v_acc_acciones:
--    tbl_acc_hallazgos.estado → alias estado_hallazgo
--    (evita duplicate column con tbl_acc_acciones.estado)
--    tbl_acc_hallazgos.descripcion → alias descripcion_hallazgo
--    tbl_acc_hallazgos.titulo → alias titulo_hallazgo
--    tbl_acc_hallazgos.severidad → alias severidad_hallazgo
--
-- 3. v_acc_seguimientos:
--    tbl_acc_acciones.estado → alias estado_accion
--    tbl_acc_hallazgos.titulo → alias titulo_hallazgo
--
-- 4. v_actas_comite:
--    tbl_comites.estado → alias estado_comite
--    tbl_tipos_comite.nombre → alias nombre_tipo_comite
--    tbl_tipos_comite.codigo → alias codigo_tipo_comite
--
-- 5. v_acta_compromisos:
--    tbl_actas.fecha_reunion → alias fecha_reunion_acta
--    tbl_actas.tipo_acta incluida para contexto del acta
--
-- 6. v_comite_miembros:
--    tbl_comites.estado → alias estado_comite
--    tbl_comites.fecha_vencimiento → alias fecha_vencimiento_comite
--    tbl_tipos_comite.nombre → alias nombre_tipo_comite
--    tbl_tipos_comite.codigo → alias codigo_tipo_comite
--
-- 7. Todas las vistas usan LEFT JOIN para no perder filas
--    cuando el id_consultor o id_comite puede ser NULL
--
-- 8. Columnas de fotos/firmas/blobs excluidas para mantener
--    vistas ágiles (<50 columnas) y útiles para Otto
-- ============================================================
