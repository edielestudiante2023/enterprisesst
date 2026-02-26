# Diseño de Base de Datos - Acta de Visita

**Base de datos:** `propiedad_horizontal`
**Prefijo tablas:** `tbl_`

---

## Diagrama de Relaciones

```
tbl_clientes (existente)
    │
    ├── 1:N ── tbl_acta_visita ──────────────────────── NUEVA
    │              │
    │              ├── 1:N ── tbl_acta_visita_integrantes ── NUEVA
    │              ├── 1:N ── tbl_acta_visita_temas ──────── NUEVA
    │              ├── 1:N ── tbl_pendientes (id_acta_visita) ── EXISTENTE (se agrega FK)
    │              └── 1:N ── tbl_acta_visita_fotos ──────── NUEVA
    │
tbl_consultor (existente)
    │
    └── 1:N ── tbl_acta_visita (id_consultor)
```

---

## Tabla Principal: `tbl_acta_visita`

```sql
CREATE TABLE tbl_acta_visita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,

    -- Datos de la visita
    fecha_visita DATE NOT NULL,
    hora_visita TIME NOT NULL,
    ubicacion_gps VARCHAR(50) NULL COMMENT 'Coordenadas GPS lat,lng',
    motivo VARCHAR(255) NOT NULL,
    modalidad VARCHAR(50) NULL DEFAULT 'Presencial' COMMENT 'Presencial/Virtual/Mixta',

    -- Contenido
    cartera TEXT NULL,
    observaciones TEXT NULL,

    -- Próxima reunión
    proxima_reunion_fecha DATE NULL,
    proxima_reunion_hora TIME NULL,

    -- Firmas (rutas a imágenes PNG)
    firma_administrador VARCHAR(255) NULL,
    firma_vigia VARCHAR(255) NULL,
    firma_consultor VARCHAR(255) NULL,

    -- Soportes documentales
    soporte_lavado_tanques VARCHAR(255) NULL,
    soporte_plagas VARCHAR(255) NULL,

    -- PDF generado
    ruta_pdf VARCHAR(255) NULL,

    -- Estado y tracking
    estado ENUM('borrador', 'pendiente_firma', 'completo') NOT NULL DEFAULT 'borrador',
    agenda_id VARCHAR(50) NULL COMMENT 'Vínculo opcional con agenda',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign keys
    CONSTRAINT fk_acta_visita_cliente
        FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_acta_visita_consultor
        FOREIGN KEY (id_consultor) REFERENCES tbl_consultor(id_consultor)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Índices
    INDEX idx_acta_cliente (id_cliente),
    INDEX idx_acta_consultor (id_consultor),
    INDEX idx_acta_fecha (fecha_visita),
    INDEX idx_acta_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tabla: `tbl_acta_visita_integrantes`

Reemplaza los campos fijos INTEGRANTE 1-4 / ROL 1-4 de AppSheet.

```sql
CREATE TABLE tbl_acta_visita_integrantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_acta_visita INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    rol VARCHAR(100) NOT NULL COMMENT 'ADMINISTRADOR, CONSULTOR CYCLOID, VIGÍA SST, etc.',
    orden TINYINT NOT NULL DEFAULT 1 COMMENT 'Orden de aparición en el acta',

    CONSTRAINT fk_integrante_acta
        FOREIGN KEY (id_acta_visita) REFERENCES tbl_acta_visita(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_integrante_acta (id_acta_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Roles predefinidos (Select en frontend):**
- ADMINISTRADOR
- CONSULTOR CYCLOID
- VIGÍA SST
- PRESIDENTE COPASST
- REPRESENTANTE LEGAL
- OTRO (campo libre)

---

## Tabla: `tbl_acta_visita_temas`

Reemplaza los campos fijos TEMA 1-5 de AppSheet.

```sql
CREATE TABLE tbl_acta_visita_temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_acta_visita INT NOT NULL,
    descripcion TEXT NOT NULL,
    orden TINYINT NOT NULL DEFAULT 1,

    CONSTRAINT fk_tema_acta
        FOREIGN KEY (id_acta_visita) REFERENCES tbl_acta_visita(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_tema_acta (id_acta_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Compromisos = `tbl_pendientes` (tabla existente, se modifica)

**HALLAZGO CLAVE del template AppSheet:** La sección "6. COMPROMISOS" del PDF usa
`<<Start: [Related tbl_pendientes]>>` — los compromisos **SON** pendientes directamente.
Columna 42 de AppSheet: `Related tbl_pendientes` (List, `REF_ROWS("tbl_pend...")`).

**NO se crea tabla separada.** Se agrego la columna FK `id_acta_visita` a `tbl_pendientes` (ya aplicado en produccion).

```sql
-- Migración ya aplicada
ALTER TABLE tbl_pendientes
    ADD COLUMN id_acta_visita INT NULL DEFAULT NULL
    COMMENT 'FK al acta de visita que generó este pendiente (nullable)',
    ADD INDEX idx_pendiente_acta (id_acta_visita),
    ADD CONSTRAINT fk_pendiente_acta_visita
        FOREIGN KEY (id_acta_visita) REFERENCES tbl_acta_visita(id)
        ON DELETE SET NULL ON UPDATE CASCADE;
```

**IMPORTANTE - Columna `id_acta` (varchar, NOT NULL):**

La tabla `tbl_pendientes` tiene una columna `id_acta` (varchar 255, NOT NULL, sin default) que es el identificador del acta que genero el pendiente (legacy, usada por el modulo existente de pendientes). Al insertar compromisos desde el Acta de Visita, se debe llenar con un valor como `'AV-{id_acta_visita}'` para evitar error SQL.

Estructura completa de `tbl_pendientes` en produccion (2026-02-22):

| Columna | Tipo | Null | Default |
|---------|------|------|---------|
| id_pendientes | int | NO | AUTO_INCREMENT |
| id_cliente | int | NO | - |
| id_acta | varchar(255) | NO | - |
| responsable | varchar(255) | NO | - |
| tarea_actividad | text | NO | - |
| fecha_asignacion | datetime | SI | CURRENT_TIMESTAMP |
| fecha_cierre | date | SI | NULL |
| estado | enum('ABIERTA','CERRADA','SIN RESPUESTA DEL CLIENTE','CERRADA POR FIN CONTRATO') | NO | ABIERTA |
| estado_avance | varchar(255) | SI | NULL |
| evidencia_para_cerrarla | text | SI | NULL |
| conteo_dias | int | SI | 0 |
| created_at | datetime | SI | CURRENT_TIMESTAMP |
| updated_at | datetime | SI | CURRENT_TIMESTAMP |
| id_acta_visita | int | SI | NULL |

**Como funciona:**

- Al llenar "Compromisos" en el acta, se insertan filas en `tbl_pendientes` con `id_acta_visita` = id del acta y `id_acta` = 'AV-{id}'
- El PDF lista compromisos con: `SELECT * FROM tbl_pendientes WHERE id_acta_visita = ?`
- Los pendientes creados desde el acta tambien aparecen en el modulo de pendientes existente
- Si se borra el acta, `id_acta_visita` se pone NULL (ON DELETE SET NULL), el pendiente sobrevive
- Campos usados: `tarea_actividad`, `fecha_cierre`, `responsable`, `id_cliente`, `id_acta`, `estado` = 'ABIERTA'

---

## Tabla: `tbl_acta_visita_fotos`

Reemplaza los campos fijos FOTO 1-3 SEG SOC y SOPORTES de AppSheet.

```sql
CREATE TABLE tbl_acta_visita_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_acta_visita INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'foto' COMMENT 'foto, soporte, seg_social',
    descripcion VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_foto_acta
        FOREIGN KEY (id_acta_visita) REFERENCES tbl_acta_visita(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_foto_acta (id_acta_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Script de Migración Completo

Este script se guardará en `app/SQL/migrate_acta_visita.php` y seguirá el patrón existente:

```php
<?php
// Ejecutar: DB_PROD_PASS=xxx php migrate_acta_visita.php production
// O sin argumento para LOCAL

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
} else {
    $host = 'localhost';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

// ... conexión y ejecución de los CREATE TABLE de arriba
```

---

## Tablas Existentes Consultadas (Solo lectura)

Estas tablas **NO se modifican**. El módulo solo las consulta para la sección "Temas Abiertos y Vencidos" del acta:

| Tabla | PK | Consulta | Sección del acta |
|-------|-----|----------|-------------------|
| `tbl_pendientes` | `id_pendientes` | `WHERE id_cliente = ? AND estado = 'ABIERTA'` | Pendientes abiertos |
| `tbl_vencimientos_mantenimientos` | `id_vencimientos_mmttos` | `WHERE id_cliente = ? AND estado_actividad = 'sin ejecutar' AND fecha_vencimiento <= +30 días` | Mantenimientos por vencer |
| `tbl_clientes` | `id_cliente` | `WHERE id_cliente = ?` + JOIN `tbl_contratos` para verificar activo | Nombre, logo, datos del cliente |
| `tbl_consultor` | `id_consultor` | `WHERE id_consultor = ?` | Nombre del consultor, firma (`firma_consultor`) |
| `tbl_contratos` | `id_contrato` | `WHERE id_cliente = ? AND estado = 'activo'` | Verificar cliente activo |
| `tbl_mantenimientos` | `id_mantenimiento` | JOIN con `tbl_vencimientos_mantenimientos` | Descripción del mantenimiento |

### Modelos CI4 existentes que se reutilizan

| Modelo | Método útil | Descripción |
|--------|-------------|-------------|
| `PendientesModel` | `where('id_cliente', $id)->where('estado', 'ABIERTA')->findAll()` | Pendientes abiertos del cliente |
| `VencimientosMantenimientoModel` | `getUpcomingVencimientos()` | Mantenimientos por vencer (próx. 30 días + vencidos) |
| `ClientModel` | `getClientWithActiveContract()` | Clientes con contrato activo |
| `ConsultantModel` | `find($id)` | Datos del consultor (nombre, firma) |

### Tabla que NO existe aún

| Tabla AppSheet | Existe en MySQL | Decisión |
|----------------|-----------------|----------|
| `TBL_HALLAZGOS_LOCATIVOS` | **NO** | Se creará cuando se implemente la inspección de "Locativas" (roadmap #3). Por ahora la sección de hallazgos del acta se omite. |

---

## Migración desde AppSheet

### Lo que reemplaza esta BD

En AppSheet, los datos se obtenían así:

```
MySQL → Google Apps Script (JDBC paginado) → Google Sheets → AppSheet (columnas calculadas)
```

**Columnas calculadas de AppSheet que ahora son queries directos:**

| Columna AppSheet | Fórmula AppSheet | Equivalente CI4 |
|------------------|------------------|-----------------|
| `pendientes_abiertos_cliente` | `IF(ISBLANK([pendientes...]), ...)` | `PendientesModel->where('id_cliente', $id)->where('estado', 'ABIERTA')` |
| `resumen_pendientes_pdf` | `IF(ISBLANK(SELECT(...)))` | Query directo en `ActaVisitaController` |
| `resumen_mantenimiento` | `IF(ISBLANK(SELECT(...)))` | `VencimientosMantenimientoModel->getUpcomingVencimientos()` |
| `resumen_hallazgos_abiertos` | `IF(ISBLANK(SELECT(...)))` | **Pendiente** (tabla no existe aún) |
| `NombreCliente_Puente` | `ANY(SELECT(tbl_clientes[nombre_cliente]...))` | `ClientModel->find($id)->nombre_cliente` |
| `NOMBRE_VISIBLE` | `CONCATENATE(ANY(...))` | JOIN directo en query |
| `COLUMNA TEMPORAL` | `[CLIENTE] & " — pu..."` | Concatenación en PHP/vista |

---

## Volumen Estimado

Basado en la data de AppSheet:
- ~10-20 actas por mes
- ~5 integrantes promedio por acta
- ~3 temas promedio por acta
- ~2 compromisos promedio por acta
- ~1-2 fotos promedio por acta

**Volumen anual:** ~200 actas, ~1000 integrantes, ~600 temas — Volumen pequeño, sin preocupaciones de rendimiento.
