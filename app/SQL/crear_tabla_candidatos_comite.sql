-- =====================================================
-- Sistema de Conformación de Comités SST
-- Fase 2: Tabla de Candidatos
-- =====================================================

-- Tabla para candidatos de elecciones de comités
-- Aplica a: COPASST, COCOLAB (votación)
-- También para: Vigía, Brigada (designación directa)

CREATE TABLE IF NOT EXISTS `tbl_candidatos_comite` (
    `id_candidato` INT AUTO_INCREMENT PRIMARY KEY,
    `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales',
    `id_cliente` INT NOT NULL COMMENT 'FK a tbl_cliente',

    -- Datos del candidato
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `documento_identidad` VARCHAR(20) NOT NULL,
    `tipo_documento` ENUM('CC', 'CE', 'TI', 'PA', 'PEP') DEFAULT 'CC',
    `cargo` VARCHAR(100) NOT NULL COMMENT 'Cargo en la empresa',
    `area` VARCHAR(100) DEFAULT NULL COMMENT 'Área o departamento',
    `email` VARCHAR(150) DEFAULT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,

    -- Foto del candidato (obligatoria para votación)
    `foto` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta de la foto',

    -- Tipo de candidatura
    `representacion` ENUM('trabajador', 'empleador') NOT NULL COMMENT 'A quién representa',
    `tipo_plaza` ENUM('principal', 'suplente') DEFAULT 'principal',

    -- Estado del candidato
    `estado` ENUM('inscrito', 'aprobado', 'rechazado', 'elegido', 'no_elegido', 'designado') DEFAULT 'inscrito',
    `motivo_rechazo` TEXT DEFAULT NULL,

    -- Votación (solo para COPASST y COCOLAB)
    `votos_obtenidos` INT DEFAULT 0,
    `porcentaje_votos` DECIMAL(5,2) DEFAULT 0.00,

    -- Certificación 50 horas SST (obligatorio para COPASST)
    `tiene_certificado_50h` TINYINT(1) DEFAULT 0,
    `archivo_certificado_50h` VARCHAR(255) DEFAULT NULL,
    `fecha_certificado_50h` DATE DEFAULT NULL,
    `institucion_certificado` VARCHAR(200) DEFAULT NULL,

    -- Metadatos
    `observaciones` TEXT DEFAULT NULL,
    `inscrito_por` INT DEFAULT NULL COMMENT 'ID del usuario que inscribió',
    `fecha_inscripcion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_aprobacion` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX `idx_proceso` (`id_proceso`),
    INDEX `idx_cliente` (`id_cliente`),
    INDEX `idx_documento` (`documento_identidad`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_representacion` (`representacion`),

    -- Restricción: No duplicar candidato en mismo proceso
    UNIQUE KEY `uk_candidato_proceso` (`id_proceso`, `documento_identidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Candidatos para procesos electorales de comités SST';

-- =====================================================
-- Índice para búsqueda por proceso y representación
-- =====================================================
CREATE INDEX IF NOT EXISTS `idx_proceso_representacion`
ON `tbl_candidatos_comite` (`id_proceso`, `representacion`, `estado`);

-- =====================================================
-- Script PHP para ejecutar este SQL
-- =====================================================
-- Para ejecutar desde el navegador:
-- Crea un archivo ejecutor o usa el siguiente código en el controlador

/*
PHP de ejemplo para ejecutar:

$db = \Config\Database::connect();
$sql = file_get_contents(APPPATH . 'SQL/crear_tabla_candidatos_comite.sql');
$queries = explode(';', $sql);
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query) && strpos($query, '--') !== 0 && strpos($query, '/*') !== 0) {
        $db->query($query);
    }
}
echo "Tabla tbl_candidatos_comite creada exitosamente";
*/
