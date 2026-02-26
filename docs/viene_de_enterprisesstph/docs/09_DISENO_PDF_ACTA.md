# 09 - Diseno del PDF Acta de Visita (DOMPDF)

## Estado actual: FUNCIONAL v2 (2026-02-23)

PDF funcional con logo, secciones numeradas 1-6, margenes ICONTEC, compromisos y registro fotografico. Este documento define el diseno, restricciones tecnicas de DOMPDF y bugs resueltos.

---

## Problemas detectados y resueltos

### 1. Encabezado sin logo - RESUELTO
- **Bug:** `FCPATH . $cliente['logo']` buscaba en raiz en vez de `uploads/`.
- **Fix:** `FCPATH . 'uploads/' . $cliente['logo']` + deteccion MIME con `mime_content_type()`.
- **Pendiente:** Para clientes sin logo, mostrar placeholder (iniciales o logo Cycloid).

### 2. Numeracion de secciones - RESUELTO
- **Bug:** Saltaba de 1 a sin-num a 2 a 4 a 5.
- **Fix:** Renumerado correctamente 1-5 (Integrantes, Temas Abiertos, Temas Tratados, Observaciones, Compromisos).

### 3. Tabla de datos generales sin bordes - RESUELTO
- **Fix:** Clase `.info-table` con `border: 1px solid #ccc` y `.info-label` con fondo gris.

### 4. Firmas en tabla de integrantes - RESUELTO
- **Fix:** Reducidas a `max-width:70px; max-height:30px` con clase `.firma-inline`.

### 5. Secciones vacias con texto verde - RESUELTO
- **Fix:** Clase `.empty-text` con `color: #888; font-style: italic`.

### 6. Margenes y espaciado - RESUELTO
- **Bug critico:** Unidades `cm` en `@page` NO funcionan en DOMPDF 3.0.0 — se rompen los margenes.
- **Fix:** Usar siempre `px`: `@page { margin: 100px 70px 80px 90px; }` + body `padding: 15px 20px`.
- Se probaron varias combinaciones; `2.5cm 2cm 2.5cm 3cm` (ICONTEC) rompio todo.

### 7. Firmas al pie - RESUELTO
- **Fix:** Tabla 3 columnas (`.firma-table`) reemplaza `inline-block`.

### 8. Encabezado con fila vacia - RESUELTO
- **Bug:** Header tenia 3 filas con una fila vacia innecesaria.
- **Fix:** Reducido a 2 filas, Codigo+Version unido en una celda.

### 9. PDF cacheado mostrando version vieja - RESUELTO
- **Bug:** `generatePdf()` servia archivo de disco si ya existia.
- **Fix:** Siempre regenera el PDF desde el template y lo sirve con headers inline.

### 10. Compromisos no se imprimian - RESUELTO
- **Causa:** No habia datos de prueba (0 pendientes con `id_acta_visita` asignado).
- **Fix:** Se insertaron 6 compromisos de prueba (3 por acta) en produccion.

### 11. Fotos no aparecian en el PDF - RESUELTO (2026-02-23)

- **Bug:** `generarPdfInterno()` no incluia fotos en `$data`. Template PDF no tenia seccion de fotos.
- **Fix:** Se cargan fotos de `tbl_acta_visita_fotos` via `ActaVisitaFotoModel::getByActa()`, se convierten a base64 con `mime_content_type()` + `file_get_contents()`, y se pasan como `'fotos'` en el array `$data`.
- **Template:** Nueva seccion "6. REGISTRO FOTOGRAFICO" con tabla 3 columnas usando `array_chunk($fotos, 3)`.
- **CSS:** `.foto-table img { max-width: 180px; max-height: 140px; border: 1px solid #ccc; }`

### 12. Fotos no se veian en vista web (view.php) - RESUELTO (2026-02-23)

- **Bug:** `view.php` no renderizaba las fotos aunque el controlador ya las pasaba.
- **Fix:** Seccion "REGISTRO FOTOGRAFICO" con grid Bootstrap `row g-2` + `col-4`.
- Miniaturas con `object-fit:cover; height:100px; cursor:pointer`.
- Clic abre modal Bootstrap oscuro (`bg-dark`) con imagen completa (`max-height:80vh`).
- Funcion JS: `openPhoto(src, desc)` crea instancia de `bootstrap.Modal`.

---

## Diseno objetivo

### Estructura del PDF (orden de secciones)

```
┌─────────────────────────────────────────────────────────┐
│  [LOGO]  │  SISTEMA DE GESTION DE SEGURIDAD  │ Codigo  │
│          │  Y SALUD EN EL TRABAJO             │ FT-SST  │
│          │  ACTA DE REUNION                   │ Version │
│          │                                     │ Fecha   │
├─────────────────────────────────────────────────────────┤
│                                                         │
│          ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA        │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  MOTIVO: xxx          │  HORARIO: xx:xx AM              │
│  CLIENTE: xxx         │  FECHA: dd/mm/yyyy              │
│  MODALIDAD: xxx       │                                 │
├─────────────────────────────────────────────────────────┤
│  1. INTEGRANTES                                         │
│  ┌──────────────┬──────────────┬──────────────┐         │
│  │ NOMBRE       │ CARGO        │ ROL          │         │
│  ├──────────────┼──────────────┼──────────────┤         │
│  │ xxx          │ xxx          │ xxx          │         │
│  └──────────────┴──────────────┴──────────────┘         │
├─────────────────────────────────────────────────────────┤
│  2. TEMAS ABIERTOS Y VENCIDOS                           │
│     MANTENIMIENTOS:                                     │
│     ┌─────────────────────┬─────────────┐               │
│     │ MANTENIMIENTO       │ VENCIMIENTO │               │
│     └─────────────────────┴─────────────┘               │
│     PENDIENTES ABIERTOS:                                │
│     ┌──────────────┬──────────┬──────┬──────┬──────┐    │
│     │ ACTIVIDAD    │ RESP.    │ ASIG.│CIERRE│ DIAS │    │
│     └──────────────┴──────────┴──────┴──────┴──────┘    │
├─────────────────────────────────────────────────────────┤
│  3. TEMAS TRATADOS                                      │
│     TEMA 1: xxx                                         │
│     DETALLE: xxx                                        │
├─────────────────────────────────────────────────────────┤
│  4. OBSERVACIONES                                       │
│     xxx                                                 │
├─────────────────────────────────────────────────────────┤
│  5. COMPROMISOS                                         │
│  ┌──────────────┬──────────────┬──────────────┐         │
│  │ ACTIVIDAD    │ FECHA CIERRE │ RESPONSABLE  │         │
│  └──────────────┴──────────────┴──────────────┘         │
├─────────────────────────────────────────────────────────┤
│  6. REGISTRO FOTOGRAFICO                                │
│  ┌──────────┬──────────┬──────────┐                     │
│  │ [foto 1] │ [foto 2] │ [foto 3] │                     │
│  │  desc.   │  desc.   │  desc.   │                     │
│  └──────────┴──────────┴──────────┘                     │
├─────────────────────────────────────────────────────────┤
│  FIRMAS                                                 │
│                                                         │
│  _______________  _______________  _______________       │
│  ADMINISTRADOR    VIGIA SST        CONSULTOR            │
│  Nombre           Nombre           Nombre               │
└─────────────────────────────────────────────────────────┘
```

### Cambios vs version actual

| Seccion | Antes | Despues |
|---------|-------|---------|
| Numeracion | 1, (sin num), 2, 4, 5 | 1, 2, 3, 4, 5, 6 |
| Integrantes | Incluye columna FIRMA | Quitar firma de integrantes, mover a seccion final |
| Integrantes | Nombre + Rol | Nombre + Cargo + Rol (3 columnas) |
| Pendientes | Actividad, Responsable, Dias | Actividad, Responsable, F.Asignacion, F.Cierre, Dias |
| Temas | Solo descripcion | Tema + Detalle separados |
| Firmas | inline-block divs | Tabla 3 columnas con nombre debajo |
| Datos generales | Sin bordes | Con bordes completos |
| Secciones vacias | Texto verde | Texto gris o seccion omitida |
| Fotos | No incluidas en PDF | Seccion 6 con miniaturas en grid 3 cols |

---

## Restricciones tecnicas de DOMPDF

### CSS soportado
- Box model completo (margin, padding, border)
- `border-collapse: collapse` para tablas
- `page-break-before: always` / `page-break-after: always`
- `display: block`, `inline`, `inline-block` (con limitaciones)
- `font-weight`, `font-style`, `text-align`, `vertical-align`
- `background-color`, `color`
- `width`, `height`, `max-width`, `max-height` (en px, %, em)
- `float: left/right` (con limitaciones)

### CSS NO soportado
- **Flexbox** (`display: flex`) - NO USAR
- **Grid** (`display: grid`) - NO USAR
- **CSS Variables** (`--var`) - NO USAR
- **calc()** - NO USAR
- **box-shadow** - NO USAR
- **border-radius** - Soporte parcial, evitar
- **opacity** - Soporte parcial
- **transform** - NO USAR

### Regla de oro
**Usar TABLAS para todo el layout.** DOMPDF renderiza tablas de forma confiable. Los divs con float o inline-block pueden dar resultados impredecibles.

### Fuentes
- `DejaVu Sans` (incluida con DOMPDF, soporta UTF-8 completo)
- `Helvetica`, `Arial` (web-safe, disponibles)
- Fuentes custom requieren instalacion manual en DOMPDF

### Imagenes
- **Base64 inline** - Metodo mas confiable (es lo que usamos)
- Las rutas de archivo absoluto tambien funcionan
- `isRemoteEnabled: true` necesario para URLs externas
- Formato: PNG, JPEG. SVG tiene soporte limitado.

### Unidades CSS en @page (BUG CRITICO DOMPDF 3.0.0)

- **`px` funciona correctamente** — usar siempre `px` para margenes de pagina
- **`cm` NO funciona** — rompe completamente los margenes (probado 2026-02-22)
- **`mm`, `in` no probados** — evitar por seguridad
- Margenes actuales: `@page { margin: 100px 70px 80px 90px; }` (aprox. ICONTEC)
- Body padding adicional: `padding: 15px 20px;`

### Tamano de papel

- Usamos `letter` (carta 8.5x11") — consistente con los demas PDFs del sistema
- Margenes default de DOMPDF: ~1cm por lado

---

## Paleta de colores (consistente con el sistema)

```css
/* Encabezados de seccion */
#1c2437    /* Azul marino oscuro - titulo de seccion */

/* Encabezados de tabla */
#e8e8e8    /* Gris claro - fondo th */
#aaa       /* Gris medio - bordes th */

/* Texto */
#333       /* Gris oscuro - texto principal */
#555       /* Gris medio - labels */
#888       /* Gris claro - texto secundario */

/* Bordes */
#333       /* Bordes de header */
#ccc       /* Bordes de celdas de datos */
#d1d5db    /* Bordes sutiles */

/* Estados */
#28a745    /* Verde - sin pendientes (usado con moderacion) */
#dc3545    /* Rojo - alertas/vencidos */
```

---

## Configuracion DOMPDF en el controlador

```php
// ActaVisitaController::generarPdfInterno()
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
```

### Datos que recibe el template

```php
$data = [
    'acta'               => [],  // tbl_actas_visita row
    'cliente'            => [],  // tbl_clientes row (incluye logo)
    'consultor'          => [],  // tbl_consultor row
    'integrantes'        => [],  // tbl_acta_visita_integrantes rows
    'temas'              => [],  // tbl_acta_visita_temas rows
    'compromisos'        => [],  // tbl_pendientes rows (where id_acta_visita = X)
    'pendientesAbiertos' => [],  // tbl_pendientes rows (estado=ABIERTA, sin este acta)
    'mantenimientos'     => [],  // tbl_vencimientos_mantenimientos + detalle
    'firmas'             => [],  // ['administrador' => base64, 'vigia' => base64, 'consultor' => base64]
    'logoBase64'         => '',  // Logo del cliente en base64
    'fotos'              => [],  // [{data: base64, descripcion: str, tipo: str}, ...] desde tbl_acta_visita_fotos
];
```

---

## Ruta del logo - Fix documentado

**Bug:** `FCPATH . $cliente['logo']` buscaba el archivo en la raiz del proyecto.
**Fix:** `FCPATH . 'uploads/' . $cliente['logo']` — los logos estan en `public/uploads/`.
**Referencia:** `FirmaElectronicaController.php:376` usa la ruta correcta.

Los logos en la BD son solo el filename (ej: `1736474559_f5b66b4b5d9f2f2d36e7.png`), no la ruta completa.

---

## Archivos involucrados

| Archivo | Rol |
|---------|-----|
| `app/Views/inspecciones/acta_visita/pdf.php` | Template HTML del PDF (secciones 1-6 + firmas) |
| `app/Views/inspecciones/acta_visita/view.php` | Vista web del acta (galeria fotos con modal ampliar) |
| `app/Controllers/Inspecciones/ActaVisitaController.php` | Metodo `generarPdfInterno()` — carga datos + fotos base64, genera PDF |
| `app/Models/ActaVisitaFotoModel.php` | Modelo fotos: `getByActa($id, $tipo)` desde `tbl_acta_visita_fotos` |
| `public/uploads/` | Carpeta donde estan los logos de clientes |
| `uploads/inspecciones/firmas/` | Firmas digitales PNG |
| `uploads/inspecciones/fotos/` | Fotos adjuntas al acta (JPG/PNG) |
| `uploads/inspecciones/pdfs/` | PDFs generados |
