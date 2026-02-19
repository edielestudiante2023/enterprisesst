# Mejoras UX - Vistas Cliente (Read-Only)

> **Archivos modificados:**
> - `app/Views/client/list_plan_trabajo.php` (Plan de Trabajo Anual - cliente)
> - `app/Views/client/list_cronogramas.php` (Cronogramas de Capacitacion - cliente)
> **Fecha:** 2026-02-19
> **Objetivo:** Replicar estas mejoras visuales en el proyecto gemelo
> **Restriccion:** Vistas 100% read-only. NO hay edicion, eliminacion ni modificacion de datos.

---

## Tabla de Contenidos

1. [Resumen de Cambios](#1-resumen-de-cambios)
2. [Diferencias entre ambas vistas](#2-diferencias-entre-ambas-vistas)
3. [Vista PTA Cliente - CSS](#3-vista-pta-cliente---css)
4. [Vista PTA Cliente - HTML/PHP](#4-vista-pta-cliente---htmlphp)
5. [Vista PTA Cliente - JS](#5-vista-pta-cliente---js)
6. [Vista Cronogramas Cliente - CSS](#6-vista-cronogramas-cliente---css)
7. [Vista Cronogramas Cliente - HTML/PHP](#7-vista-cronogramas-cliente---htmlphp)
8. [Vista Cronogramas Cliente - JS](#8-vista-cronogramas-cliente---js)
9. [Checklist de Replicacion](#9-checklist-de-replicacion)

---

## 1. Resumen de Cambios

Ambas vistas reciben las mismas 6 mejoras visuales:

| Mejora | PTA Cliente | Cronogramas Cliente |
|--------|-------------|---------------------|
| Badges de estado con color | Estado Actividad (col 0) | Estado (col 3) |
| Mini progress bar | Porcentaje de Avance (col 6) | % Cobertura (col 10) |
| Tabla estilizada (header gradiente, hover) | `#planesTable` | `#cronogramasTable` |
| Texto truncado expandible | Actividad, Observaciones | Capacitacion, Perfil Asistentes, Observaciones |
| Accordion colapsable | Tarjetas Estado + Mes | Tarjetas Estado + Mes |
| Excel export limpio (sin HTML) | Todas las columnas | Todas las columnas |

**NO se implementa** (porque son vistas read-only):
- Botones de accion (editar/eliminar)
- Inline editing
- Clases `.editable`, `.editable-select`, `.editable-date`
- Funcion `updateField()`

---

## 2. Diferencias entre ambas vistas

| Aspecto | PTA Cliente | Cronogramas Cliente |
|---------|-------------|---------------------|
| Archivo | `client/list_plan_trabajo.php` | `client/list_cronogramas.php` |
| Table ID | `#planesTable` | `#cronogramasTable` |
| Columnas | 7 (sin contar Fuente Informacion) | 14 |
| Formato fecha | DD-MM-YYYY (moment.js) | YYYY-MM-DD |
| Estados | ABIERTA, CERRADA, GESTIONANDO | PROGRAMADA, EJECUTADA, CANCELADA POR EL CLIENTE, REPROGRAMADA |
| Filtros tfoot | No tiene | Si, con `<select>` por columna |
| Tooltips originales | No tenia | Si tenia (se eliminaron, reemplazados por truncate) |
| CSS restrictivo original | Ninguno | `max-width: 50ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis` (eliminado) |
| Scroll horizontal | Ya tenia `div.table-responsive` | Se agrego `overflow-x: auto` a `.table-container` |
| stateSave | No | Si |
| Col estado (indice) | `data[0]` | `data[3]` |
| Col fecha (indice) | `data[1]` (DD-MM-YYYY) | `data[1]` (YYYY-MM-DD) |

---

## 3. Vista PTA Cliente - CSS

Agregar estos bloques **antes del cierre `</style>`**:

```css
/* === UX: Estado Badges === */
.estado-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.3px;
  text-transform: uppercase;
  white-space: nowrap;
}
.estado-abierta     { background: #cce5ff; color: #004085; }
.estado-cerrada     { background: #f8d7da; color: #721c24; }
.estado-gestionando { background: #fff3cd; color: #856404; }

/* === UX: Mini Progress Bar === */
.mini-progress { display: flex; align-items: center; gap: 6px; min-width: 100px; }
.mini-progress-bar { flex: 1; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
.mini-progress-fill { height: 100%; border-radius: 4px; transition: width .3s; }
.mini-progress-text { font-size: 0.78rem; font-weight: 600; white-space: nowrap; }

/* === UX: Tabla estilizada === */
#planesTable thead th {
  background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
  color: #fff !important;
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px 8px;
  border-bottom: 2px solid #1a3a8a;
  white-space: nowrap;
}
#planesTable tbody td {
  padding: 6px 8px;
  vertical-align: middle;
  font-size: 0.85rem;
}
#planesTable tbody tr:hover { background-color: #eef2ff !important; }

/* === UX: Texto truncado expandible === */
.col-truncate { max-width: 250px !important; }
.cell-truncate {
  max-height: 60px;
  overflow: hidden;
  transition: max-height .3s ease;
  position: relative;
}
.cell-truncate.expanded { max-height: none; }
.btn-expand {
  display: inline-block;
  font-size: 0.72rem;
  color: #4e73df;
  cursor: pointer;
  margin-top: 2px;
  font-weight: 600;
}
.btn-expand:hover { text-decoration: underline; }

/* === UX: Accordion filtros === */
.filter-toggle-btn {
  background: none;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 4px 14px;
  font-size: 0.82rem;
  color: #6c757d;
  cursor: pointer;
  transition: all .2s;
}
.filter-toggle-btn:hover { background: #f8f9fa; color: #4e73df; }
.filter-toggle-btn i { transition: transform .2s; }
.filter-toggle-btn.collapsed i { transform: rotate(-90deg); }
```

Ademas, cambiar `<thead class="table-dark">` a solo `<thead>` (el gradiente CSS lo reemplaza).

---

## 4. Vista PTA Cliente - HTML/PHP

### 4.1 Accordion - Envolver tarjetas Estado + Mes

Despues del cierre de `#yearCards`, agregar toggle + abrir collapse:

```html
<div class="row mb-4 mt-2" id="yearCards">
  <!-- Se generan dinamicamente con JS -->
</div>

<!-- NUEVO: Accordion -->
<div class="d-flex justify-content-end mb-2">
  <button class="filter-toggle-btn" data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel" aria-expanded="true">
    <i class="fas fa-chevron-down"></i> Estado y Mes
  </button>
</div>
<div class="collapse show" id="cardFiltersPanel">

<!-- Tarjetas de Estados (clickeables) -->
...
```

Despues de la ultima tarjeta mensual, cerrar el collapse:

```html
    </div> <!-- cierre row meses -->

    </div><!-- /cardFiltersPanel -->

    <!-- Titulo y nombre del cliente -->
```

### 4.2 Estado - Badge PHP

**ANTES:**
```php
<td><?= esc($plan['estado_actividad']) ?></td>
```

**DESPUES:**
```php
<td><?php
  $est = esc($plan['estado_actividad']);
  $cls = 'estado-abierta';
  if ($est === 'CERRADA') $cls = 'estado-cerrada';
  elseif ($est === 'GESTIONANDO') $cls = 'estado-gestionando';
  echo '<span class="estado-badge ' . $cls . '">' . $est . '</span>';
?></td>
```

### 4.3 Actividad - Texto truncado

**ANTES:**
```php
<td><?= esc($plan['nombre_actividad']) ?></td>
```

**DESPUES:**
```php
<td class="col-truncate"><div class="cell-truncate"><?= esc($plan['nombre_actividad']) ?></div></td>
```

### 4.4 Porcentaje de Avance - Progress Bar

**ANTES:**
```php
<td><?= esc($plan['porcentaje_avance']) ?>%</td>
```

**DESPUES:**
```php
<td><?php
  $pct = floatval($plan['porcentaje_avance']);
  $color = '#e74a3b';
  if ($pct >= 100) $color = '#1cc88a';
  elseif ($pct >= 50) $color = '#4e73df';
  elseif ($pct > 0) $color = '#f6c23e';
  $w = max($pct, 2);
  echo '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:' . $w . '%;background:' . $color . '"></div></div><span class="mini-progress-text">' . $pct . '%</span></div>';
?></td>
```

### 4.5 Observaciones - Texto truncado

**ANTES:**
```php
<td><?= esc($plan['observaciones']) ?></td>
```

**DESPUES:**
```php
<td class="col-truncate"><div class="cell-truncate"><?= esc($plan['observaciones']) ?></div></td>
```

---

## 5. Vista PTA Cliente - JS

### 5.1 stripHtml - Agregar al inicio del `$(document).ready`

```js
// Helper: extraer texto plano de HTML
function stripHtml(html) {
  var tmp = document.createElement('DIV');
  tmp.innerHTML = html;
  return (tmp.textContent || tmp.innerText || '').trim();
}
```

### 5.2 updateCardCounts - Usar stripHtml

La columna 0 (estado) ahora tiene HTML badges. Cambiar las comparaciones:

```js
// ANTES:
var countActivas = data.filter(function(x) { return x.trim() === 'ABIERTA'; }).length;

// DESPUES:
var countActivas = data.filter(function(x) { return stripHtml(x) === 'ABIERTA'; }).length;
var countCerradas = data.filter(function(x) { return stripHtml(x) === 'CERRADA'; }).length;
var countGestionando = data.filter(function(x) { return stripHtml(x) === 'GESTIONANDO'; }).length;
```

### 5.3 applyCardFilters - Usar stripHtml en estado

```js
// ANTES:
var estado = (data[0] || '').trim();

// DESPUES:
var estado = stripHtml(data[0] || '');
```

### 5.4 Excel export - format.body con stripHtml

```js
buttons: [{
  extend: 'excelHtml5',
  text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
  className: 'btn btn-success',
  title: 'Plan de Trabajo',
  exportOptions: {
    columns: ':visible',
    format: {
      body: function(data) {
        return stripHtml(data);
      }
    }
  }
}]
```

### 5.5 initTruncateButtons + Accordion

Agregar **antes** del handler `$('#clearState').click(...)`:

```js
// === UX: Texto truncado con ver mas / ver menos ===
function initTruncateButtons() {
  $('#planesTable .cell-truncate').each(function() {
    var $cell = $(this);
    $cell.removeClass('expanded');
    $cell.next('.btn-expand').remove();
    if (this.scrollHeight > 62) {
      $cell.after('<span class="btn-expand">ver más</span>');
    }
  });
}
$(document).on('click', '.btn-expand', function() {
  var $btn = $(this);
  var $cell = $btn.prev('.cell-truncate');
  if ($cell.hasClass('expanded')) {
    $cell.removeClass('expanded');
    $btn.text('ver más');
  } else {
    $cell.addClass('expanded');
    $btn.text('ver menos');
  }
});
table.on('draw.dt', function() {
  setTimeout(initTruncateButtons, 50);
});
initTruncateButtons();

// === UX: Accordion toggle ===
$('#cardFiltersPanel').on('shown.bs.collapse', function() {
  $('.filter-toggle-btn').removeClass('collapsed');
}).on('hidden.bs.collapse', function() {
  $('.filter-toggle-btn').addClass('collapsed');
});
```

---

## 6. Vista Cronogramas Cliente - CSS

### 6.1 ELIMINAR el CSS restrictivo original

**ELIMINAR:**
```css
.styled-table thead th,
.styled-table tbody td,
.styled-table tfoot th {
    max-width: 50ch;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
```

**REEMPLAZAR POR:**
```css
.styled-table tfoot th {
    white-space: nowrap;
}
```

### 6.2 REEMPLAZAR estilos de tabla

**ELIMINAR:**
```css
.table thead th {
    background-color: #007bff;
    color: #fff;
    text-align: center;
}
.table tbody td {
    text-align: center;
    font-size: 15px;
    vertical-align: middle;
}
```

**REEMPLAZAR POR:**
```css
/* === UX: Tabla estilizada con gradiente === */
#cronogramasTable thead th {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    color: #fff !important;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 8px;
    border-bottom: 2px solid #1a3a8a;
    white-space: nowrap;
    text-align: center;
}
#cronogramasTable tbody td {
    text-align: center;
    font-size: 0.85rem;
    vertical-align: middle;
    padding: 6px 8px;
}
#cronogramasTable tbody tr:hover { background-color: #eef2ff !important; }
```

### 6.3 Agregar scroll horizontal al contenedor

```css
.table-container {
    /* ... estilos existentes ... */
    overflow-x: auto;  /* AGREGAR */
}
```

### 6.4 Agregar badges, progress bar, truncate, accordion

Mismos bloques CSS de la seccion 3, pero con clases de estado del cronograma:

```css
/* === UX: Estado Badges === */
.estado-badge { /* ... igual que seccion 3 ... */ }
.estado-programada   { background: #cce5ff; color: #004085; }
.estado-ejecutada    { background: #d4edda; color: #155724; }
.estado-cancelada    { background: #f8d7da; color: #721c24; }
.estado-reprogramada { background: #fff3cd; color: #856404; }

/* Mini Progress Bar, Texto truncado, Accordion: identicos a seccion 3 */
```

Cambiar `<thead class="table-light">` a solo `<thead>`.

---

## 7. Vista Cronogramas Cliente - HTML/PHP

### 7.1 Accordion - Igual que PTA (seccion 4.1)

Envolver tarjetas de Estado + Mes en `<div class="collapse show" id="cardFiltersPanel">`.

### 7.2 Capacitacion - Texto truncado + quitar tooltip

**ANTES:**
```php
<td data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_capacitacion']); ?>">
    <?= esc($cronograma['nombre_capacitacion']); ?>
</td>
```

**DESPUES:**
```php
<td class="col-truncate"><div class="cell-truncate"><?= esc($cronograma['nombre_capacitacion']); ?></div></td>
```

### 7.3 Estado - Badge + quitar tooltip

**ANTES:**
```php
<td data-bs-toggle="tooltip" title="<?= esc($cronograma['estado']); ?>">
    <?= esc($cronograma['estado']); ?>
</td>
```

**DESPUES:**
```php
<td><?php
    $est = esc($cronograma['estado']);
    $cls = 'estado-programada';
    if ($est === 'EJECUTADA') $cls = 'estado-ejecutada';
    elseif ($est === 'CANCELADA POR EL CLIENTE') $cls = 'estado-cancelada';
    elseif ($est === 'REPROGRAMADA') $cls = 'estado-reprogramada';
    echo '<span class="estado-badge ' . $cls . '">' . $est . '</span>';
?></td>
```

### 7.4 % Cobertura - Progress Bar + quitar tooltip

**ANTES:**
```php
<td data-bs-toggle="tooltip" title="<?= esc($cronograma['porcentaje_cobertura']); ?>">
    <?= esc($cronograma['porcentaje_cobertura']); ?>%
</td>
```

**DESPUES:**
```php
<td><?php
    $pct = floatval($cronograma['porcentaje_cobertura']);
    $color = '#e74a3b';
    if ($pct >= 100) $color = '#1cc88a';
    elseif ($pct >= 50) $color = '#4e73df';
    elseif ($pct > 0) $color = '#f6c23e';
    $w = max($pct, 2);
    echo '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:' . $w . '%;background:' . $color . '"></div></div><span class="mini-progress-text">' . $pct . '%</span></div>';
?></td>
```

### 7.5 Perfil Asistentes - Texto truncado + quitar tooltip

```php
<td class="col-truncate"><div class="cell-truncate"><?= esc($cronograma['perfil_de_asistentes']); ?></div></td>
```

### 7.6 Observaciones - Texto truncado + quitar tooltip

```php
<td class="col-truncate"><div class="cell-truncate"><?= esc($cronograma['observaciones']); ?></div></td>
```

### 7.7 Demas columnas - Solo quitar tooltip

Para todas las demas columnas que tenian `data-bs-toggle="tooltip"`, simplificar a:
```php
<td><?= esc($cronograma['fecha_programada']); ?></td>
```

---

## 8. Vista Cronogramas Cliente - JS

### 8.1 stripHtml - Igual que PTA (seccion 5.1)

### 8.2 updateStatusCounts - Usar stripHtml

La columna 3 (estado) ahora tiene badges HTML:

```js
// ANTES:
var estado = data[3];

// DESPUES:
var estado = stripHtml(data[3]);
```

### 8.3 applyFilters - Usar stripHtml en estado

```js
// ANTES:
var estado = data[3] || '';

// DESPUES:
var estado = stripHtml(data[3] || '');
```

### 8.4 initComplete - stripHtml en filtros tfoot

Los `<select>` del tfoot se populan con `column.data()` que ahora contiene HTML:

```js
// ANTES:
column.data().unique().sort().each(function (d) {
    if (d) {
        if (filterElement.find('option[value="' + d + '"]').length === 0) {
            filterElement.append('<option value="' + d + '">' + d + '</option>');
        }
    }
});

// DESPUES:
column.data().unique().sort().each(function (d) {
    if (d) {
        var clean = stripHtml(d);
        if (clean && filterElement.find('option[value="' + clean + '"]').length === 0) {
            filterElement.append('<option value="' + clean + '">' + clean + '</option>');
        }
    }
});
```

### 8.5 Excel export - format.body con stripHtml

```js
{
    extend: 'excelHtml5',
    text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
    className: 'btn btn-success btn-sm',
    exportOptions: {
        columns: ':visible',
        format: {
            body: function(data) {
                return stripHtml(data);
            }
        }
    }
},
```

### 8.6 Eliminar inicializacion de tooltips

**ELIMINAR** todo este bloque (ya no se necesita):
```js
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}
initializeTooltips();
table.on('draw.dt', function () {
    initializeTooltips();
});
```

### 8.7 initTruncateButtons + Accordion

Identico a seccion 5.5 pero con `#cronogramasTable` en lugar de `#planesTable`:

```js
function initTruncateButtons() {
    $('#cronogramasTable .cell-truncate').each(function() {
        var $cell = $(this);
        $cell.removeClass('expanded');
        $cell.next('.btn-expand').remove();
        if (this.scrollHeight > 62) {
            $cell.after('<span class="btn-expand">ver más</span>');
        }
    });
}
// ... resto igual que seccion 5.5 ...
```

---

## 9. Checklist de Replicacion

### Vista PTA Cliente (`list_plan_trabajo.php`)

- [ ] CSS: Agregar bloques estado-badge (.estado-abierta/cerrada/gestionando)
- [ ] CSS: Agregar bloques mini-progress
- [ ] CSS: Agregar estilos tabla `#planesTable` (gradiente, hover)
- [ ] CSS: Agregar bloques cell-truncate + btn-expand
- [ ] CSS: Agregar bloques filter-toggle-btn (accordion)
- [ ] HTML: Cambiar `<thead class="table-dark">` a `<thead>`
- [ ] HTML: Agregar accordion wrapper alrededor de tarjetas Estado + Mes
- [ ] PHP: Estado → badge con clase por valor
- [ ] PHP: Actividad → envolver en cell-truncate
- [ ] PHP: Porcentaje Avance → progress bar PHP
- [ ] PHP: Observaciones → envolver en cell-truncate
- [ ] JS: Agregar `stripHtml()` helper
- [ ] JS: `updateCardCounts()` → usar `stripHtml(x)` en comparaciones
- [ ] JS: `applyCardFilters()` → usar `stripHtml()` para estado
- [ ] JS: Excel export → agregar `format.body` con stripHtml
- [ ] JS: Agregar `initTruncateButtons()` + handler `.btn-expand`
- [ ] JS: Registrar `table.on('draw.dt')` para reinicializar truncate
- [ ] JS: Agregar accordion toggle handlers
- [ ] Verificar filtros de tarjetas funcionan con badges HTML
- [ ] Verificar Excel export sale limpio

### Vista Cronogramas Cliente (`list_cronogramas.php`)

- [ ] CSS: ELIMINAR regla restrictiva `max-width: 50ch; white-space: nowrap; overflow: hidden; text-overflow: ellipsis`
- [ ] CSS: REEMPLAZAR `.table thead th` por `#cronogramasTable thead th` con gradiente
- [ ] CSS: Agregar `overflow-x: auto` a `.table-container`
- [ ] CSS: Agregar bloques estado-badge (.estado-programada/ejecutada/cancelada/reprogramada)
- [ ] CSS: Agregar bloques mini-progress, cell-truncate, filter-toggle-btn
- [ ] HTML: Cambiar `<thead class="table-light">` a `<thead>`
- [ ] HTML: Agregar accordion wrapper alrededor de tarjetas Estado + Mes
- [ ] PHP: Capacitacion → envolver en cell-truncate + quitar tooltip
- [ ] PHP: Estado → badge con clase por valor + quitar tooltip
- [ ] PHP: Perfil Asistentes → envolver en cell-truncate + quitar tooltip
- [ ] PHP: % Cobertura → progress bar PHP + quitar tooltip
- [ ] PHP: Observaciones → envolver en cell-truncate + quitar tooltip
- [ ] PHP: Demas columnas → quitar `data-bs-toggle="tooltip"`
- [ ] JS: Agregar `stripHtml()` helper
- [ ] JS: `updateStatusCounts()` → usar `stripHtml(data[3])`
- [ ] JS: `applyFilters()` → usar `stripHtml()` para estado
- [ ] JS: `initComplete` filtros tfoot → usar `stripHtml(d)` al popular opciones
- [ ] JS: Excel export → agregar `format.body` con stripHtml
- [ ] JS: ELIMINAR bloque `initializeTooltips()` completo
- [ ] JS: Agregar `initTruncateButtons()` con `#cronogramasTable`
- [ ] JS: Agregar accordion toggle handlers
- [ ] Verificar scroll horizontal funciona
- [ ] Verificar filtros tfoot muestran texto limpio
- [ ] Verificar contadores de tarjetas funcionan con badges
- [ ] Verificar Excel export sale limpio
