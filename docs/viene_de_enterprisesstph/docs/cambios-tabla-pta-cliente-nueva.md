# Cambios en Tabla PTA Cliente Nueva

**Fecha:** 2026-02-21
**Vista:** `app/Views/consultant/list_pta_cliente_nueva.php`
**Ruta:** `/pta-cliente-nueva/list`

---

## 1. Aumento de tamaño de fuente

| Elemento | Antes | Después |
|----------|-------|---------|
| Headers (`thead th`) | `11px` | `13px` |
| Celdas (`tbody td`) | `13px` | `14.5px` |

---

## 2. Funcionalidad de Columnas Visibles (colvis)

Se implementó un botón **"Columnas Visibles"** usando el plugin `colvis` de DataTables Buttons.

### Cómo funciona
- Al hacer clic en el botón se despliega un dropdown con la lista de todas las columnas de la tabla.
- Cada columna tiene un toggle: clic para mostrar, clic para ocultar.
- La columna **Gestión Rápida** (última) está excluida del toggler y siempre permanece visible.

### Dependencia agregada
```html
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
```

### Configuración del botón
```javascript
{
    extend: 'colvis',
    text: '<i class="fas fa-columns"></i> Columnas Visibles',
    className: 'btn btn-outline-primary',
    columns: ':not(:last-child)'
}
```

---

## 3. Columnas ocultas por defecto

Se eliminaron todas las clases `d-none` de thead, tbody y tfoot. La visibilidad ahora se controla 100% con DataTables `columnDefs`:

```javascript
"columnDefs": [
    { "visible": false, "targets": [0, 1, 2, 3, 4, 5, 13, 14, 15, 16] }
]
```

### Columnas ocultas por defecto (solicitadas por el usuario)

| Índice | Columna |
|--------|---------|
| 0 | Acciones |
| 1 | ID |
| 2 | Cliente |
| 4 | PHVA |
| 5 | Numeral Plan Trabajo |

### Columnas ocultas por defecto (ya estaban ocultas con d-none)

| Índice | Columna |
|--------|---------|
| 3 | Fuente de la Actividad |
| 13 | Responsable Definido |
| 14 | Semana |
| 15 | Created At |
| 16 | Updated At |

Todas estas columnas pueden ser activadas desde el botón "Columnas Visibles".

---

## 4. Scroll horizontal

- Se reemplazó la clase `table-responsive` por un wrapper personalizado `table-scroll-wrapper` con `overflow-x: auto`.
- Se activó `scrollX: true` en la configuración de DataTables.
- Se desactivó `responsive: false` para evitar conflictos con el scroll horizontal.

```css
.table-scroll-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
```

---

## 5. Export a Excel actualizado

El botón de exportar a Excel ahora usa `columns: ':visible'` en lugar de índices hardcodeados, exportando únicamente las columnas que el usuario tiene visibles en ese momento.

```javascript
exportOptions: {
    columns: ':visible',
    ...
}
```

---

## Notas técnicas

- Los índices de columna en `editableMapping` (edición inline) no se ven afectados ya que DataTables mantiene los índices originales internamente aunque la columna esté oculta.
- El `order` sigue usando los mismos índices `[10, 8, 4, 6]` — DataTables puede ordenar por columnas ocultas sin problema.
