<?php
/**
 * Crea la tabla tbl_agente_chat_log para auditoría del agente virtual
 *
 * USO: php app/SQL/crear_tabla_agente_chat.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$db = new mysqli('localhost', 'root', '', 'empresas_sst');
if ($db->connect_error) {
    die("Error de conexión: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

$sqls = [
    "CREATE TABLE IF NOT EXISTS `tbl_agente_chat_log` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `id_usuario` INT UNSIGNED NOT NULL,
        `rol_usuario` VARCHAR(20) NOT NULL,
        `sesion_chat` VARCHAR(64) NOT NULL COMMENT 'UUID de la sesión de chat',
        `mensaje_usuario` TEXT NOT NULL,
        `sql_generado` TEXT NULL COMMENT 'SQL que se ejecutó (si aplica)',
        `tipo_operacion` ENUM('SELECT','INSERT','UPDATE','DELETE','NONE') DEFAULT 'NONE',
        `tablas_afectadas` VARCHAR(500) NULL,
        `filas_afectadas` INT UNSIGNED DEFAULT 0,
        `respuesta_agente` TEXT NULL,
        `tokens_usados` INT UNSIGNED DEFAULT 0,
        `estado` ENUM('ok','error','rechazado','pendiente_confirmacion') DEFAULT 'ok',
        `ip_address` VARCHAR(45) NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario (`id_usuario`),
        INDEX idx_sesion (`sesion_chat`),
        INDEX idx_created (`created_at`),
        INDEX idx_tipo_op (`tipo_operacion`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    COMMENT='Log de auditoría del agente virtual de chat'"
];

foreach ($sqls as $sql) {
    if ($db->query($sql)) {
        echo "OK: tabla creada correctamente\n";
    } else {
        echo "ERROR: " . $db->error . "\n";
    }
}

$db->close();
echo "Script finalizado.\n";
