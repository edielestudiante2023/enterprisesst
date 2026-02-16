# ZZ_98 - Sistema de Historial de Versiones

## Concepto Clave
**Sin aprobacion no hay version.** El historial solo muestra versiones aprobadas, no borradores.

## Ciclo de Vida

```
┌─────────────────────────────────────────────────────┐
│  1. GENERAR DOCUMENTO                               │
│     estado: "generado"                              │
│     versiones en BD: 0 (vacio)                      │
│     Historial: "No hay versiones"                   │
└──────────────────┬──────────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────────┐
│  2. APROBAR DOCUMENTO (primera vez)                 │
│     → DocumentoVersionService::aprobarVersion()     │
│     → Guarda SNAPSHOT del contenido JSON            │
│     → Crea version 1.0 como "vigente"              │
│     estado: "aprobado"                              │
│     Historial: [1.0 vigente]                        │
└──────────────────┬──────────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────────┐
│  3. INICIAR NUEVA VERSION (modal pide tipo+desc)    │
│     → DocumentoVersionService::iniciarNuevaVersion()│
│     → Selecciona: menor (1.1) o mayor (2.0)        │
│     → Escribe descripcion del cambio                │
│     estado: "borrador" (editable otra vez)          │
│     La 1.0 sigue vigente, NO se crea version aun   │
└──────────────────┬──────────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────────┐
│  4. EDITAR + APROBAR                                │
│     → DocumentoVersionService::aprobarVersion()     │
│     → La 1.0 pasa a "obsoleto"                     │
│     → Se crea 1.1 como "vigente"                   │
│     estado: "aprobado"                              │
│     Historial: [1.1 vigente, 1.0 obsoleto]          │
└──────────────────┬──────────────────────────────────┘
                   ▼
              (ciclo se repite desde paso 3)
```

## Versionamiento: Mayor vs Menor

| Tipo | Ejemplo | Cuando usarlo |
|------|---------|---------------|
| Menor | 1.0 → 1.1 → 1.2 | Correcciones, ajustes de datos, typos |
| Mayor | 1.2 → 2.0 | Cambios significativos, reestructuracion |

**Calculo automatico:**
- Menor: incrementa decimal (X.Y → X.Y+1)
- Mayor: incrementa entero, resetea decimal (X.Y → X+1.0)
- Primera aprobacion siempre es 1.0

## Contenido Snapshot (la pieza clave)

Cada vez que se aprueba, se guarda una **foto completa** del JSON del documento en `tbl_doc_versiones_sst.contenido_snapshot` (LONGTEXT).

Esto permite:
- **Restaurar** una version anterior (copia el snapshot viejo al documento)
- **Descargar PDF** de cualquier version historica
- **Auditoria** de que contenia cada version

## Tabla en Base de Datos

**`tbl_doc_versiones_sst`**

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id_version | INT PK | Identificador unico |
| id_documento | INT FK | Referencia a tbl_documentos_sst |
| version | INT | Numero entero (1, 2, 3) |
| version_texto | VARCHAR(10) | Formato legible (1.0, 1.1, 2.0) |
| tipo_cambio | ENUM(mayor,menor) | Tipo de cambio |
| descripcion_cambio | TEXT | Descripcion obligatoria |
| contenido_snapshot | LONGTEXT | JSON completo del documento |
| estado | ENUM(vigente,obsoleto) | Solo 1 vigente por documento |
| autorizado_por | VARCHAR(255) | Nombre del usuario |
| autorizado_por_id | INT | ID del usuario |
| fecha_autorizacion | DATETIME | Cuando se aprobo |
| archivo_pdf | VARCHAR(255) | Ruta al PDF generado |
| hash_documento | VARCHAR(64) | SHA-256 para integridad |

**Campos agregados en `tbl_documentos_sst`:**

| Campo | Descripcion |
|-------|-------------|
| fecha_aprobacion | Cuando se aprobo la ultima version |
| aprobado_por | ID del usuario que aprobo |
| motivo_version | Descripcion pendiente para proxima version |
| tipo_cambio_pendiente | menor/mayor (se limpia al aprobar) |

## Maquina de Estados del Documento

```
generado ──[aprobar]──→ aprobado ──[nueva version]──→ borrador
                           ↑                              │
                           └──────[aprobar cambios]───────┘

aprobado ──[firmar]──→ pendiente_firma ──[firmado]──→ firmado

borrador ──[cancelar]──→ aprobado (descarta cambios)
```

## Servicio Centralizado

**Archivo:** `app/Services/DocumentoVersionService.php` (734 lineas)

| Metodo | Que hace |
|--------|----------|
| `aprobarVersion()` | Marca anteriores como obsoletas, crea nueva vigente, guarda snapshot |
| `crearVersionInicial()` | Delega a aprobarVersion() con tipo_cambio=mayor |
| `iniciarNuevaVersion()` | Pone documento en borrador, guarda tipo_cambio_pendiente |
| `restaurarVersion()` | Copia contenido_snapshot de version anterior al documento |
| `cancelarNuevaVersion()` | Regresa documento a aprobado, descarta cambios |
| `obtenerHistorial()` | Lista todas las versiones ordenadas por fecha DESC |
| `obtenerVersionVigente()` | Retorna la unica version con estado=vigente |
| `calcularNuevaVersionFinal()` | Calcula 1.0, 1.1, 2.0 segun tipo y previas |

**Integridad:** `aprobarVersion()` usa transaccion BD (todo o nada).

## Endpoints

| Metodo HTTP | Ruta | Accion |
|-------------|------|--------|
| POST | `/documentos-sst/aprobar-documento` | Aprueba y crea version |
| POST | `/documentos-sst/iniciar-nueva-version` | Inicia edicion (borrador) |
| POST | `/documentos-sst/restaurar-version` | Restaura version anterior |
| POST | `/documentos-sst/cancelar-nueva-version` | Cancela edicion |
| GET | `/documentos-sst/historial-versiones/{id}` | JSON con historial |
| GET | `/documentos-sst/descargar-version-pdf/{id}` | PDF de version especifica |

## Componentes Frontend

**Modal Historial:** `app/Views/documentos_sst/_components/modal_historial_versiones.php`
```php
<?= view('documentos_sst/_components/modal_historial_versiones', [
    'id_documento' => $documento['id_documento'],
    'versiones' => $versiones ?? []
]) ?>
```
- Tabla con: version, tipo, descripcion, fecha, autorizado por, acciones
- Badges: verde=vigente, gris=obsoleto
- Acciones: descargar PDF, restaurar (SweetAlert confirma)

**Modal Nueva Version:** `app/Views/documentos_sst/_components/modal_nueva_version.php`
```php
<?= view('documentos_sst/_components/modal_nueva_version', [
    'id_documento' => $documento['id_documento'],
    'version_actual' => ($documento['version'] ?? 1) . '.0',
    'tipo_documento' => $documento['tipo_documento']
]) ?>
```
- Selector visual menor/mayor con preview de proxima version
- Campo descripcion obligatorio (min 10 caracteres)
- Botones de ejemplo para rellenar rapido

## Regla: Historial siempre es modal

El boton Historial en la toolbar SIEMPRE debe ser un `<button>` que abre `#modalHistorialVersiones`.
NUNCA un `<a>` con enlace directo (ver ZZ_97_TOOLBAR_DOCUMENTOS.md, regla R4).
