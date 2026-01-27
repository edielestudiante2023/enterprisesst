# Proyecto de Documentacion SST - Parte 9

## Navegacion, Publicacion PDF en Reportes y Mejoras de Carpeta SST

---

## CONTEXTO DE ESTA PARTE

Esta parte documenta las mejoras realizadas al modulo de carpetas/documentacion SST, incluyendo:

1. **Correccion de tabla Documentos SST** para mostrar todos los estados
2. **Versionamiento visible** en la tabla con historial expandible
3. **Simplificacion de navegacion** (breadcrumbs y redirects)
4. **Notificacion por email** al consultor cuando se completan firmas
5. **Publicacion automatica y manual de PDF** en reportList
6. **Ocultamiento de seccion legacy** "Documentos" cuando hay documentos SST

---

## 1. CORRECCION DE TABLA DOCUMENTOS SST

### 1.1 Problema

La tabla "Documentos SST" en la vista de carpeta (`carpeta/171`) mostraba "No hay documentos aprobados" a pesar de que existia un documento generado. La causa era doble:

| Problema | Causa | Solucion |
|----------|-------|----------|
| Filtro muy restrictivo | Solo mostraba `['aprobado', 'firmado', 'pendiente_firma']` | Agregado `'borrador'` y `'generado'` al `whereIn` |
| Estado vacio en BD | Campo `estado` era `""` (cadena vacia) en lugar de un valor ENUM valido | UPDATE para corregir a `'borrador'` y luego `'aprobado'` segun versiones vigentes |

### 1.2 Correccion del ENUM

El ENUM original de `tbl_documentos_sst.estado` era:

```sql
ENUM('borrador', 'generado', 'aprobado', 'obsoleto')
```

Se actualizo a:

```sql
ENUM('borrador', 'generado', 'aprobado', 'pendiente_firma', 'firmado', 'obsoleto')
```

**Ejecutado en ambos entornos** (LOCAL y PRODUCCION).

### 1.3 Query Actualizado

**Archivo:** `app/Controllers/DocumentacionController.php`

```php
// Antes:
->whereIn('estado', ['aprobado', 'firmado', 'pendiente_firma'])

// Despues:
->whereIn('estado', ['borrador', 'generado', 'aprobado', 'firmado', 'pendiente_firma'])
```

### 1.4 Badges de Estado en la Vista

**Archivo:** `app/Views/documentacion/carpeta.php`

```php
$estadoBadge = match($estadoDoc) {
    'firmado' => 'bg-success',
    'pendiente_firma' => 'bg-info',
    'aprobado' => 'bg-primary',
    'borrador' => 'bg-warning text-dark',
    'generado' => 'bg-secondary',
    default => 'bg-secondary'
};
```

---

## 2. VERSIONAMIENTO VISIBLE EN TABLA

### 2.1 Problema

La tabla mostraba `v1.0` hardcodeado cuando el documento tenia 3 versiones registradas (v1.0, v1.1, v1.2).

### 2.2 Solucion

**Archivo:** `app/Controllers/DocumentacionController.php`

Se agregan queries para obtener versiones y version actual:

```php
// Obtener todas las versiones del documento
$versiones = $db->table('tbl_doc_versiones_sst')
    ->select('id_version, version_texto, tipo_cambio, descripcion_cambio, estado, autorizado_por, fecha_autorizacion, archivo_pdf')
    ->where('id_documento', $docSST['id_documento'])
    ->orderBy('id_version', 'DESC')
    ->get()
    ->getResultArray();

$docSST['versiones'] = $versiones;
$docSST['version_texto'] = !empty($versiones) ? $versiones[0]['version_texto'] : ($docSST['version'] . '.0');

// Obtener enlace PDF de la version vigente
$versionVigente = array_filter($versiones, fn($v) => $v['estado'] === 'vigente');
$versionVigente = reset($versionVigente);
$docSST['archivo_pdf'] = $versionVigente['archivo_pdf'] ?? null;
```

### 2.3 Historial Expandible

**Archivo:** `app/Views/documentacion/carpeta.php`

Se agrega un boton "X versiones" que despliega una subtabla colapsable:

```html
<button class="btn btn-sm btn-link" data-bs-toggle="collapse"
        data-bs-target="#versiones-<?= $docSST['id_documento'] ?>">
    <i class="bi bi-clock-history me-1"></i><?= count($docSST['versiones']) ?> versiones
</button>
```

La subtabla muestra:

| Columna | Descripcion |
|---------|-------------|
| Version | Badge azul (v1.0, v1.1, v1.2) |
| Tipo | Badge rojo (Mayor) o azul (Menor) |
| Descripcion del Cambio | Texto libre |
| Estado | Badge verde (Vigente) o gris (Anterior) |
| Autorizado por | Nombre |
| Fecha | dd/mm/yyyy HH:mm |

### 2.4 Columna Fecha Aprobacion

Se agrego columna "Fecha Aprobacion" a la tabla principal:

```php
<?php if (!empty($docSST['fecha_aprobacion'])): ?>
    <small><?= date('d/m/Y H:i', strtotime($docSST['fecha_aprobacion'])) ?></small>
<?php endif; ?>
```

---

## 3. TARGET BLANK EN BOTONES DE ACCION

Todos los botones de acciones en la tabla de Documentos SST ahora abren en nueva pestana:

```html
<a href="..." class="btn btn-outline-primary" title="Ver documento" target="_blank">
<a href="..." class="btn btn-outline-warning" title="Editar documento" target="_blank">
<a href="..." class="btn btn-outline-success" title="Firmas y Audit Log" target="_blank">
```

---

## 4. SIMPLIFICACION DE NAVEGACION

### 4.1 Problema

Los breadcrumbs de la vista carpeta llevaban a vistas intermedias (`carpeta/160`, `carpeta/161`) que mostraban cards sin URLs funcionales. Estas vistas de tipo `raiz` y `phva` no aportaban valor.

### 4.2 Solucion: Breadcrumbs

Todos los breadcrumbs ahora apuntan al dashboard del cliente:

```php
// Antes: cada nivel apuntaba a su carpeta padre
<a href="<?= base_url('documentacion/carpeta/' . $padre['id_carpeta']) ?>">

// Despues: todos apuntan al dashboard
<a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
```

### 4.3 Solucion: Redirect de Carpetas Intermedias

**Archivo:** `app/Controllers/DocumentacionController.php`

Se agrego redirect automatico para carpetas de tipo `raiz` o `phva`:

```php
public function carpeta($idCarpeta)
{
    // ... obtener carpeta ...

    // Carpetas tipo raiz o phva redirigen al dashboard del cliente
    if (in_array($carpeta['tipo'], ['raiz', 'phva'])) {
        return redirect()->to('documentacion/' . $carpeta['id_cliente']);
    }

    // ... resto del metodo ...
}
```

Esto significa que URLs como `/documentacion/carpeta/160` y `/documentacion/carpeta/161` redirigen automaticamente a `/documentacion/11`.

---

## 5. NOTIFICACION EMAIL AL CONSULTOR (FIRMAS COMPLETAS)

### 5.1 Flujo

```
Todas las firmas completadas
         |
         v
FirmaElectronicaController detecta firmasCompletas()
         |
         v
Actualiza documento a estado 'firmado'
         |
         v
Llama notificarConsultorFirmasCompletas()
         |
         v
Envia email via SendGrid al consultor asignado
         |
         v
Llama publicarDocumentoFirmado()
         |
         v
Genera PDF y lo publica en tbl_reporte
```

### 5.2 Metodo de Notificacion

**Archivo:** `app/Controllers/FirmaElectronicaController.php`

```php
private function notificarConsultorFirmasCompletas(int $idDocumento, array $documento): void
{
    // 1. Obtener consultor asignado al cliente
    // 2. Obtener datos de firmantes y fechas
    // 3. Enviar email via SendGrid con:
    //    - Nombre del documento
    //    - Tabla de firmantes con fechas
    //    - Boton "Ver en el Aplicativo"
}
```

### 5.3 Contenido del Email

| Elemento | Descripcion |
|----------|-------------|
| Asunto | "Firmas completadas: {titulo_documento}" |
| Cuerpo | Tabla con firmantes, cargos, fechas |
| CTA | Boton verde "Ver en el Aplicativo" |
| API | SendGrid REST API |

---

## 6. PUBLICACION AUTOMATICA DE PDF EN REPORTLIST

### 6.1 Flujo Automatico (Post-Firmas)

**Archivo:** `app/Controllers/FirmaElectronicaController.php`

Metodo `publicarDocumentoFirmado()`:

```
1. Obtener documento y cliente
2. Generar PDF con Dompdf (misma logica que exportarPDF)
3. Guardar PDF en: uploads/{nit_cliente}/{timestamp}_{codigo}_{titulo}.pdf
4. Insertar registro en tbl_reporte:
   - titulo_reporte: "PRG-CAP-001 - Programa de Capacitacion (v1 - Firmado)"
   - id_detailreport: 21 (Documento SG-SST)
   - id_report_type: 12 (Reportes SST)
   - estado: CERRADO
   - enlace: URL completa al PDF
5. Guardar enlace en tbl_doc_versiones_sst.archivo_pdf
```

### 6.2 Tipo de Documento en detail_report

Se creo un nuevo tipo de detalle de reporte:

```sql
INSERT INTO detail_report (detail_report)
SELECT 'Documento SG-SST'
WHERE NOT EXISTS (SELECT 1 FROM detail_report WHERE detail_report = 'Documento SG-SST');
```

**Resultado:** ID 21 en ambos entornos (LOCAL y PRODUCCION).

### 6.3 Clasificacion en tbl_reporte

| Campo | Valor |
|-------|-------|
| `id_detailreport` | 21 (Documento SG-SST) |
| `id_report_type` | 12 (Reportes SST) |
| `estado` | CERRADO |

---

## 7. PUBLICACION MANUAL DE PDF EN REPORTLIST

### 7.1 Necesidad

El flujo automatico solo se activa cuando se completan todas las firmas electronicas. Se necesitaba una forma manual para que el consultor publique el PDF en reportList sin esperar firmas.

### 7.2 Implementacion

**Archivo:** `app/Controllers/DocumentosSSTController.php`

Nuevo metodo `publicarPDF()`:

```php
public function publicarPDF(int $idDocumento)
{
    // 1. Obtener documento, cliente, contenido
    // 2. Preparar datos (logo, versiones, responsables, contexto, firmas)
    // 3. Renderizar PDF con Dompdf
    // 4. Guardar PDF en uploads/{nit}/
    // 5. Verificar si ya existe reporte (evitar duplicados)
    // 6. INSERT o UPDATE en tbl_reporte
    // 7. Guardar enlace en tbl_doc_versiones_sst.archivo_pdf
    // 8. Redirect al dashboard del cliente
}
```

### 7.3 Validacion Anti-Duplicados

```php
// Obtener ID del detail_report "Documento SG-SST"
$detailReport = $this->db->table('detail_report')
    ->where('detail_report', 'Documento SG-SST')
    ->get()
    ->getRowArray();
$idDetailReport = $detailReport['id_detailreport'] ?? 2;

// Verificar si ya existe un reporte para este documento
$codigoBusqueda = $documento['codigo'] ?? $documento['titulo'];
$existente = $this->db->table('tbl_reporte')
    ->like('titulo_reporte', $codigoBusqueda)
    ->where('id_cliente', $documento['id_cliente'])
    ->where('id_detailreport', $idDetailReport)
    ->get()
    ->getRowArray();

if ($existente) {
    // UPDATE: Actualiza enlace y observaciones del reporte existente
} else {
    // INSERT: Crea nuevo registro en tbl_reporte
}
```

**Comportamiento:**
- **Primera vez:** Crea registro nuevo en `tbl_reporte`
- **Siguiente vez:** Actualiza el registro existente con nuevo PDF (no duplica)

### 7.4 Ruta

**Archivo:** `app/Config/Routes.php`

```php
$routes->get('/documentos-sst/publicar-pdf/(:num)', 'DocumentosSSTController::publicarPDF/$1');
```

### 7.5 Boton en la Vista

**Archivo:** `app/Views/documentacion/carpeta.php`

```html
<a href="<?= base_url('documentos-sst/publicar-pdf/' . $docSST['id_documento']) ?>"
   class="btn btn-outline-dark" title="Publicar en Reportes"
   onclick="return confirm('¿Publicar este documento en Reportes? Será consultable desde reportList.')">
    <i class="bi bi-cloud-upload"></i>
</a>
```

---

## 8. BOTONES DE ACCION EN TABLA DOCUMENTOS SST

### 8.1 Lista Completa de Botones

| # | Icono | Estilo | Titulo | Funcion | Visibilidad |
|---|-------|--------|--------|---------|-------------|
| 1 | `bi-file-earmark-pdf` | `btn-danger` | Descargar PDF | Genera y descarga PDF al navegador | Siempre |
| 2 | `bi-patch-check-fill` | `btn-outline-danger` | PDF firmado publicado | Abre el PDF firmado ya publicado | Solo si `archivo_pdf` existe |
| 3 | `bi-eye` | `btn-outline-primary` | Ver documento | Abre vista previa del documento | Siempre |
| 4 | `bi-pencil` | `btn-outline-warning` | Editar documento | Abre editor de secciones | Siempre |
| 5 | `bi-pen` | `btn-outline-success` | Firmas y Audit Log | Abre estado de firmas | Siempre |
| 6 | `bi-cloud-upload` | `btn-outline-dark` | Publicar en Reportes | Publica PDF en reportList | Siempre (con confirm) |

### 8.2 URLs de cada Boton

```php
// 1. Descargar PDF
base_url('documentos-sst/exportar-pdf/' . $docSST['id_documento'])

// 2. PDF firmado publicado
$docSST['archivo_pdf']  // URL directa al archivo

// 3. Ver documento
base_url('documentos-sst/' . $cliente['id_cliente'] . '/programa-capacitacion/' . $docSST['anio'])

// 4. Editar documento
base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio'])

// 5. Firmas y Audit Log
base_url('firma/estado/' . $docSST['id_documento'])

// 6. Publicar en Reportes
base_url('documentos-sst/publicar-pdf/' . $docSST['id_documento'])
```

---

## 9. OCULTAMIENTO DE SECCION LEGACY "DOCUMENTOS"

### 9.1 Problema

La vista `carpeta.php` mostraba dos secciones de documentos:
1. **Documentos SST** (nuevo sistema, `tbl_documentos_sst`) — con los documentos generados
2. **Documentos** (sistema legacy, `tbl_doc_documentos`) — vacio, mostrando "No hay documentos en esta carpeta"

### 9.2 Solucion

**Archivo:** `app/Views/documentacion/carpeta.php`

Se envolvio la seccion legacy con una condicion:

```php
<?php if (!isset($tipoCarpetaFases)): ?>
<!-- Documentos con estado IA -->
<div class="col-12">
    <!-- ... seccion legacy de documentos (tbl_doc_documentos) ... -->
</div>
<?php endif; ?>
```

**Logica:** Cuando `$tipoCarpetaFases` esta definido (ej: `'capacitacion_sst'`), la seccion legacy se oculta porque el sistema nuevo de Documentos SST ya se muestra arriba. En carpetas sin tipo de fases, la seccion legacy sigue visible.

---

## 10. ESTRUCTURA DE DATOS EN REPORTLIST

### 10.1 Registro en tbl_reporte

Cuando se publica un documento SST (manual o automatico), se crea/actualiza un registro:

```sql
INSERT INTO tbl_reporte (
    titulo_reporte,      -- 'PRG-CAP-001 - Programa de Capacitacion en SST (v1)'
    id_detailreport,     -- 21 (Documento SG-SST)
    id_report_type,      -- 12 (Reportes SST)
    id_cliente,          -- ID del cliente
    enlace,              -- URL al PDF en uploads/{nit}/
    estado,              -- 'CERRADO'
    observaciones,       -- 'Documento publicado manualmente. Estado: aprobado. Año: 2026'
    created_at,
    updated_at
) VALUES (...);
```

### 10.2 Ubicacion Fisica del PDF

```
public/uploads/{nit_cliente}/{timestamp}_{codigo}_{titulo}.pdf
```

Ejemplo real:
```
public/uploads/901653912/1769526173_prg-cap-001_programa-de-capacitacion-en-sst.pdf
```

### 10.3 Consulta en reportList

El documento es consultable desde `/reportList` filtrando por:
- **Tipo de detalle:** Documento SG-SST
- **Tipo de reporte:** Reportes SST
- **Cliente:** El cliente al que pertenece

---

## 11. CAMBIOS EN BASE DE DATOS (EJECUTADOS)

### 11.1 Cambios en tbl_documentos_sst

```sql
-- ENUM actualizado con estados de firma (LOCAL y PRODUCCION)
ALTER TABLE tbl_documentos_sst
MODIFY COLUMN estado ENUM('borrador', 'generado', 'aprobado', 'pendiente_firma', 'firmado', 'obsoleto')
NOT NULL DEFAULT 'borrador';
```

### 11.2 Nuevo tipo en detail_report

```sql
-- ID 21 en ambos entornos
INSERT INTO detail_report (detail_report)
SELECT 'Documento SG-SST'
WHERE NOT EXISTS (SELECT 1 FROM detail_report WHERE detail_report = 'Documento SG-SST');
```

### 11.3 Correccion de datos

```sql
-- Documentos con estado vacio -> aprobado (si tienen version vigente)
UPDATE tbl_documentos_sst d
SET d.estado = 'aprobado'
WHERE d.estado = 'borrador'
  AND EXISTS (
    SELECT 1 FROM tbl_doc_versiones_sst v
    WHERE v.id_documento = d.id_documento
    AND v.estado = 'vigente'
  );
```

---

## 12. ARCHIVOS MODIFICADOS EN ESTA PARTE

### 12.1 Controladores

| Archivo | Cambios |
|---------|---------|
| `app/Controllers/DocumentacionController.php` | Redirect raiz/phva al dashboard, query documentos SST ampliado, breadcrumbs simplificados |
| `app/Controllers/DocumentosSSTController.php` | Nuevo metodo `publicarPDF()` con validacion anti-duplicados |
| `app/Controllers/FirmaElectronicaController.php` | Metodos `notificarConsultorFirmasCompletas()` y `publicarDocumentoFirmado()` |

### 12.2 Vistas

| Archivo | Cambios |
|---------|---------|
| `app/Views/documentacion/carpeta.php` | Badges todos los estados, version_texto dinamica, historial expandible, fecha aprobacion, target_blank, 6 botones accion, seccion legacy oculta |

### 12.3 Configuracion

| Archivo | Cambios |
|---------|---------|
| `app/Config/Routes.php` | Nueva ruta `/documentos-sst/publicar-pdf/(:num)` |

### 12.4 SQL

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/agregar_tipo_documento_sgsst.sql` | INSERT del tipo "Documento SG-SST" en detail_report |

---

## 13. FLUJO COMPLETO: DOCUMENTO -> REPORTLIST

```
Consultor genera documento SST (Fases 1-4)
         |
         v
Aprueba secciones en editor -> Aprueba Documento
         |
         v
Documento en estado 'aprobado' visible en carpeta/171
         |
         +---> OPCION A: Manual
         |         |
         |         v
         |    Clic en boton "Publicar" (bi-cloud-upload)
         |         |
         |         v
         |    publicarPDF() genera PDF con Dompdf
         |         |
         |         v
         |    Guarda en uploads/{nit}/
         |         |
         |         v
         |    INSERT/UPDATE en tbl_reporte
         |         |
         |         v
         |    Documento visible en /reportList
         |
         +---> OPCION B: Automatica (post-firmas)
                   |
                   v
              Solicitar firmas electronicas
                   |
                   v
              Firmantes firman via token unico
                   |
                   v
              Todas las firmas completas
                   |
                   v
              publicarDocumentoFirmado() genera PDF
                   |
                   v
              Guarda en uploads/{nit}/
                   |
                   v
              INSERT en tbl_reporte
                   |
                   v
              notificarConsultorFirmasCompletas() -> Email SendGrid
                   |
                   v
              Documento visible en /reportList con "(Firmado)"
```

---

## 14. ESTADO ACTUALIZADO DE TAREAS

### 14.1 Completadas en esta Parte

- [x] Tabla Documentos SST muestra todos los estados (borrador, generado, aprobado, firmado, pendiente_firma)
- [x] Version correcta (version_texto) en lugar de hardcoded v1.0
- [x] Historial de versiones expandible en la tabla
- [x] Columna Fecha Aprobacion
- [x] Todos los botones abren en nueva pestana (target_blank)
- [x] Breadcrumbs simplificados (todos llevan al dashboard)
- [x] Redirect automatico de carpetas raiz/phva al dashboard
- [x] Email al consultor cuando firmas se completan (SendGrid)
- [x] Publicacion automatica de PDF en reportList (post-firmas)
- [x] Publicacion manual de PDF en reportList (boton)
- [x] Validacion anti-duplicados en publicacion
- [x] Seccion legacy "Documentos" oculta cuando hay Documentos SST
- [x] Tipo "Documento SG-SST" en detail_report (ID 21)

### 14.2 Pendientes para Proximas Partes

- [ ] Implementar envio real de emails de solicitud de firma
- [ ] Vista de estado de firmas (`firma/estado/{id}`)
- [ ] Vista de verificacion publica de certificado (`firma/verificar/{token}`)
- [ ] Vista de audit log de firmas
- [ ] Opcion de subir imagen de firma (ademas de canvas)
- [ ] Campos email/cedula de firmantes en contexto del cliente
- [ ] Mas tipos de documentos SST (Plan Emergencias, PVE, Politica SST, etc.)
- [ ] QR Code en certificado de verificacion

---

## 15. TABLAS DE BD RELEVANTES

### 15.1 Estructura de tbl_documentos_sst (Actual)

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `id_documento` | INT AUTO_INCREMENT | PK |
| `id_cliente` | INT | FK a tbl_clientes |
| `tipo_documento` | VARCHAR(100) | Ej: 'programa_capacitacion' |
| `titulo` | VARCHAR(255) | Titulo del documento |
| `codigo` | VARCHAR(50) | Codigo unico (PRG-CAP-001) |
| `anio` | INT(4) | Ano de vigencia |
| `contenido` | LONGTEXT | JSON con secciones y datos |
| `version` | INT | Numero de version |
| `estado` | ENUM | borrador, generado, aprobado, pendiente_firma, firmado, obsoleto |
| `fecha_aprobacion` | DATETIME | Fecha y hora de ultima aprobacion |
| `aprobado_por` | INT | ID del usuario que aprobo |
| `motivo_version` | VARCHAR(255) | Motivo del cambio de version |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |
| `created_by` | INT | |
| `updated_by` | INT | |

### 15.2 Relacion con tbl_reporte

```
tbl_documentos_sst (id_documento)
         |
         | publicarPDF() o publicarDocumentoFirmado()
         v
tbl_reporte (id_reporte)
    - titulo_reporte: "{codigo} - {titulo} (v{version})"
    - id_detailreport: 21 (Documento SG-SST)
    - id_report_type: 12 (Reportes SST)
    - enlace: URL al PDF fisico
         |
         v
uploads/{nit_cliente}/{archivo}.pdf
```

---

## 16. GUIA PARA CREAR UN NUEVO TIPO DE DOCUMENTO SST

### PROMPT PARA FUTURAS SESIONES DE CLAUDE

> **Instruccion:** Cuando el usuario pida crear un nuevo tipo de documento SST (ej: "programa de mantenimiento", "plan de emergencias", "politica SST"), sigue esta guia paso a paso. El sistema ya tiene una arquitectura semi-generica pero con partes hardcodeadas que necesitan replicarse por cada nuevo tipo.

---

### DIAGNOSTICO: QUE ES GENERICO Y QUE NO

| Componente | Generico? | Accion requerida |
|------------|-----------|------------------|
| `TIPOS_DOCUMENTO` constante | NO - Solo tiene `programa_capacitacion` | **AGREGAR** nueva entrada con secciones |
| `CODIGOS_DOCUMENTO` constante | SI - Ya tiene 8 tipos | Verificar si el nuevo tipo ya esta, si no **AGREGAR** |
| `generarConIA()` metodo | SI - Recibe `$tipo` como parametro | No requiere cambios |
| `generar_con_ia.php` vista | SI - Usa `$tipo` y `$tipoDoc` dinamicamente | No requiere cambios |
| `generarConIAReal()` metodo | **NO** - Hardcodea `'programa_capacitacion'` | **REFACTORIZAR** para recibir `$tipo` |
| `getPromptBaseParaSeccion()` metodo | **NO** - Prompts solo de programa_capacitacion | **AGREGAR** prompts del nuevo tipo |
| `generarContenidoSeccion()` metodo | **NO** - Plantillas solo de programa_capacitacion | **AGREGAR** plantillas del nuevo tipo |
| `programaCapacitacion()` metodo | **NO** - Especifico | **CREAR** metodo equivalente para el nuevo tipo |
| `programa_capacitacion.php` vista previa | **NO** - Especifica | **CREAR** vista equivalente o **GENERALIZAR** |
| `pdf_template.php` | PARCIAL - Estructura generica, contenido especifico | Puede reutilizarse si la estructura es similar |
| `word_template.php` | PARCIAL - Similar al PDF | Puede reutilizarse |
| `Routes.php` | PARCIAL - Generacion es generica, vista previa no | **AGREGAR** ruta de vista previa |
| `carpeta.php` (tabla documentos) | **NO** - Links hardcodeados a programa-capacitacion | **AGREGAR** logica condicional por tipo |

---

### PASO A PASO PARA CREAR NUEVO DOCUMENTO

**Ejemplo: Programa de Mantenimiento** (`programa_mantenimiento`)

#### PASO 1: Definir secciones del documento

Antes de tocar codigo, definir con el usuario:
- Nombre completo del documento
- Lista de secciones con numero, nombre y key
- Cuales secciones se alimentan de tablas existentes vs IA/plantilla
- Codigo del documento (ej: PRG-MNT)

#### PASO 2: Agregar a TIPOS_DOCUMENTO

**Archivo:** `app/Controllers/DocumentosSSTController.php` (linea ~27)

```php
public const TIPOS_DOCUMENTO = [
    'programa_capacitacion' => [ ... ],  // EXISTENTE
    'programa_mantenimiento' => [
        'nombre' => 'Programa de Mantenimiento',
        'descripcion' => 'Programa de mantenimiento preventivo y correctivo',
        'secciones' => [
            ['numero' => 1,  'nombre' => 'Introduccion',          'key' => 'introduccion'],
            ['numero' => 2,  'nombre' => 'Objetivo General',      'key' => 'objetivo_general'],
            // ... definir todas las secciones ...
        ]
    ]
];
```

#### PASO 3: Agregar a CODIGOS_DOCUMENTO (si no existe)

**Archivo:** `app/Controllers/DocumentosSSTController.php` (linea ~47)

```php
public const CODIGOS_DOCUMENTO = [
    // ... existentes ...
    'programa_mantenimiento' => ['tipo' => 'PRG', 'tema' => 'MNT'],
];
```

#### PASO 4: REFACTORIZAR generarConIAReal() (CRITICO)

**Archivo:** `app/Controllers/DocumentosSSTController.php` (linea ~257)

**Estado actual (HARDCODEADO):**
```php
protected function generarConIAReal(string $seccion, array $cliente, ?array $contexto, int $estandares, int $anio, string $contextoAdicional): string
{
    $tipoDoc = self::TIPOS_DOCUMENTO['programa_capacitacion']; // <-- HARDCODED
    $datosIA = [
        'documento' => [
            'tipo_nombre' => 'Programa',                        // <-- HARDCODED
            'nombre' => 'Programa de Capacitacion en SST'       // <-- HARDCODED
        ],
    ];
}
```

**DEBE CAMBIARSE A:**
```php
protected function generarConIAReal(string $seccion, string $tipo, array $cliente, ?array $contexto, int $estandares, int $anio, string $contextoAdicional): string
{
    $tipoDoc = self::TIPOS_DOCUMENTO[$tipo];
    $datosIA = [
        'documento' => [
            'tipo_nombre' => $tipoDoc['nombre'],
            'nombre' => $tipoDoc['descripcion']
        ],
    ];
}
```

**IMPORTANTE:** Tambien actualizar la llamada en `generarSeccionIA()` para pasar `$tipo`:
```php
// Linea ~243 aprox, donde se llama generarConIAReal
$contenido = $this->generarConIAReal($seccionKey, $tipo, $cliente, $contexto, $estandares, $anio, $contextoAdicional);
```

#### PASO 5: Agregar prompts del nuevo tipo

**Archivo:** `app/Controllers/DocumentosSSTController.php` - metodo `getPromptBaseParaSeccion()` (linea ~306)

**Opcion A (Recomendada):** Hacer el metodo sensible al tipo:
```php
protected function getPromptBaseParaSeccion(string $seccion, int $estandares, string $tipo = 'programa_capacitacion'): string
{
    $promptsPorTipo = [
        'programa_capacitacion' => [
            'introduccion' => "Genera una introduccion para el Programa de Capacitacion...",
            // ... prompts existentes ...
        ],
        'programa_mantenimiento' => [
            'introduccion' => "Genera una introduccion para el Programa de Mantenimiento...",
            // ... nuevos prompts ...
        ],
    ];
    return $promptsPorTipo[$tipo][$seccion] ?? "Genera el contenido para la seccion {$seccion}";
}
```

**Opcion B:** Crear metodo separado `getPromptBaseMantenimiento()` (no recomendada, mas deuda tecnica).

#### PASO 6: Agregar plantillas estaticas del nuevo tipo

**Archivo:** `app/Controllers/DocumentosSSTController.php` - metodo `generarContenidoSeccion()` (linea ~390 aprox)

Este metodo genera contenido sin IA (plantilla estatica). Contiene un `switch` con contenido por defecto para cada seccion. Se debe:
1. Agregar parametro `$tipo`
2. Agregar cases para las secciones del nuevo tipo

#### PASO 7: Crear metodo de vista previa

**Archivo:** `app/Controllers/DocumentosSSTController.php`

Crear metodo equivalente a `programaCapacitacion()` (linea ~933):

```php
public function programaMantenimiento(int $idCliente, int $anio)
{
    // Misma logica que programaCapacitacion() pero con:
    // ->where('tipo_documento', 'programa_mantenimiento')
    // Y vista: 'documentos_sst/programa_mantenimiento'
}
```

**ALTERNATIVA MEJOR:** Crear un metodo generico `vistaPrevia()`:
```php
public function vistaPrevia(string $tipo, int $idCliente, int $anio)
{
    // Obtener documento por tipo, cliente, anio
    // Renderizar vista generica o por tipo
}
```

#### PASO 8: Crear vista de renderizado (si la estructura difiere)

**Si la estructura es similar a programa_capacitacion:**
- Reutilizar `programa_capacitacion.php` renombrandola a algo generico
- O crear `app/Views/documentos_sst/programa_mantenimiento.php` copiando la estructura

**Si la estructura es diferente:**
- Crear vista especifica desde cero

#### PASO 9: Agregar ruta de vista previa

**Archivo:** `app/Config/Routes.php`

```php
// Existente:
$routes->get('/documentos-sst/(:num)/programa-capacitacion/(:num)', 'DocumentosSSTController::programaCapacitacion/$1/$2');

// Nueva:
$routes->get('/documentos-sst/(:num)/programa-mantenimiento/(:num)', 'DocumentosSSTController::programaMantenimiento/$1/$2');
```

**O si se generalizo el metodo:**
```php
$routes->get('/documentos-sst/(:num)/(:segment)/(:num)', 'DocumentosSSTController::vistaPrevia/$2/$1/$3');
```

#### PASO 10: Actualizar links en carpeta.php

**Archivo:** `app/Views/documentacion/carpeta.php` (linea ~530 aprox)

Los botones "Ver" y "Editar" estan hardcodeados para programa-capacitacion:

```php
// ACTUAL (hardcodeado):
base_url('documentos-sst/' . $cliente['id_cliente'] . '/programa-capacitacion/' . $docSST['anio'])
base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio'])
```

**DEBE SER DINAMICO** usando `$docSST['tipo_documento']`:
```php
// Ver:
base_url('documentos-sst/' . $cliente['id_cliente'] . '/' . str_replace('_', '-', $docSST['tipo_documento']) . '/' . $docSST['anio'])

// Editar (la ruta de generacion YA es generica):
base_url('documentos/generar/' . $docSST['tipo_documento'] . '/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio'])
```

#### PASO 11: Asociar a carpeta (tbl_doc_plantilla_carpeta)

Insertar mapeo del nuevo tipo de documento a su carpeta de estandar correspondiente:

```sql
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion)
VALUES ('programa_mantenimiento', 'E3.2', 'Programa de Mantenimiento - Estandar 3.2');
```

El `codigo_carpeta` debe corresponder al estandar de la Resolucion 0312 donde se ubica el documento.

#### PASO 12: Verificar que tipoCarpetaFases se asigne correctamente

**Archivo:** `app/Controllers/DocumentacionController.php`

En el metodo `carpeta()`, verificar que la logica de `tipoCarpetaFases` detecte el nuevo tipo. Actualmente puede estar hardcodeada a `'capacitacion_sst'`. Si es necesario, agregar logica para el nuevo tipo.

---

### RESUMEN DE ARCHIVOS A TOCAR

```
OBLIGATORIOS:
1. app/Controllers/DocumentosSSTController.php
   - TIPOS_DOCUMENTO: agregar secciones
   - CODIGOS_DOCUMENTO: agregar codigo (si no existe)
   - generarConIAReal(): refactorizar para recibir $tipo
   - getPromptBaseParaSeccion(): agregar prompts nuevos
   - generarContenidoSeccion(): agregar plantillas nuevas
   - Nuevo metodo de vista previa (o generalizar existente)

2. app/Config/Routes.php
   - Agregar ruta de vista previa

3. app/Views/documentacion/carpeta.php
   - Hacer links dinamicos por tipo_documento

4. app/Views/documentos_sst/{nuevo_tipo}.php  (O generalizar)
   - Vista de renderizado del documento

OPCIONALES:
5. app/Views/documentos_sst/pdf_template.php
   - Si el PDF tiene estructura diferente

6. app/Views/documentos_sst/word_template.php
   - Si el Word tiene estructura diferente

7. app/Controllers/DocumentacionController.php
   - Si tipoCarpetaFases necesita logica adicional

8. SQL: INSERT en tbl_doc_plantilla_carpeta
   - Mapeo plantilla -> carpeta
```

### CHECKLIST DE VERIFICACION

Despues de crear el nuevo tipo, verificar:

- [ ] `generarConIA('nuevo_tipo', $idCliente)` carga correctamente el editor
- [ ] Cada seccion genera contenido con IA o plantilla
- [ ] Se puede guardar y aprobar cada seccion
- [ ] "Aprobar Documento" crea version en `tbl_doc_versiones_sst`
- [ ] Vista previa renderiza correctamente todas las secciones
- [ ] PDF se genera sin errores con Dompdf
- [ ] Word se genera sin errores
- [ ] El documento aparece en la tabla de carpeta.php
- [ ] Boton "Ver" abre la vista correcta (no programa-capacitacion)
- [ ] Boton "Editar" abre el editor correcto
- [ ] Boton "Publicar" genera PDF en reportList
- [ ] Boton "Descargar PDF" descarga correctamente
- [ ] El codigo generado es correcto (ej: PRG-MNT-001)
- [ ] El documento se asocia a la carpeta correcta del estandar

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 9 de 9*
