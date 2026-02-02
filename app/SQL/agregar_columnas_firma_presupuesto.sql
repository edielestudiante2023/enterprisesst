-- =====================================================
-- Agregar columnas para firma digital del presupuesto
-- Ejecutar en LOCAL y PRODUCTION
-- =====================================================

-- Agregar columnas para token de firma
ALTER TABLE tbl_presupuesto_sst
ADD COLUMN IF NOT EXISTS token_firma VARCHAR(64) NULL AFTER firmado_por,
ADD COLUMN IF NOT EXISTS token_expiracion DATETIME NULL AFTER token_firma;

-- Agregar columnas para datos de firma
ALTER TABLE tbl_presupuesto_sst
ADD COLUMN IF NOT EXISTS cedula_firmante VARCHAR(20) NULL AFTER token_expiracion,
ADD COLUMN IF NOT EXISTS firma_imagen VARCHAR(255) NULL AFTER cedula_firmante,
ADD COLUMN IF NOT EXISTS ip_firma VARCHAR(45) NULL AFTER firma_imagen;

-- Agregar estado 'pendiente_firma' si no existe en ENUM
-- Nota: Si la columna estado es VARCHAR, esto no es necesario
-- Si es ENUM, se debe modificar:
-- ALTER TABLE tbl_presupuesto_sst
-- MODIFY COLUMN estado ENUM('borrador', 'pendiente_firma', 'aprobado', 'cerrado') DEFAULT 'borrador';

-- NOTA: Las columnas representante_legal_email y delegado_sst_email
-- ya existen en tbl_cliente_contexto_sst (ver ClienteContextoSstModel.php)

-- Indice para busqueda por token
CREATE INDEX IF NOT EXISTS idx_presupuesto_token ON tbl_presupuesto_sst(token_firma);
