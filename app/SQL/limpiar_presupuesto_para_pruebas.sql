-- ============================================================================
-- SCRIPT SQL: Limpiar datos de presupuesto SST para pruebas desde cero
-- Fecha: 2026-01-30
-- Descripcion: Elimina todos los datos de presupuesto del cliente de prueba
--              para permitir probar el flujo completo nuevamente
-- ============================================================================

-- IMPORTANTE: Ajustar el id_cliente segun sea necesario
-- SET @id_cliente_prueba = 1; -- Cambiar por el ID del cliente a limpiar

-- ============================================================================
-- OPCION 1: Limpiar presupuesto de UN cliente especifico
-- ============================================================================

-- Eliminar detalles mensuales del presupuesto
DELETE pd FROM tbl_presupuesto_detalle pd
INNER JOIN tbl_presupuesto_items pi ON pd.id_item = pi.id_item
INNER JOIN tbl_presupuesto_sst ps ON pi.id_presupuesto = ps.id_presupuesto
WHERE ps.id_cliente = 1;  -- Cambiar por el ID del cliente

-- Eliminar items del presupuesto
DELETE pi FROM tbl_presupuesto_items pi
INNER JOIN tbl_presupuesto_sst ps ON pi.id_presupuesto = ps.id_presupuesto
WHERE ps.id_cliente = 1;  -- Cambiar por el ID del cliente

-- Eliminar presupuestos del cliente
DELETE FROM tbl_presupuesto_sst WHERE id_cliente = 1;  -- Cambiar por el ID del cliente

-- ============================================================================
-- OPCION 2: Limpiar TODOS los presupuestos (usar con cuidado en produccion)
-- ============================================================================

-- Descomentar las siguientes lineas para limpiar TODO:
-- TRUNCATE TABLE tbl_presupuesto_detalle;
-- TRUNCATE TABLE tbl_presupuesto_items;
-- TRUNCATE TABLE tbl_presupuesto_sst;

-- ============================================================================
-- OPCION 3: Resetear estado de presupuestos a borrador (sin eliminar datos)
-- ============================================================================

-- UPDATE tbl_presupuesto_sst
-- SET estado = 'borrador',
--     fecha_aprobacion = NULL,
--     firmado_por = NULL,
--     cedula_firmante = NULL,
--     firma_imagen = NULL,
--     ip_firma = NULL,
--     token_firma = NULL,
--     token_expiracion = NULL
-- WHERE id_cliente = 1;  -- Cambiar por el ID del cliente

-- ============================================================================
-- VERIFICAR LIMPIEZA
-- ============================================================================

-- Ver presupuestos restantes
SELECT id_presupuesto, id_cliente, anio, estado, created_at
FROM tbl_presupuesto_sst
ORDER BY id_cliente, anio;

-- Contar registros en cada tabla
SELECT
    (SELECT COUNT(*) FROM tbl_presupuesto_sst) as presupuestos,
    (SELECT COUNT(*) FROM tbl_presupuesto_items) as items,
    (SELECT COUNT(*) FROM tbl_presupuesto_detalle) as detalles;
