# Sistema de Versionamiento de Documentos SST

## Guia Completa y Transversal

---

## 1. PROBLEMA ORIGINAL

### Discrepancias Detectadas

Antes de la estandarizacion, el sistema tenia **3 flujos diferentes** de versionamiento:

| Tipo Documento | Flujo Anterior | Problema |
|----------------|----------------|----------|
| Documentos con IA (programa_capacitacion, procedimiento_control_documental) | `aprobarDocumento()` en controlador | Logica duplicada, SQL directo |
| Documentos cargados (planilla_afiliacion_srl, soportes) | Directo a aprobado, sin historial real | No permitian nuevas versiones |
| Responsabilidades (rep_legal, responsable_sst) | Modal propio con logica diferente | Inconsistente con los demas |

### Sintomas del Problema

1. **Modales diferentes**: Cada documento tenia su propio modal con campos distintos
2. **Nomenclatura inconsistente**: Algunos mostraban "v1.0", otros "Version 1", otros "001"
3. **Historial incompleto**: Documentos cargados no guardaban snapshot del contenido
4. **Codigo duplicado**: La logica de versionamiento estaba repetida en multiples controladores

---

## 2. SOLUCION IMPLEMENTADA

### Arquitectura Nueva

```
┌─────────────────────────────────────────────────────────────┐
│                   SERVICIO CENTRALIZADO                      │
│              DocumentoVersionService.php                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  METODOS PUBLICOS:                                          │
│  ├── iniciarNuevaVersion()   → Pone doc en borrador        │
│  ├── aprobarVersion()        → Crea version en historial   │
│  ├── restaurarVersion()      → Restaura version anterior   │
│  ├── cancelarNuevaVersion()  → Cancela edicion             │
│  ├── obtenerHistorial()      → Lista todas las versiones   │
│  ├── obtenerVersionVigente() → Obtiene version actual      │
│  └── compararVersiones()     → Compara dos versiones       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   COMPONENTES DE VISTA                       │
├─────────────────────────────────────────────────────────────┤
│  modal_nueva_version.php      → Modal estandar             │
│  modal_historial_versiones.php → Historial con acciones    │
└─────────────────────────────────────────────────────────────┘
```

### Archivos Clave

| Archivo | Ubicacion | Funcion |
|---------|-----------|---------|
| `DocumentoVersionService.php` | `app/Services/` | Toda la logica de versiones |
| `modal_nueva_version.php` | `app/Views/documentos_sst/_components/` | Modal reutilizable |
| `modal_historial_versiones.php` | `app/Views/documentos_sst/_components/` | Historial reutilizable |

### Cambios en Base de Datos

**Tabla `tbl_doc_versiones_sst`** - Se agrego:
```sql
tipo_documento VARCHAR(100) NULL  -- Para consultas sin JOIN
```

**Tabla `tbl_documentos_sst`** - Se agrego:
```sql
tipo_cambio_pendiente ENUM('mayor', 'menor') NULL  -- Guarda tipo mientras esta en borrador
```

**Indices creados:**
```sql
idx_tipo_documento (tipo_documento)
idx_doc_estado (id_documento, estado)
```

---

## 3. FLUJO ESTANDARIZADO

### Diagrama Completo

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│   DOCUMENTO NUEVO                                           │
│   estado: 'borrador'                                        │
│   version: NULL                                             │
│                                                             │
│              ↓ (usuario completa contenido)                 │
│                                                             │
│   PRIMERA APROBACION                                        │
│   ├── versionService->aprobarVersion()                      │
│   ├── estado: 'aprobado'                                    │
│   ├── version: 1                                            │
│   └── Se crea en tbl_doc_versiones_sst:                    │
│       ├── version_texto: '1.0'                              │
│       ├── tipo_cambio: 'mayor'                              │
│       ├── descripcion_cambio: 'Elaboracion inicial'         │
│       ├── contenido_snapshot: JSON del contenido            │
│       └── estado: 'vigente'                                 │
│                                                             │
│              ↓ (usuario quiere editar)                      │
│                                                             │
│   INICIAR NUEVA VERSION                                     │
│   ├── versionService->iniciarNuevaVersion()                 │
│   ├── estado: 'borrador'                                    │
│   ├── motivo_version: 'descripcion del cambio'              │
│   └── tipo_cambio_pendiente: 'menor' o 'mayor'              │
│                                                             │
│              ↓ (usuario edita)                              │
│                                                             │
│   APROBAR NUEVA VERSION                                     │
│   ├── versionService->aprobarVersion()                      │
│   ├── estado: 'aprobado'                                    │
│   ├── Version anterior → estado: 'obsoleto'                 │
│   └── Nueva version → estado: 'vigente'                     │
│       ├── version_texto: '1.1' (menor) o '2.0' (mayor)      │
│       └── contenido_snapshot: JSON actualizado              │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Tipos de Cambio

| Tipo | Incremento | Ejemplo | Cuando Usar |
|------|------------|---------|-------------|
| **MENOR** | Decimal | 1.0 → 1.1 → 1.2 | Correcciones, ajustes, actualizacion de datos |
| **MAYOR** | Entero | 1.0 → 2.0 → 3.0 | Restructuracion, nueva normativa, cambios significativos |

---

## 4. ERRORES QUE NO SE DEBEN COMETER

### ERROR 1: Crear logica de versionamiento en el controlador

❌ **MAL:**
```php
// En algun controlador
public function aprobarMiDocumento()
{
    // Calcular version manualmente
    $versionActual = (int)$documento['version'];
    $nuevaVersion = $versionActual + 1;

    // Insertar en tabla de versiones directamente
    $this->db->table('tbl_doc_versiones_sst')->insert([...]);

    // Actualizar documento
    $this->db->table('tbl_documentos_sst')->update([...]);
}
```

✅ **BIEN:**
```php
// En cualquier controlador
use App\Services\DocumentoVersionService;

public function aprobarMiDocumento()
{
    $versionService = new DocumentoVersionService();

    $resultado = $versionService->aprobarVersion(
        $idDocumento,
        $usuarioId,
        $usuarioNombre,
        $descripcionCambio,
        $tipoCambio
    );

    return $this->response->setJSON($resultado);
}
```

### ERROR 2: Crear modales de version personalizados

❌ **MAL:**
```php
<!-- Modal personalizado en alguna vista -->
<div class="modal" id="miModalVersion">
    <input name="version_nueva" value="2.0">
    <textarea name="motivo">...</textarea>
    <!-- Logica diferente al estandar -->
</div>
```

✅ **BIEN:**
```php
<!-- Usar el componente estandar -->
<?= view('documentos_sst/_components/modal_nueva_version', [
    'id_documento' => $documento['id_documento'],
    'version_actual' => $versionVigente['version_texto'] ?? '1.0',
    'tipo_documento' => $documento['tipo_documento']
]) ?>
```

### ERROR 3: No guardar snapshot del contenido

❌ **MAL:**
```php
// Crear version sin snapshot
$this->db->table('tbl_doc_versiones_sst')->insert([
    'id_documento' => $id,
    'version_texto' => '1.0',
    // FALTA: contenido_snapshot
]);
```

✅ **BIEN:**
El servicio `DocumentoVersionService` SIEMPRE guarda el snapshot automaticamente.

### ERROR 4: No marcar versiones anteriores como obsoletas

❌ **MAL:**
```php
// Crear nueva version sin marcar la anterior
$this->db->table('tbl_doc_versiones_sst')->insert([...]);
// Version anterior sigue como 'vigente' - INCONSISTENCIA
```

✅ **BIEN:**
El servicio marca automaticamente las versiones anteriores como 'obsoleto' antes de crear la nueva.

### ERROR 5: Omitir la descripcion del cambio

❌ **MAL:**
```php
// Aprobar sin descripcion
$versionService->aprobarVersion($id, $userId, $userName, '', 'menor');
```

✅ **BIEN:**
El servicio valida que la descripcion no este vacia y retorna error si lo esta.

### ERROR 6: No sincronizar tipo_documento en versiones

❌ **MAL:**
```php
// Crear version sin tipo_documento
$this->db->table('tbl_doc_versiones_sst')->insert([
    'id_documento' => $id,
    // FALTA: tipo_documento
]);
```

✅ **BIEN:**
El servicio incluye automaticamente el `tipo_documento` del documento padre.

---

## 5. COMO INTERVENIR UN DOCUMENTO QUE QUEDO POR FUERA

### Escenario A: Documento aprobado sin version en historial

**Diagnostico:**
```sql
-- Encontrar documentos aprobados sin version
SELECT d.id_documento, d.titulo, d.tipo_documento, d.estado, d.version
FROM tbl_documentos_sst d
LEFT JOIN tbl_doc_versiones_sst v ON d.id_documento = v.id_documento
WHERE d.estado = 'aprobado' AND v.id_version IS NULL;
```

**Solucion - Script PHP:**
```php
<?php
// app/SQL/reparar_documento_sin_version.php

$db = \Config\Database::connect();

// ID del documento a reparar
$idDocumento = 123; // CAMBIAR POR EL ID REAL

// Obtener documento
$documento = $db->table('tbl_documentos_sst')
    ->where('id_documento', $idDocumento)
    ->get()
    ->getRowArray();

if (!$documento) {
    die("Documento no encontrado");
}

// Crear version inicial
$db->table('tbl_doc_versiones_sst')->insert([
    'id_documento' => $idDocumento,
    'id_cliente' => $documento['id_cliente'],
    'tipo_documento' => $documento['tipo_documento'],
    'codigo' => $documento['codigo'],
    'titulo' => $documento['titulo'],
    'anio' => $documento['anio'],
    'version' => $documento['version'] ?? 1,
    'version_texto' => ($documento['version'] ?? 1) . '.0',
    'tipo_cambio' => 'mayor',
    'descripcion_cambio' => 'Elaboracion inicial del documento (reparado)',
    'contenido_snapshot' => $documento['contenido'],
    'estado' => 'vigente',
    'autorizado_por' => 'Sistema - Reparacion',
    'fecha_autorizacion' => $documento['fecha_aprobacion'] ?? date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s')
]);

echo "Version creada exitosamente para documento {$idDocumento}";
```

### Escenario B: Documento con version pero sin tipo_documento

**Diagnostico:**
```sql
-- Encontrar versiones sin tipo_documento
SELECT v.id_version, v.id_documento, d.tipo_documento
FROM tbl_doc_versiones_sst v
JOIN tbl_documentos_sst d ON v.id_documento = d.id_documento
WHERE v.tipo_documento IS NULL;
```

**Solucion - SQL directo:**
```sql
-- Sincronizar tipo_documento
UPDATE tbl_doc_versiones_sst v
INNER JOIN tbl_documentos_sst d ON v.id_documento = d.id_documento
SET v.tipo_documento = d.tipo_documento
WHERE v.tipo_documento IS NULL;
```

### Escenario C: Documento con multiples versiones 'vigente'

**Diagnostico:**
```sql
-- Encontrar documentos con mas de una version vigente
SELECT id_documento, COUNT(*) as vigentes
FROM tbl_doc_versiones_sst
WHERE estado = 'vigente'
GROUP BY id_documento
HAVING COUNT(*) > 1;
```

**Solucion - Script PHP:**
```php
<?php
// app/SQL/reparar_versiones_duplicadas.php

$db = \Config\Database::connect();

// Encontrar documentos con problema
$problematicos = $db->query("
    SELECT id_documento, COUNT(*) as vigentes
    FROM tbl_doc_versiones_sst
    WHERE estado = 'vigente'
    GROUP BY id_documento
    HAVING COUNT(*) > 1
")->getResultArray();

foreach ($problematicos as $doc) {
    $idDocumento = $doc['id_documento'];

    // Obtener todas las versiones vigentes ordenadas por fecha (la mas reciente primero)
    $versiones = $db->table('tbl_doc_versiones_sst')
        ->where('id_documento', $idDocumento)
        ->where('estado', 'vigente')
        ->orderBy('fecha_autorizacion', 'DESC')
        ->get()
        ->getResultArray();

    // La primera es la mas reciente, las demas deben ser obsoletas
    $primeraVez = true;
    foreach ($versiones as $version) {
        if ($primeraVez) {
            $primeraVez = false;
            continue; // Mantener la mas reciente como vigente
        }

        // Marcar como obsoleta
        $db->table('tbl_doc_versiones_sst')
            ->where('id_version', $version['id_version'])
            ->update(['estado' => 'obsoleto']);
    }

    echo "Documento {$idDocumento} reparado\n";
}
```

### Escenario D: Documento nuevo que no usa el servicio

**Situacion:** Se creo un nuevo tipo de documento y el desarrollador no uso el servicio.

**Solucion - Modificar el controlador:**

1. Agregar el use del servicio:
```php
use App\Services\DocumentoVersionService;
```

2. Agregar propiedad en la clase:
```php
protected DocumentoVersionService $versionService;
```

3. Inicializar en constructor:
```php
public function __construct()
{
    // ... otros servicios
    $this->versionService = new DocumentoVersionService();
}
```

4. Reemplazar logica de aprobacion:
```php
public function aprobar()
{
    $idDocumento = $this->request->getPost('id_documento');
    $descripcion = $this->request->getPost('descripcion_cambio');
    $tipoCambio = $this->request->getPost('tipo_cambio') ?? 'menor';

    $session = session();
    $usuarioId = $session->get('id_usuario');
    $usuarioNombre = $session->get('nombre_usuario');

    $resultado = $this->versionService->aprobarVersion(
        (int)$idDocumento,
        (int)$usuarioId,
        $usuarioNombre,
        $descripcion,
        $tipoCambio
    );

    return $this->response->setJSON($resultado);
}
```

5. Agregar el modal estandar en la vista:
```php
<?= view('documentos_sst/_components/modal_nueva_version', [
    'id_documento' => $documento['id_documento'],
    'version_actual' => $versionActual,
    'tipo_documento' => $documento['tipo_documento']
]) ?>
```

---

## 6. LISTA DE VERIFICACION PARA NUEVOS DOCUMENTOS

Antes de considerar completo un nuevo tipo de documento, verificar:

- [ ] El controlador usa `DocumentoVersionService`
- [ ] La vista incluye `modal_nueva_version.php`
- [ ] La vista incluye `modal_historial_versiones.php` (opcional pero recomendado)
- [ ] El endpoint de aprobacion usa `$versionService->aprobarVersion()`
- [ ] El endpoint de nueva version usa `$versionService->iniciarNuevaVersion()`
- [ ] El `tipo_documento` esta definido en la tabla `tbl_documentos_sst`
- [ ] Las rutas estan configuradas en `Routes.php`
- [ ] Se probaron los flujos: crear, aprobar, editar, aprobar nueva version

---

## 7. CONSULTAS UTILES

### Ver todas las versiones de un documento
```sql
SELECT
    v.id_version,
    v.version_texto,
    v.tipo_cambio,
    v.descripcion_cambio,
    v.estado,
    v.autorizado_por,
    v.fecha_autorizacion
FROM tbl_doc_versiones_sst v
WHERE v.id_documento = 123
ORDER BY v.fecha_autorizacion DESC;
```

### Ver documentos por tipo con su version actual
```sql
SELECT
    d.id_documento,
    d.titulo,
    d.tipo_documento,
    d.estado,
    d.version,
    v.version_texto,
    v.fecha_autorizacion
FROM tbl_documentos_sst d
LEFT JOIN tbl_doc_versiones_sst v ON d.id_documento = v.id_documento AND v.estado = 'vigente'
WHERE d.id_cliente = 11
ORDER BY d.tipo_documento, d.titulo;
```

### Estadisticas de versiones por cliente
```sql
SELECT
    c.nombre_empresa,
    COUNT(DISTINCT d.id_documento) as total_documentos,
    COUNT(v.id_version) as total_versiones,
    SUM(CASE WHEN v.estado = 'vigente' THEN 1 ELSE 0 END) as vigentes,
    SUM(CASE WHEN v.estado = 'obsoleto' THEN 1 ELSE 0 END) as obsoletas
FROM tbl_clientes c
JOIN tbl_documentos_sst d ON c.id_cliente = d.id_cliente
LEFT JOIN tbl_doc_versiones_sst v ON d.id_documento = v.id_documento
GROUP BY c.id_cliente, c.nombre_empresa;
```

### Buscar documentos con problemas de integridad
```sql
-- Documentos aprobados sin version
SELECT 'Sin version' as problema, d.*
FROM tbl_documentos_sst d
LEFT JOIN tbl_doc_versiones_sst v ON d.id_documento = v.id_documento
WHERE d.estado = 'aprobado' AND v.id_version IS NULL

UNION ALL

-- Versiones huerfanas
SELECT 'Version huerfana' as problema, v.*
FROM tbl_doc_versiones_sst v
LEFT JOIN tbl_documentos_sst d ON v.id_documento = d.id_documento
WHERE d.id_documento IS NULL

UNION ALL

-- Documentos con multiples vigentes
SELECT 'Multiples vigentes' as problema, v.*
FROM tbl_doc_versiones_sst v
WHERE v.id_documento IN (
    SELECT id_documento
    FROM tbl_doc_versiones_sst
    WHERE estado = 'vigente'
    GROUP BY id_documento
    HAVING COUNT(*) > 1
);
```

---

## 8. SCRIPTS DE MANTENIMIENTO

### Script: Verificar integridad del sistema de versiones
```php
<?php
// app/SQL/verificar_integridad_versiones.php

$db = \Config\Database::connect();
$problemas = [];

// 1. Documentos aprobados sin version
$sinVersion = $db->query("
    SELECT d.id_documento, d.titulo, d.tipo_documento
    FROM tbl_documentos_sst d
    LEFT JOIN tbl_doc_versiones_sst v ON d.id_documento = v.id_documento
    WHERE d.estado = 'aprobado' AND v.id_version IS NULL
")->getResultArray();

if (count($sinVersion) > 0) {
    $problemas[] = [
        'tipo' => 'DOCUMENTOS_SIN_VERSION',
        'cantidad' => count($sinVersion),
        'documentos' => $sinVersion
    ];
}

// 2. Versiones sin tipo_documento
$sinTipo = $db->query("
    SELECT v.id_version, v.id_documento, d.titulo
    FROM tbl_doc_versiones_sst v
    JOIN tbl_documentos_sst d ON v.id_documento = d.id_documento
    WHERE v.tipo_documento IS NULL
")->getResultArray();

if (count($sinTipo) > 0) {
    $problemas[] = [
        'tipo' => 'VERSIONES_SIN_TIPO',
        'cantidad' => count($sinTipo),
        'versiones' => $sinTipo
    ];
}

// 3. Documentos con multiples vigentes
$multiplesVigentes = $db->query("
    SELECT id_documento, COUNT(*) as vigentes
    FROM tbl_doc_versiones_sst
    WHERE estado = 'vigente'
    GROUP BY id_documento
    HAVING COUNT(*) > 1
")->getResultArray();

if (count($multiplesVigentes) > 0) {
    $problemas[] = [
        'tipo' => 'MULTIPLES_VIGENTES',
        'cantidad' => count($multiplesVigentes),
        'documentos' => $multiplesVigentes
    ];
}

// Resultado
if (count($problemas) === 0) {
    echo "✅ Sistema de versiones OK - Sin problemas detectados\n";
} else {
    echo "❌ Problemas encontrados:\n";
    print_r($problemas);
}
```

### Script: Reparar todos los problemas automaticamente
```php
<?php
// app/SQL/reparar_versiones_automatico.php

$db = \Config\Database::connect();
$reparaciones = [];

// 1. Sincronizar tipo_documento
$result = $db->query("
    UPDATE tbl_doc_versiones_sst v
    INNER JOIN tbl_documentos_sst d ON v.id_documento = d.id_documento
    SET v.tipo_documento = d.tipo_documento
    WHERE v.tipo_documento IS NULL
");
$reparaciones[] = "Sincronizados " . $db->affectedRows() . " tipo_documento";

// 2. Crear versiones faltantes para documentos aprobados
$sinVersion = $db->query("
    SELECT d.*
    FROM tbl_documentos_sst d
    LEFT JOIN tbl_doc_versiones_sst v ON d.id_documento = v.id_documento
    WHERE d.estado = 'aprobado' AND v.id_version IS NULL
")->getResultArray();

foreach ($sinVersion as $doc) {
    $db->table('tbl_doc_versiones_sst')->insert([
        'id_documento' => $doc['id_documento'],
        'id_cliente' => $doc['id_cliente'],
        'tipo_documento' => $doc['tipo_documento'],
        'codigo' => $doc['codigo'],
        'titulo' => $doc['titulo'],
        'anio' => $doc['anio'],
        'version' => $doc['version'] ?? 1,
        'version_texto' => ($doc['version'] ?? 1) . '.0',
        'tipo_cambio' => 'mayor',
        'descripcion_cambio' => 'Elaboracion inicial del documento (reparacion automatica)',
        'contenido_snapshot' => $doc['contenido'],
        'estado' => 'vigente',
        'autorizado_por' => 'Sistema - Reparacion',
        'fecha_autorizacion' => $doc['fecha_aprobacion'] ?? $doc['created_at'] ?? date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ]);
}
$reparaciones[] = "Creadas " . count($sinVersion) . " versiones faltantes";

// 3. Corregir multiples vigentes
$multiplesVigentes = $db->query("
    SELECT id_documento
    FROM tbl_doc_versiones_sst
    WHERE estado = 'vigente'
    GROUP BY id_documento
    HAVING COUNT(*) > 1
")->getResultArray();

foreach ($multiplesVigentes as $doc) {
    $versiones = $db->table('tbl_doc_versiones_sst')
        ->where('id_documento', $doc['id_documento'])
        ->where('estado', 'vigente')
        ->orderBy('fecha_autorizacion', 'DESC')
        ->get()
        ->getResultArray();

    $skip = true;
    foreach ($versiones as $v) {
        if ($skip) { $skip = false; continue; }
        $db->table('tbl_doc_versiones_sst')
            ->where('id_version', $v['id_version'])
            ->update(['estado' => 'obsoleto']);
    }
}
$reparaciones[] = "Corregidos " . count($multiplesVigentes) . " documentos con multiples vigentes";

echo "Reparaciones completadas:\n";
print_r($reparaciones);
```

---

## 9. ENDPOINTS DE LA API

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| POST | `/documentos-sst/iniciar-nueva-version` | DocumentosSSTController | Inicia edicion de nueva version |
| POST | `/documentos-sst/aprobar-documento` | DocumentosSSTController | Aprueba y crea version |
| POST | `/documentos-sst/restaurar-version` | DocumentosSSTController | Restaura version anterior |
| POST | `/documentos-sst/cancelar-nueva-version` | DocumentosSSTController | Cancela edicion |
| GET | `/documentos-sst/historial-versiones/{id}` | DocumentosSSTController | Obtiene historial |
| GET | `/sql-runner/estandarizar-versiones` | SqlRunnerController | Re-ejecuta migracion |

### Formato de Respuesta

**Exito:**
```json
{
    "success": true,
    "message": "Documento aprobado correctamente. Version 1.1",
    "data": {
        "id_documento": 123,
        "id_version": 456,
        "version": 1,
        "version_texto": "1.1",
        "tipo_cambio": "menor",
        "descripcion": "Actualizacion de datos",
        "fecha_aprobacion": "2026-02-05 10:30:00",
        "aprobado_por": "Usuario Admin"
    }
}
```

**Error:**
```json
{
    "success": false,
    "message": "La descripcion del cambio es obligatoria",
    "data": null
}
```

---

## 10. RESUMEN EJECUTIVO

### Lo que se hizo

1. **Servicio centralizado** (`DocumentoVersionService.php`) que maneja TODA la logica
2. **Componentes reutilizables** para modales de version e historial
3. **Migracion de BD** agregando campos y sincronizando datos
4. **Actualizacion del controlador principal** para usar el servicio

### Reglas de oro

1. **SIEMPRE** usar `DocumentoVersionService` para operaciones de version
2. **SIEMPRE** usar los componentes de vista estandar
3. **NUNCA** escribir SQL de versiones directamente en controladores
4. **NUNCA** crear modales de version personalizados
5. **SIEMPRE** incluir descripcion del cambio

### Donde buscar ayuda

- Servicio: `app/Services/DocumentoVersionService.php`
- Componentes: `app/Views/documentos_sst/_components/`
- Documentacion: `docs/MODULO_NUMERALES_SGSST/`
- Scripts SQL: `app/SQL/estandarizar_sistema_versiones.php`

---

**Fecha de creacion:** 2026-02-05
**Ultima actualizacion:** 2026-02-05
**Version del documento:** 1.0
