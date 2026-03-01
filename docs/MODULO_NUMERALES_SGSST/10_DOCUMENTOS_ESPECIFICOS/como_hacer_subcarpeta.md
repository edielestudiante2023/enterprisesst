# Como crear una subcarpeta en el arbol de carpetas SST

## Contexto

El sistema organiza documentos del SG-SST en una estructura de carpetas jerarquica basada en la Resolucion 0312/2019:

```
SG-SST 2026 (raiz)
  └─ 1. PLANEAR (phva)
       └─ 2.8.1. Mecanismos de comunicacion... (estandar)
            └─ 2.8.1.1. Matriz de Comunicacion SST (estandar)  ← subcarpeta
```

Las subcarpetas son hijas de un estandar existente. Ejemplo real: `2.5.1.1` es hija de `2.5.1`, `2.8.1.1` es hija de `2.8.1`.

## Tabla de BD

Todas las carpetas viven en `tbl_doc_carpetas`:

| Columna | Descripcion |
|---------|-------------|
| id_carpeta | PK autoincremental |
| id_cliente | FK al cliente dueno |
| id_carpeta_padre | FK a la carpeta padre (NULL = raiz) |
| nombre | Nombre completo (ej: `2.8.1.1. Matriz de Comunicacion SST`) |
| codigo | Codigo numeral (ej: `2.8.1.1`) |
| orden | Orden dentro del padre (1, 2, 3...) |
| tipo | `raiz`, `phva`, `estandar`, `custom` |
| icono | Icono Bootstrap Icons (ej: `diagram-3`) |

La subcarpeta se diferencia del padre solo por `id_carpeta_padre` apuntando al estandar padre en vez de al PHVA.

---

## Pasos para crear una subcarpeta

### Paso 1: Modificar el Stored Procedure

**Archivo:** `app/SQL/sp/sp_04_generar_carpetas_por_nivel.sql`

#### 1a. Declarar variable para capturar el ID del padre

En el bloque de `DECLARE` al inicio del SP, agregar una variable:

```sql
DECLARE v_id_281 INT;  -- para subcarpetas de 2.8.1
```

#### 1b. Capturar LAST_INSERT_ID() del padre

Inmediatamente despues del INSERT de la carpeta padre, capturar su ID:

```sql
-- 2.8.1 - Aplica: 60
IF p_nivel_estandares = 60 THEN
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '2.8.1. Mecanismos de comunicacion...', '2.8.1', 20, 'estandar', 'megaphone');
    SET v_id_281 = LAST_INSERT_ID();  -- ← AGREGAR ESTA LINEA
```

#### 1c. Insertar la subcarpeta

Justo despues, dentro del mismo `IF`, insertar la subcarpeta con `id_carpeta_padre` apuntando a la variable:

```sql
    -- 2.8.1.1 - Sub-carpeta: Matriz de Comunicacion SST (Aplica: 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_281, '2.8.1.1. Matriz de Comunicacion SST', '2.8.1.1', 1, 'estandar', 'diagram-3');
END IF;
```

**Nota:** El `orden` de la subcarpeta empieza en 1 (es relativo al padre, no al PHVA).

---

### Paso 2: Script de migracion para clientes existentes

**Crear archivo:** `app/SQL/agregar_subcarpeta_X_X_X_X_nombre.php`

El SP solo aplica a clientes NUEVOS. Para clientes existentes que ya tienen la carpeta padre, necesitas un script PHP CLI que:

1. Busca todos los clientes que tienen la carpeta padre (ej: `codigo = '2.8.1'`)
2. Verifica que no exista ya la subcarpeta (ej: `codigo = '2.8.1.1'`)
3. Inserta la subcarpeta con `id_carpeta_padre` apuntando al `id_carpeta` del padre

```php
$carpetasPadre = $pdo->query(
    "SELECT id_carpeta, id_cliente
     FROM tbl_doc_carpetas
     WHERE codigo = '2.8.1'"
)->fetchAll(PDO::FETCH_ASSOC);

foreach ($carpetasPadre as $row) {
    $idCliente  = (int) $row['id_cliente'];
    $idPadre    = (int) $row['id_carpeta'];

    // Verificar si ya existe
    $chk = $pdo->prepare(
        "SELECT COUNT(*) FROM tbl_doc_carpetas
         WHERE id_cliente = ? AND codigo = '2.8.1.1'"
    );
    $chk->execute([$idCliente]);
    if ($chk->fetchColumn() > 0) continue;

    // Insertar subcarpeta
    $pdo->prepare(
        "INSERT INTO tbl_doc_carpetas
            (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
         VALUES (?, ?, '2.8.1.1. Matriz de Comunicacion SST',
                 '2.8.1.1', 1, 'estandar', 'diagram-3')"
    )->execute([$idCliente, $idPadre]);
}
```

**Ejecutar:** LOCAL primero, PRODUCCION solo si LOCAL OK.

Referencia completa: `app/SQL/agregar_subcarpeta_2_8_1_1_matriz_comunicacion.php`

---

### Paso 3: Mapeo en DocumentacionController

**Archivo:** `app/Controllers/DocumentacionController.php`

#### 3a. Funcion `determinarTipoCarpetaFases()`

Agregar el mapeo de la subcarpeta **ANTES** del padre para que el codigo especifico se capture primero:

```php
// 2.8.1.1. Matriz de Comunicacion SST
// DEBE ir ANTES de 2.8.1 para que el código específico se capture primero
if ($codigo === '2.8.1.1') {
    return 'matriz_comunicacion_sst';
}

// 2.8.1. Mecanismos de comunicación (este ya existia)
if ($codigo === '2.8.1' || ...) {
    return 'mecanismos_comunicacion_sgsst';
}
```

**REGLA CRITICA:** La subcarpeta SIEMPRE va antes que el padre en la funcion de deteccion.

#### 3b. Filtro de documentos SST

En la seccion donde se filtran documentos por `tipoCarpetaFases`:

```php
} elseif ($tipoCarpetaFases === 'matriz_comunicacion_sst') {
    // 2.8.1.1: Matriz de Comunicacion SST
    $queryDocs->where('tipo_documento', 'procedimiento_matriz_comunicacion');
}
```

#### 3c. Soportes adicionales

En la seccion donde se cargan soportes por `tipoCarpetaFases`:

```php
} elseif ($tipoCarpetaFases === 'matriz_comunicacion_sst') {
    // 2.8.1.1 Matriz de Comunicacion SST
    $db = $db ?? \Config\Database::connect();
    $soportesAdicionales = $db->table('tbl_documentos_sst')
        ->where('id_cliente', $cliente['id_cliente'])
        ->where('tipo_documento', 'soporte_matriz_comunicacion')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->getResultArray();
}
```

---

### Paso 4: Mapeo en ClienteDocumentosSstController

**Archivo:** `app/Controllers/ClienteDocumentosSstController.php`

3 mapeos necesarios:

#### 4a. Array de tipos de documentos por carpeta

```php
'matriz_comunicacion_sst' => ['procedimiento_matriz_comunicacion'],
```

#### 4b. Array de codigo a tipo de carpeta

```php
'2.8.1.1' => 'matriz_comunicacion_sst',
```

#### 4c. Array de plantilla a tipo de documento (si aplica)

```php
'PRC-MCO' => 'procedimiento_matriz_comunicacion',
```

---

### Paso 5: Mapeo plantilla-carpeta (si hay documento asociado)

Si la subcarpeta tiene un documento Tipo A asociado con codigo de plantilla (ej: PRC-MCO), actualizar `tbl_doc_plantilla_carpeta`:

```sql
-- En el script de migracion
UPDATE tbl_doc_plantilla_carpeta
SET codigo_carpeta = '2.8.1.1'
WHERE codigo_plantilla = 'PRC-MCO' AND codigo_carpeta = '2.8.1';
```

Esto hace que el documento se asocie a la subcarpeta en vez del padre.

---

## Checklist rapido

| # | Que hacer | Archivo |
|---|-----------|---------|
| 1 | Declarar `v_id_XXX` + `SET LAST_INSERT_ID()` + INSERT subcarpeta | `sp_04_generar_carpetas_por_nivel.sql` |
| 2 | Script migracion clientes existentes (LOCAL + PROD) | `app/SQL/agregar_subcarpeta_X_X_X.php` |
| 3 | `determinarTipoCarpetaFases()` — ANTES del padre | `DocumentacionController.php` |
| 4 | Filtro documentos SST por `tipoCarpetaFases` | `DocumentacionController.php` |
| 5 | Soportes adicionales por `tipoCarpetaFases` | `DocumentacionController.php` |
| 6 | Array tipos documentos por carpeta | `ClienteDocumentosSstController.php` |
| 7 | Array codigo → tipo carpeta | `ClienteDocumentosSstController.php` |
| 8 | Array plantilla → tipo documento | `ClienteDocumentosSstController.php` |
| 9 | `tbl_doc_plantilla_carpeta` (si hay documento) | Script migracion |

## Subcarpetas existentes

| Codigo | Nombre | Padre | Creada en |
|--------|--------|-------|-----------|
| 2.5.1.1 | Listado Maestro de Documentos Externos | 2.5.1 | SP original |
| 2.8.1.1 | Matriz de Comunicacion SST | 2.8.1 | Marzo 2026 |
