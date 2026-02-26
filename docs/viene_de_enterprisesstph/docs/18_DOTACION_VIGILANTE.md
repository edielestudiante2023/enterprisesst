# 18 - Dotacion Vigilante

## Resumen

Inspeccion de la dotacion y EPP del personal de vigilancia (guardas de seguridad) en la propiedad horizontal. El consultor verifica el estado de 7 items especificos del uniforme/equipamiento del vigilante y registra evidencia fotografica.

**Patron:** Inspeccion PLANA (ver `12_PATRON_INSPECCION_PLANA.md`)
**PDF:** FT-SST-213 — Formato de Inspeccion Dotaciones y EPP

---

## Mapeo AppSheet → DB

### Columnas a MIGRAR (18 de 22)

| # | AppSheet | DB Column | Tipo |
|---|----------|-----------|------|
| 1 | ID | `id` | INT AUTO_INCREMENT PK |
| — | (nuevo) | `id_cliente` | INT FK → tbl_clientes |
| — | (nuevo) | `id_consultor` | INT FK → tbl_consultor |
| 4 | FECHA DE LA INSPECCION | `fecha_inspeccion` | DATE |
| 7 | CONTRATISTA | `contratista` | VARCHAR(255) |
| 8 | SERVICIO | `servicio` | VARCHAR(255) |
| 9 | NOMBRE DEL CARGO | `nombre_cargo` | VARCHAR(255) |
| 10 | TRES ACTIVIDADES MAS FRECUENTES | `actividades_frecuentes` | TEXT |
| 11 | FOTO DE CUERPO COMPLETO | `foto_cuerpo_completo` | VARCHAR(255) |
| 12 | FOTO CUARTO DE ALMACENAMIENTO EPPS | `foto_cuarto_almacenamiento` | VARCHAR(255) |
| 13 | Uniforme Camisa, pantalon... | `estado_uniforme` | ENUM(5) |
| 14 | Chaqueta | `estado_chaqueta` | ENUM(5) |
| 15 | Radio de onda corta | `estado_radio` | ENUM(5) |
| 16 | Baston Tonfa | `estado_baston` | ENUM(5) |
| 17 | Calzado | `estado_calzado` | ENUM(5) |
| 18 | Gorra | `estado_gorra` | ENUM(5) |
| 19 | Carne | `estado_carne` | ENUM(5) |
| 20 | Concepto Final del Inspector SST | `concepto_final` | TEXT |
| — | (nuevo) | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

**Total: ~23 columnas**

### Columnas NO almacenadas (4 de 22)

| Categoria | Columnas | Razon |
|-----------|----------|-------|
| **AppSheet interno** (1) | _RowNumber | Campo interno de AppSheet |
| **Timestamp reemplazado** (1) | FECHA DEL REGISTRO | Reemplazado por `created_at` |
| **FK normalizada** (2) | NOMBRE DEL RESPONSABLE, LOGO_CLIENTE, FECHA_SGSST | Datos vienen de tbl_clientes y tbl_consultor via FK |

---

## Items EPP — ENUM de 5 valores

Cada uno de los 7 items de dotacion tiene un campo `estado_{item}` con estos valores:

| Valor DB | Label visible |
|----------|---------------|
| `bueno` | Bueno |
| `regular` | Regular |
| `deficiente` | Deficiente |
| `no_tiene` | No Tiene |
| `no_aplica` | No Aplica |

### Constante en controlador

```php
public const ESTADOS_EPP = [
    'bueno' => 'Bueno',
    'regular' => 'Regular',
    'deficiente' => 'Deficiente',
    'no_tiene' => 'No Tiene',
    'no_aplica' => 'No Aplica',
];

public const ITEMS_EPP = [
    'uniforme' => ['label' => 'Uniforme Camisa, Pantalon y Corbata/Panoleta', 'icon' => 'fa-shirt'],
    'chaqueta' => ['label' => 'Chaqueta', 'icon' => 'fa-vest-patches'],
    'radio'    => ['label' => 'Radio de Onda Corta', 'icon' => 'fa-walkie-talkie'],
    'baston'   => ['label' => 'Baston Tonfa', 'icon' => 'fa-gavel'],
    'calzado'  => ['label' => 'Calzado', 'icon' => 'fa-shoe-prints'],
    'gorra'    => ['label' => 'Gorra', 'icon' => 'fa-hat-cowboy'],
    'carne'    => ['label' => 'Carne', 'icon' => 'fa-id-card'],
];
```

### Color coding en vistas

```php
$colores = [
    'bueno' => 'success',       // verde
    'regular' => 'warning',     // amarillo
    'deficiente' => 'danger',   // rojo
    'no_tiene' => 'secondary',  // gris
    'no_aplica' => 'light',     // gris claro
];
```

---

## Diferencia vs Patron PLANA tipico

| Aspecto | PLANA tipica (comunicaciones, botiquin) | Dotacion Vigilante |
|---------|----------------------------------------|-------------------|
| Campos por recurso | `cant_{key}` INT + `obs_{key}` TEXT | `estado_{key}` ENUM(5) — un solo campo |
| Constante RECURSOS | `label` + `icon` + `hint` | `label` + `icon` (sin hint) |
| Iteracion form/PDF | 2 inputs por recurso (cantidad + obs) | 1 select por recurso (estado) |
| Campos extra | — | contratista, servicio, nombre_cargo, actividades_frecuentes |
| Fotos | Genericas (foto_1, foto_2) | Especificas (cuerpo_completo, cuarto_almacenamiento) |

---

## Fotos (2)

| Campo DB | Descripcion |
|----------|-------------|
| `foto_cuerpo_completo` | Foto de cuerpo completo del vigilante uniformado |
| `foto_cuarto_almacenamiento` | Foto del cuarto de almacenamiento de EPPs o del personal uniformado |

Dir fotos: `uploads/inspecciones/dotacion-vigilante/`
Dir PDFs: `uploads/inspecciones/dotacion-vigilante/pdfs/`

---

## Tabla SQL: `tbl_dotacion_vigilante`

```sql
CREATE TABLE IF NOT EXISTS tbl_dotacion_vigilante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Datos del contratista/vigilante
    contratista VARCHAR(255) NULL,
    servicio VARCHAR(255) NULL,
    nombre_cargo VARCHAR(255) NULL,
    actividades_frecuentes TEXT NULL,

    -- Fotos
    foto_cuerpo_completo VARCHAR(255) NULL,
    foto_cuarto_almacenamiento VARCHAR(255) NULL,

    -- Items EPP (7 items x ENUM)
    estado_uniforme ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_chaqueta ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_radio ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_baston ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_calzado ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_gorra ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_carne ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,

    -- Concepto
    concepto_final TEXT NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_dot_vig_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_dot_vig_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dot_vig_cliente (id_cliente),
    INDEX idx_dot_vig_consultor (id_consultor),
    INDEX idx_dot_vig_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total: 23 columnas** (id + 2 FK + fecha + 4 texto/varchar + 2 foto + 7 ENUM EPP + concepto + observaciones + ruta_pdf + estado + 2 timestamps)

---

## Estructura del PDF (FT-SST-213)

### Secciones

1. **Header corporativo**
   - Logo cliente | SG-SST | Codigo: FT-SST-213 / Version: 001 / Fecha

2. **Titulo**
   - "Reporte de Inspeccion de Dotaciones"

3. **Introduccion** (texto estatico)
   - Parrafo sobre EPP y marco teorico

4. **Marco Legal** (texto estatico)
   - Articulo 230 del Codigo Sustantivo del Trabajo
   - Ley 1072 de 2011
   - Decreto 1443 de 2014
   - Resolucion 1072 de 2018
   - Listado de NTCs (13 normas tecnicas)

5. **Conceptos Basicos** (texto estatico)
   - EPP categorias (primera, segunda, tercera)
   - Dotacion de Seguridad
   - Inspeccion de Dotaciones

6. **Importancia y Aspectos a Considerar** (texto estatico)
   - 4 puntos de importancia
   - 5 aspectos a considerar

7. **Datos de la Inspeccion** (tabla datos dinamicos)
   - Fecha, Cliente, Contratista, Servicio, Cargo, Actividades frecuentes

8. **Registro Fotografico** (fotos base64)
   - Foto cuerpo completo
   - Foto cuarto almacenamiento EPPs

9. **Estado de Dotacion** (tabla datos dinamicos)
   - 7 items con estado (BUENO/REGULAR/DEFICIENTE/NO TIENE/NO APLICA)
   - Color coding por estado

10. **Concepto Final del Inspector** (texto dinamico)

---

## Formulario — Secciones (5)

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha inspeccion | 0 |
| 2 | Datos del Contratista | contratista, servicio, nombre_cargo, actividades_frecuentes | 0 |
| 3 | Registro Fotografico | — | 2 (cuerpo completo, cuarto almacenamiento) |
| 4 | Estado de Dotacion EPP | 7 selects (uno por item) | 0 |
| 5 | Concepto Final y Observaciones | concepto_final textarea, observaciones textarea | 0 |
| 6 | Acciones | Guardar borrador + Finalizar | 0 |

**Total:** ~11 campos editables + 2 fotos + 7 selects = ~20 inputs

---

## Rutas (10)

```php
// Dotacion Vigilante
$routes->get('dotacion-vigilante', 'DotacionVigilanteController::list');
$routes->get('dotacion-vigilante/create', 'DotacionVigilanteController::create');
$routes->get('dotacion-vigilante/create/(:num)', 'DotacionVigilanteController::create/$1');
$routes->post('dotacion-vigilante/store', 'DotacionVigilanteController::store');
$routes->get('dotacion-vigilante/edit/(:num)', 'DotacionVigilanteController::edit/$1');
$routes->post('dotacion-vigilante/update/(:num)', 'DotacionVigilanteController::update/$1');
$routes->get('dotacion-vigilante/view/(:num)', 'DotacionVigilanteController::view/$1');
$routes->get('dotacion-vigilante/pdf/(:num)', 'DotacionVigilanteController::generatePdf/$1');
$routes->post('dotacion-vigilante/finalizar/(:num)', 'DotacionVigilanteController::finalizar/$1');
$routes->get('dotacion-vigilante/delete/(:num)', 'DotacionVigilanteController::delete/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 24 |
| `tag` | `dot_vig_id:{id}` |

---

## Dashboard

- **Card:** icono `fa-user-shield`, label "Dotacion Vigilante"
- **Pendientes:** borradores despues de Plan de Emergencia (o segun orden)
- **Conteo:** `totalDotacionVigilante` (estado=completo)

---

## Archivos a crear (7)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_dotacion_vigilante.php` | Migracion ~23 columnas |
| `app/Models/DotacionVigilanteModel.php` | Modelo CRUD |
| `app/Controllers/Inspecciones/DotacionVigilanteController.php` | Controlador con 2 fotos + 7 ENUMs + PDF |
| `app/Views/inspecciones/dotacion-vigilante/list.php` | Listado cards |
| `app/Views/inspecciones/dotacion-vigilante/form.php` | Formulario (~5 secciones) |
| `app/Views/inspecciones/dotacion-vigilante/view.php` | Vista read-only |
| `app/Views/inspecciones/dotacion-vigilante/pdf.php` | Template DOMPDF (texto legal estatico + datos) |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | 10 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |

---

## Notas de implementacion

### getInspeccionPostData()

A diferencia de la PLANA tipica que itera `cant_` + `obs_`, aqui se itera `estado_`:

```php
foreach (self::ITEMS_EPP as $key => $info) {
    $val = $this->request->getPost('estado_' . $key);
    $data['estado_' . $key] = in_array($val, array_keys(self::ESTADOS_EPP)) ? $val : null;
}
```

### Formulario — Selects EPP

```html
<?php foreach ($itemsEpp as $key => $info): ?>
<div class="col-md-6 mb-3">
    <label class="form-label"><i class="fas <?= $info['icon'] ?> me-1"></i><?= $info['label'] ?></label>
    <select name="estado_<?= $key ?>" class="form-select">
        <option value="">-- Seleccione --</option>
        <?php foreach ($estadosEpp as $val => $label): ?>
        <option value="<?= $val ?>" <?= ($inspeccion['estado_'.$key] ?? '') === $val ? 'selected' : '' ?>>
            <?= $label ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endforeach; ?>
```

### PDF — Tabla de estados EPP

```html
<table class="data-table">
    <thead><tr><th>ITEM</th><th>ESTADO</th></tr></thead>
    <tbody>
    <?php foreach ($itemsEpp as $key => $info):
        $val = $inspeccion['estado_' . $key] ?? '';
        $label = $estadosEpp[$val] ?? '-';
    ?>
    <tr>
        <td><?= $info['label'] ?></td>
        <td style="text-align:center; color:<?= $coloresEstado[$val] ?? '#000' ?>; font-weight:bold;">
            <?= $label ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
```
