# Procedimiento para la Realizacion de la Auditoria Anual del SG-SST

## Metadata
| Campo | Valor |
|-------|-------|
| `tipo_documento` | `procedimiento_auditoria_anual` |
| Nombre | Procedimiento para la Realizacion de la Auditoria Anual del SG-SST |
| Estandar | 6.1.2 |
| Flujo | `secciones_ia` (Tipo A - 1 parte) |
| Categoria | procedimientos |
| Icono | bi-clipboard-check |
| Carpeta BD | 6.1.2 (ya existe en SP nivel 60) |

## Normativa Aplicable
- **Decreto 1072 de 2015**, Art. 2.2.4.6.29 y 2.2.4.6.30: Auditoría de cumplimiento del SG-SST
- **Resolucion 0312 de 2019**, Estandar 6.1.2: La empresa adelanta auditoria por lo menos una vez al ano
- **Norma ISO 19011:2018**: Directrices para auditoría de sistemas de gestión (referencia)

## Decision: Tipo A (secciones_ia)
Este documento NO necesita PTA ni indicadores como fuente de datos.
Solo usa contexto del cliente (nombre, NIT, ARL, actividad economica, etc.).
La auditoria anual es un procedimiento que describe COMO se ejecuta la auditoria,
no un programa con actividades planificadas.

## Secciones (10)

| # | Nombre | seccion_key | Descripcion |
|---|--------|-------------|-------------|
| 1 | Objetivo | `objetivo` | Proposito del procedimiento |
| 2 | Alcance | `alcance` | A quien y que cubre |
| 3 | Definiciones | `definiciones` | Terminos clave de auditoria |
| 4 | Marco Legal | `marco_legal` | Normativa colombiana aplicable |
| 5 | Responsabilidades | `responsabilidades` | Roles y funciones |
| 6 | Planificacion de la Auditoria | `planificacion_auditoria` | Plan, programa, criterios previos |
| 7 | Ejecucion de la Auditoria | `ejecucion_auditoria` | Paso a paso de la auditoria |
| 8 | Criterios y Metodologia de Auditoria | `criterios_metodologia` | Criterios de evaluacion, listas de verificacion |
| 9 | Informe de Resultados | `informe_resultados` | Estructura del informe, hallazgos, no conformidades |
| 10 | Seguimiento y Acciones Correctivas | `seguimiento_acciones` | Plan de accion, plazos, verificacion de cierre |

## Firmantes (2)
| Orden | Tipo | Rol Display | Columna Encabezado | Licencia |
|-------|------|-------------|-------------------|----------|
| 1 | `responsable_sst` | Elaboro | Elaboro / Responsable del SG-SST | Si |
| 2 | `representante_legal` | Aprobo | Aprobo / Representante Legal | No |

## Carpeta BD
- Codigo: `6.1.2`
- Ya existe en `sp_04_generar_carpetas_por_nivel.sql` (lineas 394-398, nivel 60)
- Se necesita agregar mapeo en `tbl_doc_plantilla_carpeta`: PRC-AUD → 6.1.2

## Archivos Creados
- `app/SQL/agregar_procedimiento_auditoria_anual.php` - Script BD
- `app/Libraries/DocumentosSSTTypes/ProcedimientoAuditoriaAnual.php` - Clase PHP
- `app/Views/documentacion/_tipos/auditoria_anual.php` - Vista carpeta

## Archivos Modificados
- `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` - Registro en $tiposRegistrados
- `app/Config/Routes.php` - Ruta GET vista previa
- `app/Controllers/DocumentosSSTController.php` - Metodo procedimientoAuditoriaAnual()
- `app/Controllers/DocumentacionController.php` - determinarTipoCarpetaFases + carpeta array + filtro
- `app/Views/documentacion/_components/acciones_documento.php` - mapaRutas + urlEditar
- `app/Views/documentacion/_components/tabla_documentos_sst.php` - tiposConTabla

## URLs
- Generacion: `/documentos/generar/procedimiento_auditoria_anual/18`
- Vista previa: `/documentos-sst/18/procedimiento-auditoria-anual/2026`
