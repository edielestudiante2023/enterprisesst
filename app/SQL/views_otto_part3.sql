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
