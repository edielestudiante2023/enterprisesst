# Reglamento de Higiene y Seguridad Industrial

## Identificación

| Campo | Valor |
|-------|-------|
| `tipo_documento` | `reglamento_higiene_seguridad` |
| Estándar | `1.2.4` (BD) / `1.1.2` (Resolución 0312/2019) |
| Carpeta BD | `1.2.4` |
| Flujo | `secciones_ia` (Tipo A — 1 parte) |
| Firmantes | `representante_legal`, `responsable_sst` |
| Clase PHP | `ReglamentoHigieneSeguridadIndustrial.php` |
| Registro Factory | EXPLÍCITO (nombre clase tiene "Industrial" extra, no coincide con snake_to_pascal) |

## Decisión de Carpeta

Se asignó a carpeta **1.2.4** (igual que otros documentos obligatorios estándar 1.x).
El campo `estandar` en `tbl_doc_tipo_configuracion` = `'1.2.4'`.
El estándar normativo real es **1.1.2** de la Resolución 0312/2019 (se documenta en la clase PHP).

## Referencia Normativa

- **Código Sustantivo del Trabajo, arts. 349–352**: Obligatorio para empresas con ≥10 trabajadores permanentes
- **Resolución 1016/1989**: Organización y funcionamiento de los programas de salud ocupacional
- **Decreto 1072/2015**: Decreto Único Reglamentario del Sector Trabajo
- **Resolución 0312/2019, estándar 1.1.2**: Estándares Mínimos del SG-SST

## Secciones (11 secciones — fuente real: `tbl_doc_secciones_config`)

| # | Key | Nombre |
|---|-----|--------|
| 1 | `prescripciones_generales` | Prescripciones Generales |
| 2 | `obligaciones_empleador` | Obligaciones del Empleador |
| 3 | `obligaciones_trabajadores` | Obligaciones de los Trabajadores |
| 4 | `higiene_industrial` | Medidas de Higiene Industrial |
| 5 | `seguridad_industrial` | Medidas de Seguridad Industrial |
| 6 | `uso_equipos_maquinaria` | Normas para Uso de Equipos y Maquinaria |
| 7 | `elementos_proteccion_personal` | Elementos de Protección Personal |
| 8 | `senalizacion_demarcacion` | Señalización y Demarcación |
| 9 | `orden_limpieza` | Orden y Limpieza |
| 10 | `procedimiento_accidente` | Procedimiento ante Accidente |
| 11 | `sanciones` | Sanciones por Incumplimiento |

> **IMPORTANTE:** Los prompts IA están en `tbl_doc_secciones_config.prompt_ia`.
> NO hardcodear prompts en PHP. Editables desde `/listSeccionesConfig`.

## Firmantes (fuente real: `tbl_doc_firmantes_config`)

| Orden | `firmante_tipo` | Descripción |
|-------|-----------------|-------------|
| 1 | `representante_legal` | Representante Legal de la empresa |
| 2 | `responsable_sst` | Responsable del SG-SST |

## URLs

| Contexto | URL |
|----------|-----|
| Generación IA | `/documentos/generar/reglamento_higiene_seguridad/{id_cliente}` |
| Vista previa | `/documentos-sst/{id_cliente}/reglamento-higiene-seguridad/{anio}` |

## Archivos Implementados

| Archivo | Estado |
|---------|--------|
| `app/Libraries/DocumentosSSTTypes/ReglamentoHigieneSeguridadIndustrial.php` | ✅ |
| `app/Libraries/DocumentosSSTFactory.php` (registro explícito) | ✅ |
| BD `tbl_doc_tipo_configuracion` | ✅ LOCAL + PRODUCCIÓN |
| BD `tbl_doc_secciones_config` (11 secciones) | ✅ LOCAL + PRODUCCIÓN |
| BD `tbl_doc_firmantes_config` (2 firmantes) | ✅ LOCAL + PRODUCCIÓN |
| BD `tbl_doc_plantilla_carpeta` (REG-HSI → 1.2.4) | ✅ LOCAL + PRODUCCIÓN |
| Carpeta 1.2.4 para todos los clientes | ✅ LOCAL + PRODUCCIÓN |
| `app/Config/Routes.php` (ruta vista previa) | ✅ |
| `app/Controllers/DocumentosSSTController.php` (método) | ✅ |

## Decisiones de Diseño

- **Tipo A** (no Tipo B): El reglamento no depende de PTA ni indicadores, se genera
  100% a partir del contexto del cliente (actividad económica, nivel de riesgo, etc.)
- El `getContenidoEstatico()` de la clase PHP es el fallback cuando la IA no ha generado
  la sección aún (solo se usa si `contenido_secciones` está vacío en BD)
- Nombre de clase deliberadamente más largo que `tipo_documento` para reflejar
  el nombre oficial del documento. El Factory tiene registro explícito para mapear.
