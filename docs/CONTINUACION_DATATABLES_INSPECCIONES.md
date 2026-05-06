# Continuacion: DataTables del modulo Inspecciones no se inicializan

**Fecha:** 2026-05-06
**Branch:** cycloid -> merge a main

## Problema actual

En `https://dashboard.cycloidtalent.com/index.php/inspecciones/acta-visita` (y las otras 6 listas del modulo) la tabla **NO se transforma en DataTable** aunque el codigo intenta inicializarla.

**Sintomas visuales:**

- Encabezado de tabla blanco (deberia ser oscuro `#1c2437` por CSS)
- No hay flechas de ordenamiento en las columnas
- Los inputs de filtro por columna NO filtran
- No aparece la barra de busqueda global
- No aparece la paginacion
- No aparece el "Mostrando X a Y de Z entradas"
- No aparece el boton Excel
- Las filas no tienen el striping de DataTables

Esto sugiere que **DataTables no esta inicializando en absoluto** o el CSS no aplica.

## Que ya se hizo (y NO funciono)

Ver commits recientes en main:

- `4bca021` - Helper `dtConfigBase()` en layout para Bootstrap 5 dom
- `d4da9a6` - Mover scripts de DataTables al layout, usar `whenDtReady()`
- `059a508` - Conversion inicial de las 7 listas a DataTables

## Archivos involucrados

**Layout (carga jQuery + DataTables):**

`app/Views/inspecciones/layout_pwa.php` (lineas 351-385 aprox)

Carga en este orden:

1. Bootstrap 5 JS bundle
2. SweetAlert2
3. jQuery 3.7.0
4. Select2
5. DataTables CSS (Bootstrap 5)
6. Buttons CSS
7. DataTables JS
8. DataTables Bootstrap 5 JS
9. Buttons JS
10. Buttons Bootstrap 5
11. Buttons HTML5
12. JSZip
13. Helper inline `whenDtReady()` y `dtConfigBase()`

**Vistas convertidas (todas tienen estructura similar):**

- `app/Views/inspecciones/acta_visita/list.php`
- `app/Views/inspecciones/inspeccion_locativa/list.php`
- `app/Views/inspecciones/extintores/list.php`
- `app/Views/inspecciones/botiquin/list.php`
- `app/Views/inspecciones/senalizacion/list.php`
- `app/Views/inspecciones/pausas_activas/list.php`
- `app/Views/inspecciones/investigacion_accidente/list.php`

**NO convertir** (UX especial intencional):

- `app/Views/inspecciones/mantenimientos/list.php`

## Patron actual de cada lista

```html
<style>...badges, filters-row, table.dataTable thead...</style>

<div class="container-fluid px-3">
    <div class="d-flex justify-content-between ... mb-3">
        <h6>Titulo</h6>
        <a href="...create" class="btn btn-pwa-primary">Nueva</a>
    </div>

    <div class="table-responsive">
        <table id="tablaXxx" class="table table-striped table-bordered table-hover">
            <thead>
                <tr><th>Cliente</th><th>Fecha</th>...<th>Acciones</th></tr>
                <tr class="filters-row">
                    <th><input type="text" placeholder="Cliente"></th>
                    ...
                </tr>
            </thead>
            <tbody>
                <?php foreach (...) ?>
            </tbody>
        </table>
    </div>
</div>

<script>
whenDtReady(function($) {
    $('#tablaXxx').DataTable(dtConfigBase({
        order: [[1, 'desc']],
        initComplete: function () {
            this.api().columns().every(function (idx) {
                var column = this;
                $('input, select', $('.filters-row th').eq(idx)).on('keyup change', function () {
                    if (column.search() !== this.value) column.search(this.value).draw();
                });
            });
        }
    }));
    // ...handlers de delete con SweetAlert
});
</script>
```

## Hipotesis no probadas para investigar

1. **Conflicto de jQuery:** quizas se carga otra version de jQuery DESPUES en algun parcial y rompe DataTables. Buscar otras inclusiones de jquery en el modulo.
2. **Bootstrap 5 vs 4:** la pagina usa Bootstrap 5 pero algun CSS o componente puede estar en 4. Verificar.
3. **Service Worker:** el modulo es PWA, hay un service worker (`sw_inspecciones.js`). Puede estar cacheando los CDNs sin actualizar.
4. **CSP / CORS:** algun header bloquea CDNs externos.
5. **Errores JS silenciosos:** `whenDtReady` envuelve en addEventListener load, si DataTables no se carga, falla sin error visible.
6. **El dom string de Bootstrap5 puede tener typo o classes incorrectas** (lo arme manualmente, posible error).

## Como diagnosticar en navegador (PRIMER paso)

Abrir DevTools en `https://dashboard.cycloidtalent.com/index.php/inspecciones/acta-visita`:

1. Tab **Console**: ver si hay errores JS rojos
2. Tab **Network**: filtrar por "datatables" - verificar que los .js cargan con status 200
3. Tab **Network**: ver si hay request bloqueada por CSP
4. Tab **Application > Service Workers**: deshabilitar service worker temporalmente
5. En Console ejecutar:

```js
console.log('jQuery:', typeof jQuery, jQuery && jQuery.fn.jquery);
console.log('DataTable:', !!(jQuery && jQuery.fn.DataTable));
console.log('Buttons:', !!(jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.Buttons));
console.log('whenDtReady:', typeof whenDtReady);
console.log('dtConfigBase:', typeof dtConfigBase);
```

Si algo da `undefined`, ahi esta la causa.

## Preferencias del usuario en este proyecto

(De `~/.claude/CLAUDE.md` y memoria del proyecto:)

- **REGLA MAXIMA**: Antes de cualquier cambio explicar que se entendio y preguntar "Es correcto?"
- **Documentacion-primero**: escribir docs antes que codigo (ya hecho aqui)
- **Auto mode** generalmente activado: ejecutar autonomamente
- **Deploy flow**:
  ```bash
  git add .
  git commit -m "..."
  git checkout main
  git merge cycloid
  git push origin main
  git checkout cycloid
  ```
- **Multi-tenant** ya implementado: hay traits, filtros, superadmin (`head.consultant.cycloidtalent@gmail.com`)

## Contexto adicional

El sistema es CodeIgniter 4.6.4 + PHP 8.2 + Bootstrap 5.
Para listas FUERA del modulo inspecciones (ej `/listClients`, `/admin/users`) DataTables SI funciona. Mismo patron, sin problema. Esto sugiere que el problema es ALGO especifico del modulo inspecciones (PWA, layout, service worker, etc).

## Prompt de inicio para nuevo chat

```text
Estoy retomando el trabajo del modulo Inspecciones en EnterpriseSST.

Lee primero:
- docs/CONTINUACION_DATATABLES_INSPECCIONES.md (este archivo, contexto completo del problema)

Resumen: convertimos 7 listas del modulo inspecciones de cards a DataTables,
pero las DataTables NO se inicializan visualmente aunque el codigo esta en
su lugar. El usuario reporta que ni ordena ni filtra. Hay que diagnosticar
por que.

Antes de cambiar codigo, pideme que abra DevTools en
https://dashboard.cycloidtalent.com/index.php/inspecciones/acta-visita
y te pase: errores de Console, requests de Network filtradas por
"datatables", y resultado de los console.log de jQuery/DataTable/Buttons
indicados en el doc.

Solo despues de tener ese diagnostico, propon el fix con el flujo
"explicar -> preguntar -> ejecutar" del CLAUDE.md.
```
