# Sistema de Versiones Estandarizado para Documentos SST

## Resumen

Este documento describe el sistema de versionamiento **ESTANDARIZADO** implementado para todos los documentos del SG-SST. El objetivo es garantizar consistencia en el manejo de versiones entre todos los tipos de documentos.

## Arquitectura

### Servicio Centralizado

```
app/Services/DocumentoVersionService.php
```

Todos los documentos DEBEN usar este servicio para:
- Iniciar nuevas versiones
- Aprobar documentos
- Restaurar versiones anteriores
- Cancelar ediciones

### Componentes de Vista Reutilizables

```
app/Views/documentos_sst/_components/
├── modal_nueva_version.php      # Modal estandarizado para crear versiones
└── modal_historial_versiones.php # Modal para ver historial
```

## Tablas de Base de Datos

### tbl_documentos_sst (Principal)

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `id_documento` | INT | PK |
| `version` | INT | Numero de version (1, 2, 3...) |
| `estado` | ENUM | 'borrador', 'aprobado', 'pendiente_firma', 'firmado' |
| `motivo_version` | TEXT | Descripcion del cambio pendiente |
| `tipo_cambio_pendiente` | ENUM | 'mayor' o 'menor' (cuando esta en borrador) |

### tbl_doc_versiones_sst (Historial)

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `id_version` | INT | PK |
| `id_documento` | INT | FK |
| `tipo_documento` | VARCHAR | Tipo de documento (para consultas sin JOIN) |
| `version` | INT | Numero de version mayor |
| `version_texto` | VARCHAR | Formato X.Y (1.0, 1.1, 2.0) |
| `tipo_cambio` | ENUM | 'mayor' o 'menor' |
| `descripcion_cambio` | TEXT | Descripcion del cambio |
| `contenido_snapshot` | JSON | Snapshot del contenido al crear version |
| `estado` | ENUM | 'vigente' o 'obsoleto' |
| `autorizado_por` | VARCHAR | Nombre del usuario |
| `fecha_autorizacion` | TIMESTAMP | Fecha de creacion |

## Flujo Estandarizado

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUJO UNICO                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. CREAR DOCUMENTO                                         │
│     └─→ estado: 'borrador', version: null                   │
│                                                             │
│  2. COMPLETAR CONTENIDO                                     │
│     └─→ (IA, manual, o carga de archivo)                   │
│                                                             │
│  3. APROBAR (Primera vez)                                   │
│     └─→ estado: 'aprobado', version: 1                     │
│     └─→ Crear version 1.0 en historial                     │
│                                                             │
│  4. EDITAR (Nueva version)                                  │
│     └─→ Seleccionar tipo: Mayor (2.0) o Menor (1.1)        │
│     └─→ Ingresar descripcion (OBLIGATORIO)                 │
│     └─→ estado: 'borrador'                                 │
│                                                             │
│  5. RE-APROBAR                                              │
│     └─→ estado: 'aprobado'                                 │
│     └─→ Version anterior: estado='obsoleto'                │
│     └─→ Nueva version: estado='vigente'                    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## Tipos de Cambio

| Tipo | Formato | Cuando Usar |
|------|---------|-------------|
| **Menor** | 1.0 → 1.1 → 1.2 | Correcciones, ajustes menores, actualizacion de datos |
| **Mayor** | 1.0 → 2.0 → 3.0 | Cambios significativos, restructuracion, nueva normativa |

## Uso del Servicio

### Iniciar Nueva Version

```php
use App\Services\DocumentoVersionService;

$versionService = new DocumentoVersionService();

$resultado = $versionService->iniciarNuevaVersion(
    $idDocumento,
    'menor',  // 'mayor' o 'menor'
    'Actualizacion de datos del responsable SST'
);

if ($resultado['success']) {
    // Documento en modo edicion
    // $resultado['data']['url_edicion'] contiene la URL de edicion
}
```

### Aprobar Version

```php
$resultado = $versionService->aprobarVersion(
    $idDocumento,
    $usuarioId,
    $usuarioNombre,
    $descripcionCambio,  // opcional si ya se guardo en iniciarNuevaVersion
    $tipoCambio         // opcional si ya se guardo
);

if ($resultado['success']) {
    // $resultado['data']['version_texto'] = "1.1"
    // $resultado['data']['id_version'] = ID de la nueva version
}
```

### Obtener Historial

```php
$historial = $versionService->obtenerHistorial($idDocumento);
// Array de todas las versiones, ordenadas por fecha DESC
```

### Restaurar Version

```php
$resultado = $versionService->restaurarVersion(
    $idDocumento,
    $idVersionRestaurar,
    $usuarioId,
    $usuarioNombre
);
// El documento queda en estado 'borrador' con el contenido restaurado
```

## Uso de Componentes de Vista

### Modal Nueva Version

```php
<?= view('documentos_sst/_components/modal_nueva_version', [
    'id_documento' => $documento['id_documento'],
    'version_actual' => $versionVigente['version_texto'] ?? '1.0',
    'tipo_documento' => 'programa_capacitacion',
    // Opcional: campos adicionales especificos del documento
    'campos_adicionales' => [
        [
            'name' => 'id_responsable',
            'label' => 'Responsable SST',
            'type' => 'select',
            'options' => $listaResponsables,
            'required' => true
        ]
    ]
]) ?>
```

### Modal Historial

```php
<?= view('documentos_sst/_components/modal_historial_versiones', [
    'id_documento' => $documento['id_documento'],
    'versiones' => $versionService->obtenerHistorial($documento['id_documento']),
    'permitir_restaurar' => true,
    'permitir_descargar' => true
]) ?>
```

## Endpoints API

| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| POST | `/documentos-sst/iniciar-nueva-version` | Inicia proceso de nueva version |
| POST | `/documentos-sst/aprobar-documento` | Aprueba y crea nueva version |
| POST | `/documentos-sst/restaurar-version` | Restaura version anterior |
| POST | `/documentos-sst/cancelar-nueva-version` | Cancela edicion en curso |
| GET | `/documentos-sst/historial-versiones/{id}` | Obtiene historial |

## Reglas de Negocio

1. **Descripcion obligatoria**: Siempre se requiere descripcion del cambio
2. **Tipo de cambio obligatorio**: Siempre seleccionar 'mayor' o 'menor'
3. **Snapshot automatico**: El contenido se guarda automaticamente al crear version
4. **Estado borrador**: El documento cambia a borrador al iniciar edicion
5. **Versiones obsoletas**: Las versiones anteriores se marcan como obsoletas
6. **Usuario registrado**: Se guarda quien autorizo cada version

## Migracion Ejecutada

La migracion `app/SQL/estandarizar_sistema_versiones.php` realizo:

1. Agregar campo `tipo_documento` a `tbl_doc_versiones_sst`
2. Agregar campo `tipo_cambio_pendiente` a `tbl_documentos_sst`
3. Sincronizar tipo_documento en versiones existentes
4. Crear indice `idx_tipo_documento`
5. Crear indice `idx_doc_estado`

## Estadisticas Post-Migracion

### Local
- 42 documentos
- 57 versiones (40 vigentes, 10 obsoletas)

### Produccion
- 5 documentos
- 7 versiones (4 vigentes, 1 obsoletas)

---

**Fecha de implementacion:** 2026-02-05
**Autor:** Sistema automatizado
