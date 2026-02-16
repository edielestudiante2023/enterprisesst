# INSTRUCTIVO: Agregar ReportList a Carpetas CON FASES Existentes

---

## ⚠️ ADVERTENCIA CRITICA: NO TOCAR LAS FASES ⚠️

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│   ██████╗ ██████╗ ██╗██████╗  █████╗ ██████╗  ██████╗                      │
│  ██╔════╝██╔═══██╗██║██╔══██╗██╔══██╗██╔══██╗██╔═══██╗                     │
│  ██║     ██║   ██║██║██║  ██║███████║██║  ██║██║   ██║                     │
│  ██║     ██║   ██║██║██║  ██║██╔══██║██║  ██║██║   ██║                     │
│  ╚██████╗╚██████╔╝██║██████╔╝██║  ██║██████╔╝╚██████╔╝                     │
│   ╚═════╝ ╚═════╝ ╚═╝╚═════╝ ╚═╝  ╚═╝╚═════╝  ╚═════╝                      │
│                                                                             │
│  BAJO NINGUNA CIRCUNSTANCIA se debe modificar, eliminar o alterar:         │
│                                                                             │
│  ❌ El card de carpeta con boton de IA                                     │
│  ❌ El panel de fases (panel_fases)                                        │
│  ❌ La tabla de documentos generados con IA (tabla_documentos)             │
│  ❌ Las variables $fasesInfo, $documentoExistente, $documentosSSTAprobados │
│  ❌ La logica de verificacion de fases ($puedeGenerarDocumento)            │
│  ❌ La deteccion en determinarTipoCarpetaFases() (ya existe)               │
│  ❌ Cualquier codigo PHP existente en la parte superior de la vista        │
│                                                                             │
│  ✅ SOLO agregar codigo NUEVO al FINAL del archivo                         │
│  ✅ SOLO agregar query ADICIONAL en el controlador (no modificar existente)│
│  ✅ Usar IDs unicos en HTML/JS para evitar conflictos                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Descripcion General

Este instructivo detalla como **agregar funcionalidad ReportList** a carpetas del SG-SST que **YA tienen implementacion de FASES** (panel de fases, generacion con IA, indicadores, etc.).

**IMPORTANTE:** Este documento es para carpetas que YA tienen una vista implementada con sistema de fases. Si la carpeta es NUEVA (sin implementacion previa), use el documento `AA_REPORTLIST_CARPETA.md`.

### Diferencia con Carpetas Nuevas

| Aspecto | Carpeta Nueva | Carpeta con Fases |
|---------|---------------|-------------------|
| Vista PHP | CREAR desde cero | MODIFICAR existente |
| Deteccion en Controller | AGREGAR | YA EXISTE (no tocar) |
| Panel de Fases | No aplica | PRESERVAR intacto |
| Filtro de documentos | AGREGAR | AGREGAR (adicional) |
| Metodo adjuntar | AGREGAR | AGREGAR |
| Ruta POST | AGREGAR | AGREGAR |

---

## Arquitectura: Documentos Generados + Soportes Adicionales

Este instructivo habilita **DOS funcionalidades** en carpetas con fases:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    RESULTADO FINAL EN LA VISTA                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐ │
│  │  SECCION EXISTENTE (NO TOCAR)                                         │ │
│  │  - Card de carpeta con boton IA                                       │ │
│  │  - Panel de fases                                                     │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐ │
│  │  1. TABLA DOCUMENTOS GENERADOS (verde)            ← HABILITAR         │ │
│  │     Codigo | Nombre | Ano | Version | Estado | Firmas | Acciones      │ │
│  │     - Historial de versiones                                          │ │
│  │     - Botones: PDF, Ver, Editar, Firmas, Publicar                     │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐ │
│  │  SUBCARPETAS (si existen)                         ← NO TOCAR          │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                             │
│  ════════════════════════════════════════════════════════════════════════  │
│  <hr class="my-5">                                                         │
│  ════════════════════════════════════════════════════════════════════════  │
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐ │
│  │  2. SOPORTES ADICIONALES (gris)                   ← AGREGAR NUEVO     │ │
│  │     Card con boton "Adjuntar Soporte"                                 │ │
│  │     Tabla: Codigo | Descripcion | Ano | Fecha | Tipo | Acciones       │ │
│  │     Modal para adjuntar archivo/enlace                                │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ⚠️ LAS DOS TABLAS SON INDEPENDIENTES ⚠️

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  TABLA 1: DOCUMENTOS GENERADOS (verde)                                      │
│  ══════════════════════════════════════                                     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Codigo | Nombre | Ano | Version | Estado | Firmas | Acciones       │   │
│  │  - Historial de versiones (colapsable)                              │   │
│  │  - Botones: PDF, Ver, Editar, Firmas/Audit, Publicar                │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  PASOS QUE LA HABILITAN:                                                    │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  ✅ PASO 2.1:   Agregar tipo al array in_array() (~linea 304)       │   │
│  │  ✅ PASO 2.2:   Agregar filtro elseif con tipo_documento IA (~385)  │   │
│  │  ✅ PASO 2.1.1: Agregar tipo a $tiposConTabla en componente vista   │   │
│  │                 (tabla_documentos_sst.php linea 6)                  │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  Si NO haces estos 3 pasos → $documentosSSTAprobados queda vacio           │
│                            → La tabla verde NO aparece                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│  TABLA 2: SOPORTES ADICIONALES (gris)                                       │
│  ═════════════════════════════════════                                      │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Card con boton "Adjuntar Soporte"                                  │   │
│  │  Tabla: Codigo | Descripcion | Ano | Fecha | Tipo | Acciones        │   │
│  │  Modal para adjuntar archivo/enlace                                 │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  PASOS QUE LA HABILITAN:                                                    │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  ✅ PASO 2.3: Agregar query de soportes adicionales (~linea 446)    │   │
│  │  ✅ PASO 2.4: Agregar $soportesAdicionales al $data (~linea 467)    │   │
│  │  ✅ PASO 3:   Agregar seccion completa en la vista (al final)       │   │
│  │  ✅ PASO 4:   Agregar metodo en DocumentosSSTController             │   │
│  │  ✅ PASO 5:   Agregar ruta POST en Routes.php                       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  Si NO haces estos pasos → $soportesAdicionales queda vacio                │
│                          → La tabla gris aparece pero vacia                 │
│                          → El boton "Adjuntar" da error 404                 │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Resumen Rapido

| Tabla | Color | Variable | Pasos Requeridos |
|-------|-------|----------|------------------|
| Documentos Generados | Verde | `$documentosSSTAprobados` | 2.1, 2.1.1, 2.2 |
| Soportes Adicionales | Gris | `$soportesAdicionales` | 2.3, 2.4, 3, 4, 5 |

**Requiere:** Query adicional + seccion en vista + metodo + ruta

---

## Archivos a Modificar (5 archivos)

| # | Archivo | Accion | Descripcion |
|---|---------|--------|-------------|
| 1 | `app/Controllers/DocumentacionController.php` | **MODIFICAR** | Agregar tipo al array, filtro, y query soportes |
| 2 | `app/Views/documentacion/_components/tabla_documentos_sst.php` | **MODIFICAR** | Agregar tipo a $tiposConTabla (linea 6) |
| 3 | `app/Views/documentacion/_tipos/{nombre}.php` | **MODIFICAR** | Agregar seccion Soportes Adicionales al final |
| 4 | `app/Controllers/DocumentosSSTController.php` | **MODIFICAR** | Agregar metodo adjuntar |
| 5 | `app/Config/Routes.php` | **MODIFICAR** | Agregar ruta POST |

**NOTA:** NO se crea vista nueva, NO se modifica la deteccion (ya existe).

---

## PASO 1: Definir Identificadores del Soporte

Defina estos **4 identificadores** para los SOPORTES (no para la carpeta):

```
┌────────────────────────────────────────────────────────────────────┐
│ EJEMPLO: 3.1.2 - Promocion y Prevencion en Salud (con fases)      │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  tipoCarpetaFases YA EXISTE: promocion_prevencion_salud           │
│                              (NO CAMBIAR)                          │
│                                                                    │
│  NUEVOS IDENTIFICADORES PARA SOPORTES:                            │
│                                                                    │
│  1. tipo_documento:    soporte_pyp_salud                          │
│                        (prefijo 'soporte_' + abreviatura)          │
│                                                                    │
│  2. codigo:            SOP-PYP                                     │
│                        (prefijo 'SOP-' + 3 letras mayusculas)      │
│                                                                    │
│  3. ruta:              adjuntar-soporte-pyp-salud                 │
│                        (kebab-case)                                │
│                                                                    │
│  4. variable vista:    $soportesAdicionales                       │
│                        (para diferenciar de $documentosSSTAprobados)│
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

### Convencion de Nombres para Soportes Adicionales

| Campo | Formato | Ejemplo | Uso |
|-------|---------|---------|-----|
| `tipo_documento` | `soporte_` + abreviatura | `soporte_pyp_salud` | Campo en BD, diferente al tipo de fases |
| `codigo` | SOP-XXX | `SOP-PYP` | Codigo visible en tabla, max 7 chars |
| `ruta` | kebab-case | `adjuntar-soporte-pyp-salud` | URL del endpoint POST |
| `variable` | camelCase | `$soportesAdicionales` | Variable en vista (opcional) |

---

## PASO 2: Modificar DocumentacionController.php

**Ubicacion:** `app/Controllers/DocumentacionController.php`

Se requieren **3 modificaciones** en este archivo:

### 2.1 Agregar Tipo al Array de Tipos (linea ~304)

Buscar el array `in_array($tipoCarpetaFases, [...])` y agregar el nuevo tipo AL FINAL:

```php
// ANTES:
if (in_array($tipoCarpetaFases, ['capacitacion_sst', ... 'comite_convivencia'])) {

// DESPUES:
if (in_array($tipoCarpetaFases, ['capacitacion_sst', ... 'comite_convivencia', 'MI_TIPO_CARPETA'])) {
```

**Ejemplo real:**
```php
// Agregar 'promocion_prevencion_salud' al final del array
if (in_array($tipoCarpetaFases, [
    'capacitacion_sst', 'responsables_sst', ... 'comite_convivencia',
    'promocion_prevencion_salud'  // ← AGREGAR AQUI
])) {
```

**IMPORTANTE:** Esto habilita la TABLA VERDE de Documentos Generados.

### 2.2 Agregar Filtro elseif (linea ~385-390)

Buscar los filtros `elseif` y agregar uno nuevo ANTES de `elseif (isset($tipoDocBuscar))`:

```php
} elseif ($tipoCarpetaFases === 'comite_convivencia') {
    // 1.1.8: Conformación Comité de Convivencia
    $queryDocs->where('tipo_documento', 'soporte_comite_convivencia');

// ========== AGREGAR AQUI ==========
} elseif ($tipoCarpetaFases === 'MI_TIPO_CARPETA') {
    // X.X.X: Descripcion del numeral
    $queryDocs->where('tipo_documento', 'TIPO_DOCUMENTO_IA');
// ==================================

} elseif (isset($tipoDocBuscar)) {
    $queryDocs->where('tipo_documento', $tipoDocBuscar);
}
```

**Ejemplo real:**
```php
} elseif ($tipoCarpetaFases === 'promocion_prevencion_salud') {
    // 3.1.2: Programa de Promoción y Prevención en Salud
    $queryDocs->where('tipo_documento', 'programa_promocion_prevencion_salud');
}
```

### 2.3 Agregar Query de Soportes Adicionales (linea ~446)

Buscar donde termina el bloque de documentos (despues de `unset($docSST);`) y agregar:

```php
        unset($docSST);
    }

    // ========== AGREGAR AQUI ==========
    // Soportes adicionales para carpetas con fases
    $soportesAdicionales = [];
    if ($tipoCarpetaFases === 'MI_TIPO_CARPETA') {
        $db = $db ?? \Config\Database::connect();
        $soportesAdicionales = $db->table('tbl_documentos_sst')
            ->where('id_cliente', $cliente['id_cliente'])
            ->where('tipo_documento', 'soporte_MI_TIPO')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
    // ==================================

    // Determinar qué vista de tipo cargar
```

**Ejemplo real:**
```php
    // Soportes adicionales para carpetas con fases (3.1.2 Promoción y Prevención en Salud)
    $soportesAdicionales = [];
    if ($tipoCarpetaFases === 'promocion_prevencion_salud') {
        $db = $db ?? \Config\Database::connect();
        $soportesAdicionales = $db->table('tbl_documentos_sst')
            ->where('id_cliente', $cliente['id_cliente'])
            ->where('tipo_documento', 'soporte_pyp_salud')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
```

### 2.4 Agregar Variable al Array $data (linea ~467)

Buscar el array `$data = [...]` y agregar `soportesAdicionales`:

```php
    $data = [
        'carpeta' => $carpeta,
        // ... otras variables ...
        'documentosSSTAprobados' => $documentosSSTAprobados,
        'soportesAdicionales' => $soportesAdicionales,  // ← AGREGAR AQUI
        'contextoCliente' => $contextoCliente ?? null,
        'vistaContenido' => $vistaPath
    ];
```

---

## PASO 3: Modificar la Vista Existente

**Ubicacion:** `app/Views/documentacion/_tipos/{tipoCarpetaFases}.php`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  ⚠️  REGLA DE ORO: SOLO AGREGAR AL FINAL, NUNCA MODIFICAR LO EXISTENTE    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  La vista existente tiene esta estructura:                                  │
│                                                                             │
│  <?php /* Variables y logica de fases */ ?>     ← NO TOCAR                 │
│  <!-- Card con boton IA -->                     ← NO TOCAR                 │
│  <!-- Panel de Fases -->                        ← NO TOCAR                 │
│  <!-- Tabla de Documentos SST -->               ← NO TOCAR                 │
│  <!-- Subcarpetas -->                           ← NO TOCAR                 │
│                                                                             │
│  ════════════════════════════════════════════════════════════════════════  │
│  ↓↓↓ AGREGAR TODO EL CODIGO NUEVO AQUI ABAJO ↓↓↓                          │
│  ════════════════════════════════════════════════════════════════════════  │
│                                                                             │
│  <hr class="my-5">                              ← SEPARADOR NUEVO          │
│  <!-- Card Soportes Adicionales -->             ← CODIGO NUEVO             │
│  <!-- Tabla ReportList -->                      ← CODIGO NUEVO             │
│  <!-- Modal Adjuntar -->                        ← CODIGO NUEVO             │
│  <script> /* JS con IDs unicos */ </script>     ← CODIGO NUEVO             │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.1 Identificar el Final de la Vista Existente

Abrir la vista y ubicar donde termina el contenido actual. Normalmente termina con:
- Subcarpetas (`<?php endif; ?>` del bloque de subcarpetas)
- O el ultimo `</div>` o `<?php endif; ?>`

**NO borrar, NO mover, NO modificar nada de lo que ya existe.**

### 3.2 Agregar Seccion ReportList al Final

**CRITICO:** Agregar DESPUES de todo el contenido existente. El codigo nuevo va AL FINAL del archivo.

```php
<?php
// ============================================================
// CONTENIDO EXISTENTE (NO MODIFICAR NADA DE ARRIBA)
// ============================================================
?>

<!-- ============================================================ -->
<!-- SECCION ADICIONAL: SOPORTES (ReportList)                     -->
<!-- Esta seccion se agrega SIN modificar el codigo existente     -->
<!-- ============================================================ -->

<hr class="my-5">

<!-- Card de Soportes Adicionales -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">
                    <i class="bi bi-file-earmark-plus text-primary me-2"></i>
                    Soportes Adicionales
                </h5>
                <p class="text-muted mb-0 small">
                    Adjunte evidencias, registros fotograficos, actas u otros soportes
                    complementarios para este numeral.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoporte">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0">
            <i class="bi bi-paperclip me-2"></i>Soportes Adjuntados
        </h6>
    </div>
    <div class="card-body">
        <?php
        // Usar variable $soportesAdicionales si existe, sino array vacio
        $soportes = $soportesAdicionales ?? [];
        ?>
        <?php if (!empty($soportes)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Codigo</th>
                            <th>Descripcion</th>
                            <th style="width: 80px;">Ano</th>
                            <th style="width: 100px;">Fecha</th>
                            <th style="width: 90px;">Tipo</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soportes as $soporte): ?>
                            <?php
                            $esEnlace = !empty($soporte['url_externa']);
                            $urlArchivo = $esEnlace
                                ? $soporte['url_externa']
                                : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-XXX') ?></code></td>
                                <td>
                                    <strong><?= esc($soporte['titulo']) ?></strong>
                                    <?php if (!empty($soporte['observaciones'])): ?>
                                        <br><small class="text-muted"><?= esc($soporte['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= esc($soporte['anio']) ?></span></td>
                                <td><small><?= date('d/m/Y', strtotime($soporte['created_at'] ?? 'now')) ?></small></td>
                                <td>
                                    <?php if ($esEnlace): ?>
                                        <span class="badge bg-info"><i class="bi bi-link-45deg"></i> Enlace</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark"><i class="bi bi-file-earmark"></i> Archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-primary" target="_blank" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (!$esEnlace): ?>
                                            <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-danger" download title="Descargar">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes adjuntados.</p>
                <small class="text-muted">Use el boton "Adjuntar Soporte" para agregar evidencias.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarSoporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- IMPORTANTE: Cambiar la ruta segun el tipo -->
            <form id="formAdjuntarSoporte"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-CAMBIAR-AQUI') ?>"
                  method="post"
                  enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Toggle Archivo/Enlace -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_soporte"
                                   id="tipoCargaSoporteArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaSoporteArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_soporte"
                                   id="tipoCargaSoporteEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaSoporteEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoSoporte">
                        <label for="archivo_soporte_adicional" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_adicional" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlaceSoporte">
                        <label for="url_externa_soporte" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_soporte" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_soporte" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_soporte" name="descripcion" required
                               placeholder="Ej: Registro fotografico capacitacion, Acta de reunion...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_soporte" class="form-label">Ano</label>
                        <select class="form-select" id="anio_soporte" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_soporte" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_soporte" name="observaciones" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarSoporte">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para Toggle (usar IDs unicos para no conflictuar) -->
<script>
document.querySelectorAll('input[name="tipo_carga_soporte"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoSoporte').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceSoporte').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_adicional').required = isArchivo;
        document.getElementById('url_externa_soporte').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_adicional').value = '';
        } else {
            document.getElementById('url_externa_soporte').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporte')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarSoporte');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
```

### 3.3 Cambiar la Ruta del Formulario

En el codigo anterior, buscar y cambiar:

```php
// ANTES:
action="<?= base_url('documentos-sst/adjuntar-soporte-CAMBIAR-AQUI') ?>"

// DESPUES (ejemplo para PyP Salud):
action="<?= base_url('documentos-sst/adjuntar-soporte-pyp-salud') ?>"
```

---

## PASO 4: Agregar Metodo en DocumentosSSTController.php

**Ubicacion:** `app/Controllers/DocumentosSSTController.php`

Agregar el metodo usando el patron generico existente:

```php
/**
 * Adjuntar soporte adicional de Promocion y Prevencion en Salud (3.1.2)
 */
public function adjuntarSoportePypSalud()
{
    return $this->adjuntarSoporteGenerico(
        'soporte_pyp_salud',                    // tipo_documento (BD)
        'SOP-PYP',                               // codigo (max 7 chars)
        'soporte_pyp_salud_',                    // prefijo archivo
        'Soporte PyP Salud',                     // descripcion reporte
        'Soporte adjuntado exitosamente.'        // mensaje flash
    );
}
```

---

## PASO 5: Agregar Ruta en Routes.php

**Ubicacion:** `app/Config/Routes.php`

```php
// Soportes adicionales para carpetas con fases
$routes->post('/documentos-sst/adjuntar-soporte-pyp-salud', 'DocumentosSSTController::adjuntarSoportePypSalud');
```

---

## Checklist de Implementacion (Carpeta con Fases)

```
[ ] 1. Verificar que la carpeta YA tiene implementacion de fases:
    [ ] Vista existe en app/Views/documentacion/_tipos/
    [ ] Vista tiene panel_fases o fasesInfo
    [ ] Deteccion ya existe en determinarTipoCarpetaFases()

[ ] 2. Definir identificadores para SOPORTES:
    [ ] tipo_documento para IA: programa_________________
    [ ] tipo_documento para soportes: soporte_________________
    [ ] codigo (SOP-XXX): SOP-___
    [ ] ruta: adjuntar-soporte-________________

[ ] 3. Modificar DocumentacionController.php (4 cambios):
    [ ] 3.1 Agregar tipo al array in_array() (~linea 304)
    [ ] 3.2 Agregar filtro elseif con tipo_documento IA (~linea 385)
    [ ] 3.3 Agregar query de soportes adicionales (~linea 446)
    [ ] 3.4 Agregar $soportesAdicionales al array $data (~linea 467)

[ ] 4. Modificar componente tabla_documentos_sst.php:
    [ ] Agregar tipo a $tiposConTabla (linea 6)

[ ] 5. Modificar vista existente (NO crear nueva, agregar AL FINAL):
    [ ] Agregar <hr class="my-5"> separador
    [ ] Agregar card "Soportes Adicionales" con boton
    [ ] Agregar tabla ReportList
    [ ] Agregar modal con toggle archivo/enlace
    [ ] Agregar JavaScript (con IDs unicos para no conflictuar)
    [ ] Verificar action del formulario apunta a ruta correcta

[ ] 6. Agregar metodo en DocumentosSSTController.php

[ ] 7. Agregar ruta POST en Routes.php

[ ] 8. Probar:
    [ ] Navegar a la carpeta
    [ ] Verificar que fases funcionan igual (no se rompieron)
    [ ] Verificar que aparece TABLA VERDE (documentos IA)
    [ ] Verificar que aparece TABLA GRIS (soportes adicionales)
    [ ] Probar adjuntar archivo
    [ ] Probar adjuntar enlace
    [ ] Verificar que aparece en la tabla
```

---

## Ejemplo Completo: 3.1.2 Promocion y Prevencion en Salud

### Identificadores

```
tipoCarpetaFases existente:     promocion_prevencion_salud (NO CAMBIAR)
tipo_documento para IA:         programa_promocion_prevencion_salud
tipo_documento para soportes:   soporte_pyp_salud
codigo soportes:                SOP-PYP
ruta:                           adjuntar-soporte-pyp-salud
variable:                       $soportesAdicionales
```

### DocumentacionController.php (4 modificaciones)

**Paso 2.1 - Agregar al array (~linea 304):**
```php
if (in_array($tipoCarpetaFases, [
    'capacitacion_sst', ... 'comite_convivencia',
    'promocion_prevencion_salud'  // ← AGREGAR
])) {
```

**Paso 2.2 - Agregar filtro elseif (~linea 385):**
```php
} elseif ($tipoCarpetaFases === 'promocion_prevencion_salud') {
    // 3.1.2: Programa de Promoción y Prevención en Salud
    $queryDocs->where('tipo_documento', 'programa_promocion_prevencion_salud');
}
```

**Paso 2.3 - Agregar query soportes (~linea 446):**
```php
// Soportes adicionales para carpetas con fases (3.1.2)
$soportesAdicionales = [];
if ($tipoCarpetaFases === 'promocion_prevencion_salud') {
    $db = $db ?? \Config\Database::connect();
    $soportesAdicionales = $db->table('tbl_documentos_sst')
        ->where('id_cliente', $cliente['id_cliente'])
        ->where('tipo_documento', 'soporte_pyp_salud')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->getResultArray();
}
```

**Paso 2.4 - Agregar al $data (~linea 467):**
```php
'soportesAdicionales' => $soportesAdicionales,
```

### Vista: Agregar al final de promocion_prevencion_salud.php

```php
<!-- ... todo el contenido existente ... -->

<hr class="my-5">

<!-- Seccion de Soportes segun plantilla de arriba -->
<form action="<?= base_url('documentos-sst/adjuntar-soporte-pyp-salud') ?>" ...>
```

### DocumentosSSTController.php

```php
public function adjuntarSoportePypSalud()
{
    return $this->adjuntarSoporteGenerico(
        'soporte_pyp_salud',
        'SOP-PYP',
        'soporte_pyp_salud_',
        'Soporte PyP Salud',
        'Soporte adjuntado exitosamente.'
    );
}
```

### Routes.php

```php
$routes->post('/documentos-sst/adjuntar-soporte-pyp-salud', 'DocumentosSSTController::adjuntarSoportePypSalud');
```

---

## Notas Importantes

### IDs Unicos en JavaScript/HTML

Cuando agregas ReportList a una vista que ya tiene formularios, usa IDs unicos para evitar conflictos:

```html
<!-- Modal existente podria tener id="modalAdjuntar" -->
<!-- El nuevo modal debe tener id diferente: -->
<div class="modal" id="modalAdjuntarSoporte">

<!-- Campos existentes podrian tener id="archivo_soporte" -->
<!-- Los nuevos deben ser diferentes: -->
<input id="archivo_soporte_adicional">
```

### Variables de Vista

Si la vista existente usa `$documentosSSTAprobados` para documentos generados con IA, usa una variable diferente para soportes:

```php
// Documentos generados con IA (existente)
$documentosSSTAprobados

// Soportes adicionales (nuevo)
$soportesAdicionales
```

### Filtros en Controller

Si el controller filtra documentos con `whereIn`, asegurate de NO mezclar los tipos:

```php
// MAL: Mezclar tipos en un solo query
$queryDocs->whereIn('tipo_documento', ['programa_pyp', 'soporte_pyp_salud']);

// BIEN: Queries separados
$documentosIA = $queryDocs->where('tipo_documento', 'programa_pyp')->findAll();
$soportes = $queryDocs->where('tipo_documento', 'soporte_pyp_salud')->findAll();
```

---

## Listado de Carpetas con Fases que Pueden Recibir ReportList

| Numeral | tipoCarpetaFases | Estado ReportList |
|---------|------------------|-------------------|
| 2.1.1 | capacitacion_sst | Pendiente |
| 2.2.1 | responsables_sst | Pendiente |
| 3.1.2 | promocion_prevencion_salud | Pendiente |
| ... | ... | ... |

---

*Documento generado: 2026-02-04*
*Version: 1.0*
*Complemento de: AA_REPORTLIST_CARPETA.md*
