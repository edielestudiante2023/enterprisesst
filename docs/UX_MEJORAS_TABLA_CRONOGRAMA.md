# Mejoras UX - Tabla Cronograma de Capacitacion

> **Archivos modificados:**
> - `app/Views/consultant/list_cronogramas.php` (vista principal)
> - `app/Controllers/CronogcapacitacionController.php` (botones acciones)
> **Fecha:** 2026-02-19
> **Objetivo:** Replicar estas mejoras en el proyecto gemelo

---

## Tabla de Contenidos

1. [Resumen de Cambios](#1-resumen-de-cambios)
2. [Diferencias con la Tabla PTA](#2-diferencias-con-la-tabla-pta)
3. [CSS - Estilos Nuevos](#3-css---estilos-nuevos)
4. [HTML - Accordion de Filtros](#4-html---accordion-de-filtros)
5. [Controller - Acciones Compactas](#5-controller---acciones-compactas)
6. [JS - Helpers UX](#6-js---helpers-ux)
7. [JS - Renders de Columnas](#7-js---renders-de-columnas)
8. [JS - Inline Editing Mejorado](#8-js---inline-editing-mejorado)
9. [JS - Texto Truncado Expandible](#9-js---texto-truncado-expandible)
10. [JS - Export Excel + Accordion](#10-js---export-excel--accordion)
11. [Checklist de Replicacion](#11-checklist-de-replicacion)

---

## 1. Resumen de Cambios

| Mejora | Columnas afectadas |
|--------|--------------------|
| Badges de estado con color | col 9 (Estado) |
| Mini progress bar | col 16 (% Cobertura) |
| Texto truncado expandible ("ver mas/ver menos") | col 4 (Capacitacion), col 5 (Objetivo), col 19 (Observaciones) |
| Botones de accion compactos (iconos 30x30) | col 2 (Acciones) |
| Tabla estilizada (header azul, filas alternas, hover) | Toda la tabla |
| Accordion para filtros por Estado y Mes | Seccion de tarjetas |
| Export Excel limpia HTML | Todas las columnas exportadas |

---

## 2. Diferencias con la Tabla PTA

Esta tabla tiene diferencias clave respecto a PTA que afectan la implementacion:

| Aspecto | PTA (list_pta_cliente_nueva) | Cronograma (list_cronogramas) |
|---------|-----|-----|
| Carga de datos | PHP server-side (`<?php foreach ?>`) | AJAX via DataTables (`ajax.url`) |
| Columnas | Array por indice (`data[10]`) | Objetos por propiedad (`data.estado`) |
| Renders | PHP en tbody + JS para rebuild | JS `render` functions en columnas |
| Inline editing | `dblclick` en `td.editable` | `click` en `span.editable` / `.editable-select` / `.editable-date` |
| Filtrar HTML | Necesita `stripHtml()` en comparaciones | Usa `type !== 'display'` en render (mas limpio) |
| Estado | ABIERTA/CERRADA/GESTIONANDO/CERRADA SIN EJECUCION | PROGRAMADA/EJECUTADA/CANCELADA POR EL CLIENTE/REPROGRAMADA |
| Progress bar | Porcentaje de avance (col 11) | % Cobertura calculada (asistentes/programados) |

**Ventaja clave:** Como los renders de DataTables soportan el parametro `type`, se retorna texto plano para `filter`/`sort` y HTML solo para `display`. Esto elimina la necesidad de `stripHtml()` en comparaciones.

---

## 3. CSS - Estilos Nuevos

### 3.1 Eliminar la regla global restrictiva

**QUITAR** (causa que toda celda tenga max 20 caracteres y 25px de alto):
```css
td, th {
    max-width: 20ch;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 25px;
}
```

**REEMPLAZAR CON** un comentario:
```css
/* Regla global removida - ahora se estiliza por tabla */
```

### 3.2 Agregar bloques de estilo (despues de los estilos del banner, antes de `</style>`)

```css
/* ============ TEXTO TRUNCADO EXPANDIBLE ============ */
.cell-truncate {
    max-height: 60px;
    overflow: hidden;
    position: relative;
    transition: max-height 0.3s ease;
}
.cell-truncate.expanded { max-height: 2000px; }
.btn-expand {
    display: inline-block;
    font-size: 11px;
    color: #4e73df;
    cursor: pointer;
    font-weight: 600;
    margin-top: 2px;
    user-select: none;
}
.btn-expand:hover { color: #224abe; text-decoration: underline; }

/* ============ BADGES DE ESTADO ============ */
.estado-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.estado-programada { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
.estado-ejecutada { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.estado-cancelada { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }
.estado-reprogramada { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }

/* ============ MINI PROGRESS BAR ============ */
.mini-progress {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 100px;
}
.mini-progress-bar {
    flex: 1;
    height: 14px;
    background: #dee2e6;
    border-radius: 7px;
    overflow: hidden;
    min-width: 60px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}
.mini-progress-fill {
    height: 100%;
    border-radius: 7px;
    transition: width 0.3s ease;
    min-width: 2px;
}
.mini-progress-text {
    font-size: 13px;
    font-weight: 800;
    min-width: 40px;
    text-align: right;
    color: #333;
}

/* ============ BOTONES ACCIONES COMPACTOS ============ */
.btn-action {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 13px;
    border: none;
    transition: all 0.2s ease;
}
.btn-action:hover { transform: scale(1.1); }
.btn-action-edit { background: #ffc107; color: #000; }
.btn-action-edit:hover { background: #ffca2c; color: #000; }
.btn-action-delete { background: #dc3545; color: #fff; }
.btn-action-delete:hover { background: #e04050; color: #fff; }
.action-group { display: flex; gap: 4px; justify-content: center; }

/* ============ TABLA ESTILIZADA ============ */
#cronogramaTable { border-collapse: separate; border-spacing: 0; }
#cronogramaTable thead th {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 10px 8px;
    white-space: nowrap;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
#cronogramaTable thead th:first-child { border-radius: 8px 0 0 0; }
#cronogramaTable thead th:last-child { border-radius: 0 8px 0 0; }
#cronogramaTable tbody td {
    vertical-align: middle;
    padding: 8px 8px;
    font-size: 13px;
    border-bottom: 1px solid #e9ecef;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}
#cronogramaTable tbody tr:hover td { background-color: #f0f4ff !important; }
#cronogramaTable tbody tr:nth-child(even) td { background-color: #f8f9fc; }

/* Columnas con texto truncado: permitir wrap */
.col-truncate {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
    max-width: 250px !important;
}

/* ============ ACORDEON DE FILTROS ============ */
.filter-toggle-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}
.filter-toggle-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}
.filter-toggle-btn .fa-chevron-down { transition: transform 0.3s ease; }
.filter-toggle-btn.collapsed .fa-chevron-down { transform: rotate(-90deg); }
#cardFiltersPanel { transition: all 0.35s ease; }
```

> **NOTA:** El selector `#cronogramaTable` debe cambiarse al ID de tu tabla en el otro proyecto.
> La clase `.col-truncate` se aplica via DataTables `className` en la definicion de columna.

---

## 4. HTML - Accordion de Filtros

Las tarjetas de Estado y Mes se envuelven en un `collapse` de Bootstrap 5.

### Agregar ANTES de las tarjetas de estado:
```html
<!-- Toggle de filtros por Estado y Mes -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <button class="filter-toggle-btn collapsed" type="button"
            data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel"
            aria-expanded="false">
        <i class="fas fa-layer-group me-2"></i>Filtros por Estado y Mes
        <i class="fas fa-chevron-down ms-2"></i>
    </button>
</div>
<div class="collapse" id="cardFiltersPanel">
```

### Agregar DESPUES de la ultima tarjeta de mes:
```html
</div><!-- Fin cardFiltersPanel collapse -->
```

### JS para toggle (al final del `$(document).ready`):
```javascript
$('#cardFiltersPanel').on('show.bs.collapse', function() {
    $('.filter-toggle-btn').removeClass('collapsed');
}).on('hide.bs.collapse', function() {
    $('.filter-toggle-btn').addClass('collapsed');
});
```

---

## 5. Controller - Acciones Compactas

**ANTES** (en el controller que genera `acciones`):
```php
$cronograma['acciones'] = '<a href="..." class="btn btn-warning btn-sm">Editar</a> '
    . '<a href="..." class="btn btn-danger btn-sm" onclick="...">Eliminar</a>';
```

**DESPUES:**
```php
$cronograma['acciones'] = '<div class="action-group">'
    . '<a href="' . base_url('/editcronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pen"></i></a>'
    . '<a href="' . base_url('/deletecronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn-action btn-action-delete" title="Eliminar" onclick="return confirm(\'...\');"><i class="fas fa-trash"></i></a>'
    . '</div>';
```

---

## 6. JS - Helpers UX

Agregar **ANTES** del `$('#tabla').DataTable({`:

```javascript
// ============ HELPERS UX ============
function buildEstadoBadgeCronog(estado) {
    var cls = 'estado-programada';
    if (estado === 'EJECUTADA') cls = 'estado-ejecutada';
    else if (estado === 'CANCELADA POR EL CLIENTE') cls = 'estado-cancelada';
    else if (estado === 'REPROGRAMADA') cls = 'estado-reprogramada';
    return '<span class="editable-select estado-badge ' + cls + '" data-field="estado">' + estado + '</span>';
}

function buildProgressBar(pct) {
    pct = parseFloat(pct) || 0;
    var color = '#e74a3b';      // rojo = 0%
    if (pct >= 100) color = '#1cc88a';   // verde
    else if (pct >= 50) color = '#4e73df'; // azul
    else if (pct > 0) color = '#f6c23e';   // amarillo
    var w = Math.max(pct, 2);  // minimo visible
    return '<div class="mini-progress">'
        + '<div class="mini-progress-bar">'
        + '<div class="mini-progress-fill" style="width:' + w + '%;background:' + color + '"></div>'
        + '</div>'
        + '<span class="mini-progress-text">' + pct + '%</span>'
        + '</div>';
}
```

---

## 7. JS - Renders de Columnas

### 7.1 Capacitacion (texto truncado + editable)

```javascript
{
    data: 'nombre_capacitacion',
    className: 'col-truncate',     // <-- permite wrap en esta columna
    render: function(data, type, row) {
        data = (data === null || data === "") ? "" : data;
        if (type !== 'display') return data;  // texto plano para filtros/sort
        var displayText = data || '&nbsp;';
        return '<div class="cell-truncate">'
            + '<span class="editable" data-field="nombre_capacitacion" data-id="' + row.id_cronograma_capacitacion + '">'
            + displayText + '</span></div>';
    }
},
```

### 7.2 Objetivo (texto truncado, NO editable)

```javascript
{
    data: 'objetivo_capacitacion',
    className: 'col-truncate',
    render: function(data, type, row) {
        data = (data === null || data === "") ? "" : data;
        if (type !== 'display') return data;
        return '<div class="cell-truncate">' + (data || '&nbsp;') + '</div>';
    }
},
```

### 7.3 Estado (badge con color + editable)

```javascript
{
    data: 'estado',
    render: function(data, type, row) {
        data = (data === null || data === "") ? "" : data;
        if (type !== 'display') return data;  // texto plano para filtros/sort
        var cls = 'estado-programada';
        if (data === 'EJECUTADA') cls = 'estado-ejecutada';
        else if (data === 'CANCELADA POR EL CLIENTE') cls = 'estado-cancelada';
        else if (data === 'REPROGRAMADA') cls = 'estado-reprogramada';
        return '<span class="editable-select estado-badge ' + cls + '" '
            + 'data-field="estado" data-id="' + row.id_cronograma_capacitacion + '">'
            + (data || '&nbsp;') + '</span>';
    }
},
```

> **Clave:** El span tiene AMBAS clases: `editable-select` (para inline editing) y `estado-badge` + color (para visual).

### 7.4 % Cobertura (progress bar, NO editable)

```javascript
{
    data: 'porcentaje_cobertura',
    render: function(data, type, row) {
        var asistentes = parseFloat(row.numero_de_asistentes_a_capacitacion) || 0;
        var programados = parseFloat(row.numero_total_de_personas_programadas) || 0;
        var porcentaje = programados > 0 ? Math.round((asistentes / programados) * 100) : 0;
        if (type !== 'display') return porcentaje;  // numero plano para sort
        return buildProgressBar(porcentaje);
    }
},
```

### 7.5 Observaciones (texto truncado + editable)

```javascript
{
    data: 'observaciones',
    className: 'col-truncate',
    render: function(data, type, row) {
        data = (data === null || data === "") ? "" : data;
        if (type !== 'display') return data;
        var displayText = data || '&nbsp;';
        return '<div class="cell-truncate">'
            + '<span class="editable" data-field="observaciones" data-id="' + row.id_cronograma_capacitacion + '">'
            + displayText + '</span></div>';
    }
}
```

---

## 8. JS - Inline Editing Mejorado

### 8.1 Problema: Badge pierde clase de color al editar

Cuando el usuario cambia el estado via inline select, el span recupera el texto pero pierde la clase de color.

### 8.2 Solucion: Actualizar clase en `updateField` success

Dentro de la funcion `updateField`, en el callback `success`, agregar:

```javascript
success: function(response) {
    if (response.success) {
        // Si se cambio el estado, actualizar badge class
        if (field === 'estado') {
            cell.removeClass('estado-programada estado-ejecutada estado-cancelada estado-reprogramada');
            var cls = 'estado-programada';
            if (value === 'EJECUTADA') cls = 'estado-ejecutada';
            else if (value === 'CANCELADA POR EL CLIENTE') cls = 'estado-cancelada';
            else if (value === 'REPROGRAMADA') cls = 'estado-reprogramada';
            cell.addClass(cls);
            updateStatusCounts();  // recalcular contadores
        }

        // Si se cambiaron asistentes o programados, reconstruir progress bar
        if (field === 'numero_de_asistentes_a_capacitacion' || field === 'numero_total_de_personas_programadas') {
            var row = table.row(cell.closest('tr'));
            var rowData = row.data();
            rowData[field] = value;

            var asistentes = parseFloat(rowData.numero_de_asistentes_a_capacitacion) || 0;
            var programados = parseFloat(rowData.numero_total_de_personas_programadas) || 0;
            var porcentaje = programados > 0 ? Math.round((asistentes / programados) * 100) : 0;

            var coberturaCell = cell.closest('tr').find('td').eq(16);
            coberturaCell.html(buildProgressBar(porcentaje));  // <-- rebuild HTML
        }

        initTruncateButtons();  // re-inicializar "ver mas" botones
    }
}
```

---

## 9. JS - Texto Truncado Expandible

```javascript
// ===================================================================
// TEXTO TRUNCADO EXPANDIBLE
// ===================================================================
function initTruncateButtons() {
    $('.cell-truncate').each(function() {
        var $el = $(this);
        $el.next('.btn-expand').remove();
        $el.removeClass('expanded');
        // Solo agregar boton si el contenido desborda (>65px)
        if (this.scrollHeight > 65) {
            if ($el.next('.btn-expand').length === 0) {
                $el.after('<span class="btn-expand">ver m&aacute;s &#9660;</span>');
            }
        }
    });
}

// Delegado para click en "ver mas / ver menos"
$(document).on('click', '.btn-expand', function() {
    var $btn = $(this);
    var $cell = $btn.prev('.cell-truncate');
    if ($cell.hasClass('expanded')) {
        $cell.removeClass('expanded');
        $btn.html('ver m&aacute;s &#9660;');
    } else {
        $cell.addClass('expanded');
        $btn.html('ver menos &#9650;');
    }
});

// Inicializar despues de cada draw de DataTables
table.on('draw.dt', function() {
    setTimeout(initTruncateButtons, 50);
});
```

> El `setTimeout(50)` es necesario para que el DOM se renderice antes de medir `scrollHeight`.

---

## 10. JS - Export Excel + Accordion

### 10.1 Export Excel con limpieza de HTML

```javascript
buttons: [{
    extend: 'excelHtml5',
    text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
    className: 'btn btn-success btn-sm',
    title: 'Cronograma_Capacitacion',
    charset: 'UTF-8',
    bom: true,
    exportOptions: {
        // Excluir columnas de expand, acciones y gestion rapida
        columns: [1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
        format: {
            body: function(data, row, column, node) {
                // Limpia badges, progress bars, spans, etc.
                return $('<div/>').html(data).text();
            }
        }
    }
}]
```

### 10.2 Accordion Toggle

```javascript
$('#cardFiltersPanel').on('show.bs.collapse', function() {
    $('.filter-toggle-btn').removeClass('collapsed');
}).on('hide.bs.collapse', function() {
    $('.filter-toggle-btn').addClass('collapsed');
});
```

---

## 11. Checklist de Replicacion

### CSS
- [ ] Eliminar regla global `td, th { max-width:20ch; ... }`
- [ ] Agregar bloque `TEXTO TRUNCADO EXPANDIBLE`
- [ ] Agregar bloque `BADGES DE ESTADO` (ajustar nombres de estados)
- [ ] Agregar bloque `MINI PROGRESS BAR`
- [ ] Agregar bloque `BOTONES ACCIONES COMPACTOS`
- [ ] Agregar bloque `TABLA ESTILIZADA` (cambiar `#cronogramaTable` al ID correcto)
- [ ] Agregar bloque `ACORDEON DE FILTROS`
- [ ] Agregar clase `.col-truncate`

### HTML
- [ ] Agregar toggle button + collapse wrapper alrededor de tarjetas de filtro
- [ ] Cerrar el `</div>` del collapse despues de tarjetas de mes

### Controller
- [ ] Cambiar `acciones` de texto a iconos compactos (`action-group` + `btn-action`)

### JS Helpers
- [ ] Agregar `buildEstadoBadgeCronog()` (antes del DataTable init)
- [ ] Agregar `buildProgressBar()` (antes del DataTable init)
- [ ] Agregar `initTruncateButtons()` (despues del DataTable init)

### JS Renders
- [ ] Capacitacion: `className: 'col-truncate'` + render con `cell-truncate` wrapper
- [ ] Objetivo: `className: 'col-truncate'` + render con `cell-truncate` (no editable)
- [ ] Estado: render con `estado-badge` + clase de color + `editable-select`
- [ ] % Cobertura: render con `buildProgressBar()` + `type !== 'display'`
- [ ] Observaciones: `className: 'col-truncate'` + render con `cell-truncate` wrapper

### JS Inline Editing
- [ ] En `updateField` success: actualizar badge class al cambiar estado
- [ ] En `updateField` success: rebuild progress bar al cambiar asistentes/programados
- [ ] En `updateField` success: llamar `initTruncateButtons()`

### JS Eventos
- [ ] Registrar `initTruncateButtons` en `table.on('draw.dt')`
- [ ] Delegado click `.btn-expand` para expand/collapse
- [ ] Handler collapse para clase `collapsed` del toggle button
- [ ] Export Excel: agregar `format.body` que limpia HTML
- [ ] Export Excel: excluir columnas de acciones/gestion rapida

---

## Colores Utilizados

| Elemento | Color | Hex |
|----------|-------|-----|
| Estado PROGRAMADA | Azul sobre fondo azul claro | `#1565c0` fg, `#e3f2fd` bg |
| Estado EJECUTADA | Verde sobre fondo verde claro | `#2e7d32` fg, `#e8f5e9` bg |
| Estado CANCELADA | Rojo sobre fondo rosa | `#c62828` fg, `#fce4ec` bg |
| Estado REPROGRAMADA | Naranja sobre fondo crema | `#e65100` fg, `#fff3e0` bg |
| Progress 0% | Rojo | `#e74a3b` |
| Progress 1-49% | Amarillo | `#f6c23e` |
| Progress 50-99% | Azul | `#4e73df` |
| Progress 100% | Verde | `#1cc88a` |
| Header tabla | Degradado azul | `#4e73df` -> `#224abe` |
| Fila hover | Azul muy claro | `#f0f4ff` |
| Fila par | Gris casi blanco | `#f8f9fc` |
| Boton editar | Amarillo | `#ffc107` |
| Boton eliminar | Rojo | `#dc3545` |
| Accordion toggle | Degradado morado | `#667eea` -> `#764ba2` |

---

## Errores Comunes al Replicar

| Error | Causa | Solucion |
|-------|-------|----------|
| Columnas sin wrap con `col-truncate` | CSS global `td { white-space:nowrap }` gana especificidad | Usar `!important` en `.col-truncate` |
| Badge pierde color al editar estado | `cell.html(newValue)` reemplaza contenido pero no actualiza clase | Agregar `cell.removeClass/addClass` en updateField success |
| Progress bar desaparece al editar asistentes | `coberturaCell.text()` sobreescribia con texto plano | Usar `coberturaCell.html(buildProgressBar(porcentaje))` |
| "ver mas" no aparece | `initTruncateButtons()` no se llama post-draw | Registrar en `table.on('draw.dt')` con `setTimeout(50)` |
| Export Excel muestra HTML | Badges/progress bars se exportan como tags | Agregar `format.body: function(data) { return $('<div/>').html(data).text(); }` |
| Filtros de columna no funcionan con badges | Si usas `data[colIndex]` en busquedas | Usa `type !== 'display'` en render para que filter/sort reciban texto plano |
| Tabla sin datos al cargar | `stateSave:true` guarda estado viejo con columnas diferentes | Limpiar localStorage: `localStorage.removeItem(storageKey)` |
