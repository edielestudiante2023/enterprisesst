# Continuacion del Chat - 2026-02-17

## Estado: MODO REGENERAR IMPLEMENTADO - Listo para deploy

### Que se hizo
Separar la naturaleza de los dos botones de generacion IA:

1. **"Generar con IA" / "Regenerar" (individual)** → `modo=regenerar`
   - NO consulta getContextoBase() (sin queries PTA, indicadores, etapas)
   - NO consulta MarcoNormativoService
   - NO muestra SweetAlerts de verificacion previos
   - NO muestra toast de metadata BD
   - SI envia contenido actual del textarea como referencia
   - SI envia instrucciones del usuario como PRIORIDAD MAXIMA
   - SI mantiene prompt estructural de seccion (como guia, no obligatorio)
   - SI mantiene contexto basico empresa (nombre, NIT, riesgo, trabajadores)

2. **"Generar Todo con IA"** → `modo=completo`
   - Pipeline completo sin cambios (getContextoBase + marco + metadata + SweetAlerts)

### Archivos modificados (3)
1. **`app/Views/documentos_sst/generar_con_ia.php`** (JS):
   - `generarSeccion(seccionKey, modo='completo')` → acepta parametro modo
   - Botones individuales llaman con `'regenerar'`, batch con `'completo'`
   - Envia `contenido_actual` al backend en modo regenerar
   - Sin SweetAlerts previos en modo individual (disparo directo)

2. **`app/Controllers/DocumentosSSTController.php`**:
   - `generarSeccionIA()`: lee `modo` y `contenido_actual` del POST
   - `generarConIAReal()`: acepta `$modo` y `$contenidoActual`, salta queries pesadas en regenerar
   - Metadata BD solo se consulta en modo completo

3. **`app/Services/IADocumentacionService.php`**:
   - `construirPrompt()`: bifurca segun modo
   - Modo regenerar: contenido actual como referencia + instrucciones usuario PRIORIDAD MAXIMA
   - Modo completo: pipeline original sin cambios

### Tambien se fixeo
- `select('DISTINCT nombre_indicador')` → `->distinct()->select('nombre_indicador')` en ProgramaInduccionReinduccion.php (fix del toast error metadata BD)

### Git flow
`git add .` → `git commit` → `git checkout main` → `git merge cycloid` → `git push origin main` → `git checkout cycloid`
