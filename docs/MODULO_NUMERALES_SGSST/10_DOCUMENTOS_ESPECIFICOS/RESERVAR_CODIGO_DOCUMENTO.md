# Reservar el Código de un Documento Nuevo

> Guía operativa rápida para asignar el `codigo_sugerido` de un nuevo `tipo_documento`
> en `tbl_doc_plantillas`. **Este código es el que devuelve `getCodigoBase()` y aparece
> impreso en cada PDF generado** — sin él, el documento sale con el fallback `'DOC-GEN'`.
>
> **Aplica a CUALQUIER convención de código** — no solo `FT-SST-NNN`. El mismo mecanismo
> sirve para `POL-XXX` (políticas), `MAN-XXX` (manuales), `PRG-XXX` (programas),
> `ACT-XXX` (actas), `FOR-XXX` (formatos), etc. Solo cambia el string que escribes en
> `codigo_sugerido`; la lógica de reserva, idempotencia y orden local→prod es idéntica.
>
> Tiempo estimado: 5–10 min. Idempotente (se puede correr varias veces sin daño).

---

## 1. Cuándo aplicar esta guía

- Cuando creas un **nuevo `tipo_documento`** (clase PHP en `app/Libraries/DocumentosSSTTypes/`).
- Cuando un documento existente **devuelve `'DOC-GEN'`** en su código (síntoma de que falta el registro en `tbl_doc_plantillas`).
- Cuando quieres **renumerar** o cambiar el código de un tipo existente.

---

## 2. Convenciones de código que existen en `tbl_doc_plantillas`

El proyecto usa **dos sistemas de codificación** que conviven sin conflicto:

### 2.1 Códigos por prefijo de letras (sistema mayoritario)

El prefijo coincide con `tbl_doc_tipos.codigo` del `id_tipo` que asignas. Es el formato
recomendado para documentos clasificables claramente en una de las 14 categorías.

| Prefijo | Categoría (id_tipo) | Ejemplos en `tbl_doc_plantillas` |
|---------|---------------------|----------------------------------|
| `POL-XXX`     | 1 — Política           | (políticas SST: alcohol, acoso, desconexión, etc.) |
| `OBJ-XXX`     | 2 — Objetivos          | (plan de objetivos del SG-SST) |
| `PRG-XXX`     | 3 — Programa           | (programa capacitación, mantenimiento, inspecciones, etc.) |
| `PLA-XXX`     | 4 — Plan               | (plan de trabajo, plan de emergencias) |
| `PRO-XXX`     | 5 — Procedimiento      | `PRO-AUD`, `PRO-COM`, `PRO-DOC`, `PRO-INV`, `PRO-IPVR` |
| `PRT-XXX`     | 6 — Protocolo          | `PRT-BIO`, `PRT-EME` |
| `MAN-XXX`     | 7 — Manual             | (manuales SG-SST) |
| `INF-XXX`     | 8 — Informe            | (informes de gestión / auditoría) |
| `FOR-XXX`     | 9 — Formato            | `FOR-ASI`, `FOR-EPP`, `FOR-INC`, `FOR-INS` |
| `MTZ-XXX`     | 10 — Matriz            | (matriz de peligros, matriz EPP, etc.) |
| `ACT-XXX`     | 11 — Acta              | `ACT-BRI`, `ACT-CCL`, `ACT-COP` |
| `GUA-XXX`     | 12 — Guía              | (guías o instructivos) |
| `INS-XXX`     | 13 — Instructivo       | (instructivos paso a paso) |
| `REG-XXX`     | 14 — Reglamento        | `REG-CCL`, `REG-COP`, `REG-HSI` |
| `PVE-XXX-NNN` | 3 — Programa (subtipo) | `PVE-BIO-001`, `PVE-PSI-001` (Programas Vigilancia Epidemiológica) |
| `RES-XXX`     | (variable)             | `RES-REP`, `RES-SST`, `RES-TRA` (responsabilidades) |
| `ASG-XXX`     | (variable)             | `ASG-RES` (asignaciones) |
| `CRT-XXX`     | (variable)             | `CRT-AR` (certificaciones) |

### 2.2 Códigos `FT-SST-NNN` (numéricos genéricos)

Convención usada cuando el documento **no encaja claramente** en una categoría con prefijo
de letras, o es un formato transversal (multi-categoría).

| Rango | Uso típico | Ejemplos |
|-------|-----------|----------|
| `FT-SST-010..099` | Actas y básicos del SG-SST | `FT-SST-013` acta_constitucion_copasst, `FT-SST-022` presupuesto_sst |
| `FT-SST-100..199` | Talento humano y administrativo | `FT-SST-100` perfil_cargo |
| `FT-SST-200..299` | Socializaciones y comunicaciones | `FT-SST-201..213` socializacion_miembros_*, socializacion_cronograma_* |

### 2.3 Cómo elegir entre los dos sistemas

| Caso | Recomendación |
|------|---------------|
| Documento clasificable claramente (manual, política, programa) | **Prefijo de letras** que coincida con `tbl_doc_tipos.codigo` (ej. `MAN-SST`, `POL-ACOSO`) |
| Documento generado a partir de un proceso (acta, evidencia, socialización) | **`FT-SST-NNN`** numérico (más fácil para consecutivos) |
| Subtipo dentro de una familia | Prefijo extendido (ej. `PVE-BIO-001`, `PVE-PSI-001`) |
| Migración de un código existente en otro sistema | Conserva el código original |

> **Regla práctica:** verifica primero qué hay en BD (PASO 1) y mantén la coherencia con
> los documentos hermanos. Si todos los procedimientos usan `PRO-XXX`, el tuyo también.
> Si todas las socializaciones usan `FT-SST-2XX`, la tuya también.

---

## 3. PASO 1 — Verificar qué códigos están libres

Antes de elegir un número, corre el script de verificación que ya existe en el repo:

```bash
# LOCAL primero
php scripts/check_codigos_ft_disponibles.php

# Si quieres confirmar contra producción
php scripts/check_codigos_ft_disponibles.php --prod
```

El script muestra:
- Lista completa de `codigo_sugerido` actualmente registrados en `tbl_doc_plantillas`
  (todos los prefijos: `POL-XXX`, `MAN-XXX`, `PRG-XXX`, `ACT-XXX`, `FT-SST-NNN`, etc.).
- Disponibilidad explícita en el rango `FT-SST-200..220` por defecto.

Si el código que necesitas usa otro prefijo, consulta directamente la tabla:

```sql
-- Ver todos los procedimientos ya registrados
SELECT codigo_sugerido FROM tbl_doc_plantillas
WHERE codigo_sugerido LIKE 'PRO-%' ORDER BY codigo_sugerido;

-- Ver todos los manuales ya registrados
SELECT codigo_sugerido FROM tbl_doc_plantillas
WHERE codigo_sugerido LIKE 'MAN-%' ORDER BY codigo_sugerido;

-- Ver todos los programas ya registrados
SELECT codigo_sugerido FROM tbl_doc_plantillas
WHERE codigo_sugerido LIKE 'PRG-%' ORDER BY codigo_sugerido;
```

---

## 4. PASO 2 — Identificar el `id_tipo` correcto

`tbl_doc_plantillas.id_tipo` es FK obligatoria a `tbl_doc_tipos`. Si lo dejas vacío o pones un id inexistente, el INSERT falla con error de foreign key.

**Catálogo `tbl_doc_tipos` (snapshot 2026-05):**

| id_tipo | codigo | nombre | tiene_secciones | firma_cliente | Cuándo usar |
|---------|--------|--------|-----------------|---------------|-------------|
| 1 | POL | Política | sí (5) | sí | Políticas de SST (general, alcohol, acoso, etc.) |
| 2 | OBJ | Objetivos | sí (3) | sí | Plan de objetivos y metas |
| 3 | PRG | Programa | sí (13) | sí | Programas con cronograma e indicadores |
| 4 | PLA | Plan | sí (10) | sí | Plan de trabajo, plan de acción |
| 5 | PRO | Procedimiento | sí (8) | sí | Procedimientos operativos |
| 6 | PRT | Protocolo | sí (7) | sí | Protocolos de actuación |
| 7 | MAN | Manual | sí (8) | sí | Manuales SG-SST |
| 8 | INF | Informe | sí (6) | no | Informes de gestión / auditoría |
| 9 | FOR | Formato | no | no | **Formatos sin secciones**: PDFs generados (socializaciones, evidencias, certificados) |
| 10 | MTZ | Matriz | no | no | Matrices (peligros, EPP, comunicación) |
| 11 | ACT | Acta | sí (5) | no | Actas (constitución, reunión) |
| 12 | GUA | Guía | sí (5) | no | Guías o instructivos |
| 13 | INS | Instructivo | sí (4) | no | Instructivos paso a paso |
| 14 | REG | Reglamento | sí (6) | sí | Reglamentos internos |

**Regla práctica:**

- ¿Tu PDF es **generado** desde datos del aplicativo, sin secciones ni firmas? → `id_tipo=9` (Formato).
- ¿Es una **acta** generada desde un proceso (electoral, reunión)? → `id_tipo=11` (Acta).
- ¿Es un documento **escrito por IA** con secciones definidas? → según el tipo: política=1, programa=3, procedimiento=5, etc.

---

## 5. PASO 3 — Crear el script de reserva

Crea un script en `scripts/reservar_codigo_{tipo}.php` siguiendo este patrón **idempotente**:

```php
<?php
/**
 * Reserva en tbl_doc_plantillas el codigo (FT-SST-NNN, POL-XXX, MAN-XXX, etc.)
 * para el nuevo tipo_documento.
 * Idempotente: SKIP si ya existe.
 *
 * Uso:
 *   php scripts/reservar_codigo_{tipo}.php             # local dry-run
 *   php scripts/reservar_codigo_{tipo}.php --apply     # local apply
 *   php scripts/reservar_codigo_{tipo}.php --prod              # prod dry-run
 *   php scripts/reservar_codigo_{tipo}.php --prod --apply      # prod apply
 */

$isProd = in_array('--prod', $argv ?? [], true);
$apply  = in_array('--apply', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN') . " ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST'); $user = getenv('DB_PROD_USER');
    $pass = getenv('DB_PROD_PASS'); $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if (!$host || !$user || !$pass) { echo "ERROR env vars\n"; exit(1); }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    if (!@mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        echo "ERROR conn: " . mysqli_connect_error() . "\n"; exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

// === EDITAR AQUI: datos del nuevo tipo de documento ===
// Ejemplos validos para 'codigo':
//   'POL-DESC'        para una politica (id_tipo=1)
//   'PRG-CAP-2026'    para un programa con consecutivo (id_tipo=3)
//   'MAN-SST'         para un manual (id_tipo=7)
//   'PRO-AUD'         para un procedimiento (id_tipo=5)
//   'ACT-CCL'         para un acta (id_tipo=11)
//   'FOR-EVAL'        para un formato (id_tipo=9)
//   'FT-SST-301'      para un formato numerico generico (id_tipo=9)
$reservas = [
    [
        'tipo'    => 'mi_nuevo_tipo_documento',     // snake_case, identifica el tipo en codigo PHP
        'codigo'  => 'PRO-NUEVO',                   // codigo elegido (verificado libre en paso 1)
        'id_tipo' => 5,                             // FK a tbl_doc_tipos (paso 2)
        'nombre'  => 'Nombre Humano del Documento',
        'descr'   => 'Descripcion corta de para que sirve este documento.',
    ],
    // ...mas reservas si haces un batch
];
// =======================================================

// Idempotencia: SKIP si tipo_documento ya tiene fila en tbl_doc_plantillas
foreach ($reservas as $r) {
    $tipo = $conn->real_escape_string($r['tipo']);
    $chk = $conn->query("SELECT id_plantilla FROM tbl_doc_plantillas WHERE tipo_documento='{$tipo}'");
    if ($chk->num_rows > 0) {
        echo "  SKIP {$r['codigo']} ({$r['tipo']}) - ya existe.\n";
        continue;
    }
    if (!$apply) {
        echo "  WOULD INSERT {$r['codigo']} -> {$r['tipo']}\n";
        continue;
    }

    $sql = sprintf(
        "INSERT INTO tbl_doc_plantillas (tipo_documento, codigo_sugerido, id_tipo, nombre, descripcion, activo) VALUES ('%s','%s',%d,'%s','%s',1)",
        $conn->real_escape_string($r['tipo']),
        $conn->real_escape_string($r['codigo']),
        (int) $r['id_tipo'],
        $conn->real_escape_string($r['nombre']),
        $conn->real_escape_string($r['descr'])
    );
    if (!$conn->query($sql)) {
        echo "  ERROR insertando {$r['codigo']}: " . $conn->error . "\n";
        continue;
    }
    echo "  OK INSERT {$r['codigo']} -> {$r['tipo']}\n";
}

if (!$apply) echo "\n[DRY-RUN] Sin cambios.\n";

$conn->close();
echo "\nOK.\n";
```

---

## 6. PASO 4 — Ejecutar en orden obligatorio: LOCAL → PROD

```bash
# 1. Dry-run local (no toca BD)
php scripts/reservar_codigo_mi_tipo.php

# 2. Apply local (toca BD local)
php scripts/reservar_codigo_mi_tipo.php --apply

# 3. Verificar que el documento ya genera con el codigo correcto en local
#    (probar el flujo completo: ir a la URL, generar PDF, ver que codigo sale)

# 4. Si todo OK, hacer dry-run en prod
DB_PROD_HOST=... DB_PROD_USER=... DB_PROD_PASS=... \
  php scripts/reservar_codigo_mi_tipo.php --prod

# 5. Apply en prod
DB_PROD_HOST=... DB_PROD_USER=... DB_PROD_PASS=... \
  php scripts/reservar_codigo_mi_tipo.php --prod --apply
```

> **Nunca saltarse el orden.** Si te equivocaste en el `id_tipo`, en LOCAL corriges sin
> consecuencia. En PROD un FK-fail puede ensuciar el log y requerir UPDATE de limpieza.

---

## 7. PASO 5 — Verificar que `getCodigoBase()` devuelve el código correcto

En la clase PHP del tipo, `getCodigoBase()` heredado de `AbstractDocumentoSST` consulta
automáticamente `tbl_doc_plantillas.codigo_sugerido`:

```php
public function getCodigoBase(): string
{
    $db = \Config\Database::connect();
    $plantilla = $db->table('tbl_doc_plantillas')
        ->select('codigo_sugerido')
        ->where('tipo_documento', $this->getTipoDocumento())
        ->where('activo', 1)
        ->get()
        ->getRow();

    if ($plantilla && !empty($plantilla->codigo_sugerido)) {
        return $plantilla->codigo_sugerido;  // ← devuelve el codigo registrado (POL-XXX, MAN-XXX, FT-SST-NNN, etc.)
    }
    return 'DOC-GEN';  // ← fallback si no encontró fila
}
```

**Si tu clase devuelve `'DOC-GEN'`:**
1. Confirma que `tipo_documento` en BD coincide EXACTAMENTE con `getTipoDocumento()` (case-sensitive, snake_case).
2. Confirma que `activo=1` en la fila de BD.
3. Confirma que la BD a la que apunta el aplicativo es la misma a la que ejecutaste el script (¿corriste prod en lugar de local por error?).

---

## 8. Antipatrones (NO hacer)

| Mal | Por qué | Bien |
|-----|---------|------|
| Hardcodear `return 'FT-SST-201';` en `getCodigoBase()` de una hija | Imposible cambiar sin tocar código + redeploy | Reservar en BD y dejar que la clase abstracta lo lea |
| Insertar manualmente con phpMyAdmin / cliente | No hay registro de qué se hizo, no es reproducible | Script CLI versionado en git |
| Saltar PASO 4 y aplicar directo en prod | Si el `id_tipo` es inválido, ensucia prod logs | Local primero |
| Reusar un código (de cualquier prefijo) ya tomado | El generador puede mezclar documentos en el reportlist | Verificar con PASO 1 antes |
| Mezclar prefijos en una misma familia (algunos `PRO-XXX` y otros `FT-SST-NNN`) | Inconsistencia en el reportlist; difícil clasificar | Mantener un solo sistema por familia (todos los procedimientos `PRO-XXX`, todas las socializaciones `FT-SST-2XX`, etc.) |
| Activar el documento sin reservar el código | Sale con `'DOC-GEN'` en cada PDF | Reservar ANTES de marcar `activo=1` la primera vez |

---

## 9. Casos reales de referencia

### 9.1 Códigos `FT-SST-NNN` numéricos (commit `e6493af`)

Para las socializaciones de comités creé:

- `scripts/check_codigos_ft_disponibles.php` (verificación)
- `scripts/reservar_codigos_socializaciones.php` (reserva 6 códigos: `FT-SST-201..203`, `211..213`)
- Todos con `id_tipo=9` (Formato), porque son PDFs generados sin secciones.

### 9.2 Código con prefijo de letras (estilo legacy/clásico)

Si quisieras crear un nuevo procedimiento, ejemplo:

```php
$reservas = [
    [
        'tipo'    => 'procedimiento_orden_aseo',
        'codigo'  => 'PRO-OAS',          // sigue convención de los demás procedimientos
        'id_tipo' => 5,                  // Procedimiento
        'nombre'  => 'Procedimiento de Orden y Aseo',
        'descr'   => 'Procedimiento operativo para mantenimiento del orden y aseo en areas de trabajo.',
    ],
];
```

O un nuevo manual:

```php
$reservas = [
    [
        'tipo'    => 'manual_funciones_sst',
        'codigo'  => 'MAN-FUN',
        'id_tipo' => 7,                  // Manual
        'nombre'  => 'Manual de Funciones SST',
        'descr'   => 'Descripcion de funciones del responsable y equipo SST.',
    ],
];
```

El script y el flujo son **idénticos** — solo cambia el `codigo` y el `id_tipo`.

---

## 10. Referencias

- Catálogo de tipos: `tbl_doc_tipos` (consultar con `php scripts/check_codigos_ft_disponibles.php` que también lo lista).
- Tabla destino: `tbl_doc_plantillas` (PK `id_plantilla`, UNIQUE implícito por `tipo_documento`).
- Helper que lee el código: `App\Libraries\DocumentosSSTTypes\AbstractDocumentoSST::getCodigoBase()`.
- Guía completa de creación de un nuevo tipo de documento: `GUIA_PASO_A_PASO_DOCUMENTO_TIPO_C.md` (esta guía amplía el PASO 5 con todas las convenciones de prefijo).
- Troubleshooting cuando un código sale mal en el PDF: `02_GENERACION_IA/TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md`.
