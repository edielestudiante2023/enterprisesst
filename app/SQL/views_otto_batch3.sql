-- ============================================================
-- BATCH 3: Contexto, Responsables, Contratos, Presupuesto,
--          Matrices, Vencimientos, Vigías, Electoral,
--          Inducción, Looker Studio
-- Generado: 2026-03-16
-- Proyecto: Enterprise SST
-- ============================================================

-- ─── v_cliente_contexto ──────────────────────────────────────
-- tbl_cliente_contexto_sst (principal)
--   + nombre_cliente  de tbl_clientes
--   + nombre_consultor de tbl_consultor (via id_consultor_responsable)
-- Excluidos: niveles_riesgo_arl (longtext), peligros_identificados (text largo),
--            observaciones_contexto (text largo)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_cliente_contexto AS
SELECT
    ctx.id_contexto,
    ctx.id_cliente,
    c.nombre_cliente,
    ctx.id_consultor_responsable,
    con.nombre_consultor,
    ctx.sector_economico,
    ctx.codigo_ciiu_secundario,
    ctx.nivel_riesgo_arl,
    ctx.clase_riesgo_cotizacion,
    ctx.arl_actual,
    ctx.total_trabajadores,
    ctx.trabajadores_directos,
    ctx.trabajadores_temporales,
    ctx.contratistas_permanentes,
    ctx.numero_sedes,
    ctx.turnos_trabajo,
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
    ctx.estandares_aplicables,
    ctx.created_at,
    ctx.updated_at
FROM tbl_cliente_contexto_sst ctx
LEFT JOIN tbl_clientes c       ON c.id_cliente  = ctx.id_cliente
LEFT JOIN tbl_consultor con    ON con.id_consultor = ctx.id_consultor_responsable;


-- ─── v_responsables_sst ──────────────────────────────────────
-- tbl_cliente_responsables_sst (principal)
--   + nombre_cliente de tbl_clientes
-- Excluidos: observaciones (text largo)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_responsables_sst AS
SELECT
    r.id_responsable,
    r.id_cliente,
    c.nombre_cliente,
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
    r.created_at,
    r.updated_at,
    r.created_by,
    r.updated_by
FROM tbl_cliente_responsables_sst r
LEFT JOIN tbl_clientes c ON c.id_cliente = r.id_cliente;


-- ─── v_contratos ─────────────────────────────────────────────
-- tbl_contratos (principal)
--   + nombre_cliente   de tbl_clientes
--   + nombre_consultor de tbl_consultor (via id_consultor_responsable)
-- Excluidos: clausula_cuarta_duracion (longtext), clausula_primera_objeto (text)
--            observaciones (text), firma_cliente_imagen, ruta_pdf_contrato
--            token_firma, token_firma_expiracion (datos internos)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_contratos AS
SELECT
    ct.id_contrato,
    ct.id_cliente,
    c.nombre_cliente,
    ct.id_consultor_responsable,
    con.nombre_consultor,
    ct.numero_contrato,
    ct.fecha_inicio,
    ct.fecha_fin,
    ct.valor_contrato,
    ct.valor_mensual,
    ct.numero_cuotas,
    ct.frecuencia_visitas,
    ct.tipo_contrato,
    ct.estado,
    ct.nombre_rep_legal_cliente,
    ct.cedula_rep_legal_cliente,
    ct.direccion_cliente,
    ct.telefono_cliente,
    ct.email_cliente,
    ct.nombre_rep_legal_contratista,
    ct.cedula_rep_legal_contratista,
    ct.email_contratista,
    ct.nombre_responsable_sgsst,
    ct.cedula_responsable_sgsst,
    ct.licencia_responsable_sgsst,
    ct.email_responsable_sgsst,
    ct.banco,
    ct.tipo_cuenta,
    ct.cuenta_bancaria,
    ct.contrato_generado,
    ct.fecha_generacion_contrato,
    ct.contrato_enviado,
    ct.fecha_envio_contrato,
    ct.email_envio_contrato,
    ct.estado_firma,
    ct.firma_cliente_nombre,
    ct.firma_cliente_cedula,
    ct.firma_cliente_ip,
    ct.firma_cliente_fecha,
    ct.created_at,
    ct.updated_at
FROM tbl_contratos ct
LEFT JOIN tbl_clientes c       ON c.id_cliente   = ct.id_cliente
LEFT JOIN tbl_consultor con    ON con.id_consultor = ct.id_consultor_responsable;


-- ─── v_presupuesto ───────────────────────────────────────────
-- tbl_presupuesto_sst (principal)
--   + nombre_cliente de tbl_clientes
-- Excluidos: observaciones (text), token_firma, token_expiracion,
--            firma_imagen, token_consulta (datos internos de firma)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_presupuesto AS
SELECT
    p.id_presupuesto,
    p.id_cliente,
    c.nombre_cliente,
    p.anio,
    p.mes_inicio,
    p.estado,
    p.firmado_por,
    p.cedula_firmante,
    p.fecha_aprobacion,
    p.ip_firma,
    p.created_at,
    p.updated_at
FROM tbl_presupuesto_sst p
LEFT JOIN tbl_clientes c ON c.id_cliente = p.id_cliente;


-- ─── v_presupuesto_detalle ───────────────────────────────────
-- tbl_presupuesto_detalle (principal)
--   JOIN tbl_presupuesto_items    (via id_item)
--   JOIN tbl_presupuesto_categorias (via id_categoria de items)
--   JOIN tbl_presupuesto_sst      (via id_presupuesto de items)
--   JOIN tbl_clientes             (via id_cliente de presupuesto_sst)
--
-- ALIAS para columnas duplicadas:
--   tbl_presupuesto_items.orden     → item_orden
--   tbl_presupuesto_items.activo    → item_activo
--   tbl_presupuesto_items.created_at → item_created_at
--   tbl_presupuesto_items.updated_at → item_updated_at
--   tbl_presupuesto_categorias.orden → categoria_orden
--   tbl_presupuesto_categorias.activo → categoria_activo
--   tbl_presupuesto_sst.anio        → presupuesto_anio  (vs detalle.anio)
--   tbl_presupuesto_sst.estado      → presupuesto_estado
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_presupuesto_detalle AS
SELECT
    d.id_detalle,
    d.id_item,
    d.mes,
    d.anio,
    d.presupuestado,
    d.ejecutado,
    d.notas,
    d.updated_at,
    -- item
    i.id_presupuesto,
    i.id_categoria,
    i.codigo_item,
    i.actividad,
    i.descripcion       AS item_descripcion,
    i.orden             AS item_orden,
    i.activo            AS item_activo,
    i.created_at        AS item_created_at,
    i.updated_at        AS item_updated_at,
    -- categoría
    cat.codigo          AS categoria_codigo,
    cat.nombre          AS categoria_nombre,
    cat.orden           AS categoria_orden,
    cat.activo          AS categoria_activo,
    -- presupuesto cabecera
    ps.id_cliente,
    ps.anio             AS presupuesto_anio,
    ps.estado           AS presupuesto_estado,
    ps.firmado_por,
    -- cliente
    c.nombre_cliente
FROM tbl_presupuesto_detalle d
LEFT JOIN tbl_presupuesto_items      i   ON i.id_item        = d.id_item
LEFT JOIN tbl_presupuesto_categorias cat ON cat.id_categoria = i.id_categoria
LEFT JOIN tbl_presupuesto_sst        ps  ON ps.id_presupuesto = i.id_presupuesto
LEFT JOIN tbl_clientes               c   ON c.id_cliente     = ps.id_cliente;


-- ─── v_matrices ──────────────────────────────────────────────
-- tbl_matrices (principal)
--   + nombre_cliente de tbl_clientes
-- Excluidos: enlace (text — puede ser URL muy largo; incluido porque es dato clave)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_matrices AS
SELECT
    m.id_matriz,
    m.id_cliente,
    c.nombre_cliente,
    m.tipo,
    m.descripcion,
    m.observaciones,
    m.enlace,
    m.created_at,
    m.updated_at
FROM tbl_matrices m
LEFT JOIN tbl_clientes c ON c.id_cliente = m.id_cliente;


-- ─── v_vencimientos_mantenimientos ───────────────────────────
-- tbl_vencimientos_mantenimientos (principal)
--   + detalle_mantenimiento de tbl_mantenimientos (via id_mantenimiento)
--   + nombre_cliente        de tbl_clientes        (via id_cliente)
--   + nombre_consultor      de tbl_consultor        (via id_consultor)
-- Excluidos: observaciones (text)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_vencimientos_mantenimientos AS
SELECT
    v.id_vencimientos_mmttos,
    v.id_mantenimiento,
    man.detalle_mantenimiento,
    v.id_cliente,
    c.nombre_cliente,
    v.id_consultor,
    con.nombre_consultor,
    v.fecha_vencimiento,
    v.estado_actividad,
    v.fecha_realizacion
FROM tbl_vencimientos_mantenimientos v
LEFT JOIN tbl_mantenimientos man ON man.id_mantenimiento = v.id_mantenimiento
LEFT JOIN tbl_clientes c         ON c.id_cliente         = v.id_cliente
LEFT JOIN tbl_consultor con      ON con.id_consultor     = v.id_consultor;


-- ─── v_vigias ────────────────────────────────────────────────
-- tbl_vigias (principal)
--   + nombre_cliente de tbl_clientes
-- Excluidos: firma_vigia (text — imagen/blob), observaciones (text)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_vigias AS
SELECT
    v.id_vigia,
    v.id_cliente,
    c.nombre_cliente,
    v.nombre_vigia,
    v.cedula_vigia,
    v.periodo_texto,
    v.created_at,
    v.updated_at
FROM tbl_vigias v
LEFT JOIN tbl_clientes c ON c.id_cliente = v.id_cliente;


-- ─── v_procesos_electorales ──────────────────────────────────
-- tbl_procesos_electorales (principal)
--   + nombre_cliente   de tbl_clientes (via id_cliente)
--   + nombre tipo comité de tbl_tipos_comite (via id_comite)
-- Excluidos: observaciones (text), token_votacion (dato interno),
--            enlace_votacion (incluido — dato útil)
-- ALIAS para columnas duplicadas:
--   tbl_tipos_comite.nombre → nombre_tipo_comite
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_procesos_electorales AS
SELECT
    pe.id_proceso,
    pe.id_cliente,
    c.nombre_cliente,
    pe.id_comite,
    tc.nombre             AS nombre_tipo_comite,
    tc.codigo             AS codigo_tipo_comite,
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
    pe.fecha_inicio_periodo,
    pe.fecha_fin_periodo,
    pe.id_consultor,
    pe.total_votantes,
    pe.votos_emitidos,
    pe.enlace_votacion,
    pe.created_at,
    pe.updated_at
FROM tbl_procesos_electorales pe
LEFT JOIN tbl_clientes    c  ON c.id_cliente = pe.id_cliente
LEFT JOIN tbl_tipos_comite tc ON tc.id_tipo  = pe.id_comite;


-- ─── v_candidatos_comite ─────────────────────────────────────
-- tbl_candidatos_comite (principal)
--   + nombre_cliente  de tbl_clientes           (via id_cliente)
--   + estado_proceso  de tbl_procesos_electorales (via id_proceso)
-- Excluidos: motivo_rechazo (text), observaciones (text),
--            foto (varchar ruta), archivo_certificado_50h (varchar ruta)
-- ALIAS para columnas duplicadas:
--   tbl_procesos_electorales.estado → estado_proceso
--   tbl_procesos_electorales.anio   → anio_proceso
--   tbl_procesos_electorales.tipo_comite → tipo_comite_proceso
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_candidatos_comite AS
SELECT
    cc.id_candidato,
    cc.id_proceso,
    pe.estado             AS estado_proceso,
    pe.anio               AS anio_proceso,
    pe.tipo_comite        AS tipo_comite_proceso,
    cc.id_cliente,
    c.nombre_cliente,
    cc.nombres,
    cc.apellidos,
    cc.documento_identidad,
    cc.tipo_documento,
    cc.cargo,
    cc.area,
    cc.email,
    cc.telefono,
    cc.representacion,
    cc.tipo_plaza,
    cc.estado,
    cc.votos_obtenidos,
    cc.porcentaje_votos,
    cc.tiene_certificado_50h,
    cc.fecha_certificado_50h,
    cc.institucion_certificado,
    cc.inscrito_por,
    cc.fecha_inscripcion,
    cc.fecha_aprobacion,
    cc.estado_miembro,
    cc.fecha_ingreso_comite,
    cc.fecha_retiro_comite,
    cc.es_recomposicion,
    cc.posicion_votacion,
    cc.created_at,
    cc.updated_at
FROM tbl_candidatos_comite cc
LEFT JOIN tbl_clientes            c  ON c.id_cliente  = cc.id_cliente
LEFT JOIN tbl_procesos_electorales pe ON pe.id_proceso = cc.id_proceso;


-- ─── v_induccion_etapas ──────────────────────────────────────
-- tbl_induccion_etapas (principal)
--   + nombre_cliente de tbl_clientes
-- Excluidos: descripcion_etapa (text), temas (longtext),
--            recursos_requeridos (text)
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_induccion_etapas AS
SELECT
    e.id_etapa,
    e.id_cliente,
    c.nombre_cliente,
    e.numero_etapa,
    e.nombre_etapa,
    e.duracion_estimada_minutos,
    e.responsable_sugerido,
    e.es_personalizado,
    e.anio,
    e.estado,
    e.fecha_aprobacion,
    e.aprobado_por,
    e.created_at,
    e.updated_at
FROM tbl_induccion_etapas e
LEFT JOIN tbl_clientes c ON c.id_cliente = e.id_cliente;


-- ─── v_lookerstudio ──────────────────────────────────────────
-- tbl_lookerstudio (principal)
--   + nombre_cliente de tbl_clientes
-- Nota: enlace es text pero es el dato principal de la tabla — incluido
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_lookerstudio AS
SELECT
    l.id_looker,
    l.id_cliente,
    c.nombre_cliente,
    l.tipodedashboard,
    l.enlace,
    l.created_at,
    l.updated_at
FROM tbl_lookerstudio l
LEFT JOIN tbl_clientes c ON c.id_cliente = l.id_cliente;


-- ============================================================
-- DECISIONES DE ALIAS Y GOTCHAS — BATCH 3
-- ============================================================
--
-- 1. v_presupuesto_detalle — más joins de este batch (4 tablas):
--    • tbl_presupuesto_detalle.anio          → columna raíz (sin alias)
--    • tbl_presupuesto_sst.anio              → alias presupuesto_anio
--    • tbl_presupuesto_items.orden           → alias item_orden
--    • tbl_presupuesto_items.activo          → alias item_activo
--    • tbl_presupuesto_items.created_at      → alias item_created_at
--    • tbl_presupuesto_items.updated_at      → alias item_updated_at
--    • tbl_presupuesto_categorias.orden      → alias categoria_orden
--    • tbl_presupuesto_categorias.activo     → alias categoria_activo
--    • tbl_presupuesto_sst.estado            → alias presupuesto_estado
--    • tbl_presupuesto_items.descripcion     → alias item_descripcion
--
-- 2. v_procesos_electorales:
--    • tbl_tipos_comite.nombre               → alias nombre_tipo_comite
--    • tbl_tipos_comite.codigo               → alias codigo_tipo_comite
--    (no hay id duplicado porque el FK se llama id_comite, no id_tipo)
--
-- 3. v_candidatos_comite:
--    • tbl_procesos_electorales.estado       → alias estado_proceso
--    • tbl_procesos_electorales.anio         → alias anio_proceso
--    • tbl_procesos_electorales.tipo_comite  → alias tipo_comite_proceso
--    (tbl_candidatos_comite ya tiene su propio campo estado, anio no existe
--     pero tipo_comite coincide con el enum del proceso)
--
-- 4. Columnas EXCLUIDAS (texto largo / datos sensibles internos):
--    • tbl_cliente_contexto_sst.niveles_riesgo_arl  (longtext)
--    • tbl_cliente_contexto_sst.peligros_identificados (text)
--    • tbl_cliente_contexto_sst.observaciones_contexto (text)
--    • tbl_contratos.clausula_cuarta_duracion        (longtext)
--    • tbl_contratos.clausula_primera_objeto         (text)
--    • tbl_contratos.observaciones, ruta_pdf_contrato, token_*, firma_cliente_imagen
--    • tbl_presupuesto_sst.observaciones, token_*, firma_imagen, token_consulta
--    • tbl_vigias.firma_vigia                        (text — dato de imagen)
--    • tbl_vigias.observaciones                      (text)
--    • tbl_procesos_electorales.observaciones, token_votacion
--    • tbl_candidatos_comite.motivo_rechazo, observaciones, foto, archivo_certificado_50h
--    • tbl_induccion_etapas.descripcion_etapa, temas (longtext), recursos_requeridos
--
-- 5. tbl_consultor tiene columna id_cliente (su propio FK de cliente asignado),
--    distinto del id_cliente de la tabla principal — no genera ambigüedad
--    porque los JOINs siempre referencian con alias de tabla.
--
-- 6. tbl_mantenimientos es tabla catálogo simple (id + detalle_mantenimiento).
--    tbl_vencimientos_mantenimientos tiene id_consultor (no id_consultor_responsable).
--
-- ============================================================
