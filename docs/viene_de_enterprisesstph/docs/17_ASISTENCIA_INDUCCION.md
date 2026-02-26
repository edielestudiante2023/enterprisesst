# 17 - Listado de Asistencia / Induccion

## Resumen

El consultor dicta una sesion (induccion, charla, reunion, capacitacion) en el cliente. Los asistentes firman uno a uno en el celular del consultor (Canvas HTML5, flujo "pasar el celular"). Al finalizar se genera el PDF de listado de asistencia (FT-SST-005). Si el tipo de charla es **Induccion / Reinduccion**, se genera adicionalmente el **Acta de Responsabilidades SST** (FT-SST-003).

**Patron:** N-Items dinamico con firmas (ver `13_PATRON_INSPECCION_NITEMS.md`)

---

## Diferencia vs N-Items tipico (Extintores, Gabinetes)

| Aspecto | N-Items tipico | Asistencia/Induccion |
|---------|----------------|----------------------|
| Items | Objetos fisicos (extintores, gabinetes) | Personas (asistentes) |
| Campos por item | 12+ criterios de inspeccion | 4: nombre, cedula, cargo, firma |
| Fotos por item | 1-2 fotos opcionales | 1 firma obligatoria (Canvas) |
| Firma digital | No | Si — cada asistente firma en Canvas |
| Flujo de llenado | Consultor llena todo | Consultor llena datos, asistentes firman uno a uno |
| PDFs generados | 1 | 1 o 2 (condicional segun tipo de charla) |
| Limite items | Practico (~20) | Sin limite (AppSheet tenia 30 hardcodeado) |

---

## Mapeo AppSheet → DB

### AppSheet: 164 columnas → 2 tablas normalizadas

AppSheet hardcodeaba 30 asistentes x 5 campos = 150 columnas + 14 master = 164.
En nuestro sistema: 1 tabla master (~15 cols) + 1 tabla detalle (6 cols por fila, N filas).

### Tabla master: campos del AppSheet migrados

| # | AppSheet | DB Column | Tipo |
|---|----------|-----------|------|
| 2 | ID | `id` | INT AUTO_INCREMENT PK |
| — | (nuevo) | `id_cliente` | INT FK → tbl_clientes |
| — | (nuevo) | `id_consultor` | INT FK → tbl_consultor |
| 6 | TEMA | `tema` | TEXT |
| 7 | LUGAR | `lugar` | VARCHAR(255) |
| 8 | FECHA | `fecha_sesion` | DATE |
| 9 | OBJETIVO | `objetivo` | TEXT |
| 10 | CAPACITADOR | `capacitador` | VARCHAR(255) |
| 11 | TIPO DE CHARLA | `tipo_charla` | ENUM (5 valores) |
| 12 | MATERIAL | `material` | VARCHAR(255) |
| 13 | TIEMPO | `tiempo_horas` | DECIMAL(4,1) |
| — | (nuevo) | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf_asistencia` | VARCHAR(255) |
| — | (nuevo) | `ruta_pdf_responsabilidades` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

### Tabla detalle: 1 fila por asistente

| AppSheet (x30) | DB Column | Tipo |
|-----------------|-----------|------|
| NOMBRE{N} | `nombre` | VARCHAR(255) |
| CEDULA{N} | `cedula` | VARCHAR(50) |
| CARGO{N} | `cargo` | VARCHAR(255) |
| FIRMA{N} | `firma` | VARCHAR(255) — path PNG |

### Columnas NO almacenadas (6 de 164)

| Columna | Razon |
|---------|-------|
| _RowNumber | AppSheet interno |
| LOGO DEL CLIENTE | FK → tbl_clientes.logo |
| NOMBRE DEL CLIENTE | FK → tbl_clientes.nombre_cliente |
| NOMBRE DEL CONSULTOR | FK → tbl_consultor.nombre_consultor |
| Ultimo_Firmante{N} (x30) | Reemplazado por logica PHP: finalizar solo si todos tienen firma |
| FECHA_SGSST | FK → tbl_clientes |

---

## ENUM: tipo_charla

| Valor DB | Label visible |
|----------|---------------|
| `induccion_reinduccion` | Induccion / Reinduccion |
| `reunion` | Reunion |
| `charla` | Charla |
| `capacitacion` | Capacitacion |
| `otros_temas` | Otros Temas |

**Regla clave:** Si `tipo_charla = 'induccion_reinduccion'` → al finalizar se generan **2 PDFs** (asistencia + responsabilidades SST).

### Constante en controlador

```php
public const TIPOS_CHARLA = [
    'induccion_reinduccion' => 'Induccion / Reinduccion',
    'reunion' => 'Reunion',
    'charla' => 'Charla',
    'capacitacion' => 'Capacitacion',
    'otros_temas' => 'Otros Temas',
];
```

---

## Tablas SQL

### Master: `tbl_asistencia_induccion`

```sql
CREATE TABLE IF NOT EXISTS tbl_asistencia_induccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_sesion DATE NOT NULL,

    -- Datos de la sesion
    tema TEXT NULL,
    lugar VARCHAR(255) NULL,
    objetivo TEXT NULL,
    capacitador VARCHAR(255) NULL,
    tipo_charla ENUM('induccion_reinduccion','reunion','charla','capacitacion','otros_temas') NULL,
    material VARCHAR(255) NULL,
    tiempo_horas DECIMAL(4,1) NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf_asistencia VARCHAR(255) NULL,
    ruta_pdf_responsabilidades VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_asist_ind_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_asist_ind_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_asist_ind_cliente (id_cliente),
    INDEX idx_asist_ind_consultor (id_consultor),
    INDEX idx_asist_ind_estado (estado),
    INDEX idx_asist_ind_tipo (tipo_charla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total master: 18 columnas** (id + 2 FK + fecha + 5 texto/varchar + 1 ENUM + 1 DECIMAL + observaciones + 2 ruta_pdf + estado + 2 timestamps)

### Detalle: `tbl_asistencia_induccion_asistente`

```sql
CREATE TABLE IF NOT EXISTS tbl_asistencia_induccion_asistente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_asistencia INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    cedula VARCHAR(50) NOT NULL,
    cargo VARCHAR(255) NULL,
    firma VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_asist_det_master FOREIGN KEY (id_asistencia)
        REFERENCES tbl_asistencia_induccion(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_asist_det_master (id_asistencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total detalle: 7 columnas** (id + FK + nombre + cedula + cargo + firma + created_at)

**ON DELETE CASCADE** — si se borra la sesion master, se borran todos los asistentes.

---

## Flujo de firma — "Pasar el celular"

Este es el aspecto mas critico del UX. El consultor rota su celular entre los asistentes durante la sesion.

### Paso a paso

1. **Consultor crea la sesion** — llena datos master (tema, lugar, fecha, etc.)
2. **Consultor agrega asistentes** — nombre, cedula, cargo (puede ser antes o durante la sesion)
3. **Guardar borrador** — se guarda sin firmas
4. **Flujo de firmas (wizard)** — el consultor abre la pagina de firmas:
   - **Vista wizard: 1 firmante a la vez, pantalla completa**
   - Arriba: nombre del firmante actual + cargo + indicador "3 de 15"
   - Centro: Canvas grande para firma (mismo patron anti-accidental)
   - Abajo: 3 botones: [Guardar y Siguiente] [Limpiar] [Terminar listado]
   - Al guardar firma → POST AJAX → lienzo se limpia → carga datos del siguiente firmante
   - "Terminar listado" → sale del flujo de firmas (permite finalizar aunque falten firmas, o volver despues)
5. **Resumen post-firmas** — lista de asistentes con checkmarks (firmaron) y X (pendientes)
6. **Finalizar** — cuando todos tienen firma, se habilita el boton Finalizar

### Pagina de firma wizard (vista dedicada)

```
/inspecciones/asistencia-induccion/firmas/{id}
```

**UX Mobile-First — Flujo carousel por firmante:**

```
┌─────────────────────────────────┐
│  FIRMA DE ASISTENCIA  (3 / 15)  │
│                                 │
│  Nombre: Juan Carlos Perez      │
│  Cedula: 1012345678             │
│  Cargo:  Vigilante              │
│                                 │
│  ┌───────────────────────────┐  │
│  │                           │  │
│  │     [CANVAS FIRMA]        │  │
│  │                           │  │
│  │                           │  │
│  └───────────────────────────┘  │
│                                 │
│  [Limpiar]  [Guardar → Sgte]   │
│                                 │
│  [Terminar listado de firmas]   │
└─────────────────────────────────┘
```

**Comportamiento:**
- Al abrir, muestra el **primer asistente SIN firma** (salta los ya firmados)
- "Guardar y Siguiente" → AJAX guarda PNG → avanza al siguiente sin firma
- Si era el ultimo, muestra resumen con botones "Volver al formulario" o "Finalizar"
- "Terminar listado" → vuelve al formulario en cualquier momento
- "Limpiar" → borra el canvas para volver a intentar
- **SweetAlert2 preview** antes de guardar (mismo patron anti-accidental)
- Indicador "N de M" muestra progreso

**JS del wizard:**
```javascript
let currentIndex = 0;
const asistentes = <?= json_encode($asistentesSinFirma) ?>;

function mostrarFirmante(index) {
    const a = asistentes[index];
    $('#nombre-firmante').text(a.nombre);
    $('#cedula-firmante').text(a.cedula);
    $('#cargo-firmante').text(a.cargo);
    $('#contador').text((index + 1) + ' de ' + asistentes.length);
    limpiarCanvas();
}

function guardarYSiguiente() {
    // SweetAlert preview
    // AJAX POST firma base64
    // Avanzar al siguiente o mostrar resumen si era el ultimo
}
```

### Validacion pre-finalizacion

```php
// Verificar que TODOS los asistentes tienen firma
$asistentes = $detalleModel->where('id_asistencia', $id)->findAll();
$sinFirma = array_filter($asistentes, fn($a) => empty($a['firma']));
if (!empty($sinFirma)) {
    return redirect()->back()->with('error', 'Faltan ' . count($sinFirma) . ' firmas por recoger');
}
```

---

## PDFs generados (1 o 2)

### PDF 1: Listado de Asistencia (FT-SST-005) — SIEMPRE

**Header:** Logo + SG-SST + FT-SST-005 + V001 + Fecha

**Datos de la sesion:**
| Campo | Valor |
|-------|-------|
| Tema | `$inspeccion['tema']` |
| Lugar | `$inspeccion['lugar']` |
| Fecha | `$inspeccion['fecha_sesion']` |
| Objetivo | `$inspeccion['objetivo']` |
| Material | `$inspeccion['material']` |
| Tipo | `$inspeccion['tipo_charla']` (label) |
| Tiempo | `$inspeccion['tiempo_horas']` horas |
| Capacitador | `$inspeccion['capacitador']` |

**Tabla de asistentes:**
| NOMBRE | CEDULA | CARGO | FIRMA |
|--------|--------|-------|-------|
| foreach $asistentes → nombre | cedula | cargo | firma (imagen base64) |

### PDF 2: Acta de Responsabilidades SST (FT-SST-003) — SOLO si tipo_charla = 'induccion_reinduccion'

**Header:** Logo + SG-SST + FT-SST-003 + V001 + Fecha

**Contenido:**
1. "NOMBRE DE LA PROPIEDAD HORIZONTAL: {cliente}"
2. "FECHA: {fecha}"
3. "OBJETO DEL ACTA:" (texto estatico sobre formalizar responsabilidades SST)
4. **"1. RESPONSABILIDADES DEL ADMINISTRADOR"** (10 items, texto estatico)
5. **"2. RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST"** (10 items, texto estatico)
6. **"3. RESPONSABILIDADES DEL VIGIA DE SEGURIDAD Y SALUD EN EL TRABAJO"** (10 items, texto estatico)
7. **"4. RESPONSABILIDADES DE LOS TRABAJADORES Y CONTRATISTAS"** (10 items, texto estatico)
8. **"5. FIRMA DE ACEPTACION DE RESPONSABILIDADES"** (parrafo estatico)
9. **Tabla de firmas:** mismos asistentes con nombre, cedula, cargo, firma (base64)

**Fuente texto estatico:** `z_responsabilidadessst.txt`

---

## Estructura de archivos (8 nuevos)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_asistencia_induccion.php` | 2 tablas (master ~18 cols + detalle ~7 cols) |
| `app/Models/AsistenciaInduccionModel.php` | Modelo master |
| `app/Models/AsistenciaInduccionAsistenteModel.php` | Modelo detalle (asistentes) |
| `app/Controllers/Inspecciones/AsistenciaInduccionController.php` | Controlador CRUD + 2 PDFs + flujo firmas |
| `app/Views/inspecciones/asistencia-induccion/list.php` | Listado cards |
| `app/Views/inspecciones/asistencia-induccion/form.php` | Formulario master + tabla asistentes dinamica (JS buildRow) |
| `app/Views/inspecciones/asistencia-induccion/view.php` | Vista read-only |
| `app/Views/inspecciones/asistencia-induccion/pdf_asistencia.php` | Template DOMPDF FT-SST-005 |
| `app/Views/inspecciones/asistencia-induccion/pdf_responsabilidades.php` | Template DOMPDF FT-SST-003 (condicional) |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | ~12 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |

---

## Formulario — Secciones

| # | Seccion | Campos |
|---|---------|--------|
| 1 | Datos Generales | cliente Select2, fecha sesion |
| 2 | Informacion de la Sesion | tema, lugar, objetivo, capacitador, tipo_charla (select ENUM), material, tiempo horas |
| 3 | Asistentes | Tabla dinamica: boton "Agregar asistente" → fila con nombre, cedula, cargo + boton eliminar. JS `buildRow()` patron extintores |
| 4 | Observaciones | textarea libre |
| 5 | Acciones | Guardar borrador + Ir a firmas (redirige a pagina de firmas) + Finalizar (si todos firmaron) |

### JS del formulario

- **buildRow()** — agrega fila de asistente (nombre, cedula, cargo, boton eliminar)
- **Autoguardado localStorage** — key `asist_ind_draft_{id|new}`, solo datos master (no asistentes)
- **NO hay firma en el formulario** — las firmas se recogen en una pagina separada despues de guardar

---

## Rutas (12)

```php
// Asistencia / Induccion
$routes->get('asistencia-induccion', 'AsistenciaInduccionController::list');
$routes->get('asistencia-induccion/create', 'AsistenciaInduccionController::create');
$routes->get('asistencia-induccion/create/(:num)', 'AsistenciaInduccionController::create/$1');
$routes->post('asistencia-induccion/store', 'AsistenciaInduccionController::store');
$routes->get('asistencia-induccion/edit/(:num)', 'AsistenciaInduccionController::edit/$1');
$routes->post('asistencia-induccion/update/(:num)', 'AsistenciaInduccionController::update/$1');
$routes->get('asistencia-induccion/view/(:num)', 'AsistenciaInduccionController::view/$1');
$routes->get('asistencia-induccion/firmas/(:num)', 'AsistenciaInduccionController::firmas/$1');
$routes->post('asistencia-induccion/guardar-firma/(:num)', 'AsistenciaInduccionController::guardarFirma/$1');
$routes->get('asistencia-induccion/pdf/(:num)', 'AsistenciaInduccionController::generatePdf/$1');
$routes->post('asistencia-induccion/finalizar/(:num)', 'AsistenciaInduccionController::finalizar/$1');
$routes->get('asistencia-induccion/delete/(:num)', 'AsistenciaInduccionController::delete/$1');
```

**Rutas extra vs patron estandar:**
- `firmas/{id}` — pagina dedicada para recoger firmas (mobile-optimized)
- `guardar-firma/{idAsistente}` — POST AJAX para guardar firma individual (recibe PNG base64)

---

## Upload a Reportes

| PDF | id_report_type | id_detailreport | tag |
|-----|----------------|-----------------|-----|
| Listado Asistencia (FT-SST-005) | 6 | 22 | `asist_ind_id:{id}` |
| Responsabilidades SST (FT-SST-003) | 6 | 23 | `resp_sst_id:{id}` |

**Nota:** Se generan 2 reportes separados en `tbl_reporte` cuando es induccion.

---

## Dashboard

- **Card:** icono `fa-clipboard-list`, label "Asistencia"
- **Pendientes:** borradores despues de Reporte de Capacitacion
- **Conteo:** `totalAsistencia` (estado=completo)

---

## Firma Canvas — Reutilizacion de patron existente

La firma digital reutiliza el mismo patron de `contrato_firma.php` y Acta de Visita:

- Canvas HTML5 fullscreen en modal/pagina dedicada
- Proteccion anti-accidental: filtro multi-touch, validacion minima pixeles oscuros
- SweetAlert2 preview antes de confirmar
- Se guarda como PNG en `uploads/inspecciones/asistencia-induccion/firmas/`
- POST AJAX con imagen base64 → backend decodifica y guarda como archivo

### Guardar firma (endpoint AJAX)

```php
public function guardarFirma(int $idAsistente)
{
    $asistente = $this->detalleModel->find($idAsistente);
    // Validar que pertenece a sesion del consultor actual
    $firmaBase64 = $this->request->getPost('firma');
    // Decodificar base64 → guardar PNG
    $path = 'uploads/inspecciones/asistencia-induccion/firmas/' . uniqid() . '.png';
    file_put_contents(FCPATH . $path, base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $firmaBase64)));
    $this->detalleModel->update($idAsistente, ['firma' => $path]);
    return $this->response->setJSON(['ok' => true, 'path' => $path]);
}
```

---

## Relacion con Reporte de Capacitacion (doc 16)

Son modulos **independientes** que cubren aspectos distintos del mismo evento:

| Aspecto | Reporte Capacitacion (doc 16) | Asistencia/Induccion (doc 17) |
|---------|-------------------------------|-------------------------------|
| Enfoque | Metricas y fotos de la sesion | Listado nominal con firmas |
| Datos | Cuantos asistieron, score evaluacion, fotos | Quien asistio (nombre, cedula, firma) |
| Firmas | No | Si, cada asistente |
| PDF extra | No | Si (Responsabilidades SST si es induccion) |

**No se vinculan por FK.** El consultor puede crear uno sin el otro. A futuro se podria agregar vinculacion opcional.
