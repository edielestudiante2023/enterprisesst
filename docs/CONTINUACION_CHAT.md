# Continuación de Chat — Estado al 2026-02-18

## SESIÓN ACTUAL: RESOLUCIÓN DISCREPANCIAS FACTORY vs BD ✅

### Tareas completadas en esta sesión

#### Caso 1: Las 8 Actas — SIN acción requerida ✅
**Investigadas y concluidas:** `acta_constitucion_*` y `acta_recomposicion_*` pertenecen al módulo
`ComitesEleccionesController`. No pasan por `/documentos/generar/`. El Factory las tiene porque el
controlador de Comités usa métodos de la interfaz (`getVistaPath()`, `buildContenidoSnapshot()`).
No necesitan entradas en `tbl_doc_tipo_configuracion` del flujo estándar SST.

#### Caso 2: PVE Biomecanico y PVE Psicosocial — REGISTRADOS ✅
- Corregido `flujo = 'secciones_ia'` → `flujo = 'programa_con_pta'` en los scripts existentes
- Ejecutados `agregar_pve_riesgo_biomecanico.php` y `agregar_pve_riesgo_psicosocial.php`
- LOCAL OK (IDs 89 y 90) + PRODUCCIÓN OK (IDs 76 y 77)
- Eliminado `getPromptEstatico()`, `use DocumentoConfigService`, `$configService` de ambas clases PHP
- 12 secciones + 2 firmantes cada uno

#### Caso 3: Tipos en BD sin secciones — RESUELTOS ✅

**`politica_sst` (duplicado de `politica_sst_general`):**
- ELIMINADO de BD: `app/SQL/eliminar_politica_sst_duplicado.php`
- Sin documentos reales afectados. `politica_sst_general` intacto (ID 21 local / 16 prod)

**`matriz_requisitos_legales`:**
- NO TOCADO: flujo = `formulario`, no usa IA, no está roto

**`plan_emergencias` (estándar 5.1.1) — IMPLEMENTADO:**
- Clase PHP: `app/Libraries/DocumentosSSTTypes/PlanEmergencias.php` (12 secciones, Tipo A)
- SQL: `app/SQL/agregar_secciones_plan_emergencias_y_reglamento.php`
- LOCAL OK (ID 5, 12 secciones) + PRODUCCIÓN OK (ID 8, 12 secciones)
- Registrado en Factory (auto-detectado correctamente como `PlanEmergencias`)

**`reglamento_higiene_seguridad` (estándar 1.1.2) — IMPLEMENTADO:**
- Clase PHP: `app/Libraries/DocumentosSSTTypes/ReglamentoHigieneSeguridadIndustrial.php` (11 secciones, Tipo A)
- SQL: mismo script anterior
- LOCAL OK (ID 7, 11 secciones) + PRODUCCIÓN OK (ID 10, 11 secciones)
- Registrado en Factory EXPLÍCITAMENTE (clase tiene "Industrial" en nombre, auto-detección busca sin "Industrial")

### Archivos modificados en esta sesión
- `app/Libraries/DocumentosSSTTypes/PveRiesgoBiomecanico.php` — eliminado `getPromptEstatico()`, `configService`
- `app/Libraries/DocumentosSSTTypes/PveRiesgoPsicosocial.php` — ídem
- `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` — registrados `plan_emergencias` y `reglamento_higiene_seguridad`
- `app/SQL/agregar_pve_riesgo_biomecanico.php` — corregido flujo a `programa_con_pta`
- `app/SQL/agregar_pve_riesgo_psicosocial.php` — corregido flujo a `programa_con_pta`

### Archivos creados en esta sesión
- `app/Libraries/DocumentosSSTTypes/PlanEmergencias.php` — nuevo
- `app/Libraries/DocumentosSSTTypes/ReglamentoHigieneSeguridadIndustrial.php` — nuevo
- `app/SQL/agregar_secciones_plan_emergencias_y_reglamento.php` — ejecutado, OK
- `app/SQL/eliminar_politica_sst_duplicado.php` — ejecutado, OK

### Estado de la BD (resumen post-sesión)
| Tipo | Flujo | Secciones | Firmantes | Estado |
|------|-------|-----------|-----------|--------|
| `pve_riesgo_biomecanico` | `programa_con_pta` | 12 | 2 | ✅ |
| `pve_riesgo_psicosocial` | `programa_con_pta` | 12 | 2 | ✅ |
| `plan_emergencias` | `secciones_ia` | 12 | 2 | ✅ |
| `reglamento_higiene_seguridad` | `secciones_ia` | 11 | 2 | ✅ |
| `politica_sst` | — | — | — | Eliminado (duplicado) |
| `matriz_requisitos_legales` | `formulario` | 0 | 0 | OK (flujo diferente) |

### Discrepancias resueltas — TODAS ✅
- Factory PHP vs BD: 0 discrepancias pendientes
- Tipos en BD sin secciones: 0 pendientes (matriz_requisitos es flujo distinto)

### Pendientes para próximas sesiones
- Hacer commit de todos los cambios acumulados de las últimas sesiones
- `matriz_requisitos_legales` (`formulario`): crear formulario UI cuando se implemente ese flujo
- Probar en navegador los nuevos documentos generados con IA

---

# SESIÓN ANTERIOR: MIGRACIÓN PROMPTS IA — BD COMO FUENTE ÚNICA DE VERDAD ✅

### Tareas completadas
1. **Reglas documentadas** en `docs/MODULO_NUMERALES_SGSST/02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md`
2. **`generarConIAReal()` reescrito** — lee tipo + prompt exclusivamente de BD
3. **`getPromptParaSeccion()` eliminado** de 32 clases PHP + interfaz (verificado: 0 ocurrencias)
4. **`flujo_ia.txt`** actualizado con flujo interno real y estados de error
5. **`MEMORY.md`** actualizado con nuevas reglas

### Archivos modificados
- `app/Controllers/DocumentosSSTController.php` — `generarConIAReal()` reescrito, `buildContextoBaseGenerico()` agregado
- `app/Libraries/DocumentosSSTTypes/DocumentoSSTInterface.php` — 4 métodos eliminados de la interfaz
- 32 clases en `app/Libraries/DocumentosSSTTypes/` — `getPromptParaSeccion()` eliminado

---

# SESIÓN ANTERIOR: SUB-CARPETA 2.5.1.1 DOCUMENTOS EXTERNOS - 2026-02-17

## Estado: IMPLEMENTADA Y NIVELADA

### Que se hizo
Crear la sub-carpeta 2.5.1.1 "Listado Maestro de Documentos Externos" dentro del estandar 2.5.1 del SG-SST, con funcionalidad completa de adjuntar soportes (archivos/enlaces).

### Archivos creados (3)
1. **`app/SQL/agregar_subcarpeta_documentos_externos.sql`** - Script migracion
2. **`app/Views/documentacion/_tipos/documentos_externos.php`** - Vista tipo
3. Scripts de nivelacion (ejecutados)

### Archivos modificados (4)
1. **`app/Controllers/DocumentacionController.php`** — `determinarTipoCarpetaFases()` + 3 puntos más
2. **`app/Controllers/DocumentosSSTController.php`** — `adjuntarSoporteDocumentoExterno()`
3. **`app/Config/Routes.php`** — nueva ruta POST
4. **`app/Views/documentacion/_components/tabla_documentos_sst.php`** — `'documentos_externos'` en `$tiposConTabla`

### Git flow
`git add .` → `git commit` → `git checkout main` → `git merge cycloid` → `git push origin main` → `git checkout cycloid`
