# CONTINUACIÓN — Integrar /estandares/{id} (fuente) → /listEvaluaciones (cliente)

## Objetivo (decidido con el usuario)
`/estandares/{id}` (tabla `tbl_cliente_estandares`) es la **única fuente de edición**. Debe alimentar
de forma confiable `/listEvaluaciones` (cliente, tabla `evaluacion_inicial_sst`), **incluyendo "NO APLICA"**.

Decisiones del usuario:
- Integración = **Sync confiable** (no reescribir la vista del cliente). El sync debe CREAR el registro si no existe (hoy falla por eso) + backfill.
- Edición = **solo en /estandares/{id}**; /listEvaluaciones queda solo lectura (la vista CLIENTE ya es solo lectura).

## Diagnóstico (verificado)
- `/estandares/(:num)` → `EstandaresClienteController::index` (app/Controllers/EstandaresClienteController.php:37) → `ClienteEstandaresModel` → `tbl_cliente_estandares`. Vista: `app/Views/estandares/dashboard.php`.
- `/listEvaluaciones/(:num)` → `ClientEvaluationController::listEvaluaciones` → `EvaluationModel` → `evaluacion_inicial_sst`. Vista: `app/Views/client/list_evaluaciones.php` (SOLO LECTURA, accordion JS).
- Sync: `app/Services/SyncEstandaresService.php`. `syncDesdeClienteEstandares()` hace `return` si el registro NO existe en `evaluacion_inicial_sst` (~línea 106) → por eso no se refleja.
- "NO APLICA" no se ve en /estandares porque `index` llama `getByClienteGroupedPHVA($id)` SIN incluir no_aplica (excluye en `getByClienteCompleto`).
- Cruce: `evaluacion_inicial_sst.numeral` = `tbl_estandares_minimos.item`.

## Esquema clave
- `tbl_cliente_estandares`: id_cliente, id_estandar, estado(no_aplica|pendiente|en_proceso|cumple|no_cumple), calificacion, observaciones, fecha_cumplimiento.
- `tbl_estandares_minimos`: id_estandar, item, nombre, criterio, ciclo_phva(PLANEAR|HACER|VERIFICAR|ACTUAR), categoria_nombre, peso_porcentual, aplica_7/21/60.
- `evaluacion_inicial_sst`: id_cliente, ciclo, estandar, detalle_estandar, numeral, item_del_estandar, item, criterio, evaluacion_inicial(CUMPLE TOTALMENTE|NO CUMPLE|NO APLICA|''), valor, puntaje_cuantitativo, calificacion, observaciones.

## Plan de implementación
1. **Filtro NO APLICA** en /estandares: `EstandaresClienteController::index` → `getByClienteGroupedPHVA($idCliente, true)`. La vista ya tiene card data-filtro="no_aplica" y filas data-estado.
2. **Estándar manual → aplicable**: en `DocCarpetaModel::agregarCarpetaManual` marcar `tbl_cliente_estandares` (no_aplica/missing → 'pendiente') + sync. En `eliminarCarpetaManual` → 'no_aplica' + sync. Nunca degradar un estado ya evaluado.
3. **Sync confiable (UPSERT)**: reescribir `SyncEstandaresService::syncDesdeClienteEstandares` para INSERTAR si no existe (mapear ciclo=ciclo_phva, estandar=categoria_nombre, numeral=item, valor=peso_porcentual, evaluacion_inicial=ESTADO_TO_EVAL, puntaje). Agregar `syncTodoCliente($idCliente)` (backfill).
4. **Backfill (script CLI)** dry-run/apply, local→prod: corre `syncTodoCliente` para cliente 15. DRY-RUN muestra diffs (riesgo: no pisar evaluaciones reales si tbl_cliente_estandares está más vacía).
5. **Solo lectura**: la vista cliente ya lo es. (Editor consultor `EvaluationController::updateEvaluacion` queda como nota.)

## Estado
- [ ] Parte 1  [ ] Parte 2  [ ] Parte 3  [ ] Parte 4 backfill  [ ] deploy

## Flujo git/deploy
git add . → commit → checkout main → merge cycloid → push origin main → checkout cycloid.
Scripts BD: LOCAL primero, luego --env=prod.
