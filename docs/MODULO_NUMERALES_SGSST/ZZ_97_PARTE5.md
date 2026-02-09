# INSTRUCTIVO PARTE 5: UX/UI y Experiencia de Usuario

## Resumen Ejecutivo

La **Parte 5** documenta todos los patrones de **UX/UI** utilizados en el módulo de 3 partes para garantizar una experiencia de usuario consistente y profesional.

Este documento es una **referencia obligatoria** para mantener la coherencia visual y funcional al crear nuevos módulos.

---

## Arquitectura de 3 Partes (Recordatorio)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        MÓDULO DE 3 PARTES                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐         │
│  │     PARTE 1     │───▶│     PARTE 2     │───▶│     PARTE 3     │         │
│  │   Actividades   │    │   Indicadores   │    │   Documento     │         │
│  │   (Generador)   │    │   (Generador)   │    │   (Formal)      │         │
│  └─────────────────┘    └─────────────────┘    └─────────────────┘         │
│                                                                             │
│  Esta Parte 5 documenta los patrones UX/UI comunes a las 3 partes          │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 1. Flujo de Navegación Completo

### 1.1 Mapa de Navegación

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         FLUJO DE NAVEGACIÓN                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  /documentacion/{id_cliente}                                                │
│         │                                                                   │
│         ├──▶ Panel de Documentación (vista principal)                       │
│         │         │                                                         │
│         │         ├──▶ Card del Documento                                   │
│         │         │         │                                               │
│         │         │         ├──▶ Botón "Generar Actividades" (Parte 1)      │
│         │         │         │         └──▶ /generador-ia/{id}/[nombre]      │
│         │         │         │                                               │
│         │         │         ├──▶ Botón "Generar Indicadores" (Parte 2)      │
│         │         │         │         └──▶ /generador-ia/{id}/indicadores-X │
│         │         │         │                                               │
│         │         │         └──▶ Botón "Generar Documento" (Parte 3)        │
│         │         │                   └──▶ /documentos/generar/{tipo}/{id}  │
│         │         │                                                         │
│         │         └──▶ Botón "Volver" → /consultant/clientes                │
│         │                                                                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Estructura de URLs

| Parte | Patrón de URL | Ejemplo |
|-------|---------------|---------|
| **Panel** | `/documentacion/{id}` | `/documentacion/18` |
| **Parte 1** | `/generador-ia/{id}/[nombre-doc]` | `/generador-ia/18/objetivos-sgsst` |
| **Parte 2** | `/generador-ia/{id}/indicadores-[nombre]` | `/generador-ia/18/indicadores-objetivos` |
| **Parte 3** | `/documentos/generar/{tipo}/{id}` | `/documentos/generar/plan_objetivos_metas/18` |

### 1.3 Botón "Volver" Consistente

Todas las vistas del generador incluyen un botón "Volver" en el navbar:

```php
<!-- En el navbar de cada vista del generador -->
<a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>"
   class="btn btn-outline-light btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Volver
</a>
```

**Regla UX**: El botón "Volver" siempre regresa al panel de documentación, nunca a la vista anterior del navegador.

### 1.4 Indicadores de Prerequisitos

Cuando una fase requiere que la anterior esté completa:

```php
<!-- Alerta de prerequisito en Parte 2 -->
<?php if (count($actividadesParte1) < 3): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Prerequisito:</strong> Debes generar al menos 3 actividades en la
    <a href="<?= base_url('generador-ia/'.$idCliente.'/[nombre-doc]') ?>">Parte 1</a>
    antes de continuar.
</div>
<?php endif; ?>
```

---

## 2. Componentes UI Reutilizables

### 2.1 Toast de Notificaciones

El sistema usa un componente Toast de Bootstrap para todas las notificaciones:

```html
<!-- Toast Container (colocar antes de cerrar </body>) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="toastNotification" class="toast" role="alert">
        <div class="toast-header">
            <i class="bi me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Notificación</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastBody"></div>
    </div>
</div>
```

```javascript
/**
 * Muestra una notificación toast
 * @param {string} type - 'success', 'error', 'info', 'warning'
 * @param {string} title - Título del toast
 * @param {string} message - Mensaje (puede contener HTML)
 */
function showToast(type, title, message) {
    const toast = document.getElementById('toastNotification');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastBody = document.getElementById('toastBody');

    // Reset classes
    toast.className = 'toast';

    // Configurar según tipo
    const config = {
        'success': { icon: 'bi-check-circle-fill text-success', border: 'border-success' },
        'error':   { icon: 'bi-x-circle-fill text-danger',      border: 'border-danger' },
        'warning': { icon: 'bi-exclamation-triangle-fill text-warning', border: 'border-warning' },
        'info':    { icon: 'bi-info-circle-fill text-primary',  border: 'border-primary' }
    };

    const cfg = config[type] || config['info'];
    toastIcon.className = `bi ${cfg.icon} me-2`;
    toast.classList.add(cfg.border);

    toastTitle.textContent = title;
    toastBody.innerHTML = message;

    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();
}
```

**Uso**:
```javascript
showToast('success', 'Guardado', 'Los cambios se guardaron correctamente');
showToast('error', 'Error', 'No se pudo conectar con el servidor');
showToast('warning', 'Atención', 'Excede el límite permitido');
showToast('info', 'Info', 'Procesando solicitud...');
```

### 2.2 Modal de Preview con Selección

El modal de preview es el componente principal para mostrar items generados por IA:

```html
<!-- Modal de Preview -->
<div class="modal fade" id="modalPreview" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <!-- Header con color según contexto -->
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Seleccionar Items
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Body dinámico -->
            <div class="modal-body" id="previewContent">
                <!-- Contenido cargado dinámicamente -->
                <div class="text-center py-4">
                    <div class="spinner-border text-success"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>

            <!-- Footer con contador y acciones -->
            <div class="modal-footer justify-content-between">
                <div>
                    <span id="contadorSeleccion" class="text-muted">
                        0 items seleccionados
                    </span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success"
                            id="btnEnviarSeleccionados"
                            onclick="enviarSeleccionados()">
                        <i class="bi bi-send me-1"></i>Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Colores de Header según Parte**:
| Parte | Color Header | Clase |
|-------|-------------|-------|
| Parte 1 (Actividades) | Verde | `bg-success` |
| Parte 2 (Indicadores) | Morado | `bg-purple` o `style="background:#9c27b0"` |
| Parte 3 (Documento) | Azul | `bg-primary` |

### 2.3 Card con Checkbox para Selección

Patrón estándar para items seleccionables:

```html
<div class="card mb-3 item-card border-start border-4 border-primary" data-idx="0">
    <div class="card-body py-3">
        <div class="d-flex align-items-start">

            <!-- ═══════════════════════════════════════════════════
                 CHECKBOX PARA SELECCIONAR
                 ═══════════════════════════════════════════════════ -->
            <div class="form-check me-3 pt-1">
                <input type="checkbox"
                       class="form-check-input item-check"
                       data-idx="0"
                       checked
                       onchange="actualizarContador()">
            </div>

            <!-- Contenido del item -->
            <div class="flex-grow-1">
                <!-- Título editable -->
                <input type="text"
                       class="form-control form-control-sm fw-bold item-titulo"
                       data-idx="0"
                       value="Título del item">

                <!-- Descripción editable -->
                <textarea class="form-control form-control-sm mt-2 item-descripcion"
                          data-idx="0"
                          rows="2">Descripción...</textarea>

                <!-- Campos adicionales en grid -->
                <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-flag"></i>
                            </span>
                            <input type="text" class="form-control item-meta"
                                   data-idx="0" placeholder="Meta">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select form-select-sm item-tipo" data-idx="0">
                            <option value="opcion1">Opción 1</option>
                            <option value="opcion2">Opción 2</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 2.4 Panel Colapsable "Mejorar con IA"

Cada item tiene un panel para regenerarlo con instrucciones específicas:

```html
<!-- Panel colapsable IA (dentro de cada card) -->
<div class="border-top pt-2 mt-2">
    <div class="d-flex align-items-center justify-content-between">
        <button type="button"
                class="btn btn-sm btn-link text-decoration-none p-0"
                onclick="toggleIAPanel(0)">
            <i class="bi bi-robot me-1"></i>
            <small>Mejorar con IA</small>
            <i class="bi bi-chevron-down ms-1" id="iaChevron0"></i>
        </button>
    </div>

    <div class="collapse mt-2" id="iaPanelItem0">
        <div class="card card-body bg-light border-0 p-2">
            <div class="mb-2">
                <textarea class="form-control form-control-sm instrucciones-ia"
                          data-idx="0" rows="2"
                          placeholder="Ej: Hazlo más específico, enfoca en X..."></textarea>
            </div>
            <button type="button"
                    class="btn btn-sm btn-outline-purple w-100"
                    style="border-color:#9c27b0; color:#9c27b0;"
                    onclick="regenerarConIA(0)">
                <i class="bi bi-magic me-1"></i>Regenerar este item
            </button>
        </div>
    </div>
</div>
```

```javascript
/**
 * Toggle del panel IA para un item
 */
function toggleIAPanel(idx) {
    const panel = document.getElementById(`iaPanelItem${idx}`);
    const chevron = document.getElementById(`iaChevron${idx}`);

    if (panel.classList.contains('show')) {
        panel.classList.remove('show');
        chevron.classList.remove('bi-chevron-up');
        chevron.classList.add('bi-chevron-down');
    } else {
        panel.classList.add('show');
        chevron.classList.remove('bi-chevron-down');
        chevron.classList.add('bi-chevron-up');
    }
}
```

### 2.5 Controles de Selección Masiva

Botones para seleccionar/deseleccionar todos los items:

```html
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <strong>Total: <span id="totalItems">5</span> items sugeridos</strong>
        <small class="text-muted ms-2">(límite: <span id="limite">3</span>)</small>
    </div>
    <div>
        <button type="button" class="btn btn-sm btn-outline-primary me-1"
                onclick="seleccionarTodos(true)">
            <i class="bi bi-check-all me-1"></i>Seleccionar Todos
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="seleccionarTodos(false)">
            <i class="bi bi-x-lg me-1"></i>Deseleccionar
        </button>
    </div>
</div>
```

```javascript
/**
 * Seleccionar o deseleccionar todos los items
 */
function seleccionarTodos(seleccionar) {
    document.querySelectorAll('.item-check').forEach(cb => {
        cb.checked = seleccionar;
    });
    actualizarContador();
}
```

### 2.6 Card de Contexto del Cliente

Card estándar para mostrar información del cliente a la IA:

```html
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-cpu text-primary me-2"></i>Contexto para la IA
        </h6>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="collapse" data-bs-target="#collapseContexto">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="collapse show" id="collapseContexto">
        <div class="card-body">
            <div class="row">
                <!-- Datos del Cliente -->
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-building me-1"></i>Datos de la Empresa
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width:40%">Actividad económica:</td>
                            <td><strong><?= esc($contexto['actividad_economica']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nivel de riesgo ARL:</td>
                            <td>
                                <span class="badge bg-<?= $colorRiesgo ?>">
                                    <?= $contexto['nivel_riesgo'] ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total trabajadores:</td>
                            <td><strong><?= $contexto['numero_trabajadores'] ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Estándares aplicables:</td>
                            <td><span class="badge bg-info"><?= $estandares ?> estándares</span></td>
                        </tr>
                    </table>
                </div>

                <!-- Límites -->
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-sliders me-1"></i>Límites según Estándares
                    </h6>
                    <div class="alert alert-light border small">
                        <div class="row">
                            <div class="col-12">
                                <strong class="text-success"><?= $limite ?></strong> items máximo
                            </div>
                        </div>
                        <hr class="my-2">
                        <small class="text-muted">
                            Según Res. 0312/2019: 7 est. = X, 21 est. = Y, 60 est. = Z
                        </small>
                    </div>
                </div>
            </div>

            <hr class="my-3">

            <!-- Instrucciones adicionales -->
            <div class="row">
                <div class="col-12">
                    <label class="form-label">
                        <i class="bi bi-chat-dots me-1"></i>Instrucciones adicionales
                        <small class="text-muted">(opcional)</small>
                    </label>
                    <textarea id="instruccionesIA" class="form-control" rows="3"
                        placeholder="Describa necesidades específicas..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 3. Patrones de Interacción

### 3.1 Selección con Checkbox

**Reglas de UX**:

1. **Todos seleccionados por defecto**: Al abrir el preview, todos los items vienen con `checked`
2. **Contador dinámico**: El footer muestra cuántos items están seleccionados
3. **Validación de límite**: Si se excede el límite, el botón "Enviar" se deshabilita
4. **Feedback inmediato**: El contador se actualiza al instante con `onchange`

```javascript
/**
 * Actualiza el contador y valida el límite
 */
function actualizarContador() {
    const total = document.querySelectorAll('.item-check:checked').length;
    document.getElementById('contadorSeleccion').textContent =
        `${total} items seleccionados`;

    const btnEnviar = document.getElementById('btnEnviarSeleccionados');
    const limite = parseInt(document.getElementById('limite').textContent);

    if (total === 0) {
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<i class="bi bi-send me-1"></i>Seleccione items';
    } else if (total > limite) {
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Excede límite (${limite})`;
    } else {
        btnEnviar.disabled = false;
        btnEnviar.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${total} items`;
    }
}
```

### 3.2 Edición Inline

Todos los campos son editables directamente en el preview:

**Reglas de UX**:

1. **Campos `form-control-sm`**: Inputs y selects pequeños para no ocupar mucho espacio
2. **Placeholder descriptivo**: Indica qué tipo de información va en cada campo
3. **data-idx**: Atributo para identificar a qué item pertenece cada campo
4. **No guardar automáticamente**: Los cambios se envían solo al hacer clic en "Enviar"

```javascript
/**
 * Obtiene los datos editados de un item
 */
function getItemData(idx) {
    return {
        titulo: document.querySelector(`.item-titulo[data-idx="${idx}"]`).value,
        descripcion: document.querySelector(`.item-descripcion[data-idx="${idx}"]`).value,
        meta: document.querySelector(`.item-meta[data-idx="${idx}"]`).value,
        tipo: document.querySelector(`.item-tipo[data-idx="${idx}"]`).value
    };
}
```

### 3.3 Confirmación antes de Eliminar

Siempre usar `confirm()` antes de acciones destructivas:

```javascript
/**
 * Eliminar un item
 */
function eliminarItem(id) {
    if (!confirm('¿Eliminar este item?')) return;

    // Proceder con la eliminación...
}

/**
 * Eliminar todos los items
 */
function eliminarTodos() {
    if (!confirm('¿Eliminar TODOS los items? Esta acción no se puede deshacer.')) return;

    // Proceder con la eliminación...
}
```

### 3.4 Spinner en Botones durante Carga

Patrón estándar para mostrar que una acción está en progreso:

```javascript
/**
 * Patrón de fetch con spinner en botón
 */
async function accionConSpinner(btn, endpoint, data) {
    const btnOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            showToast('success', 'Éxito', result.message || 'Operación completada');
            // Recargar o actualizar UI
        } else {
            showToast('error', 'Error', result.message || 'Ocurrió un error');
            btn.disabled = false;
            btn.innerHTML = btnOriginal;
        }
    } catch (error) {
        showToast('error', 'Error de Conexión', 'No se pudo conectar con el servidor');
        btn.disabled = false;
        btn.innerHTML = btnOriginal;
    }
}
```

---

## 4. Feedback Visual y Colores

### 4.1 Colores por Estado

| Estado | Color | Clase Bootstrap | Uso |
|--------|-------|-----------------|-----|
| Pendiente | Amarillo | `bg-warning` | Items sin completar |
| En Proceso | Azul | `bg-info` | Generando con IA |
| Completado | Verde | `bg-success` | Items guardados |
| Error | Rojo | `bg-danger` | Falló la operación |
| Inactivo | Gris | `bg-secondary` | Items deshabilitados |

### 4.2 Colores PHVA (Ciclo Deming)

| Fase | Color | Clase | Hex |
|------|-------|-------|-----|
| PLANEAR | Azul | `bg-primary` | `#0d6efd` |
| HACER | Verde | `bg-success` | `#198754` |
| VERIFICAR | Amarillo | `bg-warning` | `#ffc107` |
| ACTUAR | Rojo | `bg-danger` | `#dc3545` |

```javascript
const phvaColors = {
    'PLANEAR': 'primary',
    'HACER': 'success',
    'VERIFICAR': 'warning',
    'ACTUAR': 'danger'
};

// Uso
const color = phvaColors[item.phva] || 'secondary';
```

### 4.3 Colores de Nivel de Riesgo ARL

```php
<?php
$colorRiesgo = match($nivel_riesgo) {
    'I', 'II' => 'success',   // Bajo
    'III' => 'warning',       // Medio
    'IV', 'V' => 'danger',    // Alto
    default => 'secondary'
};
?>
<span class="badge bg-<?= $colorRiesgo ?>"><?= $nivel_riesgo ?></span>
```

### 4.4 Animación de Éxito Temporal

Cuando un item se actualiza exitosamente:

```javascript
// Feedback visual: borde verde temporal
const card = document.querySelector(`.item-card[data-idx="${idx}"]`);
card.classList.add('border-success');
setTimeout(() => card.classList.remove('border-success'), 2000);
```

### 4.5 Estados de Botón

| Estado | Apariencia | Código |
|--------|------------|--------|
| Normal | Activo | `<button class="btn btn-success">Enviar</button>` |
| Cargando | Spinner | `<button disabled><span class="spinner-border spinner-border-sm"></span> Enviando...</button>` |
| Deshabilitado | Gris | `<button disabled class="btn btn-secondary">Seleccione items</button>` |
| Error | Rojo | `<button disabled class="btn btn-danger">Excede límite</button>` |

---

## 5. Estructura de Respuestas API (JSON)

### 5.1 Formato Estándar

Todas las respuestas API siguen este formato:

```json
{
    "success": true,
    "message": "Operación completada exitosamente",
    "data": {
        // Datos específicos de la respuesta
    }
}
```

**Error**:
```json
{
    "success": false,
    "message": "Descripción del error",
    "data": null
}
```

### 5.2 Respuestas Específicas

**Preview de Actividades/Indicadores**:
```json
{
    "success": true,
    "message": "Actividades generadas",
    "data": {
        "actividades": [
            {
                "actividad": "Título de la actividad",
                "descripcion": "Descripción detallada",
                "meta": "Meta cuantificable",
                "responsable": "Responsable SST",
                "phva": "HACER",
                "periodicidad": "Trimestral"
            }
        ],
        "limite": 5,
        "existentes": 2
    }
}
```

**Guardar Items**:
```json
{
    "success": true,
    "message": "3 actividades guardadas correctamente",
    "data": {
        "creadas": 3,
        "existentes": 0,
        "errores": 0
    }
}
```

**Regenerar con IA**:
```json
{
    "success": true,
    "message": "Item regenerado",
    "data": {
        "actividad": "Nuevo título mejorado",
        "descripcion": "Nueva descripción mejorada",
        "meta": "Nueva meta",
        "responsable": "Responsable SST"
    }
}
```

### 5.3 Códigos de Error Comunes

| Código | Mensaje | Causa |
|--------|---------|-------|
| `LIMITE_EXCEDIDO` | "Excede el límite de X items" | Intentó guardar más items de los permitidos |
| `PREREQUISITO_FALTANTE` | "Complete primero la Parte 1" | Intentó acceder a Parte 2 sin Parte 1 |
| `CLIENTE_NO_ENCONTRADO` | "Cliente no encontrado" | ID de cliente inválido |
| `IA_ERROR` | "Error al generar con IA" | Fallo en la API de OpenAI/Anthropic |
| `VALIDACION_FALLIDA` | "Datos inválidos" | Campos requeridos faltantes |

### 5.4 Manejo de Errores en Frontend

```javascript
/**
 * Patrón estándar de fetch con manejo de errores
 */
async function fetchAPI(endpoint, options = {}) {
    try {
        const response = await fetch(endpoint, {
            headers: { 'Content-Type': 'application/json' },
            ...options
        });

        const data = await response.json();

        if (!data.success) {
            showToast('error', 'Error', data.message || 'Ocurrió un error');
            return null;
        }

        return data;

    } catch (error) {
        console.error('Error de fetch:', error);
        showToast('error', 'Error de Conexión', 'No se pudo conectar con el servidor');
        return null;
    }
}

// Uso
const resultado = await fetchAPI('/api/endpoint', {
    method: 'POST',
    body: JSON.stringify({ data: 'valor' })
});

if (resultado) {
    // Procesar resultado exitoso
}
```

---

## 6. Funciones JavaScript Reutilizables

### 6.1 Utilidades Básicas

```javascript
/**
 * Escapa HTML para prevenir XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Obtiene las instrucciones adicionales del textarea
 */
function getInstruccionesIA() {
    const textarea = document.getElementById('instruccionesIA');
    return textarea ? textarea.value.trim() : '';
}

/**
 * Formatea fecha para mostrar
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}
```

### 6.2 Variables Globales Estándar

```javascript
// Variables que deben estar en todas las vistas del generador
const idCliente = <?= $cliente['id_cliente'] ?>;
const anio = <?= $anio ?>;
const limite = <?= $limite ?>;
const estandares = <?= $estandares ?>;

// Array para almacenar datos temporales
let itemsData = [];
```

### 6.3 Patrón de Regenerar Item con IA

```javascript
/**
 * Regenera un item específico con instrucciones de IA
 */
async function regenerarConIA(idx) {
    const instrucciones = document.querySelector(`.instrucciones-ia[data-idx="${idx}"]`).value;
    const itemActual = getItemData(idx);

    if (!instrucciones.trim()) {
        showToast('info', 'Instrucciones', 'Escriba instrucciones para la IA');
        return;
    }

    const btn = event.target;
    const btnOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...';

    try {
        const response = await fetch(`/api/regenerar/${idCliente}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                item: itemActual,
                instrucciones: instrucciones,
                contexto_general: getInstruccionesIA()
            })
        });

        const data = await response.json();
        btn.disabled = false;
        btn.innerHTML = btnOriginal;

        if (data.success && data.data) {
            // Actualizar campos con respuesta de IA
            const nuevo = data.data;
            document.querySelector(`.item-titulo[data-idx="${idx}"]`).value =
                nuevo.titulo || itemActual.titulo;
            document.querySelector(`.item-descripcion[data-idx="${idx}"]`).value =
                nuevo.descripcion || itemActual.descripcion;

            // Feedback visual
            const card = document.querySelector(`.item-card[data-idx="${idx}"]`);
            card.classList.add('border-success');
            setTimeout(() => card.classList.remove('border-success'), 2000);

            showToast('success', 'Regenerado', 'La IA ha actualizado el item');
        } else {
            showToast('error', 'Error', data.message || 'No se pudo regenerar');
        }
    } catch (error) {
        btn.disabled = false;
        btn.innerHTML = btnOriginal;
        showToast('error', 'Error', 'No se pudo conectar con el servidor');
    }
}
```

### 6.4 Patrón de Enviar Items Seleccionados

```javascript
/**
 * Envía los items seleccionados al servidor
 */
async function enviarSeleccionados() {
    const seleccionados = [];

    // Recolectar datos EDITADOS de los campos
    document.querySelectorAll('.item-check:checked').forEach(cb => {
        const idx = parseInt(cb.dataset.idx);
        seleccionados.push(getItemData(idx));
    });

    // Validaciones
    if (seleccionados.length === 0) {
        showToast('warning', 'Sin selección', 'Seleccione al menos un item');
        return;
    }

    if (seleccionados.length > limite) {
        showToast('warning', 'Límite excedido', `Máximo ${limite} items permitidos`);
        return;
    }

    if (!confirm(`¿Enviar ${seleccionados.length} items?`)) return;

    const btn = document.getElementById('btnEnviarSeleccionados');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

    try {
        const response = await fetch(`/api/guardar/${idCliente}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                anio: anio,
                items: seleccionados,
                instrucciones: getInstruccionesIA()
            })
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

            showToast('success', 'Guardado',
                `<strong>${data.data?.creadas || 0}</strong> items guardados`);

            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('error', 'Error', data.message);
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${seleccionados.length}`;
        }
    } catch (error) {
        showToast('error', 'Error', 'No se pudo conectar con el servidor');
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${seleccionados.length}`;
    }
}
```

---

## 7. Integración Visual entre las 3 Partes

### 7.1 Estados de Completitud por Fase

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ESTADOS DE COMPLETITUD                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐         │
│  │     PARTE 1     │    │     PARTE 2     │    │     PARTE 3     │         │
│  │  ┌───────────┐  │    │  ┌───────────┐  │    │  ┌───────────┐  │         │
│  │  │ 3/3 ✓    │  │───▶│  │ 0/4 ⚠    │  │───▶│  │ Pendiente │  │         │
│  │  │ Completo  │  │    │  │ Pendiente │  │    │  │ Bloqueado │  │         │
│  │  └───────────┘  │    │  └───────────┘  │    │  └───────────┘  │         │
│  └─────────────────┘    └─────────────────┘    └─────────────────┘         │
│                                                                             │
│  LEYENDA:                                                                   │
│  ✓ Completo (verde)    ⚠ Pendiente (amarillo)    ✗ Bloqueado (gris)        │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 7.2 Alertas de Prerequisitos

```php
<!-- Alerta cuando Parte 1 está incompleta (mostrar en Parte 2) -->
<?php
$actividadesParte1 = count($actividades);
$minimoRequerido = 3;
?>

<?php if ($actividadesParte1 < $minimoRequerido): ?>
<div class="alert alert-warning d-flex align-items-center">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div>
        <strong>Prerequisito incompleto</strong><br>
        <small>
            Tienes <?= $actividadesParte1 ?>/<?= $minimoRequerido ?> actividades.
            <a href="<?= base_url('generador-ia/'.$idCliente.'/[nombre]') ?>"
               class="alert-link">
                Completar Parte 1
            </a>
        </small>
    </div>
</div>
<?php endif; ?>
```

### 7.3 Indicadores de Progreso en Panel

```php
<!-- En el panel de documentación del cliente -->
<div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted">Parte 1: Actividades</span>
        <?php if ($actividadesCompletas): ?>
            <span class="badge bg-success"><i class="bi bi-check"></i> Completo</span>
        <?php else: ?>
            <span class="badge bg-warning"><?= $totalActividades ?>/<?= $minimo ?></span>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted">Parte 2: Indicadores</span>
        <?php if (!$actividadesCompletas): ?>
            <span class="badge bg-secondary">Bloqueado</span>
        <?php elseif ($indicadoresCompletos): ?>
            <span class="badge bg-success"><i class="bi bi-check"></i> Completo</span>
        <?php else: ?>
            <span class="badge bg-warning"><?= $totalIndicadores ?>/<?= $minimoInd ?></span>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted">Parte 3: Documento</span>
        <?php if (!$indicadoresCompletos): ?>
            <span class="badge bg-secondary">Bloqueado</span>
        <?php elseif ($documentoGenerado): ?>
            <span class="badge bg-success"><i class="bi bi-check"></i> Generado</span>
        <?php else: ?>
            <span class="badge bg-warning">Pendiente</span>
        <?php endif; ?>
    </div>
</div>
```

### 7.4 Botones Condicionales

```php
<!-- Botón de Parte 2: deshabilitado si Parte 1 incompleta -->
<button type="button" class="btn btn-purple btn-sm"
        <?= $actividadesCompletas ? '' : 'disabled' ?>
        onclick="window.location='<?= base_url('generador-ia/'.$id.'/indicadores-X') ?>'">
    <i class="bi bi-graph-up me-1"></i>
    <?= $actividadesCompletas ? 'Generar Indicadores' : 'Complete Parte 1 primero' ?>
</button>
```

---

## 8. Troubleshooting de Errores Comunes

### 8.1 Errores de Frontend

| Error | Causa Probable | Solución |
|-------|----------------|----------|
| "Toast no aparece" | Falta el container HTML | Verificar que el toast container esté antes de `</body>` |
| "Modal no abre" | Bootstrap JS no cargado | Verificar que `bootstrap.bundle.min.js` esté incluido |
| "Checkbox no responde" | `onchange` mal escrito | Verificar sintaxis: `onchange="actualizarContador()"` |
| "Spinner infinito" | Fetch falló silenciosamente | Agregar `.catch()` y revisar consola |
| "Campos vacíos al enviar" | `data-idx` incorrecto | Verificar que los índices coincidan |

### 8.2 Errores de Comunicación API

| Error | Causa Probable | Solución |
|-------|----------------|----------|
| "Error de conexión" | URL incorrecta | Verificar `base_url()` en JavaScript |
| "404 Not Found" | Ruta no existe | Verificar `Routes.php` |
| "500 Internal Server Error" | Error en controlador | Revisar logs de PHP |
| "CORS Error" | Dominio cruzado | Configurar headers CORS en backend |
| "JSON Parse Error" | Respuesta no es JSON | Verificar que el controlador retorne JSON |

### 8.3 Errores de Lógica

| Problema | Causa | Solución |
|----------|-------|----------|
| "Límite no se valida" | Variable `limite` undefined | Definir en PHP: `const limite = <?= $limite ?>` |
| "Items duplicados" | No se valida existencia | Agregar verificación en backend |
| "Datos viejos al editar" | Se envían datos originales | Usar `getItemData(idx)` en vez de `itemsData[idx]` |
| "Contador desactualizado" | Falta llamar `actualizarContador()` | Agregar en todos los eventos de checkbox |

### 8.4 Errores de Renderizado

| Problema | Causa | Solución |
|----------|-------|----------|
| "XSS en campos" | No se escapa HTML | Usar `escapeHtml()` al renderizar |
| "Caracteres raros" | Encoding incorrecto | Verificar `charset=UTF-8` en headers |
| "Estilos rotos" | CSS no cargado | Verificar orden de carga de CSS |
| "Icons no aparecen" | Bootstrap Icons faltante | Agregar link a bootstrap-icons.css |

### 8.5 Errores de IA

| Error | Causa Probable | Solución |
|-------|----------------|----------|
| "IA no responde" | API Key inválida | Verificar configuración en `.env` |
| "Respuesta vacía" | Prompt muy largo | Reducir contexto enviado |
| "Contenido cortado" | Límite de tokens | Ajustar `max_tokens` en la llamada |
| "Formato incorrecto" | IA no siguió instrucciones | Mejorar el prompt con ejemplos |

### 8.6 Debugging Tips

```javascript
// 1. Verificar datos antes de enviar
console.log('Datos a enviar:', JSON.stringify(seleccionados, null, 2));

// 2. Verificar respuesta del servidor
fetch(url, options)
    .then(r => {
        console.log('Status:', r.status);
        console.log('Headers:', Object.fromEntries(r.headers));
        return r.json();
    })
    .then(data => console.log('Response:', data));

// 3. Verificar que los selectores funcionan
console.log('Checkboxes encontrados:',
    document.querySelectorAll('.item-check').length);

// 4. Verificar variables globales
console.log({ idCliente, anio, limite, estandares });
```

### 8.7 Checklist de Verificación

Antes de reportar un error, verificar:

- [ ] Consola del navegador (F12) sin errores JavaScript
- [ ] Network tab: requests completados con status 200
- [ ] PHP error log sin excepciones
- [ ] Variables PHP pasadas correctamente a la vista
- [ ] Bootstrap JS y CSS cargados
- [ ] Toast container presente en el HTML
- [ ] data-idx correctos en todos los elementos

---

## Checklist de Implementación UX

### Al Crear una Nueva Vista del Generador

- [ ] Navbar con botón "Volver" al panel de documentación
- [ ] Card de contexto del cliente colapsable
- [ ] Textarea de instrucciones adicionales para IA
- [ ] Modal de preview con selección por checkbox
- [ ] Controles de selección masiva
- [ ] Contador dinámico de selección
- [ ] Validación de límite con botón deshabilitado
- [ ] Spinner en botones durante carga
- [ ] Toast container para notificaciones
- [ ] Panel "Mejorar con IA" en cada item
- [ ] Confirmación antes de eliminar
- [ ] Feedback visual al actualizar items

### Variables JavaScript Requeridas

```javascript
// Definir al inicio del script
const idCliente = <?= $cliente['id_cliente'] ?>;
const anio = <?= $anio ?>;
const limite = <?= $limite ?>;
const estandares = <?= $estandares ?>;
let itemsData = [];
```

### Funciones Requeridas

- [ ] `showToast(type, title, message)`
- [ ] `escapeHtml(text)`
- [ ] `getInstruccionesIA()`
- [ ] `actualizarContador()`
- [ ] `seleccionarTodos(bool)`
- [ ] `toggleIAPanel(idx)`
- [ ] `getItemData(idx)`
- [ ] `regenerarConIA(idx)`
- [ ] `enviarSeleccionados()`

---

## Relación con Otros Documentos

| Documento | Relación |
|-----------|----------|
| **ZZ_88_PARTE1.md** | Implementación de Actividades (usa estos patrones UX) |
| **ZZ_89_PARTE2.md** | Implementación de Indicadores (usa estos patrones UX) |
| **ZZ_90_PARTE3.md** | Implementación del Documento Formal |
| **ZZ_99_SEGMENT...md** | Guía de implementación de nuevos tipos |

Esta Parte 5 es una **referencia transversal** que aplica a todas las partes del módulo de 3 partes.
