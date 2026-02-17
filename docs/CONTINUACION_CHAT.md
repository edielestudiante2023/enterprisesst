# Continuacion del Chat - 2026-02-17

## Estado: CHECKLIST COMPLETADO

### Que se hizo
1. **CHECKLIST_ITEMS reescrito** en `app/Services/InduccionEtapasService.php` (lineas 27-60):
   - Eliminado item 2 (redundante con paso 1 de modalidad)
   - Item 5 (PVM) → separado en 4 (P: reservar lugar) + 5 (V: plataforma virtual)
   - Item 6 (PVM) → separado en 6 (P: pieza impresa+digital) + 7 (V: pieza digital)
   - Item 14 (PVM) → separado en 15 (P: formato fisico asistencia) + 16 (V: formulario digital asistencia)
   - Item 15 (PVM) → separado en 17 (P: quiz papel) + 18 (V: formulario online)
   - Item 18 (PVM) → separado en 21 (P: fotos evento) + 22 (V: capturas/grabacion)
   - Items de logistica limpiados (quitado prefijo "Si es presencial/virtual:")
   - Renumerado de 1-25 a 1-29 (4 items nuevos netos)

2. **Contador actualizado** en `app/Views/induccion_etapas/checklist_pta.php` (linea 182):
   - Cambiado "de 25" hardcodeado a `<?= count($checklistItems) ?>` dinamico

### Que NO se toco (todo sigue funcionando)
- JS de filtrado en checklist_pta.php (ya maneja P/V/M/PVM correctamente)
- Tarjetas de modalidad (radio buttons)
- `buildChecklistTexto()` en InduccionEtapasService.php (lee CHECKLIST_ITEMS por ID, funciona con los nuevos IDs)
- `itemAplicaModalidad()` (filtra por modalidad, sin cambios)
- Controller, Routes, generar_pta.php

### Archivos pendientes de commit (mismo feature)
- `app/Config/Routes.php`
- `app/Controllers/InduccionEtapasController.php`
- `app/Services/FasesDocumentoService.php`
- `app/Services/InduccionEtapasService.php` ← CHECKLIST_ITEMS modificado aqui
- `app/Views/induccion_etapas/generar_pta.php`
- `app/Views/induccion_etapas/index.php`
- `app/Views/induccion_etapas/checklist_pta.php` ← nuevo archivo + contador arreglado

### Git flow
`git add .` → `git commit` → `git checkout main` → `git merge cycloid` → `git push origin main` → `git checkout cycloid`
