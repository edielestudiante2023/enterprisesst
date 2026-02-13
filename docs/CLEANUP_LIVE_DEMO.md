# Cleanup Script - Live Demo (TikTok/YouTube)

> **Objetivo:** Eliminar todos los registros del cliente demo creado durante la sesion en vivo.
> Ejecutar estos queries AL FINALIZAR el live para dejar la BD limpia.

## Instrucciones
1. Reemplazar `@ID_CLIENTE` con el ID real del cliente demo
2. Ejecutar los queries **en orden inverso** (de abajo hacia arriba) para respetar foreign keys
3. O ejecutar el bloque final "SCRIPT COMPLETO" que ya tiene el orden correcto

---

## Variables
```sql
-- Definir el ID del cliente demo aqui
SET @ID_CLIENTE = NULL; -- << CAMBIAR por el ID real
```

---

## Queries de Limpieza (se iran agregando durante el live)

<!-- Aqui se iran agregando los queries a medida que avance la sesion -->

---

## SCRIPT COMPLETO (ejecutar al final)
```sql
-- ============================================
-- CLEANUP COMPLETO - LIVE DEMO
-- Ejecutar despues de terminar la sesion
-- ============================================

SET @ID_CLIENTE = NULL; -- << CAMBIAR por el ID real

-- Los queries se agregaran aqui en orden correcto
-- (tablas hijas primero, tablas padre al final)
```
