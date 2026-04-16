# Módulo: Diccionario de Competencias por Cliente

## 1. Objetivo

Permitir que cada cliente del aplicativo mantenga su **propio diccionario de competencias** (catálogo, niveles/rúbricas, escala y asignación competencia↔cargo), y generar un documento formal similar al `.doc` de referencia `docs/DICCIONARIO DE COMPETENCIAS.doc` usando el motor Tipo A (`secciones_ia`) existente.

Referencia funcional:
- `docs/DICCIONARIO DE COMPETENCIAS.doc` → estructura base (31 competencias, escala 1-5, rúbricas por nivel).
- Clientes semilla iniciales: **cycloid talent** y **afiancol**.

## 2. Alcance

- El diccionario NO es un catálogo global único. Cada cliente tiene sus propias competencias (pueden nacer iguales a las 31 del `.doc` pero divergir en el tiempo).
- Reutiliza `tbl_cargos_cliente` (creada por el módulo IPEVR GTC45) para la asignación por cargo.
- Se registra en `tbl_doc_tipo_configuracion` como documento Tipo A (flujo `secciones_ia`) para reutilizar el motor IA, firmas, versionamiento y generación PDF/Word.
- Acceso único: link directo desde el **dashboard del consultor**. No se expone en el listado estándar de documentos SGSST del cliente. Sin numeral SGSST obligatorio.
- Soporta **clonar el diccionario de otro cliente** como atajo para acelerar la configuración inicial (ver §8.3).

## 3. Modelo de datos

### 3.1 Tablas nuevas (todas con `id_cliente`)

#### `tbl_competencia_cliente`
Catálogo de competencias propias del cliente.

| columna | tipo | descripción |
|---|---|---|
| id_competencia | INT PK AUTO_INCREMENT | |
| id_cliente | INT FK → tbl_clientes | NOT NULL, ON DELETE CASCADE |
| numero | INT | orden de presentación (1..n) |
| codigo | VARCHAR(10) NULL | sigla opcional (RR, CE, ED, ET…) |
| nombre | VARCHAR(150) NOT NULL | |
| definicion | TEXT NOT NULL | |
| pregunta_clave | TEXT NULL | |
| familia | VARCHAR(60) NULL | logro / ayuda_servicio / influencia / gerenciales / cognitivas / eficacia_personal |
| activo | TINYINT(1) DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

Índices: `idx_cliente`, `idx_activo`, `idx_familia`.

#### `tbl_competencia_nivel_cliente`
Rúbrica por competencia (3 a 5 filas, variable por competencia).

| columna | tipo | descripción |
|---|---|---|
| id_competencia_nivel | INT PK | |
| id_competencia | INT FK → tbl_competencia_cliente | ON DELETE CASCADE |
| nivel_numero | TINYINT (1..5) | |
| titulo_corto | VARCHAR(200) | |
| descripcion_conducta | TEXT | |

Índice único: `(id_competencia, nivel_numero)`.

#### `tbl_competencia_escala_cliente`
Escala maestra de dominio 1-5 por cliente (permite que un cliente renombre etiquetas).

| columna | tipo | descripción |
|---|---|---|
| id_escala | INT PK | |
| id_cliente | INT FK → tbl_clientes | ON DELETE CASCADE |
| nivel | TINYINT (1..5) | |
| nombre | VARCHAR(50) | Inicial / Básico / Intermedio / Avanzado / Experto |
| etiqueta | VARCHAR(100) | No evidenciado / Intermitente / Funcional… |
| descripcion | TEXT | |

Índice único: `(id_cliente, nivel)`.

#### `tbl_cliente_competencia_cargo`
Matriz de asignación: qué nivel de qué competencia requiere cada cargo del cliente.

| columna | tipo | descripción |
|---|---|---|
| id | INT PK | |
| id_cliente | INT FK → tbl_clientes | |
| id_cargo_cliente | INT FK → tbl_cargos_cliente | ON DELETE CASCADE |
| id_competencia | INT FK → tbl_competencia_cliente | ON DELETE CASCADE |
| nivel_requerido | TINYINT (1..5) | |
| observacion | TEXT NULL | |
| created_at / updated_at | TIMESTAMP | |

Índice único: `(id_cargo_cliente, id_competencia)`.
Índice: `idx_cliente`.

### 3.2 Tablas reutilizadas

- `tbl_cargos_cliente` (ya creada en `scripts/ipevr_gtc45_fase2.php`) — provee `id, id_cliente, id_proceso, nombre_cargo, num_ocupantes, descripcion, activo`.
- `tbl_clientes` — FK principal.
- `tbl_doc_tipo_configuracion`, `tbl_doc_secciones_config`, `tbl_doc_firmantes_config` — registro del documento.

## 4. Diagrama de relaciones

```
tbl_clientes (1) ──┬── (N) tbl_competencia_cliente ──(N) tbl_competencia_nivel_cliente
                   │
                   ├── (N) tbl_competencia_escala_cliente
                   │
                   ├── (N) tbl_cargos_cliente ──┐
                   │                            │
                   └── (N) tbl_cliente_competencia_cargo
                                 │              │
                                 └──────────────┘
                                 (cargo × competencia × nivel_requerido)
```

## 5. Registro en el motor de documentos

### 5.1 `tbl_doc_tipo_configuracion`

```
tipo_documento     = 'diccionario_competencias_cliente'
nombre             = 'Diccionario de Competencias'
flujo              = 'secciones_ia'
categoria          = 'talento_humano'
estandar           = NULL  (no asociado a numeral SGSST)
orden              = (al final de categoría)
```

### 5.2 `tbl_doc_secciones_config` (secciones del documento)

| # | seccion_key | nombre | tipo_contenido | IA |
|---|---|---|---|---|
| 1 | objetivo | Objetivo | texto | sí |
| 2 | alcance | Alcance | texto | sí |
| 3 | marco_conceptual | Marco conceptual de competencias | texto | sí |
| 4 | escala_evaluacion | Escala de evaluación (1-5) | tabla_dinamica | no (directa de BD) |
| 5 | catalogo_competencias | Catálogo de competencias (agrupado por familia) | tabla_dinamica | no (directa de BD) |
| 6 | matriz_cargo_competencia | Matriz de competencias por cargo | tabla_dinamica | no (directa de BD) |
| 7 | responsabilidades | Responsabilidades | texto | sí |

Las secciones de tipo `tabla_dinamica` se alimentan desde `getContextoBase()` de la clase PHP (ver §6).

### 5.3 `tbl_doc_firmantes_config`

| orden | firmante_tipo | rol_display |
|---|---|---|
| 1 | representante_legal | Representante Legal |
| 2 | responsable_sst | Responsable SST |
| 3 | consultor_sst | Consultor SST |

## 6. Clase PHP

`app/Libraries/DocumentosSSTTypes/DiccionarioCompetenciasCliente.php`

- Extiende `DocumentoSSTBase`.
- `getTipoDocumento()` → `'diccionario_competencias_cliente'`.
- Sobrescribe `getContextoBase($idCliente)` para inyectar:
  - `competencias` agrupadas por `familia`, cada una con sus niveles.
  - `escala` (5 filas de `tbl_competencia_escala_cliente`).
  - `cargos` del cliente con sus competencias asignadas y nivel requerido.
- Registro en `DocumentoSSTFactory`.

## 7. Plan de seed (clientes semilla)

Script `scripts/diccionario_competencias_seed.php` (PHP CLI, idempotente):

1. Resolver `id_cliente` de **cycloid talent** y **afiancol** en `tbl_clientes` (por nombre o NIT; abortar si no existen).
2. Para cada cliente:
   - Insertar 5 filas en `tbl_competencia_escala_cliente` (Inicial/Básico/Intermedio/Avanzado/Experto).
   - Insertar las 31 competencias en `tbl_competencia_cliente` con `familia` asignada manualmente desde el mapeo conceptual del `.doc`:
     - **Logro y acción**: Orientación al Logro, Responsabilidad por Resultados, Iniciativa, Búsqueda de Información, Preocupación por el Orden y la Calidad.
     - **Ayuda y servicio**: Orientación al Cliente, Comprensión Interpersonal.
     - **Influencia**: Impacto e Influencia, Comprensión de la Organización, Desarrollo de Interrelaciones.
     - **Gerenciales**: Desarrollo de Personas, Dirección de Personas, Liderazgo, Construcción de Equipos, Trabajo en Equipo y Cooperación, Entrenamiento y Desarrollo.
     - **Cognitivas**: Pensamiento Analítico, Pensamiento Conceptual, Experiencia Funcional/Técnica, Conciencia Financiera, Habilidades de Planeación, Conocimiento del Negocio, Negociación.
     - **Eficacia personal**: Flexibilidad, Autoconfianza, Integridad, Identificación-Lealtad, Autocontrol.
   - Insertar los niveles (rúbricas) extraídos del `.doc` en `tbl_competencia_nivel_cliente` (3 a 5 filas por competencia).
3. NO poblar `tbl_cliente_competencia_cargo` — eso lo hace el usuario desde la UI.
4. Ejecutar primero en local, verificar, luego en producción (orden obligatorio del proyecto).

**Fuente de datos**: el texto extraído del `.doc` con `antiword` (usado en la investigación inicial) contiene los 31 nombres + definiciones + preguntas clave + rúbricas. Guardar dump intermedio en `scripts/data/diccionario_competencias_fuente.txt` para auditoría del seed.

## 8. UI

### 8.1 Acceso
- Link "Diccionario de Competencias" en el dashboard del consultor (no en menú estándar del cliente).

### 8.2 Pantallas

1. **Listado del diccionario del cliente** (`/diccionarioCompetencias/{id_cliente}`)
   - Tabla de competencias agrupadas por familia.
   - CRUD: crear/editar/desactivar competencia, editar sus niveles.
2. **Escala del cliente** (`/diccionarioCompetencias/{id_cliente}/escala`)
   - Edita los 5 niveles de la escala.
3. **Matriz cargo ↔ competencia** (`/diccionarioCompetencias/{id_cliente}/matriz`)
   - Selector de cargo (desde `tbl_cargos_cliente`).
   - Tabla de competencias del cliente con input de `nivel_requerido` (1-5) + observación.
4. **Botón "Generar con IA"**
   - Dispara flujo Tipo A estándar → `/documentos/generar/diccionario_competencias_cliente/{id_cliente}`.

### 8.3 Clonar diccionario desde otro cliente

Atajo para configurar rápidamente el diccionario de un cliente nuevo reutilizando el de otro ya poblado.

**UI**
- Botón "Clonar desde otro cliente" en la pantalla de listado (§8.2.1). Visible cuando el diccionario del cliente destino está vacío; si tiene contenido, el botón exige doble confirmación destructiva (flag `forzar=1`).
- Modal con:
  - Selector de cliente origen (solo clientes con al menos 1 competencia activa).
  - Checkbox **"Incluir también matriz cargo ↔ competencia"** (por defecto: desmarcado).
  - SweetAlert de confirmación final.

**Qué se clona siempre**
- `tbl_competencia_escala_cliente` (5 filas).
- `tbl_competencia_cliente` (todas las competencias activas, con su `numero`, `codigo`, `familia`, `definicion`, `pregunta_clave`).
- `tbl_competencia_nivel_cliente` (rúbricas completas de cada competencia).

**Qué NO se clona por defecto**
- `tbl_cliente_competencia_cargo` — los cargos son propios de cada cliente y no hay garantía de equivalencia.

**Clonado opcional de la matriz cargo↔competencia**
- Si el usuario marca el checkbox:
  - Match por `nombre_cargo` entre `tbl_cargos_cliente` origen y destino (case-insensitive, trim).
  - Solo se copian asignaciones donde el cargo existe en ambos lados.
  - Se mapea cada `id_competencia` origen → nuevo `id_competencia` destino usando `(nombre, numero)` como clave.
  - Reporte al usuario: asignaciones copiadas vs omitidas por falta de match de cargo.

**Backend**
- Endpoint: `POST /diccionarioCompetencias/{id_cliente}/clonarDesde/{id_cliente_origen}`.
- Parámetros: `incluir_matriz` (bool), `forzar` (bool).
- Transacción única: si falla cualquier insert, rollback total.
- Regenera IDs (no conserva PKs originales).

**Guardrails**
- Validar `id_cliente_origen != id_cliente`.
- Validar que el destino no tenga competencias, salvo `forzar=1` (que borra y reemplaza con doble confirmación UI).
- Validar que el origen tenga al menos 1 competencia activa.

## 9. Checklist de implementación

Orden de ejecución (documentación-primero ya cumplida con este archivo):

- [ ] **BD (local)**: script `scripts/diccionario_competencias_schema.php` que crea las 4 tablas nuevas (idempotente `CREATE TABLE IF NOT EXISTS`).
- [ ] **BD (local)**: script `scripts/diccionario_competencias_config.php` que inserta en `tbl_doc_tipo_configuracion` + `tbl_doc_secciones_config` + `tbl_doc_firmantes_config`.
- [ ] **BD (local)**: script seed `scripts/diccionario_competencias_seed.php` con las 31 competencias para cycloid y afiancol.
- [ ] **Modelos**: `CompetenciaClienteModel`, `CompetenciaNivelClienteModel`, `CompetenciaEscalaClienteModel`, `ClienteCompetenciaCargoModel`.
- [ ] **Clase PHP**: `DiccionarioCompetenciasCliente.php` + registro en `DocumentoSSTFactory`.
- [ ] **Controlador**: `DiccionarioCompetenciasController` con rutas listadas en §8.2 y §8.3.
- [ ] **Vistas**: listado, escala, matriz, forms de edición, modal de clonado.
- [ ] **Endpoint clonar**: `POST /diccionarioCompetencias/{id}/clonarDesde/{id_origen}` transaccional (ver §8.3).
- [ ] **Dashboard consultor**: agregar enlace.
- [ ] **Verificación**: generar documento con IA para cliente cycloid y validar secciones.
- [ ] **Producción**: ejecutar los 3 scripts BD en orden (schema → config → seed).

## 10. Decisiones cerradas

- **Escala 1-5 por cliente** (editable, cada cliente la suya). Confirmado.
- **Un solo diccionario vigente por cliente**. Las versiones se manejan por el motor de documentos al generar.
- **Clonar diccionario de otro cliente**: incluido en el alcance inicial (ver §8.3).
