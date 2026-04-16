# Módulo IPEVR GTC 45 — Arquitectura

> Documento oficial del módulo de Identificación de Peligros, Evaluación y Valoración de Riesgos basado en la Guía Técnica Colombiana 45 (GTC 45). Fuente de verdad para la implementación.

## 1. Contexto y motivación

Enterprise SST no tiene hoy un módulo IPEVR. Los consultores trabajan la matriz en el formato Excel `docs/FT-SST-035 Matriz IPEVR.xlsx` por fuera del aplicativo, lo que implica:

- Pérdida de trazabilidad y versionamiento.
- No se aprovecha el contexto del cliente ya capturado (`tbl_cliente_contexto_sst`).
- No se integra con el sistema de firmas electrónicas del aplicativo.
- Imposibilidad de reutilizar maestros (procesos, cargos, tareas) en otros módulos (PTA, indicadores).

**Objetivo:** Digitalizar el formato FT-SST-035 dentro del aplicativo permitiendo:

1. Diligenciamiento por consultor en **PC (tabla extensa)** y **PWA móvil (wizard fila-por-fila)** con autosave offline.
2. **Pre-poblado con IA** (gpt-4o-mini) usando el contexto del cliente como semilla.
3. **Tablas maestras reutilizables** (procesos/cargos/tareas/zonas) compartidas con otros módulos.
4. **Catálogos GTC 45** (ND/NE/NP/NC/NR + clasificación de peligros) como fuente única.
5. **Exportación** XLSX replicando FT-SST-035 y PDF landscape.
6. Integración con **versionamiento y firmas** existentes.

---

## 2. Estructura IPEVR — 22 columnas en 6 secciones

Extraída del Excel FT-SST-035:

| # | Sección | Columnas |
|---|---------|----------|
| 1 | Información proceso/actividad/tarea | Proceso · Zona o lugar · Actividad · Tarea · Rutinaria (S/N) · Cargos expuestos · N° expuestos |
| 2 | Identificación de peligros | Descripción · Clasificación (7 tipos) · Efectos posibles |
| 3 | Controles existentes | Fuente · Medio · Individuo |
| 4 | Evaluación del riesgo | ND · NE · **NP (calc)** · Interpretación NP · NC · **NR=NP×NC (calc)** · Interpretación NR (I-IV) · Aceptabilidad |
| 5 | Criterios | Peor consecuencia · Requisito legal |
| 6 | Medidas de intervención | Eliminación · Sustitución · Control ingeniería · Controles administrativos · EPP |

### Fórmulas (auto en frontend JS + validación backend)

- `NP = ND × NE`
  - ND: `MA=10 · A=6 · M=2 · B=0`
  - NE: `EC=4 · EF=3 · EO=2 · EE=1`
- `NR = NP × NC`
  - NC: `M=100 · MG=60 · G=25 · L=10`
- Interpretación NP/NR y aceptabilidad se derivan por rango desde los catálogos.

---

## 3. Decisiones de diseño

| # | Decisión | Aprobado |
|---|----------|----------|
| D1 | Plataforma: PC (tabla extensa) + PWA móvil (wizard multi-paso por fila) con autosave offline | ✅ |
| D2 | Procesos/Cargos/Tareas/Zonas como **maestros por cliente reutilizables** (nuevas tablas BD) | ✅ |
| D3 | **Semilla IA activa**: IA genera ~20-50 filas pre-diligenciadas al crear matriz | ✅ |
| D4 | Documentación-primero: este .md se escribe antes de la BD y el código | ✅ |

---

## 4. Modelo de datos

### 4.A Catálogos GTC 45 (globales, seed una sola vez)

| Tabla | Propósito | Registros clave |
|-------|-----------|-----------------|
| `tbl_gtc45_clasificacion_peligro` | 7 categorías | Biológico · Físico · Químico · Psicosocial · Biomecánico · Condiciones de seguridad · Fenómenos naturales |
| `tbl_gtc45_peligro_catalogo` | 50+ peligros específicos con FK a clasificación | Semilla desde `ContextoClienteController::getPeligrosDisponibles()` línea 456 |
| `tbl_gtc45_nivel_deficiencia` | ND | MA=10 · A=6 · M=2 · B=0 + descripción |
| `tbl_gtc45_nivel_exposicion` | NE | EC=4 · EF=3 · EO=2 · EE=1 + descripción |
| `tbl_gtc45_nivel_consecuencia` | NC | M=100 · MG=60 · G=25 · L=10 + daños personales |
| `tbl_gtc45_nivel_probabilidad` | Rangos NP → interpretación | MA:40-24 · A:20-10 · M:8-6 · B:4-2 |
| `tbl_gtc45_nivel_riesgo` | Rangos NR → nivel intervención (I-IV) | I:4000-600 · II:500-150 · III:120-40 · IV:≤20 + acción + aceptabilidad |

### 4.B Maestros por cliente (reutilizables)

| Tabla | Campos clave |
|-------|--------------|
| `tbl_procesos_cliente` | id · id_cliente · nombre_proceso · tipo (estratégico/misional/apoyo) · activo |
| `tbl_cargos_cliente` | id · id_cliente · id_proceso (FK) · nombre_cargo · num_ocupantes · activo |
| `tbl_tareas_cliente` | id · id_cliente · id_proceso · nombre_tarea · rutinaria (bool) · activo |
| `tbl_zonas_cliente` | id · id_cliente · id_sede (FK `tbl_cliente_sedes`) · nombre_zona · activo |

### 4.C Operacional IPEVR

| Tabla | Campos clave |
|-------|--------------|
| `tbl_ipevr_matriz` | id · id_cliente · version · fecha_creacion · fecha_actualizacion · estado (borrador/revisión/aprobada/vigente/historica) · elaborado_por · aprobado_por · id_consultor · snapshot_json |
| `tbl_ipevr_fila` | id · id_matriz (FK) · orden · id_proceso · id_zona · actividad · id_tarea · rutinaria · cargos_expuestos (JSON) · num_expuestos · id_peligro_catalogo · descripcion_peligro · id_clasificacion · efectos_posibles · control_fuente · control_medio · control_individuo · id_nd · id_ne · np · id_nc · nr · id_nivel_riesgo · peor_consecuencia · requisito_legal · medida_eliminacion · medida_sustitucion · medida_ing · medida_admin · medida_epp · origen_fila (ia/manual) · timestamps |
| `tbl_ipevr_control_cambios` | id · id_matriz · version · descripcion · fecha · usuario (replica patrón documentos SST) |
| `tbl_ipevr_firmas` | Reutiliza infraestructura de firmas existente (ver `firmas-sistema.md`) |

---

## 5. Arquitectura de componentes

### 5.A Patrones reutilizados

| Patrón | Origen | Uso en IPEVR |
|--------|--------|--------------|
| DataTable + modal edit | `app/Views/matriz_legal/index.php` + `MatrizLegalModel` | Vista PC: tabla con scroll horizontal + modal de edición |
| PWA layout + autosave | `app/Views/inspecciones/layout_pwa.php` + `public/js/offline_queue.js` | Vista móvil: manifest propio + autosave por fila |
| Contexto cliente | `ContextoClienteController::getContextoJson()` L231 + `getPeligrosDisponibles()` L456 | Fuente de semilla IA |
| Generación IA Tipo A | Patrón `secciones_ia` (ver memoria `modulo-3-partes-tipo-b.md`) | Endpoint `/ipevr/matriz/{id}/sugerir-ia` con gpt-4o-mini |
| Versionamiento + firmas | `docs/MODULO_NUMERALES_SGSST/05_VERSIONAMIENTO/ZZ_98_HISTORIAL_VERSIONES.md` · memoria `firmas-sistema.md` | Ciclo borrador → vigente con snapshot_json |
| Export PDF/Word/Excel | Memorias `pdf-estandar.md` · `word-estandar.md` | PDF landscape + XLSX réplica FT-SST-035 |

### 5.B Nuevos componentes

**Controllers**
- `app/Controllers/IpevrController.php` — CRUD matriz/filas, export, sugerir IA
- `app/Controllers/IpevrPwaController.php` — endpoints PWA (list, upsert, autosave, sync)
- `app/Controllers/MaestrosClienteController.php` — CRUD procesos/cargos/tareas/zonas

**Models**
- `app/Models/IpevrMatrizModel.php` · `IpevrFilaModel.php`
- `app/Models/Gtc45CatalogoModel.php` (lookups cacheados)
- `app/Models/ProcesoClienteModel.php` · `CargoClienteModel.php` · `TareaClienteModel.php` · `ZonaClienteModel.php`

**Views**
- `app/Views/ipevr/index.php` — lista matrices por cliente
- `app/Views/ipevr/editor_pc.php` — tabla extensa estilo Excel
- `app/Views/ipevr/editor_pwa.php` — wizard 6 pasos
- `app/Views/ipevr/fila_modal.php` — modal con pestañas (6 secciones)
- `app/Views/maestros_cliente/*.php`

**JS**
- `public/js/ipevr_calculadora.js` — cálculo NP/NR en vivo + lookup interpretación/aceptabilidad desde `window.GTC45_CATALOGO`
- `public/js/ipevr_pwa_queue.js` — extensión de `offline_queue.js` para colas por fila
- `public/manifest_ipevr.json`

**Libraries**
- `app/Libraries/IpevrIaSugeridor.php` — wrapper OpenAI para semilla
- `app/Libraries/IpevrExportXlsx.php` · `IpevrExportPdf.php`

**Rutas** (`app/Config/Routes.php`)

```
GET  /ipevr/cliente/(:num)              → listar matrices
GET  /ipevr/matriz/(:num)/editar        → editor PC
GET  /ipevr/matriz/(:num)/pwa           → editor PWA
POST /ipevr/matriz/crear                → nueva matriz
POST /ipevr/fila/upsert                 → crear/editar fila
POST /ipevr/fila/autosave               → autosave PWA
POST /ipevr/matriz/(:num)/sugerir-ia    → genera filas con IA
GET  /ipevr/matriz/(:num)/exportar/xlsx → export FT-SST-035
GET  /ipevr/matriz/(:num)/exportar/pdf  → export PDF landscape
POST /ipevr/matriz/(:num)/version       → nueva versión + snapshot
GET  /maestros-cliente/(:num)           → gestión maestros por cliente
```

---

## 6. Flujos UX

### Flujo 1 — Crear matriz nueva (PC)

1. Ficha cliente → botón "Matriz IPEVR GTC 45" → `/ipevr/cliente/{id}`.
2. "Nueva matriz" → modal pide nombre + versión inicial (001).
3. Sistema crea `tbl_ipevr_matriz` en estado `borrador` y muestra diálogo: **"¿Generar filas iniciales con IA desde el contexto del cliente?"** (Sí / No / Más tarde).
4. Si **Sí** → loader → `/ipevr/matriz/{id}/sugerir-ia` → backend compone prompt con contexto + peligros_identificados + sector + maestros → gpt-4o-mini devuelve JSON → inserción batch → redirect editor PC.
5. Consultor revisa/edita/borra filas. Puede disparar "Sugerir más filas con IA" en cualquier momento.

### Flujo 2 — Editor PC (tabla extensa)

- DataTable con columnas fijas (Proceso · Actividad · Tarea) y scroll horizontal para las restantes.
- Edición por modal con 6 pestañas = 6 secciones GTC 45.
- Autocomplete Proceso/Cargo/Tarea/Zona contra maestros cliente con botón "+ Crear".
- Cálculo NP y NR en vivo al seleccionar ND/NE/NC.
- Interpretación + aceptabilidad con color semáforo (verde/amarillo/naranja/rojo).

### Flujo 3 — PWA móvil (campo)

- `/ipevr/matriz/{id}/pwa` carga `editor_pwa.php` sobre `layout_pwa.php`.
- Lista de filas existentes (card por fila) + FAB "+" para nueva.
- Nueva fila → wizard 6 pasos (Sección 1 → 6) con Siguiente/Anterior.
- Autosave en cada paso vía `/ipevr/fila/autosave`.
- `ipevr_pwa_queue.js` gestiona offline: cola en IndexedDB, sync al volver online.
- Botón "Sincronizar" manual visible.

### Flujo 4 — Versionamiento y firmas

- `borrador` → `revisión` genera `snapshot_json`.
- `revisión` → `vigente` + registro en `tbl_ipevr_control_cambios` + firmas (elaborado/revisado/aprobado) con sistema existente.
- Versiones anteriores quedan en estado `historica` accesibles desde timeline.

---

## 7. Semilla IA — Contrato del prompt

**Entrada:** contexto cliente (JSON) + sector + nivel riesgo ARL + `peligros_identificados` + (opcional) procesos/cargos existentes.

**Salida esperada (JSON estricto):**

```json
{
  "filas": [
    {
      "proceso": "Administrativo",
      "zona": "Oficina contabilidad",
      "actividad": "Gestión contable",
      "tarea": "Digitación de facturas",
      "rutinaria": true,
      "cargos_expuestos": ["Auxiliar contable"],
      "num_expuestos": 2,
      "peligro_descripcion": "Movimientos repetitivos de miembros superiores",
      "clasificacion": "biomecanico",
      "efectos_posibles": "Síndrome del túnel carpiano, tendinitis",
      "control_fuente": "Ninguno",
      "control_medio": "Ninguno",
      "control_individuo": "Pausas activas",
      "nd": "M", "ne": "EC", "nc": "L",
      "peor_consecuencia": "ILT",
      "requisito_legal": "Resolución 2844/2007",
      "medidas": {
        "eliminacion": "",
        "sustitucion": "",
        "ingenieria": "Sillas ergonómicas",
        "administrativo": "Pausas activas programadas",
        "epp": "Muñequeras"
      }
    }
  ]
}
```

**Validación backend:** rechaza ND/NE/NC/clasificación desconocidos contra catálogos. Filas se marcan `origen_fila='ia'`.

**Costo estimado:** ~$0.02 por matriz con gpt-4o-mini + prompt caching.

---

## 8. Exportación

- **XLSX** (PhpSpreadsheet): réplica FT-SST-035 con 5 hojas — Matriz · Tablas evaluación · Control cambios · Instructivo · Firmas. Merge de celdas de cabecera igual al original.
- **PDF**: orientación landscape A3, estilos de `pdf-estandar.md`, escalado fit-page-width.

---

## 9. Archivos críticos

### Nuevos

- Controllers: `IpevrController.php` · `IpevrPwaController.php` · `MaestrosClienteController.php`
- Models: `IpevrMatrizModel.php` · `IpevrFilaModel.php` · `Gtc45CatalogoModel.php` · `ProcesoClienteModel.php` · `CargoClienteModel.php` · `TareaClienteModel.php` · `ZonaClienteModel.php`
- Views: `app/Views/ipevr/{index,editor_pc,editor_pwa,fila_modal}.php` · `app/Views/maestros_cliente/*.php`
- JS: `public/js/ipevr_calculadora.js` · `public/js/ipevr_pwa_queue.js`
- `public/manifest_ipevr.json`
- Libraries: `IpevrIaSugeridor.php` · `IpevrExportXlsx.php` · `IpevrExportPdf.php`
- Scripts BD CLI en la convención del proyecto (LOCAL → PRODUCCIÓN)
- `docs/MODULO_IPEVR_GTC45/GUIA_USO.md` · `CATALOGO_GTC45.md` · `PROMPT_IA.md` · `TROUBLESHOOTING.md`

### Modificados

- `app/Config/Routes.php` — rutas IPEVR
- Ficha cliente — botón acceso
- Menú principal — entrada IPEVR

### Reutilizados (no tocar sin razón)

- `ContextoClienteController.php:231,456`
- `app/Views/inspecciones/layout_pwa.php`
- `public/js/offline_queue.js`
- `MatrizLegalModel.php` + `app/Views/matriz_legal/index.php`
- Sistema firmas y versionamiento

---

## 10. Fases de implementación

| Fase | Objetivo |
|------|----------|
| 0 | **Documentación primero**: este archivo + sub-docs del módulo |
| 1 | BD catálogos GTC 45 + seed (scripts LOCAL → PRODUCCIÓN) |
| 2 | Maestros por cliente (procesos/cargos/tareas/zonas) con CRUD web |
| 3 | CRUD matriz PC: editor tabla extensa + modal 6 pestañas + calculadora JS |
| 4 | Semilla IA: `IpevrIaSugeridor` + endpoint + integración frontend |
| 5 | PWA: layout + wizard + cola offline |
| 6 | Exportación XLSX/PDF (réplica FT-SST-035) |
| 7 | Versionamiento y firmas (integración con sistema existente) |
| 8 | Pruebas end-to-end con cliente real (id 12) |

---

## 11. Verificación end-to-end

1. **BD:** script migración corre limpio en LOCAL → query a `tbl_gtc45_nivel_riesgo` devuelve 4 filas (I-IV).
2. **Maestros cliente:** crear proceso/cargo/tarea desde `/maestros-cliente/12` → aparecen en autocomplete del editor IPEVR.
3. **Matriz IA:** desde `/ipevr/cliente/12` crear matriz → "Sugerir con IA" → ≥15 filas insertadas con `origen_fila='ia'` y NP/NR calculados.
4. **Editor PC:** cambiar ND de M a A → NP y NR se recalculan en vivo → color cambia.
5. **PWA:** `/ipevr/matriz/{id}/pwa` en tablet → modo offline → crear fila → reconectar → fila aparece en PC.
6. **Export:** descargar XLSX → comparar visualmente contra `FT-SST-035 Matriz IPEVR.xlsx`.
7. **Versionamiento:** pasar matriz a `vigente` → firmar → `tbl_ipevr_control_cambios` tiene registro v001.
8. **Regresión:** `ContextoClienteController::getPeligrosDisponibles()` sigue funcionando.

---

## 12. Riesgos y notas

- **Complejidad PWA:** 22 columnas por fila obliga a wizard multi-paso → mitigar con autosave agresivo y "guardar borrador" en cualquier paso.
- **Costo IA:** ~$0.02 por matriz. Aceptable. Usar prompt caching.
- **Importador XLSX** para migrar matrices existentes: fuera de alcance inicial; evaluar en fase posterior.
- **Convención prefijo de tablas:** confirmar `tbl_` antes de crear scripts BD.
- **Nomenclatura:** `tipo` en snake_case consistente con resto del proyecto.
