# Script de Revision del Control Documental - EnterpriseSST

**Fecha de creacion:** 2026-01-30
**Objetivo:** Guia detallada para revisar y unificar el sistema de control documental del aplicativo SST

---

## CONTEXTO DEL PROBLEMA IDENTIFICADO

Durante la implementacion del modulo de Presupuesto SST, se identifico que el sistema de control documental tiene **inconsistencias arquitectonicas**:

1. El controlador `PzpresupuestoSstController.php` espera una tabla `tbl_plantillas_documentos_sst` que no existia
2. Ya existe una tabla `tbl_doc_plantillas` que parece cumplir una funcion similar
3. Hay otra tabla `tbl_doc_plantilla_carpeta` para mapear plantillas a carpetas
4. No hay claridad sobre cual es la fuente de verdad para codigos y versiones de documentos

---

## TABLAS EXISTENTES RELACIONADAS CON CONTROL DOCUMENTAL

### 1. tbl_doc_plantillas (EXISTENTE)

```sql
-- Estructura actual
id_plantilla INT(11) PK
id_tipo INT(11)
nombre VARCHAR(255)
descripcion TEXT
codigo_sugerido VARCHAR(20)   -- <-- Codigo del documento
estructura_json LONGTEXT
prompts_json LONGTEXT
variables_contexto TEXT
activo TINYINT(1)
orden INT(11)
aplica_7 TINYINT(1)
aplica_21 TINYINT(1)
aplica_60 TINYINT(1)
created_at DATETIME
updated_at DATETIME
```

**Observacion:** Tiene `codigo_sugerido` pero NO tiene columna `version`

**Datos de ejemplo:**
- PRG-CAP: Programa de Capacitacion en Promocion y Prevencion
- POL-SST: Politica de Seguridad y Salud en el Trabajo
- PRG-PVE: Programa de Vigilancia Epidemiologica

### 2. tbl_doc_plantilla_carpeta (EXISTENTE)

```sql
-- Estructura (verificar)
codigo_plantilla VARCHAR(20)
codigo_carpeta VARCHAR(20)
```

**Funcion:** Mapea plantillas a carpetas del estandar 0312/2019

### 3. tbl_plantillas_documentos_sst (RECIEN CREADA - 2026-01-30)

```sql
-- Creada para el modulo de presupuesto
id INT PK
id_estandar INT NOT NULL      -- FK a tbl_estandares_minimos_0312
codigo VARCHAR(20) NOT NULL   -- Codigo del documento
version VARCHAR(10) DEFAULT '001'
nombre_documento VARCHAR(255)
descripcion TEXT
tipo_documento VARCHAR(50) DEFAULT 'formato'
activo TINYINT(1) DEFAULT 1
created_at DATETIME
updated_at DATETIME
UNIQUE KEY idx_estandar_codigo (id_estandar, codigo)
```

**Datos actuales:**
- id_estandar=3, codigo=FT-SST-004, version=001: Asignacion de Recursos para el SG-SST

### 4. tbl_estandares_minimos_0312 (REFERENCIA)

```sql
-- Contiene los 60 estandares de la Resolucion 0312/2019
id_estandar INT PK
codigo VARCHAR(10)           -- Ej: 1.1.1, 1.1.2, 1.1.3
nombre TEXT
ciclo ENUM('PLANEAR','HACER','VERIFICAR','ACTUAR')
-- ... otros campos
```

### 5. tbl_documentos_sst (DOCUMENTOS GENERADOS)

```sql
-- Documentos generados por cliente
id_documento INT PK
id_cliente INT
id_carpeta INT
tipo_documento VARCHAR(100)
titulo VARCHAR(255)
contenido_json LONGTEXT
version VARCHAR(10)
estado ENUM('borrador','en_revision','pendiente_firma','firmado','aprobado')
-- ... otros campos de firma y control
```

---

## TAREAS DE REVISION REQUERIDAS

### TAREA 1: Inventariar Uso de Codigos de Documento

**Buscar en todo el codigo** donde se usan codigos de documento hardcodeados:

```bash
# Buscar patrones de codigos hardcodeados
grep -rn "FT-SST-" app/Controllers/
grep -rn "PRG-" app/Controllers/
grep -rn "POL-" app/Controllers/
grep -rn "ASG-" app/Controllers/
grep -rn "PLA-" app/Controllers/
grep -rn "'codigo'" app/Controllers/ | grep -i documento
```

**Archivos clave a revisar:**
1. `app/Controllers/DocumentosSSTController.php` - Constantes CODIGOS_DOCUMENTO
2. `app/Controllers/Pz*.php` - Controladores de documentos especificos
3. `app/Controllers/Hz*.php` - Otros controladores de documentos

**Pregunta a responder:**
- Cada controlador obtiene el codigo desde BD o lo tiene hardcodeado?
- Hay consistencia en el patron usado?

### TAREA 2: Analizar Arquitectura de Plantillas

**Objetivo:** Determinar si se necesitan DOS tablas de plantillas o si se pueden unificar

| Tabla | Proposito Actual | Tiene Version? | Tiene id_estandar? |
|-------|------------------|----------------|---------------------|
| tbl_doc_plantillas | Plantillas con IA, estructura JSON | NO | NO (usa id_tipo) |
| tbl_plantillas_documentos_sst | Control documental simple | SI | SI |

**Preguntas a responder:**
1. Pueden coexistir ambas tablas con propositos diferentes?
2. Deberia agregarse `version` a `tbl_doc_plantillas`?
3. Deberia `tbl_plantillas_documentos_sst` tener relacion con `tbl_doc_plantillas`?

### TAREA 3: Verificar Mapeo de Plantillas a Estandares

**Objetivo:** Asegurar que cada documento SST esta mapeado correctamente al estandar 0312

```sql
-- Verificar mapeo actual
SELECT
    p.codigo_sugerido,
    p.nombre,
    pc.codigo_carpeta
FROM tbl_doc_plantillas p
LEFT JOIN tbl_doc_plantilla_carpeta pc ON p.codigo_sugerido = pc.codigo_plantilla
ORDER BY pc.codigo_carpeta;
```

**Verificar tambien:**
```sql
-- Documentos en nueva tabla
SELECT
    pds.codigo,
    pds.nombre_documento,
    pds.version,
    e.codigo as estandar_codigo,
    e.nombre as estandar_nombre
FROM tbl_plantillas_documentos_sst pds
JOIN tbl_estandares_minimos_0312 e ON pds.id_estandar = e.id_estandar;
```

### TAREA 4: Revisar Controladores Existentes

**Patron A - DocumentosSSTController (Documentos complejos con IA):**

```php
// Revisar estas constantes en app/Controllers/DocumentosSSTController.php
const TIPOS_DOCUMENTO = [...];
const CODIGOS_DOCUMENTO = [...];

// Preguntas:
// 1. Estos codigos estan sincronizados con tbl_doc_plantillas.codigo_sugerido?
// 2. Deberian consultarse desde BD en lugar de constantes?
```

**Patron B - Controladores Pz* (Documentos simples):**

Listar todos los controladores Pz* y verificar como obtienen el codigo:

| Controlador | Documento | Obtiene codigo de BD? | Tabla usada |
|-------------|-----------|----------------------|-------------|
| PzpresupuestoSstController | FT-SST-004 | SI (ahora) | tbl_plantillas_documentos_sst |
| PzasignacionresponsableSstController | ? | ? | ? |
| PzresponsabilidadesRepLegalController | ? | ? | ? |
| ... | ... | ... | ... |

### TAREA 5: Proponer Arquitectura Unificada

Basado en el analisis, proponer:

**OPCION A: Mantener dos tablas separadas**
- `tbl_doc_plantillas` para documentos complejos con IA (estructura JSON, prompts)
- `tbl_plantillas_documentos_sst` para control documental simple (codigo, version)

**OPCION B: Unificar en una sola tabla**
- Agregar columnas `version`, `id_estandar` a `tbl_doc_plantillas`
- Migrar datos de `tbl_plantillas_documentos_sst`
- Actualizar todos los controladores

**OPCION C: Tabla intermedia de control documental**
- Nueva tabla `tbl_control_documental` que relacione:
  - id_plantilla (FK a tbl_doc_plantillas, opcional)
  - id_estandar (FK a tbl_estandares_minimos_0312)
  - codigo, version, estado

---

## CODIGO A REVISAR EN DETALLE

### 1. PzpresupuestoSstController.php (ACTUALIZADO)

```php
// Patron implementado - usar como referencia
protected const ID_ESTANDAR_PRESUPUESTO = 3;

protected function getDatosDocumento(): array
{
    $plantilla = $this->db->table('tbl_plantillas_documentos_sst')
        ->where('id_estandar', self::ID_ESTANDAR_PRESUPUESTO)
        ->get()->getRowArray();

    if ($plantilla) {
        return [
            'codigo' => $plantilla['codigo'] ?? 'FT-SST-004',
            'nombre' => $plantilla['nombre_documento'] ?? '...',
            'version' => $plantilla['version'] ?? '001'
        ];
    }
    // Fallback a valores por defecto
    return [...];
}
```

### 2. DocumentosSSTController.php

```php
// Revisar constantes existentes
const CODIGOS_DOCUMENTO = [
    'programa_capacitacion' => 'PRG-CAP',
    // ... otros
];

// Pregunta: Deberia usar el mismo patron que PzpresupuestoSstController?
```

### 3. ClienteDocumentosSstController.php

```php
// Revisar mapearPlantillaATipoDocumento()
// Este mapeo debe estar sincronizado con las tablas de plantillas
```

---

## QUERIES DE DIAGNOSTICO

Ejecutar estas queries para entender el estado actual:

```sql
-- 1. Listar todas las plantillas con sus codigos
SELECT id_plantilla, nombre, codigo_sugerido, activo
FROM tbl_doc_plantillas
ORDER BY codigo_sugerido;

-- 2. Listar mapeos plantilla-carpeta
SELECT * FROM tbl_doc_plantilla_carpeta
ORDER BY codigo_carpeta;

-- 3. Listar documentos en nueva tabla
SELECT * FROM tbl_plantillas_documentos_sst;

-- 4. Verificar estandares sin documento asociado
SELECT e.id_estandar, e.codigo, e.nombre
FROM tbl_estandares_minimos_0312 e
LEFT JOIN tbl_plantillas_documentos_sst p ON e.id_estandar = p.id_estandar
WHERE p.id IS NULL
AND e.codigo LIKE '1.%'  -- Solo PLANEAR
ORDER BY e.codigo;

-- 5. Contar documentos generados por tipo
SELECT tipo_documento, COUNT(*) as cantidad
FROM tbl_documentos_sst
GROUP BY tipo_documento
ORDER BY cantidad DESC;
```

---

## ARCHIVOS CLAVE A LEER

1. **Documentacion principal:**
   - `PROMPT_NUEVO_DOCUMENTO_SST.md` - Arquitectura documentada

2. **Controladores de documentos:**
   - `app/Controllers/DocumentosSSTController.php`
   - `app/Controllers/PzpresupuestoSstController.php` (referencia actualizada)
   - `app/Controllers/PzasignacionresponsableSstController.php`
   - `app/Controllers/DocumentacionController.php`

3. **Modelos:**
   - `app/Models/DocPlantillaModel.php` (si existe)
   - Buscar modelos relacionados con plantillas

4. **Vistas de control documental:**
   - `app/Views/documentacion/carpeta.php`
   - `app/Views/documentos_sst/generar_con_ia.php`

5. **Scripts SQL de migracion:**
   - `app/SQL/crear_tablas_documentos_sst.sql`
   - `app/SQL/insert_plantillas_completas.sql`
   - `app/SQL/agregar_columna_version_plantillas.php`

---

## ENTREGABLES ESPERADOS

Al finalizar la revision, documentar:

1. **Diagrama de relaciones** entre tablas de control documental
2. **Lista de inconsistencias** encontradas
3. **Propuesta de unificacion** con pasos de migracion
4. **Scripts SQL** necesarios para normalizar
5. **Cambios de codigo** requeridos en controladores

---

## COMANDOS PARA INICIAR LA REVISION

```bash
# 1. Ver estructura de tablas de plantillas
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');
echo \"=== tbl_doc_plantillas ===\n\";
\$stmt = \$pdo->query('DESCRIBE tbl_doc_plantillas');
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    echo \$row['Field'] . ' (' . \$row['Type'] . \")\n\";
}
echo \"\n=== tbl_plantillas_documentos_sst ===\n\";
\$stmt = \$pdo->query('DESCRIBE tbl_plantillas_documentos_sst');
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    echo \$row['Field'] . ' (' . \$row['Type'] . \")\n\";
}
"

# 2. Buscar codigos hardcodeados en controladores
grep -rn "const.*CODIGO" app/Controllers/
grep -rn "'FT-SST" app/Controllers/
grep -rn "'PRG-" app/Controllers/

# 3. Listar todos los controladores de documentos
ls -la app/Controllers/Pz*.php
ls -la app/Controllers/Hz*.php
```

---

## NOTAS IMPORTANTES

1. **NO modificar produccion** hasta tener plan completo aprobado
2. **Mantener compatibilidad** hacia atras durante la transicion
3. **Probar exhaustivamente** en local antes de migrar
4. **Documentar cada cambio** en PROMPT_NUEVO_DOCUMENTO_SST.md

---

## HISTORIAL DE CAMBIOS

| Fecha | Cambio | Autor |
|-------|--------|-------|
| 2026-01-30 | Creacion de tbl_plantillas_documentos_sst | Claude/Usuario |
| 2026-01-30 | PzpresupuestoSstController usa BD para codigo/version | Claude/Usuario |
| 2026-01-30 | Creacion de este script de revision | Claude |
| 2026-01-30 | Diagnostico completo ejecutado - Hallazgos documentados | Claude |

---

## RESULTADOS DEL DIAGNOSTICO (2026-01-30)

### CONEXION A PRODUCCION EXITOSA

Se ejecutaron queries de diagnostico directamente contra la base de datos de produccion.

---

### HALLAZGO 1: Nombre Incorrecto de Tabla de Estandares

**PROBLEMA:** El codigo referencia `tbl_estandares_minimos_0312` pero la tabla real se llama `tbl_estandares_minimos`

**Tablas de estandares encontradas:**
- `estandares`
- `estandares_accesos`
- `tbl_cliente_estandares`
- `tbl_doc_estandar_documentos` (vacia - no se esta usando)
- `tbl_estandares_minimos` <-- **ESTA ES LA CORRECTA**

**ACCION REQUERIDA:** Actualizar el codigo que referencia `tbl_estandares_minimos_0312` para usar `tbl_estandares_minimos`

---

### HALLAZGO 2: Estructura Real de Tablas de Plantillas

#### tbl_doc_plantillas (45 plantillas activas)
```
id_plantilla              int            NOT NULL
id_tipo                   int            NOT NULL  <-- FK a tbl_doc_tipos
nombre                    varchar(255)   NOT NULL
descripcion               text
codigo_sugerido           varchar(20)              <-- Codigo del documento
estructura_json           longtext                 <-- Para generacion con IA
prompts_json              longtext                 <-- Prompts de IA
variables_contexto        text
activo                    tinyint(1)     NOT NULL
orden                     int            NOT NULL
aplica_7                  tinyint(1)     NOT NULL
aplica_21                 tinyint(1)     NOT NULL
aplica_60                 tinyint(1)     NOT NULL
created_at                datetime       NOT NULL
updated_at                datetime       NOT NULL
```
**NOTA:** NO tiene columna `version`

#### tbl_plantillas_documentos_sst (1 documento)
```
id                        int            NOT NULL
id_estandar               int            NOT NULL  <-- FK a tbl_estandares_minimos
codigo                    varchar(20)    NOT NULL
version                   varchar(10)              <-- SI tiene version
nombre_documento          varchar(255)   NOT NULL
descripcion               text
tipo_documento            varchar(50)
activo                    tinyint(1)
created_at                datetime
updated_at                datetime
```

#### tbl_doc_tipos (14 tipos de documento)
```json
{"id_tipo":1,"codigo":"POL","nombre":"Política"}
{"id_tipo":2,"codigo":"OBJ","nombre":"Objetivos"}
{"id_tipo":3,"codigo":"PRG","nombre":"Programa"}
{"id_tipo":4,"codigo":"PLA","nombre":"Plan"}
{"id_tipo":5,"codigo":"PRO","nombre":"Procedimiento"}
{"id_tipo":6,"codigo":"PRT","nombre":"Protocolo"}
{"id_tipo":7,"codigo":"MAN","nombre":"Manual"}
{"id_tipo":8,"codigo":"INF","nombre":"Informe"}
{"id_tipo":9,"codigo":"FOR","nombre":"Formato"}
{"id_tipo":10,"codigo":"MTZ","nombre":"Matriz"}
{"id_tipo":11,"codigo":"ACT","nombre":"Acta"}
{"id_tipo":12,"codigo":"GUA","nombre":"Guía"}
{"id_tipo":13,"codigo":"INS","nombre":"Instructivo"}
{"id_tipo":14,"codigo":"REG","nombre":"Reglamento"}
```

---

### HALLAZGO 3: Mapeo Plantilla-Carpeta (22 registros)

| Estandar | Plantillas Mapeadas |
|----------|---------------------|
| 1.1.1 | ASG-RES |
| 1.1.2 | RES-TRA, RES-SST, RES-REP |
| 1.2.1 | PLA-CAP, PRG-CAP, CAP-ANUAL, FOR-ASI |
| 2.1.1 | POL-SST |
| 2.2.1 | OBJ-SST |
| 2.4.1 | PLA-TRA |
| 2.7.1 | MTZ-LEG |
| 3.1.1 | PRO-EMO |
| 3.1.2 | PRG-MED |
| 3.2.1 | PRO-REP |
| 3.2.2 | PRO-INV |
| 4.1.1 | PRO-IPR |
| 4.1.2 | MTZ-PEL |
| 4.2.6 | MTZ-EPP, PRO-EPP |
| 5.1.1 | PLA-EME |
| 5.1.2 | PRG-BRI |

**FALTA MAPEAR:**
- **1.1.3** - Presupuesto SST (FT-SST-004) - No existe en tbl_doc_plantilla_carpeta

---

### HALLAZGO 4: Patrones de Codigo en Controladores

Se identificaron **3 patrones diferentes** para manejar codigos de documentos:

#### Patron A: Constantes Hardcodeadas en DocumentosSSTController
```php
public const CODIGOS_DOCUMENTO = [
    'programa_capacitacion' => ['tipo' => 'PRG', 'tema' => 'CAP'],
    'politica_sst' => ['tipo' => 'POL', 'tema' => 'SST'],
    // ...
];
```
**Archivos afectados:** DocumentosSSTController.php, GeneradorDocumentoController.php

#### Patron B: Constantes Separadas en Controladores Pz*
```php
protected const CODIGO_TIPO = 'ASG';
protected const CODIGO_TEMA = 'RES';
```
**Archivos afectados:**
- PzasignacionresponsableSstController.php (ASG-RES)
- PzresponsabilidadesRepLegalController.php (RES-REP)
- PzresponsabilidadesResponsableSstController.php (RES-SST)
- PzresponsabilidadesTrabajadoresController.php (RES-TRA)
- PzresponsabilidadesVigiaSstController.php (RES-VIG)

#### Patron C: Consulta a BD (RECOMENDADO)
```php
protected const ID_ESTANDAR_PRESUPUESTO = 3;

protected function getDatosDocumento(): array
{
    $plantilla = $this->db->table('tbl_plantillas_documentos_sst')
        ->where('id_estandar', self::ID_ESTANDAR_PRESUPUESTO)
        ->get()->getRowArray();
    // ...
}
```
**Archivos afectados:** PzpresupuestoSstController.php (unico que lo usa actualmente)

---

### HALLAZGO 5: Controladores Existentes

#### Controladores Pz* (42 archivos)
Documentos del ciclo PLANEAR:
- PzactacocolabController.php
- PzactacopasstController.php
- PzasignacionresponsableSstController.php
- PzasignacionresponsabilidadesController.php
- PzcomunicacionController.php
- PzconfidencialidadcocolabController.php
- PzdocumentacionController.php
- PzexamedController.php
- PzexoneracioncocolabController.php
- PzformatodeasistenciaController.php
- ... y 32 mas

#### Controladores Hz* (10 archivos)
Documentos del ciclo HACER:
- HzaccioncorrectivaController.php
- HzauditoriaController.php
- HzentregadotacionController.php
- HzfuncionesyrespController.php
- HzindentpeligroController.php
- HzpausaactivaController.php
- HzreqlegalesController.php
- HzresponsablepesvController.php
- HzrespsaludController.php
- HzrevaltagerenciaController.php

---

### HALLAZGO 6: Documentos Generados en Produccion

| Tipo Documento | Cantidad | Codigo Ejemplo |
|----------------|----------|----------------|
| asignacion_responsable_sgsst | 1 | ASG-RES-001 |
| programa_capacitacion | 1 | PRG-CAP-001 |
| responsabilidades_rep_legal_sgsst | 1 | RES-REP-001 |
| responsabilidades_responsable_sgsst | 1 | RES-SST-001 |
| responsabilidades_trabajadores_sgsst | 1 | RES-TRA-001 |

**Total: 5 documentos generados**

---

### HALLAZGO 7: Stored Procedure Existente

Existe `sp_generar_codigo_documento` que genera codigos unicos.
Se usa en DocumentosSSTController.php:

```php
$this->db->query("CALL sp_generar_codigo_documento(?, ?, ?, @codigo)", [
    $idCliente,
    $codigos['tipo'],
    $codigos['tema']
]);
$result = $this->db->query("SELECT @codigo as codigo")->getRow();
```

---

## INCONSISTENCIAS IDENTIFICADAS

| # | Inconsistencia | Severidad | Impacto |
|---|----------------|-----------|---------|
| 1 | Nombre de tabla incorrecto `tbl_estandares_minimos_0312` vs `tbl_estandares_minimos` | ALTA | Queries fallan |
| 2 | Falta columna `version` en `tbl_doc_plantillas` | MEDIA | No hay control de version de plantillas |
| 3 | `tbl_doc_estandar_documentos` esta vacia y no se usa | BAJA | Tabla huerfana |
| 4 | Falta mapeo de 1.1.3 (Presupuesto) en `tbl_doc_plantilla_carpeta` | MEDIA | Documento no aparece en carpeta |
| 5 | 3 patrones diferentes para codigos de documentos | ALTA | Inconsistencia arquitectonica |
| 6 | Solo PzpresupuestoSstController usa BD para codigo/version | ALTA | Dificil mantener sincronizacion |
| 7 | Duplicacion conceptual entre `tbl_doc_plantillas` y `tbl_plantillas_documentos_sst` | ALTA | Confusion sobre fuente de verdad |

---

## ARQUITECTURA OBJETIVO (Segun PROMPT_NUEVO_DOCUMENTO_SST.md)

### DECISION: Deprecar Controladores Pz* y Hz*

Los controladores `Pz*.php` y `Hz*.php` son **LEGACY** y seran migrados a `DocumentosSSTController`.

**Nueva arquitectura unificada:**

| Flujo | Descripcion | Ejemplo |
|-------|-------------|---------|
| `secciones_ia` | Editor de secciones con IA | Programa Capacitacion |
| `auto_contexto` | Generacion automatica desde contexto | Asignacion Responsable |
| `formulario` | Formulario interactivo | Presupuesto SST |

**Estado de migracion:**

| Controladores | Cantidad | Estado |
|---------------|----------|--------|
| Pz*.php | 42 | LEGACY - Migrar gradualmente |
| Hz*.php | 10 | LEGACY - Migrar gradualmente |
| DocumentosSSTController | 1 | **ACTIVO** - Usar para nuevos documentos |

### Fuente de Verdad Actual

```
tbl_doc_tipos (14 tipos)
    ↓
tbl_doc_plantillas (45 plantillas)
    - codigo_sugerido (ej: PRG-CAP, POL-SST)
    - estructura_json (para Patron A)
    - prompts_json (para Patron A)
    ↓
tbl_doc_plantilla_carpeta (22 mapeos)
    - codigo_plantilla → codigo_carpeta (estandar 0312)
```

### Problema: tbl_plantillas_documentos_sst es Redundante

La tabla `tbl_plantillas_documentos_sst` (creada para Presupuesto) duplica funcionalidad de `tbl_doc_plantillas`.

**Diferencias clave:**

| Campo | tbl_doc_plantillas | tbl_plantillas_documentos_sst |
|-------|-------------------|------------------------------|
| version | NO | SI |
| id_estandar | NO | SI |
| estructura_json | SI | NO |
| prompts_json | SI | NO |

---

## PROPUESTA DE SOLUCION RECOMENDADA

### OPCION RECOMENDADA: B - Extender tbl_doc_plantillas

**Razon:** Ya tiene 45 plantillas, relacion con tipos, y mapeo a carpetas. Solo falta agregarle control documental.

### Paso 1: Agregar columnas a tbl_doc_plantillas

```sql
ALTER TABLE tbl_doc_plantillas
ADD COLUMN version VARCHAR(10) DEFAULT '001' AFTER codigo_sugerido,
ADD COLUMN id_estandar INT NULL AFTER id_tipo,
ADD INDEX idx_id_estandar (id_estandar);
```

### Paso 2: Migrar datos de tbl_plantillas_documentos_sst

```sql
-- Mapear el presupuesto SST a una plantilla existente o crear nueva
INSERT INTO tbl_doc_plantillas (
    id_tipo, id_estandar, nombre, codigo_sugerido, version, activo, orden,
    aplica_7, aplica_21, aplica_60, created_at, updated_at
)
SELECT
    9, -- id_tipo = 9 (Formato)
    id_estandar,
    nombre_documento,
    codigo,
    version,
    activo,
    100,
    1, 1, 1,
    created_at,
    updated_at
FROM tbl_plantillas_documentos_sst
WHERE codigo NOT IN (SELECT codigo_sugerido FROM tbl_doc_plantillas);
```

### Paso 3: Agregar mapeo faltante

```sql
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion, created_at)
VALUES ('FT-SST-004', '1.1.3', 'Presupuesto SST', NOW());
```

### Paso 4: Crear trait reutilizable para controladores

```php
// app/Traits/DocumentoSstTrait.php
trait DocumentoSstTrait
{
    protected function getDatosPlantilla(string $codigoPlantilla): array
    {
        $plantilla = $this->db->table('tbl_doc_plantillas')
            ->where('codigo_sugerido', $codigoPlantilla)
            ->where('activo', 1)
            ->get()->getRowArray();

        if ($plantilla) {
            return [
                'codigo' => $plantilla['codigo_sugerido'],
                'nombre' => $plantilla['nombre'],
                'version' => $plantilla['version'] ?? '001',
                'id_estandar' => $plantilla['id_estandar']
            ];
        }

        // Fallback a valores por defecto
        return [
            'codigo' => $codigoPlantilla,
            'nombre' => 'Documento SST',
            'version' => '001',
            'id_estandar' => null
        ];
    }
}
```

### Paso 5: Actualizar controladores gradualmente

Prioridad de migracion:
1. **ALTA:** Controladores que generan documentos nuevos
2. **MEDIA:** Controladores existentes con constantes hardcodeadas
3. **BAJA:** DocumentosSSTController (mas complejo, requiere refactor mayor)

---

## PROXIMOS PASOS

1. [ ] Aprobar la propuesta de solucion
2. [ ] Ejecutar scripts SQL en ambiente de desarrollo
3. [ ] Crear trait DocumentoSstTrait
4. [ ] Actualizar PzpresupuestoSstController para usar tbl_doc_plantillas
5. [ ] Probar exhaustivamente
6. [ ] Ejecutar migracion en produccion
7. [ ] Deprecar tbl_plantillas_documentos_sst

---

## SCRIPTS SQL PARA EJECUTAR

### Script 1: Agregar columnas a tbl_doc_plantillas
```sql
-- Verificar si ya existen las columnas
SET @column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'empresas_sst'
    AND TABLE_NAME = 'tbl_doc_plantillas'
    AND COLUMN_NAME = 'version'
);

-- Agregar columna version si no existe
ALTER TABLE tbl_doc_plantillas
ADD COLUMN IF NOT EXISTS version VARCHAR(10) DEFAULT '001' AFTER codigo_sugerido;

-- Agregar columna id_estandar si no existe
ALTER TABLE tbl_doc_plantillas
ADD COLUMN IF NOT EXISTS id_estandar INT NULL AFTER id_tipo;

-- Agregar indice
CREATE INDEX IF NOT EXISTS idx_id_estandar ON tbl_doc_plantillas(id_estandar);
```

### Script 2: Agregar mapeo faltante 1.1.3
```sql
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion, created_at)
SELECT 'FT-SST-004', '1.1.3', 'Presupuesto SST', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantilla_carpeta
    WHERE codigo_carpeta = '1.1.3' AND codigo_plantilla = 'FT-SST-004'
);
```

### Script 3: Agregar plantilla de presupuesto a tbl_doc_plantillas
```sql
INSERT INTO tbl_doc_plantillas (
    id_tipo, id_estandar, nombre, descripcion, codigo_sugerido, version,
    activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at
)
SELECT
    9, -- FOR (Formato)
    3, -- Estandar 1.1.3
    'Asignacion de Recursos para el SG-SST',
    'Presupuesto anual de recursos financieros, tecnicos y humanos para el SG-SST',
    'FT-SST-004',
    '001',
    1, 100, 1, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'FT-SST-004'
);
```

---

## LECCIONES APRENDIDAS - SESION 2026-01-31

### Contexto
Durante la integracion del presupuesto SST con el sistema de firmas unificado, se cometieron varios errores que deben evitarse en el futuro.

---

### LECCION 1: No Crear Flujos de Firma Paralelos

**Error cometido:**
Inicialmente cree un sistema de firmas personalizado para el presupuesto:
- Modal "Enviar a Firmas" con formulario POST
- Ruta `/documentos-sst/presupuesto/enviar-firmas`
- Email con boton "Revisar y Aprobar"
- Token personalizado en `tbl_presupuesto_sst`

**Problema:**
El usuario rechazo esto porque ya existe `FirmaElectronicaController` que maneja firmas para TODOS los documentos. El boton debia ser "Ir a Firmar" no "Revisar y Aprobar".

**Leccion:**
> Antes de implementar CUALQUIER funcionalidad nueva, verificar si ya existe una solucion centralizada en el sistema. Estudiar documentos similares (ej: `asignacion_responsable.php`) y replicar su arquitectura.

**Patron correcto:**
```php
// Vista del documento
<a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>">
    Solicitar Firmas
</a>

// NO crear modales personalizados para firmas
```

---

### LECCION 2: Los Documentos Deben Existir en tbl_documentos_sst

**Error cometido:**
El presupuesto usaba su propia tabla `tbl_presupuesto_sst` sin registro en `tbl_documentos_sst`, lo que causaba:
- No aparecia en el Control Documental (2.5.1)
- No podia usar `FirmaElectronicaController`
- Sistema de firmas duplicado y aislado

**Leccion:**
> Todo documento SST que requiera firmas DEBE tener un registro en `tbl_documentos_sst`. Sin esto, no puede integrarse con el sistema de firmas unificado.

**Solucion implementada:**
```php
// Sincronizar con tbl_documentos_sst al crear el presupuesto
$this->sincronizarConDocumentosSST($idCliente, $anio, $presupuesto, $esNuevo);

// Y tambien al cambiar estado
$this->sincronizarConDocumentosSST($idCliente, $anio, $presupuesto, false);
```

---

### LECCION 3: Mapeo de Estados Entre Tablas

**Error potencial:**
Mapear estados incorrectamente entre `tbl_presupuesto_sst` y `tbl_documentos_sst`.

**Mapeo correcto:**
| Estado Presupuesto | Estado Documento | Permite Firmas |
|-------------------|------------------|----------------|
| borrador | aprobado | SI |
| en_revision | en_revision | SI |
| pendiente_firma | pendiente_firma | SI |
| aprobado | firmado | NO (ya firmado) |
| cerrado | firmado | NO |

**Leccion:**
> "borrador" en presupuesto significa "listo para trabajar", pero para el sistema de firmas necesita estar "aprobado" (listo para firmar). El mapeo de estados debe considerar el contexto de cada sistema.

---

### LECCION 4: Estudiar Arquitectura Existente Antes de Implementar

**Error cometido:**
Implementar rapidamente una solucion sin estudiar como funcionan documentos similares.

**Lo que debi hacer:**
1. Leer `FirmaElectronicaController.php` completo
2. Estudiar `asignacion_responsable.php` (vista de referencia)
3. Entender el flujo: `firma/solicitar/{id_documento}` → email → `firma/firmar/{token}` → `firma/estado/{id_documento}`

**Leccion:**
> El usuario dijo: "mira esta arquitectura y replicala, estudiala a fondo no seas facilista". Esto es critico. Copiar el patron probado es mejor que inventar uno nuevo.

---

### LECCION 5: Sincronizacion en Multiples Puntos

**Error cometido:**
Solo sincronizar con `tbl_documentos_sst` en el metodo `preview()`, no al crear o cambiar estado.

**Problema:**
Si alguien crea un presupuesto pero nunca va al preview, el documento no aparece en Control Documental.

**Solucion:**
```php
// Sincronizar en TODOS los puntos relevantes:
1. getOrCreatePresupuesto() - Al crear
2. cambiarEstado() - Al cambiar estado
3. preview() - Al visualizar (actualiza contenido)
```

**Leccion:**
> La sincronizacion debe ocurrir en TODOS los puntos donde el estado o existencia del documento puede cambiar, no solo en un punto de visualizacion.

---

### LECCION 6: Evitar Duplicacion de Logica

**Error cometido:**
Cree dos metodos similares:
- `getOrCreateDocumentoSST()` - para preview
- `sincronizarConDocumentosSST()` - para crear/cambiar estado

**Solucion:**
Refactorizar para que uno llame al otro:
```php
protected function getOrCreateDocumentoSST(...) {
    // Primero asegurar que existe
    $this->sincronizarConDocumentosSST(...);

    // Luego obtener y actualizar contenido
    $documento = $this->db->table('tbl_documentos_sst')...
}
```

**Leccion:**
> DRY (Don't Repeat Yourself). Si dos metodos hacen cosas similares, uno debe llamar al otro o ambos deben usar un metodo comun.

---

### LECCION 7: Vista Previa del Email ≠ Arquitectura Final

**Error cometido:**
El usuario mostro un screenshot del email con boton "Revisar y Aprobar" y dijo que estaba MAL. Yo habia creado esa vista de email pensando que era correcta.

**Leccion:**
> Un email de "aprobacion" NO es lo mismo que un email de "firma". El sistema de firmas ya tiene sus propios emails (`FirmaElectronicaController::enviarCorreoFirma()`). No crear emails alternativos.

---

### LECCION 8: Verificar Rutas Existentes

**Problema potencial:**
Crear rutas duplicadas o que colisionen con rutas existentes.

**Verificacion realizada:**
```bash
# Buscar rutas de firma existentes
grep -n "firma/solicitar" app/Config/Routes.php
```

Las rutas `firma/*` ya existian y funcionaban correctamente para todos los documentos.

**Leccion:**
> Antes de crear nuevas rutas, verificar si las rutas necesarias ya existen en Routes.php.

---

### LECCION 9: Entender el Proposito de Cada Tabla

**Confusion:**
- `tbl_presupuesto_sst` - Datos especificos del presupuesto (items, montos, categorias)
- `tbl_documentos_sst` - Control documental unificado (estado, version, firmas)
- `tbl_doc_firma_solicitudes` - Solicitudes de firma electronica

**Leccion:**
> Cada tabla tiene un proposito especifico. Un documento complejo puede necesitar AMBAS: su tabla propia para datos especificos Y un registro en `tbl_documentos_sst` para integrarse con el ecosistema.

---

### LECCION 10: El Usuario Conoce Mejor Su Sistema

**Situacion:**
El usuario rechazo mi primera solucion (modal de firmas personalizado) y me redireccion a estudiar la arquitectura existente.

**Leccion:**
> Cuando el usuario dice "estudiala a fondo", hacerlo literalmente. Leer el codigo de referencia completamente antes de implementar. El usuario conoce las decisiones arquitectonicas previas.

---

### LECCION 11: Compatibilidad de Librerias Externas

**Error encontrado:**
Al intentar firmar un documento, se produjo un error fatal:
```
TypeError: Cannot assign string to property chillerlan\QRCode\QROptions::$eccLevel of type int
in c:\xampp\htdocs\enterprisesst\vendor\chillerlan\php-qrcode\src\QROptionsTrait.php on line 270
```

**Causa raiz:**
La libreria `chillerlan/php-qrcode` v5.0.5 cambio la firma de tipo para `eccLevel`:
- Version anterior: aceptaba strings como `'L'`, `'M'`, `'Q'`, `'H'`
- Version 5.x: requiere constantes enteras de `EccLevel::L`, etc.

**Codigo incorrecto:**
```php
$options = new \chillerlan\QRCode\QROptions([
    'outputType' => 'png',
    'eccLevel' => 'L',  // ERROR: string no permitido
]);
```

**Codigo correcto:**
```php
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;

$options = new \chillerlan\QRCode\QROptions([
    'outputType' => QROutputInterface::GDIMAGE_PNG,
    'eccLevel' => EccLevel::L,  // Constante entera (0b01 = 1)
]);
```

**Leccion:**
> Al actualizar librerias de composer o usar codigo existente, verificar que los tipos de parametros coincidan con la version instalada. Revisar CHANGELOG o documentacion de la libreria cuando aparezcan TypeErrors.

**Valores de EccLevel en v5.x:**
| Constante | Valor | Correccion de errores |
|-----------|-------|----------------------|
| EccLevel::L | 0b01 (1) | 7% |
| EccLevel::M | 0b00 (0) | 15% |
| EccLevel::Q | 0b11 (3) | 25% |
| EccLevel::H | 0b10 (2) | 30% |

---

## CHECKLIST PARA FUTURAS INTEGRACIONES DE DOCUMENTOS SST

Antes de implementar un nuevo documento SST, verificar:

- [ ] El documento tiene registro en `tbl_documentos_sst`?
- [ ] Usa `FirmaElectronicaController` para firmas (no sistema propio)?
- [ ] El boton de firmas apunta a `firma/solicitar/{id_documento}`?
- [ ] Los estados se sincronizan correctamente entre tablas?
- [ ] Aparece en el Control Documental (2.5.1)?
- [ ] Estudie un documento similar como referencia?
- [ ] Las rutas necesarias ya existen o las cree correctamente?

---

## ARQUITECTURA CORRECTA DE FIRMAS ELECTRONICAS

```
┌──────────────────────────────────────────────────────────────────┐
│                    FLUJO DE FIRMA CORRECTO                        │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  1. CREAR DOCUMENTO                                               │
│     └─> INSERT tbl_documentos_sst (tipo_documento, estado)        │
│     └─> INSERT tbl_doc_versiones_sst (version 1.0)                │
│                                                                   │
│  2. SOLICITAR FIRMAS (Consultor)                                  │
│     └─> GET firma/solicitar/{id_documento}                        │
│     └─> FirmaElectronicaController::solicitar()                   │
│     └─> Vista: firma/solicitar.php                                │
│                                                                   │
│  3. CREAR SOLICITUDES (Consultor hace clic)                       │
│     └─> POST firma/crear-solicitud                                │
│     └─> INSERT tbl_doc_firma_solicitudes (token, orden)           │
│     └─> UPDATE tbl_documentos_sst SET estado='pendiente_firma'    │
│     └─> Envia email con link firma/firmar/{token}                 │
│                                                                   │
│  4. FIRMAR (Firmante externo via email)                           │
│     └─> GET firma/firmar/{token}                                  │
│     └─> Vista con canvas para firma                               │
│     └─> POST firma/procesar (firma_imagen, acepto_terminos)       │
│     └─> INSERT tbl_doc_firma_evidencias                           │
│     └─> UPDATE tbl_doc_firma_solicitudes SET estado='firmado'     │
│                                                                   │
│  5. VERIFICAR ESTADO                                              │
│     └─> GET firma/estado/{id_documento}                           │
│     └─> Muestra progreso de todas las firmas                      │
│                                                                   │
│  6. TODAS LAS FIRMAS COMPLETAS                                    │
│     └─> UPDATE tbl_documentos_sst SET estado='firmado'            │
│     └─> Genera PDF firmado                                        │
│     └─> Publica en tbl_reporte                                    │
│     └─> Notifica al consultor                                     │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## HISTORIAL DE CAMBIOS (Continuacion)

| Fecha | Cambio | Autor |
|-------|--------|-------|
| 2026-01-31 | Integracion presupuesto con FirmaElectronicaController | Claude |
| 2026-01-31 | Sincronizacion con tbl_documentos_sst | Claude |
| 2026-01-31 | Documentacion de lecciones aprendidas | Claude |
| 2026-01-31 | Fix QRCode eccLevel TypeError (v5.x compatibility) | Claude |
