# Informe Anual de Gestion del COCOLAB (Comite de Convivencia Laboral)

## Metadata
- **tipo_documento:** `informe_anual_cocolab`
- **estandar:** 1.1.8
- **flujo:** `secciones_ia`
- **categoria:** `informes_comites`
- **tipoCarpetaFases:** `comite_convivencia`
- **codigo_plantilla:** `INF-COC-A`
- **clase PHP:** `InformeAnualCocolab`
- **firmantes:** `responsable_sst` + `representante_legal`

## URLs
- Generacion: `/documentos/generar/informe_anual_cocolab/{id_cliente}?anio={anio}`
- Vista: `/documentos-sst/{id_cliente}/informe-anual-cocolab/{anio}`

## Secciones (10)

| # | seccion_key | nombre | descripcion |
|---|---|---|---|
| 1 | `resumen_ejecutivo` | Resumen Ejecutivo Anual | Sintesis anual del comite. |
| 2 | `conformacion_comite` | Conformacion del COCOLAB | Composicion final + cambios del anio. Cumplimiento Resolucion 0652/2012 y 1356/2012. |
| 3 | `comparativo_trimestres` | Comparativo Trimestral | T1-T4: reuniones, asistencia, casos atendidos, compromisos. |
| 4 | `reuniones_realizadas` | Reuniones Realizadas en el Anio | Tabla anual completa. |
| 5 | `asistencia` | Asistencia Anual | % anual por miembro. |
| 6 | `casos_atendidos` | Casos / Quejas Atendidos en el Anio | Agregado anonimo de casos del anio. Confidencialidad. |
| 7 | `cumplimiento_cronograma` | Cumplimiento del Cronograma Anual | % global. |
| 8 | `hallazgos` | Hallazgos del Anio | Tendencias, focos, acciones preventivas sugeridas. |
| 9 | `recomendaciones_ia` | Recomendaciones del Consultor SST | IA + editable. |
| 10 | `plan_accion_proximo` | Plan de Accion para el Proximo Anio | Acciones priorizadas. |

## Decisiones
- Reusa `tbl_documentos_sst` + `tbl_doc_versiones_sst` (no nuevas tablas).
- Confidencialidad estricta en prompts.
- Plantilla `INF-COC-A`, mapeo carpeta `1.1.8`.
