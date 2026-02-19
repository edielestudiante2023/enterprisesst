# Continuacion de Chat - Estado al 2026-02-18

## SESION ACTUAL: PLAN EMERGENCIAS 5.1.1 CARPETA HIBRIDA

### Tareas completadas

#### Plan de Emergencias - Carpeta Hibrida (IA + Soportes)

1. **BD** - Script `app/SQL/agregar_secciones_plan_emergencias_y_reglamento.php` ejecutado OK
   - plan_emergencias: 12 secciones con prompt_ia + 2 firmantes (responsable_sst, representante_legal)
   - LOCAL OK (ID=5) + PRODUCCION OK (ID=8)

2. **DocumentacionController.php** - Query hibrida actualizada
   - `whereIn('tipo_documento', ['plan_emergencias', 'soporte_plan_emergencias'])`
   - Ahora la carpeta 5.1.1 carga ambos tipos de documentos

3. **Vista `_tipos/plan_emergencias.php`** - Reescrita como hibrida
   - Separa docs IA de soportes al inicio del archivo
   - Boton "Crear con IA" (o "Nueva version" si ya hay aprobado del ano actual)
   - Boton "Adjuntar Soporte" (abre modal)
   - `tabla_documentos_sst` para docs IA
   - `tabla_soportes` para soportes adjuntados
   - Modal adjuntar mantenido intacto

4. **acciones_documento.php** - Agregado `plan_emergencias`
   - En `$mapaRutas`: `'plan_emergencias' => 'plan-emergencias/' . $docSST['anio']`
   - En bloque `$urlEditar`: ruta `/documentos/generar/plan_emergencias/`

5. **Documentacion** - `PLAN_EMERGENCIAS.md` actualizado con seccion "Carpeta Hibrida"

### Ya existia (no se toco)
- Clase PHP `PlanEmergencias.php` (12 secciones, Tipo A, secciones_ia)
- Factory `DocumentoSSTFactory.php` (linea 74)
- Ruta `Routes.php` linea 998: `/documentos-sst/(:num)/plan-emergencias/(:num)`
- Metodo `planEmergencias()` en `DocumentosSSTController.php` linea 6273
- Metodo `adjuntarSoporteEmergencias()` linea 3071

### Archivos modificados
- `app/Controllers/DocumentacionController.php` — query hibrida whereIn
- `app/Views/documentacion/_tipos/plan_emergencias.php` — reescrita completa
- `app/Views/documentacion/_components/acciones_documento.php` — mapaRutas + urlEditar
- `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/PLAN_EMERGENCIAS.md` — actualizado

### Verificacion pendiente
- [ ] `/documentos/generar/plan_emergencias/18` — SweetAlert + generacion IA
- [ ] `/documentos-sst/18/plan-emergencias/2026` — vista previa OK
- [ ] Carpeta 5.1.1 en documentacion muestra docs IA + soportes
- [ ] Toolbar (PDF, Word, ver, editar, firmas) funciona

---

# SESION ANTERIOR: RESOLUCION DISCREPANCIAS FACTORY vs BD

### Tareas completadas
- Actas: investigadas, pertenecen a ComitesEleccionesController (no SST)
- PVE Biomecanico y Psicosocial: flujo corregido a programa_con_pta
- politica_sst: eliminado (duplicado de politica_sst_general)
- plan_emergencias y reglamento_higiene_seguridad: clases PHP + BD creados

### Estado BD
| Tipo | Flujo | Secciones | Firmantes | Estado |
|------|-------|-----------|-----------|--------|
| `plan_emergencias` | `secciones_ia` | 12 | 2 | OK |
| `reglamento_higiene_seguridad` | `secciones_ia` | 11 | 2 | OK |
| `pve_riesgo_biomecanico` | `programa_con_pta` | 12 | 2 | OK |
| `pve_riesgo_psicosocial` | `programa_con_pta` | 12 | 2 | OK |
