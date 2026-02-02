Estilos del Encabezado Estándar WORD - Referencia Técnica
Flujo de la Ruta
Ruta: GET /documentos-sst/exportar-word/{id} → DocumentosSSTController::exportarWord

Vista: word_template.php

1. ESTRUCTURA DE LA TABLA ENCABEZADO
Código HTML Base (líneas 205-230)

<table width="100%" border="1" cellpadding="0" cellspacing="0" 
       style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
    <tr>
        <!-- COLUMNA 1: Logo -->
        <td width="80" rowspan="2" align="center" valign="middle" 
            bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
            <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
        </td>
        <!-- COLUMNA 2: Título sistema -->
        <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <!-- COLUMNA 3: Info versión -->
        <td width="120" rowspan="2" valign="middle" style="border:1px solid #333; padding:0; font-size:8pt;">
            <!-- Tabla anidada de info -->
        </td>
    </tr>
    <tr>
        <!-- COLUMNA 2 (fila 2): Nombre documento -->
        <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            NOMBRE DEL DOCUMENTO
        </td>
    </tr>
</table>
2. TAMAÑOS DE LA TABLA
Elemento	Atributo	Valor	Descripción
Tabla principal	width	100%	Ocupa todo el ancho disponible
Columna Logo	width	80	80 píxeles fijos
Columna Info	width	120	120 píxeles fijos
Columna Títulos	width	automático	Se expande (resto del espacio)
Margen inferior	margin-bottom	15px	Separación del contenido
Atributos de Tabla Word

<table width="100%" border="1" cellpadding="0" cellspacing="0" 
       style="border-collapse:collapse; border:1px solid #333;">
Atributo	Valor	Propósito
border	1	Borde visible (requerido por Word)
cellpadding	0	Sin padding interno por defecto
cellspacing	0	Sin espacio entre celdas
border-collapse	collapse	Bordes unidos sin espacio
3. TAMAÑO DE LA IMAGEN/LOGO
Dimensiones Exactas

<img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
Propiedad	Valor	Descripción
width	70	70 píxeles de ancho
height	45	45 píxeles de alto
Proporción	70:45 ≈ 1.55:1	Horizontal/apaisado
Comparación PDF vs Word
Formato	Ancho Logo	Alto Logo	Ancho Celda
PDF	80px	50px max	100px
Word	70px	45px	80px
4. COLOR DE FONDO DE LA IMAGEN
Técnica de Doble Declaración (Compatibilidad Word)

<td width="80" rowspan="2" align="center" valign="middle" 
    bgcolor="#FFFFFF" 
    style="border:1px solid #333; padding:5px; background-color:#ffffff;">
Atributo	Valor	Propósito
bgcolor="#FFFFFF"	Blanco	Atributo HTML legacy - Word lo reconoce
background-color:#ffffff	Blanco	CSS estándar - Navegadores modernos
Por qué ambos?
Microsoft Word lee mejor bgcolor (atributo HTML antiguo)
Navegadores modernos usan background-color (CSS)
La doble declaración garantiza compatibilidad en ambos
Valores de Color
Elemento	bgcolor	background-color
Celda Logo	#FFFFFF	#ffffff
Fondo imagen	Heredado del <td>	Heredado
5. ESTILOS CSS COMPLETOS WORD
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
    mso-line-height-rule: exactly;  /* Directiva específica Word */
}

p { 
    margin: 2px 0; 
    line-height: 1.0; 
    mso-line-height-rule: exactly; 
}

table { 
    border-collapse: collapse; 
}
Estilos por Celda del Encabezado
Celda	Estilos
Logo	border:1px solid #333; padding:5px; background-color:#ffffff;
Títulos	border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;
Info versión	border:1px solid #333; padding:0; font-size:8pt;
6. TABLA DE INFO (ANIDADA)

<td width="120" rowspan="2" valign="middle" style="border:1px solid #333; padding:0; font-size:8pt;">
    <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td style="border-bottom:1px solid #333;"><b>Codigo:</b></td>
            <td style="border-bottom:1px solid #333;">DOC-001</td>
        </tr>
        <tr>
            <td style="border-bottom:1px solid #333;"><b>Version:</b></td>
            <td style="border-bottom:1px solid #333;">001</td>
        </tr>
        <tr>
            <td><b>Fecha:</b></td>
            <td>01/02/2026</td>
        </tr>
    </table>
</td>
Propiedad	Valor
Ancho tabla anidada	100% (de la celda padre = 120px)
cellpadding	2
Separador filas	border-bottom:1px solid #333
Última fila	Sin border-bottom
Font-size	8pt
7. VALORES DE REFERENCIA RÁPIDA
Colores
Uso	Código Hex
Texto general	#333
Bordes	#333
Fondo logo	#FFFFFF / #ffffff
Fondo encabezados tabla	#e9ecef
Azul títulos sección	#0d6efd
Verde firmas	#198754
Texto secundario	#666
Fuentes
Elemento	Fuente	Tamaño	Peso
Body	Arial, sans-serif	10pt	normal
Títulos encabezado	Arial	9pt	bold
Info versión	Arial	8pt	normal
Labels info	Arial	8pt	bold
Dimensiones Clave
Elemento	Valor
Ancho celda logo	80px
Ancho celda info	120px
Ancho imagen logo	70px
Alto imagen logo	45px
Padding celda logo	5px
Padding celdas título	5px
Margin-bottom tabla	15px
8. SNIPPET REUTILIZABLE WORD

<!-- ENCABEZADO ESTÁNDAR WORD -->
<table width="100%" border="1" cellpadding="0" cellspacing="0" 
       style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
    <tr>
        <td width="80" rowspan="2" align="center" valign="middle" 
            bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
            <?php if (!empty($logoBase64)): ?>
            <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
            <?php else: ?>
            <b style="font-size:8pt;"><?= esc($cliente['nombre_cliente']) ?></b>
            <?php endif; ?>
        </td>
        <td align="center" valign="middle" 
            style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <td width="120" rowspan="2" valign="middle" 
            style="border:1px solid #333; padding:0; font-size:8pt;">
            <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                <tr><td style="border-bottom:1px solid #333;"><b>Codigo:</b></td>
                    <td style="border-bottom:1px solid #333;"><?= esc($documento['codigo']) ?></td></tr>
                <tr><td style="border-bottom:1px solid #333;"><b>Version:</b></td>
                    <td style="border-bottom:1px solid #333;"><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td></tr>
                <tr><td><b>Fecha:</b></td>
                    <td><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" valign="middle" 
            style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            <?= esc(strtoupper($titulo ?? 'NOMBRE DEL DOCUMENTO')) ?>
        </td>
    </tr>
</table>
Comando para Replicar
Cuando necesites el encabezado Word estándar, solicítalo así:

"Usa el encabezado estándar WORD: tabla 100%, logo 70x45px en celda 80px con bgcolor=#FFFFFF, info 120px, bordes #333, fuente Arial 9pt bold"