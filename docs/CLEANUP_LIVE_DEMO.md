# Cleanup Script - Live Demo (TikTok/YouTube)

> **Objetivo:** Eliminar todos los registros del cliente demo creado durante la sesion en vivo.
> Ejecutar estos queries AL FINALIZAR el live para dejar la BD limpia.

## Datos del Cliente Demo

- **id_cliente:** 19
- **nombre:** EMPRESA OMEGA
- **nit:** 123456
- **id_contrato:** 8
- **numero_contrato:** CONT-000019-001
- **id_presupuesto:** 1

## Archivos fisicos a eliminar del servidor

```text
uploads/contratos/contrato_CONT-000019-001_20260213_092731.pdf
```

---

## Queries de Limpieza (orden: hijas primero, padre al final)

### 1. Presupuesto detalle (tbl_presupuesto_detalle) - 24 registros

```sql
-- 24 registros mensuales (12 meses x 2 items)
DELETE FROM tbl_presupuesto_detalle WHERE id_item IN (SELECT id_item FROM tbl_presupuesto_items WHERE id_presupuesto = 1);
```

### 2. Presupuesto items (tbl_presupuesto_items) - 2 registros

```sql
-- id=1: Botiquines y reposicion (cat 5)
-- id=2: Vacunacion ocupacional (cat 2)
DELETE FROM tbl_presupuesto_items WHERE id_presupuesto = 1;
```

### 3. Presupuesto SST (tbl_presupuesto_sst) - 1 registro

```sql
-- Presupuesto 2026, estado borrador
DELETE FROM tbl_presupuesto_sst WHERE id_cliente = 19;
```

### 4. Documentos SST generados (tbl_documentos_sst) - 5 registros

```sql
-- id=6: Asignacion Responsable SG-SST (firmado)
-- id=7: Responsabilidades Rep Legal (firmado)
-- id=8: Responsabilidades Responsable SST (firmado)
-- id=9: Responsabilidades Trabajadores (aprobado)
-- id=10: Presupuesto SST (aprobado)
DELETE FROM tbl_documentos_sst WHERE id_cliente = 19;
```

### 5. Responsables SST (tbl_cliente_responsables_sst) - 2 registros

```sql
-- id=5: Representante Legal OMEGA
-- id=6: Responsable/Delegado SGSST OMEGA
DELETE FROM tbl_cliente_responsables_sst WHERE id_cliente = 19;
```

### 6. Contexto SST (tbl_cliente_contexto_sst) - 1 registro

```sql
-- Contexto: Transporte, 200 trabajadores, Riesgo III, ARL Colmena (id_contexto=2)
DELETE FROM tbl_cliente_contexto_sst WHERE id_cliente = 19;
```

### 7. Carpetas documentales (tbl_doc_carpetas) - 65 registros

```sql
-- Estructura PHVA 2026: SG-SST > PLANEAR/HACER/VERIFICAR/ACTUAR (IDs 92-156)
DELETE FROM tbl_doc_carpetas WHERE id_cliente = 19;
```

### 8. Estandares del cliente (tbl_cliente_estandares) - 60 registros

```sql
-- 60 estandares inicializados (IDs 1-60)
DELETE FROM tbl_cliente_estandares WHERE id_cliente = 19;
```

### 9. Contrato (tbl_contratos)

```sql
-- Contrato id=8 del cliente demo
DELETE FROM tbl_contratos WHERE id_cliente = 19;
```

### 10. Cliente (tbl_clientes)

```sql
-- Cliente EMPRESA OMEGA
DELETE FROM tbl_clientes WHERE id_cliente = 19;
```

---

## SCRIPT COMPLETO (ejecutar al final)

```sql
-- ============================================
-- CLEANUP COMPLETO - LIVE DEMO
-- Cliente: EMPRESA OMEGA (id_cliente = 19)
-- Fecha live: 2026-02-13
-- ============================================

-- 1. Presupuesto detalle (24 registros mensuales)
DELETE FROM tbl_presupuesto_detalle WHERE id_item IN (SELECT id_item FROM tbl_presupuesto_items WHERE id_presupuesto = 1);

-- 2. Presupuesto items (2 items)
DELETE FROM tbl_presupuesto_items WHERE id_presupuesto = 1;

-- 3. Presupuesto SST (1 registro)
DELETE FROM tbl_presupuesto_sst WHERE id_cliente = 19;

-- 4. Documentos SST (5 documentos)
DELETE FROM tbl_documentos_sst WHERE id_cliente = 19;

-- 5. Responsables SST (2 registros)
DELETE FROM tbl_cliente_responsables_sst WHERE id_cliente = 19;

-- 6. Contexto SST (1 registro)
DELETE FROM tbl_cliente_contexto_sst WHERE id_cliente = 19;

-- 7. Carpetas documentales (65 registros, estructura PHVA 2026)
DELETE FROM tbl_doc_carpetas WHERE id_cliente = 19;

-- 8. Estandares del cliente (60 registros)
DELETE FROM tbl_cliente_estandares WHERE id_cliente = 19;

-- 9. Contrato
DELETE FROM tbl_contratos WHERE id_cliente = 19;

-- 10. Cliente
DELETE FROM tbl_clientes WHERE id_cliente = 19;

-- VERIFICACION: debe retornar 0 filas en todas
SELECT 'tbl_presupuesto_detalle' AS tabla, COUNT(*) AS registros FROM tbl_presupuesto_detalle WHERE id_item IN (SELECT id_item FROM tbl_presupuesto_items WHERE id_presupuesto = 1)
UNION ALL
SELECT 'tbl_presupuesto_items', COUNT(*) FROM tbl_presupuesto_items WHERE id_presupuesto = 1
UNION ALL
SELECT 'tbl_presupuesto_sst', COUNT(*) FROM tbl_presupuesto_sst WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_documentos_sst', COUNT(*) FROM tbl_documentos_sst WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_cliente_responsables_sst', COUNT(*) FROM tbl_cliente_responsables_sst WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_cliente_contexto_sst', COUNT(*) FROM tbl_cliente_contexto_sst WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_doc_carpetas', COUNT(*) FROM tbl_doc_carpetas WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_cliente_estandares', COUNT(*) FROM tbl_cliente_estandares WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_contratos', COUNT(*) FROM tbl_contratos WHERE id_cliente = 19
UNION ALL
SELECT 'tbl_clientes', COUNT(*) FROM tbl_clientes WHERE id_cliente = 19;
```
