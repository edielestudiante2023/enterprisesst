-- =====================================================
-- TABLA PARA MÓDULO DE RESPONSABLES SST
-- Resolución 0312/2019
-- Ejecutar en LOCAL y PRODUCCIÓN
-- =====================================================

CREATE TABLE IF NOT EXISTS `tbl_cliente_responsables_sst` (
    `id_responsable` INT(11) NOT NULL AUTO_INCREMENT,
    `id_cliente` INT(11) NOT NULL,
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
    `nombre_completo` VARCHAR(255) NOT NULL,
    `tipo_documento` ENUM('CC', 'CE', 'PA', 'TI', 'NIT') DEFAULT 'CC',
    `numero_documento` VARCHAR(20) NOT NULL,
    `cargo` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NULL,
    `telefono` VARCHAR(20) NULL,
    `licencia_sst_numero` VARCHAR(50) NULL,
    `licencia_sst_vigencia` DATE NULL,
    `formacion_sst` VARCHAR(255) NULL,
    `fecha_inicio` DATE NULL,
    `fecha_fin` DATE NULL,
    `acta_nombramiento` VARCHAR(255) NULL,
    `activo` TINYINT(1) DEFAULT 1,
    `observaciones` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    PRIMARY KEY (`id_responsable`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_tipo_rol` (`tipo_rol`),
    KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
