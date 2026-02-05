Estilos del CUERPO del Documento PDF - Referencia Técnica
1. ESTRUCTURA GENERAL DEL CUERPO
HTML Base (líneas 346-390)

<?php if (!empty($contenido['secciones'])): ?>
    <?php foreach ($contenido['secciones'] as $seccion): ?>
        <div class="seccion">
            <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
            <div class="seccion-contenido">
                <?= convertirMarkdownAHtmlPdf($seccion['contenido']) ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
2. ESTILOS CSS DEL BODY

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.15;
    color: #333;
}

@page {
    margin: 2cm 1.5cm;
}
Propiedad	Valor	Descripción
font-family	DejaVu Sans, Arial, sans-serif	Fuente con soporte UTF-8 para Dompdf
font-size	10pt	Tamaño base del texto
line-height	1.15	Interlineado compacto
color	#333	Gris oscuro (no negro puro)
margin @page	2cm 1.5cm	Márgenes: 2cm arriba/abajo, 1.5cm lados
3. SECCIONES
CSS Secciones (líneas 227-258)

.seccion {
    margin-bottom: 8px;
}

.seccion-titulo {
    font-size: 11pt;
    font-weight: bold;
    color: #0d6efd;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 3px;
    margin-bottom: 5px;
    margin-top: 8px;
}

.seccion-contenido {
    text-align: justify;
    line-height: 1.2;
}
Valores de Referencia - Secciones
Elemento	Propiedad	Valor
Sección contenedor	margin-bottom	8px
Título sección	font-size	11pt
font-weight	bold
color	#0d6efd (azul Bootstrap)
border-bottom	1px solid #e9ecef
padding-bottom	3px
margin-bottom	5px
margin-top	8px
Contenido sección	text-align	justify
line-height	1.2
4. PÁRRAFOS
CSS Párrafos (líneas 247-249)

.seccion-contenido p {
    margin: 3px 0;
}
Generación en PHP (línea 94)

$resultado[] = '<p style="margin: 3px 0;">' . $lineaProcesada . '</p>';
Propiedad	Valor
margin-top	3px
margin-bottom	3px
margin-left/right	0
5. LISTAS (UL/LI)
CSS Listas (líneas 251-258)

.seccion-contenido ul {
    margin: 3px 0 3px 15px;
    padding-left: 0;
}

.seccion-contenido li {
    margin-bottom: 2px;
}
Generación en PHP (líneas 82-86, 110)

// Listas ordenadas
'<ol style="margin: 3px 0 3px 15px; padding-left: 15px;">'

// Listas no ordenadas  
'<ul style="margin: 3px 0 3px 15px; padding-left: 0;">'

// Items de lista
'<li>' . $contenidoLista . '</li>'
Valores de Referencia - Listas
Elemento	Propiedad	Valor
UL	margin	3px 0 3px 15px
padding-left	0
OL	margin	3px 0 3px 15px
padding-left	15px
LI	margin-bottom	2px
6. TABLAS DE CONTENIDO
CSS Tablas (líneas 261-279)

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
Generación en PHP - Tabla Markdown (líneas 120-139)

$html = '<table class="tabla-contenido" style="width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt;">';

// Encabezados
'<th style="border: 1px solid #999; padding: 5px 8px; background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">'

// Celdas normales
'<td style="border: 1px solid #999; padding: 5px 8px;">'
Valores de Referencia - Tablas
Elemento	Propiedad	Valor
Tabla	width	100%
border-collapse	collapse
margin	10px 0
font-size	9pt
TH (encabezado)	border	1px solid #999
padding	5px 8px
background-color	#0d6efd
color	white
font-weight	bold
text-align	center
TD (celda)	border	1px solid #999
padding	5px 8px
7. FORMATO DE TEXTO
CSS Negritas (líneas 296-299)

strong, b {
    font-weight: bold;
}
Conversión Markdown → HTML (líneas 69-80)

// Negrita: **texto** → <strong>texto</strong>
preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $texto);

// Cursiva: *texto* → <em>texto</em>
preg_replace('/(?<!\{)\*([^*]+)\*(?!\})/', '<em>$1</em>', $texto);
8. SALTOS DE LÍNEA
CSS (líneas 292-294)

br {
    line-height: 0.5;
}
Propiedad	Valor	Propósito
line-height	0.5	Reduce espacio en saltos de línea
9. SECCIÓN CONTROL DE CAMBIOS
HTML (líneas 395-421)

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
        <!-- filas de versiones -->
    </table>
</div>
Valores Control de Cambios
Elemento	Propiedad	Valor
Contenedor	margin-top	25px
Título barra	background-color	#0d6efd
color	white
padding	8px 12px
font-weight	bold
font-size	10pt
TH tabla	background-color	#e9ecef
color	#333
Col Versión	width	80px
Col Fecha	width	90px
10. PIE DE DOCUMENTO
HTML (líneas 792-795)

<div class="pie-documento">
    <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
    <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
</div>
CSS (líneas 282-289)

.pie-documento {
    margin-top: 15px;
    padding-top: 8px;
    border-top: 1px solid #ccc;
    text-align: center;
    font-size: 8pt;
    color: #666;
}
Propiedad	Valor
margin-top	15px
padding-top	8px
border-top	1px solid #ccc
text-align	center
font-size	8pt
color	#666
11. PALETA DE COLORES PDF
Uso	Código Hex	Muestra
Texto principal	#333	Gris oscuro
Títulos sección	#0d6efd	Azul Bootstrap
Bordes tabla	#999	Gris medio
Fondo TH tabla	#0d6efd	Azul Bootstrap
Texto TH	white	Blanco
Fondo TH secundario	#e9ecef	Gris claro
Borde título sección	#e9ecef	Gris claro
Borde pie documento	#ccc	Gris claro
Texto pie/secundario	#666	Gris medio
Fondo Control Cambios	#0d6efd	Azul Bootstrap
Fondo Firmas	#198754	Verde Bootstrap
Filas alternas	#f8f9fa	Gris muy claro
12. TIPOGRAFÍA PDF
Elemento	Fuente	Tamaño	Peso	Color
Body	DejaVu Sans	10pt	normal	#333
Título sección	DejaVu Sans	11pt	bold	#0d6efd
Contenido	DejaVu Sans	10pt	normal	#333
Tablas	DejaVu Sans	9pt	normal	#333
TH tablas	DejaVu Sans	9pt	bold	white
Pie documento	DejaVu Sans	8pt	normal	#666
Info encabezado	DejaVu Sans	8pt	normal/bold	#333
13. SNIPPET REUTILIZABLE - CUERPO PDF

<!-- SECCIÓN ESTÁNDAR PDF -->
<div class="seccion" style="margin-bottom: 8px;">
    <div class="seccion-titulo" style="font-size: 11pt; font-weight: bold; color: #0d6efd; border-bottom: 1px solid #e9ecef; padding-bottom: 3px; margin-bottom: 5px; margin-top: 8px;">
        TÍTULO DE LA SECCIÓN
    </div>
    <div class="seccion-contenido" style="text-align: justify; line-height: 1.2;">
        <p style="margin: 3px 0;">Contenido del párrafo...</p>
        
        <ul style="margin: 3px 0 3px 15px; padding-left: 0;">
            <li style="margin-bottom: 2px;">Item de lista</li>
        </ul>
        
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt;">
            <tr>
                <th style="border: 1px solid #999; padding: 5px 8px; background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">Encabezado</th>
            </tr>
            <tr>
                <td style="border: 1px solid #999; padding: 5px 8px;">Contenido</td>
            </tr>
        </table>
    </div>
</div>
Comando para Replicar
"Usa el estilo de cuerpo PDF estándar: fuente DejaVu Sans 10pt #333, títulos 11pt bold #0d6efd con border-bottom #e9ecef, párrafos margin 3px, tablas 9pt con TH #0d6efd white, bordes #999, line-height 1.2 justify"