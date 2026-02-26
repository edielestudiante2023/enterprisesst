# 21 - Auditoria Zona Residuos

## Resumen

Auditoria del cuarto de residuos/basuras de la propiedad horizontal. El consultor inspecciona 12 aspectos del area (acceso, techo/pisos, ventilacion, drenajes, senalizacion, etc.), cada uno con un estado y una foto de evidencia.

**Patron:** Inspeccion PLANA (ver `12_PATRON_INSPECCION_PLANA.md`)
**PDF:** FT-SST-214 — Formato de Inspeccion Cuarto de Residuos

---

## Diferencia vs Patron PLANA tipico

| Aspecto | PLANA tipica | Auditoria Zona Residuos |
|---------|-------------|------------------------|
| Campos por recurso | `cant_{key}` + `obs_{key}` | `estado_{key}` ENUM + `foto_{key}` foto |
| Fotos | Genericas (foto_1, foto_2) | 1 foto por cada item (12 fotos) |
| Campos extra persona | No | No (es inspeccion de ESPACIO, no persona) |
| Campo especial | No | Si — `proliferacion_plagas` es texto libre |
| ENUM estados | No estandar | 6 valores: bueno, regular, malo, deficiente, no_tiene, no_aplica |

---

## Mapeo AppSheet → DB

### Columnas a MIGRAR (27 de 31)

| # | AppSheet | DB Column | Tipo |
|---|----------|-----------|------|
| 1 | ID | `id` | INT AUTO_INCREMENT PK |
| — | (nuevo) | `id_cliente` | INT FK → tbl_clientes |
| — | (nuevo) | `id_consultor` | INT FK → tbl_consultor |
| 4 | Fecha Inspeccion | `fecha_inspeccion` | DATE |
| 7 | Acceso | `estado_acceso` | ENUM(6) |
| 8 | Acceso Foto | `foto_acceso` | VARCHAR(255) |
| 9 | Techo Pared Pisos | `estado_techo_pared_pisos` | ENUM(6) |
| 10 | Techo Pared Pisos Foto | `foto_techo_pared_pisos` | VARCHAR(255) |
| 11 | Ventilacion | `estado_ventilacion` | ENUM(6) |
| 12 | Ventilacion Foto | `foto_ventilacion` | VARCHAR(255) |
| 13 | Prevencion Control Incendios | `estado_prevencion_incendios` | ENUM(6) |
| 14 | Prevencion Control Incendios Foto | `foto_prevencion_incendios` | VARCHAR(255) |
| 15 | Drenajes | `estado_drenajes` | ENUM(6) |
| 16 | Drenajes Foto | `foto_drenajes` | VARCHAR(255) |
| 17 | Proliferacion Plagas | `proliferacion_plagas` | VARCHAR(255) — texto libre |
| 18 | Proliferacion Plagas Foto | `foto_proliferacion_plagas` | VARCHAR(255) |
| 19 | Recipientes | `estado_recipientes` | ENUM(6) |
| 20 | Recipientes Foto | `foto_recipientes` | VARCHAR(255) |
| 21 | Reciclaje | `estado_reciclaje` | ENUM(6) |
| 22 | Reciclaje Foto | `foto_reciclaje` | VARCHAR(255) |
| 23 | Iluminarias | `estado_iluminarias` | ENUM(6) |
| 24 | Iluminarias Foto | `foto_iluminarias` | VARCHAR(255) |
| 25 | Senalizacion | `estado_senalizacion` | ENUM(6) |
| 26 | Senalizacion Foto | `foto_senalizacion` | VARCHAR(255) |
| 27 | Limpieza Desinfeccion | `estado_limpieza_desinfeccion` | ENUM(6) |
| 28 | Limpieza y Desinfeccion Foto | `foto_limpieza_desinfeccion` | VARCHAR(255) |
| 29 | Poseta | `estado_poseta` | ENUM(6) |
| 30 | Poseta Foto | `foto_poseta` | VARCHAR(255) |
| 31 | Otras Observaciones | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

**Total: ~33 columnas**

### Columnas NO almacenadas (4 de 31)

| Categoria | Columnas | Razon |
|-----------|----------|-------|
| **AppSheet interno** (1) | _RowNumber | Campo interno de AppSheet |
| **Timestamp reemplazado** (1) | Fecha Registro | Reemplazado por `created_at` |
| **FK normalizada** (2) | Nombre Responsable Inspeccion, Cliente (como Ref) | Datos vienen via FK |

---

## Items de Inspeccion — 12 items (estado + foto cada uno)

### 11 items con ENUM estandar

| Valor DB | Label visible |
|----------|---------------|
| `bueno` | Bueno |
| `regular` | Regular |
| `malo` | Malo |
| `deficiente` | Deficiente |
| `no_tiene` | No Tiene |
| `no_aplica` | No Aplica |

### 1 item especial: Proliferacion Plagas

Campo `proliferacion_plagas` es **VARCHAR(255) texto libre**. El consultor escribe lo que observa: "NO SE EVIDENCIAN PLAGAS", "MOSQUITO", "CUCARACHAS", etc.

### Constante en controlador

```php
public const ESTADOS_ZONA = [
    'bueno' => 'Bueno',
    'regular' => 'Regular',
    'malo' => 'Malo',
    'deficiente' => 'Deficiente',
    'no_tiene' => 'No Tiene',
    'no_aplica' => 'No Aplica',
];

public const ITEMS_ZONA = [
    'acceso'                  => ['label' => 'Acceso', 'icon' => 'fa-door-open'],
    'techo_pared_pisos'       => ['label' => 'Techo, Pared y Pisos', 'icon' => 'fa-building'],
    'ventilacion'             => ['label' => 'Ventilacion', 'icon' => 'fa-wind'],
    'prevencion_incendios'    => ['label' => 'Prevencion y Control de Incendios', 'icon' => 'fa-fire-extinguisher'],
    'drenajes'                => ['label' => 'Drenajes', 'icon' => 'fa-water'],
    'proliferacion_plagas'    => ['label' => 'Proliferacion de Plagas', 'icon' => 'fa-bug', 'tipo' => 'texto_libre'],
    'recipientes'             => ['label' => 'Recipientes', 'icon' => 'fa-trash-can'],
    'reciclaje'               => ['label' => 'Reciclaje', 'icon' => 'fa-recycle'],
    'iluminarias'             => ['label' => 'Iluminarias', 'icon' => 'fa-lightbulb'],
    'senalizacion'            => ['label' => 'Senalizacion', 'icon' => 'fa-triangle-exclamation'],
    'limpieza_desinfeccion'   => ['label' => 'Limpieza y Desinfeccion', 'icon' => 'fa-spray-can'],
    'poseta'                  => ['label' => 'Poseta', 'icon' => 'fa-faucet-drip'],
];
```

**Nota:** El item `proliferacion_plagas` tiene `'tipo' => 'texto_libre'` para que el form/PDF lo trate distinto (input text en vez de select).

### Color coding

```php
$colores = [
    'bueno' => 'success',       // verde
    'regular' => 'warning',     // amarillo
    'malo' => 'danger',         // rojo
    'deficiente' => 'danger',   // rojo
    'no_tiene' => 'secondary',  // gris
    'no_aplica' => 'light',     // gris claro
];
```

---

## Fotos (12) — 1 por cada item

| Campo DB | Descripcion |
|----------|-------------|
| `foto_acceso` | Foto del acceso al cuarto de residuos |
| `foto_techo_pared_pisos` | Foto del techo, paredes y pisos |
| `foto_ventilacion` | Foto del sistema de ventilacion |
| `foto_prevencion_incendios` | Foto de extintor/prevencion incendios |
| `foto_drenajes` | Foto de los drenajes |
| `foto_proliferacion_plagas` | Foto evidencia de plagas |
| `foto_recipientes` | Foto de recipientes de residuos |
| `foto_reciclaje` | Foto del area de reciclaje |
| `foto_iluminarias` | Foto de las iluminarias |
| `foto_senalizacion` | Foto de la senalizacion |
| `foto_limpieza_desinfeccion` | Foto del estado de limpieza |
| `foto_poseta` | Foto de la poseta |

Dir fotos: `uploads/inspecciones/zona-residuos/`
Dir PDFs: `uploads/inspecciones/zona-residuos/pdfs/`

---

## Tabla SQL: `tbl_auditoria_zona_residuos`

```sql
CREATE TABLE IF NOT EXISTS tbl_auditoria_zona_residuos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- 12 items: estado + foto cada uno
    estado_acceso ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_acceso VARCHAR(255) NULL,

    estado_techo_pared_pisos ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_techo_pared_pisos VARCHAR(255) NULL,

    estado_ventilacion ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_ventilacion VARCHAR(255) NULL,

    estado_prevencion_incendios ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_prevencion_incendios VARCHAR(255) NULL,

    estado_drenajes ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_drenajes VARCHAR(255) NULL,

    proliferacion_plagas VARCHAR(255) NULL,
    foto_proliferacion_plagas VARCHAR(255) NULL,

    estado_recipientes ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_recipientes VARCHAR(255) NULL,

    estado_reciclaje ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_reciclaje VARCHAR(255) NULL,

    estado_iluminarias ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_iluminarias VARCHAR(255) NULL,

    estado_senalizacion ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_senalizacion VARCHAR(255) NULL,

    estado_limpieza_desinfeccion ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_limpieza_desinfeccion VARCHAR(255) NULL,

    estado_poseta ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_poseta VARCHAR(255) NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_aud_res_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_aud_res_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_aud_res_cliente (id_cliente),
    INDEX idx_aud_res_consultor (id_consultor),
    INDEX idx_aud_res_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total: 33 columnas** (id + 2 FK + fecha + 11 ENUM estado + 1 VARCHAR plagas + 12 foto + observaciones + ruta_pdf + estado + 2 timestamps)

---

## Estructura del PDF (FT-SST-214)

### Secciones

1. **Header corporativo**
   - Logo Cycloid | SG-SST | FT-SST-214 / V001 / Fecha

2. **Titulo** — "AUDITORIA ZONA RESIDUOS"

3. **Introduccion** (texto estatico)

4. **Importancia** (texto estatico)

5. **Marco Normativo** (texto estatico)
   - Resolucion 0312/2019, Decreto 1076/2015
   - NTC 288, NTC 4114, NTC 6195, NTC 3631
   - Resolucion 2184/2019

6. **Responsabilidades** (texto estatico)

7. **Informe de Inspeccion** (datos dinamicos)
   - Fecha, Responsable, Cliente
   - 12 items: cada uno con foto + estado (label + color)
   - Observaciones generales

---

## Formulario — Secciones

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha inspeccion | 0 |
| 2-13 | Items (12 cards) | estado select + foto upload (1 card por item) | 12 |
| 14 | Observaciones | textarea libre | 0 |
| 15 | Acciones | Guardar borrador + Finalizar | 0 |

Cada item se muestra como una card con:
- Icono + label
- Select de estado (6 opciones) — o input text para proliferacion_plagas
- Boton foto (camara/galeria) con preview

**Total:** 11 selects + 1 input text + 12 fotos + 1 textarea = ~25 inputs

---

## Rutas (10)

```php
// Auditoria Zona Residuos
$routes->get('zona-residuos', 'AuditoriaZonaResiduosController::list');
$routes->get('zona-residuos/create', 'AuditoriaZonaResiduosController::create');
$routes->get('zona-residuos/create/(:num)', 'AuditoriaZonaResiduosController::create/$1');
$routes->post('zona-residuos/store', 'AuditoriaZonaResiduosController::store');
$routes->get('zona-residuos/edit/(:num)', 'AuditoriaZonaResiduosController::edit/$1');
$routes->post('zona-residuos/update/(:num)', 'AuditoriaZonaResiduosController::update/$1');
$routes->get('zona-residuos/view/(:num)', 'AuditoriaZonaResiduosController::view/$1');
$routes->get('zona-residuos/pdf/(:num)', 'AuditoriaZonaResiduosController::generatePdf/$1');
$routes->post('zona-residuos/finalizar/(:num)', 'AuditoriaZonaResiduosController::finalizar/$1');
$routes->get('zona-residuos/delete/(:num)', 'AuditoriaZonaResiduosController::delete/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 27 |
| `tag` | `aud_res_id:{id}` |

---

## Dashboard

- **Card:** icono `fa-dumpster`, label "Zona Residuos"
- **Pendientes:** borradores
- **Conteo:** `totalZonaResiduos` (estado=completo)

---

## Archivos a crear (7)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_auditoria_zona_residuos.php` | Migracion ~33 columnas |
| `app/Models/AuditoriaZonaResiduosModel.php` | Modelo CRUD |
| `app/Controllers/Inspecciones/AuditoriaZonaResiduosController.php` | Controlador con 12 fotos + 11 ENUMs + 1 texto + PDF |
| `app/Views/inspecciones/zona-residuos/list.php` | Listado cards |
| `app/Views/inspecciones/zona-residuos/form.php` | Formulario (12 items con foto + estado) |
| `app/Views/inspecciones/zona-residuos/view.php` | Vista read-only |
| `app/Views/inspecciones/zona-residuos/pdf.php` | Template DOMPDF |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | 10 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |

---

## Notas de implementacion

### getInspeccionPostData()

Itera la constante diferenciando el tipo de campo:

```php
foreach (self::ITEMS_ZONA as $key => $info) {
    if (($info['tipo'] ?? '') === 'texto_libre') {
        $data[$key] = trim($this->request->getPost($key) ?? '');
    } else {
        $val = $this->request->getPost('estado_' . $key);
        $data['estado_' . $key] = in_array($val, array_keys(self::ESTADOS_ZONA)) ? $val : null;
    }
}
```

### uploadAllPhotos()

```php
foreach (self::ITEMS_ZONA as $key => $info) {
    $campo = 'foto_' . $key;
    $file = $this->request->getFile($campo);
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $data[$campo] = $this->uploadFoto($file, 'zona-residuos');
    } else {
        $data[$campo] = $inspeccion[$campo] ?? null;
    }
}
```

### Formulario — Card por item

```html
<?php foreach ($itemsZona as $key => $info): ?>
<div class="card mb-3">
    <div class="card-header">
        <i class="fas <?= $info['icon'] ?> me-2"></i><?= $info['label'] ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?php if (($info['tipo'] ?? '') === 'texto_libre'): ?>
                <input type="text" name="<?= $key ?>" class="form-control"
                    value="<?= esc($inspeccion[$key] ?? '') ?>"
                    placeholder="Ej: No se evidencian plagas, Mosquito...">
                <?php else: ?>
                <select name="estado_<?= $key ?>" class="form-select">
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($estadosZona as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($inspeccion['estado_'.$key] ?? '') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <input type="file" name="foto_<?= $key ?>" accept="image/*" class="form-control">
                <!-- Preview si existe -->
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
```
