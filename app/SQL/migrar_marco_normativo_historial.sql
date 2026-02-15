-- Migración: Habilitar historial completo del marco normativo
-- Fecha: 2026-02-14
-- Descripción: Quitar constraint UNIQUE para permitir múltiples versiones por tipo_documento
-- La columna 'activo' marca la versión vigente (1 = actual, 0 = histórica)

-- Quitar el constraint UNIQUE para permitir múltiples versiones (si existe)
ALTER TABLE tbl_marco_normativo DROP INDEX IF EXISTS idx_tipo_documento;

-- Agregar índice compuesto para consultas eficientes (tipo + activo) (si no existe)
ALTER TABLE tbl_marco_normativo ADD INDEX IF NOT EXISTS idx_tipo_activo (tipo_documento, activo, fecha_actualizacion DESC);

-- Comentarios de documentación
ALTER TABLE tbl_marco_normativo
    MODIFY COLUMN activo TINYINT(1) DEFAULT 1 COMMENT '1 = versión vigente, 0 = histórica';
