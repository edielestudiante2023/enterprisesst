# Implementación: Adjuntar Archivo/Enlace + Tabla de Documentos

## Resumen
Esta guía documenta cómo implementar la funcionalidad de "Adjuntar Archivo/Enlace" con tabla de documentos en cualquier carpeta del módulo de numerales SG-SST.

---

## 1. Archivos a Modificar

| Archivo | Propósito |
|---------|-----------|
| `app/Controllers/DocumentacionController.php` | Agregar tipo de carpeta en `determinarTipoCarpetaFases()` y en query de documentos |
| `app/Controllers/DocumentosSSTController.php` | Crear método `adjuntarSoporte{Nombre}()` |
| `app/Config/Routes.php` | Registrar ruta POST |
| `app/Views/documentacion/_tipos/{nombre}.php` | Crear vista con modal y tabla |

---

## 2. Paso a Paso

### 2.1. DocumentacionController.php

#### A) En `determinarTipoCarpetaFases()` (~línea 530)

Agregar el mapeo del código de carpeta al tipo:

```php
// 1.1.7. Capacitación COPASST
if ($codigo === '1.1.7') {
    return 'capacitacion_copasst';  // CAMBIAR de null a 'capacitacion_copasst'
}
```

#### B) En el array de carpetas con tabla (~línea 304)

Agregar el nuevo tipo al array `in_array()`:

```php
if (in_array($tipoCarpetaFases, [
    'capacitacion_sst',
    'responsables_sst',
    // ... otros tipos existentes ...
    'capacitacion_copasst',  // AGREGAR
])) {
```

#### C) En el switch de filtrado por tipo_documento (~línea 310-410)

Agregar nuevo `elseif`:

```php
} elseif ($tipoCarpetaFases === 'capacitacion_copasst') {
    // 1.1.7: Capacitación COPASST
    $queryDocs->where('tipo_documento', 'soporte_capacitacion_copasst');
}
```

#### D) En soportes adicionales (~línea 467-496)

Agregar bloque para cargar soportes:

```php
} elseif ($tipoCarpetaFases === 'capacitacion_copasst') {
    // 1.1.7 Capacitación COPASST
    $db = $db ?? \Config\Database::connect();
    $soportesAdicionales = $db->table('tbl_documentos_sst')
        ->where('id_cliente', $cliente['id_cliente'])
        ->where('tipo_documento', 'soporte_capacitacion_copasst')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->getResultArray();
}
```

---

### 2.2. DocumentosSSTController.php

Agregar método wrapper que usa `adjuntarSoporteGenerico()`:

```php
/**
 * 1.1.7 - Adjuntar soporte de Capacitación COPASST
 */
public function adjuntarSoporteCapacitacionCopasst()
{
    return $this->adjuntarSoporteGenerico(
        'soporte_capacitacion_copasst',           // tipo_documento en BD
        'SOP-CCP',                                // prefijo código
        'soporte_capacitacion_copasst_',          // prefijo archivo
        'Soporte de Capacitación COPASST',        // descripción para reporte
        'Soporte de capacitación COPASST adjuntado exitosamente.'  // mensaje éxito
    );
}
```

---

### 2.3. Routes.php

Agregar ruta POST (~línea 850):

```php
// 1.1.7 - Capacitación COPASST
$routes->post('/documentos-sst/adjuntar-soporte-capacitacion-copasst',
              'DocumentosSSTController::adjuntarSoporteCapacitacionCopasst');
```

---

### 2.4. Vista: `app/Views/documentacion/_tipos/capacitacion_copasst.php`

Crear archivo con:

1. **Encabezado informativo** (card con descripción del numeral)
2. **Botón "Adjuntar Soporte"** que abre modal
3. **Modal con toggle Archivo/Enlace**
4. **Tabla de soportes adjuntados**
5. **JavaScript para toggle**

Ver plantilla completa en sección 4.

---

## 3. Estructura de Datos

### Tabla: `tbl_documentos_sst`

| Campo | Valor para Archivo | Valor para Enlace |
|-------|-------------------|-------------------|
| `tipo_documento` | `'soporte_capacitacion_copasst'` | `'soporte_capacitacion_copasst'` |
| `codigo` | `'SOP-CCP-001'` | `'SOP-CCP-002'` |
| `titulo` | Descripción del usuario | Descripción del usuario |
| `estado` | `'aprobado'` | `'aprobado'` |
| `archivo_pdf` | URL pública del archivo | `NULL` |
| `url_externa` | `NULL` | URL del enlace |
| `contenido` | JSON con metadata | JSON + `es_enlace_externo: true` |

### Ruta de almacenamiento de archivos
```
FCPATH/uploads/{NIT_CLIENTE}/soporte_capacitacion_copasst_YYYYMMDD_HHMMSS.{ext}
```

---

## 4. Plantilla Vista Completa

```php
<?php
/**
 * Vista: 1.1.7 Capacitación COPASST
 * Permite adjuntar soportes de capacitación (archivos o enlaces)
 */
?>

<!-- Card informativo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-mortarboard me-2"></i>1.1.7. Capacitación COPASST
        </h5>
    </div>
    <div class="card-body">
        <p class="mb-3">
            <strong>Requisito:</strong> Registro de las capacitaciones realizadas al COPASST
            o Vigía SST según el tamaño de la empresa.
        </p>

        <!-- Botón adjuntar -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarCapacitacionCopasst">
            <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
        </button>
    </div>
</div>

<!-- Tabla de soportes -->
<?php if (!empty($soportesAdicionales)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>Soportes Adjuntados</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Código</th>
                        <th>Descripción</th>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 90px;">Tipo</th>
                        <th style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($soportesAdicionales as $soporte): ?>
                        <?php $esEnlace = !empty($soporte['url_externa']); ?>
                        <tr>
                            <td><code><?= esc($soporte['codigo']) ?></code></td>
                            <td><strong><?= esc($soporte['titulo']) ?></strong></td>
                            <td><small><?= date('d/m/Y', strtotime($soporte['created_at'])) ?></small></td>
                            <td>
                                <?php if ($esEnlace): ?>
                                    <span class="badge bg-info"><i class="bi bi-link-45deg"></i> Enlace</span>
                                <?php else: ?>
                                    <span class="badge bg-dark"><i class="bi bi-file-earmark"></i> Archivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= esc($esEnlace ? $soporte['url_externa'] : $soporte['archivo_pdf']) ?>"
                                   class="btn btn-sm btn-outline-primary" target="_blank" title="Ver/Descargar">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-light border text-center">
    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
    <p class="text-muted mb-0 mt-2">No hay soportes adjuntados aún.</p>
</div>
<?php endif; ?>

<!-- Modal Adjuntar -->
<div class="modal fade" id="modalAdjuntarCapacitacionCopasst" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('documentos-sst/adjuntar-soporte-capacitacion-copasst') ?>"
                  method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte de Capacitación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Toggle Archivo/Enlace -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga"
                                   id="tipoCargaArchivoCC" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoCC">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga"
                                   id="tipoCargaEnlaceCC" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceCC">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoCC">
                        <label class="form-label">Archivo</label>
                        <input type="file" class="form-control" name="archivo_soporte"
                               id="archivo_soporte_cc" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <small class="text-muted">PDF, imágenes, Excel o Word. Máximo 10MB.</small>
                    </div>

                    <!-- Campo Enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceCC">
                        <label class="form-label">URL del Enlace</label>
                        <input type="url" class="form-control" name="url_externa"
                               id="url_externa_cc" placeholder="https://...">
                        <small class="text-muted">Enlace a documento en Google Drive, OneDrive, etc.</small>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label class="form-label">Descripción <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="descripcion" required
                               placeholder="Ej: Acta de capacitación COPASST - Enero 2025">
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2"
                                  placeholder="Notas adicionales (opcional)"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Toggle -->
<script>
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const campoArchivo = document.getElementById('campoArchivoCC');
        const campoEnlace = document.getElementById('campoEnlaceCC');
        const inputArchivo = document.getElementById('archivo_soporte_cc');
        const inputEnlace = document.getElementById('url_externa_cc');

        if (this.value === 'archivo') {
            campoArchivo.classList.remove('d-none');
            campoEnlace.classList.add('d-none');
            inputArchivo.required = true;
            inputEnlace.required = false;
            inputEnlace.value = '';
        } else {
            campoArchivo.classList.add('d-none');
            campoEnlace.classList.remove('d-none');
            inputArchivo.required = false;
            inputEnlace.required = true;
        }
    });
});
</script>
```

---

## 5. Validaciones del Backend

El método `adjuntarSoporteGenerico()` ya incluye:

| Validación | Detalle |
|------------|---------|
| Cliente existe | Busca en `tbl_clientes` por `id_cliente` |
| Descripción requerida | Campo `descripcion` no vacío |
| Archivo válido | `isValid()` + tipo MIME permitido |
| Tamaño máximo | 10MB (10 * 1024 * 1024 bytes) |
| URL válida | `filter_var($url, FILTER_VALIDATE_URL)` |

### Tipos MIME permitidos:
- `application/pdf`
- `image/jpeg`, `image/png`, `image/jpg`
- `application/vnd.ms-excel`
- `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- `application/msword`
- `application/vnd.openxmlformats-officedocument.wordprocessingml.document`

---

## 6. Errores Comunes y Soluciones

### Error: "Carpeta no encontrada" o vista genérica
**Causa:** El tipo de carpeta no está registrado en `determinarTipoCarpetaFases()`
**Solución:** Verificar que el código de carpeta coincide exactamente (ej: '1.1.7')

### Error: "El archivo no es válido"
**Causa:** Tipo MIME no permitido o archivo corrupto
**Solución:** Verificar extensión y que el archivo no esté dañado

### Error: Tabla vacía aunque hay registros
**Causa:** El `tipo_documento` no coincide en el filtro del controller
**Solución:** Verificar que el tipo en `adjuntarSoporteGenerico()` coincide con el filtro en `carpeta()`

### Error: Vista no carga
**Causa:** El archivo de vista no existe o tiene nombre incorrecto
**Solución:** El nombre debe coincidir con el valor retornado por `determinarTipoCarpetaFases()`

---

## 7. Checklist de Implementación

- [ ] Modificar `determinarTipoCarpetaFases()` - retornar el tipo correcto
- [ ] Agregar tipo al array de carpetas con tabla
- [ ] Agregar `elseif` para filtrar documentos por tipo
- [ ] Agregar bloque para cargar `$soportesAdicionales`
- [ ] Crear método en `DocumentosSSTController`
- [ ] Registrar ruta POST en `Routes.php`
- [ ] Crear archivo de vista en `_tipos/`
- [ ] Probar adjuntar archivo
- [ ] Probar adjuntar enlace
- [ ] Verificar que aparece en la tabla

---

## 8. Historial de Errores y Aprendizajes

### Fecha: 2025-02-05 - Implementación inicial 1.1.7

*Se actualizará con errores encontrados durante la implementación...*

