-- ============================================================
-- TABLA: tbl_cliente_responsables_sst
-- Módulo para gestionar responsables del SG-SST por cliente
-- ============================================================

CREATE TABLE IF NOT EXISTS `tbl_cliente_responsables_sst` (
    `id_responsable` INT(11) NOT NULL AUTO_INCREMENT,
    `id_cliente` INT(11) NOT NULL,

    -- Tipo de rol en el SG-SST
    `tipo_rol` ENUM(
        'representante_legal',
        'responsable_sgsst',
        'vigia_sst',
        'vigia_sst_suplente',
        'copasst_presidente',
        'copasst_secretario',
        'copasst_representante_empleador',
        'copasst_representante_trabajadores',
        'copasst_suplente_empleador',
        'copasst_suplente_trabajadores',
        'comite_convivencia_presidente',
        'comite_convivencia_secretario',
        'comite_convivencia_miembro',
        'brigada_coordinador',
        'brigada_lider_evacuacion',
        'brigada_lider_primeros_auxilios',
        'brigada_lider_control_incendios',
        'otro'
    ) NOT NULL,

    -- Datos personales
    `nombre_completo` VARCHAR(255) NOT NULL,
    `tipo_documento` ENUM('CC', 'CE', 'PA', 'TI', 'NIT') DEFAULT 'CC',
    `numero_documento` VARCHAR(20) NOT NULL,
    `cargo` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NULL,
    `telefono` VARCHAR(20) NULL,

    -- Datos específicos para responsable SG-SST
    `licencia_sst_numero` VARCHAR(50) NULL COMMENT 'Solo para responsable_sgsst si aplica',
    `licencia_sst_vigencia` DATE NULL,
    `formacion_sst` VARCHAR(255) NULL COMMENT 'Técnico, Tecnólogo, Profesional, Curso 50h',

    -- Periodo de vigencia del rol
    `fecha_inicio` DATE NULL COMMENT 'Fecha de inicio en el rol',
    `fecha_fin` DATE NULL COMMENT 'Fecha fin (para roles con periodo)',
    `acta_nombramiento` VARCHAR(255) NULL COMMENT 'Referencia al acta de nombramiento',

    -- Estado
    `activo` TINYINT(1) DEFAULT 1,
    `observaciones` TEXT NULL,

    -- Auditoría
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,

    PRIMARY KEY (`id_responsable`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_tipo_rol` (`tipo_rol`),
    KEY `idx_activo` (`activo`),

    -- Un cliente no puede tener dos personas con el mismo rol activo (excepto miembros de comités)
    UNIQUE KEY `uk_cliente_rol_documento` (`id_cliente`, `tipo_rol`, `numero_documento`),

    CONSTRAINT `fk_responsable_cliente`
        FOREIGN KEY (`id_cliente`)
        REFERENCES `tbl_clientes` (`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Responsables del SG-SST por cliente';

-- ============================================================
-- Índices adicionales para consultas frecuentes
-- ============================================================
CREATE INDEX `idx_responsable_nombre` ON `tbl_cliente_responsables_sst` (`nombre_completo`);
CREATE INDEX `idx_responsable_vigencia` ON `tbl_cliente_responsables_sst` (`fecha_inicio`, `fecha_fin`);

-- ============================================================
-- Vista para obtener responsables activos con descripción del rol
-- ============================================================
CREATE OR REPLACE VIEW `vw_responsables_sst_activos` AS
SELECT
    r.*,
    c.nombre_cliente,
    c.nit_cliente,
    CASE r.tipo_rol
        WHEN 'representante_legal' THEN 'Representante Legal'
        WHEN 'responsable_sgsst' THEN 'Responsable del SG-SST'
        WHEN 'vigia_sst' THEN 'Vigía de SST'
        WHEN 'vigia_sst_suplente' THEN 'Vigía de SST (Suplente)'
        WHEN 'copasst_presidente' THEN 'COPASST - Presidente'
        WHEN 'copasst_secretario' THEN 'COPASST - Secretario'
        WHEN 'copasst_representante_empleador' THEN 'COPASST - Representante Empleador'
        WHEN 'copasst_representante_trabajadores' THEN 'COPASST - Representante Trabajadores'
        WHEN 'copasst_suplente_empleador' THEN 'COPASST - Suplente Empleador'
        WHEN 'copasst_suplente_trabajadores' THEN 'COPASST - Suplente Trabajadores'
        WHEN 'comite_convivencia_presidente' THEN 'Comité Convivencia - Presidente'
        WHEN 'comite_convivencia_secretario' THEN 'Comité Convivencia - Secretario'
        WHEN 'comite_convivencia_miembro' THEN 'Comité Convivencia - Miembro'
        WHEN 'brigada_coordinador' THEN 'Brigada - Coordinador'
        WHEN 'brigada_lider_evacuacion' THEN 'Brigada - Líder Evacuación'
        WHEN 'brigada_lider_primeros_auxilios' THEN 'Brigada - Líder Primeros Auxilios'
        WHEN 'brigada_lider_control_incendios' THEN 'Brigada - Líder Control Incendios'
        ELSE 'Otro'
    END AS nombre_rol
FROM `tbl_cliente_responsables_sst` r
INNER JOIN `tbl_clientes` c ON r.id_cliente = c.id_cliente
WHERE r.activo = 1;
