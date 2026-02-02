-- =====================================================
-- TABLAS PARA MÓDULO DE PRESUPUESTO SST (1.1.3)
-- Asignación de recursos para el SG-SST
-- =====================================================

-- Tabla de categorías maestras (catálogo)
CREATE TABLE IF NOT EXISTS tbl_presupuesto_categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla principal de presupuesto por cliente/año
CREATE TABLE IF NOT EXISTS tbl_presupuesto_sst (
    id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    anio INT NOT NULL,
    mes_inicio INT DEFAULT 1 COMMENT '1=Enero, 2=Febrero, etc.',
    estado ENUM('borrador', 'aprobado', 'cerrado') DEFAULT 'borrador',
    firmado_por VARCHAR(200) NULL,
    fecha_aprobacion DATETIME NULL,
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cliente_anio (id_cliente, anio),
    INDEX idx_cliente (id_cliente),
    INDEX idx_anio (anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de ítems del presupuesto
CREATE TABLE IF NOT EXISTS tbl_presupuesto_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_presupuesto INT NOT NULL,
    id_categoria INT NOT NULL,
    codigo_item VARCHAR(10) NOT NULL COMMENT 'Ej: 1.1, 3.2, 4.1',
    actividad VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_presupuesto) REFERENCES tbl_presupuesto_sst(id_presupuesto) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES tbl_presupuesto_categorias(id_categoria),
    INDEX idx_presupuesto (id_presupuesto),
    INDEX idx_categoria (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalle mensual (presupuestado vs ejecutado)
CREATE TABLE IF NOT EXISTS tbl_presupuesto_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_item INT NOT NULL,
    mes INT NOT NULL COMMENT '1-12 para meses del año',
    anio INT NOT NULL,
    presupuestado DECIMAL(15,2) DEFAULT 0.00,
    ejecutado DECIMAL(15,2) DEFAULT 0.00,
    notas VARCHAR(255) NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_item) REFERENCES tbl_presupuesto_items(id_item) ON DELETE CASCADE,
    UNIQUE KEY uk_item_mes_anio (id_item, mes, anio),
    INDEX idx_item (id_item),
    INDEX idx_mes_anio (mes, anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES - CATEGORÍAS MAESTRAS
-- =====================================================
INSERT INTO tbl_presupuesto_categorias (codigo, nombre, orden) VALUES
('1', 'Talento Humano de SST', 1),
('2', 'Capacitación y Formación', 2),
('3', 'Salud en el Trabajo', 3),
('4', 'Seguridad Industrial', 4),
('5', 'Medio Ambiente y Saneamiento Básico', 5),
('6', 'Otros Gastos SST', 6)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), orden = VALUES(orden);

-- =====================================================
-- PLANTILLA DE DOCUMENTO PARA CARPETA SST
-- =====================================================
INSERT INTO tbl_plantillas_documentos_sst
(id_estandar, codigo, nombre_documento, descripcion, tipo_documento, patron, tiene_plantilla, campos_requeridos, orden)
VALUES
(3, 'FT-SST-004', 'Asignación de recursos para el SG-SST',
'Presupuesto anual de recursos económicos, técnicos, humanos y de otra índole requeridos para el SG-SST',
'formato', 'B', 1,
'{"campos": ["anio", "categorias", "items", "montos_mensuales"]}',
4)
ON DUPLICATE KEY UPDATE
nombre_documento = VALUES(nombre_documento),
descripcion = VALUES(descripcion);
