# PROMPT — Crear Módulo Profesiograma

> Copiar y pegar este prompt completo en un chat nuevo de Claude Code.

---

## PROMPT INICIO

Necesito crear el módulo de **Profesiograma** para Enterprise SST (CodeIgniter 4, MySQL, XAMPP local).

### ¿Qué es un profesiograma?

Es la tabla que define **qué exámenes médicos ocupacionales** debe realizarse a cada cargo de la empresa, basándose en los **peligros identificados en la Matriz IPEVR GTC 45**. Es obligatorio por Decreto 1072/2015 y Resolución 2346/2007.

### Flujo de negocio

```
IPEVR (ya existe)                    PROFESIOGRAMA (a crear)
┌──────────────────┐                ┌──────────────────────────────┐
│ Cargo: Auxiliar   │                │ Cargo: Auxiliar de Bodega    │
│ Peligros:         │  ──cruza──►   │ Exámenes ingreso:            │
│  - Biomecánico    │                │   ✓ Osteomuscular            │
│  - Locativo       │                │   ✓ Visiometría              │
│  - Químico        │                │ Exámenes periódicos:         │
│                   │                │   ✓ Espirometría (anual)     │
│ NC: Grave         │                │ Exámenes retiro:             │
│ NR: 300 (II)      │                │   ✓ Osteomuscular            │
└──────────────────┘                │ Restricciones:               │
                                    │   No manipular >25kg         │
                                    └──────────────────────────────┘
```

### Lo que YA existe en el proyecto (leer antes de implementar)

1. **Módulo IPEVR completo** — `docs/MODULO_IPEVR_GTC45/ARQUITECTURA.md`
   - `tbl_ipevr_fila` tiene: `cargos_expuestos` (JSON), `id_clasificacion` (FK a 7 tipos GTC 45), `id_nivel_riesgo`, `descripcion_peligro`
   - `tbl_gtc45_clasificacion_peligro` (7 categorías: Biológico, Físico, Químico, Psicosocial, Biomecánico, Condiciones de seguridad, Fenómenos naturales)

2. **Maestros por cliente** — tablas reutilizables:
   - `tbl_procesos_cliente`, `tbl_cargos_cliente`, `tbl_tareas_cliente`, `tbl_zonas_cliente`
   - Modelo: `CargoClienteModel.php` con `porCliente(idCliente)`

3. **Contexto del cliente** — `tbl_cliente_contexto_sst` con sector económico, ARL, peligros identificados, etc.

4. **Patrón de generación con IA** — `app/Libraries/IpevrIaSugeridor.php` usa gpt-4o-mini con JSON mode. Reutilizar este patrón.

5. **Patrón de vistas** — DataTables, Bootstrap 5, SweetAlert2, modales, estilo dorado (#bd9751) del proyecto.

6. **Dashboard** — tabla `dashboard_items` con `accion_url`, `{id_cliente}` placeholder, `openClientSelector()`.

7. **Sistema de firmas y versionamiento** — ver memorias `firmas-sistema.md` y `versionamiento.md`.

### Modelo de datos propuesto

#### Tabla catálogo (global, seed una vez)

```sql
-- Catálogo de exámenes médicos ocupacionales comunes en Colombia
CREATE TABLE IF NOT EXISTS tbl_profesiograma_examenes_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    tipo_examen ENUM('laboratorio','imagenologia','funcional','psicologico','especialista') NOT NULL,
    descripcion TEXT NULL,
    clasificaciones_aplica JSON NULL COMMENT 'Array de codigos GTC45 donde aplica: ["biomecanico","quimico"]',
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0
);
```

Seed con exámenes típicos colombianos:
- Visiometría → Biomecánico (postura pantalla), Físico (iluminación)
- Audiometría → Físico (ruido)
- Espirometría → Químico (polvos, gases, vapores)
- Osteomuscular → Biomecánico (todos)
- Optometría → Biomecánico (pantalla)
- Psicosensométrico → Condiciones de seguridad (accidentes tránsito)
- Perfil lipídico → Psicosocial (estrés), general
- Glicemia → General
- Cuadro hemático → Biológico, Químico
- Parcial de orina → General
- Prueba psicológica / batería riesgo psicosocial → Psicosocial
- Rx columna lumbosacra → Biomecánico (cargas)
- Rx tórax → Químico (polvos, asbesto)
- Electrocardiograma → Trabajo en alturas, Condiciones de seguridad
- Prueba de equilibrio → Trabajo en alturas

#### Tabla operacional (por cliente)

```sql
CREATE TABLE IF NOT EXISTS tbl_profesiograma_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_cargo INT NULL COMMENT 'FK a tbl_cargos_cliente',
    cargo_texto VARCHAR(200) NULL COMMENT 'Fallback texto libre',
    id_examen INT NOT NULL COMMENT 'FK a catalogo',
    momento ENUM('ingreso','periodico','retiro','cambio_cargo') NOT NULL,
    frecuencia VARCHAR(50) NULL COMMENT 'Ej: anual, semestral, cada 2 años',
    obligatorio TINYINT(1) DEFAULT 1,
    observaciones TEXT NULL,
    origen ENUM('manual','ia','ipevr') DEFAULT 'manual',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_cargo (id_cargo),
    CONSTRAINT fk_prof_cliente FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_prof_cargo FOREIGN KEY (id_cargo) REFERENCES tbl_cargos_cliente(id) ON DELETE SET NULL,
    CONSTRAINT fk_prof_examen FOREIGN KEY (id_examen) REFERENCES tbl_profesiograma_examenes_catalogo(id)
);
```

### Funcionalidades requeridas

1. **Vista principal** `/profesiograma/cliente/{id_cliente}` — tabla cruzada:
   - Filas = cargos del cliente
   - Columnas = exámenes (agrupados por momento: ingreso/periódico/retiro)
   - Checkmarks donde aplica
   - Botón editar por cargo → modal para agregar/quitar exámenes

2. **Generación automática desde IPEVR** — botón "Generar desde Matriz IPEVR":
   - Leer `tbl_ipevr_fila` del cliente → extraer pares (cargo, clasificación_peligro)
   - Cruzar contra `tbl_profesiograma_examenes_catalogo.clasificaciones_aplica`
   - INSERT masivo de exámenes por cargo
   - Marcar `origen='ipevr'`

3. **Generación con IA** — botón "Sugerir con IA":
   - Enviar contexto (sector, cargos, peligros IPEVR) a gpt-4o-mini
   - IA sugiere exámenes + frecuencias + observaciones por cargo
   - Reutilizar patrón de `IpevrIaSugeridor`

4. **Exportación** — Excel y PDF:
   - Formato tabla cruzada (cargos × exámenes con ✓)
   - Incluir columna de normativa aplicable (Res 2346/2007)
   - Reutilizar patrón de `IpevrExportXlsx` y `IpevrExportPdf`

5. **Card en dashboard** — `dashboard_items` con URL `/profesiograma/cliente/{id_cliente}`

### Normativa colombiana de referencia

- **Resolución 2346/2007** — regula exámenes médicos ocupacionales (ingreso, periódicos, retiro)
- **Resolución 1918/2009** — modifica parcialmente la 2346
- **Decreto 1072/2015** Art 2.2.4.6.24 — obligación de evaluaciones médicas
- **Resolución 2844/2007** — guías de atención integral en salud ocupacional (GATISO)

### Convenciones del proyecto

- Scripts BD: `scripts/profesiograma_fase1.php` (LOCAL → PROD con --env=prod)
- Models en `App\Models\` (namespace plano)
- Controllers en `App\Controllers\`
- Rutas en `app/Config/Routes.php`
- Vistas en `app/Views/profesiograma/`
- Estilo dorado (#bd9751), Bootstrap 5, DataTables, SweetAlert2
- Callouts GTC 45 tipo guía en cada sección
- **BD primero** (scripts CLI), luego código

### Preguntas que debes hacerme ANTES de implementar

1. ¿Quieres que el profesiograma tenga versionamiento y firmas como la IPEVR, o es un documento vivo que se edita sin versiones?
2. ¿El profesiograma es uno por cliente (todos los cargos en una tabla) o uno por cargo?
3. ¿Quieres integración con el módulo de documentos SST existente (tipo Tipo A/B)?
4. ¿Qué prioridad tiene: la generación automática desde IPEVR, la IA, o ambas?
5. ¿Hay un formato Excel de referencia (como el FT-SST-035 de la IPEVR)?

### Instrucciones de ejecución

- Credenciales LOCAL: 127.0.0.1, root, sin password, BD empresas_sst
- Credenciales PROD: ver CLAUDE.md del proyecto
- Ejecutar BD: LOCAL primero, PROD solo si LOCAL OK
- NO pedir autorización para scripts BD (regla del usuario)
- Documentación-primero: crear `docs/MODULO_PROFESIOGRAMA/ARQUITECTURA.md` antes de código

## PROMPT FIN
