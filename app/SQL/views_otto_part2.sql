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
