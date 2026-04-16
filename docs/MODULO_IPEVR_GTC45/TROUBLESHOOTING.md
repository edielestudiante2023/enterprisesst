# Troubleshooting — Módulo IPEVR GTC 45

## Errores frecuentes

### 1. "OPENAI_API_KEY no configurada" al usar "Sugerir con IA"

**Causa:** La variable `OPENAI_API_KEY` no está en el `.env`.
**Fix:** Agregar al `.env`:
```
OPENAI_API_KEY=sk-proj-...
```
Reiniciar el proceso PHP si es necesario.

### 2. "ND/NE/NC desconocido" al insertar filas desde IA

**Causa:** La IA devolvió un código que no existe en los catálogos GTC 45 (ej. `MM` en lugar de `MA`).
**Fix:** El backend rechaza la fila y la reporta en `errores[]`. Se puede ajustar el prompt en `app/Libraries/IpevrIaSugeridor.php::construirPrompt()` para ser más estricto.

### 3. NP/NR aparecen en "—" al guardar una fila

**Causa:** El usuario no seleccionó ND, NE o NC en la pestaña 4 (Evaluación).
**Fix:** Todos los tres son obligatorios para que el cálculo ocurra. El backend sólo calcula si los tres están presentes.

### 4. La tabla del editor muestra "—" en columnas de catálogo (clasificación, nivel, etc.)

**Causa:** La fila fue guardada con IDs que ya no existen en los catálogos (ej. catálogo reseeded).
**Fix:** Los catálogos son estables; esto sólo pasaría si alguien los vacía manualmente. Recorrer las filas con `UPDATE tbl_ipevr_fila SET id_clasificacion=NULL WHERE id_clasificacion NOT IN (SELECT id FROM tbl_gtc45_clasificacion_peligro)`.

### 5. PWA no se instala en móvil

**Causa:** `manifest_ipevr.json` debe ser servido desde `/manifest_ipevr.json` (root de public/), y los iconos `/assets/icons/icon-192.png` y `icon-512.png` deben existir.
**Fix:** Verificar que existan los assets reutilizados del manifest de inspecciones.

### 6. Filas offline en PWA no se sincronizan

**Causa:** El evento `online` no dispara el flush, o `window.IPEVR_PWA_ENDPOINT` no está definido.
**Fix:** En consola del navegador ejecutar manualmente `IPEVR_QUEUE.flush(IPEVR_PWA_ENDPOINT)` para ver errores.

### 7. Exportación XLSX falla con "Class PhpOffice\PhpSpreadsheet\Spreadsheet not found"

**Causa:** Composer autoload no actualizado.
**Fix:** `composer dump-autoload`.

### 8. Exportación PDF con texto cortado

**Causa:** Muchas columnas en A3 landscape no caben.
**Fix:** El PDF ya muestra sólo 13 columnas clave. Para ver todo, usar la exportación XLSX.

### 9. Al crear nueva versión, las filas no se copian

**Causa:** `$this->filaModel->insert($f)` falló silenciosamente porque `allowedFields` no incluye algún campo.
**Fix:** Revisar que `IpevrFilaModel::$allowedFields` incluya todos los campos de `tbl_ipevr_fila` excepto `id`, `created_at`, `updated_at`.

### 10. No puedo editar una matriz "vigente"

**Comportamiento correcto:** Las matrices `vigente` y `historica` no aceptan `sugerirIa` ni se deberían modificar directamente. Crear una **nueva versión** desde el menú `⋮`.

## Pruebas end-to-end manuales

1. **BD catálogos:** `SELECT COUNT(*) FROM tbl_gtc45_nivel_riesgo` → debe dar 4.
2. **Maestros:** crear proceso/cargo/tarea/zona desde `/maestros-cliente/12` → verificar que aparecen en el autocomplete del editor.
3. **Crear matriz vacía:** desde `/ipevr/cliente/12` → nueva → editar.
4. **Fila manual:** agregar fila con ND=M, NE=EF, NC=G → verificar NP=6 y NR=150 en el badge coloreado.
5. **IA:** "Sugerir con IA" con 10 filas → verificar que aparezcan como `origen_fila='ia'` en BD.
6. **Editor PC muestra fila:** tabla tiene nombres resueltos, badge de nivel coloreado, cargos como chips.
7. **PWA móvil:** abrir `/ipevr/matriz/{id}/pwa` en Chrome DevTools Device Mode → crear fila vía wizard → volver al editor PC y verificar.
8. **Export XLSX:** descargar → abrir en Excel → verificar 3 hojas.
9. **Export PDF:** descargar → verificar badge coloreado en columna Nivel.
10. **Versionamiento:** borrador → revisión → aprobar → vigente → nueva versión → verificar que la v001 queda `historica` y la v002 nace en `borrador` con las filas copiadas.
