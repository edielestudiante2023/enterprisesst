-- =====================================================
-- SQL de Actualizacion - Módulo Contexto SST
-- Fecha: 2026-01-22
-- Descripción: Agrega soporte para múltiples niveles de riesgo ARL
--              y cambia responsable SST a selector de consultores
-- =====================================================

-- 1. Agregar columna para múltiples niveles de riesgo (JSON)
ALTER TABLE tbl_cliente_contexto_sst
ADD COLUMN niveles_riesgo_arl JSON NULL
AFTER nivel_riesgo_arl;

-- 2. Migrar datos existentes: convertir nivel_riesgo_arl único a array JSON
UPDATE tbl_cliente_contexto_sst
SET niveles_riesgo_arl = JSON_ARRAY(nivel_riesgo_arl)
WHERE niveles_riesgo_arl IS NULL
  AND nivel_riesgo_arl IS NOT NULL;

-- 3. Agregar columna para consultor responsable del SG-SST
ALTER TABLE tbl_cliente_contexto_sst
ADD COLUMN id_consultor_responsable INT NULL
AFTER turnos_trabajo;

-- 4. Agregar foreign key (opcional pero recomendado)
ALTER TABLE tbl_cliente_contexto_sst
ADD CONSTRAINT fk_contexto_consultor
FOREIGN KEY (id_consultor_responsable) REFERENCES tbl_consultor(id_consultor)
ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- Verificación después de ejecutar
-- =====================================================
-- SELECT c.id_cliente, c.niveles_riesgo_arl, c.id_consultor_responsable,
--        co.nombre_consultor, co.numero_licencia
-- FROM tbl_cliente_contexto_sst c
-- LEFT JOIN tbl_consultor co ON c.id_consultor_responsable = co.id_consultor;
