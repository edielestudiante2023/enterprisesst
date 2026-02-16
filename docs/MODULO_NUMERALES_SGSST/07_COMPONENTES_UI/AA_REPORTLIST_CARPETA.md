# INSTRUCTIVO: Implementacion de Modulo ReportList para Carpetas SST

## Descripcion General

Este instructivo detalla como implementar un modulo de tipo **ReportList** para carpetas del SG-SST. Este patron permite a los usuarios:

1. **Adjuntar archivos** (PDF, Excel, Word, imagenes)
2. **Adjuntar enlaces** (Google Drive, OneDrive, SharePoint, etc.)
3. **Visualizar tabla** con todos los soportes adjuntados
4. **Descargar/Abrir** los soportes desde la tabla

Este patron es ideal para numerales del SG-SST que requieren **soportes documentales** sin necesidad de generar documentos con IA.

---

## Arquitectura del Patron ReportList

```
┌─────────────────────────────────────────────────────────────────────┐
│                         FLUJO DE DATOS                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  [Vista PHP]  ──POST──>  [Ruta]  ──>  [Controlador]  ──>  [BD]     │
│      │                                      │                       │
│      │                                      v                       │
│      │                            tbl_documentos_sst                │
│      │                            tbl_doc_versiones_sst             │
│      │                            tbl_reporte                       │
│      │                                      │                       │
│      <──────────── redirect + flash ────────┘                       │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### Archivos Involucrados (5 archivos a modificar/crear)

| # | Archivo | Accion | Descripcion |
|---|---------|--------|-------------|
| 1 | `app/Views/documentacion/_tipos/{nombre}.php` | CREAR | Vista con formulario y tabla |
| 2 | `app/Controllers/DocumentacionController.php` | MODIFICAR | Agregar tipo al array, filtro y deteccion |
| 3 | `app/Controllers/DocumentosSSTController.php` | MODIFICAR | Agregar metodo adjuntar |
| 4 | `app/Config/Routes.php` | MODIFICAR | Agregar ruta POST |
| 5 | Base de Datos | USAR | Tablas existentes (no modificar) |

---

## PASO 1: Definir Identificadores del Modulo

Antes de comenzar, defina estos **4 identificadores unicos**:

```
┌────────────────────────────────────────────────────────────────────┐
│ EJEMPLO: Numeral 4.2.2 - Verificacion de medidas de prevencion    │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  1. tipoCarpetaFases:  verificacion_medidas_prevencion            │
│                        (snake_case, unico, descriptivo)            │
│                                                                    │
│  2. tipo_documento:    soporte_verificacion_medidas               │
│                        (prefijo 'soporte_' + identificador)        │
│                                                                    │
│  3. codigo:            SOP-VMP                                     │
│                        (prefijo 'SOP-' + 3 letras mayusculas)      │
│                                                                    │
│  4. ruta:              adjuntar-soporte-verificacion              │
│                        (kebab-case, sin prefijo de controlador)    │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

### Convencion de Nombres (IMPORTANTE)

| Campo | Formato | Ejemplo | Uso |
|-------|---------|---------|-----|
| `tipoCarpetaFases` | snake_case | `entrega_epp` | Identificador interno, nombre de vista |
| `tipo_documento` | snake_case con prefijo | `soporte_entrega_epp` | Campo en BD `tbl_documentos_sst` |
| `codigo` | SOP-XXX | `SOP-EPP` | Codigo visible en tabla, max 7 chars |
| `ruta` | kebab-case | `adjuntar-soporte-epp` | URL del endpoint POST |

---

## PASO 2: Crear la Vista PHP

**Ubicacion:** `app/Views/documentacion/_tipos/{tipoCarpetaFases}.php`

### Estructura Completa de la Vista

```php
<?php
/**
 * Vista de Tipo: X.X.X Nombre del Numeral
 * Carpeta para adjuntar soportes de [descripcion]
 * Patron: ReportList (archivo/enlace + tabla)
 * Variables disponibles: $carpeta, $cliente, $documentosSSTAprobados, $subcarpetas
 */
?>

<!-- ============================================ -->
<!-- SECCION 1: CARD DE CARPETA CON BOTON        -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-folder-fill text-warning me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <!-- BOTON PRINCIPAL: Abre el modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntar">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECCION 2: ALERTA INFORMATIVA               -->
<!-- ============================================ -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Numeral X.X.X - Nombre del Requisito</h6>
            <p class="mb-0 small">
                Descripcion del requisito legal y que documentos se deben adjuntar.
                Ejemplo: planillas, certificados, actas, registros, etc.
            </p>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECCION 3: TABLA DE SOPORTES (REPORTLIST)   -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Soportes Adjuntados
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($documentosSSTAprobados)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Codigo</th>
                            <th>Descripcion</th>
                            <th style="width: 80px;">Ano</th>
                            <th style="width: 100px;">Fecha</th>
                            <th style="width: 100px;">Tipo</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentosSSTAprobados as $soporte): ?>
                            <?php
                            // Determinar si es enlace externo o archivo local
                            $esEnlace = !empty($soporte['url_externa']);
                            $urlArchivo = $esEnlace
                                ? $soporte['url_externa']
                                : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <!-- Columna: Codigo -->
                                <td>
                                    <code><?= esc($soporte['codigo'] ?? 'SOP-XXX') ?></code>
                                </td>

                                <!-- Columna: Descripcion + Observaciones -->
                                <td>
                                    <strong><?= esc($soporte['titulo']) ?></strong>
                                    <?php if (!empty($soporte['observaciones'])): ?>
                                        <br><small class="text-muted"><?= esc($soporte['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Columna: Ano -->
                                <td>
                                    <span class="badge bg-secondary"><?= esc($soporte['anio']) ?></span>
                                </td>

                                <!-- Columna: Fecha de carga -->
                                <td>
                                    <small><?= date('d/m/Y', strtotime($soporte['created_at'] ?? 'now')) ?></small>
                                </td>

                                <!-- Columna: Tipo (Enlace o Archivo) -->
                                <td>
                                    <?php if ($esEnlace): ?>
                                        <span class="badge bg-info">
                                            <i class="bi bi-link-45deg me-1"></i>Enlace
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-file-earmark me-1"></i>Archivo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Columna: Acciones -->
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- Boton Ver/Abrir -->
                                        <a href="<?= esc($urlArchivo) ?>"
                                           class="btn btn-outline-primary"
                                           target="_blank"
                                           title="Ver/Abrir">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <?php if ($esEnlace): ?>
                                            <!-- Boton Enlace Externo -->
                                            <a href="<?= esc($urlArchivo) ?>"
                                               class="btn btn-outline-info"
                                               target="_blank"
                                               title="Abrir enlace externo">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        <?php else: ?>
                                            <!-- Boton Descargar -->
                                            <a href="<?= esc($urlArchivo) ?>"
                                               class="btn btn-danger"
                                               download
                                               title="Descargar">
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
            <!-- Estado vacio: Sin soportes -->
            <div class="text-center py-4">
                <i class="bi bi-folder2-open text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes adjuntados aun.</p>
                <small class="text-muted">Use el boton "Adjuntar Soporte" para agregar documentos.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- SECCION 4: SUBCARPETAS (si aplica)          -->
<!-- ============================================ -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- ============================================ -->
<!-- SECCION 5: MODAL DE ADJUNTAR SOPORTE        -->
<!-- ============================================ -->
<div class="modal fade" id="modalAdjuntar" tabindex="-1" aria-labelledby="modalAdjuntarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Header del Modal -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAdjuntarLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Formulario -->
            <form id="formAdjuntar"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-XXXXX') ?>"
                  method="post"
                  enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Campos ocultos requeridos -->
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- ================================ -->
                    <!-- TOGGLE: Archivo vs Enlace       -->
                    <!-- ================================ -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga"
                                   id="tipoCargaArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>

                            <input type="radio" class="btn-check" name="tipo_carga"
                                   id="tipoCargaEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- ================================ -->
                    <!-- CAMPO: Archivo (visible por defecto) -->
                    <!-- ================================ -->
                    <div class="mb-3" id="campoArchivo">
                        <label for="archivo_soporte" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Word, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_soporte" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Formatos permitidos: PDF, JPG, PNG, Excel, Word. Maximo: 10MB
                        </div>
                    </div>

                    <!-- ================================ -->
                    <!-- CAMPO: Enlace (oculto por defecto) -->
                    <!-- ================================ -->
                    <div class="mb-3 d-none" id="campoEnlace">
                        <label for="url_externa" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa" name="url_externa"
                               placeholder="https://drive.google.com/file/d/...">
                        <div class="form-text">
                            <i class="bi bi-cloud me-1"></i>
                            Pegue el enlace compartido del archivo en la nube
                        </div>
                    </div>

                    <!-- ================================ -->
                    <!-- CAMPO: Descripcion (requerido)   -->
                    <!-- ================================ -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripcion <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required
                               placeholder="Ej: Registro de verificacion enero 2026, Acta de inspeccion...">
                        <div class="form-text">Breve descripcion del documento que esta adjuntando</div>
                    </div>

                    <!-- ================================ -->
                    <!-- CAMPO: Ano                       -->
                    <!-- ================================ -->
                    <div class="mb-3">
                        <label for="anio" class="form-label">Ano</label>
                        <select class="form-select" id="anio" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- ================================ -->
                    <!-- CAMPO: Observaciones (opcional)  -->
                    <!-- ================================ -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"
                                  placeholder="Notas adicionales, comentarios, referencias..."></textarea>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntar">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECCION 6: JAVASCRIPT                       -->
<!-- ============================================ -->
<script>
// ================================================
// Toggle entre Archivo y Enlace
// ================================================
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';

        // Mostrar/ocultar campos
        document.getElementById('campoArchivo').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlace').classList.toggle('d-none', isArchivo);

        // Cambiar campo requerido
        document.getElementById('archivo_soporte').required = isArchivo;
        document.getElementById('url_externa').required = !isArchivo;

        // Limpiar el campo que se oculta
        if (!isArchivo) {
            document.getElementById('archivo_soporte').value = '';
        } else {
            document.getElementById('url_externa').value = '';
        }
    });
});

// ================================================
// Feedback visual al enviar formulario
// ================================================
document.getElementById('formAdjuntar')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
```

---

## PASO 3: Modificar DocumentacionController.php

**Ubicacion:** `app/Controllers/DocumentacionController.php`

### 3.1 Agregar al Array de Tipos (linea ~303)

Buscar el array `in_array($tipoCarpetaFases, [...])` y agregar el nuevo tipo:

```php
// ANTES:
if (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst', ... 'custodia_historias_clinicas'])) {

// DESPUES:
if (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst', ... 'custodia_historias_clinicas', 'MI_NUEVO_TIPO'])) {
```

### 3.2 Agregar Filtro de Documentos (linea ~310-390)

Agregar un nuevo `elseif` para filtrar documentos por `tipo_documento`:

```php
} elseif ($tipoCarpetaFases === 'custodia_historias_clinicas') {
    // 3.1.5: Custodia historias clinicas
    $queryDocs->where('tipo_documento', 'soporte_custodia_hc');

// ========== AGREGAR AQUI ==========
} elseif ($tipoCarpetaFases === 'MI_NUEVO_TIPO') {
    // X.X.X: Descripcion del numeral
    $queryDocs->where('tipo_documento', 'soporte_mi_nuevo_tipo');
// ==================================

} elseif (isset($tipoDocBuscar)) {
    $queryDocs->where('tipo_documento', $tipoDocBuscar);
}
```

### 3.3 Agregar Deteccion de Carpeta (metodo determinarTipoCarpetaFases)

**IMPORTANTE:** El orden de las detecciones importa. Las mas especificas deben ir ANTES que las genericas.

```php
// ========== AGREGAR EN EL LUGAR CORRECTO ==========
// X.X.X. Nombre del numeral
if ($codigo === 'x.x.x' ||
    strpos($nombre, 'palabra_clave_1') !== false && strpos($nombre, 'palabra_clave_2') !== false ||
    strpos($nombre, 'otra_palabra') !== false) {
    return 'MI_NUEVO_TIPO';
}
// ==================================================
```

### Ejemplo Completo de Deteccion

```php
// 4.2.2. Verificacion de aplicacion de medidas de prevencion y control
if ($codigo === '4.2.2' ||
    strpos($nombre, 'verificacion') !== false && strpos($nombre, 'medidas') !== false ||
    strpos($nombre, 'verificacion') !== false && strpos($nombre, 'prevencion') !== false) {
    return 'verificacion_medidas_prevencion';
}
```

**Reglas para deteccion:**
1. Siempre incluir deteccion por `$codigo` exacto
2. Agregar 1-2 detecciones por palabras clave en `$nombre`
3. Usar `&&` para combinar palabras (AND logico)
4. Usar `||` para alternativas (OR logico)
5. `strpos()` retorna `false` si no encuentra, usar `!== false` para verificar

---

## PASO 4: Agregar Metodo en DocumentosSSTController.php

**Ubicacion:** `app/Controllers/DocumentosSSTController.php`

### 4.1 Agregar Metodo Usando el Patron Generico

Buscar el metodo `adjuntarSoporteGenerico()` y agregar un nuevo metodo ANTES de el:

```php
/**
 * Adjuntar soporte de [descripcion] (X.X.X)
 */
public function adjuntarSoporteMiNuevoTipo()
{
    return $this->adjuntarSoporteGenerico(
        'soporte_mi_nuevo_tipo',      // tipo_documento (BD)
        'SOP-MNT',                     // codigo (max 7 chars)
        'soporte_mi_nuevo_tipo_',      // prefijo archivo (con _ al final)
        'Soporte descripcion corta',   // descripcion para tbl_reporte
        'Soporte adjuntado exitosamente.' // mensaje flash
    );
}

/**
 * Metodo generico para adjuntar soportes (reutilizable)
 */
protected function adjuntarSoporteGenerico(...)
```

### 4.2 Parametros del Metodo Generico

| Parametro | Descripcion | Ejemplo |
|-----------|-------------|---------|
| `$tipoDocumento` | Valor para campo `tipo_documento` en BD | `'soporte_verificacion_medidas'` |
| `$prefijoCode` | Codigo visible, max 7 caracteres | `'SOP-VMP'` |
| `$prefijoArchivo` | Prefijo para nombre de archivo guardado | `'soporte_verificacion_'` |
| `$descripcionReporte` | Texto para `tbl_reporte.tipo_reporte` | `'Soporte verificacion medidas'` |
| `$mensajeExito` | Mensaje flash de exito | `'Soporte adjuntado exitosamente.'` |

---

## PASO 5: Agregar Ruta en Routes.php

**Ubicacion:** `app/Config/Routes.php`

Buscar la seccion de rutas de soportes y agregar:

```php
// Soportes de numerales SST
$routes->post('/documentos-sst/adjuntar-soporte-verificacion', 'DocumentosSSTController::adjuntarSoporteVerificacion');
$routes->post('/documentos-sst/adjuntar-soporte-auditoria', 'DocumentosSSTController::adjuntarSoporteAuditoria');
// ... rutas existentes ...

// ========== AGREGAR AQUI ==========
$routes->post('/documentos-sst/adjuntar-soporte-mi-nuevo', 'DocumentosSSTController::adjuntarSoporteMiNuevoTipo');
// ==================================

// Aprobacion y versionamiento de documentos SST
$routes->post('/documentos-sst/aprobar-documento', ...);
```

**IMPORTANTE:** La ruta en `action` del formulario debe coincidir exactamente con la ruta definida aqui.

---

## PASO 6: Verificar la Vista en el Formulario

En la vista creada (Paso 2), verificar que el `action` del formulario coincida con la ruta:

```php
<!-- En la vista -->
<form action="<?= base_url('documentos-sst/adjuntar-soporte-mi-nuevo') ?>" method="post" enctype="multipart/form-data">
```

```php
// En Routes.php
$routes->post('/documentos-sst/adjuntar-soporte-mi-nuevo', 'DocumentosSSTController::adjuntarSoporteMiNuevoTipo');
```

---

## Tablas de Base de Datos Utilizadas

El metodo generico inserta en estas tablas automaticamente:

### tbl_documentos_sst

| Campo | Descripcion | Ejemplo |
|-------|-------------|---------|
| `id_cliente` | FK al cliente | `1` |
| `tipo_documento` | Identificador del tipo | `'soporte_verificacion_medidas'` |
| `codigo` | Codigo visible | `'SOP-VMP-001'` |
| `titulo` | Descripcion ingresada | `'Registro verificacion enero'` |
| `anio` | Ano seleccionado | `2026` |
| `estado` | Estado del documento | `'aprobado'` |
| `observaciones` | Notas adicionales | `'Revision trimestral'` |
| `url_externa` | URL si es enlace | `'https://drive.google.com/...'` |

### tbl_doc_versiones_sst

| Campo | Descripcion | Ejemplo |
|-------|-------------|---------|
| `id_documento` | FK al documento | `123` |
| `version` | Numero de version | `1` |
| `version_texto` | Version legible | `'1.0'` |
| `archivo_pdf` | Ruta del archivo | `'/uploads/123456/soporte_xxx.pdf'` |
| `estado` | Estado de la version | `'vigente'` |

### tbl_reporte

| Campo | Descripcion | Ejemplo |
|-------|-------------|---------|
| `id_cliente` | FK al cliente | `1` |
| `tipo_reporte` | Descripcion del reporte | `'Soporte verificacion medidas'` |
| `descripcion` | Descripcion completa | `'Registro verificacion enero'` |
| `archivo_url` | URL o ruta | `'/uploads/123456/soporte_xxx.pdf'` |
| `estado` | Estado | `'publicado'` |

---

## Checklist de Implementacion

```
[ ] 1. Definir identificadores unicos:
    [ ] tipoCarpetaFases: ________________
    [ ] tipo_documento: ________________
    [ ] codigo (SOP-XXX): ________________
    [ ] ruta: ________________

[ ] 2. Crear vista PHP:
    [ ] Ubicacion: app/Views/documentacion/_tipos/{tipoCarpetaFases}.php
    [ ] Card con boton "Adjuntar Soporte"
    [ ] Alerta informativa
    [ ] Tabla ReportList
    [ ] Modal con toggle archivo/enlace
    [ ] JavaScript para toggle

[ ] 3. Modificar DocumentacionController.php:
    [ ] Agregar tipo al array in_array (linea ~303)
    [ ] Agregar filtro elseif (linea ~310-390)
    [ ] Agregar deteccion en determinarTipoCarpetaFases()

[ ] 4. Modificar DocumentosSSTController.php:
    [ ] Agregar metodo adjuntarSoporte{Nombre}()

[ ] 5. Modificar Routes.php:
    [ ] Agregar ruta POST

[ ] 6. Probar:
    [ ] Navegar a la carpeta en el sistema
    [ ] Verificar que carga la vista correcta
    [ ] Probar adjuntar archivo
    [ ] Probar adjuntar enlace
    [ ] Verificar que aparece en la tabla
    [ ] Verificar botones de accion
```

---

## Ejemplo Completo: Numeral 4.2.6 - Entrega de EPP

### Identificadores

```
tipoCarpetaFases: entrega_epp
tipo_documento:   soporte_entrega_epp
codigo:           SOP-EPP
ruta:             adjuntar-soporte-epp
```

### Vista: `app/Views/documentacion/_tipos/entrega_epp.php`

```php
<?php
/**
 * Vista de Tipo: 4.2.6 Entrega de EPP
 */
?>
<!-- [Contenido segun plantilla arriba] -->
<form action="<?= base_url('documentos-sst/adjuntar-soporte-epp') ?>" ...>
```

### DocumentacionController.php

```php
// En el array (linea ~303)
'entrega_epp'

// En los filtros (linea ~310-390)
} elseif ($tipoCarpetaFases === 'entrega_epp') {
    // 4.2.6: Entrega de EPP
    $queryDocs->where('tipo_documento', 'soporte_entrega_epp');
}

// En determinarTipoCarpetaFases()
if ($codigo === '4.2.6' ||
    strpos($nombre, 'entrega') !== false && strpos($nombre, 'epp') !== false ||
    strpos($nombre, 'elementos') !== false && strpos($nombre, 'proteccion') !== false) {
    return 'entrega_epp';
}
```

### DocumentosSSTController.php

```php
public function adjuntarSoporteEPP()
{
    return $this->adjuntarSoporteGenerico(
        'soporte_entrega_epp',
        'SOP-EPP',
        'soporte_entrega_epp_',
        'Soporte entrega EPP',
        'Soporte de entrega de EPP adjuntado exitosamente.'
    );
}
```

### Routes.php

```php
$routes->post('/documentos-sst/adjuntar-soporte-epp', 'DocumentosSSTController::adjuntarSoporteEPP');
```

---

## Solucion de Problemas Comunes

### 1. Vista no carga (muestra vista generica)

**Causa:** La deteccion en `determinarTipoCarpetaFases()` no reconoce la carpeta.

**Solucion:**
- Verificar que el `$codigo` coincide exactamente (case-sensitive)
- Agregar mas condiciones de palabras clave
- Verificar orden de detecciones (especificas antes que genericas)

### 2. Tabla vacia aunque hay documentos

**Causa:** El filtro de `tipo_documento` no coincide.

**Solucion:**
- Verificar que el `tipo_documento` en el filtro coincide con el usado en el metodo del controlador
- Revisar en BD: `SELECT * FROM tbl_documentos_sst WHERE tipo_documento = 'soporte_xxx'`

### 3. Error 404 al adjuntar

**Causa:** La ruta no esta definida o no coincide.

**Solucion:**
- Verificar que la ruta en Routes.php existe
- Verificar que el `action` del formulario coincide exactamente
- Limpiar cache: `php spark cache:clear`

### 4. Deteccion incorrecta (carga otra vista)

**Causa:** Otra deteccion mas arriba captura la carpeta primero.

**Solucion:**
- Mover la deteccion mas especifica ANTES de las genericas
- Ejemplo: `1.2.3` (curso 50h) debe ir ANTES de `1.1.1` (responsable)

---

## Listado de Modulos Implementados

| Numeral | tipoCarpetaFases | tipo_documento | Codigo |
|---------|------------------|----------------|--------|
| 1.1.4 | afiliacion_srl | planilla_afiliacion_srl | SOP-ARL |
| 1.1.6 | conformacion_copasst | soporte_conformacion_copasst | SOP-COP |
| 1.1.8 | comite_convivencia | soporte_comite_convivencia | SOP-CCV |
| 1.2.3 | responsables_curso_50h | soporte_curso_50h | SOP-C50 |
| 2.3.1 | evaluacion_prioridades | soporte_evaluacion_prioridades | SOP-EVP |
| 2.4.1 | plan_objetivos_metas | soporte_plan_objetivos | SOP-POM |
| 2.6.1 | rendicion_desempeno | soporte_rendicion_desempeno | SOP-RDD |
| 3.1.1 | diagnostico_condiciones_salud | soporte_diagnostico_salud | SOP-DCS |
| 3.1.3 | informacion_medico_perfiles | soporte_perfiles_medico | SOP-IPM |
| 3.1.4 | evaluaciones_medicas | soporte_evaluaciones_medicas | SOP-EMO |
| 3.1.5 | custodia_historias_clinicas | soporte_custodia_hc | SOP-CHC |
| 3.1.8 | agua_servicios_sanitarios | soporte_agua_servicios | SOP-AGU |
| 3.1.9 | eliminacion_residuos | soporte_eliminacion_residuos | SOP-RES |
| 4.1.4 | mediciones_ambientales | soporte_mediciones_ambientales | SOP-MAM |
| 4.2.1 | medidas_prevencion_control | soporte_medidas_prevencion_control | SOP-MPC |
| 4.2.2 | verificacion_medidas_prevencion | soporte_verificacion_medidas | SOP-VMP |
| 4.2.6 | entrega_epp | soporte_entrega_epp | SOP-EPP |
| 5.1.1 | plan_emergencias | soporte_plan_emergencias | SOP-PEM |
| 5.1.2 | brigada_emergencias | soporte_brigada_emergencias | SOP-BRI |
| 6.1.3 | revision_direccion | soporte_revision_direccion | SOP-RDI |
| 6.1.4 | planificacion_auditorias_copasst | soporte_planificacion_auditoria | SOP-AUD |

---

*Documento generado: 2026-02-04*
*Version: 1.0*
