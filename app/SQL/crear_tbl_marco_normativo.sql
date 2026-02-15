-- =============================================
-- Tabla: tbl_marco_normativo
-- Módulo: Insumos IA - Pregeneración
-- Fecha: 2026-02-14
-- Propósito: Almacenar marco normativo vigente por tipo de documento
--            para inyectar como contexto en la generación IA
-- =============================================

CREATE TABLE IF NOT EXISTS tbl_marco_normativo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(100) NOT NULL COMMENT 'snake_case: politica_sst_general, programa_capacitacion, etc.',
    marco_normativo_texto TEXT NOT NULL COMMENT 'Contenido del marco normativo vigente',
    fecha_actualizacion DATETIME NOT NULL COMMENT 'Última actualización del marco normativo',
    actualizado_por VARCHAR(100) DEFAULT 'sistema' COMMENT 'sistema | consultor | nombre_usuario',
    metodo_actualizacion VARCHAR(20) DEFAULT 'manual' COMMENT 'automatico | boton | confirmacion | manual',
    vigencia_dias INT DEFAULT 90 COMMENT 'Días de vigencia antes de considerar desactualizado',
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_tipo_documento (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
