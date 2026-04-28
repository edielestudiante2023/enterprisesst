-- Agrega columna token_inscripcion a tbl_acta_capacitacion para el flujo
-- de auto-inscripcion de asistentes via QR.
--
-- Ejecutar en LOCAL primero, luego en PRODUCCION.

ALTER TABLE tbl_acta_capacitacion
    ADD COLUMN token_inscripcion VARCHAR(64) NULL DEFAULT NULL AFTER ruta_pdf;

ALTER TABLE tbl_acta_capacitacion
    ADD INDEX idx_token_inscripcion (token_inscripcion);
