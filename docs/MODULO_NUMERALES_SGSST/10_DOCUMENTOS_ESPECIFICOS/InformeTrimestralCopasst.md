# Informe Trimestral de Gestion del COPASST

## Metadata
- **tipo_documento:** `informe_trimestral_copasst`
- **estandar:** 1.1.6 (vive en la carpeta de Conformacion COPASST)
- **flujo:** `secciones_ia` (estructura Tipo A: 1 parte, sin PTA editable previo)
- **categoria:** `informes_comites`
- **tipoCarpetaFases:** `conformacion_copasst` (carpeta existente, no se duplica)
- **codigo_plantilla:** `INF-COP-T`
- **clase PHP:** `InformeTrimestralCopasst` (sobrescribe `getContextoBase()` para inyectar datos reales del comite consultados de las tablas del modulo /actas/20)
- **firmantes:** `responsable_sst` (Elaboro) + `representante_legal` (Aprobo)

## URLs
- Generacion (snake_case): `/documentos/generar/informe_trimestral_copasst/{id_cliente}?anio={anio}&trimestre={1-4}`
- Vista web (kebab-case): `/documentos-sst/{id_cliente}/informe-trimestral-copasst/{anio}?trimestre={1-4}`

## Particularidades vs un Tipo A puro

1. **Trimestre:** se persiste en una nueva columna `tbl_documentos_sst.trimestre TINYINT NULL` (NULL para los demas docs). Permite tener 4 informes del mismo `(id_cliente, tipo_documento, anio)` distinguidos por trimestre.
2. **Form previo a generar:** ademas de `anio`, pide `trimestre` (1-4). El consultor elige al hacer click en "Generar Informe Trimestral".
3. **Contexto IA:** la clase PHP `InformeTrimestralCopasst::getContextoBase()` consulta:
   - `tbl_comites` JOIN `tbl_tipos_comite` (codigo='COPASST') — comite vigente del cliente
   - `tbl_comite_miembros` — miembros activos en el rango del trimestre
   - `tbl_actas` filtrado por `id_comite`, `anio`, y `fecha_reunion BETWEEN inicio_trim AND fin_trim`
   - `tbl_acta_asistentes` agregado por miembro (% asistencia)
   - `tbl_acta_compromisos` filtrado por id_acta IN (actas del trimestre)
   E inyecta todo eso al prompt como bloque "DATOS REALES DEL COMITE EN EL TRIMESTRE X".

## Secciones (9)

| # | seccion_key | nombre | tipo_contenido | descripcion del prompt_ia |
|---|---|---|---|---|
| 1 | `resumen_ejecutivo` | Resumen Ejecutivo | texto | Sintesis del trimestre: cantidad de reuniones, % asistencia promedio, principales decisiones, % cumplimiento compromisos. Maximo 2 parrafos. |
| 2 | `conformacion_comite` | Conformacion del COPASST | texto | Lista los miembros principales y suplentes vigentes (representacion empleador / trabajador), presidente, secretario. Vigencias y movimientos en el trimestre. |
| 3 | `reuniones_realizadas` | Reuniones Realizadas | texto | Tabla con: numero acta, fecha, modalidad, hora_inicio/fin, lugar, quorum (alcanzado o no). Indicar la frecuencia esperada (mensual segun Decreto 1072/2015) vs realizada. |
| 4 | `asistencia` | Asistencia | texto | % de asistencia por miembro y promedio del trimestre. Listar ausencias con o sin justificacion. Identificar miembros con asistencia critica (<50%). |
| 5 | `decisiones_votaciones` | Decisiones y Votaciones | texto | Sintesis de las decisiones tomadas en el trimestre extraidas de `desarrollo` y `conclusiones` de las actas. |
| 6 | `cumplimiento_cronograma` | Cumplimiento del Cronograma | texto | Compara reuniones esperadas (segun periodicidad del comite, normalmente mensual) vs realizadas. % cumplimiento. Justifica gaps si los hay. |
| 7 | `hallazgos` | Hallazgos Identificados | texto | Lista los hallazgos / observaciones / no conformidades surgidas en las reuniones del trimestre. Ordenadas por criticidad. |
| 8 | `recomendaciones_ia` | Recomendaciones del Consultor SST | texto | **IA + editable.** En base a los datos del trimestre, IA propone recomendaciones concretas: mejorar asistencia, cerrar compromisos vencidos, reforzar capacitacion, ajustar cronograma, etc. El consultor puede editar libremente. |
| 9 | `plan_accion_proximo` | Compromisos / Plan de Accion Proximo Trimestre | texto | Lista de acciones, responsables y fechas para el siguiente trimestre, basadas en los compromisos pendientes y las recomendaciones. |

## Firmantes

| firmante_tipo | rol_display | columna_encabezado | orden | mostrar_licencia |
|---|---|---|---|---|
| `responsable_sst` | Elaboro | Elaboro / Responsable del SG-SST | 1 | 1 |
| `representante_legal` | Aprobo | Aprobo / Representante Legal | 2 | 0 |

## Decisiones de diseno

- **Vive en la carpeta 1.1.6 (`conformacion_copasst`)** porque el usuario asi lo pidio. La carpeta gana 2 botones IA (trimestral + anual) sin perder el "Sistema de Conformacion" ni el "Adjuntar Soporte" que ya tenia.
- **Para distinguir 4 trimestrales del mismo anio**, se agrega columna `trimestre` a `tbl_documentos_sst`. NULL para los 31+ docs existentes (no rompe nada).
- **No se hardcodean prompts en PHP.** La clase PHP solo lee datos del comite y los inyecta en `getContextoBase()`. Los prompts viven en `tbl_doc_secciones_config.prompt_ia`.
- **Sin "regenerar solo recomendaciones"**: cada seccion se edita igual que cualquier Tipo A (TinyMCE + Guardar + Aprobar).

## Archivos creados y modificados (se llenan al terminar)

- [ ] `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/InformeTrimestralCopasst.md` (este)
- [ ] `app/SQL/agregar_informes_copasst.php`
- [ ] `app/Libraries/DocumentosSSTTypes/InformeTrimestralCopasst.php`
- [ ] `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` (registrar)
- [ ] `app/Config/Routes.php` (ruta vista previa)
- [ ] `app/Controllers/DocumentosSSTController.php` (metodo `informeTrimestralCopasst()` + soporte trimestre en endpoint generar)
- [ ] `app/Controllers/DocumentacionController.php` (filtro doble en tabla)
- [ ] `app/Views/documentacion/_tipos/conformacion_copasst.php` (agregar 2 botones IA + tabla informes)
- [ ] `app/Views/documentacion/_components/acciones_documento.php` (mapaRutas + urlEditar)
- [ ] `app/Views/documentacion/_components/tabla_documentos_sst.php` (agregar tipo)
