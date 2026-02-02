Estilos TABLA CONTROL DE CAMBIOS PDF - Referencia Técnica
1. ESTRUCTURA ESTÁNDAR
Orden de Columnas (OBLIGATORIO)
#	Columna	Ancho	Alineación	Contenido
1	Versión	80px	center + bold	Ej: 1.0, 2.0
2	Descripción del Cambio	Flexible	left	Texto descriptivo
3	Fecha	90px	center	Formato: dd/mm/YYYY
2. UBICACIÓN EN CÓDIGO
Archivo: pdf_template.php:395-421

3. CÓDIGO HTML COMPLETO

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
4. ESTILOS DEL CONTENEDOR

<div class="seccion" style="margin-top: 25px;">
Propiedad	Valor	Descripción
margin-top	25px	Separación del contenido anterior
5. ESTILOS DE LA BARRA TÍTULO

<div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
    CONTROL DE CAMBIOS
</div>
Propiedad	Valor	Descripción
background-color	#0d6efd	Azul Bootstrap
color	white	Texto blanco
padding	8px 12px	8px arriba/abajo, 12px lados
font-weight	bold	Negrita
font-size	10pt	Tamaño de fuente
6. ESTILOS DE LA TABLA
Tabla Contenedora

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
Propiedad	Valor	Descripción
width	100%	Ancho completo
margin-top	0	Pegada al título (sin espacio)
border-collapse	collapse	Heredado de .tabla-contenido
7. ESTILOS DE ENCABEZADOS (TH)
Columna Versión

<th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
Propiedad	Valor
width	80px
background-color	#e9ecef
color	#333
border	1px solid #999 (heredado)
padding	5px 8px (heredado)
Columna Descripción

<th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
Propiedad	Valor
width	Automático (resto del espacio)
background-color	#e9ecef
color	#333
Columna Fecha

<th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
Propiedad	Valor
width	90px
background-color	#e9ecef
color	#333
8. ESTILOS DE FILAS (TR)
Filas Alternas (Zebra Striping)

<tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
Índice	Fondo	Descripción
Par (0, 2, 4...)	Blanco	Sin estilo adicional
Impar (1, 3, 5...)	#f8f9fa	Gris muy claro
9. ESTILOS DE CELDAS (TD)
Columna Versión

<td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
Propiedad	Valor
text-align	center
font-weight	bold
border	1px solid #999 (heredado)
padding	5px 8px (heredado)
Columna Descripción

<td><?= esc($ver['descripcion_cambio']) ?></td>
Propiedad	Valor
text-align	left (default)
font-weight	normal (default)
Columna Fecha

<td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
Propiedad	Valor
text-align	center
font-weight	normal (default)
10. ANCHOS DE COLUMNA

┌──────────────┬───────────────────────────────────────┬──────────────┐
│    80px      │              FLEXIBLE                 │    90px      │
│   Versión    │       Descripción del Cambio          │    Fecha     │
│   center     │              left                     │   center     │
│    bold      │                                       │              │
└──────────────┴───────────────────────────────────────┴──────────────┘
11. PALETA DE COLORES
Elemento	Color	Hex
Barra título fondo	Azul Bootstrap	#0d6efd
Barra título texto	Blanco	white
TH fondo	Gris claro	#e9ecef
TH texto	Gris oscuro	#333
TD texto	Gris oscuro	#333 (heredado body)
Fila alterna (impar)	Gris muy claro	#f8f9fa
Bordes tabla	Gris medio	#999
12. TIPOGRAFÍA
Elemento	Fuente	Tamaño	Peso
Título barra	DejaVu Sans	10pt	bold
TH tabla	DejaVu Sans	9pt (heredado)	bold (heredado)
TD Versión	DejaVu Sans	9pt	bold
TD Descripción	DejaVu Sans	9pt	normal
TD Fecha	DejaVu Sans	9pt	normal
13. FORMATO DE DATOS
Versión

<?= esc($ver['version_texto']) ?>
Formato	Ejemplos
Mayor.Menor	1.0, 2.0, 2.1, 10.0
Descripción

<?= esc($ver['descripcion_cambio']) ?>
Tipo	Ejemplos
Texto libre	Elaboración inicial del documento
Actualización de procedimientos
Corrección de formato
Fecha

<?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?>
Formato	Ejemplo
dd/mm/YYYY	01/02/2026, 15/12/2025
14. FILA POR DEFECTO
Cuando $versiones está vacío:


<tr>
    <td style="text-align: center; font-weight: bold;">1.0</td>
    <td>Elaboracion inicial del documento</td>
    <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
</tr>
Campo	Valor por Defecto
Versión	1.0
Descripción	Elaboracion inicial del documento
Fecha	created_at del documento o fecha actual
15. ESTILOS HEREDADOS DE .tabla-contenido

table.tabla-contenido {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
    font-size: 9pt;
}

table.tabla-contenido th,
table.tabla-contenido td {
    border: 1px solid #999;
    padding: 5px 8px;
}

table.tabla-contenido th {
    background-color: #0d6efd;
    color: white;
    font-weight: bold;
    text-align: center;
}
Nota: Los TH de Control de Cambios sobreescriben el fondo azul con #e9ecef gris claro.

16. SNIPPET REUTILIZABLE PDF

<!-- ============================================== -->
<!-- SECCION: CONTROL DE CAMBIOS - PDF             -->
<!-- ============================================== -->
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
17. RESUMEN VISUAL

                         CONTROL DE CAMBIOS
┌─────────────────────────────────────────────────────────────────┐
│  background: #0d6efd | color: white | padding: 8px 12px         │
│  font-weight: bold | font-size: 10pt                            │
└─────────────────────────────────────────────────────────────────┘
┌──────────────┬───────────────────────────────────┬──────────────┐
│   Version    │     Descripcion del Cambio        │    Fecha     │
│    80px      │          flexible                 │    90px      │
│   #e9ecef    │           #e9ecef                 │   #e9ecef    │
│    #333      │            #333                   │    #333      │
├──────────────┼───────────────────────────────────┼──────────────┤
│     1.0      │ Elaboracion inicial del documento │  01/02/2026  │
│   center     │            left                   │    center    │
│    bold      │                                   │              │
├──────────────┼───────────────────────────────────┼──────────────┤
│     2.0      │ Actualización de procedimientos   │  15/03/2026  │
│   #f8f9fa    │          #f8f9fa                  │   #f8f9fa    │
│  (alterno)   │         (alterno)                 │  (alterno)   │
└──────────────┴───────────────────────────────────┴──────────────┘
     border: 1px solid #999 | padding: 5px 8px
Comando para Replicar
"Usa la tabla CONTROL DE CAMBIOS PDF estándar: título barra #0d6efd white padding 8px 12px font 10pt bold, columnas Versión 80px | Descripción flexible | Fecha 90px, TH fondo #e9ecef texto #333, TD Versión center bold, TD Fecha center, filas alternas #f8f9fa, bordes #999 padding 5px 8px, fecha dd/mm/YYYY"