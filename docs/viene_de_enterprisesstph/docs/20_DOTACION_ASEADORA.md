# 20 - Dotacion Aseadora

## Resumen

Inspeccion de la dotacion y EPP del personal de aseo (operarias de servicios generales) en la propiedad horizontal. El consultor verifica el estado de 8 items especificos del equipamiento de la aseadora y registra evidencia fotografica.

**Patron:** Inspeccion PLANA (ver `12_PATRON_INSPECCION_PLANA.md`)
**PDF:** FT-SST-213 — Formato de Inspeccion Dotaciones y EPP (mismo codigo que Vigilante y Todero)
**Gemelos:** `18_DOTACION_VIGILANTE.md` (7 items), `19_DOTACION_TODERO.md` (16 items)

---

## Relacion con Dotacion Vigilante / Todero

Misma estructura exacta. Solo cambia la lista de items EPP:
- Vigilante: 7 items (uniforme, chaqueta, radio, baston, calzado, gorra, carne)
- Todero: 16 items (EPP industrial completo)
- **Aseadora: 8 items** (EPP limpieza: tapabocas, guantes, caucho, gafas, uniforme, sombrero, zapato, botas caucho)

---

## Mapeo AppSheet → DB

### Columnas a MIGRAR (19 de 23)

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
| 13 | Tapabocas Desechable | `estado_tapabocas` | ENUM(5) |
| 14 | Guantes De Nitrilo | `estado_guantes_nitrilo` | ENUM(5) |
| 15 | Guantes De Caucho Calibre 20, 25, 50 | `estado_guantes_caucho` | ENUM(5) |
| 16 | Gafas De Seguridad | `estado_gafas` | ENUM(5) |
| 17 | Uniforme | `estado_uniforme` | ENUM(5) |
| 18 | Sombrero / Gorra | `estado_sombrero` | ENUM(5) |
| 19 | Zapato Antideslizante | `estado_zapato` | ENUM(5) |
| 20 | Botas de Caucho | `estado_botas_caucho` | ENUM(5) |
| 21 | Concepto Final del Inspector SST | `concepto_final` | TEXT |
| — | (nuevo) | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

**Total: ~25 columnas**

### Columnas NO almacenadas (4 de 23)

| Categoria | Columnas | Razon |
|-----------|----------|-------|
| **AppSheet interno** (1) | _RowNumber | Campo interno de AppSheet |
| **Timestamp reemplazado** (1) | FECHA DEL REGISTRO | Reemplazado por `created_at` |
| **FK normalizada** (2) | NOMBRE DEL RESPONSABLE, LOGO_CLIENTE, FECHA_SGSST | Datos vienen de tbl_clientes y tbl_consultor via FK |

---

## Items EPP — 8 items con ENUM de 5 valores

| Valor DB | Label visible |
|----------|---------------|
| `bueno` | Bueno |
| `regular` | Regular |
| `deficiente` | Deficiente |
| `no_tiene` | No Tiene |
| `no_aplica` | No Aplica |

### Constante ITEMS_EPP en controlador

```php
public const ITEMS_EPP = [
    'tapabocas'      => ['label' => 'Tapabocas Desechable', 'icon' => 'fa-head-side-mask'],
    'guantes_nitrilo'=> ['label' => 'Guantes de Nitrilo', 'icon' => 'fa-hand'],
    'guantes_caucho' => ['label' => 'Guantes de Caucho Calibre 20, 25, 50', 'icon' => 'fa-mitten'],
    'gafas'          => ['label' => 'Gafas de Seguridad', 'icon' => 'fa-glasses'],
    'uniforme'       => ['label' => 'Uniforme', 'icon' => 'fa-shirt'],
    'sombrero'       => ['label' => 'Sombrero / Gorra', 'icon' => 'fa-hat-cowboy'],
    'zapato'         => ['label' => 'Zapato Antideslizante', 'icon' => 'fa-shoe-prints'],
    'botas_caucho'   => ['label' => 'Botas de Caucho', 'icon' => 'fa-boot'],
];
```

---

## Fotos (2)

| Campo DB | Descripcion |
|----------|-------------|
| `foto_cuerpo_completo` | Foto de cuerpo completo de la aseadora uniformada |
| `foto_cuarto_almacenamiento` | Foto del cuarto de almacenamiento de EPPs |

Dir fotos: `uploads/inspecciones/dotacion-aseadora/`
Dir PDFs: `uploads/inspecciones/dotacion-aseadora/pdfs/`

---

## Tabla SQL: `tbl_dotacion_aseadora`

```sql
CREATE TABLE IF NOT EXISTS tbl_dotacion_aseadora (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Datos del contratista/aseadora
    contratista VARCHAR(255) NULL,
    servicio VARCHAR(255) NULL,
    nombre_cargo VARCHAR(255) NULL,
    actividades_frecuentes TEXT NULL,

    -- Fotos
    foto_cuerpo_completo VARCHAR(255) NULL,
    foto_cuarto_almacenamiento VARCHAR(255) NULL,

    -- Items EPP (8 items x ENUM)
    estado_tapabocas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_nitrilo ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_caucho ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_gafas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_uniforme ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_sombrero ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_zapato ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_botas_caucho ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,

    -- Concepto
    concepto_final TEXT NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_dot_ase_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_dot_ase_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dot_ase_cliente (id_cliente),
    INDEX idx_dot_ase_consultor (id_consultor),
    INDEX idx_dot_ase_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total: 25 columnas** (id + 2 FK + fecha + 4 texto/varchar + 2 foto + 8 ENUM EPP + concepto + observaciones + ruta_pdf + estado + 2 timestamps)

---

## Estructura del PDF (FT-SST-213)

Identico a Vigilante y Todero — mismo texto estatico, solo cambia tabla EPP (8 items).

---

## Formulario — Secciones (5)

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha inspeccion | 0 |
| 2 | Datos del Contratista | contratista, servicio, nombre_cargo, actividades_frecuentes | 0 |
| 3 | Registro Fotografico | — | 2 (cuerpo completo, cuarto almacenamiento) |
| 4 | Estado de Dotacion EPP | 8 selects (uno por item) | 0 |
| 5 | Concepto Final y Observaciones | concepto_final textarea, observaciones textarea | 0 |
| 6 | Acciones | Guardar borrador + Finalizar | 0 |

---

## Rutas (10)

```php
// Dotacion Aseadora
$routes->get('dotacion-aseadora', 'DotacionAseadoraController::list');
$routes->get('dotacion-aseadora/create', 'DotacionAseadoraController::create');
$routes->get('dotacion-aseadora/create/(:num)', 'DotacionAseadoraController::create/$1');
$routes->post('dotacion-aseadora/store', 'DotacionAseadoraController::store');
$routes->get('dotacion-aseadora/edit/(:num)', 'DotacionAseadoraController::edit/$1');
$routes->post('dotacion-aseadora/update/(:num)', 'DotacionAseadoraController::update/$1');
$routes->get('dotacion-aseadora/view/(:num)', 'DotacionAseadoraController::view/$1');
$routes->get('dotacion-aseadora/pdf/(:num)', 'DotacionAseadoraController::generatePdf/$1');
$routes->post('dotacion-aseadora/finalizar/(:num)', 'DotacionAseadoraController::finalizar/$1');
$routes->get('dotacion-aseadora/delete/(:num)', 'DotacionAseadoraController::delete/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 26 |
| `tag` | `dot_ase_id:{id}` |

---

## Dashboard

- **Card:** icono `fa-spray-can-sparkles`, label "Dotacion Aseadora"
- **Pendientes:** borradores
- **Conteo:** `totalDotacionAseadora` (estado=completo)

---

## Archivos a crear (7)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_dotacion_aseadora.php` | Migracion ~25 columnas |
| `app/Models/DotacionAseadoraModel.php` | Modelo CRUD |
| `app/Controllers/Inspecciones/DotacionAseadoraController.php` | Controlador con 2 fotos + 8 ENUMs + PDF |
| `app/Views/inspecciones/dotacion-aseadora/list.php` | Listado cards |
| `app/Views/inspecciones/dotacion-aseadora/form.php` | Formulario (~5 secciones) |
| `app/Views/inspecciones/dotacion-aseadora/view.php` | Vista read-only |
| `app/Views/inspecciones/dotacion-aseadora/pdf.php` | Template DOMPDF |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | 10 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |
