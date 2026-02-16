# Integracion de Documentos de Comites Electorales al Sistema de Versionamiento SST

> **Fecha:** 2026-02-14
> **Estado:** Implementado, pendiente pruebas funcionales
> **Afecta:** Modulo Comites Electorales + Nucleo Documentos SST

---

## 1. Problema detectado

Los documentos generados por el modulo de Comites Electorales (actas de constitucion y recomposicion) se insertaban directamente en `tbl_documentos_sst` **sin integrarse** al sistema de versionamiento del nucleo SST.

### Sintomas

- Documentos aparecian en el dashboard de firmas (`/firma/dashboard/{id}`) pero sin historial de versiones
- `tbl_doc_versiones_sst` no tenia registros para estos documentos
- `tbl_documentos_sst.contenido` era `NULL` (sin snapshot de datos)
- `DocumentoSSTFactory::existe('acta_constitucion_cocolab')` retornaba `false`
- No estaban registrados en `tbl_doc_plantillas`

### Causa raiz

El metodo `obtenerOCrearDocumentoActa()` en `ComitesEleccionesController.php` hacia un INSERT directo a `tbl_documentos_sst` sin:
1. Registrar el tipo en el Factory
2. Crear version en `tbl_doc_versiones_sst`
3. Almacenar contenido JSON
4. Registrar plantilla en `tbl_doc_plantillas`

Lo mismo aplicaba para `obtenerOCrearDocumentoRecomposicion()`.

---

## 2. Los 8 tipos de documento afectados

### Actas de Constitucion (proceso electoral completado)

| tipo_documento | Comite | Estandar | Codigo |
|---|---|---|---|
| `acta_constitucion_copasst` | COPASST | 1.1.6 | FT-SST-013 |
| `acta_constitucion_cocolab` | Comite Convivencia Laboral | 1.1.8 | FT-SST-013 |
| `acta_constitucion_brigada` | Brigada de Emergencias | - | FT-SST-013 |
| `acta_constitucion_vigia` | Vigia SST | 1.1.6 | FT-SST-013 |

### Actas de Recomposicion (cambio de miembros en comite vigente)

| tipo_documento | Comite | Codigo |
|---|---|---|
| `acta_recomposicion_copasst` | COPASST | FT-SST-156 |
| `acta_recomposicion_cocolab` | Comite Convivencia Laboral | FT-SST-155 |
| `acta_recomposicion_brigada` | Brigada de Emergencias | FT-SST-156 |
| `acta_recomposicion_vigia` | Vigia SST | FT-SST-156 |

---

## 3. Diferencia arquitectonica clave

Estos documentos son fundamentalmente distintos a los docs SST estandar:

| Aspecto | Docs SST (Tipo A/B) | Docs Comite Electoral |
|---|---|---|
| Contenido | JSON con secciones editables | Snapshot JSON de datos electorales |
| Generacion | IA o contenido estatico | Automatica desde tablas electorales |
| Edicion | Si, por secciones | No, datos provienen del proceso |
| Fuente de datos | `tbl_cliente_contexto_sst` | 9+ tablas: `tbl_procesos_electorales`, `tbl_jurados_proceso`, `tbl_candidatos_comite`, `tbl_votos_comite`, etc. |
| PDF | Renderiza desde `contenido` JSON | Renderiza desde vistas PHP con datos en vivo |
| IA | Si | No |

**Implicacion:** `getSecciones()` retorna un solo elemento `acta_completa`, `getPromptParaSeccion()` retorna `''`, y `getContenidoEstatico()` retorna mensaje de generacion automatica. Esto es intencional - estos documentos no participan del flujo de generacion IA.

---

## 4. Arquitectura de clases implementada

```
AbstractDocumentoSST (existente)
  |
  +-- AbstractActaConstitucion (NUEVO - parametrizada)
  |     +-- ActaConstitucionCopasst
  |     +-- ActaConstitucionCocolab
  |     +-- ActaConstitucionBrigada
  |     +-- ActaConstitucionVigia
  |
  +-- AbstractActaRecomposicion (NUEVO - parametrizada)
        +-- ActaRecomposicionCopasst
        +-- ActaRecomposicionCocolab
        +-- ActaRecomposicionBrigada
        +-- ActaRecomposicionVigia
```

### Clases base (abstractas)

Ubicacion: `app/Libraries/DocumentosSSTTypes/`

- **`AbstractActaConstitucion.php`** (~140 lineas): Define propiedades parametrizables (`$tipoComite`, `$nombreComite`, `$estandarNumeral`) e implementa todos los metodos de `DocumentoSSTInterface`. Incluye `buildContenidoSnapshot()` que serializa datos electorales a JSON.

- **`AbstractActaRecomposicion.php`** (~120 lineas): Misma estructura pero con `$codigoBase` variable (FT-SST-155 para COCOLAB, FT-SST-156 para el resto) y `buildContenidoSnapshot()` adaptado a datos de recomposicion (saliente, entrante, motivo, miembros actuales).

### Subclases thin (~20 lineas cada una)

Solo definen el constructor (asignando parametros) y `getTipoDocumento()`. Ejemplo:

```php
class ActaConstitucionCopasst extends AbstractActaConstitucion
{
    public function __construct()
    {
        $this->tipoComite = 'COPASST';
        $this->nombreComite = 'COPASST';
        $this->estandarNumeral = '1.1.6';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_constitucion_copasst';
    }
}
```

---

## 5. Metodo `buildContenidoSnapshot()`

Este metodo es la pieza central nueva. Captura el estado de los datos electorales en un JSON estructurado que se almacena en `tbl_documentos_sst.contenido` y sirve como `contenido_snapshot` en `tbl_doc_versiones_sst`.

### Estructura JSON para Acta de Constitucion

```json
{
    "flujo": "comite_electoral",
    "tipo_acta": "constitucion",
    "tipo_comite": "COPASST",
    "id_proceso": 5,
    "fecha_generacion": "2026-02-14 10:30:00",
    "proceso": {
        "fecha_votacion": "2026-02-10",
        "plazas_principales": 2,
        "plazas_suplentes": 2,
        "estado": "completado",
        "anio": 2026
    },
    "jurados": [
        {"nombre": "Juan Perez", "rol": "presidente", "cedula": "123456"}
    ],
    "resultados_votacion": [
        {"nombre": "Maria Lopez", "votos": 15}
    ],
    "miembros_elegidos": {
        "trabajadores_principales": [...],
        "trabajadores_suplentes": [...],
        "empleador_principales": [...],
        "empleador_suplentes": [...]
    },
    "estadisticas": {
        "total_votantes": 25,
        "votos_emitidos": 22,
        "participacion": 88.0,
        "total_votos": 22
    }
}
```

### Estructura JSON para Acta de Recomposicion

```json
{
    "flujo": "comite_electoral",
    "tipo_acta": "recomposicion",
    "tipo_comite": "COCOLAB",
    "id_proceso": 5,
    "id_recomposicion": 3,
    "numero_recomposicion": 1,
    "fecha_generacion": "2026-02-14 10:30:00",
    "saliente": {
        "nombre": "Pedro Garcia",
        "cedula": "789012",
        "cargo": "Asistente",
        "representacion": "trabajador",
        "motivo": "renuncia voluntaria presentada"
    },
    "entrante": {
        "nombre": "Ana Torres",
        "cedula": "345678",
        "cargo": "Coordinadora",
        "representacion": "trabajador"
    },
    "miembros_actuales": [
        {"nombre": "...", "cedula": "...", "representacion": "empleador", "tipo_plaza": "principal", "es_recomposicion": false, "marca": "A"}
    ]
}
```

---

## 6. Cambios en el Controller

### `obtenerOCrearDocumentoActa()` (linea ~2429)

**Antes:** INSERT directo sin contenido ni version.

**Despues:** Al crear documento nuevo:
1. Instancia el tipo via `DocumentoSSTFactory::crear($tipoDocumento)`
2. Genera snapshot JSON con `buildContenidoSnapshot($datosActa)`
3. Incluye `contenido` en el INSERT
4. Llama `DocumentoVersionService::crearVersionInicial()` para crear version 1.0

La firma cambio de:
```php
private function obtenerOCrearDocumentoActa(array $proceso, array $cliente): array
```
A:
```php
private function obtenerOCrearDocumentoActa(array $proceso, array $cliente, ?array $datosActa = null): array
```

Los 4 call sites fueron actualizados para pasar `$data` como tercer argumento.

### `obtenerOCrearDocumentoRecomposicion()` (linea ~3881)

Mismos cambios + se agrego `'anio' => $data['proceso']['anio']` al INSERT (faltaba antes).

---

## 7. Registro en Factory

`DocumentoSSTFactory.php` - Se agregaron 8 entradas al array `$tiposRegistrados`:

```php
// Actas de Constitucion - Comites Electorales
'acta_constitucion_copasst' => ActaConstitucionCopasst::class,
'acta_constitucion_cocolab' => ActaConstitucionCocolab::class,
'acta_constitucion_brigada' => ActaConstitucionBrigada::class,
'acta_constitucion_vigia' => ActaConstitucionVigia::class,
// Actas de Recomposicion - Comites Electorales
'acta_recomposicion_copasst' => ActaRecomposicionCopasst::class,
'acta_recomposicion_cocolab' => ActaRecomposicionCocolab::class,
'acta_recomposicion_brigada' => ActaRecomposicionBrigada::class,
'acta_recomposicion_vigia' => ActaRecomposicionVigia::class,
```

---

## 8. SQL ejecutado

### Script: `app/SQL/ejecutar_plantillas_comites.php`

Ejecutar: `php app/SQL/ejecutar_plantillas_comites.php [local|produccion]`

**Paso 1:** Inserta 8 registros en `tbl_doc_plantillas` con `id_tipo = 11` (Acta).

**Paso 2:** Busca documentos existentes de comite sin version en `tbl_doc_versiones_sst` y les crea version 1.0 como migracion.

### Resultados de ejecucion (2026-02-14)

| Entorno | Plantillas | Docs migrados |
|---|---|---|
| LOCAL | 8/8 insertadas | 2 (brigada + cocolab) |
| PRODUCCION | 8/8 insertadas | 0 (no habia docs previos) |

### Archivos SQL de referencia (no ejecutar, son los .sql puros)

- `app/SQL/insert_plantillas_comites_electorales.sql`
- `app/SQL/fix_documentos_comites_existentes.sql`

---

## 9. Archivos creados/modificados

### Archivos nuevos (12)

| Archivo | Lineas | Proposito |
|---|---|---|
| `app/Libraries/DocumentosSSTTypes/AbstractActaConstitucion.php` | ~140 | Clase base constitucion |
| `app/Libraries/DocumentosSSTTypes/ActaConstitucionCopasst.php` | ~20 | Subclase COPASST |
| `app/Libraries/DocumentosSSTTypes/ActaConstitucionCocolab.php` | ~20 | Subclase COCOLAB |
| `app/Libraries/DocumentosSSTTypes/ActaConstitucionBrigada.php` | ~20 | Subclase BRIGADA |
| `app/Libraries/DocumentosSSTTypes/ActaConstitucionVigia.php` | ~20 | Subclase VIGIA |
| `app/Libraries/DocumentosSSTTypes/AbstractActaRecomposicion.php` | ~120 | Clase base recomposicion |
| `app/Libraries/DocumentosSSTTypes/ActaRecomposicionCopasst.php` | ~20 | Subclase COPASST |
| `app/Libraries/DocumentosSSTTypes/ActaRecomposicionCocolab.php` | ~20 | Subclase COCOLAB |
| `app/Libraries/DocumentosSSTTypes/ActaRecomposicionBrigada.php` | ~20 | Subclase BRIGADA |
| `app/Libraries/DocumentosSSTTypes/ActaRecomposicionVigia.php` | ~20 | Subclase VIGIA |
| `app/SQL/insert_plantillas_comites_electorales.sql` | ~20 | SQL referencia plantillas |
| `app/SQL/fix_documentos_comites_existentes.sql` | ~20 | SQL referencia migracion |

### Archivos modificados (2)

| Archivo | Cambio |
|---|---|
| `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` | +8 entradas en `$tiposRegistrados` |
| `app/Controllers/ComitesEleccionesController.php` | 2 metodos modificados + 4 call sites actualizados |

---

## 10. Checklist de pruebas pendientes

### Prueba 1: Factory reconoce los tipos

Verificar que `DocumentoSSTFactory::existe('acta_constitucion_cocolab')` retorna `true` y `DocumentoSSTFactory::crear('acta_constitucion_cocolab')` retorna instancia valida.

### Prueba 2: Crear nuevo proceso electoral

1. Ir a `/comites-elecciones/{idCliente}`
2. Crear proceso COCOLAB o COPASST
3. Completar el proceso hasta generar acta
4. Verificar en BD:
   - `tbl_documentos_sst.contenido` tiene JSON con datos electorales
   - `tbl_doc_versiones_sst` tiene version 1.0 para el documento
   - `tbl_documentos_sst.estado` = `'aprobado'`

### Prueba 3: Dashboard de firmas sin regresion

1. Ir a `/firma/dashboard/{idCliente}`
2. Verificar que los documentos de comite siguen apareciendo correctamente
3. El boton "Ver" sigue funcionando

### Prueba 4: Recomposicion con version

1. Crear recomposicion en un comite vigente
2. Solicitar firmas
3. Verificar que se crea version 1.0 con snapshot de datos de recomposicion

### Prueba 5: Documentos migrados

1. Verificar que el documento "Acta de Constitucion COCOLAB" (el que origino la investigacion) ahora tiene version 1.0 en `tbl_doc_versiones_sst`

---

## 11. Notas de diseno

### Por que no usar secciones IA para estos documentos

Los documentos de comites no son editables por seccion - su contenido esta determinado por el proceso electoral (votantes, candidatos, resultados). Forzarlos al modelo de secciones IA seria:
- Redundante (los datos ya existen en las tablas electorales)
- Fragil (se desincronizarian de los datos reales)
- Innecesario (no hay decision editorial sobre el contenido)

### Por que `getSecciones()` retorna un solo elemento

La interfaz `DocumentoSSTInterface` obliga a implementar `getSecciones()`. Retornar un array vacio romperia `getTiposDisponibles()` que cuenta secciones. Un solo elemento `acta_completa` satisface el contrato sin mentir sobre la estructura.

### Patron de herencia 2+8

Se eligio 2 clases abstractas + 8 subclases thin en vez de 8 clases independientes para:
- Eliminar duplicacion (~100 lineas compartidas por familia)
- Facilitar agregar nuevos tipos de comite (solo ~20 lineas)
- Mantener `buildContenidoSnapshot()` centralizado por familia
