Estilos SECCIÓN FIRMAS WORD - Referencia Técnica

## ⚠️ REGLA CRÍTICA DE AUDITORÍA

**TODOS los documentos técnicos del SG-SST DEBEN incluir la firma de "Elaboró / Consultor SST"**.
Esta regla es OBLIGATORIA para cumplimiento de auditorías de la Resolución 0312/2019.

### Estructura mínima de firmas para auditoría:

- **ELABORÓ**: Consultor SST (quien redacta el documento técnico)
- **APROBÓ**: Representante Legal (máxima autoridad)
- **REVISÓ** (opcional según estándares): Vigía SST / Delegado SST / COPASST

**NO se permite** generar documentos técnicos sin la firma del Consultor/Elaboró.

---

## 1. TIPOS DE FIRMAS DISPONIBLES

El sistema determina automáticamente el tipo de firma según:

Variable	Condición	Tipo de Firma
$esFirmaFisica	tipo_firma === 'fisica' o tipo_documento === 'responsabilidades_trabajadores_sgsst'	Tabla múltiples trabajadores
$soloFirmaConsultor	solo_firma_consultor o tipo_documento === 'responsabilidades_responsable_sgsst'	1 firmante: Consultor
$soloFirmaRepLegal	solo_firma_rep_legal	2 firmantes: Elaboró (Consultor) + Aprobó (Rep. Legal) **CORREGIDO**
$firmasRepLegalYSegundo	Doc responsabilidades rep legal + segundo firmante	3 firmantes: Elaboró (Consultor) + Aprobó (Rep. Legal) + Revisó (Vigía/Delegado) **CORREGIDO**
$esSoloDosFirmantes	estandares <= 10 y no requiere delegado	2 firmantes: Elaboró (Consultor) + Aprobó (Rep. Legal)
Default	estandares > 10 o requiere delegado	3 firmantes: Elaboró (Consultor) + Revisó (Vigía/COPASST) + Aprobó (Rep. Legal)
2. TÍTULO DE LA SECCIÓN
Código (línea 421-422)

<div style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
        FIRMAS DE APROBACION
    </div>
Estilos del Título
Propiedad	Valor
margin-top (contenedor)	20px
background-color	#198754 (Verde Bootstrap)
color	white
padding	5px 8px
border	none
Variantes del Título
Condición	Texto
$soloFirmaConsultor o $soloFirmaRepLegal	FIRMA DE ACEPTACION
$firmasRepLegalYSegundo	FIRMAS DE ACEPTACION
Default	FIRMAS DE APROBACION
3. TIPO A: SOLO FIRMA CONSULTOR (1 Firmante)
Código (líneas 425-448)

<table border="1" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
    <tr>
        <td width="100%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 9pt;">
            RESPONSABLE DEL SG-SST
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 10px; border: 1px solid #999; font-size: 8pt; text-align: center;">
            <p style="margin: 2px 0;"><b>Nombre:</b> <?= $consultorNombre ?></p>
            <p style="margin: 2px 0;"><b>Documento:</b> <?= $consultorCedula ?></p>
            <p style="margin: 2px 0;"><b>Licencia SST:</b> <?= $consultorLicencia ?></p>
            <p style="margin: 2px 0;"><b>Cargo:</b> <?= $consultorCargo ?></p>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px; text-align: center; border: 1px solid #999; height: 60px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 40%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 7pt;">Firma</span>
            </div>
        </td>
    </tr>
</table>
Estructura Visual

┌─────────────────────────────────────────────────────────────────┐
│                    RESPONSABLE DEL SG-SST                       │
│            (#e9ecef, center, bold, 9pt)                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Nombre: _______________                                        │
│  Documento: _______________                                     │
│  Licencia SST: _______________                                  │
│  Cargo: Consultor SST / Responsable del SG-SST                  │
│  (center, 8pt)                                                  │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                    ─────────────────                            │
│                         Firma                                   │
│                     (height: 60px)                              │
└─────────────────────────────────────────────────────────────────┘
4. TIPO B: SOLO FIRMA REP. LEGAL (1 Firmante)
Código (líneas 450-475)

<table border="1" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
    <tr>
        <td width="100%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 9pt;">
            REPRESENTANTE LEGAL
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 10px; border: 1px solid #999; font-size: 8pt; text-align: center;">
            <p style="margin: 2px 0;"><b>Nombre:</b> <?= $repLegalNombre ?></p>
            <p style="margin: 2px 0;"><b>Documento:</b> <?= $repLegalCedula ?></p>
            <p style="margin: 2px 0;"><b>Cargo:</b> <?= $repLegalCargo ?></p>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px; text-align: center; border: 1px solid #999; height: 60px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 40%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 7pt;">Firma</span>
            </div>
        </td>
    </tr>
</table>
5. TIPO C: RESPONSABILIDADES REP. LEGAL (3 Firmantes) **CORREGIDO**

⚠️ **IMPORTANTE**: Este tipo fue corregido para incluir la firma del Consultor (Elaboró).
Anteriormente solo tenía 2 firmantes, ahora tiene 3 para cumplir con auditorías.

Código (líneas 477-522)

<table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
    <tr>
        <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            ELABORÓ
        </td>
        <td width="34%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            APROBÓ
        </td>
        <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            REVISÓ
        </td>
    </tr>
    <tr>
        <!-- CONSULTOR SST (ELABORÓ) -->
        <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
            <p style="margin: 2px 0;"><b>Licencia:</b> ___</p>
        </td>
        <!-- REPRESENTANTE LEGAL (APROBÓ) -->
        <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Documento:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
        </td>
        <!-- VIGIA/DELEGADO SST (REVISÓ) -->
        <td style="vertical-align: top; padding: 8px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Documento:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Vigía SST / Delegado SST</p>
        </td>
    </tr>
    <tr>
        <!-- Fila de firmas alineadas -->
        <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 6pt;">Firma</span>
            </div>
        </td>
        <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 6pt;">Firma</span>
            </div>
        </td>
        <td style="padding: 8px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 6pt;">Firma</span>
            </div>
        </td>
    </tr>
</table>

Estructura Visual

┌──────────────────┬───────────────────┬──────────────────┐
│     ELABORÓ      │      APROBÓ       │     REVISÓ       │
│      (33%)       │      (34%)        │      (33%)       │
│    #e9ecef       │     #e9ecef       │    #e9ecef       │
├──────────────────┼───────────────────┼──────────────────┤
│                  │                   │                  │
│  Nombre: ___     │  Nombre: ___      │  Nombre: ___     │
│  Cargo:          │  Documento: ___   │  Documento: ___  │
│  Consultor SST   │  Cargo:           │  Cargo:          │
│  Licencia: ___   │  Rep. Legal       │  Vigía/Delegado  │
│                  │                   │                  │
├──────────────────┼───────────────────┼──────────────────┤
│                  │                   │                  │
│  ─────────────   │  ─────────────    │  ─────────────   │
│     Firma        │     Firma         │     Firma        │
│  (height: 50px)  │  (height: 50px)   │  (height: 50px)  │
└──────────────────┴───────────────────┴──────────────────┘
6. TIPO D: CONSULTOR + REP. LEGAL (2 Firmantes - 7 Estándares)
Código (líneas 524-556)

<table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
    <tr>
        <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            Elaboro / Consultor SST
        </td>
        <td width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            Aprobo / Representante Legal
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 6px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
            <p style="margin: 2px 0;"><b>Licencia:</b> ___</p>
        </td>
        <td style="vertical-align: top; padding: 6px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
        </td>
    </tr>
    <tr>
        <td style="padding: 6px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 7pt;">Firma</span>
            </div>
        </td>
        <td style="padding: 6px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 7pt;">Firma</span>
            </div>
        </td>
    </tr>
</table>
7. TIPO E: 3 FIRMANTES (Estándar 21+ Estándares)
Código (líneas 558-615)

<table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
    <tr>
        <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            Elaboro
        </td>
        <td width="34%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            Reviso / Vigia SST
        </td>
        <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
            Aprobo
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
        </td>
        <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Vigía SST / COPASST</p>
        </td>
        <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
            <p style="margin: 2px 0;"><b>Nombre:</b> ___</p>
            <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
        </td>
    </tr>
    <tr>
        <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 6pt;">Firma</span>
            </div>
        </td>
        <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 6pt;">Firma</span>
            </div>
        </td>
        <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
            <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                <span style="color: #666; font-size: 6pt;">Firma</span>
            </div>
        </td>
    </tr>
</table>
Estructura Visual

┌──────────────────┬───────────────────┬──────────────────┐
│     Elaboro      │      Reviso       │     Aprobo       │
│      (33%)       │      (34%)        │      (33%)       │
│    #e9ecef       │     #e9ecef       │    #e9ecef       │
├──────────────────┼───────────────────┼──────────────────┤
│                  │                   │                  │
│  Nombre: ___     │  Nombre: ___      │  Nombre: ___     │
│  Cargo:          │  Cargo:           │  Cargo:          │
│  Consultor SST   │  Vigía/COPASST    │  Rep. Legal      │
│                  │                   │                  │
├──────────────────┼───────────────────┼──────────────────┤
│                  │                   │                  │
│  ─────────────   │  ─────────────    │  ─────────────   │
│     Firma        │     Firma         │     Firma        │
│  (height: 45px)  │  (height: 45px)   │  (height: 45px)  │
└──────────────────┴───────────────────┴──────────────────┘
8. TIPO F: FIRMA FÍSICA (Trabajadores)
Código (líneas 354-416)

<!-- Salto de página -->
<br clear="all" style="page-break-before:always">

<!-- Encabezado repetido -->
<table><!-- Mismo encabezado del documento --></table>

<!-- Instrucciones -->
<div style="background: #e7f3ff; padding: 8px 10px; margin-bottom: 12px; border-left: 3px solid #0d6efd; font-size: 9pt;">
    <b>Instrucciones:</b> Con mi firma certifico haber leido...
</div>

<!-- Tabla de firmas -->
<table class="tabla-contenido" style="width: 100%; border-collapse: collapse; font-size: 8pt;">
    <tr>
        <th style="width: 30px; background-color: #f8f9fa; border: 1px solid #333;">No.</th>
        <th style="width: 70px; background-color: #f8f9fa; border: 1px solid #333;">Fecha</th>
        <th style="background-color: #f8f9fa; border: 1px solid #333;">Nombre Completo</th>
        <th style="width: 80px; background-color: #f8f9fa; border: 1px solid #333;">Cedula</th>
        <th style="width: 100px; background-color: #f8f9fa; border: 1px solid #333;">Cargo / Area</th>
        <th style="width: 90px; background-color: #f8f9fa; border: 1px solid #333;">Firma</th>
    </tr>
    <?php for ($i = 1; $i <= $filasFirma; $i++): ?>
    <tr>
        <td style="border: 1px solid #333; padding: 6px 5px; text-align: center; height: 22px;"><?= $i ?></td>
        <td style="border: 1px solid #333; padding: 6px 5px;"></td>
        <!-- ... más celdas vacías ... -->
    </tr>
    <?php endfor; ?>
</table>
Columnas Tabla Firma Física
#	Columna	Ancho	Alineación
1	No.	30px	center
2	Fecha	70px	left
3	Nombre Completo	Flexible	left
4	Cédula	80px	left
5	Cargo / Área	100px	left
6	Firma	90px	left
9. ESTILOS COMUNES DE TABLAS DE FIRMA
Tabla Principal

<table border="1" cellpadding="0" cellspacing="0" 
       style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
Propiedad	Valor
width	100%
table-layout	fixed (anchos fijos)
border-collapse	collapse
border	1px solid #999
margin-top	0
Encabezados (TH)

<td style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
Propiedad	Valor
background-color	#e9ecef
color	#333
font-weight	bold
text-align	center
padding	4px
border	1px solid #999
font-size	8pt
Celdas de Datos

<td style="vertical-align: top; padding: 6px; border: 1px solid #999; font-size: 8pt;">
Propiedad	Valor
vertical-align	top
padding	5px - 10px (varía)
border	1px solid #999
font-size	8pt
Celdas de Firma

<td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
        <span style="color: #666; font-size: 6pt;">Firma</span>
    </div>
</td>
Propiedad	Valor
text-align	center
height	45px - 60px (varía)
vertical-align	bottom
Línea firma width	40% - 70% (varía)
Línea firma border-top	1px solid #333
Texto "Firma" color	#666
Texto "Firma" font-size	6pt - 7pt
10. DIMENSIONES SEGÚN TIPO
Tipo	Columnas	Ancho Col	Height Firma	Width Línea
1 Firmante	1	100%	60px	40%
2 Firmantes	2	50%	50-60px	65-70%
3 Firmantes	3	33%/34%/33%	45px	65%
Firma Física	6	Variable	22px	N/A
11. PALETA DE COLORES FIRMAS
Elemento	Color	Hex
Título barra fondo	Verde Bootstrap	#198754
Título barra texto	Blanco	white
TH fondo	Gris claro	#e9ecef
TH texto	Gris oscuro	#333
Bordes tabla	Gris medio	#999
Línea firma	Negro	#333
Texto "Firma"	Gris	#666
Instrucciones fondo	Azul claro	#e7f3ff
Instrucciones borde	Azul	#0d6efd
TH firma física	Gris muy claro	#f8f9fa
Bordes firma física	Negro	#333
12. TIPOGRAFÍA FIRMAS
Elemento	Tamaño
TH encabezados	8pt
TD datos	8pt
Texto "Firma"	6pt - 7pt
Instrucciones	9pt
Tabla firma física	8pt
13. SNIPPET REUTILIZABLE - 3 FIRMANTES WORD

<!-- FIRMAS DE APROBACION - WORD 3 FIRMANTES -->
<div style="margin-top: 20px;">
    <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
        FIRMAS DE APROBACION
    </div>
    <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
        <tr>
            <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Elaboro</td>
            <td width="34%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Reviso / Vigia SST</td>
            <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Aprobo</td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0;"><b>Nombre:</b> ____________</p>
                <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
            </td>
            <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0;"><b>Nombre:</b> ____________</p>
                <p style="margin: 2px 0;"><b>Cargo:</b> Vigia SST</p>
            </td>
            <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                <p style="margin: 2px 0;"><b>Nombre:</b> ____________</p>
                <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
            <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
            <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                    <span style="color: #666; font-size: 6pt;">Firma</span>
                </div>
            </td>
        </tr>
    </table>
</div>
Comando para Replicar
"Usa la sección FIRMAS WORD estándar: título barra #198754 white padding 5px 8px, tabla table-layout:fixed border #999, TH #e9ecef #333 8pt bold center padding 4px, TD 8pt padding 5px, fila firma height 45px vertical-align bottom, línea firma border-top #333 width 65%, texto Firma #666 6pt"

---

## 14. IMÁGENES CON FONDO NEGRO - SOLUCIÓN TRANSPARENCIA PNG

### Problema
Las imágenes PNG con fondo transparente (canal alpha) se renderizan con **fondo negro** en documentos Word/HTML. Esto ocurre porque Word no interpreta correctamente la transparencia de los PNG.

### Causa
- Las imágenes de firma o logos son PNG con transparencia
- El renderizador de Word convierte el canal alpha (transparente) a negro
- Afecta tanto la vista previa HTML como el documento Word descargado

### Solución: Fondo Blanco Explícito

**SIEMPRE** aplicar fondo blanco tanto al contenedor (`<td>`) como a la imagen (`<img>`):

```html
<!-- CORRECTO: Fondo blanco en celda + imagen -->
<td align="center" height="50" bgcolor="#FFFFFF" style="background-color:#ffffff;">
    <img src="<?= $imagenBase64 ?>" width="100" height="40" style="background-color:#ffffff;">
</td>

<!-- INCORRECTO: Sin fondo explícito (aparece negro) -->
<td align="center" height="50">
    <img src="<?= $imagenBase64 ?>" width="100" height="40">
</td>
```

### Atributos Requeridos

| Elemento | Atributo | Valor | Propósito |
|----------|----------|-------|-----------|
| `<td>` | `bgcolor` | `#FFFFFF` | Compatibilidad Word antiguo |
| `<td>` | `style` | `background-color:#ffffff;` | Compatibilidad navegadores |
| `<img>` | `style` | `background-color:#ffffff;` | Fondo directo en imagen |

### Aplicar a Todas las Imágenes Word

#### Logo del Encabezado
```html
<td width="80" rowspan="2" align="center" valign="middle" bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
    <?php if (!empty($logoBase64)): ?>
        <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo" style="background-color:#ffffff;">
    <?php endif; ?>
</td>
```

#### Imágenes de Firma
```html
<table width="160" cellpadding="8" cellspacing="0" style="border:1px solid #ccc; background-color:#fafafa; min-height:60px;">
    <tr>
        <td align="center" height="50" bgcolor="#FFFFFF" style="background-color:#ffffff;">
            <?php if (!empty($firmaImagen)): ?>
                <img src="<?= $firmaImagen ?>" width="100" height="40" style="background-color:#ffffff;">
            <?php endif; ?>
        </td>
    </tr>
</table>
```

### Checklist de Verificación

- [ ] Logo encabezado: `bgcolor="#FFFFFF"` en `<td>` + `style="background-color:#ffffff;"` en `<img>`
- [ ] Firma Consultor: `bgcolor="#FFFFFF"` en `<td>` + `style="background-color:#ffffff;"` en `<img>`
- [ ] Firma Rep. Legal: `bgcolor="#FFFFFF"` en `<td>` + `style="background-color:#ffffff;"` en `<img>`
- [ ] Firma Delegado/Vigía: `bgcolor="#FFFFFF"` en `<td>` + `style="background-color:#ffffff;"` en `<img>`

### Nota Importante

La combinación de `bgcolor` (atributo HTML) y `background-color` (CSS) es **intencional**:
- `bgcolor="#FFFFFF"` → Compatibilidad con Word y navegadores antiguos
- `style="background-color:#ffffff;"` → Compatibilidad con navegadores modernos

**Usar ambos** garantiza que funcione en todos los contextos de renderizado.