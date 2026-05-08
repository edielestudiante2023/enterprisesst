# Reservar un Código FT-SST para un Documento Nuevo

> Guía operativa rápida para asignar el `codigo_sugerido` de un nuevo `tipo_documento`
> en `tbl_doc_plantillas`. **Este código es el que devuelve `getCodigoBase()` y aparece
> impreso en cada PDF generado** — sin él, el documento sale con el fallback `'DOC-GEN'`.
>
> Tiempo estimado: 5–10 min. Idempotente (se puede correr varias veces sin daño).

---

## 1. Cuándo aplicar esta guía

- Cuando creas un **nuevo `tipo_documento`** (clase PHP en `app/Libraries/DocumentosSSTTypes/`).
- Cuando un documento existente **devuelve `'DOC-GEN'`** en su código (síntoma de que falta el registro en `tbl_doc_plantillas`).
- Cuando quieres **renumerar** o cambiar el código FT-SST de un tipo existente.

---

## 2. Convención de rangos FT-SST

Antes de elegir un número, revisa el rango. La convención observada en el proyecto:

| Rango | Categoría | Ejemplos |
|-------|-----------|----------|
| `FT-SST-010` a `FT-SST-099` | Actas y básicos del SG-SST | `FT-SST-013` acta_constitucion_copasst, `FT-SST-022` presupuesto_sst |
| `FT-SST-100` a `FT-SST-199` | Talento humano y administrativo | `FT-SST-100` perfil_cargo |
| `FT-SST-200` a `FT-SST-299` | Socializaciones y comunicaciones | `FT-SST-201..213` socializacion_miembros_*, socializacion_cronograma_* |
| Otros prefijos (`POL-XXX`, `PRG-XXX`, `ACT-XXX`, etc.) | Códigos legacy de la primera generación | No usar para nuevos documentos |

> **Regla:** para nuevos documentos usa siempre el prefijo `FT-SST-` con número de 3 dígitos.
> Solo asigna prefijos legacy si estás portando algo que ya tenía ese código en otro sistema.

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
- Lista completa de `codigo_sugerido` actualmente registrados en `tbl_doc_plantillas`.
- Disponibilidad explícita en el rango `FT-SST-200..220` (LIBRE / OCUPADO).

Si el rango que necesitas no está cubierto por el script, edita el rango en el script o consulta directamente la tabla:

```sql
SELECT codigo_sugerido FROM tbl_doc_plantillas
WHERE codigo_sugerido LIKE 'FT-SST-1%' ORDER BY codigo_sugerido;
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
 * Reserva en tbl_doc_plantillas el codigo FT-SST para el nuevo tipo_documento.
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
$reservas = [
    [
        'tipo'    => 'mi_nuevo_tipo_documento',     // snake_case, identifica el tipo en codigo PHP
        'codigo'  => 'FT-SST-XXX',                  // codigo elegido (verificado libre en paso 1)
        'id_tipo' => 9,                             // FK a tbl_doc_tipos (paso 2)
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
        return $plantilla->codigo_sugerido;  // ← devuelve FT-SST-XXX
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
| Reusar un código FT-SST ya tomado | El generador puede mezclar documentos en el reportlist | Verificar con paso 1 antes |
| Activar el documento sin reservar el código | Sale con `'DOC-GEN'` en cada PDF | Reservar ANTES de marcar `activo=1` la primera vez |

---

## 9. Caso real de referencia (commit `e6493af`)

Para las socializaciones de comités creé:

- `scripts/check_codigos_ft_disponibles.php` (verificación)
- `scripts/reservar_codigos_socializaciones.php` (reserva 6 códigos: FT-SST-201..203, 211..213)
- Todos con `id_tipo=9` (Formato), porque son PDFs generados sin secciones.

Es el ejemplo más reciente y completo del patrón.

---

## 10. Referencias

- Catálogo de tipos: `tbl_doc_tipos` (consultar con `php scripts/check_codigos_ft_disponibles.php` que también lo lista).
- Tabla destino: `tbl_doc_plantillas` (PK `id_plantilla`, UNIQUE implícito por `tipo_documento`).
- Helper que lee el código: `App\Libraries\DocumentosSSTTypes\AbstractDocumentoSST::getCodigoBase()`.
- Guía completa de creación de un nuevo tipo de documento: `GUIA_PASO_A_PASO_DOCUMENTO_TIPO_C.md` (esta guía es el extracto del PASO 5).
- Troubleshooting cuando un código sale mal en el PDF: `02_GENERACION_IA/TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md`.
