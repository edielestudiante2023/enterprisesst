# 22 - Preparacion Guion del Simulacro

## Resumen

Documento de planeacion del simulacro de evacuacion por sismo en la propiedad horizontal. El consultor define el cronograma detallado (9 timestamps), asigna roles, identifica recursos, designa al brigadista lider y documenta la logistica del ejercicio.

**Patron:** Inspeccion PLANA (ver `12_PATRON_INSPECCION_PLANA.md`)
**PDF:** FT-SST-223 — Preparacion y Guion del Simulacro

---

## Mapeo AppSheet → DB

### Columnas a MIGRAR (28 de 32)

| # | AppSheet | DB Column | Tipo |
|---|----------|-----------|------|
| 1 | ID | `id` | INT AUTO_INCREMENT PK |
| — | (nuevo) | `id_cliente` | INT FK → tbl_clientes |
| — | (nuevo) | `id_consultor` | INT FK → tbl_consultor |
| 5 | Fecha | `fecha_simulacro` | DATE |
| 2 | Ubicacion | `ubicacion` | VARCHAR(100) — coords GPS "lat, lng" |
| 3 | Direccion | `direccion` | VARCHAR(255) |
| 7 | Evento_Simulado | `evento_simulado` | ENUM('sismo') |
| 8 | Alcance_Simulacro | `alcance_simulacro` | ENUM('total','parcial') |
| 9 | Tipo_Evacuacion | `tipo_evacuacion` | ENUM('horizontal','vertical','mixta') |
| 10 | Personal_No_Evacua | `personal_no_evacua` | TEXT |
| 11 | Tipo_Alarma | `tipo_alarma` | TEXT (EnumList comma-separated) |
| 12 | Distintivos_Brigadistas | `distintivos_brigadistas` | TEXT (EnumList comma-separated) |
| 13 | Puntos_Encuentro | `puntos_encuentro` | TEXT |
| 14 | Recurso_Humano | `recurso_humano` | TEXT |
| 15 | Equipos_Emergencia | `equipos_emergencia` | TEXT (EnumList comma-separated) |
| 16 | nombre_brigadista_lider | `nombre_brigadista_lider` | VARCHAR(255) |
| 17 | email_brigadista_liser | `email_brigadista_lider` | VARCHAR(255) — fix typo AppSheet |
| 18 | whatsapp_brigadista_lider | `whatsapp_brigadista_lider` | VARCHAR(20) |
| 19 | Entrega_formato_evaluacion | `entrega_formato_evaluacion` | ENUM('si','no') |
| 20 | imagen_1 | `imagen_1` | VARCHAR(255) |
| 21 | imagen_2 | `imagen_2` | VARCHAR(255) |
| 22 | Hora_Inicio | `hora_inicio` | TIME |
| 23 | Alistamiento_Recursos | `alistamiento_recursos` | TIME |
| 24 | Asumir_roles | `asumir_roles` | TIME |
| 25 | Suena_alarma | `suena_alarma` | TIME |
| 26 | Distribucion_roles | `distribucion_roles` | TIME |
| 27 | Llegada_punto_encuentro | `llegada_punto_encuentro` | TIME |
| 28 | Agrupacion_por_afinidad | `agrupacion_por_afinidad` | TIME |
| 29 | Conteo_Personal | `conteo_personal` | TIME |
| 30 | Agradecimiento_y_cierre | `agradecimiento_cierre` | TIME |
| 32 | Observaciones | `observaciones` | TEXT |
| — | (nuevo) | `ruta_pdf` | VARCHAR(255) |
| — | (nuevo) | `estado` | ENUM('borrador','completo') |
| — | (nuevo) | `created_at` | DATETIME |
| — | (nuevo) | `updated_at` | DATETIME |

**Total: ~35 columnas**

### Columnas NO almacenadas (4+ de 32)

| Categoria | Columnas | Razon |
|-----------|----------|-------|
| **AppSheet interno** (1) | _RowNumber | Campo interno de AppSheet |
| **Timestamp reemplazado** (1) | FECHA DEL REGISTRO | Reemplazado por `created_at` |
| **FK normalizada** (2) | Cliente, Consultor | Datos via FK id_cliente, id_consultor |
| **Calculado** (1) | Tiempo_total | Se calcula en PHP: agradecimiento_cierre - hora_inicio |

---

## Campos ENUM

### evento_simulado — 1 valor
| Valor DB | Label visible |
|----------|---------------|
| `sismo` | Sismo |

### alcance_simulacro — 2 valores
| Valor DB | Label visible |
|----------|---------------|
| `total` | Total |
| `parcial` | Parcial |

### tipo_evacuacion — 3 valores
| Valor DB | Label visible |
|----------|---------------|
| `horizontal` | Horizontal |
| `vertical` | Vertical |
| `mixta` | Mixta (combinacion de horizontal y vertical) |

### entrega_formato_evaluacion — 2 valores
| Valor DB | Label visible |
|----------|---------------|
| `si` | SI |
| `no` | NO |

---

## Campos EnumList (TEXT comma-separated)

Almacenados como TEXT con valores separados por coma. En el formulario se presentan como checkboxes multiples.

### tipo_alarma — Opciones
```php
public const OPCIONES_ALARMA = [
    'sirena' => 'Sirena',
    'megafono' => 'Megafono',
    'radio_interno' => 'Radio interno',
];
```

### distintivos_brigadistas — Opciones
```php
public const OPCIONES_DISTINTIVOS = [
    'chaleco' => 'Chaleco',
    'brazalete' => 'Brazalete',
    'ninguno' => 'Ninguno',
];
```

### equipos_emergencia — Opciones
```php
public const OPCIONES_EQUIPOS = [
    'paletas_pare_siga' => 'Paletas de PARE y SIGA',
    'chaleco_reflectivo' => 'Chaleco reflectivo',
    'megafono_pito' => 'Megafono o, en su defecto, pito',
    'camilla' => 'Camilla',
    'botiquin' => 'Botiquin',
    'radio_onda_corta' => 'Radio de onda corta',
    'paleta_punto_encuentro' => 'Paleta Punto de Encuentro',
];
```

---

## Cronograma — 9 campos TIME

El cronograma del simulacro se registra como 9 timestamps (hora sin fecha, DATE es `fecha_simulacro`).

| # | Campo DB | Label | Descripcion |
|---|----------|-------|-------------|
| 1 | `hora_inicio` | Hora de Inicio | Inicio del ejercicio |
| 2 | `alistamiento_recursos` | Alistamiento de Recursos | Preparacion de equipos |
| 3 | `asumir_roles` | Asumir Roles | Brigadistas asumen posiciones |
| 4 | `suena_alarma` | Suena Alarma | Activacion de la alarma |
| 5 | `distribucion_roles` | Distribucion de Roles | Asignacion en terreno |
| 6 | `llegada_punto_encuentro` | Llegada al Punto de Encuentro | Evacuados llegan al punto |
| 7 | `agrupacion_por_afinidad` | Agrupacion por Afinidad | Organizacion por areas/torres |
| 8 | `conteo_personal` | Conteo de Personal | Verificacion de personas |
| 9 | `agradecimiento_cierre` | Agradecimiento y Cierre | Fin del ejercicio |

**Tiempo Total:** Calculado en PHP como `agradecimiento_cierre - hora_inicio`. NO se almacena en la DB.

```php
// En controlador/vista:
$inicio = new DateTime($inspeccion['hora_inicio']);
$cierre = new DateTime($inspeccion['agradecimiento_cierre']);
$diff = $inicio->diff($cierre);
$tiempoTotal = $diff->format('%H:%I:%S');
```

---

## Fotos (2)

| Campo DB | Descripcion |
|----------|-------------|
| `imagen_1` | Foto del simulacro (evidencia principal) |
| `imagen_2` | Foto adicional del simulacro |

Dir fotos: `uploads/inspecciones/preparacion-simulacro/`
Dir PDFs: `uploads/inspecciones/preparacion-simulacro/pdfs/`

---

## Brigadista Lider

Datos del brigadista que lidera el simulacro:

| Campo DB | Descripcion |
|----------|-------------|
| `nombre_brigadista_lider` | Nombre completo |
| `email_brigadista_lider` | Correo electronico |
| `whatsapp_brigadista_lider` | Numero WhatsApp |

---

## Tabla SQL: `tbl_preparacion_simulacro`

```sql
CREATE TABLE IF NOT EXISTS tbl_preparacion_simulacro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_simulacro DATE NOT NULL,

    -- Ubicacion
    ubicacion VARCHAR(100) NULL,
    direccion VARCHAR(255) NULL,

    -- Configuracion del simulacro
    evento_simulado ENUM('sismo') NULL,
    alcance_simulacro ENUM('total','parcial') NULL,
    tipo_evacuacion ENUM('horizontal','vertical','mixta') NULL,
    personal_no_evacua TEXT NULL,

    -- Alarma y distintivos (EnumList comma-separated)
    tipo_alarma TEXT NULL,
    distintivos_brigadistas TEXT NULL,

    -- Logistica
    puntos_encuentro TEXT NULL,
    recurso_humano TEXT NULL,
    equipos_emergencia TEXT NULL,

    -- Brigadista lider
    nombre_brigadista_lider VARCHAR(255) NULL,
    email_brigadista_lider VARCHAR(255) NULL,
    whatsapp_brigadista_lider VARCHAR(20) NULL,

    -- Evaluacion
    entrega_formato_evaluacion ENUM('si','no') NULL,

    -- Fotos
    imagen_1 VARCHAR(255) NULL,
    imagen_2 VARCHAR(255) NULL,

    -- Cronograma (9 TIME)
    hora_inicio TIME NULL,
    alistamiento_recursos TIME NULL,
    asumir_roles TIME NULL,
    suena_alarma TIME NULL,
    distribucion_roles TIME NULL,
    llegada_punto_encuentro TIME NULL,
    agrupacion_por_afinidad TIME NULL,
    conteo_personal TIME NULL,
    agradecimiento_cierre TIME NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_prep_sim_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_prep_sim_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_prep_sim_cliente (id_cliente),
    INDEX idx_prep_sim_consultor (id_consultor),
    INDEX idx_prep_sim_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Total: 35 columnas** (id + 2 FK + fecha + 4 ENUM + 5 TEXT + 5 VARCHAR + 2 foto + 9 TIME + observaciones + ruta_pdf + estado + 2 timestamps)

---

## Estructura del PDF (FT-SST-223)

1. Header corporativo (logo + SG-SST + FT-SST-223 + V001 + Fecha)
2. Titulo: "Preparacion y Guion del Simulacro"
3. Datos generales (cliente, fecha, direccion, ubicacion)
4. Configuracion (evento, alcance, tipo evacuacion)
5. Logistica (personal, alarma, distintivos, puntos encuentro, recursos, equipos)
6. Brigadista lider (nombre, email, whatsapp)
7. Cronograma (tabla con 9 filas de hora + tiempo total calculado)
8. Evaluacion (entrega formato)
9. Fotos evidencia (base64)
10. Texto estatico: "Importancia de la Preparacion y del Guion del Simulacro de Sismo" (5 secciones)
11. Observaciones

---

## Formulario — Secciones (10)

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha simulacro | 0 |
| 2 | Ubicacion | ubicacion (GPS), direccion | 0 |
| 3 | Configuracion del Simulacro | evento_simulado, alcance, tipo_evacuacion, personal_no_evacua | 0 |
| 4 | Alarma y Distintivos | tipo_alarma checkboxes, distintivos_brigadistas checkboxes | 0 |
| 5 | Logistica | puntos_encuentro, recurso_humano, equipos_emergencia checkboxes | 0 |
| 6 | Brigadista Lider | nombre, email, whatsapp | 0 |
| 7 | Registro Fotografico | — | 2 (imagen_1, imagen_2) |
| 8 | Cronograma | 9 inputs time | 0 |
| 9 | Evaluacion y Observaciones | entrega_formato_evaluacion, observaciones textarea | 0 |
| 10 | Acciones | Guardar borrador + Finalizar | 0 |

---

## Rutas (10)

```php
// Preparacion Simulacro
$routes->get('preparacion-simulacro', 'PreparacionSimulacroController::list');
$routes->get('preparacion-simulacro/create', 'PreparacionSimulacroController::create');
$routes->get('preparacion-simulacro/create/(:num)', 'PreparacionSimulacroController::create/$1');
$routes->post('preparacion-simulacro/store', 'PreparacionSimulacroController::store');
$routes->get('preparacion-simulacro/edit/(:num)', 'PreparacionSimulacroController::edit/$1');
$routes->post('preparacion-simulacro/update/(:num)', 'PreparacionSimulacroController::update/$1');
$routes->get('preparacion-simulacro/view/(:num)', 'PreparacionSimulacroController::view/$1');
$routes->get('preparacion-simulacro/pdf/(:num)', 'PreparacionSimulacroController::generatePdf/$1');
$routes->post('preparacion-simulacro/finalizar/(:num)', 'PreparacionSimulacroController::finalizar/$1');
$routes->get('preparacion-simulacro/delete/(:num)', 'PreparacionSimulacroController::delete/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 28 |
| `tag` | `prep_sim_id:{id}` |

---

## Dashboard

- **Card:** icono `fa-clipboard-check`, label "Preparacion Simulacro"
- **Pendientes:** borradores
- **Conteo:** `totalPreparacionSimulacro` (estado=completo)

---

## Archivos a crear (7)

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/migrate_preparacion_simulacro.php` | Migracion ~35 columnas |
| `app/Models/PreparacionSimulacroModel.php` | Modelo CRUD |
| `app/Controllers/Inspecciones/PreparacionSimulacroController.php` | Controlador con 2 fotos + 9 TIME + EnumLists + PDF |
| `app/Views/inspecciones/preparacion-simulacro/list.php` | Listado cards |
| `app/Views/inspecciones/preparacion-simulacro/form.php` | Formulario (~10 secciones) |
| `app/Views/inspecciones/preparacion-simulacro/view.php` | Vista read-only |
| `app/Views/inspecciones/preparacion-simulacro/pdf.php` | Template DOMPDF |

## Archivos a modificar (3)

| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | 10 rutas |
| `app/Views/inspecciones/dashboard.php` | Card + pendientes |
| `app/Controllers/Inspecciones/InspeccionesController.php` | Import + conteo + pendientes |

---

## Texto estatico del PDF

### Importancia de la Preparacion y del Guion del Simulacro de Sismo

**1. Claridad en la organizacion del ejercicio**
El guion constituye una herramienta de planeacion estrategica fundamental que permite definir paso a paso el desarrollo del simulacro. A traves de este documento se establecen con precision:
- Cronograma detallado: tiempos especificos para cada fase
- Asignacion de roles: responsabilidades claras para brigadistas, residentes y administradores
- Inventario de recursos: alarmas, distintivos, puntos de encuentro y equipos de comunicacion necesarios

**2. Preparacion del recurso humano (brigadistas)**
Los brigadistas representan el eje central del simulacro. La preparacion anticipada les permite:
- Dominar sus funciones especificas
- Fortalecer habilidades de liderazgo y comunicacion
- Ensayar escenarios realistas

**3. Entrenamiento en condiciones controladas y seguras**
El simulacro reproduce fielmente una situacion de emergencia sin exponer a los participantes a riesgos reales.

**4. Cumplimiento normativo y aplicacion de buenas practicas**
Decreto 1072 de 2015 y Resolucion 0312 de 2019 establecen que las organizaciones deben contar con planes de emergencia probados y evaluados.

**5. Fortalecimiento de la cultura de prevencion**
Un simulacro bien planificado transmite un mensaje contundente a la comunidad sobre la importancia de la prevencion.
