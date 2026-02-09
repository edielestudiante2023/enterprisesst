# Sistema de Editores - Configuracion de Acciones de Documento

## Descripcion General

El componente `acciones_documento.php` controla los botones de accion que aparecen en cada fila de documento en las tablas de documentos SST. **Para que un documento tenga el boton de Editar (lapiz amarillo), debe estar configurado en DOS lugares** dentro de este componente.

---

## 1. Ubicacion del Componente

```
app/Views/documentacion/_components/acciones_documento.php
```

---

## 2. Variables Requeridas

| Variable | Tipo | Descripcion |
|----------|------|-------------|
| `$docSST` | array | Datos del documento SST (id_documento, tipo_documento, anio, etc.) |
| `$cliente` | array | Datos del cliente (id_cliente) |

---

## 3. Botones de Accion Disponibles

| Boton | Icono | Clase | Descripcion | Siempre visible |
|-------|-------|-------|-------------|-----------------|
| PDF | `bi-file-earmark-pdf` | `btn-danger` | Descargar PDF generado | Si |
| PDF Firmado | `bi-patch-check-fill` | `btn-outline-danger` | Ver PDF firmado publicado | Solo si existe `archivo_pdf` |
| Ver | `bi-eye` | `btn-outline-primary` | Ver documento en vista previa | Si |
| Editar | `bi-pencil` | `btn-outline-warning` | Ir al editor del documento | Solo si esta configurado |
| Firmas | `bi-pen` | `btn-outline-success` | Ver estado de firmas | Si (docs con firma electronica) |
| Publicar | `bi-cloud-upload` | `btn-outline-dark` | Publicar nueva version | Si (docs con firma electronica) |

---

## 4. Configuracion del Boton VER (Mapa de Rutas)

El boton "Ver" se configura en el array `$mapaRutas`:

```php
$mapaRutas = [
    'tipo_documento' => 'ruta-slug/' . $docSST['anio'],
    // Ejemplos:
    'programa_capacitacion' => 'programa-capacitacion/' . $docSST['anio'],
    'politica_sst_general' => 'politica-sst-general/' . $docSST['anio'],
    'plan_objetivos_metas' => 'plan-objetivos-metas/' . $docSST['anio'],
];
```

**URL resultante:**
```
/documentos-sst/{id_cliente}/ruta-slug/{anio}
```

---

## 5. Configuracion del Boton EDITAR (Cadena de elseif)

El boton "Editar" se configura en la cadena de `elseif`:

```php
if ($tipoDoc === 'programa_capacitacion') {
    $urlEditar = base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'politica_sst_general') {
    $urlEditar = base_url('documentos/generar/politica_sst_general/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
} elseif ($tipoDoc === 'otro_tipo') {
    $urlEditar = base_url('documentos/generar/otro_tipo/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
// Si $urlEditar queda null, NO se muestra el boton
```

**URL patron comun:**
```
/documentos/generar/{tipo_documento}/{id_cliente}?anio={anio}
```

**Excepcion (presupuesto_sst):**
```
/documentos-sst/presupuesto/{id_cliente}/{anio}
```

---

## 6. PROBLEMA COMUN: Documento sin Boton Editar

### Sintoma
Un documento aparece sin el boton de editar (lapiz amarillo) mientras que otros documentos si lo tienen.

### Causa
El `tipo_documento` no esta agregado a la cadena de `elseif` que define `$urlEditar`.

### Solucion
Agregar el tipo a la cadena de elseif:

```php
} elseif ($tipoDoc === 'mi_nuevo_tipo') {
    $urlEditar = base_url('documentos/generar/mi_nuevo_tipo/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
```

---

## 7. Checklist para Nuevo Tipo con Editor

```
[ ] 1. Agregar al array $mapaRutas (para boton VER)
[ ] 2. Agregar elseif con $urlEditar (para boton EDITAR)
[ ] 3. Crear ruta en Routes.php para la vista del documento
[ ] 4. Crear ruta en Routes.php para el editor (generador)
[ ] 5. Crear metodo en DocumentosSSTController para la vista
[ ] 6. Crear o reutilizar vista del generador
```

---

## 8. Estructura Completa del Componente

```
┌─────────────────────────────────────────────────────────────────┐
│ acciones_documento.php                                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Obtener $tipoDoc del documento                              │
│                                                                 │
│  2. MAPA DE RUTAS (array)                                       │
│     Define URL para boton VER segun tipo                        │
│                                                                 │
│  3. LOGICA URL VER                                              │
│     - Si presupuesto_sst → ruta especial                        │
│     - Si existe en $mapaRutas → usar ruta mapeada               │
│     - Default → programa-capacitacion                           │
│                                                                 │
│  4. CADENA ELSEIF (para EDITAR)                                 │
│     Define URL para boton EDITAR segun tipo                     │
│     Si tipo no esta → $urlEditar = null → sin boton             │
│                                                                 │
│  5. HTML DE BOTONES                                             │
│     - PDF (siempre)                                             │
│     - PDF Firmado (condicional)                                 │
│     - Ver (siempre)                                             │
│     - Editar (condicional - si $urlEditar)                      │
│     - Firmas/Adjuntar (segun tipo documento)                    │
│     - Publicar (segun tipo documento)                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 9. Ejemplo: Agregar Editor para Nuevo Tipo

Supongamos que queremos agregar editor para `mi_documento`:

### Paso 1: Agregar a $mapaRutas (VER)

```php
$mapaRutas = [
    // ... otros tipos ...
    'mi_documento' => 'mi-documento/' . $docSST['anio'],
];
```

### Paso 2: Agregar elseif (EDITAR)

```php
} elseif ($tipoDoc === 'mi_documento') {
    $urlEditar = base_url('documentos/generar/mi_documento/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
```

### Paso 3: Crear Rutas en Routes.php

```php
// Vista del documento
$routes->get('/documentos-sst/(:num)/mi-documento/(:num)', 'DocumentosSSTController::miDocumento/$1/$2');

// Editor/Generador
$routes->get('/documentos/generar/mi_documento/(:num)', 'GeneradorController::miDocumento/$1');
$routes->post('/documentos/generar/mi_documento/(:num)', 'GeneradorController::guardarMiDocumento/$1');
```

---

## 10. Tipos de Documento Actualmente Configurados

### Con Editor (tienen boton Editar)

| Tipo | Ruta Editor | Estandar |
|------|-------------|----------|
| `programa_capacitacion` | `/documentos/generar/programa_capacitacion/` | 1.2.1 |
| `procedimiento_control_documental` | `/documentos/generar/procedimiento_control_documental/` | 2.5.1 |
| `presupuesto_sst` | `/documentos-sst/presupuesto/` | 1.1.5 |
| `identificacion_alto_riesgo` | `/documentos/generar/identificacion_alto_riesgo/` | 2.6.1 |
| `politica_sst_general` | `/documentos/generar/politica_sst_general/` | 2.1.1 |
| `politica_prevencion_emergencias` | `/documentos/generar/politica_prevencion_emergencias/` | 2.1.1 |
| `plan_objetivos_metas` | `/documentos/generar/plan_objetivos_metas/` | 2.2.1 |
| `politica_alcohol_drogas` | `/documentos/generar/politica_alcohol_drogas/` | 2.1.1 |
| `politica_acoso_laboral` | `/documentos/generar/politica_acoso_laboral/` | 2.1.1 |
| `politica_violencias_genero` | `/documentos/generar/politica_violencias_genero/` | 2.1.1 |
| `politica_discriminacion` | `/documentos/generar/politica_discriminacion/` | 2.1.1 |
| `programa_promocion_prevencion_salud` | `/documentos/generar/programa_promocion_prevencion_salud/` | 3.1.3 |
| `programa_induccion_reinduccion` | `/documentos/generar/programa_induccion_reinduccion/` | 3.1.1 |
| `procedimiento_matriz_legal` | `/documentos/generar/procedimiento_matriz_legal/` | 2.4.1 |
| `manual_convivencia_laboral` | `/documentos/generar/manual_convivencia_laboral/` | 1.1.8 |

### Sin Editor (solo boton Ver)

Tipos que estan en `$mapaRutas` pero NO tienen elseif para `$urlEditar`.
Estos documentos se generan automaticamente y no requieren editor con IA:

- `asignacion_responsable_sgsst` (generado automaticamente)
- `responsabilidades_rep_legal_sgsst` (generado automaticamente)
- `responsabilidades_responsable_sgsst` (generado automaticamente)
- `responsabilidades_trabajadores_sgsst` (documento de firma fisica)

---

## 11. Comportamiento del Boton Editar en HTML

```php
<?php if ($urlEditar): ?>
<a href="<?= $urlEditar ?>"
   class="btn btn-outline-warning"
   title="Editar documento"
   target="_blank">
    <i class="bi bi-pencil"></i>
</a>
<?php endif; ?>
```

**Importante:** Solo se renderiza si `$urlEditar` tiene valor (no es null).

---

## 12. Casos Especiales

### Presupuesto SST

El presupuesto tiene una estructura de rutas diferente:

```php
// URL VER
if ($tipoDoc === 'presupuesto_sst') {
    $urlVer = base_url('documentos-sst/presupuesto/preview/' . $cliente['id_cliente'] . '/' . $docSST['anio']);
}

// URL EDITAR
if ($tipoDoc === 'presupuesto_sst') {
    $urlEditar = base_url('documentos-sst/presupuesto/' . $cliente['id_cliente'] . '/' . $docSST['anio']);
}
```

### Responsabilidades Trabajadores

Este tipo tiene logica especial para adjuntar documento escaneado (firma fisica):

```php
<?php if ($tipoDoc === 'responsabilidades_trabajadores_sgsst'): ?>
    <!-- Botones para documento de firma fisica -->
    <!-- Ver escaneado, Adjuntar escaneado -->
<?php else: ?>
    <!-- Botones para firma electronica -->
    <!-- Firmas, Publicar -->
<?php endif; ?>
```

---

## 13. Diagrama de Decision

```
┌─────────────────────────────────────────────────────────────────┐
│ Documento en tabla                                              │
│                                                                 │
│ ¿Tipo esta en $mapaRutas?                                       │
│     SI → $urlVer = ruta mapeada                                 │
│     NO → $urlVer = ruta default (programa-capacitacion)         │
│                                                                 │
│ ¿Tipo tiene elseif para $urlEditar?                             │
│     SI → $urlEditar = ruta del editor                           │
│     NO → $urlEditar = null                                      │
│                                                                 │
│ Renderizar botones:                                             │
│     [PDF] [Ver] [$urlEditar ? [Editar] : nada] [Firmas/Publicar]│
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 14. Validacion: Por que un Documento no tiene Editor

| Verificacion | Donde revisar | Que buscar |
|--------------|---------------|------------|
| 1. Tipo existe en elseif | acciones_documento.php | `elseif ($tipoDoc === 'mi_tipo')` |
| 2. URL esta bien formada | Routes.php | Ruta GET para el editor |
| 3. Controlador existe | Controllers/ | Metodo para el editor |
| 4. Vista existe | Views/generador_ia/ o Views/documentos_sst/ | Archivo .php del editor |

---

## 15. Resumen Visual de Botones

```
┌─────────────────────────────────────────────────────────────────┐
│ BOTONES DE ACCION POR DOCUMENTO                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Documento CON editor configurado:                               │
│ [PDF] [PDF Firmado*] [Ver] [Editar] [Firmas] [Publicar]         │
│  rojo   rojo outline  azul  amarillo  verde   gris              │
│                                                                 │
│ Documento SIN editor configurado:                               │
│ [PDF] [PDF Firmado*] [Ver] [Firmas] [Publicar]                  │
│  rojo   rojo outline  azul  verde   gris                        │
│                                                                 │
│ * Solo aparece si existe archivo_pdf firmado                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

*Documento creado: 2026-02-05*
*Version: 1.0*
*Relacionado con: 6_AA_COMPONENTE_TABLA_DOCUMENTOS_SST.md, 2_AA_WEB.md*
