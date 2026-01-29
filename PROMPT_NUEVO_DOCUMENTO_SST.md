# Prompt para Crear Nuevo Documento SST

## INSTRUCCIONES PARA CLAUDE

```
Necesito crear un nuevo tipo de documento SST para el aplicativo EnterpriseSST.

ANTES DE ESCRIBIR CUALQUIER CODIGO, haz lo siguiente:

### FASE 1: Determinar el tipo de documento

Preguntame primero:
- ¿El documento requiere generacion con IA y edicion de secciones? (como Programa de Capacitacion)
- ¿O es un documento simple que se genera automaticamente desde el contexto del cliente? (como Asignacion de Responsable)

Segun la respuesta, usaremos uno de estos DOS patrones:

**PATRON A - Documento complejo con IA (editor de secciones):**
- Referencia: DocumentosSSTController.php + programa_capacitacion.php
- Flujo: generarConIA() -> editor de secciones -> aprobar -> vista previa -> firmas
- Ejemplo: Programa de Capacitacion, Plan de Emergencias, Programa de Vigilancia Epidemiologica

**PATRON B - Documento simple auto-generado (sin IA):**
- Referencia: PzasignacionresponsableSstController.php (duplicar y renombrar)
- Flujo: boton "Generar" -> crea documento desde contexto -> vista previa -> firmas
- Ejemplo: Asignacion de Responsable, Politica SST, Actas

### FASE 2: Leer archivos de referencia

**Si es PATRON A (complejo con IA), leer:**
1. app/Controllers/DocumentosSSTController.php - especialmente:
   - Constantes TIPOS_DOCUMENTO y CODIGOS_DOCUMENTO
   - Metodo generarConIA() y generarSeccionIA()
   - Metodo programaCapacitacion() (vista previa)
   - Metodo getPromptBaseParaSeccion() (prompts IA)
2. app/Views/documentos_sst/programa_capacitacion.php (vista de referencia)
3. app/Views/documentos_sst/generar_con_ia.php (editor de secciones)

**Si es PATRON B (simple sin IA), leer:**
1. app/Controllers/PzasignacionresponsableSstController.php (controlador de referencia)
2. app/Views/documentos_sst/asignacion_responsable.php (vista de referencia)
3. Las rutas existentes en Routes.php para ese controlador

### FASE 3: Preguntarme sobre el nuevo documento

Hazme estas preguntas UNA POR UNA:

**Pregunta 1 - Identidad del documento:**
- ¿Como se llama el documento? (ej: "Programa de Vigilancia Epidemiologica")
- ¿Cual es el codigo? Muestrame los existentes en CODIGOS_DOCUMENTO para elegir formato (ej: PRG-VEP)

**Pregunta 2 - Secciones del documento:**
- Propon una lista de secciones basandote en la Resolucion 0312/2019 y normativa colombiana
- Cada seccion necesita: numero, nombre, key
- Preguntame cuales se alimentan de tablas existentes vs generadas por IA/plantilla

**Pregunta 3 - Ubicacion en carpetas:**
- Preguntame en cual estandar de la Resolucion 0312 se ubica este documento
- Consultar tbl_doc_carpetas para ver opciones disponibles

**Pregunta 4 - Dependencias:**
- ¿Requiere fases previas? (cronograma, PTA, indicadores, responsables)
- ¿O es independiente?

**Pregunta 5 - Vista previa:**
- ¿Similar a Programa de Capacitacion? (encabezado + secciones + firmas + control cambios)
- ¿O estructura diferente?

### FASE 4: Implementar EN BLOQUE

IMPORTANTE: Implementar TODO de una vez, en este orden:

---

#### BLOQUE 1: ENTORNO CONSULTOR (crear/editar/aprobar/firmar)

**Si es PATRON A (complejo con IA):**

| Paso | Archivo | Accion |
|------|---------|--------|
| 1 | DocumentosSSTController.php | Agregar en TIPOS_DOCUMENTO |
| 2 | DocumentosSSTController.php | Agregar en CODIGOS_DOCUMENTO |
| 3 | DocumentosSSTController.php | Agregar prompts en getPromptBaseParaSeccion() |
| 4 | DocumentosSSTController.php | Agregar plantillas en generarContenidoSeccion() |
| 5 | DocumentosSSTController.php | Crear metodo vista previa (ej: programaVigilancia()) |
| 6 | app/Views/documentos_sst/[nuevo].php | Crear vista previa |
| 7 | Routes.php | Agregar rutas del documento |
| 8 | DocumentacionController.php | Actualizar tipoCarpetaFases si aplica |
| 9 | app/Views/documentacion/carpeta.php | Agregar boton si es necesario |
| 10 | FasesDocumentoService.php | Agregar fase si tiene dependencias |

**Si es PATRON B (simple sin IA):**

| Paso | Archivo | Accion |
|------|---------|--------|
| 1 | PzasignacionresponsableSstController.php | DUPLICAR archivo |
| 2 | Pz[nuevo]Controller.php | RENOMBRAR clase y constantes |
| 3 | Pz[nuevo]Controller.php | Adaptar TIPO_DOCUMENTO, CODIGO_TIPO, CODIGO_TEMA |
| 4 | Pz[nuevo]Controller.php | Adaptar construirContenido() con campos del documento |
| 5 | asignacion_responsable.php | DUPLICAR vista |
| 6 | app/Views/documentos_sst/[nuevo].php | RENOMBRAR y adaptar vista |
| 7 | Routes.php | Agregar rutas del documento |
| 8 | app/Views/documentacion/carpeta.php | Agregar boton si es necesario |

---

#### BLOQUE 2: ENTORNO CLIENTE (solo lectura - ver/descargar PDF)

| Paso | Archivo | Accion |
|------|---------|--------|
| 1 | ClienteDocumentosSstController.php | Agregar mapeo en mapearPlantillaATipoDocumento() |

```php
// En ClienteDocumentosSstController::mapearPlantillaATipoDocumento()
$mapa = [
    'PRG-CAP' => 'programa_capacitacion',
    'ASG-RES' => 'asignacion_responsable_sgsst',
    'NUEVO-COD' => 'nuevo_tipo_documento',  // <-- Agregar aqui
];
```

**NOTA:** El cliente accede via `/client/mis-documentos-sst` y solo ve documentos con estado `aprobado` o `firmado`.

---

#### BLOQUE 3: BASE DE DATOS (LOCAL + PRODUCCION)

| Paso | Tabla | Accion |
|------|-------|--------|
| 1 | tbl_doc_plantilla_carpeta | INSERT mapeo codigo_plantilla -> codigo_carpeta |
| 2 | tbl_doc_plantillas | INSERT plantilla si no existe |
| 3 | tbl_doc_tipos | INSERT tipo si no existe |

Crear script en `app/SQL/ejecutar_[nombre_documento].php` y ejecutar:
```bash
php app/SQL/ejecutar_[nombre_documento].php
```

---

### FASE 5: Verificacion

**Verificar entorno CONSULTOR:**
1. Ir a la carpeta del estandar y ver que aparezca el boton para generar
2. Generar el documento (con IA o automatico segun patron)
3. Ver vista previa
4. Exportar PDF
5. Solicitar firmas
6. Publicar en reportList

**Verificar entorno CLIENTE:**
1. Acceder a /client/mis-documentos-sst
2. Navegar a la carpeta del estandar
3. Ver el documento en la tabla (solo si esta aprobado/firmado)
4. Descargar PDF

### REGLAS IMPORTANTES:

- PATRON A: Todo en DocumentosSSTController.php (comparte infraestructura IA)
- PATRON B: DUPLICAR Y RENOMBRAR controlador existente (Pz*, Hz*)
- Reutilizar vistas PDF/Word si la estructura es similar
- Los prompts de IA deben ser especificos para normativa colombiana SST
- Siempre ejecutar cambios de BD en LOCAL y PRODUCCION
- Cada prompt debe ajustarse por estandares (7, 21, 60)
- SIEMPRE agregar mapeo en ClienteDocumentosSstController para vista cliente

### DOCUMENTO QUE QUIERO CREAR:

[ESCRIBIR AQUI EL NOMBRE DEL DOCUMENTO]
```

---

## EJEMPLO 1: Documento complejo con IA

```
DOCUMENTO QUE QUIERO CREAR: Programa de Vigilancia Epidemiologica

Tipo: PATRON A (complejo con IA, multiples secciones editables)
```

Claude usara DocumentosSSTController como base, agregara las constantes, prompts, y metodos necesarios.

---

## EJEMPLO 2: Documento simple sin IA

```
DOCUMENTO QUE QUIERO CREAR: Politica de Seguridad y Salud en el Trabajo

Tipo: PATRON B (simple, se genera desde contexto del cliente)
```

Claude duplicara PzasignacionresponsableSstController y lo adaptara.

---

## ARCHIVOS DE REFERENCIA

### Para PATRON A (complejo con IA):

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/DocumentosSSTController.php` | Controlador principal, constantes, prompts, metodos |
| `app/Views/documentos_sst/programa_capacitacion.php` | Vista previa de referencia |
| `app/Views/documentos_sst/generar_con_ia.php` | Editor de secciones |
| `app/Views/documentos_sst/pdf_template.php` | Template PDF |
| `app/Services/FasesDocumentoService.php` | Definicion de fases y dependencias |
| `app/Controllers/DocumentacionController.php` | Logica de carpeta.php |

### Para PATRON B (simple sin IA):

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/PzasignacionresponsableSstController.php` | Controlador de referencia para duplicar |
| `app/Views/documentos_sst/asignacion_responsable.php` | Vista de referencia para duplicar |
| `app/Config/Routes.php` | Rutas existentes como referencia |

### Vista del Cliente (solo lectura):

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/ClienteDocumentosSstController.php` | Controlador para vista del cliente |
| `app/Views/client/documentos_sst/index.php` | Arbol de carpetas PHVA |
| `app/Views/client/documentos_sst/carpeta.php` | Tabla de documentos con boton PDF |

---

## NOTAS TECNICAS

- El PATRON A comparte infraestructura: versionamiento, firmas, exportacion PDF/Word
- El PATRON B es independiente: cada controlador tiene su propia logica
- Para 100+ documentos, el PATRON B escala mejor (duplicar y renombrar)
- El PATRON A es mejor cuando se necesita IA y edicion colaborativa de secciones
- El cliente accede via `/client/mis-documentos-sst` - solo ve documentos aprobados/firmados
- Las carpetas del cliente estan filtradas por `id_cliente` en `tbl_doc_carpetas`

---

## CREDENCIALES Y PATRON PARA CAMBIOS EN BASE DE DATOS

### Instruccion para Claude

Cuando necesites ejecutar cambios de base de datos (INSERT, UPDATE, ALTER, CREATE), **SIEMPRE** debes ejecutarlos en **ambos entornos**: LOCAL y PRODUCCION.

### Credenciales

```php
// LOCAL (XAMPP)
$localConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'empresas_sst',
    'username' => 'root',
    'password' => ''
];

// PRODUCCION (DigitalOcean)
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
    'ssl' => true  // IMPORTANTE: Requiere SSL
];
```

### Patron de Codigo PHP para Migraciones

Crear archivo en `app/SQL/ejecutar_{nombre}.php`:

```php
<?php
/**
 * Script de migracion: {descripcion}
 * Ejecutar con: php app/SQL/ejecutar_{nombre}.php
 */

// Configuracion de entornos
$environments = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'empresas_sst',
        'user' => 'root',
        'pass' => '',
        'ssl' => false
    ],
    'PRODUCTION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'dbname' => 'empresas_sst',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// SQL a ejecutar
$sqlStatements = [
    "INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
     SELECT 'NUEVO-COD', '1.1.1'
     FROM DUAL
     WHERE NOT EXISTS (
         SELECT 1 FROM tbl_doc_plantilla_carpeta
         WHERE codigo_plantilla = 'NUEVO-COD' AND codigo_carpeta = '1.1.1'
     )",

    // Agregar mas statements si es necesario
];

foreach ($environments as $envName => $config) {
    echo "\n========== {$envName} ==========\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "Conectado a {$envName}\n";

        foreach ($sqlStatements as $sql) {
            try {
                $pdo->exec($sql);
                echo "OK: " . substr($sql, 0, 60) . "...\n";
            } catch (PDOException $e) {
                // Ignorar errores de duplicado (ya existe)
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "SKIP (ya existe): " . substr($sql, 0, 40) . "...\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

    } catch (PDOException $e) {
        echo "ERROR conexion {$envName}: " . $e->getMessage() . "\n";
    }
}

echo "\n========== COMPLETADO ==========\n";
```

### Ejecucion

```bash
# Desde la raiz del proyecto
php app/SQL/ejecutar_{nombre}.php
```

### Reglas para Migraciones

1. **NUNCA** ejecutar solo en LOCAL o solo en PRODUCCION
2. **SIEMPRE** usar el patron de doble conexion
3. **SIEMPRE** manejar errores de duplicado (ya existe)
4. **SIEMPRE** mostrar resultado por consola
5. Para PRODUCCION, SSL es **OBLIGATORIO**
6. El archivo debe quedar en `app/SQL/` para referencia futura
7. Nombrar archivos descriptivamente: `ejecutar_agregar_plantilla_[documento].php`

---

## CHECKLIST RAPIDO PARA NUEVO DOCUMENTO

```
[ ] BLOQUE CONSULTOR
    [ ] Controlador creado/actualizado
    [ ] Vista previa creada
    [ ] Rutas agregadas
    [ ] Boton en carpeta.php (si aplica)

[ ] BLOQUE CLIENTE
    [ ] Mapeo en mapearPlantillaATipoDocumento()

[ ] BLOQUE BASE DE DATOS
    [ ] Script de migracion creado
    [ ] Ejecutado en LOCAL
    [ ] Ejecutado en PRODUCCION

[ ] VERIFICACION
    [ ] Consultor puede crear documento
    [ ] Consultor puede ver vista previa
    [ ] Consultor puede exportar PDF
    [ ] Consultor puede exportar Word
    [ ] Consultor puede solicitar firmas
    [ ] Cliente ve documento aprobado/firmado
    [ ] Cliente puede descargar PDF
    [ ] Negritas y formato se renderizan correctamente en PDF/Word
```

---

## LECCIONES APRENDIDAS (ERRORES COMUNES)

### 1. Collation de Base de Datos

**Problema:** Error `Illegal mix of collations` al usar LIKE o comparar strings entre tablas.

**Solucion:** La base de datos debe tener collation unificado `utf8mb4_general_ci`. Si hay errores de collation:

```bash
# Ejecutar script de unificacion
php app/SQL/ejecutar_unificar_collation.php
```

**Regla:** NUNCA agregar `COLLATE` manualmente en queries de CodeIgniter. Unificar la BD es la solucion permanente.

---

### 2. HTML en Exportacion PDF/Word (Negritas no se renderizan)

**Problema:** Los tags `<strong>` aparecen como texto literal en PDF/Word: `<strong>Nombre</strong>` en vez de **Nombre**.

**Causa:** Las funciones `convertirMarkdownAHtml()` y `convertirMarkdownAHtmlPdf()` en los templates escapan TODO el HTML con `htmlspecialchars()`.

**Solucion:** Los templates ya estan corregidos para preservar tags HTML existentes. Si creas contenido con HTML:

```php
// CORRECTO - El contenido puede tener HTML
$texto = "<strong>{$nombre}</strong> con cedula <strong>{$cedula}</strong>";

// Los templates PDF/Word preservaran los tags <strong>, <b>, <em>, <i>
```

**Archivos corregidos:**
- `app/Views/documentos_sst/pdf_template.php` - funcion `convertirMarkdownAHtmlPdf()`
- `app/Views/documentos_sst/word_template.php` - funcion `convertirMarkdownAHtml()`

---

### 3. Nombres de Campos en Tablas

**Tabla `tbl_cliente_contexto_sst` (contexto):**
```php
$contexto['representante_legal_nombre']  // Nombre del rep. legal
$contexto['representante_legal_cedula']  // Cedula del rep. legal
$contexto['id_consultor_responsable']    // ID del consultor asignado
$contexto['estandares_aplicables']       // 7, 21 o 60
$contexto['delegado_sst_nombre']         // Si aplica
$contexto['delegado_sst_cargo']          // Si aplica
```

**Tabla `tbl_consultor` (consultor):**
```php
$consultor['nombre_consultor']   // Nombre completo
$consultor['cedula_consultor']   // Documento de identidad
$consultor['numero_licencia']    // Licencia SST (incluye fecha, ej: "4241 de 19/08/2022")
$consultor['firma_consultor']    // Ruta imagen de firma
```

**ERROR COMUN:** Usar `licencia_sst` en vez de `numero_licencia`.

---

### 4. Manejo de Stored Procedures en CodeIgniter 4

**Problema:** Error `Call to undefined method Result::close()` al llamar SP.

**Solucion:** CodeIgniter 4 no tiene metodo `close()`. Usar este patron:

```php
protected function generarCodigo(int $idCliente): string
{
    $query = $this->db->query(
        "CALL sp_generar_codigo_documento(?, ?, ?, @codigo)",
        [$idCliente, self::CODIGO_TIPO, self::CODIGO_TEMA]
    );
    $query->getResult();

    // Liberar resultados del SP para evitar "commands out of sync"
    if (method_exists($query, 'freeResult')) {
        $query->freeResult();
    }
    while ($this->db->connID->next_result()) {
        $this->db->connID->store_result();
    }

    $result = $this->db->query("SELECT @codigo as codigo")->getRow();
    return $result->codigo ?? (self::CODIGO_TIPO . '-' . self::CODIGO_TEMA . '-001');
}
```

---

### 5. Titulo del Documento en Templates PDF/Word

**Problema:** El PDF/Word muestra "DOCUMENTO SST" en vez del titulo real.

**Causa:** El template usa `$contenido['titulo']` pero no todos los documentos lo incluyen en el JSON.

**Solucion:** Siempre usar fallback chain:

```php
// En pdf_template.php y word_template.php
<?= esc(strtoupper($contenido['titulo'] ?? $documento['titulo'] ?? 'DOCUMENTO SST')) ?>
```

**Regla:** Si el documento PATRON B no incluye 'titulo' en el JSON de contenido, el template usara `$documento['titulo']` de la tabla.

---

### 6. Busqueda de Consultor (Fallback Chain)

**Problema:** El documento no muestra datos del consultor.

**Solucion:** Buscar en multiples fuentes con fallback:

```php
// Obtener datos del consultor asignado (responsable SST)
$consultor = null;
$consultorModel = new ConsultantModel();

// 1. Primero: id_consultor_responsable del contexto
$idConsultor = $contexto['id_consultor_responsable'] ?? null;
if ($idConsultor) {
    $consultor = $consultorModel->find($idConsultor);
}

// 2. Fallback: buscar consultor asignado al cliente en tbl_consultor
if (!$consultor) {
    $consultor = $consultorModel->where('id_cliente', $idCliente)->first();
}

// 3. Fallback: id_consultor del cliente
if (!$consultor && !empty($cliente['id_consultor'])) {
    $consultor = $consultorModel->find($cliente['id_consultor']);
}
```

---

### 7. URLs de Navegacion (Boton Volver)

**Problema:** El boton "Volver" lleva a error 404.

**Solucion:** Usar URLs simples y probadas:

```php
// CORRECTO - URL simple
<a href="<?= base_url('documentacion/' . $documento['id_cliente']) ?>">
    Volver a Documentacion
</a>

// INCORRECTO - URL compleja que puede fallar
<a href="<?= base_url('documentacion/carpeta/' . $documento['id_carpeta']) ?>">
```

---

### 8. Vista Web vs Exportacion (Escapado de HTML)

**En la vista web (.php):** NO escapar contenido que ya tiene HTML:
```php
<!-- CORRECTO - Renderiza HTML -->
<?= $seccion['contenido'] ?>

<!-- INCORRECTO - Escapa los tags -->
<?= esc($seccion['contenido']) ?>
```

**En PDF/Word:** Los templates ya manejan esto automaticamente.

---

### 9. Testing de Exportaciones

**SIEMPRE probar AMBOS formatos despues de crear un documento:**

1. **PDF:** Verificar que negritas, tablas y formato se renderizan
2. **Word:** Verificar lo mismo (Word usa template diferente)
3. **Titulo:** Verificar que aparece el nombre correcto en el encabezado
4. **Firmas:** Verificar que la seccion de firmas tiene los datos correctos

---

### 10. Importacion de Modelos (Case Sensitivity)

**Problema:** Error `Class not found` por nombre incorrecto del modelo.

**Regla:** El nombre del `use` debe coincidir EXACTAMENTE con el archivo:

```php
// Si el archivo es ClienteContextoSstModel.php
use App\Models\ClienteContextoSstModel;  // CORRECTO

// NO usar:
use App\Models\ClienteContextoSSTModel;  // INCORRECTO (SST vs Sst)
```

---

### 11. Regeneracion de Documentos con Edicion Directa

**Problema:** El usuario edita datos en un formulario pero el documento regenerado sigue mostrando los datos antiguos.

**Causa:** El modal de regeneracion solo MOSTRABA los datos pero NO los editaba. El usuario creia que estaba cambiando datos, pero la fuente de datos (tbl_cliente_contexto_sst) no se actualizaba.

**Solucion:** Implementar edicion directa en el modal de regeneracion:

```php
// En el controlador regenerar(), ANTES de reconstruir contenido:

// 1. Recibir datos editados del formulario
$nuevoRepLegalNombre = $this->request->getPost('representante_legal_nombre');
$nuevoRepLegalCedula = $this->request->getPost('representante_legal_cedula');
$nuevoIdConsultor = $this->request->getPost('id_consultor_responsable');

// 2. Actualizar tbl_cliente_contexto_sst si hay cambios
$datosActualizar = [];
if ($nuevoRepLegalNombre && $nuevoRepLegalNombre !== ($contexto['representante_legal_nombre'] ?? '')) {
    $datosActualizar['representante_legal_nombre'] = $nuevoRepLegalNombre;
}
if (!empty($datosActualizar)) {
    $this->db->table('tbl_cliente_contexto_sst')
        ->where('id_cliente', $idCliente)
        ->update($datosActualizar);

    // 3. Recargar contexto actualizado
    $contexto = $contextoModel->getByCliente($idCliente);
}

// 4. AHORA construir contenido con datos frescos
$nuevoContenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);
```

**Regla:** Siempre que el usuario pueda "actualizar" un documento, asegurar que:
1. El formulario tenga campos EDITABLES (no solo informativos)
2. El controlador ACTUALICE la fuente de datos antes de regenerar
3. Se RECARGUE el contexto despues de actualizar

---

### 12. Campos ENUM en MySQL - Fallo Silencioso

**Problema:** Al intentar actualizar un campo ENUM con un valor que no existe en la definicion, MySQL falla SILENCIOSAMENTE (retorna 0 filas afectadas) sin lanzar error.

**Ejemplo del error:**
```php
// El campo 'estado' tiene ENUM('vigente', 'obsoleto')
$pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE id_version = 9");
// Resultado: 0 filas afectadas, pero NO hay error
// El campo queda VACIO en vez de actualizarse
```

**Solucion:** SIEMPRE verificar que los valores ENUM incluyan todos los estados necesarios ANTES de usarlos:

```sql
-- Verificar estructura actual
SHOW COLUMNS FROM tbl_doc_versiones_sst WHERE Field = 'estado';

-- Si falta un valor, alterar la tabla
ALTER TABLE tbl_doc_versiones_sst
MODIFY COLUMN estado ENUM('vigente', 'obsoleto', 'historico', 'pendiente_firma')
NOT NULL DEFAULT 'vigente';
```

**Regla:** Cuando agregues nuevos estados a un flujo de trabajo:
1. Verificar que el ENUM de la columna los incluya
2. Si no existen, ejecutar ALTER TABLE antes de usar los nuevos valores
3. MySQL NO lanza error al intentar insertar valor invalido en ENUM - falla silenciosamente

---

### 13. Estados de Versiones de Documentos

**Flujo correcto de estados en `tbl_doc_versiones_sst`:**

| Estado | Descripcion | Cuando se usa |
|--------|-------------|---------------|
| `pendiente_firma` | Version recien creada/regenerada | Al crear nueva version, antes de firmar |
| `vigente` | Version activa actual | Despues de firmar o aprobar |
| `historico` | Version anterior reemplazada | Al crear nueva version, la anterior pasa a historico |
| `obsoleto` | Version descartada/invalida | Cuando se invalida manualmente |

**Codigo para regenerar documento con estados correctos:**

```php
// 1. Marcar version anterior como historico
$this->db->table('tbl_doc_versiones_sst')
    ->where('id_documento', $documento['id_documento'])
    ->where('estado', 'vigente')
    ->update(['estado' => 'historico']);

// 2. Crear nueva version en pendiente_firma
$this->db->table('tbl_doc_versiones_sst')->insert([
    'id_documento' => $documento['id_documento'],
    'version_texto' => $nuevaVersion,
    'estado' => 'pendiente_firma',  // NO 'vigente' directamente
    // ... otros campos
]);

// 3. Actualizar documento principal
$this->db->table('tbl_documentos_sst')
    ->where('id_documento', $documento['id_documento'])
    ->update([
        'estado' => 'pendiente_firma',
        'version' => $nuevaVersion
    ]);
```

**Regla:** Un documento regenerado NUNCA debe quedar en estado 'vigente' inmediatamente. Debe pasar por 'pendiente_firma' hasta que se firme.

---

### 14. Manejo de Estados Vacios en Vistas

**Problema:** La columna Estado en tablas de historial muestra valores vacios cuando el campo no tiene valor o tiene NULL.

**Solucion en el controlador:** Asignar valores por defecto al cargar versiones:

```php
// Al obtener versiones del documento
$versiones = $db->table('tbl_doc_versiones_sst')
    ->select('id_version, version_texto, estado, ...')
    ->where('id_documento', $idDocumento)
    ->orderBy('id_version', 'DESC')
    ->get()->getResultArray();

// Asignar estado por defecto a versiones que no lo tengan
foreach ($versiones as $idx => &$ver) {
    if (empty($ver['estado'])) {
        // La version mas reciente (primera en array) es vigente, las demas historicas
        $ver['estado'] = ($idx === 0) ? 'vigente' : 'historico';
    }
}
unset($ver);
```

**Solucion en la vista:** Usar operador null coalescing y match para mostrar valores legibles:

```php
<?php
$estadoVer = $ver['estado'] ?? 'historico';
$estadoVerBadge = match($estadoVer) {
    'vigente' => 'bg-success',
    'pendiente_firma' => 'bg-info',
    'historico' => 'bg-secondary',
    default => 'bg-secondary'
};
$estadoVerTexto = match($estadoVer) {
    'vigente' => 'Vigente',
    'pendiente_firma' => 'Pendiente firma',
    'historico' => 'Historico',
    default => ucfirst(str_replace('_', ' ', $estadoVer))
};
?>
<span class="badge <?= $estadoVerBadge ?>"><?= $estadoVerTexto ?></span>
```

**Regla:** NUNCA confiar en que un campo tendra valor. Siempre usar fallbacks tanto en controlador como en vista

---

### 15. Case Sensitivity en Linux (Produccion) vs Windows (Local)

**Problema:** Error `Class not found` solo en produccion pero funciona en local.

**Causa:** Windows es case-insensitive, Linux es case-sensitive. El archivo `ClienteContextoSstModel.php` funcionaba con import `ClienteContextoSSTModel` en Windows pero fallaba en Linux.

**Solucion:** El nombre del `use` debe coincidir EXACTAMENTE con el nombre del archivo:

```php
// Archivo: ClienteContextoSstModel.php

// CORRECTO
use App\Models\ClienteContextoSstModel;

// INCORRECTO - Falla en Linux
use App\Models\ClienteContextoSSTModel;  // SST vs Sst
```

**Regla:** SIEMPRE verificar que el case del import coincida con el nombre del archivo. Buscar con:
```bash
# Buscar archivos con nombre similar
ls -la app/Models/ | grep -i contexto
```

**Archivos afectados tipicamente:**
- Controladores que importan modelos
- Servicios que importan modelos
- Cualquier clase con acronimos (SST, SG, PDF, etc.)

---

### 16. Error de Doble COLLATE en Queries

**Problema:** Error SQL `syntax error near 'COLLATE ... COLLATE'`.

**Causa:** Poner COLLATE dos veces en la misma expresion:

```php
// INCORRECTO - Doble COLLATE
->where("campo COLLATE utf8mb4_general_ci LIKE 'valor%' COLLATE utf8mb4_general_ci")

// CORRECTO - Solo un COLLATE
->where("campo LIKE 'valor%' COLLATE utf8mb4_general_ci", null, false)
```

**Regla:** COLLATE solo va UNA vez en la expresion, idealmente despues del valor de comparacion.

---

### 17. Flujo Correcto de Documentos: Generar → Firmar → Aprobar

**Problema:** El flujo anterior era Generar → Aprobar → Firmar, lo cual no tenia sentido porque si el cliente no aprobaba al firmar, la version ya estaba creada.

**Solucion - Flujo correcto:**

```
1. Generar documento (secciones con IA o automatico)
2. Aprobar secciones (marcar cada seccion como lista)
3. Enviar a Firmas (el cliente revisa y firma)
4. Aprobacion automatica (al completarse todas las firmas, se crea la version)
```

**Implementacion:**

En `generar_con_ia.php`, cambiar el boton segun estado:
```php
<?php if ($estadoDoc === 'firmado'): ?>
    <!-- Documento firmado y aprobado -->
    <a href="firma/estado/<?= $idDocumento ?>">Ver Firmas</a>
<?php elseif ($estadoDoc === 'pendiente_firma'): ?>
    <!-- Esperando firmas -->
    <a href="firma/estado/<?= $idDocumento ?>">Estado Firmas</a>
<?php elseif ($todasSeccionesListas && $idDocumento): ?>
    <!-- Listo para firmas -->
    <a href="firma/solicitar/<?= $idDocumento ?>">Enviar a Firmas</a>
<?php endif; ?>
```

En `FirmaElectronicaController.php`, al completar todas las firmas:
```php
if ($this->firmaModel->firmasCompletas($idDocumento)) {
    // Cambiar estado a firmado
    $this->db->table('tbl_documentos_sst')
        ->where('id_documento', $idDocumento)
        ->update(['estado' => 'firmado']);

    // Crear version automaticamente
    $this->aprobarDocumentoAutomatico($idDocumento);
}
```

**Regla:** El cliente SIEMPRE debe revisar y firmar ANTES de que el documento quede oficialmente aprobado.

---

### 18. Tamano de Imagenes de Firma en PDF

**Problema:** Las firmas se ven muy pequenas en el PDF.

**Solucion:** Aumentar el tamano maximo de las imagenes de firma:

```php
// ANTES (muy pequeno)
style="max-height: 40px; max-width: 120px;"

// DESPUES (+40%)
style="max-height: 56px; max-width: 168px;"
```

**Archivos a modificar:**
- `app/Views/documentos_sst/pdf_template.php`
- `app/Views/documentos_sst/programa_capacitacion.php`
- `app/Views/documentos_sst/asignacion_responsable.php`

**Tamaños recomendados:**
| Firmantes | Altura | Ancho |
|-----------|--------|-------|
| 2 firmantes | 56px | 168px |
| 3 firmantes | 49px | 140px |

---

### 19. Calidad de Firmas Electronicas (Canvas)

**Problema:** La firma dibujada por el cliente se ve pixelada o de baja calidad en el PDF.

**Causas:**
1. El canvas no usa `devicePixelRatio` para pantallas de alta densidad (retina)
2. La firma se exporta con todo el espacio vacio alrededor
3. El trazo es muy delgado

**Solucion en `firmar.php`:**

```javascript
// 1. Usar alta resolucion
let dpr = window.devicePixelRatio || 1;
canvas.width = rect.width * dpr;
canvas.height = 200 * dpr;
ctx.scale(dpr, dpr);

// 2. Trazo mas grueso
ctx.lineWidth = 3;  // Era 2

// 3. Al exportar, recortar y optimizar
function exportarFirmaOptimizada() {
    // Encontrar bounding box del dibujo
    // Recortar solo el area con contenido
    // Escalar a tamaño fijo (150px altura)
    return tempCanvas.toDataURL('image/png');
}

// 4. Para imagenes subidas, redimensionar
function optimizarImagenFirma(dataUrl, callback) {
    // Redimensionar a max 150px altura, 400px ancho
    // Mantener proporcion
}
```

**Regla:** Las firmas nuevas deben procesarse con alta resolucion. Las firmas existentes de baja calidad requieren que el cliente vuelva a firmar.

---

### 20. Navegacion - Boton Volver

**Problema:** El boton "Volver" lleva a una pagina incorrecta o genera error.

**Solucion:** Definir claramente la jerarquia de navegacion:

```php
// Desde responsables-sst/{id} -> volver a contexto/{id}
<a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>">Volver</a>

// Desde documento -> volver a documentacion/{id}
<a href="<?= base_url('documentacion/' . $documento['id_cliente']) ?>">Volver</a>
```

**Jerarquia recomendada:**
```
contexto/{id} (ficha del cliente)
├── responsables-sst/{id}
├── documentacion/{id}
│   ├── documentos/generar/programa_capacitacion/{id}
│   └── documentos-sst/{id}/programa-capacitacion/{anio}
```

**Regla:** El boton "Volver" debe llevar al nivel inmediatamente superior en la jerarquia, NO a una pagina aleatoria.

---

### 21. Botones de Acciones en Vista del Documento

**Problema:** Confusion sobre cuando mostrar cada boton (Editar, Firmar, Aprobar, Ver PDF).

**Matriz de botones segun estado:**

| Estado | Editar | Enviar a Firmas | Estado Firmas | Ver PDF |
|--------|--------|-----------------|---------------|---------|
| borrador | Si | No | No | Si (preview) |
| en_revision | Si | No | No | Si |
| secciones_listas | No | **Si** | No | Si |
| pendiente_firma | No | No | **Si** | Si |
| firmado | No | No | Ver Firmas | Si (oficial) |
| aprobado | No | No | - | Si (oficial) |

**Codigo en vista:**
```php
<?php
$estado = $documento['estado'] ?? 'borrador';
$seccionesListas = $todasSeccionesListas ?? false;
?>

<?php if ($estado === 'firmado'): ?>
    <span class="badge bg-success">Firmado y Aprobado</span>
    <a href="firma/estado/<?= $id ?>">Ver Firmas</a>
<?php elseif ($estado === 'pendiente_firma'): ?>
    <span class="badge bg-warning">Pendiente Firma</span>
    <a href="firma/estado/<?= $id ?>">Estado Firmas</a>
<?php elseif ($seccionesListas): ?>
    <a href="firma/solicitar/<?= $id ?>" class="btn btn-success">Enviar a Firmas</a>
<?php else: ?>
    <span class="badge bg-secondary">En edicion</span>
<?php endif; ?>
```

---

### 22. Configurar Acceso en carpeta.php para Nuevos Documentos

**Problema:** Creaste el controlador, la vista y las rutas, pero al entrar a la carpeta no aparece el botón para crear el documento.

**Causa:** La vista `carpeta.php` usa `$tipoCarpetaFases` para decidir qué botones mostrar. Si no configuras el tipo de carpeta, no aparece nada.

**Solucion - 3 pasos obligatorios:**

**Paso 1: Agregar detección en DocumentacionController.php**

```php
// En determinarTipoCarpetaFases()
protected function determinarTipoCarpetaFases(array $carpeta): ?string
{
    $nombre = strtolower($carpeta['nombre'] ?? '');
    $codigo = strtolower($carpeta['codigo'] ?? '');

    // ... otros tipos existentes ...

    // AGREGAR: Detectar tu nueva carpeta por código o nombre
    if ($codigo === '1.1.2' || strpos($nombre, 'responsabilidades') !== false) {
        return 'responsabilidades_sgsst';  // <-- Tu nuevo tipo
    }

    return null;
}
```

**Paso 2: Agregar tipo al array de documentos SST**

```php
// En carpeta() de DocumentacionController.php - buscar la línea con in_array
if (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst'])) {
    // ^^^^ AGREGAR tu nuevo tipo aquí
```

**Paso 3: Agregar condición en carpeta.php para el botón**

```php
// En carpeta.php - sección de botones (~línea 280)
<?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsabilidades_sgsst'): ?>
    <!-- Tu botón o dropdown aquí -->
    <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-tu-documento') ?>" method="post">
        <button type="submit" class="btn btn-success">Generar Documento</button>
    </form>
<?php elseif ...
```

**Regla:** Sin estos 3 pasos, el documento NO aparecerá en la interfaz aunque el controlador y rutas existan.

---

### 23. Múltiples Documentos en Una Carpeta (Patrón Dropdown)

**Problema:** Una carpeta contiene varios documentos relacionados (ej: 1.1.2 tiene 4 documentos de responsabilidades).

**Solucion:** Usar dropdown en lugar de botón único.

**Paso 1: En DocumentacionController - obtener todos los tipos**

```php
// En carpeta() - buscar documentos de múltiples tipos
if ($tipoCarpetaFases === 'responsabilidades_sgsst') {
    $queryDocs->whereIn('tipo_documento', [
        'responsabilidades_rep_legal_sgsst',
        'responsabilidades_responsable_sgsst',
        'responsabilidades_trabajadores_sgsst',
        'responsabilidades_vigia_sgsst'
    ]);
}
```

**Paso 2: Si necesitas filtrar por nivel de estándares, pasar contextoCliente**

```php
// En carpeta() - obtener contexto del cliente
$contextoCliente = null;
if ($tipoCarpetaFases === 'responsabilidades_sgsst') {
    $db = \Config\Database::connect();
    $contextoCliente = $db->table('tbl_cliente_contexto_sst')
        ->where('id_cliente', $cliente['id_cliente'])
        ->get()
        ->getRowArray();
}

// Pasar a la vista
return view('documentacion/carpeta', [
    // ... otros datos ...
    'contextoCliente' => $contextoCliente ?? null
]);
```

**Paso 3: En carpeta.php - crear dropdown con filtro**

```php
<?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'responsabilidades_sgsst'): ?>
    <?php
    $nivelEstandares = $contextoCliente['estandares_aplicables'] ?? 60;
    $docsExistentesTipos = [];
    if (!empty($documentosSSTAprobados)) {
        foreach ($documentosSSTAprobados as $d) {
            if ($d['anio'] == date('Y')) {
                $docsExistentesTipos[$d['tipo_documento']] = true;
            }
        }
    }
    ?>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <?php if (!isset($docsExistentesTipos['tipo_doc_1'])): ?>
            <li>
                <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-doc-1') ?>" method="post">
                    <button type="submit" class="dropdown-item">Documento 1</button>
                </form>
            </li>
            <?php endif; ?>

            <!-- Documento condicional (solo 7 estándares) -->
            <?php if ($nivelEstandares <= 7 && !isset($docsExistentesTipos['tipo_doc_vigia'])): ?>
            <li>
                <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-vigia') ?>" method="post">
                    <button type="submit" class="dropdown-item">Vigía SST (Solo 7 est.)</button>
                </form>
            </li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>
```

**Paso 4: Configurar URLs de visualización para cada tipo**

```php
// En carpeta.php - sección de acciones de la tabla
$mapaRutas = [
    'responsabilidades_rep_legal_sgsst' => 'responsabilidades-rep-legal/' . $docSST['id_documento'],
    'responsabilidades_responsable_sgsst' => 'responsabilidades-responsable-sst/' . $docSST['id_documento'],
    // ... agregar todos los tipos
];

if (isset($mapaRutas[$tipoDoc])) {
    $urlVer = base_url('documentos-sst/' . $cliente['id_cliente'] . '/' . $mapaRutas[$tipoDoc]);
}
```

**Regla:** Cuando una carpeta tiene múltiples documentos:
1. Usar `whereIn()` para obtener todos los tipos
2. Pasar `$contextoCliente` si hay filtros por estándares
3. Crear dropdown con verificación de documentos existentes
4. Configurar mapa de URLs para cada tipo de documento

---

### 24. Checklist Ampliado para Documentos con Acceso en Carpeta

```
[ ] BLOQUE CONSULTOR
    [ ] Controlador creado/actualizado
    [ ] Vista previa creada
    [ ] Rutas agregadas en Routes.php

[ ] BLOQUE ACCESO EN CARPETA (NUEVO)
    [ ] determinarTipoCarpetaFases() - agregar detección del tipo
    [ ] in_array() en carpeta() - agregar tipo para obtener documentos
    [ ] carpeta.php - agregar condición para botón/dropdown
    [ ] Si múltiples docs: configurar dropdown con filtros
    [ ] Si filtro por estándares: pasar contextoCliente
    [ ] Configurar mapaRutas para URLs de visualización

[ ] BLOQUE CLIENTE
    [ ] Mapeo en mapearPlantillaATipoDocumento()

[ ] BLOQUE BASE DE DATOS
    [ ] Script de migracion creado
    [ ] Ejecutado en LOCAL
    [ ] Ejecutado en PRODUCCION
```

---

## RESUMEN RAPIDO DE ERRORES COMUNES

| Error | Causa | Solucion Rapida |
|-------|-------|-----------------|
| Class not found en produccion | Case sensitivity | Verificar nombre exacto del archivo |
| Syntax error COLLATE | Doble COLLATE | Usar solo un COLLATE |
| Firma muy pequeña | max-height bajo | Aumentar a 56px/168px |
| Firma pixelada | Sin devicePixelRatio | Agregar dpr al canvas |
| Boton volver error | URL incorrecta | Usar base_url + id_cliente |
| Aprobado sin firmas | Flujo incorrecto | Firmar ANTES de aprobar |
| **Botón no aparece en carpeta** | **Falta tipo en determinarTipoCarpetaFases** | **Agregar detección + in_array + condición en vista** |
| **Dropdown vacío** | **Falta whereIn para múltiples tipos** | **Agregar todos los tipo_documento al whereIn** |
| **Documento Vigía aparece siempre** | **Falta filtro por estándares** | **Pasar contextoCliente y verificar nivelEstandares <= 7** |
