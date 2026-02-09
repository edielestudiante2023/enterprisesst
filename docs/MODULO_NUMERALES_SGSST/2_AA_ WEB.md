Estilos Vista WEB - Referencia Técnica

> **Nota sobre FIRMAS**: Para la sección de firmas de documentos, consultar:
> - `AA_PDF_FIRMAS.md` - Firmas para exportación PDF
> - `AA_WORD_FIRMAS.md` - Firmas para exportación Word
> - `SISTEMA_FIRMAS_DOCUMENTOS.md` - Documentación completa del sistema de firmas
>
> ⚠️ **REGLA CRÍTICA**: TODOS los documentos técnicos DEBEN incluir "Elaboró / Consultor SST"

---

## 1. ESTRUCTURA GENERAL DE LA PÁGINA

Layout General

┌─────────────────────────────────────────────────────────────────┐
│  BARRA HERRAMIENTAS (sticky, no-print)                          │
├─────────────────────────────────────────────────────────────────┤
│  PANEL APROBACIÓN (gradiente, no-print)                         │
├─────────────────────────────────────────────────────────────────┤
│  INFO DOCUMENTO (gris claro, no-print)                          │
├─────────────────────────────────────────────────────────────────┤
│  ENCABEZADO FORMAL (tabla 3 columnas)                           │
├─────────────────────────────────────────────────────────────────┤
│  SECCIONES DEL CONTENIDO                                        │
├─────────────────────────────────────────────────────────────────┤
│  CONTROL DE CAMBIOS                                             │
├─────────────────────────────────────────────────────────────────┤
│  FIRMAS                                                         │
├─────────────────────────────────────────────────────────────────┤
│  PIE DE DOCUMENTO                                               │
└─────────────────────────────────────────────────────────────────┘
2. DEPENDENCIAS CSS/JS

<!-- Bootstrap 5.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
3. ESTILOS PARA IMPRESIÓN
CSS Media Print (líneas 10-14)

@media print {
    .no-print { display: none !important; }
    .documento-contenido { padding: 20px !important; }
    body { font-size: 11pt; }
}
Clase	Comportamiento en Impresión
.no-print	Se oculta completamente
.documento-contenido	Padding reducido a 20px
body	Fuente 11pt
4. BARRA DE HERRAMIENTAS
Código (líneas 107-133)

<div class="no-print bg-dark text-white py-2 sticky-top">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Botones de navegación y exportación -->
        </div>
    </div>
</div>
Estilos
Propiedad	Valor	Descripción
background	bg-dark	Fondo oscuro (#212529)
color	text-white	Texto blanco
padding	py-2	Padding vertical 0.5rem
position	sticky-top	Fijo en la parte superior
display	.no-print	No se imprime
Botones
Botón	Clase	Icono
Volver	btn-outline-light btn-sm	bi-arrow-left
Historial	btn-outline-light btn-sm	bi-clock-history
PDF	btn-danger btn-sm	bi-file-earmark-pdf
Word	btn-primary btn-sm	bi-file-earmark-word
Actualizar	btn-warning btn-sm	bi-arrow-repeat
5. PANEL DE APROBACIÓN
Código (líneas 140-156)

<div class="panel-aprobacion no-print">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="badge bg-dark"><?= $documento['codigo'] ?></span>
                <span class="badge bg-light text-dark">v<?= $documento['version'] ?>.0</span>
                <span class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>Aprobado
                </span>
            </div>
            <small class="opacity-75">Documento firmado por el Consultor SST</small>
        </div>
    </div>
</div>
CSS Panel Aprobación (líneas 90-96)

.panel-aprobacion {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    color: white;
}
Propiedad	Valor
background	linear-gradient(135deg, #667eea 0%, #764ba2 100%)
border-radius	12px
padding	20px
margin-bottom	20px
color	white
6. INFO DOCUMENTO
Código (líneas 159-174)

<div class="info-documento no-print">
    <div class="row">
        <div class="col-md-4">
            <small class="text-muted">Tipo de documento:</small>
            <span class="fw-bold">Responsabilidades</span>
        </div>
        <!-- ... más columnas ... -->
    </div>
</div>
CSS Info Documento (líneas 98-103)

.info-documento {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
Propiedad	Valor
background-color	#f8f9fa
padding	15px
border-radius	8px
margin-bottom	20px
7. ENCABEZADO FORMAL WEB
Código (líneas 177-213)

<table class="encabezado-formal">
    <tr>
        <td class="encabezado-logo" rowspan="2">
            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo">
        </td>
        <td class="encabezado-titulo-central">
            <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
        </td>
        <td class="encabezado-info" rowspan="2">
            <table class="encabezado-info-table">
                <!-- Código, Versión, Fecha -->
            </table>
        </td>
    </tr>
    <tr>
        <td class="encabezado-titulo-central">
            <div class="nombre-doc">RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST</div>
        </td>
    </tr>
</table>
CSS Encabezado Formal (líneas 16-71)

.encabezado-formal {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

.encabezado-formal td {
    border: 1px solid #333;
    vertical-align: middle;
}

.encabezado-logo {
    width: 150px;
    padding: 10px;
    text-align: center;
}

.encabezado-logo img {
    max-width: 130px;
    max-height: 70px;
    object-fit: contain;
}

.encabezado-titulo-central {
    text-align: center;
    padding: 0;
}

.encabezado-titulo-central .sistema {
    font-size: 0.85rem;
    font-weight: bold;
    color: #333;
    padding: 8px 15px;
    border-bottom: 1px solid #333;
}

.encabezado-titulo-central .nombre-doc {
    font-size: 0.85rem;
    font-weight: bold;
    color: #333;
    padding: 8px 15px;
}

.encabezado-info {
    width: 170px;
    padding: 0;
}

.encabezado-info-table {
    width: 100%;
    border-collapse: collapse;
}

.encabezado-info-table td {
    border: none;
    border-bottom: 1px solid #333;
    padding: 3px 8px;
    font-size: 0.75rem;
}

.encabezado-info-table tr:last-child td {
    border-bottom: none;
}

.encabezado-info-table .label {
    font-weight: bold;
}
Comparación Encabezado: Web vs PDF vs Word
Propiedad	WEB	PDF	WORD
Ancho Logo	150px	120px	80px
Max img width	130px	100px	70px
Max img height	70px	60px	45px
Ancho Info	170px	140px	120px
Font títulos	0.85rem	10pt	9pt
Font info	0.75rem	8pt	8pt
Padding títulos	8px 15px	6px 10px	5px
margin-bottom	25px	20px	15px
8. SECCIONES DEL CONTENIDO
Código (líneas 216-225)

<?php if (!empty($contenido['secciones'])): ?>
    <?php foreach ($contenido['secciones'] as $seccion): ?>
        <div class="seccion">
            <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
            <div class="seccion-contenido">
                <?= $seccion['contenido'] ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
CSS Secciones (líneas 73-88)

.seccion {
    margin-bottom: 25px;
    page-break-inside: avoid;
}

.seccion-titulo {
    font-size: 1.1rem;
    font-weight: bold;
    color: #0d6efd;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.seccion-contenido {
    text-align: justify;
    line-height: 1.7;
}
Comparación Secciones: Web vs PDF vs Word
Propiedad	WEB	PDF	WORD
margin-bottom sección	25px	8px	6px
font-size título	1.1rem	11pt	11pt
border-bottom título	2px solid #e9ecef	1px solid #e9ecef	1px solid #ccc
padding-bottom título	8px	3px	2px
margin-bottom título	15px	5px	4px
line-height contenido	1.7	1.2	1.0
9. CONTROL DE CAMBIOS WEB
Código (líneas 227-264)

<div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
        <i class="bi bi-journal-text me-2"></i>CONTROL DE CAMBIOS
    </div>
    <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
        <thead>
            <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                <th style="width: 100px; text-align: center;">Version</th>
                <th>Descripcion del Cambio</th>
                <th style="width: 130px; text-align: center;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">
                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px;">
                        <?= $ver['version_texto'] ?>
                    </span>
                </td>
                <td><?= $ver['descripcion_cambio'] ?></td>
                <td style="text-align: center;"><?= date('d/m/Y') ?></td>
            </tr>
        </tbody>
    </table>
</div>
Estilos Control de Cambios WEB
Elemento	Estilos
Contenedor	margin-top: 40px; page-break-inside: avoid;
Título barra	background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px;
Tabla	table table-bordered; font-size: 0.85rem;
TH fondo	linear-gradient(135deg, #f8f9fa, #e9ecef)
Col Versión	width: 100px; text-align: center;
Col Fecha	width: 130px; text-align: center;
Badge versión	background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px;
Comparación Control Cambios: Web vs PDF vs Word
Propiedad	WEB	PDF	WORD
margin-top	40px	25px	20px
Título fondo	gradiente azul-morado	#0d6efd sólido	#0d6efd sólido
Título padding	10px 15px	8px 12px	5px 8px
Título border-radius	5px	ninguno	ninguno
TH fondo	gradiente gris	#e9ecef sólido	#e9ecef sólido
Col Versión width	100px	80px	80px
Col Fecha width	130px	90px	90px
Badge versión	Sí (pill)	No	No
10. SECCIÓN FIRMAS WEB
Código (líneas 266-316)

<div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
    <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
        <i class="bi bi-pen me-2"></i>FIRMA DE ACEPTACION
    </div>

    <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
        <tbody>
            <tr>
                <td style="vertical-align: top; padding: 25px; height: 200px; position: relative;">
                    <div class="text-center mb-3">
                        <strong style="color: #495057; font-size: 1rem;">RESPONSABLE DEL SG-SST</strong>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong style="color: #495057;">Nombre:</strong>
                        <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 300px;">
                            <?= $responsableSst['nombre'] ?>
                        </span>
                    </div>
                    <!-- Más campos... -->
                    <div style="text-align: center; margin-top: 30px;">
                        <?php if (!empty($firmaConsultor)): ?>
                            <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 70px; max-width: 200px;">
                        <?php endif; ?>
                        <div style="border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px;">
                            <small style="color: #666;">Firma</small>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
Estilos Firmas WEB
Elemento	Estilos
Contenedor	margin-top: 40px; page-break-inside: avoid;
Título barra	background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px;
Celda datos	padding: 25px; height: 200px;
Título firmante	font-size: 1rem; color: #495057;
Labels	color: #495057;
Línea datos	border-bottom: 1px dotted #999; min-width: 300px;
Imagen firma	max-height: 70px; max-width: 200px;
Línea firma	border-top: 1px solid #333; width: 60%;
Comparación Firmas: Web vs PDF vs Word
Propiedad	WEB	PDF	WORD
margin-top	40px	25px	20px
Título fondo	gradiente verde	#198754 sólido	#198754 sólido
Título border-radius	5px	ninguno	ninguno
Celda height	200px	no definido	no definido
Celda padding	25px	15px	10px
Línea datos	1px dotted #999	ninguna	ninguna
Imagen firma max-height	70px	60px	no aplica
Imagen firma max-width	200px	180px	no aplica
Línea firma width	60%	50%	40%
11. PIE DE DOCUMENTO WEB
Código (líneas 318-322)

<div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
    <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
    <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
</div>
Estilos
Clase/Estilo	Valor
text-center	Centrado
text-muted	Color #6c757d
mt-4	margin-top 1.5rem
pt-3	padding-top 1rem
border-top	1px solid #dee2e6
font-size	0.75rem
12. CONTENEDOR DEL DOCUMENTO
Código (líneas 136-137)

<div class="container my-4">
    <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">
Propiedad	Valor	Descripción
max-width	900px	Ancho máximo del documento
padding	40px	Espacio interno
margin	0 auto	Centrado horizontal
background	bg-white	Fondo blanco
shadow	Bootstrap shadow	Sombra suave
13. PALETA DE COLORES WEB
Uso	Color	Hex
Fondo página	Gris claro	bg-light (#f8f9fa)
Fondo documento	Blanco	bg-white (#fff)
Barra herramientas	Oscuro	bg-dark (#212529)
Panel aprobación inicio	Azul	#667eea
Panel aprobación fin	Morado	#764ba2
Títulos sección	Azul Bootstrap	#0d6efd
Control cambios inicio	Azul	#0d6efd
Control cambios fin	Morado	#6610f2
Firmas inicio	Verde	#198754
Firmas fin	Verde claro	#20c997
Texto principal	Negro	#333
Texto secundario	Gris	#6c757d
Bordes	Negro	#333
Labels formulario	Gris oscuro	#495057
14. TIPOGRAFÍA WEB
Elemento	Tamaño	Peso
Body	Bootstrap default (1rem = 16px)	normal
Títulos sección	1.1rem	bold
Contenido	heredado	normal
Encabezado títulos	0.85rem	bold
Encabezado info	0.75rem	normal/bold
Tablas	0.85rem	normal
Pie documento	0.75rem	normal
Firmante título	1rem	bold
15. RESUMEN COMPARATIVO: WEB vs PDF vs WORD
Aspecto	WEB	PDF	WORD
Framework CSS	Bootstrap 5.3	CSS puro	CSS + MSO
Fuente	System/Bootstrap	DejaVu Sans	Arial
Unidades	rem	pt	pt/px
Gradientes	Sí (títulos)	No	No
Border-radius	Sí (5px, 12px)	No	No
Shadows	Sí	No	No
Iconos	Bootstrap Icons	No	No
Elementos no-print	Sí (barra, paneles)	N/A	N/A
Imágenes firma	URL directa	Base64	No
Interactividad	Modales, botones	N/A	N/A
Comando para Replicar
"Usa el estilo WEB estándar: Bootstrap 5.3, encabezado tabla 3 cols (logo 150px, info 170px), títulos 1.1rem bold #0d6efd border-bottom 2px #e9ecef, line-height 1.7, Control Cambios gradiente azul-morado, Firmas gradiente verde, contenedor max-width 900px padding 40px shadow"

---

## 16. ⚠️ FIRMA DEL CONSULTOR EN VISTA WEB (CRÍTICO)

**La firma del Consultor SST tiene DOS fuentes posibles (en orden de prioridad):**

### Fuente 1: Firma Electrónica (si firmó electrónicamente el documento)

```php
$firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
```

- Origen: `tbl_doc_firma_evidencias` (cuando el consultor firma un documento específico)
- Formato: URL con imagen base64 en `$firmaConsultorElectronica['evidencia']['firma_imagen']`

### Fuente 2: Firma Física (del perfil del consultor)

```php
$firmaConsultorFisica = $consultor['firma_consultor'] ?? '';
```

- Origen: `tbl_consultor.firma_consultor` (firma subida en el perfil)
- Formato: Nombre de archivo en `uploads/`

### Implementación correcta para WEB

Para WEB, NO se necesita conversión a base64. Usar URL directa:

```php
// Definir fuentes de firma
$firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
$firmaConsultorFisica = $consultor['firma_consultor'] ?? '';

// Mostrar (prioridad: electrónica > física)
<?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
    <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>"
         alt="Firma Consultor" style="max-height: 60px; max-width: 150px;">
<?php elseif (!empty($firmaConsultorFisica)): ?>
    <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>"
         alt="Firma Consultor" style="max-height: 60px; max-width: 150px;">
<?php endif; ?>
```

### ❌ ERROR COMÚN (NO hacer esto)

```php
// INCORRECTO: Solo busca firma electrónica, ignora firma física del perfil
$firmaConsultor = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
<?php if ($firmaConsultor): ?> <!-- Si no firmó electrónicamente, nunca muestra firma -->
```

---

## 17. DIFERENCIA CLAVE: WEB vs PDF

| Aspecto | WEB | PDF (DOMPDF) |
|---------|-----|--------------|
| Firma física consultor | URL directa `base_url('uploads/' . $firma)` | Convertir a Base64 |
| Firma electrónica | URL directa (ya es base64) | URL directa (ya es base64) |
| Motivo | El navegador puede cargar URLs | DOMPDF no puede cargar archivos externos |

---

## 18. TIPOS DE DOCUMENTO Y SUS FIRMANTES (Referencia Rápida)

| Tipo Documento | Firmantes | Notas |
|----------------|-----------|-------|
| `responsabilidades_responsable_sgsst` | 1: Consultor SST | Solo firma consultor |
| `responsabilidades_rep_legal_sgsst` (sin 2do firmante) | 2: Elaboró + Aprobó | Consultor + Rep. Legal |
| `responsabilidades_rep_legal_sgsst` (con 2do firmante) | 3: Elaboró + Aprobó + Revisó | Consultor + Rep. Legal + Vigía/Delegado |
| Otros documentos (≤10 estándares) | 2: Elaboró + Aprobó | Consultor + Rep. Legal |
| Otros documentos (>10 estándares) | 3: Elaboró + Revisó + Aprobó | Consultor + Vigía/COPASST + Rep. Legal |

### ⚠️ REGLA DE ORO

**NUNCA** crear un tipo de firma que excluya al Consultor en documentos técnicos.
Si un nuevo tipo de documento excluye "Elaboró/Consultor", está **violando la regla de auditoría**.

---

## 19. CONVERSIÓN MARKDOWN A HTML (Parsedown)

### Problema

El contenido generado por IA usa sintaxis Markdown (`**texto**` para negrita, `*texto*` para cursiva). Si se muestra directamente en la vista, los asteriscos aparecen literalmente en lugar de aplicar el formato.

### Solución: Usar Parsedown

**Parsedown** es una librería PHP que convierte Markdown a HTML. Ya está instalada en el proyecto.

### Instalación (si no está)

```bash
composer require erusev/parsedown
```

### Implementación en Vistas

```php
<!-- Crear instancia ANTES del loop (evita múltiples instancias) -->
<?php if (!empty($contenido['secciones'])): ?>
    <?php $parsedown = new \Parsedown(); ?>
    <?php foreach ($contenido['secciones'] as $seccion): ?>
        <div class="seccion">
            <h3 class="seccion-titulo">
                <?= esc($seccion['numero'] ?? '') ?>. <?= esc($seccion['titulo']) ?>
            </h3>
            <div class="seccion-contenido">
                <?= $parsedown->text($seccion['contenido'] ?? '') ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
```

### ❌ ERROR COMÚN (NO hacer esto)

```php
<!-- INCORRECTO: Muestra asteriscos literales -->
<?= $seccion['contenido'] ?? '' ?>

<!-- INCORRECTO: Crear instancia dentro del loop (ineficiente) -->
<?php foreach ($contenido['secciones'] as $seccion): ?>
    <?= (new \Parsedown())->text($seccion['contenido']) ?>
<?php endforeach; ?>
```

### ✅ Conversiones que realiza Parsedown

| Markdown | HTML Resultante |
|----------|-----------------|
| `**texto**` | `<strong>texto</strong>` |
| `__texto__` | `<strong>texto</strong>` |
| `*texto*` | `<em>texto</em>` |
| `_texto_` | `<em>texto</em>` |
| `# Título` | `<h1>Título</h1>` |
| `- item` | `<ul><li>item</li></ul>` |
| `1. item` | `<ol><li>item</li></ol>` |

### Vistas que ya usan Parsedown

| Vista | Ruta |
|-------|------|
| Plan Objetivos y Metas | `app/Views/documentos_sst/plan_objetivos_metas.php` |

### ⚠️ IMPORTANTE

Cuando se crea una **nueva vista de documento SST**, verificar que el contenido de las secciones pase por `$parsedown->text()` para evitar que aparezcan asteriscos en el texto.

---

## 20. TABLAS MARKDOWN: Normalización de Formatos (CRÍTICO)

### Problema

La IA genera tablas Markdown en **dos formatos** distintos:

| Formato | Ejemplo | Nombre |
|---------|---------|--------|
| Con pipes enclosing | `\| Col1 \| Col2 \| Col3 \|` | Formato estándar |
| Sin pipes enclosing | `Col1 \| Col2 \| Col3` | Formato simplificado |

La función `convertirTablasMarkdown()` originalmente solo detectaba el **formato estándar** (con `|` al inicio y final). El formato simplificado se renderizaba como **texto plano con pipes visibles** en vez de tabla HTML.

### Causa Raíz

El regex de detección exigía que la línea empezara Y terminara con `|`:

```php
// Solo detecta: | Col1 | Col2 | Col3 |
// NO detecta:   Col1 | Col2 | Col3
if (preg_match('/^\|(.+)\|$/', $lineaTrim)) {
```

### Solución: Normalización antes de la detección

Se agrega un bloque de normalización **antes** del regex de detección. Si una línea contiene `|` pero no empieza con `|`, se le agregan pipes al inicio y final:

```php
foreach ($lineas as $linea) {
    $lineaTrim = trim($linea);

    // Normalizar tablas Markdown sin pipes al inicio/final
    // "Col1 | Col2 | Col3" → "| Col1 | Col2 | Col3 |"
    if (strpos($lineaTrim, '|') !== false && substr($lineaTrim, 0, 1) !== '|') {
        $lineaTrim = '| ' . $lineaTrim . ' |';
    }

    // Detectar linea de tabla (empieza con |)
    if (preg_match('/^\|(.+)\|$/', $lineaTrim)) {
        // ... lógica existente funciona para ambos formatos
    }
}
```

### Vistas que incluyen esta normalización

| Vista | Ruta |
|-------|------|
| Programa Capacitación | `app/Views/documentos_sst/programa_capacitacion.php` |
| Documento Genérico | `app/Views/documentos_sst/documento_generico.php` |
| Procedimiento Control Documental | `app/Views/documentos_sst/procedimiento_control_documental.php` |
| Programa Promoción Prevención Salud | `app/Views/documentos_sst/programa_promocion_prevencion_salud.php` |

### REGLA: Al crear nueva vista con `convertirTablasMarkdown()`

**SIEMPRE** incluir el bloque de normalización antes del regex `^\|(.+)\|$`. Sin este bloque, las tablas generadas por la IA en formato simplificado se mostrarán como texto plano.