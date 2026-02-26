# 19 - Dotacion Todero

## Resumen

Inspeccion de la dotacion y EPP del personal de aseo/todero (mantenimiento, jardineria, servicios generales) en la propiedad horizontal. El consultor verifica el estado de 16 items especificos del equipamiento del todero y registra evidencia fotografica.

**Patron:** Inspeccion PLANA (ver `12_PATRON_INSPECCION_PLANA.md`)
**PDF:** FT-SST-213 — Formato de Inspeccion Dotaciones y EPP (mismo codigo que Vigilante)
**Gemelo:** `18_DOTACION_VIGILANTE.md` — Misma estructura exacta, solo cambian los items EPP (7 vs 16)

---

## Relacion con Dotacion Vigilante

Ambos modulos comparten:
- **Mismo texto estatico PDF** (introduccion, marco legal, NTCs, conceptos basicos)
- **Mismos campos base** (contratista, servicio, cargo, actividades, 2 fotos)
- **Mismos valores ENUM** (bueno, regular, deficiente, no_tiene, no_aplica)
- **Mismo codigo PDF** FT-SST-213

La UNICA diferencia es la **lista de items EPP**:
- Vigilante: 7 items (uniforme, chaqueta, radio, baston, calzado, gorra, carne)
- Todero: 16 items (tapabocas, guantes nitrilo, mascarilla, guantes nylon, guantes caucho, gafas, uniforme, sombrero, zapato, casco, careta, protector auditivo, respirador, guantes vaqueta, botas dielectricas, delantal PVC)

**Implementacion:** Tablas separadas pero controladores pueden heredar de una clase base o compartir logica via trait.

---

## Mapeo AppSheet → DB

### Columnas a MIGRAR (27 de 31)

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
| 15 | Mascarilla Para Polvo Desechable Sin Filtro | `estado_mascarilla_polvo` | ENUM(5) |
| 16 | Guantes Nylon Recubierto Con Poliuretano | `estado_guantes_nylon` | ENUM(5) |
| 17 | Guantes De Caucho Calibre 20, 25, 50 | `estado_guantes_caucho` | ENUM(5) |
| 18 | Gafas De Seguridad | `estado_gafas` | ENUM(5) |
| 19 | Uniforme - Camisa Pantalon - Overol | `estado_uniforme` | ENUM(5) |
| 20 | Sombrero / Gorra | `estado_sombrero` | ENUM(5) |
| 21 | Zapato Antideslizante | `estado_zapato` | ENUM(5) |
| 22 | Casco De Seguridad Con Rachet | `estado_casco` | ENUM(5) |
| 23 | Careta De Proteccion | `estado_careta` | ENUM(5) |
| 24 | Protector Auditivo De Copa | `estado_protector_auditivo` | ENUM(5) |
| 25 | Respirador De Media Cara | `estado_respirador` | ENUM(5) |
| 26 | Guantes De Vaqueta | `estado_guantes_vaqueta` | ENUM(5) |
| 27 | Botas De Seguridad Dielectricas | `estado_botas_dielectricas` | ENUM(5) |
| 28 | Delantal De Pvc | `estado_delantal_pvc` | ENUM(5) |
| 29 | Concepto Final del Inspector SST | `concepto_final` | TEXT |
| — | (nuevo) | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

**Total: ~32 columnas**

### Columnas NO almacenadas (4 de 31)

| Categoria | Columnas | Razon |
|-----------|----------|-------|
| **AppSheet interno** (1) | _RowNumber | Campo interno de AppSheet |
| **Timestamp reemplazado** (1) | FECHA DEL REGISTRO | Reemplazado por `created_at` |
| **FK normalizada** (2) | NOMBRE DEL RESPONSABLE, LOGO_CLIENTE, FECHA_SGSST | Datos vienen de tbl_clientes y tbl_consultor via FK |

---

## Items EPP — 16 items con ENUM de 5 valores

Mismos valores que Dotacion Vigilante:

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
    'tapabocas'           => ['label' => 'Tapabocas Desechable', 'icon' => 'fa-head-side-mask'],
    'guantes_nitrilo'     => ['label' => 'Guantes de Nitrilo', 'icon' => 'fa-hand'],
    'mascarilla_polvo'    => ['label' => 'Mascarilla Para Polvo Desechable Sin Filtro', 'icon' => 'fa-mask-face'],
    'guantes_nylon'       => ['label' => 'Guantes Nylon Recubierto Con Poliuretano', 'icon' => 'fa-hand'],
    'guantes_caucho'      => ['label' => 'Guantes de Caucho Calibre 20, 25, 50', 'icon' => 'fa-mitten'],
    'gafas'               => ['label' => 'Gafas de Seguridad', 'icon' => 'fa-glasses'],
    'uniforme'            => ['label' => 'Uniforme - Camisa Pantalon - Overol', 'icon' => 'fa-shirt'],
    'sombrero'            => ['label' => 'Sombrero / Gorra', 'icon' => 'fa-hat-cowboy'],
    'zapato'              => ['label' => 'Zapato Antideslizante', 'icon' => 'fa-shoe-prints'],
    'casco'               => ['label' => 'Casco de Seguridad Con Rachet', 'icon' => 'fa-hard-hat'],
    'careta'              => ['label' => 'Careta de Proteccion', 'icon' => 'fa-head-side-virus'],
    'protector_auditivo'  => ['label' => 'Protector Auditivo de Copa', 'icon' => 'fa-headphones'],
    'respirador'          => ['label' => 'Respirador de Media Cara', 'icon' => 'fa-mask-ventilator'],
    'guantes_vaqueta'     => ['label' => 'Guantes de Vaqueta', 'icon' => 'fa-mitten'],
    'botas_dielectricas'  => ['label' => 'Botas de Seguridad Dielectricas', 'icon' => 'fa-boot'],
    'delantal_pvc'        => ['label' => 'Delantal de PVC', 'icon' => 'fa-vest'],
];
```

---

## Fotos (2)

| Campo DB | Descripcion |
|----------|-------------|
| `foto_cuerpo_completo` | Foto de cuerpo completo del todero uniformado |
| `foto_cuarto_almacenamiento` | Foto del cuarto de almacenamiento de EPPs |

Dir fotos: `uploads/inspecciones/dotacion-todero/`
Dir PDFs: `uploads/inspecciones/dotacion-todero/pdfs/`

---

## Tabla SQL: `tbl_dotacion_todero`

```sql
CREATE TABLE IF NOT EXISTS tbl_dotacion_todero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Datos del contratista/todero
    contratista VARCHAR(255) NULL,
    servicio VARCHAR(255) NULL,
    nombre_cargo VARCHAR(255) NULL,
    actividades_frecuentes TEXT NULL,

    -- Fotos
    foto_cuerpo_completo VARCHAR(255) NULL,
    foto_cuarto_almacenamiento VARCHAR(255) NULL,

    -- Items EPP (16 items x ENUM)
    estado_tapabocas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_nitrilo ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_mascarilla_polvo ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_nylon ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_caucho ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_gafas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_uniforme ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_sombrero ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_zapato ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_casco ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_careta ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_protector_auditivo ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_respirador ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_vaqueta ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_botas_dielectricas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_delantal_pvc ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,

    -- Concepto
    concepto_final TEXT NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_dot_tod_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_dot_tod_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dot_tod_cliente (id_cliente),
    INDEX idx_dot_tod_consultor (id_consultor),
    INDEX idx_dot_tod_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total: 32 columnas** (id + 2 FK + fecha + 4 texto/varchar + 2 foto + 16 ENUM EPP + concepto + observaciones + ruta_pdf + estado + 2 timestamps)

---

## Estructura del PDF (FT-SST-213)

**Identico al PDF de Dotacion Vigilante** — mismo texto estatico, misma estructura. Solo cambia la tabla de items EPP (16 items en vez de 7).

### Secciones

1. **Header corporativo** — Logo cliente | SG-SST | FT-SST-213 / V001 / Fecha
2. **Titulo** — "Reporte de Inspeccion de Dotaciones"
3. **Introduccion** (texto estatico)
4. **Marco Legal** (texto estatico — Art 230, Ley 1072, Decreto 1443, Res 1072, 13 NTCs)
5. **Conceptos Basicos** (texto estatico)
6. **Importancia y Aspectos** (texto estatico)
7. **Datos de la Inspeccion** (tabla dinamica — fecha, cliente, contratista, servicio, cargo, actividades)
8. **Registro Fotografico** (2 fotos base64)
9. **Estado de Dotacion** (tabla dinamica — 16 items con estado y color coding)
10. **Concepto Final del Inspector** (texto dinamico)

---

## Formulario — Secciones (5)

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha inspeccion | 0 |
| 2 | Datos del Contratista | contratista, servicio, nombre_cargo, actividades_frecuentes | 0 |
| 3 | Registro Fotografico | — | 2 (cuerpo completo, cuarto almacenamiento) |
| 4 | Estado de Dotacion EPP | 16 selects (uno por item) | 0 |
| 5 | Concepto Final y Observaciones | concepto_final textarea, observaciones textarea | 0 |
| 6 | Acciones | Guardar borrador + Finalizar | 0 |

**Total:** ~20 campos editables + 2 fotos + 16 selects = ~38 inputs

---

## Rutas (10)

```php
// Dotacion Todero
$routes->get('dotacion-todero', 'DotacionToderoController::list');
$routes->get('dotacion-todero/create', 'DotacionToderoController::create');
$routes->get('dotacion-todero/create/(:num)', 'DotacionToderoController::create/$1');
$routes->post('dotacion-todero/store', 'DotacionToderoController::store');
$routes->get('dotacion-todero/edit/(:num)', 'DotacionToderoController::edit/$1');
$routes->post('dotacion-todero/update/(:num)', 'DotacionToderoController::update/$1');
$routes->get('dotacion-todero/view/(:num)', 'DotacionToderoController::view/$1');
$routes->get('dotacion-todero/pdf/(:num)', 'DotacionToderoController::generatePdf/$1');
$routes->post('dotacion-todero/finalizar/(:num)', 'DotacionToderoController::finalizar/$1');
$routes->get('dotacion-todero/delete/(:num)', 'DotacionToderoController::delete/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 25 |
| `tag` | `dot_tod_id:{id}` |

---

## Dashboard

- **Card:** icono `fa-broom`, label "Dotacion Todero"
- **Pendientes:** borradores
- **Conteo:** `totalDotacionTodero` (estado=completo)

---

## Archivos a crear (7)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_dotacion_todero.php` | Migracion ~32 columnas |
| `app/Models/DotacionToderoModel.php` | Modelo CRUD |
| `app/Controllers/Inspecciones/DotacionToderoController.php` | Controlador con 2 fotos + 16 ENUMs + PDF |
| `app/Views/inspecciones/dotacion-todero/list.php` | Listado cards |
| `app/Views/inspecciones/dotacion-todero/form.php` | Formulario (~5 secciones) |
| `app/Views/inspecciones/dotacion-todero/view.php` | Vista read-only |
| `app/Views/inspecciones/dotacion-todero/pdf.php` | Template DOMPDF (texto legal estatico + datos) |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | 10 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |
