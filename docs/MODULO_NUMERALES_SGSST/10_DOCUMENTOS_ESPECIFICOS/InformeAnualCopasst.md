# Informe Anual de Gestion del COPASST

## Metadata
- **tipo_documento:** `informe_anual_copasst`
- **estandar:** 1.1.6 (vive en la carpeta de Conformacion COPASST)
- **flujo:** `secciones_ia`
- **categoria:** `informes_comites`
- **tipoCarpetaFases:** `conformacion_copasst` (compartida con el trimestral)
- **codigo_plantilla:** `INF-COP-A`
- **clase PHP:** `InformeAnualCopasst` (sobrescribe `getContextoBase()` con el comparativo anual)
- **firmantes:** `responsable_sst` (Elaboro) + `representante_legal` (Aprobo)

## URLs
- Generacion (snake_case): `/documentos/generar/informe_anual_copasst/{id_cliente}?anio={anio}`
- Vista web (kebab-case): `/documentos-sst/{id_cliente}/informe-anual-copasst/{anio}`

## Particularidades

1. **No usa trimestre**: la columna `trimestre` queda NULL para este tipo. Solo `anio` distingue los informes.
2. **Form previo a generar:** solo `anio`.
3. **Contexto IA:** la clase `InformeAnualCopasst::getContextoBase()` agrega TODO el anio (4 trimestres en bloque) y ademas calcula:
   - Total reuniones del anio vs esperadas (12 si periodicidad mensual)
   - % asistencia promedio anual
   - Compromisos cerrados vs pendientes vs vencidos
   - Comparativo trimestre a trimestre (cantidad de reuniones, asistencia, compromisos)
   E inyecta todo como bloques separados al prompt.

## Secciones (10 — las 9 del trimestral + 1 comparativo)

| # | seccion_key | nombre | descripcion del prompt_ia |
|---|---|---|---|
| 1 | `resumen_ejecutivo` | Resumen Ejecutivo Anual | Sintesis anual: total reuniones, % asistencia anual, decisiones clave, % compromisos cumplidos. Maximo 2 parrafos. |
| 2 | `conformacion_comite` | Conformacion del COPASST | Composicion final del comite al cierre del anio + cambios durante el anio (ingresos, retiros). |
| 3 | `comparativo_trimestres` | Comparativo Trimestral | Tabla comparativa T1-T2-T3-T4: numero de reuniones, % asistencia, compromisos generados / cerrados, principales decisiones. Identificar tendencias. |
| 4 | `reuniones_realizadas` | Reuniones Realizadas en el Anio | Listado completo del anio (numero acta, fecha, modalidad, quorum). Total vs esperado segun periodicidad. |
| 5 | `asistencia` | Asistencia Anual | % asistencia anual por miembro y agregado. Identifica miembros que requieren reemplazo (asistencia critica sostenida). |
| 6 | `decisiones_votaciones` | Decisiones y Votaciones del Anio | Sintesis de las principales decisiones tomadas durante el anio extraidas de las actas. |
| 7 | `cumplimiento_cronograma` | Cumplimiento del Cronograma Anual | % cumplimiento global del cronograma esperado (12 reuniones/anio si es mensual). Justifica gaps. |
| 8 | `hallazgos` | Hallazgos del Anio | Hallazgos / no conformidades del anio agrupados por tema. |
| 9 | `recomendaciones_ia` | Recomendaciones del Consultor SST | **IA + editable.** Recomendaciones para el siguiente periodo en base al comportamiento anual. |
| 10 | `plan_accion_proximo` | Plan de Accion para el Proximo Anio | Lista priorizada de acciones, responsables, fechas para el siguiente periodo. |

## Firmantes

Iguales al trimestral (`responsable_sst` + `representante_legal`).

## Decisiones de diseno

- **Vive en la misma carpeta** (`conformacion_copasst`) que el trimestral. Diferentes botones, distintos tipos de documento, misma carpeta destino.
- **Codigo de carpeta:** `1.1.6` para `tbl_doc_plantilla_carpeta`.
- **Reusa la clase `InformeAnualCopasst` el mismo patron que `InformeTrimestralCopasst`** pero con consulta anual completa (sin filtro de fechas por trimestre).

## Archivos creados y modificados

Ver `InformeTrimestralCopasst.md` — los mismos archivos cubren ambos tipos. La diferencia es:
- `InformeAnualCopasst.php` (clase propia)
- Registro adicional en Factory
- Ruta y metodo controller adicional para vista previa anual
