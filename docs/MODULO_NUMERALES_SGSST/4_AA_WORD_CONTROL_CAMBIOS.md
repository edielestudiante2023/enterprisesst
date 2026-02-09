Estilos TABLA CONTROL DE CAMBIOS - Referencia Técnica
1. ESTRUCTURA ESTÁNDAR
Orden de Columnas (OBLIGATORIO)
#	Columna	Ancho	Contenido
1	Versión	80px	Ej: 1.0, 2.0, 2.1
2	Descripción del Cambio	Flexible (resto)	Texto descriptivo
3	Fecha	90px	Formato: dd/mm/YYYY
2. CÓDIGO PDF
Ubicación: pdf_template.php:395-421

<!-- CONTROL DE CAMBIOS - PDF -->
<div class="seccion" style="margin-top: 25px;">
    <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        CONTROL DE CAMBIOS
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
            <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
            <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
        </tr>
        <?php if (!empty($versiones)): ?>
            <?php foreach ($versiones as $idx => $ver): ?>
            <tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                <td><?= esc($ver['descripcion_cambio']) ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td style="text-align: center; font-weight: bold;">1.0</td>
                <td>Elaboracion inicial del documento</td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>
3. CÓDIGO WORD
Ubicación: word_template.php:281-307

<!-- CONTROL DE CAMBIOS - WORD -->
<div class="seccion" style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
        CONTROL DE CAMBIOS
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
            <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
            <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
        </tr>
        <?php if (!empty($versiones)): ?>
            <?php foreach ($versiones as $ver): ?>
            <tr>
                <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                <td><?= esc($ver['descripcion_cambio']) ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td style="text-align: center; font-weight: bold;">1.0</td>
                <td>Elaboracion inicial del documento</td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>
4. ESTILOS DEL TÍTULO/BARRA
Comparación PDF vs Word
Propiedad	PDF	Word
background-color	#0d6efd	#0d6efd
color	white	white
padding	8px 12px	5px 8px
font-weight	bold	bold
font-size	10pt	heredado (11pt)
border	ninguno	none
margin-top contenedor	25px	20px
Snippet Título

<!-- PDF -->
<div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
    CONTROL DE CAMBIOS
</div>

<!-- WORD -->
<div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
    CONTROL DE CAMBIOS
</div>
5. ESTILOS DE LA TABLA
Tabla Contenedora
Propiedad	Valor
width	100%
margin-top	0 (pegada al título)
border-collapse	collapse (heredado de .tabla-contenido)
Encabezados (TH)
Columna	Width	Estilos
Versión	80px	background-color: #e9ecef; color: #333;
Descripción	automático	background-color: #e9ecef; color: #333;
Fecha	90px	background-color: #e9ecef; color: #333;
Celdas (TD)
Columna	Estilos
Versión	text-align: center; font-weight: bold;
Descripción	sin estilos adicionales (text-align: left)
Fecha	text-align: center;
Filas Alternas (Solo PDF)

<tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
Fila	Fondo
Par (0, 2, 4...)	Blanco (sin estilo)
Impar (1, 3, 5...)	#f8f9fa (gris muy claro)
6. ANCHOS DE COLUMNA (CRÍTICO)

┌──────────┬─────────────────────────────────────────┬──────────┐
│  80px    │              FLEXIBLE                   │   90px   │
│ Versión  │       Descripción del Cambio            │  Fecha   │
└──────────┴─────────────────────────────────────────┴──────────┘
Columna	Ancho	Justificación
Versión	80px	Suficiente para "10.0" con padding
Descripción	Resto	Se expande para ocupar espacio disponible
Fecha	90px	Suficiente para "31/12/2026" con padding
7. PALETA DE COLORES
Elemento	Color	Hex
Barra título fondo	Azul Bootstrap	#0d6efd
Barra título texto	Blanco	white
TH fondo	Gris claro	#e9ecef
TH texto	Gris oscuro	#333
TD texto	Gris oscuro	#333 (heredado)
Fila alterna	Gris muy claro	#f8f9fa
Bordes	Gris medio	#999 (heredado de .tabla-contenido)
8. FORMATO DE DATOS
Columna Versión

<?= esc($ver['version_texto']) ?>
// Ejemplos: "1.0", "2.0", "2.1", "3.0"
Formato	Ejemplo
Mayor.Menor	1.0, 2.1, 10.0
Columna Descripción

<?= esc($ver['descripcion_cambio']) ?>
// Ejemplos: "Elaboración inicial del documento", "Actualización de procedimientos"
Columna Fecha

<?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?>
// Formato: dd/mm/YYYY
// Ejemplo: "01/02/2026"
9. FILA POR DEFECTO (Sin Versiones)
Cuando no hay versiones registradas en tbl_doc_versiones_sst:


<tr>
    <td style="text-align: center; font-weight: bold;">1.0</td>
    <td>Elaboracion inicial del documento</td>
    <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
</tr>
Campo	Valor por Defecto
Versión	1.0
Descripción	Elaboracion inicial del documento
Fecha	created_at del documento o fecha actual
10. SNIPPET REUTILIZABLE COMPLETO
PDF

<!-- CONTROL DE CAMBIOS - PDF ESTÁNDAR -->
<div class="seccion" style="margin-top: 25px;">
    <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        CONTROL DE CAMBIOS
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
            <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
            <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
        </tr>
        <?php if (!empty($versiones)): ?>
            <?php foreach ($versiones as $idx => $ver): ?>
            <tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                <td><?= esc($ver['descripcion_cambio']) ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td style="text-align: center; font-weight: bold;">1.0</td>
                <td>Elaboracion inicial del documento</td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>
WORD

<!-- CONTROL DE CAMBIOS - WORD ESTÁNDAR -->
<div class="seccion" style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
        CONTROL DE CAMBIOS
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
            <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
            <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
        </tr>
        <?php if (!empty($versiones)): ?>
            <?php foreach ($versiones as $ver): ?>
            <tr>
                <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                <td><?= esc($ver['descripcion_cambio']) ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td style="text-align: center; font-weight: bold;">1.0</td>
                <td>Elaboracion inicial del documento</td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>
11. RESUMEN VISUAL

┌─────────────────────────────────────────────────────────────────┐
│  CONTROL DE CAMBIOS                                             │
│  (fondo: #0d6efd, texto: white, padding: 8px 12px PDF/5px 8px W)│
├──────────┬─────────────────────────────────────────┬────────────┤
│ Version  │       Descripcion del Cambio            │   Fecha    │
│ (80px)   │           (flexible)                    │   (90px)   │
│ #e9ecef  │              #e9ecef                    │  #e9ecef   │
│ center   │               left                      │   center   │
│ bold     │                                         │            │
├──────────┼─────────────────────────────────────────┼────────────┤
│   1.0    │ Elaboracion inicial del documento       │ 01/02/2026 │
│  center  │                                         │   center   │
│   bold   │                                         │            │
├──────────┼─────────────────────────────────────────┼────────────┤
│   2.0    │ Actualización de procedimientos         │ 15/03/2026 │
│ #f8f9fa  │ (fila alterna solo en PDF)              │  #f8f9fa   │
└──────────┴─────────────────────────────────────────┴────────────┘
Comando para Replicar
"Usa la tabla CONTROL DE CAMBIOS estándar: título #0d6efd white, columnas Versión 80px center bold | Descripción flexible left | Fecha 90px center, TH fondo #e9ecef texto #333, filas alternas #f8f9fa (PDF), fecha formato dd/mm/YYYY"