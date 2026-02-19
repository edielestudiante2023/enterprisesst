# Mejoras UX - Tabla Plan de Trabajo Anual (PTA)

> **Archivo modificado:** `app/Views/consultant/list_pta_cliente_nueva.php`
> **Fecha:** 2026-02-19
> **Objetivo:** Replicar estas mejoras en el proyecto gemelo

---

## Tabla de Contenidos

1. [Resumen de Cambios](#1-resumen-de-cambios)
2. [CSS - Estilos Nuevos](#2-css---estilos-nuevos)
3. [HTML - Cambios en Estructura](#3-html---cambios-en-estructura)
4. [JS - Helpers de Inline Editing](#4-js---helpers-de-inline-editing)
5. [JS - Texto Truncado Expandible](#5-js---texto-truncado-expandible)
6. [JS - Comparaciones con stripHtml](#6-js---comparaciones-con-striphtml)
7. [Accordion de Filtros por Tarjetas](#7-accordion-de-filtros-por-tarjetas)
8. [Columna "Fuente de la Actividad"](#8-columna-fuente-de-la-actividad)
9. [Checklist de Replicacion](#9-checklist-de-replicacion)

---

## 1. Resumen de Cambios

| Mejora | Que hace | Columnas afectadas |
|--------|----------|--------------------|
| Texto truncado expandible | Limita altura a 60px con "ver mas/ver menos" | col 6 (Actividad), col 12 (Observaciones) |
| Badges de estado con color | Pill badges con colores segun estado | col 10 (Estado Actividad) |
| Mini progress bar | Barra visual + numero de porcentaje | col 11 (Porcentaje Avance) |
| Botones de accion compactos | Iconos 30x30 en vez de texto | col 0 (Acciones) |
| Tabla estilizada | Header azul degradado, filas alternas, hover | Toda la tabla |
| Accordion de filtros | Colapsa tarjetas Año/Estado/Mes | Seccion de filtros |
| Columna tipo_servicio visible | Antes estaba `d-none`, ahora visible | col 3 (Fuente de la Actividad) |

---

## 2. CSS - Estilos Nuevos

Agregar **despues** del `@keyframes pulse-btn` y **antes** de los estilos del Toast:

```css
/* ============ TEXTO TRUNCADO EXPANDIBLE ============ */
.cell-truncate {
    max-height: 60px;
    overflow: hidden;
    position: relative;
    transition: max-height 0.3s ease;
}
.cell-truncate.expanded {
    max-height: 2000px;
}
.btn-expand {
    display: inline-block;
    font-size: 11px;
    color: #4e73df;
    cursor: pointer;
    font-weight: 600;
    margin-top: 2px;
    user-select: none;
}
.btn-expand:hover {
    color: #224abe;
    text-decoration: underline;
}

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
.estado-abierta { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
.estado-cerrada { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.estado-gestionando { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
.estado-cerrada-sin { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }

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

/* ============ CELDAS EDITABLES ============ */
td.editable {
    cursor: pointer;
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

/* ============ FILAS COMPACTAS Y TABLA ESTILIZADA ============ */
#ptaTable {
    border-collapse: separate;
    border-spacing: 0;
}
#ptaTable thead th {
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
#ptaTable thead th:first-child { border-radius: 8px 0 0 0; }
#ptaTable thead th:last-child { border-radius: 0 8px 0 0; }
#ptaTable tbody td {
    vertical-align: middle;
    padding: 8px 8px;
    font-size: 13px;
    border-bottom: 1px solid #e9ecef;
}
#ptaTable tbody tr:hover td {
    background-color: #f0f4ff !important;
}
#ptaTable tbody tr:nth-child(even) td {
    background-color: #f8f9fc;
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
.filter-toggle-btn .fa-chevron-down {
    transition: transform 0.3s ease;
}
.filter-toggle-btn.collapsed .fa-chevron-down {
    transform: rotate(-90deg);
}
#cardFiltersPanel {
    transition: all 0.35s ease;
}
```

---

## 3. HTML - Cambios en Estructura

### 3.1 Columna de Acciones (col 0) - Botones Iconos

**ANTES:**
```html
<td>
    <a href="...edit..." class="btn btn-sm btn-warning">Editar</a>
    <a href="...delete..." class="btn btn-sm btn-danger" onclick="return confirm(...)">Eliminar</a>
</td>
```

**DESPUES:**
```html
<td>
    <div class="action-group">
        <a href="<?= base_url('/pta-cliente-nueva/edit/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>"
           class="btn-action btn-action-edit" title="Editar">
            <i class="fas fa-pen"></i>
        </a>
        <a href="<?= base_url('/pta-cliente-nueva/delete/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>"
           class="btn-action btn-action-delete" title="Eliminar"
           onclick="return confirm('¿Seguro que deseas eliminar este registro?')">
            <i class="fas fa-trash"></i>
        </a>
    </div>
</td>
```

### 3.2 Columna Actividad (col 6) - Texto Truncado

**ANTES:**
```html
<td class="editable"><?= esc($row['actividad_plandetrabajo']) ?></td>
```

**DESPUES:**
```html
<td class="editable">
    <div class="cell-truncate"><?= esc($row['actividad_plandetrabajo']) ?></div>
</td>
```

### 3.3 Columna Estado (col 10) - Badge con Color

**ANTES:**
```html
<td class="editable"><?= esc($row['estado_actividad']) ?></td>
```

**DESPUES:**
```html
<td class="editable">
    <?php
    $estado = $row['estado_actividad'];
    $badgeClass = 'estado-abierta';
    if ($estado === 'CERRADA') $badgeClass = 'estado-cerrada';
    elseif ($estado === 'GESTIONANDO') $badgeClass = 'estado-gestionando';
    elseif ($estado === 'CERRADA SIN EJECUCIÓN') $badgeClass = 'estado-cerrada-sin';
    ?>
    <span class="estado-badge <?= $badgeClass ?>"><?= esc($estado) ?></span>
</td>
```

### 3.4 Columna Porcentaje (col 11) - Progress Bar

**ANTES:**
```html
<td class="editable"><?= esc($row['porcentaje_avance']) ?></td>
```

**DESPUES:**
```html
<td class="editable">
    <?php
    $pct = (float)($row['porcentaje_avance'] ?? 0);
    $barColor = '#e74a3b';  // rojo = 0%
    if ($pct >= 100) $barColor = '#1cc88a';      // verde
    elseif ($pct >= 50) $barColor = '#4e73df';    // azul
    elseif ($pct > 0) $barColor = '#f6c23e';      // amarillo
    $barWidth = max($pct, 2); // minimo 2% para que sea visible
    ?>
    <div class="mini-progress">
        <div class="mini-progress-bar">
            <div class="mini-progress-fill" style="width:<?= $barWidth ?>%;background:<?= $barColor ?>"></div>
        </div>
        <span class="mini-progress-text"><?= number_format($pct, 0) ?>%</span>
    </div>
</td>
```

### 3.5 Columna Observaciones (col 12) - Texto Truncado

**ANTES:**
```html
<td class="editable"><?= esc($row['observaciones']) ?></td>
```

**DESPUES:**
```html
<td class="editable">
    <div class="cell-truncate"><?= esc($row['observaciones']) ?></div>
</td>
```

---

## 4. JS - Helpers de Inline Editing

> **CRITICO:** Cuando las celdas contienen HTML (badges, progress bars, divs truncados), el inline editing debe:
> 1. Extraer el valor plano ANTES de crear el input
> 2. Reconstruir el HTML DESPUES de guardar

### 4.1 Funciones Helper (agregar dentro del bloque `if ($('#ptaTable').length)`)

```javascript
// Helper: extraer texto plano de HTML
function stripHtml(html) {
    return $('<div/>').html(html).text().trim();
}

// Construir badge de estado con la clase CSS correcta
function buildEstadoBadge(estado) {
    var cls = 'estado-abierta';
    if (estado === 'CERRADA') cls = 'estado-cerrada';
    else if (estado === 'GESTIONANDO') cls = 'estado-gestionando';
    else if (estado === 'CERRADA SIN EJECUCIÓN') cls = 'estado-cerrada-sin';
    return '<span class="estado-badge ' + cls + '">' + estado + '</span>';
}

// Construir progress bar con color segun porcentaje
function buildProgressBar(pct) {
    pct = parseFloat(pct) || 0;
    var color = '#e74a3b';
    if (pct >= 100) color = '#1cc88a';
    else if (pct >= 50) color = '#4e73df';
    else if (pct > 0) color = '#f6c23e';
    var w = Math.max(pct, 2); // minimo visible
    return '<div class="mini-progress">'
         + '<div class="mini-progress-bar">'
         + '<div class="mini-progress-fill" style="width:' + w + '%;background:' + color + '"></div>'
         + '</div>'
         + '<span class="mini-progress-text">' + pct + '%</span>'
         + '</div>';
}

// Construir celda truncada (escapa HTML del texto)
function buildTruncateCell(text) {
    return '<div class="cell-truncate">' + $('<span/>').text(text).html() + '</div>';
}
```

### 4.2 Modificar el handler de dblclick (inline editing)

**Cambio clave al extraer el valor para el input:**

```javascript
$('#ptaTable tbody').on('dblclick', 'td.editable', function() {
    var cell = table.cell(this);
    var originalHtml = cell.data();    // Ahora contiene HTML
    var $td = $(this);
    if ($td.find('input, select').length > 0) return;
    var colIndex = table.cell($td).index().column;

    // ... (mapping y validaciones igual que antes) ...

    // NUEVO: Extraer valor plano segun la columna
    var plainValue = stripHtml(originalHtml);
    // Para porcentaje, quitar el '%'
    if (colIndex === 11) plainValue = plainValue.replace('%', '').trim();

    // Crear input con plainValue (no con originalHtml)
    var inputElement;
    if (colIndex === 8 || colIndex === 9) {
        inputElement = $('<input type="date" class="form-control form-control-sm" />').val(plainValue);
    } else if (colIndex === 10) {
        inputElement = $('<select class="form-select form-select-sm"></select>');
        var options = ["ABIERTA", "CERRADA", "GESTIONANDO", "CERRADA SIN EJECUCIÓN"];
        $.each(options, function(i, option) {
            var selected = (plainValue === option) ? "selected" : "";
            inputElement.append('<option value="' + option + '" ' + selected + '>' + option + '</option>');
        });
    } else if (colIndex === 11) {
        inputElement = $('<input type="number" class="form-control form-control-sm" min="0" max="100" step="1" />').val(plainValue);
    } else {
        inputElement = $('<input type="text" class="form-control form-control-sm" />').val(plainValue);
    }

    $td.empty().append(inputElement);
    inputElement.focus();

    // ... handler blur/keydown ...
});
```

### 4.3 Reconstruir HTML en el success del AJAX

```javascript
success: function(response) {
    if (response.status === 'success') {
        // RECONSTRUIR HTML segun la columna
        if (colIndex === 10) {
            cell.data(buildEstadoBadge(newValue)).draw();
        } else if (colIndex === 11) {
            cell.data(buildProgressBar(newValue)).draw();
        } else if (colIndex === 6 || colIndex === 12) {
            cell.data(buildTruncateCell(newValue)).draw();
        } else {
            cell.data(newValue).draw();
        }

        // Fecha cierre → estado CERRADA (actualizar badge)
        if (colIndex === 9 && newValue && newValue.trim() !== '') {
            var estadoCell = table.cell($td.closest('tr'), 10);
            estadoCell.data(buildEstadoBadge('CERRADA')).draw();
        }

        // Estado cambio → actualizar progress bar
        if (fieldName === 'estado_actividad' && response.porcentaje_avance !== undefined) {
            var porcentajeCell = table.cell($td.closest('tr'), 11);
            porcentajeCell.data(buildProgressBar(response.porcentaje_avance)).draw();
        }

        // Fecha cierre → actualizar progress bar
        if (colIndex === 9 && response.porcentaje_avance !== undefined) {
            var porcentajeCell = table.cell($td.closest('tr'), 11);
            porcentajeCell.data(buildProgressBar(response.porcentaje_avance)).draw();
        }

        updateCardCounts();
        updateMonthlyCounts();
        initTruncateButtons(); // Re-inicializar botones "ver mas"
    } else {
        alert('Error: ' + response.message);
        cell.data(originalHtml).draw();
    }
}
```

---

## 5. JS - Texto Truncado Expandible

```javascript
// ===================================================================
// TEXTO TRUNCADO EXPANDIBLE
// ===================================================================
function initTruncateButtons() {
    $('.cell-truncate').each(function() {
        var $el = $(this);
        // Remover boton previo si existe
        $el.next('.btn-expand').remove();
        $el.removeClass('expanded');
        // Solo agregar boton si el contenido desborda (scrollHeight > 65px)
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
if (table) {
    table.on('draw', function() {
        initTruncateButtons();
    });
    initTruncateButtons();
}
```

---

## 6. JS - Comparaciones con stripHtml

> **PROBLEMA:** Al meter HTML en las celdas (badges, progress bars), todas las comparaciones tipo `data[10] === 'CERRADA'` dejan de funcionar porque `data[10]` ahora es `<span class="estado-badge estado-cerrada">CERRADA</span>`.

> **SOLUCION:** Usar `stripHtml()` en TODAS las comparaciones:

### 6.1 updateCardCounts()

```javascript
function updateCardCounts() {
    var data = table.column(10, { search: 'applied' }).data().toArray();
    var countActivas = data.filter(function(x) {
        return stripHtml(x) === 'ABIERTA';           // <-- stripHtml
    }).length;
    var countCerradas = data.filter(function(x) {
        return stripHtml(x) === 'CERRADA';            // <-- stripHtml
    }).length;
    var countGestionando = data.filter(function(x) {
        return stripHtml(x) === 'GESTIONANDO';        // <-- stripHtml
    }).length;
    var countCerradasSinEjecucion = data.filter(function(x) {
        return stripHtml(x) === 'CERRADA SIN EJECUCIÓN';  // <-- stripHtml
    }).length;
    // ... resto igual ...
}
```

### 6.2 applyFilters() - Custom DataTable search

```javascript
$.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
        var fechaPropuesta = data[8] || '';
        // STRIP HTML del estado antes de comparar
        var estadoActividad = $('<div/>').html(data[10] || '').text().trim();
        // ... resto de filtros usan estadoActividad (ya limpio) ...
    }
);
```

### 6.3 btnCalificarCerradas

```javascript
$('#btnCalificarCerradas').click(function() {
    var ids = [];
    table.rows().every(function() {
        var data = this.data();
        if (stripHtml(data[10]) === 'CERRADA') {    // <-- stripHtml
            ids.push(data[1]);
        }
    });
    // ...
    success: function(response) {
        table.rows().every(function() {
            var data = this.data();
            if (stripHtml(data[10]) === 'CERRADA') { // <-- stripHtml
                // Reconstruir progress bar con 100%
                data[11] = '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:100%;background:#1cc88a"></div></div><span class="mini-progress-text">100%</span></div>';
                this.data(data);
            }
        });
    }
});
```

### 6.4 Export Excel - Limpiar HTML

```javascript
exportOptions: {
    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    format: {
        body: function(data, row, column, node) {
            // Esto limpia cualquier HTML (badges, progress bars, etc.)
            return $('<div/>').html(data).text();
        }
    }
}
```

---

## 7. Accordion de Filtros por Tarjetas

### 7.1 HTML - Wrapper

Envolver las secciones de tarjetas (Año, Estado, Mes) en:

```html
<!-- Toggle de filtros por tarjetas -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <button class="filter-toggle-btn collapsed" type="button"
            data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel"
            aria-expanded="false">
        <i class="fas fa-layer-group me-2"></i>Filtros por Tarjetas (Año / Estado / Mes)
        <i class="fas fa-chevron-down ms-2"></i>
    </button>
    <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-times"></i> Limpiar Filtros
    </button>
</div>
<div class="collapse" id="cardFiltersPanel">
    <!-- AQUI VAN: yearCards + status cards + month cards -->
</div><!-- Fin cardFiltersPanel collapse -->
```

### 7.2 JS - Toggle clase collapsed

```javascript
$('#cardFiltersPanel').on('show.bs.collapse', function() {
    $('.filter-toggle-btn').removeClass('collapsed');
}).on('hide.bs.collapse', function() {
    $('.filter-toggle-btn').addClass('collapsed');
});
```

---

## 8. Columna "Fuente de la Actividad"

La columna `tipo_servicio` de `tbl_pta_cliente` estaba oculta con `class="d-none"`.

### Cambios:

1. **thead**: Quitar `d-none`, renombrar a "Fuente de la Actividad"
2. **tbody**: Quitar `d-none`
3. **tfoot**: Quitar `d-none`, agregar input de busqueda:
   ```html
   <th><input type="text" placeholder="Buscar Fuente" class="form-control form-control-sm"></th>
   ```
4. **Export Excel**: Incluir la columna en el array `columns`:
   ```javascript
   columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
   ```

### Vistas Add y Edit:

En `add_pta_cliente_nueva.php` y `edit_pta_cliente_nueva.php` cambiar el label:

```html
<!-- ANTES -->
<label for="tipo_servicio" class="form-label">Tipo Servicio</label>

<!-- DESPUES -->
<label for="tipo_servicio" class="form-label">Fuente de la Actividad</label>
```

---

## 9. Checklist de Replicacion

Usa esta checklist para ir marcando mientras replicas en el proyecto gemelo:

- [ ] **CSS**: Copiar bloque `TEXTO TRUNCADO EXPANDIBLE`
- [ ] **CSS**: Copiar bloque `BADGES DE ESTADO`
- [ ] **CSS**: Copiar bloque `MINI PROGRESS BAR`
- [ ] **CSS**: Copiar bloque `CELDAS EDITABLES`
- [ ] **CSS**: Copiar bloque `BOTONES ACCIONES COMPACTOS`
- [ ] **CSS**: Copiar bloque `FILAS COMPACTAS Y TABLA ESTILIZADA` (ajustar `#ptaTable` al id de tu tabla)
- [ ] **CSS**: Copiar bloque `ACORDEON DE FILTROS`
- [ ] **HTML**: Cambiar col Acciones a iconos (`action-group` + `btn-action`)
- [ ] **HTML**: Envolver Actividad y Observaciones en `<div class="cell-truncate">`
- [ ] **HTML**: Estado → badge PHP con `estado-badge` + clase de color
- [ ] **HTML**: Porcentaje → progress bar PHP con `mini-progress`
- [ ] **HTML**: Agregar wrapper collapse alrededor de tarjetas de filtro
- [ ] **HTML**: Mostrar columna tipo_servicio (quitar `d-none`) y renombrar
- [ ] **JS**: Agregar `stripHtml()` helper
- [ ] **JS**: Agregar `buildEstadoBadge()` helper
- [ ] **JS**: Agregar `buildProgressBar()` helper
- [ ] **JS**: Agregar `buildTruncateCell()` helper
- [ ] **JS**: Modificar dblclick para extraer `plainValue` con `stripHtml()`
- [ ] **JS**: Modificar success AJAX para reconstruir HTML segun columna
- [ ] **JS**: Actualizar `updateCardCounts()` con `stripHtml()`
- [ ] **JS**: Actualizar `applyFilters()` para strip HTML del estado
- [ ] **JS**: Actualizar `btnCalificarCerradas` con `stripHtml()` y rebuild progress bar
- [ ] **JS**: Actualizar export Excel con `format.body` que limpia HTML
- [ ] **JS**: Agregar `initTruncateButtons()` + delegado click `.btn-expand`
- [ ] **JS**: Registrar `initTruncateButtons()` en `table.on('draw')`
- [ ] **JS**: Agregar handler collapse para clase `collapsed` del toggle btn
- [ ] **Vistas Add/Edit**: Renombrar label "Tipo Servicio" → "Fuente de la Actividad"

---

## Errores Comunes al Replicar

| Error | Causa | Solucion |
|-------|-------|----------|
| Filtro de estado no funciona | `data[10]` ahora tiene HTML, comparacion directa falla | Usar `stripHtml(data[10])` o `$('<div/>').html(data[10]).text().trim()` |
| Input inline muestra HTML crudo | `cell.data()` retorna HTML completo | Extraer con `stripHtml(originalHtml)` antes de crear input |
| Porcentaje muestra "50%" en input | El `%` viene del `<span class="mini-progress-text">` | Hacer `plainValue.replace('%', '').trim()` para col 11 |
| Progress bar invisible en 0% | Width 0% = sin barra visible | `min-width: 2px` en CSS + `max($pct, 2)` en PHP + `Math.max(pct, 2)` en JS |
| "ver mas" no aparece | `initTruncateButtons()` no se ejecuta despues de draw | Registrar `table.on('draw', initTruncateButtons)` |
| Export Excel muestra HTML | Badges/progress bars exportan como HTML | Agregar `format.body` que hace `$('<div/>').html(data).text()` |

---

## Colores Utilizados

| Elemento | Color | Hex |
|----------|-------|-----|
| Estado ABIERTA | Naranja sobre fondo crema | `#e65100` fg, `#fff3e0` bg |
| Estado CERRADA | Verde sobre fondo verde claro | `#2e7d32` fg, `#e8f5e9` bg |
| Estado GESTIONANDO | Azul sobre fondo azul claro | `#1565c0` fg, `#e3f2fd` bg |
| Estado CERRADA SIN EJECUCION | Rojo sobre fondo rosa | `#c62828` fg, `#fce4ec` bg |
| Progress 0% | Rojo | `#e74a3b` |
| Progress 1-49% | Amarillo | `#f6c23e` |
| Progress 50-99% | Azul | `#4e73df` |
| Progress 100% | Verde | `#1cc88a` |
| Header tabla | Degradado azul | `#4e73df` → `#224abe` |
| Fila hover | Azul muy claro | `#f0f4ff` |
| Fila par | Gris casi blanco | `#f8f9fc` |
| Boton editar | Amarillo | `#ffc107` |
| Boton eliminar | Rojo | `#dc3545` |
| Accordion toggle | Degradado morado | `#667eea` → `#764ba2` |
