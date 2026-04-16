# Continuación — Módulo IPEVR GTC 45

**Estado al cierre del chat (sprint sin pruebas intermedias):** Implementación completa end-to-end (Fases 0 → 3b.7 + docs).

## Lo que ya está hecho y verificado

### Fases con pruebas HTTP LOCAL verdes
- **Fase 0** — Documentación arquitectura: [docs/MODULO_IPEVR_GTC45/ARQUITECTURA.md](docs/MODULO_IPEVR_GTC45/ARQUITECTURA.md)
- **Fase 1** — 7 tablas catálogo GTC 45 + seed (LOCAL + PROD): [scripts/ipevr_gtc45_fase1.php](scripts/ipevr_gtc45_fase1.php)
- **Fase 2** — 4 tablas maestros cliente (LOCAL + PROD): [scripts/ipevr_gtc45_fase2.php](scripts/ipevr_gtc45_fase2.php)
- **Fase 3a** — 3 tablas operacionales IPEVR (LOCAL + PROD): [scripts/ipevr_gtc45_fase3a.php](scripts/ipevr_gtc45_fase3a.php)
- **Fase 3b.1** — 8 Models + 3 Controllers esqueleto + 21 rutas: probado vía HTTP.
- **Fase 3b.2** — CRUD maestros cliente completo: probado create/edit/soft-delete.
- **Fase 3b.3.1** — Lista de matrices por cliente + crear nueva: probado.
- **Fase 3b.3.2a** — Editor PC estructura + calculadora JS: probado.
- **Fase 3b.3.2b** — CRUD de filas completo + modal 6 pestañas + cálculo NP/NR: probado con 2 casos (NP=6→NR=150, NP=18→NR=450).

### Fases construidas SIN pruebas (sprint sin parar)
- **Fase 3b.3.2c** — Tabla del editor resuelve IDs a nombres + badge coloreado nivel NR + cargos como chips.
- **Fase 3b.4** — Semilla IA: `IpevrIaSugeridor` (gpt-4o-mini con JSON mode) + endpoint `sugerirIa()` + botón integrado en editor.
- **Fase 3b.5** — PWA completa: `editor_pwa.php` + `manifest_ipevr.json` + `ipevr_pwa_queue.js` + wizard 6 pasos + cola offline.
- **Fase 3b.6** — Export: `IpevrExportXlsx` (3 hojas: Matriz + Tablas + Instructivo) + `IpevrExportPdf` (A3 landscape).
- **Fase 3b.7** — Versionamiento: transiciones `borrador→revision→vigente→historica` + copia de filas en nueva versión + snapshot JSON + control de cambios + menú `⋮` en el editor PC.

## Checklist de pruebas para el próximo chat

Copiar y probar uno por uno. Reportar cada ❌ con número y mensaje de error.

### Pruebas básicas (regresión)
- [ ] GET `/maestros-cliente/12` → 200, muestra 4 pestañas.
- [ ] GET `/ipevr/cliente/12` → 200, botón "Nueva matriz".
- [ ] Crear matriz v001 → redirect al editor PC → 200.
- [ ] Crear fila manual con ND=M, NE=EF, NC=G → NP=6, NR=150, badge naranja (Nivel II).

### Pruebas Fase 3b.3.2c (pulido tabla)
- [ ] Editor PC con 1 fila: columna "Clasif." muestra badge azul (no "—").
- [ ] Columna "Nivel" muestra badge con color del catálogo.
- [ ] Columna "Cargos" muestra chips separados.
- [ ] Columna "Interp.NP" muestra nombre (ej. "Medio").

### Pruebas Fase 3b.4 (IA)
- [ ] `OPENAI_API_KEY` configurada en `.env`.
- [ ] Botón "Sugerir con IA" → ingresar 10 → confirmar.
- [ ] Alert con "X insertadas, Y rechazadas" en 30-90s.
- [ ] SQL: `SELECT COUNT(*), origen_fila FROM tbl_ipevr_fila WHERE id_matriz=N GROUP BY origen_fila;` → filas con `origen_fila='ia'`.
- [ ] Si rechaza todo: revisar `errores` y ajustar el prompt en `IpevrIaSugeridor::construirPrompt()`.

### Pruebas Fase 3b.5 (PWA)
- [ ] GET `/ipevr/matriz/{id}/pwa` → 200.
- [ ] Chrome DevTools → Device Mode → iPhone.
- [ ] FAB "+" → wizard paso 1 → Siguiente → ... → paso 6 → Guardar → fila nueva.
- [ ] DevTools → Network → Offline → crear otra → cola local (alert "sin conexión").
- [ ] Volver a Online → `IPEVR_QUEUE.size()` en consola disminuye.
- [ ] `manifest_ipevr.json` carga sin 404.

### Pruebas Fase 3b.6 (Export)
- [ ] GET `/ipevr/matriz/{id}/exportar/xlsx` → descarga Excel con 3 hojas.
- [ ] Columna "Nivel" en Excel con celda coloreada.
- [ ] GET `/ipevr/matriz/{id}/exportar/pdf` → descarga PDF landscape con badge.

### Pruebas Fase 3b.7 (Versionamiento)
- [ ] Menú `⋮` → "Enviar a revisión" → prompt → estado `revision`.
- [ ] Menú `⋮` → "Aprobar y marcar vigente" → estado `vigente` + snapshot_json.
- [ ] SQL: `SELECT version,descripcion FROM tbl_ipevr_control_cambios WHERE id_matriz=N;` → 2 registros.
- [ ] Menú `⋮` → "Crear nueva versión" → redirect a v002 en `borrador`.
- [ ] SQL: `SELECT version,estado FROM tbl_ipevr_matriz WHERE id_cliente=12;` → v001 `historica`, v002 `borrador`.
- [ ] Filas de v002 con `origen_fila='importada'`.

## Archivos creados/modificados en este sprint

### Backend PHP
- `app/Controllers/IpevrController.php` — CRUD matrices/filas + sugerirIa + export + versionamiento
- `app/Controllers/IpevrPwaController.php` — vista PWA + autosave + sync
- `app/Controllers/MaestrosClienteController.php` — CRUD maestros cliente
- `app/Models/Ipevr{Matriz,Fila,ControlCambios}Model.php` + `Gtc45CatalogoModel.php`
- `app/Models/{Proceso,Cargo,Tarea,Zona}ClienteModel.php`
- `app/Libraries/IpevrIaSugeridor.php` — OpenAI gpt-4o-mini con JSON mode
- `app/Libraries/IpevrExportXlsx.php` — PhpSpreadsheet
- `app/Libraries/IpevrExportPdf.php` — Dompdf A3 landscape

### Frontend
- `app/Views/ipevr/index.php` — lista matrices
- `app/Views/ipevr/editor_pc.php` — editor tabla + modal 6 pestañas + versionamiento
- `app/Views/ipevr/editor_pwa.php` — PWA wizard 6 pasos
- `app/Views/maestros_cliente/index.php` — CRUD maestros 4 pestañas
- `public/js/ipevr_calculadora.js` — cálculo NP/NR
- `public/js/ipevr_pwa_queue.js` — cola offline localStorage
- `public/manifest_ipevr.json` — PWA manifest

### Rutas
22 rutas en `app/Config/Routes.php:1827-1862`.

### BD (ya aplicado LOCAL + PROD)
- `scripts/ipevr_gtc45_fase1.php` — 7 catálogos + seed
- `scripts/ipevr_gtc45_fase2.php` — 4 maestros cliente
- `scripts/ipevr_gtc45_fase3a.php` — 3 operacionales IPEVR

**Total:** 14 tablas nuevas. Ninguna tabla existente tocada.

### Docs
- `docs/MODULO_IPEVR_GTC45/ARQUITECTURA.md`
- `docs/MODULO_IPEVR_GTC45/GUIA_USO.md`
- `docs/MODULO_IPEVR_GTC45/TROUBLESHOOTING.md`
- `docs/CONTINUACION_CHAT_IPEVR.md` ← este archivo.

## Puntos de extensión pendientes

1. **Integración profunda con sistema de firmas existente.** Hoy al aprobar sólo se llenan los campos `elaborado_por` / `aprobado_por` como texto. Para integrar con el flujo real de firmas electrónicas, revisar memoria `firmas-sistema.md` y enlazar desde `nuevaVersion() case 'aprobar'`.
2. **Drag-and-drop de reordenamiento de filas.** Agregar SortableJS al tbody del editor PC si se requiere.
3. **Importador XLSX de matrices existentes.** Permitir arrastrar un `FT-SST-035.xlsx` del cliente para importar filas en lote.
4. **Service Worker real para PWA offline completo.** Hoy la cola offline funciona con `localStorage` + evento `online`, pero no hay cache de la página. Si se quiere que el editor PWA cargue sin conexión, agregar `sw_ipevr.js` similar a `sw_inspecciones.js`.
5. **Filtros y búsqueda con DataTables.js** en el editor PC cuando haya >50 filas.

## Prompt sugerido para el próximo chat

```
Estoy retomando el módulo IPEVR GTC 45. Leer docs/CONTINUACION_CHAT_IPEVR.md
para ver el estado exacto y el checklist de pruebas.

Quiero que me ayudes a:
1. Ejecutar el checklist de pruebas en LOCAL una por una.
2. Reportar cada fallo encontrado con su fix propuesto.
3. Aplicar los fixes uno a uno, confirmándome antes de cada cambio.

La BD ya está completa en LOCAL y PROD (14 tablas, fases 1/2/3a).
No tocar tablas existentes del proyecto.
```
