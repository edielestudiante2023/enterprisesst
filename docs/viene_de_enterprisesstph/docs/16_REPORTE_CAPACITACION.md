# 16 - Reporte de Capacitacion

## Resumen

Cuando el consultor va a un cliente a dictar una capacitacion en SST, debe quedar un reporte con los datos de la sesion, asistentes, evaluacion y fotos. Este modulo registra ese reporte y genera un PDF (FT-SST-211).

**Patron:** Inspeccion PLANA (ver `12_PATRON_INSPECCION_PLANA.md`)
**NO es una inspeccion fisica** — es un registro de capacitacion, pero sigue el mismo flujo CRUD+PDF.

---

## Relacion con sistema existente

Ya existe un sistema de **cronograma de capacitaciones** (`tbl_cronog_capacitacion`, `CronogcapacitacionModel`). Ese sistema programa las fechas de capacitacion. El **Reporte de Capacitacion** registra lo que PASO en la sesion (asistentes, fotos, evaluacion).

Son sistemas complementarios:
- `tbl_cronog_capacitacion` → PLANIFICACION (que capacitaciones hay que dar y cuando)
- `tbl_reporte_capacitacion` → EJECUCION (que paso cuando se dicto la capacitacion)

Por ahora **no se vinculan** por FK. El consultor puede crear un reporte sin que exista un registro en el cronograma. A futuro se podria agregar un campo `id_cronograma_capacitacion` opcional.

---

## Mapeo AppSheet → DB

### Columnas a MIGRAR (18 de 31)

| # | AppSheet | DB Column | Tipo |
|---|----------|-----------|------|
| 1 | ID | `id` | INT AUTO_INCREMENT PK |
| — | (nuevo) | `id_cliente` | INT FK → tbl_clientes |
| — | (nuevo) | `id_consultor` | INT FK → tbl_consultor |
| 4 | FECHA DE LA CAPACITACION | `fecha_capacitacion` | DATE |
| 7 | NOMBRE DE LA CAPACITACION | `nombre_capacitacion` | TEXT |
| 8 | OBJETIVO DE LA CAPACITACION | `objetivo_capacitacion` | TEXT |
| 16 | PERFIL DE ASISTENTES | `perfil_asistentes` | TEXT (comma-separated multi-select) |
| 17 | NOMBRE DEL CAPACITADOR | `nombre_capacitador` | VARCHAR(255) |
| 18 | HORAS DE DURACION | `horas_duracion` | DECIMAL(4,1) |
| 20 | NUMERO DE ASISTENTES | `numero_asistentes` | SMALLINT |
| 21 | NUMERO TOTAL DE PERSONAS PROGRAMADAS | `numero_programados` | SMALLINT |
| 23 | NUMERO DE PERSONAS EVALUADAS | `numero_evaluados` | SMALLINT |
| 24 | PROMEDIO DE CALIFICACIONES | `promedio_calificaciones` | DECIMAL(5,2) |
| 25 | REGISTRO FOTOGRAFICO LISTADO ASISTENCIA | `foto_listado_asistencia` | VARCHAR(255) |
| 26 | REGISTRO FOTOGRAFICO CAPACITACION | `foto_capacitacion` | VARCHAR(255) |
| 27 | REGISTRO FOTOGRAFICO EVALUACION | `foto_evaluacion` | VARCHAR(255) |
| 28 | OTROS REGISTROS DE LA SESION | `foto_otros_1` | VARCHAR(255) |
| 29 | OTROS REGISTROS DE LA_SESION | `foto_otros_2` | VARCHAR(255) |
| — | (nuevo) | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

**Total: ~24 columnas**

### Columnas NO almacenadas (13 de 31)

| Categoria | Columnas | Razon |
|-----------|----------|-------|
| **AppSheet interno** (1) | _RowNumber | Campo interno de AppSheet |
| **FK normalizada** (4) | NOMBRE DEL CONSULTOR, CLIENTE, LOGO_CLIENTE, FECHA_SGSST | Datos vienen de tbl_clientes y tbl_consultor via FK |
| **Timestamp reemplazado** (1) | FECHA DEL REGISTRO | Reemplazado por `created_at` |
| **Programacion NO usada** (5) | FECHA PROGRAMADA, SEMANA PROGRAMADA, MES PROGRAMADO, AÑO DE LA CAPACITACION, FECHA DE REALIZACION | Siempre vacios. Programacion vive en tbl_cronog_capacitacion |
| **Calculados** (3) | DIAS PARA LA CAPACITACION, INDICADOR DE REALIZACION, % COBERTURA | Se calculan en PHP al vuelo |
| **Estado reemplazado** (1) | ESTADO (AppSheet) | Reemplazado por ENUM('borrador','completo') |

### Campos CALCULADOS en PHP (no almacenados)

```php
$cobertura = $numero_programados > 0
    ? round(($numero_asistentes / $numero_programados) * 100, 1)
    : 0;
```

---

## PERFIL DE ASISTENTES (multi-select)

Campo `perfil_asistentes` almacena multiples valores separados por coma.

### Valores posibles (5)

| Valor DB | Label visible |
|----------|---------------|
| `contratistas` | Contratistas |
| `administrador` | Administrador |
| `consejo_administracion` | Consejo de Administracion |
| `residentes` | Residentes |
| `todos` | Todos |

### Implementacion en formulario

Checkboxes multiples (no select):

```html
<div class="form-check">
    <input type="checkbox" name="perfil_asistentes[]" value="contratistas" class="form-check-input">
    <label class="form-check-label">Contratistas</label>
</div>
<!-- ... -->
```

### Almacenamiento en DB

```php
// Guardar
$data['perfil_asistentes'] = implode(',', $this->request->getPost('perfil_asistentes') ?? []);

// Leer
$perfiles = explode(',', $inspeccion['perfil_asistentes']);
```

### Constante en controlador

```php
public const PERFILES_ASISTENTES = [
    'contratistas' => 'Contratistas',
    'administrador' => 'Administrador',
    'consejo_administracion' => 'Consejo de Administracion',
    'residentes' => 'Residentes',
    'todos' => 'Todos',
];
```

---

## Fotos (5)

| Campo DB | Descripcion |
|----------|-------------|
| `foto_listado_asistencia` | Foto del listado de asistencia firmado |
| `foto_capacitacion` | Foto de la sesion de capacitacion |
| `foto_evaluacion` | Foto de la evaluacion escrita |
| `foto_otros_1` | Otros registros de la sesion (1) |
| `foto_otros_2` | Otros registros de la sesion (2) |

Dir fotos: `uploads/inspecciones/reporte-capacitacion/`
Dir PDFs: `uploads/inspecciones/reporte-capacitacion/pdfs/`

---

## Tabla SQL: `tbl_reporte_capacitacion`

```sql
CREATE TABLE IF NOT EXISTS tbl_reporte_capacitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_capacitacion DATE NOT NULL,

    -- Datos de la capacitacion
    nombre_capacitacion TEXT NULL,
    objetivo_capacitacion TEXT NULL,
    perfil_asistentes TEXT NULL,
    nombre_capacitador VARCHAR(255) NULL,
    horas_duracion DECIMAL(4,1) NULL,

    -- Asistencia y evaluacion
    numero_asistentes SMALLINT NULL DEFAULT 0,
    numero_programados SMALLINT NULL DEFAULT 0,
    numero_evaluados SMALLINT NULL DEFAULT 0,
    promedio_calificaciones DECIMAL(5,2) NULL,

    -- Fotos
    foto_listado_asistencia VARCHAR(255) NULL,
    foto_capacitacion VARCHAR(255) NULL,
    foto_evaluacion VARCHAR(255) NULL,
    foto_otros_1 VARCHAR(255) NULL,
    foto_otros_2 VARCHAR(255) NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rep_cap_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_rep_cap_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_rep_cap_cliente (id_cliente),
    INDEX idx_rep_cap_consultor (id_consultor),
    INDEX idx_rep_cap_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total: 24 columnas** (id + 2 FK + fecha + 4 texto + 3 SMALLINT + 2 DECIMAL + 5 foto + observaciones + ruta_pdf + estado + 2 timestamps)

---

## Estructura del PDF (FT-SST-211)

### Secciones

1. **Header corporativo**
   - Logo cliente | SG-SST | Codigo: FT-SST-211 / Version: 001 / Fecha

2. **Titulo**
   - "REPORTE CAPACITACION CYCLOID TALENT"
   - "Capacitacion en SST para Contratistas en Propiedad Horizontal"

3. **Introduccion** (texto estatico)
   - Parrafo sobre importancia de capacitacion SST en propiedad horizontal

4. **Beneficios** (texto estatico, 4 items)
   - Prevencion de Accidentes y Enfermedades Laborales
   - Cumplimiento Legal (Ley 1563/2015, Decreto 1072/2015)
   - Mejora en la Productividad
   - Mejora en la Imagen de la Comunidad

5. **Datos de la Capacitacion** (tabla datos dinamicos)
   - Fecha, Cliente, Nombre capacitacion, Perfil asistentes
   - Nombre del capacitador, Horas de duracion

6. **Indicadores** (tabla datos dinamicos)
   - Numero de asistentes / programados / evaluados
   - % Cobertura (calculado)
   - Promedio de calificaciones

7. **Registro Fotografico** (fotos base64)
   - Listado de asistencia, Capacitacion, Evaluacion, Otros

8. **Observaciones** (si hay)

---

## Formulario — Secciones (6)

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha capacitacion | 0 |
| 2 | Informacion de la Capacitacion | nombre, objetivo, perfil (checkboxes), capacitador, horas | 0 |
| 3 | Asistencia y Evaluacion | asistentes, programados, evaluados, promedio calificaciones | 0 |
| 4 | Registro Fotografico | — | 5 (listado, capacitacion, evaluacion, otros x2) |
| 5 | Observaciones | textarea libre | 0 |
| 6 | Acciones | Guardar borrador + Finalizar | 0 |

**Total:** ~13 campos editables + 5 fotos + checkboxes perfil = ~19 inputs

---

## Rutas (10)

```php
// Reporte de Capacitacion
$routes->get('reporte-capacitacion', 'ReporteCapacitacionController::list');
$routes->get('reporte-capacitacion/create', 'ReporteCapacitacionController::create');
$routes->get('reporte-capacitacion/create/(:num)', 'ReporteCapacitacionController::create/$1');
$routes->post('reporte-capacitacion/store', 'ReporteCapacitacionController::store');
$routes->get('reporte-capacitacion/edit/(:num)', 'ReporteCapacitacionController::edit/$1');
$routes->post('reporte-capacitacion/update/(:num)', 'ReporteCapacitacionController::update/$1');
$routes->get('reporte-capacitacion/view/(:num)', 'ReporteCapacitacionController::view/$1');
$routes->get('reporte-capacitacion/pdf/(:num)', 'ReporteCapacitacionController::generatePdf/$1');
$routes->post('reporte-capacitacion/finalizar/(:num)', 'ReporteCapacitacionController::finalizar/$1');
$routes->get('reporte-capacitacion/delete/(:num)', 'ReporteCapacitacionController::delete/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 21 |
| `tag` | `rep_cap_id:{id}` |

---

## Dashboard

- **Card:** icono `fa-chalkboard-teacher`, label "Capacitaciones"
- **Pendientes:** borradores despues de Plan de Emergencia
- **Conteo:** `totalCapacitaciones` (estado=completo)

---

## Archivos a crear (7)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_reporte_capacitacion.php` | Migracion ~24 columnas |
| `app/Models/ReporteCapacitacionModel.php` | Modelo CRUD |
| `app/Controllers/Inspecciones/ReporteCapacitacionController.php` | Controlador con 5 fotos + PDF |
| `app/Views/inspecciones/reporte-capacitacion/list.php` | Listado |
| `app/Views/inspecciones/reporte-capacitacion/form.php` | Formulario (~6 secciones) |
| `app/Views/inspecciones/reporte-capacitacion/view.php` | Vista read-only |
| `app/Views/inspecciones/reporte-capacitacion/pdf.php` | Template DOMPDF (texto estatico + datos) |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | 10 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |

---

## Diferencias vs Inspeccion PLANA tipica

| Aspecto | Inspeccion PLANA tipica | Reporte Capacitacion |
|---------|------------------------|----------------------|
| Constante RECURSOS | Si (iterable) | No — campos fijos, no iterables |
| Campo multi-select | No | Si — `perfil_asistentes` (checkboxes) |
| Campos numericos | cantidades enteras | Incluye DECIMAL (horas, promedio, cobertura) |
| Campo calculado | No | Si — % cobertura = asistentes/programados |
| Texto estatico PDF | Corto | Medio (~4 parrafos intro + beneficios) |
| Icono dashboard | Varia | `fa-chalkboard-teacher` |
