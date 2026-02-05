Estilos del CUERPO del Documento WORD - Referencia Técnica
1. ESTRUCTURA GENERAL DEL CUERPO
HTML Base (líneas 232-276)

<?php if (!empty($contenido['secciones'])): ?>
    <?php foreach ($contenido['secciones'] as $seccion): ?>
        <div class="seccion">
            <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
            <div class="seccion-contenido">
                <?= convertirMarkdownAHtml($seccion['contenido'], true) ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
2. ESTILOS CSS DEL BODY
CSS Global (líneas 156-201)

@page {
    size: letter;
    margin: 2cm 1.5cm;
}

body {
    font-family: Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.0;
    color: #333;
    mso-line-height-rule: exactly;
}

p { 
    margin: 2px 0; 
    line-height: 1.0; 
    mso-line-height-rule: exactly; 
}

br { 
    mso-data-placement: same-cell; 
}

table { 
    border-collapse: collapse; 
}
Valores de Referencia - Body
Propiedad	Valor	Descripción
font-family	Arial, sans-serif	Fuente estándar Windows
font-size	10pt	Tamaño base del texto
line-height	1.0	Interlineado simple (más compacto que PDF)
color	#333	Gris oscuro
mso-line-height-rule	exactly	Directiva Word - fuerza interlineado exacto
margin @page	2cm 1.5cm	Márgenes: 2cm arriba/abajo, 1.5cm lados
size @page	letter	Tamaño carta
3. SECCIONES
CSS Secciones (líneas 171-182)

.seccion { 
    margin-bottom: 6px; 
}

.seccion-titulo {
    font-size: 11pt;
    font-weight: bold;
    color: #0d6efd;
    border-bottom: 1px solid #ccc;
    padding-bottom: 2px;
    margin-bottom: 4px;
    margin-top: 8px;
    line-height: 1.0;
}

.seccion-contenido { 
    text-align: justify; 
    line-height: 1.0; 
}
Comparación PDF vs Word - Secciones
Propiedad	PDF	Word
margin-bottom sección	8px	6px
border-bottom título	#e9ecef	#ccc
padding-bottom título	3px	2px
margin-bottom título	5px	4px
line-height contenido	1.2	1.0
4. PÁRRAFOS
CSS Párrafos (línea 168)

p { 
    margin: 2px 0; 
    line-height: 1.0; 
    mso-line-height-rule: exactly; 
}
Generación en PHP (líneas 82, 94)

// Párrafos desde tags HTML
'<p style="margin: 2px 0;">'

// Párrafos desde texto plano
$resultado[] = '<p style="margin: 2px 0;">' . $lineaProcesada . '</p>';
Comparación PDF vs Word - Párrafos
Propiedad	PDF	Word
margin	3px 0	2px 0
line-height	heredado (1.15)	1.0
mso-line-height-rule	N/A	exactly
5. LISTAS (UL/OL/LI)
CSS Listas (líneas 199-200)

ul { 
    margin: 2px 0 2px 15px; 
    padding-left: 0; 
    line-height: 1.0; 
}

li { 
    margin-bottom: 1px; 
    line-height: 1.0; 
}
Generación en PHP (líneas 83-86, 110)

// Listas ordenadas
'<ol style="margin: 2px 0 2px 15px; padding-left: 15px;">'

// Listas no ordenadas  
'<ul style="margin: 2px 0 2px 15px; padding-left: 0;">'

// Agrupación automática
'<ul style="margin: 3px 0 3px 15px; padding-left: 0;">$0</ul>'
Comparación PDF vs Word - Listas
Propiedad	PDF	Word
margin UL	3px 0 3px 15px	2px 0 2px 15px
margin-bottom LI	2px	1px
line-height	heredado	1.0
6. TABLAS DE CONTENIDO
CSS Tablas (líneas 183-198)

table.tabla-contenido {
    width: 100%;
    border-collapse: collapse;
    margin: 4px 0;
    font-size: 9pt;
}

table.tabla-contenido th,
table.tabla-contenido td {
    border: 1px solid #999;
    padding: 2px 4px;
}

table.tabla-contenido th {
    background-color: #0d6efd;
    color: white;
    font-weight: bold;
}
Generación en PHP - Tabla Markdown (líneas 120-139)

$html = '<table class="tabla-contenido" style="width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt;">';

// Encabezados
'<th style="border: 1px solid #999; padding: 4px 6px; background-color: #0d6efd; color: white; font-weight: bold;">'

// Celdas normales
'<td style="border: 1px solid #999; padding: 3px 5px;">'
Comparación PDF vs Word - Tablas
Propiedad	PDF	Word
margin tabla	10px 0	4px 0 (CSS) / 8px 0 (inline)
padding TH	5px 8px	4px 6px
padding TD	5px 8px	3px 5px
text-align TH	center	sin definir (left)
7. FORMATO DE TEXTO
Conversión Markdown → HTML (líneas 69-80)

// Negrita: **texto** → <b>texto</b>
$lineaProcesada = str_replace(['{{BOLD_START}}', '{{BOLD_END}}'], ['<b>', '</b>'], $lineaProcesada);

// Cursiva: *texto* → <i>texto</i>
$lineaProcesada = str_replace(['{{ITALIC_START}}', '{{ITALIC_END}}'], ['<i>', '</i>'], $lineaProcesada);
Diferencia PDF vs Word
Formato	PDF	Word
Negrita	<strong>	<b>
Cursiva	<em>	<i>
Nota: Word interpreta mejor <b> y <i> que las versiones semánticas.

8. DIRECTIVAS ESPECÍFICAS WORD (MSO)
Propiedades MSO usadas

mso-line-height-rule: exactly;  /* Fuerza interlineado exacto */
mso-data-placement: same-cell;  /* Control de saltos de línea en celdas */
Declaración XML Word (líneas 148-155)

<!--[if gte mso 9]>
<xml>
    <w:WordDocument>
        <w:View>Print</w:View>
        <w:Zoom>100</w:Zoom>
    </w:WordDocument>
</xml>
<![endif]-->
Directiva	Propósito
w:View	Vista de impresión por defecto
w:Zoom	Zoom al 100%
mso-line-height-rule	Controla cálculo de altura de línea
9. SECCIÓN CONTROL DE CAMBIOS
HTML (líneas 281-307)

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
    </table>
</div>
Comparación PDF vs Word - Control Cambios
Propiedad	PDF	Word
margin-top contenedor	25px	20px
padding título	8px 12px	5px 8px
10. PIE DE DOCUMENTO
HTML (líneas 620-623)

<div style="margin-top:20px; padding-top:10px; border-top:1px solid #ccc; text-align:center; font-size:8pt; color:#666;">
    <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
    <p>Documento generado el <?= date('d/m/Y') ?></p>
</div>
Comparación PDF vs Word - Pie
Propiedad	PDF	Word
margin-top	15px	20px
padding-top	8px	10px
border-top	#ccc	#ccc
11. PALETA DE COLORES WORD
Uso	Código Hex
Texto principal	#333
Títulos sección	#0d6efd
Bordes tabla	#999
Fondo TH tabla principal	#0d6efd
Texto TH principal	white
Fondo TH secundario	#e9ecef
Texto TH secundario	#333
Borde título sección	#ccc
Borde pie documento	#ccc
Texto pie/secundario	#666
Fondo Control Cambios	#0d6efd
Fondo Firmas	#198754
Fondo instrucciones	#e7f3ff
Borde instrucciones	#0d6efd
12. TIPOGRAFÍA WORD
Elemento	Fuente	Tamaño	Peso	Color
Body	Arial	10pt	normal	#333
Título sección	Arial	11pt	bold	#0d6efd
Contenido	Arial	10pt	normal	#333
Tablas	Arial	9pt	normal	#333
TH tablas	Arial	9pt	bold	white
Pie documento	Arial	8pt	normal	#666
Info encabezado	Arial	8pt	normal/bold	#333
Firmas	Arial	8pt	normal	#333
Labels firmas	Arial	6-7pt	normal	#666
13. COMPARACIÓN GLOBAL PDF vs WORD
Aspecto	PDF	Word
Fuente	DejaVu Sans	Arial
Line-height body	1.15	1.0
Line-height contenido	1.2	1.0
Margin párrafo	3px 0	2px 0
Margin sección	8px	6px
Margin tabla	10px 0	4px 0
Padding TD	5px 8px	3px 5px
Border título	#e9ecef	#ccc
Negrita tag	<strong>	<b>
Cursiva tag	<em>	<i>
Directivas MSO	No	Sí
14. SNIPPET REUTILIZABLE - CUERPO WORD

<!-- SECCIÓN ESTÁNDAR WORD -->
<div class="seccion" style="margin-bottom: 6px;">
    <div class="seccion-titulo" style="font-size: 11pt; font-weight: bold; color: #0d6efd; border-bottom: 1px solid #ccc; padding-bottom: 2px; margin-bottom: 4px; margin-top: 8px; line-height: 1.0;">
        TÍTULO DE LA SECCIÓN
    </div>
    <div class="seccion-contenido" style="text-align: justify; line-height: 1.0;">
        <p style="margin: 2px 0; line-height: 1.0;">Contenido del párrafo...</p>
        
        <ul style="margin: 2px 0 2px 15px; padding-left: 0; line-height: 1.0;">
            <li style="margin-bottom: 1px; line-height: 1.0;">Item de lista</li>
        </ul>
        
        <table style="width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 9pt;">
            <tr>
                <th style="border: 1px solid #999; padding: 4px 6px; background-color: #0d6efd; color: white; font-weight: bold;">Encabezado</th>
            </tr>
            <tr>
                <td style="border: 1px solid #999; padding: 3px 5px;">Contenido</td>
            </tr>
        </table>
    </div>
</div>
Comando para Replicar
"Usa el estilo de cuerpo WORD estándar: fuente Arial 10pt #333, line-height 1.0 con mso-line-height-rule:exactly, títulos 11pt bold #0d6efd con border-bottom #ccc, párrafos margin 2px, tablas 9pt con TH #0d6efd white padding 4px 6px, TD padding 3px 5px, bordes #999"