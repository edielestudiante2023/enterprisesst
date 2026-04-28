<?php
/**
 * Migration: agrega columna token_inscripcion a tbl_acta_capacitacion para
 * habilitar flujo de auto-inscripcion via QR.
 *
 * Uso:
 *   php cli_run_sql.php app/SQL/agregar_token_inscripcion_acta_capacitacion.php
 *   o ejecutar el SQL directamente desde el cliente MySQL:
 *
 *   ALTER TABLE tbl_acta_capacitacion
 *     ADD COLUMN token_inscripcion VARCHAR(64) NULL DEFAULT NULL AFTER ruta_pdf;
 *   ALTER TABLE tbl_acta_capacitacion
 *     ADD INDEX idx_token_inscripcion (token_inscripcion);
 */

$db = \Config\Database::connect();

// Detectar si la columna ya existe (idempotente)
$existe = $db->query("
    SELECT COUNT(*) AS cnt
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_acta_capacitacion'
      AND COLUMN_NAME = 'token_inscripcion'
")->getRow()->cnt;

if ($existe > 0) {
    echo "[SKIP] tbl_acta_capacitacion.token_inscripcion ya existe\n";
} else {
    try {
        $db->query("ALTER TABLE tbl_acta_capacitacion
                    ADD COLUMN token_inscripcion VARCHAR(64) NULL DEFAULT NULL AFTER ruta_pdf");
        echo "[OK] Columna token_inscripcion agregada\n";
    } catch (\Exception $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
    }
}

// Index (idempotente)
$indexExiste = $db->query("
    SELECT COUNT(*) AS cnt
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_acta_capacitacion'
      AND INDEX_NAME = 'idx_token_inscripcion'
")->getRow()->cnt;

if ($indexExiste > 0) {
    echo "[SKIP] idx_token_inscripcion ya existe\n";
} else {
    try {
        $db->query("ALTER TABLE tbl_acta_capacitacion ADD INDEX idx_token_inscripcion (token_inscripcion)");
        echo "[OK] Index idx_token_inscripcion agregado\n";
    } catch (\Exception $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
    }
}
