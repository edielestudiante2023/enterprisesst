Estilos del Encabezado Estándar PDF - Referencia
Estructura HTML Base

<table class="encabezado-formal" style="width:100%;" cellpadding="0" cellspacing="0">
    <tr>
        <td class="encabezado-logo" rowspan="2" style="width:100px;" valign="middle" align="center">
            <!-- Logo o nombre empresa -->
        </td>
        <td class="encabezado-titulo-central" valign="middle">
            <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
        </td>
        <td class="encabezado-info" rowspan="2" style="width:130px;" valign="middle">
            <!-- Tabla de info: Código, Versión, Fecha -->
        </td>
    </tr>
    <tr>
        <td class="encabezado-titulo-central" valign="middle">
            <div class="nombre-doc">NOMBRE DEL DOCUMENTO</div>
        </td>
    </tr>
</table>
CSS Completo del Encabezado

/* ============================================
   ENCABEZADO FORMAL - ESTILOS ESTÁNDAR
   ============================================ */

/* Fuente base del documento */
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.15;
    color: #333;
}

/* Tabla contenedora del encabezado */
.encabezado-formal {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.encabezado-formal td {
    border: 1px solid #333;
    vertical-align: middle;
}

/* COLUMNA 1: Logo */
.encabezado-logo {
    width: 120px;
    padding: 8px;
    text-align: center;
    background-color: #ffffff;
}

.encabezado-logo img {
    max-width: 100px;
    max-height: 60px;
    background-color: #ffffff;
}

/* COLUMNA 2: Títulos centrales */
.encabezado-titulo-central {
    text-align: center;
    padding: 0;
}

.encabezado-titulo-central .sistema {
    font-size: 10pt;
    font-weight: bold;
    padding: 6px 10px;
    border-bottom: 1px solid #333;
}

.encabezado-titulo-central .nombre-doc {
    font-size: 10pt;
    font-weight: bold;
    padding: 6px 10px;
}

/* COLUMNA 3: Información del documento */
.encabezado-info {
    width: 140px;
    padding: 0;
}

.encabezado-info-table {
    width: 100%;
    border-collapse: collapse;
}

.encabezado-info-table td {
    border: none;
    border-bottom: 1px solid #333;
    padding: 3px 6px;
    font-size: 8pt;
}

.encabezado-info-table tr:last-child td {
    border-bottom: none;
}

.encabezado-info-table .label {
    font-weight: bold;
}
Valores Específicos
Propiedad	Valor Exacto
Fuente	DejaVu Sans, Arial, sans-serif
Color texto	#333
Color borde	#333 (negro)
Fondo logo	#ffffff (blanco)
Tamaño fuente títulos	10pt
Tamaño fuente info	8pt
Peso fuente títulos	bold
Ancho col logo	100px - 120px
Ancho col info	130px - 140px
Padding celdas título	6px 10px
Padding celdas info	3px 6px
Padding logo	8px
Max logo	100px ancho × 60px alto
Código PHP Reutilizable
Cuando necesites replicar el encabezado, usa este snippet:


<!-- Encabezado Formal Estándar -->
<table style="width:100%; border-collapse:collapse; margin-bottom:20px;" cellpadding="0" cellspacing="0">
    <tr>
        <td rowspan="2" style="width:100px; border:1px solid #333; padding:8px; text-align:center; vertical-align:middle; background:#fff;">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
            <?php else: ?>
                <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente']) ?></div>
            <?php endif; ?>
        </td>
        <td style="border:1px solid #333; text-align:center; padding:6px 10px; border-bottom:1px solid #333; vertical-align:middle;">
            <div style="font-size:10pt; font-weight:bold; font-family:DejaVu Sans,Arial,sans-serif; color:#333;">
                SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
            </div>
        </td>
        <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
            <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt; font-weight:bold;">Codigo:</td>
                    <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><?= esc($documento['codigo']) ?></td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt; font-weight:bold;">Version:</td>
                    <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                </tr>
                <tr>
                    <td style="padding:3px 6px; font-size:8pt; font-weight:bold;">Vigencia:</td>
                    <td style="padding:3px 6px; font-size:8pt;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
            <div style="font-size:10pt; font-weight:bold; font-family:DejaVu Sans,Arial,sans-serif; color:#333;">
                <?= esc(strtoupper($titulo ?? 'NOMBRE DEL DOCUMENTO')) ?>
            </div>
        </td>
    </tr>
</table>
Comando para Replicar
Cuando necesites el encabezado estándar en otro documento, solicítamelo así:

"Usa el encabezado estándar PDF con logo, código, versión y fecha de vigencia"

Y usaré exactamente estos estilos:

Fuente: DejaVu Sans, 10pt, #333
Bordes: 1px solid #333
Info: 8pt
Sin colores de fondo excepto blanco en logo