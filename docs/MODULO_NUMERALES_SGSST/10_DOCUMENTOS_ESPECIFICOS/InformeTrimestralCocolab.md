# Informe Trimestral de Gestion del COCOLAB (Comite de Convivencia Laboral)

## Metadata
- **tipo_documento:** `informe_trimestral_cocolab`
- **estandar:** 1.1.8 (vive en la carpeta de Conformacion Comite de Convivencia)
- **flujo:** `secciones_ia`
- **categoria:** `informes_comites`
- **tipoCarpetaFases:** `comite_convivencia` (carpeta existente, no se duplica)
- **codigo_plantilla:** `INF-COC-T`
- **clase PHP:** `InformeTrimestralCocolab` (sobrescribe `getContextoBase()` para inyectar datos reales del comite COCOLAB consultados de las tablas del modulo /actas)
- **firmantes:** `responsable_sst` (Elaboro) + `representante_legal` (Aprobo)

## Particularidades vs COPASST
- Misma arquitectura: trimestre persistido en columna `tbl_documentos_sst.trimestre`.
- Filtra por `tbl_tipos_comite.codigo = 'COCOLAB'` en lugar de COPASST.
- Prompts referencian normativa especifica del Comite de Convivencia Laboral:
  - **Resolucion 0652 de 2012** (conformacion del COCOLAB).
  - **Resolucion 1356 de 2012** (modifica la 0652).
  - **Resolucion 3461 de 2025** (prevencion y atencion del acoso laboral, sexual, violencias).
  - **Ley 1010 de 2006** (acoso laboral).
- Periodicidad esperada: trimestral por defecto del COCOLAB, vs mensual del COPASST. La IA debe usar el campo `periodicidad_dias` real del tipo en BD.

## URLs
- Generacion: `/documentos/generar/informe_trimestral_cocolab/{id_cliente}?anio={anio}&trimestre={1-4}`
- Vista web: `/documentos-sst/{id_cliente}/informe-trimestral-cocolab/{anio}?trimestre={1-4}`

## Secciones (9)

| # | seccion_key | nombre | descripcion del prompt_ia |
|---|---|---|---|
| 1 | `resumen_ejecutivo` | Resumen Ejecutivo | Sintesis del trimestre (reuniones, asistencia, casos atendidos, compromisos). |
| 2 | `conformacion_comite` | Conformacion del COCOLAB | Composicion: representantes empleador/trabajador, presidente, secretario, vigencia. Cumplimiento Resolucion 0652/2012 y 1356/2012. |
| 3 | `reuniones_realizadas` | Reuniones Realizadas | Tabla con actas, fechas, modalidad, quorum, tema general. |
| 4 | `asistencia` | Asistencia | % asistencia por miembro y promedio del trimestre. |
| 5 | `casos_atendidos` | Casos / Quejas Atendidos | Casos atendidos en el trimestre (sin nombres, anonimo): tipo, area, estado, conciliacion alcanzada o derivada. |
| 6 | `cumplimiento_cronograma` | Cumplimiento del Cronograma | Reuniones esperadas vs realizadas. |
| 7 | `hallazgos` | Hallazgos / Tendencias | Hallazgos del periodo: tendencias de quejas, areas con mayor incidencia, tipos de conducta reportados. Con confidencialidad. |
| 8 | `recomendaciones_ia` | Recomendaciones del Consultor SST | IA + editable. |
| 9 | `plan_accion_proximo` | Compromisos / Plan de Accion del Proximo Trimestre | Acciones para el siguiente trimestre. |

## Firmantes
- `responsable_sst` (Elaboro)
- `representante_legal` (Aprobo)

## Decisiones de diseno
- **Confidencialidad**: los prompts instruyen explicitamente a la IA a NO mencionar nombres de quejosos / denunciados, areas individualizables ni detalles que rompan la reserva del proceso. Solo agregados.
- Vive en la carpeta `1.1.8` con la conformacion existente. Se agregan 2 botones IA al card de la carpeta sin perder lo que ya hay.
