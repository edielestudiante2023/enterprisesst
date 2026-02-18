# Plan de Prevención, Preparación y Respuesta ante Emergencias

## Identificación

| Campo | Valor |
|-------|-------|
| `tipo_documento` | `plan_emergencias` |
| Estándar | `5.1.1` |
| Carpeta BD | `5.1.1` (o carpeta asignada al crear) |
| Flujo | `secciones_ia` (Tipo A — 1 parte) |
| Firmantes | `responsable_sst`, `representante_legal` |
| Clase PHP | `PlanEmergencias.php` |
| Registro Factory | Auto-detección OK (PlanEmergencias → plan_emergencias) |

## Referencia Normativa

- **Resolución 0312/2019, estándar 5.1.1**: Plan de prevención, preparación y respuesta ante emergencias
- **Decreto 1072/2015, art. 2.2.4.6.25**: Prevención, preparación y respuesta ante emergencias
- **Ley 1523/2012**: Política Nacional de Gestión del Riesgo de Desastres
- **Resolución 256/2014**: Conformación de brigadas de emergencia
- **GTC 45**: Identificación de peligros y análisis de vulnerabilidad
- **NTC 1410, NTC 3807**: Señalización de emergencias

## Secciones (12 secciones — fuente real: `tbl_doc_secciones_config`)

| # | Key | Nombre |
|---|-----|--------|
| 1 | `objetivo_alcance` | Objetivo y Alcance |
| 2 | `marco_legal` | Marco Legal |
| 3 | `definiciones` | Definiciones |
| 4 | `identificacion_amenazas` | Identificación de Amenazas |
| 5 | `analisis_vulnerabilidad` | Análisis de Vulnerabilidad |
| 6 | `organizacion_brigadas` | Organización para Emergencias (Brigadas) |
| 7 | `procedimientos_emergencia` | Procedimientos de Emergencia |
| 8 | `plan_evacuacion` | Plan de Evacuación |
| 9 | `comunicaciones_emergencia` | Comunicaciones de Emergencia |
| 10 | `equipos_recursos` | Equipos y Recursos |
| 11 | `capacitacion_simulacros` | Capacitación y Simulacros |
| 12 | `investigacion_post_emergencia` | Investigación Post-Emergencia |

> **IMPORTANTE:** Los prompts IA están en `tbl_doc_secciones_config.prompt_ia`.
> NO hardcodear prompts en PHP. Editables desde `/listSeccionesConfig`.

## Firmantes (fuente real: `tbl_doc_firmantes_config`)

| Orden | `firmante_tipo` | Descripción |
|-------|-----------------|-------------|
| 1 | `responsable_sst` | Responsable del SG-SST |
| 2 | `representante_legal` | Representante Legal de la empresa |

## URLs

| Contexto | URL |
|----------|-----|
| Generación IA | `/documentos/generar/plan_emergencias/{id_cliente}` |
| Vista previa | `/documentos-sst/{id_cliente}/plan-emergencias/{anio}` |

## Archivos Implementados

| Archivo | Estado |
|---------|--------|
| `app/Libraries/DocumentosSSTTypes/PlanEmergencias.php` | ✅ |
| `app/Libraries/DocumentosSSTFactory.php` (auto-detección) | ✅ |
| BD `tbl_doc_tipo_configuracion` | ✅ LOCAL + PRODUCCIÓN |
| BD `tbl_doc_secciones_config` (12 secciones) | ✅ LOCAL + PRODUCCIÓN |
| BD `tbl_doc_firmantes_config` (2 firmantes) | ✅ LOCAL + PRODUCCIÓN |
| `app/Config/Routes.php` (ruta vista previa) | ✅ |
| `app/Controllers/DocumentosSSTController.php` (método) | ✅ |

## Decisiones de Diseño

- **Tipo A** (no Tipo B): El plan de emergencias se genera a partir del contexto del
  cliente (actividad económica, ubicación, nivel de riesgo, cantidad de trabajadores).
  No depende de PTA ni indicadores.
- El `getContenidoEstatico()` sirve de fallback genérico mientras la IA no ha generado
  el contenido personalizado para el cliente.
- 12 secciones cubre el ciclo completo: identificación de amenazas → respuesta →
  investigación post-emergencia, siguiendo la metodología del estándar 5.1.1.
- El análisis de vulnerabilidad usa la metodología de 3 elementos (personas, recursos,
  sistemas) acorde con GTC 45.
- La sección de simulacros cumple el requisito mínimo de 1 simulacro anual (Res. 0312/2019).
