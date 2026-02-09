# Componente: Tabla de Documentos SST

## Descripcion General

El componente `tabla_documentos_sst.php` es un componente reutilizable que muestra una tabla con los documentos SST aprobados/firmados de una carpeta. **Siempre muestra los encabezados** de la tabla, incluso cuando no hay documentos (patron similar a ReportList).

---

## 1. Ubicacion y Archivo

```
app/Views/documentacion/_components/tabla_documentos_sst.php
```

---

## 2. Variables Requeridas

| Variable | Tipo | Descripcion |
|----------|------|-------------|
| `$tipoCarpetaFases` | string | Identificador del tipo de carpeta (ej: `'politicas_2_1_1'`) |
| `$documentosSSTAprobados` | array | Array de documentos SST con sus datos |
| `$cliente` | array | Datos del cliente actual |

---

## 3. Tipos de Carpeta Soportados (CRITICO - 2 LUGARES)

### 3.1 Array en el Componente (tabla_documentos_sst.php)

El componente solo se renderiza si el tipo esta en este array:

```php
// app/Views/documentacion/_components/tabla_documentos_sst.php (linea 6)
$tiposConTabla = [
    'capacitacion_sst',
    'responsables_sst',
    'responsabilidades_sgsst',
    'archivo_documental',
    'presupuesto_sst',
    'induccion_reinduccion',
    'promocion_prevencion_salud',
    'matriz_legal',
    'politicas_2_1_1'
];
```

### 3.2 Array en el Controlador (DocumentacionController.php) - MUY IMPORTANTE

**CRITICO:** El tipo TAMBIEN debe estar en el array del controlador para que se ejecute la query y se carguen los documentos:

```php
// app/Controllers/DocumentacionController.php (linea ~304)
if (in_array($tipoCarpetaFases, [
    'capacitacion_sst',
    'responsables_sst',
    'responsabilidades_sgsst',
    'archivo_documental',
    'presupuesto_sst',
    // ... otros tipos ...
    'politicas_2_1_1'  // <-- DEBE ESTAR AQUI
])) {
    // SOLO SI ESTA AQUI se ejecuta la query de documentos
    $queryDocs = $db->table('tbl_documentos_sst')...
}
```

### 3.3 PROBLEMA COMUN: Tabla vacia aunque hay documentos en BD

| Sintoma | Causa | Solucion |
|---------|-------|----------|
| Tabla muestra "No hay documentos" pero SI hay en BD | Tipo **NO esta** en array del CONTROLADOR | Agregar tipo al `in_array()` en `DocumentacionController.php` linea ~304 |
| Tabla no aparece en absoluto | Tipo **NO esta** en array del COMPONENTE | Agregar tipo a `$tiposConTabla` en `tabla_documentos_sst.php` linea 6 |
| Tabla aparece pero sin los documentos correctos | Falta filtro `elseif` por tipo_documento | Agregar bloque `elseif` en `DocumentacionController.php` linea ~310+ |

### 3.4 Checklist OBLIGATORIO para Nuevo Tipo (3 PASOS)

```
[ ] 1. Agregar tipo a $tiposConTabla en tabla_documentos_sst.php (linea 6)
[ ] 2. Agregar tipo al in_array() en DocumentacionController.php (linea ~304)
[ ] 3. Agregar elseif con filtro tipo_documento en DocumentacionController.php (linea ~310+)
```

**IMPORTANTE:** Si falta el paso 2, la tabla aparecera pero siempre vacia.

### 3.5 Ejemplo: Agregar tipo 'mi_carpeta'

**Paso 1 - Componente:**
```php
// tabla_documentos_sst.php
$tiposConTabla = [..., 'mi_carpeta'];
```

**Paso 2 - Controlador (array principal):**
```php
// DocumentacionController.php linea ~304
if (in_array($tipoCarpetaFases, [..., 'mi_carpeta'])) {
```

**Paso 3 - Controlador (filtro):**
```php
// DocumentacionController.php linea ~310+
} elseif ($tipoCarpetaFases === 'mi_carpeta') {
    $queryDocs->where('tipo_documento', 'mi_tipo_documento');
}
```

---

## 4. Estructura de la Tabla

### 4.1 Columnas

| # | Columna | Ancho | Contenido |
|---|---------|-------|-----------|
| 1 | Codigo | 120px | Codigo del documento (ej: `POL-SST-001`) |
| 2 | Nombre | flexible | Titulo del documento + boton versiones |
| 3 | Ano | 80px | Badge con el ano (ej: 2026) |
| 4 | Version | 80px | Badge con version (ej: v1.0) |
| 5 | Estado | 110px | Badge con estado (Firmado, Aprobado, etc.) |
| 6 | Fecha Aprobacion | 150px | Fecha en formato dd/mm/YYYY HH:ii |
| 7 | Firmas | 110px | Contador de firmas (ej: 2/3) |
| 8 | Acciones | 180px | Botones de accion (ver, editar, PDF, Word, firmar) |

### 4.2 Estados y sus Badges

| Estado | Clase Badge | Icono | Texto |
|--------|-------------|-------|-------|
| `firmado` | `bg-success` | `bi-patch-check-fill` | Firmado |
| `pendiente_firma` | `bg-info` | `bi-pen` | Pendiente firma |
| `aprobado` | `bg-primary` | `bi-check-circle` | Aprobado |
| `borrador` | `bg-warning text-dark` | `bi-pencil-square` | Borrador |
| `generado` | `bg-secondary` | `bi-file-earmark-text` | Generado |

---

## 5. Comportamiento Tabla Vacia

### ANTES (problema)
Cuando no habia documentos, se ocultaba toda la tabla y solo se mostraba un mensaje centrado:

```php
<?php if (!empty($documentosSSTAprobados)): ?>
    <table>...</table>
<?php else: ?>
    <div class="text-center">No hay documentos...</div>
<?php endif; ?>
```

**Resultado:** Sin encabezados visibles, solo icono y mensaje.

### AHORA (solucion - patron ReportList)
La tabla siempre se muestra con encabezados. El mensaje de vacio va DENTRO del `<tbody>`:

```php
<table class="table table-hover mb-0">
    <thead class="table-light">
        <tr>
            <th>Codigo</th>
            <th>Nombre</th>
            <!-- ... mas columnas ... -->
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($documentosSSTAprobados)): ?>
            <?php foreach ($documentosSSTAprobados as $docSST): ?>
                <tr>...</tr>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fila de mensaje vacio -->
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No hay documentos aprobados o firmados aun.</p>
                    <small class="text-muted">Complete las fases y apruebe el documento para verlo aqui.</small>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
```

**Resultado:** Encabezados siempre visibles + mensaje dentro de fila con `colspan="8"`.

---

## 6. Estructura HTML Completa

```html
<!-- Card contenedor -->
<div class="card border-0 shadow-sm mb-4">
    <!-- Header verde (o azul para archivo_documental) -->
    <div class="card-header bg-success text-white">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-check me-2"></i>Documentos SST
        </h6>
    </div>

    <!-- Body sin padding (p-0) para que tabla llegue a los bordes -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Codigo</th>
                        <th>Nombre</th>
                        <th style="width: 80px;">Ano</th>
                        <th style="width: 80px;">Version</th>
                        <th style="width: 110px;">Estado</th>
                        <th style="width: 150px;">Fecha Aprobacion</th>
                        <th style="width: 110px;">Firmas</th>
                        <th style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de documentos o mensaje vacio -->
                </tbody>
            </table>
        </div>
    </div>
</div>
```

---

## 7. Componentes Auxiliares

### 7.1 Acciones del Documento

```php
<?= view('documentacion/_components/acciones_documento', [
    'docSST' => $docSST,
    'cliente' => $cliente
]) ?>
```

Renderiza los botones de accion: Ver, Editar, PDF, Word, Firmar.

### 7.2 Historial de Versiones

```php
<?= view('documentacion/_components/historial_versiones', [
    'versiones' => $docSST['versiones']
]) ?>
```

Se muestra como fila colapsable debajo de cada documento.

---

## 8. Como Usar en una Vista de Carpeta

### 8.1 Incluir el Componente

```php
<!-- En app/Views/documentacion/_tipos/mi_carpeta.php -->

<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'mi_tipo_carpeta',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>
```

### 8.2 Asegurar que el Tipo esta Registrado

En el componente `tabla_documentos_sst.php`, agregar el tipo al array:

```php
$tiposConTabla = [
    // ... tipos existentes ...
    'mi_tipo_carpeta'  // AGREGAR
];
```

---

## 9. Estructura de Datos del Documento

Cada elemento de `$documentosSSTAprobados` debe tener esta estructura:

```php
[
    'id_documento' => 123,
    'codigo' => 'POL-SST-001',
    'titulo' => 'Politica de Seguridad y Salud en el Trabajo',
    'anio' => 2026,
    'version' => 1,
    'version_texto' => '1.0',
    'estado' => 'aprobado',  // firmado, pendiente_firma, aprobado, borrador, generado
    'fecha_aprobacion' => '2026-02-05 10:30:00',
    'firmas_total' => 3,
    'firmas_firmadas' => 2,
    'versiones' => [  // Opcional - para historial
        ['version_texto' => '1.0', 'fecha' => '...', 'archivo_pdf' => '...'],
        // ...
    ]
]
```

---

## 10. Estilos CSS Utilizados

| Clase | Uso |
|-------|-----|
| `card border-0 shadow-sm` | Card sin borde con sombra suave |
| `card-header bg-success text-white` | Header verde con texto blanco |
| `card-body p-0` | Body sin padding (tabla toca bordes) |
| `table-responsive` | Scroll horizontal en pantallas pequenas |
| `table table-hover mb-0` | Tabla Bootstrap con hover, sin margen inferior |
| `table-light` | Fondo gris claro para thead |
| `badge bg-*` | Badges de colores para estado, version, ano |

---

## 11. Comparacion: Tabla SST vs ReportList

| Aspecto | Tabla Documentos SST | ReportList (Soportes) |
|---------|---------------------|----------------------|
| Proposito | Documentos generados con IA | Archivos/enlaces adjuntados |
| Header | bg-success (verde) | bg-primary (azul) |
| Columnas | 8 (con estado, firmas, version) | 6 (mas simple) |
| Acciones | Ver, Editar, PDF, Word, Firmar | Ver, Descargar |
| Versionamiento | Si (historial colapsable) | No |
| Firmas | Si (contador) | No |
| Encabezados siempre | Si (actualizado) | Si |

---

## 12. Flujo de Renderizado

```
┌─────────────────────────────────────────────────────────────────┐
│  Vista de Carpeta (_tipos/politicas_2_1_1.php)                  │
│                                                                 │
│  1. Card de carpeta con boton                                   │
│  2. Alerta informativa                                          │
│  3. Panel de fases (si aplica)                                  │
│  4. ════════════════════════════════════════════                │
│     │  COMPONENTE: tabla_documentos_sst.php  │                  │
│     ════════════════════════════════════════════                │
│         ↓                                                       │
│     Verifica $tipoCarpetaFases in $tiposConTabla                │
│         ↓                                                       │
│     Renderiza card + table                                      │
│         ↓                                                       │
│     Si hay docs → foreach filas                                 │
│     Si NO hay docs → fila con colspan y mensaje                 │
│                                                                 │
│  5. Lista de subcarpetas                                        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 13. Checklist para Nueva Carpeta con Tabla

```
[ ] 1. Crear vista en _tipos/{nombre}.php
[ ] 2. Agregar tipo a $tiposConTabla en tabla_documentos_sst.php
[ ] 3. Incluir componente con view('..._components/tabla_documentos_sst', [...])
[ ] 4. Asegurar que DocumentacionController pasa $documentosSSTAprobados
[ ] 5. Probar con datos y sin datos (verificar encabezados visibles)
```

---

## 14. Resumen Visual

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ██ Documentos SST                                          (bg-success) │
├─────────┬──────────────────┬──────┬─────────┬─────────┬────────┬───────┤
│ Codigo  │ Nombre           │ Ano  │ Version │ Estado  │ Fecha  │ Firmas│ Acciones │
├─────────┼──────────────────┼──────┼─────────┼─────────┼────────┼───────┤
│         │                  │      │         │         │        │       │
│         │    📂 No hay documentos aprobados o firmados aun.            │
│         │    Complete las fases y apruebe el documento...              │
│         │                  │      │         │         │        │       │
└─────────┴──────────────────┴──────┴─────────┴─────────┴────────┴───────┘
          ↑
          Fila con colspan="8" - ENCABEZADOS SIEMPRE VISIBLES
```

---

*Documento creado: 2026-02-05*
*Version: 1.0*
*Relacionado con: AA_REPORTLIST_CARPETA.md, 2_AA_WEB.md*
