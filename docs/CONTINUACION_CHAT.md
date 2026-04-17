# Continuación: fecha_cierre_real + SIN RESPUESTA DEL CLIENTE

**Fecha:** 2026-04-17
**Branch:** cycloid

## Estado: Bloque 1 COMPLETADO, Bloques 2 y 3 PENDIENTES

## Lo que se hizo

### FASE 0-3: Columna fecha_cierre_real
- ALTER TABLE tbl_pendientes ADD COLUMN fecha_cierre_real DATE NULL (ejecutado LOCAL + PROD)
- Backfill: LOCAL 66 filas, PROD 106 filas con DATE(updated_at)
- PendientesModel: allowedFields + calculateConteoDias usa fecha_cierre_real
- PendientesController: 4 métodos actualizados (updatePendiente, listPendientes, addPendientePost, editPendientePost, recalcularConteoDias)
- ConsultantController::retirarCliente(): agrega fecha_cierre_real = CURDATE()
- SIN RESPUESTA DEL CLIENTE excluido del auto-set manual (solo CERRADA y CERRADA POR FIN CONTRATO auto-llenan fecha_cierre_real)

### FASE 4: UI
- 4 vistas actualizadas con columna "Cierre Real"
- Label "Fecha Cierre" renombrado a "Fecha de Plazo" en las 4 vistas de pendientes (solo visual, BD sigue fecha_cierre)

### Archivos modificados
1. app/SQL/add_fecha_cierre_real.php (NUEVO)
2. app/Models/PendientesModel.php
3. app/Controllers/PendientesController.php
4. app/Controllers/ConsultantController.php
5. app/Views/consultant/list_pendientes.php
6. app/Views/consultant/dashboard_pendientes.php
7. app/Views/client/list_pendientes.php
8. app/Views/client/dashboard_pendientes.php

## Lo que falta

### Bloque 2: Cron auto-clasificación SIN RESPUESTA DEL CLIENTE
- Crear app/Commands/ClasificarSinRespuesta.php (patrón igual a ResumenPendientes.php)
- Lógica: pendientes ABIERTOS donde CURDATE() > fecha_cierre + 90 días → estado = 'SIN RESPUESTA DEL CLIENTE', fecha_cierre_real = CURDATE()
- Email a cliente y consultor informando la clasificación
- Mensaje: la actividad X fue clasificada SIN RESPUESTA DEL CLIENTE por ausencia de gestión, si tiene soportes que los remita vía email
- Referencia: app/Commands/ResumenPendientes.php ya tiene enviarEmail() con SendGrid via cURL

### Bloque 3: Mostrar SIN RESPUESTA en informe-avances
- InformeAvancesController.php: incluir pendientes SIN RESPUESTA DEL CLIENTE en las métricas
- app/Views/informe_avances/view.php: mostrar sección SIN RESPUESTA
- app/Views/informe_avances/pdf.php: mostrar sección SIN RESPUESTA
- El desglose_pendientes ya agrupa por estado, verificar si SIN RESPUESTA llega naturalmente

### Decisiones tomadas
- Solo 1 columna nueva (fecha_cierre_real), no 2 como en PH (porque aquí fecha_cierre nunca se sobrescribe)
- SIN RESPUESTA DEL CLIENTE solo lo pone el cron, no el usuario manualmente
- Patrón cron: CI4 BaseCommand + SendGrid cURL (ya probado en ResumenPendientes.php)
